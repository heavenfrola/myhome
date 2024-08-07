<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  실시간접속현황
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_manage/main/main_box.php";

	/* +----------------------------------------------------------------------------------------------+
	' |  세션 분석
	' +----------------------------------------------------------------------------------------------+*/
	$monly = addslashes($_GET['monly']);
	$vpage = addslashes($_GET['vpage']);
	$ccode = addslashes($_GET['ccode']);
	$cname = addslashes($_GET['cname']);

	$v_count = array();
	$v_cate = array("main" => "메인", "shop" => "상품리스트", "detail" => "상품상세", "order" => "주문", "bbs" => "게시판" ,"mypage" => "마이페이지", "manage" => "관리자", "etc" => "기타페이지");
	$v_array_shop = array("shop/big_section.php", "coordi/coordi_list.php", "coordi/coordi_view.php");
	$v_array_detail = array("shop/detail.php", "coordi/coordi_view.php");
	$v_array_order = array("shop/cart.php", "shop/order.php");
	$v_array_bbs = array("shop/product_review_list.php", "shop/product_review.php", "shop/product_qna_list.php", "shop/product_qna.php");

	if($_GET['monly'] == 'Y') $w .= " and member_no > 0";
	$w.=" and left(`remote_addr`, 12) != '121.254.156.' and left(`remote_addr`, 12) != '121.254.159.' and left(`remote_addr`, 8) != '27.1.44.' ";

	$session_time = $now - 300;
	$res = $pdo->iterator("select *, if(member_no>0, (select member_id from $tbl[member] where no=s.member_no), '') as member_id from wm_session s where accesstime >= '$session_time' $w");
    foreach ($res as $session) {
		$script_name = preg_replace('/^\/|\?.*$/', '', $session['page']);
		list($v_dir, $v_file) = explode("/", $script_name);

		$v_code = "";
		if($v_dir == 'main' || $v_dir == '') $v_code = 'main';
		if($v_dir == 'mypage') $v_code = 'mypage';
		if($v_dir == 'board' || in_array($script_name, $v_array_bbs)) $v_code = 'bbs';
		if(in_array($script_name, $v_array_detail)) $v_code = 'detail';
		if(in_array($script_name, $v_array_order)) $v_code = 'order';
		if(in_array($script_name, $v_array_shop) || (!$v_code && $v_dir == 'shop')) $v_code = 'shop';
		if(preg_match('/^\_manage/', $script_name)) $v_code = 'manage';
		if(!$v_code) $v_code = 'etc';

		$data = $db_session_handler->unserialize($session['data']);
		$session['conversion'] = $data['conversion'];

		$session['v_code'] = $v_code;
		$v_count[$v_code]++;
		$v_count_total++;

		if($_GET['vpage'] && $_GET['vpage'] != $v_code) {
			continue;
		}

		if($v_code == 'detail') {
			$session['hash'] = preg_replace("/.*pno=([^&]+).*$/","$1", $session['page']);
			$prd_name = cutstr(stripslashes(strip_tags($pdo->row("select `name` from `$tbl[product]` where `hash` = '$session[hash]'"))),50);
			$session['prd'] = "<a href='$root_url/shop/detail.php?pno=$session[hash]' target='_blank'>$prd_name</a>";
		}

		$sidx++;
		$_nsession[$sidx] = $session;
	}

	$v_count_total = count($_nsession);
	if($_nsession) krsort($_nsession);

?>
<div class="box_title first">
	<h2 class="title">실시간접속현황</h2>
</div>
<table class="tbl_col tbl_col_bottom">
	<caption class="hidden">실시간접속현황</caption>
	<thead>
		<tr>
			<th scope="col">전체</th>
			<?foreach($v_cate as $ccode => $cname){?>
			<th scope="col"><?=$cname?></th>
			<?}?>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?=number_format($v_count_total)?></td>
			<?foreach($v_cate as $ccode => $cname){?>
			<td><?=number_format($v_count[$ccode])?></td>
			<?}?>
		</tr>
	</tbody>
</table>
<div class="box_title">
	<h2 class="title">검색</h2>
</div>
<form method="get" action="./index.php">
	<table class="tbl_row">
		<caption class="hidden">검색</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">검색</th>
			<td>
				<input type="hidden" name="body" value="<?=$body?>">
				<select name="monly" onchange="this.form.submit()">
					<option value="">전체접속</option>
					<option value="Y" <?=checked($monly,"Y",1)?>>회원만</option>
				</select>
				<select name="vpage"onchange="this.form.submit()">
					<option value="">전체페이지</option>
					<?foreach($v_cate as $ccode => $cname){?>
					<option value="<?=$ccode?>" <?=checked($vpage, $ccode, 1)?>><?=$cname?></option>
					<?}?>
				</select>
			</td>
		</tr>
	</table>
</form>
<div class="box_middle3 left">현재 데이터는 최대 5분까지 오차가 있을수 있습니다.</div>
<table class="tbl_col tbl_col_bottom">
	<caption class="hidden">검색 리스트</caption>
	<colgroup>
		<col style="width:60px">
	</colgroup>
	<thead>
		<tr>
			<th scope="col">순번</th>
			<th scope="col">페이지</th>
			<th scope="col">아이디</th>
			<th scope="col">접속시간</th>
			<th scope="col">최종갱신</th>
			<th scope="col">아이피</th>
			<th scope="col">상품상세</th>
		</tr>
	</thead>
	<tbody>
		<?
			if($_nsession) {
			foreach ($_nsession as $key => $val) {
				$nidx++;
				$class = ($nidx % 2 == 0) ? "tcol2" : "tcol3";
		?>
		<tr>
			<td><?=$nidx?></td>
			<td class="left"><?=$v_cate[$val[v_code]]?></td>
			<td><a href="javascript:;" onClick="viewMember('<?=$val['member_no']?>','<?=$val['member_id']?>')"><?=$val['member_id']?></a></td>
			<td><?=date("d일 H:i:s", $val[regdate])?></td>
			<td><?=date("d일 H:i:s", $val[accesstime])?></td>
			<td><a href="http://www.apnic.net/apnic-bin/whois.pl?searchtext=<?=$val['remote_addr']?>" target="_blank" title="IP 정보"><?=$val['remote_addr']?></a></td>
			<td><?=dispConversion($val[conversion])?> <?=$val['prd']?></td>
		</tr>
		<?}}?>
	</tbody>
</table>