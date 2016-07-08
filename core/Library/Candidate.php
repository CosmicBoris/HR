<?php

/**
 * Created by PhpStorm.
 * User: boris
 * Date: 08.05.2016
 * Time: 18:06
 */
class Candidate extends Init
{
    public
        $id,
        $fullname,
        $sex,
        $age,
        $profile,
        $email,
        $phone,
        $photo,
        $skills;

    public function __construct($obj = false)
    {
        parent::__construct();
        if($obj)
            $this->Init($obj);
    }
}