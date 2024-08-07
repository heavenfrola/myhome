<?PHP

	$no = numberOnly($_GET['no']);
	$allow_ext=str_replace("|", ", ", $_bconfig[upfile_ext]);
	if(!authChk("write")) msg("글 작성 권한이 없습니다", "back");

	$title = stripslashes($_bconfig['title']);
	if($no){
		$data = $pdo->assoc("select * from `$tbl[intra_board]` where `no`='$no' limit 1");
		$no=$data[no];
		$_view_member = explode('@', $data['view_member']);
		$title = stripslashes($data['title']);

		if($admin['partner_no'] > 0 && $data['member_id'] != $admin['admin_id']) msg('글 수정 권한이 없습니다.', 'back');
	}

?>
<script language="javasscript" type="text/javascript" src="<?=$engine_url?>/_engine/R2Na/R2Na.js"></script>
<form name="boardFrm" method="post" action="<?=$PHP_SELF?>" target="hidden<?=$now?>" onsubmit="return intraBoardChk(this);" enctype="multipart/form-data">
	<input type="hidden" name="body" value="intra@board.exe">
	<input type="hidden" name="db" value="<?=$db?>">
	<input type="hidden" name="no" value="<?=$no?>">
	<input type="hidden" name="QueryString" value="<?=$QueryString5?>">
	<div class="box_title first">
		<h2 class="title">
			<?=$title?>
		</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">공지사항</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<?if($cfg['use_partner_shop'] == 'Y' && $_bconfig['auth_list'] == 4 && $admin['level'] < 4) {?>
		<tr>
			<th scope="row"><strong>권한</strong></th>
			<td>
				<ul class="box_scroll">
					<li>
						<label class="p_cursor first"><input type="checkbox" onclick="$('.view_member').prop('checked', this.checked)"> 전체</label>
					</li>
					<?PHP
					$ii = 1;
					$pres = $pdo->iterator("select no, corporate_name from $tbl[partner_shop] where `stat` between 2 and 4 order by corporate_name asc");
                    foreach ($pres as $pdata) {
						$ii++;
						$ochecked = (is_array($_view_member)) ? checked(in_array($pdata['no'], $_view_member), true) : '';
					?>
					<li>
						<label class="p_cursor"><input type="checkbox" class="view_member" name="view_member[]" value="<?=$pdata['no']?>" <?=$ochecked?>> <?=stripslashes($pdata['corporate_name'])?></label>
					</li>
					<?}?>
					<?if($ii > 4) {for($blnak = 0; $blnak <= ($ii%4); $blnak++) {?>
					<li style="height: 27px;">&nbsp;</li>
					<?}}?>
				</ul>
			</td>
		</tr>
		<?}?>
		<tr>
			<th scope="row"><strong>제목</strong></th>
			<td><input type="text" name="title" value="<?=$data[title]?>" class="input input_full" maxlength="200"></td>
		</tr>
		<tr>
			<th scope="row"><strong>내용</strong></th>
			<td><textarea id="content" name="content" class="txta"><?=htmlspecialchars($data[content])?></textarea></td>
		</tr>
		<?
			if(authChk("upload")){
				for($ii=1; $ii<=2; $ii++){
					if($data["upfile".$ii]){
						$_link="$root_url/$data[updir]/".$data["upfile".$ii];
						${"file_link".$ii}="(<a href=\"$_link\" target=\"_blank\">".$data["ori_upfile".$ii]."</a> <input type=\"checkbox\" name=\"delfile".$ii."\" id=\"delfile".$ii."\" value=\"Y\"><label for=\"delfile".$ii."\">기존 파일 삭제</label>)<br>";
					}
		?>
		<tr>
			<th scope="row">첨부파일<?=$ii?></th>
			<td><?=${"file_link".$ii}?><input type="file" name="upfile<?=$ii?>" class="input input_full"> <span class="explain">(<?=$allow_ext?>)</span></td>
		</tr>
		<?
			}
		}
		?>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<span class="box_btn gray"><input type="button" value="취소" onclick="history.back();"></span>
	</div>
</form>

<script language="JavaScript">
	var editor = new R2Na('content', {
		'editor_gr': 'intra',
		'editor_code': 'intra_<?=$now?>'
	});
	editor.initNeko(editor_code, 'content', 'img');	
	function intraBoardChk(f){
		oEditors.getById['content'].exec("UPDATE_CONTENTS_FIELD", []);
		if(!checkBlank(f.title, '제목을 입력해주세요.')) return false;
	}
</script>