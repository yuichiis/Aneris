<?php
namespace Aneris\Container;

interface InjectTypeInterface
{
    const ARGUMENT_DEFAULT   = 'default';
    const ARGUMENT_VALUE     = 'value';
    const ARGUMENT_REFERENCE = 'ref';

    const SCOPE_SINGLETON = 'singleton';
    const SCOPE_PROTOTYPE = 'prototype';

    // extend for web service container
    const SCOPE_REQUEST   = 'request'; // http request
    const SCOPE_SESSION   = 'session'; // php session
    const SCOPE_GLOBAL_SESSION = 'global_session'; // global portlet session
}