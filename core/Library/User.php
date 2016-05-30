<?php
class User extends Init implements IteratorAggregate
{
    public $id;
    public $name;
    public $surname;
    public $email;
    public $password;

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