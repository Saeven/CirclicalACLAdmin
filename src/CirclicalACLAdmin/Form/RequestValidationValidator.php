<?php

namespace CirclicalACLAdmin\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class RequestValidationValidator implements InputFilterAwareInterface

{
    protected $inputFilter;

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter)
        {
            $this->inputFilter = new InputFilter();
            $factory           = new InputFactory();

            $this->inputFilter->add($factory->createInput( array(
                'name' => 'email',
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array (
                        'name' => 'EmailAddress',
                        'options' => array(
                            'message' => array(
                                "Email address doesn't appear to be valid.",
                            )
                        ),
                    ),
                    array (
                        'name' => 'NotEmpty',
                        'options' => array(
                            'messages' => array(
                                'isEmpty' => 'Email address is required',
                            )
                        ),
                    ),
                ),
            )));
        }

        return $this->inputFilter;
    }
}