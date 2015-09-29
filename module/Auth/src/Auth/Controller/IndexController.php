<?php

namespace Auth\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Result;
use Zend\Crypt\Password\Bcrypt;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

use Auth\Form\UserForm;
use Auth\Model\Entity\User;
use Application\Form\MessageForm;
use Application\Model\Entity\Message;
use Auth\Model\AclAdapter;

class IndexController extends AbstractActionController
{
	private $usersTable;
	private $acl;

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
        /*$auth = new AuthenticationService();
        
        if ($auth->hasIdentity())
            return new ViewModel();*/

        return $this->redirect()->toRoute('auth', array(
            'action' => 'login'
        ));
    }

    public function loginAction()
    {
        $data = array();

        $xmlHttpRequest = $this->getRequest()->isXmlHttpRequest();
        $data['xmlHttpRequest'] = $xmlHttpRequest;

        if (!is_null($this->getAnonymousIdentity()))
            return $this->redirect()->toRoute('application/talker');

        try {
            $form = new UserForm();
            $form->get('submit')->setValue('Login');
            $data['form'] = $form;

            $data["genders"] = array(
                array("genders_id" => 1, "name" => "Male"),
                array("genders_id" => 2, "name" => "Female")
            );
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

    public function attempAction()
    {
        $request = $this->getRequest();

        /* If request is POST try login */
        if ($request->isPost())
        {
            if (!is_null($this->getAnonymousIdentity()))
            {
                $data['Exception'] = "You have other session active, try reload the page or press F5";
                $response = $this->getResponse()->setContent(\Zend\Json\Json::encode( $data ));
                return $response;
            }
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

                        /* create json file with users settings */
                        $user_info = array(
                            "username" => $form_data->username,
                            "avatar" => $form_data->avatar
                        );
                        file_put_contents('data/cache/' . $form_data->username . '.json', json_encode($user_info));

                        $data["user"] = $form_data->username;

                        $response = $this->getResponse()->setContent(\Zend\Json\Json::encode( $data ));
                        return $response;                    
                    }
                    else
                        $data["formErrors"] = $form->getMessages();
                }
                catch (\Exception $e) 
                {
                    $data['Exception'] = $e->getMessage();
                    $response = $this->getResponse()->setContent(\Zend\Json\Json::encode( $data ));
                    return $response;
                }
            }

            $response = $this->getResponse()->setContent(\Zend\Json\Json::encode( $data ));
            return $response;
        }     
    }
}
