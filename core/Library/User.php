<?php
class User extends Init
{
    public
        $id,
        $name,
        $surname,
        $email,
        $password;

    public function __construct($obj = false)
    {
        parent::__construct();
        if($obj)
            $this->Init($obj);
    }
}    