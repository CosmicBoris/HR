<?php

/**
 * Created by PhpStorm.
 * User: Boris
 * Date: 12.02.2016
 * Time: 10:37
 */
final class Storage
{
    private $data = array();

    public function Set($name, $value){
        $this->data[$name] = $value;
    }
    public function Get($name){
        return (isset($this->data[$name]) ? $this->data[$name] : null);
    }
    public function __isset($name){
        return isset($this->data[$name]);
    }
    public function has($key) {
        return isset($this->data[$key]);
    }
}