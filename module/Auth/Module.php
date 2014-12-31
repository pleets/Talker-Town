<?php

namespace Auth;

use Auth\Model\Entity\User;
use Auth\Model\Entity\UsersTable;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Auth\Model\DbAdapter;
use Zend\Authentication\Adapter\DbTable as AuthAdapter;

class Module
{

    public function getAutoloaderConfig()
	{
		return array(
			'Zend\Loader\ClassMapAutoloader' => array(
				__DIR__ . '/autoload_classmap.php',
			),
			'Zend\Loader\StandardAutoloader' => array(
				'namespaces' => array(
					__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
				),
			),
		);
	}

	public function getConfig()
	{
		return include __DIR__ . '/config/module.config.php';
	}

	public function getServiceConfig()
	{
		return array(
			'factories' => array(
				'Auth\Model\Entity\UsersTable' =>  function($sm) {
					$tableGateway = $sm->get('UsersTableGateway');
					$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
					$table = new UsersTable($tableGateway, $dbAdapter);
					return $table;
				},
				'UsersTableGateway' => function ($sm) {
					$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
					$resultSetPrototype = new ResultSet();
					$resultSetPrototype->setArrayObjectPrototype(new User());
					return new TableGateway('users', $dbAdapter, null, $resultSetPrototype);
				},
				'AuthAdapter' => function ($sm) {
					$DbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
					$authAdapter = new AuthAdapter($DbAdapter);
					$authAdapter
						->setTableName('users')
						->setIdentityColumn('users_id')
						->setCredentialColumn('password')
					;
					return $authAdapter;
				},
			),
		);
	}
}
