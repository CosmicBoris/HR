<?php

/**
 * Created by PhpStorm.
 * User: boris
 * Date: 15.05.2016
 * Time: 23:41
 */
class event extends Init implements IteratorAggregate
{
    public $id;
    public $datetime_start;
    public $datetime_end;
    public $event_type;

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