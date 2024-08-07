<?PHP

	$w = '';

	// 검색
	$_search_type[buyer_name]='주문자 이름';
	$_search_type[buyer_cell]='주문자 전화';
	$_search_type[buyer_phone]='주문자 휴대폰';
	$_search_type[member_id]='회원아이디';
	$_search_type[bank_name]='입금자';
	$_search_type[addressee_name]='수령인 이름';
	$_search_type[ono]='주문번호';
	$_search_type[dlv_code]='송장번호';
	$_search_type[buyer_email]='주문자 이메일';
	$_search_type[addressee_addr1]='수령인 주소';
	$_search_type[addressee_addr2]='수령인 상세 주소';

	$search = $_GET['search'];
	$all_date = $_GET['all_date'];
	$start_date = $_GET['start_date'];
	$finish_date = $_GET['finish_date'];
	$search_date_type = numberOnly($_GET['search_date_type']);

	if(!$_GET[search_date_type]){
		$_mng_sset=mySearchSet("ordersearch");
		$_stat=explode("@", $_mng_sset[ostat]);
		for($ii=0; $ii<count($_stat); $ii++){
			if(!$_stat[$ii]) continue;
			$stat[]=$_stat[$ii];
		}
		$search_date_type=$_mng_sset[period];
		$all_date=$_mng_sset[period_all];
		if(!$all_date) {
			$_default_start_date = ($_mng_sset['seach_date_period']) ? $_mng_sset['seach_date_period'] : '-15 days';
			$start_date = date('Y-m-d', strtotime($_default_start_date, $now));
			$finish_date = date('Y-m-d', $now);
		}
		$pay_type=$_mng_sset[paytype];
		$orderby=$_mng_sset[orderby];
		$search_type=$_mng_sset[search];
		unset($_mng_sset);
	}

	if(!$start_date || !$finish_date || !$search_date_type) {
		$all_date="Y";
	}
	if(!$all_date) {
		$_start_date = strtotime($start_date);
		$_finish_date = strtotime($finish_date)+86399;
		$w.=" and a.date{$search_date_type} between '$_start_date' and '$_finish_date'";
	}
	if(!$start_date || !$finish_date) {
		$start_date = $finish_date = date("Y-m-d",$now);
	}

	$search_type = $_GET['search_type'];
	$search_str = addslashes(trim($_GET['search_str']));
	if($_search_type[$search_type] && $search_str) {
		$w.=" and a.$search_type like '%$search_str%'";
		if($search_type!="member_id" && $search_type!="buyer_name" && $search_type!="ono" && $search_type!="dlv_code") {
			$sfield .= ", a.$search_type";
		}
	}

	include_once $engine_dir."/_engine/include/shop.lib.php";

	$oby = numberOnly($_GET['oby']);
	if(!$oby) $oby=1;
	$_oby[1] = " a.date1 asc";
	$_oby[2] = " a.date2 asc";

	$w .= " and a.stat=4";
	$w .= " and a.checkout='N'";

    if($cfg['use_talkbuy'] == 'Y') $w .= " and external_order != 'talkpay'";
	if($cfg['n_smart_store'] == 'Y') $w .= " and a.smartstore='N'";

	if($search) {
		$res = $pdo->iterator("
			select distinct a.no, date1, date2, ono, buyer_name, addressee_name $afield
				from $tbl[order] a inner join $tbl[order_product] b using(ono)
				where b.stat=4 $w order by".$_oby[$oby]
		);
		$total = $res->rowCount();
	}

?>
<!-- 검색 폼 -->
<form method="get" action="./" id="search">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="search" value="true">
	<div class="box_title first">
		<h2 class="title">배송완료</h2>
	</div>

	<div id="search">
		<div class="box_search">
			<div class="box_input">
				<div class="select_input shadow full">
					<div class="select">
						<?=selectArray($_search_type,"search_type",2,"",$search_type)?>
					</div>
					<div class="area_input">
						<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" placeholder="검색어를 입력해주세요.">
					</div>
				</div>
			</div>
		</div>
		<table class="tbl_search">
			<caption class="hidden">배송완료</caption>
			<colgroup>
				<col style="width:15%">
			</colgroup>
			<tr>
				<th scope="row">기간</th>
				<td>
					<select name="search_date_type">
						<option value="1" <?=checked($search_date_type,1,1)?>>주문일</option>
						<option value="2" <?=checked($search_date_type,2,1)?>>입금일</option>
						<option value="4" <?=checked($search_date_type,4,1)?>>상품발송일</option>
						<option value="5" <?=checked($search_date_type,5,1)?>>배송완료일</option>
					</select>
					<label class="p_cursor"><input type="checkbox" name="all_date" value="Y" <?=checked($all_date,"Y")?> onClick="searchDate(this.form)"> 전체 기간</label>
					<input type="text" name="start_date" value="<?=$start_date?>" size="10" class="input datepicker"> ~ <input type="text" name="finish_date" value="<?=$finish_date?>" size="10" class="input datepicker">
					<script type="text/javascript">
					searchDate(document.getElementById('search'));
					</script>
				</td>
			</tr>
		</table>
		<div class="box_bottom top_line">
			<span class="box_btn blue"><input type="submit" value="검색"></span>
			<span class="box_btn"><input type="button" value="초기화" onclick="location.href='./?body=<?=$_GET[body]?>&order_stat_group=<?=$order_stat_group?>'"></span>
		</div>
	</div>
</form>
<!-- //검색 폼 -->
<!-- 검색 테이블 -->
<?if($search){?>
<div class="box_title">
	총 <strong id="total_prd"><?=number_format($total)?></strong>개의 배송중 주문이 검색되었습니다.
</div>
<form name="prdFrm" method="get" action="./">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="oby" value="<?=$oby?>">
	<table class="tbl_col">
		<caption class="hidden">배송완료</caption>
		<colgroup>
			<col style="width:50px">
			<col style="width:80px">
			<col style="width:80px">
			<col style="width:130px">
			<col style="width:90px">
			<col style="width:90px">
			<col>
			<col>
			<col style="width:80px">
			<col>
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><input type="checkbox" id="ono<?=$data[no]?>" value="<?=$data[no]?>" onClick="$(':checkbox[name^=dlv_prd]').prop('checked',this.checked);"></th>
				<th scope="col">주문</th>
				<th scope="col">입금</th>
				<th scope="col">주문번호</th>
				<th scope="col">주문자</th>
				<th scope="col">수취인</th>
				<th scope="col" colspan="6">배송 완료 상품 선택</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$idx=0;
                foreach ($res as $data) {
					$idx++;
					$res2 = $pdo->iterator("select * from `$tbl[order_product]` where `ono`='$data[ono]' and `stat`=4 order by `dlv_code`");
					$p_total = $res2->rowCount();
					if($p_total == 0) $p_total = 1;
			?>
			<tr>
				<td rowspan="<?=$p_total?>">
					<input type="hidden" name="ono[]" value="<?=$data[no]?>">
					<input type="checkbox" id="ono<?=$data[no]?>" value="<?=$data[no]?>" name="dlv_prd" onClick="checkOrdPrd(this,'<?=$data[no]?>',<?=$p_total?>)">
				</td>
				<td rowspan="<?=$p_total?>"><?=date("y/m/d",$data[date1])?></td>
				<td rowspan="<?=$p_total?>"><?=date("y/m/d",$data[date2])?></td>
				<td rowspan="<?=$p_total?>"><a href="javascript:;" onClick="viewOrder('<?=$data[ono]?>')"><?=$data[ono]?></a></td>
				<td rowspan="<?=$p_total?>"><?=stripslashes($data[buyer_name])?></td>
				<td rowspan="<?=$p_total?>"><?=stripslashes($data[addressee_name])?></td>
				<?php
					$i=1;
                    foreach ($res2 as $prd) {
						$prd['name'] = cutStr(stripslashes(strip_tags($prd['name'])), 50);

						$dlv_code = '';
						if($prd['dlv_code']) {
							$dlv = getDlvUrl($prd);
							$dlv_code = $dlv['name']."(<a href=\"$dlv[url]\" target=\"_blank\">$prd[dlv_code]</a>)";
						}

						if($prd['option']) {
							$prd['option_str']=str_replace("<split_big>",$split_big,$prd['option']);
							$prd['option_str']=str_replace("<split_small>",":",$prd['option_str']);
							$prd['option_str']=$opt_deco1.$prd['option_str'].$opt_deco2;
						}

						if($i != 1) echo '<tr>';

                        if ($prd['set_pno'] > 0) {
                            $setname = $pdo->row("select name from {$tbl['product']} where no=?", array($prd['set_pno']));
                            if ($setname) {
                                $prd['set_name'] .= stripslashes($setname);
                            }
                        }
				?>
				<?if($prd['no'] > 0) {?>
					<input type="checkbox" id="dlv_prd<?=$data[no].$i?>" name="dlv_prd<?=$data[no].$i?>" style="display:none" value="<?=$prd[no]?>">
				<?}?>
				<td class="left">
                    <a href="./?body=product@product_register&pno=<?=$prd[pno]?>" target="_blank"><?=$prd['name']?></a>
                    <?php if ($prd['set_name']) { ?>
                    <div class="explain"><span class="set_label">SET</span> <?=$prd['set_name']?></div>
                    <?php } ?>
                </td>
				<td class="left"><?=$prd['option_str']?></td>
				<td><?=$prd['buy_ea']?></td>
				<td><?=$dlv_code?></td>
			</tr>
			<?
				$i++;
				}
				if($i == 1) echo "<td colspan=\"5\"></td></tr>";
			}?>
		</tbody>
	</table>
	<div class="box_middle2">
		<p class="p_color2 left">전체상품이 발송된 주문은 적립금이 적립되며 메일이 발송됩니다</p>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="button" value="배송완료처리" onclick="inputDlv(document.prdFrm)"></span>
	</div>
</form>
<?} else {?>
<div class="box_full">
	<ul class="list_msg">
		<li><?=$_order_stat[2]?>된 주문을 검색하여 배송시작 처리합니다.</li>
		<li>기준일과 기간을 입력한 뒤 검색하세요.</li>
		<li><strong class="p_color2">너무 많은 주문건이 나올 경우 작업이 원활하지 않을 수 있습니다.</strong></li>
	</ul>
</div>
<?}?>
<!-- //검색 테이블 -->

<script type="text/javascript">
function checkOrdPrd(o,no,total){
	if (o.checked==true) {
		ex=1;
	} else
		ex=2;
		for (i=1; i<=total; i++) {
			var prd=document.getElementById('dlv_prd'+no+i);
			if (ex==1) prd.checked=true;
			else prd.checked=false;
		}
}

function inputDlv(f){
	f.target=hid_frame;
	f.method='post';
	f.body.value='order@delivery_finish.exe';
	f.submit();
}

function schDate(o){
	sf=document.prdFrm;
	sf.oby.value=o;
	sf.submit();
}
</script>