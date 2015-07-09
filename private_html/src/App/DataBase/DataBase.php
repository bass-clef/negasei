<?php
/**
 * mysqlに接続するための簡単なpdoラッパー
 */

namespace src\App\DataBase;

class DataBase
{
	private $db;
	private $queryStrings;
	private $params;
	private $pdoStatement;


	/**
	 * 初期化
	 */
	private function initalize()
	{
		$this->queryStrings = ''; 
		$this->params = null;
	}

	/**
	 * 接続
	 */
	public function __construct($dbName=null, $hostName=null)
	{
		$dsn = 'mysql:';
		$dsn .= 'dbname=' . \Config::$db['name'] . ';';
		$dsn .= 'host=' . \Config::$db['host'] . ';';
		$dsn .= 'charset=utf8';
		$user = \Config::$db['user'];
		$password = \Config::$db['pass'];
		try{
			$this->db = new \PDO($dsn, $user, $password);
		} catch (\PDOException $e){
			error_log('Connection failed:' . $e->getMessage());
			echo $e->getMessage();
			die();
		}

		$this->initalize();
		return $this;
	}

	/**
	 * queryのタイプをINSERTにする
	 *
	 * @param string $tableName テーブル名
	 */
	public function insertQuery($tableName)
	{
		$this->queryStrings = 'INSERT INTO ' . $tableName;
		return $this;
	}

	/**
	 * queryのタイプをSELECTにする
	 *
	 * @param string $tableName テーブル名
	 */
	public function selectQuery($tableName)
	{
		$this->queryStrings = 'SELECT * FROM ' . $tableName;
		return $this;
	}

	/**
	 * select句の追加
	 *
	 * @param string[] $columns   カラム名
	 * @param string   $tableName テーブル名
	 */
	public function select($columns, $tableName)
	{
		if (!is_array($columns)) {
			$columns = [$columns];
		}
		$this->queryStrings = 'SELECT ' . implode(',', $columns) . ' FROM ' . $tableName;
		return $this;
	}

	/**
	 * queryのタイプをUPDATEにする
	 *
	 * @param string $tableName テーブル名
	 */
	public function updateQuery($tableName)
	{
		$this->queryStrings = 'UPDATE ' . $tableName;
		return $this;
	}

	/**
	 * values句の追加
	 *
	 * @param string[] $param1 対象のカラム名($param2省略時は代入する値)
	 * @param mixed[]  $param2 対象に代入する値
	 */
	public function values($param1, $param2 = null)
	{
		if (!is_null($param2)) {
			$this->queryStrings .= ' (' . implode(',', $param1) . ')';
			$values = $param2;
		} else {
			$values = $param1;
		}
		$values = array_map(function($row) { return "'$row'"; }, $values);
		$this->queryStrings .= ' VALUES (' . implode(',', $values) . ')';

		return $this;
	}

	/**
	 * set句の追加
	 *
	 * @param string $columnName 対象のカラム名
	 * @param mixed  $value      対象に代入する値
	 */
	public function set($columnName, $value)
	{
		$this->queryStrings .= ' SET ' . $columnName . ' = ?';

		if (!is_array($this->params)) {
			$this->params = [];
		}
		if (!is_array($value)) {
			$value = [$value];
		}
		$this->params = array_merge($this->params, $value);

		return $this;
	}

	/**
	 * where句の追加
	 *
	 * @param string $columnName 対象のカラム名
	 * @param mixed  $value      対象と比較する値
	 * @param string $operator   対象と比較する際に使用する演算子
	 */
	public function where($columnName, $value, $operator = '=')
	{
		if (!is_array($this->params)) {
			$this->params = [];
		}
		if (FALSE !== strpos($this->queryStrings, 'WHERE')) {
			$whereString = ' AND';
		} else {
			$whereString = ' WHERE';
		}

		switch($operator){
		case 'IN':
			$this->queryStrings .= $whereString . ' ' . $columnName . ' IN(' . implode(',', $value) . ')';
			break;
			
		case 'BETWEEN':
			$this->queryStrings .= $whereString . ' ' . $columnName . ' BETWEEN ' . $value[0] . ' AND ' . $value[1];
			break;

		default:
			$this->queryStrings .= $whereString . ' ' . $columnName . ' ' . $operator . ' ?';
			break;
		}

		if (!is_array($value)) {
			$value = [$value];
		}
		$this->params = array_merge($this->params, $value);

		return $this;
	}

	/**
	 * limit句の追加
	 * 
	 * @param int $param1 オフセット($param2省略時は件数)
	 * @param int $param2 件数
	 */
	public function limit($param1, $param2 = null)
	{
		if (is_null($param2)) {
			$this->queryStrings .= ' LIMIT ' . $param1;
		} else {
			$this->queryStrings .= ' LIMIT ' . $param1 . ', ' . $param2;
		}

		return $this;
	}

	/**
	 * orderBy句の追加
	 *
	 * @param string $columnName
	 * @param string $order 順序(省略時は降順)
	 */
	public function orderBy($columnName, $order = 'DESC')
	{
		$this->queryStrings .= ' ORDER BY ' . $columnName . ' ' .$order;

		return $this;
	}

	/**
	 * SQLを出力
	 */
	public function toSQL()
	{
		var_dump($this->params);
		var_dump($this->queryStrings);
		return str_replace('?', $this->params, $this->queryStrings);
	}

	/**
	 * クエリの実行
	 */
	public function execute()
	{
		if (!is_null($this->params)) {
			$this->pdoStatement = $this->db->prepare($this->queryStrings);
			$this->pdoStatement->execute($this->params);
		} else {
			$this->pdoStatement = $this->db->query($this->queryStrings);
		}

		$this->initalize();
		return $this->pdoStatement;
	}

	/**
	 * 行の取得
	 */
	public function fetch()
	{
		$result = $this->execute()->fetch(\PDO::FETCH_ASSOC);
		return $result;
	}

	/**
	 * 複数行の取得
	 */
	public function fetchAll()
	{
		$result = $this->execute()->fetchAll(\PDO::FETCH_ASSOC);
		return $result;
	}
}

