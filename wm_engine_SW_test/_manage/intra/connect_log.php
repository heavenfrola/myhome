<?PHP

	if(!isTable($tbl['privacy_view_log'])) {
		include_once $engine_dir."/_config/tbl_schema.php";
		$pdo->query($tbl_schema['privacy_view_log']);
	}

	$w = '';

	$_page_name = array('order' => '주문', 'member' => '회원', 'member_access_log' => '회원접속로그', 'milage' => '적립금', 'emoney' => '예치금', 'board' => '게시판', 'qna' => '상품문의', '1to1' => '1대1 상담', 'cash' => '현금영수증');
	$_search_type = array(
		'name' => '관리자명',
		'admin_id' => '관리자아이디',
	);

	$search_str = trim($_GET['search_str']);
	$search_type = trim($_GET['search_type']);
	$page_id = addslashes(trim($_GET['page_id']));
	$page_type = addslashes(trim($_GET['page_type']));
	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);

	if($search_str && $_search_type[$search_type]) {
		$_search_str1 = addslashes($search_str);
		$w .= " and b.$search_type like '%$_search_str1%'";
	}

	if ($page_id) {
        if ($page_id == 'order') $w .= " and page_id in ('order', 'cash')";
        else $w .= " and page_id='$page_id'";
    }
	if ($page_type) {
        if ($page_type == 'view') $w .= " and (page_type='$page_type' or page_type like '상세%')";
        else $w .= " and page_type='$page_type'";
    }

    // 기간 검색
    $date_type = array(
        '오늘' => '-0 days',
        '1주일' => '-1 weeks',
        '15일' => '-15 days',
        '1개월' => '-1 months', '3개월' => '-3 months', '6개월' => '-6 months',
        '1년' => '-1 years', '2년' => '-2 years', '3년' => '-3 years'
    );
	if ($_GET['all_date']) $all_date = $_GET['all_date'];
	if ($_GET['start_date']) $start_date = $_GET['start_date'];
	if ($_GET['finish_date']) $finish_date = $_GET['finish_date'];
	if (!$start_date || !$finish_date) {
		$start_date = date('Y-m-d', strtotime('-3 months'));
		$finish_date = date('Y-m-d', $now);
	}
	if ($all_date != 'Y') {
		$_start_date = strtotime($start_date);
		$_finish_date = strtotime($finish_date)+86399;
		$w .= " and a.reg_date between '$_start_date' and '$_finish_date'";
	}
    $date_picker = array();
    foreach ($date_type as $key => $val) {
        $_btn_class = ($val && !$all_date && $finish_date == date('Y-m-d', $now) && $start_date == date('Y-m-d', strtotime($val))) ? 'on' : '';
        $_sdate = $_fdate = null;
        if ($val) {
            $_sdate = date('Y-m-d', strtotime($val));
            $_fdate = date('Y-m-d', $now);
        }
        $date_picker[$key] = array(
            's_date' => $_sdate,
            'f_date' => $_fdate,
            'btn_class' => $_btn_class
        );
    }

	$sql = "select a.*, b.name from $tbl[privacy_view_log] a inner join $tbl[mng] b on a.admin_no=b.no where 1 $w order by a.no desc";

    if ($_GET['body'] == 'intra@connect_log_excel.exe') {
        return;
    }

	// 페이징
	include $engine_dir."/_engine/include/paging.php";

    $page = (int) $_GET['page'];
	if($page <= 1) $page = 1;
	if(!$row) $row = 20;
	if($row > 100) $row = 100;
	$block = 10;

	foreach($_GET as $key=>$val) {
		if($key!="page") $QueryString.="&".$key."=".$val;
	}
	if(!$QueryString) $QueryString = '&body='.$_GET['body'];

	$NumTotalRec = $pdo->row("select count(*) from $tbl[privacy_view_log] a inner join $tbl[mng] b on a.admin_no=b.no where 1 $w");

	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult = $PagingInstance->result($pg_dsn);

	$sql .= $PagingResult['LimitQuery'];

	$pageRes = $PagingResult['PageLink'];
	$res = $pdo->iterator($sql);
	$idx = $NumTotalRec-($row*($page-1));

	function getTarget($data) {
		$txt = $data['target_id'];
		if($data['target_cnt'] > 1) {
			$txt .= " 외 ".(number_format($data['target_cnt']-1)).'명';
		}
		return $txt;
	}

    $excel_qry = '?body=intra@connect_log_excel.exe'.makeQueryString('body');

?>
<!-- 검색폼 -->
<div class="box_title first">
	<h2 class="title">개인정보 접속기록 내역</h2>
</div>
<form id="connectFrm" name="connectFrm">
	<input type="hidden" name="body" value="<?=$body?>">
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
			<caption class="hidden">개인정보 접속기록 검색</caption>
			<colgroup>
				<col style="width:150px">
				<col>
			</colgroup>
			<tr>
            <tr>
                <th scope="row">기간</th>
                <td>
                    <label><input type="checkbox" name="all_date" value="Y" <?=checked($all_date,"Y")?> onClick="searchDate(this.form)"> 전체기간</label>
                    <input type="text" name="start_date" value="<?=$start_date?>" size="7" class="input datepicker"> ~ <input type="text" name="finish_date" value="<?=$finish_date?>" size="7" class="input datepicker">
                    <?php foreach($date_picker as $key => $val) { ?>
                    <span class="box_btn_d <?=$val['btn_class']?> strong">
                        <input type="button" value="<?=$key?>" onclick="setSearchDatee(this.form, 'start_date', 'finish_date', '<?=$val['s_date']?>', '<?=$val['f_date']?>', '<?=$_GET['body']?>');">
                    </span>
                    <?php } ?>
                    <script type="text/javascript">
                        searchDate(document.searchFrm);
                    </script>
                </td>
            </tr>
			<tr>
				<th scope="row">구분</th>
				<td>
					<label class="p_cursor"><input type="radio" name="page_id" value="" checked> 전체</label>
                    <?php foreach ($_page_name as $key => $val) { ?>
					<label class="p_cursor"><input type="radio" name="page_id" value="<?=$key?>" <?=checked($page_id, $key)?>> <?=$val?></label>
                    <?php } ?>
				</td>
			</tr>
			<tr>
				<th scope="row">접속페이지</th>
				<td>
					<label class="p_cursor"><input type="radio" name="page_type" value="" checked> 전체</label>
					<label class="p_cursor"><input type="radio" name="page_type" value="list" <?=checked($page_type, 'list')?>> 리스트</label>
					<label class="p_cursor"><input type="radio" name="page_type" value="view" <?=checked($page_type, 'view')?>> 상세</label>
					<label class="p_cursor"><input type="radio" name="page_type" value="update" <?=checked($page_type, 'update')?>> 수정</label>
					<label class="p_cursor"><input type="radio" name="page_type" value="excel" <?=checked($page_type, 'excel')?>> 엑셀 다운로드</label>
					<label class="p_cursor"><input type="radio" name="page_type" value="password" <?=checked($page_type, 'password')?>> 비밀번호 변경</label>
				</td>
			</tr>
		</table>
		<div class="box_bottom top_line">
			<span class="box_btn blue"><input type="submit" value="검색"></span>
			<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>'"></span>
		</div>
	</div>
</form>
<!-- //검색 폼 -->
<!-- 검색 총합 -->
<div class="box_title">
	<strong id="total_prd"><?=number_format($NumTotalRec)?></strong> 개의 개인정보 접속기록 내역이 검색되었습니다.
    <div class="btns">
        <span class="box_btn_s icon excel btt"><input type="button" value="엑셀다운" onclick="location.href='<?=$excel_qry?>'";></span>
    </div>
</div>
<!-- //검색 총합 -->
<table class="tbl_col">
	<colgroup>
		<col style="width:80px">
		<col style="width:150px">
		<col style="width:150px">
		<col style="width:150px">
		<col style="width:150px">
		<col>
		<col style="width:150px">
	</colgroup>
	<thead>
		<tr>
            <th scope="col">번호</th>
			<th scope="col">수행일시</th>
			<th scope="col">관리자</th>
			<th scope="col">구분</th>
			<th scope="col">접속페이지</th>
			<th scope="col">수행업무</th>
			<th scope="col">접속아이피</th>
		</tr>
	</thead>
	<tbody>
    <?php
    foreach ($res as $key => $data) {
        switch($data['page_type']) {
            case 'list' : $page_type = '리스트'; break;
            case 'view' : $page_type = '상세'; break;
            case 'excel' : $page_type = '엑셀 다운로드'; break;
            case 'update' : $page_type = '수정'; break;
            case 'password' : $page_type = '비밀번호변경'; break;
            default :
                $page_type = $data['page_type'];
        }

	?>
	<tr>
        <td><?=$idx-$key?></td>
		<td><?=date('Y/m/d H:i:s', $data['reg_date'])?></td>
		<td><?=$data['name']?>(<?=$data['admin_id']?>)</td>
		<td><?=$_page_name[$data['page_id']]?></td>
		<td><?=$page_type?></td>
		<td class="left"><?=getTarget($data)?></td>
		<td><?=$data['ip']?></td>
	</tr>
	<?php } ?>
    <?php if ($NumTotalRec == 0) { ?>
    <tr>
        <td colspan="7"><p class="nodata">검색된 접속 내역이 없습니다.</p></td>
    </tr>
    <?php } ?>
	</tbody>
</table>
<div class="box_bottom">
	<?=$pageRes?>
</div>
<div class="box_middle2 left">
	<div class="list_info">
		<p class="title">[개인정보 안정성 확보조치 기준]</p>
		<p>개인정보취급자가 개인정보처리시스템에 접속한 기록을 최소 1년 이상 기록/보관해야하며, 반기별 1회 이상 점검해야 합니다.</p>
	</div>
</div>