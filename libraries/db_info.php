<?php
class DbInfo {
	
	private $_db;
	
	public function __construct() {
		$this->_db = Loader::db();
	}
	
	public function getVariableList($variableName) {
		$sql = "SHOW VARIABLES LIKE ?";
		$list = $this->_db->GetAll($sql, array($variableName));
		$vars = array();
		foreach ($list as $row) {
			array_push($vars, $row['Value']);
		}
		return $vars;
	}
	
	public function getVariable($variableName) {
		$list = $this->getVariableList($variableName);
		return sizeof($list) > 0 ? array_shift($list) : null;
	}
	
}
?>