<?php
/*  ini_set('session.cookie_lifetime', 60 * 60);
    ini_set('session.gc-maxlifetime', 60 * 60);*/
	session_start();
    // Main config file
    require_once 'core/App/Config.php';

    function __autoload($class_name)
    {
        // разбивает строку по Заглавным буквам, возвращает массив строк
        $segments = preg_split('/(?<=[a-z])(?=[A-Z])/', $class_name);
        $filename = $class_name.'.php';
        if (count($segments) < 2) {
            $filepath = 'core/Library/';
        } else {
            $filepath = 'core/'. $segments[1]. 's/';
        }
        if (file_exists($filepath.$filename) == false) {
            die('file '.$filepath.$filename.' not found');
        }
        require_once $filepath.$filename;
    }

    $router = new Router();