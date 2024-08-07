<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  주문상품 수량옵션 통계
	' +----------------------------------------------------------------------------------------------+*/

	set_time_limit(0);

	include $engine_dir."/_manage/order/order_list.php";

	$sql = "select
				p.name, p.origin_name, p.sell_prc, p.origin_prc, p.ea_type, p.ea as prd_ea,
				pr.provider, pr.arcade, pr.floor, pr.plocation, pr.account1, pr.account2, pr.ptel, pr.pcell,
				(select name from $tbl[category] x where no = p.big) as big,
				(select name from $tbl[category] x where no = p.mid) as mid,
				(select name from $tbl[category] x where no = p.small) as small,
				c.option, sum(c.buy_ea) as buy_ea, c.complex_no, c.option_idx, c.pno,
				if(c.complex_no > 0, curr_stock(c.complex_no), '-') as 'stock'
			from $tbl[order_product] c inner join $tbl[product] p on c.pno = p.no
				inner join $tbl[order] a using(ono)
				left join $tbl[provider] pr on p.seller_idx = pr.no
			where a.stat != 11 $w
			group by c.pno, c.option
			order by p.seller_idx = 0, pr.provider, p.name, c.option
			";

	$res = $pdo->iterator($sql);

	function getOptionStock($data) {
		global $tbl, $pdo;

		if($data['complex_no'] > 0) return $data['stock']; // 복합옵션일 경우

		if(!$data['option_idx'] || $data['ea_type'] == 2) {
			$stock = ($data['ea_type'] == 2) ? '무제한' : $data['prd_ea'];
		} else {
			$poption_idx = array();
			$spt = explode('<split_big>', $data['option_idx']);
			foreach($spt as $osmall) {
				$spt2 = explode('<split_small>', $osmall);
				$poption_idx[] = $spt2[1];
			}
			if(count($poption_idx) > 0) {
				$oino = implode(',', $poption_idx);
				$stock = $pdo->assoc("select ea_ck, min(ea) as 'stock' from $tbl[product_option_item] where no in ($oino) and ea_ck='Y'");
				$stock = $stock ? number_format($stock['stock']) : $data['prd_ea'];
			}
		}

		return $stock;
	}

	function strips($str) {
		$str = stripslashes(strip_tags($str));
		return $str;
	}

	if(preg_match('/\.exe/', $body)) return;

?>
<?if($_GET['mode'] != 'print'){?>
<div class="box_title first">
	<h2 class="title">주문 상품수량 통계</h2>
</div>
<div class="box_middle center">
	<span class="box_btn blue"><input type="button" value="엑셀로 저장" onclick="location.href='./?body=order@order_product.exe&mode=excel<?=$xls_query?>'"></span>
	<span class="box_btn"><input type="button" value="인쇄" onclick="opPrint()"></span>
	<span class="box_btn"><input type="button" value="재검색" onclick="location.href='./?body=order@order_list<?=$xls_query?>'"></span>
</div>
<?}?>
<table class="tbl_col">
	<caption class="hidden">주문상품 수량옵션 통계</caption>
	<colgroup>
		<col>
		<col span="3">
		<col style="width:100px;">
		<col style="width:100px;">
	</colgroup>
	<thead>
		<tr>
			<th scope="col">상품명</th>
			<th scope="col">장기명</th>
			<th scope="col">사입처</th>
			<th scope="col">옵션</th>
			<th scope="col">주문수량</th>
			<th scope="col">재고수량</th>
		</tr>
	</thead>
	<tbody>
		<?php
            foreach ($res as $data) {
				$data['provider']	= strips($data['provider']);
				$data['arcade']		= strips($data['arcade']);
				$data['floor']		= strips($data['floor']);
				$data['plocation']	= strips($data['plocation']);
				$data['name']		= strips($data['name']);
				$data['origin_name']= strips($data['origin_name']);
				$data['stock']		= getOptionStock($data);
				$data['option']		= parseOrderOption($data['option']);
				$data['category']	= strips($data['big']);
				$data['sell_prc']	= number_format($data['sell_prc']);
				$data['account1']	= strips($data['account1']);
				$data['account2']	= strips($data['account2']);
				if($data['mid']) $data['category'] .= ' > '.strips($data['mid']);
				if($data['small']) $data['category'] .= ' > '.strips($data['small']);
		?>
			<tr>
				<td class="left"><a href="?body=product@product_register&pno=<?=$data['pno']?>" target="_blank"><?=$data['name']?></a></td>
				<td class="left"><?=$data['origin_name']?></td>
				<td class="left"><?=$data['provider']?></td>
				<td class="left"><?=$data['option']?></td>
				<td class="p_color2"><strong><?=$data['buy_ea']?></strong></td>
				<td><?=$data['stock']?></td>
			</tr>
		<?}?>
	</tbody>
</table>


<script type="text/javascript">
	function opPrint() {
		var	w = window.open('./?body=order@order_product.frm&mode=print<?=$xls_query?>','opPrint','status=no, width=800px, height=600px, resize=yes');
	}
</script>