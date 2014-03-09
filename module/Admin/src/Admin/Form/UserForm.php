<?php

namespace Admin\Form;

use Zend\Form\Form;
use Admin\Model\UserTable;
use Zend\Db\Adapter\Adapter;
use Admin\Model\Admin;

class UserForm extends Form
{
    protected $sm;
    
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('user');        
 
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'id',
            'type' => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'id',
            ),
        ));
        $this->add(array(
            'name' => 'first_name',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class'=> 'form-control',
                'id' => 'first_name',
            ),
            'options' => array(
                'label' => 'First Name',
            ),
        ));
        $this->add(array(
            'name' => 'middle_init',          
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class'=> 'form-control',
                'id' => 'middle_init',
                'maxlength' => 1,
                'size' => 1,
                'style' => 'width:60px;'
            ),
            'options' => array(
                'label' => 'Middle Initial',
            ),
        ));
        $this->add(array(
            'name' => 'email',          
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class'=> 'form-control',
                'id' => 'email',
            ),
            'options' => array(
                'label' => 'Email',
            ),
        ));
        $this->add(array(
            'name' => 'last_name',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class'=> 'form-control',
                'id' => 'last_name',
            ),
            'options' => array(
                'label' => 'Last Name',
            ),
        ));
         $this->add(array(
            'name' => 'user_roles',
            'type' => 'Zend\Form\Element\MultiCheckbox',
            'attributes' => array(
                'id' => 'user_roles',
            ),
            'options' => array(
                'value_options' => $this->getroles(),
            ),
        ));
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Go',
                'id' => 'submitbutton',
                'class'=> 'btn btn-primary btn-lg',
            ),
        ));
    }    
    
    public function getroles()
   {      
      $admin =  new Admin();    
      return $admin->getRoleTerms();
    }
}