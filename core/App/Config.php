<?php
define('LOG_QUERY_AND_RESULT', TRUE);

class Config
{
    const DEFAULT_CONTROLLER = "Home";
    const STYLES_DIR         = "/assets/css/"; // works on host to...
    const JAVAScripts_DIR    = "/assets/js/";
    const LAYOUT_TYPE        = ".phtml";
    const MAIN_LAYOUT        = "index";
    const LOG_DIR            = "/logs/";
    const ERROR_LOG          = 'errorsLog.txt';
    const SECRET             = "2016DB"; // add this to user password
    const DB_PASS            = "ro8t1ng";

    const DB_HOST            = "localhost";
    const DB_USER            = "headhunter";
    const DB_NAME            = "hr";
    const CORE_FOLDER        = "core/";
    const LIB_FOLDER         = "core/Library/";
    const CONTROLLERS_DIR    = "core/Controllers/";
    const LAYOUT_DIR         = "/layouts/";

    //TsjgffcNoNCKoMO6XO
    /*const DB_HOST = 'mysql.hostinger.com.ua';
    const DB_USER = 'u635387968_hr';
    const DB_NAME = 'u635387968_hr';
    const CORE_FOLDER = '/home/u635387968/public_html/core/';
    const LIB_FOLDER = '/home/u635387968/public_html/core/Library/';
    const CONTROLLERS_DIR = "/home/u635387968/public_html/core/Controllers/";
    const VIEWS_DIR = '/home/u635387968/public_html/core/Views/';
    const LAYOUT_DIR = '/home/u635387968/public_html/layouts/';*/

    const SEARCH_STRING = "search_str";
}