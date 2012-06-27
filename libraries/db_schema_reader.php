<?php
class DbSchemaReader {
	
	private $_parser = null;
	
	public function __construct() {
		Loader::library("db_schema_parser", "db_case_sensitivity");
		$this->_parser = new DbSchemaParser();
	}
	
	public function fixDatabaseNames() {
		$sqls = $this->getFixScriptRows();
		$db = Loader::db();
		foreach ($sqls as $sql) {
			$db->Execute($sql);
		}
	}

	public function getFixScriptRows($appendCorrect = false) {
		$names = $this->getDatabaseTableNames();
		
		$db = Loader::db();
		$sql = "SHOW TABLES";
		$currentTables = $db->getCol($sql);
		$rows = array();
		foreach ($names as $tbl) {
			$migrateTbl = strtolower($tbl);
			if ($appendCorrect || ($key = array_search($migrateTbl, $currentTables)) !== false) {
				// Rename the lower case table to correct name
				// First we need to change the table name to temporary
				// name to bypass the MySQL bug for renaming lower case
				// table name to the case sensitive name
				$tmpName = $migrateTbl . "_tmp";
				$sql = "RENAME TABLE " . $migrateTbl . " TO " . $tmpName . ";";
				array_push($rows, $sql);
				// First drop the old correctly named table for
				// the renaming to be possible
				$sql = "DROP TABLE IF EXISTS " . $tbl . ";";
				array_push($rows, $sql);
				unset($currentTables[$key2]);
				// After that we can change the temp name to the correct
				// case sensitive name.
				$sql = "RENAME TABLE " . $tmpName . " TO " . $tbl . ";";
				array_push($rows, $sql);
				
				// For better performance in future loops, unset the
				// current tables key.
				unset($currentTables[$key]);
			}
		}
		return $rows;
	}

	public function getMissingTables() {
		$names = $this->getDatabaseTableNames();
		
		$db = Loader::db();
		$sql = "SHOW TABLES";
		$currentTables = $db->getCol($sql);
		foreach ($names as $tbl) {
			$migrateTbl = strtolower($tbl);
			if (($key = array_search($migrateTbl, $currentTables)) !== false) {
				unset($currentTables[$key]);
			}
			if (($key = array_search($tbl, $currentTables)) !== false) {
				unset($currentTables[$key]);
			}
		}
		return $currentTables;
	}
	
	/**
	 * Returns correct database table names.
	 */
	public function getDatabaseTableNames() {		
		if (sizeof($this->_parser->getTableNames()) > 0) {
			return $this->_parser->getTableNames();
		}
		$this->_parseCoreSchema();
		$this->_parseBlockSchemas();
		$this->_parseAttributeTypeSchemas();
		$this->_parsePackageSchemas();
		
		return $this->_parser->getTableNames();
	}
	
	private function _parseCoreSchema() {
		return $this->_parseSchema(DIR_BASE_CORE . "/config", FILENAME_PACKAGE_DB);
	}
	
	private function _parseBlockSchemas() {
		$blocks = BlockTypeList::getInstalledList();
		foreach ($blocks as $b) {
			$dir = $b->getBlockTypePath();
			if ($b->getPackageID() > 0) {
				$pkgHandle = $b->getPackageHandle();
				$dir = DIR_PACKAGES . '/' . $pkgHandle . '/' . DIRNAME_BLOCKS . '/' . $b->getBlockTypeHandle();
			} else if ($b->isCoreBlockType()) {
				$dir = DIR_FILES_BLOCK_TYPES_CORE . '/' . $b->getBlockTypeHandle();
			}
			if (file_exists($dir . '/' . FILENAME_BLOCK_DB)) {
				$this->_parseSchema($dir, FILENAME_BLOCK_DB);
			}
		}
		return true;
	}
	
	private function _parseAttributeTypeSchemas() {
		$ats = AttributeType::getList();
		foreach ($ats as $at) {
			$file = $at->getAttributeTypeFilePath(FILENAME_ATTRIBUTE_DB);
			if ($file) {
				$this->_parseSchema($file);
			}
		}
		return true;
	}
	
	private function _parsePackageSchemas() {
		$packages = Package::getInstalledList();
		foreach ($packages as $pkg) {
			$pkg = Loader::package($pkg->getPackageHandle());
			
			if (is_object($pkg)) {
				$dir = DIR_PACKAGES . '/' . $pkg->getPackageHandle() . '/';
				if (file_exists($dir . '/' . FILENAME_PACKAGE_DB)) {
					$this->_parseSchema($dir, FILENAME_BLOCK_DB);
				}
				// Integration function for packages for tables that
				// do not show up in the default schema files
				if (method_exists($pkg, "getDatabaseSpecialTables")) {
					$arr = $pkg->getDatabaseSpecialTables();
					if (is_array($arr)) {
						foreach ($arr as $tbl) {
							$this->_parser->addTableName($tbl);
						}
					}
				}
			}
		}
		return true;
	}
	
	private function _parseSchema($dirOrPath, $dbFile = null) {
		$xmlFile = $dirOrPath;
		if ($dbFile !== null) {
			$xmlFile .= "/" . $dbFile;
		}
		$this->_parser->parseSchema($xmlFile);
	}
	

}
?>