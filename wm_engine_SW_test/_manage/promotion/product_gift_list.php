<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  사은품 관리
	' +----------------------------------------------------------------------------------------------+*/

	$addq=" and `delete`!='Y'";
	$sql="select * from `".$tbl['product_gift']."` where 1 $addq order by `no` desc";

	// 페이징 설정
	include $engine_dir."/_engine/include/paging.php";
	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);

	if($page<=1) $page=1;
	$row=20;
	$block=10;
	$QueryString="&body=".$_GET[body];

	$NumTotalRec = $pdo->row(str_replace("select *","select count(`no`)",$sql));
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult[LimitQuery];

	$pg_res=$PagingResult[PageLink];
	$res = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1));

    setListURL('prdgiftList');

?>
<div class="box_title first">
	<h2 class="title">사은품 관리</h2>
</div>
<table class="tbl_col">
	<caption class="hidden">사은품 관리</caption>
	<colgroup>
		<col style="width:40px">
		<col>
		<col style="width:210px">
		<col style="width:100px">
		<col style="width:100px">
		<col style="width:100px">
	</colgroup>
	<thead>
		<tr>
			<th scope="col">번호</th>
			<th scope="col">사은품명</th>
			<th scope="col">증정조건</th>
			<th scope="col">등록일</th>
			<th scope="col">사용여부</th>
			<th scope="col">삭제</th>
		</tr>
	</thead>
	<tbody>
		<?php

            foreach ($res as $data) {
				$name=stripslashes($data[name]);
				$reg_date=date("Y/m/d",$data[reg_date]);

				$use_on = ($data['use'] == 'Y') ? 'on' : '';

				$price_str = array();
				if($data['price_limit'] > 0) $price_str[] = parsePrice($data['price_limit'], true).' '.$cfg['currency_type'].' 이상 ';
				if($data['price_max'] > 0) $price_str[] = parsePrice($data['price_max'], true).' '.$cfg['currency_type'].' 이하 ';
				$price_str = implode(' ~ ', $price_str);
				if(!$price_str) $price_str = '제한 없음';
		?>
		<tr>
			<td><?=$idx?></td>
			<td class="left"><a href="./?body=promotion@product_gift_register&gno=<?=$data[no]?>"><?=$name?></a></td>
			<td><?=$price_str?></td>
			<td><?=$reg_date?></td>
			<td>
				<div class="switch <?=$use_on?>" onclick="toggleUseGift(<?=$data['no']?>, $(this))" data-expired="<?=$expired?>"></div>
			</td>
			<td><span class="box_btn_s gray"><input type="button" value="삭제" onclick="delPrdGift('<?=$data[no]?>')"></span></td>
		</tr>
		<?
				$idx--;
			}
		?>
	</tbody>
</table>
<div class="box_bottom">
	<?=$pg_res?>
</div>
<div class="box_middle2 right">
	<span class="box_btn blue"><input type="button" value="사은품 등록" onclick="goM('promotion@product_gift_register');return false;"></span>
</div>
<form name="delPrdGiftFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onSubmit="return">
	<input type="hidden" name="body" value="promotion@product_gift_delete.exe">
	<input type="hidden" name="gno" value="">
</form>
<script type="text/javascript">
	function toggleUseGift(no, o) {
		$.post('?body=promotion@product_gift_register.exe', {'exec':'toggle', 'no':no}, function(r) {
			if(r.changed == 'Y') {
				o.addClass('on');
			} else {
				o.removeClass('on');
			}
		});
	}
</script>