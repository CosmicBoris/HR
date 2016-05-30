<?php
class Model
{
    protected $dbLink;   // link to database and DbHelper methods
    protected $_error;

    public function __construct()
    {
        $this->dbLink = DbHelper::GetDbLink();
    }
    public function DbMethod($action_name, $param = false)
    {
        return $this->dbLink->$action_name($param);
    }
    public function getDBError(){
        return $this->dbLink->getErrors();
    }
    public function RowsCount()
    {
        return $this->dbLink->_rowsCount;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->_error;
    }
}