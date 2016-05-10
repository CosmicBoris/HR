<?php
class htmlselectHelper
{
    public static function Form($data, $id, $value)
    {
        $str = '';
        foreach ($data as $item) {
            $str .= '<option value="'.$item->$id.'">'.$item->$value.'</option>';
        }
        return $str;
    }
}