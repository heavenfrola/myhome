<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  IP 차단 설정
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_config/tbl_schema.php';
	if(!isTable($tbl['intercept_ip'])) {
		$pdo->query($tbl_schema[intercept_ip]);
	}


	if(!$mode)$mode = 'U'; //U :user , A :admin
	if($mode == 'U') {
		$admY = "";
		$intercept_adm_yn = "N";
		$intercept_use = $cfg['intercept_use'];
		$intercept_msg = $cfg['intercept_msg'];
	} else {
		$admY = "_adm";
		$intercept_adm_yn = "Y";
		$intercept_use = $cfg['intercept_use_adm'];
		$intercept_msg = $cfg['intercept_msg_adm'];
	}

	$sql = "select * from $tbl[intercept_ip] where intercept_adm_yn = '$intercept_adm_yn' ORDER BY intercept_ip";
?>

<form name="configFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onSubmit="return interceptConfigChk(this);"  >
	<input type="hidden" name="body" value="config@config.exe">

	<div class="box_title first">
		<h2 class="title">IP 차단 사용 유무</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">IP 차단 사용 유무</caption>
		<colgroup>
			<col style="width:15%">
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th rowspan="2" scope="row">IP 차단 사용</th>
			<td colspan="2">
				<label class="p_cursor"><input type="radio" <?=checked($mode,'U')?> onclick="location.href='?body=<?=$body?>&mode=U';"> 사용자</label>
				<label class="p_cursor"><input type="radio" <?=checked($mode,'A')?> onclick="location.href='?body=<?=$body?>&mode=A';"> 관리자</label>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<label class="p_cursor"><input type="radio" id="intercept_use_y" name="intercept_use<?=$admY?>" value="Y" <?=checked($intercept_use ,'Y')?>> 사용</label>
				<label class="p_cursor"><input type="radio" name="intercept_use<?=$admY?>" value="N" <?=checked($intercept_use ,'N').checked($intercept_use,"")?>> 사용안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">IP 차단 메세지</th>
			<td colspan="2">
				<input type="text" id="intercept_msg_id" name="intercept_msg<?=$admY?>" maxlength="50" value="<?=inputText($intercept_msg)?>" class="input input_full">
			</td>
		</tr>
	</table>
	<div class="box_middle2 left">
		<ul class="list_msg">
			<li>IP 차단 메세지 : 입력된 차단 메세지를 보냅니다. (예 : 해당 IP는 접근 불가능한 IP입니다. 관리자에게 문의하십시오)</li>
			<li>사용자 메세지 예 : 해당 IP는 접근 불가능한 IP입니다. 관리자에게 문의하십시오.</li>
			<li>관리자 메세지 예 : 해당 IP는 관리자 접근이 불가능합니다.</li>
		</ul>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue" style="margin-top:10px;"><input type="submit" value="확인"></span>
	</div>
</form>
<form name="interceptFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onSubmit="return addInterceptIp();" >
	<input type="hidden" name="body" value="config@intercept_ip.exe">
	<input type="hidden" name="mode" value="<?=$mode?>">

	<div class="box_title">
		<h2 class="title">차단 IP 추가</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">IP 차단 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<td><input type="text" name="intercept_ip_no" maxlength="15" value="" class="input"></td>
			<td><span class="box_btn_s"><input type="button" onclick="addInterceptIp();" value="IP 추가하기"></span></td>
		</tr>
	</table>
	<div class="box_middle2">
		<select id="select" class="select_n" name="intercept_ip_list" size="5" style="width:100%;">
			<?php
				$res = $pdo->iterator($sql);
                foreach ($res as $data) {
					echo "<option value='".inputText($data['intercept_ip'])."'>".$data['intercept_ip']."</option>";
				}
			?>
		</select>
		<div id="dlv_btns" style="padding-top:10px;">
			<span class="box_btn_s gray"><input type="button" value="삭제" onclick="delInterceptIp();"></span>
		</div>
	</div>
	<div class="box_middle2 left">
		<ul class="list_msg">
			<li>IP 차단 사용 : 입력된 IP의 컴퓨터는 접근할 수 없습니다.(IP는 0.0.0.0 ~ 255.255.255.255 까지 입력가능합니다)</li>
			<li>255.255.255.* 도 입력 가능합니다. (단, C CLASS 까지만 이용가능합니다. 255.255.255.* 입력시 255.255.255.0 ~ 255.255.255.255 IP 대역 모두 차단)</li>
		</ul>
	</div>
</form>


<script type="text/javascript">
	function chkIp(ip) {
		var _tmpIp = true;
		var _arrIp = ip.split(".");
		if(_arrIp.length != 4)return false;
		for( var i = 0; i < _arrIp.length; i++ ) {
			if( (i==3&&_arrIp[i] == "*") || (parseInt(_arrIp[i],10) >= 0 && parseInt(_arrIp[i],10) <= 255) ) {
				continue;
			} else {
				_tmpIp = false;
			}
		}

		return _tmpIp;
	}

	function addInterceptIp() {
		var _addIpNo = document.interceptFrm.intercept_ip_no;
		if(chkIp(_addIpNo.value)) {
			//중복 IP 체크
			var _sel = document.interceptFrm.intercept_ip_list;
			for(i=0; i < _sel.options.length; i++) {
				if ( _sel.options[i].value == _addIpNo.value) {
					alert("해당 IP는 이미 리스트에 등록되어있습니다.");
					return false;
				}
			}
			document.interceptFrm.submit();
			return false;
		} else {
			alert("올바른 IP를 입력해주세요.");
			_addIpNo.focus();
			return false;
		};
	}

	function delInterceptIp() {
		var _depIp = document.interceptFrm.intercept_ip_list.value;
		if(confirm(_depIp+'가 삭제됩니다.\n정말 삭제하시겠습니까?')) {
			$.post('./index.php?body=config@intercept_ip.exe', {'intercept_ip_no':_depIp, 'exec':'remove', 'mode' : '<?=$mode?>'}, function(r) {
				location.reload();
			});
		}

	}

	function interceptConfigChk(f) {
		if($('#intercept_use_y').is(":checked")) {
			if(!checkBlank(f.intercept_msg<?=$admY?>,'IP 차단 메세지를 입력해주세요.')) return false;
		}

		return true;
	}
</script>
