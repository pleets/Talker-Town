<?php

namespace Auth\Model;

use Zend\Permissions\Acl\Resource\GenericResource as Resource;
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Permissions\Acl\Acl as ZendAclAdapter;

use Zend\Permissions\Acl\Acl;

class AclAdapter
{
	private $acl;
    private $rol;

	public function __construct($controller = null) 
	{
        if (is_null($controller))
        {
            $acl = new ZendAclAdapter();
        }
        else
            $acl = $controller->getServiceLocator()->get('ZendAcl');

        if (!count($acl->getRoles()))
        {
            // Roles
            $administrator = new Role('administrator');
            $guest = new Role('guest');

            // Resources
            $viewDevelopment = new Resource('viewDevelopment');

            $acl->addRole($administrator)
                ->addRole($guest)
            ;

            $acl->addResource($viewUsers)
                ->addResource($viewDevelopment)
            ;

            $acl->addRole($administrator, array('guest'));

            $parents = array('guest', 'administrator');
            $root = new Role('root');
            $acl->addRole($root, $parents);

            $acl->allow('guest', null, array(
                'viewDevelopment'
            ));


            $acl->allow('root', null, array(
                'viewDevelopment'
            ));
        }

		$this->acl = $acl;
	}

	public function getAcl()
	{
		return $this->acl;
	}

    public function parseRol($roles_id)
    {
        $dataBaseRoles = array(
            2 => 'administrator',
            5 => 'guest',
            6 => 'root',
        );

        foreach ($dataBaseRoles as $key => $value) {
            if ($key == $roles_id) {
                $this->rol = $dataBaseRoles[$key];
                return $this;
            }
        }

        return false;
    }

    public function getRol()
    {
        return $this->rol;
    }

    public function isAllowed($resource)
    {
        return $this->acl->isAllowed($this->rol, null, $resource);
    }

}
