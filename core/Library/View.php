<?php
/**
 * Created by PhpStorm.
 * User: Boris
 * Date: 28.12.2015
 * Time: 9:13
 */
class View
{
    protected $_title;
    protected $_common_layout = Config::MAIN_LAYOUT;
    protected $_page_content;
    protected $_data;     // contains data for view
    protected $_errors;

    public function __construct($storage)
    {
        $this->_data = $storage;
    }

    public function __get($name)
    {
        return $this->_data->Get($name);
    }
    public function __set($name, $value)
    {
        $this->_data->Set($name, $value);
    }
    public function __isset($name)
    {
        return $this->_data->has($name);
    }

    public function SetTitle($title)
    {
        $this->_title = $title;
    }

    /**
     * @param string|bool $layout
     * @param string|bool $view
     */
    public function render($layout = false, $view = false)
    {
        if($layout) $this->_common_layout = $layout;

        $this->_page_content = Config::LAYOUT_DIR
            .Router::getControllerName(Router::SHORT_NAME).'/';
        if($view) $this->_page_content .= $view;
        else $this->_page_content .= lcfirst(Router::getActionName(0));
        $this->_page_content .= Config::LAYOUT_TYPE;

        try {
            include_once Config::LAYOUT_DIR.$this->_common_layout.Config::LAYOUT_TYPE;
        }
        catch(Exception $e) {}
    }
    public function partialRender($layout = false)
    {
        $_view = Config::LAYOUT_DIR.Router::getControllerName(0).'/';
        if($layout) $_view .= $layout;
        else $_view .= lcfirst(Router::getActionName(0));
        $_view .='_partial'.Config::LAYOUT_TYPE;

        try {
            include_once $_view;
        } catch(Exception $e) {}
    }

    public function SetPropertyArray($array)
    {
        foreach($array as $key => $value)
            $this->_data->$key = $value;
    }
    public function SetErrors($errors, $key = false)
    {
        if($key){
            $this->_errors[$key] = $errors;
        }else{
            $this->_errors = $errors;
        }
    }
}