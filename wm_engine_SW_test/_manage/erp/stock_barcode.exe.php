<?PHP

	printAjaxHeader();

	$exec = $_POST['exec'];
	$barcode = addslashes(trim($_POST['barcode']));

	$data = $pdo->assoc("select a.*, b.complex_no, b.force_soldout, b.qty as stock, b.opts from $tbl[product] a inner join erp_complex_option b on b.pno=a.no where b.barcode='$barcode'");
	if(!$data['no']) exit('{"result":"0000","msg":"존재하지 않는 상품입니다."}');

	$data['oname'] = getComplexOptionName($data['opts']);

	// 상품 정보 출력
	if($exec == 'barcode') {
		switch($data['force_soldout']) {
			case 'N' : $force_soldout = $_erp_force_stat['N']; break;
			case 'Y' : $force_soldout = "<span style='color:#ff1111;'>$_erp_force_stat[Y]</a>"; break;
			case 'L' : $force_soldout = "<span style='color:#00cc00;'>$_erp_force_stat[L]</span>"; break;
		}
		ob_start();
		?>
		<div class="box_setup" style="margin-top: 10px;">
			<div class="thumb"><img src="<?=getFileDir($data['updir'])?>/<?=$data['updir']?>/<?=$data['upfile3']?>" style="width:50px;"></div>
			<dl>
				<dt class="title">
					<a href="?body=product@product_register&pno=<?=$data['no']?>" target="_blank"><?=stripslashes(strip_tags($data['name']))?></a>
				</dt>
				<dd class="cstr"><?=stripslashes($data['oname'])?></td>
				<dd>
					재고수량 : <?=$data['stock']?> 개 / <?=$force_soldout?>상품
					<span class="explain">(수량은 화면을 보시는 도중 주문/취소 등에 의해 변경될수 있습니다.)</span>
				</dd>
			</dl>
		</div>
		<?
		$html = ob_get_contents();
		ob_end_clean();

		exit(json_encode(array(
			'result' => '1000',
			'html' => $html
		)));
	}


	// 재고 조정
	if($_POST['mode'] == 'P' && $_POST['ea'] > $data['stock'] && $data['force_soldout'] == 'L') {
		exit('{"result" :"0000", "msg" :"윙포스 한정상품입니다.\\n차감 할 수량이 재고보다 많으므로 처리할수 없습니다."}');
	}

	$new_ea = ($_POST['mode'] == 'P') ? $data['stock']-$_POST['ea'] : $data['stock']+$_POST['ea'];
	$remote_ip = $_SERVER['REMOTE_ADDR'];
	$gap = numberOnly($_POST['ea']);
	if($gap > 0) {
		resolveHold($data['complex_no'], $gap);
	} else {
		setOutputToHold($data['complex_no'], -($gap));
	}

	$pdo->query("insert into erp_inout (complex_no, inout_kind, qty, remark, reg_user, reg_date, remote_ip) values ($data[complex_no], '$_POST[mode]', $gap, '바코드 재고조정', '$admin[admin_id]', now(), '$remote_ip')");
	$pdo->query("update erp_complex_option set qty=curr_stock(complex_no) where complex_no='$data[complex_no]'");

	$mode_str = $_POST['mode'] == 'P' ? '차감' : '증가';
	$title = "$data[stock] / $_POST[ea] / $new_ea / ".'['.date('Y-m-d H:i:s').'] <strong>\''.stripslashes(trim($data['name'].'\'</strong> '.$data['oname'])).'의 재고가 '.number_format($_POST['ea'])." 개 <span class='desc3'>$mode_str</span>되었습니다. (재고 : $new_ea 개)";

	exit(json_encode(array(
		'result' => 1000,
		'msg' => $title
	)));

?>