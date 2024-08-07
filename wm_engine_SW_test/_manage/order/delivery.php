<?PHP

	$w = " and p.stat in (2,3)";

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

	if(!$all_date) {
		$_start_date = strtotime($start_date);
		$_finish_date = strtotime($finish_date)+86399;
		$w.=" and o.`date".$search_date_type."` between '$_start_date' and '$_finish_date'";
	}
	if(!$start_date || !$finish_date) {
		$start_date = $finish_date = date("Y-m-d",$now);
	}

	$search_type = $_GET['search_type'];
	$search_str = addslashes(trim($_GET['search_str']));
	if($_search_type[$search_type] && $search_str) {
		$w.=" and o.`$search_type` like '%$search_str%'";
		if($search_type!="member_id" && $search_type!="buyer_name" && $search_type!="ono" && $search_type!="dlv_code") {
			$sfield.=",`$search_type`";
		}
	}

	if($_GET['dlv_hold']) {
		$w .= " and p.dlv_hold!='Y'";
		$w2 .= " and a.dlv_hold!='Y'";
	}

	$oby = numberOnly($_GET['oby']);
	if(!$oby) $oby=1;
	$_oby[1]=" `date1` asc";
	$_oby[2]=" `date2` asc";

	if($admin['level'] == 4) { // 업체별 배송 사용시
		$w .= " and p.partner_no='$admin[partner_no]'";
	}

	$sql = "select o.* from $tbl[order] o inner join $tbl[order_product] p using(ono) where 1 $w group by ono order by null";

	if($search) {
		include $engine_dir."/_engine/include/paging.php";

		$page = numberOnly($_GET['page']);
		if($page<=1) $page=1;
		$row=numberOnly($row);
		if($row<1 || $row>500) $row=500;
		$block=10;

		$NumTotalRec = $pdo->row("select count(distinct ono) from $tbl[order] o inner join $tbl[order_product] p using(ono) where 1 $w");

		$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
		$PagingInstance->addQueryString($QueryString);
		$PagingResult=$PagingInstance->result($pg_dsn);
		$sql.=$PagingResult[LimitQuery];

		$pg_res = $PagingResult[PageLink];
		$res = $pdo->iterator($sql);

		$idx=$NumTotalRec-($row*($page-1));
	}

?>
<style type="text/css">
.box_setup p {
	margin: 3px 0;
}
</style>
<!-- 검색 폼 -->
<form method="get" action="./" id="search">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="search" value="true">
	<div class="box_title first">
		<h2 class="title">배송시작</h2>
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
			<caption class="hidden">배송시작</caption>
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
			<tr>
				<th scope="row">배송보류</th>
				<td>
					<label class="p_cursor"><input type="checkbox" name="dlv_hold" value="N" <?=checked($_GET['dlv_hold'], 'N')?>> 전체 배송보류된 주문을 검색에서 제외</label>
				</td>
			</tr>
		</table>
		<div class="box_bottom top_line">
			<span class="box_btn blue"><input type="submit" value="검색"></span>
			<span class="box_btn"><input type="button" value="초기화"onclick="location.href='./?body=<?=$_GET[body]?>&order_stat_group=<?=$order_stat_group?>'"></span>
		</div>
	</div>
</form>
<!-- //검색 폼 -->
<!-- 검색 테이블 -->
<div id="process_info">
	<?if($search) {?>
	<div class="box_title">
		총 <strong id="total_prd"><?=number_format($NumTotalRec)?></strong>개의 배송 할 주문이 검색되었습니다.
		<div class="btns">
			<span class="box_btn_s icon excel"><input type="button" value="엑셀다운" onclick="deliveryExcel()"></span>
		</div>
	</div>
	<form id="prdFrm" name="prdFrm" method="get" action="./">
		<input type="hidden" name="body" value="<?=$body?>">
		<input type="hidden" name="oby" value="<?=$oby?>">
		<input type="hidden" name="dlv_csv" value="1">
		<table class="tbl_col">
			<caption class="hidden">배송시작</caption>
			<colgroup>
				<col style="width:50px">
				<col style="width:50px">
				<col style="width:100px">
				<col style="width:120px">
				<col style="width:80px">
				<col style="width:150px">
				<col>
				<col style="width:50px">
				<col style="width:70px">
			</colgroup>
			<thead>
				<tr>
					<th scope="col"><input type="checkbox" checked onclick="$('.check_ono').prop('checked', this.checked);"></th>
					<th scope="col">주문</th>
					<th scope="col">입금</th>
					<th scope="col">주문번호</th>
					<th scope="col">주문자</th>
					<th scope="col">운송장번호</th>
					<th scope="col">미배송상품</th>
					<th scope="col">수량</th>
					<th scope="col">배송보류</th>
				</tr>
			</thead>
			<tbody>
				<?php
					$idx=0;
                    foreach ($res as $data) {
						if(!$prev) $prev = $data['ono'];

						$idx++;
						$res2 = $pdo->iterator("select a.*, b.ea_type, b.`upfile3`,b.`w3`,b.`h3`,b.`updir`, a.complex_no, if(ea_type=1, curr_stock(a.complex_no), b.ea) as ea from `$tbl[order_product]` a left join `$tbl[product]` b on a.`pno` = b.`no` where `ono`='$data[ono]' and a.`stat`<4 $w2"); // 미배송상품 : 부분배송 아닐 경우라도 가능케
						$prows = $res2->rowCount();

						// 사은품 출력
						$data['order_gift'] = preg_replace('/@?_[0-9]+/', '', $data['order_gift']); // erp 연동상품 제외
						if($data['order_gift']) {
							$data['order_gift'] = preg_replace('/^@|@$/', '', $data['order_gift']);
							$gift_rows = explode('@', $data['order_gift']);
							$prows += count($gift_rows);
							$gift = implode(',', $gift_rows);

							$gift_res = $pdo->iterator("select * from $tbl[product_gift] where no in ($gift)");
						}

						$i=1;
						$prd_total_num=0;
                        foreach ($res2 as $prd) {
							if($cfg['use_partner_delivery'] == 'Y') { // 업체별 배송 사용 시
								if($prd['partner_no'] != $admin['partner_no'] && !($admin['partner_no'] == 0 && $prd['dlv_type'] == 1)) {
									$prd = array(
										'name' => '타사상품',
										'stat' => $prd['stat'],
									);
								}
							}

							if($prd['complex_no']) {
								$complex_data = $pdo->assoc("select * from erp_complex_option where complex_no='$prd[complex_no]'");
								if($complex_data['del_yn'] == 'Y') $prd['deleted'] = "<span class='desc3'>[삭제된 윙포스 옵션 : 바코드 $complex_data[barcode]]</span>";
							}
							if($prd['option']) {
								$prd['option_str']=str_replace("<split_big>",$split_big,$prd['option']);
								$prd['option_str']=str_replace("<split_small>",":",$prd['option_str']);
								$prd['option_str']=$opt_deco1.$prd['option_str'].$opt_deco2;
							}
							$img=prdImg(3,$prd,70,70);

							$prd_total_num++;

							$style = '';
							if($prev != $data['ono']) {
								$prev = $data['ono'];
								$style = "border-top: double 3px #ccc;";
							}

                            if ($prd['set_pno'] > 0) {
                                $setname = $pdo->row("select name from {$tbl['product']} where no=?", array($prd['set_pno']));
                                if ($setname) {
                                    $prd['set_name'] .= stripslashes($setname);
                                }
                            }
				?>
				<tr>
					<?if($i == 1) {?>
					<td rowspan="<?=$prows?>">
						<input type="hidden" name="ono[]" value="<?=$data[no]?>">
						<input type="hidden" name="ono2[<?=$data[no]?>]" value="<?=$data[ono]?>">
						<label class="p_cursor"><input type="checkbox" name="check_ono[]" class="check_ono" checked value="<?=$data['ono']?>"></label>
					</td>
					<td rowspan="<?=$prows?>"><?=date("m/d",$data[date1])?></td>
					<td rowspan="<?=$prows?>"><?=date("m/d",$data[date2])?></td>
					<td rowspan="<?=$prows?>">
						<a href="javascript:;" onClick="viewOrder('<?=$data[ono]?>')"><?=$data[ono]?></a>
					</td>
					<td rowspan="<?=$prows?>"><?=stripslashes($data[buyer_name])?></td>
					<td rowspan="<?=$prows?>"><input type="text" name="dlv_code<?=$data[no]?>" value="" class="input" size="15"></td>
					<?}?>
					<td class="left">
						<div class="box_setup">
							<div class="thumb">
								<?if($prd['no']) {?>
								<a href="?body=product@product_register&pno=<?=$prd[pno]?>" target="_blank"><img src="<?=$img[0]?>" <?=$img[1]?>></a>
								<?}?>
							</div>
							<p><?=$prd['deleted']?></p>
							<p>
								<?if($prd['no'] > 0) {?>
								<input type="checkbox" class="oprd_<?=$data['ono']?>" name="dlv_prd<?=$data[no]?>[]" value="<?=$prd[no]?>" <?=($prd['dlv_hold'] == 'Y') ? '' : 'checked'?>>
								<input type="hidden" name="total_prd<?=$data['no']?>[]" value="<?=$prd['no']?>">
								<?}?>
								<a href="./?body=product@product_register&pno=<?=$prd[pno]?>" target="_blank"><strong><?=cutStr(strip_tags(stripslashes($prd['name'])),50)?></strong></a>
							</p>
							<p><?=$prd['option_str']?></p>
                            <?php if ($prd['set_name']) { ?>
                            <div class="explain"><span class="set_label">SET</span> <?=$prd['set_name']?></div>
                            <?php } ?>
						</div>
						<input type="hidden" name="prd_total_num[<?=$data[no]?>]" value="<?=$prd_total_num?>">
					</td>
					<td><?=$prd['buy_ea']?></td>
					<td>
						<?
							$tgl_val = $prd['dlv_hold'] == 'Y' ? '지연' : '정상';
							$tbl_bg = $prd['dlv_hold'] == 'Y' ? 'gray' : 'blue';
						?>
						<?if($prd['no'] > 0) {?>
						<span class="box_btn_s <?=$tbl_bg?>"><input type="button" value="<?=$tgl_val?>" onclick='toggleHold(this, "<?=$prd['ono']?>", <?=$prd['no']?>)'></a>
						<?}?>
					</td>
				</tr>
				<?
					$i++;
					}
					if($data['order_gift']) {
                    foreach ($gift_res as $gdata) {
						$gimg = prdimg('', $gdata, 70, 70);
				?>
				<tr>
					<td class="left" colspan="3">
						<div class="box_setup">
							<div class="thumb"><img src="<?=$gimg[0]?>" <?=$gimg[1]?>></div>
							<p><?=$gdata['name']?></p>
							<p class="p_color2">사은품</p>
						</div>
					</td>
				</tr>
				<?
					}
					}
					}
				?>
			</tbody>
		</table>
		<div class="box_middle2 left">
			<p class="explain">송장 번호를 입력한 주문만 처리됩니다</p>
			<ul>
				<li>
					<label class="p_cursor"><input type="checkbox" name="chg_stat" value="1" checked> 배송중으로 변경</label>
					<label class="p_cursor"><input type="checkbox" name="mail_snd" value="1" checked> 상품출고 메일/SMS 발송 </label>(상품발송 <a href="javascript:goM('member@sms_config')"><u>SMS</u></a>/<a href="javascript:goM('member@email_config')"><u>메일</u></a> 설정시)
				</li>
				<li>
					<span>택배사 선택 :</span>
					<select name="dlv_no">
						<?php
                        $asql = ($admin['partner_no'] > 0) ? "partner_no='{$admin['partner_no']}'" : "partner_no in (0, '')";
						$dres = $pdo->iterator("select * from {$tbl['delivery_url']} where $asql order by `sort`,`no` desc");
                        foreach ($dres as $dlv) {
						?>
						<option value="<?=$dlv[no]?>" <?=checked($dlv[no],$data[dlv_no],1)?>><?=$dlv[name]?></option>
						<?}?>
					</select>
				</li>
			</ul>
		</div>
		<div class="box_bottom">
			<?=$pg_res?>
			<div class="left_area">
				<span class="box_btn blue"><input type="button" value="배송시작처리" onclick="inputDlv(document.prdFrm);"></span>
			</div>
		</div>
	</form>
	<?} else {?>
	<div class="box_full">
		<ul class="list_msg left">
			<li><?=$_order_stat[2]?>된 주문을 검색하여 배송시작 처리합니다.</li>
			<li>기준일과 기간을 입력한 뒤 검색하세요.</li>
			<li><strong class="p_color2">너무 많은 주문건이 나올 경우 작업이 원활하지 않을 수 있습니다.</strong></li>
		</ul>
	</div>
	<?}?>
</div>
<!-- //검색 테이블 -->

<script type="text/javascript">
	function checkOrdPrd(o,no,total){
		if (isEmpty(o.value)) {
			ex=1;
		}
		else ex=2;
		for (i=1; i<=total; i++) {
			var prd=document.getElementById('dlv_prd'+no+i);
			if (ex==1) prd.checked=false;
			else prd.checked=true;
		}
	}

	function inputDlv(f){
        printLoading();

		f.target=hid_frame;
		f.method='post';
		f.body.value='order@delivery.exe';
		f.submit();
	}

	function schDate(o){
		f=document.prdFrm;
		f.oby.value=o;
		f.submit();
	}

	function toggleHold(obj, ono, no) {
		var obj = $(obj);
		var btn = $(obj).parent();
		$.post('?body=order@order_update.exe', {"exec":"setDlvHold", "ono":ono, "pno":no}, function(result) {
			switch(result) {
				case 'Y' :
					btn.removeClass('blue');
					btn.addClass('gray');
					obj.val('지연');
				break;
				case 'N' :
					btn.removeClass('gray');
					btn.addClass('blue');
					obj.val('정상');
				break;
			}
			obj.blur();
		});
	}

	function deliveryExcel() {
		if($(':checked.check_ono').length < 1) {
			window.alert('엑셀출력할 주문을 선택해 주세요.');
			return false;

		}
		var f = document.getElementById('prdFrm');
		var bak = f.body.value;
		f.body.value = 'order@order_excel.exe';
		f.method = 'POST';
		f.submit();

		f.body.balue = bak;
		f.method = 'GET';
		f.target = '';
	}

	function viewComplexOption(complex_no) {
		var win = window.open('?body=erp@stock_detail&pno='+complex_no, 'viewComplexOption');
		if(win) win.focus();
	}
</script>