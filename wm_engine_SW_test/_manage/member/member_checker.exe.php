<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  특별회원그룹 설정
	' +----------------------------------------------------------------------------------------------+*/

	switch($_POST['exec']) {

		case 'register' :
			$name = addslashes(trim($_POST['name']));
			checkBlank($name, '그룹명을 입력해주세요.');

			$pdo->query("insert into `$tbl[member_checker]` (name, members, reg_date) values ('$name', '0', '$now')");

			$no = $pdo->lastInsertId();
			$pdo->query("alter table $tbl[member] add checker_$no enum ('N','Y') not null default 'N'");
		break;

		case 'edit' :
			$no = numberOnly($_POST['no']);
			$name = addslashes(trim($_POST['name']));
			$no_milage = ($_POST['no_milage'] == 'Y') ? 'Y' : 'N';
			$no_discount = ($_POST['no_discount'] == 'Y') ? 'Y' : 'N';
			$no_coupon = ($_POST['no_coupon'] == 'Y') ? 'Y' : 'N';
			$no_pg = ($_POST['no_pg'] == 'Y') ? 'Y' : 'N';
			$deny = ($_POST['deny'] == 'Y') ? 'Y' : 'N';
			$homepage = addslashes(trim($_POST['homepage']));
			$login_msg_type = ($_POST['login_msg_type'] == '1') ? '1' : '2';
			$login_msg = addslashes(trim($_POST['login_msg']));

			if(fieldExist($tbl['member_checker'], 'no_milage') == false) {
				$pdo->query("
					alter table $tbl[member_checker] add no_milage enum('N','Y') default 'N',
					add no_sale enum('N','Y') default 'N',
					add no_pg enum('N','Y') default 'N',
					add homepage varchar(100) not null,
					add login_msg text not null;
				");
			}

			if(fieldExist($tbl['member_checker'], 'no_discount') == false) {
                addField($tbl['member_checker'], 'no_discount', 'enum("Y", "N") not null default "N" after no_sale');
                addField($tbl['member_checker'], 'no_coupon', 'enum("Y", "N") not null default "N" after no_discount');
            }

			$pdo->query("
				update $tbl[member_checker] set
					name='$name', no_milage='$no_milage', no_discount='$no_discount', no_coupon='$no_coupon', no_pg='$no_pg',
					homepage='$homepage', login_msg='$login_msg'
				where no='$no'
			");
			msg('', '?body=member@member_checker', 'parent');
		break;

		case 'delete' :
			$no = numberOnly($_POST['no']);
			$pdo->query("delete from `$tbl[member_checker]` where no='$no'");
			$pdo->query("alter table $tbl[member] drop checker_$no");
			exit('OK');
		break;

	}

	msg('', 'reload', 'parent');

?>