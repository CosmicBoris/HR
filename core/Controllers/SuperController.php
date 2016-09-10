<?php
class SuperController extends Controller
{
    public function __construct(Router $router)
    {
        parent::__construct($router); // initialize view + parent common methods
        $this->_view->SetTitle('Адміністратор');
        $this->_model = new SuperModel();
    }

    public function actionIndex()
    {
        if($this->isPost()){
            $user = new User($_POST);
            if($this->_model->AddUser($user))
                Response::ReturnJson(['success'=>1]);
        } else
            $this->_view->render();
    }
}