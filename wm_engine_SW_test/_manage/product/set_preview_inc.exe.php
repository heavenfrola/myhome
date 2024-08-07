<?PHP

/**
 * 세트상품 미리보기 (세트 아이콘 클릭 시)
 **/

require 'set_preview.exe.php';
include $engine_dir."/_engine/include/paging.php";

$page = numberOnly($_GET['page']);
$row = numberOnly($_GET['row']);
if($page <= 1) $page = 1;
if(!$row) $row = 5;
if($row > 100) $row = 100;
$block=10;

$NumTotalRec = $pdo->row("select count(*) from {$tbl['product_refprd']} r inner join {$tbl['product']} p on r.refpno=p.no where r.pno='$pno' and r.`group`=99");

$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
$PagingInstance->addQueryString(makeQueryString('page'));
$PagingResult = $PagingInstance->result($pg_dsn);

$pageRes = $PagingResult['PageLink'];
$idx = $NumTotalRec-($row*($page-1));
$sort = ($row*($page-1))+1;
$pg_res = preg_replace('/href="([^"]+)"/', 'href="javascript:" onclick="setPreview.open(\'$1\')"', $PagingResult['PageLink']);

$res = $pdo->iterator("
    select
        p.no, p.hash, p.name, p.updir, p.upfile3, p.sell_prc, p.stat, p.big, p.mid, p.small $afield
    from {$tbl['product_refprd']} r inner join {$tbl['product']} p on r.refpno=p.no
    where r.pno='$pno' and r.`group`=99 order by r.sort asc
".$PagingResult['LimitQuery']);

?>
<div id="popupContent" class="popupContent layerPop" style="width:600px;">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop">상품검색</div>
	</div>
	<div id="popupContentArea">

		<table class="tbl_col">
			<colgroup>
				<col>
				<col style="width:100px;">
				<col style="width:100px;">
			</colgroup>
			<thead>
				<th>상품</th>
				<th>가격</th>
				<th>상태</th>
			</thead>
			<tbody>
				<?php while($data = parseProduct($res, 50, 50)) { ?>
				<tr>
					<td class="left">
						<div class="box_setup" style="padding-right:0;">
							<div class="thumb">
								<a href="<?=$root_url?>/shop/detail.php?pno=<?=$data['hash']?>" target="_blank"><img src="<?=$data['img']?>" style="height:50px;"></a>
							</div>
							<div style="margin-left:60px;">
								<p class="title"><a href="?body=product@product_register&pno=<?=$data['no']?>" target="_blank"><?=$data['name']?></a></p>
								<p class="cstr"><?=makeCategoryName($data)?></p>
							</div>
						</div>
					</td>
					<td><?=$data['sell_prc']?></td>
					<td><?=$_prd_stat[$data['stat']]?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div class="box_bottom">
		<?=$pg_res?>
	</div>
	<div class="pop_bottom">
		<span class="box_btn_s gray"><input type="button" value="창닫기" onclick="setPreview.close()"></span>
	</div>
</div>