<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  타이틀/바로가기 인클루드 - order
	' +----------------------------------------------------------------------------------------------+*/

	if(!$_GET['order_stat_group']) {
		$o_menu_addwhere = '';
		$stats = array();
		if($admin['level'] == 4) {
			$o_menu_addwhere = " and op.partner_no='$admin[partner_no]'";
		}
		$o_menu_addwhere .= " and date1 >= '".strtotime('-6 months')."'";
		$res = $pdo->iterator("
			select count(distinct(ono)) as cnt, op.stat
			from wm_order o inner join wm_order_product op using(ono)
			where o.stat < 5 and op.stat between 1 and 4 $o_menu_addwhere
			group by op.stat
			order by NULL
		");
        foreach ($res as $sdata) {
			$stats[$sdata['stat']] = $sdata['cnt'];
			$stats['total'] += $sdata['cnt'];
			if($sdata['stat'] > 10) $stats['cancel'] += $sdata['cnt'];
		}
		$stats['total'] = number_format($stats['total']);
		$stats['cancel'] = number_format($stats['cancel']);

		for($i = 1; $i <= 4; $i++) {
			$stats[$i] = number_format($stats[$i]);
		}
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
		<p class="location" style="display:none;">
			홈 &gt; <?=$current_big->attr('name')?> &gt; <?=$current_menu->val('name')?>
		</p>
		<p onmouseover="qg_view()" onmouseout="qg_hide()">
			<a href="#" onclick="qmPlus('<?=$current_menu->val('pgcode')?>', 2); return false;" onmouseover="showToolTip(event,'이 페이지를 퀵메뉴로 추가할 수 있습니다.')" onmouseout="hideToolTip();"><img src="<?=$engine_url?>/_manage/image/icon/favorite.gif" alt=""> 퀵메뉴로 추가</a>
			<span class="quickguide"></span>
		</p>
	</div>
	<ul id="search_manual">
		<li><a href="#help" class="<?=$_manual_icon?>" onclick="toggleManual(false, 2); return false;">도움말</a></li>
	</ul>
</div>
<div class="clear"></div>
<div class="box_title first">
	<h2 class="title">
		<?=$current_menu->val('name')?>
	</h2>
	<?php if ($_GET['order_stat_group'] == 9) { ?>
	<div class="btns">
	<span class="box_btn_s"><input type="button" value="주문수집" onclick="getOpenmarketOrders();"></span>
	</div>
	<?php } ?>
	<?php if (!$_GET['order_stat_group']) { ?>
	<dl class="total">
		<dt class="hidden">현황</dt>
		<dd><?=$_order_stat[1]?> <strong style="color:#<?=$_order_color[1]?>"><?=$stats[1]?></strong></dd>
		<dd><?=$_order_stat[2]?> <strong style="color:<?=$_order_color[2]?>"><?=$stats[2]?></strong></dd>
		<dd><?=$_order_stat[3]?> <strong style="color:<?=$_order_color[3]?>"><?=$stats[3]?></strong></dd>
		<dd><?=$_order_stat[4]?> <strong style="color:<?=$_order_color[4]?>"><?=$stats[4]?></strong></dd>
	</dl>
	<?php } ?>
</div>