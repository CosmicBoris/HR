<?php
class RegisterController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->_view->SetTitle('Реєстрація');
        $this->_model = new registerModel();
    }
    public function actionIndex()
    {
        if($this->isPost()) {
            $this->actionResult();
        }
        $this->_view->territory = $this->_model->GetTerritory();
        $this->_view->jobs = $this->_model->GetJobs();
        $this->_view->render();
    }
    public function actionResult()
    {
        $validator = Validator::GetInstance()->Validate($_POST, array('tLogin','Password','tPib','tMail','sTU','sIns','sJob'))->CheckForEmpty();
        if($validator->IsError()) {
            $this->_errors['validator'] = $validator->getErrors();
        } else {
            if(filter_var($validator->GetField('tMail'), FILTER_VALIDATE_EMAIL)){
                $user = $this->_model->GetUserByEmail($validator->GetField('tMail'));  //  проверяем свободен ли email
                if($user !== false) {
                    $this->_errors['mail'][] = 'вже в базі';
                }
                else {
                    $password = md5($validator->getField('Password').Config::SECRET);

                    $user = new User();
                    $user->UserLogin = $validator->GetField('tLogin');
                    $user->UserPassword = $password;
                    $user->UserFullName = $validator->GetField('tPib');
                    $user->UserEmail = $validator->GetField('tMail');
                    $user->InspectionID =  $validator->GetField('sIns');
                    if( false !== ($this->_model->AddUser($user, $_POST['sJob'])))
                    {
                        $this->_view->success = 'Заявка успішно надіслана очікуйте повідомлення на вказану електронну пошту.';
                    }else{
                        $this->_view->SetErrors('Db', $this->_model->getDBError());
                    }
                }
            } else {
                $this->_errors['mail'] = 'Електорона адреса хибна';
            }
        }
        $this->_view->SetErrors($this->_errors);
    }
    public function actionCheckEmail()
    {
        $validator = Validator::GetInstance()->Validate(array('mail'=>Url::GetParam()))->CheckForEmpty();
        if($validator->IsError()){
            echo "Електронна адреса хибна";
        }else if(!filter_var($validator->GetField('mail'), FILTER_VALIDATE_EMAIL)){
            echo "Електронна адреса несправжня";
        }else{
            $user = $this->_model->GetUserByEmail($validator->GetField('mail'));
            if($user !== false)
                echo "Електронна адреса вже зареєстрована в системі!";
        }

    }
    public function actionCheckLogin()
    {
        if($this->_model->GetUserByLogin(Url::GetParam()))
        {
            echo 'Логін вже в базі, оберіть інший';
        }
    }
    public function actionGetInspections()
    {
        $this->_model->GetInspections(Url::GetParam());
    }
}