<?php
namespace Aneris\Form\View\Theme;

class Bootstrap2Basic
{
	public static $config = array(
        'field'  => array(
            'success' => array(
                'field' => array('class'=>'form-group'),
            ),
            'error'   => array(
                'field' => array('class'=>'form-group error'),
            ),
        ),
        'label'  => array(
            'default'  => array('class'=>'control-label'),
        ),
        'errors' => array('class'=>'help-block'),
        'widget' => array(
            'radio'    => array('itemLabelClass'=>'radio'),
            'checkbox' => array('itemLabelClass'=>'checkbox'),
            'submit'   => array('class'=>'btn'),
            'button'   => array('class'=>'btn'),
            'image'    => array('class'=>'img-rounded'),
            'reset'    => array('class'=>'btn'),
        ),
    );
}