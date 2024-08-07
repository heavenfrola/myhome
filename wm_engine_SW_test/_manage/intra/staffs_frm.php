<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  사원 등록
	' +----------------------------------------------------------------------------------------------+*/

	if($data[birth]){
		list($birth_y, $birth_m, $birth_d)=explode("-", $data[birth]);
	}

	$is_wm = file_exists($engine_dir.'/_engine/include/account/ssoLogin.inc.php');
	if($is_wm && $data['ver'] != 2) {
		$old_id = $data['admin_id'];
		/*
		if($data['level'] == 2) $data['admin_id'] = $wec->get(103);
		else $data['admin_id'] = '';
		*/
	}
	if(!$data['level']) $data['level'] = 3;

	if($cfg['use_partner_shop'] != 'Y') {
		unset($_mng_levels[4]);
	}

	if($cfg['use_partner_shop'] == 'Y') {
		$_mng_partner = array();
		$_style_none = "";
		if($data['level'] != 4)$_style_none = "display:none;";
		$p_sql = $pdo->iterator("select no, corporate_name from $tbl[partner_shop] where `stat` between 2 and 4 order by corporate_name asc");
        foreach ($p_sql as $pdata) {
			$_mng_partner[$pdata[no]] = $pdata[corporate_name];
		}
	}
?>
<table class="tbl_row">
	<caption class="hidden">정보 등록 및 수정</caption>
	<colgroup>
		<col style="width:15%">
	</colgroup>
	<?if($cfg['staffs_access_limit']=="Y") {?>
	<tr>
		<th scope="row">계정잠금</th>
		<td>
			<input type="checkbox" name="access_lock" value="Y" <?=checked($data['access_lock'] ,'Y')?>>
		</td>
	</tr>
	<?}?>
	<tr>
		<th scope="row"><strong>아이디</strong></th>
		<td>
				<?if($body == 'intra@my_info') {?>
					<?=$data['admin_id']?>
				<?} else {?>
                    <?php if ($no) { ?>
                    <?=$data['admin_id']?>
                    <input type="hidden" name="admin_id" value="<?=$data['admin_id']?>">
                    <?} else if($is_wm == true) {?>
					<input type="text" name="admin_id" value="<?=$data['admin_id']?>" class="input pointer" size="25" readonly onclick="this.blur(); searchWisaID(<?=$data['level']?>);" >
					<span class="box_btn_s"><input type="button" id="xbt" value="검색" onclick="searchWisaID(<?=$data['level']?>)"></span>
					<ul class="list_msg">
						<li>등록할 사원의 위사(<a href="http://wisa.co.kr" target="_blank">www.wisa.co.kr</a>)아이디를 검색 버튼을 눌러 검색하세요.</li>
						<li>
							위사 회원이 아닐 경우 사원등록을 할 수 없습니다.
							<a href="http://redirect.wisa.co.kr/join" target="_blank">위사회원가입</a>
						</li>
						<li>등록된 사원은 위사에 로그인하여 <span class="p_color">쇼핑몰바로가기</span>를 통해 쇼핑몰 관리자로 접속합니다.</li>
					</dl>
					<?} else {?>
					<input type="text" name="admin_id" value="<?=$data['admin_id']?>" class="input">
					<?}?>
				<?}?>
		</td>
	</tr>
	<?if(!$is_wm) {?>
	<tr>
		<th scope="row"><strong>패스워드</strong></th>
		<td><input type="password" name="password" class="input" size="20"></td>
	</tr>
	<?}?>
	<?if($mng){ // 관리자가 수정할 경우?>
	<tr>
		<th scope="row"><strong>최고관리자<br>비밀번호</strong></th>
		<td><input type="password" name="root_pwd" class="input"> <span class="explain">(보안상 확인 절차)</span></td>
	</tr>
	<?}?>
	<tr>
		<th scope="row"><strong>성명</strong></th>
		<td><input type="text" name="name" class="input" value="<?=$data[name]?>"></td>
	</tr>
	<?if($admin['level'] < 3 && $admin['level'] > 0) {?>
	<tr>
		<th scope="row">관리자등급</th>
		<td>
			<label class="p_cursor"><input type="radio" name="level" value="2" <?=checked($data['level'],"2")?> onclick="chgLevel(this);"> 최고관리자</label>
			<label class="p_cursor"><input type="radio" name="level" value="3" <?=checked($data['level'],"3")?> onclick="chgLevel(this);"> 부관리자</label>
			<label class="p_cursor"><input type="radio" name="level" value="4" <?=checked($data['level'],"4")?> onclick="chgLevel(this);"> 입점사관리자</label>
			<?if($data['no']) {?>
			<div class="list_info">
				<?if($data['level']==2) {?>
				<p>최고관리자는 입점사관리자로 변경할 수 없습니다.</p>
				<?}?>
				<?if($data['level']==4 && $data['partner_no']) {?>
				<p>'<?=$_mng_partner[$data['partner_no']]?>' 입점사관리자로 지정된 관리자입니다. 입점사관리자 해제 후 최고관리자 또는 부관리자로 변경할 수 있습니다.</p>
				<?}?>
			</div>
			<?}?>
		</td>
	</tr>
	<?
	if($cfg['use_partner_shop'] == 'Y') {
	?>
	<tr id="ptn_search_tr" style="<?=$_style_none?>">
		<th scope="row">입점사</th>
		<td>
			<?=selectArray($_mng_partner, 'partner_no', null, "=====", $data['partner_no'])?>
			<span class="box_btn_s blue"><input type="button" value="입점사 검색" onclick="ptn_search.open();"></span>
		</td>
	</tr>
	<?}?>
	<?}?>
	<?if($mng || $admin[level] == 1 || $admin[level] == 2){ // 관리자가 수정할 경우?>
	<tr>
		<th scope="row">소속</th>
		<td>
			<select name="team">
				<option value="">===== 미지정 =====</option>
				<?
						if($data[team1] || $data[team2]){
							$data[team1]=$data[team1] ? $data[team1] : "0";
							$data[team2]=$data[team2] ? $data[team2] : "0";
							$data[team]=$data[team1]."/".$data[team2];
						}
						foreach($_team1 as $key=>$val){
							$_selected=($data[team] == $key."/0") ? " selected" : "";
							echo "<option value=\"$key/0\" $_selected>$val</option>";
							foreach($_team as $key2=>$val2){
								if($_team[$key2][ref] == $key){
									$_selected=($data[team] == $key."/".$key2) ? " selected" : "";
									echo "<option value=\"$key/$key2\" style=\"color:#779D4D;\" $_selected> → ".$_team[$key2][name]."</option>";
								}
							}
						}
				?>
			</select>
			<?if(!$mng) echo " <span class=\"explain\">(관리자일 경우 자신의 소속과 직급을 수정할 수 있습니다)</span>";?>
		</td>
	</tr>
	<tr>
		<th scope="row">직급</th>
		<td>
			<?
				$_position=array("사원", "주임", "계장", "대리", "과장", "차장", "부장", "이사", "상무", "전무", "부사장", "사장", "부회장", "회장");
				echo selectArray($_position, "position_select", 1, "=====", $data[position], "this.form.position.value=this.value;");
			?>
			<input type="text" name="position" class="input" value="<?=$data[position]?>" size="9">
		</td>
	</tr>
	<?
		} else {
			$data[team]=$data[team2] ? $data[team2] : $data[team1];
			$teamname=($data[team]) ? $_team[$data[team]][name] : "-";
			$position=($data[position]) ? $data[position] : "-";
	?>
	<tr>
		<th scope="row">소속</th>
		<td><?=$teamname?></td>
	</tr>
	<tr>
		<th scope="row">직급</th>
		<td><?=$position?></td>
	</tr>
	<?
		}
		unset($_team, $_team1);
	?>
	<tr>
		<th scope="row">전화번호</th>
		<td><input type="text" name="phone" class="input" value="<?=$data[phone]?>"></td>
	</tr>
	<tr>
		<th scope="row"><strong>휴대폰</strong></th>
		<td><input type="text" name="cell" class="input" value="<?=$data[cell]?>"></td>
	</tr>
	<tr>
		<th scope="row"><strong>이메일</strong></th>
		<td><input type="text" name="email" class="input" value="<?=$data[email]?>"></td>
	</tr>
	<tr>
		<th scope="row">주소</th>
		<td><input type="text" name="address" class="input input_full" value="<?=$data[address]?>"></td>
	</tr>
	<tr>
		<th scope="row">생년월일</td>
		<td>
			<select name="birth_y">
				<option value="">====</option>
				<?
					for($ii=(date("Y")-15); $ii>1950; $ii--){
						$selected=($birth_y == $ii) ? " selected" : "";
						echo "<option value='$ii'$selected>$ii</option>";
					}
				?>
			</select> 년
			<select name="birth_m">
				<option value="">==</option>
				<?
					for($ii=1; $ii<=12; $ii++){
						$ii=($ii<10) ? "0".$ii : $ii;
						$selected=($birth_m == $ii) ? " selected" : "";
						echo "<option value='$ii'$selected>$ii</option>";
					}
				?>
			</select> 월
			<select name="birth_d">
				<option value="">==</option>
				<?
					for($ii=1; $ii<=31; $ii++){
						$ii=($ii<10) ? "0".$ii : $ii;
						$selected=($birth_d == $ii) ? " selected" : "";
						echo "<option value='$ii'$selected>$ii</option>";
					}
				?>
			</select> 일
		</td>
	</tr>
</table>
<div class="box_bottom">
	<?if(!$data[no] || !$mng){?>
	<span class="box_btn blue"><input type="submit" value="확인"></span>
	<?}else{?>
	<span class="box_btn blue"><input type="submit" value="수정"></span>
	<span class="box_btn"><input type="button" value="취소" onclick="location.href='./?body=<?=$body?>'"></span>
	<?}?>
</div>


<script language="JavaScript">
	$(document).ready(function() {
		var _edit = '<?=$data[no]?>';
		var _level = '<?=$data[level]?>';
		chgAccess(_edit, _level);
	});

	//입점파트너 레이어 추가
	var ptn_search = new layerWindow('product@product_join_shop.inc.exe');
	ptn_search.psel = function(no,stat) {
		if(stat == "신청") {
			alert("선택한 입점파트너는 ["+stat+"] 상태입니다.");
			return false;
		}
		document.frm.partner_no.value = no;
		ptn_search.close();
	}


	function chgLevel(that) {
		if(that.value == "4") {
			document.getElementById('ptn_search_tr').style.display = "";
		} else {
			document.getElementById('ptn_search_tr').style.display = "none";
		}
	}

	function chgAccess(edit, level) {
		var prn_no = '<?=$data[partner_no]?>';
		if(!edit) return;
		if(level == "2") {
			$("input:radio[name='level'][value='4']").prop("disabled", true);
		}else {
			$("input:radio[name='level'][value='4']").prop("disabled", false);
		}
		if(level == "4" && prn_no>0) {
			$("input:radio[name='level'][value='2']").prop("disabled", true);
			$("input:radio[name='level'][value='3']").prop("disabled", true);
		}else {
			$("input:radio[name='level'][value='2']").prop("disabled", false);
			$("input:radio[name='level'][value='3']").prop("disabled", false);
		}
	}
</script>