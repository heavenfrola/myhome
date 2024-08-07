<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  쇼핑몰관리권한 설정
	' +----------------------------------------------------------------------------------------------+*/

	adminCheck(2);

	function cAuth($a) {
		global $data;
		$ck=(@strchr($data[auth], $a)) ? " checked" : "";
		$re="<input type=\"checkbox\" name=\"auth_".$a."[".$data[no]."]\" value=\"Y\"".$ck." class='allck_$data[no]'>";
		return $re;
	}

?>
<form name="authFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="intra@staffs_auth.exe">
	<div class="box_title first">
		<h2 class="title">쇼핑몰관리권한 설정</h2>
	</div>
	<table class="tbl_col">
		<caption class="hidden">쇼핑몰관리권한 설정</caption>
		<thead>
			<tr>
				<th scope="col"><?=$_mng_group[3]?>정보</th>
				<th scope="col">ALL</td>
				<th scope="col">관리자홈</td>
				<?php
					foreach($menudata->big as $key => $val) {
						if($val->attr('pgcode') == '11000') continue; // 인트라넷제외
						$bname = str_replace("관리", "", $val->attr('name'));
					?>
				<th scope="col"><?=$bname?></th>
				<?}?>
				<?php
					$res = $pdo->iterator("select * from `$tbl[mng]` where `level`='3' order by `name`");
                    foreach ($res as $data) {
				?>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<input type="hidden" name="mng_no[<?=$data[no]?>]" value="<?=$data[no]?>">
					<a href="./?body=intra@staffs_edt&no=<?=$data[no]?>"><?=$data[name]?> <font style="font-size:8pt;">(<?=$data[admin_id]?>)</font></a>
				</td>
				<td><input type="checkbox" name="allck[<?=$data[no]?>]" onclick="allCk(this, <?=$data[no]?>)" class="allck_<?=$data[no]?>"></td>
				<td><?=cAuth("main")?></td>
				<?
					foreach($menudata->big as $key => $val) {
					if($val->attr('pgcode') == '11000') continue; // 인트라넷제외
				?>
				<td>
					<?=cAuth($val->attr('category'))?>
					<?
						if(0 && ($val->attr('pgcode') == '12000' || $val->attr('pgcode') == '13000')) echo "&nbsp;";
						else{
					?>
					<span class="box_btn_s"><input type="button" value="설정" onClick="authPop('<?=$data[no]?>','<?=$val->attr('pgcode')?>');"></span>
					<?}?>
				</td>
				<?}?>
			</tr>
			<?
			}
			unset($data);
			?>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="설정완료"></span>
	</div>
</form>

<script language="JavaScript">
	function allCk(w, no) {
		$('.allck_'+no).prop('checked', w.checked);
	}

	function authPop(no, mode){
		nurl='./?body=intra@staffs_auth.frm&no='+no+'&mode='+mode;
		window.open(nurl,'authStaffs','top=10,left=10,width=10px,height=10px,status=no,toolbars=no,scrollbars=no');
	}
</script>