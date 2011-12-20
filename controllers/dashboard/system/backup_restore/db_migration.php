<?php defined('C5_EXECUTE') or die("Access Denied.");
class DashboardSystemBackupRestoreDbMigrationController extends Controller {
	
	public function view() {
		$h = Loader::helper("db_fix", "db_case_sensitivity");
		$this->set("dbscript", $h->getFixScript());
		$this->set("missingTables", $h->getMissingTables());
		$this->set("lowerCaseEnabled", $h->isDatabaseLowerCase());
		
		$this->set("backupFiles", $this->_getBackupFiles());
	}
	
	public function migrate() {
		$h = Loader::helper("db_fix", "db_case_sensitivity");
		$h->fix();
		$this->set("message", t("Migrated successfully!"));
		$this->view();
	}
	
	public function download_script() {
		$h = Loader::helper("db_fix", "db_case_sensitivity");
		$th = Loader::helper("text");
		$site = $th->sanitizeFileSystem(SITE);
		
		$filename = "db_migration_" . $site . "_" . date("Y-m-d") . ".sql";
		header('Content-Type: text/plain');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		echo "-- DB Migration script" . "\r\n";
		echo "-- Generated: " . date("Y-m-d G:i:s") . "\r\n";
		echo "\r\n";
		echo $h->getFixScript();
		
		exit;
	}
	
	public function migrate_backup() {
		$files = $this->_getBackupFiles();
		if (sizeof($files) > 0) {
			$fh = Loader::helper('file');
			$crypt = Loader::helper('encryption');
			$fix = Loader::helper('db_fix', 'db_case_sensitivity');
			
			$tables = $fix->getCorrectTables();
			foreach ($files as $file) {
				// Read the SQL file
				$encrypt = false;
				$file = DIR_FILES_BACKUPS . '/' . $file;
				$str_restSql = $fh->getContents($file);
				if ( !preg_match('/INSERT/m',$str_restSql) && !preg_match('/CREATE/m',$str_restSql) ) {
					$encrypt = true;	
					$str_restSql = $crypt->decrypt($str_restSql);
				}
				// Write the table names correctly
				foreach ($tables as $tbl) {
					$str_restSql = str_replace(strtolower($tbl), $tbl, $str_restSql);
				}
				// Write back the SQL file
				chmod($file,700);
				$fh->clear($file);
				$fh->append($file, $encrypt ? $crypt->encrypt($str_restSql) : $str_restSql);
				chmod($file,000);
			}
			
			$this->set("message", t("Successfully migrated your backup files!"));
		}
		$this->view();
	}
	
	private function _getBackupFiles() {
		$fh = Loader::helper('file');
		$arr = @$fh->getDirectoryContents(DIR_FILES_BACKUPS);
		return is_array($arr) ? $arr : array();
	}
	
}
?>