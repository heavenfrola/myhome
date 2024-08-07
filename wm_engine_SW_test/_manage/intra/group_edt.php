<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  조직도 관리
	' +----------------------------------------------------------------------------------------------+*/

	adminCheck(2);
	$sql = $pdo->iterator("select * from `$tbl[intra_group]` where `ref`=0 order by `name`");
	$gname=array();
    foreach ($sql as $arr) {
		$gname[$arr[no]]=$arr[name];
	}

?>
<form name="groupFrm" method="post" action="<?=$PHP_SELF?>" target="hidden<?=$now?>" onSubmit="return checkGrFrm(this)">
	<input type="hidden" name="body" value="intra@group_edt.exe">
	<input type="hidden" name="exec" value="">
	<input type="hidden" name="no" value="">
	<input type="hidden" name="level" value="">
	<div class="box_title first">
		<h2 class="title">조직도관리</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">조직도 관리</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">그룹생성</td>
			<td>
				<?=selectArray($gname, "ref", 2, "================")?>
				<input type="text" name="gname" class="input">
				<span class="box_btn_s blue"><input type="submit" name="sbtn" value="생성"></span>
			</td>
		</tr>
	</table>
	<div class="box_title">
		<h2 class="title">생성된 그룹</h2>
	</div>
	<table class="tbl_mini full">
		<caption class="hidden">생성된 그룹</caption>
		<colgroup>
			<col>
			<col style="width:150px">
			<col style="width:100px">
			<col style="width:100px">
		</colgroup>
		<?php
		$line=" style=\"border-top:1 dotted #ECE6FF;\"";
		$sub_icon="→ ";
		$staffq="select count(*) from `$tbl[mng]` where `level`!=1";
		foreach($gname as $key=>$val){
			$staff_num=$pdo->row($staffq." and `team1`='$key'");
		?>
		<tr>
			<th scope="row" style="padding-left:15px; text-align:left !important;">
				<span id="gname<?=$key?>"><?=$val?></span>
				<?=$staff_num ? " <a href=\"./?body=intra@view_staffs&no1=$key&no2=0\" class=\"small\">[".$staff_num."]</a>" : "";?>
			</th>
			<td><span class="box_btn_s"><input type="button" value="하위그룹생성" onclick="subGrp(<?=$key?>)"></span></td>
			<td><span class="box_btn_s"><input type="button" value="수정" onclick="modGrp(<?=$key?>)"></span></td>
			<td><span class="box_btn_s gray"><input type="button" value="삭제" onclick="delGrp(<?=$key?>)"></span></td>
		</tr>
		<?php
			$sql2 = $pdo->iterator("select * from `$tbl[intra_group]` where `ref`='$key' order by `name`");
            foreach ($sql2 as $arr2) {
				$staff_num=$pdo->row($staffq." and `team1`='$key' and `team2`='$arr2[no]'");
		?>
		<tr>
			<th scope="rowgroup" colspan="2" style="padding-left:15px; text-align:left !important;"><?=$sub_icon?><span id="gname<?=$arr2[no]?>"><?=$arr2[name]?></span><?=$staff_num ? " <a href=\"./?body=intra@view_staffs&&no1=$key&no2=$arr2[no]\" class=\"small\">[".$staff_num."]</a>" : "";?></th>
			<td><span class="box_btn_s"><a href="javascript:;" onclick="modGrp(<?=$arr2[no]?>)">수정</a></span></td>
			<td><span class="box_btn_s gray"><a href="javascript:;" onclick="delGrp(<?=$arr2[no]?>)">삭제</a></span></td>
		</tr>
		<?
				}
			}
		?>
	</table>
</form>

<script language="JavaScript">
	function checkGrFrm(f){
		if(!checkBlank(f.gname, '그룹명을 입력해주세요.')) return false;
	}
	f=document.groupFrm;
	function subGrp(no){
		f.no.value='';
		f.level.value='2';
		f.ref.value=no;
		f.sbtn.value=f.sbtn.defaultValue;
		f.gname.value='';
		f.gname.focus();
	}
	function modGrp(no){
		f.no.value=no;
		f.ref.value='';
		f.sbtn.value='수정';
		oname=document.getElementById('gname'+no).innerText;
		f.gname.value=oname;
		f.gname.focus();
	}
	function delGrp(no){
		if(!confirm('해당 그룹을 삭제하시겠습니까?     ')) return;
		f.exec.value='delete';
		f.no.value=no;
		f.submit();
	}
</script>