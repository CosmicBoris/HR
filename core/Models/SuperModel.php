<?php
/**
 * Created by PhpStorm.
 * User: Boris
 * Date: 30.12.2015
 * Time: 9:37
 */
class SuperModel extends Model
{
    public function __construct()
    {
        parent::__construct();
    }
    public function GetNonAthorizedCount($params = false)
    {
        return $this->dbLink->NonAthorizedCount($params);
    }
    public function GetNonAthorized($from = false, $searchStr = false)
    {
        $users = $this->dbLink->GetNonAthorized(paginationHelper::Limit($from), $searchStr);
        if(count($users)==0 && $from > 0) {
            paginationHelper::setCurrentPage($from-1);
            return $this->dbLink->GetNonAthorized(paginationHelper::Limit($from-1), $searchStr);
        } else {
            return $users;
        }
    }
    public function GetUsersCount($params = false)
    {
        return $this->dbLink->GetUsersCount($params);
    }
    public function GetUser($id)
    {
        $result = $this->dbLink->select('user',
            array (
                'user' => array(
                    'UserID',
                    'UserLogin',
                    'UserFullName',
                    'UserEmail',
                    'InspectionID'
                ),
                'region' => array(
                    'RegionDescription',
                    'RegionID'
                ),
                'permission'=>array(
                    'permissionID'
                ),
            'InspectionDescription',
            'permissionDescription',
            )
        )->innerJoin('userpermission', array('UserID'=>'UserID'))
            ->innerJoin('permission', array('permissionID'=>'permissionID'), 'userpermission')
            ->innerJoin('inspection', array('InspectionID'=>'InspectionID'))
            ->innerJoin('region', array('RegionID'=>'RegionID'), 'inspection')
            ->where(array('user.UserID'=>$id))->RunQuery();

        if($result) {
            $obj = $result-> fetch_assoc();
            if($obj) {
                $user = new User($obj);
                $user->region = $obj["RegionDescription"];
                $user->RegionID = $obj["RegionID"];
                $user->inspection = $obj["InspectionDescription"];
                $user->permission = $obj["permissionDescription"];
                $user->permissionID = $obj["permissionID"];
                $result->close();
                return $user;
            }
            return false;
        }
        return false;
    }
    public function GetUsers($from = false, $params = false)
    {
        if(Filter::Check()){
            $params[] = Filter::ONLY_AUTHORIZED;
        }
        $users = $from ? $this->dbLink->GetUsers(paginationHelper::Limit($from), $params)
            : $this->dbLink->GetUsers(paginationHelper::Limit(0), $params);
        if(count($users)==0 && $from > 0){
            paginationHelper::setCurrentPage($from-1);
            return $this->dbLink->GetUsers(paginationHelper::Limit($from-1), $params);
        }else{
            return $users;
        }
    }

    public function EditUser($data, &$arg){
        $fields = array('UserLogin' => $data['tLogin'],'UserFullName' => $data['tPib'],
            'UserEmail' => $data['tMail'],'InspectionID' => $data['sIns']);

        $this->dbLink->autocommit(false);
        if($this->dbLink->update('user', $fields)->where(array('UserID'=>Url::GetParam()))->RunQuery())
        {
            if($this->dbLink->update('userpermission', array('permissionID' => $data['sJob']))->where(array('UserID'=>Url::GetParam()))->RunQuery())
            {
                if($this->dbLink->commit()) {
                    $arg = $this->dbLink->GetUser(Url::GetParam());
                    return true;
                }
            }
        }
        $arg = $this->dbLink->getErrors();
        return false;
    }
    public function ApproveUser($uid)
    {
        return $this->dbLink->Athorize($uid);
    }
    public function RejectUser($uid)
    {
        return $this->dbLink->DeleteReq($uid);
    }
    public function DeleteUser($uid)
    {
        return $this->dbLink->DeleteUser($uid);
    }
    public function ExecQuery($str)
    {
        if($this->dbLink->ExecuteSql($str)){
            return "Успіх!";
        }
        return "Помылка: ". $this->dbLink->getErrors();
    }
}