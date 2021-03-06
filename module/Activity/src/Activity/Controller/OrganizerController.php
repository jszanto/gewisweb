<?php

namespace Activity\Controller;

use Activity\Model\Activity;
use Activity\Service\Signup;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container as SessionContainer;
use Activity\Form\ModifyRequest as RequestForm;
use Zend\View\Model\ViewModel;
use DOMPDFModule\View\Model\PdfModel;

/**
 * Controller that gives some additional details for activities, such as a list of email adresses
 * or an export function specially tailored for the organizer.
 */
class OrganizerController extends AbstractActionController
{
    /**
     * Show the email adresses belonging to an Activity
     */
    public function emailAction()
    {
        $id = (int) $this->params('id');
        $queryService = $this->getServiceLocator()->get('activity_service_activityQuery');
        $translatorService = $this->getServiceLocator()->get('activity_service_activityTranslator');
        $langSession = new SessionContainer('lang');

        /** @var $activity Activity*/
        $activity = $queryService->getActivityWithDetails($id);
        $translatedActivity = $translatorService->getTranslatedActivity($activity, $langSession->lang);

        if (is_null($activity)) {
            return $this->notFoundAction();
        }

        return [
            'activity' => $translatedActivity,
        ];
    }

    /**
     * Return the data for exporting activities
     *
     * @return array
     */
    public function exportAction()
    {
        $id = (int) $this->params('id');
        $queryService = $this->getServiceLocator()->get('activity_service_activityQuery');
        $translatorService = $this->getServiceLocator()->get('activity_service_activityTranslator');
        $langSession = new SessionContainer('lang');


        /** @var $activity Activity*/
        $activity = $queryService->getActivityWithDetails($id);
        $translatedActivity = $translatorService->getTranslatedActivity($activity, $langSession->lang);

        if (is_null($activity)) {
            return $this->notFoundAction();
        }

        return [
            'activity' => $translatedActivity,
            'signupData' => $translatorService->getTranslatedSignedUpData($activity, $langSession->lang),
        ];
    }

    public function updateAction()
    {
        $id = (int) $this->params('id');
        $queryService = $this->getServiceLocator()->get('activity_service_activityQuery');

        $activity = $queryService->getActivityWithDetails($id);

        $activityService = $this->getServiceLocator()->get('activity_service_activity');
        $form = $activityService->getForm();

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            $form->setData($postData);

            if ($form->isValid()) {
                $activityService->createUpdateProposal(
                    $activity,
                    $form->getData(\Zend\Form\FormInterface::VALUES_AS_ARRAY),
                    $postData['language_dutch'],
                    $postData['language_english']
                );
                $view = new ViewModel();
                $view->setTemplate('activity/activity/updateSuccess.phtml');
                return $view;
            }
        }
        $form->bind($activity);
        $languages = $queryService->getAvailableLanguages($activity);
        return ['form' => $form, 'activity' => $activity, 'languages' => $languages];
    }

    public function exportPdfAction()
    {
        $pdf = new PdfModel();
        $pdf->setVariables($this->exportAction());
        return $pdf;
    }
}
