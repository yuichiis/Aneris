<?php
namespace Aneris\Validator;

use Aneris\Stdlib\ListCollection;
use Aneris\Stdlib\Entity\PropertyAccessPolicyInterface;
use Aneris\Annotation\ElementType;
use Aneris\Validator\Constraints\GroupSequence;
use Aneris\Validator\Constraints\CList;

class Validator implements ValidatorInterface
{
    const TRANSLATOR_TEXT_DOMAIN    = 'validator';
    const TRANSLATOR_TEXT_BASE_PATH = '/messages';
    const TRANSLATOR_FILE_PATTERN   = '%s/LC_MESSAGES/validator.mo';

    protected $tron;

    public static function getTranslatorTextDomain()
    {
        return self::TRANSLATOR_TEXT_DOMAIN;
    }

    public static function getTranslatorBasePath()
    {
        return __DIR__.self::TRANSLATOR_TEXT_BASE_PATH;
    }

    public static function getTranslatorFilePattern()
    {
        return self::TRANSLATOR_FILE_PATTERN;
    }

    protected $translator;
    public $translatorTextDomain;
    protected $constraintManager;
    protected $constraintValidatorFactory;

    public function __construct(
        $translator=null,
        ConstraintManagerInterface $constraintManager=null,
        ConstraintValidatorFactoryInterface $constraintValidatorFactory=null)
    {
        $this->setTranslator($translator);

        if($constraintManager)
            $this->constraintManager = $constraintManager;
        else
            $this->constraintManager = new ConstraintContextBuilder($constraintValidatorFactory);
    }

    public function getConstraintManager()
    {
        return $this->constraintManager;
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
        return $this;
    }

    public function getTranslator()
    {
        return $this->translator;
    }

    //public function setTranslatorTextDomain($translatorTextDomain=self::TRANSLATOR_TEXT_DOMAIN)
    public function setTranslatorTextDomain($translatorTextDomain)
    {
        $this->translatorTextDomain = $translatorTextDomain;
        return $this;
    }

    public function validate($object,$groups=null)
    {
        if(!is_object($object))
            throw new Exception\DomainException('Target is not object.');
        $className = get_class($object);
        $constraints = $this->constraintManager->getConstraints($className);
        $validationOrder = $this->determineValidationOrder($groups,$constraints);

        $violations = new ListCollection();
        foreach($validationOrder as $currentGroups) {
            $this->validateConstraintsForCurrentGroups(
                $object, $constraints, $currentGroups, $violations);
            if(count($violations)!=0)
                break;
        }
        return $violations;
    }

    public function validateProperty($object,$propertyName,$groups=null)
    {
        if(!is_object($object))
            throw new Exception\DomainException('Target is not object.');
        $className = get_class($object);
        $constraints = $this->constraintManager->getConstraints($className);
        $validationOrder = $this->determineValidationOrder($groups,$constraints);

        if(!isset($constraints[$propertyName]))
            throw new Exception\DomainException('the property is not defined in a contraints.:'.$propertyName);
        $constraintList = $constraints[$propertyName];

        $violations = new ListCollection();
        foreach($validationOrder as $currentGroups) {
            $value = $this->getValueFromProperty($object,$propertyName);
            $this->validateConstraintsForValue(
                $value,$object,$propertyName,$constraintList,$currentGroups,$violations);
            if(count($violations)!=0)
                break;
        }
        return $violations;
    }

    public function validateValue($className,$propertyName,$value,$groups=null)
    {
        if(is_object($className))
            $className = get_class($className);

        $constraints = $this->constraintManager->getConstraints($className);
        $validationOrder = $this->determineValidationOrder($groups,$constraints);

        if(!isset($constraints[$propertyName]))
            throw new Exception\DomainException('the property is not defined in a contraints.:'.$propertyName);
        $constraintList = $constraints[$propertyName];

        $violations = new ListCollection();
        foreach($validationOrder as $currentGroups) {
            $this->validateConstraintsForValue(
                $value,null,$propertyName,$constraintList,$currentGroups,$violations);
            if(count($violations)!=0)
                break;
        }
        return $violations;
    }

    public function getConstraintsForClass($className)
    {
        if(is_object($className))
            $className = get_class($className);
        return $this->constraintManager->getConstraints($className);
    }

    protected function determineValidationOrder($groups,$constraints)
    {
        if($groups===null) {
            $groups = array('Default');
        } else {
            if(is_scalar($groups))
                $groups = array($groups);
        }
        if(isset($constraints['__CLASS__'][__NAMESPACE__.'\Constraints\GroupSequence'])) {
            if($groups==array('Default'))
                $groups = $constraints['__CLASS__'][__NAMESPACE__.'\Constraints\GroupSequence'];
        }
        if($groups instanceof GroupSequence) {
            $validationOrder = array();
            if(!is_array($groups->value))
                throw new Exception\DomainException('@GroupSequence do not have groups.');
            foreach($groups->value as $tmp) {
                $validationOrder[] = array($tmp);
            }
        } else {
            $validationOrder = array($groups);
        }
        return $validationOrder;
    }

    protected function validateConstraintsForCurrentGroups(
        $object,$constraints,$groups,$violations)
    {
        foreach ($constraints as $propertyName => $constraintList) {
            if($propertyName=='__CLASS__')
                continue;
            $value = $this->getValueFromProperty($object,$propertyName);
            $this->validateConstraintsForValue(
                $value,$object,$propertyName,$constraintList,$groups,$violations);
        }
    }

    protected function getValueFromProperty($object,$propertyName)
    {
        if(method_exists($object, $propertyName)) {
            $value = $object->$propertyName();
        } else if(property_exists($object, $propertyName)) {
            if($object instanceof PropertyAccessPolicyInterface) {
                $value = $object->$propertyName;
            } else {
                $getter = 'get'.ucfirst($propertyName);
                $value = $object->$getter();
            }
        } else {
            throw new Exception\DomainException('the property is not exist.:'.get_class($object).'::$'.$propertyName);
        }
        return $value;
    }

    protected function validateConstraintsForValue(
        $value, $object,$propertyName,$constraintList,$groups,$violations)
    {
        $isValid = true;
        foreach($constraintList as $constraint) {
            $isValid = $this->validateConstraintContext(
                $value, $object,$propertyName,$constraint,$groups,$violations);
            if(!$isValid)
                break;
        }
        return $isValid;
    }

    protected function validateConstraintContext(
        $value, $object,$propertyName,$constraintContext,$groups,$violations)
    {
        $constraint = $constraintContext->constraint;
        $validator = $constraintContext->validator;

        $isValid = true;
        if(!$this->checkGroups($groups,$constraint))
            return true;

        if($constraint->path)
            $propertyPath = $constraint->path;
        else
            $propertyPath = $propertyName;
        $context = new ConstraintValidatorContext(
            $object,
            $value,
            $propertyPath,
            $constraint,
            $violations,
            $this->translator,
            $this->translatorTextDomain);
        $isValid = $validator->isValid($value,$context);
        if(!$isValid) {
            if($context->isDefaultConstraintViolation()) {
                $context->addConstraintViolation();
            }
        }
        return $isValid;
    }

    protected function checkGroups($groups,$constraint)
    {
        $constraintGroups = null;
        if(is_array($constraint->groups)) {
            if(count($constraint->groups)==0)
                $constraintGroups = array('Default');
            else
                $constraintGroups = $constraint->groups;
        } else {
            if($constraint->groups==null)
                $constraintGroups = array('Default');
            else
                $constraintGroups = array($constraint->groups);
        }
        foreach($groups as $group) {
            if(array_search($group, $constraintGroups)!==false)
                return true;
        }
        return false;
    }
}
