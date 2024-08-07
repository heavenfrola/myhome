<?PHP
	trncCart(6);

	include_once $engine_dir."/_engine/include/shop.lib.php";

	$_order_stat[3] = $_sbscr_order_stat[3];
	$_order_stat[5] = $_sbscr_order_stat[5];
    foreach ($_order_stat_sbscr as $key => $val) {
        $_sbscr_order_stat[$key] .= "($val)";
    }

	if($admin['level'] == 4) {
		$prd_part = " and partner_no='$admin[partner_no]'";
	}

    if ($body == 'order@sbscr_excel.exe') {
        $sfield = 'a.*';
    } else {
        $sfield  = "a.no, a.sbono, a.date1, a.date2, a.buyer_name, a.member_no, a.member_id, a.bank_name, a.stat, a.pay_type, a.conversion, a.s_total_prc, a.buyer_cell, a.title, a.mobile, a.s_prd_prc, a.s_dlv_prc, a.addressee_name ";
        $sfield .= ", (select group_concat(concat(name,'(',buy_ea,')') separator ' / ') from $tbl[sbscr_product] where sbono = a.sbono $prd_part) as `opname`, c.dlv_finish_date";
    }
	$w = '';

	if($cfg['recipient'] == 'Y') $sfield .= ", addressee_name";

	if($cfg['bank_name2']) $cfg['bank_name'] = $cfg['bank_name2'];

	if($_GET['search_date_type']) $search_date_type = numberOnly($_GET['search_date_type']);
	if($_GET['all_date']) $all_date = $_GET['all_date'];
	$stat = ($_GET['stat']) ? numberOnly($_GET['stat']) : array();

	// 상태검색
	$stat_check_str = '';
	foreach($_sbscr_order_stat as $key=>$val) {
		if(in_array($key, array(31, 33, 40))) continue;
		$chkd = (is_array($stat) && in_array($key,$stat)) ? 'checked' : '';
		if($key == 11) $stat_check_str .= '</ul><ul class="list_common3">';
		else $stat_check_str .= "<li><label class=\"p_cursor\"><input type='checkbox' id='stat' name='stat[]' value='$key' $chkd> $val</label></li>";
	}
	if ( count($stat) > 0) $ws_ok=1;

	$ws = '';
	if($ws_ok) {
		$ws = " and a.stat in (".implode(',', $stat).")";
	}
	$ord_stat = numberOnly($_GET['ord_stat']);
	if($ord_stat > 0) {
		$ws_tab .= " /*order_stat_tab*/ and a.stat='$ord_stat'";
	}
	$ws = '/*order_stat*/ '.$ws.$ws_tab;
	$w .= $ws;

	$w .= " and a.`stat` != 11 and a.`stat` != 31";

	// 기간검색
	if($_GET['start_date']) $start_date = $_GET['start_date'];
	if($_GET['finish_date']) $finish_date = $_GET['finish_date'];
	if(!$start_date || !$finish_date) {
		$all_date = 'Y';
	}
	if(!$search_date_type) $search_date_type = 1;
	if(!$all_date) {
		$_start_date = strtotime($start_date);
		$_finish_date = strtotime($finish_date) + 86399;
		$w .= " and a.`date".$search_date_type."` between '$_start_date' and '$_finish_date'";
	}
	if(!$start_date || !$finish_date) {
		$start_date = $finish_date=date('Y-m-d', $now);
	}

	if(!$pay_type) $pay_type = numberOnly($_GET['pay_type']);
	if($pay_type) $w .= " and a.`pay_type`='$pay_type'";

	if($_GET['member_type'] == 'Y') $w .= ' and a.`member_no` > 0';
	elseif($_GET['member_type'] == 'N') $w .= ' and a.`member_no`=0';

	$pay_prc_s = numberOnly($_GET['pay_prc_s']);
	$pay_prc_f = numberOnly($_GET['pay_prc_f']);
	if($pay_prc_s != '') $w .= " and a.`s_pay_prc` >= '$pay_prc_s'";
	if($pay_prc_f != '') $w .= " and a.`s_pay_prc` <= '$pay_prc_f'";

	$prd_prc_s = numberOnly($_GET['prd_prc_s']);
	$prd_prc_f = numberOnly($_GET['prd_prc_f']);
	if($prd_prc_s != '') $w .= " and a.`s_prd_prc` >= '$prd_prc_s'";
	if($prd_prc_f != '') $w .= " and a.`s_prd_prc` <= '$prd_prc_f'";

	$mobile = $_GET['mobile'];
	if(is_array($mobile)){
		$tmp = $mobile;
		foreach($mobile as $key => $val) {
			$tmp[$key] = "'".addslashes($val)."'";
		}
		$w .= " and a.mobile in (".implode(',', $tmp).")";
	} else {
		$mobile = array();
	}

	$conversion_s = $_GET['conversion_s'];
	if(is_array($conversion_s)) {
		$tmp = '';
		foreach($conversion_s as $key => $val) {
			$tmp .= "or conversion like '%@".addslashes($val)."%'";
		}
		if($tmp) {
			$tmp = substr($tmp, 3);
			$w .= " and ($tmp)";
		}
	} else {
		$conversion_s = array();
	}

	// 텍스트 검색
	$_search_type = array();
	$_search_type['sbono'] = '주문번호';
	$_search_type['member_id'] = '회원아이디';
	$_search_type['buyer_name'] = '주문자 이름';
	$_search_type['buyer_cell'] = '주문자 휴대폰';
	$_search_type['buyer_phone'] = '주문자 전화';
	$_search_type['buyer_email'] = '주문자 이메일';
	$_search_type['bank_name'] = '입금자';
	$_search_type['pname'] = '상품명';
	$_search_type['addressee_name'] = '수령인 이름';
	$_search_type['addressee_addr1'] = '수령인 주소';
	$_search_type['addressee_addr2'] = '수령인 상세 주소';

	if(!$search_type) $search_type = $_GET['search_type'];
	$search_str = addslashes(trim($_GET['search_str']));
	if($_search_type[$search_type] && $search_str) {
		if($search_type == 'pname') {
			$w .= " and c.`name` like '%$search_str%'";
		} else {
			$w .= " and a.`$search_type` like '%$search_str%'";
		}
	}

	$pno = numberOnly($_GET['pno']);
	$optstr = addslashes(trim($_GET['optstr']));

	$sfield = "distinct a.no,".$sfield;
	$j .= " inner join `$tbl[sbscr_product]` c using(sbono)";

	if($pno > 0) {
		$w .= " and c.pno='$pno'";
		if($optstr) $w .= " and c.`option` like '%$optstr%'";
	}

	$QueryString = makeQueryString(true, 'page');
	$xls_query = makeQueryString('page', 'body');
	$list_tab_qry = makeQueryString(true, 'page', 'ord_stat');

	// 정렬
	$_order_by_name=array();
	$_order_by=array();

	$_order_by_name[1]="주문일역순";
	$_order_by[1]="`date1` desc";
	$_order_by_name[2]="주문일순";
	$_order_by[2]="`date1` asc";
	$_order_by_name[3]="입금일역순";
	$_order_by[3]="`date2` desc";
	$_order_by_name[4]="입금일순";
	$_order_by[4]="`date2` asc";

	$orderby = numberOnly($_GET['orderby']);
	if(!$orderby || !$_order_by[$orderby]) {
		$orderby=1;
	}

	if($admin['level'] < 4 && $cfg['use_partner_shop'] == 'Y') {
		$_partners = array('0' => '본사');
		$tres = $pdo->iterator("select no, corporate_name from $tbl[partner_shop] where stat=2 order by corporate_name asc");
        foreach ($tres as $tmp) {
			$_partners[$tmp['no']] = stripslashes($tmp['corporate_name']);
		}

		$partner_no = numberOnly($_GET['partner_no']);
		if(strlen($partner_no) > 0) {
			$w .= " and c.partner_no='$partner_no'";
		}
	}

	$sql  = "select $sfield from `$tbl[sbscr]` a $j where 1 $w group by `sbono` order by ".$_order_by[$orderby];
	$count_field = $j ? 'distinct a.sbono' : '*';
	$sql2 = "select count($count_field) from `$tbl[sbscr]` a $j where 1 $w";

	include $engine_dir.'/_engine/include/paging.php';

	$page = numberOnly($_GET['page']);
	if(!$row) $row = numberOnly($_GET['row']);
	if($page <= 1) $page = 1;
	if($row < 1 || $row > 1000) {
		$row = ($order_stat_group == 9) ? 100 : 10;
	}
	if($row > 100) $cfg['ord_list_first_prc'] = 'N';
	$block = 10;

	$NumTotalRec = $pdo->row($sql2);
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult['LimitQuery'];

	$pg_res = $PagingResult['PageLink'];
	$res = $pdo->iterator($sql);
	$idx = $NumTotalRec-($row*($page-1));

	$qs_without_row = makeQueryString(true, 'row');

    if ($body == 'order@sbscr_excel.exe') return;

	// 상태별 통계
	$_tabcnt = array();
	$wt = str_replace($ws, '', $w);
	$_tmpres = $pdo->iterator("select a.stat, count(distinct sbono) as cnt from $tbl[sbscr] a $j where 1 $wt group by a.stat");
    foreach ($_tmpres as $_tmp) {
		$_tabcnt[$_tmp['stat']] = $_tmp['cnt'];
	}
	$list_tab_qry = preg_replace('/^&/', '?', $list_tab_qry);
	${'list_tab_active'.$ord_stat} = 'class="active"';
	$wt = str_replace($ws_tab, '', $w);
	$_tabcnt['total'] = $pdo->row("select count($count_field) from `$tbl[sbscr]` a $j where 1 $wt");

?>
<div class="box_title first">
	<h2 class="title">정기배송 주문조회</h2>
</div>
<form method="post" id="ordSearchFrm" name="ordSearchFrm" action="./" onsubmit="searchSubmit(this,'<?=$_GET['body']?>')" >
	<input type="hidden" name="body" value="">
	<input type="hidden" name="exec" value="">
	<input type="hidden" name="ext" value="">
	<input type="hidden" name="t1" value="<?=$t1?>">
	<input type="hidden" name="approval" value="<?=$approval?>">
	<input type="hidden" name="msg_where">
	<input type="hidden" name="sms_deny">
	<input type="hidden" name="prd_no" value="<?=$prd_no?>">
	<!-- 검색 폼 -->
	<div id="search">
		<div class="box_search">
			<div class="box_input">
				<div class="select_input shadow">
					<div class="select">
						<?=selectArray($_search_type,"search_type",2,"",$search_type)?>
					</div>
					<div class="area_input order">
						<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" placeholder="검색어를 입력해주세요.">
						<span id="btn_search_setup" class="setup btt p_cursor" onclick="wisaOpen('./pop.php?body=order@order_search.frm',false,'600px','400px');" tooltip="검색설정"></span>
					</div>
				</div>
				<div class="view">
					<div id="searchCtl" onclick="toggle_shadow()"><?php searchBoxBtn("ordSearchFrm", $_COOKIE['ord_detail_search_on']) ?></div>
					<label class="p_cursor always"><input type="checkbox" id="search_cookie_ck" onclick="searchBoxCookie(this, 'ord_detail_search_on');" <?=checked($_COOKIE['ord_detail_search_on'], "Y")?>> 항상 상세검색</label>
				</div>
			</div>
		</div>
		<table class="tbl_search search_box_omit">
			<caption class="hidden">전체주문조회 </caption>
			<colgroup>
				<col style="width:150px">
				<col>
				<col style="width:150px">
				<col>
			</colgroup>
			<tr>
				<th scope="row">기간</th>
				<td colspan="3">
					<select name="search_date_type">
						<option value="1" <?=checked($search_date_type,1,1)?>>주문일</option>
						<option value="2" <?=checked($search_date_type,2,1)?>>입금일</option>
						<option value="3" <?=checked($search_date_type,3,1)?>>진행일</option>
						<option value="5" <?=checked($search_date_type,5,1)?>>진행종료일</option>
					</select>
					<label><input type="checkbox" name="all_date" value="Y" <?=checked($all_date,"Y")?> onClick="searchDate(this.form)"> 전체기간</label>
					<input type="text" name="start_date" value="<?=$start_date?>" size="7" class="input datepicker"> ~ <input type="text" name="finish_date" value="<?=$finish_date?>" size="7" class="input datepicker">
					<?PHP
						$date_type = array(
							'오늘' => '-0 days',
							'1주일' => '-1 weeks',
							'15일' => '-15 days',
							'1개월' => '-1 months',
							'3개월' => '-3 months',
							'6개월' => '-6 months',
							'1년' => '-1 years',
							'2년' => '-2 years',
							'3년' => '-3 years'
						);
						foreach($date_type as $key => $val) {
							$_btn_class=($val && !$all_date && $finish_date == date("Y-m-d", $now) && $start_date == date("Y-m-d", strtotime($val))) ? "on" : "";
							$_sdate=$_fdate = null;
							if($val) {
								$_sdate=date("Y-m-d", strtotime($val));
								$_fdate=date("Y-m-d", $now);
							}
							?> <span class="box_btn_d <?=$_btn_class?> strong"><input type="button" value="<?=$key?>" onclick="setSearchDatee(this.form, 'start_date', 'finish_date', '<?=$_sdate?>', '<?=$_fdate?>', '<?=$_GET['body']?>');"></span><?php
						}
					?>
					<script type="text/javascript">
						searchDate(document.ordSearchFrm);
					</script>
				</td>
			</tr>
			<tr>
				<th scope="row">거래상태</th>
				<td colspan="3">
					<ul class="list_common3">
						<?=$stat_check_str?>
					</ul>
				</td>
			</tr>
			<tr>
				<th scope="row">결제수단</th>
				<td>
					<?=selectArray($_pay_type,"pay_type",2,"::전체::",$pay_type)?>
				</td>
				<th scope="row">회원여부</th>
				<td>
					<label class="p_cursor"><input type="radio" name="member_type" value="" <?=checked($_GET['member_type'],"")?>> 전체</label>
					<label class="p_cursor"><input type="radio" name="member_type" value="Y" <?=checked($_GET['member_type'],"Y")?>> 회원</label>
					<label class="p_cursor"><input type="radio" name="member_type" value="N" <?=checked($_GET['member_type'],"N")?>> 비회원</label>
				</td>
			</tr>
			<tr>
				<th scope="row">결제금액</th>
				<td>
					<input type="text" name="pay_prc_s" size="10" value="<?=$pay_prc_s?>" class="input"> ~ <input type="text" name="pay_prc_f" size="10" value="<?=$pay_prc_f?>" class="input">
				</td>
				<th scope="row">페이지모드 </th>
				<td>
					<label class="p_cursor"><input type="checkbox" name="mobile[]" value="N" <?=in_array("N",$mobile)?'checked':''?>> PC화면</label>
					<label class="p_cursor"><input type="checkbox" name="mobile[]" value="Y" <?=in_array("Y",$mobile)?'checked':''?>> <?=$cfg['mobile_name']?> Web</label>
					<label class="p_cursor"><input type="checkbox" name="mobile[]" value="A" <?=in_array("A",$mobile)?'checked':''?>> <?=$cfg['mobile_name']?> App</label>
				</td>
			</tr>
			<tr>
				<th scope="row">상품가격</th>
				<td>
					<input type="text" name="prd_prc_s" size="10" value="<?=$prd_prc_s?>" class="input"> ~ <input type="text" name="prd_prc_f" size="10" value="<?=$prd_prc_f?>" class="input">
				</td>
				<th scope="row">주문 상품</th>
				<td>
					<span class="box_btn_s"><input type="button" value="찾기" onclick="searchOrderPrd(this)"></span>
					<span class="box_btn_s"><input type="button" value="검색취소" onclick="$('#search_prd').html('')"></span>
					<div id="search_prd" style="margin: 5px 0">
						<?php if ($pno) include $engine_dir.'/_manage/order/order_search_inc.exe.php'; ?>
					</div>
				</td>
			</tr>
			<tr>

			</tr>
			<?php if ($admin['level'] < 4 && $cfg['use_partner_shop'] == 'Y') { ?>
			<tr>
				<th>입점사</th>
				<td colspan="3">
					<?=selectArray($_partners, 'partner_no', false, ':: 입점사 ::', $_GET['partner_no'])?>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<th scope="row">유입경로</th>
				<td class="bcol2" colspan="3">
					<?=selectArrayConv("conversion_s")?>
				</td>
			</tr>
			<?php
				$convarr2 = selectArrayConv("conversion_s", 2);
				if($convarr2) {
			?>
			<tr>
				<th scope="row">배너광고유입</th>
				<td scope="row" class="bcol2" colspan="3">
					<?=$convarr2?>
				</td>
			</tr>
			<?php } ?>
		</table>
		<div class="box_bottom top_line">
			<span class="box_btn blue"><input type="submit" value="검색"></span>
			<span class="box_btn"><input type="button" value="초기화" onclick="location.href='./?body=<?=$_GET['body']?>'"></span>
		</div>
	</div>
	<!-- //검색 폼 -->
	<!-- 검색 총합 -->
	<?php if (!$order_stat_group) { ?>
	<style type="text/css" title="">
	@media all and (max-width:1700px) {
		.box_tab .btns {top:55px; z-index:5;}
	}
    .list_common3 li {
        width: 120px;
    }
	</style>
	<div class="box_tab">
		<ul>
			<li><a href="<?=$list_tab_qry?>" <?=$list_tab_active?>>전체&nbsp;<i class="icon_info btt" tooltip="'전체' 탭은 취소/환불/반품/교환 등 모든 상태를 포함하므로<br> 일반상태(미입금~배송완료)탭의 합과 차이가 있습니다."></i><span><?=number_format($_tabcnt['total'])?></span></a></li>
			<li><a href="<?=$list_tab_qry?>&ord_stat=1" <?=$list_tab_active1?>><?=$_sbscr_order_stat[1]?><span><?=number_format($_tabcnt[1])?></span></a></li>
			<li><a href="<?=$list_tab_qry?>&ord_stat=2" <?=$list_tab_active2?>><?=$_sbscr_order_stat[2]?><span><?=number_format($_tabcnt[2])?></span></a></li>
			<li><a href="<?=$list_tab_qry?>&ord_stat=3" <?=$list_tab_active3?>><?=$_sbscr_order_stat[3]?><span><?=number_format($_tabcnt[3])?></span></a></li>
			<li><a href="<?=$list_tab_qry?>&ord_stat=5" <?=$list_tab_active5?>><?=$_sbscr_order_stat[5]?><span><?=number_format($_tabcnt[5])?></span></a></li>
		</ul>
		<div class="btns">
			<span class="box_btn_s icon excel btt"><input type="button" value="엑셀다운" onclick="printExcel()"></span>
        </div>
	</div>
	<?php } else { ?>
	<div class="box_title">
		<strong id="total_prd"><?=number_format($NumTotalRec)?></strong> 개의 주문이 검색되었습니다.
	</div>
	<?php } ?>
	<!-- //검색 총합 -->
	<!-- 정렬 -->
	<div class="box_sort">
		<dl class="list">
			<dt class="hidden">정렬</dt>
			<dd>
				<select name="row" onchange="location.href='<?=$qs_without_row?>&row='+this.value">
					<option value="10" <?=checked($row,10,1)?>>10개</option>
					<option value="20" <?=checked($row,20,1)?>>20개</option>
					<option value="30" <?=checked($row,30,1)?>>30개</option>
					<option value="50" <?=checked($row,50,1)?>>50개</option>
					<option value="70" <?=checked($row,70,1)?>>70개</option>
					<option value="100" <?=checked($row,100,1)?>>100개</option>
					<option value="500" <?=checked($row,500,1)?>>500개</option>
					<option value="1000" <?=checked($row,1000,1)?>>1000개</option>
				</select>&nbsp;&nbsp;
				정렬
				<?=selectArray($_order_by_name,"orderby",2,"",$orderby, "location.href='$qs_without_sort&orderby='+this.value")?>
			</dd>
		</dl>
	</div>
	<!-- //정렬 -->
</form>
<!-- 검색 테이블 -->
<form method="post" name="prdFrm" action="./" onsubmit="searchSubmit(this,'<?=$_GET['body']?>')">
	<input type="hidden" name="body" value="">
	<input type="hidden" name="exec" value="">
	<input type="hidden" name="ext" value="">
	<input type="hidden" name="t1" value="<?=$t1?>">
	<input type="hidden" name="order_stat_group" value="<?=$order_stat_group?>">
	<input type="hidden" name="approval" value="<?=$approval?>">
	<input type="hidden" name="msg_where">
	<input type="hidden" name="sms_deny">
	<input type="hidden" name="prd_no" value="<?=$prd_no?>">
	<input type="hidden" name="black">
	<table class="tbl_col">
		<caption class="hidden">정기결제 주문조회 목록</caption>
		<colgroup>
			<col style="width:50px;">
			<col style="width:50px;">
			<col>
			<col>
			<col style="width:100px;">
			<col style="width:100px;">
			<col style="width:100px;">
			<col style="width:100px;">
			<col style="width:80px;">
			<col style="width:100px;">
			<col style="width:80px;">
		</colgroup>
		<thead>
			<tr>
				<th><input type="checkbox" onclick="checkAll(document.prdFrm.check_pno,this.checked)"></th>
				<th>번호</th>
				<th>주문번호</th>
				<th>주문상품</th>
				<th>주문일시</th>
				<th>주문자</th>
				<th>수령인</th>
				<th>총주문액</th>
				<th>결제방법</th>
				<th>상태</th>
				<th>상세보기</th>
			</tr>
		</thead>
		<tbody>
			<?PHP
                foreach ($res as $data) {
					$data['title'] = $data['opname'];
                    $data['stat2'] = getOrdStat($data, $data['stat']);
					$data = parseOrder($data);
					$data['title'] = addslashes(str_replace("'", "\"", $data['title']));

					$date2 = ($data['date2'] > 0) ? date("Y/m/d h:i:s A", $data['date2']) : " -";

					$data['conversion'] = dispConversion($data['conversion']);
					$data['mobile_icon'] = ($data['mobile'] == 'Y') ? "mobile" : "";
					$data['mobile_icon'] = ($data['mobile'] == 'A') ? "app" : $data['mobile_icon'];

					$printOno = $data['sbono'];

					$dlv_tulltip  = "<b>주문</b> : ".date("Y/m/d h:i:s A", $data['date1'])."<br>";
					$dlv_tulltip .= "<b>입금</b> : $date2";

					if($data['dlv_finish_date']=='0000-00-00') {
						$pay_type_text = "정기(무기한)";
					}
					if($data['dlv_finish_date']!='0000-00-00') {
						$pay_type_text = "정기(기한)";
					}
					if($data['pay_type']!=23) {
						$pay_type_text = "일괄";
					}

                    // 리스트에서 개인정보 마스킹
                    $data['member_id_v'] = $data['member_id'];
                    if ($scfg->comp('use_order_list_protect', 'Y') == true) {
                        $data['name'] = strMask($data['name'], 2, '＊');
                        $data['member_id_v'] = strMask($data['member_id'], 5, '***');
                    }
			?>
			<tr>
				<td><input type="checkbox" name="check_pno[]" id="check_pno" value="<?=$data['sbono']?>"></td>
				<td><?=$idx?></td>
				<td class="left">
					<a href="javascript:;" onClick="viewSbscr('<?=$data['sbono']?>')"><strong><?=$printOno?></strong></a>
				</td>
				<td class="left order_title p_cursor" title="" data-ono="<?=$data['sbono']?>">
					<div class="magicDIV <?=$data['mobile_icon']?>">
						<?=strip_tags(stripslashes($data['title']))?>
					</div>
				</td>
				<td onmouseover="showToolTip(event,'<?=$dlv_tulltip?>')" onmouseout="hideToolTip();"><?=date("m/d H:i",$data['date1'])?></td>
				<td><?=stripslashes($data['buyer_name'])?> <?=blackIconPrint('',$data)?></td>
				<td><?=stripslashes($data['addressee_name'])?></td>
				<td onmouseover="showToolTip(event,'<b>상품가격</b> : <?=parsePrice($data['s_prd_prc'], true)?> 원<br><b>배송비</b> : <?=parsePrice($data['s_dlv_prc'], true)?> 원<br>')" onmouseout="hideToolTip();"><?=parsePrice($data['s_total_prc'], true)?></td>
				<td><?=$pay_type_text?></td>
				<td class="right"><?=$data['stat2']?></td>
				<td><span class="box_btn_s blue"><input type="button" value="보기" onclick="listShow('<?=$data['no']?>');"></span></td>
			</tr>
			<?php include $engine_dir."/_manage/order/sbscr_schedule_list.exe.php"; ?>
			<?php
				$idx--;
			}?>
		</tbody>
	</table>
	<!-- 페이징 & 버튼 -->
	<div class="box_bottom">
		<?=$pg_res?>
        <!--
		<div class="left_area">
			<span class="box_btn_s icon setup"><input type="button" value="주문서수동생성" onclick="sbscrCreate();"></span>
		</div>
        -->
		<div class="right_area">
			<span class="box_btn_s icon delete"><input type="button" value="선택 삭제" onclick="deleteOrd(document.prdFrm);"></span>
		</div>
	</div>
	<!-- //페이징 & 버튼 -->
	<!-- 하단 탭 메뉴 -->
	<div id="controlTab">
		<ul class="tabs">
			<li id="ctab_1" class="selected">일괄상태변경</li>
		</ul>
		<div class="context">
			<div id="edt_layer_1">
				<div class="box_middle2 left">
					선택된 주문서 내
					<select name="ext1">
						<option value="" selected>전체</option>
						<option value="1"><?=$_sbscr_order_stat[1]?></option>
						<option value="2"><?=$_sbscr_order_stat[2]?></option>
						<option value="5"><?=$_sbscr_order_stat[5]?></option>
					</select>
					상태의 상품을
					<select name="ext2">
						<option value="1"><?=$_sbscr_order_stat[1]?></option>
						<option value="2" selected><?=$_sbscr_order_stat[2]?></option>
						<option value="5"><?=$_sbscr_order_stat[5]?></option>
					</select>
					상태로 변경합니다.
				</div>
				<div class="box_bottom"><span class="box_btn blue"><a href="javascript:" onclick="chgOrdStat(document.prdFrm);">확인</a></span></div>
			</div>
		</div>
	</div>
	<!-- //하단 탭 메뉴 -->
</form>
<!-- //검색 테이블 -->
<script type="text/javascript">
	var mw='<?=addslashes($w)?>';
	var use_trash_ord = '<?=$cfg['use_trash_ord']?>';
	function deleteOrd(f){
		if(!checkCB(f.check_pno,"삭제할 주문을 선택해주세요.")) return;

        if (window.confirm('삭제하시겠습니까?') == true) {
            f.body.value="order@sbscr_update.exe";
            f.exec.value = 'delete';
            f.method='post';
            f.target=hid_frame;
            f.submit();
        }
	}

	function layerSH(layer_name){
		 if(tmp_lyr != layer_name && tmp_lyr != ''){
			 if(document.getElementById(tmp_lyr).style.display == 'block') layTgl2(tmp_lyr);
		 }
		 tmp_lyr=layer_name;
		 layTgl(document.getElementById(layer_name));
	}

	var psearch = new layerWindow('product@product_inc.exe');
	psearch.psel = function(pno) {
		$.post('?body=order@order_search_inc.exe', {"exec":"prd", "pno":pno}, function(data) {
			$('#search_prd').html(data);
		})
		this.close();
	}

	function searchOrderPrd(obj) {
		psearch.input = obj;
		psearch.open();
	}

	$('input[name=search_str]').bind({
		"focus" : function() {
			$('#btn_search_setup').mouseenter();
		},
		"blur" : function() {
			$('#btn_search_setup').mouseleave();
		}
	});

	var oconfig = new layerWindow('order@order_config_order.exe');

	// 주문 상품 미리보기
	$('.order_title').tooltip({
		'show': {'effect':'fade', 'duration':100},
		'hide': {'effect':'fade', 'duration':100},
		'track': true,
		'content': function(callback) {
			var ono = $(this).attr('data-ono');
			$.ajax({
				'url': "./index.php",
				'data': {'body':'order@order_preview.exe', 'ono':ono, 'sbscr':'Y'},
				'type': "GET",
				'success': function(r) {
					callback(r);
				}
			});
		}
	}).click(function() {
		var ono = $(this).attr('data-ono');
		viewSbscr(ono);
	});

	function listShow(no) {
		var layer = document.getElementById('list_detail_tr_'+no);

		if(layer.style.display == '') {
			layer.style.display = 'none';
		}else {
			layer.style.display = '';
		}
	}

	function sbscrCreate() {
		if (!confirm('주문서를 수동생성하시겠습니까?')) return false;

		var param = {'exec_file':'cron/auto_sbscr.exe.php', 'exec':'json'};
		$.ajax({
			url:'/main/exec.php',
			data:param,
			dataType:'html',
			type:'POST',
			success: function(r) {
				alert(r);
			}
		});
	}

	function sbscrSchSearch(row, page, idno) {
		var frow = '';

		if(row) frow += '&bvrow='+row;
		if(page) frow += '&bvpage='+page;
		if(idno) frow += '&idno='+idno;
		frow += '&paging=Y';

		$.ajax({
			type : 'GET',
			url : './?body=order@sbscr_schedule_list.exe',
			data: frow,
			dataType : 'html',
			success : function(result) {
				console.log(result);
				$("#list_detail_tr_"+idno).html(result);
			}
		});
	}

	//예약내역 결제/결제취소
	function chgBooking(schno, type) {
		var f=document.bkFrm;

		if(type==1) {
			if(!confirm('해당 내역을 결제 처리 하시겠습니까?')) return false;
			$.post('/main/exec.php?exec_file=cron/auto_sbscr_pay.exe.php', {"schno":schno}, function(msg) {
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
		}
	}

	// 일괄 상태변경
	function chgOrdStat(f){
		if(!checkCB(f.check_pno,"변경할 주문을 선택해주세요.")) return;
		if (!confirm('선택하신 주문의 상태를 일괄 변경하시겠습니까?')) return;

		f.body.value="order@sbscr_update.exe";
		f.exec.value='stat';
		f.method='post';
		f.target=hid_frame;
		f.submit();
	}

    function printExcel()
    {
        if (confirm('엑셀파일을 출력하시겠습니까?') == true) {
            location.href = '?body=order@sbscr_excel.exe<?=$xls_query?>';
        }
    }
</script>
<style type="text/css">
.magicDIV {
	height: 16px;
	overflow: hidden;
}

.magicDIV.mobile {
	padding-left: 15px;
	background: url('<?=$engine_url?>/_manage/image/mobile_icon.gif') no-repeat left 0;
}

.magicDIV.app {
	padding-left: 15px;
	background: url('<?=$engine_url?>/_manage/image/app_icon.gif') no-repeat left 0;
}
</style>