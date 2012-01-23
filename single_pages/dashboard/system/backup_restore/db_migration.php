<?php defined('C5_EXECUTE') or die("Access Denied.");
$ih = Loader::helper("concrete/interface");
?>

<div class="ccm-pane">
<?php echo Loader::helper('concrete/dashboard')->getDashboardPaneHeaderWrapper(t('Database Migration Tool'), false, false, false)?>
<div class="ccm-pane-body">
<h3><?php echo t("Database Migration Tool") ?></h3>
	<div style="padding-bottom:20px;">
		<p><?php echo t("This tool lets you easily convert your case insensitive database table names to case sensitive.") ?></p>
	</div>
	<h2><?php echo t("Migrate Automatically") ?></h2>
	<div style="padding-bottom:20px;">
		<p><?php echo t("If you want to migrate this site's table names, just click the button below.") ?></p>
		<?php if ($lowerCaseEnabled) : ?>
		<div style="margin-bottom:10px; padding:10px 20px; border: 1px solid; background:#ebe9bd;">
			<p style="color:#ff0000;"><strong><?php echo t("Your database is set to lower case mode and running this script automatically will not work!") ?></strong></p>
			<p><?php echo t("Please note that for you to be able to do this, you need to have following configuration in your MySQL server's my.cnf or my.ini.") ?></p>
			<pre>lower_case_table_names=0</pre>
			<p><?php echo t("You can also run the script manually after you've moved the site by downloading the migration script below.") ?></p>
		</div>
		<?php endif; ?>
		<?php echo $ih->button(t("Run Migration"), $this->action("migrate"), "left", "btn primary"); ?>
	</div>
	
<?php if (sizeof($backupFiles) > 0) : ?>
	<h2><?php echo t("Migrate Backup Files") ?></h2>
	<div style="padding-bottom:20px;">
		<p><?php echo sprintf(t("Currently you have %d backup file(s) available. If you want to migrate these files, just click on the link below."), sizeof($backupFiles)) ?></p>
		<?php echo $ih->button(t("Migrate Backup Files"), $this->action("migrate_backup"), "left", "btn primary"); ?>
	</div>
<?php endif; ?>
	
	<h2><?php echo t("Manual Migration") ?></h2>
	<div style="padding-bottom:20px;">
		<p><?php echo t("If you need to migrate your moved site download the SQL script below and run it on your database server to be migrated.") ?></p>
		<?php echo $ih->button(t("Download Migration Script"), $this->action("download_script"), "left"); ?>
	</div>
	
<?php if (sizeof($missingTables) > 0) : ?>
	<h2><?php echo t("Some tables are missing from the migration!") ?></h2>
	<div style="padding-bottom:20px;">
		<p><?php echo t("Because concrete5 is open source and has a wide range of third party add-ons, this tool cannot guarantee that all the database tables are included in the migration.") ?></p>
		<p><?php echo t("These tables are missing from this site's database migration:") ?>
		<pre><?php 
		$str = "";
		foreach ($missingTables as $tbl)  {
			$str .= $tbl . "\r\n";
		}
		echo trim($str);
		?></pre>
	</div>
<?php endif; ?>
</div>
<?php Loader::helper('concrete/dashboard')->getDashboardPaneFooterWrapper(false); ?>
</div>