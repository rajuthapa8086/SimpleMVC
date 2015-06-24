<?php

/**
 * Class active_record
 * Inherits db
 * @see ./db.php for class db
 */
 

class active_record extends db {

/**
 * CLASS PROPERTIES AND METHODS
 */
	protected function __construct() {
		parent::__construct();		
	}
	
/**
 * STATIC PROPERTIES AND METHODS
 */
	private static $settings;
	protected static $_instance;
	public static $tbl_pfx;
	
	private static function make_instance() {
		if (self::$settings['class'] != "") {
			if (!is_null(self::$_instance)) {
				if (!(self::$_instance instanceof self::$settings['class'])) {
					self::$_instance = new self::$settings['class']();
				} else {
					self::$_instance = self::$_instance;				
				}
			} else {
				self::$_instance = new self::$settings['class']();			
			}
		}
	}
	
	public static function instance() {
		if (self::$settings['class'] != "") {
			self::make_instance();
			return self::$_instance;
		}
		return null;
	}
	
	protected static function _init($settings) {
		if (!utils::key_exists(array("table", "class"), $settings)) {
			die("Active Record: Invalid Configuration");
		}
		self::$tbl_pfx = parent::$tbl_pfx;
		self::$settings = $settings;
		self::make_instance();
	}
	
	private static function get_fields($table = "") {
		$sql = sprintf(
			"DESCRIBE %s",
			$table == "" ?
			self::filter_table(self::$settings['table']):
			self::filter_table($table)
		);
		$arr = parent::rows($sql);
		$return = array();
		foreach($arr as $a) {
			$return[] = $a['Field'];
		}
		return $return;
	}
		
	private static function filter_as($val) {
		$val = str_ireplace(" as ", " AS ", $val);
		$explode = explode(" AS ", $val);
		if (count($explode) == 2) {
			return array(
				trim($explode[0]),
				trim($explode[1])
			);
		}
		return array($val, "");
	}
	
	private static function filter_field($val) {
		$val = trim($val);
		if (strtoupper(substr($val, 0, 2)) == "F:") {
			return substr($val, 2);
		}
		$val = str_replace("`", "", $val);
		$arr = self::filter_as($val);
		$return = "";
		$explode = explode(".", $arr[0]);
		foreach($explode as $item) {
			$return .= sprintf("`%s`.", $item);
		}
		$return = substr($return, 0, -1);
		$return .= ($arr[1] != "") ? sprintf(" AS `%s`", $arr[1]) : "";
		return $return;
	}
	
	private static function filter_table($val) {
		$val = str_replace("`", "", $val);
		$arr = self::filter_as($val);
		$return = sprintf("`%s%s`", parent::$tbl_pfx, $arr[0]);
		$return .= ($arr[1] != "") ? sprintf(" AS `%s`", $arr[1]) : "";
		return $return;
	}
	
	private static function where_keys($val) {
		$val = trim($val);
		$explode = explode("|", $val);
		$count = count($explode);
		$return = array();
		if ($count == 1) {
			$return[0] = trim($explode[0]);
			$return[1] = "=";
			$return[2] = "AND";
			$return[3] = "FF";
		}
		if ($count == 2) {
			$return[0] = trim($explode[0]);
			$return[1] = trim($explode[1]);
			$return[2] = "AND";
			$return[3] = "FF";
		}
		if ($count == 3) {
			$return[0] = trim($explode[0]);
			$return[1] = trim($explode[1]);
			$return[2] = trim($explode[2]);
			$return[3] = "FF";
		}
		if ($count > 3) {
			$return[0] = trim($explode[0]);
			$return[1] = trim($explode[1]);
			$return[2] = trim($explode[2]);
			$return[3] = strtoupper(trim($explode[3]));
		}
		return $return;
	}
	
	private static function filter_where($key, $val, $last = false) {
		$return = "";
		$key = trim($key);
		$keys = self::where_keys($key);
		$count = 0;
		$return .= sprintf(
			"%s",
			substr($keys[3], 0, 1) == "F" ?
			self::filter_field($keys[0]) :
			sprintf("'%s'", $keys[0])
		);
		$return .= sprintf(
			" %s ",
			$keys[1]
		);
		if (is_array($val)) {
			$count = count($val);
			if (!empty($val)) {
				if ($count == 2) {
					$return .= sprintf(
						"'%s' AND '%s'",
						parent::esc_str($val[0]),
						parent::esc_str($val[1])
					);
				} else if ($count > 2) {
					$return .= sprintf(
						"'%s' %s '%s'",
						strtoupper(trim($val[1])),
						parent::esc_str($val[0]),
						parent::esc_str($val[2])
					);
				} else {
					$return .= " ";
				}
			} else {
				$return .= " ";
			}
		} else {
			$val = trim($val);
			$return .= sprintf(
				"%s",
				substr($keys[3], -1, 1) == "T" ?
				self::filter_field($val) :
				sprintf("'%s'", parent::esc_str($val))
			);
		}
		$return .= sprintf(
			"%s",
			!$last ? sprintf(" %s ", $keys[2]) : ""
		);
		return $return;
	}
	
	private static function where_str($where = array()) {
		$str = "";
		if (is_array($where) && !empty($where)) {
			$str = " WHERE ";
			$count = count($where);
			$i = 1;
			foreach($where as $key => $value) {
				$str .= self::filter_where($key, $value, ($count == $i));
				$i++;
			}
		}
		return $str;
	}
	
	private static function order_str($sort, $order = "DESC") {
		$order = strtoupper($order);
		$order = $order == "DESC" ? "DESC" : "ASC";
		$str = "";
		$vsort = array();
		$vfields = self::get_fields();
		if (is_array($sort) && !empty($sort)) {
			foreach($sort as $s) {
				if (!in_array($s, $vfields)) {
					$vsort[] = $vfields[0];
					break(1);
				} else {
					$vsort[] = $s;
				}
			}
			$sort_str = "";
			foreach($vsort as $vs) {
				$sort_str .= sprintf("%s, ", self::filter_field($vs));
			}
			$sort_str = substr($sort_str, 0, -2);
			$str = sprintf(" ORDER BY %s %s", $sort_str, $order);
		}
		return $str;
	}

	private static function limit_str($offset, $limit) {
		$offset = (int) $offset;
		$limit = (int) $limit;
		if ($limit != 0) {
			$str = sprintf(
				" LIMIT %d, %d",
				$offset,
				$limit
			);
		} else {
			$str = "";
		}
		return $str;
	}
	
	public static function get_total($where = array()) {
		$sql = "SELECT COUNT(*) AS `tr_%s` FROM %s%s;";
		$sql = sprintf(
			$sql,
			self::$settings['table'],
			self::filter_table(self::$settings['table']),
			self::where_str($where)			
		);
		$row = 	parent::row($sql);
		debug::set('sql', self::$settings['class'] .'::'. __FUNCTION__, $sql);
		return $row['tr_' . self::$settings['table']];
	}
	
	public static function single($fields = array("*"), $where = array()) {
		$sql = "SELECT %s FROM %s%s;";
		$fields_str = "";
		if (empty($fields) || self::$settings['class'] != "") {
			$fields_str = "*";
		} else {
			if (str_replace("`", "", $fields[0]) == "*") {
				$fields_str = "*";
			} else {
				foreach($fields as $field) {
					if ($field != "*") {
						$fields_str .= sprintf("%s, ", self::filter_field($field));
					}
				}
				$fields_str = substr($fields_str, 0, -2);
			}
		}
		$sql = sprintf(
			$sql,
			$fields_str,
			self::filter_table(self::$settings['table']),
			self::where_str($where)
		);
		debug::set('sql', self::$settings['class'] .'::'. __FUNCTION__, $sql);
		if (self::$settings['class'] != "") {
			if (parent::row($sql) == null) {
				return null;
			} else {
				utils::array_to_object(parent::row($sql), self::$_instance);
				return self::$_instance;
			}
		}
		debug::set('sql', self::$settings['class'] .'::'. __FUNCTION__, $sql);
		return parent::row($sql);
	}
	
	public static function multiple($fields = array("*"), $where = array(), $sort = array(), $order = "DESC", $offset = 0, $limit = 0) {
		$return = array();
		$sql = "SELECT %s FROM %s%s%s%s;";
		$fields_str = "";
		if (empty($fields) || self::$settings['class'] != "") {
			$fields_str = "*";
		} else {
			if (str_replace("`", "", $fields[0]) == "*") {
				$fields_str = "*";
			} else {
				foreach($fields as $field) {
					if ($field != "*") {
						$fields_str .= sprintf("%s, ", self::filter_field($field));
					}
				}
				$fields_str = substr($fields_str, 0, -2);
			}
		}
		$sql = sprintf(
			$sql,
			$fields_str,
			self::filter_table(self::$settings['table']),
			self::where_str($where),
			self::order_str($sort, $order),
			self::limit_str($offset, $limit)
		);
		debug::set('sql', self::$settings['class'] .'::'. __FUNCTION__, $sql);
		$rows = parent::rows($sql);
		foreach($rows as $row) {
			$return[] = $row;
		}
		if (self::$settings['class'] != "") {
			$return_objs = array();
			foreach($return as $r) {
				self::instance();
				utils::array_to_object($r, self::$_instance);
				$return_objs[] = self::$_instance;
				self::$_instance = null;
			}
			return $return_objs;
		}
		return $return;
	}
	
	public static function insert($object = null, $array = array()) {
		$sql = "INSERT INTO %s (%s) VALUES (%s);";
		$data = array();
		if (self::$settings['class'] != "") {
			if (is_object($object) && $object instanceof self::$settings['class']) {
				$data = utils::object_to_array($object);
			}
		} else {
			$data = $array;
		}
		if (!empty($data) && is_array($data)) {
			if (utils::is_in_array(array_keys($data), self::get_fields())) {
				$fields = "";
				$values = "";
				foreach($data as $key => $value) {
					if (!is_null($value)) {
						$fields .= sprintf("%s, ", self::filter_field($key));
						$values .= sprintf("'%s', ", parent::esc_str($value));
					}
				}
				$fields = substr($fields, 0, -2);
				$values = substr($values, 0, -2);
				$sql = sprintf(
					$sql,
					self::filter_table(self::$settings['table']),
					$fields,
					$values
				);
				debug::set('sql', self::$settings['class'] .'::'. __FUNCTION__, $sql);
				return parent::execute($sql) ? 1 : 0;
			}
		}
		return -1;
	}
	
	public static function update($object = null, $array = array(), $where = array()) {
		$sql = "UPDATE %s SET %s%s;";
		$data = array();
		if (self::$settings['class'] != "") {
			if (is_object($object) && $object instanceof self::$settings['class']) {
				$data = utils::object_to_array($object);
			}
		} else {
			$data = $array;
		}
		if (!empty($data) && is_array($data)) {
			if (utils::is_in_array(array_keys($data), self::get_fields())) {
				$fields_values = "";
				foreach($data as $key => $value) {
					if (!is_null($value)) {
						$fields_values .= sprintf("%s = '%s', ", self::filter_field($key), parent::esc_str($value));
					}
				}
				$fields_values = substr($fields_values, 0, -2);
				$sql = sprintf(
					$sql,
					self::filter_table(self::$settings['table']),
					$fields_values,
					self::where_str($where)
				);
				debug::set('sql', self::$settings['class'] .'::'. __FUNCTION__, $sql);
				return parent::execute($sql) ? 1 : 0;
			}
		}
		return -1;
	}
	
	public static function delete($where = array()) {
		$sql = "DELETE FROM %s%s;";
		$sql = sprintf(
			$sql,
			self::filter_table(self::$settings['table']),
			self::where_str($where)
		);
		debug::set('sql', self::$settings['class'] .'::'. __FUNCTION__, $sql);
		return parent::execute($sql) ? 1 : 0;
		return -1;	
	}
	
	public static function insert_id() {
		return parent::insert_id();
	}
	
	public static function join_type($jtype) {
		$jtype = trim($jtype);
		switch (strtoupper($jtype)) {
			case "INNER":
				$jtype = "INNER JOIN";
				break;
			case "CROSS":
				$jtype = "CROSS JOIN";
				break;
			case "LEFT OUTER":
				$jtype = "LEFT OUTER JOIN";
				break;
			case "RIGHT OUTER":
				$jtype = "RIGHT OUTER JOIN";
				break;
			case "LEFT":
				$jtype = "LEFT JOIN";
				break;
			case "RIGHT":
				$jtype = "RIGHT JOIN";
				break;
			default:
				$jtype = "JOIN";
				break;
		}
		return $jtype;
	}
	
	public static function join_str($fields = array('*'), $join = array(), $where = array(), $sort = array(), $order = "DESC", $offset = 0, $limit = 0) {
		$sql = "SELECT %s FROM %s%s%s%s%s;";
		$fields_str = "";
		$from = self::filter_table(self::$settings['table']);

		if (empty($fields)) {
			$fields_str = "*";
		} else {
			if (str_replace("`", "", $fields[0]) == "*") {
				$fields_str = "*";
			} else {
				foreach($fields as $field) {
					if ($field != "*") {
						if (substr($field, 0, 1) == ":") {
							$fields_str .= sprintf("%s, ", self::filter_field($from.".".substr($field, 1, strlen($field)-1)));
						} else {
							$fields_str .= sprintf("%s, ", self::filter_field($field));
						}
					}
				}
				$fields_str = substr($fields_str, 0, -2);
			}
		}

		$join_str = "";
		foreach($join as $_join) {
			$join_str .= " " . self::join_type($_join['jtype']);
			foreach($_join['join'] as $k => $v) {
				$join_str .= " " . self::filter_table($k);
				$join_str .= " ON ";
				$join_str .= substr(self::where_str($v), 7, strlen(self::where_str($v))-1);
			}
		}
		$sql = sprintf(
			$sql,
			$fields_str,
			$from,
			$join_str,
			self::where_str($where),
			self::order_str($sort, $order),
			self::limit_str($offset, $limit)
		);
		debug::set('sql', self::$settings['class'] .'::join', $sql);
		return $sql;
	}
	
	public static function join($fields = array('*'), $join = array(), $where = array(), $single = false, $sort = array(), $order = "DESC", $offset = 0, $limit = 0) {
		if ($single) {
			return parent::row(self::join_str(
				$fields,
				$join,
				$where,
				$sort,
				$order,
				$offset,
				$limit
			));
		} else {
			return parent::rows(self::join_str(
				$fields,
				$join,
				$where,
				$sort,
				$order,
				$offset,
				$limit
			));		
		}
	}
	
}
