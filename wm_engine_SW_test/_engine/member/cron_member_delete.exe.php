<?PHP

	header('Content-type:text/html; charset='._BASE_CHARSET_.';');
    set_time_limit(0);
    ini_set('memory_limit', -1);

	include_once $engine_dir.'/_engine/include/common.lib.php';

	if(!fieldExist($tbl['member'], 'whole_mem')) {
		addField($tbl['member'], 'whole_mem', 'enum("Y","N") not null default "N"');
		$pdo->query("alter table $tbl[member] add index whole_mem (whole_mem)");
	}

	$urlfix = 'Y';
	$no_qcheck = true;

	$date1 = strtotime(date('Y-m-d', strtotime('-11 months', $now)));
	$date1e = $date1+86399;
	$date2 = strtotime(date('Y-m-d', strtotime('-12 months', $now)));
	$mail_case = 12;

	// 휴면 처리
	$w = " and last_order <= '$date2' and last_con <= '$date2' and whole_mem != 'Y' and `level`!=1";

    $fd = '';
    if (fieldExist($tbl['member'], 'birth2') == true) {
        $fd .= ', birth2';
    }
	$res = $pdo->iterator("
        select no, member_id, name, email, phone, cell, zip, addr1, addr2, birth, sex, milage, emoney $fd
        from $tbl[member] where withdraw in ('N', 'D1') $w
    ");
    foreach ($res as $data) {
		$pdo->query("
			insert into $tbl[member_deleted] (no, member_id, name, email, phone, cell, zip, addr1, addr2, birth, birth2, gender, milage, emoney, reg_date)
			values ('$data[no]', '$data[member_id]', '$data[name]', '$data[email]', '$data[phone]', '$data[cell]', '$data[zip]', '$data[addr1]', '$data[addr2]', '$data[birth]', '$data[birth2]', '$data[sex]', '$data[milage]', '$data[emoney]', '$now')
		");
		$pdo->query("update $tbl[member] set name='휴면회원', email='', phone='', cell='', zip='', addr1='', addr2='', birth='', sex='', milage=0, emoney=0, withdraw='D2' where no='$data[no]' and member_id='$data[member_id]'");

		if(is_object($erpListener)) {
			$erpListener->setChangedMember($data['member_id'], $data['no']);
		}
	}

	// 휴면 안내 메일
	$res = $pdo->iterator("select no, member_id, name, email, cell, reg_date, if (last_con > last_order, last_con, last_order) last_date from $tbl[member] where withdraw='N' and `level`!=1 having last_date between '$date1' and '$date1e'");
    echo $pdo->getqry();
    foreach ($res as $data) {
		if (!$cfg['del_send_type1'] || $cfg['del_send_type1'] == "Y") {
			include $engine_dir.'/_engine/include/mail.lib.php';
			sendMailContent($mail_case, $data['name'], $data['email']);
		}

		if ($cfg['del_send_type2'] == "Y") {
			include_once $engine_dir."/_engine/sms/sms_module.php";
			$sms_replace['trans_date'] = date('Y년 m월 d일', strtotime('+30 days', $now));
			$sms_replace['name'] = $data['name'];
            $sms_replace['member_id'] = $data['member_id'];
			SMS_send_case(28, $data['cell']);
		}

		$pdo->query("update $tbl[member] set withdraw='d1' where no='$data[no]'");
		$cnt2++;
	}

?>