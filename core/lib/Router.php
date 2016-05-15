<?php

/**
 * Class Router
 */
class Router
{
    const SHORT_NAME = 0;
    const FULL_NAME  = 1;

	private static $segments = array();

    public function __construct()
	{
        self::$segments = explode('/', explode('?', $_GET['url'])[0]); // Uri without get parameters
		$this->Start();
	}

    public function Start()
	{
		if(!empty(self::$segments[0])) { // [0] имя контролера с большой буквы
            self::$segments[0] = ucfirst(self::$segments[0]);
            $controller_name = self::$segments[0].'Controller';
		} else {
            // если не указан контролер | default controller;
            $controller_name = Config::DEFAULT_CONTROLLER.'Controller';
        }
		if(file_exists(Config::CONTROLLERS_DIR.$controller_name.'.php')) {
			$controller = new $controller_name();
            $action_name = (empty(self::$segments[1])) ? 'actionIndex' : 'action'.ucfirst(self::$segments[1]);
			if(method_exists($controller, $action_name)) {
				$controller->$action_name();
    		} else {
                $controller_name = Config::DEFAULT_CONTROLLER.'Controller';
                $controller = new $controller_name();
                $controller->show404();
            }
		} else {
            $controller_name = Config::DEFAULT_CONTROLLER.'Controller';
            $controller = new $controller_name();
			$controller->show404();
		}
	}

    /**
     * @param $mod
     * @return mixed|string
     */
    public static function getControllerName($mod) {
        $name = "";
        if(!empty(self::$segments[0]))
            $name = self::$segments[0];
        else
            $name = Config::DEFAULT_CONTROLLER;

        return $mod == self::FULL_NAME ? $name."Controller" : $name;
	}

    /**
     * @param $mod(FULL_NAME|SHORT_NAME)
     * @return string
     */
    public static function getActionName($mod) {
        $name = "";
        if(!empty(self::$segments[1]))
            $name = ucfirst(self::$segments[1]);
        else
            $name = 'Index';

        return $mod == self::FULL_NAME ? 'action'.$name : $name;
	}

    /**
     * @param int|bool $pos
     * @return array|bool
     */
    public static function getUriSegment($pos = false)
    {
        if($pos === false){
            return self::$segments;
        }
        if(isset(self::$segments[$pos])) {
            return self::$segments[$pos];
        }
        return false;
    }
}