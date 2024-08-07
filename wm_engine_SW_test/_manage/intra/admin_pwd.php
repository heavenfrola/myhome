<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  관리자 아이디 변경
	' +----------------------------------------------------------------------------------------------+*/

	adminCheck(2);

	$admin_id2 = $pdo->assoc("select `admin_id`,`no` from `$tbl[mng]` where `level`=2");

	$new_id = $wec->get(103);
	if($new_id -= $admin_id2['admin_id'] && 0) {

?>
	<div class="box_title first">
		<h2 class="title">관리자 아이디 변경</h2>
	</div>
	<div class="box_middle">
		<ul class="list_msg left">
			<li>구 관리자 아이디를 새로운 로그인방식에 맞게 변경합니다.</li>
			<li>기존에 사용하시던 'admin' 아이디가 쇼핑몰을 만들때 사용하셨던 위사(wisa.co.kr) 아이디로 변경되며, 새로운 관리자 로그인 방식으로 관리자에 접속하실수 있습니다.</li>
		</ul>
	</div>
	<form name="bankFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onSubmit="return checkAdminPwd(this)">
		<input type="hidden" name="body" value="intra@staffs_edt.exe">
		<input type="hidden" name="no" value="<?=$admin_id2['no']?>">
		<input type="hidden" name="exec" value="id">
		<table class="tbl_row">
			<caption class="hidden">관리자 아이디 변경</caption>
			<colgroup>
				<col style="width:15%">
			</colgroup>
			<tr>
				<th>관리자 아이디</th>
				<td><input type="text" name="mng_id" size="12" class="input" value="<?=$new_id?>" readonly> 로 변경</td>
			</tr>
		</table>
		<div class="box_bottom">
			<span class="box_btn blue"><input type="submit" value="확인"></span>
		</div>
	</form>
	<script language="JavaScript">
		function admin_id_chg(f){
			var admin_id2_1=document.getElementById('admin_id2_1');
			var admin_id2_2=document.getElementById('admin_id2_2');
			f.admin_id2.value='<?=$admin_id2?>';
			admin_id2_1.style.display='none';
			admin_id2_2.style.display='block';
		}
	</script>
<?} else {?>
	<div class="box_title first">
		<h2 class="title">관리자 아이디 변경</h2>
	</div>
	<div class="box_bottom top_line">
		<ul class="list_msg left">
			<li>구 관리자 아이디를 새로운 로그인방식에 맞게 변경합니다.</li>
			<li>관리자 아이디가 정상적으로 등록되어있으므로 <span class="p_color2">변경하실 필요가 없습니다</span></li>
		</ul>
	</div>
<?}?>