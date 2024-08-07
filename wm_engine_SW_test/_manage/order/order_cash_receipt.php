<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  현금영수증 관리
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/shop.lib.php";

	if($cfg[cash_receipt_use]!="Y") {
	?>
	<div class="box_title">
		<h2 class="title">현금 영수증 관리</h2>
	</div>
	<div class="box_full center">
		현재 현금영수증 발급신청기능이 설정되어 있지 않습니다.<br>
		<span class="box_btn_s"><a href="./?body=config@cash_receipt" target="_blank">설정변경하기</a></span>
	</div>
	<?
	return;
	}

	if(!fieldExist($tbl['cash_receipt'], 'msg')) {
		addField($tbl['cash_receipt'],"msg","VARCHAR(100) NOT NULL");
	}

	addField($tbl['cash_receipt'],"chk_approval_no","VARCHAR(30) NOT NULL default ''");

	$cfg[cash_r_pg]=$cfg[cash_r_pg] ? $cfg[cash_r_pg] : $cfg[card_pg];

	$order_stat=array(''=>'전체', '1'=>$_order_stat[1], '2'=>$_order_stat[2], '3'=>$_order_stat[3], '4'=>$_order_stat[4], '5'=>$_order_stat[5], '19'=>$_order_stat[19]);

	$_search_type['cons_name']='주문자';
	$_search_type['member_id']='회원아이디';
	$_search_type['cash_reg_num']='신청번호';
	$_search_type['ono']='주문번호';
	$_search_type['mtrsno']='승인번호';
	$_search_type['chk_approval_no'] = '국세청승인번호';

	$stat = numberOnly($_GET['stat']);
	if(!empty($stat) && $stat > 0) $w .= " and c.stat='$stat'";

	$ostat = numberOnly($_GET['ostat']);
	if($ostat > 0) {
		if($ostat == 19) $w .= " and o.`stat2` like '%$ostat@%'";
		else $w .= " and o.`stat`='$ostat'";
	}
	$search_type = trim($_GET['search_type']);
	$search_str = addslashes(trim($_GET['search_str']));
	if($_search_type[$search_type] && $search_str!="") {
		if($search_type == 'member_id') $w.=" and o.`$search_type` like '%$search_str%'";
		else $w.=" and c.`$search_type` like '%$search_str%'";
	}

	$all_date = $_GET['all_date'];
	$start_date = trim($_GET['start_date']);
	$finish_date = trim($_GET['finish_date']);
	$search_date_type = numberOnly($_GET['search_date_type']);
	if(!$start_date || !$finish_date) {
		$all_date="Y";
		$start_date = $finish_date = date("Y-m-d",$now);
	}
	if(!$all_date) {
		if($search_date_type == 1) {
			$_start_date = strtotime($start_date);
			$_finish_date = strtotime($finish_date)+86399;
			$w.=" and c.`reg_date` between '$_start_date' and '$_finish_date'";
		}elseif($search_date_type == 2) {
			$_start_date=str_replace("-", "", $start_date);
			$_finish_date=str_replace("-", "", $finish_date);
			$w.=" and left(c.`ono`, 8) between '$_start_date' and '$_finish_date'";
		}elseif($search_date_type == 3) {
			$_start_date=strtotime($start_date);
			$_finish_date=strtotime($finish_date)+86399;
			$w.=" and c.`tsdtime` between '$_start_date' and '$_finish_date'";
		}
	}

	$sql="select c.*, o.`stat` as ostat, o.bank, o.date1 from `$tbl[cash_receipt]` c left join `$tbl[order]` o on c.`ono`=o.`ono` where c.amt1>0 $w order by c.`no` desc";
	$sql2="select count(*) from `$tbl[cash_receipt]` c left join `$tbl[order]` o on c.`ono`=o.`ono` where c.amt1>0 $w";

	if(strpos($body, 'order@order_cash_receipt_excel.exe') !== false) return;

	include $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);
	if($page<=1) $page=1;
	$row=numberOnly($row);
	if($row<1 || $row>1000) $row=20;
	$block=10;
	$QueryString = makeQueryString('page');
	$xls_query = makeQueryString('page', 'body');

	$NumTotalRec = $pdo->row($sql2);
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult=$PagingInstance->result($pg_dsn);

	if($body == 'order@order_cash_receipt.exe') return;

	$sql.=$PagingResult[LimitQuery];

	$pg_res=$PagingResult[PageLink];
	$res = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1));

    // 엑셀 다운로드 인증 방식
    if ($scfg->comp('use_cexcel_protect', 'Y') == true) {
        if ($scfg->comp('cexcel_otp_method', 'sms') == true) $excel_auth_str = '관리자 휴대폰번호';
        else if ($scfg->comp('cexcel_otp_method', 'mail') == true) $excel_auth_str = '관리자 이메일주소';
    }

?>
<!-- 검색 폼 -->
<form name="searchFrm" method="get" action="./" class='searchFrm'>
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="row" value="<?=$row?>">
	<div class="box_title first">
		<h2 class="title">현금 영수증 관리</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">현금 영수증 관리 검색</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">상태</th>
			<td>
				<?foreach($_order_cash_stat as $key=>$val) {?>
				<label class="p_cursor"><input type="radio" name="stat" value="<?=$key?>" <?=checked($stat,$key)?>> <?=$val?></label>
				<?}?>
			</td>
		</tr>
		<tr>
			<th scope="row">주문상태</th>
			<td>
				<?php foreach ($order_stat as $key=>$val) { ?>
				<label class="p_cursor"><input type="radio" name="ostat" value="<?=$key?>" <?=checked($ostat,$key)?>> <?=$val?></label>
				<?php } ?>
			</td>
		</tr>
		<tr>
			<th scope="row">기간</th>
			<td>
				<select name="search_date_type">
					<option value="1" <?=checked($search_date_type,1,1)?>>신청일기준</option>
					<option value="2" <?=checked($search_date_type,2,1)?>>주문일기준</option>
					<option value="3" <?=checked($search_date_type,3,1)?>>발급일기준</option>
				</select>
				<label class="p_cursor"><input type="checkbox" name="all_date" value="Y" <?=checked($all_date,"Y")?> onClick="searchDate(this.form)"> 전체 기간</label>
				<input type="text" name="start_date" value="<?=$start_date?>" size="10" class="input datepicker"> ~ <input type="text" name="finish_date" value="<?=$finish_date?>" size="10" class="input datepicker">
				<script type="text/javascript">
				searchDate(document.searchFrm);
				</script>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<?=selectArray($_search_type,"search_type",2,"::선택::",$search_type)?>
		<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" size="40">
		<span class="box_btn gray"><input type="submit" value="검색"></span>
		<span class="box_btn"><input type="button" value="초기화" onclick="document.location.href='/_manage/?body=<?=$body?>';"></span>
	</div>
</form>
<!-- //검색 폼 -->
<!-- 검색 총합 -->
<div class="box_title">
	<strong id="total_prd"><?=number_format($NumTotalRec)?></strong> 개의 신청내역이 검색되었습니다.
	<div class="btns">
        <?php if ($scfg->comp('use_cexcel_protect', 'Y') == true) { ?>
        <span class="box_btn_s icon excel btt"><input type="button" value="엑셀다운" onclick="showExcelBtn(event);"></span>
        <?php } else { ?>
		<span class="box_btn_s icon excel"><a href="?body=order@order_cash_receipt_excel.exe<?=$xls_query?>">엑셀다운</a></span>
        <?php } ?>
	</div>
</div>
<!-- //검색 총합 -->

<!-- 엑셀 저장 레이어 -->
<form method="post" action="./?body=order@order_cash_receipt_excel.exe<?=$xls_query?>" target="hidden<?=$now?>" id="excelLayer" class="popup_layer"style="display:none;">
	<input type="hidden" name="ckno" value="">
	<input type="hidden" name="checked" value="">
	<table class="tbl_mini">
        <colgroup>
            <col style="width: 50px">
            <col>
        </colgroup>
		<tr>
			<th scope="row">인증</th>
			<td class="left">
                <input type="text" name="xls_down" class="input" placeholder="<?=$excel_auth_str?>">
			</td>
		</tr>
	</table>
	<div class="btn_bottom">
		<span class="box_btn_s blue"><input type="button" value="엑셀다운" onclick="orderExcel()"></span>
		<span class="box_btn_s"><input type="button" value="닫기" onclick="showExcelBtn(event);"></span>
	</div>
</form>
<!-- //엑셀 저장 레이어 -->

<!-- 정렬 -->
<div class="box_sort">
	<dl class="list">
		<dt class="hidden">정렬</dt>
		<dd class="first-child">
			내역수
			<?=selectArray(array(20,50,100),"row",1,"",$row,"$('.searchFrm').find('[name=row]').val(this.value); $('.searchFrm').submit();")?> 개씩 보기
		</dd>
	</dl>
</div>
<!-- //정렬 -->
<form name="prdFrm" method="post" action="./">
	<input type="hidden" name="body" value="">
	<input type="hidden" name="exec" value="">
	<input type="hidden" name="ext" value="">
	<input type="hidden" name="cash_r_pg" value="<?=$cfg[cash_r_pg]?>">
	<input type="hidden" name="query_string" value="<?=urlencode($QueryString)?>">
	<!-- 검색 테이블 -->
	<table class="tbl_col">
		<caption class="hidden">현금 영수증 관리 리스트</caption>
		<colgroup>
			<col style="width:60px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><input type="checkbox" onclick="checkAll(document.prdFrm.check_pno,this.checked)"></th>
				<th scope="col">번호</th>
				<th scope="col">주문번호</th>
				<th scope="col">신청일</th>
				<th scope="col">신청번호</th>
				<th scope="col">승인번호</th>
				<th scope="col">국세청 승인번호</th>
				<th scope="col">사업자번호</th>
				<th scope="col">주문자</th>
				<th scope="col">총결제액</th>
				<th scope="col">비과세</th>
                <th scope="col">부가세</th>
				<th scope="col">결제방법</th>
				<th scope="col">주문상태</th>
				<th scope="col">상태</th>
			</tr>
		</thead>
		<tbody>
			<?php
                foreach ($res as $data) {
					$stat=$_order_cash_stat[$data['stat']];
                    $pay_type = $_pay_type[$data['pay_type']];
                    $pay_type = preg_replace('/ 입금$/', '', $pay_type);
					$style_class=($idx%2==0) ? "tcol3" : "tcol2";

					$data[cons_name]=($data[member_no] && $data[member_id]) ? "<a href=\"javascript:;\" onclick=\"viewMember('$data[member_no]','$data[member_id]')\">$data[cons_name]($data[member_id])</a>" : $data[cons_name];
					$_ostat=$data['ostat'];
					$_ostatv=($_ostat) ? getOrdStat($data,$_ostat) : "";

					$bank = mb_strimwidth($data['bank'], 0, 4, null, _BASE_CHARSET_);
					if($bank) $bank = "($bank)";
					if(!$data['mtrsno']) $data['mtrsno'] = "--";

					// 타 상점과의 주문번호 충돌 방지
					if(defined('use_cash_receipt_prefix') == true) {
						$ono_prefix = $data['date1'].'_';
					}

					$viewOrder = (preg_match('/SS/',$data['ono'])) ? "viewSbscr('$data[ono]')":"viewOrder('$data[ono]')";
                    $log_rows = $pdo->row("select count(*) from {$tbl['cash_receipt_log']} where cno=?", array($data['no']));

                    if ($NumTotalRec==$idx) {
                        addPrivacyViewLog(array(
                            'page_id' => 'cash',
                            'page_type' => 'list',
                            'target_id' => $data['member_id'],
                            'target_cnt' => $NumTotalRec
                        ));
                    }

                    // 리스트에서 개인정보 마스킹
                    $data['member_id_v'] = $data['member_id'];
                    if ($scfg->comp('use_member_list_protect', 'Y') == true) {
                        $data['b_num'] = strMask($data['b_num'], 5, '＊＊＊');
                        $data['cons_name'] = strMask($data['cons_name'], 2, '＊');
                    }

			?>
			<tr>
				<td>
					<input type="checkbox" name="check_pno[]" id="check_pno" value="<?=$data[no]?>">
				</td>
				<td><?=$idx?></td>
				<td><a href="javascript:;" onClick="<?=$viewOrder?>"><?=$data['ono']?></a></td>
				<td><?=date("Y/m/d",$data['reg_date'])?></td>
				<td><?=$data['cash_reg_num']?></td>
				<td><?=$data['mtrsno']?><?=($data[mtrsno] != "--") ? " ".cashReceiptView($ono_prefix.$data['ono']) : "";?></td>
				<td><?=$data['chk_approval_no']?></td>
				<td><?=$data['b_num']?></td>
				<td><?=$data['cons_name']?></td>
				<td><?=parsePrice($data['amt1'], true)?> 원</td>
                <td><?=parsePrice($data['taxfree_amt'], true)?> 원</td>
                <td><?=parsePrice($data['amt4'], true)?> 원</td>
				<td><?=$pay_type?><?=$bank?></td>
				<td><?=$_ostatv?></td>
				<td>
                    <span id="stat<?=$data['no']?>"><?=$stat?></span>
                    <?php if ($log_rows > 0) {?>
                    <div>
                        <a href="#" onclick="log.open('cno=<?=$data['no']?>'); return false;">
                            [변경내역 <strong style="text-decoration:underline; color: #000;"><?=$log_rows?></strong>건]
                        </a>
                    </div>
                    <?php } ?>
                </td>
			</tr>
			<?if($data['msg']) {?>
			<tr>
				<td colspan="2"></td>
				<td colspan="12" style="text-align:left;padding-left:30px">
                    <ul class="list_info">
                        <li class="warning"><?=stripslashes($data['msg'])?></li>
                    </ul>
                </td>
			</tr>
			<?}?>
			<?
				$idx--;
				}
			?>
		</tbody>
	</table>
	<!-- //검색 테이블 -->
	<!-- 페이징 & 버튼 -->
	<div class="box_bottom">
		<?=$pg_res?>
		<div class="left_area">
			<span class="box_btn"><input type="button" value="발급신청" onclick="chgOrdStat(document.prdFrm, 1);"></span>
			<span class="box_btn"><input type="button" value="발급취소" onclick="chgOrdStat(document.prdFrm, 2);"></span>
		</div>
		<ul class="list_info left">
			<br>
			<li>타 서버와 통신으로 처리가 진행되므로, 많은 데이터 처리 시 시간이 소요될 수 있습니다.</li>
			<li>발급신청은 상태가 '신청'이 아닌 경우 적용되지 않습니다.</li>
			<li>발급취소는 상태가 '발급'이 아닌 경우 적용되지 않습니다.</li>
			<li>사업자번호 변경은 상태가 '신청'이 아닌 경우 적용되지 않습니다.</li>
			<li>현금영수증 발급내역은 국세청홈택스 사이트에서 로그인 > [조회/발급]에서 현금영수증 발급내역을 확인할 수 있습니다.</li>
			<li>가상계좌 입금의 경우 현금영수증 확인은 이용중인 PG사 상점관리자에서 확인이 가능합니다.</li>
		<ul>
	</div>
	<!-- //페이징 & 버튼 -->
	<!-- 하단 탭 메뉴 -->
	<div id="controlTab">
		<ul class="tabs">
			<li id="ctab_1" onclick="tabSH(1)" class="selected">일괄정보변경</li>
		</ul>
		<div class="context">
			<div id="edt_layer_1" class="box_middle2 left">
				<select name="ssmode">
					<option value="2">선택한 주문</option>
					<option value="4">검색된 모든 주문(<?=number_format($NumTotalRec)?>명)</option>
					<option value="3">전체 주문</option>
				</select>
				의 사업자번호를
				<input type="text" name="b_num" value="" class="input"> 로 변경합니다.
			</div>
			<div class="box_bottom">
				<span class="box_btn blue"><a href="javascript:" onclick="chgBnum(document.prdFrm);">확인</a></span>
			</div>
		</div>
	</div>
	<!-- //하단 탭 메뉴 -->
</form>
<div style="padding-top:30px;" class="center">
<span class="box_btn blue"><a href="https://www.hometax.go.kr" target="_blank">국세청 홈택스 바로가기</a></span>
</div>

<form id="delFrm" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="order@order_cash_receipt.exe">
	<input type="hidden" name="exec" value="delete">
	<input type="hidden" name="check_pno[]" value="">
</form>

<script type="text/javascript">
function deleteOrd(f) {
	return;
	if(!checkCB(f.check_pno,"삭제할 신청건을 선택해주세요.")) return;
	if(!confirm('선택하신 신청건을 삭제하시겠습니까?')) return;
	f.body.value="order@order_cash_receipt.exe";
	f.exec.value="delete";
	f.method='post';
	f.target=hid_frame;
	f.submit();
}

function chgBnum(f) {

	if(!checkBlank(f.b_num, '변경하실 사업자번호를 입력해주세요.')) return;
	if(f.ssmode.value==2) {
		if(!checkCB(f.check_pno,"사업자번호를 변경할 주문을 선택해주세요.")) return;
		msg="선택하신 주문의 사업자번호를\t\n"+f.b_num.value+"(으)로 변경하시겠습니까?";
	}

	if(f.ssmode.value == 4) msg="검색된 모든 주문의 사업자번호를\t\n"+f.b_num.value+"(으)로 변경하시겠습니까?";
	if(f.ssmode.value==3) msg="정말로 전체 사업자번호를\t\n"+f.b_num.value+"(으)로 변경하시겠습니까?";

	if(!confirm(msg)) return;

	f.body.value="order@order_cash_receipt.exe";
	f.exec.value='chgBnum';
	f.method='post';
	f.target=hid_frame;
	f.submit();
}

function chgOrdStat(f, s) {
	if(!s) sn=f.ext2.selectedIndex+1;
	else sn = s;
	if(sn == 1) sn='발급신청';
	if(sn == 2) sn='발급취소';
	if(sn == 3) sn='재발급';
	if(!checkCB(f.check_pno, sn+'할 주문서를 선택해주세요.')) return;
	if(!confirm('선택하신 주문서를 '+sn+'하시겠습니까?     ')) return;
	f.body.value="order@order_cash_receipt.exe";
	f.ext.value=s;
	f.method='post';
	f.target=hid_frame;
	f.submit();
}

function deleteReceipt(no) {
	if(confirm('세금계산서 신청내역을 삭제하시겠습니까?')) {
		var f = document.getElementById('delFrm');
		f.elements['check_pno[]'].value = no;
		f.submit();
	}
}

function showExcelBtn(ev) {
    var ev = window.event ? window.event : ev;
    var layer = document.getElementById('excelLayer');

    if(layer.style.display == 'block') {
        layer.style.display = 'none';
    } else {
        layer.style.display = 'block';
        layer.style.position = 'absolute';
        layer.style.top = ($(document).scrollTop()+ev.clientY+25)+'px';
        layer.style.right = '72px';
    }
}

function orderExcel() {
    var f = document.getElementById('excelLayer');
    f.submit();
}

var log = new layerWindow('order@order_cash_receipt_log.pop');
</script>