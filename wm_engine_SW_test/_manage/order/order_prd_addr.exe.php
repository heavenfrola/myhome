<?PHP

	printAjaxHeader();

	$exec = $_POST['exec'];
	$no = preg_replace('/[^,0-9]/', '', $_POST['no']);

	$prd = $pdo->assoc("select * from $tbl[order_product] where no in ($no)");
	$cnt = $pdo->row("select count(*) from $tbl[order_product] where no in ($no)");
	if($cnt > 1) $prd['name'] .= ' 외 '.($cnt-1).' 건';

	$ord = $pdo->assoc("select * from $tbl[order] where ono='$prd[ono]'");

	if($exec == 'process') {
		if($cfg['opmk_api']) {
			include $engine_dir.'/_engine/api/shoplinker/'.$cfg['opmk_api'].'.class.php';
			include $engine_dir.'/_engine/api/shoplinker/'.$cfg['opmk_api'].'Order.class.php';

			$apiname = $cfg['opmk_api'].'Order';
			$openmarket = new $apiname();
		}

		$r_name = addslashes(trim($_POST['r_name']));
		$r_zip = addslashes(trim($_POST['r_zip']));
		$r_addr1 = addslashes(trim($_POST['r_addr1']));
		$r_addr2 = addslashes(trim($_POST['r_addr2']));
		$r_addr3 = addslashes(trim($_POST['r_addr3']));
		$r_addr4 = addslashes(trim($_POST['r_addr4']));
		$r_phone = addslashes(trim($_POST['r_phone']));
		$r_cell = addslashes(trim($_POST['r_cell']));
		$r_message = addslashes(trim($_POST['r_message']));

		checkBlank($r_name, '받는 분 이름을 입력해주세요.');
		checkBlank($r_zip, '배송지 우편번호를 입력해주세요.');
		checkBlank($r_addr1, '배송지 주소를 입력해주세요.');
		checkBlank($r_addr2, '배송지 상세주소를 입력해주세요.');
		checkBlank($r_cell, '받는 분 휴대폰번호를 입력해주세요.');

		if($prd['openmarket_ono'] && is_object($openmarket)) {
			$openmarket->setDeilveryPrepare(explode(',', $no));
		}

		if(isTable($tbl['order_addr_log']) == false) {
			include_once $engine_dir.'/_config/tbl_schema.php';
			$pdo->query($tbl_schema['order_addr_log']);

			addField($tbl['order_product'], 'addr_changed', 'enum("N", "Y") default "N"');
			$pdo->query("alter table $tbl[order_product] add index addr_changed(addr_changed)");
		}

		$res = $pdo->iterator("select no, r_name, r_zip, r_addr1, r_addr2, r_phone, r_cell, r_message from $tbl[order_product] where no in ($no)");
        foreach ($res as $old) {
			if(!$old['r_name']) $old['r_name'] = $ord['addressee_name'];
			if(!$old['r_zip']) $old['r_zip'] = $ord['addressee_zip'];
			if(!$old['r_addr1']) $old['r_addr1'] = $ord['addressee_addr1'];
			if(!$old['r_addr2']) $old['r_addr2'] = $ord['addressee_addr2'];
			if(!$old['r_phone']) $old['r_phone'] = $ord['addressee_phone'];
			if(!$old['r_cell']) $old['r_cell'] = $ord['addressee_cell'];
			if(!$old['r_message']) $old['r_message'] = $ord['dlv_memo'];

			if($old['r_message'] != $r_message) {
				$pdo->query("update $tbl[order_product] set r_message='$r_message' where no='$old[no]'");
			}

			if(
				($old['r_name'].$old['r_zip'].$old['r_addr1'].$old['r_addr2'].$old['r_phone'].$old['r_cell']) ==
				($r_name.$r_zip.$r_addr1.$r_addr2.$r_phone.$r_cell)
			) continue;

			$r = $pdo->query("update $tbl[order_product] set r_name='$r_name', r_zip='$r_zip', r_addr1='$r_addr1', r_addr2='$r_addr2', r_phone='$r_phone', r_cell='$r_cell', r_message='$r_message', addr_changed='Y' where no='$old[no]'");
			if($r) {
				$pdo->query("
					insert into $tbl[order_addr_log] (ono, opno, org_name, org_zip, org_addr1, org_addr2, org_phone, org_cell, new_name, new_zip, new_addr1, new_addr2, new_phone, new_cell, admin_id, reg_date)
					values
					('$prd[ono]', '$old[no]', '$old[r_name]', '$old[r_zip]', '$old[r_addr1]', '$old[r_addr2]', '$old[r_phone]', '$old[r_cell]', '$r_name', '$r_zip', '$r_addr1', '$r_addr2', '$r_phone', '$r_cell', '$admin[admin_id]', '$now')
				");

                addPrivacyViewLog(array(
                    'page_id' => 'order',
                    'page_type' => 'address_update',
                    'target_id' => $ord['ono'],
                    'target_cnt' => 1
                ));
			}
		}

		if($_POST['setDefault'] == 'Y') {
			$pdo->query("update $tbl[order] set addressee_name='$r_name', addressee_zip='$r_zip', addressee_addr1='$r_addr1', addressee_addr2='$r_addr2', addressee_phone='$r_phone', addressee_cell='$r_cell', dlv_memo='$r_message' where ono='$prd[ono]'");
		}

		if(empty($r_addr3) == false || empty($r_addr4) == false) {
			$pdo->query("update $tbl[order] set addressee_addr3='$r_addr3', addressee_addr4='$r_addr4' where ono='{$prd['ono']}'");
		}

		if(is_object($openmarket)) {
			$openmarket->setDeilvery();
		}

		if(is_object($erpListener)) {
			$erpListener->setOrder($prd['ono']);
		}

		javac("parent.$(parent.document).scrollTop(0); parent.location.reload();");
		exit;
	}

	if(!$prd['ono']) exit('존재하지 않는 주문상품입니다.');

	if(!$prd['r_name']) $prd['r_name'] = $ord['addressee_name'];
	if(!$prd['r_zip']) $prd['r_zip'] = $ord['addressee_zip'];
	if(!$prd['r_addr1']) $prd['r_addr1'] = $ord['addressee_addr1'];
	if(!$prd['r_addr2']) $prd['r_addr2'] = $ord['addressee_addr2'];
	if(!$prd['r_phone']) $prd['r_phone'] = $ord['addressee_phone'];
	if(!$prd['r_cell']) $prd['r_cell'] = $ord['addressee_cell'];
	if(!$prd['r_message']) $prd['r_message'] = $ord['dlv_memo'];

	$prd = array_map('stripslashes', $prd);

	$rows = 3;
	if($ord['nations']){
		$rows = 6;
		$nations = getCountryNameFromCode($ord['nations']);
	}

?>
<form id='orderProductAddrFrm' name='orderProductAddrFrm' method="post" action='./index.php' onsubmit='return orderProductChgAddr(this);' class='register'>
	<input type='hidden' name='body' value='order@order_prd_addr.exe' />
	<input type='hidden' name='exec' value='process' />
	<input type='hidden' name='no' value='<?=$no?>' />

	<table class="tbl_row">
		<colgroup>
			<col style='width: 120px;' />
		</colgroup>
		<caption><span class="p_color"><?=$prd['name']?></span> 상품의 배송지 변경</caption>
		<tr>
			<th scope="row">받는 분</th>
			<td class='lastChild'><input type="text" name="r_name" value="<?=$prd['r_name']?>" class="input"></td>
		</tr>
		<tr>
			<th scope="row" rowspan='<?=$rows?>'>주소</th>
			<td>
				<input type="text" name="r_zip" value="<?=$prd['r_zip']?>" class="input">
				<span class='box_btn_s'><input type="button" name="" value="우편번호" onClick="zipSearchM('orderProductAddrFrm', 'r_zip','r_addr1','r_addr2')"></span>
			</td>
		</tr>
		<?if(empty($nations) == false) {?>
		<tr>
			<td><?=$nations?></td>
		</tr>
		<?}?>
		<tr>
			<td><input type="text" name="r_addr1" value="<?=$prd['r_addr1']?>" class="input" size="50" /></td>
		</tr>
		<?if(empty($nations) == false) {?>
		<tr>
			<td><input type="text" name="r_addr3" value="<?=$ord['addressee_addr3']?>" class="input" size="50" /></td>
		</tr>
		<tr>
			<td><input type="text" name="r_addr4" value="<?=$ord['addressee_addr4']?>" class="input" size="50" /></td>
		</tr>
		<?}?>
		<tr>
			<td><input type="text" name="r_addr2" value="<?=$prd['r_addr2']?>" class="input" size="70"></td>
		</tr>
		<tr>
			<th scope="row">전화번호</th>
			<td class='lastChild'><input type="text" name="r_phone" value="<?=$prd['r_phone']?>" class="input" size="20"></td>
		</tr>
		<tr>
			<th scope="row">휴대폰</th>
			<td class='lastChild'>
				<input type="text" name="r_cell" value="<?=$prd['r_cell']?>" class="input" size="20">
			</td>
		</tr>
		<tr>
			<th scope="row">주문메시지</th>
			<td class='lastChild'>
				<textarea name="r_message" class='txta' cols='100' rows='5'><?=$prd['r_message']?></textarea>
			</td>
		</tr>
		<?if($admin['level'] < 4) {?>
		<tr>
			<th scope="row">기본주소</th>
			<td>
				<label>
					<input type="checkbox" name="setDefault" value="Y" checked /> 주문서의 기본 배송정보를 현재 정보로 수정
					<span class="explain">(선택되지 않은 나머지 상품은 주소가 변경되지 않습니다.)</span>
				</label>
			</td>
		</tr>
		<?}?>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인" /></span>
		<span class="box_btn gray"><input type="button" value="닫기" onclick="$('#repayDetail').slideUp('fast').html(''); parent.$(parent.document).scrollTop(0);" /></span>
	</div>
</form>
<script type="text/javascript">
	function orderProductChgAddr(f) {
		if(confirm('주문상품의 배송주소를 변경하시겠습니까?')) {
			f.target = hid_frame;
			return true;
		}
		return false;
	}
</script>