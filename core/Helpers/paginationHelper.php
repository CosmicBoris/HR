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
    private static $currentPage = 0;

    static function SetCountPerPage($count)
    {
        self::$elementsPerPage = $count;
    }
    static function Form($count, $destination)
    {
        $page = Url::GetParam();
        if(self::$currentPage == 0 && $page > 0){
            self::$currentPage = $page;
        }

        if($count <= self::$elementsPerPage) {
            return;
        }
        self::$nPages = ceil($count/self::$elementsPerPage);

        $output = '<div class="pull-right">';
        $output .= '<ul class="pagination pagination-sm">';

        if(self::$currentPage == 0){
            $output .= '<li class="disabled">';
        }else{
            $output .= '<li class="pageAct" data-action="'.$destination.'/0">';
        }
        $output .='<span aria-hidden="true">&laquo;</span></li>';

        $limit = (self::$currentPage+3) < self::$nPages ? self::$currentPage+3 : self::$nPages;
        for($i = (self::$currentPage > 2) ? self::$currentPage-2 : 0; $i < $limit; $i++ ):
            if($i == self::$currentPage) {
                $output .= '<li class="active"><span>' . ($i + 1) . ' <span class="sr-only">(0)</span></span></li>';
                continue;
        }
            $output .= '<li class="pageAct" data-action="'.$destination.'/'. $i .'"><span>'.($i + 1).'</span></li>';
        endfor;
        if(self::$currentPage == self::$nPages - 1){
            $output .= '<li class="disabled">';
        }else{
            $output .= '<li class="pageAct" data-action="'.$destination.'/'.(self::$nPages - 1).'">';
        }

        $output .= '<a aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li></ul>';
        $output .= '</div>';

        return $output;
    }
    static function Limit($cPage){
        return $cPage * self::$elementsPerPage;
    }

    /**
     * @param mixed $currentPage
     */
    public static function setCurrentPage($currentPage)
    {
        self::$currentPage = ($currentPage === false) ? 0 : $currentPage;
    }
}