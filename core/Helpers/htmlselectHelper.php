<?php
class htmlselectHelper
{
    /**
     * @param $data
     * @param array|string|bool $skip
     * @return string
     */
    public static function Form($data, $skip = false)
    {
        $str = '';
        if($skip) {
            if(is_array($skip)) {
                foreach ($data as $value) {
                    if(!in_array($value->description, $skip)){
                        $str .= '<option value="' . $value->id . '">' . $value->description . '</option>';
                    }
                }
            }
            else{
                foreach ($data as $value) {
                    if($value->description==$skip){
                        continue;
                    }
                    $str .= '<option value="' . $value->id . '">' . $value->description . '</option>';
                }
            }
        }
        else{
            foreach ($data as $value) {
                $str .= '<option value="' . $value->id . '">' . $value->description . '</option>';
            }
        }
        return $str;
    }
}