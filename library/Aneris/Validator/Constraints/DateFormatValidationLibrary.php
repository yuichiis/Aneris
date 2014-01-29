<?php
namespace Aneris\Validator\Constraints;

use Aneris\Validator\ConstraintValidatorInterface;
use Aneris\Validator\ConstraintInterface;
use Aneris\Validator\ConstraintValidatorContextInterface;
use DateTime;

class DateFormatValidationLibrary
{
    public static function isDate($value)
    {
        $parts = explode('-', $value);
        if(count($parts)!=3)
            return false;
        foreach($parts as $part) {
            if(!ctype_digit($part))
                return false;
        }
        $year = $parts[0];
        $month = $parts[1];
        $day = $parts[2];
        return checkdate($month, $day, $year);
    }

    public static function isTime($value,&$adjust)
    {
        $parts = explode(':', $value);
        if(count($parts)!=3 && count($parts)!=2)
            return false;
        if(!ctype_digit($parts[0]) || $parts[0]>23)
            return false;
        if(!ctype_digit($parts[1]) || $parts[1]>59)
            return false;
        if(!isset($parts[2])) {
            // For Google Chrome
            $parts[2] = '00';
        } else {
            $sec = explode('.', $parts[2]);
            if(count($sec)!=1 && count($sec)!=2)
                return false;
            if(!ctype_digit($sec[0]) || $sec[0]>59)
                return false;
            // For Safari
            if(count($sec)!=1) {
                if(!ctype_digit($sec[1]))
                    return false;
                $parts[2] = $sec[0];
            }
        }
        $adjust = implode(':', $parts);
        return true;
    }

    public static function isDateTimeLocal($value)
    {
        $parts = explode('T', $value);
        if(count($parts)!=2)
            return false;
        if(!self::isDate($parts[0]))
            return false;
        return self::isTime($parts[1],$adjust);
    }

    public static function convertToDateTime($value)
    {
        if(ctype_digit($value)) {
            $value = new DateTime('@'.$value);
        } else if(is_string($value)) {
            $part = date_parse_from_format(DATE_W3C,$value);
            if($part['error_count'])
                return false;
            $value = new DateTime($value);
        } else if(!($value instanceof DateTime)) {
            return false;
        }
        return $value;
    }
}
