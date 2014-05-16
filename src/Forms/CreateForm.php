<?php

namespace Forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;



class CreateForm extends AbstractType
{
    private $header = array();
    
    public function __construct($header = array())
    {
        $this->header = $header;
    }
    public function buildForm(FormBuilderInterface $builder, array $option)
    {
        foreach ($this->header as $key) {
            $builder->add($key, 'text', array(
                'label' => $key,
                'required' => false,
                'constraints' => array(new Assert\Type(array('type' => 'string'))),
                ));
        }
    }
    public function getName()
    {
        return 'CreateForm';
    }

}