<?php

/**
 * Created by PhpStorm.
 * User: boris
 * Date: 15.05.2016
 * Time: 23:27
 */
class erroralertHelper
{
    public static function Form($errorType = false, $allErrors = false)
    {
        $output = "";
        $output .= '<div class="alert alert-danger" role="alert">';
        $output .= '<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>';
        $output .= '<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span>';
            // TODO check if errorType if array;
        if($allErrors):
            foreach(($errorType !== false) ? $this->_errors[$errorType] : $this->_errors as $error):
                if(is_array($error)){
                    foreach($error as $item => $value):
                        $output .= "<span>$item : $value</span>";
                    endforeach;
                }
                else {
                    $output.="<span>$error</span>";
                }
            endforeach;
        else:
            $output.='<span>'.$this->_errors[$errorType][0].'</span></span><br>';
        endif;
        $output.="</div>";
        return $output;
    }
}