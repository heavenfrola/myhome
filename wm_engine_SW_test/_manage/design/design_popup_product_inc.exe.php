<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품선택
	' +----------------------------------------------------------------------------------------------+*/

	printAjaxHeader();

	include_once $engine_dir.'/_engine/include/shop_detail.lib.php';
	include $engine_dir."/_engine/include/paging.php";

	$datas = explode('@', trim($_GET['datas'], '@'));
	$_pno = array();
	foreach($datas as $val) {
		if(preg_match('/^prd([0-9]+)$/', $val, $tmp)) $_pno[] = $tmp[1];
	}
	$_pno = implode(',', $_pno);
	if($_pno) {
		$idx = 0;
		$res = $pdo->iterator("select no, hash, name, stat, updir, upfile3, w3, h3, sell_prc from {$tbl['product']} where no in ($_pno) order by name asc");
        foreach ($res as $prd) {
			$prd['name'] = inputText(strip_tags($prd['name']));
			$prd['sell_prc'] = number_format($prd['sell_prc']);

			$idx++;

			$selected_prd_list .= "
			<li>
				<img src='$engine_url/_manage/image/icon/ic_gift.gif'>
				[$idx] <a href='$root_url/shop/detail.php?pno={$prd['hash']}' target='_blank'>{$prd['name']}</a>
				- {$prd['sell_prc']} 원 <a href='#' onclick='resetTargetPrd(\"prd\", {$prd['no']}); return false;' class='sclink blank'>선택취소</a>
			</li>";
		}
	}

	if($_GET['exec'] == 'selected') {
		exit($selected_prd_list);
	}

	if ($_GET[$cm."big"]) $cw .= " or (`level`= '2' and `big` = '".$_GET['big']."')";
	if ($_GET[$cm."mid"]) $cw .= " or (`level`= '3' and `mid` = '".$_GET['mid']."')";
	$sql = $pdo->iterator("select `no`,`name`,`ctype`,`level` from {$tbl['category']} where `ctype`= '1' and (`level` = '1' $cw )order by `level`,`sort`");
    foreach ($sql as $cate) {
		switch ($cate['level']) {
			case "1" : $cl = $cm."big"; break;
			case "2" : $cl = $cm."mid"; break;
			case "3" : $cl = $cm."small"; break;
		}
		$sel = ( $_GET[$cl] == $cate['no'] ) ? "selected" : "";
		${"item_".$cate['ctype']."_".$cate['level']} .= "\n\t<option value='{$cate['no']}' $sel>".stripslashes($cate['name'])."</option>";
	}

	$big = numberOnly($_GET['big']);
	$mid = numberOnly($_GET['mid']);
	$small = numberOnly($_GET['small']);
	if($small > 0) $w .= " and small='$small'";
	elseif($mid > 0) $w .= " and mid='$mid'";
	elseif($big > 0) $w .= " and big='$big'";

	$search_key = addslashes($_GET['search_key']);
	$search_str = mb_convert_encoding(trim($_GET['search_str']), _BASE_CHARSET_, array('utf8', 'euckr'));
	if($search_str) $w .= " and `$search_key` like '%".addslashes(addslashes($search_str))."%'";
	$w .= " and `stat` in (2,3,4)";

	$sql = "select no, hash, name, stat, updir, upfile3, w3, h3, sell_prc, milage, min_ord from {$tbl['product']} where wm_sc = 0 $w  order by reg_date desc";

	foreach($_GET as $key => $val) {
		if($key != 'page' && $val) {
			if(is_array($val)) {
				foreach($val as $key2 => $val2) {
					$QueryString .= "&{$key}[$key2]=".urlencode($val2);
				}
			} else {
				$QueryString .= "&$key=".urlencode($val);
			}
		}
	}

	$row = numberOnly($_GET['row']);
	$page = numberOnly($_GET['page']);

	if($page<=1) $page=1;
	$NumTotalRec=$pdo->row("select count(*) from {$tbl['product']} where wm_sc = 0 and stat in (2,3) $w");
	$PagingInstance = new Paging($NumTotalRec, $page, 5, 10);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult['LimitQuery'];

	$pg_res=$PagingResult['PageLink'];
	$res = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1));
	$group=getGroupName();

	$pg_res = preg_replace('/href="([^"]+)"/', 'href="javascript:" onclick="targetSelector.open(\'$1\')"', $pg_res);

?>
<style type='text/css'>
#selectedPrds {padding:10px 5px; border:1px solid #aaa;}
#selectedPrds legend {font-weight:bold;}
#selectedPrds li {padding:2px 0;}
</style>
<div id="popupContent" class="popupContent layerPop" style="width:600px;">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop">상품검색</div>
	</div>
	<div id="popupContentArea">
		<fieldset id="selectedPrds" class="contentFrm">
			<legend>선택된 상품</legend>
			<ul style="max-height:88px; overflow:auto;">
				<?=$selected_prd_list?>
			</ul>
		</fieldset>
		<script type="text/javascript">
		reloadTargetPrd(<?=$case?>);
		</script>
		<form id="search" style="margin: 5px 0;" onsubmit="return targetSelector.fsubmit(this);">
			<input type="hidden" name="body" value="<?=$_GET['body']?>">
			<table class="tbl_row">
				<caption class="hidden">검색</caption>
				<colgroup>
					<col style="width:15%">
				</colgroup>
				<tr>
					<th scope="row">매장분류</th>
					<td>
						<select name="big" onchange="chgCate(this,'mid','small')">
							<option value="">::대분류::</option>
							<?=$item_1_1?>
						</select>
						<select name="mid" onchange="chgCate(this,'small')">
							<option value="">::중분류::</option>
							<?=$item_1_2?>
						</select>
						<select name="small">
							<option value="">::소분류::</option>
							<?=$item_1_3?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">검색</th>
					<td>
						<select name="search_key">
							<option value="name" <?=checked($search_key, 'name', 1)?>>상품명</option>
							<option value="keyword" <?=checked($search_key, 'keyword', 1)?>>검색키워드</option>
							<option value="origin_name" <?=checked($search_key, 'origin_name', 1)?>>장기명</option>
							<option value="seller" <?=checked($search_key, 'seller', 1)?>>사입처명</option>
							<option value="code" <?=checked($search_key, 'code', 1)?>>상품코드</option>
						</select>
						<input type="text" name="search_str" class="input" size="20" value="<?=inputtext($search_str)?>">
						<span class="box_btn blue"><input type="submit" id="searchBtn" value="검색"></span>
					</td>
				</tr>
			</table>
		</form>
		<table class="tbl_col">
			<thead>
				<tr>
					<th scope="col">상품</th>
					<th scope="col">가격</th>
					<th scope="col">적립금</th>
					<th scope="col">상태</th>
					<th scope="col" style="width:40px;">선택</th>
				</tr>
			</thead>
			<tbody>
				<?php
                    foreach ($res as $prd) {

						$prd['parent'] = $prd['no'];
						$prd['name'] = inputText(strip_tags($prd['name']));

						if($prd['upfile3']) {
							$file_dir = getFileDir($prd['updir']);
							$is = setImageSize($prd['w3'], $prd['h3'], 30, 30);
							$is[2] = str_replace('"', "'", $is[2]);
							$prd['imgstr'] = "<img src='$file_dir/{$prd['updir']}/{$prd['upfile3']}' $is[2]>";
						}

						switch($prd['stat']) {
							case '2' : $prd['stat'] = '정상'; break;
							case '3' : $prd['stat'] = '품절'; break;
							case '4' : $prd['stat'] = '숨김'; break;
						}
				?>
				<tr>
					<td class="left">
						<div class="box_setup">
							<div class="thumb"><a href="<?=$root_url?>/shop/detail.php?pno=<?=$prd['hash']?>" target="_blank"><?=$prd['imgstr']?></a></div>
							<dl>
								<dt class="title"><a href="<?=$root_url?>/shop/detail.php?pno=<?=$prd['hash']?>" target="_blank"><?=$prd['name']?></a></dt>
								<dd class="func"><a href="./?body=product@product_register&pno=<?=$prd['no']?>" target="_blank">상품수정</a></dd>
							</dl>
						</div>
					</td>
					<td><?=number_format($prd['sell_prc'])?></td>
					<td><?=number_format($prd['milage'])?></td>
					<td><?=$prd['stat']?></td>
					<td><span class="box_btn_s blue"><input type="button" value="선택" onclick="setTargetPrd('prd', <?=$prd['no']?>)"></span></td>
				</tr>
				<?}?>
			</tbody>
		</table>
		<div class="box_bottom">
			<?=$pg_res?>
		</div>
		<div class="box_middle2">
			<span class="box_btn blue"><input type="button" value="확인" onclick="targetSelector.close()"></span>
		</div>
	</div>
</div>