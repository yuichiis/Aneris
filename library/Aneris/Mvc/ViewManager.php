<?php
namespace Aneris\Mvc;

class ViewManager implements ViewManagerInterface
{
    protected $config;

    public function render($response,$templateName,$templatePaths,$context)
    {
        $config = $context->getServiceLocator()->get('config');
        if(isset($config['mvc']['view']))
            $this->config = $config['mvc']['view'];
        $fullpath = $this->resolveTemplate($templateName,$templatePaths);
        $view = new View($context->getPluginManager());
        $output = $view->renderTemplate($fullpath,$response);
        if($view->layout)
            $layout = $view->layout;
        else if(isset($this->config['layout']))
            $layout = $this->config['layout'];
        else
            $layout = null;
        if($layout) {
            $fullpath = $this->resolveTemplate($layout,$templatePaths);
            $view->content = $output;
            $output = $view->renderTemplate($fullpath,$response);
        }
        return $output;
    }

    public function resolveTemplate($templateName,$templatePaths)
    {
        $found = false;
        $filename = $templateName.$this->getPostfix();
        foreach($templatePaths as $path) {
            $fullpath = $path.'/'.$filename;
            if(file_exists($fullpath)) {
                $found = true;
                break;
            }
        }
        if(!$found)
            throw new Exception\DomainException('template not found: "'.$templateName.'"');
        return $fullpath;
    }

    protected function getPostfix()
    {
        if(isset($this->config['postfix']))
            return $this->config['postfix'];
        else
            return '.php';
    }
}
