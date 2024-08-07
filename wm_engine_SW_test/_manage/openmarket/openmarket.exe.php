<?PHP

	if($_POST['exec'] == 'remove') {
		$no = numberOnly($_POST['no']);
		$pdo->query("delete from $tbl[openmarket_cfg] where no='$no'");
		exit;
	}

	if($_POST['config_mode'] == 'openmarket') {
		if($_POST['opmk_api'] != '') {
			if(!isTable($tbl['openmarket_cfg'])) {
				include_once $engine_dir.'/_config/tbl_schema.php';
				$pdo->query($tbl_schema['openmarket_cfg']);
				$pdo->query($tbl_schema['product_openmarket']);
				$pdo->query($tbl_schema['openmarket_api_log']);

				$pdo->query("alter table $tbl[order] add openmarket_id varchar(50) not null default ''");
				$pdo->query("alter table $tbl[order] add openmarket_ono varchar(50) not null default ''");
				$pdo->query("alter table $tbl[order_product] add openmarket_id varchar(50) not null default ''");
				$pdo->query("alter table $tbl[order_product] add openmarket_ono varchar(50) not null default ''");
				$pdo->query("alter table $tbl[order_product] add openmarket_hash varchar(64) not null default ''");
				$pdo->query("alter table $tbl[product_field_set] add shoplinker_cd varchar(50) not null default '' comment '샵링커 품목코드'");
				$pdo->query("alter table $tbl[product] add opmk_cache text not null default '' comment '오픈마켓 게시여부 캐시'");

				$pdo->query("alter table $tbl[order] add index openmarket(openmarket_id, openmarket_ono)");
				$pdo->query("alter table $tbl[order_product] add index openmarket(openmarket_id, openmarket_ono)");

				if($_POST['opmk_api'] == 'shopLinker') {
					include $engine_dir.'/_manage/config/shoplinker_tbl.exe.php';
				}
			}
		}

		include 'config.exe.php';

		return;
	}

	$no = $_POST['no'];
	$name =  $_POST['name'];
	$api_code = $_POST['api_code'];
	$account_id = $_POST['account_id'];
	$is_active = $_POST['is_active'];
	$content = $_POST['content'];

	foreach($no as $key => $val) {
		$_no = numberOnly($no[$key]);
		$_name = addslashes(trim($name[$key]));
		$_api_code = addslashes(trim($api_code[$key]));
		$_account_id = addslashes(trim($account_id[$key]));
		$_is_active = ($is_active[$key] == 'Y') ? 'Y' : 'N';
		$_content = addslashes(trim($content[$key]));

		if(!$_name) continue;

		if($_no > 0) {
			$pdo->query("update $tbl[openmarket_cfg] set name='$_name', api_code='$_api_code', account_id='$_account_id', is_active='$_is_active', content='$_content' where no='$_no'");
		} else {
			$sort = $pdo->row("select max(no) from $tbl[openmarket_cfg]")+1;
			$pdo->query("
				insert into $tbl[openmarket_cfg]
					(name, api_code, account_id, is_active, content, sort, reg_date)
					values
					('$_name', '$_api_code', '$_account_id', '$_is_active', '$_content', '$sort', '$now')
			");
		}
	}

	msg('저장되었습니다.', 'reload', 'parent');

?>