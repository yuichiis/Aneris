<?php
namespace Aneris\Aop;

interface AspectInterface
{
	const ADVICE_BEFORE = 'before';
	const ADVICE_AFTER_RETURNING  = 'after-returning';
	const ADVICE_AFTER_THROWING  = 'after-throwing';
	const ADVICE_AFTER  = 'after';
	const ADVICE_AROUND = 'around';

    public static function getAdvices();
}
