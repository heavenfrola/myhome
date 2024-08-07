<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  게시판 관리 - 보기
	' +----------------------------------------------------------------------------------------------+*/

	$_tbl="mari_board";
	$mari_path=$engine_dir."/board";

	$no = numberOnly($_GET['no']);

	$data=$pdo->assoc("select * from `$_tbl` where `no`='$no' limit 1");
	$no=$data[no];
	if(!$no) msg("존재하지 않는 글입니다", "back");

	for($ii=1; $ii<=4; $ii++){
		if($data["upfile".$ii]){
			$file_url = getFileDir('board/'.$data['up_dir']);
			$ext=strtolower(getExt($data["upfile".$ii]));
			$_link=$file_url."/board/".$data[up_dir].'/'.$data["upfile".$ii];
			if(@strchr("jpeg|jpg|gif|png|bmp", $ext)){
				$width="";
				if($file_url == $root_url) {
					$size=@getimagesize($root_dir."/".$data[up_dir].$data["upfile".$ii]);
					if($size[0] > 700) $width=" width=700";
				}
				${"file_img".$ii}="<a href=\"$_link\" target=\"_blank\"><img src=\"$_link\" vspace=\"10\" $width></a><br>";
			}
			${"file_link".$ii}="[<a href=\"$_link\" target=\"_blank\">".$data["ori_upfile".$ii]."</a>] &nbsp;";

			$file_img.= ${"file_img".$ii};
			$file_link.= ${"file_link".$ii};
		}
	}

	$data[content]=stripslashes($data[content]);
	if($data[html] != 3) {
		$data[content]=nl2br($data[content]);
	}
	if($data[html] == 1) {
		$data[content]=autolink($data[content]);
	}
	$content_link=$root_url."/board/?db=".$data[db]."&no=".$data[no]."&mari_mode=view@view";

	$config = $pdo->assoc("select * from mari_config where db='$data[db]'");
	$tmp_name = unserialize(stripslashes($config['tmp_name']));
	if(is_array($tmp_name)) {
		foreach($tmp_name as $key => $val) {
			if(!$val) $val = '추가항목'.numberOnly($key);
			$tmp_name[$key] = stripslashes($val);
		}
	}

	addPrivacyViewLog(array(
		'page_id' => 'board',
		'page_type' => 'view',
		'target_id' => $data['member_id'],
		'target_cnt' => 1
	));
?>
<div class="box_title first">
	<h2 class="title">게시물 관리</h2>
</div>
<table class="tbl_row">
	<caption class="hidden">게시물 관리</caption>
	<colgroup>
		<col style="width:15%">
		<col style="width:18%">
		<col style="width:15%">
		<col style="width:19%">
		<col style="width:15%">
		<col style="width:18%">
	</colgroup>
	<tr>
		<th scope="row">작성자</th>
		<td><a href="javascript:;" onClick="viewMember('<?=$data[member_no]?>','<?=$data[member_id]?>')"><b><?=$data[name]?></b></a></td>
		<th scope="row">등록일시</th>
		<td><?=date("Y-m-d H:i", $data[reg_date])?></td>
		<th scope="row">조회수</th>
		<td><?=$data[hit]?></td>
	</tr>
	<tr>
		<th scope="row">아이디</th>
		<td><a href="javascript:;" onClick="viewMember('<?=$data[member_no]?>','<?=$data[member_id]?>')"><?=$data[member_id]?></a></td>
		<th scope="row">아이피</th>
		<td colspan="3"><?=$data[ip]?></td>
	</tr>
	<tr>
		<th scope="row">이메일</th>
		<td><?=$data['email']?></td>
		<th scope="row">연락처</th>
		<td colspan="3"><?=$data['phone']?></td>
	</tr>
	<tr>
		<th scope="row">첨부파일</th>
		<td colspan="5">
		<?=$file_link?>
		</td>
	</tr>
	<tr>
		<th scope="row">게시물 링크</th>
		<td colspan="5">
		<a href="<?=$content_link?>" target="_blank"><?=$content_link?></a>
		</td>
	</tr>
	<?for($i = 1; $i <= $cfg['board_add_temp']; $i++) {?>
	<?if($tmp_name['temp'.$i]) {?>
	<tr>
		<th scope="row"><?=$tmp_name['temp'.$i]?></th>
		<td colspan="5"><?=stripslashes($data['temp'.$i])?></td>
	</tr>
	<?}?>
	<?}?>
	<tr>
		<td colspan="6"><b><?=$data[title]?></b></td>
	</tr>
</table>
<div class="box_middle2 board_view left">
	<?=$file_img?>
	<?=$data[content]?>
</div>
<div id="reg_footer" class="box_bottom">
	<span class="box_btn blue"><input type="button" value="글수정" onclick="location.href='./?body=board@content_write&mode=write&no=<?=$no?>';"></span>
	<span class="box_btn gray"><input type="button" value="삭제" onclick="boardDel();"></span>
	<span class="box_btn gray"><input type="button" value="리스트" onclick="location.href='<?=$_SESSION['list_url']?>';"></span>
</div>
<?
	preg_match('/MSIE ([0-9.]+)/', $_SERVER['HTTP_USER_AGENT'], $agent);
	settype($agent[1], 'integer');
	if($agent[1] > 6 || $agent[1] == 0) {
?>
<div id="fastBtn">
	<span class="box_btn blue"><input type="button" value="글수정" onclick="location.href='./?body=board@content_write&mode=write&no=<?=$no?>';"></span>
	<span class="box_btn gray"><input type="button" value="삭제" onclick="boardDel();"></span>
	<span class="box_btn gray"><input type="button" value="리스트" onclick="location.href='<?=$_SESSION['list_url']?>';"></span>
</div>
<?}?>
<?include $engine_dir."/_manage/board/board_comment.php";?>
<form name="delFrm" action="<?=$PHP_SELF?>" method="post" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="board@content.exe">
	<input type="hidden" name="exec" value="delete">
	<input type="hidden" name="check_pno[]" value="<?=$no?>">
</form>
<form name="comDelFrm" action="<?=$PHP_SELF?>" method="post" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="board@content.exe">
	<input type="hidden" name="exec" value="delete">
	<input type="hidden" name="mng" value="2">
	<input type="hidden" name="check_pno[]">
</form>

<script language="JavaScript">
	function boardDel(){
		if(!confirm('삭제하시겠습니까?')) return;
		f=document.delFrm;
		f.submit();
	}
	function comDel(no){
		if(!confirm('삭제하시겠습니까?')) return;
		f=document.comDelFrm;
		f['check_pno[]'].value=no;
		f.submit();
	}

	// FIXED 슬라이드 저장버튼
	function refineFastBtn() {
		$('#fastBtn').css('left', $('#contentArea').css('margin-left')).width($('#contentTop').innerWidth()+100);
	}
	function toggleFastBtn() {
		var doc = document.documentElement.scrollTop > document.body.scrollTop ? document.documentElement : document.body;
		var fastBtn = $('#fastBtn');

		if(fastBtn.css('opacity') == 1) fastBtn.css('opacity', '.8');

		if(doc.scrollTop > $('#reg_footer').offset().top-$(window).height()) {
			if(fastBtn.css('opacity') > 0) {
				fastBtn.animate({"opacity":"0"}, {"queue":false}).css('display','none');
			}
		} else {
			if(fastBtn.css('opacity') == 0) {
				fastBtn.animate({"opacity":".8"}, {"queue":false}).css('display','');
			}
		}
	}
	if($('#fastBtn').length > 0) {
		$(document).ready(function() {
			toggleFastBtn();
			refineFastBtn();

			$('#contentArea').change(refineFastBtn);
		});

		$(window).bind({
			"resize": refineFastBtn,
			"scroll": toggleFastBtn
		});
	}
</script>