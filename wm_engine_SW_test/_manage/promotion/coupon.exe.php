<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  쿠폰 처리
	' +----------------------------------------------------------------------------------------------+*/

    use Wing\common\WorkLog;

	set_time_limit(0);

	printAjaxHeader();
	checkBasic();

	$is_type = (addslashes($_POST['is_type']) == 'B') ? 'B' : 'A';
	$down_type = addslashes($_POST['down_type']);

	// down_type 매칭
	if($is_type == 'A') {
		$tmp_down_type = addslashes($_POST['tmp_down_type']);
		$tmp_sub_type = addslashes($_POST['tmp_sub_type'.$tmp_down_type]);
		$down_type = $tmp_sub_type;
		if($tmp_down_type == 3) $down_type = 'D';
		if($tmp_down_type == 4) {
			$down_type = addslashes($_POST['tmp_sub_type1']);
			$is_birth = 'Y';
		}
		if($down_type == 'C' && $_POST['tmp_is_app'] == 'Y') $down_type = 'E';
		if($down_type == 'L' && $_POST['tmp_is_app'] == 'Y') $down_type = 'L2';
	}

	function couponLogw($cno,$cname,$stat,$is_type="", $content="") {
		global $tbl, $admin, $pdo;

		return $pdo->query("
            insert into {$tbl['coupon_log']}
            (cno, cname, is_type, stat, admin_id, admin_no, content, ip, reg_date)
            values (
                '$cno', '$cname', '$is_type', '$stat',
                '{$admin['admin_id']}', '{$admin['no']}', '$content', '{$_SERVER['REMOTE_ADDR']}', unix_timestamp(now())
            )
        ");
	}

	function makeCode($no) {
		global $_auth_code;
		$rand = mt_rand();
		$tmp = md5($rand);
		$tmp_code = substr($tmp,0,10);
		$tmp_code = $no."-".strtoupper($tmp_code);
		if(strchr($_auth_code, $tmp_code)) return "";
		else return $tmp_code;
	}

	addField($tbl['coupon'], 'is_birth', 'enum("Y","N") not null default "N"');
	if(!fieldExist($tbl['coupon'], 'weeks')) {
		addField($tbl['coupon'], 'weeks', 'varchar(13)');
		addField($tbl['coupon_download'], 'weeks', 'varchar(13)');
	}

	if(!fieldExist($tbl['coupon'], 'sale_prc_over')) {
		addField($tbl['coupon'], 'sale_prc_over', 'char(1) not null default ""');
		addField($tbl['coupon_download'], 'sale_prc_over', 'char(1) not null default ""');
	}
	addField($tbl['coupon'], 'cpn_option', 'varchar(500) not null default ""');
	addField($tbl['coupon'], 'explain', 'text not null');

	if(!$tbl['coupon_download']) {
		msg("쿠폰 기능이 정상적으로 셋팅되지 않았습니다");
	}

	$no = numberOnly($_REQUEST['no']);
	$exec = addslashes($_POST['exec']);
	$download_limit = addslashes($_POST['download_limit']);
	$drelease_limit = addslashes($_POST['drelease_limit']);
	$down_grade = numberOnly($_POST['down_grade']);
	$name = addslashes($_POST['name']);
	$stype = numberOnly($_POST['stype']);
	$sale_prc_over = ($_POST['sale_prc_over'] == 'Y') ? 'Y' : '';

	if($stype == 5) {
		if(addField($tbl['coupon_download'], 'cart_no', 'int(10) not null default "0" comment "개별상품쿠폰 적용 장바구니 번호"')) {
			$pdo->query("alter table $tbl[coupon_download] add index cart_no(cart_no)");
			addField($tbl['cart'], 'opno', 'int(10) not null default "0" comment "관련 주문상품 테이블 키"');
			addField($tbl['order'], 'sale7', 'double(8, 2) not null default "0.00" comment "개별상품쿠폰 할인금액" after sale6');
			addField($tbl['order_product'], 'sale7', 'double(8, 2) not null default "0.00" comment "개별상품쿠폰 할인금액" after sale6');
			addField($tbl['order_product'], 'prdcpn_no', 'varchar(100) not null default "" comment "적용된 개별상품쿠폰 번호(취소 후 복구용)"');
		}
	}

	switch($exec) {
		case 'down_delete' :
			$cno = $pdo->row("select cno from $tbl[coupon_download] where no='$no'");

			$pdo->query("delete from `$tbl[coupon_download]` where `no`='$no'");
			if(is_object($erpListener)) {
				$erpListener->removeCoupon($no);
			}

			$down_hit = $pdo->row("select count(*) from $tbl[coupon_download] where cno='$cno'");
			$pdo->query("update $tbl[coupon] set down_hit='$down_hit' where no='$cno'");

			msg("","reload","parent");
		break;
		case 'delete_authcode' :
			$auth_code = $_POST['auth_code'];
			checkBlank($auth_code,"삭제할 코드를 입력해주세요.");
			$auth_code = addslashes($_POST['auth_code']);
			$delCode=$auth_code."@";
			$pdo->query("update `$tbl[coupon]` set `auth_code`=replace(`auth_code`, '$delCode', ''), `release_limit_ea`=`release_limit_ea`-1 where `no`='$no'");
			$pdo->query("delete from `$tbl[coupon_auth_code]` where `auth_code`='$auth_code'");
			msg("","reload","parent");
		break;
		case 'csv' :
            if (isset($_POST['use_cpn_sms']) == false) {
                define('__NO_CPN_SMS__', true);
            }
			$no = numberOnly($_POST['no']);
			$cpn = $pdo->assoc("select * from $tbl[coupon] where no='$no'");
			if(!$cpn['no']) msg('존재하지 않는 쿠폰정보입니다.');
			if(!$_FILES['csv']['size']) msg('csv 파일을 업로드해주세요.');

            // 쿠폰 발급 한정일때 한정수량 미만인지 체크
            if ($cpn['release_limit'] == '2') {
                $fp_chk = fopen($_FILES['csv']['tmp_name'], 'r');
                $isCpnCnt = $pdo->row("select count(*) from {$tbl['coupon_download']} where cno='{$cpn['no']}'"); // 이미 발급받은 수량
                while (fgetcsv($fp_chk, 512)) { // 업로드 수량
                    $isCpnCnt++;
                }
                if ($isCpnCnt > $cpn['release_limit_ea']) {
                    msg('쿠폰의 발급 제한 수량을 초과하였습니다.');
                }
            }

			$success = 0;
			$fp = fopen($_FILES['csv']['tmp_name'], 'r');
			while($mem = fgetcsv($fp, 512)) {
				$member_id = trim($mem[0]);
				if(putCoupon($cpn, $pdo->assoc("select no, name, member_id, level, cell, sms from $tbl[member] where member_id='$member_id'"))) {
					$success++;
				}
			}
			javac("parent.showDownloadList('$success','$no');");
			exit;
		break;
		case 'restore' :
			$no = numberOnly($_POST['no']);
			$data = $pdo->assoc("select * from $tbl[coupon_download] where no='$no'");
			$ono = $data['ono'];

			if($data['is_type'] == 'A') {
				$pdo->query("update $tbl[coupon_download] set ono='', use_date='0' where no='$no'");
				if(is_object($erpListener)) {
					$erpListener->setCoupon($no);
				}
			} else {
				$pdo->query("delete from $tbl[coupon_download] where no='$no'");
				if(is_object($erpListener)) {
					$erpListener->removeCoupon($no);
				}
			}

            $log = new WorkLog();
            $log->createLog(
                $tbl['coupon_download'],
                (int) $no,
                'name',
                $data,
                $pdo->assoc("select * from {$tbl['coupon_download']} where no=?", array($no))
            );

			$pdo->query("update $tbl[order] set prd_nums=concat(prd_nums, '@$no') where ono='$ono'");
			exit;
		break;
		case 'recall' :
			$no = numberOnly($_POST['no']);
			$total = $pdo->row("select count(*) from $tbl[coupon_download] where cno='$no' and ono=''");
			if($total > 0) {
				$pdo->row("delete from $tbl[coupon_download] where cno='$no' and ono=''");

				$down_hit = $pdo->row("select count(*) from $tbl[coupon_download] where cno='$no'");
				$pdo->query("update $tbl[coupon] set down_hit='$down_hit' where no='$no'");

				exit($total.'개의 쿠폰을 회수처리 하였습니다.');
			}
			exit('회수 가능한 쿠폰이 없습니다.');
		break;
	}

	if($no) {
		$data=get_info($tbl[coupon],"no",$no);
		if(!$data[no]) {
			msg("존재하지 않는 자료입니다");
		}
	}
	if($exec=="delete") {
		$social_coupon = $pdo->row("select `no` from `wm_social_coupon_info` where `cno`='$no'");
		if($social_coupon) {
			msg('소셜쿠폰으로 등록된 쿠폰으로 삭제가 불가능합니다.');
		}
		deletePrdImage($data,1,1);

		$pdo->query("delete from `$tbl[coupon]` where `no`='$no'");
		$pdo->query("delete from `$tbl[coupon_download]` where `cno`='$no' and `use_date`='0'");
		$pdo->query("delete from `$tbl[coupon_auth_code]` where `cno`='$no'");

		couponLogw($no,$data[name],3,$data[is_type]); // 2008-11-07 : 쿠폰로그 - Han
		msg("삭제되었습니다","reload","parent");
	}

	$sale_prc = numberOnly($_POST['sale_prc']);
	$prc_limit = numberOnly($_POST['prc_limit']);
	$sale_limit = numberOnly($_POST['sale_limit']);
	$release_limit_ea = numberOnly($_POST['release_limit_ea']);
	$download_limit_ea = numberOnly($_POST['download_limit_ea']);
	$attachtype = numberOnly($_POST['attachtype']);
	$attach_items = addslashes($_POST['attach_items_'.$attachtype]);
	$stype = addslashes($_POST['stype']);
	$device = addslashes($_POST['device']);
	$pay_type = numberOnly($_POST['pay_type']);
	$release_limit = numberOnly($_POST['release_limit']);
	$rdate_type = numberOnly($_POST['rdate_type']);
	$rstart_date = addslashes($_POST['rstart_date']);
	$rfinish_date = addslashes($_POST['rfinish_date']);
	$udate_type = numberOnly($_POST['udate_type']);
	$ustart_date = addslashes($_POST['ustart_date']);
	$ufinish_date = addslashes($_POST['ufinish_date']);
	$sale_type = ($_POST['sale_type'] == 'p') ? 'p' : 'm';
	$buy_prcs = numberOnly($_POST['buy_prcs']);
	$buy_prce = numberOnly($_POST['buy_prce']);
	$udate_limit = numberOnly($_POST['udate_limit']);
	$use_limit = addslashes($_POST['use_limit']);
	$down_gradeonly = addslashes($_POST['down_gradeonly']);
	$cpn_option = (is_array($_POST['cpn_option']) == true) ? '@'.implode('@', $_POST['cpn_option']).'@' : '';
	$serial_code_type = $_POST['serial_code_type'];
	$explain = addslashes($_POST['explain']);
    $place = addslashes($_POST['place']);

	if($_POST['download_cnt'] == "Y") {
		$sql="update `$tbl[coupon]` set  `rstart_date`='$rstart_date', `rfinish_date`='$rfinish_date', `rdate_type`='$rdate_type', `explain` = '$explain' where `no`='$no'";
		$r=$pdo->query($sql);
		$_logcontent="";
		foreach($_POST as $key=>$val){
			$_logcontent .= "$key:$val<wisa>";
		}
		couponLogw($data[no],$data['name'],2,$data['is_type'],$_logcontent);
		msg("수정되었습니다","?body=promotion@coupon&is_type=$is_type", "parent");
	}

	checkBlank($name,'쿠폰명을 입력해주세요.');

	if(is_array($_POST['weeks'])) {
		$weeks = $_POST['weeks'];
		$weeks = implode('@', numberOnly($weeks));
	}

	if($stype != 3 && $stype != 4) {
		checkBlank($sale_prc,"할인 금액(율)을 숫자만 입력해주세요.");
	} else {
		$sale_prc=0;
		$sale_limit=0;
	}

	checkBlank($prc_limit,"사용 제한 결제액을 입력해주세요.");

	if($auto_cpn!='Y') {
		if($download_limit==3) {
			checkBlank($download_limit_ea,"다운로드 제한 갯수를 입력해주세요.");
		}
		if($drelease_limit==2) {
			checkBlank($release_limit_ea,"발급 제한 갯수를 입력해주세요.");
		}
	}

	if(empty($no) == true && $is_type == "B" && $down_type == 'B' && $serial_code_type == 'manual') {
		unset($_FILES['csv_cpnno']);
		$serial_code = addslashes(trim($_POST['serial_code']));
		checkBlank($serial_code, '생성할 쿠폰 코드를 입력해주세요.');
	}

	if($is_type == "B" && $release_limit == 2 && $release_limit_ea < 1){
		if($down_type == 'A' && $_FILES['csv_cpnno']['size'] < 0) msg("발급수량을 정확히 입력해주시기 바랍니다");
	}

	if($sale_type=="p") {
		if($sale_prc<1 || $sale_prc>100) {
			msg("할인률은 1~100% 입니다");
		}
		if($sale_limit<1) {
			$sale_limit="";
		}
		checkBlank($sale_limit,"최대 할인 금액을 입력해주세요.");
	}

	if($sale_type == 'e' && $attachtype != 1 && $attachtype != 2) {
		msg("무료 상품교환 쿠폰은\\n할인대상이 \'지정카테고리 적용\', \'지정 상품 적용\' 일때만\\n사용 가능합니다.");
	}

	if($is_type == 'B' && $release_limit == 2 && $release_limit_ea < 1) {
		msg('한정 발급수량을 입력해주세요.');
	}

	$name = addslashes($name);
	$cp_q = $cp_q1 = $cp_q2 = '';

	// 로그인 시 자동발급 쿠폰 개별 변수
	if($down_type == 'L' || $down_type == 'L2') {
		$down_grade = numberOnly($_POST['down_grade2']);
		$down_gradeonly = addslashes($_POST['down_gradeonly2']);
		$down_msg = trim(addslashes($_POST['down_msg']));
		if(empty($down_grade)) $down_grade = '0';

		$cp_q  .= ", down_msg='$down_msg'";
		$cp_q1 .= ", down_msg";
		$cp_q2 .= ", '$down_msg'";

		addField($tbl['coupon'], 'down_msg', 'varchar(200) not null default "" comment "다운로드시 메시지"');
	}

	if($down_type){
	    if(($down_type == "B" || $down_type == 'L' || $down_type == 'L2') && $is_type <> "B"){ checkBlank($down_grade,"발급 회원 등급을 입력해주세요."); }
		else{ $down_grade="0"; $down_gradeonly="Y"; }
	    $cp_q  .= " , `down_type`='$down_type', `down_grade`='$down_grade', `down_gradeonly`='$down_gradeonly'";
	    $cp_q1 .= " , `down_type`, `down_grade`, `down_gradeonly`";
	    $cp_q2 .= " , '$down_type', '$down_grade', '$down_gradeonly'";
	}

	if($down_type == 'F' || $down_type == 'G') {
		if(!fieldExist($tbl['coupon_download'], 'ono_from')) {
			addField($tbl['coupon'], 'buy_prcs', 'int(10) not null default "0" comment "필요 결제 최소금액"');
			addField($tbl['coupon'], 'buy_prce', 'int(10) not null default "0" comment "필요 결제 최대금액"');
			addField($tbl['coupon_download'], 'ono_from', 'varchar(30) not null default "" comment "쿠폰을 발급 주문서"');
			$pdo->query("alter table $tbl[coupon_download] add index ono_from (ono_from)");
		}

		$buy_prcs = numberOnly($_POST['buy_prcs']);
		$buy_prce = numberOnly($_POST['buy_prce']);
		if($buy_prce > 0 && $buy_prcs > $buy_prce) {
			msg('구매완료 쿠폰의 필요 결제 최대 금액이 최소 금액보다 작습니다.');
		}
		$cp_q .= ", buy_prcs='$buy_prcs', buy_prce='$buy_prce'";
		$cp_q1 .= ", buy_prcs, buy_prce";
		$cp_q2 .= ", '$buy_prcs', '$buy_prce'";

		if($down_type == 'G') {
			$download_limit_ea = 1;
			$download_limit = 3;
		}
	}

	$cp_q1 .= " , `is_type`";
	$cp_q2 .= " , '$is_type'";

	include_once $GLOBALS['engine_dir'].'/_config/set.upload.php';
	wingUploadRule($_FILES, 'coupon');

	$updir=$data['updir'];
	for($ii=1; $ii<=1; $ii++) {
		$chg_file="";
		// 파일 삭제 또는 덮어 쓰기
		if($updir && ($_POST['delfile'.$ii]=="Y" || $_FILES['upfile'.$ii][tmp_name])) {
			deletePrdImage($data,$ii,$ii);
			$up_filename=$width=$height="";
			$chg_file=1;
		}
		if($_FILES['upfile'.$ii][tmp_name]) {
			// 파일업디렉토리
			if(!$updir) {
				$updir=$dir['upload']."/".$dir['coupon']."/".date("Ym",$now)."/".date("d",$now);
				makeFullDir($updir);
				$asql.=" , `updir`='$updir'";
			}

			$up_filename=md5($ii+time()); // 새파일명
			$up_info=uploadFile($_FILES["upfile".$ii],$up_filename,$updir,"jpg|jpeg|gif|png|bmp");
			$up_filename=$_up_filename[$ii]=$up_info[0];
			$chg_file=1;
		}

		if($chg_file) $asql.=" , `upfile".$ii."`='".$up_filename."'";
	}

	if(!$stype) {
		$stype=1;
	}

	$_logcontent="";
	foreach($_POST as $key=>$val){
		$_logcontent .= "$key:$val<wisa>";
	}

	addField($tbl['coupon'], "udate_limit", "mediumint not null default 0");
	$udate_limit = numberOnly($udate_limit);
	addField($tbl['coupon'], "pay_type", "int(1) not null default 1");
	addField($tbl['coupon_download'], "pay_type", "int(1) not null default 1");
	$pdo->query("alter table $tbl[coupon] change down_type down_type varchar(2) not null default 'A'");

	if($cfg['use_partner_shop'] == 'Y') {
		$partner_type = numberOnly($_POST['partner_type']);
		$partner_no = numberOnly($_POST['partner_no']);
		$partner_fee = numberOnly($_POST['partner_fee'], true);
		$cp_q  .= ", partner_type='$partner_type', partner_no='$partner_no', partner_fee='$partner_fee'";
		$cp_q1 .= ", partner_type, partner_no, partner_fee";
		$cp_q2 .= ", '$partner_type', '$partner_no', '$partner_fee'";
	}

	if($udate_type == 1) {
		$ustart_date = $ufinish_date = '';
	}

	if($data[no]) {
		$sql="update `$tbl[coupon]` set `name`='$name', `device`='$device', place='$place', `sale_prc`='$sale_prc', `prc_limit`='$prc_limit',`sale_limit`='$sale_limit', `rstart_date`='$rstart_date', `rfinish_date`='$rfinish_date', `ustart_date`='$ustart_date', `ufinish_date`='$ufinish_date', `sale_type`='$sale_type', `rdate_type`='$rdate_type', `udate_type`='$udate_type', `udate_limit`='$udate_limit', `stype`='$stype', `auto_cpn`='$auto_cpn', `download_limit`='$download_limit', `download_limit_ea`='$download_limit_ea', `use_limit`='$use_limit', `release_limit`='$release_limit', `release_limit_ea`='$release_limit_ea', `is_birth`='$is_birth', attachtype='$attachtype', attach_items='$attach_items', pay_type='$pay_type', weeks='$weeks', sale_prc_over='$sale_prc_over', cpn_option='$cpn_option', `explain`='$explain' $asql $cp_q where `no`='$data[no]'";
		$r=$pdo->query($sql);

		couponLogw($data[no],$name,2,$is_type,$_logcontent);
	}
	else {
		$sql="INSERT INTO `$tbl[coupon]` (`name`, `updir`, `upfile1`, `device`, place, `sale_prc`, `prc_limit`, `sale_limit`, `rstart_date`, `rfinish_date`, `ustart_date`, `ufinish_date`, `sale_type`, `reg_date`, `rdate_type`, `udate_type`, `udate_limit`, `stype`, `download_limit`, `download_limit_ea`, `use_limit`, `release_limit`, `release_limit_ea`, `is_birth`, `attachtype`, `attach_items`, `pay_type`, `weeks`, sale_prc_over, cpn_option, `explain` $cp_q1)  VALUES ('$name', '$updir', '$up_filename', '$device', '$place', '$sale_prc', '$prc_limit', '$sale_limit', '$rstart_date', '$rfinish_date', '$ustart_date', '$ufinish_date', '$sale_type', '$now', '$rdate_type', '$udate_type', '$udate_limit', '$stype', '$download_limit', '$download_limit_ea', '$use_limit', '$release_limit', '$release_limit_ea', '$is_birth', '$attachtype', '$attach_items', '$pay_type', '$weeks', '$sale_prc_over', '$cpn_option', '$explain' $cp_q2)";
		$r=$pdo->query($sql);

		$cno=$pdo->row("select max(`no`) from `$tbl[coupon]`");

		couponLogw($cno,$name,1,$is_type,$_logcontent);
	}

	if(!isTable($tbl['coupon_auth_code'])) {
		include_once $engine_dir.'/_config/tbl_schema.php';
		$pdo->query($tbl_schema['coupon_auth_code']);
	}

	// 시리얼 쿠폰 번호 생성
	if($is_type == 'B') {
		$ori_release_limit_ea = $data['release_limit_ea'];
		if(!$data['no']) $data['no'] = $cno;
		$data = $pdo->assoc("select * from $tbl[coupon] where no='$data[no]'");
		if($down_type == 'A' || !$data['auth_code']) {
			if($_FILES['csv_cpnno']['size'] > 0) {
				$_auth_code = '@';
				$fp = fopen($_FILES['csv_cpnno']['tmp_name'], 'r');
				$ii = 0;
				while($tmp_code = fgetcsv($fp, 128)) {
					if(!$tmp_code[0]) continue;
					$pdo->query("insert into $tbl[coupon_auth_code] (cno, auth_code) values ('$data[no]', '$tmp_code[0]')");
					$ii++;
					if($pdo->lastRowCount() > 0) {
						$_auth_code .= $tmp_code[0].'@';
						if($down_type == 'B') break;
					}
				}
				$pdo->query("update `$tbl[coupon]` set `auth_code`='$_auth_code', `release_limit_ea`='$ii' where `no`='$data[no]'");
			} else {
				if(!$release_limit_ea) $release_limit_ea = 1;
				if($is_type == 'B' && $release_limit == '1') {
					$release_limit_ea = 1;
				}
				if(!$data['no'] || $ori_release_limit_ea != $release_limit_ea){

					if($data['auth_code']) {
						$pdo->query("delete from $tbl[coupon_auth_code] where cno='$data[no]'");
					}

					if($serial_code_type == 'manual') {
						$_auth_code = '@'.$serial_code.'@';
						$pdo->query("insert into {$tbl['coupon_auth_code']} (cno, auth_code) values ('{$data['no']}', '$serial_code')");
					} else {
						$_auth_code = '@';
						for($ii = 0; $ii < $release_limit_ea; $ii++) {
							while(!$tmp_code){
								$tmp_code = makeCode($data[no]);
							}

							$pdo->query("insert into $tbl[coupon_auth_code] (cno, auth_code) values ('$data[no]', '$tmp_code')");
							if($pdo->lastRowCount() > 0) {
								$_auth_code .= $tmp_code."@";
								if($down_type == 'B') break;
							}
							$tmp_code = "";
						}
					}
					$pdo->query("update `$tbl[coupon]` set `auth_code`='$_auth_code' where `no`='$data[no]'");
				}
			}
		}

		if($down_type == 1) {
			$cnt = $pdo->row("select count(*) from $tbl[coupon_auth_code] where cno='$data[no]'");
			$pdo->query("update $tbl[coupon] set release_limit_ea='$cnt' where no='$data[no]'");
		}
	}

	if(!$cno) msg("수정되었습니다","?body=promotion@coupon&is_type=$is_type", "parent");
	else msg("쿠폰이 생성되었습니다","?body=promotion@coupon&is_type=$is_type","parent");

?>