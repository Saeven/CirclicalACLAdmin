<?php


namespace CirclicalACLAdmin\Controller;

use Zend\Form\Form;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $viewModel = new ViewModel();
        return $viewModel;
    }


    public function usersAction()
    {
        $viewModel = new ViewModel();
        $viewModel->setTerminal( true );


        /* @var $utilities \CirclicalACLAdmin\Model\Utilities */
        $utilities  = $this->getServiceLocator()->get('circlical_acl_utilities');
        $repository = $utilities->getUserRepository();
        $all_users  = $repository->findAll();

        $config     = $this->getServiceLocator()->get('Config');
        $config     = $config['circlical-acl-admin'];


        $viewModel->setVariable( 'welcome_message', $config['default_welcome_message'] );
        $viewModel->setVariable( 'user_list', $all_users );

        return $viewModel;
    }


    public function inviteUsersAction()
    {
        $model      = new JsonModel();
        $request    = $this->getRequest();

        $emails     = $request->getPost( 'email_addresses');
        $message    = $request->getPost( 'message' );

        $email_validator = new \Zend\Validator\EmailAddress();
        $email_validator->useDeepMxCheck( true );

        $errors     = array();
        $utilities  = $this->getServiceLocator()->get('circlical_acl_utilities');
        $repository = $utilities->getUserRepository();

        foreach( $emails as $e )
        {
            try
            {
                if( !($z = $email_validator->isValid( $e )) )
                {
                    var_dump( $email_validator->getMessages() );
                    throw new \Exception( "This email " . $e . " isn't valid." );
                }

                if( ($user_list = $repository->findByEmail( $e )) )
                {
                    $u  = reset( $user_list );
                    throw new \Exception( "This email is taken by <a href='javascript:CLADM.showUser(" .  $u->getId()  . ");'>this user</a>." );
                }
            }
            catch( \Exception $x )
            {
                $errors['field'][$e] = $x->getMessage();
            }
        }

        $model->setVariable( 'success', count( $errors ) == 0 );
        $model->setVariable( 'errors', $errors );

        // sent out the invites
        if( !count($errors) )
        {
            foreach( $emails as $e )
            {
                try
                {
                    // upsert a salt entry into the check database
                    /* @var $object_manager \Doctrine\ORM\EntityManager */
                    $object_manager = $utilities->getObjectManager();
                    $stmt           = $object_manager->getConnection()->executeQuery( "SELECT id FROM users_acl_extra WHERE email = ?", array( $e ), array( \PDO::PARAM_STR ) );
                    $response       = $stmt->fetch();
                    $id             = $response['id'];

                    $nsalt          = strtoupper( sha1( $e . time() . uniqid( "", true ) ) );

                    if( !$id )
                    {
                        $stmt       = $object_manager->getConnection()->prepare( "INSERT INTO users_acl_extra ( email, create_hash, hash_used ) VALUES ( ?, ?, 0 )" );
                        $stmt->bindValue( 1, $e );
                        $stmt->bindValue( 2, $nsalt );
                        $stmt->execute();
                        $id         = $object_manager->getConnection()->lastInsertId();
                    }
                    else
                    {
                        $object_manager->getConnection()->executeUpdate( "UPDATE users_acl_extra SET create_hash = ?, hash_used = 0 WHERE email=?", array( $nsalt, $e ) );
                    }

                    $this->sendInvitation( $e, $message, $id, $nsalt );
                }
                catch( \Exception $x )
                {
                    echo $x->getMessage();
                }
            }
        }
        return $model;
    }

    private function sendInvitation( $email_address, $message, $id, $nsalt )
    {
        // figure out the base URL
        $uri        = $this->getRequest()->getUri();
        $scheme     = $uri->getScheme();
        $host       = $uri->getHost();
        $port       = $uri->getPort();
        $base       = sprintf('%s://%s:%s', $scheme, $host, $port );
        $url        = "{$base}/acl-register/{$id}/{$nsalt}/";

        $view       = new \Zend\View\Renderer\PhpRenderer();
        $resolver   = new \Zend\View\Resolver\TemplateMapResolver();
        $resolver->setMap( array(
            'invite_email_template' => __DIR__ . '/../../../view/circlical-acl-admin/index/email_invitation.phtml'
        ));
        $view->setResolver($resolver);

        $viewModel  = new \Zend\View\Model\ViewModel();
        $viewModel->setTemplate('invite_email_template')
            ->setVariables(array(
                'message' => nl2br( $message ),
                'url' => $url
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
        $mail->setBody( $body  );
        $mail->setSubject( $config['mail']['messages']['invite']['subject'] );
        $mail->addFrom( $config['mail']['from_email'], $config['mail']['from_name'] );
        $mail->addTo( $email_address );
        $mail->setEncoding( "utf-8" );

        // send the message
        $transport  = $this->getServiceLocator()->get('circlical_mail_transport');
        $transport->send( $mail );
    }
}