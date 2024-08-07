
<style type="text/css" title="">
body {background:#e8e8e8;}
</style>
<?PHP

	$ono = addslashes(trim($_GET['sbono']));
	checkBlank($ono, '주문번호를 입력해주세요.');
	$data = get_info($tbl['sbscr'], 'sbono', $ono);
	if(!$data['no']) {
		msg('주문이 삭제되었거나 존재하지 않습니다.', 'close');
	}

    if ($data['pay_type'] == '23') { // 정기결제일 경우 상태값 치환
        foreach ($_order_stat_sbscr as $key => $val) {
            $_order_stat[$key] = $val;
            $_sbscr_order_stat[$key] = $val;
        }
    }

	include_once $engine_dir.'/_engine/include/shop.lib.php';

	if(!$data['member_id']) $data['member_id']="비회원";

	if($data['sms'] != 'Y') $data['sms'] = 'N';

	if($cfg['repay_part'] == "Y"){
		$_repay_part_stat=array("0" => "일반", 13 => "취소");
	}else $_repay_part_stat=array("" => 0);

	$pay_type = $_pay_type[$data['pay_type']];

	if($data['pay_type'] == 2 || $data['pay_type'] == 4) {
		if($data['pay_type'] == 4) {
			$card = get_info($tbl['vbank'], 'wm_ono', $ono);
			$pay_type .= " (거래 번호 : $card[tno])";
			$data['bank'] = "$card[bankname] $card[account] $card[depositor]";
		}
		$pay_type .= " ($data[bank])";
		if($data['bank_name']) {
			$pay_type .= " <u>입금자명 : ".stripslashes($data['bank_name'])."</u>";
		}
	}

	$pay_type .= ' ( <b>'.parsePrice($data['s_pay_prc'], true).' '.$cfg['currency_type'].'</b> )';

	$prd_sql = $pdo->iterator("select `stat`,`no` from `$tbl[sbscr_product]` where `sbono`='$ono' order by stat, no");
	$prdTotal = $prd_sql->rowCount();
    foreach ($prd_sql as $prd) {
		if($cfg['repay_part']=="Y"){
			if($prd['stat'] == 13 || $prd['stat'] == 15 || in_array($prd['stat'], array(17,22,23)) || in_array($prd['stat'], array(19,24))){
				$nstat = $prd['stat'];
				if(in_array($nstat, array(17, 22, 23)) == true) $nstat = 17; // npay
				if(in_array($nstat, array(24, 25, 26)) == true) $nstat = ''; // npay

				$sn="prdStat".$nstat;
				${$sn}[]=$prd['no'];
			}else $prdStat[]=$prd['no'];
		}else $prdStat[]=$prd['no'];
	}
	unset($prd_sql, $prd);

	$amember = $pdo->assoc("select * from `$tbl[member]` where `no`='$data[member_no]'");
	$mno = $amember['no'];

	if($amember['no'] > 0) {
		$mchecker = array();
		if(isTable($tbl['member_checker'])) {
			$mcres = $pdo->iterator("select no, name from `$tbl[member_checker]` order by name asc");
            foreach ($mcres as $mcdata) {
				$mchecker[$mcdata['no']] = stripslashes($mcdata['name']);
			}
		}
		$member_checker = getMemberChecker($amember);
	}

	$_omode = array("한개의 창만 사용","여러개의 새창 사용");
	if(!$omode) {
		$def_omode=$_COOKIE['def_omode'];
		if(!$def_omode) $def_omode=0;
		$omode=$def_omode;
	}

    // 전체 적립금 계산
    $total_milage = $pdo->row("
        select sum(total_milage)
        from {$tbl['sbscr_product']} a inner join {$tbl['sbscr_schedule_product']} b on a.no=b.sbpno
        where a.sbono=? and a.stat < 10 and b.stat < 10",
        array($ono)
    );

?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/resize.js"></script>
<script type="text/javascript" src="<?=$engine_url?>/_manage/order.js?ver=20180109"></script>
<div id="pop_crm" class="crm_order">
	<div id="header">
		<h1><img src="<?=$engine_url?>/_manage/image/crm/title_crm_order.png" alt="wisa 주문정보"></h1>
		<div class="tab">
			<div class="tab_order">
				<a href="./?body=member@member_view.frm&smode=order&mno=<?=$mno?>&mid=<?=$mid?>"><img src="<?=$engine_url?>/_manage/image/crm/tab_order_on.png" alt="주문서"></a>
				<form name="" method="post" action="" target="" onSubmit="return" class="search search_order">
					<div class="search_input">
						<input type="text" name="" value="" class="input_search" placeholder="이름, 주문번호로 주문조회가 가능합니다!" onkeyup="autoComplete('order', this)"><input type="image" src="<?=$engine_url?>/_manage/image/crm/btn_search.gif" class="btn_search">
					</div>
					<div id="auto_complete" class="auto">
					</div>
				</form>
			</div>
			<div class="tab_member">
				<a href="./?body=member@member_view.frm&smode=main&mno=<?=$mno?>&mid=<?=$mid?>"><img src="<?=$engine_url?>/_manage/image/crm/tab_member.png" alt="회원정보"></a>
			</div>
		</div>
		<a href="javascript:;" onClick="layTgl2('setDiv');" class="btn_setup">설정</a>
		<!-- 설정 새창 -->
		<div id="setDiv" class="setDiv" style="display:none;">
			<table class="tbl_mini full">
				<tr>
					<th scope="row">주문상세창 새창설정</th>
					<td><?=selectArray($_omode,"omode", 2, "", $def_omode,"setConfig('def_omode',this.value);location.reload();");?></td>
				</tr>
			</table>
			<div class="pop_bottom"><span class="box_btn_s gray"><input type="button" value="닫기" onClick="layTgl2('setDiv');"></span></div>
		</div>
		<!-- //설정 새창 -->
	</div>
	<div id="container">
		<div class="snb">
			<div class="area_scroll">
				<div class="profile">
					<?php if ($amember['no']) { ?>
						<p class="name"><a href="./?body=member@member_view.frm&smode=main&mno=<?=$mno?>&mid=<?=$mid?>"><?=$amember['name']?><?php if ($cfg['member_join_id_email'] != 'Y'){ ?>(<?=$amember['member_id']?>)<?php } ?> <?=blackIconPrint($amember['blacklist'])?></a></p>
					<?php } else { ?>
						<p class="name"><?=$data['buyer_name']?></p>
					<?php } ?>
					<p class="email"><?=$amember['email']?></p>
					<div class="send">
						<span class="box_btn_s"><a onclick="smsSend('<?=$amember['cell']?>')">sms</a></span>
					</div>
				</div>
				<div class="order_no">
					<dl>
						<dt>주문번호</dt>
						<dd>
							<?=$ono?>
						</dd>
					</dl>
				</div>
				<ul id="crm_order_list" class="menu">
					<li><a class="main selected" onclick="tabover(0)">전체</a></li>
					<li><a class="order_prd" onclick="tabover(1)">주문 상품</a></li>
					<li><a class="order" onclick="tabover(2)">주문 정보</a></li>
					<li><a class="info" onclick="tabover(3)">주문자 정보</a></li>
					<li><a class="delivery" onclick="tabover(4)">배송지 정보</a></li>
					<?php if ($admin['level'] < 4) { ?>
					<li><a class="counsel" onclick="tabover(5)">주문 관련 상담</a></li>
					<?php } ?>
					<li><a class="order" onclick="tabover(6)">정기배송 예약내역</a></li>
				</ul>
			</div>
		</div>
		<div id="content">
			<div class="content_box">
				<div class="tabcnt tabcnt1">
					<form name="prdFrm" id="prdFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
						<div class="box_title first">
							<h3 class="title">주문 상품</h3>
						</div>
						<?php if ($cfg['repay_part'] == 'Y') { ?>
						<div id="controlTab" class="none_margin">
							<ul class="tabs">
								<?php
									foreach($_repay_part_stat as $key => $val){
										if($key > 0 && $key % 2 == 0) continue;

                                        $prdNum = 0;
                                        if ($key == 0) {
    										$prdNum = count($prdStat);
                                        } else {
                                            if (isset(${'prdStat'.$key}) == true && is_array(${'prdStat'.$key}) == true) {
                                                $prdNum = count(${'prdStat'.$key});
                                            }
                                        }
										$sel = ($key == 0) ? "class='selected'" : '';
								?>
								<li id="ctab_<?=$key?>" onclick="layerSH(<?=$key?>)" <?=$sel?> style="width:80px;"><?=$val?>상태(<?=number_format($prdNum)?>)</li>
								<?php } ?>
							</ul>
						</div>
						<?php } ?>
						<?php foreach ($_repay_part_stat as $key => $val) { $hide = ($key == 0) ? '' : 'none';?>
						<table id="edt_layer_<?=$key?>" class="tbl_col" style="display:<?=$hide?>; border-top:0; table-layout:auto;">
							<caption class="hidden">주문상세 상품리스트</caption>
							<colgroup>
								<col style="width: 40px;">
								<col>
								<col style="width: 37px;">
								<col style="width: 75px;">
								<col>
								<col style="width: 75px;">
								<col style="width: 100px;">
								<col>
							</colgroup>
							<thead>
								<tr>
									<th scope="col">번호</th>
									<th scope="col">제품명</th>
									<th scope="col">수량</th>
									<th scope="col">상품가격</th>
									<th scope="col">할인적용</th>
									<th scope="col">소계</th>
									<th scope="col">배송횟수</th>
									<th scope="col">배송기간</th>
								</tr>
							</thead>
							<tbody>
								<?PHP

									$idx = 1;
									$total = array();
									$pnm = ($key == 0) ? 'prdStat' : 'prdStat'.$key;

									$_sum_buy_ea = 0;
									$_sum_prd_prc = 0;
									$_sum_sale_prc = 0;
									$_sum_milage = 0;
									$_sum_total_prc = 0;

                                    if (isset(${$pnm}) == false) ${$pnm} = array();
									for($ii=0; $ii < count(${$pnm}); $ii++) {
										$prd = $pdo->assoc("select *, sp.total_prc as sp_total_prc from $tbl[sbscr_product] as sp inner join $tbl[sbscr_schedule_product] as ssp on sp.no=ssp.sbpno inner join $tbl[sbscr_schedule] as ss on ssp.schno=ss.no where sp.no='".${$pnm}[$ii]."'");

										if($admin['partner_no'] > 0 && $prd['partner_no'] != $admin['partner_no']) {
											$prd = array(
												'name' => '타사상품',
												'stat' => $prd['stat'],
											);
										}

										// 요약 합계 정보
										$prd_prc_sum += ($prd['sell_prc']*$prd['buy_ea']);

										foreach($_order_sales as $fn => $fv) {
											${'total_'.$fn} += $prd[$fn];
										}

										// 옵션명
										$prd['option_str'] =''; //초기화
										if($prd['option']) {
											$prd['option_str'] = str_replace('<split_big>', ', ', $prd['option']);
											$prd['option_str'] = str_replace('<split_small>', ':', $prd['option_str']);
											$prd['option_str'] = $opt_deco1.$prd['option_str'].$opt_deco2;
										}

										// 상품 이미지
										$cprd = $pdo->assoc("select no, hash, wm_sc, stat, updir, upfile3, w3, h3 $cprd_field from $tbl[product] where no='$prd[pno]'");
										$cprd = shortCut($cprd);
										$img = prdImg(3, $cprd, 50, 50);
										if($cprd['storage_no'] > 0) {
											$storage = $pdo->assoc("select big, mid, small, depth4 from $tbl[erp_storage] where no='$cprd[storage_no]'");
											$storage_name = getStorageLocation($storage);
										}

										// 윙Pos
										if($prd['complex_no'] > 0) {
											$wingpos = $pdo->assoc("select curr_stock(complex_no) as stock, force_soldout from erp_complex_option where complex_no='$prd[complex_no]'");
											$erp_qty = number_format($wingpos['stock']);
											$erp_qty = ($wingpos['force_soldout'] == 'N') ? '무제한' : $erp_qty.' ea';
										}

										$prd['prd_total_sale_prc'] = getOrderTotalSalePrc($prd);

										$_yoil = array('일', '월', '화', '수', '목', '금', '토');
                                        $_cart_week_text = $_yoil[date('w', strtotime($prd['dlv_start_date']))];

										$prd['dlv_finish_date'] = ($prd['dlv_finish_date']=='0000-00-00') ? '':$prd['dlv_finish_date'];

								?>
								<tr>
									<td><?=$idx?></td>
									<td class="left">
										<div class="box_setup order_stat">
											<div class="thumb"><a href="<?=$root_url?>/shop/detail.php?pno=<?=$cprd['hash']?>" target="_blank"><img src="<?=$img[0]?>" <?=$img[1]?>></a></div>
											<div style="margin-left:60px;">
												<p class="title">
													<a href="./?body=product@product_register&pno=<?=$prd['pno']?>" target="_blank"><?=strip_tags(stripslashes($prd['name']))?></a>
												</p>
												<p class="cstr">
													<?=$prd['option_str']?><?php if ($cprd['stat']=='3') { ?>&nbsp;<span class="p_color2">[품절]</span><?php } ?>
												</p>
												<?php if ($prd['partner_no'] > 0) { ?>
												<p class="p_color4" style="margin:2px 0;"><strong><?=getPartnerName($prd['partner_no'])?></strong></p>
												<?php } ?>
												<?php if ($prd['complex_no'] > 0) { ?>
												<p class="explain">
													현재고 : <?=$erp_qty?> <a href="#" onclick="viewStockDetail(<?=$prd['complex_no']?>); return false;" class="p_color3">[재고상세]</a>
												</p>
												<?php } ?>
												<?php if ($cprd['storage_no'] > 0) { ?>
												<p class="explain">
													<?=$storage_name?>
												</p>
												<?php } ?>
											</div>
										</div>
									</td>
									<td><?=number_format($prd['buy_ea'])?></td>
									<td><?=parsePrice($prd['sell_prc'], true)?></td>
									<td class="right" style='white-space:nowrap;'>
										<?php foreach ($_order_sales as $fn => $fv) { ?>
										<?php if ($prd[$fn] != 0) { ?><div class="p_color explain"><?=$fv?> : <?=parsePrice(-($prd[$fn]), true)?></div><?php } ?>
										<?php } ?>
									</td>
									<td><?=parsePrice($prd['sp_total_prc']-$prd['prd_total_sale_prc'], 2)?></td>
									<td>
										<?php if ($prd['dlv_finish_date']) { ?>
											<?=$prd['dlv_total_cnt']?>
										<?php } else { ?>
											-
										<?php } ?>
									</td>
									<td>
										기간: <?=$prd['dlv_start_date']?> ~ <?=$prd['dlv_finish_date']?><br>
										<?php if ($prd['dlv_finish_date']) { ?>
											횟수: <?=$prd['dlv_total_cnt']?>
										<?php } ?>
									</td>
								</tr>
								<?php
									$idx++;
									$_sum_buy_ea += $prd['buy_ea'];
									$total_prd_prc = $_sum_prd_prc += ($prd['sell_prc']*$prd['buy_ea']);
									$_sum_sale_prc += -(getOrderTotalSalePrc($prd));
									$_sum_milage += $prd['total_milage'];
									$_sum_total_prc += ($prd['sp_total_prc']-$prd['prd_total_sale_prc']);
									}

								?>
							</tbody>
							<?php if ($_sum_buy_ea > 0) { ?>
							<tfoot>
								<tr>
									<td colspan="2">합계</td>
									<td><?=number_format($_sum_buy_ea)?></td>
									<td><?=parsePrice($_sum_prd_prc, true)?></td>
									<td><?=parsePrice($_sum_sale_prc, true)?></td>
									<td><?=parsePrice($_sum_total_prc, true)?></td>
									<td></td>
									<td></td>
								</tr>
							</tfoot>
							<?php } ?>
						</table>
						<?php } ?>
						<div id="repayDetail" style="border:2px solid #44affb; margin:10px; padding:10px; display:none;"></div>
					</form>
				</div>
				<div class="tabcnt tabcnt6">
					<form name="bkFrm" id="bkFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
					<input type="hidden" name="body" value="order@sbscr_update.exe">
					<input type="hidden" name="exec" value="">
					<input type="hidden" name="sno" value="<?=$ono?>">
					<input type="hidden" name="type" value="">
					<input type="hidden" name="schno" value="">
					<div class="box_title first">
						<h3 class="title">정기배송 예약내역</h3>
					</div>
					<table class="tbl_col">
						<caption class="hidden">정기배송 예약내역</caption>
						<colgroup>
							<col style="width:40px;">
							<col style="width:200px;">
							<col>
							<col>
							<col style="width:200px;">
							<col style="width:135px;">
						</colgroup>
						<thead>
							<tr>
								<th scope="col"></th>
								<th scope="col">배송예약일</th>
								<th scope="col">주문번호</th>
								<th scope="col">결제금액</th>
								<th scope="col">상태</th>
								<th scope="col">결제/취소</th>
							</tr>
						</thead>
						<tbody>
							<?php
								// 페이징
								include $engine_dir."/_engine/include/paging.php";

								$page = numberOnly($_GET['page']);
								$row = numberOnly($_GET['row']);
								if($page <= 1) $page = 1;
								if(!$row) $row = 5;
								if($row > 100) $row = 100;
								$block=10;

								$sbsql = "select *, sum(total_prc) AS tot_pay_prc, count(*) as cnt from $tbl[sbscr_schedule] where sbono='$ono' group by `date`";
								$sbcount = $pdo->row("select count(distinct date) from $tbl[sbscr_schedule] where sbono='$ono'");

								$PagingInstance = new Paging($sbcount, $page, $row, $block);
								$PagingInstance->addQueryString(makeQueryString('page'));
								$PagingResult = $PagingInstance->result($pg_dsn);

								$sbsql .= $PagingResult['LimitQuery'];

								$pageRes = $PagingResult['PageLink'];
								$sbres = $pdo->iterator($sbsql);
								$idx = $sbcount-($row*($page-1));
								$sort = ($row*($page-1))+1;

								$jcnt = 1;
                                foreach ($sbres as $bv) {
									$sspdata = $pdo->assoc("select * from $tbl[sbscr_schedule_product] where schno='$bv[no]'");
									if($sspdata['ono']) $stat_text = "주문서 생성완료";
									else $stat_text = $_sbscr_order_stat[$sspdata['stat']];
							?>
							<tr>
								<td><input type="checkbox" name="bno[]" value="<?=$bv['date']?>" class="check_pno"></td>
								<td><?=$bv['date']?></td>
								<td class="left">
                                    <a onclick="viewOrder('<?=$sspdata['ono']?>')" class="p_cursor"><?=$sspdata['ono']?></a>
                                    <?php if ($sspdata['ono'] && $data['pay_type'] == '27') { ?>
                                    <div class="explain">└ 네이버페이 merchantPayId : <?=$ono?>_<?=$bv['no']?></div>
                                    <?php } ?>
                                </td>
								<td><?=parsePrice($bv['tot_pay_prc'], true)?></td>
								<td><?=$stat_text?></td>
								<td class="center">
									<?php if ($sspdata['stat'] == "1") { ?>
										<span class="box_btn_s blue"><input type="button" value="결제" onClick="schOrder('<?=$bv['no']?>', '1');return false;" ></span>
										<span class="box_btn_s blue"><input type="button" value="취소" onClick="schOrder('<?=$bv['no']?>', '2');return false;" ></span>
									<?php } else if($sspdata['stat'] == "2" && !$sspdata['ono']) { ?>
										<span class="box_btn_s blue"><input type="button" value="취소" onClick="schOrder('<?=$bv['no']?>', '2');return false;" ></span>
									<?php } ?>
								</td>
							</tr>
							<?php
								$jcnt++;
								}
							?>
						</tbody>
					</table>
					<div class="box_bottom"><?=$pageRes?></div>
					<div class="box_bottom left">
						<span class="box_btn_s gray"><input type="button" onClick="schCancel('1')" value="주문서 전체취소"></span>
						<span class="box_btn_s gray"><input type="button" onClick="schCancel('2')" value="선택주문서 취소"></span>
						<?php
							$dlv_finish_count = $pdo->row("select count(*) from $tbl[sbscr_product] where dlv_finish_date='0000-00-00' and sbono='$ono'");
							if($dlv_finish_count > 0) {
						?>
						<span class="box_btn_s gray"><input type="button" onClick="schStop()" value="예약내역 중지"></span>
						<?php } ?>
						<ul class="list_msg">
							<li>예약 내역을 취소해도 실결제 취소와는 관련이 없습니다.</li>
							<li>결제된 정기결제의 경우 해당 실 주문서에서 취소/교환/반품 등이 가능합니다.</li>
						</ul>
					</div>
					</form>
				</div>
				<?php
					$pay_type_icn = "<img src='$engine_url/_manage/image/icon/pay{$data['pay_type']}.gif'>";

					// 총 할인 금액 계산
					foreach($_order_sales as $fn => $fv) {
						$total_sale_prc += ${'total_'.$fn};
					}

				?>
				<div class="tabcnt tabcnt1">
					<div class="box_title first">
						<h3 class="title">결제금액정보</h3>
					</div>
					<div class="box_middle4">
						<div class="priceinfo">
							<div class="list">
								<div class="frame">
									<table>
										<thead>
											<tr>
												<th><span>총주문액</span></th>
												<th class="sale"><span>할인금액</span></th>
												<th><span>적립금</span></th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td>
													<ul>
														<li>└ 상품가격합계 <span><?=parsePrice($data['s_prd_prc'], true)?> <?=$cfg['currency_type']?></span></li>
														<li>└ 배송비 <span><?=parsePrice($data['s_dlv_prc'], true)?> <?=$cfg['currency_type']?></span></li>
													</ul>
												</td>
												<td class="sale">
													<ul>
														<?php foreach ($_order_sales as $fn => $fv) { if(${'total_'.$fn} != 0) { ?>
														<li>
															└ <?=$fv?> <span>(<?=(${'total_'.$fn} > 0) ? '-' : '+'?>) <?=parsePrice(abs(${'total_'.$fn}), true)?> <?=$cfg['currency_type']?></span>
														</li>
														<?php }} ?>
													</ul>
												</td>
												<td>
													<ul>
														<li>
															└ <?=$cfg['milage_name']?> <span><?=parsePrice($total_milage, true)?> <?=$cfg['currency_type']?></span>
														</li>
													</ul>
												</td>
											</tr>
											<tr>
												<td class="last"><b><?=parsePrice($data['s_total_prc'], true)?></b> <?=$cfg['currency_type']?></td>
												<td class="last sale">(-) <b><?=parsePrice($total_sale_prc, true)?></b> <?=$cfg['currency_type']?></td>
												<td class="last"><b><?=parsePrice($data['total_milage'], true)?></b> <?=$cfg['currency_type']?></td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
							<?php
							if($data['pay_type']!=23 && $prd['dlv_finish_date']!='0000-00-00') {?>
								<div class="total">
									<ul>
										<li>총주문액<span><?=parsePrice($data['s_prd_prc']+$data['s_dlv_prc'], true)?> 원</span></li>
										<li>할인금액<span>(-) <?=parsePrice($data['s_sale_prc'], true)?> 원</span></li>
									</ul>
								</div>
								<div class="totalpay">
									실결제금액<span><?=$pay_type_icn?> <strong><?=parsePrice($data['s_pay_prc'], true)?></strong> <?=$cfg['currency_type']?></span>
								</div>
							<?php } else { ?>
								<div class="total">
									<ul>
										<li>첫 결제금액<span><?=parsePrice($total_prd_prc+$prd['dlv_prc'], true)?> 원</span></li>
										<li>할인금액<span>(-) <?=parsePrice($total_sale_prc, true)?> 원</span></li>
									</ul>
								</div>
								<div class="totalpay">
									회차별 결제금액<span><?=$pay_type_icn?> <strong><?=parsePrice($total_prd_prc+$prd['dlv_prc']-$total_sale_prc, true)?></strong> <?=$cfg['currency_type']?></span>
								</div>
							<?php } ?>
						</div>
						<div style="clear:both;"></div>
					</div>
				</div>
				<form name="ordFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
					<input type="hidden" name="ono" value="<?=$ono?>">
					<input type="hidden" name="body" value="order@sbscr_update.exe">
					<input type="hidden" name="stat" value="<?=$data['stat']?>">
					<input type="hidden" name="exec" value="">
					<input type="hidden" name="ext" value="">
					<div class="tabcnt tabcnt2">
						<!-- 주문정보 -->
						<div class="box_title first">
							<h3 class="title">주문정보</h3>
						</div>
						<table class="tbl_row">
							<caption class="hidden">주문정보</caption>
							<colgroup>
								<col style="width:15%">
								<col style="width:42.5%">
								<col style="width:42.5%">
								<col>
							</colgroup>
							<tr>
								<th scope="row">주문 번호</th>
								<td colspan="2">
									<b><?=$ono?></b>
								</td>
							</tr>
							<tr>
								<th scope="row">주문 일시</th>
								<td colspan="2"><?=date("Y/m/d g:i A",$data['date1'])?></td>
							</tr>
							<tr>
								<th scope="row">결제 방법</th>
								<td colspan="2">
									<!-- 결제방법 -->
									<div>
                                        <?php if ($data['pay_type'] == '25') { ?>
                                        <img src="<?=$engine_url?>/_manage/image/icon/pay25.gif">
                                        <?php } elseif ($data['pay_type'] == '27') { ?>
                                        <img src="<?=$engine_url?>/_manage/image/icon/pay27.gif">
                                        <?php } else { ?>
                                        <?=$pay_type?>
                                        <?php } ?>
                                    </div>
								</td>
							</tr>
							<tr>
								<th scope="row">결제내역</th>
								<td colspan="2">
									<table class="tbl_inner line full">
										<caption class="hidden">결제내역</caption>
										<colgroup>
											<col style="width:80px">
											<col>
											<?php if ($data['pay_type']!=23) { ?>
											<col style="width:110px">
											<?php } ?>
											<col style="width:110px">
											<col style="width:110px">
											<col style="width:110px">
											<col style="width:80px">
										</colgroup>
										<thead>
											<tr>
												<th scope="row">종류</th>
												<th scope="row">사유</th>
												<?php if ($data['pay_type']!=23) { ?>
												<th scope="row">금액</th>
												<?php } ?>
												<th scope="row">결제방법</th>
												<th scope="row">처리일시</th>
												<th scope="row">처리자</th>
												<th scope="row">상태</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td rowspan="2">
													<strong>최초결제</strong>
												</td>
												<td rowspan="2">
												</td>
												<?php if ($data['pay_type']!=23) { ?>
												<td>
													<?=parsePrice($data['s_pay_prc'], true)?>
												</td>
												<?php } ?>
												<td>
													<?=nl2br($_pay_type[$data['pay_type']])?>
												</td>
												<td>
													<?=date('y-m-d H:i', $data['date1'])?>
												</td>
												<td rowspan="2"></td>
												<td rowspan="2">
													완료
												</td>
											</tr>
											<tr>
												<td colspan="3" class="left">
													<?php if ($data['pay_type'] == 2) { ?>
													입금 계좌정보 : <?=$data['bank']?> <?=$data['bank_account']?> <?=$data['bank_name']?>
													<?php } ?>
												</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
							<?php
							// 자동결제
							if($data['pay_type'] == '23' || $data['pay_type'] == '27') {
								?>
								<tr class="card_info">
									<th scope="row">카드 정보</th>
									<td colspan="2">
										<table class="tbl_inner line full">
										<?PHP
											$ssp_res = $pdo->iterator("select * from $tbl[sbscr_schedule_product] where sbono='$ono' and stat=2 and ono!='' group by schno order by schno");
                                            foreach ($ssp_res as $ssp_data) {
											$card_ono = $ssp_data['sbono'].$ssp_data['schno'];
											$card = $pdo->assoc("select * from $tbl[card] where wm_ono='$card_ono'");
                                            $spno = (int) str_replace($ono, '', $card['wm_ono']);
                                            $_ono = $pdo->row("select ono from {$tbl['sbscr_schedule_product']} where schno='$spno'");
											if($card['no']) {
												$pg_link=linkPGAdmin($card['pg']);
												if($data['pay_type']=="1" ) {
													if($card['quota']=="00") {
														$card['quota_str']="일시불";
													} else {
														$card['quota_str']=$card['quota']."개월";
													}
												}
                                                $card_cancel_plug = '';
                                                if($card['pg'] == 'nsp' && $card['pg_version'] == 'autobill') $card_cancel_plug = ($cfg['nsp_sub_use_tax'] == 'Y') ? 'tax' : '';
										?>
										<tr>
											<td>
												<?php if ($card['stat'] == "2") { ?>
												<p class="pgcancel">
													'PG사 결제 환불'버튼을 통해 환불처리가 가능합니다.
													<span class="box_btn_s gray"><input type="button" value="PG사 결제 환불" onclick="cardCancel('', '<?=$card['no']?>');"></span>
												</p>
												<?php } ?>
												<div class="card_cancel_div_<?=$card['no']?> card_cancel_box" style="display:none;">
                                                    <?php if ($card_cancel_plug == 'tax') { ?>
                                                    <!-- 복합과세 취소 -->
                                                    과세금액 <input type="text" name="taxScopeAmount" size="10" class="input block" value="<?=parsePrice($card['wm_price']-$card['wm_free_price'])?>" placeholder="과세금액">
                                                    비과세 금액 <input type="text" name="taxExScopeAmount" size="10" class="input block" value="<?=parsePrice($card['wm_free_price'])?>" placeholder="비과세금액">
                                                    <span class="box_btn_s full white"><input type="button" value="확인" onClick="cancelTax('<?=$card['no']?>', '<?=$card['no']?>');"></span>
                                                    <?php } else { ?>
													<label class="p_cursor"><input type="radio" name="card_cancel_type" value="1" onclick="if(this.checked) this.form.card_cancel_price.value=0; if(this.checked) this.form.card_cancel_price.disabled=true;" checked> 전체환불</label>
													<label class="p_cursor"><input type="radio" name="card_cancel_type" value="2" onclick="if(this.checked) this.form.card_cancel_price.disabled=false;"> 부분환불</label>
													<input type="text" name="card_cancel_price" size="10" class="input block" value="0" disabled>
													<span class="box_btn_s full white"><input type="button" value="확인" onClick="window.frames[hid_frame].location.href='./?body=order@order_card_cancel.exe&cno=<?=$card['no']?>&price='+this.form.card_cancel_price.value;"></span>
                                                    <?php } ?>
												</div>
												<?php if ($card['stat'] == "3") { ?>
												<p class="pgcancel">
													<span class="p_color2">환불처리가 완료된 주문서입니다.</span>
												</p>
												<?php } ?>
												<?php
													// 카드 취소 로그
													$card_log_sql = $pdo->iterator("select * from `$tbl[card_cc_log]` where `cno`='$card[no]'");
													$_card_cc_log="";
                                                    foreach ($card_log_sql as $card_log_data) {
														$_card_cc_log .= "<li>";
														$_card_cc_log .= "<span>".date("Y-m-d H:i", $card_log_data['reg_date'])."</span> : 관리자[".$card_log_data['admin_id']."]님이 요청한  ";
														$_card_cc_log .= ($card_log_data['price'] > 0) ? "<span>".parsePrice($card_log_data['price'], true)."</span> $cfg[currency_type]" : "";
														$_card_cc_log .= ($card_log_data['stat'] == "2") ? "<br><strong>환불 완료</strong>($card_log_data[res_msg])" : "<br><strong>환불 실패</strong> ($card_log_data[res_msg])";
														$_card_cc_log .= '</li>';
													}
													if($_card_cc_log != "") echo "<ul class=\"log\">$_card_cc_log<ul>";
													unset($_card_cc_log);
												?>
											</td>
											<td class="lb vtat">
												<p class="pg_info">
													카드 정보 : <span class="val"><?=$card['card_name']?> - <?=strip_tags($card['res_msg'])?></span>
													<?php
														if($card['pg'] == 'danal') $pg_link='https://cp.teledit.com/login/';
														else if($card['pg']=='kakaopay') $pg_link='https://pg-web.kakao.com/v1/confirmation/p/';
                                                        else if ($card['pg'] == 'nsp') $pg_link = 'https://admin.pay.naver.com/';
													?>
													<span class="box_btn_s2 btn"><a href="<?=$pg_link?>" target="_blank">PG사 확인</a></span>
												</p>
												<p class="pg_info">
													거래 번호 : <span class="val"><?=$card['tno']?></span>
													<?php
														$card_dacom_id=$cfg['card_auto_dacom_id'];
														$card_dacom_key=$cfg['card_dacom_auto_key'];
														$card['authdata'] = md5($card_dacom_id.$card['tno'].$card_dacom_key);
                                                        $rcpt_link = '';
														switch($card['pg']) {
															case 'dacom' :
																$rcpt_link = "javascript:showReceiptByTID('$card_dacom_id', '$card[tno]', '$card[authdata]')";
																break;
															case 'nicepay' :
																$rcpt_link = 'javascript:nicepayReceipt(\''.$card['tno'].'\');';
															break;
														}
													?>
                                                    <?php if ($rcpt_link) { ?>
													<script type="text/javascript" src="//pgweb.dacom.net/WEB_SERVER/js/receipt_link.js"></script>
													<span class="box_btn_s2 btn"><a href="<?=$rcpt_link?>">영수증 확인</a></span>
                                                    <?php } ?>
												</p>
                                                <p class="pg_info">주문번호 : <a href="#" onclick="viewOrder('<?=$_ono?>'); return false;"><?=$_ono?></a></p>
											</td>
											<?php } ?>
										</tr>
										<?php } ?>
									</table>
									</td>
								</tr>
								<?php
							} else {
                                // 카드 취소 다이얼로그
                                $card = $pdo->assoc("select * from $tbl[card] where wm_ono='$ono'");
                                require 'order_card_info.inc.php';
                            ?>
                            <?php
								// 현금영수증 정보 조회
								if($data['pay_type'] == '2' && $admin['level'] < 4) {
									$_cash = $pdo->assoc("
                                        select * from {$tbl['cash_receipt']} where ono='{$data['sbono']}' order by no desc limit 1
                                    ");
							?>
							<tr>
								<th scope="row">현금영수증</th>
								<td colspan="2">
									<?php if ($_cash['no']) { ?>
										신청함 (발급번호: <u><?=$_cash['cash_reg_num']?></u>)
									<?php } else { ?>
										신청내역없음 <span class="box_btn_s"><a href="?body=order@order_cash_receipt_sub&ono=<?=$ono?>" target="_blank">개별 현금영수증 등록</a></span>
									<?php } ?>
								</td>
							</tr>
							<?php }} ?>
							<?php if ($data['pay_type']!=23 && $prd['dlv_finish_date']!='0000-00-00') { ?>
							<tr>
								<th scope="row">상태</th>
								<td colspan="2">
									<ul class="ordStat">
										<?php
											$sspcount = $pdo->row("select count(*) from $tbl[sbscr_schedule_product] where sbono='$data[sbono]' and `stat`=2 and ono!=''");
											$totalcount = $pdo->row("select count(*) from $tbl[sbscr_schedule_product] where sbono='$data[sbono]'");
											if($sspcount==$totalcount) {
												$data['stat'] = 5;
											}else if($sspcount>=1 && $sspcount!=$totalcount) {
												$data['stat'] = 3;
											}
											for($ii = 1; $ii <=5; $ii++) {
                                                if (!$_sbscr_order_stat[$ii]) continue;
												$class = ($data['stat'] < 5 && $ii <= $data['stat']) ? 'blue' : '';
												$date = ($data['date'.$ii]) ? date("y/m/d g:i A",$data['date'.$ii]) : '-';
												if($ii == 5) $childno = "class='last-child'";
										?>
										<li <?=$childno?>>
											<dl>
												<dt><span style="letter-spacing:-1px;" class="<?=$class?>"><?=$_sbscr_order_stat[$ii]?></span></dt>
												<dd><?=$date?></dd>
											</dl>
										</li>
										<?php } ?>
									</ul>
								</td>
							</tr>
							<?php } else { ?>
							<tr>
								<th scope="row">상태</th>
								<td colspan="2">
									<ul class="ordStat">
										<?php
											$sspcount = $pdo->row("select count(*) from $tbl[sbscr_schedule_product] where sbono='$data[sbono]' and `stat`=2 and ono!=''");
											$totalcount = $pdo->row("select count(*) from $tbl[sbscr_schedule_product] where sbono='$data[sbono]'");
											if($sspcount==$totalcount) {
												$data['stat'] = 5;
											} else if ($sspcount > 0) {
												$data['stat'] = 3;
											} else {
												$data['stat'] = 2;
											}
											for($ii = 1; $ii <=5; $ii++) {
												if(isset($_sbscr_order_stat[$ii]) == false) continue;
												$class = ($data['stat'] < 4 && $ii <= $data['stat']) ? 'blue' : '';
												$date = ($data['date'.$ii]) ? date("y/m/d g:i A",$data['date'.$ii]) : '-';
												if($ii == 5) $childno = "class='last-child'";
										?>
										<li <?=$childno?>>
											<dl>
												<dt><span style="letter-spacing:-1px;" class="<?=$class?>"><?=$_sbscr_order_stat[$ii]?></span></dt>
												<dd><?=$date?></dd>
											</dl>
										</li>
										<?php } ?>
									</ul>
								</td>
							</tr>
							<?php } ?>
						</table>
						<!-- //주문정보 -->
					</div>

					<!-- 주문자 정보 -->
					<div class="tabcnt tabcnt3">
						<div class="box_title first">
							<h3 class="title">주문자 정보</h3>
						</div>
						<table class="tbl_row">
							<caption class="hidden">주문자 정보</caption>
							<colgroup>
								<col style="width:15%">
								<col>
							</colgroup>
							<tr>
								<th scope="row">이름</th>
								<td><?=$data['buyer_name']?> <a href="javascript:viewMember('<?=$data['member_no']?>','<?=$data['member_id']?>')">(<?=$data['member_id']?>) <?=blackIconPrint('',$data)?></a></td>
							</tr>
							<?php if ($amember['no'] > 0) { ?>
							<tr>
								<th scope="row">회원그룹</th>
								<td>
									<ul class="list_msg">
										<li><?=stripslashes($pdo->row("select name from $tbl[member_group] where no='$amember[level]'"))?></li>
										<?php foreach ($member_checker as $val) { ?><li><?=$val?></li><?php } ?>
									</ul>
								</td>
							</tr>
							<?php } ?>
							<tr>
								<th scope="row">주문 IP</th>
								<td><a href="./?body=log@count_log_list&exec=search&ext=&y1=<?=date("Y", $data['date1'])?>&m1=<?=date("m", $data['date1'])?>&d1=01&h1=00&y2=<?=date("Y", $data['date1'])?>&m2=<?=date("m", $data['date1'])?>&d2=<?=date("t", $data['date1'])?>&h2=23&search_type=ip&search_str=<?=$data['ip']?>" target="_blank"><?=$data['ip']?></a></td>
							</tr>
							<tr>
								<th scope="row">전화번호</th>
								<td><?=$data['buyer_phone']?></td>
							</tr>
							<tr>
								<th scope="row">휴대폰</th>
								<td>
									<?=$data['buyer_cell']?>
								</td>
							</tr>
							<tr>
								<th scope="row">이메일</th>
								<td>
									<?=$data['buyer_email']?>
									<span class="box_btn_s"><input type="button" value="메일보내기" onClick="smsMail('<?=$data['buyer_email']?>')"></span>
								</td>
							</tr>
						</table>
					</div>
					<!-- //주문자 정보 -->

					<!-- 배송지 정보 -->
					<div class="tabcnt tabcnt4">
						<div class="box_title first">
							<h3 class="title">배송지 정보</h3>
							<?php if ($admin['level'] < 4) { ?>
							<span class="box_btn_s blue btns"><input type="button" value="배송지정보수정" onclick="updateSbscr('edit_addressee');"></span>
							<?php } ?>
						</div>
						<table class="tbl_row">
							<caption class="hidden">배송지 정보</caption>
							<colgroup>
								<col style="width:15%">
								<col>
							</colgroup>
							<tr>
								<th scope="row">받는 분</th>
								<td><input type="text" name="addressee_name" value="<?=$data['addressee_name']?>" class="input"></td>
							</tr>
							<?php if($cfg['order_add_field_use'] == 'Y') { ?>
							<tr>
								<th scope="row">ID 번호</th>
								<td><input type="text" name="addressee_id" value="<?=$data['addressee_id']?>" class="input"></td>
							</tr>
							<?php } ?>

							<?php if ($data['nations']) { ?>
							<tr>
								<th scope="row">배송국가</th>
								<td>
									<?=getCountryNameFromCode($data['nations'])?>
								</td>
							</tr>
							<?php } ?>
							<tr>
								<th scope="row">주소</th>
								<td>
									<input type="text" name="addressee_zip" value="<?=$data['addressee_zip']?>" class="input" size="7"> <span class="box_btn_s"><input type="button" name="" value="우편번호" onClick="zipSearchM('ordFrm','addressee_zip','addressee_addr1','addressee_addr2')"></span>
									<?=$data['nations']?><br>
									<input type="text" name="addressee_addr1" value="<?=$data['addressee_addr1']?>" class="input" size="60" style="margin:5px 0;"><br>
                                    <?php if ($data['nations']) { ?>
									<?php if(fieldExist($tbl['order'],'addressee_addr3')) { ?>
										<input type="text" name="addressee_addr3" value="<?=inputText($data['addressee_addr3'])?>" class="input" size="60" style="margin:0 0 5px 0;">
									<?php } ?>
									<?php if(fieldExist($tbl['order'],'addressee_addr4')) { ?>
										<input type="text" name="addressee_addr4" value="<?=inputText($data['addressee_addr4'])?>" class="input" size="60" style="margin:0 0 5px 0;">
									<?php } ?>
                                    <?php }?>
									<input type="text" name="addressee_addr2" value="<?=inputText($data['addressee_addr2'])?>" class="input" size="60">
								</td>
							</tr>
							<tr>
								<th scope="row">전화번호</th>
								<td><input type="text" name="addressee_phone" value="<?=$data['addressee_phone']?>" class="input" size="13"></td>
							</tr>
							<tr>
								<th scope="row">휴대폰</th>
								<td>
									<input type="text" name="addressee_cell" value="<?=$data['addressee_cell']?>" class="input" size="13">
									<span class="box_btn_s"><input type="button" value="SMS" onClick="smsSend(this.form.addressee_cell.value)"></span>
								</td>
							</tr>
							<tr>
								<th scope="row">주문메세지</th>
								<td><textarea name="dlv_memo" class="txta" cols="90" rows="5"><?=stripslashes($data['dlv_memo'])?></textarea></td>
							</tr>
							<?php if ($_use['order_gift'] == 'Y') { ?>
							<tr>
								<th scope="row">보내는 분 표기</th>
								<td><?=$data['gift_name']?></td>
							</tr>
							<tr>
								<th scope="row">배송 희망 시간</th>
								<td><?=$data['dlv_date']?></td>
							</tr>
							<tr>
								<th scope="row">메세지 종류</th>
								<td><?=$_gift_type[$data['gift_type']]?></td>
							</tr>
							<tr>
								<th scope="row">메세지</th>
								<td><?=nl2br(stripslashes($data['gift_msg']))?></td>
							</tr>
							<?php } ?>
						</table>
					</div>
					<!-- //배송지 정보 -->

					<?php if ($admin['level'] < 4) { ?>
					<!-- 주문 관련 상담 -->
					<div class="tabcnt tabcnt5">
						<div class="box_title first">
							<h3 class="title">주문 관련 상담</h3>
						</div>
						<table class="tbl_col">
							<caption class="hidden">주문 관련 상담</caption>
							<colgroup>
								<col style="width:60px">
								<col style="width:120px">
								<col>
								<col style="width:100px">
								<col style="width:100px">
							</colgroup>
							<thead>
								<tr>
									<th scope="col">번호</th>
									<th scope="col">분류</th>
									<th scope="col">제목</th>
									<th scope="col">질문일</th>
									<th scope="col">답변일</th>
								</tr>
							</thead>
							<tbody>
								<?php
									$sql="select * from `$tbl[cs]` where `ono`='$ono' order by `reg_date` desc";
									$res = $pdo->iterator($sql);
									$idx = $res->rowCount();
                                    foreach ($res as $cs) {
										$rclass=($idx%2==0) ? "tcol2" : "tcol3";
										if($cs['reply_date']) {
											$cs['reply_date']=date("Y/m/d",$cs['reply_date']);
										}
										else {
											$cs['reply_date']="답변없음";
										}
								?>
								<tr>
									<td><?=$idx?></td>
									<td><?=$_cust_cate[$cs['cate1']][$cs['cate2']]?></td>
									<td class="left"><a href="javascript:;" onClick="wisaOpen('pop.php?body=member@1to1_view.frm&no=<?=$cs['no']?>')"><?=cutStr(stripslashes($cs['title']),50)?></a></td>
									<td><?=date("Y/m/d",$cs['reg_date'])?></td>
									<td><?=$cs['reply_date']?></td>
								</tr>
								<?php
										$idx--;
									}
								?>
							</tbody>
						</table>
					</div>
					<!-- 주문 관련 상담 -->
					<?php } ?>
				</form>

				<div class="pop_bottom" style="margin-bottom:50px;">
					<?php if ($admin['level'] < 4) { ?>
					<span class="box_btn gray"><input type="button" value="삭제" onClick="updateSbscr('delete')"></span>
					<?php } ?>
					<span class="box_btn gray"><input type="button" value="창닫기" onClick="wclose()"></span>
				</div>
			</div>
			<div class="aside">
				<div id="mng_memo_area">
					<?php if ($data['mng_memo']) { ?>
					<div class="frame_memo write">
						<textarea name="mng_memo" class="input_memo" placeholder="메모를 입력하세요."><?=stripslashes($data['mng_memo'])?></textarea>
						<div class="btn_bottom">
							<input type="button" onClick="updeteOrder('mng_memo','')">
						</div>
					</div>
					<?php } else { include $engine_dir."/_manage/member/member_memo_list.exe.php"; } ?>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	// 탭
	function tabover(no) {
		var tabs = $('#crm_order_list').find('li');
		tabs.each(function(idx) {
			window.scrollTo(0,0);
			var detail = $('.tabcnt'+idx);
			var aClass = $(this).find('a');
			if(no == idx) {
				detail.css('display', 'block');
				aClass.addClass('selected');
			} else {
				detail.css('display', 'none');
				aClass.removeClass('selected');
			}

			if (no == 0) {
				$('.tabcnt').show();
			}
		})
	}

	var f = document.ordFrm;
	this.focus();

	// input field 숫자만 입력가능
	function numCk(){
		if(event.keyCode != 8 && ((event.keyCode < 48) || (event.keyCode > 57)))  event.returnValue=false;
	}

	// 상태변경 레이어 처리
	var prev_sh = '0';
	function layerSH(no){
		for(var i = 0; i <= 19; i++) {
			var layer = document.getElementById('edt_layer_'+i);
			var ctab = document.getElementById('ctab_'+i);

			if(!ctab || !layer) continue;

			if(i == no) {
				layer.style.display = '';
				ctab.className = 'selected';
			} else {
				layer.style.display = 'none';
				ctab.className = '';
			}
		}
		if(no > 12) {
			$('#repayStat').hide();
		}
		if (no == 0) {
			$('#repayStat').show();
		}

		$('.check_pno_'+prev_sh).prop('checked', false);
		prev_sh = no;
	}

	function show(obj) {
		var obj = document.getElementById (obj);
		obj.style.display = ( obj.style.display == "none") ? "table" : "none";
	}

	// PG 취소 처리
	function cardCancel(mode, cno){
		if(mode == 'ALL') {
			if(!confirm('해당 거래내역을 취소하시겠습니까?')) return;
			window.frames[hid_frame].location.href='./?body=order@order_card_cancel.exe&cno='+cno;
		} else {
			if(!confirm('환불을 진행하시겠습니까?\n환불금액을 입력하신 후 확인 버튼을 눌러주시기 바랍니다.')) return;
			if(cno) {
				$('.card_cancel_box').hide();
				$('.card_cancel_div_'+cno).show();
			} else {
				$('.card_cancel_div').show();
			}
		}
	}

	//예약내역 취소
	function schCancel(type) {
		var f=document.bkFrm;
		if(type==2) {
			if(!checkCB(f["bno[]"], "취소하실 예약을 선택해주세요.")) return;
		}
		if(!confirm('선택하신 예약내역을 취소하시겠습니까?')) return false;

		f.body.value = "order@sbscr_update.exe";
		f.exec.value='cancel';
		f.type.value=type;
		f.submit();
	}

	//예약내역 결제/결제취소
	function schOrder(schno, type) {
		var f=document.bkFrm;

		if(type==1) {
			if(!confirm('해당 내역을 결제 처리 하시겠습니까?')) return false;
            printLoading();
			$.post('/main/exec.php?exec_file=cron/auto_sbscr_pay.exe.php', {"urlfix": "Y", "schno":schno}, function(msg) {
				alert(msg);
				location.reload();
			});
		}else {
			if(!confirm('해당 내역을 취소 하시겠습니까?')) return false;
			f.body.value = "order@sbscr_update.exe";
			f.exec.value='cancel';
			f.type.value=3;
			f.schno.value=schno;
			f.submit();
            printLoading();
		}
	}

	//예약내역 중지
	function schStop() {
		var f=document.bkFrm;
		if(!confirm('예약내역 중지하시겠습니까?')) return false;

		f.body.value = "order@sbscr_update.exe";
		f.exec.value='stop';
		f.submit();
	}

	function allCancel() {
		if(this.checked) this.form.card_cancel_price_<?=$card['no']?>.value=0; if(this.checked) this.form.card_cancel_price_<?=$card['no']?>.disabled=true;
	}

    function updateSbscr(exec)
    {
        if (exec == 'delete') {
            if (confirm('선택한 주문을 삭제하시겠습니까?') == false) {
                return false;
            }
        }
        printLoading();

        var form = document.ordFrm;
        form.exec.value = exec;
        form.submit();
    }

    /**
     * 과세, 비과세 금액 입력 취소 방식
     **/
    function cancelTax(cno, cname)
    {
        if (typeof cname == 'object') {
            cname = '';
        }

        var cname = (!cname) ? '' : '_'+cname;
        var url = './?body=order@order_card_cancel.exe&cno='+cno;
        $('.card_cancel_div'+cname).find('input').each(function() {
            if (this.name) {
                url += '&'+this.name+'='+this.value;
            }
        });

        printLoading();

        window.frames[hid_frame].location.href = url;
    }

    $(function() {
        removeLoading();

        $(window).on('keydown', function(e) {
            if (e.ctrlKey == true && e.altKey == true && e.keyCode == 76) {
                window.open(
                    './pop.php?body=order@order_log&ono=<?=$ono?>',
                    'order_log_<?=$ono?>',
                    'top=20px, left=20px, status=no, toolbars=no, scrollbars=yes, height=400px, width=1200px'
                );
                return false;
            }
        });
    });

    $(window).on('keydown', function(e) {
        console.log(e.keyCode);
        if (e.ctrlKey == true && e.altKey == true && e.keyCode == 76) {
            window.open(
                './pop.php?body=order@order_log&ono=<?=$ono?>',
                'order_log_<?=$ono?>',
                'top=20px, left=20px, status=no, toolbars=no, scrollbars=yes, height=400px, width=1200px'
            );
            return false;
        }
    });
</script>
</html>