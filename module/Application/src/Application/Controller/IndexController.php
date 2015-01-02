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
use Auth\Model\Entity\User;

use Zend\Authentication\AuthenticationService;
use Zend\Session\Container;

class IndexController extends AbstractActionController
{
    private $gendersTable;
    private $anonymousIdentity;

    private function getGendersTable()
    {
        if (!$this->gendersTable)
            $this->gendersTable = $this->getServiceLocator()->get('Application\Model\Entity\GendersTable');
        return $this->gendersTable;
    }

    private function getAnonymousIdentity()
    {
        $session = new Container('anonymous_identity');
        return $session->username;
    }

    private function setAnonymousIdentity($username)
    {
        $session = new Container('anonymous_identity');
        $session->username = $username;        
    }

    public function indexAction()
    {
        $data = array();

        $xmlHttpRequest = $this->getRequest()->isXmlHttpRequest();
        $data['xmlHttpRequest'] = $xmlHttpRequest;

        var_dump($this->getAnonymousIdentity());

        if (!is_null($this->getAnonymousIdentity())) {
            // redirect to general room
        }

        try {
            $form = new UserForm();
            $form->get('submit')->setValue('Login');
            $data['form'] = $form;

            $gendersTable = $this->getGendersTable();
            $data["genders"] = $gendersTable->fetchAll()->toArray();
        }
        catch (\Exception $e) {

            $data['Exception'] = $e->getMessage();
            $view = new ViewModel($data);

            if ($xmlHttpRequest)
                $view->setTerminal(true);
            return $view;
        }

        $view = new ViewModel($data);

        if ($xmlHttpRequest)
            $view->setTerminal(true);
        return $view;
    }

    public function loginAction()
    {
        $data = array();

        $request = $this->getRequest();

        if (!$request->isPost())
            return $this->redirect()->toRoute('home');

        if (!is_null($this->getAnonymousIdentity()))
            return $this->redirect()->toRoute('home');
        else
        {
            $form_data = $this->request->getPost();

            try {
                $user = new User();
                $form = new UserForm($this);

                $form->setValidationGroup('username', 'genders_id');
                $form->setInputFilter($user->getInputFilter());
                $form->setData($request->getPost());

                if ($form->isValid())
                {
                    $this->setAnonymousIdentity($form_data->username);
                    return $this->redirect()->toRoute('home');
                }
            }
            catch (\Exception $e) {
                $data['Exception'] = $e->getMessage();
                $view = new ViewModel($data);
                return $view;
            }
        }
    }

    public function generalRoomAction()
    {
        $view = new ViewModel($data);
        return $view;
    }
}
