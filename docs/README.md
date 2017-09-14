Wellcome to the Aneris Framework
================================
Master: [![Build Status](https://travis-ci.org/yuichiis/Aneris.png?branch=master)](https://travis-ci.org/yuichiis/Aneris)
Develop: [![Build Status](https://travis-ci.org/yuichiis/Aneris.png?branch=develop)](https://travis-ci.org/yuichiis/Aneris)

The Aneris Framework is a PHP Application Framework that provides a modern programming and configuration model to all PHP programmers.
Aneris is modular and your program will be modular and that is independence to PHP frameworks.
Then, You can focus on application-level business logic.

You can use the Dependency Injection Container and use the Interceptor based Aspect Oriented Programming easily.
You can choose both "Annotation" based configuration and PHP's "Array" based configuration.
Not only using with the Zend Framework and the Symfony etcetera but also it can use with Aneris alone.

### Feature:

  * Dependency Injection & Aspect Oriented Programming
  * Annotation Based Configuration 
  * Pre-Compiled Configuration and Caching

Requirements
------------
Aneris is supported on PHP 5.3.3 and later.

When it will use with other frameworks, It will varies depending on the implementation.
Aneris provides the reference implementation of standard bridges to the Zend Framework and The Symfony through additional modules.


Installation
------------
Download from github.com and extract to your library directory.
It is planing to use the composer.
And it is planing to provide Web Application skeleton for Zend Framework and Symfony and standalone.


Documentation
-------------
You will see soon.
I can write English a little.
I need your help to write in English.


Components
----------
Aneris is separated some components. Those are It can also be used just PHP library independently.

### Core Component
Core Component is constituted Dependency Injection Container and Module Manager.

  * The Dependency Injection Container manages life cycle of application objects, and  initializes objects according to the dependencies between objects. Dependencies are compiling and caching to APC extention and Filesystem.

  * The Module Manager manages program codes and configurations as pluggable modules.

### AOP Component
AOP Component is constituted AOP Manager and Interceptor and Event Manager.

  * AOP Manager extends Dependency Injection Container and manages the concern over multiple modules as the "Aspect".

  * Interceptor get into the gap of the object invocation and monitor concerns to invoke it.

  * Event Manager manages events translated form aspects and be a bridge between interceptors and Aspects.

### Annotation Component
Annotation Component is constituted Annotation Manager.

  * Annotation Manager compile and cache "Annotation" in PHP class definition. Almost all Aneris's Component can use "Annotation" by this capability.

### Validator Component
Validator Component is constituted Validator and Contraints.

  * Validator validates PHP Object like the "JSR 303 Bean Validation". Validation Configuration will be written by "Annotation" in PHP oject class definition.

  * Constraints are definition of constraints like the "Bean Validation".

### Form Component
Form Component is constituted Form Context Builder and Form Renderer.

  * Form Context Builder makes the Form Object Structure for HTML form element from PHP oject class decorated by "Annotation".

  * Form Renderer draw HTML form element the Form Object Structure and the Form Theme.

### MVC Component
MVC Component is constituted MVC Application Manager.

  * When you want to use Aneris standalone without other frameworks (ZF2 and Symfony and something), You can use MVC Application Manager. This Manager can be configured to use "Annotation". And it can use the Dependency Injection without being aware of DI Container.

### Modules
Aneris has some module in the standard.

  * Doctrine Module
  * Twig Module
  * Smarty Module

### Bridge to other frameworks
Aneris has some bridge in the standard.

  * The "AnerisModule" for Zend Framework 2
  * The "AnerisBundle" for Symfony 2
