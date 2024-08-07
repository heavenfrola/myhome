<?PHP

	if($cfg['category_ctype_expand'] != 'Y') {
		$pdo->query("insert into {$tbl['config']} (name, value, reg_date) values ('category_ctype_expand', 'Y', '$now')");
		$pdo->query("alter table {$tbl['category']} change ctype ctype int(2) not null default '1' comment '카테고리 종류'");
	}

	$ctype = $_GET['ctype'] = 10;
	include $engine_dir.'/_manage/product/catework.php';

?>