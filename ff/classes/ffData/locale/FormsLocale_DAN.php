<?php
/**
 * Data Handling: danese
 *
 * @package FormsFramework
 * @subpackage base
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */

$FormsLocale_DAN_format = array(
	  "Number" 		=> ""
	, "DateTime" 	=> ""
	, "Time" 		=> ""
	, "Date" 		=> ""
	, "Currency" 	=> ""
);

function FormsLocale_DAN_SetDateTime($oFormsData, $value)
	{
		preg_match_all("/((\d+):(\d+)(:(\d+))*\s+(\d+)[-\/](\d+)[-\/](\d+))|((\d+)[-\/](\d+)[-\/](\d+)\s+(\d+):(\d+)(:(\d+))*)/", $value, $matches);
		$oFormsData[0]->value_date_day = $matches[6][0] ? $matches[6][0] : $matches[10][0];
		$oFormsData[0]->value_date_month = $matches[7][0] ? $matches[7][0] : $matches[11][0];
		$oFormsData[0]->value_date_year = $matches[8][0] ? $matches[8][0] : $matches[12][0];
		$oFormsData[0]->value_date_hours = $matches[2][0] ? $matches[2][0] : $matches[13][0];
		$oFormsData[0]->value_date_minutes = $matches[3][0] ? $matches[3][0] : $matches[14][0];
		$oFormsData[0]->value_date_seconds = $matches[5][0] ? $matches[5][0] : $matches[16][0];

		FormsLocale_DAN_NormalizeDate($oFormsData);
	}

function FormsLocale_DAN_SetDate($oFormsData, $value)
	{
		preg_match_all("/(\d+)[-\/\s]*(\d+)[-\/\s]*(\d+)/", $value, $matches);
		$oFormsData[0]->value_date_day = $matches[1][0];
		$oFormsData[0]->value_date_month = $matches[2][0];
		$oFormsData[0]->value_date_year = $matches[3][0];

		FormsLocale_DAN_NormalizeDate($oFormsData);
	}

function FormsLocale_DAN_SetTime($oFormsData, $value)
	{
		preg_match_all("/(\d+)[:\s]*(\d+)/", $value, $matches);
		$oFormsData[0]->value_date_hours = $matches[1][0];
		$oFormsData[0]->value_date_minutes = $matches[2][0];
	}

function FormsLocale_DAN_NormalizeDate($oFormsData)
	{
		if (strlen($oFormsData[0]->value_date_year) == 2)
			{
				$tmp = substr($oFormsData[0]->value_date_year, 0, 1);
				if (intval($tmp) >= 5)
					$oFormsData[0]->value_date_year = "19" . $oFormsData[0]->value_date_year;
				else
					$oFormsData[0]->value_date_year = "20" . $oFormsData[0]->value_date_year;
			}
	}

function FormsLocale_DAN_GetDateTime($oFormsData)
	{
		if ($oFormsData[0]->value_date_year == 0 || $oFormsData[0]->value_date_month == 0 || $oFormsData[0]->value_date_day == 0
            || !strlen($oFormsData[0]->ori_value))
			return "";
        else
		    return  sprintf("%02d", intval($oFormsData[0]->value_date_day)) . "/" . sprintf("%02d", intval($oFormsData[0]->value_date_month)) . "/" . sprintf("%04d", intval($oFormsData[0]->value_date_year)) .
				    " " .  sprintf("%02d", intval($oFormsData[0]->value_date_hours)) . ":" .  sprintf("%02d", intval($oFormsData[0]->value_date_minutes)) . ":" .  sprintf("%02d", intval($oFormsData[0]->value_date_seconds));
	}

function FormsLocale_DAN_GetDate($oFormsData)
	{
		if ($oFormsData[0]->value_date_year == 0 || $oFormsData[0]->value_date_month == 0 || $oFormsData[0]->value_date_day == 0
            || !strlen($oFormsData[0]->ori_value))
			return "";
		else
			return sprintf("%02d", intval($oFormsData[0]->value_date_day)) . "/" . sprintf("%02d", intval($oFormsData[0]->value_date_month)) . "/" . sprintf("%04d", intval($oFormsData[0]->value_date_year));
	}

function FormsLocale_DAN_GetTime($oFormsData)
	{
		return $oFormsData[0]->value_date_hours . ":" . $oFormsData[0]->value_date_minutes /*. ":" . $oFormsData[0]->value_date_seconds*/;
	}

function FormsLocale_DAN_SetCurrency($oFormsData, $value)
	{
		$oFormsData[0]->value_text = $value;

		$value = str_replace(",", "", $value);
		preg_match_all("/^(\d+)(\.(\d+)){0,1}$/", $value, $matches);

		$oFormsData[0]->value_numeric_integer = $matches[1][0];
		$oFormsData[0]->value_numeric_decimal = $matches[3][0];
	}

function FormsLocale_DAN_GetCurrency($oFormsData)
	{
		return number_format($oFormsData[0]->value_numeric_integer + round($oFormsData[0]->value_numeric_decimal / pow(10, strlen($oFormsData[0]->value_numeric_decimal)), 2), 2, ".", ",");
	}

function FormsLocale_DAN_SetNumber($oFormsData, $value)
{
	$oFormsData[0]->value_text = $value;

	$value = str_replace(",", "", $value);
	preg_match_all("/^(\\-){0,1}\\s*(\\d+)(\\.(\\d+)){0,1}$/", $value, $matches);

	if (strlen($matches[1][0]))
		$oFormsData[0]->value_sign = true;
	else
		$oFormsData[0]->value_sign = false;

	$oFormsData[0]->value_numeric_integer = preg_replace("/[^0-9]+/", "", $matches[2][0]);
	$oFormsData[0]->value_numeric_decimal = preg_replace("/[^0-9]+/", "", $matches[4][0]);
}

function FormsLocale_DAN_GetNumber($oFormsData)
{
	if ($oFormsData[0]->value_sign)
		$sign = -1;
	else
		$sign = 1;

	if(intval($oFormsData[0]->value_numeric_decimal) > 0)
		return number_format($oFormsData[0]->value_numeric_integer * $sign, 0, "", ",") . "." . $oFormsData[0]->value_numeric_decimal;
	else
		return $oFormsData[0]->value_numeric_integer * $sign;
}

function FormsLocale_DAN_GetTimestamp($oFormsData)
{
    if($oFormsData[0]->value_date_hours == 0 
        && $oFormsData[0]->value_date_hours == 0
        && $oFormsData[0]->value_date_minutes == 0
        && $oFormsData[0]->value_date_seconds == 0
        && $oFormsData[0]->value_date_month == 0
        && $oFormsData[0]->value_date_day == 0
        && $oFormsData[0]->value_date_year == 0
    ) {
        return 0;
    } else {
        return mktime($oFormsData[0]->value_date_hours, $oFormsData[0]->value_date_minutes, $oFormsData[0]->value_date_seconds,
					$oFormsData[0]->value_date_month, $oFormsData[0]->value_date_day, $oFormsData[0]->value_date_year);
    }
}

function FormsLocale_DAN_SetTimestamp($oFormsData, $value)
{
	if(is_numeric($value) && $value > 0) {
		$oFormsData[0]->value_date_day = date("d", $value);
		$oFormsData[0]->value_date_month = date("m", $value);
		$oFormsData[0]->value_date_year = date("Y", $value);
		$oFormsData[0]->value_date_hours = date("H", $value);
		$oFormsData[0]->value_date_minutes = date("i", $value);
		$oFormsData[0]->value_date_seconds = date("s", $value);
	}
}
    
function FormsLocale_DAN_GetTimeToSec($oFormsData)
{        
    if(intval($oFormsData[0]->value_date_hours) == 0
        && intval($oFormsData[0]->value_date_minutes) == 0
        && intval($oFormsData[0]->value_date_seconds) == 0
        && intval($oFormsData[0]->value_date_month) == 0
        && intval($oFormsData[0]->value_date_day) == 0
        && intval($oFormsData[0]->value_date_year) == 0
    ) 
	{
        return 0;
    } 
	else 
	{
		if(intval($oFormsData[0]->value_date_month) == 0
	        && intval($oFormsData[0]->value_date_year) == 0
        ) {
			return ((intval($oFormsData[0]->value_date_day) * 24 * 60 * 60) + (intval($oFormsData[0]->value_date_hours) * 60 * 60) + (intval($oFormsData[0]->value_date_minutes) * 60) + intval($oFormsData[0]->value_date_seconds));
        } else {
            return gmmktime(intval($oFormsData[0]->value_date_hours), intval($oFormsData[0]->value_date_minutes), intval($oFormsData[0]->value_date_seconds), intval($oFormsData[0]->value_date_month), intval($oFormsData[0]->value_date_day), intval($oFormsData[0]->value_date_year));
		}
    }
}

function FormsLocale_DAN_SetTimeToSec($oFormsData, $value)
{
	if(is_numeric($value) && $value > 0)
	{
		$oFormsData[0]->value_date_day = intval(gmdate("d", $value));
		$oFormsData[0]->value_date_month = intval(gmdate("m", $value));
		$oFormsData[0]->value_date_year = intval(gmdate("Y", $value));
		$oFormsData[0]->value_date_hours = intval(gmdate("H", $value));
		$oFormsData[0]->value_date_minutes = intval(gmdate("i", $value));
		$oFormsData[0]->value_date_seconds = intval(gmdate("s", $value));
	}
}
 
function FormsLocale_DAN_CheckTime($raw_value)
{
    if (!preg_match("/\\d{1,2}:\\d{1,2}(:\\d{1,2}){0,1}/", $raw_value))
        return FALSE;
    else
        return true;
}

function FormsLocale_DAN_CheckDate($raw_value)
{
    if (!preg_match("/\\d{1,2}\\/\\d{1,2}\\/\\d{4}/", $raw_value))
        return FALSE;
    else
        return true;
}

function FormsLocale_DAN_CheckDateTime($raw_value)
{
    if (!preg_match("/\\d{1,2}\\/\\d{1,2}\\/\\d{4}\\s*\\d{1,2}:\\d{1,2}(:\\d{1,2}){0,1}/", $raw_value))
        return FALSE;
    else
        return true;
}

function FormsLocale_DAN_CheckCurrency($raw_value)
{
    if (!preg_match("/^\\s*\\-{0,1}\\s*\\d{1,3}(\\,{0,1}\\d{3})*\\s*(\\.\\s*\\d{1,2}){0,1}\\s*$/", $raw_value))
        return FALSE;
    else
        return true;
}

function FormsLocale_DAN_CheckNumber($raw_value)
{
     if (!preg_match("/^\\s*\\-{0,1}\\s*\\d+\\s*(\\.\\s*\\d+){0,1}\\s*$/", $raw_value))
        return FALSE;
    else
        return true;
}    
