<?php
namespace Aneris\Aop;

use ReflectionClass;
use ReflectionException;
use Aneris\Stdlib\Cache\CacheFactory;

class InterceptorBuilder
{
    const MODE_INTERFACE   = 'interface';
    const MODE_INHERITANCE = 'inheritance';
    public function getInterceptorDeclare($className,$mode=null)
    {
        if($mode==null || $mode===true || $mode===self::MODE_INHERITANCE)
            return $this->getInheritanceBasedInterceptorDeclare($className,$mode);
        else if($mode===self::MODE_INTERFACE)
            return $this->getInterfaceBasedInterceptorDeclare($className,$mode);
        else
            throw new Exception\DomainException('unknown proxy mode "'.$mode.'" to create a proxy for: '.$className);
    }

    public function getInterfaceBasedInterceptorDeclare($className,$mode)
    {
        $tmpClassName = str_replace('\\', '/', $this->getInterceptorClassName($className,$mode));
        $namespace  = str_replace('/', '\\',dirname($tmpClassName));
        $classBaseName  = basename($tmpClassName);
        $classRef = new ReflectionClass($className);
        $interfaces = $classRef->getInterfaces();
        $copy = $interfaces;
        foreach ($copy as $interface) {
            foreach($interface->getInterfaces() as $parent) {
                unset($interfaces[$parent->name]);
            }
        }

        $interfacesImplements = '';
        if(count($interfaces)) {
            foreach ($interfaces as $interface) {
                if(empty($interfacesImplements))
                    $interfacesImplements = 'implements \\'.$interface->name;
                else
                    $interfacesImplements .= ',\\'.$interface->name;
            }
        }

        $methodDescribe = '';
        foreach ($classRef->getMethods() as $methodRef) {
            if($methodRef->isStatic())
                $methodDescribe .= $this->getInterfaceStaticMethod($methodRef);
            else if(!$methodRef->isConstructor() && !$methodRef->isAbstract() && !$methodRef->isPrivate() && !$methodRef->isProtected() )
                $methodDescribe .= $this->getInterfaceMethod($methodRef);
        }
        
        $classDeclare = <<<EOD
<?php
namespace ${namespace};
use Aneris\\Aop\\Interceptor;
class ${classBaseName} extends Interceptor ${interfacesImplements}
{
${methodDescribe}
}
EOD;
        return $classDeclare;
    }

    public function getInterfaceMethod($methodRef)
    {
        $calledName = $methodRef->name;
        $methodType = $this->getMethodType($methodRef);
        $paramTypes = $this->getParamTypes($methodRef);
        $paramsDescribe = $this->getParamsDescribe($methodRef,true);
        $describe = <<<EOD

    ${methodType} function ${calledName}(${paramTypes})
    {
        return \$this->__call('${calledName}', array(${paramsDescribe}));
    }
EOD;
        return $describe;
    }

    public function getInterfaceStaticMethod($methodRef)
    {
        $calledName = $methodRef->name;
        $methodType = $this->getMethodType($methodRef);
        $paramTypes = $this->getParamTypes($methodRef);
        $paramsDescribe = $this->getParamsDescribe($methodRef,false);
        $describe = <<<EOD

    ${methodType} function ${calledName}(${paramTypes})
    {
        return parent::${calledName}(${paramsDescribe});
    }
EOD;
        return $describe;
    }

    protected function getMethodType($methodRef)
    {
        $methodType = '';
        if($methodRef->isFinal())
            $methodType .= ' final';
        //if($methodRef->isAbstract())
        //    $methodType .= ' abstract';

        if($methodRef->isPublic()||$methodRef->isConstructor())
            $methodType .= ' public';
        else if($methodRef->isProtected())
            $methodType .= ' protected';
        else if($methodRef->isPrivate())
            $methodType .= ' private';

        if($methodRef->isStatic())
            $methodType .= ' static';

        return trim($methodType);
    }

    protected function getParamTypes($methodRef)
    {
        $paramTypes = '';
        foreach ($methodRef->getParameters() as $paramRef) {
            if(!empty($paramTypes))
                $paramTypes .= ',';

            if($paramRef->isArray()) {
                $paramTypes .= 'array ';
            } else if($paramRef->isCallable()) {
                $paramTypes .= 'callable ';
            } else {
                try {
                    $paramClassRef = $paramRef->getClass();
                } catch(\Exception $e) {
                    throw new Exception\DomainException($e->getMessage().': '.$methodRef->getFileName().'('.$methodRef->getStartLine().')',0);
                }
                if($paramClassRef)
                    $paramTypes .= '\\'.$paramClassRef->name.' ';
            }
            if($paramRef->isPassedByReference()){
                $paramTypes .= '&';
            }

            $paramTypes .= '$'.$paramRef->name;
            if($paramRef->isOptional()) {
                $paramTypes .= '='.var_export($paramRef->getDefaultValue(),true);
            }
        }
        return $paramTypes;
    }

    protected function getParamsDescribe($methodRef,$reference=false)
    {
        $paramsDescribe = '';
        foreach ($methodRef->getParameters() as $paramRef) {
            if(!empty($paramsDescribe))
                $paramsDescribe .= ',';
            if($reference && $paramRef->isPassedByReference()){
                $paramsDescribe .= '&';
            }
            $paramsDescribe .= '$'.$paramRef->name;
        }
        return $paramsDescribe;
    }

    public function getInheritanceBasedInterceptorDeclare($className,$mode)
    {
        $tmpClassName = str_replace('\\', '/', $this->getInterceptorClassName($className,$mode));
        $namespace  = str_replace('/', '\\',dirname($tmpClassName));
        $classBaseName  = basename($tmpClassName);
        $classRef = new ReflectionClass($className);
        if($classRef->getConstructor())
            $constructor = '\'__aop_construct\'';
        else
            $constructor = 'null';
        $methodDescribe = '';
        foreach ($classRef->getMethods() as $methodRef) {
            //if(($methodRef->isPublic()||$methodRef->isConstructor()) && !$methodRef->isFinal())
            if($methodRef->isConstructor() || (!$methodRef->isStatic() && !$methodRef->isFinal() && !$methodRef->isAbstract() && !$methodRef->isPrivate() && !$methodRef->isProtected()))
                $methodDescribe .= $this->getMethodDescribe($methodRef);
        }
        
        $classDeclare = <<<EOD
<?php
namespace ${namespace};
use Aneris\\Aop\\Interceptor;
class ${classBaseName} extends \\${className}
{
    protected \$__aop_interceptor;
    public function __construct(\$container,\$component,\$events=null,\$lazy=null)
    {
        \$this->__aop_interceptor = new Interceptor(
            \$container,\$component,\$events,true,\$this,${constructor});
        if(!\$lazy)
            \$this->__aop_interceptor->__aop_instantiate();
    }
${methodDescribe}
}
EOD;
        return $classDeclare;
    }

    public function getMethodDescribe($methodRef)
    {
        if($methodRef->isConstructor()) {
            $calledName = '__aop_construct';
            $methodName   = $methodRef->name;
        } else {
            $calledName = $methodRef->name;
            $methodName = $methodRef->name;
        }

        $methodType = $this->getMethodType($methodRef);
        $paramTypes = $this->getParamTypes($methodRef);
        $paramsDescribe = $this->getParamsDescribe($methodRef,true);
        $parentParamsDescribe = $this->getParamsDescribe($methodRef,false);

        $describe = <<<EOD

    public function __aop_method_${methodName}(${paramTypes})
    {
        return parent::${methodName}(${parentParamsDescribe});
    }
    ${methodType} function ${calledName}(${paramTypes})
    {
        \$this->__aop_interceptor->__aop_before('${methodName}',array(${paramsDescribe}));
        try {
            \$result = \$this->__aop_interceptor->__aop_around('${methodName}',array(${paramsDescribe}),'__aop_method_${methodName}');
        } catch(\\Exception \$e) {
            \$this->__aop_interceptor->__aop_afterThrowing('${methodName}',array(${paramsDescribe}),\$e);
            throw \$e;
        }
        \$this->__aop_interceptor->__aop_afterReturning('${methodName}',array(${paramsDescribe}),\$result);
        return \$result;
    }
EOD;
        return $describe;
    }

    public function getInterceptorFileName($className,$mode)
    {
        return CacheFactory::$fileCachePath . '/' . str_replace('\\', '/', __CLASS__.'\\interceptors\\'.$this->getInterceptorClassName($className,$mode)) . '.php';
    }

    public function getInterceptorClassName($className,$mode)
    {
        $postfix = '';
        if($mode==null || $mode===true || $mode===self::MODE_INHERITANCE)
            $postfix = 'IH';
        else if($mode===self::MODE_INTERFACE)
            $postfix = 'IF';
        return $className.$postfix.'Interceptor';
    }

    public function buildInterceptor($className,$mode)
    {
        $code = $this->getInterceptorDeclare($className,$mode);
        $filename = $this->getInterceptorFileName($className,$mode);
        if(!is_dir(dirname($filename))) {
            $dirname = dirname($filename);
            mkdir(dirname($filename),0777,true);
        }
        file_put_contents($filename, $code);
        return $this;
    }

 }