<?PHP

	if($_POST['pno']) {
		$pno = numberOnly($_POST['pno']);
		$stat = numberOnly($_POST['stat']);
		include_once $engine_dir.'/_manage/product/product_hdd.exe.php';
		include_once $engine_dir."/_engine/include/shop_detail.lib.php";
		$data=get_info($tbl['product'],"no",$pno);
	}

	$_wdrowspan = $cfg['disable_wingdisk'] == true ? 2 : 4;
	$_diskname = ($cfg['disable_wingdisk'] == true && $_SESSION['mall_goods_idx'] != '3') ? $_SESSION['disk_svc_name'] : '무료Disk';

?>
<div>
	<?php
    $log_count = $pdo->row("select count(*) from `$tbl[product_content_log]` where `pno`='$pno' and `mobile`='P'");
	if($log_count > 0) {
	?>
		<ul class="log_register">
			<?php
				$pcl_sql = $pdo->iterator("select `no`,`admin_id`, `mode`, `reg_date`, `edt_date` from `$tbl[product_content_log]` where `pno`='$pno' and `mobile`='P' order by `no` desc limit 5");
                foreach ($pcl_sql as $pcl_arr) {
				$_mode_str = array("1"=>"변경", "2"=>"복구");
				$_log_str = ($pcl_arr['mode'] == '1') ? "상품상세설명 변경":"상품상세설명 ".date("Y-m-d H:i", $pcl_arr['edt_date'])." 로 복구";
			?>
			<li>
				<?=date("Y-m-d H:i", $pcl_arr['reg_date'])?> : <?=$pcl_arr['admin_id']?> 님에 의해 <?=$_log_str?>
				<a onclick="preDetailPreview('<?=$pno?>','<?=$pcl_arr['no']?>')"><?=$_mode_str[$pcl_arr['mode']]?> 전 상품상세설명 보기</a>
			</li>
			<?php
				}
			?>
		</ul>
	<?php } ?>
	<textarea id="content2" name="content2" style="margin:0; padding:0; border:0; width:100%; height:<?=($_COOKIE['product_content_height'] > 0) ?  $_COOKIE['product_content_height'] : "500"; ?>px;"><?=stripslashes($data['content2'])?></textarea>
	<div id="Uploader"></div>
	<table class="tbl_row_reg tbl_row_reg_line">
		<caption class="hidden">상품상세설명 파일 업로드</caption>
		<colgroup>
			<col style="width:134px">
		</colgroup>
		<tr>
			<th scope="row">
                <?=$_diskname?>
                <div class="explain">(<?=getWingUploadSize('prdContent', true, 3)?>)</div>
            </th>
			<td>
				<iframe id="up_fdisk" src="./?body=product@product_file.frm&filetype=3&stat=<?=$stat?>&pno=<?=$pno?>&content_id=content2" width="100%" height="50px" scrolling="no" frameborder="0"></iframe>
				<div class="amount">
					<div class="title">사용량</div>
					<dl id="hdd_filetype3" class="file_graph">
						<dt><img src="<?=$engine_url?>/_manage/image/file_graph_bg.gif"> <?=$_basic_img_size_used?> / <?=$_basic_img_size_limit?> (<?=$per?>%)</dt>
						<dd style="width:<?=$per*3?>px; background:#83a914;"></dd>
					</dl>
				</div>
			</td>
		</tr>
		<?php if ($_SESSION['mall_goods_idx'] == '3' && $asvcs[0]->type[0] != '10') { ?>
		<tr>
			<th scope="row"><?=$_SESSION['disk_svc_name']?></th>
			<td>
				<iframe id="up_wdisk" src="./?body=product@product_file.frm&filetype=9&stat=<?=$stat?>&pno=<?=$pno?>&content_id=content2" width="100%" height="50px" scrolling="no" frameborder="0"></iframe>
				<div class="amount">
					<div class="title">사용량</div>
					<dl id="hdd_filetype9" class="file_graph">
						<dt><?=$_wdisk_used?> / <?=$_wdisk_limit?> (<?=$per?>%)</dt>
						<dd style="width:<?=$per*3?>px; background:#e05e17;"></dd>
					</dl>
				</div>
			</td>
		</tr>
		<?php } ?>
	</table>
</div>
<?php if ($_POST['pno']) { ?>
	<script type="text/javascript">
		var content2;
		$(document).ready(function() {
			setTimeout(function() {
                var upf1 = document.getElementById('m_up_fdisk');
                var upf2 = document.getElementById('m_up_wdisk');
                content2 = seCall('content2', '', (upf2) ? 'up_wdisk' : 'up_fdisk');
			}, 1000);
		});

	function preDetailPreview(pno,idx) {
		var prd = '&pno='+pno+'&idx='+idx+'&mobile=P';

		wisaOpen('./?body=product@product_predetail.frm'+prd, 'preDetailPreview', 'yes', 1066, 800);
	}
	</script>
<?php } ?>