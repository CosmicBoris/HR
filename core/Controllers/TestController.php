<?php
class TestController extends Controller
{
	function __construct()
	{
		parent::__construct();
		$this->_model = new testModel();
	}

	public function actionIndex()
	{
		$this->_view->partialRender("data-tables");
	}
	public function actionFillUsers(){
		$this->_model->FillUsers();
	}
	public function actionFillEntitys(){
		$this->_model->FillEntitys();
	}
	public function actionFillInspections(){

	}
}