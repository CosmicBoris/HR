<?php

/**
 * Created by PhpStorm.
 * User: Boris
 * Date: 06.02.2016
 * Time: 18:37
 * This class helps to generate html tables
 */
class htmltableHelper
{
    private $output;
    private $id;
    private $class;
    private $dataAttr;
    private $thead;
    private $tbody;

    public function SetTableId($id)
    {
        $this->id = ' id="'.$id.'"';
        return $this;
    }
    public function SetTableClass($class)
    {
        $this->class = ' class="'.$class.'"';
        return $this;
    }
    public function SetDataAttributes($attr)
    {
        $this->dataAttr = "";
        if(is_array($attr))
        {
            foreach($attr as $name => $value ) {
                $this->dataAttr .= ' data-'.$name.'="'.$value.'"';
            }
        }
        else {
            $this->dataAttr = ' data-'.$attr;
        }

        return $this;
    }
    /**
     * Use key "content" to insert in tag, other keys ass adds parameters to tag
     * @param $params
     * @return $this
     */
    public function Head($params)
    {
        $this->thead = '<thead><tr>';
        foreach ($params as $param)
        {
            if (is_array($param)) {
                $this->thead .= '<th ';
                foreach($param as $name => $value){
                    if($name == 'content'){
                        $this->thead .= '>'.$value.'<';
                        continue;
                    }
                    $this->thead .= $name.'="'.$value.'" ';
                }
                $this->thead .= '/th>';
            } else {
                $this->thead .= '<th>' . $param . '</th>';
            }
        }

        $this->thead .= '</tr></thead>';
        return $this;
    }
    public function Body($params)
    {
        $this->tbody = '';

        foreach ($params as $param) {
            if (is_array($param))
            {
                $this->tbody .= '<tr';
                if(array_key_exists('tr', $param))
                {
                    if(is_array($param['tr']))
                    {
                        foreach($param['tr'] as $k => $v)
                        {
                            $this->tbody .= $k.'="'.$v.'" ';
                        }
                    }else{
                        $this->tbody .= ' '.$param['tr'];
                    }
                    unset($param['tr']);
                }
                $this->tbody .= '>';
                foreach($param as $name => $value){
                        $this->tbody .= '<td>'.$value.'</td>';
                }
                $this->tbody .= '</tr>';
            } else if(is_string($param)) {
                $this->tbody .= '<tr><td>' . $param . '</td></tr>';
            }
        }
        return $this;
    }
    public function BodyFromObj($objects, $orderOfFields)
    {
        $this->tbody = '';

        foreach ($objects as $object) {
            $this->tbody .= '<tr>';
            foreach($orderOfFields as $name){
                $this->tbody .= '<td>'.$object->$name.'</td>';
            }
            $this->tbody .= '</tr>';
        }
        return $this;
    }

    /**
     * Form and return table;
     * @return string
     */
    public function Form()
    {
        $this->output = '<table '.$this->id.$this->class.$this->dataAttr.'>'.$this->thead.$this->tbody.'</table>';

        return $this->output;
    }

    /**
     * @return mixed
     */
    public function getTableBody()
    {
        return $this->tbody;
    }
}