<?php

namespace App\Model\Entity;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Message implements InputFilterAwareInterface
{
	public $word;

	protected $inputFilter;

	public function exchangeArray($data)
	{
		$this->word = (isset($data["word"])) ? $data["word"] : null;
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
				'name' => 'word',
				'required' => true,
				'filters' => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
				)
			));

			$this->inputFilter = $inputFilter;
		}
		return $this->inputFilter;
	}
}
