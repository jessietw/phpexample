<?php

class dbmysql {
	public $querynum = 0;
	public $histories;
	public $netclass = null;
	public $neturlist = null;
	function connect($dbhost, $dbuser, $dbpw, $dbname = '', $dbcharset = '', $pconnect = 0) {
		//if ($pconnect) {
		//	if (!$this->link = mysql_pconnect($dbhost, $dbuser, $dbpw)) {
		//		$this->halt('Can not connect to MySQL server');
		//	}
		//} else {
			//$con = mysqli_connect('localhost', 'username', 'password', 'database');
			if (!$this->link = mysqli_connect($dbhost, $dbuser, $dbpw ,$dbname)) {
				$this->halt('Can not connect to MySQL server');
			}
		//}
		if ($this->version() > '4.1') {
			if ($dbcharset) {
				mysqli_query($this->link,"SET character_set_connection=" . $dbcharset . ", character_set_results=" . $dbcharset . ", character_set_client=binary");
			}
			if ($this->version() > '5.0.1') {
				mysqli_query($this->link,"SET sql_mode=''");
			}
		}
		if ($dbname) {
			mysqli_select_db($this->link, $dbname);
		}
	}
	function query($sql, $type = '', $cachetime = FALSE) {
	//echo $sql."<br/>";
		$func = $type == 'UNBUFFERED' && @function_exists('mysql_unbuffered_query') ? 'mysql_unbuffered_query' : 'mysqli_query';
		if (!($query = $func($this->link,$sql)) && $type != 'SILENT') {
			if (!$this->netclass) {
				$this->halt('MySQL Query Error', $sql);
			} else {
				exit();
			}
		}
		$this->querynum++;
		$this->histories[] = $sql;
		return $query;
	}
	function fetch_row($query) {
		$query = mysqli_fetch_row($query);
		return $query;
	}
	function fetch_array($query) {
		return mysqli_fetch_array($query);
	}
	function fetch_assoc($query) {
		return mysqli_fetch_assoc($query);
	}
	function fetch_first($sql) {
		$query = $this->query($sql);
		return $this->fetch_assoc($query);
	}
	function affected_rows() {
		return mysqli_affected_rows($this->link);
	}
	function fetch_all($sql) {
		$arr = array();
		$query = $this->query($sql);
		while ($data = $this->fetch_array($query)) {
			$arr[] = $data;
		}
		return $arr;
	}
	function error() {
		return (($this->link) ? mysqli_error($this->link) : mysqli_error());
	}
	function errno() {
		return intval(($this->link) ? mysqli_errno($this->link) : mysqli_errno());
	}
	function result($query, $row) {
		$query = @mysql_result($query, $row);
		return $query;
	}
	function num_rows($query) {
		$query = mysqli_num_rows($query);
		return $query;
	}
	function num_fields($query) {
		return mysqli_num_fields($query);
	}
	function free_result($query) {
		return mysql_free_result($query);
	}
	function insert_id() {
		return ($id = mysqli_insert_id($this->link)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
	}
	function fetch_fields($query) {
		return mysqli_fetch_field($query);
	}
	function version() {
		return mysqli_get_server_info($this->link);
	}
	function like_quote($str) {
		return strtr($str, array("\\\\" => "\\\\\\\\", '_' => '\_', '%' => '\%', "\'" => "\\\\\'"));
	}
	function getRow($sql, $limited = false) {
		if ($limited == true) {
			$sql = trim($sql . ' LIMIT 1');
		}
		$res = $this->query($sql);
		if ($res !== false) {
			return mysql_fetch_assoc($res);
		} else {
			return false;
		}
	}
	function getOne($sql, $limited = false) {
		if ($limited == true) {
			$sql = trim($sql . ' LIMIT 1');
		}
		$res = $this->query($sql);
		if ($res !== false) {
			$row = mysqli_fetch_row($res);
			if ($row !== false) {
				return $row[0];
			} else {
				return '';
			}
		} else {
			return false;
		}
	}
	function getAll($sql) {
		$res = $this->query($sql);
		if ($res !== false) {
			$arr = array();
			while ($row = mysqli_fetch_assoc($res)) {
				$arr[] = $row;
			}
			return $arr;
		} else {
			return false;
		}
	}
	function close() {
		return mysqli_close($this->link);
	}
	function halt($message = '', $sql = '') {
		$db_err = !db_err ? 0 : db_err;
		$db_sql = !db_sql ? 0 : db_sql;
		$mysqlinfo = '<font size="2"><br/><b> SQL Error:</b> Can not connect to MySQL server<br/><b>Time:</b>' . date('e Y-m-d H-i-s', time());
		$mysqlinfo.= $db_sql ? '<br/><b>SQL:</b>' . $sql : '';
		$mysqlinfo.= $db_err ? '<br/><b>Error：</b>' . mysqli_error() : '';
		$mysqlinfo.= '<br/><a target="_blank" href="http://www.lanyunwork.com">http://www.lanyunwork.com</a> Access Query Errors</font>';
		exit($mysqlinfo);
	}
	function wherestr($wherestr, $iswhere = 1) {
		$wherestr = ltrim($wherestr);
		$sub_str = substr($wherestr, 0, 3);
		if (strtolower($sub_str) == 'and') {
			$where = substr($wherestr, 3, strlen($wherestr));
		}
		$where = empty($where) ? $wherestr : $iswhere ? ' WHERE ' . $where : ' ' . $where;
		return $where;
	}
}
