<?php
class SuperController extends Controller
{
    public function __construct()
    {
        parent::__construct(); // initialize view + parent common methods
        $this->_view->SetTitle('Адміністратор');
        $this->_model = new SuperModel();
        if(!Auth::isAdmin()) {
            header("Location: /login");
            die();
        }
    }

    public function actionIndex()
    {
        $this->_view->territory = $this->_model->DbMethod('GetAllTA');
        $this->_view->jobs = $this->_model->DbMethod('GetAllpermission');
        $this->_view->userAwaitsCount = $this->_model->GetNonAthorizedCount();
        $this->_view->userAllCount = $this->_model->GetUsersCount();

        $this->_view->render('workspace');
    }

    public function actionLoadNonAuth()
    {
        if($csearch = $_GET['search']){
            Response::ReturnJson();
            $resp = array('heading' => 'Результати пошуку: '.$csearch);

            $users = $this->_model->GetNonAthorized(Url::GetParam(), $csearch);

            foreach( $users as &$user ){
                $user->btnApprove = htmlbuttonHelper::Form(array("class" => "btn btn-success btn-xs", "id" =>$user->UserID,
                    "onClick" => 'Accept(this)', '<span class="glyphicon glyphicon glyphicon-ok" aria-hidden="true"></span>'));
                $user->btnRemove = htmlbuttonHelper::Form(array("class" => "btn btn-danger btn-xs", "id" =>$user->UserID,
                    "onClick" => 'Accept(this)', '<span class="glyphicon glyphicon glyphicon-remove" aria-hidden="true"></span>'));
                unset($user->UserPassword);
                unset($user->InspectionID);
                unset($user->permissionID);
                $user = (array)$user;
            }
            $ht = new htmltableHelper();
            $resp['table'] = $ht->Body($users)->getTableBody();
            $resp['pagination'] = paginationHelper::Form(
                $this->_model->GetNonAthorizedCount($csearch), "super/LoadNonAuth");
            echo json_encode($resp);
        } else {
            $this->_view->heading = 'Заяви на реєстрацію:';
            $this->_view->nUserCount = $this->_model->GetNonAthorizedCount();
            $this->_view->users = $this->_model->GetNonAthorized(Url::GetParam());
            $this->_view->PartialRender('adminNonAuth');
        }
    }

    public function actionGetAllUsers()
    {
        if($csearch = $_GET['search']) {
            Response::ReturnJson();
            $resp = array('heading' => 'Результати пошуку: '.$csearch);
            $par = array();
            $par[] = $csearch;
            $users = $this->_model->GetUsers(Url::GetParam(), $par);

            foreach( $users as &$user ){
                $user->btnInfo = htmlbuttonHelper::Form(array("class" => "btn btn-info btn-xs", "id" =>$user->UserID,
                    "data-pid" => $user->permissionID, '<span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>'));
                $user->btnRemove = htmlbuttonHelper::Form(array("class" => "btn btn-danger btn-xs", "id" =>$user->UserID,
                    "data-pid" => $user->permissionID, '<span class="glyphicon glyphicon glyphicon-remove" aria-hidden="true"></span>'));
                $user->btnEdit = htmlbuttonHelper::Form(array("class" => "btn btn-warning btn-xs", "id" =>$user->UserID, "data-pid" => $user->permissionID, 'ред.<span class="glyphicon glyphicon-edit" aria-hidden="true"></span>'));
                unset($user->UserPassword);
                unset($user->InspectionID);
                unset($user->permissionID);
                $user = (array)$user;
            }
            $ht = new htmltableHelper();
            $resp['table'] = $ht->Body($users)->getTableBody();
            $resp['pagination'] = paginationHelper::Form(
                $this->_model->GetUsersCount($csearch), "super/GetAllUsers");
            echo json_encode($resp);
        } else {
            $this->heading = 'Всі користувачі:';
            $this->_view->users = $this->_model->GetUsers(Url::GetParam());
            $this->_view->nUserCount = $this->_model->DbMethod("GetUsersCount");
            $this->_view->PartialRender('adminAllUsers');
        }
    }

    public function actionApprove()
    {
        Response::ReturnJson();
        if($this->_model->ApproveUser(Url::GetParam()))
        {
            echo json_encode(array('status' => 1));
        } else {
            echo json_encode(array('status' => "error"));
        }
    }

    public function actionReject()
    {
        Response::ReturnJson();
        if($this->_model->RejectUser(Url::GetParam())) {
            echo json_encode(array('status' => 1));
        }else{
            echo json_encode(array('status' => "error"));
        }
    }

    public function actionDelete()
    {
        Response::ReturnJson();
        $res = $this->_model->DeleteUser(Url::GetParam());
        if($res === true){
            echo json_encode(array('status' => 1));
        }else{
            echo json_encode(array('status' => "error", 'result' => $res));
        }
    }

    public function actionEditUser()
    {
        Response::ReturnJson();
        $arg = array();
        if($this->isPost()) {
            if($this->_model->EditUser($_POST, $arg)) {
                $arg->status = 1;
            }
        } else {
            $user = $this->_model->GetUser(Url::GetParam());
            $arg = $user;
        }
        echo json_encode($arg);
    }

    public function actionUpdate()
    {
        $ar = array();
        $ar['Awaits'] = $this->_model->GetNonAthorizedCount();
        $ar['All'] = $this->_model->GetUsersCount();
        Response::ReturnJson();
        echo json_encode($ar);
    }

    public function actionSql()
    {
        echo $this->_model->ExecQuery($_POST['query']);
    }

    public function actionSetParam()
    {
        Response::ReturnJson();
        foreach($_POST as $key => $value) {
            setcookie($key, $value, time()+(60*60*24*7), '/');
        }
        echo json_encode(array('status' => 1));
    }

    public function actionSearch()
    {
        $this->heading = 'Результати пошуку:';
        $this->_view->users = $this->_model->GetUsers();
        $this->_view->nUserCount = $this->_model->DbMethod("GetUsersCount");
        $this->_view->PartialRender('adminAllUsers');
    }
}