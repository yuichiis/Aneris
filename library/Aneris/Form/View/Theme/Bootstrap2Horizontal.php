<?php
namespace Aneris\Form\View\Theme;

class Bootstrap2Horizontal
{
	public static $config = array(
        'field'  => array(
            'success' => array(
                'field' => array('class'=>'form-group'),
                'widget' => array('class'=>'controls'),
            ),
            'error'   => array(
                'field' => array('class'=>'form-group error'),
                'widget' => array('class'=>'controls'),
            ),
        ),
        'label'  => array(
            'default'  => array('class'=>'control-label'),
        ),
        'errors' => array('class'=>'help-block'),
        'widget' => array(
            'form'     => array('class'=>'form-horizontal'),
            'radio'    => array('itemLabelClass'=>'radio'),
            'checkbox' => array('itemLabelClass'=>'checkbox'),
            'submit'   => array('class'=>'btn'),
            'button'   => array('class'=>'btn'),
            'image'    => array('class'=>'img-rounded'),
            'reset'    => array('class'=>'btn'),
        ),
    );
}