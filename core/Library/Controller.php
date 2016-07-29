<?php
abstract class Controller
{
    protected
        $_model,
        $_view,
        $_storage,
        $_errors;

    public function __construct()
    {
        $this->_storage = new Storage(); // common storage between Controller Model and View;;
        $this->_view = new View($this->_storage);
        $this->_errors = array();
    }
    /** default action of every Controller **/
    abstract public function actionIndex();

    public function show404() // if user require non-existent action
    {
        Response::ReturnCode(404);
        $this->_view->SetTitle('Page not found');
        $this->_view->render('nopage');
    }

    public function isPost() : bool
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            return true;
        }
        return false;
    }
    public function isAjax() : bool
    {
        if(isset($_GET['ajax'])) return true;
        return false;
    }
    public function __set($name, $value)
    {
        $this->_storage->Set($name, $value);
    }
    public function __get($name)
    {
        return $this->_storage->Get($name);
    }
    public function __isset($name)
    {
        return $this->_storage->has($name);
    }
}