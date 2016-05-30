<?php

/**
 * Created by PhpStorm.
 * User: boris
 * Date: 08.05.2016
 * Time: 18:06
 */
class Vacancy extends Init implements IteratorAggregate
{
    public $id;
    public $user_id;
    public $title;
    public $state;
    public $date_added;
    public $description;
    
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