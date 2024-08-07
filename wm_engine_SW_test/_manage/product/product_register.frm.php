<?PHP

	$pno = numberOnly($_POST['pno']);
	$stat = numberOnly($_POST['stat']);

	if($pno) {
		include_once $engine_dir.'/_manage/product/product_hdd.exe.php';
		include_once $engine_dir."/_engine/include/shop_detail.lib.php";
		$data=get_info($tbl['product'],"no",$pno);
	}

	$_wdrowspan = $cfg['disable_wingdisk'] == true ? 2 : 4;
	$_diskname = $cfg['disable_wingdisk'] == true ? '이미지' : '무료Disk';

?>
<style type="text/css" title="">
.register .content2_bottom_1 {
	padding: 0;
	text-align: center;
	background: #fbfbfb;
}

.register .content2_bottom_2 {
	padding: 0;
	background: url(<?=$engine_url?>/_manage/image/bg_content2_left.gif) repeat-x;
}

.register .content2_bottom_2 ul {
	float: right;
	padding-left: 24px;
	width: 126px;
	background: url(<?=$engine_url?>/_manage/image/bg_content2_right.gif) no-repeat;
}

.register .content2_bottom_2 li {
	display: inline;
	padding: 2px 0 0 1px;
}

/* 디스크사용량 그래프 */
.file_graph dt {height:18px; font-size:10px; font-family:dotum verdana; letter-spacing:-1px;}
.file_graph dt img {margin-right:10px;}
.file_graph dd {position:absolute; margin-top:-13px; height:10px; background:red;}
</style>

<div style="background:#fff;">
	<textarea id="content2" name="content2" style="margin:0; padding:0; border:0; width:100%; height:<?=($_COOKIE[product_content_height] > 0) ?  $_COOKIE[product_content_height] : "350"; ?>px;"><?=stripslashes($data['content2'])?></textarea>
	<div id="Uploader"></div>
	<table class="tbl_row">
		<caption class="hidden">상품상세설명 파일 업로드</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row"><?=$_diskname?></th>
			<td>
				<iframe id="up_fdisk" src="./?body=product@product_file.frm&filetype=3&stat=<?=$stat?>&pno=<?=$pno?>" width="100%" height="50px" scrolling="no" frameborder="0"></iframe>
			</td>
		</tr>
		<tr>
			<th scope="row">사용량</th>
			<td>
				<div class="box_setup">
					<dl id="hdd_filetype3" class="file_graph">
						<dt><img src="<?=$engine_url?>/_manage/image/file_graph_bg.gif"> <?=$_basic_img_size_used?> / <?=$_basic_img_size_limit?> (<?=$per?>%)</dt>
						<dd style="width:<?=$per*3?>px; background:#83a914;"></dd>
					</dl>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">윙Disk</th>
			<td>
				<iframe id="up_wdisk" src="./?body=product@product_file.frm&filetype=9&stat=<?=$stat?>&pno=<?=$pno?>" width="100%" height="50px" scrolling="no" frameborder="0"></iframe>
			</td>
		</tr>
		<tr>
			<th scope="row">사용량</th>
			<td>
				<div class="box_setup">
					<dl id="hdd_filetype9" class="file_graph">
						<dt><?=$_wdisk_used?> / <?=$_wdisk_limit?> (<?=$per?>%)</dt>
						<dd style="width:<?=$per*3?>px; background:#e05e17;"></dd>
					</dl>
				</div>
			</td>
		</tr>
	</table>
</div>

<?if($_POST['pno']) {?>
<script type="text/javascript">
	var content2;
	$(document).ready(function() {
		setTimeout(function() {
			content2 = new R2Na('content2');
		}, 1000);
	});
</script>
<?}?>