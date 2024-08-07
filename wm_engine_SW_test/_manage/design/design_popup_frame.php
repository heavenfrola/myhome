<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  팝업스킨 편집
	' +----------------------------------------------------------------------------------------------+*/
	$from_popup = numberOnly($_GET['from_popup']);
	$ori_frame = numberOnly($_GET['ori_frame']);

	$sql="select * from {$tbl['popup_frame']} order by `no` desc";
	$res = $pdo->iterator($sql);
	$_sizewh = $from_popup ? 100 : 140;

?>
<?php if ($from_popup) { ?>
<style type="text/css" title="">
body {background:none;}
</style>
<?php } ?>
<?php if (!$from_popup) { ?>
<div class="box_title first">
	<h2 class="title">팝업스킨 편집</h2>
</div>
<?php } ?>
<table class="tbl_col">
	<colgroup>
		<col>
		<col style="width:100px;">
	</colgroup>
	<thead>
		<th>팝업스킨명</th>
		<th>수정</th>
	</thead>
	<tbody>
		<?PHP foreach ($res as $data) { ?>
		<tr>
			<td class="left">
				<a href="?body=design@design_popup_frame_register&no=<?=$data['no']?>"><?=$data['title']?></a>
			</td>
			<td><span class="box_btn_s blue"><input type="button" value="수정" onclick="goM('design@design_popup_frame_register&no=<?=$data['no']?>');"></span></td>
		</tr>
		<?php } ?>
	</tbody>
</table>
<div class="box_bottom">
	<span class="box_btn blue"><input type="button" value="추가" onclick="location.href='./?body=design@design_popup_frame_register';"></span>
</div>