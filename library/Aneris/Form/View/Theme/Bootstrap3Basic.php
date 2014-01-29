<?php
namespace Aneris\Form\View\Theme;

class Bootstrap3Basic
{
	public static $config = array(
        'field'  => array(
            'success' => array(
                'field' => array('class'=>'form-group'),
            ),
            'error'   => array(
                'field' => array('class'=>'form-group has-error'),
            ),
        ),
        'label'  => array(
            'default'  => array('class'=>'control-label'),
        ),
        'errors' => array('class'=>'help-block'),
        'widget' => array(
            'default'  => array('class'=>'form-control'),
            'radio'    => array('itemDivClass'=>'radio'),
            'checkbox' => array('itemDivClass'=>'checkbox'),
            'submit'   => array('class'=>'btn btn-default'),
            'button'   => array('class'=>'btn btn-default'),
            'image'    => array('class'=>'img-rounded'),
            'reset'    => array('class'=>'btn btn-default'),
        ),
    );
}