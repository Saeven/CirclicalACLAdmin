<?php
/**
 * Circlical ACL Admin Module for BjyAuthorize Module
 *
 * @link TBD for the Circlical ACL Admin canonical source repository
 * @link https://github.com/bjyoungblood/BjyAuthorize for the BjyAuthorize canonical source repository
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */

namespace CirclicalACLAdmin;

use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ControllerPluginProviderInterface;
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;
use Zend\Mvc\ApplicationInterface;

/**
 * Circlical ACL Admin Interface for BJYAuthorize
 *
 * @author Alexandre Lemaire <alemaire@circlical.com>
 */
class Module implements
    AutoloaderProviderInterface,
    BootstrapListenerInterface,
    ConfigProviderInterface,
    ControllerPluginProviderInterface,
    ViewHelperProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function onBootstrap(EventInterface $event)
    {

    }


    /**
     * {@inheritDoc}
     */
    public function getViewHelperConfig()
    {
        return array(
            'factories' => array(

            ),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getControllerPluginConfig()
    {
        return array(
            'factories' => array(

            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'invokables' => array(
                'circlical_acl_utilities'	=> 'CirclicalACLAdmin\Model\Utilities',
            ),

            'factories' => array(
                'circlical_mail_transport' => function($instance, $sm) {
                    $config     = $this->getServiceLocator()->get('Config');
                    $config     = $config['circlical-acl-admin']['mail'];

                    $b          = new \Zend\Mail\Transport\Smtp();
                    $b->setOptions(
                        new \Zend\Mail\Transport\SmtpOptions(
                            array(
                                'host'              => $config['smtp']['host'],
                                'connection_class'  => $config['smtp']['class'],
                                'port'              => $config['smtp']['port'],
                                'connection_config' => array(
                                    'username' => $config['smtp']['user'],
                                    'password' => $config['smtp']['pass'],
                                )
                            )
                        )
                    );
                    return $b;
                },
            ),
        );

    }

    /**
     * {@inheritDoc}
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/../../src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }
}
