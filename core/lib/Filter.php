<?php

/**
 * Created by PhpStorm.
 * User: Boris
 * Date: 01.03.2016
 * Time: 17:25
 */
class Filter
{
    const ONLY_AUTHORIZED = "authOnly";
    const ONLY_INUSERFIELDS = "uf";
    public static function Check()
    {
        if($_COOKIE[self::ONLY_AUTHORIZED] == "true") {
            return "checked";
        }
        return false;
    }
}