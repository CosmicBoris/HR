<?php
/**
 * Created by PhpStorm.
 * User: Boris
 * Date: 11.01.2016
 * Time: 14:25
 */
class RegisterModel extends Model
{
    public function __construct()
    {
        parent::__construct();
    }
    public function GetTerritory()
    {
        return $this->dbLink->GetAllTA();
    }
    public function GetJobs()
    {
        return $this->dbLink->GetAllpermission();
    }
    public function GetUserByEmail($str)
    {
        return $this->dbLink->FindUserBy("UserEmail", $str);
    }
    public function GetUserByLogin($str)
    {
        $result = $this->dbLink->select('user', 'UserLogin')->where(array('UserLogin'=>$str))->RunQuery();
        if($result->num_rows > 0)
        {
            return true;
        }
        return false;
    }
    public function GetInspections($RegId)
    {
        $ins = $this->dbLink->GetInspectionsByRegion($RegId);
        foreach($ins as $value)
        {
            echo '<option value="'.$value->id.'">'.$value->description.'</option>';
        }
    }
    public function AddUser($user, $job)
    {
        return $this->dbLink->AddUser($user, $job);
    }
}