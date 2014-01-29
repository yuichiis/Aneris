<?php
namespace Aneris\Annotation\Annotations;

use Aneris\Annotation\AnnotationProviderInterface;
use Aneris\Annotation\ElementType;
use Aneris\Annotation\Exception;
/**
 */
class TargetProvider implements AnnotationProviderInterface
{
    const TARGET_TYPE            = 1;
    const TARGET_FIELD           = 2;
    const TARGET_METHOD          = 4;
    const TARGET_ANNOTATION_TYPE = 8;
    const TARGET_PACKAGE         = 16;
    const TARGET_LOCAL_VARIABLE  = 32;
    const TARGET_CONSTRUCTOR     = 64;
    const TARGET_PARAMETER       = 128;
    static $targetBit = array(
        ElementType::TYPE            => self::TARGET_TYPE,
        ElementType::FIELD           => self::TARGET_FIELD,
        ElementType::METHOD          => self::TARGET_METHOD,
        ElementType::ANNOTATION_TYPE => self::TARGET_ANNOTATION_TYPE,
        ElementType::PACKAGE         => self::TARGET_PACKAGE,
        ElementType::LOCAL_VARIABLE  => self::TARGET_LOCAL_VARIABLE,
        ElementType::CONSTRUCTOR     => self::TARGET_CONSTRUCTOR,
        ElementType::PARAMETER       => self::TARGET_PARAMETER,
        );

    public function getJoinPoints()
    {
        return array(
            'initalize' => array(
        		AnnotationProviderInterface::EVENT_CREATED,
        	),
            'invoke' => array(
        		AnnotationProviderInterface::EVENT_USED_PARENT,
        	),
        );
    }

    public function initalize($event)
    {
        $args = $event->getArgs();
        $annotationClassName = $args['annotationname'];
        $metadata = $args['metadata'];
        $location = $args['location'];

        if($location['target']!=ElementType::ANNOTATION_TYPE)
            throw new Exception\DomainException("@Target must be placed as ANNOTAION_TYPE in ".$location['uri'].': '.$location['filename']);
        if($metadata->value!=null && !is_array($metadata->value))
            $metadata->value = array($metadata->value);
        if($metadata->value==null || count($metadata->value)==0) {
            throw new Exception\DomainException("@Target dose not have element types in ".$location['uri'].': '.$location['filename']);
        }
        $metadata->binValue = 0;
        foreach($metadata->value as $elementType) {
            // for Doctrine Patch
            if($elementType=='PROPERTY')
                $elementType = ElementType::FIELD;
            else if($elementType=='CLASS')
                $elementType = ElementType::TYPE;
            else if($elementType=='ANNOTATION')
                $elementType = ElementType::ANNOTATION_TYPE;
            if(!isset(self::$targetBit[$elementType])) {
                if(isset($location['linenumber']))
                    $linenumber = '('.$location['linenumber'].')';
                else
                    throw new \Exception('linenumber not found');
                throw new Exception\DomainException('the paremeter "'.$elementType.'" is a invalid argument for the @Target in '.$location['uri'].': '.$location['filename'].'('.$location['linenumber'].')');
            }
            $metadata->binValue |= self::$targetBit[$elementType];
        }
    }

	public function invoke($event)
	{
        $args = $event->getArgs();
        $annotationClassName = $args['annotationname'];
        $metadata = $args['metadata'];
        $location = $args['location'];

        $elementType = $location['target'];
        if(!isset(self::$targetBit[$elementType]) ||
           ($metadata->binValue & self::$targetBit[$elementType])==0) {
            if(isset($location['linenumber']))
                $linenumber = '('.$location['linenumber'].')';
            else
                throw new \Exception('linenumber not found');
            throw new Exception\DomainException('the annotation "@'.$annotationClassName.'" do not allow to '.$location['target'].' in '.$location['uri'].': '.$location['filename'].'('.$location['linenumber'].')');
        }
	}
}