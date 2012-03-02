<?php
class DbCaseSensitivityPackage extends Package {
	
	protected $pkgHandle = 'db_case_sensitivity';
	protected $appVersionRequired = '5.4.2';
	protected $pkgVersion = '1.0.1';
	
	public function getPackageDescription() {
		return t('Fixes the database table names to case sensitive.');
	}
	
	public function getPackageName(){
		return t('Database Case Sensitivity Migration');
	}
	
	public function install() {
		$pkg = parent::install();
		
		Loader::model("single_page");
		$def = SinglePage::add('/dashboard/system/db_migration/', $pkg);
		$def->update(array('cName' => "Database Migration"));
	}
	
	public function on_start() {
		if (defined('DB_CASE_SENSITIVITY_FIX') && DB_CASE_SENSITIVITY_FIX === true) {
			$h = Loader::helper("db_fix", $this->pkgHandle);
			$h->fix();
		}
	}
	
	/**
	 * Returns all the special table names that are not
	 * included in the default schemas. Some of the table
	 * names might just not exist in the schema files.
	 */
	public function getDatabaseSpecialTables() {
		$tables = array(
			"btFile",
			"CollectionSearchIndexAttributes",
			"FileSearchIndexAttributes",
			"UserSearchIndexAttributes"
		);
		$tables = array_merge($tables, $this->_getSpecialTablesCoreCommerce());
		
		return $tables;
	}
	
	private function _getSpecialTablesCoreCommerce() {
		$ec = Package::getByHandle("core_commerce");
		$tables = array();
		if ($ec) {
			$tables[] = 'CoreCommerceOrderSearchIndexAttributes';
			$tables[] = 'CoreCommerceProductSearchIndexAttributes';
			// Go through special cases in the eCommerce add-on
			Loader::model("shipping/type", "core_commerce");
			Loader::model("discount/type", "core_commerce");
			Loader::model("payment/method", "core_commerce");
			$parser = new DbSchemaParser();
			
			 // Please note that CoreCommerce package on_start might not be
			 // run at this point, so let's set the db file name manually.
			$DB_FILE = "db.xml";
			
			$sts = CoreCommerceShippingType::getList();
			foreach ($sts as $st) {
				$file = $st->getShippingTypeFilePath($DB_FILE);
				if ($file) {
					$parser->parseSchema($file);
				}
			}
			$dts = CoreCommerceDiscountType::getList();
			foreach ($dts as $dt) {
				$file = $dt->getDiscountTypeFilePath($DB_FILE);
				if ($file) {
					$parser->parseSchema($file);
				}
			}
			$pms = CoreCommercePaymentMethod::getList();
			foreach ($pms as $pm) {
				$file = $pm->getPaymentMethodFilePath($DB_FILE);
				if ($file) {
					$parser->parseSchema($file);
				}
			}
			$tables = array_merge($tables, $parser->getTableNames());
		}
		return $tables;
	}
	
}
?>