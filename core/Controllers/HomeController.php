<?php

/**
 * Created by PhpStorm.
 * User: Boris
 * Date: 16.12.2015
 * Time: 14:27
 */
class HomeController extends Controller
{
    public function __construct(Router $router)
    {
        parent::__construct($router);
    }
    public function actionIndex()
    {
        if(Auth::IsLogged()) {
            header('Location: /workspace');
            exit();
        }
        header('Location: /login');
    }
}