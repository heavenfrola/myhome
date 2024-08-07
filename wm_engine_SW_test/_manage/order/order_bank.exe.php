<?PHP

	printAjaxHeader();
	
	$exec = $_POST['exec'];
	if($exec=="delete") {
		$no = numberOnly($_POST['no']);
		$bank = $pdo->row("select bank from $tbl[bank_customer] where no='$no'");
		$pdo->query("delete from $tbl[bank_customer] where no='$no'");
		exit;
	}else if($exec=="insert") {
		$bank = addslashes($_POST['bank']);
		$code = numberOnly($_POST['code']);

		$no = $pdo->row("select * from $tbl[bank_customer] where code='$code'");
		if(!$no) {
			$pdo->query("insert into $tbl[bank_customer] (`bank`, `code`, `reg_date`) VALUES ('$bank', '$code', '$now')");
			$bcno = $pdo->lastInsertId();
		}else {
			echo "pass";
			exit;
		}
	}

?>
<li id="<?=$bcno?>"><?=$bank?> (은행코드 : <?=$code?>)<a onclick="bankDelete(<?=$bcno?>, '<?=$bank?>');" class="delete">삭제</a></li>