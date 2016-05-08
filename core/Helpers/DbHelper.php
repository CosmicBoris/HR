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
	private function GetSafeStr($data, $mod = false)
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
		}else{
			$this->_table = $this->GetSafeStr($table);
			$this->_sql = 'SELECT '.$this->GetSafeStr($fields).' FROM '.$this->_table;
		}

		return $this;
	}
	public function update($table, $fields) {
		$this->_sql = 'UPDATE '.$this->GetSafeStr($table).' SET ';
		foreach($fields as $key => $value)
		{
			if(!$value){
				continue;
			}
			$this->_sql .= $this->GetSafeStr($key);
			$this->_sql .= "=";
			$this->_sql .= $this->GetSafeStr($value, true).',';
		}
		$this->_sql = substr($this->_sql, 0, -1).' ';

		return $this;
	}
	public function insert($table, $fields)
	{
		$this->_sql = 'INSERT INTO '.$this->GetSafeStr($table);
		$this->_sql .='('.$this->GetSafeStr(array_keys($fields)).')';
		$this->_sql .= ' VALUES('.$this->GetSafeStr(array_values($fields), true).')';
		return $this;
	}
	public function innerJoin($table, $fields, $tableToJoin = false)
	{
		if(is_array($table)){
			$tableName = $this->GetSafeStr(current($table));
			$this->_sql .= ' INNER JOIN '.$this->GetSafeStr(key($table)).' AS '
				.$tableName.' ON ';
		}else{
			$tableName = $this->GetSafeStr($table);
			$this->_sql .= ' INNER JOIN '.$tableName.' ON ';
		}

		foreach($fields as $key => $value)
		{
			if(!$value){
				continue;
			}
			if($tableToJoin){
				$this->_sql .= $this->GetSafeStr($tableToJoin).'.'.$this->GetSafeStr($key);
			}else{
				$this->_sql .= $this->_table.'.'.$this->GetSafeStr($key);
			}
			$this->_sql .= "=";
			$this->_sql .= $tableName.'.'.$this->GetSafeStr($value);
		}
		return $this;
	}
	/**
	 * @param $params
	 * @param string $comparison (=, LIKE, IS, etc...)
	 * @param string $condition (AND | OR)
	 * @return $this
	 */
	public function where($params, $comparison = '=', $condition = '')
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
				} else {
					$this->_sql .= $this->GetSafeStr($field).$comparison.$this->GetSafeStr($value, true).' '.$condition.' ';
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
	/**
	 * @return bool | mysqli_result
	 */
	public function RunQuery()
	{
		$result = $this->_db->query($this->_sql);
		$this->_sql = '';
		if($result === false) $this->setErrors();
		
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
		$result = $this->_db->query($this->_sql);
		if ($result && !$this->_db->error)
		{
			if($result->num_rows > 1){
				$output = array();
				while ($obj = $result-> fetch_assoc()) {
					$output[]= $obj;
				}
				$result->close();
				return $output;
			} else {
				if($output = $result->fetch_assoc()){
					$result->close();
					return $output;
				}
			}
		}
		$this->_errors = $this->_db->error;
		$result->close();
		return false;
	}
	public function lastInsertedId()
	{
		return $this->_db->insert_id;
	}
	
    function GetAllTA()
	{
        $regions = array();
		$sql = "SELECT `r`.`RegionID`, `r`.`RegionCode`, `r`.`RegionDescription` FROM `region` as `r`";
		if ($result = $this->_db->query($sql))
		{
			while ($obj = $result-> fetch_assoc()) // fetch_object("Region") 
			{
				$reg = new Region( (int)$obj["RegionID"], (int)$obj["RegionCode"], $obj["RegionDescription"]);
				$regions[] = $reg;  
			}
			$result->close();
		}
		return $regions;		    
	}
	function GetTA($id)
	{
		$sql = "SELECT * FROM region WHERE RegionID='$id'";
		if ($result = $this->_db->query($sql))
		{
			$obj = $result-> fetch_assoc();
			$reg = new Region( (int)$obj["RegionID"], (int)$obj["RegionCode"], $obj["RegionDescription"]);
			$result->close();
			return reg;
		}
    }
	function GetAllInspections()
	{
		$ins = array();
		$sql = "SELECT * FROM `inspection`";
		if ($result = $this->_db->query($sql))
		{
			while ($obj = $result-> fetch_assoc()) 
			{
				$inspection = new Inspection( (int)$obj["InspectionID"], (int)$obj["InspectionCode"], $obj["InspectionDescription"]);
				$ins[] = $inspection;  
			}

			$result->close();
		}
		return $ins;	
	}
	function GetInspectionsByRegion($id)
	{
		$ins = array();
		$sql = "SELECT * FROM `inspection` WHERE RegionID='$id'";
		if ($result = $this->_db->query($sql))
		{
			while ($obj = $result-> fetch_assoc()) 
			{
				$inspection = new Inspection( (int)$obj["InspectionID"], (int)$obj["InspectionCode"], $obj["InspectionDescription"]);
				$ins[] = $inspection;  
			}
			$result->close();
		}
		return $ins;	
	}
	function GetInspection($id)
	{
		$sql = "SELECT * FROM `inspection` WHERE InspectionID = '$id'";
		if ($result = $this->_db->query($sql))
		{
			while ($obj = $result-> fetch_assoc()) 
			{
				$inspection = new Inspection( (int)$obj["InspectionID"], (int)$obj["InspectionCode"], $obj["InspectionDescription"]);
			}
			$result->close();
		}
		return $inspection;	
	}
	function GetAllpermission()
	{
		$perm = array();
		$sql = "SELECT * FROM  `permission`";
		if ($result = $this->_db->query($sql))
		{
			while ($obj = $result-> fetch_assoc())
			{
				$permission = new Permission( (int)$obj["permissionID"], (int)$obj["permissionCode"], $obj["permissionDescription"]);
				$perm[] = $permission;  
			}
			$result->close();
		}
		return $perm;	
    }
	function GetPermission($id)
	{
		$sql = "SELECT * FROM  `permission` WHERE permissionID = '$id'";
        if ($result = $this->_db->query($sql))
		{
			$obj = $result-> fetch_assoc();
			if($obj !== false) {
				$permission = new Permission( (int)$obj["permissionID"], (int)$obj["permissionCode"], $obj["permissionDescription"]);
				$result->close();
				return $permission;
			}
				$result->close();
		}
	}
	function GetUsersCount($param)
	{
		$sql = 'SELECT `user`.`UserID` FROM `user`';
		$where = "";

		if(Filter::Check()) {
			$sql = 'SELECT `user`.`UserID` FROM `user` '
				  .'LEFT JOIN `nonauthorized` AS `n` ON `user`.`UserID` = `n`.`UserID`';
			$where = ' WHERE `n`.`UserID` IS NULL ';
		}
		if($param) {
			if($where == ""){
				$where .= " WHERE ";
			} else {
				$where .= " AND ";
			}
			$where .= '(`user`.`UserLogin` LIKE \'%'.$param.'%\' OR '
				.'`user`.`UserFullName` LIKE \'%'.$param.'%\' OR '
				.'`user`.`UserEmail` LIKE \'%'.$param.'%\')';
		}
		if($where != '') {$sql.=$where;}
		if ($result = $this->_db->query($sql)) {
			return $result->num_rows;
		}
		return false;
	}
	function AddUser($user, $per)
	{
        $this->_db->autocommit(FALSE);
		$sql = 'INSERT INTO ';
		$sql.='`user`(`UserLogin`, `UserPassword`, `UserFullName`, `UserEmail`, `InspectionID`)';
		$sql.=' VALUES(\''.$user->UserLogin.'\',\'' .$user->UserPassword.'\',\''.$user->UserFullName.'\',\''.$user->UserEmail.'\','.$user->InspectionID.')';

		if ($this->_db->query($sql) && !$this->_db->error)
		{
			$uid = $this->_db->insert_id;

			$sql = 'INSERT INTO `userpermission`(`UserID`, `permissionID`) VALUES('.$uid.','.$per.')';
			if (false !== ($this->_db->query($sql))) {
                $sql = 'INSERT INTO '
					.'`nonauthorized`(UserID, permissionID)'
					.' VALUES('.$uid.','.$per.')';
                if (false !== ($this->_db->query($sql))) {
                    if ($this->_db->commit()) {
                        return $uid;
                    }
                }
			}
		}
		$this->_errors = $this->_db->error;
		return false;
	}
	
	function FindUserBy($key, $param)
	{
		$sql = "SELECT * FROM `user` WHERE `$key` = $param";
		if ($result = $this->_db->query($sql))
		{
			$obj = $result-> fetch_assoc();
			if($obj){
				$user =new User($obj);
				$result->close();
				return $user;
			}
		}
		return false;
	}
	function IsAuthentic($id)
	{
		$sql = "SELECT * FROM  `nonauthorized` WHERE UserID = '$id'";

		if ($result = $this->_db->query($sql))
		{
			if($result->num_rows > 0){
				return false;
			}
		}
		return true;
	}
	function NonAthorizedCount($params = false)
	{
		$sql = "SELECT `nonauthorized`.`UserID` FROM `nonauthorized`";

		if($params){
			$sql = "SELECT `nonauthorized`.`UserID` FROM `nonauthorized` "
			."LEFT JOIN `user` ON `nonauthorized`.`UserID`=`user`.`UserID` "
			."WHERE (`user`.`UserLogin` LIKE '%$params%' OR "
					."`user`.`UserFullName` LIKE '%$params%' OR "
					."`user`.`UserEmail` LIKE '%$params%') ";
		}

		if ($result = $this->_db->query($sql))
		{
			return $result->num_rows;
		}
	}

	function GetNonAthorized($from, $searchStr = false)
	{
		$sql = 'SELECT `user`.`UserID`, `user`.`UserLogin`, `user`.`UserFullName`, `user`.`UserEmail`,'
				.'`region`.`RegionDescription`, '
				.'`inspection`.`InspectionDescription`, '
				.'`permission`.`permissionDescription`, `permission`.`permissionID` '
				.'FROM `nonauthorized` '
				.'LEFT JOIN `user` ON `nonauthorized`.`UserID`=`user`.`UserID` '
				.'LEFT JOIN `permission` ON `nonauthorized`.`permissionID`=`permission`.`permissionID` '
				.'LEFT JOIN `inspection` ON `user`.`InspectionID`=`inspection`.`InspectionID` '
				.'LEFT JOIN `region` ON `inspection`.`RegionID`=`region`.`RegionID` ';
		if($searchStr){
			$sql .= " WHERE (`user`.`UserLogin` LIKE '%$searchStr%' OR "
					."`user`.`UserFullName` LIKE '%$searchStr%' OR "
					."`user`.`UserEmail` LIKE '%$searchStr%') ";
		}
		$sql .="LIMIT $from,".paginationHelper::$elementsPerPage;
		$users = array();

		if ($result = $this->_db->query($sql))
		{
			while ($obj = $result-> fetch_assoc())
			{
				$user =new User($obj);
				$user->region = $obj["RegionDescription"];
				$user->inspection = $obj["InspectionDescription"];
				$user->permission = $obj["permissionDescription"];
				$user->permissionID = $obj["permissionID"];
				$users[] = $user;  
			}
			$result->close();
		} else {
			$this->setErrors();
		}
		return $users;
	}
	function GetUsers($from, $params = false)
	{
		$sql = 'SELECT `user`.`UserID`, `user`.`UserLogin`, `user`.`UserFullName`, `user`.`UserEmail`,'
			.'`region`.`RegionDescription`, '
			.'`inspection`.`InspectionDescription`, '
			.'`permission`.`permissionDescription`, `permission`.`permissionID` '
			.'FROM `user` '
			.'LEFT JOIN `userpermission` ON `user`.`UserID`=`userpermission`.`UserID` '
			.'LEFT JOIN `permission` ON `userpermission`.`permissionID`=`permission`.`permissionID` '
			.'LEFT JOIN `inspection` ON `user`.`InspectionID`=`inspection`.`InspectionID` '
			.'LEFT JOIN `region` ON `inspection`.`RegionID`=`region`.`RegionID` ';
		$where = " WHERE ";
		$addWhere = false;
			if(is_array($params)){
				foreach($params as $param) {
					switch($param){
						case Filter::ONLY_AUTHORIZED:
							$sql .= 'LEFT JOIN `nonauthorized` AS n ON `user`.`UserID` = `n`.`UserID` ';
							if($addWhere) $where .= ' AND ';
							$where .= '`n`.`UserID` IS NULL ';
							$addWhere = true;
						break;
						default:
							if($addWhere) $where .= ' AND ';
							$where .= '(`user`.`UserLogin` LIKE \'%'.$param.'%\' OR '
								.'`user`.`UserFullName` LIKE \'%'.$param.'%\' OR '
								.'`user`.`UserEmail` LIKE \'%'.$param.'%\')';
							$addWhere = true;
							break;
					}
				}
			}else{
				switch($params){
					case Filter::ONLY_AUTHORIZED:
						$sql .= 'LEFT JOIN `nonauthorized` AS n ON `user`.`UserID` = `n`.`UserID` ';
						if($addWhere) $where .= ' AND ';
						$where .= '`n`.`UserID` IS NULL ';
						$addWhere = true;
						break;
					default:
						$sql .= $params;
						break;
				}
			}
		if($addWhere) $sql .= $where;
		$sql .= ' LIMIT ';
        $sql .= $from;
        $sql .= " ,";
        $sql .= paginationHelper::$elementsPerPage;
		$users = array();
		if ($result = $this->_db->query($sql))
		{
			$this->_rowsCount = $result->num_rows;
			while ($obj = $result-> fetch_assoc())
			{
				$user = new User($obj);
				$user->region = $obj["RegionDescription"];
				$user->inspection = $obj["InspectionDescription"];
				$user->permission = $obj["permissionDescription"];
				$user->permissionID = $obj["permissionID"];
				$users[] = $user;
			}
			$result->close();
		}
		return $users;
	}
	function GetUser($uid)
	{
		$sql = 'SELECT `u`.`UserID`,`UserLogin`,`UserFullName`,`UserEmail`,`r`.`RegionID`,'
			.'`RegionDescription`,`i`.`InspectionID`,`InspectionDescription`, `permissionDescription`,`p`.`permissionID`'
			.'FROM `user` AS `u` INNER JOIN `userpermission` ON `u`.`UserID`=`userpermission`.`UserID`'
			.'INNER JOIN `permission` AS `p` ON `userpermission`.`permissionID`=`p`.`permissionID`'
			.'INNER JOIN `inspection` AS `i` ON `u`.`InspectionID`=`i`.`InspectionID`'
			.'INNER JOIN `region` AS `r` ON `i`.`RegionID`=`r`.`RegionID`'
			.'WHERE `u`.`UserID`= '.$uid;
		if($result = $this->_db->query($sql))
		{
			if($obj = $result->fetch_assoc())
			{
				$user = new User($obj);
				$user->RegionDescription = $obj['RegionDescription'];
				$user->InspectionDescription = $obj['InspectionDescription'];
				$user->permissionDescription = $obj['permissionDescription'];
				return $user;
			}
		}
		return false;
	}
	function Athorize($uid)
	{
		$sql = "DELETE FROM `nonauthorized` WHERE `UserID`='$uid'";
		if ($this->_db->query($sql) === TRUE){
    		return true;
        }
        return false;
	}

	function GetEntitysInfo($from, $param = false)
	{
		$sql = 'SELECT `e`.`Entity_EDRPOU`, `e`.`Entity_Name`,`ceo`.`FullName`, `ea`.`Address` '
			.'FROM `entity` AS `e`'
			.' INNER JOIN `entity_address` AS `ea` ON `e`.`Entity_pAddress`=`ea`.`Address_Id`'
            .' INNER JOIN `entityofficial` AS `ceo` ON `e`.`Entity_DirectorId`=`ceo`.`Id`'
			.' INNER JOIN `entity_user` AS `eu` ON `e`.`Entity_Id`=`eu`.`EntityId`'
            .' WHERE `eu`.`UserId`='. Auth::GetUserID();


		if($param){
			$param = $this->GetSafeStr('%'.$param.'%', true);
			$sql .=" AND `e`.`Entity_EDRPOU` LIKE $param";
		}

		$sql .=' ORDER BY `e`.`Entity_DateModified`'
			.' LIMIT '.$from.','.paginationHelper::$elementsPerPage;

		$co = array();
		$c = array();

		if ($result = $this->_db->query($sql))
		{
			while ($obj = $result->fetch_assoc()) {
				$c['No'] = (int)++$from;
				$c['Entity_EDRPOU'] = $obj['Entity_EDRPOU'];
				$c['Name'] = $obj['Entity_Name'];
				$c['CEO'] = $obj['FullName'];
				$c['Addr'] = $obj['Address'];
				$co[] = $c;
			}
			$result->close();
			return $co;
		}
		return array("error" => $this->_db->error_list);
	}
	function GetEntityInfoFull($id)
	{
		 $sql = "SELECT `e`.`Entity_Id`,`e`.`Entity_EDRPOU`, `e`.`Entity_DRFO`, `e`.`Entity_DateModified`, `e`.`Entity_Name`, `e`
			.`Entity_FullName`, `e`.`Entity_DirectorId`, `e`.`Entity_Phone`, `e`.`Entity_Fax`, `e`.`Entity_EMail`, `e`.`Entity_KVED`, `e`.`Entity_KOPFG`, `e`.`Entity_Status`, `e`.`Entity_UserId`, `e`.`Entity_lAddress`, `e`.`Entity_pAddress`, `e`.`Entity_Shtat`,
			`d`.`Id`,`d`.`INN`,`d`.`FullName`,`d`.`BirthDate`,`d`.`HireDate`,`d`.`JobTitle`,`d`.`Nationality`,`d`
			.`PlaceOfResidence`,`d`.`PlaceOfBirth`,`d`.`Passport`,
			`af`.`Address_Id` AS 'fAddress[Address_Id]',`af`.`Index` AS 'fAddress[Index]',`af`.`Address` AS 'fAddress[Address]',`af`.`KOATUU1` AS 'fAddress[KOATUU1]',`af`.`KOATUU2` AS 'fAddress[KOATUU2]',`af`.`KOATUU3` AS 'fAddress[KOATUU3]',`af`.`KOATUU4` AS 'fAddress[KOATUU4]',
			`au`.`Address_Id` AS 'uAddress[Address_Id]',`au`.`Index` AS 'uAddress[Index]',`au`.`Address` AS
			'uAddress[Address]',`au`.`KOATUU1` AS 'uAddress[KOATUU1]',`au`.`KOATUU2` AS 'uAddress[KOATUU2]',`au`.`KOATUU3` AS 'uAddress[KOATUU3]',`au`.`KOATUU4` AS 'uAddress[KOATUU4]'
			FROM `entity` AS `e`
			INNER JOIN `entityofficial` AS `d` ON `e`.`Entity_DirectorId`=`d`.`Id`
			INNER JOIN `entity_address` AS `af` ON `e`.`Entity_pAddress`=`af`.`Address_Id`
			INNER JOIN `entity_address` AS `au` ON `e`.`Entity_lAddress`=`au`.`Address_Id`
			WHERE `e`.`Entity_EDRPOU`=$id";
		if($result = $this->_db->query($sql)){
			return $result->fetch_assoc();
		}
		$this->setErrors();
		return false;
	}
	function GetEntityOfficial($id)
	{
		$official = new Entityofficial();

		$result = $this->select('entityofficial', array_keys(get_object_vars($official)))
			->where(array('Id'=>$id))->RunQuery();

		if(empty($this->_errors))
		{
			$official->Init($result->fetch_assoc());
		}
		return $official;
	}

    function AddEntity($data, &$arg)
    {
        $this->_db->autocommit(FALSE);

		$data['Entityofficial']['BirthDate'] = date("Y-m-d", strtotime($data['Entityofficial']['BirthDate']));
		$data['Entityofficial']['HireDate'] = date("Y-m-d", strtotime($data['Entityofficial']['HireDate']));

        $sql = 'INSERT INTO ';
		$sql .= '`entityofficial`('.$this->GetSafeStr(array_keys($data['Entityofficial'])).')';
		$sql .= 'VALUES('.$this->GetSafeStr(array_values($data['Entityofficial']), true).')';
		$arg['first query'] = $sql;
        if (false !== ($this->_db->query($sql)))
        {
            $data['Entity']['Entity_DirectorId'] = $this->_db->insert_id;
			$data['fAddress']['Status'] = 2;
			$string_adr = $this->GetSafeStr(array_keys($data['fAddress']));
            $sql = 'INSERT INTO ';
			$sql .= '`Entity_address`('.$string_adr.')';
			$sql .= 'VALUES('.$this->GetSafeStr(array_values($data['fAddress']), true).')';

			$arg['second query'] = $sql;
            if (false !== ($this->_db->query($sql))){
				$data['Entity']['Entity_pAddress'] = $this->_db->insert_id;
				$data['uAddress']['Status'] = 1;
				$sql = 'INSERT INTO ';
				$sql .= '`Entity_address`('.$string_adr.')';
				$sql .= 'VALUES('.$this->GetSafeStr(array_values($data['uAddress']), true).')';
				$arg['third query'] = $sql;
				if (false !== ($this->_db->query($sql))) {
					$data['Entity']['Entity_lAddress'] = $this->_db->insert_id;
					$data['Entity']['Entity_DRFO'] = array_key_exists('Entity_DRFO', $data['Entity']) ? 1 : 0;
					$data['Entity']['Entity_UserId'] = Auth::GetUserID();
					$data['Entity']['Entity_DateModified'] = date("Y-m-d H:i:s");

					$sql = "INSERT INTO ";
					$sql .= '`Entity`('.$this->GetSafeStr(array_keys($data['Entity'])).')';
					$sql .= 'VALUES('.$this->GetSafeStr(array_values($data['Entity']),true).')';
					$arg['forth query'] = $sql;
					if (false !== ($this->_db->query($sql))) {
						$eid = $this->_db->insert_id;
						$sql = "INSERT INTO ";
						$sql .= "`Entity_user`(EntityId, UserId)";
						$sql .= 'VALUES('.$eid.','.Auth::GetUserID().')';
						$arg['fifth query'] = $sql;
						if (false !== ($this->_db->query($sql))) {
							if ($this->_db->commit()) {
								return $eid;
							}
						}
					}
				}
            }
        }
		$this->_errors = $this->_db->error_list;
        return false;
    }
	function UpdateEntity($id, $data, &$arg)
	{
		if($this->dbLink->update('entityofficial', $data['Entityofficial'])->where(array('UserID'=>Url::GetParam()))->RunQuery())

		$this->_db->autocommit(FALSE);

		$data['Entityofficial']['BirthDate'] = date("Y-m-d", strtotime($data['Entityofficial']['BirthDate']));
		$data['Entityofficial']['HireDate'] = date("Y-m-d", strtotime($data['Entityofficial']['HireDate']));

		$sql = 'UPDATE ';
		$sql .= '`entityofficial`('.$this->GetSafeStr(array_keys($data['Entityofficial'])).')';
		$sql .= 'VALUES('.$this->GetSafeStr(array_values($data['Entityofficial']), true).')';
		$arg['first query'] = $sql;
		if (false !== ($this->_db->query($sql)))
		{
			$data['Entity']['Entity_DirectorId'] = $this->_db->insert_id;
			$data['fAddress']['Status'] = 2;
			$string_adr = $this->GetSafeStr(array_keys($data['fAddress']));
			$sql = 'INSERT INTO ';
			$sql .= '`Entity_address`('.$string_adr.')';
			$sql .= 'VALUES('.$this->GetSafeStr(array_values($data['fAddress']), true).')';

			$arg['second query'] = $sql;
			if (false !== ($this->_db->query($sql))){
				$data['Entity']['Entity_pAddress'] = $this->_db->insert_id;
				$data['uAddress']['Status'] = 1;
				$sql = 'INSERT INTO ';
				$sql .= '`Entity_address`('.$string_adr.')';
				$sql .= 'VALUES('.$this->GetSafeStr(array_values($data['uAddress']), true).')';
				$arg['third query'] = $sql;
				if (false !== ($this->_db->query($sql))) {
					$data['Entity']['Entity_lAddress'] = $this->_db->insert_id;
					$data['Entity']['Entity_DRFO'] = array_key_exists('Entity_DRFO', $data['Entity']) ? 1 : 0;
					$data['Entity']['Entity_UserId'] = Auth::GetUserID();
					$data['Entity']['Entity_DateModified'] = date("Y-m-d H:i:s");

					$sql = "INSERT INTO ";
					$sql .= '`Entity`('.$this->GetSafeStr(array_keys($data['Entity'])).')';
					$sql .= 'VALUES('.$this->GetSafeStr(array_values($data['Entity']),true).')';
					$arg['forth query'] = $sql;
					if (false !== ($this->_db->query($sql))) {
						$eid = $this->_db->insert_id;
						$sql = "INSERT INTO ";
						$sql .= "`Entity_user`(EntityId, UserId)";
						$sql .= 'VALUES('.$eid.','.Auth::GetUserID().')';
						$arg['fifth query'] = $sql;
						if (false !== ($this->_db->query($sql))) {
							if ($this->_db->commit()) {
								return $eid;
							}
						}
					}
				}
			}
		}
		$this->_errors = $this->_db->error_list;
		return false;
	}

	function DeleteReq($uid)
	{
		$sql = 'DELETE FROM `user` WHERE `UserID`='.$uid;
	
		if ($result = $this->_db->query($sql)) {
    		return true;
		}
		return false;
	}
	function DeleteUser($uid)
	{
		$this->_db->autocommit(FALSE);
		$sql = 'INSERT INTO';
		$sql .=' `deleteduser`(`UserID`, `UserLogin`, `UserPassword`, `UserFullName`, `UserEmail`, `InspectionID`, `permissionID`)';
		$sql .=' SELECT `u`.`UserID`, `u`.`UserLogin`, `u`.`UserPassword`,';
		$sql .=' `u`.`UserFullName`, `u`.`UserEmail`, `u`.`InspectionID`,`p`.`permissionID`';
		$sql .=' FROM `user` AS `u`';
		$sql .=' INNER JOIN `userpermission` AS `p` ON `u`.`UserID`=`p`.`UserID`';
		$sql .=' WHERE `u`.`UserID`='. $uid;

		if (false !== ($this->_db->query($sql)))
		{
			$sql = 'DELETE FROM `user` WHERE `UserID`='.$uid;

			if (false !== ($this->_db->query($sql))) {
				if ($this->_db->commit()) {
					return true;
				}
			}
		}
		return $this->_db->error_list.' '.$this->_db->errno;
	}
	private function setErrors(){
		$this->_errors = $this->_db->error_list;
	}
	public function getErrors()
	{
		return $this->_errors;
	}
	public function __destruct() {
		if(!empty($this->_errors)){
			$this->_errors['date'] = date("Y-m-d H:i:s");
			if(file_exists(Config::LOG_DIR.Config::ERROR_LOG)){
				file_put_contents(Config::LOG_DIR.Config::ERROR_LOG,
					serialize($this->_errors), FILE_APPEND |
					LOCK_EX);
			} else {
				file_put_contents(Config::LOG_DIR.Config::ERROR_LOG,
					serialize($this->_errors), LOCK_EX);
			}
		}
		$this->_db->close();
   }
}