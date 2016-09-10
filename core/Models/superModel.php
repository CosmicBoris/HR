<?php

/**
 * Created by PhpStorm.
 * User: boris
 * Date: 10.09.2016
 * Time: 2:12
 */
class SuperModel extends Model
{
    public function __construct()
    {
        parent::__construct();
    }
    function AddUser(User $u)
    {
        $u->password = md5($u->password.Config::SECRET);
        return $this->dbLink->insert('user', $u)->RunQuery();
    }
}