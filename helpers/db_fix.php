<?php
class DbFixHelper {
	
	private $_reader = null;
	private $_info = null;
	
	public function __construct() {
		Loader::library("db_schema_reader", "db_case_sensitivity");
		Loader::library("db_info", "db_case_sensitivity");
		$this->_reader = new DbSchemaReader();
		$this->_info = new DbInfo();
	}
	
	public function fix() {
		$this->_reader->fixDatabaseNames();
	}
	
	public function getFixScript() {
		$script = "";
		$i = 0;
		foreach ($this->_reader->getFixScriptRows(false) as $row) {
			if ($i > 0) {
				$script .= "\r\n";
			}
			$script .= $row;
			$i++;
		}
		return $script;
	}
	
	public function getCorrectTables() {
		return $this->_reader->getDatabaseTableNames();
	}
	
	public function getMissingTables() {
		return $this->_reader->getMissingTables();
	}
	
	public function isDatabaseLowerCase() {
		$ret = intval($this->_info->getVariable("lower_case_table_names"));
		return $ret === 1;
	}
	
}
?>