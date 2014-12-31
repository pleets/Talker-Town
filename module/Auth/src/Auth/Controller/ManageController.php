<?php

namespace Auth\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Result;
use Zend\Crypt\Password\Bcrypt;
use Zend\View\Model\ViewModel;
use Auth\Model\Entity\User;
use Auth\Model\Entity\LogIngress;
use Auth\Model\AclAdapter;
use Auth\Form\UserForm;

class ManageController extends AbstractActionController
{
	private $usersTable;
	private $acl;

    private function authentication()
    {
        $auth = new AuthenticationService();
        if (!$auth->hasIdentity()) {

            $terminal = ($xmlHttpRequest = $this->getRequest()->isXmlHttpRequest()) ? "xmlHttpRequest" : "attemp";

            return $this->redirect()->toRoute('auth',
                array('action' => 'login', 'id' => $terminal)
            );
        }
    }

    private function forceAuthentication()
    {
        $auth = new AuthenticationService();
        $xmlHttpRequest = $this->getRequest()->isXmlHttpRequest();
        if (!$auth->hasIdentity() && $xmlHttpRequest)
            exit;
    }

    private function authenticate()
    {
        $this->authentication();
        $this->forceAuthentication();
    }

    private function getUsersTable()
    {
        if (!$this->userTable) {
            $sm = $this->getServiceLocator();
            $this->userTable = $sm->get('Auth\Model\Entity\UsersTable');
        }
        return $this->userTable;
    }

    private function getIdentity()
    {
        $auth = new AuthenticationService();
        return $auth->getIdentity();
    }

    private function configureAcl()
    {
        $acl = new AclAdapter($this);
        $acl->parseRol($this->getUsersTable()->getPermission($this->getIdentity()->users_id));
        $this->acl = $acl;
        return $acl;
    }

    private function isAllow($resource)
    {
        $acl = new AclAdapter($this);
        $acl->parseRol($this->getUsersTable()->getPermission($this->getIdentity()->cod_usu));
        return $acl->isAllowed($resource);
    }


	public function indexAction()
	{
        $xmlHttpRequest = $this->getRequest()->isXmlHttpRequest();

        return new ViewModel(array(
            'users' => $this->getUsersTable()->search(),
            'num_users' => $this->getUsersTable()->countUsers(),
            'last_user_registered' => $this->getUsersTable()->lastUserRegistered(),
        ));
    }

    public function createDefaultUserAction()
    {
        /* */
    }

    public function loginAction()
    {
        /* */
    }

    public function logoutAction()
    {
        $auth = new AuthenticationService();
        if ($auth->hasIdentity())
            $auth->clearIdentity();

        return $this->redirect()->toRoute('auth', array(
            'action' => 'login'
        ));
    }

}
