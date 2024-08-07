<?PHP

	printAjaxHeader();

	switch($_POST['exec']) {
		case 'remove' :
			$no = numberOnly($_POST['no']);
			$pdo->query("delete from $tbl[excel_preset] where no='$no'");
		break;
		case 'setName' :
			$no = numberOnly($_POST['no']);
			$name = addslashes(trim($_POST['name']));
			$pdo->query("update $tbl[excel_preset] set name='$name' where no='$no'");
		break;
		case 'make' :
			$type = addslashes($_POST['type']);

			$sort = $pdo->row("select max(sort) from $tbl[excel_preset] where type='$type'")+1;
			$pdo->query("insert into $tbl[excel_preset] (type, name, data, sort, reg_date) values ('$type', '새 양식', '', '$sort', '$now')");

			echo $pdo->lastInsertId();
			exit;
		break;
		case 'saveSet' :
			$no = numberOnly($_POST['xls_set']);
			$type = addslashes($_POST['type']);
			$name = addslashes(trim($_POST['set_name']));
			$set_data = addslashes($_POST['set_data']);

			checkBlank($name, '세트명을 입력해주세요.');

			$pdo->query("alter table $tbl[excel_preset] change data data varchar(1000) not null"); // 엑셀필드 길이 연장

			if($no) {
				$pdo->query("update $tbl[excel_preset] set name='$name', data='$set_data' where no='$no'");
			} else {
				$sort = $pdo->row("select max(sort) from $tbl[excel_preset] where partner_no='$admin[partner_no]'")+1;
				$pdo->query("insert into $tbl[excel_preset] (type, name, data, sort, reg_date, partner_no) values ('$type', '$name', '$data', '$sort', '$now', '$admin[partner_no]')");
			}

			msg('', 'reload', 'parent');
		break;
	}

?>