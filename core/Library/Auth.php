<?php
class Auth
{
    const USERID = 'id';
    const USERNAME = 'name';
    const USERSURNAME = 'surname';
    const USEREMAIL = 'usermail';
    const ROLE = 'role';

    public static function IsAdmin() : bool
    {
        if($_SESSION[self::ROLE] == 'admin') {
            return true;
        }
        return false;
    }
    public static function IsLogged() : bool
    {
        if(!empty($_SESSION[self::USERID])) {
            return true;
        }
        return false;
    }
    public static function GetUserID()
    {
        return $_SESSION[self::USERID];
    }
    public static function GetUserName()
    {
        return $_SESSION[self::USERNAME].' '.$_SESSION[self::USERSURNAME];
    }
}