<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Saeven
 * Date: 7/2/13
 * Time: 3:23 PM
 * To change this template use File | Settings | File Templates.
 */
namespace CirclicalACLAdmin\Model;


use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Utilities implements ServiceLocatorAwareInterface
{

    public function getObjectManager()
    {
        // ascertain the user repo that BjyAuthorize uses
        $config = $this->getServiceLocator()->get('BjyAuthorize\Config');

        if ( ! isset($config['role_providers']['BjyAuthorize\Provider\Role\ObjectRepositoryProvider'])) {
            throw new InvalidArgumentException(
                'Config for "BjyAuthorize\Provider\Role\ObjectRepositoryProvider" not set'
            );
        }

        $providerConfig = $config['role_providers']['BjyAuthorize\Provider\Role\ObjectRepositoryProvider'];

        if ( ! isset($providerConfig['role_entity_class'])) {
            throw new InvalidArgumentException('role_entity_class not set in the bjyauthorize role_providers config.');
        }

        if ( ! isset($providerConfig['object_manager'])) {
            throw new InvalidArgumentException('object_manager not set in the bjyauthorize role_providers config.');
        }

        $objectManager = $this->getServiceLocator()->get($providerConfig['object_manager']);

        return $objectManager;

    }

    public function getUserRepository()
    {
        $objectManager = $this->getObjectManager();
        try
        {
            $main_config    = $this->getServiceLocator()->get( 'Config' );
            $user_config    = $main_config['zfcuser'];
            $entity_class   = $user_config['user_entity_class'];
        }
        catch( \Exception $x )
        {
            echo $x->getMessage();
        }

        return $objectManager->getRepository( $entity_class );
    }


    private $serviceLocator;

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}