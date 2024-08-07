<?PHP
	/* +----------------------------------------------------------------------------------------------+
	' |  상품수정/관리
	' +----------------------------------------------------------------------------------------------+*/

	// 기획전 정렬
	if($_GET['ctype']) $ctype = numberOnly($_GET['ctype']);
	if($_GET['ename']) $ename = addslashes($_GET['ename']);
	if($ctype == 2 || $ctype == 6) {
		if(!${$ename}) {
			echo "<tr><td colspan='14'>기획전을 선택하세요</td></tr>";
			return;
		}
		if(!$_GET['sort']) {
			$sort_str = " l.sort_big";
		}
	}

	function selSort($nowSel) {
		global $NumTotalRec;
		if($nowSel > $NumTotalRec) {
			$nowSel = $NumTotalRec;
		}
		$sort_str = '<select name="sort[]">';
		for($ii = 1; $ii <= $NumTotalRec; $ii++) {
			$checked = checked($ii, $nowSel, 1);
			$sort_str .= "<option value='$ii' $checked>$ii</option>";
		}
		$sort_str.= '</select>';
		return $sort_str;
	}

	// 정렬순서
	$sort = numberOnly($_GET['sort']);
	if($sort == '') $sort = 2;
	if(!$stat) $w .= " and `stat` != '1'";
	if(!$sort_str) $sort_str = $cfg['mng_sort'][$sort];

	if($cfg['ts_use'] == 'Y') {
		getTsPrd();
		$prd_add_where .= ", ts_use, ts_dates, ts_datee";
	}
	if(($cfg['delivery_fee_type'] == 'O' || $cfg['delivery_fee_type'] == 'A') && fieldExist($tbl['product'], 'oversea_free_delivery')){
		$prd_add_where .= ", p.oversea_free_delivery";
	}
	if($is_trash == 'Y') {
		$prd_add_where .= ", p.del_date, p.del_admin";
	}
	if($cfg['max_cate_depth'] >= 4) {
		$prd_add_where .= ", p.depth4";
	}
	if($cfg['use_kakaoTalkStore'] == 'Y') {
		$prd_add_where .= ", p.use_talkstore";
	}
    if ($cfg['use_talkpay'] == 'Y') {
        $prd_add_where .= ", p.use_talkpay";
    }

	if(getSmartStoreState() == true) {
		addField($tbl['product'], "smartstore", "enum('N','Y') default 'N' not null after `checkout`");
		$prd_add_where .= ", p.n_store_check as smartstore, p.nstoreId as smartstoreId";
	}
	$sql = "
		select
		p.no, p.hash, p.name, p.code, p.updir, p.upfile3, p.w3, p.h3, p.prd_type, p.big, p.mid, p.small,
		p.reg_date, p.ebig, p.xbig, p.ybig, p.sell_prc, p.normal_prc, p.milage, p.stat,
		p.hit_order, p.hit_sales, p.hit_wish, p.hit_cart, p.hit_view, p.wm_sc,
		p.dlv_alone, p.event_sale, p.member_sale, p.free_delivery, p.checkout,
		p.ea_type, p.seller, p.origin_name, p.origin_prc $prd_add_where
		from `{$tbl['product']}` p $prd_join where 1 $w order by {$sort_str}";
	setListURL('prdList');

	// 페이징
	include $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);
	if($page <= 1) $page = 1;
	if(!$row) $row = 20;
	if($row > 100) $row = 100;
	$block=10;

	$NumTotalRec = $pdo->row("select count(distinct p.no) from $tbl[product] p $prd_join where 1 $w");

	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult = $PagingInstance->result($pg_dsn);

	$sql .= $PagingResult['LimitQuery'];

	$pageRes = $PagingResult['PageLink'];
	$res = $pdo->iterator($sql);
	$idx = $NumTotalRec-($row*($page-1));
	$sort = ($row*($page-1))+1;

    foreach ($res as $data) {
		$realno = $data['no'];
		$prd_milage = $data['milage'];
		$data = shortCut($data);
		$data['name'] = strip_tags(stripslashes($data['name']));
		$data['sell_prc'] = parsePrice($data['sell_prc']);
		$data['normal_prc'] = parsePrice($data['normal_prc']);

		$file_dir = getFileDir($data['updir']);

		if($data['upfile3'] && ((!$_use['file_server'] && is_file($root_dir."/".$data['updir']."/".$data['upfile3'])) || $_use['file_server'] == "Y")) {
			$is = setImageSize($data['w3'], $data['h3'], 50, 50);
			$data['imgstr'] = "<img src='$file_dir/{$data['updir']}/{$data['upfile3']}' class='prdimgs' $is[2]>";
		} else {
		    $data['imgstr'] = "<img src='$file_dir/{$cfg['noimg3']}'class='prdimgs' width='50' height'50'";
		}

		$view_link = "shop";
		$edit_link = ($data['prd_type'] == '4' || $data['prd_type'] == '5' || $data['prd_type'] == '6') ? 'product@set_register' : 'product@product_register';

		$data['sopt'] = '';
		if($data['event_sale'] == "Y") $data['sopt'] .= "<span class='sopt esale'>이</span> ";
		if($data['member_sale'] == "Y") $data['sopt'] .= "<span class='sopt msale'>회</span> ";
		if($data['free_delivery'] == "Y") $data['sopt'] .= "<span class='sopt fdlv'>무</span> ";
		if($data['dlv_alone'] == "Y") $data['sopt'] .= "<span class='sopt noint'>단</span> ";
		if($data['checkout'] == "Y") $data['sopt'] .= "<span class='sopt checkout'>네</span> ";
		if($data['use_talkpay'] == "Y") $data['sopt'] .= "<span class='sopt talkpay'>카</span> ";
		if($data['smartstore'] == "Y" && $data['smartstoreId'] != "") $data['sopt'] .= "<span class='sopt smartstore'>스</span> ";
		if($data['use_talkstore'] == "Y") $data['sopt'] .= "<span class='sopt talkstore'>카</span> ";
		if(($cfg['delivery_fee_type'] == 'O' || $cfg['delivery_fee_type'] == 'A') && fieldExist($tbl['product'], 'oversea_free_delivery')){
			if($data['oversea_free_delivery'] == "Y") $data['sopt'] .= "<span class='sopt fdlv'>무(해)</span> ";
		}

		$data['sopt'] = preg_replace('/,\s$/', '', $data['sopt']);

		$cstr = makeCategoryName($data, 1);

		$productname = ($data['sc']) ? $data['name']." <img src='$engine_url/_manage/image/shortcut2.gif' alt='바로가기 상품입니다'>" : $data['name'];

		if($data['ea_type'] == 1) {
			$data['qty'] = number_format($pdo->row("select sum(if(qty>0, qty, 0)) from erp_complex_option where pno='{$data['parent']}' and del_yn='N' and force_soldout!='Y'"));
		} else {
			$data['qty'] = '-';
		}

		if($data['stat'] == 3) {
			$class = 'soldout';
		} else if ($data['stat'] == 4) {
			$class = 'hide';
		} else {
			$class = '';
		}

		$add_urlfix = '';
		if($data['stat']==4) $add_urlfix = '&urlfix=Y';

        $change_mode = ($data['prd_type'] == '4') ? 'readOnly' : '';

?>
<tr class=<?=$class?>>
	<td>
		<input type="hidden" name="pno[]" value="<?=$data['no']?>">
		<input type="checkbox" name="check_pno[]" id="check_pno" value="<?=$realno?>">
	</td>
	<?php if ($ctype==2 || $ctype==6) { ?>
	<td>
		<input type="hidden" name="sortidx[]" value="<?=$data['sortidx']?>" />
		<?=selSort($data['sort_big'])?>
	</td>
	<td><?=$data['sort_big']?></td>
	<?php } else { ?>
	<td><?=$idx?></td>
	<?php } ?>
	<?php if ($data['imgstr']) { ?>
	<td><div class="thumb"><a href="/<?=$view_link?>/detail.php?pno=<?=$data['hash']?><?=$add_urlfix?>" target="_blank"><?=$data['imgstr']?></a></div></td>
	<?php } else { ?>
	<td><div class="thumb"><a href="/<?=$view_link?>/detail.php?pno=<?=$data['hash']?><?=$add_urlfix?>" target="_blank"><?=$data['imgstr']?></a></div></td>
	<?php } ?>
	<td class="left">
		<div class="box_setup">
			<?php if ($cfg['use_partner_shop'] == 'Y') { ?>
			<strong class="p_color">[<?=$_partner_names[$data['partner_no']]?>]</strong>
			<?php } ?>
			<?php if ($cfg['prd_prd_code'] == 'Y' && $data['code']) { ?>
			<span><?=$data['code']?></span>
			<?php } ?>
			<p class="title" style="line-height:200%;">
				<a href="./?body=<?=$edit_link?>&pno=<?=$data['no']?>"><?=$productname?></a>
				<?php if ($data['ts_use'] == 'Y' && $data['ts_dates'] <= $now && ($data['ts_datee'] == 0 || $data['ts_datee'] >= $now)) { ?>
					<span class="p_color3">- 타임세일진행중</span>
				<?php } ?>
			</p>
			<?php if ($cfg['prd_name_referer'] == 'Y' && $data['name_referer']) { ?>
			<p class="cstr"><?=stripslashes($data['name_referer'])?></p>
			<?php } ?>
			<?php if ($data['prd_type'] == '4' || $data['prd_type'] == '5' || $data['prd_type'] == '6') { ?>
			<p class="cstr p_color set_title" style="cursor:pointer" title="" data-pno="<?=$data['parent']?>"><img src="<?=$engine_url?>/_manage/image/icon/ic_set.png"></p>
			<?php } ?>
			<p class="cstr"><br><?=$cstr?></p>
			<span class="box_btn_s btnp">
				<a href="./?body=<?=$edit_link?>&pno=<?=$data['no']?>">수정</a>
			</span>
		</div>
	</td>
	<?php if ($cfg['prd_reg_date'] == 'Y') { ?>
	<td><?=date("y/m/d",$data['reg_date'])?></td>
	<?php } ?>
	<?php if ($admin['level'] == '4' && $cfg['partner_prd_accept'] == 'Y') { ?>
	<td><?=number_format($data['sell_prc'])?> 원</td>
	<td><?=number_format($prd_milage)?> 원</td>
	<td><?=$_prd_stat[$data['stat']]?></td>
	<?php } else { ?>
	<td><input type="text" name="sell_prc[]" value="<?=$data['sell_prc']?>" class="input right input_won <?=$change_mode?>" size="5" <?=$change_mode?>></td>
	<?php if ($cfg['prd_normal_prc'] == 'Y') { ?>
	<td><input type="text" name="normal_prc[]" value="<?=$data['normal_prc']?>" class="input right input_won <?=$change_mode?>" size="5" <?=$change_mode?>></td>
	<?php } ?>
	<td><input type="text" name="milage[]" value="<?=parsePrice($prd_milage)?>" class="input right input_won <?=$change_mode?>" size="5" <?=$change_mode?>></td>
	<td>
		<select name="stat[]">
			<option value="2" <?=checked($data['stat'],"2",1)?>>정상</option>
			<option value="3" <?=checked($data['stat'],"3",1)?>>품절</option>
			<option value="4" <?=checked($data['stat'],"4",1)?>>숨김</option>
		</select>
	</td>
	<?php } ?>
	<td><?=$data['sopt']?></td>
    <?php if ($cfg['prd_origin_name'] == 'Y') { ?>
        <td><?=$data['origin_name']?></td>
    <?php } ?>
    <?php if ($cfg['prd_seller'] == 'Y') { ?>
        <td><?=$data['seller']?></td>
    <?php } ?>
    <?php if ($cfg['prd_origin_prc'] == 'Y') { ?>
        <td><?=parsePrice($data['origin_prc'], true)?></td>
    <?php } ?>
	<?php if ($is_trash == 'Y') { ?>
	<td><?=date('Y-m-d', $data['del_date'])?></td>
	<?php if ($cfg['use_trash_prd'] == "Y" && $cfg['trash_prd_trcd'] > 0) { ?>
	<td><?=date('Y-m-d', strtotime($cfg['trash_prd_trcd'].'day', $data['del_date']))?></td>
	<?php } ?>
	<td><?=stripslashes($data['del_admin'])?></td>
	<?php } else { ?>
	<td><?=number_format($data['hit_view'])?></td>
    <?php if ($admin['level'] == '4') { ?>
    <td><?=number_format($data['hit_order'])?></td>
    <?php } else { ?>
	<td onmouseover="showToolTip(event,'클릭하시면 이 상품을 구매한 회원들을 보실수 있습니다')" onmouseout="hideToolTip();">
		<a href='?body=member@member_list&search_type=pno&search_str=<?=$data['parent']?>'><?=number_format($data['hit_order'])?></a>
	</td>
    <?php } ?>
	<td><?=number_format($data['hit_sales'])?></td>
	<td><?=number_format($data['hit_wish'])?></td>
	<td><?=number_format($data['hit_cart'])?></td>
	<td>
		<?php if ($data['ea_type'] == 1) { ?>
		<a href="#" onclick="getProductQty(<?=$data['parent']?>, 1, this); return false;"><?=$data['qty']?></a>
		<?php } else { ?>
		-
		<?php } ?>
	</td>
	<?php } ?>
</tr>
<?PHP

	$idx--;
	$sort++;
	}

	// 상태별 통계
	$_tabcnt = array('total' => 0, 2 => 0, 3 => 0, 4 => 0);
	$wt = preg_replace("/ and p.`stat`='[0-9]'/", '', $w);
	$_tmpres = $pdo->iterator("select stat, count(*) as cnt from $tbl[product] p $prd_join where 1 $wt group by stat");
    foreach ($_tmpres as $_tmp) {
		$_tabcnt[$_tmp['stat']] = $_tmp['cnt'];
		$_tabcnt['total'] +=$_tmp['cnt'];
	}

?>
<script type="text/javascript" defer="defer">
$('#total_prd').html('<?=number_format($NumTotalRec)?>');
$('.prdimgs').mouseover(function() {
	new R2Tip(this, '<img src='+this.src+'>', 'R2Tip2', event);
});

function showExcelBtn(ev) {
	var ev = window.event ? window.event : ev;
	var layer = document.getElementById('excelLayer');

	if(layer.style.display == 'block') {
		layer.style.display = 'none';
	} else {
		layer.style.display = 'block';
		layer.style.position = 'absolute';
		layer.style.top = (document.documentElement.scrollTop+ev.clientY+25)+'px';
		layer.style.right = '72px';
	}
}

$('.prd_stat_total').html("<?=$_tabcnt['total']?>");
$('.prd_stat_2').html("<?=$_tabcnt[2]?>");
$('.prd_stat_3').html("<?=$_tabcnt[3]?>");
$('.prd_stat_4').html("<?=$_tabcnt[4]?>");

<?php if ($cfg['milage_type'] == 2 && $cfg['milage_type_per'] > 0) { ?>
$('input[name="milage[]"]').attr('readonly', true).click(function() {
	window.alert('현재 결재 금액 단위의 적립금 설정을 이용하고 계시며\n상품별 적립금은 유효하지 않습니다.');
});
<?php } ?>

// 세트 상품 미리보기
var setPreview = new layerWindow('product@set_preview_inc.exe');
$('.set_title').tooltip({
	'show': {'effect':'fade', 'duration':100},
	'hide': {'effect':'fade', 'duration':100},
	'track': true,
	'content': function(callback) {
		var pno = $(this).attr('data-pno');
		console.log(pno);
		$.ajax({
			'url': "./index.php",
			'data': {'body':'product@set_preview.exe', 'pno':pno},
			'type': "GET",
			'success': function(r) {
				if(!r) r = '선택된 세트 구성품이 없습니다.';
				callback(r);
			}
		});
	}
}).click(function() {
	var pno = $(this).attr('data-pno');
	setPreview.open('pno='+pno);
});
</script>