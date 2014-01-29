<?php
namespace Aneris\Form;

use Aneris\Stdlib\Entity\EntityHydrator;
use Aneris\Stdlib\Entity\PropertyHydrator;
use Aneris\Stdlib\Entity\SetterHydrator;
use Aneris\Stdlib\Entity\EntityInterface;
use Aneris\Stdlib\Entity\PropertyAccessPolicyInterface;
use Aneris\Stdlib\Entity\SetterAccessPolicyInterface;

class FormContext
{
    protected $form;
    protected $bindingEntity;
    protected $validator;
    protected $hydrator;
    protected $data;
    protected $violation;
    protected $validated;

    public function __construct(ElementCollection $form, $bindingEntity=null, $validator= null, $hydrator=null)
    {
    	$this->form           = $form;
    	$this->bindingEntity  = $bindingEntity;
        $this->validator      = $validator;
        $this->hydrator       = $hydrator;
    }

    public function getForm()
    {
        return $this->form;
    }

    public function getEntity()
    {
        return $this->bindingEntity;
    }

    public function bind($bindingEntity)
    {
        $this->bindingEntity = $bindingEntity;
        return $this;
    }

    public function setHydrator($hydrator)
    {
        $this->hydrator = $hydrator;
        return $this;
    }

    public function getHydrator()
    {
        if($this->hydrator)
            return $this->hydrator;
        if(is_object($this->bindingEntity)) {
            if($this->bindingEntity instanceof EntityInterface)
                $this->hydrator = new EntityHydrator();
            else if($this->bindingEntity instanceof PropertyAccessPolicyInterface)
                $this->hydrator = new PropertyHydrator();
            else
                $this->hydrator = new SetterHydrator();
            return $this->hydrator;
        }
        throw new Exception\DomainException('Binding Entity or Hydrator type is not spacified.');
    }

    public function setData($data)
    {
        if(is_object($data)) {
            $data = $this->getHydrator()->extract($data,$this->form->keys());
        }
        $this->data = array();
        foreach($this->form as $key => $element) {
            if(isset($data[$key])) {
                $element->value = $data[$key];
                $this->data[$key] = $data[$key];
            } else {
                $element->value = null;
                $this->data[$key] = null;
            }
        }
        return $this;
    }

    public function setAttributes($attributes)
    {
        $this->form->attributes = $attributes;
        return $this;
    }

    public function setAttribute($name,$value)
    {
        $this->form->attributes[$name] = $value;
        return $this;
    }

    public function setErrors($violation)
    {
        foreach($this->form as $key => $element) {
            if(isset($violation[$key]))
                $element->errors = array($violation[$key][0]->getMessage());
        }
        return $this;
    }

    public function isValid()
    {
        if($this->validated!==null)
            return $this->validated;

        if($this->data==null) {
            throw new Exception\DomainException('there is no data to validate.');
        }
        if($this->bindingEntity==null) {
            throw new Exception\DomainException('there is no data type to validate.');
        }
        $this->getHydrator()->hydrate($this->data,$this->bindingEntity);
        if($this->validator==null) {
            throw new Exception\DomainException('there is no validator.');
        }
        $this->violation = $this->validator->validate($this->bindingEntity);
        $this->setErrors($this->violation);
        $this->validate = (count($this->violation)==0);

        return $this->validate;
    }

    public function hasErrors()
    {
        return !$this->isValid();
    }

    public function getViolation()
    {
        return $this->violation;
    }
}