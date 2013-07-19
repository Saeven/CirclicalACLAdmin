<?php


return array(


    'bjyauthorize' => array(

        // Guard listeners to be attached to the application event manager
        'guards'                => array(
            'BjyAuthorize\Guard\Controller' => array(
                array('controller' => 'CirclicalACLAdmin', 'roles' => array( 'admin' ) ),
                array('controller' => 'CirclicalACLRegister', 'roles' => array() )
            ),
        ),

    ),


    'controllers' => array(
        'invokables' => array(
            'CirclicalACLAdmin' => 'CirclicalACLAdmin\Controller\IndexController',
            'CirclicalACLRegister' => 'CirclicalACLAdmin\Controller\RegistrationController'
        ),
    ),


    'service_manager' => array(

        'factories' => array(

        ),

        'initializers' => array(

        ),
    ),


    'view_manager' => array(
        'template_path_stack' => array(
            'CirclicalACLAdmin' => __DIR__ . '/../view',
        ),
    ),


    'circlical-acl-admin' => array(

        /*
         * ID from your role table, assigned to users when they register
         */
        'default_invited_role_id' => 1,


        /*
         * Default welcome message that appears in the invitation box on the admin ACL panel
         */
        'default_welcome_message' => "Hello!\nWe'd would like to invite you to their central application. Please use the link below to access it.",


        /*
         * Not sure what I wanted to do with this here
         */
        'admin_role' => 'user',

        /*
         * Details used to send the invitation email to users as they're added through the ACL panel
         */
        'mail' => array(
            'from_email' => 'from@configureme.circlical.com',
            'from_name' => 'The Corporation',
            'messages' => array(
                'invite' => array(
                    'subject' => "Welcome to Our Control Panel!",
                ),
                'registered' => array(
                    'subject' => "Thanks for registering"
                )
            ),

            /*
             * Used to send invitation, and registration thanks emails
             */
            'smtp' => array(
                'host' => 'configure-me.circlical.com',
                'user' => '',
                'pass' => '',
                'port' => 25,
                'class' => 'login'
            ),
        ),
        /*
         * Use this block to define which fields should be visible on the user-registration page. If you're
         * like me, you've got some secret fields in the user entity that you don't want people to be able
         * to fill out for themselves
         */
        'registration_form' => array(
            'visible_fields' => array(
                'email', 'password', 'passwordVerify', 'firstName', 'lastName'
            ),
        ),
    ),


    'router' => array(
        'routes' => array(

            'circlical-acl-admin' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/acl-admin[/:action]',
                    'defaults' => array(
                        'controller'    => 'CirclicalACLAdmin',
                        'action'        => 'index',
                        'module'        => 'CirclicalACLAdmin'
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:action]',
                            'constraints' => array(
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),

            'circlical-acl-register' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/acl-register/[:id]/[:hash]/',
                    'defaults' => array(
                        'controller'    => 'CirclicalACLRegister',
                        'action'        => 'index',
                        'module'        => 'CirclicalACLAdmin'
                    ),
                    'constraints' => array(
                        'id'     => '[0-9]*',
                        'hash'   => '[A-Z0-9]{40}',
                    ),
                ),
                'may_terminate' => true,
            ),

            'circlical-acl-register-finish' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/acl-register/finish/[:id]/[:hash]/',
                    'defaults' => array(
                        'controller'    => 'CirclicalACLRegister',
                        'action'        => 'finish',
                        'module'        => 'CirclicalACLAdmin'
                    ),
                    'constraints' => array(
                        'id'     => '[0-9]*',
                        'hash'   => '[A-Z0-9]{40}',
                    ),
                ),
                'may_terminate' => true,
            ),
        ),
    ),

    /**
     * Assetic
     */
    'assetic_configuration' => array(

        /*
         * The default assets to load.
         * If the "mixin" option is true, then the listed assets will be merged with any controller / route
         * specific assets. If it is false, the default assets will only be used when no routes or controllers
         * match
         */
        'default' => array(
            'assets' => array(
                '@base_css',
                '@head_js',
            ),

            'options' => array(
                'mixin' => true,
            ),
        ),

        /*
         * In this configuration section, you can define which js, and css resources the module has.
         */
        'modules' => array(

            'sss' => array(

                # module root path for yout css and js files
                'root_path' => __DIR__ . '/../assets',

                # collection od assets
                'collections' => array(

                    'base_css' => array(
                        'assets' => array(
                            'css/*.css'
                        ),
                        'filters' => array(),
                        'options' => array(),
                    ),

                    'head_js' => array(
                        'assets' => array(
                            'js/*.js' // relative to 'root_path'
                        ),
                        'filters' => array(),
                        'options' => array(),
                    ),

                    'base_images' => array(
                        'assets'=> array(
                            'images/*.png'
                        ),
                        'options' => array(
                            'move_raw' => true,
                        )
                    ),
                ),
            ),
        ),
    ),
);
