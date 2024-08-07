<?php
	/* +----------------------------------------------------------------------------------------------+
	' |  적립금 내역
	' +----------------------------------------------------------------------------------------------+*/

	$_ctype[1]="+";
	$_ctype[2]="-";
	$type = numberOnly($_REQUEST['type']);
	$ctype=$_ctype[$type];
	if($ctype) $w2 = " and `ctype`='$ctype'";

	$milage_title[11] .= ' (-)';
	$milage_title[12] .= ' (-)';
	$milage_title[0] .= ' (+)';
	$milage_title[1] .= ' (+)';
	$milage_title[2] .= ' (+)';
	$mtype = $_GET['mtype'];
	$all_date = $_GET['all_date'];
	if($mtype!="") $w = " and `mtype`='$mtype'";

	$_search_type['member_name']='이름';
	$_search_type['member_id']='아이디';
	$_search_type['title']='적요';

	$start_date = $_GET['start_date'];
	$finish_date = $_GET['finish_date'];
	if(!$start_date || !$finish_date) {
		$start_date = date('Y-m-d', strtotime('-1 months'));
		$finish_date = date('Y-m-d');
	}
	if(!$all_date) {
		$w.=" and FROM_UNIXTIME(`reg_date`, '%Y-%m-%d') >= '$start_date'";
		$w.=" and FROM_UNIXTIME(`reg_date`, '%Y-%m-%d') <= '$finish_date'";
	}

	$search_str = addslashes(trim($_GET['search_str']));
	$search_type = addslashes(trim($_GET['search_type']));
	if($_search_type[$search_type] && $search_str!="") {
		$w.=" and `$search_type` like '%$search_str%'";
	}

	$xls_query=$QueryString;
	foreach($_GET as $key=>$val) {
		if($key!="page" && !is_array($val)) $add_QueryString="&".$key."=".$val;

		if($add_QueryString) {
			$QueryString.=$add_QueryString;
			if($key!="body") {
				$xls_query.=$add_QueryString;
			}
		}
	}

	$sql="select * from `$tbl[milage]` where 1 $w $w2 order by `no` desc";

	if($body=='member@milage_excel.exe') return;

	$xls_query=preg_replace("/&?(body|pgCode)=($body|$pgCode)/","",$_SERVER['QUERY_STRING']);
	$xls_query=preg_replace("/&?(exec|page|row)=[^&]+/","",$xls_query);

	include $engine_dir."/_engine/include/paging.php";

	$list_tab_qry = makeQueryString(true, 'page', 'type', 'rstat');
	$qs_without_row = makeQueryString(true, 'row');

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);
	if($page<=1) $page=1;
	if(!$row) $row=20;
	$block=20;
	if(!$QueryString) $QueryString="&body=".$_GET['body'];

	$NumTotalRec = $pdo->row("select count(*) from `$tbl[milage]` where 1 $w $w2");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult['LimitQuery'];

	$pg_res=$PagingResult['PageLink'];
	$res = $pdo->iterator($sql);
	if(!$admin['level']==1) {
		echo $sql;
	}
	$idx=$NumTotalRec-($row*($page-1));

	$group=getGroupName();

	include_once $engine_dir.'/_engine/include/milage.lib.php';

	// 상태별 통계
	$_tabcnt = array();
	$_tmpres = $pdo->iterator("select ctype, count(*) as cnt from $tbl[milage] where 1 $w group by ctype");
    foreach ($_tmpres as $_tmp) {
		$_rstat = ($_tmp['ctype'] == '+') ? '1' : '2';
		$_tabcnt[$_rstat] = $_tmp['cnt'];
		$_tabcnt['total'] += $_tmp['cnt'];
	}
	${'list_tab_active'.$type} = 'class="active"';

    // 엑셀 다운로드 인증 방식
    $excel_auth_str = '관리자 비밀번호';
    if ($scfg->comp('use_mexcel_protect', 'Y') == true) {
        if ($scfg->comp('mexcel_otp_method', 'sms') == true) $excel_auth_str = '관리자 휴대폰번호';
        else if ($scfg->comp('mexcel_otp_method', 'mail') == true) $excel_auth_str = '관리자 이메일주소';
    }

?>
<!-- 검색 폼 -->
<form name="mnseFrm" method="get" action="./" id="search">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="type" value="<?=$type?>">
	<div class="box_title first">
		<h2 class="title">적립금 내역</h2>
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
			<caption class="hidden">적립금 내역 검색</caption>
			<colgroup>
				<col style="width:12%;">
				<col style="width:38%;">
			</colgroup>
			<tr>
				<th scope="row">지급/사용일</th>
				<td>
					<label class="p_cursor"><input type="checkbox" name="all_date" value="Y" <?=checked($all_date,"Y")?> onClick="searchDate(this.form)"> 전체 기간</label>
					<input type="text" name="start_date" value="<?=$start_date?>" size="10" class="input datepicker"> ~ <input type="text" name="finish_date" value="<?=$finish_date?>" size="10" class="input datepicker">
					<script type="text/javascript">
					searchDate(document.mnseFrm);
					</script>
				</td>
				<th scope="row">사유</th>
				<td colspan="3"><?=selectArray($milage_title, 'mtype', false, '::전체::', $mtype)?></td>
			</tr>
		</table>
		<div class="box_bottom top_line">
			<span class="box_btn blue"><input type="submit" value="검색"></span>
			<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>&type=<?=$type?>'"></span>
		</div>
	</div>
</form>
<!-- 검색 폼 -->
<!-- 검색 총합 -->
<div class="box_tab">
	<ul>
		<li><a href="<?=$list_tab_qry?>" <?=$list_tab_active?>>전체<span><?=number_format($_tabcnt['total'])?></span></a></li>
		<li><a href="<?=$list_tab_qry?>&type=1" <?=$list_tab_active1?>>지급<span><?=number_format($_tabcnt[1])?></span></a></li>
		<li><a href="<?=$list_tab_qry?>&type=2" <?=$list_tab_active2?>>사용<span><?=number_format($_tabcnt[2])?></span></a></li>
	</ul>
    <span class="box_btn_s btns icon excel"><input type="button" value="엑셀다운" onclick="xlsDown(event);"></span>
</div>
<div class="box_sort">
    <dl class="list">
        <dt class="hidden">출력갯수</dt>
        <dd>
            <select name="row" onchange="location.href='<?=$qs_without_row?>&row='+this.value">
                <option value="20" <?=checked($row,20,1)?>>20</option>
                <option value="30" <?=checked($row,30,1)?>>30</option>
                <option value="50" <?=checked($row,50,1)?>>50</option>
                <option value="70" <?=checked($row,70,1)?>>70</option>
                <option value="100" <?=checked($row,100,1)?>>100</option>
                <option value="500" <?=checked($row,500,1)?>>500</option>
                <option value="1000" <?=checked($row,1000,1)?>>1000</option>
            </select>
        </dd>
    </dl>
</div>

<!-- 엑셀 저장 레이어 -->
<form method="post" id="excelLayer" class="popup_layer" style="display:none;" action="?body=member@milage_excel.exe&<?=$xls_query?>" target="hidden<?=$now?>">
    <table class="tbl_mini">
        <tr>
            <th scope="row"><?=$excel_auth_str?></th>
            <td class="left">
                <input type="password" name="xls_down" class="input" autocomplete="false">
            </td>
        </tr>
    </table>
    <div class="btn_bottom">
        <span class="box_btn_s blue"><input type="submit" value="엑셀다운"></span>
        <span class="box_btn_s"><input type="button" value="닫기" onclick="$('#excelLayer').toggle();"></span>
    </div>
</form>
<!-- //엑셀 저장 레이어 -->

<!-- //검색 총합 -->
<form name="mnFrm" method="post" action="./" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="member@money_list.exe">
	<input type="hidden" name="exec" value="delete">
	<input type="hidden" name="tbn" value="milage">
	<!-- 검색 테이블 -->
	<table class="tbl_col">
		<caption class="hidden">적립금 지급 내역 리스트</caption>
		<colgroup>
			<col style="width:50px">
			<col style="width:50px">
			<col style="width:100px">
			<col>
			<col style="width:100px">
			<col>
			<col style="width:80px">
			<col style="width:80px">
			<col style="width:100px">
			<col style="width:100px">
			<col style="width:80px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><input type="checkbox" onclick="checkAll(document.mnFrm.mno,this.checked)"></th>
				<th scope="col">번호</th>
				<th scope="col">이름</th>
				<th scope="col">아이디</th>
				<th scope="col">구분</th>
				<th scope="col">적요</th>
				<th scope="col">적립금</th>
				<th scope="col">회원소계</th>
				<th scope="col">날짜</th>
				<th scope="col">만료예정일</th>
				<th scope="col">삭제</th>
			</tr>
		</thead>
		<tbody>
			<?php
                foreach ($res as $data) {
					$rclass=($idx%2==0) ? "tcol2" : "tcol3";
					if($data['expire'] == 'Y') $data['title'] = "<s>$data[title]</s>";

                    // 리스트에서 개인정보 마스킹
                    $data['member_id_v'] = $data['member_id'];
                    if ($scfg->comp('use_member_list_protect', 'Y') == true) {
                        $data['member_name'] = strMask($data['member_name'], 2, '＊');
                        $data['member_id_v'] = strMask($data['member_id'], 5, '***');
                    }

					if ($NumTotalRec == $idx) {
						addPrivacyViewLog(array(
							'page_id' => 'milage',
							'page_type' => 'list',
							'target_id' => $data['member_id'],
							'target_cnt' => $NumTotalRec
						));
					}

			?>
			<tr class="">
				<td><input type="checkbox" name="mno[]" id="mno" class="list_check" value="<?=$data['no']?>"></td>
				<td><?=$idx?></td>
				<td><a href="javascript:;" onClick="viewMember('<?=$data['member_no']?>','<?=$data['member_id']?>')"><b><?=$data['member_name']?></b></a></td>
				<td><a href="javascript:;" onClick="viewMember('<?=$data['member_no']?>','<?=$data['member_id']?>')"><?=$data['member_id_v']?></a></td>
				<td><?=$milage_title[$data['mtype']]?></td>
				<td class="left"><?=stripslashes($data['title'])?></td>
				<td><?=$data['ctype']?><?=number_format($data['amount'],$cfg['currency_decimal'])?></td>
				<td><?=number_format($data['member_milage'],$cfg['currency_decimal'])?></td>
				<td title="<?=date("Y/m/d H:i:s",$data['reg_date'])?>"><?=date("Y/m/d",$data['reg_date'])?></td>
				<?php if ($data['ctype'] == '+') { ?>
				<td><?=($data['expire_date'] > 0) ? date('Y/m/d', $data['expire_date']) : '무제한'?></td>
				<?php } else { ?>
				<td>-</td>
				<?php } ?>
				<td title="내역을 삭제합니다">
					<span class="box_btn_s gray"><input type="button" value="삭제" onclick="delList('<?=$data['no']?>');"></span>
				</td>
			</tr>
			<?php
				$idx--;
			}
			?>
		</tbody>
	</table>
	<!-- //검색 테이블 -->
	<!-- 페이징 & 버튼 -->
	<div class="box_bottom"><?=$pg_res?></div>
	<!-- //페이징 & 버튼 -->
</form>

<form name="money_frm" action="./" method="post" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="member@money_list.exe">
	<input type="hidden" name="exec" value="delete">
	<input type="hidden" name="tbn" value="milage">
	<input type="hidden" name="no">
</form>

<script type="text/javascript">
	function delList(no){
		if(!confirm("\n해당 내역을 삭제하시겠습니까? \n\n실제로는 적립금액의 변동이 없으며 내역만 삭제됩니다    ")) return;
		f=document.money_frm;
		f.no.value=no;
		f.submit();
	}

    function xlsDown(ev)
    {
        var ev = window.event ? window.event : ev;
        var layer = document.getElementById('excelLayer');

        if (layer.style.display == 'block') {
            layer.style.display = 'none';
        } else {
            layer.style.display = 'block';
            layer.style.position = 'absolute';
            layer.style.top = ($(document).scrollTop()+ev.clientY+25)+'px';
            layer.style.right = '72px';
        }
    }
</script>