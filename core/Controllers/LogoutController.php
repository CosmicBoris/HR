<?php
class LogoutController extends Controller
{
    public function __construct(Router $router)
    {
        parent::__construct($router);
    }
	public function actionIndex()
	{
		setcookie(session_id(), "", time() - 3600);
		session_destroy();
		header("Location: /");
	}
}