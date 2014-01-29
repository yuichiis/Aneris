<?php
namespace Aneris\Module\Doctrine;

use Aneris\Annotation\AnnotationManager;
use Doctrine\Common\Annotations\Reader;

class AnnotationReaderProxy extends AnnotationManager implements Reader
{}