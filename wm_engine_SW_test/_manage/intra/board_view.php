<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  게시판 처리
	' +----------------------------------------------------------------------------------------------+*/

	$no = numberOnly($_GET['no']);
	$_tbl=$tbl[intra_board];
	if(!authChk("view")) msg("글 조회 권한이 없습니다", "back");

	$partner_f = "";
	$partner_j = "";
	if($_bconfig['auth_list'] == 4) {
		$partner_f = ", b.corporate_name";
		$partner_j = "left join `$tbl[partner_shop]` b on a.partner_no=b.no";
	}
	$data = $pdo->assoc("select a.* $partner_f from `$_tbl` a $partner_j where a.`no`='$no' limit 1");
	$no=$data[no];
	if(!$no) msg("존재하지 않는 글입니다", "back");
	if($data[member_no] != $admin[no] && $admin[admin_id] != "wisa"){
		if(!@strchr($data[view_member], "@".$admin[no]."@")) $hitw=", `view_member`='".$data[view_member].$admin[no]."@'";
		$pdo->query("update `$_tbl` set hit=hit+1".$hitw." where `no`='$no' limit 1");
	}

	$file_url = getFileDir($data['updir']);
	for($ii=1; $ii<=2; $ii++){
		if($data["upfile".$ii]){
			$ext=strtolower(getExt($data["upfile".$ii]));
			$_link="$file_url/$data[updir]/".$data["upfile".$ii];
			if(@strchr("jpeg|jpg|gif|png|bmp", $ext)){
				$width="";
				${"file_img".$ii} = "<p class=\"attachImage\"><a href=\"$_link\" target=\"_blank\"><img src=\"$_link\" onload=\"attachResize(this);\"></a></p>";
			}
			${"file_link".$ii}="[<a href=\"$_link\" target=\"_blank\">".$data["ori_upfile".$ii]."</a>] &nbsp;";
		}
	}

	if($_bconfig['auth_list'] == 4) {
		if($data['member_level']==4) {
			$bo_corporate_name = stripslashes($data['corporate_name']);
		}else {
			$bo_corporate_name = stripslashes($cfg['company_name']);
		}
		$colspan = "colspan='5'";
		$colspan2 = "colspan='7'";
	}else {
		$colspan = "colspan='3'";
		$colspan2 = "colspan='5'";
	}
?>
<style type="text/css">
.attachImage {
	width: 100%;
	display: block;
	overflow: hidden;
	margin: 0 0 10px 0;
}
</style>
<script type="text/javascript">
	function attachResize(o) {
		var pwidth = $(o).parents('.attachImage').width();
		var twidth = $(o).width();
		if(pwidth < twidth) $(o).css('width', pwidth);
	}
</script>
<div class="box_title first">
	<h2 class="title"><?=$data[title]?></h2>
</div>
<table class="tbl_row">
	<colgroup>
		<col style="width:15%">
		<col>
		<col style="width:15%">
		<col>
		<col style="width:15%">
		<col>
	</colgroup>
	<tr>
		<th scope="row">작성자</th>
		<td><?=$data[name]?></td>
		<?if($_bconfig['auth_list'] == 4) {?>
			<th scope="row">입점사</th>
			<td><?=$bo_corporate_name?></td>
		<?}?>
		<th scope="row">등록일시</th>
		<td><?=date("Y-m-d H:i", $data[reg_date])?></td>
		<th scope="row">조회수</th>
		<td><?=$data[hit]?></td>
	</tr>
	<?php
		if(adminAuth()){
			if(strlen($data[view_member]) > 1){
				$_vmem_arr=explode("@", $data[view_member]);
				$data[view_member_name]="";
				foreach($_vmem_arr as $key=>$val){
					if($val){
						$_vmname=$pdo->row("select `name` from `$tbl[mng]` where `no`='$val' limit 1");
						if($_vmname) $data[view_member_name] .= $data[view_member_name] ? ", ".$_vmname : $_vmname;
					}
				}
			}
	?>
	<tr>
		<th scope="row">아이디</th>
		<td><?=$data[member_id]?></td>
		<th scope="row">아이피</th>
		<td <?=$colspan?>><?=$data[ip]?></td>
	</tr>
	<tr>
		<th scope="row">확인</th>
		<td <?=$colspan2?>><?=$data[view_member_name]?></td>
	</tr>
	<?}?>
	<tr>
		<th scope="row">첨부파일</th>
		<td <?=$colspan2?>>
			<?=$file_link1.$file_link2?>
		</td>
	</tr>
	<tr>
		<th scope="row">제목</th>
		<td <?=$colspan2?>><?=$data[title]?></td>
	</tr>
</table>
<div class="box_middle2 left">
	<?=$file_img1.$file_img2?>
	<?=$data[content]?>
</div>
<div class="box_bottom">
	<?if(editAuth($data)){?>
	<span class="box_btn blue"><input type="button" value="글수정" onclick="location.href='./?mode=write<?=$QueryString4?>'"></span>
	<span class="box_btn gray"><input type="button" value="삭제" onclick="intraBoardDel();"></span>
	<?}?>
	<span class="box_btn"><input type="button" value="리스트" onclick="location.href='./?<?=str_replace("no=$no&", "", $QueryString4)?>'"></span>
	</td>
</div>
<?include $engine_dir."/_manage/intra/board_comment.php";?>
<form name="delFrm" action="<?=$PHP_SELF?>" method="post" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="intra@board.exe">
	<input type="hidden" name="exec" value="delete">
	<input type="hidden" name="db" value="<?=$db?>">
	<input type="hidden" name="no" value="<?=$no?>">
	<input type="hidden" name="QueryString" value="<?=str_replace("no=$no&", "", $QueryString5)?>">
</form>

<script language="JavaScript">
	function intraBoardDel(){
		if(!confirm('삭제하시겠습니까?')) return;
		f=document.delFrm;
		f.submit();
	}
	function comFrmChk(f){
		if(!checkBlank(f.ccontent, '내용을 입력해주세요.')) return false;
	}
	function intraComMod(no){
		f=document.commentFrm;
		f.cno.value=no;
		content=document.getElementById('com'+no).innerText;
		f.ccontent.value=content;
		f.ccontent.focus();
	}
	function intraComDel(no){
		if(!confirm('삭제하시겠습니까?')) return;
		f=document.commentFrm;
		f.cno.value=no;
		f.exec.value='comment_delete';
		f.submit();
	}
</script>