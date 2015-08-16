<?php

namespace Company\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AdminController extends AbstractActionController
{

    public function indexAction()
    {
        $companyService = $this->getCompanyService();
        
        $vm = new ViewModel(array(
            'companyList' => $companyService->getHiddenCompanyList()
        ));
        
        return $vm;

    }
    
    public function addPacketAction()
    {
        $companyService = $this->getCompanyService();
        $packetForm = $companyService->getPacketForm();
        $request = $this->getRequest();
        $companyName = $this->params('slugCompanyName');    
        if ($request->isPost()) {
            $companyService = $this->getCompanyService();
            $packetForm->setData($request->getPost());

            // TODO: isValid does not work yet
            //if ($companyForm->isValid()) {
            //    $company = $companyService->insertCompany();
            //    $company->exchangeArray($request->getPost()); // Temporary fix, bind does not work yet?
            //    $companyService->saveCompany();
            //    return $this->redirect()->toRoute('admin_company/default', 
             //                                     array('action'=>'edit', 
             //                                           'slugCompanyName' => $companyName), 
             //                                     array(), false);   
            //}
            //if ($companyForm->isValid()) {
                $packet=$companyService->insertPacketForCompanySlugName($companyName);
                $packet->exchangeArray($request->getPost()); 
                $companyService->savePacket();
                return $this->redirect()->toRoute('admin_company/editCompany', array('slugCompanyName'=>$companyName),array(),false);   
            //}
        }
        //$company = $companyService->insertCompany();
        //$companyForm->bind($company);
        $packetForm->setAttribute('action', 
                                   $this->url()->fromRoute('admin_company/editCompany/addPacket', 
                                                           array('slugCompanyName' => $companyName))
                                  );
        $vm = new ViewModel(array(
          //  'company' => $company,
            'companyEditForm' => $packetForm,
        ));
        
        return $vm;
        
    }
    public function addCompanyAction()
    {
        $companyService = $this->getCompanyService();
        $companyForm = $companyService->getCompanyForm();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if (!isset($companyName)){
                $companyName = $request->getPost()['slugName'];
            }
            $companyService = $this->getCompanyService();
            $companyForm = $companyService->getCompanyForm();
            $companyForm->setData($request->getPost());

            // TODO: isValid does not work yet
            //if ($companyForm->isValid()) {
            //    $company = $companyService->insertCompany();
            //    $company->exchangeArray($request->getPost()); // Temporary fix, bind does not work yet?
            //    $companyService->saveCompany();
            //    return $this->redirect()->toRoute('admin_company/default', 
             //                                     array('action'=>'edit', 
             //                                           'slugCompanyName' => $companyName), 
             //                                     array(), false);   
            //}
            //if ($companyForm->isValid()) {
                $company=$companyService->insertCompany($request->getPost()["languages"]);
                $company->exchangeArray($request->getPost()); 
                $companyService->saveCompany();
                //var_dump($company);
                return $this->redirect()->toRoute('admin_company/default', array('action'=>'edit', 'slugCompanyName'=>$companyName),array(),false);   
            //}
        }
        //$company = $companyService->insertCompany();
        //$companyForm->bind($company);
        $companyForm->setAttribute('action', 
                                   $this->url()->fromRoute('admin_company/default', 
                                                           array('action' => 'addCompany'))
                                  );
        $vm = new ViewModel(array(
          //  'company' => $company,
            'companyEditForm' => $companyForm,
        ));
        
        return $vm;
        
    }
    public function deletePacketAction(){
        $companyService = $this->getCompanyService();
        $packetID = $this->params('packetID');    
        $companyName = $this->params('slugCompanyName');
        $request = $this->getRequest();
         if ($request->isPost()) {
             $del = $request->getPost('del', 'No');
             if ($del == 'Yes') {
                 $companyService->deletePacket($packetID);
             }

             return $this->redirect()->toRoute('admin_company/editCompany', array('slugCompanyName' => $companyName));
         }
        return new ViewModel(array(
            'packet' => $companyService->getEditablePacket($packetID),
            'slugName' => $companyName,
            'translator' => $companyService->getTranslator()
        ));
    }
    public function deleteCompanyAction(){
        $companyService = $this->getCompanyService();
        $slugName = $this->params('slugCompanyName');    
        $request = $this->getRequest();
         if ($request->isPost()) {
             $del = $request->getPost('del', 'No');
             if ($del == 'Yes') {
                 $companyService->deleteCompaniesWithSlug($slugName);
             }

             return $this->redirect()->toRoute('admin_company');
         }
        return new ViewModel(array(
            'companies' => $companyService->getEditableCompaniesWithSlugName($slugName),
            'translator' => $companyService->getTranslator()
        ));
    }

    public function addJobAction()
    {
        $companyService = $this->getCompanyService();
        $companyName = $this->params('slugCompanyName');    
        $packetId = $this->params('packetID');    
        
        $companyForm = $companyService->getJobForm();
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if (!isset($jobName)){
                $jobName = $request->getPost()['slugName'];
            }
            $companyService = $this->getCompanyService();
            $companyForm = $companyService->getJobForm();
            $companyForm->setData($request->getPost());

            if ($companyForm->isValid()) {
                $job = $companyService->insertJobIntoPacketID($packetId);
                $job->exchangeArray($request->getPost()); 
                $companyService->saveCompany();
                return $this->redirect()->toRoute('admin_company/editCompany/editPacket/editJob', 
                                                  array('slugCompanyName' => $companyName, 
                                                        'packetID' => $packetId,
                                                        'jobName' => $jobName));   
            }
        }

        $companyForm->setAttribute('action', 
                                   $this->url()->fromRoute('admin_company/editCompany/editPacket/addJob', 
                                                           array('slugCompanyName' => $companyName, "packetID" => $packetId))
                                  );
        $vm = new ViewModel(array(
          //  'company' => $company,
            'companyEditForm' => $companyForm,
        ));
        return $vm;
        
    }
    
    public function editPacketAction(){
        $companyService = $this->getCompanyService();
        
        $companyName = $this->params('slugCompanyName');    
        $packetID = $this->params('packetID');    
        $packetForm = $companyService->getPacketForm();
        //$companyList = $companyService->getEditableCompaniesWithSlugName($companyName);
        $packet = $companyService->getEditablePacket($packetID);
        //echo($this->url()->fromRoute('admin_company/default', array('action'=>'save', 'slugCompanyName'=>$companyName)));
        $request = $this->getRequest();
        if ($request->isPost()) {
            if (!isset($packetID)){
                $companyName = $request->getPost()['packetID'];
            }
            $packetForm->setData($request->getPost());

            //if ($companyForm->isValid()) {
                $packet->exchangeArray($request->getPost()); // Temporary fix, bind does not work yet?
                $companyService->saveCompany();
            //} else {
            //    die();
            //    return $this->forward()->dispatch('Company\Controller\AdminController', 
            //                                      array('action'=> 'editCompany', 
            //                                            'form' => $companyForm)
            //                                     );
            //}
        }
        // TODO: display error page when packet is not found
        $packetForm->bind($packet);
        $packetForm->setAttribute('action', 
                                   $this->url()->fromRoute('admin_company/editCompany/editPacket', 
                                                           array('packetID' => $packetID,
                                                                 'slugCompanyName' => $companyName))
                                  );
        $vm = new ViewModel(array(
            'packet' => $packet,
            'companyName' => $companyName,
            'packetEditForm' => $packetForm,
        ));
        
        return $vm;

    }
    
    public function saveJobAction()
    {
        $jobName = $this->params('slugJobName');    
        $slugCompanyName = $this->params('slugCompanyName');    
        $request = $this->getRequest();
        if ($request->isPost()) {
            if (!isset($jobName)){
                $jobName = $request->getPost()['slugName'];
            }
            $companyService = $this->getCompanyService();
            $companyForm = $companyService->getJobForm();
            $companyForm->setData($request->getPost());

            // TODO: isValid does not work yet
            //if ($companyForm->isValid()) {
                $job = $companyService->insertJobForCompanySlugName($slugCompanyName);
                $job->exchangeArray($request->getPost()); // Temporary fix, bind does not work yet?
                $companyService->saveCompany();
            //}
        }

        return $this->redirect()->toRoute('admin_company/default', 
                                          array('action'=>'edit', 
                                                'slugCompanyName' => $slugCompanyName, 
                                                'slugJobName' => $jobName), 
                                          array(), true);   
    }
    
    public function editJobAction()
    {
        $companyService = $this->getCompanyService();
        $packetID = $this->params('packetID');    
        
        $companyName = $this->params('slugCompanyName');    
        $slugCompanyName = $this->params('slugCompanyName');    
        $jobName = $this->params('jobName');    
        $companyForm = $companyService->getJobForm();
        $companyList = $companyService->getEditableJobsWithSlugName($jobName, $companyName);
        //echo($this->url()->fromRoute('admin_company/default', array('action'=>'save', 'slugCompanyName'=>$companyName)));
        if (empty($companyList)){
            $company = null;
            echo "No job found";
        } else {
            $company = $companyList[0];
            $companyForm->bind($company);
            $companyForm->setAttribute('action', 
                                       $this->url()->fromRoute('admin_company/editCompany/editPacket/editJob', 
                                                               array('jobName' => $jobName, 
                                                                     'packetID' => $packetID,
                                                                     'slugCompanyName' => $companyName))
                                      );
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            if (!isset($jobName)){
                $jobName = $request->getPost()['slugName'];
            }
            $companyService = $this->getCompanyService();
            $companyForm = $companyService->getJobForm();
            $companyForm->setData($request->getPost());

            if ($companyForm->isValid()) {
                 echo "VALID";
                $job = $companyService->insertJobForCompanySlugName($slugCompanyName);
                $job->exchangeArray($request->getPost()); // Temporary fix, bind does not work yet?
                $companyService->saveCompany();
            } else {
                echo "NOT VALID";
            }
            return $this->redirect()->toRoute('admin_company/editCompany/editPacket/editJob', 
                                              array('slugCompanyName' => $slugCompanyName, 
                                                    'packetID' => $packetID,
                                                    'slugJobName' => $jobName), 
                                              array(), true);   
        }
        $return = $companyService->getJobsWithCompanySlugName($companyName);
        $vm = new ViewModel(array(
            'joblist' => $return, 
            'companyEditForm' => $companyForm,
        ));
        
        return $vm;

    }
    
    public function editCompanyAction()
    {
        $companyService = $this->getCompanyService();
        
        $companyName = $this->params('slugCompanyName');    
        $companyForm = $companyService->getCompanyForm();
        $companyList = $companyService->getEditableCompaniesWithSlugName($companyName);
        //echo($this->url()->fromRoute('admin_company/default', array('action'=>'save', 'slugCompanyName'=>$companyName)));
        $request = $this->getRequest();
        if ($request->isPost()) {
            if (!isset($companyName)){
                $companyName = $request->getPost()['slugName'];
            }
            $companyService = $this->getCompanyService();
            $companyForm = $companyService->getCompanyForm();
            $companyForm->setData($request->getPost());

            //if ($companyForm->isValid()) {
                $company = $companyService->insertCompany($request->getPost()["languages"]);
                $company->exchangeArray($request->getPost()); // Temporary fix, bind does not work yet?
                $companyService->saveCompany();
            //} else {
            //    return $this->forward()->dispatch('Company\Controller\AdminController', 
            //                                      array('action'=> 'editCompany', 
            //                                            'form' => $companyForm)
            //                                     );
            //}
        }
        if (empty($companyList)){
            $company = null;
        } else {
            $company = $companyList[0];
            $companyForm->bind($company);
            $companyForm->setAttribute('action', 
                                       $this->url()->fromRoute('admin_company/default', 
                                                               array('action' => 'editCompany', 
                                                                     'slugCompanyName' => $companyName))
                                      );
            $companyForm->get('languages')->setValue($company->getArrayCopy()["languages"]);
        }
        $return = $companyService->getJobsWithCompanySlugName($companyName);
        $vm = new ViewModel(array(
            'company' => $company,
            //'slugJobName' => $jobName,
            'joblist' => $return, 
            'companyEditForm' => $companyForm,
        ));
        
        return $vm;

    }
    
    protected function getCompanyService()
    {
        return $this->getServiceLocator()->get("company_service_company");
    }

}
