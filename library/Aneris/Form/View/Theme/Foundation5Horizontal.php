<?php
namespace Aneris\Form\View\Theme;

class Foundation5Horizontal
{
	public static $config = array(
        'field'  => array(
            'success' => array(
                'field' => array('class'=>'row'),
                'label'  => array('class'=>'large-2 medium-2 small-4 columns'),
                'widget' => array('class'=>'large-10 medium-10 small-8 columns'),
            ),
            'error'   => array(
                'field' => array('class'=>'row'),
                'label'  => array('class'=>'large-2 medium-2 small-4 columns error'),
                'widget' => array('class'=>'large-10 medium-10 small-8 columns error'),
            ),
        ),
        'label'  => array(
            'default'  => array('class'=>'right inline'),
            'radio'    => array('class'=>'right'),
            'checkbox' => array('class'=>'right'),
        ),
        'widget' => array(
            'submit'   => array('class'=>'button radius'),
            'reset'    => array('class'=>'button radius'),
        ),
    );
}