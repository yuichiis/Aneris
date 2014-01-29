<?php
namespace Aneris\Mvc;

interface ViewManagerInterface
{
    public function render($response,$template,$templatePaths,$context);
}
