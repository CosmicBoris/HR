<?php

class LoginController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        if(Auth::IsLogged())
        {
            if(Auth::IsAdmin()){
                header('Location: /super');
            } else {
                header('Location: /workspace');
            }
            exit();
        }
    }
    public function actionIndex()
    {
        if($this->isPost())
        {
            Response::ReturnJson();
            $response = array();
            $validator = Validator::GetInstance()->Prepare($_POST, ['email','password'])->CheckForEmpty();
            if(!$validator->IsError()) {
                $this->_model = new loginModel();
                if(!$response = $this->_model->Login($validator->GetField('email'), $validator->GetField('password'))) {
                    if(empty($this->_model->getError())){
                        $response['error'] = "Wrong e-mail or password!";
                    } else {
                        $response['error'] = $this->_model->getError();
                    }
                }
            }
            else {
                $response['error'] = $validator->GetErrors();
            }
            echo json_encode($response);
        } else {
            $this->_view->SetTitle('Sing In');
            $this->_view->render();
        }
    }
}