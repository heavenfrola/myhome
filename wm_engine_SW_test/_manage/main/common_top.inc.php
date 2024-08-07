<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  타이틀/바로가기 인클루드
	' +----------------------------------------------------------------------------------------------+*/
	if(is_object($current_menu)) {
		$current_menu_name = $current_menu->val('name');
		$current_menu_pgcode = $current_menu->val('pgcode');
		$current_big_name = $current_big->attr('name');
	}
	if($body == 'product@product_register' && $_GET['pno']) $current_menu_name = '상품 수정';
	if($body == 'product@product_list' && !$cpn_mode) {
		$stats = array();
		if($admin['level'] == 4) $p_menu_addwhere = " and partner_no='$admin[partner_no]'";
		$sql = $pdo->iterator("select `stat`, count(*) as `cnt` from `$tbl[product]` where `stat` != '1' and wm_sc=0 $p_menu_addwhere group by `stat`");
        foreach ($sql as $sdata) {
			$stats[$sdata['stat']] = $sdata['cnt'];
			$stats['total'] += $sdata['cnt'];
		}
		$stats['total'] = $stats['total'];
	}
	switch($_GET['body']) {
		case 'openmarket@compare_setup' :
			$common_top_btn = "<span class='btn blue small'><input type='button' onclick=\"window.open('http://redirect.wisa.co.kr/adnaver')\" value='네이버쇼핑 안내/입점'></span>";
		break;
		case 'openmarket@show_setup' :
			$common_top_btn = "<span class='btn blue small'><input type='button' onclick=\"window.open('http://redirect.wisa.co.kr/daumhow')\" value='다음쇼핑하우 안내/입점'></span>";
		break;
	}

    $_manual_icon = (is_object($_online_manual) == true && $_online_manual->video == 'Y') ? 'movie' : 'help';

?>
<script type="text/javascript">
function qg_view() {
	$('.quickguide').show();
}
function qg_hide() {
	$('.quickguide').hide();
}
</script>
<div id="contentTop">
	<div class="location">
		<p style="display:none;">
			홈 &gt; <?=$current_big_name?> &gt; <?=$current_menu_name?>
		</p>
		<?php if ($current_menu_pgcode){?>
		<p onmouseover="qg_view()" onmouseout="qg_hide()">
			<a href="#" onclick="qmPlus('<?=$current_menu_pgcode?>', 2); return false;" onmouseover="showToolTip(event,'이 페이지를 퀵메뉴로 추가할 수 있습니다.');" onmouseout="hideToolTip();"><img src="<?=$engine_url?>/_manage/image/icon/favorite.gif" alt=""> 퀵메뉴로 추가</a>
			<span class="quickguide"></span>
		</p>
		<?php } ?>
		<?php if ($body == 'config@ipin') { ?>
		<span class="box_btn_s blue" style="margin-top:10px;">
			<a href="http://www.wisa.co.kr/#/solution/manage/detail/10248" target="_blank">i-pin 안내</a>
		</span>
		<?php } ?>
	</div>
	<ul id="search_manual">
		<li><a href="#help" class="<?=$_manual_icon?>" onclick="toggleManual(false, 2); return false;">도움말</a></li>
	</ul>
</div>
<div class="clear"></div>
<?PHP
	// 검색 타이틀 노출
	if(!in_array($body, array(
		'product@product_list',
		'member@member_list',
	))) return;
?>
<div class="box_title first">
	<h2 class="title">
		<?=$current_menu->val('name')?>
		<?=$common_top_btn?> <?php if($body == 'wmb@category_config' || $body == 'wmb@category_config2') echo '<img src="{$engine_url}/_manage/image/mobile_icon.gif" alt="모바일">'; ?>
	</h2>
	<?php if ($body == 'product@product_list' && !$cpn_mode) { ?>
	<dl class="total">
		<dt class="hidden">현황</dt>
		<dd>총 <strong><?=number_format($stats['total'])?></strong></dd>
		<dd>정상 <strong><?=number_format($stats[2])?></strong></dd>
		<dd>품절 <strong><?=number_format($stats[3])?></strong></dd>
		<dd>숨김 <strong><?=number_format($stats[4])?></strong></dd>
	</dl>
	<?php } ?>
</div>