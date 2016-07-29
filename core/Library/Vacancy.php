<?php

/**
 * Created by PhpStorm.
 * User: boris
 * Date: 08.05.2016
 * Time: 18:06
 */
class Vacancy extends Init
{
    public
        $id,
        $user_id,
        $title,
        $state,
        $date_added,
        $description;
    
    public function __construct($obj = false)
    {
        parent::__construct();
        if($obj)
            $this->Init($obj);
    }
}