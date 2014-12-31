<?php

namespace Auth\Model\Entity;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class User implements InputFilterAwareInterface
{
	public $username;
	public $password;
	public $roles_id;
	public $state;

	protected $inputFilter;

	public function exchangeArray($data)
	{
		$this->username = (isset($data["username"])) ? $data["username"] : null;
		$this->password = (isset($data["password"])) ? $data["password"] : null;
		$this->roles_id = (isset($data["roles_id"])) ? $data["roles_id"] : null;
		$this->state = (isset($data["state"])) ? $data["state"] : null;
	}

	public function getArrayCopy()
	{
		return get_object_vars($this);
	}

	public function setInputFilter(InputFilterInterface $inputFilter)
	{
		throw new \Exception("Not used");
	}

	public function getInputFilter()
	{
		if (!$this->inputFilter)
		{
			$inputFilter = new InputFilter();

			$inputFilter->add(array(
				'name' => 'username',
				'required' => true,
				'filters' => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
				),
				'validators' => array(
					'Alnum' => new \Zend\I18n\Validator\Alnum(),
					'StringLength' => new \Zend\Validator\StringLength(array('min' => 3, 'max' => 25)),
				),
			));


			$inputFilter->add(array(
				'name' => 'password',
				'required' => true,
				'validators' => array(
					'StringLength' => new \Zend\Validator\StringLength(array('min' => 4, 'max' => 60)),
				),
			));

			$inputFilter->add(array(
				'name' => 'roles_id',
				'required' => false,
				'validators' => array(
					'Digits' => new \Zend\Validator\Digits(),,
				),
			));

			$inputFilter->add(array(
				'name' => 'state',
				'required' => false,
				'validators' => array(
					'values' => new \Zend\Validator\InArray(array('haystack' => array(0,1))),
				),
			));

			$this->inputFilter = $inputFilter;
		}
		return $this->inputFilter;
	}
}
