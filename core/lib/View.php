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
        {
            $this->_data->$key = $value;
        }
    }
    public function SetErrors($errors, $key = false)
    {
        if($key){
            $this->_errors[$key] = $errors;
        }else{
            $this->_errors = $errors;
        }
    }
    public function GetError($errorType = false, $allErrors = false)
    {
        $output = "";
        if(!empty($this->_errors)):
        $output .= '<div class="alert alert-danger" role="alert">';
        $output .= '<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>';
        $output .= '<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span>';

            // TODO check if errorType if array;
            if($allErrors):
                foreach(($errorType !== false) ? $this->_errors[$errorType] : $this->_errors as $error):
                    if(is_array($error)){
                        foreach($error as $item => $value):
                            $output .= "<span>$item : $value</span>";
                        endforeach;
                    }
                    else {
                        $output.="<span>$error</span>";
                    }
                endforeach;
            else:
                $output.='<span>'.$this->_errors[$errorType][0].'</span></span><br>';
            endif;
        $output.="</div>";
        endif;
        return $output;
    }
    public function GetSuccess()
    {
        if(isset($this->success)):
?>
        <div class="alert alert-success alert-dismissible fade in" role="alert">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
                <span>
                    <?=$this->success?>
                </span>
        </div>
        <br>
<?
        endif;
    }
}