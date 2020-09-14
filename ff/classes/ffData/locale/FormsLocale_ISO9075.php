<?php
/**
 * Data Handling: iso9075 (php, mysql and others)
 *
 * @package FormsFramework
 * @subpackage base
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */

function FormsLocale_ISO9075_SetDateTime($oFormsData, $value)
{
	preg_match_all("/(\d+)-(\d+)-(\d+)\s(\d+):(\d+):(\d+)/", $value, $matches);
	$oFormsData[0]->value_date_day = $matches[3][0];
	$oFormsData[0]->value_date_month = $matches[2][0];
	$oFormsData[0]->value_date_year = $matches[1][0];
	$oFormsData[0]->value_date_hours = $matches[4][0];
	$oFormsData[0]->value_date_minutes = $matches[5][0];
	$oFormsData[0]->value_date_seconds = $matches[6][0];

	FormsLocale_ISO9075_NormalizeDate($oFormsData);
}

function FormsLocale_ISO9075_SetTime($oFormsData, $value)
{
	preg_match_all("/(\d+)[:\s]*(\d+)/", $value, $matches);
	$oFormsData[0]->value_date_hours = $matches[1][0];
	$oFormsData[0]->value_date_minutes = $matches[2][0];
	$oFormsData[0]->value_date_seconds = $matches[3][0];
}

function FormsLocale_ISO9075_SetDate($oFormsData, $value)
{
	preg_match_all("/(\d+)-(\d+)-(\d+)/", $value, $matches);
	$oFormsData[0]->value_date_day = $matches[3][0];
	$oFormsData[0]->value_date_month = $matches[2][0];
	$oFormsData[0]->value_date_year = $matches[1][0];

	FormsLocale_ISO9075_NormalizeDate($oFormsData);
}

function FormsLocale_ISO9075_NormalizeDate($oFormsData)
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

function FormsLocale_ISO9075_GetDateTime($oFormsData)
{
	if ($oFormsData[0]->value_date_year == 0 || $oFormsData[0]->value_date_month == 0 || $oFormsData[0]->value_date_day == 0)
		return "";
	else
		return  sprintf("%'04u", $oFormsData[0]->value_date_year) . "-" . sprintf("%'02u", $oFormsData[0]->value_date_month) . "-" . sprintf("%'02u", $oFormsData[0]->value_date_day) . " " .
				sprintf("%'02u", $oFormsData[0]->value_date_hours) . ":" . sprintf("%'02u", $oFormsData[0]->value_date_minutes) . ":" . sprintf("%'02u", $oFormsData[0]->value_date_seconds);
}

function FormsLocale_ISO9075_GetDate($oFormsData)
{
	if ($oFormsData[0]->value_date_year == 0 || $oFormsData[0]->value_date_month == 0 || $oFormsData[0]->value_date_day == 0)
		return "";
	else
		return sprintf("%'04u", $oFormsData[0]->value_date_year) . "-" . sprintf("%'02u", $oFormsData[0]->value_date_month) . "-" . sprintf("%'02u", $oFormsData[0]->value_date_day);
}

function FormsLocale_ISO9075_GetEmptyDate()
{
	return "0000-00-00";
}

function FormsLocale_ISO9075_GetEmptyDateTime()
{
	return "0000-00-00 00:00:00";
}

function FormsLocale_ISO9075_GetTime($oFormsData)
{
	return sprintf("%'02u", $oFormsData[0]->value_date_hours) . ":" . sprintf("%'02u", $oFormsData[0]->value_date_minutes) . ":" . sprintf("%'02u", $oFormsData[0]->value_date_seconds);
}

function FormsLocale_ISO9075_SetNumber($oFormsData, $value)
{
	$oFormsData[0]->value_text = $value;

	preg_match_all("/^\\s*(\\-){0,1}\\s*(\\d+)\\s*(\\.\\s*(\\d+)){0,1}\\s*$/", $value, $matches);

	if (strlen($matches[1][0]))
		$oFormsData[0]->value_sign = true;
	else
		$oFormsData[0]->value_sign = false;

	$oFormsData[0]->value_numeric_integer = $matches[2][0];
	$oFormsData[0]->value_numeric_decimal = $matches[4][0];
}

function FormsLocale_ISO9075_GetNumber($oFormsData)
{
    if ($oFormsData[0]->value_sign)
      $sign = "-";
    else
      $sign = "";

    if(intval($oFormsData[0]->value_numeric_decimal) > 0)
      return $sign . $oFormsData[0]->value_numeric_integer . "." . $oFormsData[0]->value_numeric_decimal;
    else
      return $sign . $oFormsData[0]->value_numeric_integer;
}

function FormsLocale_ISO9075_SetExtNumber($oFormsData, $value)
{
	$oFormsData[0]->value_text = $value;

	preg_match_all("/^\\s*(\\-){0,1}\\s*(\\d+)\\s*(\\.\\s*(\\d+)){0,5}\\s*$/", $value, $matches);

	if (strlen($matches[1][0]))
		$oFormsData[0]->value_sign = true;
	else
		$oFormsData[0]->value_sign = false;

	$oFormsData[0]->value_numeric_integer = $matches[2][0];
	$oFormsData[0]->value_numeric_decimal = $matches[4][0];
}

function FormsLocale_ISO9075_GetExtNumber($oFormsData)
{
    if ($oFormsData[0]->value_sign)
      $sign = "-";
    else
      $sign = "";

    if(intval($oFormsData[0]->value_numeric_decimal) > 0)
      return $sign . $oFormsData[0]->value_numeric_integer . "." . $oFormsData[0]->value_numeric_decimal;
    else
      return $sign . $oFormsData[0]->value_numeric_integer;
}

function FormsLocale_ISO9075_GetTimestamp($oFormsData)
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
        return mktime(intval($oFormsData[0]->value_date_hours), intval($oFormsData[0]->value_date_minutes), intval($oFormsData[0]->value_date_seconds), intval($oFormsData[0]->value_date_month), intval($oFormsData[0]->value_date_day), intval($oFormsData[0]->value_date_year));
    }
}

function FormsLocale_ISO9075_SetTimestamp($oFormsData, $value)
{
	if(is_numeric($value) && $value > 0)
	{
		$oFormsData[0]->value_date_day = intval(date("d", $value));
		$oFormsData[0]->value_date_month = intval(date("m", $value));
		$oFormsData[0]->value_date_year = intval(date("Y", $value));
		$oFormsData[0]->value_date_hours = intval(date("H", $value));
		$oFormsData[0]->value_date_minutes = intval(date("i", $value));
		$oFormsData[0]->value_date_seconds = intval(date("s", $value));
	}
}

function FormsLocale_ISO9075_GetTimeToSec($oFormsData)
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

function FormsLocale_ISO9075_SetTimeToSec($oFormsData, $value)
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

function FormsLocale_ISO9075_CheckTime($raw_value)
{
    if (!preg_match("/\\d{1,2}:\\d{1,2}(:\\d{1,2}){0,1}/", $raw_value))
        return FALSE;
    else
        return true;
}

function FormsLocale_ISO9075_CheckDate($raw_value)
{
    if (!preg_match("/\\d{1,4}\\-\\d{1,2}\\-\\d{2}/", $raw_value))
        return FALSE;
    else
        return true;
}

function FormsLocale_ISO9075_CheckDateTime($raw_value)
{
    if (!preg_match("/\\d{1,4}\\-\\d{1,2}\\-\\d{2}\\s*\\d{1,2}:\\d{1,2}(:\\d{1,2}){0,1}/", $raw_value))
        return FALSE;
    else
        return true;
}

