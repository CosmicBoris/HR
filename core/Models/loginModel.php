<?php
class LoginModel extends Model
{
    public function __construct()
    {
        parent::__construct();
    }
    public function Login($l, $p)
    {
        $user = new User();
        $result = $this->dbLink->select('user', array_keys(get_object_vars($user)))
            ->where(['email'=>$l, 'password'=>md5($p.Config::SECRET)], '=', "AND")
            ->RunQuery();

        if(!$result) {
            $this->_error = $this->dbLink->getErrors();
            return false;
        } else if($result->num_rows == 0) return false;

        if(!$obj = $result->fetch_assoc()) {
            $this->_error = "Can`t fetch result!";
            return false;
        }

        $user->Init($obj);
        foreach ($user as $prop => $value){
            $_SESSION[$prop] = $value;
        }
        return $user;
    }
}