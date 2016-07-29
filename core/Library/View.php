<?php
/**
 * Created by PhpStorm.
 * User: Boris
 * Date: 28.12.2015
 * Time: 9:13
 */
class View
{
    protected
        $_title,
        $_common_layout = Config::MAIN_LAYOUT,
        $_page_content,
        $_data,     // contains data for view
        $_errors;

    public function __construct(Storage $storage)
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

    public function SetTitle(string $title)
    {
        $this->_title = $title;
    }

    /**
     * @param string|bool $layout
     * @param string|bool $view
     */
    public function render(string $layout = "", string $view = null)
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
    public function partialRender(string $layout = null)
    {
        $_view = Config::LAYOUT_DIR.Router::getControllerName(0).'/';
        $_view .= $layout ?? lcfirst(Router::getActionName(0));
        $_view .='_partial'.Config::LAYOUT_TYPE;

        try {
            include_once $_view;
        } catch(Exception $e) {}
    }

    public function SetPropertyArray(array $array)
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