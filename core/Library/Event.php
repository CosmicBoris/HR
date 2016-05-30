<?php
/**
 * Created by PhpStorm.
 * User: boris
 * Date: 15.05.2016
 * Time: 23:41
 */
class Event extends Init implements IteratorAggregate
{
    public $id;
    public $title;
    public $start;
    public $end;
    public $event_type;
    public $candidate_id;
    public $vacancy_id;

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