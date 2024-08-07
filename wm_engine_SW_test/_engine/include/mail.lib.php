<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  메일발송 라이브러리
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/ext.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";
	include_once $engine_dir."/_engine/include/shop_detail.lib.php";

	$email_checked = explode('@', preg_replace('/^@|@$/', '', $cfg['email_checked']));

	if(!defined('_mail_')) {
		define('_mail_',true);

		$_mail_menu_arr = array(
            1 => '회원가입', 2 => '주문내역확인', 3 => '상품배송', 4 => '배송완료', 9 => '문의글 답변',
            13 => '회원가입인증', 14 => '이메일정보수정인증', 12 => '휴면회원 사전안내', 15 => '수신동의여부확인', 6 => '기타안내',
            16 => '비밀번호수정', 17 => '적립금소멸(정보성)', 18 => '적립금소멸(광고성)', 19 => '인증번호 발송', 21 => '광고성정보 변경여부 안내',
            22 => '개인정보 이용내역', 23 => '주문내역확인(입점사)', 24 => '생일쿠폰 발행'
        );

		function genMailContent($mail_case,$only_content="",$file_location='') {
			global $root_dir,$root_url,$cfg,$member,$_mstr,$_mail_from_file,$engine_dir;

			if($mail_case == 10) $mail_case = 6;
			if(!$cfg['mail_lang']) $cfg['mail_lang'] = 'kor';
			$_mail_from_file="";
			$code="mail_case_msg".$mail_case;
			if($cfg['mail_lang']) $code .= "_".$cfg['mail_lang'];

			$get_content=getWMDefault(array($code));
			$file=@stripslashes($get_content[$code]);

			if(!$file){
				if($file_location == 'engine'){ //재귀 호출시 engine에 있는 파일 선 체크
					$temp=$engine_dir."/_engine/skin_module/default/MODULE/mail_".$cfg['mail_lang'].".wsm";
					if(!is_file($temp)) $temp=$root_dir."/_template/mail/mail_".$cfg['mail_lang'].".php";
				}else{
					$temp=$root_dir."/_template/mail/mail_".$cfg['mail_lang'].".php";
					if(!is_file($temp)) $temp=$engine_dir."/_engine/skin_module/default/MODULE/mail_".$cfg['mail_lang'].".wsm";
				}
				if(!is_file($temp)) $temp=$root_dir."/_template/mail/mail.php";
				if(!is_file($temp)) return;

				$file=trim(implode("", file($temp)));
				$_mail_from_file=1;
			}
			if(!$file) return;

			$_mstr['br_title']=$cfg['br_title'];
			$_mstr['root_url']=$root_url;
			$_mstr['title_num']="0".$mail_case;
			if(!$_mstr['bottom']) $_mstr['bottom']="
				".stripslashes($cfg['company_name'])." | ".__lang_email_product_owner." : $cfg[company_owner] | Tel : $cfg[company_phone]<br>
				$cfg[company_addr1] $cfg[company_addr2]<br>
				".__lang_email_product_registration_num." : $cfg[company_biz_num] | ".__lang_email_product_cummerce_vendor." : $cfg[company_online_num]<br><br>
				Copyright(C) ".stripslashes($cfg['company_name'])." All right reserved.
			";

			foreach($_mstr as $key=>$val) {
				if($only_content && !strchr("br_title@root_url@title_num@bottom",$key)) continue;
				$file=replaceKey($file,$key,$val);
			}

			if($_mail_from_file){
				for($ii=1; $ii<=24; $ii++) {
					$f1="{case_".$ii."_start}";
					$f2="{case_".$ii."_finish}";
					if($ii==$mail_case) {
						if(strpos($file,$f1) === false || strpos($file,$f2) === false){ //템플릿 파일은 있는데 내용이 없으면 재귀
							if(!$file_location) $rechk_file = genMailContent($mail_case,$only_content,'engine'); //내용이 아예 빠져있는경우 대비 재귀는 한번만
							return $rechk_file;
						}
						$file=str_replace($f1,"",$file);
						$file=str_replace($f2,"",$file);
					}
					else {
						$file=removeSel($file,$f1,$f2);
					}
				}
			}

			return $file;
		}

		function replaceKey($file,$key,$val) {
			$file=str_replace("{".$key."}",$val,$file);
			return $file;
		}

		function sendMailContent($mail_case,$member_name,$to_mail,$from="") {
			global $mail_title, $cfg, $root_url, $email_checked, $_mstr, $amount, $expire_date;

            if (is_array($email_checked) == false) {
                $email_checked = explode('@', preg_replace('/^@|@$/', '', $cfg['email_checked']));
            }

			if(!in_array($mail_case, $email_checked) && !in_array($mail_case, array(5, 6, 7, 8, 9, 12, 13, 14, 15, 16, 17, 18, 19, 21, 22, 23, 24))) {
				return;
			}

			if(!trim($to_mail)) return;

			$content=genMailContent($mail_case);

			if(($mail_case == '13' || $mail_case == '14' || $mail_case == '16') && !$content) return;

			$title=replaceKey($mail_title[$mail_case],"쇼핑몰이름",stripslashes($cfg['company_mall_name']));
			$title=replaceKey($title,"회원이름",$member_name);
			if($mail_case == 20) $title = '(광고)'.$title;

			$domain = preg_replace("/https?:\/\/((www|m)\.)?/","",$root_url);
			if(!$from) $from="send@".$domain;

			if($cfg['use_mail_server'] == 'Y') {
				$title = '=?UTF-8?B?'.base64_encode($title).'?=';
				return mail($to_mail, $title, $content, "From: {$from}\r\nMIME-Version: 1.0\r\nContent-type: text/html; charset=utf-8");
			}

			$wec = new weagleEyeClient($GLOBALS['_we'], 'mailutf8');

			$args = $GLOBALS['_we'];
			$args['version'] = 'wing';
			$args['mail_case'] = $mail_case;
			$args['to_mail'] = $to_mail;
			$args['from'] = $from;
			$from_name = urlencode($cfg['company_mall_name']);
			$args['from_name'] = $from_name;
			$args['subject'] = urlencode($title);
			$args['content'] = urlencode($content);
			$args['charset'] = _BASE_CHARSET_;
			$wec->call('send', $args);

			if($wec->result == 'OK') return true;
			else return false;
		}

	}

	$mail_title = array();
	$tmp = $pdo->iterator("select code, value from {$tbl['default']} where code like 'email_title_$cfg[mail_lang]_%'");
    foreach ($tmp as $tmpdata) {
		$tmp_data['no'] = preg_replace('/.*_/', '', $tmpdata['code']);
		$mail_title[$tmp_data['no']] = stripslashes($tmpdata['value']);
	}

		$kor = array(
		"1" => "[{쇼핑몰이름}] 회원가입",
		"2" => "[{쇼핑몰이름}] 주문내용확인",
		"3" => "[{쇼핑몰이름}] 상품배송",
		"4" => "[{쇼핑몰이름}] 배송완료",
		"5" => "[{쇼핑몰이름}] {회원이름}님, 문의하신 비밀번호입니다",
		"7" => "[{쇼핑몰이름}] %s",
		"8" => "[{쇼핑몰이름} 문의] %s",
		"9" => "[{쇼핑몰이름}] 문의글 답변",
		"10" => "[{쇼핑몰이름}] {회원이름}님의 %s 게시물이 작성되었습니다.",
		"12" => "[{쇼핑몰이름}] 휴면회원 사전안내",
		"13" => "[{쇼핑몰이름}] 회원가입 인증",
		"14" => "[{쇼핑몰이름}] 이메일 정보수정 인증",
		"15" => "[{쇼핑몰이름}] 광고성정보 수신동의 안내",
		"16" => "[{쇼핑몰이름}] 비밀번호 변경",
		"17" => "[{쇼핑몰이름}] 적립금 소멸",
		"18" => "(광고) [{쇼핑몰이름}] 적립금 소멸",
		"19" => "[{쇼핑몰이름}] 인증번호 발송",
		"21" => "[{쇼핑몰이름}] 광고성정보 수신동의 변경 안내",
		"22" => "[{쇼핑몰이름}] 개인정보 이용내역 안내",
		"23" => "[{쇼핑몰이름}] 주문내용확인",
		"24" => "[{쇼핑몰이름}] 생일쿠폰 발행"
	);

	$eng = array(
		"1" => "[{쇼핑몰이름}] Join Us",
		"2" => "[{쇼핑몰이름}] Order History",
		"3" => "[{쇼핑몰이름}] Delivery",
		"4" => "[{쇼핑몰이름}] Delivery completed",
		"5" => "[{쇼핑몰이름}] {회원이름}, this is your password.",
		"7" => "[{쇼핑몰이름}] %s",
		"8" => "[{쇼핑몰이름} Inquiry] %s",
		"9" => "[{쇼핑몰이름}] Reply message information",
		"10" => "[{쇼핑몰이름}] {회원이름} has posted on %s.",
		"12" => "[{쇼핑몰이름}] Dormant member.",
		"13" => "[{쇼핑몰이름}] Complete your registration",
		"14" => "[{쇼핑몰이름}] Complete your profile",
		"15" => "[{쇼핑몰이름}] To notify receiving Information for marketing purpose",
		"16" => "[{쇼핑몰이름}] Change password",
		"17" => "[{쇼핑몰이름}] The extinction of mileage",
		"18" => "(Advertisement) [{쇼핑몰이름}] The extinction of mileage",
		"19" => "[{쇼핑몰이름}] Verification number",
		"21" => "[{쇼핑몰이름}] To notify changing Information for marketing purpose",
		"22" => "[{쇼핑몰이름}] Report on personal information usage",
		"23" => "[{쇼핑몰이름}] Order History",
		"24" => "[{쇼핑몰이름}] Birthday coupon issued"
	);

	$ch1 = array(
		"1" => "[{쇼핑몰이름}] 注册",
		"2" => "[{쇼핑몰이름}] 确认订单信息",
		"3" => "[{쇼핑몰이름}] 商品配送",
		"4" => "[{쇼핑몰이름}] 完成配送",
		"5" => "[{쇼핑몰이름}] {회원이름}，这是您的临时密码。",
		"7" => "[{쇼핑몰이름}] %s",
		"8" => "[{쇼핑몰이름} 咨询] %s",
		"9" => "[{쇼핑몰이름}] 询问服务",
		"10" => "[{쇼핑몰이름}] {회원이름}的 %s 已发至布告板。",
		"12" => "[{쇼핑몰이름}] 休眠会员事先通知",
		"13" => "[{쇼핑몰이름}] 注册验证",
		"14" => "[{쇼핑몰이름}] 修改信息认证",
		"15" => "[{쇼핑몰이름}] 介绍同意接收广告信息",
		"16" => "[{쇼핑몰이름}] 修改密码",
		"17" => "[{쇼핑몰이름}] 积分消失",
		"18" => "( 广告) [{쇼핑몰이름}] 积分消失",
		"19" => "[{쇼핑몰이름}] 发送验证码",
		"21" => "[{쇼핑몰이름}] 介绍边陲接收广告信息",
		"22" => "[{쇼핑몰이름}] 个人信息使用明细指南",
		"23" => "[{쇼핑몰이름}] 确认订单信息",
		"24" => "[{쇼핑몰이름}] Birthday coupon issued"
	);

	$ch2 = array(
		"1" => "[{쇼핑몰이름}] 註冊",
		"2" => "[{쇼핑몰이름}] 確認訂單信息",
		"3" => "[{쇼핑몰이름}] 商品配送",
		"4" => "[{쇼핑몰이름}] 完成配送",
		"5" => "[{쇼핑몰이름}] {회원이름}，請您再次驗證郵箱。",
		"7" => "[{쇼핑몰이름}] %s",
		"8" => "[{쇼핑몰이름} 咨詢] %s",
		"9" => "[{쇼핑몰이름}] 詢問服務",
		"10" => "[{쇼핑몰이름}] {회원이름}的 %s 已發至佈告板。",
		"12" => "[{쇼핑몰이름}] 休眠會員事先通知",
		"13" => "[{쇼핑몰이름}] 註冊驗證",
		"14" => "[{쇼핑몰이름}] 修改信息認證",
		"15" => "[{쇼핑몰이름}] 介紹同意接收廣告信息",
		"16" => "[{쇼핑몰이름}] 修改密碼",
		"17" => "[{쇼핑몰이름}] 積分消失",
		"18" => "(廣告) [{쇼핑몰이름}] 積分消失",
		"19" => "[{쇼핑몰이름}] 發送驗證碼",
		"21" => "[{쇼핑몰이름}] 介紹邊陲接收廣告信息",
		"22" => "[{쇼핑몰이름}] 個人信息使用明細指南",
		"23" => "[{쇼핑몰이름}] 確認訂單信息",
		"24" => "[{쇼핑몰이름}] Birthday coupon issued"
	);

	if(!$cfg['mail_lang']) {
		$cfg['mail_lang'] = $cfg['language_pack'];
	}

	if(empty($mail_title[1])) $mail_title[1] = ${$cfg['mail_lang']}[1];
	if(empty($mail_title[2])) $mail_title[2] = ${$cfg['mail_lang']}[2];
	if(empty($mail_title[3])) $mail_title[3] = ${$cfg['mail_lang']}[3];
	if(empty($mail_title[4])) $mail_title[4] = ${$cfg['mail_lang']}[4];
	if(empty($mail_title[5])) $mail_title[5] = ${$cfg['mail_lang']}[5];
	if(empty($mail_title[8])) $mail_title[8] = ${$cfg['mail_lang']}[8];
	if(empty($mail_title[9])) $mail_title[9] = ${$cfg['mail_lang']}[9];
	if(empty($mail_title[10])) $mail_title[10] = ${$cfg['mail_lang']}[10];
	if(empty($mail_title[12])) $mail_title[12] = ${$cfg['mail_lang']}[12];
	if(empty($mail_title[13])) $mail_title[13] = ${$cfg['mail_lang']}[13];
	if(empty($mail_title[14])) $mail_title[14] = ${$cfg['mail_lang']}[14];
	if(empty($mail_title[15])) $mail_title[15] = ${$cfg['mail_lang']}[15];
	if(empty($mail_title[16])) $mail_title[16] = ${$cfg['mail_lang']}[16];
	if(empty($mail_title[17])) $mail_title[17] = ${$cfg['mail_lang']}[17];
	if(empty($mail_title[18])) $mail_title[18] = ${$cfg['mail_lang']}[18];
	if(empty($mail_title[19])) $mail_title[19] = ${$cfg['mail_lang']}[19];
	if(empty($mail_title[21])) $mail_title[21] = ${$cfg['mail_lang']}[21];
	if(empty($mail_title[22])) $mail_title[22] = ${$cfg['mail_lang']}[22];
	if(empty($mail_title[23])) $mail_title[23] = ${$cfg['mail_lang']}[23];
	if(empty($mail_title[24])) $mail_title[24] = ${$cfg['mail_lang']}[24];

	// 제목에 광고 태그 추가
	/*
	if(preg_match('/^\(광고\)/', $mail_title[18]) == false) {
		$mail_title[18] = '(광고)'.$mail_title[18];
	}
	*/
	$_mstr['쇼핑몰이름'] = stripslashes($cfg['company_mall_name']);
	$email_logo = $cfg['email_logo_img'] ? "<img src='".$root_url.$cfg['email_logo_img']."'>" : '';
	$_mstr['로고'] = $email_logo;
	$_mstr['로고URL'] = $root_url.$cfg['email_logo_img'];
	$_mstr['쇼핑몰주소'] = $root_url;

	if($ord['ono']) {
		include_once $engine_dir."/_manage/skin_module/_skin_module.php";
		include_once $engine_dir."/_engine/include/design.lib.php";
		if(!$_skin) $_skin = getSkinCfg();

		// 주문목록
		$oprd_str="<table width=\"100%\" align=\"center\" cellspacing=1 cellpadding=1 bgcolor=\"#3E3E3E\">
			<tr bgcolor=\"#EEEEEE\">
				<td align=\"center\" width=\"50%\">".__lang_email_product_name."</td>
				<td align=\"center\" width=\"20%\">".__lang_email_product_price."</td>
				<td align=\"center\" width=\"15%\">".__lang_email_product_ea."</td>
				<td align=\"center\" width=\"20%\">".__lang_email_product_sum."</td>
			</tr>";
		$_mail_tmp = '';
		if(file_exists($_skin['folder'].'/MODULE/mail_order_product_list.wsm') == false) { // 기본 스킨 복사
			include_once $engine_dir."/_engine/include/img_ftp.lib.php";
			$file['name'] = "mail_order_product_list.wsm";
			$file['tmp_name'] = $engine_dir.'/_engine/skin_module/default/MODULE/'.$_edit_pg;
			ftpUploadFile($_skin['folder'].'/MODULE', $file, "wsm");
		}
		$_mail_line = getModuleContent('mail_order_product_list');
		while($cart=orderCartList(",  ",":","(",")")) {
			$oprd_str.="<tr bgcolor=\"#FFFFFF\">
					<td><a href=\"$cart[plink]\" target=\"_blank\">$cart[name]</a> $cart[option_str]</td>
					<td align=\"right\">$cart[sell_prc] ".$cfg['currency_type']."</td>
					<td align=\"right\">$cart[buy_ea]</td>
					<td align=\"right\">$cart[total_prc] ".$cfg['currency_type']."</td>
				</tr>";
			$_mail_tmp .= lineValues("mail_order_product_list", $_mail_line, $cart, "common_module");
		}
        unset($orderCartRes, $GLOBALS['orderCartRes']);
		$_mail_tmp = listContentSetting($_mail_tmp, $_mail_line);
		$_mstr['메일주문상품목록']=$_mail_tmp;

		$ord['prd_prc']=parsePrice($ord['prd_prc'], true);
		$ord['dlv_prc']=parsePrice($ord['dlv_prc'], true);
		$ord['total_prc']=parsePrice($ord['total_prc'], true);
		$oprd_str.="<tr bgcolor=\"#FFFFFF\">
				<td colspan=\"20\" style=\"\" align=\"right\">
				[".__lang_email_product_product_sum_price."] <b>$ord[prd_prc] ".$cfg['currency_type']."</b> +
				[".__lang_email_product_shipping_fee."] <b>$ord[dlv_prc] ".$cfg['currency_type']."</b>
				= [".__lang_email_product_total_pay_price."] <b>$ord[total_prc] ".$cfg['currency_type']."</b>
			</tr>
		</table>";

		$_mstr['주문상품목록']=$oprd_str;
		$_mstr['주문금액']=$ord['prd_prc'];
		$_mstr['배송비']=$ord['dlv_prc'];
		$_mstr['총할인금액']=getOrderTotalSalePrc($ord, true);
		$_mstr['결제금액']=$ord['total_prc'];
	}

	// 회원가입
	if($mail_case==1) {
		$_mstr['회원이름']=$member_name=$name;
		$_mstr['아이디']=$member_id;
		$_mstr['가입일시']=date("Y/m/d H:i",$now);
		$to_mail=$email;
	}
	// 주문확인
	elseif($mail_case==2 || $mail_case == 23) {
		$_mstr['회원이름']=$member_name=$ord['buyer_name'];
		$_mstr['주문번호']=$ord['ono'];
		$_mstr['주문일시']=date("Y/m/d H:i",$ord['date1']);
		$_mstr['주문상품']=$ord['title'];
		$_mstr['주문상품목록']=$oprd_str; // 2007-11-15 - Han
		if($ord['cart_where']==7) {
			$_mstr['상품포인트']=number_format($ord['total_prc']);
		}
		else {
			$_mstr['상품포인트']=0;
		}
		$_mstr['결제방법']=$_pay_type[$ord['pay_type']];
		$_mstr['결제금액']=number_format($ord['pay_prc'],$cfg['currency_decimal']);
		$_mstr['화폐단위']=$cfg['currency_type'];
		$_mstr['적립금액']=number_format($ord['total_milage'],$cfg['currency_decimal'])	;
		$_mstr['수취인명']=$ord['addressee_name'];
		$_mstr['수취인주소']=$ord['addressee_addr1']." ".$ord['addressee_addr2'];
		$_mstr['수취인전화번호']=$ord['addressee_phone'];
		$_mstr['수취인휴대번호']=$ord['addressee_cell'];
		$to_mail=$ord['buyer_email'];

		if(in_array('0', $email_checked) && $ord['ono']) {
            if ($mail_case == 23) {
                //입점사
                $admin_email = $partner['partner_email'];
            } else {
                //본사
                if($cfg['email_admin']) { $admin_email=$cfg['email_admin'];	} else { $admin_email=$cfg['admin_email']; }
            }
			sendMailContent($mail_case,$cfg['company_mall_name']." 관리자",$admin_email);
		}
		$_mstr['메일주문상품목록']=$_mail_tmp;
		$_mstr['주문금액']=$ord['prd_prc'];
		$_mstr['배송비']=$ord['dlv_prc'];
		$_mstr['총할인금액']=getOrderTotalSalePrc($ord, true);
		$_mstr['사용적립금']=number_format($ord['milage_prc']);
		$_mstr['사용예치금']=number_format($ord['emoney_prc']);
	}
	// 배송출발
	elseif($mail_case==3) {
		$_mstr['회원이름']=$member_name=$data['buyer_name'];
		$_mstr['주문번호']=$data['ono'];
		$_mstr['주문상품']=$data['title'];
		$_mstr['택배사']=$dlv['name'];
		$_mstr['배송추적링크']=$dlv['url'];
		$_mstr['송장번호']=$data['dlv_code'];
		$_mstr['주문상품목록']=$oprd_str;
		$_mstr['수취인명']=stripslashes($data['addressee_name']);
		$_mstr['수취인주소']=stripslashes($data['addressee_addr1']." ".$data['addressee_addr2']);
		$_mstr['수취인전화번호']=$data['addressee_cell'];
		$_mstr['수취인휴대번호']=$data['addressee_cell'];
		$_mstr['메일주문상품목록']=stripslashes($_mail_tmp);
		$_mstr['주문금액']=$ord['prd_prc'];
		$_mstr['배송비']=$ord['dlv_prc'];
		$_mstr['총할인금액']=number_format($ord['sale1']+$ord['sale2']+$ord['sale3']+$ord['sale4']+$ord['sale5']+$ord['sale6']);
		$_mstr['사용적립금']=number_format($ord['milage_prc']);
		$_mstr['사용예치금']=number_format($ord['emoney_prc']);
		$_mstr['결제금액']=number_format($ord['pay_prc'],$cfg['currency_decimal']);
		$to_mail=$data['buyer_email'];
	}
	// 배송완료
	elseif($mail_case==4) {
		$_mstr['회원이름']=$member_name=$data['buyer_name'];
		$_mstr['주문번호']=$data['ono'];
		$_mstr['주문상품']=$data['title'];
		$_mstr['배송지']="$data[addressee_zip] $data[addressee_addr1] $data[addressee_addr2]";
		$_mstr['연락처']=$data['addressee_phone'];
		$_mstr['주문상품목록']=$oprd_str;
		$_mstr['메일주문상품목록']=stripslashes($_mail_tmp);
		$_mstr['주문금액']=$ord['prd_prc'];
		$_mstr['배송비']=$ord['dlv_prc'];
		$_mstr['총할인금액']=number_format($ord['sale1']+$ord['sale2']+$ord['sale3']+$ord['sale4']+$ord['sale5']+$ord['sale6']);
		$_mstr['사용적립금']=number_format($ord['milage_prc']);
		$_mstr['사용예치금']=number_format($ord['emoney_prc']);
		$_mstr['결제금액']=number_format($ord['pay_prc'],$cfg['currency_decimal']);
		$to_mail=$data['buyer_email'];
	}
	// 비밀번호문의
	elseif($mail_case==5) {
		$_mstr['회원이름']=$data['name'];
		$_mstr['비밀번호']=$new_tmp_pwd;
		$_mstr['아이피']=$_SERVER['REMOTE_ADDR'];
		$_mstr['문의시간']=date("Y/m/d H:i",$now);
		$to_mail=$data[email];
	}
	// 메일 직접전송
	elseif($mail_case==6) {
		$_mstr['메일내용']=$content1;
		$_mstr['수신거부링크'] = $root_url.'/main/exec.php?exec_file=member/deny_email.exe.php&e='.md5($data['email']);
		$to_mail=$email;
	}
	// 관리자에게메일보내기
	elseif($mail_case==8) {
		$_mstr['메일내용']=$content;
		$_mstr['성명']=$from_name;
		$_mstr['연락처']=$tel;
		$to_mail=$to_email;
	}
	// 문의글 답변
	elseif($mail_case == 9) {
		$_mstr['회원이름'] = $name;
		$_mstr['문의제목'] = $title;
		$_mstr['문의내용'] = nl2br($content);
		$_mstr['답변내용'] = nl2br($answer);
		$to_mail = $email;
	}
	elseif($mail_case == 10) {
		$to_mail = ($cfg['email_admin_board']) ? $cfg['email_admin_board'] : $cfg['admin_email'];
		$member_name = $name;
		$_mstr['메일내용'] = $title;
		$mail_title[10] = sprintf("[{쇼핑몰이름}] {회원이름}님의 %s 게시물이 작성되었습니다.", $board_name);//"[{쇼핑몰이름}] {회원이름}님의 ".$board_name."게시물이 작성되었습니다.";
	}
	elseif($mail_case == 12) {
		$_mstr['회원이름'] = stripslashes($data['name']);
		$_mstr['아이디'] = stripslashes($data['member_id']);
		$_mstr['가입일'] = date('Y년 m월d일', $data['reg_date']);
		$_mstr['최종접속일'] = date('Y년 m월d일', $data['last_con']);
		$_mstr['휴면처리일'] = date('Y년 m월 d일', strtotime('+30 days', $now));
		$_mstr['휴면처리대기일'] = date('Y년 m월 d일', strtotime('+29 days', $now));
		$to_mail = $data['email'];
	}elseif($mail_case==13) {
		$_mstr['회원이름']=$member_name=$name;
		$_mstr['인증키']=$key;

		$to_mail=$email;
	}elseif($mail_case==14) {
		$_mstr['회원이름']=$member_name=$name;
		$_mstr['인증키']=$key;

		$to_mail=$email;
	}else if($mail_case == 15) {
		$member_name = $mem['name'];
		$to_mail = $mem['email'];

		$_mstr['회원이름'] = $member_name;
		$_mstr['아이디'] = $mem['member_id'];
		$_mstr['이메일수신허용일'] = ($mem['mailing'] == 'Y') ? date('Y년 m월 d일', $mem['mailing_chg_date']) : '미수신';
		$_mstr['SMS수신허용일'] = ($mem['sms'] == 'Y') ? date('Y년 m월 d일', $mem['sms_chg_date']) : '미수신';
		$_mstr['메일발송일'] = date('Y년 m월 d일', $now);
	}elseif($mail_case==16) {
		$_mstr['회원이름']=$member_name=$name;
		$_mstr['비밀번호변경인증키']=$key;

		$to_mail=$email;
	}elseif($mail_case==17) {
		$_mstr['회원이름']=$data['name'];
		$_mstr['소멸적립금']=parsePrice($amount, true);
		$_mstr['소멸예정일']=$expire_date;

		$to_mail=$email;
	}elseif($mail_case==18) {
		$_mstr['회원이름']=$data['name'];
		$_mstr['소멸적립금']=parsePrice($amount, true);
		$_mstr['소멸예정일']=$expire_date;
		$_mstr['수신거부링크']=$root_url.'/main/exec.php?exec_file=member/deny_email.exe.php&e='.md5($data['email']);

		$to_mail=$email;
	}elseif($mail_case==19) {
		$_mstr['인증번호'] = $reg_code;

		$to_mail=$email;
	}elseif($mail_case==21) {
		$_mstr['회원이름'] = $member_name;
		$_mstr['광고성정보변경일자'] = $marketing_regdate;
		$_mstr['SMS이메일수신동의여부'] = $sms_email_yn;

		$to_mail=$email;
	}elseif($mail_case==22) {
		$_mstr['회원이름'] = $data['name'];

		$to_mail=$email;
	}elseif($mail_case==24) {
		$_mstr['회원이름'] = $data['name'];

		$to_mail=$email;
	}

?>