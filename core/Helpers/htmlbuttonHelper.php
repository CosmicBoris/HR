<?php

/**
 * Created by PhpStorm.
 * User: Boris
 * Date: 24.03.2016
 * Time: 15:48
 */
class htmlbuttonHelper
{
	/**
	 * @param $params
	 * value should be last param
	 * @return string
	 */
	public static function Form($params)
	{
		$output = "<button";
		$text = array_pop($params);
		foreach($params as $param => $value){
			$output .= ' '.$param.'="'.$value.'"';
		}
		return $output .= '>'.$text."</button>";
	}
}