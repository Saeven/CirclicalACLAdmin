<?php

namespace CirclicalACLAdmin\Controller;

use CirclicalACLAdmin\Form\RequestValidationForm;
use CirclicalACLAdmin\Form\RequestValidationValidator;
use Zend\Form\Form;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Crypt\Password\Bcrypt;
use Zend\Stdlib\Parameters;

class RegistrationController extends AbstractActionController
{

    private function getVerificationHash( $email, $hash, $id )
    {
        return md5( $email . $hash .  $id );
    }


    /**
     * Process checkvars and get request email if passed
     * @return string Email if passed, null otherwise
     */
    private function checkLocalGuards()
    {
        $id     = $this->params()->fromRoute('id');
        $hash   = $this->params()->fromRoute('hash');

        // check to see if this was already registered
        /* @var $utilities \CirclicalACLAdmin\Model\Utilities */
        $utilities      = $this->getServiceLocator()->get('circlical_acl_utilities');

        /* @var $object_manager \Doctrine\ORM\EntityManager */
        $object_manager = $utilities->getObjectManager();
        $stmt           = $object_manager->getConnection()->executeQuery(
            "SELECT hash_used, email FROM users_acl_extra WHERE id=?",
            array( $id ),
            array( \PDO::PARAM_INT )
        );
        $result     = $stmt->fetch();

        if( $result === false )
        {
            // no such id exists, get outta dodge
            return null;
        }
        else
        {
            return $result['hash_used'] ? null : $result['email'];
        }

        return null;
    }


    public function indexAction()
    {
        $viewModel = new ViewModel();
        $id        = $this->params()->fromRoute('id');
        $hash      = $this->params()->fromRoute('hash');

        if( !($email = $this->checkLocalGuards()) )
            $this->redirect()->toRoute( '/' )->setStatusCode( "201" );

        // if we get here, validate the salt and hash with email
        $form       = new RequestValidationForm();
        $form->setAttribute( 'action', "/acl-register/" . $id . "/" . $hash . "/" );
        $form->setAttribute( 'method', 'post' );
        $form->get('id')->setValue( $id );
        $form->get('hash')->setValue( $hash );

        $request = $this->getRequest();

        if($request->isPost())
        {
            $validator  = new RequestValidationValidator();
            $form->setInputFilter( $validator->getInputFilter() );
            $form->setData($request->getPost());

            // check to ensure that emails match
            if($form->isValid())
            {
                $submitted_email = $form->get( 'email' )->getValue();
                if( strtolower( $submitted_email ) == strtolower( $email ) )
                {
                    $domain = (preg_match( "/^localhost/", $_SERVER['HTTP_HOST'] ) ) ? false : $_SERVER['HTTP_HOST'];
                    setcookie( "authverify", $this->getVerificationHash( $submitted_email, $hash, $id ), 0, "/", $domain, false, true );
                    $this->redirect()->toRoute( 'circlical-acl-register-finish', array( 'id' => $id, 'hash' => $hash ) );
                }
                else
                {
                    $form->get('email')->setMessages( array( "Sorry, that email address doesn't appear in the guest list. For security reasons, please use the email address that was invited." ) );
                }
            }
        }

        $viewModel->setVariable( 'form', $form );

        return $viewModel;
    }

    public function finishAction()
    {


        $viewModel           = new ViewModel();

        $verification_cookie = $this->getRequest()->getCookie()->authverify;
        $id                  = $this->params()->fromRoute('id');
        $hash                = $this->params()->fromRoute('hash');
        $request             = $this->getRequest();
        $service             = $this->getServiceLocator()->get('zfcuser_user_service');


        if( !($email = $this->checkLocalGuards()) )
            $this->redirect()->toRoute( '/' )->setStatusCode( "201" );

        if( $verification_cookie != $this->getVerificationHash( $email, $hash,  $id ) )
            $this->redirect()->toRoute( "circlical-acl-register", array( 'id' => $id, 'hash' => $hash ) );

        /* @var $form \ZfcUser\Form\Register */
        $form = $this->getServiceLocator()->get('zfcuser_register_form');
        $form->setAttribute( 'action', "/acl-register/finish/" . $id . "/" . $hash . "/" );
        $conf = $this->getServiceLocator()->get('config');
        $conf = $conf['circlical-acl-admin'];

        // only show user-visible fields
        foreach( $form->getElements() as $key => $element )
        {
            if( $key != 'submit' && !in_array( $key, $conf['registration_form']['visible_fields'] ) )
                $form->remove( $key );
        }
        $form->get('email')->setValue( $email );
        $form->add( array(
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'circlical-trigger',
            'attributes' => array(
                'value' => '1'
            )
        ));

        if($request->isPost() && $this->params()->fromPost( 'circlical-trigger') )
        {
            $post       = $request->getPost();

            /* @var $service \ZfcUser\Service\User */

            $class      = $service->getOptions()->getUserEntityClass();
            $user       = new $class;
            $hydrator   = $service->getServiceManager()->get('zfcuser_register_form_hydrator');
            $form->setHydrator( $hydrator );
            $form->bind( $user );
            $form->setData( $post );

            if( $form->isValid() )
            {
                try
                {
                    /* @var $user \ZfcUser\Entity\UserInterface */
                    $user   = $form->getData();
                    $bcrypt = new Bcrypt;
                    $bcrypt->setCost($service->getOptions()->getPasswordCost());
                    $user->setPassword($bcrypt->create($user->getPassword()));

                    if ($service->getOptions()->getEnableUsername()) {
                        $user->setUsername($form->get('username')->getValue());
                    }
                    if ($service->getOptions()->getEnableDisplayName()) {
                        $user->setDisplayName($form->get('display_name')->getValue());
                    }

                    if ($service->getOptions()->getEnableUserState()) {
                        if ($service->getOptions()->getDefaultUserState()) {
                            $user->setState($service->getOptions()->getDefaultUserState());
                        }
                    }
                    $service->getEventManager()->trigger(__FUNCTION__, $this, array('user' => $user, 'form' => $form));
                    $service->getUserMapper()->insert($user);

                    /* @var $utilities \CirclicalACLAdmin\Model\Utilities */
                    $default_role = $conf['default_invited_role_id'];
                    $utilities    = $this->getServiceLocator()->get('circlical_acl_utilities');
                    /* @var $object_manager \Doctrine\ORM\EntityManager */
                    $object_manager = $utilities->getObjectManager();
                    $object_manager->getConnection()->executeQuery(
                        "INSERT INTO users_roles ( user_id, role_id ) VALUES ( ?, ? )",
                        array( $user->getId(), $default_role ),
                        array( \PDO::PARAM_INT, \PDO::PARAM_INT )
                    );
                    $this->sendConfirmation( $user );

                    $service->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('user' => $user, 'form' => $form));


                    if ($service->getOptions()->getLoginAfterRegistration())
                    {
                        $post = array();
                        $identityFields = $service->getOptions()->getAuthIdentityFields();
                        if (in_array('email', $identityFields))
                        {
                            $post['identity'] = $user->getEmail();
                        }
                        elseif (in_array('username', $identityFields))
                        {
                            $post['identity'] = $user->getUsername();
                        }
                        $post['credential'] = $form->get('password')->getValue();
                        $request->setPost(new Parameters($post));
                        return $this->forward()->dispatch(\ZfcUser\Controller\UserController::CONTROLLER_NAME, array('action' => 'authenticate'));
                    }
                }
                catch( \Exception $x )
                {
                    die( $x->getMessage() );
                }
            }
        }

        $viewModel->setVariable( 'form', $form );

        return $viewModel;
    }




    private function sendConfirmation( \ZfcUser\Entity\UserInterface $user )
    {
        // figure out the base URL
        $uri        = $this->getRequest()->getUri();
        $scheme     = $uri->getScheme();
        $host       = $uri->getHost();
        $port       = $uri->getPort();
        $base       = sprintf('%s://%s:%s', $scheme, $host, $port );

        $view       = new \Zend\View\Renderer\PhpRenderer();
        $resolver   = new \Zend\View\Resolver\TemplateMapResolver();
        $resolver->setMap( array(
            'registered_email_template' => __DIR__ . '/../../../view/circlical-acl-admin/registration/email_registered.phtml'
        ));
        $view->setResolver($resolver);

        $viewModel  = new \Zend\View\Model\ViewModel();
        $viewModel->setTemplate('registered_email_template')
            ->setVariables(array(
                'url' => $base,
                'user' => $user
            ));
        $content    = $view->render($viewModel);

        // set up the HTML message
        $html       = new \Zend\Mime\Part( $content );
        $html->type = "text/html";
        $body       = new \Zend\Mime\Message();
        $body->setParts( array( $html ) );

        $config     = $this->getServiceLocator()->get('Config');
        $config     = $config['circlical-acl-admin'];


        // set up the container
        $mail       = new \Zend\Mail\Message();
        $mail->setBody( $body );
        $mail->setSubject( $config['mail']['messages']['registered']['subject'] );
        $mail->addFrom( $config['mail']['from_email'], $config['mail']['from_name'] );
        $mail->addTo( $user->getEmail() );
        $mail->setEncoding( "utf-8" );

        // send the message
        $transport  = $this->getServiceLocator()->get('circlical_mail_transport');
        $transport->send( $mail );
    }

}