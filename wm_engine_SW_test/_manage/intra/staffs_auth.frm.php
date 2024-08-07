<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  부관리자 권한 세부설정
	' +----------------------------------------------------------------------------------------------+*/

	$_big_code = array();
	foreach($menudata->big as $key => $val) {
		$_big_code[$val->attr('category')] = $val->attr('pgcode');
	}

	$no = numberOnly($_GET['no']);
	$mode = numberOnly($_GET['mode']);
	adminCheck(2);
	$data = $pdo->assoc("select * from `$tbl[mng]` where `no`='$no' limit 1");
	if(!$data[no]) msg("존재하지 않는 정보입니다", "close");
	if(!$mode) $mode=1000;
	$code_name=@array_search($mode, $_big_code);
	if(!@strchr($data[auth], "@".$code_name) && $admin['level'] > 2) msg("해당 관리자는 해당 메뉴에 접근이 허용되지 않습니다", "close");
	$chk="all_menu";
	if(@strchr($data[auth], "@auth_detail")){
		$chk=$pdo->row("select `$code_name` from `$tbl[mng_auth]` where `admin_no`='$data[no]' limit 1");
		if(!$chk) $chk="all_menu";
	}

?>
<style type="text/css" title="">
body {background:none;}
</style>
<form name="frm" method="post" action="./" target="hidden<?=$now?>" onSubmit="return checkFrm(this)">
	<input type="hidden" name="body" value="intra@staffs_auth.exe">
	<input type="hidden" name="no" value="<?=$data[no]?>">
	<input type="hidden" name="exec" value="auth_detail">
	<input type="hidden" name="code_name" value="<?=$code_name?>">
	<div style="overflow:auto; width:400px; height:500px;">
		<table class="tbl_mini full">
			<caption style="padding:5px; background:#eee; font-weight:bold;">부관리자 권한 세부 설정 - <?=$data[name]?> (<?=$data[admin_id]?>)</caption>
			<?
				if($code_name == "order") {// 카드 취소 권한
			?>
			<tr>
				<td class="left">
					<input type="checkbox" name="auth_cardcc" id="auth_cardcc" value="Y" <?=(@strchr($data[auth], "cardcc")) ? " checked" : "";?>>
					<label for="auth_cardcc" class="p_color2 p_cursor">카드 취소</label>
					<span class="explain">(PG 사 결제 취소 권한을 부여합니다)</span>
				</td>
			</tr>
			<tr>
				<td class="left">
                    <label>
    					<input type="checkbox" name="auth_orderexcel" id="auth_orderexcel" value="Y" <?=(@strchr($data['auth'], 'auth_orderexcel')) ? " checked" : '';?>> 주문엑셀 다운로드
                    </label>
				</td>
			</tr>
			<?
				}
				if($code_name == "product") {// wingDisk 권한
			?>
			<tr>
				<td class="left">
					<input type="checkbox" name="auth_wftp" id="auth_wftp" value="Y" <?=(@strchr($data[auth], "wftp")) ? " checked" : "";?>>
					<label for="auth_wftp" class="p_color2 p_cursor">윙Disk</label>
					<span class="explain">(윙디스크 접근 권한을 부여합니다)</span>
				</td>
			</tr>
			<?
				}

                if ($code_name == 'member') {
            ?>
			<tr>
				<td class="left">
                    <label>
    					<input type="checkbox" name="auth_memberexcel" id="auth_memberexcel" value="Y" <?=(@strchr($data['auth'], 'auth_memberexcel')) ? " checked" : '';?>> 회원엑셀 다운로드
                    </label>
				</td>
			</tr>
            <?
                }
				$mcodeck = array();
				if(is_object($menudata->big)) {
					foreach($menudata->big as $bkey=>$bval) {
						if ($bval->attr('pgcode') != $mode) continue;
						$dlink = str_replace('body=', '', $bval->attr('link'));
						if(!is_object($bval)) continue;
						foreach($bval->mid as $mkey => $mval) {
			?>
			<tr>
				<th class="left"><?=$mval->attr('name')?></th>
			</tr>
			<?
				foreach($mval->small as $skey => $sval) {
				$_hidden = $sval->val('mcode');
				if($sval->val('if')) if(!eval("return (".$sval->val('if').");")) $_hidden = 'Y'; // 메뉴 출력 조건식
				if($_hidden == "Y" || $sval->val('sc') == "Y" || in_array($sval->val('mcode'),$mcodeck)) continue;
				$mcodeck[] = $sval->val('mcode');
			?>
			<tr>
				<td class="left">
					<input type="checkbox" name="auth[]" id="auth<?=$sval->val('pgcode')?>" value="<?=$sval->val('mcode')?>" <?=checked($chk,"all_menu").checked(1,substr_count($chk,"@".$sval->val('mcode')."@"))?>>
					<label for="auth<?=$sval->val('pgcode')?>" class="p_cursor"><?=$sval->val('name')?></label>
				</td>
			</tr>
			<?
				}
						}
					}
				}
			?>
		</table>
	</div>
	<div class="pop_bottom">
		<span class="box_btn_s blue"><input type="submit" value="설정완료"></span>
		<span class="box_btn_s gray"><input type="button" value="닫기" onclick="window.close();"></span>
	</div>
</form>

<script language="JavaScript">
	function checkFrm(f){
		chk=0;
		for(ii=0; ii<f['auth[]'].length; ii++){
			if(f['auth[]'][ii].checked == true) chk++;
		}
		if(chk < 1){
			alert('모든 하부 메뉴에 접근을 차단하시려면 메뉴 차단을 사용하시기 바랍니다');
			return false;
		}
	}

	window.onload=function (){
		selfResize();
	}
	this.focus();
</script>