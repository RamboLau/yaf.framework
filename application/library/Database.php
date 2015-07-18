<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2015 panjun.liu <http://176code.com lpj163@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

class Database extends PDO
{

	private $db = null;

	public function __construct($dsn, $username, $password) {
		if (empty($this->db)) {
			try {
        $this->db = new \PDO($dsn, $username, $password);
        // prior to 5.3.6 the charset key in the connection string is ignored
        // so we can check the PHP version and force charset this way
        if (version_compare(phpversion(), '5.3.6') < 0) {
          $this->db->exec("SET NAMES utf8");
        }

				# We can now log any exceptions on Fatal error. 
				$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				
				# Disable emulation of prepared statements, use REAL prepared statements instead.
				$this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			}
			catch (PDOException $e) {
				throw new Exception($e->getMessage());
			}
		}
	}

	/**
	 * get table fields.
	 * 
	 * @param array $data
	 *
	 * @return string
	 */
	private function get_fields($data) {
		$fields = array();
		if (is_int(key($data))) {
			$fields = implode(',', $data);
		}
		else if (!empty($data)) {
			$fields = implode(',', array_keys($data));
		}
		else {
			$fields = '*';
		}
		return $fields;
	}

	/**
	 * get condition string
	 * 
	 * @param array $condition
	 * @param string $operator
	 * @param string $logical_connector
	 *
	 * @return string
	 */
	private function get_condition($condition, $operator = '=', $logical_connector = 'AND') {
		$cdts = '';
		if (empty($condition)) {
			return $cdts = '';
		}
		else if (is_array($condition)) {
			$_cdta = array();
			foreach($condition as $k => $v) {
				if (!is_array($v)) {
					if (strtolower($operator) == 'like') {
						$v = '\'%' . $v . '%\'';
					}
					else if (is_string($v)) {
						$v = '\'' . $v . '\'';
					}
					$_cdta[] = ' ' . $k . ' ' . $operator . ' ' . $v . ' ' ;
				}
				else if (is_array($v)) {
					$_cdta[] = $this->split_condition($k, $v);
				}
			}
			$cdts .= implode($logical_connector, $_cdta);
		}
		return $cdts;
	}

	/**
	 * split condition
	 *
	 * @param string $field
	 * @param array $cdt
   *
	 * @return string
	 */
	private function split_condition($field, $cdt) {
		$cdts = array();
		$oper = empty($cdt[1]) ? '=' : $cdt[1];
		$logc = empty($cdt[2]) ? 'AND' : $cdt[2];
		if (!is_array($cdt[0])) {
			$cdt[0] = is_string($cdt[0]) ? "'$cdt[0]'" : $cdt[0];
		}
		else if (is_array($cdt[0]) || strtoupper(trim($cdt[1])) == 'IN') {
			$cdt[0] = '(' . implode(',', $cdt[0]) . ')';
		}

		$cdta[] = " $field $oper {$cdt[0]} ";
		if (!empty($cdt[3])) {
			$cdta[] = $this->get_condition($cdt[3]);
		}
		$cdts = ' ( ' . implode($logc, $cdta) . ' ) ';
		return $cdts;
	}

	/**
	 * get field data
   *
	 * @param array $data
	 *
	 * @return array
	 */
	private function get_fields_datas($data) {
		$arrf = $arrd = array();
		foreach($data as $f => $d) {
			$arrf[] = '`' . $f . '`';
			$arrd[] = is_string($d) ? '\'' . $d . '\'' : $d;
		}
		$res = array(implode(',', $arrf), implode(',', $arrd));
		return $res;
	}

	/**
	 * parepare string sql
	 */
	private function prepare_sql($table, $condition = array(), $column = array()) {
		$fields = $this->get_fields($column);
		$cdts = $this->get_condition($condition);
		$where = empty($condition) ? '' : ' WHERE ' . $cdts;
		$sql = 'SELECT ' . $fields . ' FROM ' . $table . $where;		
		return $sql;
	}

  /**
   * Save data to table
   *
   * @param string $table
   * @param array $data
   * @param array $conditions
   *
   * @return Statement
   */
  public function save($table, $data, $conditions) {
    // Update if primary key exists in params set or insert new row
		$cdt = $this->get_condition($conditions);
		list($strf, $strd) = $this->get_fields_datas($data);
		$has_exist = $this->fetchAssoc($table, $conditions);
		if (!$has_exist) {
			$enum = $this->insert($table, $data);
		}
		else {
			$enum = $this->update($table, $data, $conditions);
		}
		return $enum;
  }

  /**
   * adds a row to the specified table
   *
   * @param string $table - the name of the db table we are adding row to
   * @param array $data - associative array representing the columns and their respective values
   *
   * @return statement
   */
	public function insert($table, $data = array()) {
		$cols = $marks = array();
		foreach ($data as $field => $value) {
			$cols[] = $field;
			$marks[] = '?';
		}
		$query = 'INSERT INTO ' . $table
              . ' (' . implode(', ', $cols) . ')'
              . ' VALUES (' . implode(', ', $marks) . ')';

		return $this->execute($query, array_values($data));
	}

  /**
   * update a row to the specified table
   *
   * @param string $table - the name of the db table we are adding row to
   * @param array $data - associative array representing the columns and their respective values
   * @param array $condition (Optional) - the where clause of the query
   *
   * @return Statement
   */
	public function update($table, $data = array(), $condition = array()) {
		$sql = 'UPDATE ' . $table . ' SET ';
		$fields = array();
		foreach ($data as $field => $value) {
			$fields[] = $field . '=?';
		}
		$sql .= join(',', $fields);

		// lets add our where clause if we have one
		if (!empty($condition)) {
			// load each key value pair, and implode them with an AND
			$condition_array = array();
			foreach($condition as $key => $val) {
				$condition_array[] = "$key=:condition_$key";
			}
			// build the final where string
			$sql .= 'WHERE '.implode(' AND ', $condition_array);
		}		

		return $this->execute($sql, array_values($data));
	}

	/**
	 * deletes rows from a table based on the parameter
	 *
	 * @param table - the name of the db table we are deleting the rows from
	 * @param params - associative array representing the WHERE clause filters
	 *
	 * @return bool - associate representing the fetched table row, false on failure
	 */	 
	public function delete($table, $params = array()) {
		// building query string
		$sql = "DELETE FROM $table";
		// append WHERE if necessary
		$sql .= ( count($params)>0 ? ' WHERE ' : '' );
		
		$add_and = false;
		// add each clause using parameter array
		foreach ($params as $key=>$val) {
			// only add AND after the first clause item has been appended
			if ($add_and) {
				$sql .= ' AND ';
			} else {
				$add_and = true;
			}
			
			// append clause item
			$sql .= "$key = :$key";
		}
		
		return $this->execute($sql);
	}

	/**
	 * retrieve information from the database, as an array
	 *
	 * @param string $table - the name of the db table we are retreiving the rows from
	 * @param array $condition - associative array representing the WHERE clause filters
	 * @param array $column - contains the column names
	 *
	 * @return mixed - associate representing the fetched table row, false on failure
	 */
	public function fetchAll($sql) {
		$statement = $this->db->execute($sql);
		return $statement->fetchAll(\PDO::FETCH_ASSOC);
	}

	/**
	 * retrieve information from the database, as an object
	 *
	 * @param string $table - the name of the db table we are retreiving the rows from
	 * @param array $condition - associative array representing the WHERE clause filters
	 * @param array $column - contains the column names
	 *
	 * @return mixed - associate representing the fetched table row, false on failure
	 */
	public function fetchObject($table, $condition = array(), $column = array()){
		$sql = $this->prepare_sql($table, $condition, $column);
		$statement = $this->execute($sql);
		return $statement->fetchObject();
	}

	/**
	 * retrieve information from the database, as an array
	 *
	 * @param string $table - the name of the db table we are retreiving the rows from
	 * @param array $condition - associative array representing the WHERE clause filters
	 * @param array $column - contains the column names
	 *
	 * @return mixed - associate representing the fetched table row, false on failure
	 */
	public function fetchAssoc($table, $condition = array(), $column = array()) {
		$sql = $this->prepare_sql($table, $condition, $column);
		$statement = $this->execute($sql);
		return $statement->fetch(\PDO::FETCH_ASSOC);
	}

	public function prepareSql($sql) {
		return $this->db->prepare($sql);
	}

	public function Rexecute($sql, array $params = array()) {
		if (empty($params)) {
			return $this->db->exec($sql);
		}
		$statement = $this->prepareSql($sql);
		$index = 1;
		foreach ($params as $key => $value) {
			$statement->bindValue($index, $value);
			$index ++;
		}
		$statement->execute();
		return $statement->rowCount();
	}

	public function lastInsertId($seqname = NULL) {
		return $this->db->lastInsertId();
	}

	public function getConnection() {
		return $this->db;
	}
}