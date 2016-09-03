<?php 
final class DbHelper {
	protected static $_dataBaselink = null;
	private $_db;
	private $_sql;
	private $_table;
	private $_errors;
	/**
	 * @return mixed
	 */
	private function __construct()
	{
		$this->_db = @new mysqli(Config::DB_HOST, Config::DB_USER, Config::DB_PASS, Config::DB_NAME);
		if($this->_db->connect_errno){
			trigger_error('Не має зєднання з базою MySQL: ('. $this->_db->connect_errno.  ') ' . $this->_db->connect_error);
			exit();
		}
		$this->_db->set_charset("utf-8");
		date_default_timezone_set('Europe/Kiev');

		if(defined('LOG_QUERY_AND_RESULT')) {
			$this->_hfile = fopen($_SERVER['DOCUMENT_ROOT'].'/'.Config::LOG_DIR.'SQL_LOG.txt', 'ab');
		}
	}
	/**This method allow only one connected to Db object to exists!
	 * @return DbHelper
     */
	public static function GetDbLink()
	{
		if(is_null(self::$_dataBaselink)){
			self::$_dataBaselink = new self;
		}
		return self::$_dataBaselink;
	}
	/**
	 * @param $data
	 * @param bool|false $mod, (we need '$var' in some cases)
	 * @return string
	 */
	public function GetSafeStr($data, $mod = false)
	{
		if(is_array($data)) {
			$temp_arr = array();
			foreach($data as $key => $value) {
				if( is_string($key) ) {
					if( is_string($value)){
						$temp_arr[] = '`'.$this->_db->real_escape_string($key).'`'
							.'.'
							.'`'.$this->_db->real_escape_string($value).'`';
					} elseif (is_array($value)){
						$k = $this->_db->real_escape_string($key);
						foreach($value as $item){
							$temp_arr[] = '`'.$k.'`'
								.'.'
								.'`'.$this->_db->real_escape_string($item).'`';
						}
					}
				} else if(is_string($value)) {
					if($mod){
						$temp_arr[] = "'".$this->_db->real_escape_string($value)."'";
					}else{
						$temp_arr[] = '`'.$this->_db->real_escape_string($value).'`';
					}
				} else {
					$temp_arr[] = $value;
				}
			}
			return implode(',', $temp_arr);
		}
		if(is_string($data)){
			if($mod){
				return "'".$this->_db->real_escape_string($data)."'";
			}else{
				return '`'.$this->_db->real_escape_string($data).'`';
			}
		}
		return $data;
	}
	/**
	 * @param string $table, (we are working with)
	 * @param string/array $fields, (that we select)
	 * @return $this, (now we get our Helper with select string)
	 */
	public function select($table, $fields) {
		if(is_array($table)){
			$this->_table = $this->GetSafeStr(current($table));
			$this->_sql = 'SELECT '.$this->GetSafeStr($fields).' FROM '.$this->GetSafeStr(key($table)).' AS '
				.$this->_table;
		} else {
			$this->_table = $this->GetSafeStr($table);
			$this->_sql = 'SELECT '.$this->GetSafeStr($fields).' FROM '.$this->_table;
		}

		return $this;
	}
	public function update($table, $fields) {
		$this->_sql = 'UPDATE '.$this->GetSafeStr($table).' SET ';
		foreach($fields as $key => $value)
		{
			if($value === null)
				continue;
			$this->_sql .= $this->GetSafeStr($key)
			. "=" .
			$this->GetSafeStr($value, true).',';
		}
		$this->_sql = substr($this->_sql, 0, -1).' ';

		return $this;
	}
	public function insert($table, $obj)
	{
        $this->_sql = 'INSERT INTO '.$this->GetSafeStr($table);
        if(is_object($obj)) {
            foreach ($obj as $key => $value )
                if(empty($value)) unset($obj->$key);

            $this->_sql .= '('.$this->GetSafeStr(array_keys( get_object_vars($obj))).')'
                .' VALUES('.$this->GetSafeStr(array_values(get_object_vars($obj)), true).')';
            return $this;
        }

		$this->_sql .= '('.$this->GetSafeStr(array_keys($obj)).')'
				.' VALUES('.$this->GetSafeStr(array_values($obj), true).')';
		return $this;
	}
	public function delete($table)
	{
		$this->_sql = 'DELETE FROM '.$this->GetSafeStr($table);

		return $this;
	}
	public function innerJoin($table, $fields, $tableToJoin = false)
	{
		return $this->Join("INNER", $table, $fields, $tableToJoin);
	}
    public function LeftJoin($table, $fields, $tableToJoin = false)
    {
        return $this->Join("LEFT", $table, $fields, $tableToJoin);
    }
    public function Join($type, $table, $fields, $tableToJoin = false)
    {
        if(is_array($table)){ // example ['user'=>'u'] user AS u
            $tableName = $this->GetSafeStr(current($table));
            $this->_sql .= ' '.$type.' JOIN '.$this->GetSafeStr(key($table)).' AS '
                .$tableName.' ON ';
        } else {
            $tableName = $this->GetSafeStr($table);
            $this->_sql .= ' '.$type.' JOIN '.$tableName.' ON ';
        }

        foreach($fields as $key => $value)
        {
            if(!$value) continue;
            if($tableToJoin)
                $this->_sql .= $this->GetSafeStr($tableToJoin).'.'.$this->GetSafeStr($key);
            else
                $this->_sql .= $this->_table.'.'.$this->GetSafeStr($key);

            $this->_sql .= "=";
            $this->_sql .= $tableName.'.'.$this->GetSafeStr($value);
        }
        return $this;
    }

	/**
	 * @param array $params
	 * @param string $comparison (=, LIKE, IS, etc...)
	 * @param string $condition (AND | OR)
	 * @return $this
	 */
	public function where(array $params, $comparison = '=', $condition = '')
	{
		$this->_sql .= ' WHERE ';
		if(count($params) > 1) {
			foreach ($params as $field => $value) {
				// we can specify relation `from`.`Param`
				if(strpos($field, '.') !== false){
					$keys = explode('.', $field);
					$this->_sql .= $this->GetSafeStr($keys[0])
						.'.'
						.$this->GetSafeStr($keys[1])
						.$comparison
						.$this->GetSafeStr($value, true).' '.$condition.' ';
				}else if (is_string($field)){
					$this->_sql .= $this->GetSafeStr($field).$comparison.$this->GetSafeStr($value, true).' '.$condition.' ';
				} else {
					$this->_sql .= $this->GetSafeStr($value, true).' '.$condition.' ';
				}
			}
			// remove last condition operator | WHERE id=5 AND name='Alisa' `AND`(remove)
			$this->_sql = substr($this->_sql, 0, -(strlen($condition)+1));
		} else {  // one key => value pair
			$keys = array_keys($params);
			// for complex cases (user.UserID = value, etc.)
			if(strpos($keys[0], '.') !== false){
				$keys = explode('.', $keys[0]);
				$this->_sql .= $this->GetSafeStr($keys[0])
					.'.'
					.$this->GetSafeStr($keys[1])
					.$comparison
					.$this->GetSafeStr(array_shift($params), true);
			} else {
				$this->_sql .= $this->GetSafeStr($keys[0])
					.$comparison
					.$this->GetSafeStr(array_shift($params), true);
			}
		}
		return $this;
	}
    public function limit($from, $count) {
        $this->_sql .= " LIMIT $from,$count ";
        return $this;
    }
	/**
	 * @return bool | mysqli_result
	 */
	public function RunQuery()
	{
		$result = $this->_db->query($this->_sql);

		if(isset($this->_hfile)) {
			$str = "\r\nDate: ".date("Y-m-d H:i:s")."\r\nSQL: \r\n".$this->_sql."\r\nResult: \r\n";
			fwrite($this->_hfile, $str);
			fwrite($this->_hfile, print_r($result, TRUE));
			if(!empty($this->_db->error)){
				fwrite($this->_hfile, "Errors: \r\n");
				fwrite($this->_hfile, print_r($this->_db->error_list, TRUE));
			}
		}

		$this->_sql = '';
		if($result === false) {
			$this->setErrors();
		}
		return $result;
	}
	public function showSql(){
		return $this->_sql;
	}
	public function autocommit($mode = true){
		if(!$mode){
			$this->_db->autocommit(FALSE);
		}
	}
	public function commit(){
		if ($this->_db->commit()) {
			return true;
		}
		$this->_errors = $this->_db->error;
		return false;
	}
	public function ExecuteSql($query)
	{
		$output = null;
		$this->_sql = $query;
		$result = $this->RunQuery();
		if ($result && !$this->_db->error) {
			if($result->num_rows > 1) {
				$output = array();
				while ($obj = $result-> fetch_assoc()) {
					$output[]= $obj;
				}
				$result->close();
				return $output;
			} else if($result->num_rows == 1) {
				if($output = $result->fetch_assoc()){
					$result->close();
					return $output;
				}
			} else {return 0;}
		}
		$this->_errors = $this->_db->error;
		return false;
	}
	public function lastInsertedId()
	{
		return $this->_db->insert_id;
	}
	
	function FindUserBy($key, $param)
	{
		$sql = "SELECT * FROM `user` WHERE `$key` = $param";
		if ($result = $this->_db->query($sql))
		{
			$obj = $result-> fetch_assoc();
			if($obj){
				$user = new User($obj);
				$result->close();
				return $user;
			}
		}
		return false;
	}
	function GetCandidate($id)
	{
		$candidate = new Candidate();

		$result = $this->select('candidates', array_keys(get_object_vars($candidate)))
			->innerJoin(['user_candidates'=>'uc'], ['id'=>'candidate_id'])
			->where(array('uc.candidate_id'=>$id, 'uc.user_id'=> Auth::GetUserID()), '=', 'AND')->RunQuery();

		if(empty($this->_errors)) {
			if($obj = $result->fetch_assoc()) {
				$candidate->Init($obj);
			}
		}
		return $candidate;
	}
	function DeleteUser($uid)
	{
		$sql = 'DELETE FROM `user` WHERE `id`='.$uid;
		return $this->_db->query($sql);
	}
	private function setErrors(){
		$this->_errors = $this->_db->error_list;
	}
	public function getErrors()
	{
		return $this->_errors;
	}
	public function __destruct() {
		if(!empty($this->_errors)) {
			$this->_errors['date'] = date("Y-m-d H:i:s");

            file_put_contents($_SERVER['DOCUMENT_ROOT'].Config::LOG_DIR.Config::ERROR_LOG,
                print_r($this->_errors, TRUE), FILE_APPEND | LOCK_EX);
		}
		$this->_db->close();
   }
}