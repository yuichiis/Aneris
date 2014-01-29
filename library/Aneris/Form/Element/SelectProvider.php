<?php
namespace Aneris\Form\Element;

use Aneris\Annotation\AnnotationProviderInterface;
use Aneris\Form\Element;

class SelectProvider implements AnnotationProviderInterface
{
    public function getJoinPoints()
    {
        return array(
            'initalize' => array(
                AnnotationProviderInterface::EVENT_CREATED,
            ),
        );
    }

    public function initalize($event)
    {
        $args = $event->getArgs();
        $annotationClassName = $args['annotationname'];
        $metadata = $args['metadata'];
        $location = $args['location'];

        if($metadata->options==null)
            return;
        if(!is_array($metadata->options))
            $options = array($metadata->options);
        else
            $options = $metadata->options;

        if(isset($options[0]))
            $asIs = true;
        else
            $asIs = false;
        foreach($options as $value => $label) {
            $element = new Element();
            $element->label = $label;
            if($asIs) {
                $element->value = $label;
                $metadata[$label] = $element;
            } else {
                $element->value = $value;
                $metadata[$value] = $element;
            }
        }
    }

	public function invoke($event)
	{
	}
}
