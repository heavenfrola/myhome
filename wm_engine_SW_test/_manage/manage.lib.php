<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  _manage/manage.lib.php - 관리자모드 공통 라이브러리
	' +----------------------------------------------------------------------------------------------+*/

	if(!$root_dir) $root_dir="..";
	include_once $engine_dir."/_engine/include/common.lib.php";

	if($_REQUEST['urlfix'] != 'Y' && $cfg['ssl_type'] == 'Y' && $_SERVER['HTTPS'] != 'on') {
		msg('', str_replace('http://', 'https://', getURL()));
	}

	if($_SESSION['partner_login_no'] > 0) {
		$admin['level'] = 4;
		$admin['partner_no'] = $_SESSION['partner_login_no'];
	}

	if(file_exists($engine_dir.'/_engine/include/account/getHspec.inc.php')) {
		// include $engine_dir.'/_engine/include/account/getHspec.inc.php';
	} else {
		// 이미지 업로드 스펙
		include_once $GLOBALS['engine_dir'].'/_config/set.upload.php';
		$_h_spec['img_upload_limit'] = $up_cfg['prdBasic']['filesize'];
		$_h_spec['img_limit'] = 0;
		$_SESSION['h_spec'] = $_h_spec;
	}

	if($admin['level'] == 4 && !$admin['partner_no']) {
		unset($_SESSION['admin_no'], $_SESSION['admin_id']);
		msg('입점파트너가 지정되어 있지않는 담당자입니다.', $root_ur.'/_manage/index.php');
	}

	//계정잠금 추가
	if($admin['access_lock']=="Y" && $body!="intra@access_limit.exe" && $body!="intra@access_limit.frm") {
		$rURL=$this_url;
		include_once $engine_dir."/_manage/main/login.php";
		exit;
	}

	if((!is_array($admin) || !$_SESSION['admin_no']) && $body!="main@login.exe" && $body!="R2Na2@R2Na_field.exe" && $body != 'board@mng_login.frm' && $body != 'board@mng_login.exe' && $exec_file != 'common/ssoLogin.php' && $exec_file != 'common/ssoLogin2.php' && $body != 'css@manage.css' && $body != "config@cash_receipt.exe" && $exec_file != 'order/auto_order_finish.exe.php' && $body != 'member@member_login.exe' && $body!="intra@access_limit.exe" && $body!="intra@access_limit.frm" && $body!="intra@intra_factor.frm" && preg_match('/intra@password_expire\./', $body) == false && $exec_file != 'mypage/withdraw.exe.php' && $_REQUEST['exec_file'] != 'api/openapi.exe.php') {
		$rURL=$this_url;
		include_once $engine_dir."/_manage/main/login.php";
		exit;
	}

	include_once $engine_dir."/_engine/include/ext.lib.php";
	include_once $engine_dir."/_manage/manage2.lib.php";
	include_once $engine_dir."/_engine/include/file.lib.php";

	$pg_dsn="fm";

	$_ctitle[1]="대분류";
	$_ctitle[2]=($_ctitle[2]) ? $_ctitle[2] : "기획전";
	$_ctitle[3]="분류";
	$_ctitle[6]=$cfg['mobile_name']."기획전";
	$_ctitle[11]="코디세트";

	$_order_color_def[1]="#330000";
	$_order_color_def[2]="#FF00FF";
	$_order_color_def[3]="#3300FF";
	$_order_color_def[4]="#CC00CC";
	$_order_color_def[5]="#339900";

	$_order_color_def[11]="#FF6600";
	$_order_color_def[12]="#FF0000";
	$_order_color_def[13]="#663300";
	$_order_color_def[14]="#FF0000";
	$_order_color_def[15]="#663300";
	$_order_color_def[16]="#FF0000";
	$_order_color_def[17]="#663300";
	$_order_color_def[18]="#FF0000";
	$_order_color_def[19]="#663300";
	$_order_color_def[20]="#000000";

	$_order_color_def[31]="#FF0000";

	$_order_color = setOrderColor();

	if($cfg['use_partner_shop'] == 'Y') {
		$_mng_group=array(2=>"최고관리자", 3=>"사원", 4=>"입점사(상점관리)");
	}else {
		$_mng_group=array(2=>"최고관리자", 3=>"사원");
	}
	if(!is_array($_cont_page)) {
		$_cont_page_name=array("회사소개","이용안내","이용약관 페이지","이용약관 내용","개인정보취급방침");
		$_cont_page=array("company","guide","uselaw","join_rull","privacy");
	}

	$_kdate=array("일","월","화","수","목","금","토");

	$cfg['member_local_cut']=2;

	// 옵션 단위 예제
	$_ounit=array('㎎','㎏','㎜','㎝','㎞','㎖','ℓ','㎘','㏄','㎈','㎉','㎐','㎑','㎒','㎓','㏅','㏓','㎣','㎤','㎥','㎦','㎟','㎠','㎡','㎢');

	// 회원 접속 로그
	$_login_result[0]="정상";
	$_login_result[1]="비밀번호 오류";
	$_login_result[2]="아이디 없음";
	$_login_result[3]="탈퇴회원";
	$_login_result[4]="미인증 상태";
	$_login_result[5]="자동로그인";

	$dummy_cate="---------------";

	// 메인 출력 주문 상태
	$_morder_stat=array(1,2,3,4,12,14,16);

	// 시간 단위
	$date_type_items = array(
		'Y' => '년(4자리)',
		'y' => '년(2자리)',
		'm' => '월',
		'd' => '일',
		'D' => '요일(Sun~Sat)',
		'l' => '요일(Sunday~Saturday)',
		'%' => '요일(월~일)',
		'^' => '요일(월요일~일요일)',
		'H' => '시간(00~24)',
		'a h' => '시간(am/pm 0~12)',
		'A h' => '시간(AM/PM 0~12)',
		'i' => '분',
		's' => '초',
		' ' => '공백문자',
		':' => ':',
		'/' => '/',
		'-' => '-'
	);

	/* +----------------------------------------------------------------------------------------------+
	' |  void upNum(string input필드명) - 숫자단위 자동입력
	' +----------------------------------------------------------------------------------------------+*/
	function upNum($obj) {
		?>
			<div style="padding:5px 0;">
				<span class="box_btn_s"><input type="button" value="백만" onClick="upNum(<?=$obj?>,1000000)"></span>
				<span class="box_btn_s"><input type="button" value="십만" onClick="upNum(<?=$obj?>,100000)"></span>
				<span class="box_btn_s"><input type="button" value="만" onClick="upNum(<?=$obj?>,10000)"></span>
				<span class="box_btn_s"><input type="button" value="천" onClick="upNum(<?=$obj?>,1000)"></span>
				<span class="box_btn_s"><input type="button" value="백" onClick="upNum(<?=$obj?>,100)"></span>
				<span class="box_btn_s"><input type="button" value="십" onClick="upNum(<?=$obj?>,10)"></span>
				<span class="box_btn_s"><input type="button" value="정정" onClick="upNum(<?=$obj?>,0)"></span>
			</div>
		<?php
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  void trncPrd(int 적용시간, int 제외상품코드) - 보관시간이 초과된 장바구니 정리
	' +----------------------------------------------------------------------------------------------+*/
	function trncPrd($hr = 0, $pno = null) {
		global $now,$tbl,$root_dir,$use_pack,$cfg, $pdo;

		$del_time = ($hr) ? $now - (60 * 60 * $hr) : $now;
		if($pno) $w .= " and `no` != '$pno'"; // 현재상품은 제외
		if($cfg['use_partner_shop'] == 'Y') {
			$w .= " and partner_stat not in (1, 3)";
		}
		$res = $pdo->iterator("select no from $tbl[product] where stat=1 and name='' and reg_date < $del_time $w");
        foreach ($res as $data) {
			delPrd($data['no']);
		}
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string getSex(string 주민번호) - 주민등록번호로 성별 출력
	' +----------------------------------------------------------------------------------------------+*/
	function getSex($jumin) {
		$sex=((@substr($jumin,7,1)%2)==1) ? "남":"여";
		return $sex;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  int getAage(string 주민번호, string 생일) - 주민번호 혹은 생일로 나이 출력
	' +----------------------------------------------------------------------------------------------+*/
	function getAge($jumin="",$birth="") {
		global $now;
		if($jumin || $birth){
			$y1=date("Y",$now);
			$y2=@substr($jumin,0,2);
			$c=@substr($jumin,7,1);
			$cafe24=@substr($jumin,10,4);

			if($c<3 || $cafe24=="XXXX") $y2=1900+$y2;
			else $y2=2000+$y2;

			if($birth) $y2=$birth;

			$age=$y1-$y2+1;
			return $age;
		}
	}


	/* +----------------------------------------------------------------------------------------------+
	' |  void delPrd(int 상품번호) - 지정된 상품 밑 제반 데이터/파일들 삭제
	' +----------------------------------------------------------------------------------------------+*/
	function delPrd($pno) {
		global $tbl, $cfg, $root_dir, $_use, $engine_dir, $file_server, $wec_account, $wdisk_con, $_we, $now, $admin, $pdo;

		if(is_object($wec_account) == false) {
			$wec_account = new weagleEyeClient($_we, 'account');
		}

		if(!$wdisk_con) {
			include_once $engine_dir.'/_manage/product/product_wdisk.inc.php';
		}

		$prd = $pdo->assoc("select * from $tbl[product] where no='$pno'");
		if(!$prd['no']) return;

		if($cfg['use_trash_prd'] == 'Y' && $prd['stat'] < 5 && $prd['stat'] > 1) { // 휴지통
			$pdo->query("update $tbl[product] set stat=5, del_stat='$prd[stat]', del_date='$now', del_admin='$admin[admin_id]' where no='$prd[no]'");
			if($prd['wm_sc'] == 0) {
				$pdo->query("update $tbl[product] set stat=5, del_stat='$prd[stat]', del_date='$now', del_admin='$admin[admin_id]' where wm_sc='$prd[no]'");
			}
			prdStatLogw($prd['no'], 5, $prd['stat']);
			return true;
		}

		if($prd['stat'] > 1) delete_log("P", $prd['no'], $prd['name']);

		if($prd['wm_sc'] == 0) { // 바로가기 아닐 경우
			$wdisk_size = 0;
			$res = $pdo->iterator("select * from $tbl[product_image] where `pno`='$pno'");
            foreach ($res as $data) {
				if($data['ori_no'] > 0) continue;
				if($data['filetype'] == 9) { // 윙디스크
					$wdisk_size += $data['filesize'];
					$fullpath = $data['updir'].'/'.$data['filename'];

					$wdisk_con->delete('_thumb_'.$fullpath); // 섬네일 삭제
					$wdisk_con->delete($fullpath); // 이미지 삭제
				} else {
					deleteAttachFile($data['updir'], $data['filename']);
				}
			}
			if($wdisk_size > 0) { // 용량복구
				$wec_account->queue('wdiskSize', $wec_account->config['account_idx'], 'delete', $wdisk_size);
				$wec_account->send_clean();
			}
			$pdo->query("delete from `".$tbl['product_image']."` where `pno`='$pno'");

			// 상품평
			$res = $pdo->iterator("select updir, upfile1, upfile2 from $tbl[review] where `pno`='$pno'");
            foreach ($res as $data) {
				deletePrdImage($data, 1, 2);
			}
			$pdo->query("delete from $tbl[review] where pno='$pno'");

			// 윙POS
			$res = $pdo->iterator("select complex_no from erp_complex_option where pno='$pno'");
            foreach ($res as $pos) {
				$pdo->query("delete from erp_inout where complex_no='$pos[complex_no]'");
			}
			$pdo->query("delete from erp_complex_option where pno='$pno'");

			// 기본이미지
			deletePrdImage($prd);

			// 옵션
			deleteOption($pno);

			// 기타
			$pdo->query("delete from `".$tbl['qna']."` where `pno`='$pno'"); // 상품 문의
			$pdo->query("delete from `".$tbl['product_filed']."` where `pno`='$pno'"); // 상품 항목
			$pdo->query("delete from `".$tbl['product_annex']."` where `pno`='$pno'"); // 부속 상품
			$pdo->query("delete from `".$tbl['product_stat_log']."` where `pno`='$pno'"); // 상품 상태 변경 로그
			$pdo->query("delete from `".$tbl['product']."` where `wm_sc`='$pno'"); // 바로가기삭제
			$pdo->query("delete from `".$tbl['product_openmarket']."` where `wm_sc`='$pno'"); // 오픈마켓가격정보
			$pdo->query("delete from `".$tbl['product_link']."` where `pno`='$pno'"); // 카테고리 연결정보
			$pdo->query("delete from {$tbl['product_refprd']} where pno='$pno'");
			$pdo->query("delete from {$tbl['product_refprd']} where refpno='$pno'");
            $pdo->query("delete from {$tbl['cart']} where pno='$pno'");
            if ($cfg['use_set_product'] == 'Y') {
                $pdo->query("delete from {$tbl['cart']} where set_pno='$pno'");
            }
		}

		$pdo->query("delete from $tbl[product] where no='$pno'");
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  void deleteOption(int 상품번호[, int 옵션번호]) - 지정 상품의 옵션데이터들 삭제
	' +----------------------------------------------------------------------------------------------+*/
	function deleteOption($pno, $opno = null) {
		global $tbl, $pdo;

		if(!$pno && !$opno) return;
		if($pno > 0) $w = " and pno='$pno'";
		if($opno) {
			$w1 .= " and opno = $opno";
			$w2 .= " and no = $opno";
		}

		// 옵션명 이미지
		$res = $pdo->iterator("select '_data/prd_common' as updir, upfile1 from $tbl[product_option_set] where 1 $w $w2");
        foreach ($res as $data) {
			//deleteAttachFile($data['updir'], $data['upfile1']);
		}

		// 옵션아이템 이미지, 윙POS 옵션
		$res = $pdo->iterator("select no, updir, upfile1 from $tbl[product_option_img] where 1 $w $w1");
        foreach ($res as $data) {
			//deleteAttachFile($data['updir'], $data['upfile1']);
			$pdo->query("delete from erp_complex_option where `opt1`='$data[no]' or `opt2`='$data[no]'");
		}

		$pdo->query("delete from $tbl[product_option_set] where 1 $w $w2");
		$pdo->query("delete from $tbl[product_option_item] where 1 $w $w1");
		$pdo->query("delete from $tbl[product_option_img] where 1 $w $w1");
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  void delOrd(string 주문번호) - 지정된 주문서를 삭제
	' +----------------------------------------------------------------------------------------------+*/
	function delOrd($ono) {
		global $tbl, $use_pack, $root_dir, $_use, $cfg, $engine_dir, $admin, $now, $erpListener, $pdo;

		$ord = $pdo->assoc("select no, stat, member_no from $tbl[order] where ono='$ono'");

		if($ord['stat'] != 32) {
			include_once $engine_dir.'/_engine/include/wingPos.lib.php';
			$res = $pdo->iterator("select * from `$tbl[order_product]` where `ono`='$ono'");
            foreach ($res as $data) {
				if($cfg['erp_timing'] <= $data['stat'] && $data['stat'] < 10) stockChange($data, '+', $data['buy_ea'], $data['ono'].' 주문 삭제');
			}
		}

		if(is_object($erpListener)) {
			$erpListener->removeOrder($ono);
		}

		if($cfg['use_trash_ord'] == 'Y' && $ord['stat'] != 32) { // 휴지통
			$pdo->query("update $tbl[order] set stat=32, del_stat='$ord[stat]', del_date='$now', del_admin='$admin[admin_id]' where no='$ord[no]'");
			$res = $pdo->iterator("select no, stat from $tbl[order_product] where ono='$ono'");
            foreach ($res as $oprd) {
				$pdo->query("update $tbl[order_product] set stat=32, del_stat='$oprd[stat]' where no='$oprd[no]'");
			}
			ordChgPart($ono);
			ordStatLogw($ono, 32, 'N');

			if($ord['member_no']) {
				setMemOrd($ord['member_no'], 1);
			}
			return true;
		}

		$pdo->query("delete from `$tbl[order_product]` where `ono`='$ono'");
		$pdo->query("delete from `$tbl[order_payment]` where `ono`='$ono'");
		$pdo->query("delete from `$tbl[order]` where `ono`='$ono'");

		if($ord['member_no']) {
			setMemOrd($ord['member_no'], 1);
		}
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  void adminCheck(int 관리자레벨, string 세부권한) - 관리자모드 접속 등급 체크
	' +----------------------------------------------------------------------------------------------+*/
	function adminCheck($level,$auth="") {
		global $admin;
		if($level<$admin['level']) msg("접근할 수 없습니다","back");
		if($admin['level']==2) {
			$auth="";
		}
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  void updateWMCode(string 코드명, string 값[, string 추가코드]) - 사이트 디폴트값(캐시) 저장
	' +----------------------------------------------------------------------------------------------+*/
	function updateWMCode($code,$value,$ext="") {
		global $tbl, $pdo;
		$data=get_info($tbl['default'],"code",$code);
		$value=addslashes($value);
		if(!$data['code']) {
			$sql="INSERT INTO `".$tbl['default']."` ( `code` , `value` , `ext` ) VALUES ( '$code', '$value', '$ext')";
		}
		else {
			$sql="update {$tbl['default']} set `value`='$value', `ext`='$ext' where `code`='$data[code]'";
		}
		$pdo->query($sql);
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string delImgStr(array 상품정보, int 업로드순서, bool 이미지출력여부) - 첨부파일 미리보기 및 삭제체크박스 출력
	' +----------------------------------------------------------------------------------------------+*/
	function delImgStr($data,$ii, $print = false) {
		global $root_dir,$root_url,$_use;

		$field = (preg_match("/[^0-9]/",$ii)) ? $ii : "upfile".$ii;
		$name = (preg_match("/[^0-9]/",$ii)) ? $ii."_del" : "delfile".$ii;

		if($data[$field] && ((($_use['file_server'] != "Y" || !fsConFolder($data['updir'])) && is_file($root_dir."/".$data['updir']."/".$data[$field])) || ($_use['file_server'] == "Y" && fsConFolder($data['updir'])))) {
			$file_dir = getFileDir($data['updir']);
			$r = ($print) ? "<a href='$file_dir/$data[updir]/$data[$field]' target='_blank'><img src='$file_dir/$data[updir]/{$data[$field]}' height='{$print}px' align='absmiddle'></a> " : "<span class=\"box_btn_s\" style=\"margin-bottom:5px; text-align:center;\"><a href=\"".$file_dir."/".$data['updir']."/".$data[$field]."\" target=\"_blank\">기존이미지 보기</a></span>";
			$r.="<br><label class=\"p_cursor\"><input type=\"checkbox\" name=\"$name\" value=\"Y\"> 기존이미지 삭제</label>";
			$r = "<div style=\"padding:5px 0;\">$r</div>";
		}
		return $r;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  array parseOrder(array 주문리스트) - 주문리스트의 출력값 정리
	' +----------------------------------------------------------------------------------------------+*/
	function parseOrder($data) {
		global $_pay_type,$pay_type,$cfg,$rclass,$idx,$tbl,$_use,$cut_title,$engine_url,$admin, $pdo, $scfg;
		$pay_type="<img src=\"".$engine_url."/_manage/image/icon/pay".$data['pay_type'].".gif\">";
		if($data['pay_type']!=3) {
			if($data['milage_prc']>0 || ($data['checkout'] == 'Y' && $data['point_use'] > 0)) $pay_type.="<img src=\"".$engine_url."/_manage/image/icon/pay+.gif\">";
		}
		if($data['pay_type']!=6) {
			if($data['emoney_prc']>0) $pay_type.="<img src=\"".$engine_url."/_manage/image/icon/pay+6.gif\">";
		}
		if($data['sale5']>0 || $data['sale7'] > 0) {
			$pay_type.="<img src=\"".$engine_url."/_manage/image/icon/pay+c.gif\">";
		}
		if($data['pay_type']==5) { // 가상계좌
			$card=get_info($tbl['card'], 'wm_ono', $data['ono']);
			if($card['quota']=="Y") { // 에스크로
				$pay_type.="(에)";
			}
		}

		if(($data['checkout'] == 'Y' || $data['smartstore'] == 'Y') && ($data['pay_type'] == '26' || $data['pay_type'] == '24')) {
			$pay_type = str_replace('pay24.gif', 'pay24n.gif', $pay_type);
			$pay_type = str_replace('pay26.gif', 'pay26n.gif', $pay_type);
		}

		if(!$data['pay_type']) {
			$pay_type="";
		}
		$rclass=($idx%2==0) ? "tcol2" : "tcol3";

		// 부분 배송
		$stat2 = preg_replace('/^@|@$/', '', $data['stat2']);
		if($cfg['dlv_part'] == 'Y' && substr_count($stat2, '@') > 0) {
			$_count = array();
			$stats = explode('@', $stat2);
			foreach($stats as $val) {
				$_count[$val]++;
			}
			$stats = array_unique($stats);
			foreach($stats as $key => $val) {
				$stats[$key] = getOrdStat($data, $val)."($_count[$val])";
			}
			$stat = implode('</li><li>', $stats);
		}
		else {
			$stat=getOrdStat($data).'('.count(explode('@', $stat2)).')';
		}
		$data['stat']=$stat;

		if($data['aff']) {
			$data['title']="[".$cfg['aff_name']."] ".$data['title'];
		}

		if(!$cut_title) {
			$cut_title=25;
			if($cfg['bank_name']=="Y") {
				$cut_title-=5;
			}
			if($cfg['bank_price']=="Y"){
				$cut_title-=8;
			}
		}

		if($_use['recom_member']=="Y" && $data['member_no']) {
			$data['recom_member'] = $pdo->row("select recom_member from {$tbl['member']} where no=?", array($data['member_no']));
		}

		if($data['member_id']) {
            // 리스트에서 개인정보 마스킹
            $data['member_id_v'] = $data['member_id'];
            $data['buyer_name_v'] = $data['buyer_name'];
            if ($scfg->comp('use_order_list_protect', 'Y') == true) {
                $data['buyer_name_v'] = strMask($data['buyer_name'], 2, '＊');
                $data['member_id_v'] = strMask($data['member_id'], 5, '***');
            }
			$data['buyer_name'] = "{$data['buyer_name_v']}({$data['member_id_v']})";
            if ($admin['level'] < 4) {
                $data['buyer_name'] = "<a href=\"javascript:\" onmouseover=\"showToolTip(event,'회원 정보')\" onmouseout=\"hideToolTip();\" onClick=\"viewMember('{$data['member_no']}','{$data['member_id']}');return false\">{$data['buyer_name']}</a>";
            }
		}

		switch($data['postpone_yn']) {
			case 'Y' : $data['postpone_yn'] = '<span class="p_color2">전체보류</span>'; break;
			case 'B' : $data['postpone_yn'] = '<span class="p_color5">부분보류</span>'; break;
			default  : $data['postpone_yn'] = '';
		}

		return $data;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string getOrdStat(array 주문데이터, int 주문상태) - 주문상태 시각화 및 배송추적 링크 삽입
	' +----------------------------------------------------------------------------------------------+*/
	function getOrdStat($data,$ostat="") {
		global $_order_stat, $_order_stat_sbscr, $_order_color;
		if(!$ostat) {
			$ostat=$data['stat'];
		}
		$r=$_order_stat[$ostat];
        if (isset($data['sbono']) == true && $data['pay_type'] == '23') {
            $r = str_replace($_order_stat[2], $_order_stat_sbscr[2], $r);
        }

		if($_order_color[$ostat]) {
			$r="<span style='color:$_order_color[$ostat] !important;'>".$r."</span>";
		}

		$data['dlv_code'] = str_replace('-', '', $data['dlv_code']);
		$dlv=getDlvUrl($data);

		if($data['dlv_code']) {
			$r="<a href=\"$dlv[url]\" target=\"_blank\">".$r."</a>";
		} else if ($data['stat'] == '41') { // 동명이인입금
            $bank = explode(' ', $data['bank']);
            $r = sprintf(
                "<a href='#' onclick=\"goMywisa('?body=support@service@bank_list&acc_no=%s&name=%s&bkinput=%s&dates=%s'); return false;\">$r</a>",
                str_replace('-', '', $bank[1]), $data['bank_name'], parsePrice($data['pay_prc']), date('Y-m-d', $data['date1'])
            );
        }

		return $r;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string align(strng 코드) - align 태그 출력
	' +----------------------------------------------------------------------------------------------+*/
	function align($code) {
		global $cfg;
		if($cfg[$code]) {
			return "align=\"".$cfg[$code]."\"";
		}
	}

	function setTotalPrds($_cate,$utd="") {
        global $pdo;

		if($_cate['ctype']==2) {
			$w="`".$GLOBALS['_cate_colname'][$_cate['ctype']][$_cate['level']]."` like '%@$_cate[no]%'";
		}
		else {
			$w="`".$GLOBALS['_cate_colname'][$_cate['ctype']][$_cate['level']]."`='$_cate[no]'";
		}

		$r=$pdo->row("select count(*) from `".$GLOBALS['tbl']['product']."` where $w");
		if($utd) {
			$pdo->query("update `".$GLOBALS['tbl']['category']."` set `total_item`='$r' where `no`='$_cate[no]'");
		}

		return $r;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  void newSvc(void) - 페이지 업데이트 경고 출력
	' +----------------------------------------------------------------------------------------------+*/
	function newSvc() {
		echo "<ul class='desc1 square'><li>본 기능을 사용하기 위해서는 디자인관리에서 쇼핑몰 페이지에서 쿠폰이 노출되어야 합니다.</li></ul>";
	}


	/* +----------------------------------------------------------------------------------------------+
	' |  string getYoil(string 날짜[, int 옵션]) - 날짜값으로 한글 요일을 출력
	' +----------------------------------------------------------------------------------------------+*/
	$yoil=$_kdate;
	function getYoil($date,$type="") {
		global $_kdate;
		if($type==1) {
			$_dt=explode("-",$date);
			$date=mktime(0, 0, 0, $_dt[1], $_dt[2], $_dt[0]);
		}
		$d=date("w",$date);
		$y=$yoil[$d];
		return $y;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string linkPGAdmin(string PG코드) - 각 PG별 관리자 페이지 링크주소 반환
	' +----------------------------------------------------------------------------------------------+*/
	function linkPGAdmin($pg) {
		switch($pg) {
			case 'allat':
				$pg_link="https://cp.mcash.co.kr/mcht/login.jsp";
			break;
			case 'inicis':
				$pg_link="https://iniweb.inicis.com/security/login.do";
			break;
			case 'dacom':
				$pg_link="https://www.tosspayments.com/";
			break;
			case 'allthegate':
				$pg_link="https://admin7.allthegate.com/chaMng/login/login.jsp";
			break;
			case 'paypal':
				$pg_link="https://www.paypal.com/home";
			break;
			case 'alipay':
				$pg_link="https://www.alipay.com/";
			break;
			case 'cyrexpay':
			case 'paypal_c':
				$pg_link="https://pg.cyrexpay.com/";
			break;
			case 'wechat':
				$pg_link="https://merchant.eximbay.com/backoffice/common/login.do";
			break;
			case 'sbi':
				$pg_link="";
			break;
			case 'tosspay':
				$pg_link="http://pay.toss.im/paybo/app/";
			break;
			case 'nicepay':
				$pg_link="https://npg.nicepay.co.kr/logIn.do";
			break;
			default:
				$pg_link="https://admin8.kcp.co.kr/";
			break;
		}

		return $pg_link;
	}


	/* +----------------------------------------------------------------------------------------------+
	' |  string cnvTip(string 텍스트, int 툴팁길이) - 텍스트를 js에서 사용가능한 툴팁텍스트로 변환
	' +----------------------------------------------------------------------------------------------+*/
	function cnvTip($content,$cut=0) {
		$content=stripslashes($content);
		if($cut>0) $content=cutStr($content,$cut,"");
		$content=strip_tags($content);
		$content=nl2br($content);
		$content=addslashes($content);
		$content=str_replace("\r","\\\r",$content);

		return $content;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string dateSelectBox(int 시작값, Int 끝값, string 셀렉트명, string 선택된값) - 날짜 셀렉트박스 출력
	' +----------------------------------------------------------------------------------------------+*/
	function dateSelectBox($st,$fn,$name,$sel,$blank="",$oc="",$trm=0,$style="") {
		$str="<select name=\"$name\" onChange=\"$oc\" style=\"$style\">";
		if($blank) $str.="<option value=\"\">$blank</option>";
		for($ii=$st; $ii<=$fn; $ii++) {
			if($ii<10) $k="0".$ii;
			else $k=$ii;
			$check=checked($sel,$k,1);
			$str.="<option value=\"$k\" $check>$k</option>";
			if($trm>0) {
				$ii+=$trm-1;
			}
		}
		$str.="</select>";
		return $str;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  void cancelPoint(void) - 사용 포인트를 취소/복구
	' +----------------------------------------------------------------------------------------------+*/
	function cancelPoint(){
		global $tbl,$data,$ext,$ext_stat;
		$_ext=$ext;
		if(!$_ext) $_ext=$ext_stat;
		if(!$data['member_no'] || !$data['point_use']) return;
		$_mem=get_info($tbl['member'], 'no', $data['member_no']);
		if($_ext == 13 || $_ext == 15 || $_ext == 17){
			if(function_exists("usePoint")) usePoint($data['order_gift'], $_mem, 'Y', $data['point_use']);
		}
		if(($data['stat'] == 13 || $data['stat'] == 15 || $data['stat'] == 17) && $_ext < 10){
			if(function_exists("usePoint")) usePoint($data['order_gift'] ,$_mem, '', $data['point_use']);
		}
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string getcatecode(int 카테고리레벨) - 카테고리 등급에 따른 필드명(big/mid/small) 반환
	' +----------------------------------------------------------------------------------------------+*/
	function getcatecode($level) {
		return $GLOBALS['_cate_colname'][1][$level];
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string mate_tree(int 카테고리 번호) - 현재 카테고리 트리를 단계 출력
	' +----------------------------------------------------------------------------------------------+*/
	function make_tree($no){
		global $tbl, $cfg, $pdo;

		$asql = '';
		if($cfg['max_cate_depth'] > 3) {
			$addfield = ', small';
		}

		list($name, $big, $mid, $level, $small) = $pdo->assoc("select name, big, mid, level $addfield from $tbl[category] where no='$no'");
		if(!$small) $small = 0;

		$tree = "<a href='javascript:moveCat(0)'>전체</a>";

		$res = $pdo->iterator("select no, name from $tbl[category] where no in ('$big', '$mid', '$small', '$no') order by level asc");
        foreach ($res as $data) {
			$data['name'] = stripslashes($data['name']);
			$tree .= " &gt; <a href='javascript:moveCat($data[no])'><strong style='color:$color'>$data[name]</strong></a>";
			javac("open_cat($data[no],1)");
		}

		return $tree;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string print_cat(int 부모카테고리 번호, int 출력할 카테고리 레벨) - 카테고리 트리구조를 출력(재귀호출)
	' +----------------------------------------------------------------------------------------------+*/
	function print_cat($parent=0, $level=1){
		global $tbl, $engine_url, $ctype, $pdo;

		switch ($level) {
			case "2" : $pcode = "big"; break;
			case "3" : $pcode = "mid"; break;
			case "4" : $pcode = "small"; break;
		}

		if ($pcode) $psearch = " and `$pcode`='$parent'";

		$qry = "select * from `$tbl[category]` where `ctype`='$ctype' and `level`='$level' $psearch order by `sort`";
		$res = $pdo->iterator($qry);

		$display = ($parent == 0) ? "block" : "none";
		$space = ($parent == 0) ? "0" : "15px";
		$level++;

		echo "<ul id='cat_$parent' class='catelist' style='display:$display; padding-left:$space'>";

		if ($parent == 0) {
			if($_GET['body'] == 'wmb@category_config') $addStyle="style=\"height:30px;padding:10px 5px 0 5px;\"";
			echo ("
				<li id='div_0' class='cat_item'>
					<img id='folder_$no' src='$engine_url/_manage/image/icon/ic_folder_o.gif'> <a href='javascript:moveCat(0)' id='item_0'>전체</a>
				</li>\n
			");
		}

        foreach ($res as $data) {
			$name = stripslashes($data['name']);

			if ($level == 5) {
				$folder = "ic_folder_o.gif";
				$plus = "ic_minus.gif";
				$link = "";
			} else {
				$folder = "ic_folder_c.gif";
				$plus = "ic_plus.gif";
				$link = "onClick='open_cat($data[no])' ";
			}

			if($_GET['body'] == 'wmb@category_config') { // 윙Mobile 2013-01-16 cham

				$mobile_on=($data['mobile_hidden']=='N') ? 'On' : '';

				$bgcolor=($i %2 ==1) ? "#f6f6f6" : "#fff";
				echo ("
					<input type='hidden' id=\"no{$data['no']}\"  name=\"no[{$data['no']}]]\" value=\"{$data['mobile_hidden']}\">
					<li id='div_$data[no]' class='cat_item' style=\"background:$bgcolor;padding:10px 5px 0 5px;height:30px;border-top:1px dotted #d8d8d8;\">
						<img id='ic_$data[no]' src='$engine_url/_manage/image/icon/$plus' $link class='p_cursor'>
						<img id='folder_$data[no]' src='$engine_url/_manage/image/icon/$folder'>
						<a id='name_$data[no]' href='javascript:moveCat($data[no], \"M\")' id='item_$data[no]' title='CODE:$data[no]'>$name</a>
						<div style='float:right'>
							<div id=\"img{$data['no']}\" class=\"p_cursor m_category{$mobile_on}\" onclick=\"javascript:mobileSelectCat($data[no])\" style=\"position:relative;top:2px;\"></div>
						</div>
					</li>\n
				");
			} else {

				echo ("
					<li id='div_$data[no]' class='cat_item'>
						<img id='ic_$data[no]' src='$engine_url/_manage/image/icon/$plus' $link class='p_cursor'>
						<img id='folder_$data[no]' src='$engine_url/_manage/image/icon/$folder'>
						<a id='name_$data[no]' href='javascript:moveCat($data[no])' id='item_$data[no]' title='CODE:$data[no]'>$name</a>
					</li>\n
				");
			}

			print_cat($data['no'],$level,$ctype);
		}
		echo ("</ul>\n");
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  void delete_log(string 삭제위치, int 삭제번호, string 상세내역) - 상품/회원,주문 삭제 내역을 저장
	' +----------------------------------------------------------------------------------------------+*/
	function delete_log($type, $deleted, $title) {
		global $admin, $tbl, $now, $engine_dir, $pdo;
		$title = addslashes($title);
		$pdo->query ("insert into `$tbl[delete_log]`	 (`type`,`deleted`,`title`,`admin`,`deldate`) values ('$type','$deleted','$title','$admin[admin_id]','$now')");
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  bool productLogw(int 상품번호, string 상품명, int 변경할 상태값) 상품 상태변경 로그 저장
	' +----------------------------------------------------------------------------------------------+*/
	function productLogw($pno,$pname,$stat){
		global $tbl,$admin,$now,$engine_dir, $pdo;
		$sql="insert into `$tbl[product_log]`(`pno`, `pname`, `stat`, `admin_id`, `admin_no`, `ip`, `reg_date`) values('$pno', '$pname', '$stat', '$admin[admin_id]', '$admin[no]', '$_SERVER[REMOTE_ADDR]', '$now')";
		$r=$pdo->query($sql);
		return $r;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string mySearchSet(string 출력타입) - 검색폼 개인화
	' +----------------------------------------------------------------------------------------------+*/
	function mySearchSet($type=""){
		global $admin;
		$ordersearch=$admin['ordersearch'] ? $admin['ordersearch'] : "ostat:<wisa>period:1<wisa>period_all:<wisa>paytype:<wisa>orderby:1<wisa>search:buyer_name<wisa>sort_fd:10<wisa>";
		$membersearch=$admin['membersearch'] ? $admin['membersearch'] : "milage_up:10000<wisa>milage_limit:500000<wisa>visit_up:10<wisa>visit_limit:500<wisa>order_up:10<wisa>order_limit:100<wisa>prc_up:100000<wisa>prc_limit:5000000<wisa>search:name<wisa>";
		$value=explode("<wisa>", ${$type});
		$re=array();
		foreach($value as $val){
			$value2=explode(":", $val);
			$re[$value2[0]]=$value2[1];
		}
		return $re;
	}

	$_conversion_list = array(
        'wingkr' => 'wingkr',
		'naver_cbox' => '네이버검색광고',
		'naver_tbox_a' => '네이버 테마쇼핑 a',
		'naver_tbox_b' => '네이버 테마쇼핑 b',
		'naver_tbox_c' => '네이버 테마쇼핑 c',
		'naver_is' => '네이버 지식쇼핑',
		'google' => 'GDN',
		'daum_bbox' => '다음 쇼핑박스',
		'daum_show' => '다음 쇼핑하우',
		'daum_sbox' => '다음 소호박스',
		'daum_clicks' => '다음클릭스',
		'nate_box1' => '네이트 쇼핑박스 1탭',
		'nate_box2' => '네이트 쇼핑박스 2탭',
		'nate_box4' => '네이트 쇼핑박스 4탭',
        'criteo' => '크리테오',
        'wsmk_zigzag' => '지그재그',
	);

	/* +----------------------------------------------------------------------------------------------+
	' |  string dispConversion(string 컨버젼데이터) - 리스트 내 컨버전 내역을 아이콘으로 표시
	' +----------------------------------------------------------------------------------------------+*/
	function dispConversion($conv) {
		global $engine_url, $_conversion_list, $conv_ic_cache, $conv_nm_cache, $tbl, $pdo;

		if(!$conv_ic_cache) {
			$conv_ic_cache = array();
			$res = $pdo->iterator("select `name`,`code`,`icon` from `$tbl[pbanner_group]`");
            foreach ($res as $pb) {
				$code = $pb['code'];
				$icon = $pb['icon'];
				$conv_ic_cache[$code] = $icon;
				$conv_nm_cache[$code] = stripslashes($pb['name']);
			}
		}

		$conv = explode("@", $conv);
		foreach ( $conv as $val) {
			if(!$val) continue;

			if (array_key_exists($val, $_conversion_list)) {
				$str .= "\n<img src='$engine_url/_manage/image/icon/ic_conv_{$val}.gif' class='tolltip_event' title='{$_conversion_list[$val]}' alt='{$_conversion_list[$val]}'>";
			} elseif (preg_match('/^wsmk_/', $val)) {
				$code = preg_replace('/^wsmk_/', '', $val);
				if($conv_ic_cache[$code]) {
					$str .= "\n<img src='$engine_url/_manage/image/icon/$conv_ic_cache[$code]' class='tolltip_event' title='{$conv_nm_cache[$code]}' alt='{$conv_nm_cache[$code]}'>";
				}
			} elseif (preg_match('/^naver_cbox_[0-9]+$/', $val) || preg_match('/^naver_tbox_[a-z]$/', $val)) {
				$tval = preg_replace('/_[^_]+$/', '', $val);
				$tnum = preg_replace('/^.*_/', '', $val);
				$str .= "\n<img src='$engine_url/_manage/image/icon/ic_conv_{$tval}.gif' class='tolltip_event' title='{$_conversion_list[$tval]}' alt='{$_conversion_list[$tval]}'>";
			}
		}
		?>
		<script type='text/javascript'>
			$(document).ready(function(){
			  $(".tolltip_event").tooltip();
			});
		</script>
		<?php
		return $str;
	}

    function dispConversionText($conv)
    {
        global $pdo, $tbl, $_conversion_list, $conv_nm_cache;

        // make cache
		if (isset($conv_nm_cache) == false || is_array($conv_nm_cache) == false) {
			$conv_nm_cache = array();
			$res = $pdo->iterator("select name, code from {$tbl['pbanner_group']}");
            foreach ($res as $pb) {
				$code = $pb['code'];
				$conv_nm_cache[$code] = stripslashes($pb['name']);
			}
		}

        // get promotion
        $promotions = array();
        $conv = explode('@', $conv);
        foreach ($conv as $code) {
            if (preg_match('/^wsmk_([0-9]+)/', $code, $tmp) == true) {
                $promotions[] = $conv_nm_cache[$tmp[1]];
            } else if (array_key_exists($code, $_conversion_list) == true) {
                $promotions[] = $_conversion_list[$code];
            }
        }

        return implode(', ', $promotions);
    }

	/* +----------------------------------------------------------------------------------------------+
	' |  string selectArrayConv(string 컨버젼데이터, int 기본/추가프로모션여부) - 구매전환 체크박스 리스트 출력
	' +----------------------------------------------------------------------------------------------+*/
	function selectArrayConv($variable, $type = 1) {
		global $_conversion_list, $engine_url, $engine_dir, $tbl, $pdo;

		$search = $GLOBALS[$variable];

		if($type == 1) {
			foreach ($_conversion_list as $key => $val) {
				if (is_array($search)) $ck = in_array($key, $search) ? "checked" : "";
				$icon = file_exists($engine_dir.'/_manage/image/icon/ic_conv_'.$key.'.gif') ? "<img src='$engine_url/_manage/image/icon/ic_conv_{$key}.gif' style='margin-top:-2px; vertical-align:middle;'>" : "";
				$list .= "<li><label class='p_cursor'><input type='checkbox' class='$variable$type' name='{$variable}[]' value='$key' $ck> $icon $val</label></li>\n";
			}
		}

		// 2010-01-19 광고배너리스트에서 읽어오기
		if($type == 2) {
			$res = $pdo->iterator("select `name`,`code`,`icon` from `$tbl[pbanner_group]` order by `no` asc");
            foreach ($res as $data) {
				if (is_array($search)) $ck = in_array('wsmk_'.$data['code'], $search) ? "checked" : "";
				$list .= "<li><label><input type='checkbox' class='$variable$type' name='{$variable}[]' value='wsmk_{$data['code']}' $ck> <img src='$engine_url/_manage/image/icon/{$data['icon']}' /> {$data['name']}</label></li>\n";
			}
		}

		if($list) {
			$list .= "<li><label><input type='checkbox' onclick=\"$('.$variable$type').attr('checked',this.checked)\"> 전체선택</label><li>";
			$list = "<ul id=\"conversion_list\" class=\"list\">\n$list</ul>";
		}

		return $list;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string hideBarcode(string 바코드) - 바코드 뒷자리 숨김
	' +----------------------------------------------------------------------------------------------+*/
	function hideBarcode($barcode) {
		return $barcode;

		if($GLOBALS['wp_stat'] == 3) return $barcode;
		else {
			return preg_replace('/.{7}$/', '*******', $barcode);
		}
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string makeOrdNo(string 주문번호앞자리) - 새로운 주문번호 생성
	' +----------------------------------------------------------------------------------------------+*/
	function makeOrdNo($ono1 = null) {
		global $now, $tbl, $member, $onow, $pdo;
		if(!$ono1) $ono1=date("Ymd", $now);

		$mr=mt_rand();
		if(!$onow) {
			$onow=$now;
		}
		$onow++;

		$ono2=strtoupper(substr(md5($now+$mr+$member['no']),1,5));
		$tmp=$pdo->row("select `no` from `$tbl[order_no]` where `ono1`='$ono1' and `ono2`='$ono2'");
		if($tmp) return false;
		else {
			$r = $pdo->query("insert into `$tbl[order_no]` (`ono1`,`ono2`) values ('$ono1','$ono2')");
			if($r) return $ono1."-".$ono2;
			else return false;
		}
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  int deleteMember(int 삭제할회원코드) - 선택한 회원 및 회원 게시물 삭제
	' +----------------------------------------------------------------------------------------------+*/
	function deleteMember($check_pno) {
		global $tbl, $engine_dir, $now, $cfg, $body, $pdo;

		$cnt = count($check_pno);
		$mnos = implode(',', numberOnly($check_pno));

        if (!$mnos) return false;

		if($body == 'main@main' || $body == 'member@member_list') {
			$_POST['del_option1'] = $cfg['del_option1'];
			$_POST['del_option2'] = $cfg['del_option2'];
			$_POST['del_option3'] = $cfg['del_option3'];
			$_POST['del_option4'] = $cfg['del_option4'];
		}

		if($cnt > 0) {
            $mids = $pdo->iterator("select member_id from {$tbl['member']} where no in ($mnos)");
            $member_ids = '';
            foreach ($mids as $mid) {
                $member_ids .= '\''.$mid['member_id'].'\',';
            }
            $member_ids = substr($member_ids, 0, -1);

			if($_POST['del_option1'] == '1') $pdo->query("delete from `$tbl[review]` where `member_no` in ($mnos)");
			if($_POST['del_option2'] == '1') $pdo->query("delete from `$tbl[qna]` where `member_no` in ($mnos)");
			if($_POST['del_option3'] == '1') $pdo->query("delete from `$tbl[order]` where `member_no` in ($mnos)");
			if($_POST['del_option4'] == '1') $pdo->query("delete from `$tbl[cs]` where `member_no` in ($mnos)");

			$pdo->query("delete from `$tbl[wish]` where `member_no` in ($mnos)");
			$pdo->query("delete from `$tbl[member]` where `no` in ($mnos)");
			$pdo->query("delete from {$tbl['member_deleted']} where no in ($mnos)");
			$pdo->query("delete from `$tbl[milage]` where `member_no` in ($mnos)");
			$pdo->query("delete from `$tbl[sns_join]` where `member_no` in ($mnos)");
            $pdo->query("delete from {$tbl['order_memo']} where ono in ($member_ids) and type='2'"); // 회원메모 삭제

			if($cfg['use_biz_member']=="Y") {
				$pdo->query("delete from `$tbl[biz_member]` where `ref` in ($mnos)");
			}
		}
		return $cnt;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  void deleteAuto(void) 탈퇴요청 회원 자동 삭제
	' +----------------------------------------------------------------------------------------------+*/
	function deleteAuto() {
		global $tbl, $engine_dir, $now, $cfg, $pdo;

		if($cfg['withdrawal'] < 1) return;

		$ck = $pdo->row("select `value` from `$tbl[default]` where code='once_date'");
		if($ck != date('Ymd')) {
			if(!$ck) $pdo->query("insert into `$tbl[default]` values ('once_date', '$now','')");
			else $pdo->query("update `$tbl[default]` set `value` = ".date('Ymd')." where code='once_date'");
		}
		$del_time = $now - (86400* $cfg['withdrawal']);

		$check_pno = array();
		$res = $pdo->iterator("select * from `$tbl[member]` where `withdraw` = 'Y' and substr(`withdraw_content`,-10) <= $del_time");
        foreach ($res as $del) {
			$check_pno[] = $del['no'];
		}
		if(count($check_pno) > 0) deleteMember($check_pno);
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string blackIconPrint(int 아이콘출력여부, array 회원데이터) - 회원 블랙리스트 여부를 아이콘으로 출력
	' +----------------------------------------------------------------------------------------------+*/
	function blackIconPrint($blacklist="", $data="") {
		global $tbl, $engine_url, $mchecker, $checker_sql, $pdo;

		if(!$blacklist && $data) {
			if(!is_array($mchecker)) {
				$mchecker = array();
				if(isTable($tbl['member_checker'])) {
					$mcres = $pdo->iterator("select no, name from `$tbl[member_checker]`");
                    foreach ($mcres as $mcdata) {
						$mchecker[$mcdata['no']] = stripslashes($mcdata['name']);
						$checker_sql .= ', checker_'.$mcdata['no'];
					}
				}
			}

			if($data['member_no'] < 1) return '';
			$amember = $pdo->assoc("select `no`, `blacklist` $checker_sql from `$tbl[member]` where `no` = '$data[member_no]'");
			$blacklist = $amember['blacklist'];
			$cdata = trim(implode(',', getMemberChecker($amember)));
		}
		$icon = ($blacklist=='1') ? "<img src=$engine_url/_manage/image/blacklist.gif>" : "";
		if($cdata) $icon .= " <img id='blacklit{$amember['no']}' src='$engine_url/_manage/image/icon/ic_spmember.gif' class='R2Tip' alt='$cdata' />";

		return $icon;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string nformat(int 숫자값) - 숫자를 단위수에 따라 다른 색으로 표시
	' +----------------------------------------------------------------------------------------------+*/
	function nformat($str) {
		$_color = array('', '#999', '#333', '#4481ff', '#ff1111');

		$str = number_format($str);
		$str = explode(',', $str);
		$tmp = array();
		foreach($str as $key => $val) {
			$color = $_color[(count($str)-$key)];
			$tmp[] = "<span style='color:$color;'>$val</span>";
		}

		return implode(',', $tmp);;
	}


	/* +----------------------------------------------------------------------------------------------+
	' |  string getMemberChecker(array 회원정보);
	' +----------------------------------------------------------------------------------------------+*/
	function getMemberChecker($amember) {
		global $mchecker;

		if(count($mchecker) < 1) return array();

		$data = array();
		foreach($mchecker as $key => $val) {
			if($amember['checker_'.$key] == 'Y') $data[] = $val;
		}

		return $data;
	}

	function getMngNameCache(){
		global $cfg, $tbl, $pdo;

		$str = array();
		$res = $pdo->iterator("select `name`, `admin_id` from `".$tbl['mng']."` order by `no`");
        foreach ($res as $data) {
			$str[$data['admin_id']] = $data['name'];
		}
		return $str;
	}

	function getMemberMilagePer($level) {
		global $cfg, $tbl, $pdo;

		if($cfg['member_event_use'] == 'Y' && ($cfg['member_event_type'] == 1 || $cfg['member_event_type'] == 3)) {
			$per = $pdo->row("select milage2 from $tbl[member_group] where no='$level'");
			if(!$per) $per = 0;
			return $per;
		}
		return 0;
	}

	function parseOrderOption($str, $split1 = ' / ', $split2 = ' : ') {
		$str = str_replace('<split_big>', $split1, $str);
		$str = str_replace('<split_small>', $split2, $str);

		return $str;
	}

	function getPartnerName($partner_no) {
		global $_partner_name_cache, $pdo;

		if(!$partner_no) return;
		if($_partner_name_cache[$partner_no]) {
			return $_partner_name_cache[$partner_no];
		}

		$name = $pdo->row("select corporate_name from {$GLOBALS['tbl']['partner_shop']} where no='$partner_no'");
		$name = stripslashes($name);
		$_partner_name_cache[$partner_no] = $name;

		return $name;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  void trncTrash(void) - 휴지통 정리 (메인 및 각 휴지통 메뉴에서 체크)
	' +----------------------------------------------------------------------------------------------+*/
	function trncTrash() {
		global $tbl, $root_dir, $cfg, $pdo;

		if($cfg['use_trash_prd'] == 'Y' && $cfg['trash_prd_trcd'] > 0) {
			$timestamp = strtotime('- '.$cfg['trash_prd_trcd'].' days');
			$res = $pdo->iterator("select no from $tbl[product] where stat=5 and del_date<='$timestamp'");
            foreach ($res as $data) {
				delPrd($data['no']);
			}
		}

		if($cfg['use_trash_ord'] == 'Y' && $cfg['trash_ord_trcd'] > 0) {
			$timestamp = strtotime('- '.$cfg['trash_ord_trcd'].' days');
			$res = $pdo->iterator("select ono from $tbl[order] where stat=32 and del_date<='$timestamp'");
            foreach ($res as $data) {
				delOrd($data['ono']);
			}
		}

		if($cfg['use_trash_bbs'] == 'Y' && $cfg['trash_bbs_trcd'] > 0) {
			$timestamp = strtotime('- '.$cfg['trash_bbs_trcd'].' days');
			$res = $pdo->iterator("select no, data from $tbl[common_trashbox] where tblname='mari_board' and del_date<='$timestamp'");
            foreach ($res as $data) {
				$tmp = unserialize($data['data']);
				for($i = 1; $i <= 2; $i++) {
					deleteAttachFile('board/'.$tmp['up_dir'], $tmp['upfile'.$i]);
				}
				$pdo->query("delete from $tbl[common_trashbox] where no='$data[no]'");
				$pdo->query("delete from mari_comment where ref='$tmp[no]'");
			}
		}

		if($cfg['use_trash_qna'] == 'Y' && $cfg['trash_qna_trcd'] > 0) {
			$timestamp = strtotime('- '.$cfg['trash_qna_trcd'].' days');
			$res = $pdo->iterator("select no, data from $tbl[common_trashbox] where tblname='$tbl[qna]' and del_date<='$timestamp'");
            foreach ($res as $data) {
				$tmp = unserialize($data['data']);
				for($i = 1; $i <= 2; $i++) {
					deleteAttachFile($tmp['updir'], $tmp['upfile'.$i]);
				}
				$pdo->query("delete from $tbl[common_trashbox] where no='$data[no]'");
			}
		}

		if($cfg['use_trash_rev'] == 'Y' && $cfg['trash_rev_trcd'] > 0) {
			$timestamp = strtotime('- '.$cfg['trash_rev_trcd'].' days');
			$res = $pdo->iterator("select no, data from $tbl[common_trashbox] where tblname='$tbl[review]' and del_date<='$timestamp'");
            foreach ($res as $data) {
				$tmp = unserialize($data['data']);
				for($i = 1; $i <= 2; $i++) {
					deleteAttachFile($tmp['updir'], $tmp['upfile'.$i]);
				}
				$pdo->query("delete from $tbl[common_trashbox] where no='$data[no]'");
				$pdo->query("delete from $tbl[review_comment] where ref='$tmp[no]'");
			}
		}
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  휴지통의 상품 수 노출
	' +----------------------------------------------------------------------------------------------+*/
    function getTrashBoxRows($box) {
		global $tbl, $pdo, $scfg;

        $cnt = 0;
		switch($box) {
			case 'product' :
				$cnt = $pdo->row("select count(*) from $tbl[product] where stat=5");
			break;
			case 'order' :
				$cnt = $pdo->row("select count(*) from $tbl[order] where stat=32");
			break;
			case 'board' :
                if ($scfg->comp('use_trash_bbs', 'Y')) {
				    $cnt = $pdo->row("select count(*) from $tbl[common_trashbox] where tblname='mari_board'");
                }
			break;
			case 'review' :
                if ($scfg->comp('use_trash_bbs', 'Y')) {
				    $cnt = $pdo->row("select count(*) from $tbl[common_trashbox] where tblname='$tbl[review]'");
                }
			break;
			case 'qna' :
                if ($scfg->comp('use_trash_bbs', 'Y')) {
    				$cnt = $pdo->row("select count(*) from $tbl[common_trashbox] where tblname='$tbl[qna]'");
                }
			break;
		}
		if($cnt >= 1000) $cnt = '999+';

		return $cnt;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  string couponTpName(string 쿠폰타입코드) - 쿠폰타입코드를 쿠폰타임명으로 변환
	' +----------------------------------------------------------------------------------------------+*/
	function couponTpName($tp="", $cpn = null){
		if(is_array($cpn)) {
			if($cpn['is_birth'] == 'Y') return '생일쿠폰';
		}
	    $arr=array(__lang_common_info_cpnA__);
		if($tp == "A") return __lang_common_info_cpnA__;
		if($tp == "B") return __lang_common_info_cpnB__;
		if($tp == "C") return __lang_common_info_cpnC__;
		if($tp == "D") return __lang_common_info_cpnD__;
		if($tp == "E") return __lang_common_info_cpnC__.' (APP)';
		if($tp == "F") return '구매 완료 시 자동발급';
		if($tp == "G") return '첫구매 완료 시 자동발급';
		if($tp == "L") return '로그인 시 자동발급';
		if($tp == "L2") return '로그인 시 자동발급 (APP)';
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  boolean getIsCardTest() - 현재 PG가 테스트 모드 상태인지 확인합니다. true=테스트
	' +----------------------------------------------------------------------------------------------+*/
	function getIsCardTest($type = 'all') {
		global $cfg;

		$pc = (
			($cfg['card_pg'] == 'dacom' && $cfg['card_test'] == 'Y') ||
			($cfg['card_pg'] == 'kcp' && $cfg['card_test'] == '_test') ||
			($cfg['card_pg'] == 'allat' && $cfg['card_test'] == 'Y') ||
			($cfg['card_pg'] == 'inicis' && $cfg['card_test'] == 'Y')
		);
		if($cfg['mobile_use'] == 'Y') {
			$mobile = (($cfg['card_mobile_pg'] == 'dacom' && $cfg['card_mobile_test'] == 'Y') ||
					($cfg['card_mobile_pg'] == 'kcp' && $cfg['card_mobile_test'] == '_test') ||
					($cfg['card_mobile_pg'] == 'inicis' && $cfg['card_mobile_test'] == 'Y')
			);
		} else {
			$mobile = false;
		}
		$all = ($pc || $mobile);
		return ${$type};
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  개인정보 접속기록
	' +----------------------------------------------------------------------------------------------+*/
	function addPrivacyViewLog($data) {
		global $admin, $now, $engine_dir, $tbl, $pdo;

		if(!isTable($tbl['privacy_view_log'])) {
			include_once $engine_dir."/_config/tbl_schema.php";
			$pdo->query($tbl_schema['privacy_view_log']);
		}

		//if(preg_match('/^118\.129\.243\./', $_SERVER['REMOTE_ADDR'])) return;

		$admin_no = $admin['no'];
		$admin_id = $admin['admin_id'];
		$page_id = $data['page_id'];
		$page_type = $data['page_type'];
		$target_id = ($data['target_id']) ? $data['target_id'] : 'guest';
		$target_cnt = ($data['target_cnt']) ? $data['target_cnt'] : 1;

		$pdo->query(trim("
			insert into $tbl[privacy_view_log] (admin_no, admin_id, page_id, page_type, target_id, target_cnt, reg_date, ip)
			values ('$admin_no', '$admin_id', '$page_id', '$page_type', '$target_id', '$target_cnt', '$now', '$_SERVER[REMOTE_ADDR]')
		"));
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  열람기록삭제
	' +----------------------------------------------------------------------------------------------+*/
	function deletePrivacyViewLog() {
		global $tbl, $now, $engine_dir, $pdo;

		if(!isTable($tbl['privacy_view_log'])) {
			include_once $engine_dir."/_config/tbl_schema.php";
			$pdo->query($tbl_schema['privacy_view_log']);
		}

		$sixmonth = strtotime("-2 years", $now);
		$pdo->query("delete from $tbl[privacy_view_log] where reg_date<='$sixmonth'");
	}

	function setOrderColor() {
		global $cfg, $_order_color_def;

		foreach($_order_color_def as $i => $val) {
			$_order_color[$i]=($cfg['order_color'.$i]) ? $cfg['order_color'.$i] : $_order_color_def[$i];
		}

		return $_order_color;
	}

?>