<?php

/**
 * Created by PhpStorm.
 * User: boris
 * Date: 08.05.2016
 * Time: 18:06
 */
class Candidate extends Init implements IteratorAggregate
{
    public $id;
    public $fullname;
    public $sex;
    public $age;
    public $profile;
    public $email;
    public $phone;
    public $photo;
    public $skills;

    public function __construct($obj = false)
    {
        parent::__construct();
        if($obj)
            $this->Init($obj);
    }
    public function getIterator()
    {
        return new ArrayIterator($this);
    }
}