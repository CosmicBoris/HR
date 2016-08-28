<?php

/**
 * Created by PhpStorm.
 * User: Boris
 * Date: 28.01.2016
 * Time: 22:49
 */
class paginationHelper
{
    static $nPages;
    static $elementsPerPage = 15;
    private static $currentPage = 1;

    static function SetCountPerPage(int $count)
    {
        self::$elementsPerPage = $count;
    }
    static function Form($count, $destination) : string
    {
        if($count <= self::$elementsPerPage) {
            return false;
        }
        self::$nPages = ceil($count/self::$elementsPerPage);

        $output = '<div class="pull-right">';
        $output .= '<ul class="pagination pagination-sm">';

        if(self::$currentPage == 1){
            $output .= '<li class="disabled">';
        }else{
            $output .= '<li class="pageAct" data-action="'.$destination.'">';
        }
        $output .='<span aria-hidden="true">&laquo;</span></li>';

        $limit = (self::$currentPage+3) < self::$nPages ? self::$currentPage+3 : self::$nPages;
        for($i = (self::$currentPage > 2) ? self::$currentPage-2 : 1; $i <= $limit; $i++):
            if($i == self::$currentPage) {
                $output .= '<li class="active"><span>' . $i . ' <span class="sr-only">(0)</span></span></li>';
                continue;
            }
            $output .= '<li class="pageAct" data-action="'.$destination.'?page='. $i .'"><span>'.$i.'</span></li>';
        endfor;
        if (self::$currentPage == self::$nPages) {
            $output .= '<li class="disabled">';
        } else {
            $output .= '<li class="pageAct" data-action="'.$destination.'?page='.(self::$nPages).'">';
        }

        $output .= '<span aria-hidden="true">&raquo;</span></li></ul></div>';

        return $output;
    }
    static function Limit() : int
    {
        return (self::$currentPage * self::$elementsPerPage) - self::$elementsPerPage;
    }
    static function LimitString() : string
    {
        return self::Limit().','.self::$elementsPerPage;
    }

    /**
     * @param integer $newPage
     * Call this from controller constructor (int)$_GET['page']
     */
    public static function setCurrentPage($newPage)
    {
        self::$currentPage = (is_numeric($newPage) && $newPage > 0) ? $newPage : 1;
    }
    public static function getCurrentPage() : int
    {
        return self::$currentPage;
    }
}