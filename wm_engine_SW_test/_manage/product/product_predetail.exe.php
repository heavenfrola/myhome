<?PHP

	checkBasic();
	$pno = numberOnly($_POST['pno']);
	$idx = numberOnly($_POST['idx']);
	$mobile = ($_POST['mobile'] == "P") ? "P" : "M";
	checkBlank($pno,"필수값(PNO)이 없습니다.");
	checkBlank($idx,"필수값(IDX)이 없습니다.");

	$data=get_info($tbl['product_content_log'],"no",$idx);

	if($pno != $data['pno']) msg('상품상세내용 변경이 정상적으로 되지 않았습니다.');
	if($mobile == 'P') {
		$content2 = addslashes($data['content2']);
		$_content2 = addslashes($pdo->row("select `content2` from $tbl[product] where no='$pno'"));

		if($content2 == $_content2) msg('상품상세내용이 동일하여 변경 되지 않았습니다.');

		if($data['content2'] && $content2 != $_content2) {
			$pdo->query("update $tbl[product] set content2='$content2' where no='$pno'");
			$pdo->query("INSERT INTO `".$tbl['product_content_log']."` (`pno`, `content2`, `admin_id`, `reg_date`, `mode`, `mobile`, `edt_date`) VALUES ('$pno', '$_content2', '".$admin['admin_id']."', '$now', '2', 'P', '".$data['reg_date']."')");
			$content_log = $pdo->assoc("select count(*) as `count`, min(`no`) as `no` from `".$tbl['product_content_log']."` where `pno` = '$pno' and `mobile` = 'P'");
			if($content_log['count'] > 10) {
				$pdo->query("delete from `".$tbl['product_content_log']."` where `no` = '".$content_log['no']."'");
			}
		}
	} else {
	    $m_content = addslashes($data['content2']);
		$_m_content = addslashes($pdo->row("select `m_content` from $tbl[product] where no='$pno'"));

		if($m_content == $_m_content) msg('상품상세내용이 동일하여 변경 되지 않았습니다.');

		if($data['content2'] && $m_content != $_m_content) {
			$pdo->query("update $tbl[product] set m_content='$m_content' where no='$pno'");
			$pdo->query("INSERT INTO `".$tbl['product_content_log']."` (`pno`, `content2`, `admin_id`, `reg_date`, `mode`, `mobile`, `edt_date`) VALUES ('$pno', '$_m_content', '".$admin['admin_id']."', '$now', '2', 'M', '".$data['reg_date']."')");
			$m_content_log = $pdo->assoc("select count(*) as `count`, min(`no`) as `no` from `".$tbl['product_content_log']."` where `pno` = '$pno' and `mobile` = 'M'");
			if($m_content_log['count'] > 10) {
				$pdo->query("delete from `".$tbl['product_content_log']."` where `no` = '".$m_content_log['no']."'");
			}
		}
	}

	msg('정상적으로 변경되었습니다.', 'close', 'parent');
?>