<?php

namespace CirclicalACLAdmin\Form;

use Zend\Captcha;
use Zend\Form\Element;
use Zend\Form\Form;

class RequestValidationForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('');

        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'email',
            'type' => 'Zend\Form\Element\Email',
            'attributes' => array(
                'placeholder' => 'Email Address...',
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Email',
            ),
        ));

        $this->add(array(
            'name' => 'id',
            'type' => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'value' => '100',
            )
        ));

        $this->add(array(
            'name' => 'hash',
            'type' => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'value' => 'ABCABC',
            )
        ));

        $this->add(array(
            'name' => 'csrf',
            'type' => 'Zend\Form\Element\Csrf',
            'options' => array(
                'csrf_options' => array(
                    'timeout' => 600
                ),
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'type' => 'Zend\Form\Element\Submit',
            'attributes' => array(
                'value' => 'Validate Now!'
            ),
            'options' => array(
                'label' => 'Validate Now!'
            )
        ));
    }
}