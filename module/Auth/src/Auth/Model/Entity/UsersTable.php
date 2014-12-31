<?php

namespace Auth\Model\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Sql;

class UsersTable
{
	private $sql;
	private $dbAdapter;
	protected $tableGateway;

	public function __construct(TableGateway $tableGateway, \Zend\Db\Adapter\Adapter $dbAdapter)
	{
		$this->tableGateway = $tableGateway;
		$this->sql = new Sql($dbAdapter);
		$this->dbAdapter = $dbAdapter;
	}

	public function hasUsers()
	{
		return count($this->search("", array("limit" => 1)));
	}

	public function fetchAll()
	{
		$select = $this->sql->select();
		$sql = $select 
				->from('users')
				->join('roles', 'users.roles_id = roles.roles_id', 
					array('rolname'),
					$select::JOIN_LEFT)
		;
		$selectString = $this->sql->getSqlStringForSqlObject($sql);
		$adapter = $this->dbAdapter;
		$results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
		return $results;
	}

	public function search($username = "", $options = null)
	{
		$options["limit"] = isset($options["limit"]) ? $options["limit"]: 30;

		$spec = function (Where $where) use ($username) {
		    $where->like('username', "%$username%");
		};
		$select = $this->sql->select();
		$join = $select
				->from('users')
				->join('roles', 'users.roles_id = roles.roles_id', 
					array('rolname'),
					$select::JOIN_LEFT)
		;
		$join->where($spec);
		$sql = $join->order('users_id ASC')->limit($options["limit"]);

		$selectString = $this->sql->getSqlStringForSqlObject($sql);
		$adapter = $this->dbAdapter;
		$results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
		return $results;
	}

	public function countUsers()
	{
		$sql = "SELECT COUNT(users_id) AS total FROM users";

		$adapter = $this->dbAdapter;
		$rowset = $adapter->query($sql, $adapter::QUERY_MODE_EXECUTE);
		return $rowset->current()->total;
	}

	public function countActiveUsers()
	{
		$sql = "SELECT COUNT(users_id) AS total FROM users
				WHERE state = 1";

		$adapter = $this->dbAdapter;
		$rowset = $adapter->query($sql, $adapter::QUERY_MODE_EXECUTE);
		return $rowset->current()->total;
	}

	public function countInactiveUsers()
	{
		$sql = "SELECT COUNT(users_id) AS total FROM users
				WHERE state = 0";

		$adapter = $this->dbAdapter;
		$rowset = $adapter->query($sql, $adapter::QUERY_MODE_EXECUTE);
		return $rowset->current()->total;
	}

	public function lastUserRegistered()
	{
		$sql = "SELECT * FROM users
				ORDER BY record_date DESC
				LIMIT 1";

		$adapter = $this->dbAdapter;
		$rowset = $adapter->query($sql, $adapter::QUERY_MODE_EXECUTE);
		return $rowset->current();
	}



	public function getUserById($id)
	{
		$sql = "SELECT *
				FROM users AS a
				INNER JOIN roles AS b ON a.roles_id = b.roles_id
				WHERE a.users_id = '$id'
				";

		$adapter = $this->dbAdapter;
		$rowset = $adapter->query($sql, $adapter::QUERY_MODE_EXECUTE);
		return $rowset->current();
	}

	public function getPermission($id)
	{
		return $this->getUserById($id)->roles_id;
	}

}
