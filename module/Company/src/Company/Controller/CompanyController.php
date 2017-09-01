<?php

namespace Company\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class CompanyController extends AbstractActionController
{
    /**
     *
     * Action to display a list of all nonhidden companies
     *
     */
    public function listAction()
    {
        $companyService = $this->getCompanyService();
        $featuredPackage = $companyService->getFeaturedPackage();
        if ($featuredPackage == null) {
            return new ViewModel([
                'companyList' => $companyService->getCompanyList(),
                'translator' => $companyService->getTranslator(),
            ]);
        }
        return new ViewModel([
            'companyList' => $companyService->getCompanyList(),
            'translator' => $companyService->getTranslator(),
            'featuredCompany' => $featuredPackage->getCompany(),
            'featuredPackage' => $featuredPackage,
        ]);

    }

    public function showAction()
    {
        $companyService = $this->getCompanyService();
        $companyName = $this->params('slugCompanyName');
        $company = $companyService->getCompanyBySlugName($companyName);
        if (!is_null($company)) {
            return new ViewModel([
                'company' => $company,
                'translator' => $companyService->getTranslator(),
            ]);
        }

        return $this->notFoundAction();
    }

    /**
     *
     * Action that shows the 'company in the spotlight' and the article written by the company in the current language
     *
     */
    public function spotlightAction()
    {
        $companyService = $this->getCompanyService();
        $translator = $companyService->getTranslator();

        $featuredPackage = $companyService->getFeaturedPackage();
        if (!is_null($featuredPackage)) {
            // jobs for a single company
            return new ViewModel([
                'company' => $featuredPackage->getCompany(),
                'featuredPackage' => $featuredPackage,
                'translator' => $translator,
            ]);
        }

        // There is no company is the spotlight, so throw a 404
        $this->getResponse()->setStatusCode(404);
    }

    /**
     *
     * Action that displays a list of all jobs (facaturebank)
     *
     */
    public function jobListAction()
    {
        $companyService = $this->getCompanyService();
        $companyName = $this->params('slugCompanyName');
        if (isset($companyName)) {
            // jobs for a single company
            return new ViewModel([
                'company' => $companyService->getCompanyBySlugName($companyName),
                'jobList' => $companyService->getJobsByCompanyName($companyName),
                'translator' => $companyService->getTranslator(),
                'randomize' => false,
            ]);
        }
        // all jobs
        return new ViewModel([
            'jobList' => $companyService->getJobList(),
            'translator' => $companyService->getTranslator(),
            'randomize' => true,
        ]);
    }

    /**
     *
     * Action to list jobs of a certain company
     *
     */
    public function jobsAction()
    {
        $companyService = $this->getCompanyService();
        $jobName = $this->params('slugJobName');
        $companyName = $this->params('slugCompanyName');
        if ($jobName != null) {
            $jobs = $companyService->getJobsBySlugName($companyName, $jobName);
            if (count($jobs) != 0) {
                return new ViewModel([
                    'job' => $jobs[0],
                    'translator' => $companyService->getTranslator(),
                ]);
            }
            return $this->notFoundAction();
        }
        return new ViewModel([
            'activeJobList' => $companyService->getActiveJobList(),
            'translator' => $companyService->getTranslator(),
        ]);
    }

    /**
     * Method that returns the service object for the company module.
     *
     *
     */
    protected function getCompanyService()
    {
        return $this->getServiceLocator()->get('company_service_company');
    }
}
