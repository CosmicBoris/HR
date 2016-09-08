<?php
/**
 * Created by PhpStorm.
 * User: boris
 * Date: 15.05.2016
 * Time: 23:41
 */
class Event extends Init
{
    public
        $id,
        $title,
        $description,
        $created,
        $start,
        $end,
        $event_type,
        $className,
        $candidate_id,
        $vacancy_id;

    public function __construct($obj = false)
    {
        parent::__construct();
        if($obj) $this->Init($obj);
    }
}