<?php
namespace Activity;

use User\Permissions\Assertion\IsOrganMember;;

class Module
{

    /**
     * Get the autoloader configuration.
     *
     * @return array Autoloader config
     */
    public function getAutoloaderConfig()
    {
        if (APP_ENV === 'production') {
            return [
                'Zend\Loader\ClassMapAutoloader' => [
                    __DIR__ . '/autoload_classmap.php',
                ]
            ];
        }

        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ]
            ]
        ];
    }

    /**
     * Get the configuration for this module.
     *
     * @return array Module configuration
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * Get service configuration.
     *
     * @return array Service configuration
     */
    public function getServiceConfig()
    {
        return [
            'invokables' => [
                'activity_service_activity' => 'Activity\Service\Activity',
                'activity_service_activityQuery' => 'Activity\Service\ActivityQuery',
                'activity_service_activityTranslator' => 'Activity\Service\ActivityTranslator',
                'activity_service_email' => 'Activity\Service\Email', 
                'activity_form_activityfield_fieldset' => 'Activity\Form\ActivityFieldFieldSet',
                'activity_form_activity_signup' => 'Activity\Form\ActivitySignup'
            ],
            'factories' => [
                // fake 'alias' for entity manager, because doctrine uses an abstract factory
                // and aliases don't work with abstract factories
                'activity_doctrine_em' => function ($sm) {
                    return $sm->get('doctrine.entitymanager.orm_default');
                },
                'activity_form_activity' => function ($sm) {
                    $organService = $sm->get('decision_service_organ');
                    $organs = $organService->getEditableOrgans();
                    $translator = $sm->get('translator');
                    $form = new \Activity\Form\Activity($organs, $translator, $sm->get('activity_doctrine_em'));
                    $form->setHydrator($sm->get('activity_hydrator'));
                    return $form;
                },
                'activity_hydrator' => function ($sm) {
                    return new \DoctrineModule\Stdlib\Hydrator\DoctrineObject(
                        $sm->get('activity_doctrine_em')
                    );
                },
                'activity_service_signup' => function ($sm) {
                    $ac = new Service\Signup();
                    $ac->setServiceManager($sm);

                    return $ac;
                },
                'activity_service_signoff' => function ($sm) {
                    $ac = new Service\Signup();
                    $ac->setServiceManager($sm);

                    return $ac;
                },
                'activity_mapper_activity' => function ($sm) {
                    return new \Activity\Mapper\Activity(
                        $sm->get('activity_doctrine_em')
                    );
                },
                'activity_mapper_activity_field_value' => function ($sm) {
                    return new \Activity\Mapper\ActivityFieldValue(
                        $sm->get('activity_doctrine_em')
                    );
                },
                'activity_mapper_activity_option' => function ($sm) {
                    return new \Activity\Mapper\ActivityOption(
                        $sm->get('activity_doctrine_em')
                    );
                },
                'activity_mapper_proposal' => function ($sm) {
                    return new \Activity\Mapper\Proposal(
                        $sm->get('activity_doctrine_em')
                    );
                },
                'activity_mapper_signup' => function ($sm) {
                    return new \Activity\Mapper\Signup(
                        $sm->get('activity_doctrine_em')
                    );
                },
                'activity_acl' => function ($sm) {
                    $acl = $sm->get('acl');
                    $acl->addResource('activity');
                    $acl->addResource('activitySignup');
                    $acl->addResource('model');

                    $acl->allow('guest', 'activity', 'view');
                    $acl->allow('guest', 'activitySignup', 'view');

                    $acl->allow('user', 'activity', 'create');
                    $acl->allow('user', 'activitySignup', ['signup', 'signoff', 'checkUserSignedUp']);
                    $acl->allow('active_member', 'activity', 'viewDetails');

                    $acl->allow('admin', 'activity', 'update');

                    $acl->allow('sosuser', 'activitySignup', ['signup', 'signoff', 'checkUserSignedUp']);

                    return $acl;
                },
            ]
        ];
    }
}