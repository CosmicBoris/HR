<?php
/**
 * Created by PhpStorm.
 * User: Boris
 * Date: 30.12.2015
 * Time: 13:15
 */
final class Validator // vse proverki tut
{
    protected static $_instance = null;
    private $_data;
    private $_required_fields;
    private $_errors = array();

    private function __construct() {}
    public static function GetInstance()
    {
        if(is_null(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }
    public function Prepare($data, $required_fields = false, $keys = false)
    {
        $this->_data = $this->GetSafeString($data, $keys);
        $this->_required_fields = $required_fields;
        return $this;
    }
    public function CheckForEmpty()
    {
        if($this->_required_fields === false)
        {
            foreach((array)$this->_data as $key => $value)
            {
                if(empty($value)){
                    $this->_errors['empty'][] = $key;
                }
            }
        }
        else {
            foreach($this->_required_fields as $field_name){
                if(is_null($this->_data[$field_name])) {
                    $this->_errors['empty'][] = $field_name;
                }
            }
        }
        return $this;
    }
    public function GetAllFields(){
        return $this->_data;
    }
    public function GetField($field_name = false)
    {
        if(is_array($this->_data) && isset($this->_data[$field_name])){
            return $this->_data[$field_name];
        }
        if(!$field_name)return $this->_data;
        return null;
    }
    public function CheckEmail(string $field)
    {
        return filter_var($this->GetField($field), FILTER_VALIDATE_EMAIL);
    }
    
    public function IsError() : bool
    {
        if(!empty($this->_errors)){
            return true;
        }
        return false;
    }
    public function GetErrors()
    {
        return $this->_errors;
    }
    // remove html code
    private function GetSafeString($data, $keys) // $keys which we ignore
    {
        if(is_array($data)){
            foreach ($data as $key => $value) {
                if (is_array($keys)) // we have keys to ignore
                {
                    if (!in_array($key, $keys)) // if key that we don`t ignore
                    {
                        $data[$key] = trim(htmlspecialchars($value)); // trim remove space at begin end end of str
                    }
                }
                else // we don`t have keys to ignore
                {
                    $data[$key] = trim(htmlspecialchars($value));
                }
                return $data;
            }
        } else {
            return htmlspecialchars($data);
        }
    }
}