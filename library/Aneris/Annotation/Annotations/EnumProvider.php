<?php
namespace Aneris\Annotation\Annotations;

use Aneris\Annotation\AnnotationProviderInterface;
use Aneris\Annotation\ElementType;
use Aneris\Annotation\Exception;
/**
 */
class EnumProvider implements AnnotationProviderInterface
{
    public function getJoinPoints()
    {
        return array(
            'initalize' => array(
                AnnotationProviderInterface::EVENT_CREATED,
            ),
            'invoke' => array(
                AnnotationProviderInterface::EVENT_SET_FIELD,
            ),
        );
    }

    public function initalize($event)
    {
        $args = $event->getArgs();
        $annotationClassName = $args['annotationname'];
        $metadata = $args['metadata'];
        $location = $args['location'];

        if($location['target']!=ElementType::FIELD)
            throw new Exception\DomainException("@Enum must be placed as FILED in ".$location['uri'].': '.$location['filename']);
        if($metadata->value!==null && !is_array($metadata->value))
            $metadata->value = array($metadata->value);
        if($metadata->value==null || count($metadata->value)==0) {
            throw new Exception\DomainException("@Enum dose not have enumulated values in ".$location['uri'].': '.$location['filename']);
        }
        foreach ($metadata->value as $value) {
            $metadata->hashValue[$value] = true;
        }
    }

	public function invoke($event)
	{
        $args = $event->getArgs();
        $annotationClassName = $args['annotationname'];
        $fieldName       = $args['fieldname'];
        $metadata = $args['metadata'];
        $value    = $args['value'];
        $location = $args['location'];

        $enum = $metadata->value;
        if(!isset($metadata->hashValue[$value]))
            throw new Exception\DomainException('a value "'.$value.'" is not allowed for the field "'.$fieldName.'" of annotation @'.$annotationClassName.' in '.$location['uri'].': '.$location['filename']);
	}
}