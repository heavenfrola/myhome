<?PHP

	if(!$cfg['alimtalk_profile_key']) {
		msg('카카오 알림톡 사용신청을 먼저 진행해 주세요.', '?body=wing@service@main');
	}

	if(!isTable($tbl['alimtalk_template'])) {
		include_once $engine_dir.'/_config/tbl_schema.php';
		$pdo->query($tbl_schema['alimtalk_template']);
	}

	$wec_alm = new weagleEyeClient($_we, 'alimtalk');
	include $engine_dir.'/_engine/sms/sms_module.php';
	foreach($sms_case_admin as $key => $val) {
		$sms_case_title[$val] = '(관리자) '.$sms_case_title[$val];
	}
	unset($sms_case_title[16]);
	unset($sms_case_title[19]);
	unset($sms_case_title[21]);

    $status = $wec_alm->call('sender', array(
        'senderKey' => $cfg['alimtalk_profile_key'],
    ));
    $status = json_decode($status);

    switch ($status->data->profileStatus) {
        case 'S' :
            javac("$(function() {
                recoverProfile();
            })");
            break;
        case 'D' :
            msg('삭제된 카카오 알림톡입니다. 신규 등록해주세요.', '?body=wing@service@main');
            break;
    }

	$_reg_status = array(
		'REG' => '등록',
		'REQ' => '심사요청',
		'APR' => '승인',
		'REJ' => '반려',
	);

	$list_tab_qry = makeQueryString(true, 'page', 'r_status', 'status');

	if($_GET['status']) {
		$_GET['r_status'] = addslashes($_GET['status']);
	}
	$sms_case = numberOnly($_GET['sms_case']);
	$use_yn = addslashes($_GET['use_yn']);
	$r_status = trim(addslashes($_GET['r_status']));

	$w = " and reg_status!='RMVD'";
	if($sms_case > 0) $w .= " and sms_case='$sms_case'";
	if($use_yn) $w .= " and use_yn='$use_yn'";
    $w_cnt = $w; // 상태별 개수 조건
	if($r_status) $w .= " and reg_status='$r_status'";

	$sql = "select * from $tbl[alimtalk_template] where reg_status!='DEL' $w order by no desc";

	include $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	if($page <= 1) $page = 1;
	$row = 10;
	$block = 10;

	$NumTotalRec = $pdo->row("select count(*) from $tbl[alimtalk_template] where reg_status!='DEL' $w");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult = $PagingInstance->result($pg_dsn);
	$sql.=$PagingResult['LimitQuery'];

	$pg_res = $PagingResult['PageLink'];
	$res = $pdo->iterator($sql);
	$idx = $NumTotalRec - ($row * ($page - 1));

	$datas = array();
    foreach ($res as $data) {
		if($data['reg_status'] == 'REQ') { // 템플릿 상태 갱신
			$ret = $wec_alm->call('getTemplateStatus', array('templateCode'=>$data['templateCode']));
			$ret = json_decode($ret);

			if(!fieldExist($tbl['alimtalk_template'], 'message')) {
				addField($tbl['alimtalk_template'], 'message', 'varchar(255) null default ""');
			}

			$asql = '';
			if(array_key_exists($ret->data->inspectionStatus, $_reg_status) && $data['reg_status'] != $ret->data->inspectionStatus) {
				$data['reg_status'] = $ret->data->inspectionStatus;
				$asql .= ", reg_status='$data[reg_status]'";
				//반려사유
				if(count($ret->data->comments)>0) {
					$_count = count($ret->data->comments)-1;
					$data['message'] = $ret->data->comments[$_count]->content;
					$asql .= ", message='$data[message]'";
				}
			}
			if($asql) {
				$asql = substr($asql, 1);
				$pdo->query("update $tbl[alimtalk_template] set $asql where no='$data[no]'");
			}
		}
        $datas[] = $data;
	}

	// 상태별 통계
	$_tabcnt = array();
	$_tmpres = $pdo->iterator("select reg_status, count(*) as cnt from $tbl[alimtalk_template] where reg_status!='DEL' $w_cnt group by reg_status");
    foreach ($_tmpres as $_tmp) {
		$_tabcnt[$_tmp['reg_status']] = $_tmp['cnt'];
	}
	$list_tab_qry = preg_replace('/^&/', '?', $list_tab_qry);
	${'list_tab_active'.$r_status} = 'class="active"';
	$_tabcnt['total'] = $pdo->row("select count(*) from $tbl[alimtalk_template] where reg_status!='DEL' $w_cnt");

	setListURL('kakao_amt_msg');

?>
<form method="get" action="./index.php">
	<input type="hidden" name="body" value="<?=$_GET['body']?>">
	<div class="box_title first">
		<h2 class="title">카카오 알림톡 메시지 관리</h2>
	</div>
	<div id="search">
		<table class="tbl_search">
			<caption class="hidden">카카오 알림톡 메시지 조회</caption>
			<colgroup>
				<col style="width:150px">
				<col>
				<col style="width:150px">
				<col>
			</colgroup>
			<tr>
				<th scope="row">분류</th>
				<td>
					<?=selectArray($sms_case_title, 'sms_case', false, '전체', $sms_case)?>
				</td>
				<th scope="row">사용여부</th>
				<td>
					<label><input type="radio" name="use_yn" value="" <?=checked($_GET['use_yn'], '')?>> 전체</label>
					<label><input type="radio" name="use_yn" value="Y" <?=checked($_GET['use_yn'], 'Y')?>> 사용중</label>
					<label><input type="radio" name="use_yn" value="N" <?=checked($_GET['use_yn'], 'N')?>> 사용안함</label>
				</td>
			</tr>
		</table>
		<div class="box_bottom top_line">
			<span class="box_btn blue"><input type="submit" value="검색"></span>
			<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>'"></span>
		</div>
	</div>
</form>
<form method="POST" action="./index.php" target="hidden<?=$now?>" onsubmit="return removeTemplate()">
    <input type="hidden" name="body" value="member@kakao_amt_reg.exe">
    <input type="hidden" name="exec" value="remove">

	<div class="box_tab">
		<ul>
			<li><a href="<?=$list_tab_qry?>" <?=$list_tab_active?>>전체<span><?=number_format($_tabcnt['total'])?></span></a></li>
			<li><a href="<?=$list_tab_qry?>&status=REG" <?=$list_tab_activeREG?>>등록<span><?=number_format($_tabcnt['REG'])?></span></a></li>
			<li><a href="<?=$list_tab_qry?>&status=REQ" <?=$list_tab_activeREQ?>>심사요청<span><?=number_format($_tabcnt['REQ'])?></span></a></li>
			<li><a href="<?=$list_tab_qry?>&status=APR" <?=$list_tab_activeAPR?>>승인<span><?=number_format($_tabcnt['APR'])?></span></a></li>
			<li><a href="<?=$list_tab_qry?>&status=REJ" <?=$list_tab_activeREJ?>>반려<span><?=number_format($_tabcnt['REJ'])?></span></a></li>
		</ul>
	</div>
	<div class="box_sort"></div>
	<table class="tbl_col">
		<caption class="hidden">카카오 알림톡 메시지 관리</caption>
		<colgroup>
            <col style="width:50px">
			<col style="width:120px">
			<col style="width:120px">
			<col>
			<col style="width:120px">
			<col style="width:150px">
			<col style="width:150px">
		</colgroup>
		<thead>
			<tr>
                <th><input type="checkbox" class="check_all"></th>
				<th>코드</th>
				<th>발송분류</th>
				<th>메시지명</th>
				<th>검수상태</th>
				<th>등록일시</th>
				<th>관리</th>
			</tr>
		</thead>
		<tbody>
			<?foreach($datas as $data) {?>
			<tr>
                <td><input type="checkbox" class="check_sub" name="tno[]" value="<?=$data['no']?>"></td>
				<td><?=$data['templateCode']?></td>
				<td><?=$sms_case_title[$data['sms_case']]?></td>
				<td class="left">
					<?=$data['templateName']?>
					<div class="explain" style="margin-top: 5px;"><?=nl2br($data['templateContent'])?></div>
				</td>
				<td>
					<div style="position:relative;">
						<?=$_reg_status[$data['reg_status']]?>
						<?if($data['reg_status'] == 'REJ') {?>
						<span class="info_square2 p_cursor" onclick="toggle_layer('layer_return_<?=$data['no']?>');">정보</span>
						<div class="layer_view layer_return layer_return_<?=$data['no']?>">
							<?=$data['message']?>
						</div>
						<?}?>
					</div>
				</td>
				<td><?=date('Y-m-d H:i', $data['reg_date'])?></td>
				<td>
					<?if($data['reg_status'] == 'REG' || $data['reg_status'] == 'REJ') {?>
					<span class="box_btn_s"><input type="button" value="심사요청" onclick="requestTemplate(<?=$data['no']?>);"></span>
					<span class="box_btn_s"><input type="button" value="수정" onclick="goM('member@kakao_amt_reg&no=<?=$data['no']?>');"></span>
					<?}?>
					<?if($data['reg_status'] == 'APR') {?>
					<?if($data['use_yn'] == 'N') {?>
					<span class="box_btn_s blue"><input type="button" value="사용하기" onclick="useTemplate(<?=$data['no']?>, 'Y')"></span>
					<?} else {?>
					<span class="box_btn_s gray"><input type="button" value="해제하기" onclick="useTemplate(<?=$data['no']?>, 'N')"></span>
					<?}?>
					<?}?>
				</td>
			</tr>
			<?}?>
		</tbody>
	</table>
	<!-- 페이징 & 버튼 -->
	<div class="box_bottom">
		<?=$pg_res?>
        <div class="left_area">
            <span class="box_btn_s icon delete"><input type="submit" value="선택 삭제"></span>
        </div>
        <div class="right_area">
            <span class="box_btn_s icon regist"><input type="button" value="등록" onclick="goM('member@kakao_amt_reg');"></span>
        </div>
	</div>
	<!-- //페이징 & 버튼 -->
	<ul class="box_bottom list_msg left" style="padding: 10px;">
		<li>메시지 등록 및 수정 요청 시 3~7일 정도 검수 기간이 소요될 수 있습니다.</li>
		<li>검수 통과된 메시지만 사용 가능합니다.</li>
		<li>템플릿 검수상태가 등록/반려 일때만 메시지의 수정이 가능합니다.</li>
	</ul>
</form>
<script type="text/javascript">
function removeTemplate(no) {
    if ($('.check_sub:checked').length == 0) {
        window.alert('삭제할 메시지를 선택해주세요.');
        return false;
    }
    if (confirm('삭제한 메시지는 복구하실수 없습니다.\n선택한 메시지를 삭제하시겠습니까?') == false) {
        return false;
    }

    printLoading();

    return true;
}

function requestTemplate(no) {
	if(confirm('심사요청 하신 후 메시지의 수정이 불가능합니다.\n선택한 메시지의 심사를 요청 하시겠습니까?')) {
		$.post('./?body=member@kakao_amt_reg.exe', {'exec':'request', 'no':no}, function(r) {
			if(r == 'OK') {
				location.reload();
			} else {
				window.alert(r);
			}
		});
	}
}

function useTemplate(no, use_type) {
	var msg = '문자 발송시 선택한 메시지가 알림톡으로 전송되도록 설정하시겠습니까?\n이전에 설정된 다른 메시지가 있을 경우 사용해제 됩니다.';
	if(use_type == 'N') {
		msg = '문자 발송시 알림톡으로 발송되지 않도록 설정을 해제하시겠습니까?\n확인을 클릭하시면 일반 SMS로 발송됩니다.';
	}
	if(confirm(msg)) {
		$.post('./?body=member@kakao_amt_reg.exe', {'exec':'use', 'no':no, 'use_type':use_type}, function(r) {
			location.reload();
		});
	}
}

function recoverProfile()
{
    printLoading();
    setTimeout(function() {
        if (confirm('카카오 알림톡이 휴면상태입니다.\n복구하시겠습니까?') == true) {
            $.post('./?body=member@kakao_amt_reg.exe', {'exec':'recover'}, function(r) {
                if (r == 'OK') {
                    location.reload();
                } else {
                    window.alert(r);
                    history.back();
                }
            });
        } else {
            history.back();
        }
    }, 1);
}

function toggle_layer(name) {
	$('.'+name).not('.'+name).hide();
	$('.'+name).toggle();
}

chainCheckbox($('.check_all'), $('.check_sub'));
</script>