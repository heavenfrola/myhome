<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  적립금/예치금/포인트 라이브러리
	' +----------------------------------------------------------------------------------------------+*/

	function ctrlMilage($ctype,$mtype,$amount,$member,$add_title="",$skip_log="",$m_admin_id="", $ono = null) {
		global $tbl,$now,$milage_title,$admin, $cfg, $pdo;

		if (!$member['no']) return;

		if ($cfg['milage_use'] != 1) return;

		$member_tbl = ($member['withdraw'] == 'D2') ? $tbl['member_deleted'] : $tbl['member'];

		$milage_lang_title = eval(__lang_milage_title);
		$title=$milage_lang_title[$mtype]?$milage_lang_title[$mtype]:$milage_title[$mtype];
		$member_milage=$pdo->row("select `milage` from {$member_tbl} where no='{$member['no']}'");
		$member_milage=($member_milage > 0) ? $member_milage : 0;
		if($ctype=="+") {
			$member_milage+=$amount;
		}
		else {
			$member_milage-=$amount;
			if($member_milage<0) $member_milage=0;
		}

		$pdo->query("update `$member_tbl` set `milage`='$member_milage' where `no`='{$member['no']}'");

		if($ctype == '-' && $mtype != 15) {
			$tamount = $amount;
			$res = $pdo->iterator("select no, amount, use_amount from {$tbl['milage']} where ctype='+' and member_no='$member[no]' and (expire_date > $now or expire_date = 0) and amount>use_amount order by no asc");
            foreach ($res as $data) {
				$tmp = $data['amount']-$data['use_amount'];
				$tmp = ($tamount > $tmp) ? $tmp : $tamount;
				$tamount -= $tmp;
				$pdo->query("update {$tbl['milage']} set use_amount=use_amount+$tmp where no='{$data['no']}'");
				if($tamount < 1) break;
			}
		}

		if(!$skip_log) {
			if ($GLOBALS['ono']) $add_title = $GLOBALS['ono'] . ' | ' . $add_title;
			$values = array(
				$member['no'], $member['member_id'], $member['name'], $add_title, $amount, $ctype, $mtype, $member_milage, $now, $m_admin_id
			);

			if($cfg['milage_expire'] && $ctype == '+') {
				unset($skip_log);
				$asql1 = ", expire_date";
				$asql2 = ", ?";
				$values[] = strtotime('+ '.$cfg['milage_expire'], strtotime(date('Y-m-d 23:59:59')));
			}

			$pdo->query("
				INSERT INTO {$tbl['milage']} 
				(`member_no` , `member_id` , `member_name` , `title` , `amount` , `ctype` , `mtype` , `member_milage` , `reg_date` , `admin_id` $asql1) 
				VALUES 
				(?, ?, ?, ?, ?, ?, ?, ?, ?, ? $asql2)
			", $values);
		}


		$erpListener = $GLOBALS['erpListener'];
		if(is_object($erpListener)) { // dooson
			$erpListener->setMilage(array(
				'member_no' => $member['no'],
				'member_id' => $member['member_id'],
				'member_name' => $member['name'],
				'title' => $add_title,
				'ctype' => $ctype,
				'mtype' => $mtype,
				'amount' => $amount,
				'member_milage' => $member_milage,
				'admin_id' => $m_admin_id,
				'reg_date' => $now,
				'ono' => $ono,
			), 'milage');
		}
	}


	/* +----------------------------------------------------------------------------------------------+
	' |  적립금 만료 처리
	' +----------------------------------------------------------------------------------------------+*/
	function expireMilage($member_id = '') {
		global $tbl, $now, $milage_title, $pdo;

		$member_id = addslashes(trim($member_id));
		if($member_id) $w .= " and member_id='$member_id'";
		$res = $pdo->iterator("select member_id, sum(amount-use_amount) as emileage from {$tbl['milage']} where expire_date > 0 and expire_date <= $now and expire='N' $w group by member_id having emileage > 0 order by null");
        foreach ($res as $data) {
			$mdata = $pdo->assoc("select * from {$tbl['member']} where member_id='$data[member_id]'");
			ctrlMilage('-', 15, $data['emileage'], $mdata, $milage_title[15]);
			$pdo->query("update {$tbl['milage']} set expire='Y' where expire_date > 0 and expire_date <= $now and expire='N' and member_id='$data[member_id]' and amount>use_amount");
		}
	}


	function ctrlEmoney($ctype,$mtype,$amount,$member,$add_title="",$skip_log="",$e_admin_id="", $ono = null) {
		global $tbl,$now,$milage_title,$admin,$cfg,$admin, $pdo;

		if($cfg['emoney_use']!='Y') {
			return;
		}

		$milage_lang_title = eval(__lang_milage_title);
		$title=$milage_lang_title[$mtype]?$milage_lang_title[$mtype]:$milage_title[$mtype];

		$member_milage = $pdo->row("select emoney from {$tbl['member']} where no='$member[no]'");
		$member_milage=($member_milage > 0) ? $member_milage : 0;
		if($ctype=="+") {
			$member_milage+=$amount;
		}
		else {
			$member_milage-=$amount;
			if($member_milage<0) $member_milage=0;
		}

		$pdo->query("update {$tbl['member']} set `emoney`='$member_milage' where `no`='{$member['no']}'");

		if(!$skip_log) {
			if($GLOBALS['ono']) $add_title = $GLOBALS['ono']." | $add_title";
			$sql="INSERT INTO {$tbl['emoney']} ( `member_no` , `member_id` , `member_name` , `title` , `amount` , `ctype` , `mtype` , `member_emoney` , `reg_date`, `admin_id` ) VALUES ('$member[no]','$member[member_id]','$member[name]','$add_title','$amount','$ctype','$mtype','$member_milage','$now', '$e_admin_id')";
			$pdo->query("
				INSERT INTO {$tbl['emoney']} ( `member_no` , `member_id` , `member_name` , `title` , `amount` , `ctype` , `mtype` , `member_emoney` , `reg_date`, `admin_id` ) 
				VALUES 
				(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
			", array(
				$member['no'], $member['member_id'], $member['name'], $add_title, $amount, $ctype, $mtype, $member_milage, $now, $e_admin_id
			));
		}

		$erpListener = $GLOBALS['erpListener'];
		if(is_object($erpListener)) { // dooson
			$erpListener->setMilage(array(
				'member_no' => $member['no'],
				'member_id' => $member['member_id'],
				'member_name' => $member['name'],
				'title' => $add_title,
				'ctype' => $ctype,
				'mtype' => $mtype,
				'amount' => $amount,
				'member_milage' => $member_milage,
				'admin_id' => $m_admin_id,
				'reg_date' => $now,
				'ono' => $ono,
			), 'emoney');
		}
	}

	function milageLoop($unit="") {
		global $resMilage,$milage_title,$idx,$cfg, $pdo;
        $data = $resMilage->current();
      	$resMilage->next();
		if($data == false) return false;

		$milage_lang_title = eval(__lang_milage_title);
		$data['mtitle']=$milage_lang_title[$data['mtype']]?$milage_lang_title[$data['mtype']]:$milage_title[$data['mtype']];

		if($data['title']) $data['mtitle'].=" (".strip_tags($data['title']).")";
		$data[date]=date("Y/m/d",$data['reg_date']);
		$data['expire_date'] = ($data['expire_date'] > 0) ? date('Y/m/d', $data['expire_date']) : '';

		$data['minus']=0;
		$data['plus']=0;
		if($data['ctype']=="+") $data['plus']=number_format($data['amount'],$cfg['currency_decimal']);
		else $data['minus']=number_format($data['amount'],$cfg['currency_decimal']);
		$data['minus'].=$unit;
		$data['plus'].=$unit;


		$data['member_milage']=number_format($data['member_milage'],$cfg['currency_decimal']);
		$data['member_milage'].=$unit;
		$idx--;
		return $data;
	}

	function emoneyLoop($unit="") {
		global $resEmoney,$milage_title,$idx,$cfg;

        $data = $resEmoney->current();
        $resEmoney->next();
		if($data == false) return false;

		$milage_lang_title = eval(__lang_milage_title);
		$data['mtitle']=$milage_lang_title[$data['mtype']]?$milage_lang_title[$data['mtype']]:$milage_title[$data['mtype']];

		if($data['title']) $data['mtitle'].=" (".$data['title'].")";
		$data[date]=date("Y/m/d",$data['reg_date']);

		$data['minus']=0;
		$data['plus']=0;
		if($data['ctype']=="+") $data['plus']=number_format($data['amount'],$cfg['currency_decimal']);
		else $data['minus']=number_format($data['amount'],$cfg['currency_decimal']);
		$data['minus'].=$unit;
		$data['plus'].=$unit;


		$data['member_emoney']=number_format($data['member_emoney'],$cfg['currency_decimal']);
		$data['member_emoney'].=$unit;
		$idx--;

		return $data;
	}

	function reviewMilage($no,$delete=0,$milage=null,$milage_image=null) {
		global $tbl, $cfg, $dir, $now, $admin, $pdo;
		$data = $pdo->assoc("select * from {$tbl['review']} where no='$no'");

		if($data['milage'] <= 0) $data['milage'] = 0;

		if(!$data['no'] || (!$delete && $data['milage']) || ($delete && !$data['milage']) || !$data['member_no'] || ($cfg['product_review_atype'] == 2 && $data['stat']==1)) {
			return false;
		}
		$amember = $pdo->assoc("select * from {$tbl['member']} where no='{$data['member_no']}'");
		if(!$amember) {
			return false;
		}
		$prdname = $pdo->row("select `name` from {$tbl['product']} where `no`='{$data['pno']}' limit 1");
		if(!$prdname) $prdname = $data['title'];
		if($delete && $data['milage'] > 0){ // 삭제시 반환
			ctrlMilage("-",3,$data['milage'],$amember,$prdname." 상품평 삭제","",$admin['admin_id']);
			return true;
		}
		// 2007-10-31 : 이미지추가적립 - Han
		if(is_null($milage) === true) $milage = numberOnly($cfg['milage_review'], $cfg['currency_decimal']);
		if(is_null($milage_image) === true) $milage_image = numberOnly($cfg['milage_review_image'], $cfg['currency_decimal']);

		$amount = $milage;
        if ($milage_image > 0) {
    		if ($data['upfile1'] || $data['upfile2']) $amount += $milage_image;
            else {
                // 에디터 이미지가 prefix
                $_prefix = getListImgURL($dir['upload'].'/editor_attach', '');

                // 본문 내에 에디터 삽입 이미지가 있는지 체크
                $dom = new DomDocument('1.0', 'UTF-8');
                $dom->loadHTML($data['content']);
                $imgs = $dom->getElementsByTagName('img');
                foreach ($imgs as $img) {
                    $src = $img->getAttribute('src');
                    if ($src && preg_match('/^'.preg_quote($_prefix, '/').'/', $src) == true) {
                        $amount += $milage_image;
                        break;
                    }
                }
            }
        }
		if($amount < 1) return false;

		ctrlMilage("+",6,$amount,$amember,$prdname,"",$admin['admin_id']);
		$pdo->query("update {$tbl['review']} set `milage_date`='$now', `milage`='$amount' where `no`='$no'");
		return true;
	}

	function memberLevelUp($mno = null) {
		global $tbl,$cfg, $pdo;

		$GLOBALS['no_qcheck'] = true;

		$erpListener = $GLOBALS['erpListener'];
		$mw = $ow = '';
		$cnt = 0;
		$change_date = time();
		if(!$cfg['member_level_field']) $cfg['member_level_field'] = 'prc';

		if(!fieldExist($tbl['member_group'], 'protect')) {
			addField($tbl['member_group'], 'protect', 'enum("N","Y") default "N"');
		}
		if(!fieldExist($tbl['member_group'], 'move_qty')) {
			addField($tbl['member_group'], 'move_qty', 'int(5) not null default 0 after move_price');
		}

		// 회원 그룹 설정 체크
		$mprice = $mqty = $protects = array();
		$gres = $pdo->iterator("select no, move_price, move_qty, protect from {$tbl['member_group']} where use_group='Y' and no > 1 order by no asc");
        foreach ($gres as $gdata) {
			if($gdata['protect'] != 'Y') {
				$mprice[$gdata['no']] = $gdata['move_price'];
				$mqty[$gdata['no']] = $gdata['move_qty'];
			} else {
				$protects[] = $gdata['no'];
			}
		}
		$mw .= (count($protects) > 0) ? " and m.level not in (".implode(',', $protects).")" : "";

		// 추가 조건
		if($mno > 0) $mw .= " and m.no='$mno'"; // 지정 회원만 처리
		if($cfg['member_level_limit'] > 0) { // 등급조건 적용 기간
			$member_level_limit = strtotime('-'.numberOnly($cfg['member_level_limit']).' months');
			$member_level_limit = strtotime(date('Y-m-d 00:00:00', $member_level_limit));
			$ow .= " and date5 >= '$member_level_limit'";
		}

		// 주문서 체크
		$timestamp = time();
		if($cfg['member_level_limit'] > 0) { // 기간 제한 있을 경우
			$res = $pdo->iterator("
				select
					m.no, m.member_id, m.level,
					sum(pay_prc-dlv_prc) as total_prc, count(*) as total_ord
				from {$tbl['order']} o inner join {$tbl['member']} m on o.member_no=m.no
				where
					m.level > 1 and o.member_no > 0 $mw $ow
					and o.stat=5
				group by m.no
			");
		} else { // 전체일 경우 주문데이터 참조하지 않음
			$res = $pdo->iterator("
				select
					m.no, m.member_id, m.level, m.total_prc, m.total_ord
				from {$tbl['member']} m
				where m.level!=1 $mw
			");
		}
		if(!$cfg['member_level_field']) $cfg['member_level_field'] = 'prc';
        foreach ($res as $data) {
			foreach($mprice as $level => $val) {
				switch($cfg['member_level_field']) {
					case 'both' :
						if($mprice[$level] <= $data['total_prc'] && $mqty[$level] <= $data['total_ord']) {
							break 2;
						}
						continue;
					case 'either' :
						if($mprice[$level] <= $data['total_prc'] || $mqty[$level] <= $data['total_ord']) {
							break 2;
						}
						continue;
					case 'prc' :
						if($mprice[$level] <= $data['total_prc']) {
							break 2;
						}
						continue;
					case 'qty' :
						if($mqty[$level] <= $data['total_ord']) {
							break 2;
						}
						continue;
				}
			}
			if(in_array($level, $protects)) continue;
			if($level == $data['level']) {
				$pdo->query("update {$tbl['member']} set jumin='$timestamp' where no='$data[no]'");
				continue;
			}

			if($level < $data['level'] || ($level > $data['level'] && $cfg['member_auto_move_down'] == 'Y')) {
				$pdo->query("update $tbl[member] set level='$level', jumin='$timestamp' where no='$data[no]'");

				if($mno > 0 && is_object($erpListener)) { // dooson
					$erpListener->setChangedMember('', $mno);
				}
				$cnt++;
			}
		}

		// 나머지 내역 강등
		if($cfg['member_auto_move_down'] == 'Y') {
			$pdo->query("update $tbl[member] m set level=9 where level not in (1, 9) and jumin!='$timestamp' $mw");
		}

		return $cnt;
	}

	function usePoint($nums="",$member=false,$cancel="",$amount=0){
		global $tbl,$cfg;
		if(!$member['no'] && !$amount) return;
		$ctype=($cancel) ? "+" : "-";
		$utype=($cancel) ? "-" : "+";
		$add_title=($cancel) ? "사은품 구매취소" : "사은품 구매";

		ctrlPoint($amount,"_use",$member['no'],1,$ctype,3,$add_title,$utype);
	}

	function milageChanging($amount,$member,$w=1,$ck=0){
		global $cfg,$tbl;
		$cfg['point_name']=($cfg['point_name']) ? $cfg['point_name'] : "포인트";
		$_pm=($w == 1) ? "point" : "milage";
		$_w=($w == 1) ? 2 : 1;
		$cfg['milage_use']=($cfg['milage_use'] == "2") ? "2" : "1";
		$change_use=0;
		if($cfg['point_use'] == "Y" && $cfg['milage_use'] == 1 && $cfg["point_change".$_w] == "Y" && floor($cfg['point_change_ratio'])>0) $change_use=1;
		if($ck) return $change_use;
		if(!$change_use || $amount<1 || !$member['no']) return;

		$wtitle=($w == 1) ? $cfg['point_name'] : "적립금";
		if($amount > $member[$_pm]) msg(__lang_mypage_error_transmileage__);

		if($w == 1){ // 포인트->적립금
			$mamount=floor($amount/$cfg['point_change_ratio']);
			if($mamount < 1) msg(__lang_mypage_error_moremileage__);
			ctrlPoint($amount,"_use",$member['no'],"","-",3,"적립금(".$mamount.") 전환","+");
			ctrlMilage("+",3,$mamount,$member,$cfg['point_name']."(".$amount.") 전환");
		}elseif($w == 2){ // 적립금->포인트
			$pamount=floor($cfg['point_change_ratio']*$amount);
			if($pamount < 1) msg(__lang_mypage_error_moremileage__);
			ctrlMilage("-",3,$amount,$member,$cfg['point_name']."(".$pamount.") 전환");
			ctrlPoint($pamount,4,$member['no'],"","+",3,"적립금(".$amount.") 전환","+");
		}

		msg(__lang_mypage_info_trancemilage__, 'reload', 'parent');
	}

?>