<?php

namespace Auth\Form;

use Zend\Form\Form;
use Zend\Db\ResultSet\ResultSet;
use Auth\Model\Entity\Roles;
use Auth\Model\Entity\RolesTable;
use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceManager;

class UserForm extends Form
{
    public function __construct($controller = null)
    {
        parent::__construct('frmUsers');

        /*$dbAdapter = $controller->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $tableGateway = new TableGateway('roles', $dbAdapter);

        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new Role());

        $rolesTable = new RolesTable($tableGateway, $dbAdapter, null, $resultSetPrototype);

        $result = $rolesTable->fetchAll()->toArray();*/

        $roles = array();
        /*foreach ($result as $role) {
            $roles[$role["roles_id"]] = $role["rolename"];
        }*/

        $this->add(array(
            'name' => 'users_id',
            'type' => 'hidden',
            'options' => array(
                'label' => 'User ID',
            ),
            'attributes' => array(
                'placeholder' => 'user id',
                'required' => 'required',
                'minlength' => '1',
            ),
        ));

        $this->add(array(
            'name' => 'username',
            'type' => 'text',
            'options' => array(
                'label' => 'Username',
            ),
            'attributes' => array(
                'placeholder' => 'username',
                'autofocus' => 'autofocus',
                'required' => 'required',
                'minlength' => '3',
                'maxlength' => '25',
            ),
        ));

        $this->add(array(
            'name' => 'password',
            'type' => 'text',
            'options' => array(
                'label' => 'Password',
            ),
            'attributes' => array(
                'placeholder' => 'password',
                'required' => 'required',
                'minlength' => '4',
                'maxlength' => '60',
            ),
        ));

        $this->add(array(
            'name' => 'roles_id',
            'type' => 'select',
            'options' => array(
                'label' => 'Role',
                'value_options' => $roles,
            ),
            'attributes' => array(
                'value' => '1',
            ),
        ));

        $this->add(array(
            'name' => 'state',
            'type' => 'select',
            'options' => array(
                'label' => 'State',
                'value_options' => array(
                    '1' => 'Activo',
                    '0' => 'Inactive'
                ),
            ),
            'attributes' => array(
                'value' => '1',
            ),
        ));


        $this->add(array(
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => array(
                'class' => 'ui large submit button',
                'value' => 'Iniciar Sesión'
            ),
        ));

    }
}
