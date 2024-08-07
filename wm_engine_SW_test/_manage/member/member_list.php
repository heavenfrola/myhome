<?PHP
	/* +----------------------------------------------------------------------------------------------+
	' |  회원 조회
	' +----------------------------------------------------------------------------------------------+*/

	// 탈퇴회원 자동삭제 처리
	deleteAuto();
	loadPlugin('manage_member_list_start');
$aa = preg_match( "/[0-9]/", $_POST['fb_pixel_id']);

print '<xmp>';
print_r($aa);
print '</xmp>';

	require_once 'member_list_search.inc.php';
	$query_string = array();
	foreach($_GET as $key => $val) {
		if(in_array($key, array('body', 'page', 'row', 'sort', 'query_string')) == true || !$val) continue;
		$query_string[$key] = $val;
	}
	$query_string = urlencode(serialize($query_string));

	include_once $engine_dir."/_manage/member/member_excel_config.php";
	foreach($mbr_excel_set as $key=>$val) {
		$xls_sets .= "<option value='$key' $sel>- $mbr_excel_set_name[$key]</option>\n";
	}

	if($body=="member@member_excel.exe") {
		return;
	}

	include $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);
	if($page <= 1) $page = 1;
	if(!$row) $row = 20;
	$block = 20;

    if ($scfg->comp('use_member_list_protect', 'Y') == false || $_GET['search'] == 'Y') {
        $NumTotalRec = $pdo->row($sql_t);
        $PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
        $PagingInstance->addQueryString(makeQueryString('page'));
        $PagingResult = $PagingInstance->result($pg_dsn);
        $sql.=$PagingResult['LimitQuery'];

        $pg_res = $PagingResult['PageLink'];
        $res = $pdo->iterator($sql);
        $idx = $NumTotalRec - ($row * ($page - 1));
    }

	$group = getGroupName();
	foreach($group as $key => $val) {
		$checked = in_array($key, $s_group) ? 'checked' : '';
		$group_seach .= "<li><label class='p_cursor'><input type='checkbox' name='s_group[]' value='$key' $checked> $val</label></li>";
	}

	$qs_without_row = '?'.preg_replace('/&row=[^&]+/', '', $_SERVER['QUERY_STRING']);

    // 엑셀 다운로드 인증 방식
    $excel_auth_str = '관리자 비밀번호';
    if ($scfg->comp('use_mexcel_protect', 'Y') == true) {
        if ($scfg->comp('mexcel_otp_method', 'sms') == true) $excel_auth_str = '관리자 휴대폰번호';
        else if ($scfg->comp('mexcel_otp_method', 'mail') == true) $excel_auth_str = '관리자 이메일주소';
    }


    // 쿠폰 발급 SMS 사용 여부
    $use_cpn_sms = $pdo->row("select use_check from {$tbl['sms_case']} where `case`='38'");

    // 수동적립금발송 SMS 사용 여부
    $use_milage_sms = $pdo->row("select use_check from {$tbl['sms_case']} where `case`='39'");

?>

<form id="prdFrm" name="prdFrm" method="get" action="./">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="withdraw" value="<?=$withdraw?>">
	<input type="hidden" name="search" value="Y">
	<input type="hidden" name="exec" value="">
	<input type="hidden" name="ext" value="">
	<input type="hidden" name="sms_deny" value="">
	<input type="hidden" name="check_pno" value="<?=$check_pno?>">
	<input type="hidden" name="sort" value="">
	<input type="hidden" name="query_string" value="">

	<div id="search">
		<div class="box_search">
			<div class="box_input">
				<div class="select_input shadow">
					<div class="select">
						<?=selectArray($_search_type,"search_type",2,"",$search_type)?>
					</div>
					<div class="area_input">
						<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" placeholder="검색어를 입력해주세요.">
					</div>
				</div>
				<div class="view">
					<div id="searchCtl" onclick="toggle_shadow()"><?searchBoxBtn("prdFrm", $_COOKIE['member_detail_search_on'])?></div>
					<label class="p_cursor always"><input type="checkbox" id="search_cookie_ck" onclick="searchBoxCookie(this, 'member_detail_search_on');" <?=checked($_COOKIE['member_detail_search_on'], "Y")?>> 항상 상세검색</label>
				</div>
			</div>
			<ul class="quick_search">
				<?
				$preset_menu = 'member';
				include_once $engine_dir."/_manage/config/quicksearch.inc.php";
				?>
			</ul>
		</div>
		<table class="tbl_search search_box_omit">
			<caption class="hidden">회원 조회</caption>
			<colgroup>
				<col style="width:150px">
				<col>
				<col style="width:150px">
				<col>
			</colgroup>
			<tr>
				<th scope="row">그룹</th>
				<td colspan="3">
					<ul class="list_common inline">
						<?=$group_seach?>
					</ul>
				</td>
			</tr>
			<?if(count($mchecker) > 0) {?>
			<tr>
				<th>특별회원그룹</th>
				<td colspan="3">
					<ul class="list_common inline">
					<?foreach($mchecker as $key => $val) {?>
						<li><label class="p_cursor"><input type="checkbox" name="mc[]" value="<?=$key?>" <?=checked(in_array($key, $mc),true)?>> <?=$val?></label></li>
					<?}?>
					</ul>
				</td>
			</tr>
			<?}?>
			<tr>
				<th scope="row">블랙리스트</th>
				<td colspan="3">
					<?=selectArray($_blacklist, "blacklist",2, "::전체::", $blacklist)?>
				</td>
			</tr>
			<?if($_GET[withdraw]) {?>
			<tr>
				<th scope="row">탈퇴요청일</th>
				<td colspan="3">
					<label class="p_cursor"><input type="checkbox" name="all_with" value="Y" <?=checked($all_with,"Y")?> onClick="searchDate(this.form)"> 전체</label>
					<input type="text" name="with1" value="<?=$with1?>" class="input datepicker" size="10"> ~ <input type="text" name="with2" value="<?=$with2?>" class="input datepicker" size="10">
				</td>
			</tr>
			<?}?>
			<tr>
				<th scope="row">가입일</th>
				<td>
					<label class="p_cursor"><input type="checkbox" name="all_date" value="Y" <?=checked($all_date,"Y")?> onClick="searchDate(this.form)"> 전체</label>
					<input type="text" name="date1" value="<?=$date1?>" class="input datepicker" size="10"> ~ <input type="text" name="date2" value="<?=$date2?>" class="input datepicker" size="10">
				</td>
				<th scope="row">최종로그인</th>
				<td>
					<input type="text" id="lastcon" name="lastcon" size="7" value="<?=$lastcon?>" class="input datepicker"> 일
					<span class="box_btn_s <?=$lastcon_btn1?>"><input type="button" value="일주일" onclick="$('#lastcon').val('<?=$lastcon1?>'); this.form.submit();"></span>
					<span class="box_btn_s <?=$lastcon_btn2?>"><input type="button" value="15일" onclick="$('#lastcon').val('<?=$lastcon2?>'); this.form.submit();"></span>
					<span class="box_btn_s <?=$lastcon_btn3?>"><input type="button" value="30일" onclick="$('#lastcon').val('<?=$lastcon3?>'); this.form.submit();" ></span>
					<span class="box_btn_s <?=$lastcon_btn4?>"><input type="button" value="3개월" onclick="$('#lastcon').val('<?=$lastcon4?>'); this.form.submit();" ></span>
					<span class="box_btn_s <?=$lastcon_btn5?>"><input type="button" value="6개월" onclick="$('#lastcon').val('<?=$lastcon5?>'); this.form.submit();" ></span>
					<span class="box_btn_s <?=$lastcon_btn6?>"><input type="button" value="1년" onclick="$('#lastcon').val('<?=$lastcon6?>'); this.form.submit();" ></span>
				</td>
			</tr>
			<tr>
				<th scope="row">주문수</th>
				<td>
					<label class="p_cursor"><input type="checkbox" name="all_ord" value="Y" <?=checked($all_ord,"Y")?> onClick="searchDate(this.form)"> 전체</label>
					<input type="text" name="ord1" value="<?=$ord1?>" class="input" size="10"> ~ <input type="text" name="ord2" value="<?=$ord2?>" class="input" size="10">
				</td>
				<th scope="row">SMS수신</th>
				<td>
					<label class="p_cursor"><input type="radio" name="sms" value="" <?=checked($sms,"")?>> 전체</label>
					<label class="p_cursor"><input type="radio" name="sms" value="Y" <?=checked($sms,"Y")?>> 허용</label>
					<label class="p_cursor"><input type="radio" name="sms" value="N" <?=checked($sms,"N")?>> 거부</label>
				</td>
			</tr>
			<tr>
				<th scope="row">구매금액</th>
				<td>
					<label class="p_cursor"><input type="checkbox" name="all_prc" value="Y" <?=checked($all_prc,"Y")?> onClick="searchDate(this.form)"> 전체</label>
					<input type="text" name="prc1" value="<?=$prc1?>" class="input" size="10"> ~ <input type="text" name="prc2" value="<?=$prc2?>" class="input" size="10">
				</td>
				<th scope="row">메일수신</th>
				<td>
					<label class="p_cursor"><input type="radio" name="mailing" value="" <?=checked($mailing,"")?>> 전체</label>
					<label class="p_cursor"><input type="radio" name="mailing" value="Y" <?=checked($mailing,"Y")?>> 허용</label>
					<label class="p_cursor"><input type="radio" name="mailing" value="N" <?=checked($mailing,"N")?>> 거부</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?=$cfg['milage_name']?></th>
				<td>
					<label class="p_cursor"><input type="checkbox" name="all_milage" value="Y" <?=checked($all_milage,"Y")?> onClick="searchDate(this.form)"> 전체</label>
					<input type="text" name="milage1" value="<?=$milage1?>" class="input" size="10"> ~ <input type="text" name="milage2" value="<?=$milage2?>" class="input" size="10">
				</td>
				<th scope="row">성별</th>
				<td>
					<label class="p_cursor"><input type="radio" name="sex" value="" <?=checked($sex,"")?>> 전체</label>
					<label class="p_cursor"><input type="radio" name="sex" value="1" <?=checked($sex,"1")?>> 남</label>
					<label class="p_cursor"><input type="radio" name="sex" value="2" <?=checked($sex,"2")?>> 여</label>
				</td>
			</tr>
			<tr>
				<th scope="row">접속수</th>
				<td>
					<label class="p_cursor"><input type="checkbox" name="all_con" value="Y" <?=checked($all_con,"Y")?> onClick="searchDate(this.form)"> 전체</label>
					<input type="text" name="con1" value="<?=$con1?>" class="input" size="10"> ~ <input type="text" name="con2" value="<?=$con2?>" class="input" size="10">
				</td>
				<th scope="row">지역</th>
				<td>
					<select name="local">
						<option value="">::전체::</option>
						<?foreach($_kr_state_code as $state_nm) {?>
						<option value="<?=$state_nm?>" <?=checked($local, $state_nm, true)?>><?=$state_nm?></option>
						<?}?>
					</select>
				</td>
			</tr>
			<tr>
				<? if(fieldExist($tbl['member'],"mobile")) {?>
				<th scope="row">가입모드 </th>
				<td>
					<label class="p_cursor"><input type="checkbox" name="mobile[]" value="N" <?=in_array("N",$mobile)?'checked':''?>> PC화면</label>
					<label class="p_cursor"><input type="checkbox" name="mobile[]" value="Y" <?=in_array("Y",$mobile)?'checked':''?>> <?=$cfg['mobile_name']?> Web</label>
					<label class="p_cursor"><input type="checkbox" name="mobile[]" value="A" <?=in_array("A",$mobile)?'checked':''?>> <?=$cfg['mobile_name']?> App</label>
				</td>
				<? } ?>
				<th scope="row">가입 방법</th>
				<td>
					<label class='p_cursor'><input type='checkbox' name='login_type[]' value='n' <?= ((is_array($_GET['login_type']) && in_array('n', $_GET['login_type']))) ? "checked" : "" ?>>쇼핑몰</label>
					<label class='p_cursor'><input type='checkbox' name='login_type[]' value='nvr' <?= ((is_array($_GET[login_type]) && in_array('nvr', $_GET[login_type]))) ? "checked" : "" ?>><img src="<?=$engine_url?>/_manage/image/icon/ic_conv_na.png" style='margin-top:-5px; vertical-align:middle;' ></label>
					<label class='p_cursor'><input type='checkbox' name='login_type[]' value='fb' <?= ((is_array($_GET[login_type]) && in_array('fb', $_GET[login_type]))) ? "checked" : "" ?>><img src="<?=$engine_url?>/_manage/image/icon/ic_conv_fb.png" style='margin-top:-5px; vertical-align:middle;' ></label>
					<label class='p_cursor'><input type='checkbox' name='login_type[]' value='kko' <?= ((is_array($_GET[login_type]) && in_array('kko', $_GET[login_type]))) ? "checked" : "" ?>><img src="<?=$engine_url?>/_manage/image/icon/ic_conv_ka.png" style='margin-top:-5px; vertical-align:middle;' ></label>
					<?if($cfg['wonder_login_use']=="Y") {?>
					<label class='p_cursor'><input type='checkbox' name='login_type[]' value='wnd' <?= ((is_array($_GET[login_type]) && in_array('wnd', $_GET[login_type]))) ? "checked" : "" ?>><img src="<?=$engine_url?>/_manage/image/icon/ic_conv_wm.png" style='margin-top:-5px; vertical-align:middle;' ></label>
					<?}?>
					<?if($cfg['apple_login_use']=="Y") {?>
					<label class='p_cursor'><input type='checkbox' name='login_type[]' value='apple' <?= ((is_array($_GET['login_type']) && in_array('apple', $_GET[login_type]))) ? "checked" : "" ?>><img src="<?=$engine_url?>/_manage/image/icon/ic_conv_ap.png" style='margin-top:-5px; vertical-align:middle;' ></label>
					<?}?>
				</td>
			</tr>
			<? if($cfg['use_whole_mem'] == "Y") {?>
			<tr>
				<th scope="row">평생회원</th>
				<td colspan="3">
					<label class="p_cursor"><input type="radio" name="whole_mem" value="" <?=checked($whole_mem,"")?>> 전체</label>
					<label class="p_cursor"><input type="radio" name="whole_mem" value="Y" <?=checked($whole_mem,"Y")?>> 동의함</label>
					<label class="p_cursor"><input type="radio" name="whole_mem" value="N" <?=checked($whole_mem,"N")?>> 동의안함</label>
				</td>
			</tr>
			<?}?>
            <tr>
                <th scope="row">사업자 회원</th>
                <td>
                    <label><input type="radio" name="buniness" value=""  <?=checked($buniness, '')?> > 전체</label>
                    <label><input type="radio" name="buniness" value="Y" <?=checked($buniness, 'Y')?>> 승인</label>
                    <label><input type="radio" name="buniness" value="N" <?=checked($buniness, 'N')?>> 미승인</label>
                </td>
                <?php if ($scfg->comp('join_14_limit', 'B') == true) { ?>
                <th scope="row">14세 미만 회원</th>
                <td>
                    <label><input type="radio" name="under14" value=""  <?=checked($under14, '')?> > 전체</label>
                    <label><input type="radio" name="under14" value="Y" <?=checked($under14, 'Y')?>> 승인</label>
                    <label><input type="radio" name="under14" value="N" <?=checked($under14, 'N')?>> 미승인</label>
                </td>
                <?php } else {?>
                <th></th>
                <td></td>
                <?php } ?>
            </tr>
			<tr>
				<th scope="row">유입경로</th>
				<td colspan="3">
					<?=selectArrayConv("conversion_s")?>
				</td>
			</tr>
			<?
				$convarr2 = selectArrayConv("conversion_s", 2);
				if($convarr2) {
			?>
			<tr>
				<th scope="row">배너광고유입</th>
				<td colspan="3">
					<?=$convarr2?>
				</td>
			</tr>
			<?}?>
		</table>
		<div class="box_bottom top_line">
			<span class="box_btn blue"><input type="submit" value="검색"></span>
			<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>'"></span>
			<span class="box_btn quicksearch"><a onclick="viewQuickSearch('prdFrm', 'member');">#단축검색등록</a></span>
		</div>
	</div>
	<!-- 검색 총합 -->
	<div class="box_title">
		<strong id="total_prd"><?=number_format($NumTotalRec)?></strong> 명의 회원이 검색되었습니다.
		<div class="btns">
            <?php if ($NumTotalRec > 0 && $admin['level'] < 3 || strchr($admin['auth'], '@auth_memberexcel') == true) { ?>
			<span class="box_btn_s icon excel"><input type="button" value="엑셀다운" onclick="xlsDown(0, event);"></span>
            <?php } ?>
		</div>
	</div>
	<!-- //검색 총합 -->

    <!-- 엑셀 저장 레이어 -->
    <div method="post" id="excelLayer" class="popup_layer" style="display:none;">
        <table class="tbl_mini">
            <tr>
                <th scope="row">엑셀양식</th>
                <td class="left">
                    <select class="xls_set">
                        <?=$xls_sets; unset($xls_sets);?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">대상회원</th>
                <td class="left">
                    <label class="p_cursor"><input type="radio" name="xls_searchtype" value="1" checked> 검색된 내역</label>
                    <label class="p_cursor"><input type="radio" name="xls_searchtype" value="2"> 선택된 내역</label>
                </td>
            </tr>
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
            <span class="box_btn_s blue"><input type="button" value="엑셀다운" onclick="xlsDown(1)"></span>
            <span class="box_btn_s gray"><input type="button" value="엑셀설정" onclick="goM('member@member_excel_config')"></span>
            <span class="box_btn_s"><input type="button" value="닫기" onclick="$('#excelLayer').toggle();"></span>
        </div>
    </div>
    <!-- //엑셀 저장 레이어 -->

	<!-- 정렬 -->
	<div class="box_sort">
		<dl class="list">
			<dt class="hidden">정렬</dt>
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
	<!-- //정렬 -->
	<!-- 검색 테이블 -->
	<table class="tbl_col">
		<caption class="hidden">회원 조회 리스트</caption>
		<thead>
			<tr>
				<th scope="col"><input type="checkbox" class="all_chkbox"></th>
				<th scope="col">번호</th>
				<th scope="col">이름</th>
				<th scope="col">아이디</th>
				<th scope="col">그룹</th>
				<?if($cfg['join_jumin_use'] == "Y"){?>
				<th scope="col">성별</th>
				<th scope="col">나이</th>
				<?}else{?>
				<?if($cfg['join_sex_use'] == "Y"){?>
				<th scope="col">성별</th>
				<?}?>
				<?if($cfg['join_birth_use'] == "Y"){?>
				<th scope="col">나이</th>
				<?}?>
				<?}?>
				<th scope="col">지역</th>
				<th scope="col">연락처</th>
				<th scope="col"><?=$cfg['milage_name']?></th>
				<th scope="col">예치금</th>
				<th scope="col">접속</th>
				<th scope="col">주문</th>
				<th scope="col">구매금액</th>
				<th scope="col">가입일</th>
				<?if($_GET['withdraw']){?>
				<th scope="col"><a href="<?=$sort2?>">탈퇴요청일 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir2?>.gif" class="arrow <?=$arrowcolor2?>"></a></th>
				<?}?>
				<th scope="col">경로</th>
				<th scope="col">SNS</th>
			</tr>
		</thead>
		<tbody>
			<?php
                if (isset($idx) == true) { foreach ($res as $data) {
					$rclass = ($idx % 2 == 0) ? "tcol2" : "tcol3";
					$_withdraw = explode(":::::", $data['withdraw_content']);
					$member_checker = getMemberChecker($data, $mchecker);

					//SNS 회원 조회
					$snsType = "";
					if(strlen(stristr($data["login_type"],"nvr")) > 0) $snsType .= "<img src='$engine_url/_manage/image/icon/ic_conv_na.png' class='sns_icon'>";
					if(strlen(stristr($data["login_type"],"fb")) > 0)  $snsType .= "<img src='$engine_url/_manage/image/icon/ic_conv_fb.png' class='sns_icon'>";
					if(strlen(stristr($data["login_type"],"kko")) > 0) $snsType .= "<img src='$engine_url/_manage/image/icon/ic_conv_ka.png' class='sns_icon'>";
					if(strlen(stristr($data["login_type"],"pyc")) > 0) $snsType .= "<img src='$engine_url/_manage/image/icon/ic_conv_pc.png' class='sns_icon'>";
					if(strlen(stristr($data["login_type"],"wnd")) > 0) $snsType .= "<img src='$engine_url/_manage/image/icon/ic_conv_wm.png' class='sns_icon'>";
					if(strlen(stristr($data["login_type"],"apple")) > 0) $snsType .= "<img src='$engine_url/_manage/image/icon/ic_conv_ap.png' class='sns_icon'>";

					$data['mobile_icon'] = ($data['mobile'] == 'Y') ? "mobile" : "";
					$data['mobile_icon'] = ($data['mobile'] == 'A') ? "app" : $data['mobile_icon'];

					if($NumTotalRec==$idx) {
						addPrivacyViewLog(array(
							'page_id' => 'member',
							'page_type' => 'list',
							'target_id' => $data['member_id'],
							'target_cnt' => $NumTotalRec
						));
					}

                    // 리스트에서 개인정보 마스킹
                    $data['member_id_v'] = $data['member_id'];
                    if ($scfg->comp('use_member_list_protect', 'Y') == true) {
                        $data['name'] = strMask($data['name'], 2, '＊');
                        $data['member_id_v'] = strMask($data['member_id'], 5, '***');
                    }

			?>
			<tr>
				<td><input type="checkbox" name="check_pno[]" id="check_pno" class="sub_chkbox" value="<?=$data['no']?>"></td>
				<td><?=$idx?></td>
				<td><a href="javascript:;" onClick="viewMember('<?=$data['no']?>','<?=$data['member_id']?>')"><b><?=$data['name']?></b></a></td>
				<td class="left"><div class="magicDIV <?=$data['mobile_icon']?>"><a href="javascript:;" onClick="viewMember('<?=$data['no']?>','<?=$data['member_id']?>')"><?=$data['member_id_v']?> <?=blackIconPrint($data['blacklist'])?></a></div></td>
				<td>
					<?=$group[$data['level']]?>
					<ul class="desc1">
						<?foreach($member_checker as $val){?><li>- <?=$val?></li><?}?>
					</ul>
				</td>
				<?if($cfg['join_jumin_use'] == "Y"){?>
				<td><?=getSex($data['jumin'])?></td>
				<td><?=getAge($data['jumin'])?></td>
				<?}else{?>
				<?if($cfg['join_sex_use'] == "Y"){?>
				<td><?=$data['sex']?></td>
				<?}?>
				<?if($cfg['join_birth_use'] == "Y"){?>
				<td><?=getAge('', $data['birth'])?></td>
				<?}?>
				<?}?>
				<td><?=cutStr($data['addr1'], $cfg['member_local_cut'], '')?></td>
				<td style="font-size: 12px">
					<?if($data['phone']){?>
					<a href="javascript:;" onmouseover="showToolTip(event,'<?=$data['name']?>님 전화번호 : <?=$data['phone']?>')" onmouseout="hideToolTip();"><img src="<?=$engine_url?>/_manage/image/icon/ic_phone.gif"></a>
					<?}?>
					<?if($data['cell']){?>
					<a href="javascript:;" onClick="smsSend('<?=$data['cell']?>')" onmouseover="showToolTip(event,'<?=$data['name']?>님 휴대폰번호 : <?=$data['cell']?> 클릭하시면 문자메세지를 보냅니다')" onmouseout="hideToolTip();"><img src="<?=$engine_url?>/_manage/image/icon/ic_sms.gif"></a>
					<?}?>
				</td>
				<td><?=number_format($data['milage'],$cfg['currency_decimal'])?></td>
				<td><?=number_format($data['emoney'],$cfg['currency_decimal'])?></td>
				<td><?=number_format($data['total_con'])?></td>
				<td><?=number_format($data['total_ord'])?></td>
				<td><?=number_format($data['total_prc'])?></td>
				<td onmouseover="showToolTip(event,'<?=date("Y/m/d <br> h:i:s A", $data['reg_date'])?>')" onmouseout="hideToolTip();"><?=date("y/m/d", $data['reg_date'])?></td>
				<?if($_GET['withdraw']) {?><td class="center number" onmouseover="showToolTip(event,'<?=date("Y/m/d h:i:s A", $_withdraw[1])?>')" onmouseout="hideToolTip();"><?=date("y/m/d",$_withdraw[1])?></td><?}?>
				<td><?=dispConversion($data['conversion'])?></td>
				<td><?=$snsType?></td>
			</tr>
			<?php
			$idx--;
			}}
			?>
		</tbody>
	</table>
    <?php if (isset($idx) == false) { ?>
    <div class="box_middle2">검색버튼을 클릭해주세요.</div>
    <?php } ?>
	<!-- //검색 테이블 -->
	<!-- 페이징 & 버튼 -->
	<div class="box_bottom">
		<?=$pg_res?>
		<?if($withdraw) {?>
		<div class="left_area">
			<span class="box_btn gray"><input type="button" value="탈퇴요청 취소" onclick="cancelWithdraw()"></span>
		</div>
		<?}?>
		<div class="right_area">
			<span class="box_btn gray"><input type="button" value="선택 삭제" onclick="tabSH(9)"></span>
		</div>
	</div>
	<!-- //페이징 & 버튼 -->
	<!-- 하단 탭 메뉴 -->
	<div id="controlTab">
		<ul class="tabs">
			<li id="ctab_1" onclick="tabSH(1)" class="selected">적립금 관리</li>
			<li id="ctab_2" onclick="tabSH(2)">예치금 관리</li>
			<li id="ctab_4" onclick="tabSH(4)">그룹 이동</li>
			<li id="ctab_5" onclick="tabSH(5)">쿠폰 지급</li>
			<li id="ctab_6" onclick="tabSH(6)">윙문자 발송</li>
			<li id="ctab_8" onclick="tabSH(8)">메일 발송</li>
			<?if(count($mchecker) > 0) {?>
			<li id="ctab_10" onclick="tabSH(10)">특별회원그룹</li>
			<?}?>
			<li id="ctab_9" onclick="tabSH(9)">선택삭제</li>
		</ul>
		<div class="context">
			<!-- 적립금 관리 -->
			<div id="edt_layer_1">
				<div class="box_middle2 left">
					<table class="tbl_mini">
						<caption>지급/반환할 적립금과 사유를 입력하세요</caption>
                        <colgroup>
                            <col style="width:80px">
                            <col>
                        </colgroup>
						<tr>
							<th scope="row">구분</th>
							<td>
								<label class="p_cursor"><input type="radio" name="mctype" id="mctype_1" value="milage" checked> 지급</label>
								<label class="p_cursor"><input type="radio" name="mctype" id="mctype_2" value="milage_minus"> 반환</label>
							</td>
						</tr>
						<tr>
							<th scope="row">사유</th>
							<td>&nbsp;&nbsp;<input type="text" name="mtitle" value="" class="input">&nbsp;&nbsp;</td>
						</tr>
						<tr>
							<th scope="row">적립금</th>
							<td>&nbsp;&nbsp;<input type="text" name="mprc" value="" class="input">&nbsp;&nbsp;</td>
						</tr>
                        <?php if ($use_milage_sms == 'Y') { ?>
                        <tr class="tr_milage_sms">
                            <th scope="row">알림</th>
                            <td class="left"><label><input type="checkbox" name="milage_sms" value="Y"> 적립금 지급 SMS 발송</label></td>
                        </tr>
                        <?php } ?>
					</table>
				</div>
				<div class="box_bottom">
					<span class="box_btn blue"><input type="button" value="확인" onclick="multiMilageMember(2);"></span>
				</div>
			</div>
			<!-- //적립금 관리 -->
			<!-- 예치금 관리 -->
			<div id="edt_layer_2" style="display:none;">
				<div class="box_middle2 left">
					<table class="tbl_mini">
						<caption>지급/반환할 예치금과 사유를 입력하세요</caption>
						<tr>
							<th scope="row">구분</th>
							<td>
								<label class="p_cursor"><input type="radio" name="ectype" id="ectype_1" value="emoney" checked> 지급</label>
								<label class="p_cursor"><input type="radio" name="ectype" id="ectype_2" value="emoney_minus"> 반환</label>
							</td>
						</tr>
						<tr>
							<th scope="row">사유</th>
							<td>&nbsp;&nbsp;<input type="text" name="etitle" value="" class="input">&nbsp;&nbsp;</td>
						</tr>
						<tr>
							<th scope="row">예치금</th>
							<td>&nbsp;&nbsp;<input type="text" name="eprc" value="" class="input">&nbsp;&nbsp;</td>
						</tr>
					</table>
				</div>
				<div class="box_bottom">
					<span class="box_btn blue"><input type="button" value="확인" onclick="multiEmoneyMember(2);"></span>
				</div>
			</div>
			<!-- //예치금 관리 -->
			<!-- 그룹 이동 -->
			<div id="edt_layer_4" style="display:none;">
				<div class="box_middle2 left">
					<?=selectArray($group,"m_group",2)?> 으로 이동
				</div>
				<div class="box_bottom">
					<span class="box_btn blue"><input type="button" value="확인" onclick="multiGroupMember(2);"></span>
				</div>
			</div>
			<!-- //그룹 이동 -->
			<!-- 쿠폰 발송 -->
			<div id="edt_layer_5" style="display:none;">
				<div class="box_middle2 left">
					<select name="cpmode">
						<option value="2">선택한 회원</option>
						<option value="3">전체 회원</option>
						<option value="4">검색된 모든 회원(<?=number_format($NumTotalRec)?>명)</option>
					</select> 에게
					<select name="cpnno">
						<?php
						$today = date("Y-m-d");
						$cpn_q = $pdo->iterator("select `no`, `name` from `$tbl[coupon]` where (`rdate_type`=1 or (`rdate_type`=2 and `rstart_date` <= '$today' and `rfinish_date` >= '$today')) and `is_type`='A'");
						$cpnNum = $cpn_q->rowCount();
						if($cpnNum){
							echo "<option value=''>:: 선택 ::</option>";
                            foreach ($cpn_q as $cpn) {
								echo "<option value='$cpn[no]'>".del_html(stripslashes($cpn['name']))."</option>";
							}
						}else echo "<option value=''>쿠폰없음</option>";
						?>
					</select> 지급
                    <?php if ($use_cpn_sms == 'Y') { ?>
                    <label><input type="checkbox" name="use_cpn_sms" value="Y" checked> 쿠폰 발급 SMS 발송</label>
                    <?php } ?>
					<ul class="list_msg left">
						<li>발급기간이 아닌쿠폰과 시리얼 쿠폰은 제외됩니다.</li>
						<li>만약 원하시는 쿠폰이 목록에 없을 경우 발급기간을 확인해보시기 바랍니다.</li>
                        <li>쿠폰 대량 발급을 통한 SMS 발송 시 시간이 오래 소요될 수 있습니다.</li>
					</ul>
					<span class="box_btn_s"><a href="./?body=promotion@coupon&is_type=A" target="_blank" class="sclink">쿠폰설정확인하기</a></span>
				</div>
				<div class="box_bottom">
					<span class="box_btn blue"><input type="button" value="확인" onclick="couponMember(2);"></span>
				</div>
			</div>
			<!-- //쿠폰 발송 -->
			<!-- sms 발송 -->
			<div id="edt_layer_6" style="display:none;">
				<div class="box_middle2 left">
					<select name="ssmode">
						<option value="2">선택한 회원</option>
						<option value="3">전체 회원</option>
						<option value="4">검색된 모든 회원(<?=number_format($NumTotalRec)?>명)</option>
					</select>
					에게 MMS/LMS/SMS 발송
					<label class="p_cursor"><input type="checkbox" name="smsblock" value="Y" checked> 수신거부 회원제외</label>
					<label class="p_cursor"><input type="checkbox" name="correct_num" value="Y" checked> 부정확한 번호제외</label>
				</div>
				<div class="box_bottom">
					<span class="box_btn blue"><input type="button" value="확인" onclick="multiSMS();"></span>
				</div>
			</div>
			<!-- //sms 발송 -->
			<!-- 메일 발송 -->
			<div id="edt_layer_8" style="display:none;">
				<div class="box_middle2 left">
					<select name="mmode">
						<option value="2">선택한 회원</option>
						<option value="3">전체 회원</option>
						<option value="4">검색된 모든 회원(<?=number_format($NumTotalRec)?>명)</option>
					</select> 에게
					<input type="hidden" name="mtype" value="2">
					메일 발송
					<label class="p_cursor"><input type="checkbox" name="mailblock" value="Y" checked> 수신거부회원제외</label>
				</div>
				<div class="box_bottom">
					<span class="box_btn blue"><input type="button" value="확인" onclick="multiMail();"></span>
				</div>
			</div>
			<!-- //메일 발송 -->
			<!-- 선택삭제 -->
			<div id="edt_layer_9" style="display:none;">
				<div class="box_middle2 left">
					<ul>
						<li><label class="p_cursor"><input type="checkbox" name="del_option1" value="1"> 상품평</label></li>
						<li><label class="p_cursor"><input type="checkbox" name="del_option2" value="1"> 상품 질문</label></li>
						<li><label class="p_cursor"><input type="checkbox" name="del_option3" value="1"> 주문 내역 <span class="explain">(삭제시 매출 분석등이 변동됩니다)</span></label></li>
						<li><label class="p_cursor"><input type="checkbox" name="del_option4" value="1" checked> 1:1 상담 내역</label></li>
						<li><label class="p_cursor"><input type="checkbox" name="del_option1234" value="1" checked disabled> 위시리스트, 적립금/예치금 내역(필수)</label></li>
					</ul>
					<ul class="list_msg">
						<li>선택한 회원과 관련된 자료도 함께 삭제됩니다</li>
						<li>같이 삭제할 자료를 선택해 주세요</li>
					</ul>
				</div>
				<div class="box_bottom">
					<span class="box_btn blue"><input type="button" value="확인" onclick="multiDeleteMember(2);"></span>
				</div>
			</div>
			<!-- //선택삭제 -->
			<div id="edt_layer_10" style="display:none;">
				<div class="box_middle2 left">
					<table class="tbl_mini">
						<tr>
							<th>대상</th>
							<td>
								<select name="cmode">
									<option value="2">선택한 회원</option>
									<option value="3">전체 회원</option>
									<option value="4">검색된 모든 회원(<?=number_format($NumTotalRec)?>명)</option>
								</select> 을
								<select name="mchecker">
								<?foreach($mchecker as $key => $val) {?>
									<option value="<?=$key?>"><?=$val?></option>
								<?}?>
								</select> 그룹
							</td>
						</tr>
						<tr>
							<th>구분</th>
							<td>
								<label class="p_cursor"><input type="radio" name="cvalue" value="Y" checked> 지정</label>
								<label class="p_cursor"><input type="radio" name="cvalue" value="N"> 해제</label>
							</td>
						</tr>
					</table>
				</div>
				<div class="box_bottom">
					<span class="box_btn blue"><input type="button" value="확인" onclick="multiMemberChecker();"></span>
				</div>
			</div>
		</div>
	</div>
	<!-- //하단 탭 메뉴 -->
	<input type="hidden" name="msg_where" value="">
</form>

<script type="text/javascript">
var f=document.prdFrm;
var mw='<?=addslashes($w)?>';
var emoney_use='<?=$cfg[emoney_use]?>';
var emoney_update='1';
var tmp_lyr='';
var query_string = "<?=$query_string?>";
function multiDeleteMember(tp){

	if(!checkCB(f.check_pno,"삭제할 회원을 선택해주세요.")) return;
	if (tp==1)
	{
		layerSH('delDiv');
	}
	else
	{
		if (!confirm('정말로 삭제하시겠습니까?\n\n 삭제된 자료는 복구될 수 없습니다.')) return;
		f.exec.value='delete';
		f.body.value='member@member_update_multi.exe';
		f.target=hid_frame;
		f.method='post';
		f.query_string.value = query_string;
		f.submit();

		f.query_string.value = '';
	}
}

function multiSMS(tp){
	if(tp==1){
		layerSH('smsDiv');
		return;
	}else{
		if (f.ssmode.value==2)
		{
			if(!checkCB(f.check_pno,"문자를 전송할 회원을 선택해주세요.")) return;
		}
		if (f.ssmode.value==3){
			if(!confirm("정말로 전체 회원에게 문자를 발송하시겠습니까?")) return;
		}
		if(f.smsblock.checked) f.sms_deny.value="Y";
		else f.sms_deny.value="N";

		window.open('','wm_sms','top=10,left=200,width=920,height=650,status=no,toolbars=no,scrollbars=yes');
		var old_body=f.body.value;
		f.body.value='member@sms_sender.frm';
		f.target='wm_sms';
		f.method='post';
		f.query_string.value = query_string;
		f.msg_where.value=mw;
		f.submit();

		f.body.value=old_body;
		f.query_string.value = '';
		f.target='';
	}
}

function multiGroupMember(tp){

	if(!checkCB(f.check_pno,"이동할 회원을 선택해주세요.")) return;
	if (tp==1)
	{
		layerSH('groupDiv');
	}
	else
	{
		if (!confirm('정말로 이동하시겠습니까?')) return;
		f.exec.value='group';
		f.body.value='member@member_update_multi.exe';
		f.query_string.value = query_string;
		f.target=hid_frame;
		f.method='post';
		f.submit();

		f.query_string.value = '';
	}
}

function multiMemberChecker() {
	if(f.cmode.value == '2' && !checkCB(f.check_pno,"이동할 회원을 입력해주세요.")) return;
	if(!confirm('설정을 적용하시겠습니까?')) return;

	var old_body = f.body.value;
	var old_action = f.action;

	f.exec.value = 'mchecker';
	f.body.value = 'member@member_update_multi.exe';
	f.query_string.value = query_string;
	f.target = hid_frame;
	f.method = 'post';
	f.msg_where.value = mw;
	f.submit();

	f.body.value = old_body;
	f.query_string.value = '';
	f.action = old_action;
	f.method = 'get';
	f.target = '';
}

function multiMilageMember(tp){

	if(!checkCB(f.check_pno,"적립금을 지급/반환할 회원을 입력해주세요.")) return;
	if (tp==1)
	{
		layerSH('mileDiv');
	}
	else
	{
		if (!checkBlank(f.mtitle,"사유를 입력해주세요.")) return;
		if (!checkBlank(f.mprc,"적립 금액을 숫자로 입력해주세요.")) return;
		if (!confirm('적립금을 지급/반환하시겠습니까?')) return;
		if(f.mctype[0].checked) f.exec.value=f.mctype[0].value;
		else f.exec.value=f.mctype[1].value;
		f.body.value='member@member_update_multi.exe';
		f.query_string.value = query_string;
		f.target=hid_frame;
		f.method='post';
		f.submit();

		f.query_string.value = '';
	}
}

function multiEmoneyMember(tp){
	if (emoney_update!='1' || emoney_use!='Y')
	{
		if (confirm('예치금을 사용하고 계시지 않습니다.\n설정을 변경하시겠습니까?')) {
			document.location='./?body=config@emoney';
		}
		return;
	}

	if(!checkCB(f.check_pno,"예치금을 지급/반환할 회원을 선택해주세요.")) return;
	if (tp==1)
	{
		layerSH('emoneyDiv');
	}
	else
	{
		if (!checkBlank(f.etitle,"지급 사유를 입력해주세요.")) return;
		if (!checkBlank(f.eprc,"지급 금액을 숫자로 입력해주세요.")) return;
		if (!confirm('예치금을 지급/반환하시겠습니까?')) return;
		if(f.ectype[0].checked) f.exec.value=f.ectype[0].value;
		else f.exec.value=f.ectype[1].value;
		f.body.value='member@member_update_multi.exe';
		f.query_string.value = query_string;
		f.target=hid_frame;
		f.method='post';
		f.submit();

		f.query_string.value = '';
	}
}

function multiMail(tp){
	if (tp) {
		layerSH('mailDiv');
		return;
	}
	if (f.mmode.value==2)
	{
		if(!checkCB(f.check_pno,"메일을 전송할 회원을 선택해주세요.")) return;
	}
	if (f.mmode.value==3){
		if(!confirm("정말로 전체 회원에게 메일을 발송하시겠습니까?")) return;
	}
	window.open('','sendMailW','top=10,left=10,width=750,height=100,status=no,toolbars=no,scrollbars=yes');
	var old_body=f.body.value;
	var old_action=f.action;
	f.body.value='member@mail_send';
	f.query_string.value = query_string;
	f.action='./pop.php';
	f.target='sendMailW';
	f.method='post';
	f.msg_where.value=mw;
	f.submit();

	f.method='get';
	f.body.value=old_body;
	f.query_string.value = '';
	f.action=old_action;
	f.msg_where.value='';
	f.target='';
}

function couponMember(tp){
	if (tp==1) {
		layerSH('couponDiv');
	} else {
		<?
		if($cpnNum < 1){
		?>
		alert("지급가능한 쿠폰이 존재하지 않습니다.");
		return;
		<?
		}else{
		?>
		if (f.cpmode.value==2)
		{
			if(!checkCB(f.check_pno,"쿠폰 지급할 회원을 선택해주세요.")) return;
		}
		if(!checkSel(f.cpnno, "지급할 쿠폰을 선택해 주세요.")) return;
		if (!confirm('해당 회원들에게 쿠폰을 지급하시겠습니까?')) return;
		f.exec.value='coupon';
		f.body.value='member@member_update_multi.exe';
		f.query_string.value = query_string;
		f.target=hid_frame;
		f.msg_where.value=mw;
		f.method='post';
		f.submit();

		f.query_string.value = '';
		<?
		}
		?>
	}
}

function xlsDown(s, ev){
	if(s == 1){
		if (!checkBlank(f.xls_down, '<?=$excel_auth_str?>를 입력해주세요.')) return;
        if (!checkBlank(f.xls_reason, '다운로드 사유를 입력해주세요.')) return;

		var url='./?body=member@member_excel.exe<?=$xls_query?>&xls_reason='+encodeURIComponent(f.xls_reason.value);

        if (f.xls_searchtype.value == '2') {
            var checked = $(':checked[name="check_pno[]"]');
            if (checked.length == 0) {
                window.alert('다운로드할 회원을 선택해주세요.');
                return false;
            }

            var check_no = '';
            checked.each(function() {
                if (check_no) check_no += ',';
                check_no += this.value;
            });
            url += '&check_pno='+check_no;
        }
		window.frames[hid_frame].location.href=url+'&xls_set_temp='+$('.xls_set').val()+'&xls_down='+encodeURIComponent(f.xls_down.value);
		f.xls_down.value='';
	}else{
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
}

$('#excelLayer').find('input').on('keydown', function(ev) {
	if(ev.keyCode == 13) {
		xlsDown(1);
		return false;
	}
});

function layerSH(layer_name){
	 if(tmp_lyr != layer_name && tmp_lyr != ''){
		 if(document.getElementById(tmp_lyr).style.display == 'block') layTgl2(tmp_lyr);
	 }
	 tmp_lyr=layer_name;
	 layTgl(document.getElementById(layer_name));
}

function searchDate(f) {
	<?if($_GET[withdraw]==1){?>
		var fields = new Array('date', 'ord', 'prc', 'milage', 'con', 'with');
	<?}else{?>
		var fields = new Array('date', 'ord', 'prc', 'milage', 'con');
	<?}?>
	for(var idx in fields) {
		var chk = f.elements['all_'+fields[idx]];
		var date1 = f.elements[fields[idx]+'1'];
		var date2 = f.elements[fields[idx]+'2'];

		if(chk.checked == true) {
			date1.disabled = true;
			date2.disabled = true;
			date1.style.backgroundColor='#eee';
			date2.style.backgroundColor='#eee';
		} else {
			date1.disabled = false;
			date2.disabled = false;
			date1.style.backgroundColor='';
			date2.style.backgroundColor='';
		}
	}
}

searchDate(document.getElementById('prdFrm'));

function cancelWithdraw() {
	if(!checkCB(f.check_pno,"탈퇴요청 취소할 회원을 선택해주세요.")) return;
	if(!confirm("선택한 회원의 탈퇴요청을 취소하시겠습니까?")) return;
	f.body.value='member@member_update_multi.exe';
	f.exec.value='cancel_withdraw';
	f.query_string.value = query_string;
	f.target=hid_frame;
	f.method='post';
	f.submit();

	f.query_string.value = '';
}

<?
	$hyphen = ($search_type == "jumin") ? "" : "-";
	if ($search_type == "birth" || $search_type == "jumin"){
?>
$('[name=search_str]').datepicker({
	monthNamesShort: ['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
	dayNamesMin: ['일','월','화','수','목','금','토'],
	weekHeader: 'Wk',
	dateFormat: 'mm<?=$hyphen?>dd',
	autoSize: false,
	changeMonth: true,
	showButtonPanel: true,
	currentText: '오늘 <?=date("Y-m-d", $now)?>',
	closeText: '닫기',
	beforeShow: function (input, inst) {
		inst.dpDiv.addClass('BirthdayDatePicker');
	},
	onClose: function(dateText, inst){
		inst.dpDiv.removeClass('BirthdayDatePicker');
	}
});
<?}?>

$('[name=search_type]').change(function(){
	if ($(this).val() == 'birth' || $(this).val() == 'jumin') {
		$('[name=search_str]').datepicker({
			monthNamesShort: ['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
			dayNamesMin: ['일','월','화','수','목','금','토'],
			weekHeader: 'Wk',
			dateFormat: 'mm<?=$hyphen?>dd',
			autoSize: false,
			changeMonth: true,
			showButtonPanel: true,
			currentText: '오늘 <?=date("Y-m-d", $now)?>',
			closeText: '닫기',
			beforeShow: function (input, inst) {
				inst.dpDiv.addClass('BirthdayDatePicker');
			},
			onClose: function(dateText, inst){
				inst.dpDiv.removeClass('BirthdayDatePicker');
			}
		});
	}
	else $('[name=search_str]').datepicker('destroy');
});

$(':radio[name=mctype]').on('change', function() {
    if (this.value == 'milage_minus') $('.tr_milage_sms').hide();
    else $('.tr_milage_sms').show();
});

new chainCheckbox(
	$('.all_chkbox'),
	$('.sub_chkbox')
)
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