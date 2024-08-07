<?PHP

	// 날짜형식 유효검사함수
	function date_validation($date_str) {
		// 0000-00-00 기준
		return (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})/", $date_str)) ? True : False;
	}

	$where = " 1 AND nr.`del_stat`='N' ";
	$orderby = "ORDER BY nr.`no` DESC ";
	$search_columns = "
					  nr.*
					, m.`name` as member_name, m.`member_id`
					, p.`hash`, p.`name` as product_name, p.`sell_prc`, p.`wm_sc`, p.`updir`, p.`upfile3`, p.`w3`, p.`h3` ";

	// 신청상태 배열
	$stat_array = array(
		  "1" => "신청완료"
		, "2" => "알림완료"
		, "3" => "신청취소"
        , "4" => "알림기간만료"
	);

	// 텍스트 검색
	$_search_type = array();
	$_search_type['pname'] = '상품명';
	$_search_type['name'] = '회원명';
	$_search_type['member_id'] = '회원아이디';

	if(!$search_type) $search_type = $_GET['search_type'];
	$search_str = addslashes(trim($_GET['search_str']));
	if($_search_type[$search_type] && $search_str) {
		if($search_type == 'pname') {
			$where .= " AND p.`name` LIKE '%$search_str%'";
		} elseif($search_type == "name" || $search_type == "member_id") {
			$where .= " AND m.`$search_type` LIKE '%$search_str%'";
		} else {
			$where .= " AND nr.`$search_type` LIKE '%$search_str%'";
		}
	}

	$pno = numberOnly($_GET['pno']);
	$search_stat = numberOnly($_GET['search_stat']);
	$all_date = ($_GET['all_date'] == "Y") ? "Y" : "";

	// 기간검색 처리
	$start_date = $_GET['start_date'];
	$finish_date = $_GET['finish_date'];
	if(date_validation($start_date) && date_validation($finish_date)) {
		$start_time = strtotime($start_date." 00:00:00");
		$finish_time = strtotime($finish_date." 23:59:59");
	} else {
		$start_time = strtotime(date('Y-m-d').' 00:00:00') - (84600*15); // 15일전으로 세팅
		$finish_time = strtotime(date('Y-m-d').' 23:59:59');
		$start_date = date('Y-m-d', $start_time);
        $finish_date = date('Y-m-d', $finish_time);
	}

	if($all_date == "Y") {
		$start_date = $finish_date = "";
	} else {
		$where .= " AND nr.`reg_date` BETWEEN '$start_time' AND '$finish_time' ";
	}

	// 상태검색
    $stat_sql = "";
	$search_stat = trim($_GET['search_stat']);
	if(!$stat_array[$search_stat]) {
		$search_stat = "";
	}
	if($search_stat) {
		$stat_sql = " AND nr.`stat` = '$search_stat' ";
	    $where .= $stat_sql;
	}

	$QueryString = makeQueryString(true, 'page');
	$list_tab_qry = makeQueryString(true, 'page', 'search_stat');

	// 기본쿼리
	$sql = "SELECT
				$search_columns
			FROM
				$tbl[notify_restock] nr
				LEFT JOIN $tbl[member] m ON nr.`member_no`=m.`no`
				INNER JOIN $tbl[product] p ON nr.`pno`=p.`no`
			WHERE
				$where
            $orderby
		  ";

	// 전체카운트 쿼리
	$sql2 = "SELECT
				count(nr.`no`)
			FROM
				$tbl[notify_restock] nr
				LEFT JOIN $tbl[member] m ON nr.`member_no`=m.`no`
				INNER JOIN $tbl[product] p ON nr.`pno`=p.`no`
			WHERE
				$where
			";

    if ($body == 'member@notify_restock_excel.exe') return;

	// 상태카운트 쿼리
    $sql3 = str_replace($search_columns, " nr.`stat`, count(nr.`stat`) as cnt ", $sql);
    $sql3 = str_replace($stat_sql, "", $sql3);
    $sql3 = str_replace($orderby, "", $sql3);
    $sql3 .= " GROUP BY nr.`stat`";
    $_res = $pdo->iterator($sql3);
    $stat_count_total = 0;
    foreach ($_res as $_row) {
        $stat_count_array[$_row['stat']] = $_row['cnt'];
        $stat_count_total += $_row['cnt'];
    }
    unset($_row, $_res);

    ${'list_tab_active'.$search_stat} = 'class="active"';

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

	$qs_without_row = '?'.preg_replace('/&row=[^&]+/', '', $_SERVER['QUERY_STRING']);
	$qs_without_sort = '?'.preg_replace('/&orderby=[^&]+/', '', $_SERVER['QUERY_STRING']);
    $qs_excel = makeQueryString('page', 'body', 'row');
    $qs_excel = '?body=member@notify_restock_excel.exe'.$qs_excel;

?>
<form method="post" id="ordSearchFrm" name="ordSearchFrm" action="./" onsubmit="searchSubmit(this,'<?=$_GET['body']?>')" >
	<input type="hidden" name="body" value="">
	<input type="hidden" name="exec" value="">
	<div class="box_title first">
		<h2 class="title">재입고 알림 신청 내역</h2>
	</div>
    <!-- 검색 폼 -->
	<div id="search">
		<div class="box_search">
			<div class="box_input">
				<div class="select_input shadow full">
					<div class="select">
						<?=selectArray($_search_type,"search_type",2,"",$search_type)?>
					</div>
					<div class="area_input order">
						<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" placeholder="검색어를 입력해주세요.">
					</div>
				</div>
			</div>
			<ul class="quick_search">
			</ul>
		</div>
		<table class="tbl_search">
			<caption class="hidden">재입고 알림 신청 내역</caption>
			<colgroup>
				<col style="width:12%;">
				<col>
			</colgroup>
			<tr>
				<th scope="row">기간</th>
				<td>
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
						);
						foreach($date_type as $key => $val) {
							$_btn_class=($val && !$all_date && $finish_date == date("Y-m-d", $now) && $start_date == date("Y-m-d", strtotime($val))) ? "on" : "";
							$_sdate=$_fdate = null;
							if($val) {
								$_sdate=date("Y-m-d", strtotime($val));
								$_fdate=date("Y-m-d", $now);
							}
							?> <span class="box_btn_d <?=$_btn_class?> strong"><input type="button" value="<?=$key?>" onclick="setSearchDatee(this.form, 'start_date', 'finish_date', '<?=$_sdate?>', '<?=$_fdate?>', '<?=$_GET['body']?>');"></span><?
						}
					?>
					<script type="text/javascript">
						searchDate(document.ordSearchFrm);
					</script>
				</td>
			</tr>
		</table>
		<div class="box_bottom top_line">
			<span class="box_btn blue"><input type="submit" value="검색"></span>
			<span class="box_btn"><input type="button" value="초기화" onclick="location.href='./?body=<?=$_GET[body]?>'"></span>
		</div>
	</div>
	<!-- //검색 폼 -->
	<!-- 검색 총합 -->
	<div class="box_tab">
		<ul>
			<li><a href="<?=$list_tab_qry?>" <?=$list_tab_active?>>전체<span><?=number_format($stat_count_total)?></span></a></li>
			<li><a href="<?=$list_tab_qry?>&search_stat=1" <?=$list_tab_active1?>><?=$stat_array[1]?><span><?=number_format($stat_count_array[1])?></span></a></li>
			<li><a href="<?=$list_tab_qry?>&search_stat=2" <?=$list_tab_active2?>><?=$stat_array[2]?><span><?=number_format($stat_count_array[2])?></span></a></li>
            <li><a href="<?=$list_tab_qry?>&search_stat=3" <?=$list_tab_active3?>><?=$stat_array[3]?><span><?=number_format($stat_count_array[3])?></span></a></li>
            <li><a href="<?=$list_tab_qry?>&search_stat=4" <?=$list_tab_active5?>><?=$stat_array[4]?><span><?=number_format($stat_count_array[4])?></span></a></li>
		</ul>
        <div class="btns">
	    	<span class="box_btn_s icon excel"><input type="button" value="엑셀다운"></span>
    	</div>
	</div>
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
				</select>
			</dd>
		</dl>
	</div>
	<!-- //정렬 -->
</form>

<!-- 검색 테이블 -->
<form method="post" name="notify_restockFrm" action="./" onsubmit="searchSubmit(this,'<?=$_GET['body']?>')">
	<input type="hidden" name="body" value="">
	<input type="hidden" name="exec" value="">
    <input type="hidden" name="msg_where" value="">
    <input type="hidden" name="sms_deny" value="N">
    <input type="hidden" name="black" value="">
	<table class="tbl_col">
		<caption class="hidden">재입고 알림 신청 내역</caption>
		<colgroup>
			<col style="width:50px;">
			<col style="width:50px;">
			<col style="width:80px;">
			<col>
			<col style="width:160px;">
			<col style="width:80px;">
			<col>
			<col style="width:120px;">
			<col style="width:130px;">
			<col style="width:130px;">
			<col style="width:100px;">
		</colgroup>
		<thead>
			<tr>
				<th><input type="checkbox" onclick="checkAll(document.notify_restockFrm.check_no,this.checked)"></th>
				<th>번호</th>
				<th colspan="2">상품명</th>
				<th>옵션</th>
				<th>상품금액</th>
				<th>회원</th>
				<th>신청번호</th>
				<th>신청일시</th>
				<th>발송일시</th>
				<th>상태</th>
			</tr>
		</thead>
		<tbody>
			<?PHP
                foreach ($res as $data) {
					// 상품옵션
					$_options_str = $data['option'];

					// 신청자
					$_member_info = "";
					$_member_script = "";
					$_buyer_cell = $data['buyer_cell'];
					if($data['member_no']) {
						$_member_info = "$data[member_name]($data[member_id])";
						$_member_script = "viewMember('$data[member_no]', '$data[member_id]');";
					} else {
						$_member_info = "비회원";
					}

					$_reg_date = date('Y/m/d H:i', $data['reg_date']); // 신청일
					$_send_date = ($data['send_date']) ? date('Y/m/d H:i', $data['send_date']) : "-"; // 신청일
					$_stat = $stat_array[$data['stat']]; // 신청상태


					$view_link = "shop";
					$edit_link = "product@product_register";
					$file_dir = getFileDir($data['updir']);
					if($data['upfile3'] && ((!$_use['file_server'] && is_file($root_dir."/".$data['updir']."/".$data['upfile3'])) || $_use['file_server'] == "Y")) {
						$is = setImageSize($data['w3'], $data['h3'], 50, 50);
						$data['imgstr'] = "<img src='$file_dir/{$data[updir]}/{$data[upfile3]}' class='prdimgs' $is[2]>";
					}
					$productname = $data['product_name'];
			?>
			<tr>
				<td><input type="checkbox" name="check_no[]" id="check_no" value="<?=$data[no]?>"></td>
				<td><?=$idx?></td>
				<td class="nobd"><a href="<?=$root_url?>/<?=$view_link?>/detail.php?pno=<?=$data['hash']?>" target="_blank"><?=$data['imgstr']?></a></td>
				<td class="left"><a href="./?body=<?=$edit_link?>&pno=<?=$data['pno']?>"><?=$productname?></a></td>
				<td class="left order_title"><?= $_options_str; ?></td>
				<td><?= number_format($data['sell_prc']); ?></td>
				<td>
					<a href="javascript:;" onClick="<?= $_member_script; ?>"><?= $_member_info; ?></a>
				</td>
				<td><?= $_buyer_cell; ?></td>
				<td><?= $_reg_date; ?></td>
				<td><?= $_send_date; ?></td>
				<td><?= $_stat; ?></td>
			</tr>
			<?
				$idx--;
			}
			?>
		</tbody>
	</table>
	<!-- 페이징 & 버튼 -->
	<div class="box_bottom">
		<?=$pg_res?>
		<?if($admin['level'] < 4) {?>
		<div class="left_area">
			<span class="box_btn_s icon delete"><input type="button" value="선택 삭제" onclick="deleteOrd(document.notify_restockFrm);"></span>
		</div>
		<?}?>
		<div class="right_area">
		</div>
	</div>
	<!-- //페이징 & 버튼 -->
	<!-- 하단 탭 메뉴 -->
	<div id="controlTab">
		<ul class="tabs">
			<li id="ctab_1" onclick="tabSH(1)" class="selected">일괄상태변경</li>
			<?if($admin['level'] < 4) {?>
			<li id="ctab_2" onclick="tabSH(2)">문자 발송</li>
			<?}?>
		</ul>
		<div class="context">
			<div id="edt_layer_1">
				<div class="box_middle2 left">
					선택된 신청 내역의 상태를
					<select name="ext">
						<option value="1"><?=$stat_array[1]?></option>
						<option value="2"><?=$stat_array[2]?></option>
						<option value="3"><?=$stat_array[3]?></option>
						<option value="4"><?=$stat_array[4]?></option>
					</select>
					상태로 변경합니다.
					<div class="list_info">
						<p>일괄상태변경에 따른 알림발송은 진행되지 않습니다.</p>
					</div>
				</div>
				<div class="box_bottom"><span class="box_btn blue"><a href="javascript:" onclick="chgOrdStat(document.notify_restockFrm);">확인</a></span></div>
			</div>
			<div id="edt_layer_2" style="display: none">
				<div class="box_middle2 left">
					<select name="ssmode">
						<option value="2">선택한 내역</option>
						<option value="4">검색된 모든 내역(<?=number_format($NumTotalRec)?>명)</option>
						<option value="3">전체 내역</option>
					</select>
					<label><input type="checkbox" name="correct_num" value="Y" checked> 부정확한 번호제외</label>
				</div>
				<div class="box_bottom"><span class="box_btn blue"><a href="javascript:" onclick="multiSMS();">확인</a></span></div>
			</div>
		</div>
	</div>
	<!-- //하단 탭 메뉴 -->
</form>
<!-- //검색 테이블 -->
<script type="text/javascript">
    $('.prdimgs').mouseover(function() {
        new R2Tip(this, '<img src='+this.src+'>', 'R2Tip2', event);
    });

	var mw='<?=addslashes($where)?>';
	function deleteOrd(f){
		if(!checkCB(f.check_no,"삭제할 내역을 선택해주세요.")) return;

		var del_msg = '선택한 모든 내역이 삭제됩니다.\n정말로 삭제하시겠습니까?';
		if(!confirm(del_msg)) return;
		f.body.value="member@notify_restock.exe";
		f.exec.value = 'delete';
		f.method='post';
		f.target=hid_frame;
		f.submit();
	}

    function chgOrdStat(f){
        if(!checkCB(f.check_no,"변경할 내역을 선택해주세요.")) return;
        if (!confirm('선택하신 내역의 상태를 일괄 변경하시겠습니까?')) return;

        f.body.value="member@notify_restock.exe";
        f.exec.value = 'update_stat';
        f.method='post';
        f.target=hid_frame;
        f.submit();
    }

	var tmp_lyr='';
	function multiSMS(tp){
		f=document.notify_restockFrm;
		if(tp==1){
			layerSH('smsDiv');
			return;
		}else{
			if (f.ssmode.value==2)
			{
				if(!checkCB(f.check_no,"문자를 전송할 내역을 선택해주세요.")) return;
			}
			if (f.ssmode.value==3){
				if(!confirm("정말로 전체 신청내역에 문자를 발송하시겠습니까?")) return;
			}

			window.open('','wm_sms','top=10,left=200,width=920,height=650,status=no,toolbars=no,scrollbars=yes');
			var old_body=f.body.value;
			f.body.value='member@sms_sender.frm';
			f.target='wm_sms';
			f.method='post';
			f.msg_where.value=mw;
			f.exec.value='form_notify_restock';
			f.submit();

			f.body.value=old_body;
			f.exec.value='';
			f.target='';
		}
	}

	function layerSH(layer_name){
		 if(tmp_lyr != layer_name && tmp_lyr != ''){
			 if(document.getElementById(tmp_lyr).style.display == 'block') layTgl2(tmp_lyr);
		 }
		 tmp_lyr=layer_name;
		 layTgl(document.getElementById(layer_name));
	}

	$('input[name=search_str]').bind({
		"focus" : function() {
			$('#btn_search_setup').mouseenter();
		},
		"blur" : function() {
			$('#btn_search_setup').mouseleave();
		}
	});

    $('.excel>input[type=button]').on('click', function() {
        location.href = '<?=$qs_excel?>';
    });
</script>