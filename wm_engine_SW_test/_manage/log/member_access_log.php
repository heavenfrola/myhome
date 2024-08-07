<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원접속통계
	' +----------------------------------------------------------------------------------------------+*/
	$start_date = addslashes($_GET['start_date']);
	$finish_date = addslashes($_GET['finish_date']);
	$login_result = addslashes($_GET['login_result']);
	$search_type = addslashes($_GET['search_type']);

	if(!$start_date) $start_date = date('Y-m-d');
	if(!$finish_date) $finish_date = $start_date;

	if($login_result!="") {
		$w.=" and `login_result`='$login_result'";
	}

	$search_str = addslashes(trim($_GET['search_str']));
	if($search_type && $search_str!="") {
		$w.=" and `$search_type` like '%$search_str%'";
	}

	if($_GET['all_date'] != 'Y') {
		$_sdate = strtotime($start_date);
		$_edate = strtotime($finish_date)+86399;

		$w .= " and log_date between '$_sdate' and '$_edate'";
	}

	foreach($_GET as $key=>$val) {
		if($key!="page") $QueryString.="&".$key."=".$val;
	}

	$_search_type[member_id]='아이디';
	$_search_type[ip]='아이피';

	$sql="select * from `$tbl[member_log]` where 1 $w order by `no` desc";

    if ($body == 'log@member_access_log_excel.exe') return;

	include $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);

	if($page<=1) $page=1;
	if(!$row) $row=20;
	$block=10;
	$QueryString .="&body=$body&smode=$smode&mno=$mno";

	$NumTotalRec = $pdo->row("select count(*) from `$tbl[member_log]` where 1 $w");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult[LimitQuery];

	$pg_res=$PagingResult[PageLink];
	$res = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1));

	$qs_without_row = '?'.preg_replace('/&row=[^&]+/', '', $_SERVER['QUERY_STRING']);

    // 엑셀 다운로드 인증 방식
    $excel_auth_str = '관리자 비밀번호';
    if ($scfg->comp('use_mexcel_protect', 'Y') == true) {
        if ($scfg->comp('mexcel_otp_method', 'sms') == true) $excel_auth_str = '관리자 휴대폰번호';
        else if ($scfg->comp('mexcel_otp_method', 'mail') == true) $excel_auth_str = '관리자 이메일주소';
    }

?>
<form id="logFrm" method="get" action="./">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="exec" value="">
	<div class="box_title first">
		<h2 class="title">회원접속통계</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">회원접속통계</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">날짜</th>
			<td><?=setDateBunttonSet('start_date', 'finish_date', $start_date, $finish_date, true)?></td>
		</tr>
		<tr>
			<th scope="row">상태</th>
			<td><?=selectArray($_login_result,"login_result",2,"::전체::",$login_result)?></td>
		</tr>
	</table>
	<div class="box_bottom">
		<?=selectArray($_search_type,"search_type",2,"",$search_type)?>
		<input type="text" name="search_str" value="<?=inputText($search_str)?>" size="40" class="input">
		<span class="box_btn blue"><input type="submit" value="검색"></span>
		<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>'"></span>
	</div>
</form>

<div>
	<div class="box_title">
		<strong id="total_prd"><?=number_format($NumTotalRec)?></strong> 건의 로그가 검색되었습니다.
        <div class="btns">
            <span class="box_btn_s icon excel"><input type="button" value="엑셀다운" onclick="xlsdown(0)"></span>
        </div>
	</div>

    <!-- 엑셀 저장 레이어 -->
    <form id="excelLayer" class="popup_layer" style="display: none" onsubmit="return xlsdown(1);">
        <table class="tbl_mini">
            <tr>
                <th scope="row"><?=$excel_auth_str?></th>
                <td class="left">
                    <input type="password" name="xls_down" class="input" autocomplete="new-password">
                </td>
            </tr>
            <tr>
                <th scope="row">다운로드 사유</th>
                <td class="left">
                    <input type="text" name="xls_reason" class="input">
                </td>
            </tr>
        </table>
        <div class="btn_bottom">
            <span class="box_btn_s blue"><input type="submit" value="엑셀다운"></span>
            <span class="box_btn_s"><input type="button" value="닫기" onclick="$('#excelLayer').toggle();"></span>
        </div>
    </form>
    <!-- //엑셀 저장 레이어 -->

	<div class="box_sort">
		<dl class="list">
			<dt class="hidden">정렬</dt>
			<dd>
				로그수
				<select name="row" onchange="location.href='<?=$qs_without_row?>&row='+this.value">
					<option value="20" <?=checked($row,20,1)?>>20</option>
					<option value="30" <?=checked($row,30,1)?>>30</option>
					<option value="50" <?=checked($row,50,1)?>>50</option>
					<option value="70" <?=checked($row,70,1)?>>70</option>
					<option value="100" <?=checked($row,100,1)?>>100</option>
				</select>
			</dd>
		</dl>
	</div>

	<table class="tbl_col">
		<colgroup>
			<col style="width:60px">
			<col style="width:60px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><input type="checkbox" onclick="checkAll(document.prdFrm.check_pno,this.checked)"></th>
				<th scope="col">순서</th>
				<th scope="col">아이디</th>
				<th scope="col">일시</th>
				<th scope="col">결과</th>
				<th scope="col">IP</th>
			</tr>
		</thead>
		<tbody>
			<?php
                foreach ($res as $idx => $data) {
                    // 개인정보 접속 로그
                    if ($idx == 1) {
                        addPrivacyViewLog(array(
                            'page_id' => 'member_access_log',
                            'page_type' => 'list',
                            'target_id' => $data['member_id'],
                            'target_cnt' => $NumTotalRec
                        ));
                    }
            ?>
			<tr>
				<td><input type="checkbox" name="check_pno[]" id="check_pno" value="<?=$data[no]?>"></td>
				<td><?=$idx?></td>
				<td class="left">
					<a href="./?body=member@member_list&search_type=member_id&search_str=<?=$data[member_id]?>" target="_blank"  title="<?=$data[member_id]?>의 회원 정보 찾기"><?=$data[member_id]?></a>
					<span class="box_btn_s"><input type="button" value="검색" onclick="location.href='./?body=<?=$body?>&search_type=member_id&search_str=<?=$data[member_id]?>'"></span>
				</td>
				<td><?=date("Y/m/d H:i:s",$data[log_date])?></td>
				<td><?=$_login_result[$data[login_result]]?></td>
				<td>
					<a href="http://www.apnic.net/apnic-bin/whois.pl?searchtext=<?=$data[ip]?>" target="_blank" title="IP 정보"><?=$data[ip]?></a>
					<span class="box_btn_s"><input type="button" value="검색" onclick="location.href='./?body=<?=$body?>&search_type=ip&search_str=<?=$data[ip]?>'"></span>
				</td>
			</tr>
			<?php }	?>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn_s gray left_area"><a href="javascript:;" onclick="deleteMemberAccess(document.prdFrm);">선택삭제</a></span>
		<?=$pg_res?>
	</div>
</div>
<script>
function xlsdown(s)
{
    if (s == 0) {
        $('#excelLayer').show().css({
            'position': 'absolute',
            'right': '50px'
        });
        return;
    }

    let f = document.querySelector('#logFrm');
    let xf = document.querySelector('#excelLayer');

    if (checkBlank(xf.xls_down, '인증번호를 입력해주세요.') == false) return false;
    if (checkBlank(xf.xls_reason, '다운로드 사유를 입력해주세요.') == false) return false;

    let param = $(f).serialize().replace(/body=[^&]+/, '');
    param += '&xls_down='+xf.xls_down.value;
    param += '&xls_reason='+xf.xls_reason.value;

    window.frames[hid_frame].location.href = './index.php?body=log@member_access_log_excel.exe'+param;

    return false;
}
</script>