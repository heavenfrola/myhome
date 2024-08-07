<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  재입고 알림 설정
	' +----------------------------------------------------------------------------------------------+*/

	if(!$cfg['notify_restock_use']) $cfg['notify_restock_use'] = "N"; // Y/N
	if(!$cfg['notify_restock_target']) $cfg['notify_restock_target'] = 1; // 알림대상 1:전체 2:회원 3:비회원
	if(!$cfg['notify_restock_type_l']) $cfg['notify_restock_type_l'] = "Y"; // 품절방식 한정 Y/N
	if(!$cfg['notify_restock_type_f']) $cfg['notify_restock_type_f'] = "Y"; // 품절방식 강제품절 Y/N
	if(!$cfg['notify_restock_min_qty']) $cfg['notify_restock_min_qty'] = "5"; // 입고알림 기준 수량(개)

	$_notify_restock_expire = array(
		'' => '제한없음',
		'-1 days' => '1일',
		'-2 days' => '2일',
		'-3 days' => '3일',
		'-4 days' => '4일',
		'-5 days' => '5일',
		'-6 days' => '6일',
		'-7 days' => '7일',
		'-8 days' => '8일',
		'-9 days' => '9일',
		'-10 days' => '10일',
		'-11 days' => '11일',
		'-12 days' => '12일',
		'-13 days' => '13일',
		'-14 days' => '14일',
	);

	// 실행파일 생성
	if(file_exists($root_dir.'/mypage/notify_restock.php') == false) {
		$tmp_file = $root_dir.'/_data/tmp';
		$fp = fopen($tmp_file, 'w');
		fwrite($fp, "<?PHP\n\n\tinclude '../_config/set.php';\n\tinclude \$engine_dir.'/_engine/mypage/notify_restock.php';\n\n?>");
		fclose($fp);

		include_once $engine_dir."/_engine/include/img_ftp.lib.php";
		ftpUploadFile('/mypage', array(
			'name' => 'notify_restock.php',
			'tmp_name' => $tmp_file,
			'size' => strlen($tmp_file)
		), 'php');
		unlink($tmp_file);
	}

?>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="notify_restock_config">
	<div class="box_title first">
		<h2 class="title">재입고 알림 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">재입고 알림 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">사용여부</th>
			<td>
				<input type="radio" name="notify_restock_use" id="notify_restock_use_Y" value="Y" <?=checked($cfg['notify_restock_use'], "Y")?>> <label for="notify_restock_use_Y" class="p_cursor">사용함</label>
				<input type="radio" name="notify_restock_use" id="notify_restock_use_N" value="N" <?=checked($cfg['notify_restock_use'], "N")?>> <label for="notify_restock_use_N" class="p_cursor">사용안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">대상</th>
			<td>
				<input type="radio" name="notify_restock_target" id="notify_restock_target_1" value="1" <?=checked($cfg['notify_restock_target'],1)?>> <label for="notify_restock_target_1" class="p_cursor">전체</label>
				<input type="radio" name="notify_restock_target" id="notify_restock_target_2" value="2" <?=checked($cfg['notify_restock_target'],2)?>> <label for="notify_restock_target_2" class="p_cursor">회원</label>
				<input type="radio" name="notify_restock_target" id="notify_restock_target_3" value="3" <?=checked($cfg['notify_restock_target'],3)?>> <label for="notify_restock_target_3" class="p_cursor">비회원</label>
			</td>
		</tr>
		<tr>
			<th scope="row">허용 품절방식</th>
			<td>
				<input type="checkbox" name="notify_restock_type_l" id="notify_restock_type_l" value="Y" <?=checked($cfg['notify_restock_type_l'], "Y")?>> <label for="notify_restock_type_l" class="p_cursor">한정</label>
				<input type="checkbox" name="notify_restock_type_f" id="notify_restock_type_f" value="Y" <?=checked($cfg['notify_restock_type_f'], "Y")?>> <label for="notify_restock_type_f" class="p_cursor">강제품절</label><br><br>
				<ul class="list_info">
					<li>한정 :남은 재고수량이 0개 이하일 경우 예약가능</li>
					<li>강제품절 : 남은 재고수량과 상관없이 예약가능</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">재입고 알림 기준 수량</th>
			<td>
				<input type="text" name="notify_restock_min_qty" id="notify_restock_min_qty" value="<?=$cfg['notify_restock_min_qty']?>" class="input" size="5"> <label for="notify_restock_min_qty" class="p_cursor">개 이상</label><br><br>
				<ul class="list_info">
					<li>품절방식이 강제품절인 재입고 알림 신청의 경우 '재입고 알림 기준 수량'을 충족하는 조건에서 한정 또는 무제한으로 품절방식이 변경될 경우 알림이 발송됩니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">알림기간만료 설정</th>
			<td>
				<?=selectArray($_notify_restock_expire, 'notify_restock_expire', false, '', $cfg['notify_restock_expire'])?>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>