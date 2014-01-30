<?php
namespace AcmeTest\DiContainer\Component;

use Aneris\Container\Annotations\Named;
use Aneris\Container\Annotations\Inject;
use Aneris\Stdlib\Entity\EntityTrait;

/**
* @Named("model2")
*/
class Model2
{
	use EntityTrait;
	/**
	* @Inject({@Named("model1")})
	*/
	protected $model1;
}
