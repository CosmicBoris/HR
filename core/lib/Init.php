<?php

/**
 * Created by PhpStorm.
 * User: Boris
 * Date: 20.02.2016
 * Time: 23:44
 */
class Init
{
    public function __construct(){ }

    public function Init($obj)
    {
        foreach($obj as $key => $value)
        {
            if(property_exists($this, $key)){
                $this->$key = $value;
            }
        }
    }
}