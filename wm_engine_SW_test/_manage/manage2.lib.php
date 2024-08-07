<?PHP

  	use Wing\HTTP\CurlConnection;
    use Wing\common\XlsxExcelWriter;

	if(defined("_wisa_mng2_lib_included")) return;
    define("_wisa_mng2_lib_included",true);

	function mngLoginLog($id,$err){
		global $tbl,$now, $pdo;

		$no = $pdo->row("select max(`no`) from `$tbl[mng_log]`");
		$no++;

		$pdo->query("INSERT INTO `$tbl[mng_log]` ( `no`,`member_id` , `login_result` , `log_date` , `ip` ) VALUES  ('$no','$id','$err','$now','".$_SERVER['REMOTE_ADDR']."')");
	}

	function orderMilageChg($repay_milage = false) {
		global $ext,$tbl,$engine_dir,$ono,$data,$asql,$now,$cfg,$admin, $pdo;

        // 상품 판매 횟수 처리
        $res = $pdo->iterator("select pno from {$tbl['order_product']} where ono='{$data['ono']}'");
        foreach ($res as $prd) {
            $cnt = $pdo->row("select sum(buy_ea) from {$tbl['order_product']} where stat=5 and pno='{$prd['pno']}'");
            $pdo->query("update {$tbl['product']} set hit_sales='$cnt' where no='{$prd['pno']}'");
        }
        // 세트 판매 횟수 처리
        if ($cfg['use_set_product'] == 'Y') {
            $res = $pdo->iterator("select set_pno from {$tbl['order_product']} where ono='{$data['ono']}' and set_idx>0 group by set_idx");
            foreach ($res as $prd) {
                $cnt = $pdo->row("select count(distinct set_idx) from {$tbl['order_product']} where stat=5 and set_pno='{$prd['set_pno']}'");
                $pdo->query("update {$tbl['product']} set hit_sales='$cnt' where no='{$prd['set_pno']}'");
            }
        }

        if ($cfg['milage_use'] != '1') return;

		// 적립금 지급 또는 반환
		if($data['member_no']) { // 회원이며 사용적립금 존재
			if($cfg['repay_part'] == "Y" && $data['repay_milage'] > 0) $data['total_milage'] -= $data['repay_milage']; // 부분취소/환불 적립금

			$tmember=get_info($tbl['member'], 'no', $data['member_no']);
			// 구매로 인해 발생한 적립금
			if($data['total_milage']>0) {
				if($ext=="5" && $data['milage_down']!="Y") { // 배송완료 및 적립금 미적립시 구매로 인해 발생되는 적립금 지급
					ctrlMilage("+","0",$data['total_milage'],$tmember,$data['title']);
					$milage_down="Y";
					$milage_down_date=$now;
				}
				elseif($data['milage_down']=="Y") { // 구매로 인해 발생한 적립금 반환
					if($repay_milage > 0) $data['total_milage'] = $repay_milage;
					ctrlMilage("-",12,$data['total_milage'],$tmember,$data['title']);
					$milage_down="N";
					$milage_down_date="";
				}
				$asql.=",`milage_down`='$milage_down',`milage_down_date`='$milage_down_date'";
			}

			if($cfg['first_order_milage'] > 0 && $tmember['first_order_milage'] == '') { // 첫구매 적립금
				$check_cnt = $pdo->row("select count(*) from $tbl[order] where member_id='$tmember[member_id]' and stat=5");
				if($check_cnt == 1) {
					ctrlMilage("+", 16, $cfg['first_order_milage'], $tmember);
					$pdo->query("update $tbl[member] set first_order_milage='$ono' where no='$tmember[no]'");
				}
			}

			// 구매시 사용한 적립금
			if($data['milage_prc']>0 && $data['stat'] != 11 && $ext != 11) { // 취소/환불/반품 완료이며 환급 내역이 없을 경우 구매시 사용한 적립금 환급
				if(($ext==13 || $ext==15 || $ext==17) && $data['milage_recharge']!="Y") {
					ctrlMilage('+', 8, $data['milage_prc'], $tmember, $data['title']);
					$milage_recharge="Y";
					$milage_recharge_date=$now;
				}
				elseif($data['milage_recharge']=="Y") {
					ctrlMilage('-', 12, $data['milage_prc'], $tmember, $data['title']);
					$milage_recharge="N";
					$milage_recharge_date="";
				}

				$asql.=",`milage_recharge`='$milage_recharge', `milage_recharge_date`='$milage_recharge_date'";
			}

			// 구매시 사용한 예치금
			if($data['emoney_prc'] > 0) {
				if($data['emoney_recharge']!="Y") { // 예치금 복구
					if (in_array($ext, array(13,15,17)) && !in_array($data['stat'], array(13,15,17))) {
						ctrlEmoney('+', 8, $data['emoney_prc'], $tmember, $data['title']);
						$asql.=",`emoney_recharge`='Y', `emoney_recharge_date`='$now'";
					}
				} else { // 예치금 차감
					if($data['stat'] > 11 && $ext < 10) {
						ctrlEmoney("-",12,$data['emoney_prc'], $tmember, $data['title']);
						$asql.=",`emoney_recharge`='N', `emoney_recharge_date`=''";
					}
				}
			}
		}
	}

	function reloadOrderMilage($ono) {
		global $tbl, $pdo;

		$milage = $pdo->assoc("select sum(total_milage) as mp, sum(member_milage) as mmp from $tbl[order_product] where ono='$ono' and stat!=11");
		$repay_milage = $pdo->row("select sum(total_milage) from $tbl[order_product] where ono='$ono' and stat > 11");
		$osql = " , total_milage='$milage[mp]', member_milage='$milage[mmp]', repay_milage='$repay_milage'";

		$pdo->query("update $tbl[order] set ono='$ono' $osql where ono='$ono'");
		if($repay_milage > 0 && $milage['mp'] == $repay_milage) {
			$pdo->query("update $tbl[order] set ono='$ono', milage_down='N', milage_down_date='0' where ono='$ono' and milage_down='Y'");
		}

		return $milage;
	}

	// 회원 누적 구매액 계산
	function setMemOrd($mno, $move = 0, $ono = null) {
		global $tbl,$cfg, $engine_dir, $pdo;
		if(!$mno) return;

		$tmp1 = $pdo->row("select sum(if((pay_prc-dlv_prc) > 0, pay_prc-dlv_prc, 0)) from {$tbl['order']} where member_no='$mno' and stat=5");
		$tmp2 = $pdo->row("select count(*) from {$tbl['order']} where member_no='$mno' and stat=5");
		$pdo->query("update {$tbl['member']} set total_prc='$tmp1', total_ord='$tmp2' where no='$mno'");

		// 회원 등급 처리
		if($move == 1 && $cfg['member_auto_move_use'] == 'Y' && function_exists('memberLevelUp')) {
			$pdo->query("SET @member_chg_ref='member';");
			memberLevelUp($mno);
		}

		// 첫구매 추천인/비추천인 적립금
		if($cfg['recom_first_order1'] == 'Y' || $cfg['recom_first_order2'] == 'Y') {
			if(function_exists('ctrlMilage') == false) {
				include_once $engine_dir.'/_engine/include/milage.lib.php';
			}
			$tmember = $pdo->assoc("select * from {$tbl['member']} where no='$mno'"); // 추천인 아이디 체크
			if($tmember['recom_member']) {
				$rmember = $pdo->assoc("select * from {$tbl['member']} where member_id='{$tmember['recom_member']}'"); // 피추천인 아이디 체크
				$check1 = $pdo->assoc("select * from {$tbl['milage']} where member_no='$mno' and mtype in (17, 19) order by no desc limit 1"); // 추천인 적립금 지급내역
                $check2 = $pdo->assoc("select * from {$tbl['milage']} where member_id='{$tmember['recom_member']}' and mtype in (18, 20) and (title = '{$tmember['member_id']}' or title like '%| {$tmember['member_id']}') order by no desc limit 1"); // 피추천인 적립금 지급내역
				if($tmp2 == 1) {
					$recom_first_order2 = $pdo->row("select count(*) from {$tbl['milage']} where member_id='{$tmember['recom_member']}' and mtype = 18");
					if (isset($cfg['recom_limit']) == false || strlen($cfg['recom_limit']) == 0) $cfg['recom_limit'] = 0;
					if($cfg['recom_first_order1'] == 'Y' && $check1['mtype'] != '17') ctrlMilage('+', '17', $cfg['milage_recom1'], $tmember, $rmember['member_id']);
					if($cfg['recom_first_order2'] == 'Y' && $check2['mtype'] != '18' && ($recom_first_order2 <= $cfg['recom_limit'] || $cfg['recom_limit'] == 0)) ctrlMilage('+', '18', $cfg['milage_recom2'], $rmember, $tmember['member_id']);
				} else if($tmp2 == 0) {
					if($check1['no'] > 0) {
						if($check1['amount'] > 0 && $check1['mtype'] == '17') ctrlMilage('-', '19', $check1['amount'], $tmember, $rmember['member_id']);
						if($check2['amount'] > 0 && $check2['mtype'] == '18') ctrlMilage('-', '20', $check2['amount'], $rmember, $tmember['member_id']);
					}
				}
			}
		}

		if($ono) {
			putBuyCoupon($mno, $ono, $tmp2);
		}
	}

	// (첫)구매 쿠폰 지급
	function putBuyCoupon($mno, $ono, $total_ord = 0) {
		global $tbl, $now, $pdo;

		$today = date('Y-m-d');
		$is_first_order_cpn = false; // 첫구매 쿠폰 지급 여부

		$ord = $pdo->assoc("select ono, stat, pay_prc from $tbl[order] where ono='$ono' and stat=5");
		$mem = $pdo->assoc("select * from $tbl[member] where no='$mno'");
		if($ord['ono']) {
			$res = $pdo->iterator("select * from $tbl[coupon] where down_type in ('F', 'G') and (rdate_type=1 or (rdate_type=2 and rstart_date<='$today' and rfinish_date>='$today')) order by down_type='G' desc");
            foreach ($res as $data) {
				if($data['down_type'] == 'G' && $total_ord > 1) continue;
				if($data['down_type'] == 'F' && $is_first_order_cpn == true) break;
				if($data['buy_prcs'] > 0 && $ord['pay_prc'] < $data['buy_prcs']) continue;
				if($data['buy_prce'] > 0 && $ord['pay_prc'] >= $data['buy_prce']) continue;
				if($pdo->row("select count(*) from $tbl[coupon_download] where cno='$data[no]' and ono_from='$ono'") > 0) continue; // 상태복구로 인한 중복지급 차단

				if(putCoupon($data, $mem, $ono)) {
					$content = "쿠폰 자동 지급\n- ".$data['name'];
					$pdo->query("insert into $tbl[order_memo] (admin_no, admin_id, ono, content, type, reg_date) values ('0', 'system', '$ono', '$content', '1', '$now')");
				}
				if($data['down_type'] == 'G') $is_first_order_cpn = true;
			}
		}
	}

	function autoOrderCheck($member, $ord){
		global $tbl, $now, $pdo;

		$ocard_tbl=($ord['pay_type'] == 4) ? $tbl['vbank'] : $tbl['card'];
		$ocard = $pdo->assoc("select * from `$ocard_tbl` where `wm_ono`='$ord[ono]' limit 1");
		$title=addslashes($ord['title']);

		// 적립금
		if($ord['milage_prc']>0) {
			ctrlMilage('-', 11, $ord['milage_prc'], $member, $title);
		}

		// 예치금
		if($ord['emoney_prc'] > 0) {
			ctrlEmoney('-', 11, $ord['emoney_prc'], $member, $title);
		}

		// 쿠폰
		if($ocard['cpn_no']) {
			$pdo->query("update `$tbl[coupon_download]` set `use_date`='$now',`ono`='$ord[ono]' where `no`='$ocard[cpn_no]'");
		}

		// 오프라인 쿠폰
		if($ocard['cpn_auth_code']) {
			$cpn_auth_code=trim(strtoupper($ocard['cpn_auth_code']));
			list($offcpn_no,$offcpn_code)=explode("-",$cpn_auth_code);
			$offcpn=$pdo->assoc("select * from `$tbl[coupon]` where `no`='$offcpn_no' limit 1");
			if($offcpn['no']){
				$offcpnq="insert into `$tbl[coupon_download]`(`member_no`, `member_name`, `member_id`, `cno`, `code`, `name`, `sale_prc`, `prc_limit`, `sale_limit`, `udate_type`, `ustart_date`, `ufinish_date`, `sale_type`, `use_date`, `ono`, `stype`, `is_type`, `auth_code`) values('$member[no]', '$member[name]', '$member[member_id]', '$offcpn[no]', '$offcpn[code]', '$offcpn[name]', '$offcpn[sale_prc]', '$offcpn[prc_limit]', '$offcpn[sale_limit]', '$offcpn[udate_type]', '$offcpn[ustart_date]', '$offcpn[ufinish_date]', '$offcpn[sale_type]', '$now', '$ord[ono]', '1', '$offcpn[is_type]', '$cpn_auth_code')";
				$pdo->query($offcpnq);
			}
		}

		// 주문수 추가
		$pdo->query("update `$tbl[member]` set `total_ord`=`total_ord`+1 where `no`='$member[no]'");
	}

	function dacomEsc($ord, $dlv_code="", $deli_corp = '') {
		global $cfg;
		if(!$dlv_code) return;

		$dlv_code = str_replace('-','', $dlv_code);

		$_test_code = ($cfg['card_test'] != 'N') ? ':7085' : '';
		$_dlvdate = date('YmdHi');
		$_dlvinfo = array('현대택배'=>'HD', '롯데택배'=>'HD', '한진택배'=>'HJ', '대한통운'=>'CJ', 'CJ대한통운'=>'CJ', 'CJGLS'=>'CJ', 'KGB택배'=>'KB', '로젠'=>'LG', '우체국택배'=>'PO', '옐로우캡'=>'YC', 'KG로지스'=>'FE', '천일택배'=>'CI', '일양로직스'=>'IY', '경동택배'=>'KD', '합동택배'=>'HA', 'GTX로지스택배'=>'GT',  '대신택배'=>'DS');
		$_dlvcompcode = $_dlvinfo[str_replace(' ', '', $deli_corp)];
		$_hashdata=md5($cfg['card_dacom_id'].$ord['ono'].$_dlvdate.$_dlvcompcode.$dlv_code.$cfg['card_dacom_key']);

		$gurl   = "http://pgweb.dacom.net".$_test_code."/pg/wmp/mertadmin/jsp/escrow/rcvdlvinfo.jsp";
		$param  = "mid=".$cfg['card_dacom_id'];
		$param .= "&oid=".$ord['ono'];
		$param .= "&dlvtype=03";
		$param .= "&dlvdate=".$_dlvdate;
		$param .= "&dlvcompcode=".$_dlvcompcode;
		$param .= "&dlvno=".$dlv_code;
		$param .= "&dlvworker=".$ord['addressee_name'];
		$param .= "&dlvworkertel=".$ord['addressee_cell'];
		$param .= "&hashdata=".$_hashdata;
		$param .= "&productid=";

		$result = comm($gurl, $param);
		return trim($result);
	}

	function getIntraTeam(){
		global $tbl, $pdo;
		$res = $pdo->iterator("select * from `$tbl[intra_group]` order by `name`");
		$_grp=array();
        foreach ($res as $arr) {
			$_grp[$arr['no']]['name']=$arr['name'];
			$_grp[$arr['no']]['ref']=$arr['ref'];
		}
		return $_grp;
	}

	function searchBoxBtn($frm="", $cookie=""){
		global $engine_url;
		?>
		<input type="hidden" name="detaiil_search" value="<?=(int) $_GET['detaiil_search']?>">
		<span class="btn"><a href="javascript:;" onclick="searchBoxSH(1, '<?=$frm?>');" id="search_box_btnf"></a></span>
		<span class="btn"><a href="javascript:;" onclick="searchBoxSH(2, '<?=$frm?>');" id="search_box_btns" style="display:none;"></a></span>
		<?php if($_GET['detaiil_search'] || $cookie == "Y") { ?>
			<script type="text/javascript">
			$(document).ready(function() {
				searchBoxSH(1, '<?=$frm?>');
			});
			</script>
		<?php
		}
	}

	function btn($val="확인", $action="", $w="80"){
		global $engine_url, $btn_css;
		$type="button";
		$onclick=$action;
		if($action == "[submit]"){
			$type="submit";
			$onclick="";
		}elseif(substr($action,0,4) == "[go]"){
			$onclick="location.href='".str_replace("[go]", "", $action)."';";
		}elseif(substr($action,0,10) == "[go:blank]"){
			$onclick="window.open('".str_replace("[go:blank]", "", $action)."');";
		}
		$bc="#888A96";
		$wc=$w-14;
		$btn="<div id=\"abtn\" style=\"display:inline; width:".$w."px; height:19px; padding:0; margin:1px; vertical-align: middle\">
 <table border=\"0\" width=\"".$w."px\" height=\"19px\" cellpadding=\"0\" cellspacing=\"0\">
	<tr bgcolor=\"$bc\">
		<td width=\"4\"><img src=\"".$engine_url."/_manage/image/btn/btn2_left.gif\" width=\"4\" height=\"19\"></td>
		<td width=\"".$wc."\"><input type=\"".$type."\" style=\"background-image:url(".$engine_url."/_manage/image/btn/btn2_bg.gif); width:".$wc."px; background-color:$bc;\" onclick=\"".$onclick."\" value=\"".$val."\" class=\"btns1\"></td>
		<td width=\"12\"><img src=\"".$engine_url."/_manage/image/btn/btn2_right.gif\" width=\"12\" height=\"19\"></td>
	</tr>
 </table>
 </div>";
		return $btn;
	}

	function btn2($name="modify", $_img_type=1, $add_tag="", $_imgnum=""){
		global $engine_url;

		// submit 형태
		$_btn['확인']['name']="confirm";
		$_btn['확인']['imgtype']=1;
		$_btn['저장하기']['name']="save";
		$_btn['저장하기']['imgtype']=1;
		$_btn['검색']['name']="search";
		$_btn['검색']['imgtype']=1;
		$_btn['접속하기']['name']="connect";
		$_btn['접속하기']['imgtype']=1;
		$_btn['설정완료']['name']="set";
		$_btn['설정완료']['imgtype']=1;
		$_btn['수정']['name']="modify";
		$_btn['수정']['imgnum']=2; // 이미지 주소
		$_btn['수정']['imgtype']=1;
		$_btn['추가']['name']="add";
		$_btn['추가']['imgtype']=1;
		$_btn['전송']['name']="send";
		$_btn['전송']['imgtype']=1;
		$_btn['사용하기']['name']="use";
		$_btn['사용하기']['imgtype']=1;
		$_btn['서비스신청']['name']="service_apply";
		$_btn['서비스신청']['imgtype']=1;
		$_btn['편집하기']['name']="edit";
		$_btn['편집하기']['imgtype']=1;
		// 일반이미지 A 링크로 걸림
		$_btn['초기화']['name']="back";
		$_btn['적립금지급']['name']="milage";
		$_btn['창닫기']['name']="close";
		$_btn['복구하기']['name']="recovery";
		$_btn['취소']['name']="cancel";
		$_btn['보기']['name']="view";
		$_btn['카드결제신청']['name']="card_apply";
		$_btn['SMS서비스충전']['name']="sms_recharge";
		$_btn['삭제']['name']="delete";
		$_btn['삭제']['imgnum']=2;
		$_btn['복사']['name']="copy";
		$_btn['새로고침']['name']="refresh";
		$_btn['새폴더']['name']="newfolder";
		$_btn['처리하기']['name']="proceed";
		$_btn['이동']['name']="move";
		$_btn['다음단계']['name']="next";
		$_btn['쿠폰지급']['name']="coupon";
		$_btn['회원그룹재설정']['name']="member_reset";
		$_btn['배송완료처리']['name']="delivery_finish";
		$_btn['배송시작처리']['name']="delivery_start";
		$_btn['배송지수정']['name']="address_modify";
		$_btn['인쇄하기']['name']="print";
		$_btn['리스트']['name']="list";
		$_btn['가격일괄변경']['name']="price_set";
		$_btn['적립금일괄변경']['name']="milage_set";
		$_btn['상태일괄변경']['name']="state_set";
		$_btn['적용하기']['name']="apply";
		$_btn['바로가기생성']['name']="copy_product";
		$_btn['이동하기']['name']="product_move";
		$_btn['쿠폰적용']['name']="coupon_apply";
		$_btn['글쓰기']['name']="write";
		$_btn['글수정']['name']="modify";
		$_btn['글수정']['imgnum']=3; // 이미지 주소
		$_btn['미리보기']['name']="preview";
		$_btn['이페이지보기']['name']="this_page";
		$_btn['레이아웃설정']['name']="layout";
		$_btn['백업']['name']="backup";

		$_btn[$name]['imgnum']=$_imgnum ? $_imgnum : $_btn[$name]['imgnum'];
		$_btn_type=($_img_type == 2 || !$_btn[$name]['imgtype']) ? "img" : "input type=\"image\"";
		$_img_name=$_btn[$name]['name'] ? $_btn[$name]['name'] : $name;
		$_imgnum=$_btn[$name]['imgnum'] ? $_btn[$name]['imgnum'] : 1;

		$btn="<".$_btn_type." src=\"".$engine_url."/_manage/image/btn/".$_img_name.$_imgnum.".gif\"".$add_tag." align=\"absmiddle\" alt=\"".$_alt."\">";
		unset($_btn);
		return $btn;
	}

	// 금지함수 필터링
	function funcFilter($content, $phpck="", $return=""){
		$deny_functions = array(
			"chmod","chgrp","chown","copy","delete",
			"disk_free_space","disk_total_space",
			"file_get_contents","file_fut_ contents","file","flock",
			"fopen", "fpassthru",
			"mkdir","move_ uploaded_ file","popen","readfile","rename","rmdir","symlink","touch","unlink","umask",
			"exec","passthru","proc_open","shell_exec","system",
		);
		if($phpck){
			// PHP 구문 제어
			if(preg_match("/(<\?(.*?)\?>)/is", $content)){
				if($return) return 1;
				else msg("내용에 입력 금지된 코드가 존재합니다 - 프로그램 구문 ");
			}
		}
		if(!preg_match("/(.*)<\?(PHP)?(=)?(.*?)\?>(.*)/is", $content)) return false;
		$content = preg_replace ("/(\?>(.*?)<\?(PHP)?(=)?)/is", "", $content);
		$content = preg_replace ("/(.*)<\?(PHP)?(=)?(.*?)\?>(.*)/is", "$4", $content);
		$content = trim($content);
		foreach ( $deny_functions as $val) {
			if(preg_match("/([^a-z_]?)$val\s*\(/is", $content) > 0){
				if($return) return 1;
				else msg("$val 내용에 입력 금지된 코드가 존재합니다");
			}
		}
		return false;
	}

	function inicisEsc($data, $dlv_name, $dlv_code) {
		global $root_dir, $engine_dir, $cfg, $tbl, $pdo;

        $dlv_code = getInicisDlvCode($dlv_name);

		if(class_exists('INIpay50') == false) {
			require_once $engine_dir.'/_engine/card.inicis/INIweb/libs/INILib.php';
		}
		$vbank = $pdo->assoc("select * from $tbl[vbank] where wm_ono='$data[ono]'");
		if($cfg['pg_version'] == 'INIweb') {
			$web_id = ($data['mobile'] == 'Y') ? $cfg['card_inicis_mobile_id'] : $cfg['escrow_web_id'];
		} else {
			$web_id = ($data['mobile'] == 'Y') ? $cfg['card_inicis_mobile_id'] : $cfg['escrow_mall_id'];
		}
		$iniescrow = new INIpay50;

		$iniescrow->SetField("inipayhome", $root_dir.'/_data/INIpay41');
		$iniescrow->SetField("tid", $vbank['tno']); // 거래아이디
		$iniescrow->SetField("mid", $web_id); // 상점아이디
		$iniescrow->SetField("admin", "1111"); // 키패스워드(상점아이디에 따라 변경)
		$iniescrow->SetField("type", "escrow"); 				                    // 고정 (절대 수정 불가)
		$iniescrow->SetField("escrowtype", "dlv"); 				                    // 고정 (절대 수정 불가)
		$iniescrow->SetField("dlv_ip", getenv("REMOTE_ADDR")); // 고정
		$iniescrow->SetField("debug",false); // 로그모드("true"로 설정하면 상세한 로그가 생성됨)

		$iniescrow->SetField("oid", $data['ono']);
		$iniescrow->SetField("soid","1");
		$iniescrow->SetField("dlv_date", date('Ymd', $data['date4']));
		$iniescrow->SetField("dlv_time", date('His', $data['date4']));
		$iniescrow->SetField("dlv_report", 'I');
		$iniescrow->SetField("dlv_invoice", $data['dlv_code']);
		$iniescrow->SetField("dlv_name", $dlv_name);

		$iniescrow->SetField("dlv_excode", $dlv_code);
		$iniescrow->SetField("dlv_exname", '');
		$iniescrow->SetField("dlv_charge", 'BH');

		$iniescrow->SetField("dlv_invoiceday", date('Ymd', $data['date4']));
		$iniescrow->SetField("dlv_sendname", mb_convert_encoding($data['buyer_name'], 'euc-kr', _BASE_CHARSET_));
		$iniescrow->SetField("dlv_sendpost", mb_convert_encoding($data['addressee_zip'], 'euc-kr', _BASE_CHARSET_));
		$iniescrow->SetField("dlv_sendaddr1", mb_convert_encoding($data['addressee_addr1'], 'euc-kr', _BASE_CHARSET_));
		$iniescrow->SetField("dlv_sendaddr2", mb_convert_encoding($data['addressee_addr2'], 'euc-kr', _BASE_CHARSET_));
		$iniescrow->SetField("dlv_sendtel", mb_convert_encoding($data['buyer_phone'], 'euc-kr', _BASE_CHARSET_));

		$iniescrow->SetField("dlv_recvname", mb_convert_encoding($data['addressee_name'], 'euc-kr', _BASE_CHARSET_));
		$iniescrow->SetField("dlv_recvpost", mb_convert_encoding($data['addressee_zip'], 'euc-kr', _BASE_CHARSET_));
		$iniescrow->SetField("dlv_recvaddr", mb_convert_encoding($data['addressee_addr1'], 'euc-kr', _BASE_CHARSET_).' '.mb_convert_encoding($data['addressee_addr2'], 'euc-kr', _BASE_CHARSET_));
		$iniescrow->SetField("dlv_recvtel", mb_convert_encoding($data['addressee_phone'], 'euc-kr', _BASE_CHARSET_));

		$iniescrow->SetField("dlv_goodscode", '');
		$iniescrow->SetField("dlv_goods", mb_convert_encoding($data['title'], 'euc-kr', _BASE_CHARSET_));
		$iniescrow->SetField("dlv_goodscnt", 1);
		$iniescrow->SetField("price", parsePrice($data['pay_prc']));
		$iniescrow->SetField("dlv_reserved1",$reserved1);
		$iniescrow->SetField("dlv_reserved2",$reserved2);
		$iniescrow->SetField("dlv_reserved3",$reserved3);

		$iniescrow->SetField("pgn",$pgn);

		/*********************
		 * 3. 배송 등록 요청 *
		 *********************/
		$iniescrow->startAction();

		$tid        = $iniescrow->GetResult("tid"); 					// 거래번호
		$resultCode = $iniescrow->GetResult("ResultCode");		// 결과코드 ("00"이면 지불 성공)
		$resultMsg  = $iniescrow->GetResult("ResultMsg"); 			// 결과내용 (지불결과에 대한 설명)
		$dlv_date   = $iniescrow->GetResult("DLV_Date");
		$dlv_time   = $iniescrow->GetResult("DLV_Time");

		$resultMsg  = iconv('euckr', 'utf8', $resultMsg);

		return $resultMsg;
	}

    /**
     * 이니시스 에스크로 배송등록 API
     **/
    function inicisEscAPI($ord, $dlv_name, $dlv_code)
    {
        global $cfg, $tbl, $pdo, $admin;

        $vbank = $pdo->assoc("select tno from {$tbl['vbank']} where wm_ono='{$ord['ono']}'");
        $dlv = $pdo->assoc("select name from {$tbl['delivery_url']} where no='{$ord['dlv_no']}'");

        if ($cfg['iniweb_escrow_apikey']) $apiKey = $cfg['iniweb_escrow_apikey'];
        switch($cfg['inicis_GID']) {
            case 'HOSTwisaG1' : $apiKey = 'dGXgJphqzegTL0VB'; break;
            case 'HOSTwisaG2' : $apiKey = 'BxT3k7VWQZUfC38K'; break;
            case 'HOSTwisaG3' : $apiKey = 'CHfsqkNAzisKvukV'; break;
        }
        if (!$apiKey) return false;

        $param = array(
            'type' => 'Dlv',
            'mid' => $cfg['card_web_id'],
            'clientIp' => $_SERVER['REMOTE_ADDR'],
            'timestamp' => date('YmdHis'),
            'tid' => $vbank['tno'],
            'oid' => $ord['ono'],
            'report' => 'I',
            'invoice' => $ord['dlv_code'],
            'registName' => 'admin',
            'exCode' => getInicisDlvCode($dlv['name']),
            'exName' => $dlv['name'],
            'charge' => 'SH ',
            'invoiceDay' => date('Y-m-d', $ord['date4']),
            'sendName' => $ord['buyer_name'],
            'sendPost' => ($ord['buyer_zip']) ? $ord['buyer_zip'] : $ord['addressee_zip'],
            'sendAddr1' => ($ord['buyer_addr1']) ? $ord['buyer_addr1'] : $ord['addressee_addr1'],
            'sendTel' => $ord['buyer_cell'],
            'recvName' => $ord['addressee_name'],
            'recvPost' => $ord['addressee_zip'],
            'recvAddr' => $ord['addressee_addr1'],
            'recvTel' => $ord['addressee_cell'],
            'price' => parsePrice($ord['pay_prc'])
        );

        $param['hashData'] = hash('sha512',
            $apiKey.$param['type'].$param['timestamp'].$param['clientIp'].
            $param['mid'].$param['oid'].$param['tid'].$param['price']
        );

        $curl = new CurlConnection('https://iniapi.inicis.com/api/v1/escrow', 'POST', http_build_query($param));
        $curl->exec();
        $res = json_decode($curl->getResult());

        if ($res->resultCode == '00') { // 성공
            return true;
        }

        return false;
    }

    /**
     * 이니시스 택배사 코드 매칭
     **/
    function getInicisDlvCode($dlv_name)
    {
        $_providers = array(
            'CJ대한통운' => 'korex',
            '대한통운' => 'korex',
            'CJGLS' => 'cjgls',
            '로젠' => 'kgb',
            '로젠택배' => 'kgb',
            'KGB택배' => 'kgb',
            '우체국택배' => 'EPOST',
            '한진택배' => 'hanjin',
            '현대택배' => 'hyundai',
            '롯데택배' => 'hyundai',
        );
        $code = $_providers[strtoupper($dlv_name)];
        if (!$code) $code = '9999';

        return $code;
    }

	function nicepayEsc($data, $dlv_no, $dlv_code) {
		global $tbl, $cfg, $pdo;

		if($data['mobile'] == 'Y' || $data['mobile'] == 'A') {
			$mid = $cfg['nicepay_m_mid'];
			$licenseKey = $cfg['nicepay_licenseKey'];
		} else {
			$mid = $cfg['nicepay_m_mid'];
			$licenseKey = $cfg['nicepay_m_licenseKey'];
		}
		$card_tbl = ($data['pay_type'] == 4) ? $tbl['vbank'] : $tbl['card'];
		$vbank = $pdo->assoc("select tno from $card_tbl where wm_ono='{$data['ono']}' and stat=2 order by no desc limit 1");
		$dlv = $pdo->assoc("select name from {$tbl['delivery_url']} where no='$dlv_no'");

		$param  = 'ReqType=03';
		$param .= '&MID='.$mid;
		$param .= '&TID='.$vbank['tno'];
		$param .= '&DeliveryCoNm='.stripslashes($dlv['name']);
		$param .= '&BuyerAddr='.urlencode(mb_convert_encoding($data['addressee_addr1'].' '.$data['addressee_addr2'], 'EUC-KR', _BASE_CHARSET_));
		$param .= '&InvoiceNum='.numberOnly($dlv_code);
		$param .= '&RegisterName='.관리자;
		$param .= '&ConfirmMail=2';
		$param .= '&MallIP='.$_SERVER['SERVER_ADDR'];
		$param .= '&UserIp='.$data['ip'];
		$param .= '&EncodeKey='.urlencode($licenseKey);
		$r = comm('https://webapi.nicepay.co.kr/lite/escrowProcess.jsp', $param);
		$r = explode('|', mb_convert_encoding(trim($r), _BASE_CHARSET_, 'EUC-KR'));
		$ret = array();
		foreach($r as $val) {
			list($key, $val) = explode('=', $val);
			$ret[$key] = trim($val);
		}

		return $ret;
	}

	function escDlvRegist($data, $dlv_no=null, $dlv_code=null) {
		global $cfg, $tbl, $engine_dir, $pdo;
		if(!$dlv_no) $dlv_no = $data['dlv_no'];
		if(!$dlv_code) $dlv_code = $data['dlv_code'];

		if(($data['pay_type'] != 4 && $data['pay_type'] != 17) || !$dlv_no || !$dlv_code) return false;


		$dlv = get_info($tbl['delivery_url'], "no" ,$dlv_no);
		$card = $pdo->assoc("select * from $tbl[vbank] where wm_ono='$data[ono]'");
		if(!$card) $card = $pdo->assoc("select * from $tbl[card] where wm_ono='$data[ono]'");

		switch($card['pg']) {
			case "kcp" :
				$req_tx		= $_POST['req_tx'] = "mod_escrow";
				$mod_type	= $_POST['mod_type'] = "STE1";
				$deli_numb	= $_POST['deli_numb'] = $dlv_code;
                $deli_corp	= $_POST['deli_corp'] = iconv('utf-8', 'euc-kr', $dlv['name']);
				$ono		= $_POST['ordr_idxx'] = $data['ono'];
				$tno		= $_POST['tno']		  = $card['tno'];
				$mod_desc	= $_POST['mod_desc']  = iconv('utf-8', 'euc-kr//IGNORE', '배송정보등록');
                include $engine_dir."/_engine/card.kcp/pp_ax_hub.php";
			break;
			case "dacom" :
				$result = dacomEsc($data, $dlv_code, $dlv['name']);
			break;
			case "inicis" :
                if ($cfg['iniweb_escrow_apikey'] || $cfg['inicis_GID']) {
    				$result = inicisEscAPI($data, $dlv['name'], $dlv_code);
                } else {
    				$result = inicisEsc($data, $dlv['name'], $dlv_code);
                }
			break;
			case "allat" :
				if(!$dlv_no || !$dlv_code) return false;

				include_once $engine_dir.'/_engine/card.allat/allatutil_extra.php';

				$at_shop_id = $data['mobile'] == 'Y' ? $cfg['mobile_card_partner_id'] : $cfg['card_partner_id'];
				$at_cross_key = $data['mobile'] == 'Y' ? $cfg['mobile_card_cross_key'] : $cfg['card_cross_key'];

				$dlv_name = stripslashes($dlv['name']);

				$at_enc = setValue($at_enc, "allat_shop_id", $at_shop_id);
				$at_enc = setValue($at_enc, "allat_order_no", $data['ono']);
				$at_enc = setValue($at_enc, "allat_escrow_send_no", $dlv_code);
				$at_enc = setValue($at_enc, "allat_escrow_express_nm", $dlv_name);
				$at_enc = setValue($at_enc, "allat_pay_type", 'VBANK');

				$at_data = "allat_shop_id=".$at_shop_id."&allat_enc_data=".$at_enc."&allat_cross_key=".$at_cross_key;
				$at_txt = EscrowChkReq($at_data, "SSL");

				$REPLYCD = getValue("reply_cd", $at_txt);
				$REPLYMSG = getValue("reply_msg", $at_txt);
				if($REPLYCD == '0000') return true;

				return false;
			break;
			case 'allthegate' :
				$_POST['trcode'] = 'E100';
				$_POST['pay_kind'] = '03';
				$_POST['retailer_id'] = $cfg['allthegate_StoreId'];
				$_POST['deal_time'] = substr($card['ipgm_time'], 0, 8);
				$_POST['send_no'] = $card['tno'];
				include $engine_dir.'/_engine/card.allthegate/AGS_escrow_ing.php';
				if($rSuccYn == 'y') $result = '배송등록 성공';
			break;
			case 'payco' :
				$ord = $pdo->assoc("select stat, dlv_date from $tbl[order] where ono='$data[ono]'");
				if((!$ord['dlv_date'] && ($ord['stat'] == 3 || $ord['stat'] == 4)) || $ord['stat'] == 5) {
					$_productStatus = array(
						3 => 'DELIVERY_START',
						4 => 'DELIVERY_START',
						5 => 'PURCHASE_DECISION',
					);
					$json = json_encode(array(
						'sellerKey' => $cfg['payco_sellerKey'],
						'orderNo' => $card['ordr_idxx'],
						'sellerOrderProductReferenceKey' => $data['ono'],
						'orderProductStatus' => $_productStatus[$ord['stat']]
					));
					include_once $engine_dir.'/_engine/card.payco/lib/payco_config.php';
					$GLOBALS['URL_upstatus'] = $URL_upstatus;
					include_once $engine_dir.'/_engine/card.payco/lib/payco_util.php';
					$ret = payco_upstatus(urldecode(stripslashes($json)));
					if(!$ord['dlv_date']) {
						$pdo->query("update $tbl[order] set dlv_date=unix_timestamp(now()) where ono='$data[ono]'");
					}
				}
			break;
			case 'nicepay' :
				$result = nicepayEsc($data, $dlv_no, $dlv_code);
			break;
		}
		return $result;
	}

	// 기간별 검색버튼 세트
	function setDateBunttonSet($sname, $fname, $sdata, $fdata, $use_all = false) {
		global $now;

		$set = '';
		if($use_all == true) {
			$checked = checked($GLOBALS['all_date'], 'Y');
			$set .= "<label class='p_cursor'><input type='checkbox' name='all_date' value='Y' $checked onClick='searchDate(this.form)'> 전체기간</label> ";
		}

		$set .= "<input type='text' name='$sname' value='$sdata' size='10' class='input datepicker'> ~ ";
		$set .= "<input type='text' name='$fname' value='$fdata' size='10' class='input datepicker'>";

		$date_type=array("오늘" => "-0 day", "1주일" => "-1 week", "15일" => "-15 day", "1개월" => "-1 month", "3개월" => "-3 month");
		foreach($date_type as $key => $val) {
			$_btn_class = ($val && !$GLOBALS['all_date'] && $fdata == date("Y-m-d", $now) && $sdata == date("Y-m-d", strtotime($val))) ? "blue" : "gray";
			$_sdate = $_fdate = null;
			if($val) {
				$_sdate = date("Y-m-d", strtotime($val));
				$_fdate = date("Y-m-d", $now);
			}
			$set .= " <span class='box_btn_s $_btn_class strong'><input type='button' value='$key' onclick=\"setSearchDatee(this.form, 'start_date', 'finish_date', '$_sdate', '$_fdate', '$_GET[body]');\"></span>";
		}
		$set .= "<script type='text/javascript'>searchDate($('input[name=$fname]').parents('form')[0]);</script>";

		return $set;
	}

	function orderAccountLog($account_idx, $title) {
		global $tbl, $admin, $now, $pdo;

		$account_idx = numberOnly($account_idx);
		$title = addslashes(trim($title));
		$data = $pdo->assoc("select * from $tbl[order_account] where no='$account_idx'");
		if(!$data['no']) return;

		$pdo->query("
			insert into $tbl[order_account_log]
			(account_idx, title, prd_prc, dlv_prc, fee_prc, cpn_tot, cpn_master, cpn_partner, input_prc, stat, reg_date, admin_id)
			values
			('$account_idx', '$title', '$data[prd_prc]', '$data[dlv_prc]', '$data[fee_prc]', '$data[cpn_tot]', '$data[cpn_master]', '$data[cpn_partner]', '$data[input_prc]', '$data[stat]', '$now', '$admin[admin_id]')
		");
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  창고 위치 이름 출력
	' +----------------------------------------------------------------------------------------------+*/
	function getStorageLocation($storage, $use_cache = true) {
		global $tbl, $storage_nm_cache, $pdo;

		if($use_cache == true && is_array($storage_nm_cache) == true) {
			$cate = $storage_nm_cache;
		} else {
			$cate = array();
			$w = ($use_cache == true) ? '' : " and no in ($storage[big],$storage[mid],$storage[small],$storage[depth4])";
			$res = $pdo->iterator("select no, name from $tbl[category] where ctype=9 $w");
            foreach ($res as $data) {
				$cate[$data['no']] = stripslashes($data['name']);
			}
			if($use_cache == true) {
				$storage_nm_cache = $cate;
			}
		}

		$name = $cate[$storage['big']];
		if($storage['mid'] > 0) $name .= '-'.$cate[$storage['mid']];
		if($storage['small'] > 0) $name .= '-'.$cate[$storage['small']];
		if($storage['depth4'] > 0) $name .= '-'.$cate[$storage['depth4']];

		return $name;
	}

	function getStorage($data) {
		global $tbl, $pdo;

		if($data['storage_no'] < 1) return array();
		if($data['storage']) return $data['storage'];

		$storage = $pdo->assoc("select name, big, mid, small, depth4 from $tbl[erp_storage] where no='$data[storage_no]'");
		$storage['name'] = stripslashes($storage['name']);

		return $storage;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |	상품-카테고리 정렬 데이터 생성
	' +----------------------------------------------------------------------------------------------+*/
	function createProductLink($pno, $ctype, $nbig, $nmid, $nsmall, $ndepth4, $is_multi = false) {
		global $tbl, $cfg, $_cate_colname, $pdo;

		if(!$nbig && $is_multi == false) {
			$pdo->query("delete from $tbl[product_link] where pno='$pno' and ctype='$ctype'");
			return false;
		}
		if(!$nmid) $nmid = 0;
		if(!$nsmall) $nsmall = 0;
		if(!$ndepth4) $ndepth4 = 0;

		$sdata = $pdo->assoc("select * from $tbl[product_link] where pno='$pno' and ctype='$ctype'");
		$sasql = $sqsql1 = $sqsql2 = '';
		for($i = 1; $i <= $cfg['max_cate_depth']; $i++) {
			$_ctitle = $_cate_colname[1][$i];
			$_cno = ${'n'.$_ctitle};
			if($_cno != $sdata['n'.$_ctitle]) {
				if($_cno > 0) {
					$_sort = 1;
					$pdo->query("update $tbl[product_link] set sort_{$_ctitle}=sort_{$_ctitle}+1 where n{$_ctitle}='$_cno'");
				} else {
					$_sort = 0;
				}
				$sasql .= ", sort_{$_ctitle}='$_sort'";
				${'_sort_'.$_ctitle} = $_sort;
			}
		}
		if($cfg['max_cate_depth'] >= 4) {
			$sasql  .= ", ndepth4='$ndepth4'";
			$sqsql1 .= ", ndepth4, sort_depth4";
			$sqsql2 .= ", '$ndepth4', '$_sort_depth4'";
		}
		if($sdata) { // 변경
			$pdo->query("update $tbl[product_link] set nbig='$nbig', nmid='$nmid', nsmall='$nsmall' $sasql where idx='$sdata[idx]'");
		} else {
			$pdo->query("
				insert into $tbl[product_link] (pno, ctype, nbig, nmid, nsmall, sort_big, sort_mid, sort_small $sqsql1) values
				('$pno', '$ctype', '$nbig', '$nmid', '$nsmall', '$_sort_big', '$_sort_mid', '$_sort_small' $sqsql2)
			");
		}
	}

	function chgCashReceipt($ono) {
		global $tbl, $pdo, $scfg;

		$cash = $pdo->assoc("select * from `$tbl[cash_receipt]` where `ono`='$ono'");
		if(!$cash['no'] || $scfg->comp('cash_receipt_auto', 'Y') == false) return;

		$ord = $pdo->assoc("select * from $tbl[order] where ono='$ono'");
		$amt1 = $ord['pay_prc'];

        require_once __ENGINE_DIR__.'/_engine/include/migration/cfg_cash_receipt_taxfree.inc.php';

		// 비과세상품 제외
        $taxfree_amt = 0;
		$ores = $pdo->iterator("select op.* from $tbl[order_product] op inner join $tbl[product] p on op.pno=p.no where ono='$ono' and op.stat<10 and p.tax_free='Y'");
        foreach ($ores as $odata) {
            $taxfree_amt += ($odata['total_prc']-getOrderTotalSalePrc($odata));
		}
		$amt1 = parsePrice($amt1);

		if($cash['amt1'] == $amt1 && $cash['taxfree_amt'] == $taxfree_amt && $cash['stat'] != 1) {
            return;
        }

		if($cash['stat']==2) {
			cashReceiptAuto($ord, 13);
		}
		if($amt1 > 0) {
			$amt4 = round(($amt1-$taxfree_amt)/11); // 부가세
			$amt2 = ($amt1-$amt4); // 공급가액

			$pdo->query("update `$tbl[cash_receipt]` set `stat`=1, `amt1`='$amt1', `amt2`='$amt2', amt4='$amt4', taxfree_amt='$taxfree_amt' where `ono`='$ono' limit 1");

			cashReceiptAuto($ord, $ord['stat'], 3);
		} else {
			$pdo->query("update `$tbl[cash_receipt]` set `stat`=3 where `ono`='$ono' and stat=1");
		}
	}

	function makeCategoryName($array, $ctype = 1, $spliter = ' &gt; ') {
		global $cfg, $_cate_colname, $_cname_cache, $_cname_cache_ctype;

		if(is_array($_cname_cache_ctype[$ctype]) == false) {
			$_cname_cache_ctype[$ctype] = getCategoriesCache($ctype);
			if(is_array($_cname_cache) == true)	$_cname_cache = array_merge($_cname_cache,  $_cname_cache_ctype[$ctype]);
			else $_cname_cache = $_cname_cache_ctype[$ctype];
		}

		$category_name = '';
		for($i = 1; $i <= $cfg['max_cate_depth']; $i++) {
			$val = $array[$_cate_colname[$ctype][$i]];
			if($val) {
				if($category_name) $category_name .= $spliter;
				$category_name .= $_cname_cache[$val];
			}
		}

		return $category_name;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  주문 할인 필드 중 생성된 필드만 출력
	' +----------------------------------------------------------------------------------------------+*/
	function getSaleField($separator = ',') {
		global $tbl, $_order_sales, $pdo;

		$ftmp = $pdo->assoc("select * from $tbl[order_product] limit 1");
		$fd = array();
		if(is_array($ftmp)) {
			foreach($ftmp as $key => $val) {
				$fd[] = $key;
			}
		}
		$cq = '';
		foreach($_order_sales as $key => $val) {
			if(in_array($key, $fd)) {
				$cq .= $separator."$key";
			}
		}
		return $cq;
	}

	function getAddr($target, $SI_NM = null, $SGG_NM = null, $EMD_NM = array(), $RI_NM = array()) {
		global $cfg, $_sido_mapping;

        if (!$EMD_NM) $EMD_NM = array();
        if (!$RI_NM) $RI_NM = array();

		switch($target) {
			case 'sido' :
			break;
			case 'gugun' :
				$parent_nm = $SI_NM;
			break;
			case 'dong' :
				$parent_nm = $SGG_NM;
			break;
			case 'ri' :
				$parent_nm = $EMD_NM;
			break;
		}

		// 우편번호 API 접속
		$SI_NM = array_search($SI_NM, $_sido_mapping);
		$wec = new weagleEyeClient($GLOBALS['_we'], 'etc');
		$res = $wec->call('getSubAddress', array(
			'target' => $target,
			'SI_NM' => $SI_NM,
			'SGG_NM' => $SGG_NM,
			'EMD_NM' => $EMD_NM
		));

		$res = json_decode($res);

		// 리스트 생성
		if($target == 'dong' || $target == 'ri') {
			if ($target == 'dong') $checked = (count($EMD_NM) == 0) ? 'checked' : '';
			if ($target == 'ri') $checked = (count($RI_NM) == 0) ? 'checked' : '';
			$checkbox = "<input type='checkbox' name='{$target}s[]' value='' $checked onclick=\"return setDong(this, '$target');\">";
		}
		if($target == 'gugun' && !$SGG_NM) $selected = 'selected';

		$result = '';
		if($target != 'sido') $result .= "<li class='all $selected' data-name='' data-type='plain'><label>$checkbox<strong>$parent_nm 전체</strong></label></li>";
		foreach($res as $key => $val) {
			if($target == 'sido') $val = $_sido_mapping[$val];

			$selected = (($target == 'sido' && $_sido_mapping[$SI_NM] == $val) || ($target == 'gugun' && $SGG_NM == $val) || ($target == 'dong' && in_array($val, $EMD_NM))) ? 'selected' : '';

            $checked = '';
			if ($target == 'dong' && in_array($val, $EMD_NM) == true) $checked = 'checked';
			else if ($target == 'ri' && in_array($val, $RI_NM) == true) $checked = 'checked';

			if($target == 'dong' || $target == 'ri') $checkbox = "<input type='checkbox' name='{$target}s[]' value='$val' $checked onclick=\"return setDong(this, '$target');\">";

            $type = ($checkbox) ? 'checkbox' : 'plain';
			$result .= "<li class='$selected' data-name='$val' data-type='$type'><label>$checkbox$val</label></li>";
		}

		return $result;
	}

    function getPGName() {
        global $scfg;

        switch($scfg->get('card_pg')) {
            case 'dacom' : return '토스페이먼츠';
            case 'kcp' : return 'NHN KCP';
            case 'inicis' : return 'KG 이니시스';
            case 'nicepay' : return 'NICE PAY';
            case 'allay' : return 'KG 올앳';
            case 'kspay' : return 'KSPAY';
        }
    }

    function getSubscriptionPGName() {
        global $scfg;

        switch($scfg->get('autobill_pg')) {
            case 'dacom' : return '토스페이먼츠';
            case 'kcp' : return 'NHN KCP';
            case 'inicis' : return 'KG 이니시스';
            case 'nicepay' : return 'NICE PAY';
        }
    }

	function makeColorchipCache($pno) {
		global $tbl, $cfg, $pdo;

		if(!$pno) return;

		$chips = array();
		$res = $pdo->iterator("select chip_idx from {$tbl['product_option_item']} where pno='$pno' and chip_idx > 0 and hidden!='Y' order by sort asc");
        foreach ($res as $data) {
			$chips[] = $data['chip_idx'];
		}
		$chips = implode(',', $chips);
		$pdo->query("update {$tbl['product']} set colorchip_cache='{$chips}' where no='$pno' or wm_sc='$pno'");

		return $chips;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  배송 설정을 입점사 설정으로 치환
	' +----------------------------------------------------------------------------------------------+*/
	function setPartnerDlvConfig($partner_no) {
		global $tbl, $cfg, $pdo;

		if($partner_no < 1) return $cfg;

		$tmp = $pdo->assoc("select * from $tbl[partner_delivery] where partner_no='$partner_no'");
		if(!$tmp['delivery_type']) $tmp['delivery_type'] = 1; // 배송정책 미설정시 무료배송을 기본 값으로
		$cfg['delivery_type'] = $tmp['delivery_type'];
		$cfg['delivery_fee'] = ($tmp['delivery_type'] == 3) ? parsePrice($tmp['delivery_fee']) : 0;
		$cfg['dlv_fee2'] = numberOnly($tmp['dlv_fee2'], true);
		$cfg['delivery_base'] = $tmp['delivery_base'];
		$cfg['delivery_free_limit'] = numberOnly($tmp['delivery_free_limit'], true);
		$cfg['delivery_free_milage'] = $tmp['delivery_free_milage'];
		$cfg['adddlv_type'] = $tmp['adddlv_type'];
		$cfg['free_delivery_area'] = $tmp['free_delivery_area'];

		return $tmp;
	}

    /**
     * 관리자 권한체크
     *
     * @big   string 메뉴 대분류
     * @mcode string 메뉴 소분류 코드
     **/
    function authCheck($big, $mcode)
    {
        global $tbl, $pdo, $admin;

        if ($admin['level'] < 3) return true;

        if (strpos($admin['auth'], '@'.$big) > -1) {
            $_auth_detail = $pdo->row("
                select `$big` from {$tbl['mng_auth']} where admin_no=?",
                array($admin['no'])
            );
            if (strlen($_auth_detail) == 0 || strpos($_auth_detail, $mcode) > -1) {
                return true;
            }
        }

        return false;
    }

    /**
     * 업로드 위치별 업로드 용량 제한 리턴
     **/
    function getWingUploadSize($title, $unit = false, $filetype = null)
    {
        global $up_cfg;

        include __ENGINE_DIR__.'/_config/set.upload.php';

        if (isset($up_cfg[$title]) == true) {
            $size = $up_cfg[$title]['filesize']*1024;
            if ($unit == true) {
                $size = filesizestr($size);
            }
            return $size;
        }
        return ($unit == true) ? '무제한' : 0;
    }

    /**
     * 엑셀 다운로드 라이브러리 호출 및 인스턴스 생성
     **/
    function setExcelWriter()
    {
        if (class_exists('ZipArchive')) {
            //ZipArchive 라이브러리 설치 환경이라면 설정된 excel writer 사용 (기본값 : XlsxExcelWriter)
            $ExcelWriter = new XlsxExcelWriter();
        } else {
            //XmlExcelWriter 사용
            include_once __ENGINE_DIR__.'/_engine/include/classes/ExcelWriterXML.class.php';
            include_once __ENGINE_DIR__.'/_engine/include/classes/common/XmlExcelWriter.php';
            $ExcelWriter = new XmlExcelWriter();
        }
        return $ExcelWriter;
    }

    /**
     * 개인정보 마스킹
     **/
    function strMask($str, $strlen = 3, $mark = '***') {
        $orglen = mb_strlen($str, _BASE_CHARSET_);
        if ($orglen <= $strlen) {
            $strlen -= 1;
        }

        if (preg_match('/^[^@]+@/', $str) == true) { // 이메일 형태
            $tmp = explode('@', $str);
            $tmp[0] = strMask($tmp[0], $strlen, $mark);
            return $tmp[0].'@'.$tmp[1];
        }

        $tmp_str = mb_substr($str, 0, $strlen, _BASE_CHARSET_);
        return $tmp_str.$mark;
    }

?>