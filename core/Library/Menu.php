<?php
class Menu
{
    private static $menu = array(
                array(
                    'href' => "/workspace/Events",
                    'title' => "<span class=\"glyphicon glyphicon-blackboard\"></span>Events<span class=\"badge badge-danger\"></span>"
                ),
                array(
                    'href' => "/workspace/Candidates",
                    'title' => "<span class=\"glyphicon glyphicon-user\"></span>Candidates<span class=\"badge\"></span>"
                ),
                array(
                    'href' => "/workspace/Vacancies",
                    'title' => "<span class=\"glyphicon glyphicon-briefcase\"></span>Vacancies<span class=\"badge\"></span>"
                ),
                array(
                    'href' => "/workspace/Journal",
                    'title' => "<span class=\"glyphicon glyphicon-book\"></span>Journal<span class=\"badge\"></span>"
                ),
                array(
                    'href' => "/Logout",
                    'title' => "<span class=\"glyphicon glyphicon-log-out\"></span>LogOut"
                )
    );
    public static function setMenu(array $menu)
    {
        self::$menu = $menu;
    }
    public static function getMenu() : string
    {
        $output = '<div id="btn_menu_trigger" class="hamburger-button">'
                 .'<div id="nav-icon3">'
                 .'<span></span>'
                 .'<span></span>'
                 .'<span></span>'
                 .'<span></span>'
                 .'</div></div>'
                 .'<div id="menu" class="menu_container"><ul class="SideMenu">';

        foreach (self::$menu as $menuItem) {
            if(!Auth::IsLogged() && $menuItem['href'] == '/Logout') {
                continue;
            }
            $output .= '<li data-action="'.$menuItem['href'].'"><a>' . $menuItem['title'] . '</a></li>';
        }
        return $output . '</ul></div>';
    }
    public function isActiveItem($cat_id = false) : string
    {
        if($cat_id === false && Router::getUriSegment(0) === false){
            return 'class="active"';
        }
        if($cat_id == Router::getUriSegment(2)){
            return 'class="active"';
        }
    }
}