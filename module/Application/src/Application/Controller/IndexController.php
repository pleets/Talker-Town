<?php

/*
 * Index Module
 * http://www.pleets.org
 *
 * Copyright 2014, Pleets Apps
 * Free to use under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Auth\Form\UserForm;

class IndexController extends AbstractActionController
{
    private function getUsersTable()
    {
        if (!$this->usersTable)
            $this->usersTable = $this->getServiceLocator()->get('Users\Model\Entity\UsersTable');
        return $this->usersTable;
    }

    public function indexAction()
    {
        $data = array();

        $xmlHttpRequest = $this->getRequest()->isXmlHttpRequest();
        $data['xmlHttpRequest'] = $xmlHttpRequest;

        $form = new UserForm();
        $form->get('submit')->setValue('Login');
        $data['form'] = $form;

        $request = $this->getRequest();

        if ($request->isPost()) 
        {
            try {
                $user = new User();
                $form->setInputFilter($user->getInputFilter());
                $form->setData($request->getPost());

                if ($form->isValid())
                {
                    /* Login validation */

                    $data['Success'] = true;
                }
            }
            catch (\Exception $e) {
                
                $data['Exception'] = $e->getMessage();
                $view = new ViewModel($data);
                
                if ($xmlHttpRequest)
                    $view->setTerminal(true);
                return $view;
            }      
        }

        $view = new ViewModel($data);
            
        if ($xmlHttpRequest)
            $view->setTerminal(true);
        return $view;
    }

    public function loginAction()
    {
    	
    	
        return new ViewModel();
    }
}
