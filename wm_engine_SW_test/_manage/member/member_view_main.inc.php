<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원 - CRM 종합정보
	' +----------------------------------------------------------------------------------------------+*/

	$birth=$amember[birth];

	$withdraw_info=($amember[withdraw]=='Y') ? "탈퇴 요청 회원입니다" : "탈퇴 요청 회원이 아닙니다.";
	$withdraw_btn=($amember[withdraw]=='Y') ? "탈퇴요청취소" : "탈퇴요청상태로 변경";

    if ($amember['join_ref'] == 'mng' || $amember['join_ref'] == 'mng2') $reg_type = '(수동등록된 회원입니다.)';
	else if($amember['reg_sms'] == 'Y') $reg_type = '(SMS로 인증된 회원입니다.)';
	else if($amember['reg_email'] == 'Y') $reg_type = '(이메일로 인증된 회원입니다.)';
	else if($amember['reg_email'] == 'W') $reg_type = '(이메일 인증대기중인 회원입니다.)';

	list($ry, $rm, $rd) = explode('-', date('Y-m-d', $amember['reg_date']));

	// 특별 회원그룹
	$mchecker = array();
	if(is_array($mc) == false) $mc = array();
	if(isTable($tbl['member_checker'])) {
		$mcres = $pdo->iterator("select no, name from `$tbl[member_checker]` order by name asc");
        foreach ($mcres as $mcdata) {
			$mchecker[$mcdata['no']] = stripslashes($mcdata['name']);
		}
	}

	//SNS 회원 조회
	$snsType = "";
	if(strlen(stristr($amember["login_type"],"nvr")) > 0) $snsType .= "<img src='$engine_url/_manage/image/icon/ic_conv_na.png' class='sns_icon'>";
	if(strlen(stristr($amember["login_type"], "fb")) > 0) $snsType .= "<img src='$engine_url/_manage/image/icon/ic_conv_fb.png' class='sns_icon'>";
	if(strlen(stristr($amember["login_type"],"kko")) > 0) $snsType .= "<img src='$engine_url/_manage/image/icon/ic_conv_ka.png' class='sns_icon'>";
	if(strlen(stristr($amember["login_type"],"wnd")) > 0) $snsType .= "<img src='$engine_url/_manage/image/icon/ic_conv_wm.png' class='sns_icon'>";
	if(strlen(stristr($amember["login_type"],"apple")) > 0) $snsType .= "<img src='$engine_url/_manage/image/icon/ic_conv_ap.png' class='sns_icon'>";

	$amember['mailing'] = ($amember['mailing'] == 'Y') ? '수신' : '거부';
	$amember['sms'] = ($amember['sms'] == 'Y') ? '수신' : '거부';
	if(!$amember['sms_chg_date']) $amember['sms_chg_date'] = $amember['reg_date'];
	if(!$amember['mailing_chg_date']) $amember['mailing_chg_date'] = $amember['reg_date'];

    // 회원 메모 조회 권한
    $perm_memo = ($admin['level'] == '3' && authCheck('member', 'C0245') == false) ? false : true;

    $email_style = 'text-decoration: underline;';
    if (isset($amember['email_reserve']) == true && $amember['email_reserve']) {
        $email_style = 'text-decoration: line-through;';
    }

?>
<form id="crmFrm" action="<?=$PHP_SELF?>" method="post" target="hidden<?=$now?>">
	<input type="hidden" name="body">
	<input type="hidden" name="exec">
	<input type="hidden" name="mid" value="<?=$mid?>">
	<input type="hidden" name="smode" value="<?=$smode?>">
	<input type="hidden" name="check_pno" value="<?=$amember[no]?>">
	<div class="box_title first">
		<h3 class="title">가입정보</h3>
		<span class="box_btn_s btns"><a href="?body=member@member_view.frm&smode=info&mno=<?=$mno?>&mid=<?=$mid?>">정보수정</a></span>
	</div>
	<table class="tbl_row">
		<caption class="hidden">가입정보</caption>
		<colgroup>
			<col style="width:13%;">
			<col style="width:37%;">
			<col style="width:13%;">
			<col style="width:37%;">
		</colgroup>
		<tr>
			<th scope="row">이름</th>
			<td <?=$cfg['member_join_id_email']=='Y'?'colspan="3"':''?>>
				<?=$amember['name']?>
				<?=$snsType?>
				<span class="box_btn_s icon login"><a href="#" onclick="userLogin('<?=$mno?>', '<?=$mid?>');">로그인</a></span>
                <span class="explain"><?=$reg_type?></span>
			</td>
			<? if($cfg['member_join_id_email'] != 'Y') { ?>
			<th scope="row">아이디</th>
			<td>
				<?=$amember['member_id']?>
				<?if($amember['reg_email'] == 'W'){?>
				<span class="box_btn_s"><input type="button" onclick="mailCertificate()" value="수동인증하기"></span>
				<?}?>
			</td>
			<? } ?>
		</tr>
		<tr>
			<th scope="row">회원등급</th>
			<td><?=selectArray($group,"m_group",2,"",$amember[level],"chGroup(this);")?></td>
			<th scope="row">닉네임</th>
			<td><?=$amember[nick]?></td>
		</tr>
		<tr>
			<th scope="row">생년월일</th>
			<td><?=$birth?></td>
			<th scope="row">성별/나이</th>
			<td><?=$amember[sex] ? $amember[sex] : '';?>/<?=getAge($amember[jumin], $amember[birth])?></td>
		</tr>
		<tr>
			<th scope="row">전화번호</th>
			<td><?=$amember[phone]?></td>
			<th scope="row">휴대폰번호</th>
			<td>
				<a href="javascript:;" onclick="smsSend('<?=$amember[cell]?>');" title="문자보내기"><u><?=$amember[cell]?></u></a>
				<span class="box_btn_s icon sms"><input type="button" onclick="smsSend('<?=$amember[cell]?>')" value="SMS"></span>
			</td>
		</tr>
		<tr>
			<th scope="row">주소</th>
			<td colspan="3">
				<div><?=stripslashes($amember['nations'])?></div>
				(<?=$amember[zip]?>)
				<?=$amember[addr1]." ".$amember[addr2]?>
			</td>
		</tr>
		<tr>
			<th scope="row">추천인</th>
			<td><?=$amember[recom_member]?></td>
			<th scope="row">이메일</th>
			<td>
				<a href="javascript:;" onClick="return false" style="<?=$email_style?>"><?=$amember['email']?></a>
				<span class="box_btn_s icon mail"><input type="button" onclick="smsMail('<?=$amember['email']?>')" value="메일보내기"></span>
				<?php if(($cfg['member_join_id_email'] == 'Y' && $amember['reg_email'] == 'W') || $amember['email_reserve']) { ?>
					<span class="box_btn_s"><input type="button" onclick="mailCertificate()" value="수동인증하기"></span>
				<?php } ?>
                <div><?=$amember['email_reserve']?></div>
			</td>
		</tr>
		<tr>
			<th scope="row">적립금</th>
			<td><?=number_format($amember[milage])?></td>
			<th scope="row">예치금</th>
			<td><?=number_format($amember[emoney])?></td>
		</tr>
		<tr>
			<th scope="row">총 주문내역</th>
			<td>
				<?=number_format($total_ord)?>건
				<span class="box_btn_s icon list"><a href="./?body=member@member_view.frm&smode=order&mno=<?=$mno?>&mid=<?=$mid?>">주문내역보기</a></span>
			</td>
			<th scope="row">가입일</th>
			<td><?=date("Y/m/d H:i",$amember[reg_date])?></td>
		</tr>
		<tr>
			<th scope="row">최근접속</th>
			<td><?=$amember[last_con]?></td>
			<th scope="row">가입아이피</th>
			<td><?=$amember[ip]?></td>
		</tr>
		<tr>
			<th scope="row">탈퇴요청</th>
			<td colspan="3">
				<?=$withdraw_info?>
				<span class="box_btn_s gray"><input type="button" value="<?=$withdraw_btn?>" onclick="chWithdraw('<?=$amember[withdraw]?>')"></span>
				<span class="box_btn_s icon list"><a href="./?body=member@member_view.frm&smode=withdraw&mno=<?=$mno?>&mid=<?=$mid?>">요청내역</a></span>
				<?if($amember[withdraw]=='Y') {?><span class="box_btn_s"><a href="./?body=member@member_list&withdraw=1&search_type=member_id&search_str=<?=$mid?>" target="_blank">탈퇴처리(삭제)</a></span><?}?>
			</td>
		</tr>
		<tr>
			<th>이메일 수신</th>
			<td>
				<?=$amember['mailing']?>
				(최종 수정일 : <?=date('Y-m-d H:i:s', $amember['mailing_chg_date'])?>)
			</td>
			<th>SMS 수신</th>
			<td>
				<?=$amember['sms']?>
				(최종 수정일 : <?=date('Y-m-d H:i:s', $amember['sms_chg_date'])?>)
			</td>
		</tr>
		<tr>
			<th scope="row">블랙리스트</th>
			<td colspan="3">
				<label class="p_cursor"><input type="checkbox" name="blackList" value="1" <?=checked($amember[blacklist], "1")?>> 블랙리스트로 지정</label>
				<!--
				<span class="box_btn_s icon guide"><a href="http://help.wisa.co.kr/manual?idx=1197" target="_blank">안내</a></span>
				-->
				<span class="box_btn_s icon change"><a href="./?body=member@member_view.frm&smode=blacklist&mno=<?=$mno?>&mid=<?=$mid?>">변경내역</a></span>
				<span>지정 사유 : <input type="text" name="black_reason" size="45" class="input" value="<?=$amember[black_reason]?>"></span>
				<span class="box_btn_s blue"><a href="javascript:;" onclick="chgBlackList()">확인</a></span>
			</td>
		</tr>
		<?if(count($mchecker) > 0) {?>
		<tr>
			<th scope="row">특별회원그룹</th>
			<td colspan="3">
				<ul class="list">
					<?foreach($mchecker as $key => $val) {?>
					<li><label class="p_cursor"><input type="checkbox" name="mc[]" value="<?=$key?>" <?=checked($amember['checker_'.$key], 'Y')?>> <?=$val?></label></li>
					<?}?>
				</ul>
				<div>
					<span class="box_btn_s blue"><a href="#" onclick="ckMemberChecker()">확인</a></span>
				</div>
			</td>
		</tr>
		<?}?>
	</table>
</form>
<div class="box_title">
	<h3 class="title">최근 주문 내역</h3>
</div>
<table class="tbl_col tbl_col_bottom">
	<caption class="hidden">최근 주문 내역</caption>
	<colgroup>
		<col style="width:50px">
		<col style="width:140px">
		<col>
		<col style="width:120px">
		<col style="width:120px">
		<col style="width:100px">
		<col style="width:100px">
	</colgroup>
	<thead>
		<tr>
			<th scope="col">순서</th>
			<th scope="col">주문번호</th>
			<th scope="col">주문상품</th>
			<th scope="col">주문일시</th>
			<th scope="col">결제금액</th>
			<th scope="col">결제방법</th>
			<th scope="col">상태</th>
		</tr>
	</thead>
	<tbody>
		<?
			include_once $engine_dir."/_engine/include/shop.lib.php";

			$idx = 1;
			$sql="select *, (select group_concat(concat(name,'(',buy_ea,')') separator ' / ') from $tbl[order_product] where ono = a.ono) as `title` from $tbl[order] a where `stat` != 11 and `member_no`='$mno' $id_where order by `date1` desc limit 5";
			$res = $pdo->iterator($sql);
            foreach ($res as $data) {
				$data=parseOrder($data);
				$date2=($data[date2]>0) ? date("Y/m/d h:i:s A",$data[date2]) : " -";
				$date3=($data[date3]>0) ? date("Y/m/d h:i:s A",$data[date3]) : " -";
				$date4=($data[date4]>0) ? date("Y/m/d h:i:s A",$data[date4]) : " -";
				$date5=($data[date5]>0) ? date("Y/m/d h:i:s A",$data[date5]) : " -";

				$dono=$data[ono];
				if($data['print']>0) {
					$dono="<span style=\"color:#3300cc\" onmouseover=\"showToolTip(event,'인쇄:".$data['print']."회')\" onmouseout=\"hideToolTip();\">$dono</span>";
				}
		?>
		<script type="text/javascript">helptext[<?=$idx?>]="<?=addslashes(strip_tags($data[title]))?>";</script>
		<tr>
			<td><?=$idx?></td>
			<td><a href="javascript:;" onClick="viewOrder('<?=$data[ono]?>')"><?=$dono?></a></td>
			<td class="left" onmouseover="showToolTip(event,'<?=$idx?>','1')" onmouseout="hideToolTip();">
				<a href="javascript:;" onClick="viewOrder('<?=$data[ono]?>')"><strong><?=cutStr(strip_tags($data[title]),$cut_title+20)?></strong></a>
			</td>
			<td onmouseover="showToolTip(event,'<b>주문</b> : <?=date("Y/m/d h:i:s A",$data[date1])."<br><b>입금</b> : ".$date2."<br><b>상품준비</b> : ".$date3."<br><b>배송시작</b> : ".$date4."<br><b>배송완료</b> : ".$date5?>')" onmouseout="hideToolTip();"><?=date("m/d H:i",$data[date1])?></td>
			<td onmouseover="showToolTip(event,'<b>상품가격</b> : <?=number_format($data[prd_prc])?> 원<br><b>배송비</b> : <?=number_format($data[dlv_prc])?> 원<br>')" onmouseout="hideToolTip();"><?=number_format($data[total_prc])?></td>
			<td><?=$pay_type?></td>
			<td><?=$data['stat']?></td>
		</tr>
		<?
			$idx++;
			}
		?>
	</tbody>
</table>

<?php if ($perm_memo == true) { ?>
<div class="box_title">
	<h3 class="title">메모 내역</h3>
</div>
<table class="tbl_col tbl_col_bottom">
	<caption class="hidden">메모 내역</caption>
	<colgroup>
		<col style="width:50px">
		<col>
		<col style="width:150px">
		<col style="width:150px">
	</colgroup>
	<thead style="display:none;">
		<tr>
			<th scope="col">순서</th>
			<th scope="col">내용</th>
			<th scope="col">작성자</th>
			<th scope="col">작성일</th>
		</tr>
	</thead>
	<tbody>
		<?php
			if (!isTable($tbl['order_memo'])) {
				include_once $engine_dir."/_config/tbl_schema.php";
				$pdo->query($tbl_schema[order_memo]);
			}

			$idx = 0;
			$res = $pdo->iterator("select * from `$tbl[order_memo]` where `ono`='$mid' and type=2 order by no desc limit 5");
            foreach ($res as $data) {
				$idx++;
		?>
		<tr>
			<td><?=$idx?></td>
			<td class="left"><?=cutstr(nl2br(stripslashes($data['content'])), 50)?></td>
			<td><?=$data['admin_id']?></td>
			<td title="<?=date("Y/m/d H:i",$data[reg_date])?>"><?=date("Y-m-d  H:i",$data[reg_date])?></td>
		</tr>
		<?
		}
		if($total_memo == 0) {
		?>
		<tr>
			<td colspan="4">작성된 메모가 없습니다.</td>
		</tr>
		<?}?>
	</tbody>
</table>
<?php } ?>
<?
	$limitq=" limit 3";
	include $engine_dir."/_manage/member/member_view_qna.inc.php"
?>
<?
	include $engine_dir."/_manage/member/member_view_1to1.inc.php"
?>
<div class="box_title">
	<h3 class="title">최근 게시물 작성 내역</h3>
</div>
<table class="tbl_col tbl_col_bottom">
	<caption class="hidden">최근 게시물 작성 내역</caption>
	<colgroup>
		<col style="width:50px">
		<col style="width:150px">
		<col>
		<col style="width:120px">
		<col style="width:100px">
	</colgroup>
	<thead>
		<tr>
			<th scope="col">번호</th>
			<th scope="col">게시판</th>
			<th scope="col">제목</th>
			<th scope="col">작성일시</th>
			<th scope="col">조회수</th>
		</tr>
	</thead>
	<tbody>
		<?
			$board=array();
			$res = $pdo->iterator("select `db`, `title` from `mari_config` order by `no`");
            foreach ($res as $data) {
				$board[$data[db]]=$data[title];
			}
			$res = $pdo->iterator("select `no`, `db`, `title`, `reg_date`, `hit` from `mari_board` where `member_no`='$mno' $id_where order by `no` desc $limitq");
			$idx = $pdo->row("select count(*) from `mari_board` where `member_no`='$mno' $id_where");
            foreach ($res as $data) {
				$rclass=($idx%2==0) ? "tcol2" : "tcol3";
		?>
		<tr>
			<td><?=$idx?></td>
			<td><a href="<?=$root_url?>/board/?db=<?=$data[db]?>" target="_blank"><?=$board[$data[db]]?></a></td>
			<td class="left <?=$rclass?>"><a href="<?=$root_url?>/board/?db=<?=$data[db]?>&mari_mode=view@view&no=<?=$data[no]?>" target="_blank"><?=cutStr(stripslashes($data[title]),45)?></a></td>
			<td><?=date("Y/m/d H:i", $data[reg_date])?></td>
			<td><?=$data[hit]?></td>
		</tr>
		<?
			$idx--;
			}
		?>
	</tbody>
</table>
<form name="mGroupFrm" action="<?=$PHP_SELF?>" method="post" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="member@member_update_multi.exe">
	<input type="hidden" name="exec" value="group">
	<input type="hidden" name="check_pno[]" value="<?=$amember[no]?>">
	<input type="hidden" name="m_group" value="">
	<input type="hidden" name="withdraw">
</form>

<script type="text/javascript">
	function chGroup(obj){
		if(obj.value == '<?=$amember[level]?>') return;
		if(!confirm('그룹을 변경하시겠습니까?')) {
			$(obj).val(<?=$amember[level]?>);
			return;
		}
		f=document.mGroupFrm;
		f.m_group.value=obj.value;
		f.submit();
	}

	function chWithdraw(withdraw){
		if(!confirm("회원의 상태를 변경하시겠습니까?")) return;
		f=document.mGroupFrm;
		f.exec.value="ch_withdraw";
		f.withdraw.value=withdraw;
		f.submit();
	}

	function chgBlackList(){
		var f=document.getElementById("crmFrm");
		if(f.blackList.checked==true && !checkBlank(f.black_reason, "블랙리스트 변경 사유를 입력해주세요.")) return;
		if(f.blackList.checked==true && !confirm('블랙리스트 회원으로 변경하시겠습니까?')) return;
		if(f.blackList.checked==false && !confirm('일반 회원으로 변경하시겠습니까?')) return;
		f.blackList.value=f.blackList.checked;
		f.body.value="member@member_update_multi.exe";
		f.exec.value="chg_BlackList";
		f.submit();
	}

	function mailCertificate(){
        printLoading();

		var f=document.getElementById("crmFrm");
		f.body.value="member@member_update_multi.exe";
		f.exec.value="chg_regEmail";
		f.submit();
	}

	function ckMemberChecker() {
		if(!confirm('특별회원그룹 정보를 저장하시겠습니까?')) {
			location.reload();
			return;
		}

		var args = {'mno': '<?=$mno?>', 'exec': 'mchecker'};
		$(':checkbox[name="mc[]"]').each(function() {
			args['mc['+this.value+']'] = this.checked == true ? 'Y' : 'N';
		});
		$.post('?body=member@member_update.exe', args, function(result) {
			window.alert('특별회원그룹 변경이 완료되었습니다.');
		});
	}

    function userLogin(mno, mid) {
        var target = window;
        if(opener && opener.closed == false) {
            target = opener;
        }
        target.window.open("<?=$ori_root_url?>/_manage/index.php?body=member@member_login.exe&mno="+mno+"&mid="+mid+"&ssid=<?=session_id()?>&urlfix=Y");
    }
</script>