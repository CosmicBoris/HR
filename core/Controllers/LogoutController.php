<?php
class LogoutController{
	public function actionIndex()
	{
		setcookie(session_id(), "", time() - 3600);
		session_destroy();
		header("Location: /");
	}
}