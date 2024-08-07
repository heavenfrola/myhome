<style type="text/css" title="">
body {background:#e8e8e8;}
</style>
<?PHP

	$_smode['main']='CRM 종합정보';
	$_smode['info']='개인정보';
	$_smode['order']='주문내역';
	$_smode['milage']='적립금내역';
	$_smode['emoney']='예치금내역';
	$_smode['cp_list']='쿠폰 발급내역';
	$_smode['qna']='상품Q&A';
	$_smode['1to1']='1:1상담 내역';
	$_smode['review']='상품후기';
	$_smode['cart']='장바구니';
	$_smode['wishlist']='위시리스트';
	$_smode['memo']='회원메모';
	$_smode['level']='회원그룹 변경내역';
	$_smode['log']='접속로그';
	$_smode['blacklist']='블랙리스트 변경내역';
	$_smode['dooson']='두손 연동데이터';

    $_sauth = array(
        'order' => array('big' => 'order', 'mcode' => 'C0021'),
        'milage' => array('big' => 'member', 'mcode' => 'C0045'),
        'emoney' => array('big' => 'member', 'mcode' => 'C0046'),
        'cp_list' => array('big' => 'promotion', 'mcode' => 'C0148'),
        'qna'  => array('big' => 'member', 'mcode' => 'C0038'),
        '1to1'  => array('big' => 'member', 'mcode' => 'C0041'),
        'review'  => array('big' => 'member', 'mcode' => 'C0039'),
        'cart'  => array('big' => 'log', 'mcode' => 'C0140'),
        'wishlist'  => array('big' => 'log', 'mcode' => 'C0139'),
        'memo'  => array('big' => 'member', 'mcode' => 'C0245'),
        'log'  => array('big' => 'log', 'mcode' => 'C0131')
    );

	$smode = $_GET['smode'];
    $sauth = $_sauth[$smode];
	$mno = numberOnly($_GET['mno']);
	$mid = addslashes($_GET['mid']);

	if(!$smode) {
		$def_smode=$_COOKIE['def_smode'];
		if(!$def_smode) $def_smode="main";
		$smode=$def_smode;
	}
	if($smode == "cp_list") $inc=$engine_dir."/_manage/promotion/coupon_down_list.php"; // 2006-12-21 - 쿠폰발급내역 - Han
	else $inc=$engine_dir."/_manage/member/member_view_".$smode.".inc.php";
	if(!is_file($inc)) {
		exit("구성파일이 존재하지 않습니다");
	}

	$_vmode = array("새창", "현재창");
	if (!$def_vmode) $def_vmode = 0;

	//2011-03-30 새창열리는 방식 - Jung :)
	$_cmode = array("한개의 창만 사용","여러개의 새창 사용");
	if(!$cmode) {
		$def_cmode=$_COOKIE['def_cmode'];
		if(!$def_cmode) $def_cmode=0;
		$cmode=$def_cmode;
	}

	if(!$_GET['mno'] && $_GET['mid']) {//크리마 요청사항
		checkBlank($_GET['mid'],"회원번호를 입력해주세요.");
		$amember=get_info($tbl[member],"member_id",$_GET['mid']);
		$mno = $amember['no'];
	}else {
		checkBlank($_GET['mno'],"회원번호를 입력해주세요.");
		$amember=get_info($tbl[member],"no",$_GET['mno']);
	}

	if(!$amember[no]) msg("존재하지 않는 회원입니다","close","");
	if($mid) { // 삭제회원의 경우 id 및 no 비교 2007-02-15 - Jin
		if($mid!=$amember[member_id]) {
			 msg("삭제된 회원입니다","close","");
		}
		$id_where=" and `member_id`='$amember[member_id]'";
	} else {
		$mid = $amember['member_id'];
	}

	if($cfg[use_biz_member]=="Y") {
		$abiz=get_info($tbl[biz_member],"ref",$amember[no]);
	}


	if($amember[last_con]) {
		$amember[last_con]=date("Y/m/d H:i",$amember[last_con]);
	}
	else {
		$amember[last_con]="접속안함";
	}

	$total_ord=$pdo->row("select count(*) from `$tbl[order]` where `member_no`='$mno' $id_where and stat not in (11, 31, 32)");
	$total_cs=$pdo->row("select count(*) from `$tbl[cs]` where `member_no`='$mno' $id_where");
	if($use_pack['print']!="Y"){
		$total_cp=$pdo->row("select count(*) from `$tbl[coupon_download]` a inner join wm_coupon b  on a.cno = b.no where a.`is_type`='A' and a.`member_no`='$mno' $id_where");
	}
	$total_review=$pdo->row("select count(*) from `$tbl[review]` where `member_no`='$mno' $id_where");
	$total_qna=$pdo->row("select count(*) from `$tbl[qna]` where `member_no`='$mno' $id_where");
	$total_memo=$pdo->row("select count(*) from `$tbl[order_memo]` where `ono`='$mid' and type=2");
	$total_chg = $pdo->row("select count(*) from {$tbl['member_level_log']} where member_no='$mno'");
	$total_wishlist=$pdo->row("select count(*) from `$tbl[wish]` w inner join `$tbl[product]` p on w.`pno`= p.`no`  where w.`member_no`='$mno' and p.`stat` != '4'");
	$total_cart=$pdo->row("select count(*) from `$tbl[cart]` c inner join `$tbl[product]` p on c.`pno`= p.`no` where `member_no`='$mno' and p.`stat` != '4'");

	// 회원 그룹 2006-12-01
	$group=getGroupName();

    if ($smode != 'info') {
        addPrivacyViewLog(array(
            'page_id' => 'member',
            'page_type' => '상세('.$smode.')',
            'target_id' => $amember['member_id'],
            'target_cnt' => 1
        ));
    }

	include_once $engine_dir."/_manage/main/main_box.php";

	$selected[$smode] = "selected";

?>
<div id="pop_crm" class="crm_member">
	<div id="header">
		<h1><img src="<?=$engine_url?>/_manage/image/crm/title_crm_member.png" alt="wisa 회원정보"></h1>
		<div class="tab">
			<div class="tab_order">
				<a href="./?body=member@member_view.frm&smode=order&mno=<?=$mno?>&mid=<?=$mid?>"><img src="<?=$engine_url?>/_manage/image/crm/tab_order.png" alt="주문서"></a>
			</div>
			<div class="tab_member">
				<a href="./?body=member@member_view.frm&smode=main&mno=<?=$mno?>&mid=<?=$mid?>"><img src="<?=$engine_url?>/_manage/image/crm/tab_member_on.png" alt="회원정보"></a>
				<form name="" method="post" action="" target="" onSubmit="return" class="search search_member">
					<div class="search_input">
						<input type="text" name="" value="" class="input_search" placeholder="이름, 아이디, 전화번호로 회원 검색이 가능합니다!" onkeyup="autoComplete('member', this)"><input type="image" src="<?=$engine_url?>/_manage/image/crm/btn_search.gif" class="btn_search">
					</div>
					<div id="auto_complete" class="auto auto2">
					</div>
				</form>
			</div>
		</div>
		<a href="javascript:;" onClick="layTgl2('setDiv');" class="btn_setup">설정</a>
		<!-- 설정 새창 -->
		<div id="setDiv" class="setDiv" style="display:none;">
			<table class="tbl_mini">
				<tr>
					<th scope="row">창이 열릴때 기본 메뉴</th>
					<td class="left"><?=selectArray($_smode,"def_mode",2,"",$def_smode,"setConfig('def_smode',this.value);location.reload();")?></td>
				</tr>
				<tr>
					<th scope="row">CRM 정보 링크 위치</th>
					<td class="left"><?=selectArray($_vmode,"vmode", "0", "", $def_vmode,"setConfig('def_vmode',this.value);location.reload();");?></td>
				</tr>
				<tr>
					<th scope="row">CRM창 새창설정</th>
					<td class="left"><?=selectArray($_cmode,"cmode", 2, "", $def_cmode,"setConfig('def_cmode',this.value);location.reload();");?></td>
				</tr>
			</table>
			<div class="pop_bottom"><span class="box_btn_s gray"><input type="button" value="닫기" onClick="layTgl2('setDiv');"></span></div>
		</div>
		<!-- //설정 새창 -->
	</div>
	<?if($ref_front){ //외부에서 볼 경우?>
	<div class="msg_login">
		현재 쇼핑몰 관리자로 로그인중입니다.<br><span class="box_btn blue"><a href="/_manage/" target="_blank">관리자페이지 바로가기</a></span>
	</div>
	<?}?>
	<div id="container">
		<div class="snb">
			<div class="area_scroll">
				<div class="profile">
					<p class="name"><?=$amember['name']?> (<?=$amember['member_id']?>) <?=blackIconPrint($amember['blacklist'])?></p>
					<p class="email"><?=$amember['email']?></p>
					<div class="send">
						<span class="box_btn_s icon sms"><a onclick="smsSend('<?=$amember['cell']?>');">sms</a></span>
					</div>
				</div>
				<ul class="menu">
					<li><a href="./?body=member@member_view.frm&smode=main&mno=<?=$mno?>&mid=<?=$mid?>" class="main <?=$selected['main']?>"><?=$_smode['main']?></a></li>
					<li><a href="./?body=member@member_view.frm&smode=info&mno=<?=$mno?>&mid=<?=$mid?>" class="info <?=$selected['info']?>"><?=$_smode['info']?></a></li>
					<li><a href="./?body=member@member_view.frm&smode=order&mno=<?=$mno?>&mid=<?=$mid?>" class="order <?=$selected['order']?>"><?=$_smode['order']?> <span><?=number_format($total_ord)?></span></a></li>
					<li><a href="./?body=member@member_view.frm&smode=milage&mno=<?=$mno?>&mid=<?=$mid?>" class="milage <?=$selected['milage']?>"><?=$cfg[milage_name]?>내역<span><?=number_format($amember[milage],$cfg['currency_decimal'])?></span></a></li>
					<li><a href="./?body=member@member_view.frm&smode=emoney&mno=<?=$mno?>&mid=<?=$mid?>" class="emoney <?=$selected['emoney']?>"><?=$_smode['emoney']?> <span><?=number_format($amember[emoney],$cfg['currency_decimal'])?></span></a></li>
					<?if($use_pack['print']!="Y"){?>
					<li><a href="./?body=member@member_view.frm&smode=cp_list&mno=<?=$mno?>&mid=<?=$mid?>" class="cp_list <?=$selected['cp_list']?>"><?=$_smode['cp_list']?> <span><?=number_format($total_cp)?></span></a></li>
					<?}?>
					<li><a href="./?body=member@member_view.frm&smode=qna&mno=<?=$mno?>&mid=<?=$mid?>" class="qna <?=$selected['qna']?>"><?=$_smode['qna']?> <span><?=number_format($total_qna)?></span></a></li>
					<li>
						<a href="./?body=member@member_view.frm&smode=1to1&mno=<?=$mno?>&mid=<?=$mid?>" class="counsel <?=$selected['1to1']?>"><?=$_smode['1to1']?> <span><?=number_format($total_cs)?></span></a>
						<?if($amember[withdraw]=="Y"){?>
						<ul class="sideSmall">
							<li><a href="./?body=member@member_view.frm&smode=withdraw&mno=<?=$mno?>&mid=<?=$mid?>">탈퇴 요청 회원입니다</a></li>
						</ul>
						<?}?>
					</li>
					<li><a href="./?body=member@member_view.frm&smode=review&mno=<?=$mno?>&mid=<?=$mid?>" class="review <?=$selected['review']?>"><?=$_smode['review']?> <span><?=number_format($total_review)?></span></a></li>
					<li><a href="./?body=member@member_view.frm&smode=cart&mno=<?=$mno?>&mid=<?=$mid?>" class="cart <?=$selected['cart']?>"><?=$_smode['cart']?> <span><?=number_format($total_cart)?></span></a></li>
					<li><a href="./?body=member@member_view.frm&smode=wishlist&mno=<?=$mno?>&mid=<?=$mid?>" class="wishlist <?=$selected['wishlist']?>"><?=$_smode['wishlist']?> <span><?=number_format($total_wishlist)?></span></a></li>
					<li><a href="./?body=member@member_view.frm&smode=memo&mno=<?=$mno?>&mid=<?=$mid?>" class="memo <?=$selected['memo']?>"><?=$_smode['memo']?> <span><?=number_format($total_memo)?></span></a></li>
					<li><a href="./?body=member@member_view.frm&smode=level&mno=<?=$mno?>&mid=<?=$mid?>" class="level <?=$selected['level']?>"><?=$_smode['level']?> <span><?=number_format($total_chg)?></span></a></li>
					<li><a href="./?body=member@member_view.frm&smode=log&mno=<?=$mno?>&mid=<?=$mid?>" class="log <?=$selected['log']?>"><?=$_smode['log']?></a></li>
					<?if(is_object($erpListener) && $cfg['erp_interface_name'] == 'dooson') {?>
					<li>
						<a href="./?body=member@member_view.frm&smode=dooson&MENU=0&mno=<?=$mno?>&mid=<?=$mid?>" class="dooson <?=$selected['dooson']?>">두손 연동데이터</a>
						<ul>
							<li><a href="./?body=member@member_view.frm&smode=dooson&MENU=0&mno=<?=$mno?>&mid=<?=$mid?>">구매내역</a></li>
							<li><a href="./?body=member@member_view.frm&smode=dooson&MENU=3&mno=<?=$mno?>&mid=<?=$mid?>">쿠폰사용</a></li>
							<li><a href="./?body=member@member_view.frm&smode=dooson&MENU=4&mno=<?=$mno?>&mid=<?=$mid?>">수선</a></li>
							<li><a href="./?body=member@member_view.frm&smode=dooson&MENU=8&mno=<?=$mno?>&mid=<?=$mid?>">재고</a></li>
							<li><a href="./?body=member@member_view.frm&smode=dooson&MENU=9&mno=<?=$mno?>&mid=<?=$mid?>">묶음배송</a></li>
						</ul>
					</li>
					<?}?>
				</ul>
			</div>
		</div>
		<div id="content">
			<div class="content_box">
                <?php
                if (is_array($sauth) == true && authCheck($sauth['big'], $sauth['mcode']) == false) {
                    echo "
                    <div class='msg_topbar warning' style='margin-top:10px; border:1px solid #c9c9c9;'>
                        메뉴 접근 권한이 없습니다.
                    </div>
                    ";
                } else {
                ?>
				<div class="box_title first">
					<h2 class="title"><?=$_smode[$smode]?></h2>
					<?php if ($selected['milage']=="selected") { ?>
						<span class="box_btn_s btns"><a href="javascript:layTgl2('mileDiv')"><?=$cfg[milage_name]?> 지급/반환</a></span>
					<?php } elseif ($selected['emoney']=="selected" && $cfg['emoney_use'] == "Y") {?>
						<span class="box_btn_s btns"><a href="javascript:layTgl2('emoneyDiv')">예치금 지급/반환</a></span>
					<?php } ?>
				</div>
				<?php include $inc; ?>
                <?php } ?>
			</div>
			<div class="aside">
				<div id="mng_memo_area">
					<?php include 'member_memo_list.exe.php'; ?>
				</div>
                <?php if ($smode != 'memo') { ?>
                <div style="display: none">
                    <?php
                        $memo_type = 2;
                        $pno = $mid;
                        require $engine_dir.'/_manage/product/product_memo_list_in.exe.php';
                    ?>
                </div>
                <?php } ?>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	var isCRM = <?=$def_vmode?>;
	window.onload=function (){
		selfResize();
	}

	function putMilage(f,t){
		if (t=='2') ttl="예치금";
		else ttl="적립금";

		if (f.exec2[0].checked==true) e='지급';
		else e='반환'
		if (!checkBlank(f.mtitle,e+" 사유를 입력해주세요.")) return;
		if (!checkBlank(f.mprc,e+" 금액을 숫자로 입력해주세요.")) return;
		if (!confirm(ttl+'을 '+e+'하시겠습니까?')) return;
		f.submit();
	}

	this.focus();
</script>

<form id="smsFrm" method="post">
	<input type="hidden" name="body" value="member@sms_sender.frm">
	<input type="hidden" name="sms_deny" value="N">
	<input type="hidden" name="ssmode" value="4">
	<input type="hidden" name="msg_where" value=" and `no`=<?=$mno?>">
</form>