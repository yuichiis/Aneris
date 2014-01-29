<?php
namespace Aneris\Module\Doctrine;

class Module
{
    public function getConfig()
    {
        return array(
            'container' => array(
                'aliases' => array(
                    'Doctrine\ORM\EntityManagerService' => 'Doctrine\ORM\EntityManager',
                ),
                'components' => array(
                    'Doctrine\ORM\EntityManager' => array(
                        'class' => 'Doctrine\ORM\EntityManager',
                        'factory' => 'Aneris\Module\Doctrine\EntityManagerFactory::factory',
                    ),
                ),
            ),
        );
    }
}
