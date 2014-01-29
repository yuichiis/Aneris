<?php
namespace Aneris\Form\View\Theme;

class Foundation5Basic
{
	public static $config = array(
        'field'  => array(
            'success' => array(
                'field' => array('class'=>true),
            ),
            'error'   => array(
                'field' => array('class'=>'error'),
            ),
        ),
        'widget' => array(
            'submit'  => array('class'=>'button radius'),
            'reset'   => array('class'=>'button radius'),
        ),
    );
}