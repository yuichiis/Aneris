<?php
namespace Aneris\Validator;

use Aneris\Stdlib\ListCollection;

class ConstraintValidatorContext implements ConstraintValidatorContextInterface
{
    protected $useDefaultConstraintViolation = true;
    protected $rootBean;
    protected $value;
    protected $propertyPath;
    protected $constraintMessageTemplate;
    protected $constraint;
    protected $violations;
    protected $translator;
    protected $parameters;
    protected $translatorTextDomain;

    public function __construct(
        $rootBean,
        $value, 
        $propertyPath, 
        ConstraintInterface $constraint,
        ListCollection $violations,
        $translator,
        $translatorTextDomain)
    {
        $this->rootBean = $rootBean;
        $this->value = $value;
        $this->propertyPath = $propertyPath;
        $this->constraint = $constraint;
        $this->violations = $violations;
        $this->translator = $translator;
        $this->translatorTextDomain = $translatorTextDomain;
    }

    public function getConstraint()
    {
        return $this->constraint;
    }

    public function setConstraint(ConstraintInterface $constraint)
    {
        $this->constraint = $constraint;
        return $this;
    }

    public function disableDefaultConstraintViolation()
    {
        $this->useDefaultConstraintViolation = false;
        return $this;
    }

    public function isDefaultConstraintViolation()
    {
        return $this->useDefaultConstraintViolation;
    }

    /**
     * @return String the current uninterpolated default message.
     */
    public function getDefaultConstraintMessageTemplate()
    {
        return $this->constraint->message;
    }

    /**
     * @messageTemplate String 
     * @return ConstraintViolationBuilder
     */
    public function setMessageTemplate($messageTemplate)
    {
        $this->constraintMessageTemplate = $messageTemplate;
    	return $this;
    }

    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
        return $this;
    }

    public function getMessageTemplate()
    {
        if($this->constraintMessageTemplate)
            return $this->constraintMessageTemplate;
        return $this->getDefaultConstraintMessageTemplate();
    }

    public function setPropertyPath($path)
    {
        $this->propertyPath = $name;
        return $this;
    }

    protected function translate($message)
    {
        if($this->translator) {
            if($this->translatorTextDomain) {
                return $this->translator->translate($message,$this->translatorTextDomain);
            } else {
                return $this->translator->translate($message);
            }
        }
        return $message;
    }

    /**
     * @return ConstraintValidatorContext this 
     */
    public function addConstraintViolation()
    {
        if($this->parameters)
            $parameters = $this->parameters;
        else
            $parameters = get_object_vars($this->constraint);

        $message = $this->translate($this->getMessageTemplate());
        //if(get_class($object)=='Aneris2ValidatorValidatorTest\Product3')
        //    var_dump($parameters);
        foreach($parameters as $name => $value) {
            if(is_string($value) || is_numeric($value) || is_scalar($value) || $value === null)
                $message = str_replace('{'.$name.'}', $value, $message);
        }
        $violation = new ConstraintViolation(
            $this->constraint->message,
            $message,
            $this->rootBean,
            $this->value,
            $this->propertyPath
        );
        $this->violations->add($this->propertyPath,$violation);
        return $this;
    }
}
