<?php


return array(
    'bjyauthorize' => array(
        // default role for unauthenticated users
        'default_role'          => 'guest',

        // default role for authenticated users (if using the
        // 'BjyAuthorize\Provider\Identity\AuthenticationIdentityProvider' identity provider)
        'authenticated_role'    => 'user',

        // Role providers to be used to load all available roles into Zend\Permissions\Acl\Acl
        // Keys are the provider service names, values are the options to be passed to the provider
        'role_providers'        => array(),

        // Resource providers to be used to load all available resources into Zend\Permissions\Acl\Acl
        // Keys are the provider service names, values are the options to be passed to the provider
        'resource_providers'    => array(),

        // Rule providers to be used to load all available rules into Zend\Permissions\Acl\Acl
        // Keys are the provider service names, values are the options to be passed to the provider
        'rule_providers'        => array(),

        // Guard listeners to be attached to the application event manager
        'guards'                => array(),

        // Template name for the unauthorized strategy
        'template'              => 'error/403',
    ),

    'service_manager' => array(

        'factories' => array(

        ),

        'invokables'  => array(

        ),

        'initializers' => array(

        ),
    ),

    'view_manager' => array(
        'template_map' => array(
            'circlical-acl-admin/index' => __DIR__ . '/../view/circlical-acl-admin/index.phtml',
        ),
    ),

    'circlical-acl-admin' => array(

    ),
);
