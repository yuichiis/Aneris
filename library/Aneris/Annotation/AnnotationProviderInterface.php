<?php
namespace Aneris\Annotation;

interface AnnotationProviderInterface
{
    const EVENT_CREATED     = 'CREATED';
    const EVENT_USED_PARENT = 'USED_PARENT';
    const EVENT_SET_FIELD   = 'SET_FIELD';

    public function getJoinPoints();
    public function initalize($event);
	public function invoke($event);
}
