<?php

namespace Application\Form;

use Zend\Form\Form;

class MessageForm extends Form
{
    public function __construct($controller = null)
    {
        parent::__construct('frmMessage');

        $this->add(array(
            'name' => 'word',
            'type' => 'text',
            'options' => array(
                'label' => 'Message',
            ),
            'attributes' => array(
                'placeholder' => 'message',
                'autofocus' => 'autofocus',
                'id' => 'word'
            ),
        ));
    }
}
