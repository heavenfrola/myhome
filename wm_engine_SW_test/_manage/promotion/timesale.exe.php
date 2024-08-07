<?PHP

	if($_POST['ts_use'] == 'Y') {
		addField($tbl['order_product'], 'sale3', 'double(10,2) unsigned not null default "0" after sale2');
		addField($tbl['product'], 'ts_use', 'enum("N","Y") not null default "N"');
		addField($tbl['product'], 'ts_dates', 'int(10) not null default "0"');
		addField($tbl['product'], 'ts_datee', 'int(10) not null default "0"');
		addField($tbl['product'], 'ts_names', 'varchar(200) not null default ""');
		addField($tbl['product'], 'ts_namee', 'varchar(200) not null default ""');
		addField($tbl['product'], 'ts_saleprc', 'int(10) not null default "0"');
		addField($tbl['product'], 'ts_saletype', 'varchar(10) not null default "0"');
		addField($tbl['product'], 'ts_state', 'int(2) not null default "0"');
		addField($tbl['product'], 'ts_ing', 'enum("N","Y") not null default "N"');
		$pdo->query("alter table $tbl[product] add index ts_use (ts_use)");
		$pdo->query("alter table $tbl[product] add index ts_date (ts_dates, ts_datee)");

		if(isTable($tbl['product_timesale_set']) == false) {
			include_once $engine_dir.'/_config/tbl_schema.php';
			$pdo->query($tbl_schema['product_timesale_set']);
		}
	}

	if(isset($_POST['use_ts_mark_1']) == false) $_POST['use_ts_mark_1'] = 'N';
	if(isset($_POST['use_ts_mark_2']) == false) $_POST['use_ts_mark_2'] = 'N';
	if(isset($_POST['use_ts_mark_3']) == false) $_POST['use_ts_mark_3'] = 'N';
	if(isset($_POST['use_ts_mark_4']) == false) $_POST['use_ts_mark_4'] = 'N';

	include $engine_dir.'/_manage/config/config.exe.php';

?>