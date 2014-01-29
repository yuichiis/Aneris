<?php
namespace Aneris\Form\View\Theme;

class Bootstrap3Horizontal
{
	public static $config = array(
        'field'  => array(
            'success' => array(
                'field' => array('class'=>'form-group'),
                'widget' => array('class'=>'col-sm-10 columns'),
            ),
            'error'   => array(
                'field' => array('class'=>'form-group has-error'),
                'widget' => array('class'=>'col-sm-10 columns'),
            ),
        ),
        'label'  => array(
            'default'  => array('class'=>'col-sm-2 control-label'),
        ),
        'errors' => array('class'=>'help-block'),
        'widget' => array(
            'default'  => array('class'=>'form-control'),
            'form'     => array('class'=>'form-horizontal'),
            'radio'    => array('itemDivClass'=>'radio'),
            'checkbox' => array('itemDivClass'=>'checkbox'),
            'submit'   => array('class'=>'btn btn-default'),
            'button'   => array('class'=>'btn btn-default'),
            'image'    => array('class'=>'img-rounded'),
            'reset'    => array('class'=>'btn btn-default'),
        ),
    );
}