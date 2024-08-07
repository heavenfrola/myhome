<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  설정 저장 처리
	' +----------------------------------------------------------------------------------------------+*/

    use Wing\API\Kakao\KakaoSync;
    use Wing\API\Kakao\KakaoTalkPay;
    use Wing\common\Config;

	if(!$no_reload_config) {
		checkBasic();
	}

	$config_code = $_POST['config_code'];
	if($admin['admin_id'] != 'wisa') {
		$reg_code = addslashes($_POST['reg_code']);
		if(!$reg_code && $cfg['admin_set_confirm'] == 'Y') {
			$config_code = addslashes($_POST['config_code']);
			$card_mobile_pg = addslashes($_POST['card_mobile_pg']);
			$card_pg = addslashes($_POST['card_pg']);
			$res = $pdo->row("select `use_yn` from `wm_cfg_confirm_list` where `code` = '$config_code'");
			if($res == 'Y') {
				$confirm_use = $pdo->row("select `cfg_confirm` from `wm_mng` where `admin_id` = '$admin[admin_id]' ");
				if($confirm_use == 'Y') {
					if($config_code == "card_pg") {
						if($card_mobile_pg) {
							$config_code = $card_mobile_pg;
							$device = 'mobile';
						} else {
							$config_code = $card_pg;
							$device = 'pc';
						}
					}
					echo "<script>
					parent.openCfgCertFrm('$config_code', '$device')</script>";
					exit;
				} else {
					msg("2단계 인증에 따른 사전에 등록되어있는 관리자만 휴대폰 인증절차 후 수정 가능합니다.");
				}
			}
		} elseif ($reg_code && $cfg['admin_set_confirm'] == 'Y') {
			$sms_res = $pdo->iterator("select `cell`,`name`, `admin_id` from `wm_mng` where `cfg_receive` = 'Y'");
            foreach ($sms_res as $sdata) {
				if($sdata['cell']){
					$config_name = $pdo->row("select `name` from `wm_cfg_confirm_list` where `code` = '$config_code'");
					$admin = $sdata['name']."(".$sdata['admin_id'].")";
					include_once $engine_dir."/_engine/sms/sms_module.php";
					$sms_replace['config_name'] = $config_name;
					$sms_replace['admin'] = $admin;
					SMS_send_case(19, $sdata['cell']);
				}
			}
		}
	}

	// config.php 백업
	$tmp = '';
	$backup_dir = $dir['upload'].'/'.$dir['config'];
	$backup_file = $root_dir.'/'.$backup_dir.'/'.date('YmdHis', $now).'_'.$admin['no'].'.bak';
	if(!is_dir($root_dir.'/'.$backup_dir)) makeFullDir($backup_dir);

	$res = $pdo->iterator("select name, value from $tbl[config]");
    foreach ($res as $cdata) {
		$cdata['value'] = stripslashes($cdata['value']);
		$tmp .= "[$cdata[name]] $cdata[value]\n";
	}
	$tmp .= "[modify_id] $admin[admin_id]\n";
	$tmp .= "[modify_ip] $_SERVER[REMOTE_ADDR]\n";
	$fp = fopen($backup_file, 'w');
	fwrite($fp, $tmp);
	fclose($fp);
	unset($tmp);

	if(!empty($card_mobile_dacom_id) && !empty($card_mobile_dacom_key)) $config_code = 'mobile_dacom_card';

	switch($config_code) {
		case "account":
            // 국내 결제
            foreach (array(1, 2, 4, 5, 7) as $key) {
                if(isset($_POST['pay_type_'.$key]) == false) {
                    $_POST['pay_type_'.$key] = false;
                }
            }

            // 간편 결제
            if(isset($_POST['use_nsp']) == false) $_POST['use_nsp'] = false;
            if(isset($_POST['use_payco']) == false) $_POST['use_payco'] = false;
            if(isset($_POST['use_kakaopay']) == false) $_POST['use_kakaopay'] = false;
            if(isset($_POST['use_tosscard']) == false) $_POST['use_tosscard'] = false;
            if(isset($_POST['use_samsungpay']) == false) $_POST['use_samsungpay'] = false;

            // 해외 결제
            if(isset($_POST['use_paypal']) == false) $_POST['use_paypal'] = false;
            if(isset($_POST['use_paypal_c']) == false) $_POST['use_paypal_c'] = false;
            if(isset($_POST['use_alipay']) == false) $_POST['use_alipay'] = false;
            if(isset($_POST['use_alipay_e']) == false) $_POST['use_alipay_e'] = false;
            if(isset($_POST['use_wechat']) == false) $_POST['use_wechat'] = false;
            if(isset($_POST['use_sbipay']) == false) $_POST['use_sbipay'] = false;
            if(isset($_POST['use_exim']) == false) $_POST['use_exim'] = false;

            // 정기 결제
            if (isset($_POST['use_nsp_sbscr']) == false) $_POST['use_nsp_sbscr'] = false;
			break;
        case 'danal' :
			$wec = new weagleEyeClient($_we, 'Etc');
			$wec->call('setExternalService', array(
				'service_name' => 'pg_danal',
				'use_yn' => ($_POST['mobile_danal'] == 'Y' ? 'Y' : 'N'),
				'root_url' => $root_url,
				'extradata' => $_POST['danal_subcp_id']
			));
            break;
		case 'account_bank_limit' :
            if($_POST['use_bank_time'] == 'N') {
                $_POST['banking_time_std'] = null;
                $_POST['banking_time'] = null;
            }
            unset($_POST['use_bank_time']);

			$wec_acc = new weagleEyeClient($_we, 'account');
			$wec_acc->call('setBankCancelCron', array(
				'time_std' => $_POST['banking_time_std'],
				'term' => $_POST['banking_time'],
			));
			if($wec_acc->result == 'OK') {
				$_POST['use_bankCancelCron'] = 'Y';
			}
			break;
        case 'account_sms_notice' :
            if ($_POST['use_banking_sms'] != 'Y') $_POST['use_banking_sms'] = 'N';

            if ($_POST['banking_sms_time'] > $_POST['banking_sms_until']) {
                msg('알림 설정 발송 범위의 시작일을 종료일보다 크게 설정할 수 없습니다.');
            }

            $wec = new weagleEyeClient($_we, 'account');
            $result = $wec->call('setBankSMS', array(
                'use' => $_POST['use_banking_sms'],
                'url'=>$root_url
            ));
            $pdo->query("update {$tbl['sms_case']} set use_check=:use_check where `case`=9", array(
                ':use_check' => $_POST['use_banking_sms']
            ));

            unset($_POST['use_banking_sms']);
            break;
		case "pg_charge":
			addField($tbl['order'], 'sale0', 'double(10,2) signed not null default "0.00"');
			addField($tbl['order_product'], 'sale0', 'double(10,2) signed not null default "0.00"');
		break;
		case "smartstore":
			addField($tbl['product'],'nstoreId',"bigint(20) NOT NULL default '0'");
			addField($tbl['product'],'n_store_check',"enum('Y','N') NOT NULL default 'N' COMMENT '스마트스토어 사용여부' ");
			addField($tbl['order'], "smartstore", "enum('N','Y') default 'N' not null");
			addField($tbl['order'], "smartstore_last", "INT(10) default 0 not null");
			addField($tbl['order_product'], "smartstore_ono", "VARCHAR(30) default '' not null");
			addField($tbl['product_nstore'], 'timestamp', 'varchar(100) not null default ""');

			$_POST['n_smart_store'] = trim($_POST['n_smart_store']);
			$_POST['n_smart_id'] = trim($_POST['n_smart_id']);
			$_POST['n_smart_api_id'] = trim($_POST['n_smart_api_id']);

			include_once $engine_dir.'/_config/tbl_schema.php';

			if(isTable($tbl['store_summary']) == false) {
				$pdo->query($tbl_schema['store_summary']);
			}

			if(isTable($tbl['product_nstore']) == false) {
				$pdo->query($tbl_schema['product_nstore']);
			}

			if(isTable("wm_store_summary_type") == false) {
				$result = comm('http://smapi.wisa.ne.kr/summary.php', $param);
				$summary_json = json_decode($result, true);

				$pdo->query($summary_json[0]);
				$pdo->query($summary_json[1]);
			}

			if(isTable('wm_store_summary_list') == false) {
				$list_result = comm('http://smapi.wisa.ne.kr/summary_list.php', $param);
				$summary_list_json = json_decode($list_result, true);

				$pdo->query($summary_list_json[0]);
				$pdo->query($summary_list_json[1]);
			}

			$wec_acc = new weagleEyeClient($_we, 'Etc');
			$wec_acc->call('setSmartstoreOrderCron', array(
				'use_yn' => $_POST['n_smart_store'],
				'root_url' => $root_url,
				'smartstore_id' => $_POST['n_smart_id']
			));
		break;
		case "card_int":
			if(!$_POST['use_alipay']) $_POST['use_alipay']="";
			if(!$_POST['use_paypal']) $_POST['use_paypal']="";
			if(!$_POST['use_cyrexpay']) $_POST['use_cyrexpay']="";
			if(!$_POST['use_sbipay']) $_POST['use_sbipay']="";
			if(!$_POST['use_paypal_c']) $_POST['use_paypal_c']="";
			if(!$_POST['use_wechat']) $_POST['use_wechat']="";
			if(!$_POST['use_alipay_e']) $_POST['use_alipay_e']="";

			if($_POST['use_alipay']){

				$file = $_FILES['alipay_key_cacert'];
				if($_POST['alipay_key_cacert_del'] == 'Y') $_POST['alipay_key_cacert']="";

				if($file['size'] > 0) {
					$ext = getExt($file['name']);
					if($ext != 'pem')  msg('확장자가 pem인 파일만 업로드 가능합니다.');
					$filename = "cacert";
					$file_path = $root_dir.'/_data/alipay/'.$filename.".".$ext;

					$_POST['alipay_key_cacert'] = $file_path;
					uploadFile($file, $filename, '_data/alipay',"","",true);
				}

				$_dir = $dir['upload'].'/alipay';
				$_file = $root_dir.'/'.$_dir.'/card_pay.exe.php'; //return url 생성
				if(!is_dir($root_dir.'/'.$_dir)) makeFullDir($_dir);

				$tmp = '<?
	include_once "../../_config/set.php";
	include_once ($engine_dir."/_engine/card.alipay/card_pay.exe.php");
?>';

				$fp = fopen($_file, 'w');
				fwrite($fp, $tmp);
				fclose($fp);
				unset($tmp);

				$_file = $root_dir.'/'.$_dir.'/note_url.php'; //note url 생성
				if(!is_dir($root_dir.'/'.$_dir)) makeFullDir($_dir);

				$tmp = '<?
	include_once "../../_config/set.php";
	include_once ($engine_dir."/_engine/card.alipay/note_url.php");
?>';
				$fp = fopen($_file, 'w');
				fwrite($fp, $tmp);
				fclose($fp);
				unset($tmp);
			}
		break;
		case "order":
			if(!$_POST['auto_stat3']) $_POST['auto_stat3']="";
			if(!$_POST['auto_stat3_2']) $_POST['auto_stat3_2']="";
			if($_POST['product_restore_use'] != ""){
				$_prstat="@";
				foreach($product_restore_stat as $key=>$val){
					$_prstat .= $val."@";
				}
				$_POST['product_restore_stat']=$_prstat;
			}
			if(!$_POST['bank_name2']) $_POST['bank_name2'] = '';
			if(!$_POST['recipient']) $_POST['recipient'] = '';
			if(!$_POST['bank_price']) $_POST['bank_price'] = '';
			if(!$_POST['ord_list_phone']) $_POST['ord_list_phone']='';
			if(!$_POST['ord_list_mgroup']) $_POST['ord_list_mgroup'] = '';
			if(!$_POST['ord_list_memo_icon']) $_POST['ord_list_memo_icon']='';
			elseif($_POST['ord_list_memo_icon'] == 'Y' && !fieldExist($tbl['order'], 'memo_cnt')) {
				addField($tbl['order'], "memo_cnt", "int(3) NOT NULL default 0");
				$res = $pdo->iterator("SELECT ono, count(*) as cnt FROM `wm_order_memo` group by ono");
                foreach ($res as $mdata) {
					$pdo->query("update $tbl[order] set `memo_cnt`='$mdata[cnt]' where `ono`='$mdata[ono]'");
				}
			}
			if(!$_POST['ord_list_postpone']) $_POST['ord_list_postpone']='';
			if(!$_POST['ord_list_first_prc']) $_POST['ord_list_first_prc']='';

            foreach($_order_stat as $key => $val) {
                if (isset($_POST['order_stat_custom_'.$key]) == true) {
                    $val = trim($_POST['order_stat_custom_'.$key]);
                    if (empty($val) == true) {
                        unset($_POST['order_stat_custom_'.$key]);
                        $scfg->remove('order_stat_custom_'.$key);
                    }
                }
            }
		break;
		case 'order3' :
			if($_POST['use_trash_ord'] == 'Y') { // 주문서 휴지통
				addField($tbl['order'], 'del_stat', 'tinyint(2) not null default "1" comment "삭제 전 주문상태"');
				addField($tbl['order'], 'del_date', 'int(10) not null default "0" comment "휴지통 추가일"');
				addField($tbl['order'], 'del_admin', 'varchar(50) not null default "" comment "휴지통 처리 관리자"');
				addField($tbl['order_product'], 'del_stat', 'tinyint(2) not null default "1" comment "삭제 전 주문상태"');
			}
			if($_POST['ord_list_first_prc'] != 'Y') $_POST['ord_list_first_prc'] = 'N';
		break;
		case 'order2' :
			if(!$_POST['order_add_field_use']) $_POST['order_add_field_use']="";
			if($_POST['order_add_field_use'] == 'Y'){
				addField($tbl['order'], "addressee_id", "varchar(50) after addressee_name");
			}

			$auto_dlv_finish = numberOnly($_POST['auto_dlv_finish']);
			$wec_acc = new weagleEyeClient($_we, 'mall');
			$wec_acc->call('setAutoDlvFinish', array('day'=>$auto_dlv_finish));
			if($wec_acc->error) {
				alert(php2java($wec_acc->result));
				exit;
			}
		break;
		case "milage3":
			if(!$_POST['milage_join_add_info']) $_POST['milage_join_add_info']="";
			if(!$_POST['recom_first_order1']) $_POST['recom_first_order1'] = 'N';
			if(!$_POST['recom_first_order2']) $_POST['recom_first_order2'] = 'N';
		break;
		case 'milage4' :
			if($_POST['use_cpn_milage_msg'] != 'Y') $_POST['use_cpn_milage_msg'] = 'N';
			if($_POST['first_order_milage'] > 0) {
				addField($tbl['member'], 'first_order_milage', 'varchar(20) not null comment "첫구매 적립금 지급 주문"');
			}
		break;
		case 'delivery' :
			if(isset($_POST['delivery_free_milage']) == false) $_POST['delivery_free_milage'] = 'N';
			if(isset($_POST['delivery_prd_free']) == false) $_POST['delivery_prd_free'] = 'N';
			if(isset($_POST['delivery_prd_free2']) == false) $_POST['delivery_prd_free2'] = 'N';
		break;
		case 'prd_dlvprc' :
			if($_POST['use_prd_dlvprc'] == 'Y') {
				include_once $engine_dir.'/_config/tbl_schema.php';
				$pdo->query($tbl_schema['product_delivery_set']);
				addField(
					$tbl['product'],
					'delivery_set',
					'int(5) not null default "0" comment "상품별 배송정책 세트 번호"'
				);
				if(fieldExist($tbl['product'], 'delivery_set') == false) msg('DB 설정 오류');
				addField(
					$tbl['order_product'],
					'prd_dlv_prc',
					'double(10,2) not null default "0.00" comment "상품별 개별 배송비" after buy_ea'
				);
				if(fieldExist($tbl['order_product'], 'prd_dlv_prc') == false) msg('DB 설정 오류');
				addField(
					$tbl['order_product'],
					'repay_prd_dlv_prc',
					'double(10,2) not null default "0.00" comment "환불한 상품별 개별 배송비" after prd_dlv_prc'
				);
				if(fieldExist($tbl['order_product'], 'repay_prd_dlv_prc') == false) msg('DB 설정 오류');
				addField(
					$tbl['order'],
					'prd_dlv_prc',
					'double(10,2) not null default "0.00" comment "상품별 개별 배송비" after dlv_prc'
				);
				if(fieldExist($tbl['order'], 'prd_dlv_prc') == false) msg('DB 설정 오류');
				if($cfg['use_sbscr'] == 'Y') {
					addField(
						$tbl['sbscr_product'],
						'prd_dlv_prc',
						'double(10,2) not null default "0.00" comment "상품별 개별 배송비"'
					);
					if(fieldExist($tbl['sbscr_product'], 'prd_dlv_prc') == false) msg('DB 설정 오류');
				}
			}
		break;
		case "member_jumin":
			if($cfg['member_join_addr'] != 'Y') $cfg['member_join_addr'] = 'N';
			if($cfg['jumin_encode'] == 'N' && $_POST['jumin_encode'] == 'Y') {
				$cfg['jumin_encode'] = 'Y';
				include_once $engine_dir.'/_manage/member/member_jumin_encode.php';
			}
			if($_POST['member_confirm_email'] != 'Y') $_POST['member_confirm_email'] = 'N';
			if($_POST['member_confirm_sms'] != 'Y') $_POST['member_confirm_sms'] = 'N';
			if($_POST['member_confirm_sms'] == 'Y') {
				include_once $engine_dir.'/_config/tbl_schema.php';
				$pdo->query($tbl_schema['join_sms']);
                $data_type = $pdo->row("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE COLUMN_NAME='reg_code' and TABLE_SCHEMA='".$con_info[4]."' and TABLE_NAME='{$tbl['join_sms']}'");
                if ($data_type != 'varchar(100)') {
                    modifyField($tbl['join_sms'], 'reg_code', 'varchar(100) NOT NULL');
                }
			}
			if($_POST['member_reconfirm'] != 'Y') $_POST['member_reconfirm'] = 'N';
			if($_POST['rebirth'] == 'Y') {
				$res = $pdo->iterator("select no, jumin from $tbl[member] where jumin!='' and (birth='' or sex='')");
                foreach ($res as $mdata) {
					$birth = preg_replace('/^([0-9]{2})([0-9]{2})([0-9]{2}).*$/', '$1-$2-$3', $mdata['jumin']);
					$gender = substr($mdata['jumin'], 7, 1);
					$birth = in_array($gender, array(1, 2, 5, 6)) ? '19'.$birth : '20'.$birth;
					$gender = $gender % 2 == 1 ? '남' : '여';

					$asql = '';
					if(!$mdata['birth']) $asql .= ",birth='$birth', birth_type='양'";
					if(!$mdata['sex']) $asql .= ",sex='$gender'";

					$pdo->query("update $tbl[member] set no='$mdata[no]' $asql where no='$mdata[no]'");
				}
			}
			if(!$_POST['password_engnum']) $_POST['password_engnum'] = 'N';
			if(!$_POST['password_special']) $_POST['password_special'] = 'N';
			$_POST['password_min'] = numberOnly($_POST['password_min']);
			$_POST['password_max'] = numberOnly($_POST['password_max']);
			if($_POST['password_max'] > 4 && $_POST['password_min'] >= $_POST['password_max']) {
				msg('패스워드 최소길이가 패스워드 최대 길이보다 클수 없습니다.');
			}
			if($_POST['use_whole_mem'] == "Y") {
				if(!fieldExist($tbl['member'], 'whole_mem')) {
					addField($tbl['member'], 'whole_mem', 'enum("Y","N") not null default "N"');
					$pdo->query("alter table $tbl[member] add index whole_mem (whole_mem)");
				}
			}
			if(isset($_POST['member_join_nm_num']) == false) $_POST['member_join_nm_num'] = 'N';
			if(isset($_POST['member_join_nm_spc']) == false) $_POST['member_join_nm_spc'] = 'N';

			for($i = 1; $i <= 3; $i++) {
				$fn = 'name_filter_'.$i;
				$name_filter = trim(preg_replace('/\"|\'|\s|\||\\//', '', $_POST[$fn]));
				$name_filter = preg_replace('/,+/', ',', trim($name_filter, ','));
				$name_filter = addslashes($name_filter);
				if($pdo->row("select count(*) from {$tbl['default']} where code='$fn'") >0) {
					$pdo->query("update $tbl[default] set value='$name_filter' where code='$fn'");
				} else {
					$pdo->query("insert into {$tbl['default']} (code, value) values ('$fn', '$name_filter')");
				}
				unset($_POST[$fn]);
			}
			if($_POST['join_birth_use'] != "Y" || ($_POST['join_birth_use'] == "Y" && $_POST['member_join_birth'] == "N")) {
				if($_POST['join_14_limit_method'] == 1) $_POST['join_14_limit_method'] = '';
			}
			//사업자번호 API
			if($_POST['use_biz_api_yn'] == 'Y' && !$_POST['use_biz_api_skey']) msg('사업자회원 API 사이트 키를 입력해 주세요.');
		break;
		case "member_search":
			if($_POST['find_search'] != 'Y') $_POST['find_search'] = '';
		break;
		case "product_qna":
			if(!$_POST['qna_protect_name_strlen'])		$_POST['qna_protect_name_strlen']="1";
			if(!$_POST['qna_protect_name_suffix'])		$_POST['qna_protect_name_suffix']="**";
			if(!$_POST['qna_protect_id_strlen'])		$_POST['qna_protect_id_strlen']="3";
			if(!$_POST['qna_protect_id_suffix'])		$_POST['qna_protect_id_suffix']="****";

			$_POST['product_qna_row'] = numberOnly($_POST['product_qna_row']);
			//if($_POST['product_qna_row'] > 100 || $_POST['product_qna_row'] < 1) {
			//	msg('한페이지 글수는 1~100개 사이로 입력해 주세요.');
			//}

			$fsubject = addslashes($_POST['fsubject']);
			if($pdo->row("select count(*) from $tbl[default] where code='qna_fsubject'") > 0) {
				$pdo->query("update $tbl[default] set value='$fsubject' where code='qna_fsubject'");
			} else {
				if($fsubject) {
					$pdo->query("insert into $tbl[default] (code, value) values ('qna_fsubject', '$fsubject')");
				}
			}

			if($_POST['use_trash_qna'] == 'Y') {
				include_once $engine_dir.'/_config/tbl_schema.php';
				if(!isTable($tbl['common_trashbox'])) {
					$pdo->query($tbl_schema['common_trashbox']);
				}
			}

			$_POST['product_qna_able_ext'] = preg_replace('/[^0-9a-z,]/', '', strtolower($_POST['product_qna_able_ext']));
			$_POST['product_qna_able_ext'] = trim(preg_replace('/,+/', ',', $_POST['product_qna_able_ext']), ',');
			$_POST['product_qna_able_ext'] = str_replace(',', '|', $_POST['product_qna_able_ext']);
		break;
		case "product_review":
			if(!$_POST['review_protect_name_strlen'])	$_POST['review_protect_name_strlen']="1";
			if(!$_POST['review_protect_name_suffix'])	$_POST['review_protect_name_suffix']="**";
			if(!$_POST['review_protect_id_strlen'])		$_POST['review_protect_id_strlen']="3";
			if(!$_POST['review_protect_id_suffix'])	$_POST['review_protect_id_suffix']="****";
			if($_POST['product_review_atype_detail'] != 'Y') $_POST['product_review_atype_detail'] = 'N';

			$_POST['product_review_row'] = numberOnly($_POST['product_review_row']);
			//if($_POST['product_review_row'] > 100 || $_POST['product_review_row'] < 1) {
			//	msg('한페이지 글수는 1~100개 사이로 입력해 주세요.');
			//}

			$fsubject = addslashes($_POST['fsubject']);
			if($pdo->row("select count(*) from $tbl[default] where code='review_fsubject'") > 0) {
				$pdo->query("update $tbl[default] set value='$fsubject' where code='review_fsubject'");
			} else {
				if($fsubject) {
					$pdo->query("insert into $tbl[default] (code, value) values ('review_fsubject', '$fsubject')");
				}
			}

			if($_POST['use_trash_rev'] == 'Y') {
				include_once $engine_dir.'/_config/tbl_schema.php';
				if(!isTable($tbl['common_trashbox'])) {
					$pdo->query($tbl_schema['common_trashbox']);
				}
			}
		break;
		case 'autobill_pg' :
			if($_POST['autobill_pg'] == 'dacom') {
				makeFullDir('_data/Xpay/conf');
				makeFullDir('_data/Xpay/log');
				chmod($root_dir.'/_data/Xpay/log', 0777);

				copy($engine_dir.'/_engine/card.dacom/XpayAutoBilling/lgdacom/conf/ca-bundle.crt', $root_dir.'/_data/Xpay/conf/ca-bundle.crt');
				copy($engine_dir.'/_engine/card.dacom/XpayAutoBilling/lgdacom/conf/lgdacom.conf', $root_dir.'/_data/Xpay/conf/lgdacom.conf');

				$mall_conf  = "server_id = 01\n";
				$mall_conf .= "timeout = 60\n";
				$mall_conf .= "log_level = 4\n";
				$mall_conf .= "verify_cert = 1\n";
				$mall_conf .= "verify_host = 1\n";
				$mall_conf .= "report_error = 1\n";
				$mall_conf .= "output_UTF8 = 1\n";
				$mall_conf .= "auto_rollback = 1\n";
				$mall_conf .= "log_dir = ".$root_dir."/_data/Xpay/log\n";
				$mall_conf .= "t{$_POST['card_auto_dacom_id']} = {$_POST['card_dacom_auto_key']}\n";
				$mall_conf .= "{$_POST['card_auto_dacom_id']} = {$_POST['card_dacom_auto_key']}";

				if(fwriteTo('_data/Xpay/conf/mall.conf', $mall_conf, 'w') == false) {
					msg('PG설정을 저장하는중 오류가 발생하였습니다.');
				}
			}
		break;
		case 'mobile_dacom_card' :
		case "card_pg":
			if($card_pg == "inicis" || $card_pg == "dacom"){
				$pdo->query("alter table `$tbl[card]` modify `tno` varchar(50) not null");
				$pdo->query("alter table `$tbl[vbank]` modify `tno` varchar(50) not null");
			}
			foreach($_POST as $pkey=>$pval) {
				$_POST[$pkey] = trim($pval);
			}
			//if($_POST['mobile_pg_use'] != 'Y') $_POST['mobile_pg_use'] = 'N';
			if(($_POST['card_pg'] == 'dacom' && ($_POST['pg_version'] == 'Xpay'|| $_POST['pg_version'] == 'XpayNon')) || $_POST['card_mobile_pg'] == 'dacom') {
				include_once $engine_dir.'/_engine/include/file.lib.php';

				if($_POST['card_pg']) {
					//$xpay_type = 'Xpay';
					$xpay_type = $_POST['pg_version'];
					$pg_version = $_POST['pg_version'];
					$xpay_id = $_POST['card_dacom_id'];
					$xpay_key = $_POST['card_dacom_key'];
					if(!$xpay_id) $xpay_id = $cfg['card_dacom_id'];
					if(!$xpay_key) $xpay_key = $cfg['card_dacom_key'];
				} else {
					$xpay_type = 'smartXpay';
					$pg_version = $_POST['pg_mobile_version'];
					$xpay_id = $_POST['card_mobile_dacom_id'];
					$xpay_key = $_POST['card_mobile_dacom_key'];
					if(!$xpay_id) $xpay_id = $cfg['card_mobile_dacom_id'];
					if(!$xpay_key) $xpay_key = $cfg['card_mobile_dacom_key'];
				}

				makeFullDir('_data/'.$xpay_type.'/conf');
				makeFullDir('_data/'.$xpay_type.'/log');
				chmod($root_dir.'/_data/'.$xpay_type.'/log', 0777);

				copy($engine_dir.'/_engine/card.dacom/'.$pg_version.'/conf/ca-bundle.crt', $root_dir.'/_data/'.$xpay_type.'/conf/ca-bundle.crt');
				copy($engine_dir.'/_engine/card.dacom/'.$pg_version.'/conf/lgdacom.conf', $root_dir.'/_data/'.$xpay_type.'/conf/lgdacom.conf');
				//copy($engine_dir.'/_engine/card.dacom/'.$pg_version.'/card_finish.exe.php', $root_dir.'/_data/'.$xpay_type.'/card_finish.exe.php');

				$mall_conf  = "server_id = 01\n";
				$mall_conf .= "timeout = 60\n";
				$mall_conf .= "log_level = 4\n";
				$mall_conf .= "verify_cert = 1\n";
				$mall_conf .= "verify_host = 1\n";
				$mall_conf .= "report_error = 1\n";
				$mall_conf .= "output_UTF8 = 1\n";
				$mall_conf .= "auto_rollback = 1\n";
				$mall_conf .= "log_dir = ".$root_dir."/_data/".$xpay_type."/log\n";
				$mall_conf .= $xpay_id." = ".$xpay_key."\n";
				$mall_conf .= 't'.$xpay_id." = ".$xpay_key."\n";

				$fp = fopen($root_dir.'/_data/'.$xpay_type.'/conf/mall.conf', 'w');
				if($fp) {
					fwrite($fp, $mall_conf);
					fclose($fp);
					chmod($root_dir.'/_data/'.$xpay_type.'/conf/mall.conf', 0777);
				} else {
					msg('U+ Xpay 설정을 저장하는중 오류가 발생하였습니다.');
				}
			}
			if($_POST['card_pg'] == 'dacom') {
				if(isset($_POST['xpay_use_paynow']) == false) $_POST['xpay_use_paynow'] = 'N';
				if(isset($_POST['sxpay_use_paynow']) == false) $_POST['sxpay_use_paynow'] = 'N';
			}

			if($_POST['card_pg']) {
				switch($_POST['card_pg']) {
					case 'dacom' : $pg_id = $_POST['card_dacom_id']; break;
					case 'kcp' : $pg_id = $_POST['card_site_cd']; break;
					case 'allat' : $pg_id = $_POST['card_partner_id']; break;
					case 'allat' : $pg_id = $_POST['card_partner_id']; break;
					case 'allat' : $pg_id = $_POST['card_partner_id']; break;
					case 'allthegate' : $pg_id = $_POST['allthegate_StoreId']; break;
					case 'kspay' : $pg_id = $_POST['kspay_storeid']; break;
					case 'nicepay' : $pg_id = $_POST['nicepay_mid']; break;
					case 'inicis' :
						if($_POST['pg_version'] == '') $pg_id = $_POST['card_mall_id'];
						else if($_POST['pg_version'] == 'INILite') $pg_id = $_POST['card_inicis_id'];
						else if($_POST['pg_version'] == 'INIweb') $pg_id = $_POST['card_web_id'];
					break;
				}

				$wec = new weagleEyeClient($_we, 'Etc');
				$wec->call('setExternalService', array(
					'service_name' => 'card_pg',
					'use_yn' => 'Y',
					'root_url' => $root_url,
					'extradata' => $_POST['card_pg'].'@'.$pg_id,
                    'client_ip' => $_SERVER['REMOTE_ADDR']
				));
			}
			include $engine_dir.'/_manage/config/config_pg.inc.php';
		break;
		case "intra":
			if($_POST['intra_day_check']) {
				$_POST['intra_day_check_start'] = $_POST['intra_day_check_st'].':'.$_POST['intra_day_check_sm'];
				$_POST['intra_day_check_end'] = $_POST['intra_day_check_et'].':'.$_POST['intra_day_check_em'];
			}
		break;
		case 'dtd' :
			$check_dtd = trim($_POST['frontDTD']);
			if(preg_match('/^<!DOCTYPE[^><]+>$/', $check_dtd) == false && $check_dtd != '') msg('정상적인 DTD가 아닙니다.');
			if(!$_POST['compatible_edge']) $_POST['compatible_edge'] = 'N';
			if(!$_POST['br_title_cate'] || $_POST['br_title_prd'] != 1) $_POST['br_title_cate'] = 'N';

			$head = addslashes(trim($_POST['head']));
			if($head) {
				$pdo->query("insert into $tbl[default] (code, value) values ('head_$now', '$head')");
			}
		break;
		case 'erp' :
			$erp_stock_undo = $_POST['erp_stock_undo'];
			$_POST['erp_stock_undo'] = implode(',', $erp_stock_undo);
			if($cfg['erp_auto_hold'] == 'Y' || $cfg['erp_auto_release'] == 'Y') {
				addField($tbl['order_product'], 'dlv_hold_order', 'int(4) not null default 0 after dlv_hold');
			}
			if($cfg['erp_force_limit'] == 'Y') {
				addField('erp_complex_option', 'limit_qty', 'int(4) not null default 0 after force_soldout');
			}
		break;
		case 'cash_receipt' :
			if(!$cfg['cash_receipt_auto_date']) {
				$_POST['cash_receipt_auto_date'] = ($_POST['cash_receipt_auto'] == 'Y') ? $now : '';
			} else {
				$_POST['cash_receipt_auto_date'] = ($_POST['cash_receipt_auto'] != 'Y') ? '' : $cfg['cash_receipt_auto_date'];
			}
			if(!$_POST['cash_receipt_ness']) $_POST['cash_receipt_ness']="N";
		break;
		case 'shop_info' :
			if($cfg['cash_receipt_use'] == 'Y' && $cfg['cash_r_pg'] == 'dacom') {
				if((numberOnly($cfg['company_biz_num']) != numberOnly($_POST['company_biz_num'])) || ($cfg['company_owner'] != $_POST['company_owner']) || ($cfg['company_addr1'] != $_POST['company_addr1']) ||  ($cfg['company_addr2'] != $_POST['company_addr2']) || ($cfg['company_name'] != $_POST['company_name'])) {
					include_once $engine_dir.'/_engine/include/ext.lib.php';
					$b_num_ck=checkBizNo($_POST[company_biz_num]);
					if(!$b_num_ck) msg("유효하지 않은 사업자 번호입니다");
					checkBlank(trim($_POST['company_name']), '상호를 입력해주세요.');
					checkBlank(trim($_POST['company_phone']), '사업자 전화번호를 입력해주세요.');
					checkBlank(trim($_POST['company_owner']), '사업자 대표자 성명을 입력해주세요.');
					checkBlank(trim($_POST['company_addr1']), '사업장 주소를 입력해주세요.');
					include_once $engine_dir.'/_manage/config/cash_receipt_comm.exe.php';
				}
			}
		break;
		case 'common' :
			if (!$_POST['prd_prd_code']) $_POST['prd_prd_code'] = '';
			if (!$_POST['prd_reg_date']) $_POST['prd_reg_date'] = '';
			if (!$_POST['prd_normal_prc']) $_POST['prd_normal_prc'] = '';
			if (!$_POST['prd_name_referer']) $_POST['prd_name_referer'] = '';
            if (!$_POST['prd_origin_name']) $_POST['prd_origin_name'] = '';
            if (!$_POST['prd_seller']) $_POST['prd_seller'] = '';
            if (!$_POST['prd_origin_prc']) $_POST['prd_origin_prc'] = '';
		break;
		case 'compare' :
			if($_POST['compare_image_no'] === '0' && !fieldExist($tbl['product'], 'upfile0')) {
				addField($tbl['product'], 'upfile0', 'varchar(100) after upfile3');
				addField($tbl['product'], 'w0', 'int(4) after w3');
				addField($tbl['product'], 'h0', 'int(4) after h3');
			}
			if($_POST['import_flag_use'] == "Y") {
				addField($tbl['product'], 'import_flag', 'enum("Y","N") not null default "N"');
			}
			if($_POST['compare_today_start_use'] == "Y") {
				if(!fieldExist($tbl['product'], 'compare_today_start')) {
					addField($tbl['product'], 'compare_today_start', 'enum("Y","N") not null default "N"');
				}
			}
            if ($_POST['use_navershopping_book'] == 'Y') {
                if (fieldExist($tbl['product'], 'is_book') == false) {
                    addField($tbl['product'], 'is_book', 'enum("N", "P", "E", "A") not null default "N"');
                    $pdo->query("alter table {$tbl['product']} add INDEX is_book(is_book)");

                    require __ENGINE_DIR__.'/_config/tbl_schema.php';
                    $pdo->query($tbl_schema['product_book']);
                }
            }

			$wec = new weagleEyeClient($_we, 'Etc');
			$wec->call('setExternalService', array(
				'service_name' => 'navershopping',
				'use_yn' => ($_POST['compare_use'] == 'Y' ? 'Y' : 'N'),
				'root_url' => $root_url,
				'extradata' => ''
			));
		break;
		case 'ems_use' :
			if($_POST['use_ems'] == 'Y') {
				if(!fieldExist($tbl['product'], 'weight')) {
					$pdo->query("alter table `$tbl[product]` add `weight` int(10) not null default '0' after origin_prc");
					$pdo->query("alter table `$tbl[order]` add `nations` varchar(30) NOT NULL");
				}
			}
		break;
		case 'up_aimg' :
			include_once $engine_dir.'/_engine/include/file.lib.php';
			if($_POST['up_aimg_sort'] == 'Y' && !fieldExist($tbl['product_image'], "sort")) {
				addField($tbl['product_image'], "sort", "int(3) NULL DEFAULT NULL COMMENT '정렬순서'");
				$res = $pdo->iterator("SELECT `pno`, count(*) as cnt FROM `wm_product_image` where `filetype` in (2,8) group by `pno`");
                foreach ($res as $pdata) {
					$res2 = $pdo->iterator("SELECT `no` FROM `wm_product_image` where `filetype` in (2,8) and `pno`='$pdata[pno]' order by `no` asc");
                    foreach ($res2 as $pdata2) {
						$pdo->query("update `{$tbl['product_image']}` set `sort`='$pdata[cnt]' where `no`='$pdata2[no]'");
						$pdata['cnt']--;
					}
				}
			}
			$updir = "/_data/_default/prd/";
			if(!is_dir($root_dir.$updir)) makeFullDir($updir);
			for($ii=1; $ii<=3; $ii++) {
				if($_FILES['upfile'.$ii][tmp_name]) {
					deleteAttachFile($updir, $_FILES['upfile'.$ii]);
					$ext = getExt($_FILES['upfile'.$ii]['name']);
					$up_filename=md5($ii+1+time());
					$_POST['noimg'.$ii.'_mng'] = $updir.$up_filename.'.'.$ext;
					$up_info=uploadFile($_FILES['upfile'.$ii],$up_filename,$updir,"jpg|jpeg|gif|png|bmp|swf|flv");
				}
			}

            // 성인 상품 대체 섬네일
            if ($_FILES['upfile_adult'] && $_FILES['upfile_adult']['tmp_name']) {
                if ($cfg['thumb_adult']) {
                    deleteAttachFile($updir, $cfg['thumb_adult']);
                }
                $img = getImagesize($_FILES['upfile_adult']['tmp_name']);
                $up_filename = md5(microtime());
                $up_info = uploadFile($_FILES['upfile_adult'], $up_filename, $updir, 'jpg|jpeg|gif|png');
                $_POST['thumb_adult'] = $up_info[0];
                $_POST['thumb_adult_w'] = $img[0];
                $_POST['thumb_adult_h'] = $img[1];
            }
		break;
		case 'crema' :
			$crema_auto_use = $_POST['crema_auto_use'];
			$wec_acc = new weagleEyeClient($_we, 'mall');
			$wec_acc->call('setAutoCrema', array('auto_use'=>$crema_auto_use));
			if($wec_acc->error) {
				alert(php2java($wec_acc->result));
				exit;
			}

			$wec = new weagleEyeClient($_we, 'Etc');
			$wec->call('setExternalService', array(
				'service_name' => 'crema',
				'use_yn' => (empty($_POST['crema_app_id']) == true ? 'N' : 'Y'),
				'root_url' => $root_url,
				'extradata' => $_POST['crema_app_id']
			));
		break;
		case 'tax' :
			if(count($_POST['tax_add_limit']) > 0){
				foreach($_POST["tax_add_limit"] as $k=>$v){
					$_POST["tax_add_limit${k}"] = $v;
				}
			}
			if(count($_POST['tax_add_per']) > 0){
				foreach($_POST["tax_add_per"] as $k=>$v){
					$_POST["tax_add_per${k}"] = $v;
				}
			}

		break;
		case 'bbs_common' :
			if($_POST['use_trash_bbs'] == 'Y') {
				include_once $engine_dir.'/_config/tbl_schema.php';
				if(!isTable($tbl['common_trashbox'])) {
					$pdo->query($tbl_schema['common_trashbox']);
				}

				if(isAutoIncrement('mari_board') < 1) {
					$pdo->query("alter table mari_board drop index no");
					$pdo->query("alter table mari_board add primary key(no)");
					$pdo->query("alter table mari_board change no no int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '고유번호'");
				}
			}
		break;
		case "easypay" :
			if($_POST['use_payco'] == 'Y') {
				include_once $engine_dir.'/_engine/include/file.lib.php';
				makeFullDir('_data/compare/payco');

				$content  = "<?PHP\n";
				$content .= "include '../../../_config/set.php';\n";
				$content .= "include \$engine_dir.'/_engine/card.payco/get_dlv_prc.exe.php';\n";
				$content .= "?>";
				$fp = fopen($root_dir.'/_data/compare/payco/delivery.php', 'w');
				fwrite($fp, $content);
				fclose($fp);

				$_POST['payco_CpId'] = $_POST['payco_sellerKey'];
				$_POST['payco_productId'] = $_POST['payco_sellerKey'].'_EASYP';
				$_POST['payco_productId2'] = 'DELIVERY_PROD';
			}

			if(isset($_POST['use_talkpay']) == true) {
                if ($_POST['use_talkpay'] == 'Y') {
                    if ($scfg->comp('talkpay_ShopID') == false) {
                        $wec = new weagleEyeClient($_we, 'account');
                        $ret = $wec->call('getAccountByAPI');
                        $ret = json_decode($ret);
                        if (!$ret->account_id) {
                            msg('계정 정보를 가져올수 없습니다.');
                        }
                        $_POST['talkpay_ShopID'] = $cfg['talkpay_ShopID'] = $ret->account_id;
                    }

                    if (empty($_POST['talkpay_ShopKey']) == true) {
                        msg('ShopKey키를 입력해주세요.');
                    } else {
                        $cfg['talkpay_ShopKey'] = trim($_POST['talkpay_ShopKey']);
                        $talkpay = new KakaoTalkPay($scfg);
                        $ret = $talkpay->mapping();
                        if ($ret->authKey) {
                            $_POST['talkpay_authkey'] = $ret->authKey;
                            $talkpay->serviceOn();

                            // 카카오페이구매 오픈 시 전체 상품을 카카오페이구매 사용함으로 변경
                            require_once __ENGINE_DIR__.'/_engine/include/migration/cfg_stock_convert.inc.php';
                            addField($tbl['product'], 'use_talkpay', "enum('N','Y') not null default 'N' after checkout");
                            $pdo->query("update {$tbl['product']} set use_talkpay='Y' where ea_type=1");
                        } else {
                            msg($ret->message."\\n카카오페이구매 연동오류가 확인되었습니다. 고객센터로 문의해주세요.");
                        }
                    }
                } else {
                    // 카카오페이구매 서비스 비활성화
                    if ($cfg['use_talkpay'] == 'Y') {
                        $talkpay = new KakaoTalkPay($scfg);
                        $talkpay->serviceOff();
                    }
                }

                if (isset($_POST['talkpay_btn_snack_mb']) == false) $_POST['talkpay_btn_snack_mb'] = '';

				$wec = new weagleEyeClient($_we, 'Etc');
				$wec->call('setExternalService', array(
					'service_name' => 'pg_talkpay',
					'use_yn' => ($_POST['use_talkpay'] == 'Y' ? 'Y' : 'N'),
					'root_url' => $root_url,
					'extradata' => $_POST['talkpay_ShopKey'],
                    'client_ip' => $_SERVER['REMOTE_ADDR']
				));

                if ($_POST['use_talkpay'] == 'Y') {
                    include_once $engine_dir.'/_engine/include/file.lib.php';
                    makeFullDir('_data/compare/kakao');

                    $source  = "<?php\n";
                    $source .= "include '../../../_config/set.php';\n";
                    $source .= "if (!\$_GET['type']) \$_GET['type'] = 'order';\n";
                    $source .= "include \$engine_dir.'/_engine/cron/cron_talkpay_'.strtolower(\$_GET['type']).'.exe.php';\n";
                    $source .= "?>";
                    fwriteTo('_data/compare/kakao/talkpay_callback.php', $source, 'w');

                    $source  = "<?php\n";
                    $source .= "include '../../../_config/set.php';\n";
                    $source .= "include \$engine_dir.'/_engine/promotion/talkpay_product_summary.exe.php';\n";
                    $source .= "?>";
                    fwriteTo('_data/compare/kakao/talkpay_product_summary.php', $source, 'w');

                    $source  = "<?php\n";
                    $source .= "include '../../../_config/set.php';\n";
                    $source .= "include \$engine_dir.'/_engine/promotion/talkpay_product.exe.php';\n";
                    $source .= "?>";
                    fwriteTo('_data/compare/kakao/talkpay_product_simple.php', $source, 'w');

                    $source  = "<?php\n";
                    $source .= "include '../../../_config/set.php';\n";
                    $source .= "\$mode = 'detail';\n";
                    $source .= "include \$engine_dir.'/_engine/promotion/talkpay_product.exe.php';\n";
                    $source .= "?>";
                    fwriteTo('_data/compare/kakao/talkpay_product.php', $source, 'w');

                    $source  = "<?php\n";
                    $source .= "include '../../../_config/set.php';\n";
                    $source .= "\$mode = 'detail';\n";
                    $source .= "include \$engine_dir.'/_engine/promotion/talkpay_review.exe.php';\n";
                    $source .= "?>";
                    fwriteTo('_data/compare/kakao/talkpay_review.php', $source, 'w');

                    $source  = "<?php\n";
                    $source .= "include '../../../_config/set.php';\n";
                    $source .= "include \$engine_dir.'/_engine/promotion/talkpay_detail.exe.php';\n";
                    $source .= "?>";
                    fwriteTo('_data/compare/kakao/talkpay_detail.php', $source, 'w');

                    addField($tbl['order'], 'external_order', 'varchar(10) not null default ""');
                    addField($tbl['order_product'], 'external_id', 'varchar(30) not null default ""');
                    addField($tbl['order_product'], 'external_last_chg', 'int(10) not null default 0');

                    $pdo->query("alter table {$tbl['order']} add index external_order(external_order)");
                    $pdo->query("alter table {$tbl['order_product']} add index external_id(external_id)");
                    $pdo->query("alter table {$tbl['order_product']} add index external_last_chg(external_last_chg)");

                    addField($tbl['qna'], 'external_id', 'varchar(15) not null default ""');
                    addField($tbl['qna'], 'external_answer_id', 'varchar(15) not null default ""');
                    $pdo->query("alter table {$tbl['qnd']} add index external_id(external_id)");

                    addField($tbl['review'], 'external_id', 'varchar(10) not null default ""');
                    $pdo->query("alter table {$tbl['review']} add index external_id(external_id)");
                }
			}

			if(isset($_POST['use_payco']) == true) {
				$wec = new weagleEyeClient($_we, 'Etc');
				$wec->call('setExternalService', array(
					'service_name' => 'pg_payco',
					'use_yn' => ($_POST['use_payco'] == 'Y' ? 'Y' : 'N'),
					'root_url' => $root_url,
					'extradata' => $_POST['payco_sellerKey'],
                    'client_ip' => $_SERVER['REMOTE_ADDR']
				));
			}

			if(isset($_POST['use_kakaopay']) == true) {
				$wec = new weagleEyeClient($_we, 'Etc');
				$wec->call('setExternalService', array(
					'service_name' => 'pg_kakaopay',
					'use_yn' => ($_POST['use_kakaopay'] == 'Y' ? 'Y' : 'N'),
					'root_url' => $root_url,
					'extradata' => $_POST['kakao_cid'],
                    'client_ip' => $_SERVER['REMOTE_ADDR']
				));
			}

			if(isset($_POST['use_tosspayment']) == true) {
				$wec = new weagleEyeClient($_we, 'Etc');
				$wec->call('setExternalService', array(
					'service_name' => 'pg_toss',
					'use_yn' => ($_POST['use_tosspayment'] == 'Y' ? 'Y' : 'N'),
					'root_url' => $root_url,
					'extradata' => $_POST['tosspayment_api_key'],
                    'client_ip' => $_SERVER['REMOTE_ADDR']
				));
			}

			if(isset($_POST['use_tosscard']) == true) {
				$wec = new weagleEyeClient($_we, 'Etc');
				$wec->call('setExternalService', array(
					'service_name' => 'pg_tosscard',
					'use_yn' => ($_POST['use_tosscard'] == 'Y' ? 'Y' : 'N'),
					'root_url' => $root_url,
					'extradata' => $_POST['tossc_liveApiKey'],
                    'client_ip' => $_SERVER['REMOTE_ADDR']
				));
			}

            if (isset($_POST['use_samsungpay']) == true) {
                $wec = new weagleEyeClient($_we, 'Etc');
                $wec->call('setExternalService', array(
                    'service_name' => 'pg_samsungpay',
                    'use_yn' => ($_POST['use_samsungpay'] == 'Y' ? 'Y' : 'N'),
                    'root_url' => $root_url,
                    'extradata' => $_POST['samsungpay_id'].' @ '.$_POST['samsungpay_pwd'],
                    'client_ip' => $_SERVER['REMOTE_ADDR']
                ));
            }

			if(isset($_POST['use_nsp']) == true) {
				$wec = new weagleEyeClient($_we, 'Etc');
				$wec->call('setExternalService', array(
					'service_name' => 'pg_nsp',
					'use_yn' => ($_POST['use_nsp'] == 'Y' ? 'Y' : 'N'),
					'root_url' => $root_url,
					'extradata' => $_POST['nsp_partnerId'],
                    'client_ip' => $_SERVER['REMOTE_ADDR']
				));

                $pdo->query("
                    alter table {$tbl['card']} change good_name good_name varchar(128)
                ");

                addField(
                    $tbl['card'],
                    'wm_free_price',
                    'double(10,2) NULL DEFAULT "0.00" COMMENT "면세 금액" after wm_price'
                );

                addField(
                    $tbl['sbscr_schedule'],
                    'taxfree_prc',
                    'double(10,2) NULL DEFAULT "0.00" COMMENT "면세 금액" after total_prc'
                );

                include_once $engine_dir.'/_config/tbl_schema.php';
                $pdo->query($tbl_schema['subscription_key']);
			}
		break;
		case 'temp_common' :
			addField('mari_config', 'tmp_name', 'text not null default ""');
			for($i = 4; $i <= $_POST['board_add_temp']; $i++) {
				addField('mari_board', 'temp'.$i, 'varchar(200) not null default ""');
			}
		break;
		case 'deleteMember':
			if(isset($_POST['del_send_type1']) == false) $_POST['del_send_type1'] = 'N';
			if(isset($_POST['del_send_type2']) == false) $_POST['del_send_type2'] = 'N';
			$delete_sms = $pdo->row("select `no` from `$tbl[sms_case]` where `case` = 28");
			if(!$delete_sms) {
				$msg = "[".$cfg['company_mall_name']."] {이름}님, {휴면처리일}에 휴면회원으로 전환될 예정입니다.";
			    $pdo->query("insert into `$tbl[sms_case]` (`case`,`msg`,`use_check`,`sms_night`, alimtalk_code, mng_push) values ('28','$msg','Y','N', '', '')");
			}
			if($_POST['use_dormancy'] == 'Y') {
				$pdo->query("alter table $tbl[member] change withdraw withdraw enum('N','Y','D1','D2') not null default 'N'");
			}
			$wec_acc = new weagleEyeClient($_we, 'account');
			$wec_acc->call('setAutoMemberDelete', array('use'=>$_POST['use_dormancy'], 'root_url'=>$root_url));
		break;
		case 'mobile_set' :
			if($_POST['mobile_use'] == 'Y') {
				if(fieldExist($tbl['member'], 'mobile') == false) {
					addField($tbl['member'], 'mobile', 'char(1) not null default ""');
					$pdo->query("alter table $tbl[member] add index mobile(mobile)");
				}
			}
		break;
		case 'ga' :
			if($_POST['use_ga_UserID'] != 'Y') $_POST['use_ga_UserID'] = 'N';
			if($_POST['use_ga_ecommerce'] != 'Y') $_POST['use_ga_ecommerce'] = 'N';
			if($_POST['use_ga_enhanced_ec'] != 'Y') $_POST['use_ga_enhanced_ec'] = 'N';

			$wec = new weagleEyeClient($_we, 'Etc');
			$wec->call('setExternalService', array(
				'service_name' => 'googleAnalytics',
				'use_yn' => ($_POST['use_ga'] == 'Y' ? 'Y' : 'N'),
				'root_url' => $root_url,
				'extradata' => $_POST['ga_code']
			));
		break;
        case 'ge' :
			$wec = new weagleEyeClient($_we, 'Etc');
			$wec->call('setExternalService', array(
				'service_name' => 'google feed',
				'use_yn' => ($_POST['use_ge'] == 'Y' ? 'Y' : 'N'),
				'root_url' => $root_url,
				'extradata' => ''
			));
			$wec->call('setGoogleFeedCron', array(
				'use_yn' => ($_POST['use_ge'] == 'Y' ? 'Y' : 'N'),
				'root_url' => $root_url,
			));

            // 최초 갱신
            $_REQUEST['site_key'] = $_we['wm_key_code'];
            if ($scfg->comp('use_ge', 'Y') == false) {
                $scfg->set('ge_image_no', $_POST['ge_image_no']);
                require_once __ENGINE_DIR__.'/_engine/cron/cron_ge.exe.php';
            }
        break;
		case 'captcha' :
			if($_POST['usecap_member_qna'] != 'Y') $_POST['usecap_member_qna'] = '';
			if($_POST['usecap_nonmember_qna'] != 'Y') $_POST['usecap_nonmember_qna'] = '';
			if($_POST['usecap_member_review'] != 'Y') $_POST['usecap_member_review'] = '';
			if($_POST['usecap_nonmember_review'] != 'Y') $_POST['usecap_nonmember_review'] = '';
			if($_POST['usecap_member_to'] != 'Y') $_POST['usecap_member_to'] = '';
			if($_POST['usecap_nonmember_to'] != 'Y') $_POST['usecap_nonmember_to'] = '';
			$con_res = $pdo->iterator("select * from `mari_config` order by no");
            foreach ($con_res as $condata) {
				if($_POST['usecap_member_'.$condata['db']] != 'Y') $_POST['usecap_member_'.$condata['db']] = '';
				if($_POST['usecap_nonmember_'.$condata['db']] != 'Y') $_POST['usecap_nonmember_'.$condata['db']] = '';
			}
		break;
		case 'happytalk' :
			include_once $engine_dir.'/_engine/include/file.lib.php';
			$updir = "/_data/_default/happytalk/";
			if(!is_dir($root_dir.$updir)) makeFullDir($updir);
				if($_FILES['upfile1'][tmp_name]) {
					deleteAttachFile($updir, $_FILES['upfile1']);
					$ext = getExt($_FILES['upfile1']['name']);
					$up_filename=md5(time());
					$_POST['happytalk_img'] = $updir.$up_filename.'.'.$ext;
					$up_info=uploadFile($_FILES['upfile1'],$up_filename,$updir,"jpg|jpeg|gif|png|bmp|swf|flv");
				}
		break;
		case 'pwd_change' :
			if($_POST['use_pwd_change'] == 'Y') {
				addField($tbl['member'], 'change_pwd_date', "int(11) not null");
				addField($tbl['member'], 'change_pwd_next', "enum('N', 'Y') not null default 'N'");
			}
		break;
		case 'notify_restock_config' : // 재입고 알림 설정
			if(!$_POST['notify_restock_type_l'] && !$_POST['notify_restock_type_f']) msg('허용 품절방식을 설정해 주세요.');
			if($_POST['notify_restock_type_l'] != 'Y') $_POST['notify_restock_type_l'] = 'N';
			if($_POST['notify_restock_type_f'] != 'Y') $_POST['notify_restock_type_f'] = 'N';
			$_POST['notify_restock_min_qty'] = numberOnly($_POST['notify_restock_min_qty']);
			if(!$_POST['notify_restock_min_qty']) $_POST['notify_restock_min_qty'] = 0;
			if($_POST['notify_restock_use'] == "Y") {
				// 재입고 알림 테이블 체크 후 생성
				if(!isTable($tbl['notify_restock'])) {
					include_once $engine_dir.'/_config/tbl_schema.php';
					$qry=$pdo->query($tbl_schema['notify_restock']);
				}
			}
		break;
		case 'change_pay_type' :
			$change_pay_type = '';
			if(isset($_POST['change_pay_type']) && is_array($_POST['change_pay_type'])) {
				$change_pay_type = implode('@', $_POST['change_pay_type']);
			}
			$_POST['change_pay_type'] = $change_pay_type;
			if($_POST['use_paytype_change'] == 'Y') {
				if(strlen($change_pay_type) > 0) {
					include_once $engine_dir.'/_config/tbl_schema.php';
					$pdo->query($tbl_schema['order_paytype_change']);

					addField($tbl['order'], 'pay_type_changed', 'varchar(32) not null default "" comment "결제방식 변경 ID" after pay_type');
					addField($tbl['order'], 'new_pay_prc', 'double(10,2) unsigned not null default 0 comment "결제방식 변경 후 변경 될 결제금액" AFTER `pay_type_changed`');
				} else {
					msg('변경 가능 결제수단을 선택해주세요.');
				}
			}
		break;
		case 'daum_show_linkage' :
			$wec = new weagleEyeClient($_we, 'Etc');
			$wec->call('setExternalService', array(
				'service_name' => 'daumshopping',
				'use_yn' => ($_POST['show_use'] == 'Y' ? 'Y' : 'N'),
				'root_url' => $root_url,
				'extradata' => ''
			));
		case 'recopick_linkage' :
			$wec = new weagleEyeClient($_we, 'Etc');
			$wec->call('setExternalService', array(
				'service_name' => 'recopick',
				'use_yn' => ($_POST['recopick_use'] == '1' ? 'Y' : 'N'),
				'root_url' => $root_url,
				'extradata' => $_POST['recopick_widget_id']
			));
		break;
		case 'easemob' :
			$wec = new weagleEyeClient($_we, 'Etc');
			$wec->call('setExternalService', array(
				'service_name' => 'easemob',
				'use_yn' => ($_POST['use_easemob_plugin'] == 'Y' ? 'Y' : 'N'),
				'root_url' => $root_url,
				'extradata' => $_POST['easemob_plugin_id']
			));
		break;
		case '080_call' :
			$wec = new weagleEyeClient($_we, 'Etc');
			$wec->call('setExternalService', array(
				'service_name' => '080_call',
				'use_yn' => ($_POST['use_080sms'] == 'Y' ? 'Y' : 'N'),
				'root_url' => $root_url,
				'extradata' => $_POST['080_number']
			));
		break;
		case 'payco_login' :
			$wec = new weagleEyeClient($_we, 'Etc');
			$wec->call('setExternalService', array(
				'service_name' => 'sns_payco',
				'use_yn' => ($_POST['payco_login_use'] == 'Y' ? 'Y' : 'N'),
				'root_url' => $root_url,
				'extradata' => $_POST['payco_login_client_id']
			));
		break;
		case 'naver_login' :
			$wec = new weagleEyeClient($_we, 'Etc');
			$wec->call('setExternalService', array(
				'service_name' => 'sns_naver',
				'use_yn' => ($_POST['naver_login_use_y'] == 'Y' ? 'Y' : 'N'),
				'root_url' => $root_url,
				'extradata' => $_POST['naver_login_client_id']
			));
		break;
		case 'facebook_login' :
			$wec = new weagleEyeClient($_we, 'Etc');
			$wec->call('setExternalService', array(
				'service_name' => 'sns_facebook',
				'use_yn' => ($_POST['facebook_login_use_y'] == 'Y' ? 'Y' : 'N'),
				'root_url' => $root_url,
				'extradata' => $_POST['facebook_id']
			));
		break;
		case 'kakao_login' :
			$wec = new weagleEyeClient($_we, 'Etc');
			$wec->call('setExternalService', array(
				'service_name' => 'sns_kakaotalk',
				'use_yn' => ($_POST['kakao_login_use_y'] == 'Y' ? 'Y' : 'N'),
				'root_url' => $root_url,
				'extradata' => $_POST['kakao_sns_id']
			));
		break;
		case 'wonder_login' :
			$wec = new weagleEyeClient($_we, 'Etc');
			$wec->call('setExternalService', array(
				'service_name' => 'sns_wemakeprice',
				'use_yn' => ($_POST['wonder_login_use_y'] == 'Y' ? 'Y' : 'N'),
				'root_url' => $root_url,
				'extradata' => $_POST['wonder_login_client_id']
			));
		break;
		case '14_joinform':
			if($_POST['join_14_limit_method'] == 1 && ($cfg['join_birth_use'] == "N" || $cfg['member_join_birth'] == "N")) {
				msg("인증수단으로 생년월일을 사용하기 위해서는 가입 설정 내 \'생년월일(필수설정)\'을 사용해야 합니다.");
			}
			include_once $engine_dir.'/_engine/include/file.lib.php';
			$file = $_FILES['14_join_form_file'];

			if($file['size'] > 0) {
				$filename = md5($file['name'].$now);
				$ext = getExt($file['name']);
				$_POST['14_join_form_file'] = $filename.'.'.$ext;
				@unlink($root_dir."/_data/member_addinfo/".$cfg['14_join_form_file']);
				$ext = getExt($file['name']);
				uploadFile($file, $filename, '_data/member_addinfo',"","",true);
			}

			addField($tbl['member'], '14_limit', "enum('N','Y') DEFAULT 'N'");
			addField($tbl['member'], '14_limit_agree', "enum('N','Y') DEFAULT 'N'");
			addField($tbl['member'], '14_agree_type', "varchar(1) not null");
		break;
		case 'gift_config':
		    if(!$_POST['order_gift_first']) $_POST['order_gift_first'] = "N";
		break;
		case 'milage_config':
            $_POST['milage_type_per'] = (float) $_POST['milage_type_per'];

		   	$wec_acc = new weagleEyeClient($_we, 'etc');
			$wec_acc->call('setMilageExpireCron', array(
				'sms_use' => $_POST['expire_sms_use'],
				'email_use' => $_POST['expire_email_use'],
				'root_url' => $root_url,
			));
			if ($_POST['expire_sms_use'] == "Y") {
				$expire_case = ($_POST['milage_expire_sms_case'] == "A") ? "20" : "21";
				$expire_sms_use = $pdo->row("select `case` from {$tbl['sms_case']} where `case` = '$expire_case'");
				if ($expire_sms_use) {
					$pdo->query("update {$tbl['sms_case']} set use_check = 'Y' where `case` = '$expire_case'");
				} else {
					if ($_POST['milage_expire_sms_case'] == "A") {
						$msg = "[스마트윙 개발] 보유하고 계신 적립금 {소멸적립금}원이 {소멸예정일} 소멸예정입니다. 자세한 사항은 쇼핑몰 공지사항을 확인해주시기 바랍니다.";
					} else {
					    $msg = "[스마트윙 개발] 보유하고 계신 적립금 {소멸적립금}원이 {소멸예정일} 소멸예정입니다. 소멸된 적립금은 복구되지 않으니 유효기간 내 사용하시기 바랍니다.";
					}
				    $pdo->query("insert into {$tbl['sms_case']} (`case`, msg, use_check, sms_night, alimtalk_code, mng_push) values ('20','$msg','Y','H', '', '')");
				}
			}
		break;
		case 'sms_config' :
			if($_POST['night_sms_start'] != '' && $_POST['night_sms_end'] != '') {
				if(abs($_POST['night_sms_start']-$_POST['night_sms_end']) < 2) msg('발송제한 시작시간과 종료시간의 차이를 최소 2시간 이상으로 설정해 주세요.');
			}
		break;
		case 'email_config' :
			include_once $engine_dir.'/_engine/include/file.lib.php';
			include_once $engine_dir.'/_engine/include/img_ftp.lib.php';
			$updir = "/_image/_default/logo/";
			if(!is_dir($root_dir.$updir)) makeFullDir($updir);
			if($_FILES['email_logo_img'][tmp_name]) {
				deleteAttachFile($updir, $_FILES['email_logo_img']);
				$ext = getExt($_FILES['email_logo_img']['name']);
				$up_filename = "mail";
				$_POST['email_logo_img'] = $updir.$up_filename.'.'.$ext;
				$_FILES['email_logo_img']['name'] = $up_filename.'.'.$ext;
				ftpUploadFile($root_dir.$updir, $_FILES['email_logo_img'], "jpg|jpeg|gif|png|bmp");
			}
		break;
		case 'email':
			$email_privacy = (strpos($_POST['email_checked'], "@22") === false) ? "N" : "Y";
			$wec_acc = new weagleEyeClient($_we, 'etc');
			$wec_acc->call('setMemberPrivacyCron', array(
				'email_use' => $email_privacy,
				'root_url' => $root_url,
			));
		break;
        case 'session' :
            if($_POST['session_engine'] == 'Redis') {
                if(class_exists('\Redis') == false) {
                    msg('Redis 모듈이 설치되어있지 않습니다.');
                }
                if(empty($_POST['redis_host']) == true) {
                    msg('Redis 서버의 접속정보를 입력해주세요.');
                }
                $redis_host = explode(':', $_POST['redis_host']);
                if (count($redis_host) == 3) {
                    $redis_host[0] = $redis_host[0].':'.$redis_host[1];
                    $redis_host[1] = $redis_host[2];
                }
                $redis = new \Redis();
                $test = $redis->connect($redis_host[0], $redis_host[1]);
                if($test == false) {
                    msg('입력하신 Redis 서버의 접속정보가 정확하지 않습니다.');
                }
            }
        break;
		case 'ipin_config':
		    if ($_POST['ipin_checkplus_use'] != 'Y') $_POST['limit_19'] = '';
		break;
        case 'intra_2factor':
            if ($_POST['intra_2factor_use'] === 'Y') {
                //사용인 경우만 체크. 미사용시 기존 선택한 인증수단 유지
                if (!isset($_POST['intra_2factor_email'])) {
                    $_POST['intra_2factor_email'] = 'N';
                }
                if (!isset($_POST['intra_2factor_phone'])) {
                    $_POST['intra_2factor_phone'] = 'N';
                }
            }
            break;
        case 'mng_pass_expire' :
            addField($tbl['mng'], 'expire_pwd', 'date not null');
            if (empty($_POST['mng_pass_expire']) == false) {
                $expire_pwd = date('Y-m-d', strtotime("+{$_POST['mng_pass_expire']} months"));
                $pdo->query("update {$tbl['mng']} set expire_pwd='$expire_pwd' where expire_pwd='0000-00-00'");
            }
            break;
        case 'clarity':
            $wec = new weagleEyeClient($_we, 'Etc');
            $wec->call('setExternalService', array(
                'service_name' => 'clarity',
                'use_yn' => ($_POST['use_clarity'] == 'Y' ? 'Y' : 'N'),
                'root_url' => $root_url,
                'extradata' => addslashes($_POST['clarity_code'])
            ));
            break;

			//[매장지도] 설정 값 추가
		case "store_location_config":
			for($i=1; $i<=1; $i++ ) {
				
				$_FILES['upfile'.$i] = $_FILES['store_marker_upfile'.$i];

				if ($_FILES['upfile'.$i]) {
					$file = $_FILES['upfile'.$i];

					if ($file['size'] > 0) {
						if ($data['upfile'.$i]) {
							deletePrdImage($data, 1, 1);
						}

						if (!$updir) {
							$updir = $dir['upload'] . '/store/location/';
							makeFullDir($updir);
							$_POST['store_marker_updir'] = $updir;
						}

						$up_filename = md5($file['name'].$now.$file['size']);

						$up_info = uploadFile($file, $up_filename, $updir,'jpg|png');
						${'upfile'.$i} = $up_info[0];
						$_POST['store_marker_upfile'.$i] = ${'upfile'.$i};
					}
				}
				// 파일업로드
				$_file = array(
					'tmp_name' => $_FILES['upfile'.$i]['tmp_name'],
					'name' => $_FILES['upfile'.$i]['name'],
					'size' => $_FILES['upfile'.$i]['size'],
				);
				if(($_file['size'] > 0 ) || $_POST['delfile'.$i] == 'Y') {
					if(!$_file['size']) {
						$_POST['store_marker_upfile'.$i] = '';
					}
				}
			}

			$local = $_kakao_store_handler->kakaoRestApi('address', ['query' => $_POST['gps_center_addr1'] . ' ' . $_POST['gps_center_addr2']], 'json');
			$local = $local['documents'][0]['road_address'];

			if ((!$local['y'] || !$local['x'])) msg('[GPS]정확한 주소값을 입력해 주세요.');

			$_POST['gps_center_lat'] = $local['y'];
			$_POST['gps_center_lng'] = $local['x'];
			break;
	}

	if($_POST['delivery_fee_type'] == 'D'){
		$_POST['delivery_fee_D'] = 'KR';
	}else if($_POST['delivery_fee_type'] == 'O'){
		$_POST['delivery_fee_D'] = '';
	}else if($_POST['delivery_fee_type'] == 'A'){
		$_POST['delivery_fee_D'] = 'KR';
	}

	if(!$_POST['delivery_fee_type'] && $_POST['delivery_fee_D']){
		$_POST['delivery_fee_type'] = 'D';
	}


	if($_POST['card_pg'] == 'inicis') {
		switch($_POST['pg_version']) {
			case 'INILite' :
				if(!is_dir($root_dir.'/_data/INILite') || !file_exists($root_dir.'/_data/INILite')) {

					@mkdir("{$root_dir}/_data/INILite");
					@chmod("{$root_dir}/_data/INILite", 0777);

					@mkdir("{$root_dir}/_data/INILite/log");
					@chmod("{$root_dir}/_data/INILite/log", 0777);
				}
			break;
			default :
				if($_FILES['inicis_key']['size'] > 0) {
					include $engine_dir.'/_manage/config/inicis_install.inc.php';
				}
			break;
		}
	}

	if($_POST['br_title']) {
		$new_br_title=strip_tags($_POST['br_title']);
		if($_POST['br_title'] != $new_br_title) msg("웹브라우저 타이틀에는 태그를 사용 할 수 없습니다.");
		$_POST['br_title'] = $new_br_title;
	}

	if($config_code == 'refprds') {
		for($i = 2; $i <= $cfg['refprds']; $i++) {
			$_cfg_name[] = 'refprd'.$i.'_name';
		}
	}

	if($config_code == 'prdetc') {
		if($_POST['use_prd_etc1'] == 'Y') addField($tbl['product'], 'etc1', 'varchar(150) not null default "" comment "상품추가항목1"');
		if($_POST['use_prd_etc2'] == 'Y') addField($tbl['product'], 'etc2', 'varchar(150) not null default "" comment "상품추가항목2"');
		if($_POST['use_prd_etc3'] == 'Y') addField($tbl['product'], 'etc3', 'varchar(150) not null default "" comment "상품추가항목3"');
	}

	if($config_code == 'prc_consultation'){
		if(!fieldExist($tbl['product'], 'sell_prc_consultation')) {
			addField($tbl['product'], 'sell_prc_consultation', 'varchar(150) comment "판매가 대체문구"');
		}
		if(!fieldExist($tbl['product'], 'sell_prc_consultation_msg')) {
			addField($tbl['product'], 'sell_prc_consultation_msg', 'varchar(300) comment "판매가 대체문구 주문시 메시지"');
		}
		if($_POST['use_option_product'] == 'Y') {
			if(!fieldExist($tbl['product_option_item'], 'complex_no')) {
				addField($tbl['product_option_item'], 'complex_no', 'int(10) not null default "0"');
				$pdo->query("alter table $tbl[product_option_item] add index complex_no(complex_no)");
				$pdo->query("alter table $tbl[product_option_set] change necessary necessary char(1) not null default 'N'");
			}
		}
		if($_POST['use_trash_prd'] == 'Y') { // 상품 휴지통
			$pdo->query("alter table $tbl[product] change stat stat tinyint(2) not null default '1' comment '상품상태'");
			addField($tbl['product'], 'del_stat', 'tinyint(2) not null default "1" comment "삭제 전 상태"');
			addField($tbl['product'], 'del_date', 'int(10) not null default "0" comment "휴지통 추가일"');
			addField($tbl['product'], 'del_admin', 'varchar(50) not null default "" comment "휴지통 처리 관리자"');
		}

		if($_POST['use_m_content_product'] == 'Y') {
			if(!fieldExist($tbl['product'], 'm_content')) addField($tbl['product'], 'm_content', 'text not null default ""');
			if(!fieldExist($tbl['product'], 'use_m_content')) addField($tbl['product'], 'use_m_content', 'enum("Y","N") not null default "N"');
		}

		if($_POST['use_qty_discount'] == 'Y') {
			addField($tbl['product'], 'qty_rate', 'varchar(200) not null default "" after sell_prc');
			addField($tbl['order'], 'sale9', 'double(8,2) not null default "0" after sale6');
			addField($tbl['order_product'], 'sale9', 'double(8,2) not null default "0" after sale6');
		}

		if($_POST['use_no_mile/cpn'] == 'Y') {
			addField($tbl['product'], 'no_milage', 'enum("N", "Y") not null default "N" after tax_free');
			addField($tbl['product'], 'no_cpn', 'enum("N", "Y") not null default "N" after tax_free');
		}

		if ($_POST['use_set_product'] == 'Y') {
			$pdo->query("
			ALTER TABLE {$tbl['product']}
				ADD COLUMN set_rate varchar(500) NOT NULL DEFAULT '' COMMENT '골라담기 할인율' AFTER sell_prc,
				ADD COLUMN set_sale_prc DOUBLE(10,2) UNSIGNED NOT NULL DEFAULT '0' COMMENT '세트 할인' AFTER origin_prc,
				ADD COLUMN set_sale_type ENUM('m','p') NOT NULL DEFAULT 'm' COMMENT '세트 할인 기준 p.퍼센트 m.금액' AFTER set_sale_prc,
				ADD COLUMN set_each enum('N', 'Y') NOT NULL DEFAULT 'N' COMMENT '세트 하위상품 개별 판매 불가' AFTER set_sale_type;
			");
			$pdo->query("
			ALTER TABLE {$tbl['cart']}
				ADD COLUMN set_pno int(10) not null default '0' COMMENT '세트상품번호',
				ADD COLUMN set_idx varchar(46) not null default '' COMMENT '세트일련번호',
				ADD INDEX set_idx (set_idx);
			");
			$pdo->query("
			ALTER TABLE {$tbl['order']}
				ADD COLUMN has_set ENUM('N', 'Y') not null default 'N' COMMENT '세트주문 여부' after stat2,
				ADD INDEX has_set (has_set);
			");
			$pdo->query("
			ALTER TABLE {$tbl['order_product']}
				ADD COLUMN set_pno int(10) not null default '0' COMMENT '세트상품번호',
				ADD COLUMN set_idx varchar(46) not null default '' COMMENT '세트일련번호',
				ADD INDEX set_pno (set_pno),
				ADD INDEX set_idx (set_idx);
			");
            // 이미 있을수도 있는 필드는 따로 처리
			addField($tbl['order_product'], 'sale1', 'double(8,2) not null default "0.00" comment "세트할인"');
			addField($tbl['order'], 'sale1', 'double(8,2) not null default "0.00" comment "세트할인"');
            $pdo->query("alter table {$tbl['product']} ADD INDEX prd_type (prd_type)");
		}

        if ($_POST['compare_explain'] == 'Y') {
            addField($tbl['product'], 'no_ep', 'enum("Y","N") not null default "N" after tax_free');
        }
	}

	if($config_code=='subscription') {
		if($_POST['use_sbscr']=='Y' && !isTable($tbl['sbscr'])) {
			include_once $engine_dir.'/_plugin/subScription/tbl_schema.php';
			$pdo->query($tbl_schema['sbscr']);
			$pdo->query($tbl_schema['sbscr_product']);
			$pdo->query($tbl_schema['sbscr_schedule']);
			$pdo->query($tbl_schema['sbscr_schedule_product']);
			$pdo->query($tbl_schema['sbscr_set']);
			$pdo->query($tbl_schema['sbscr_set_product']);
			$pdo->query($tbl_schema['sbscr_holiday']);
			$pdo->query($tbl_schema['sbscr_cart']);

			$pdo->query("ALTER TABLE $tbl[card] CHANGE COLUMN wm_ono wm_ono VARCHAR(21) NOT NULL DEFAULT '' COMMENT '주문 번호' AFTER `no`");

            addField($tbl['order_product'], 'sale8', 'double(8,2) not null default 0.00 after sale7');
            addField($tbl['order'], 'sale8', 'double(8,2) not null default 0.00 after sale7');
		}

        $wec = new weagleEyeClient($_we, 'Etc');
        $wec->call('setExternalService', array(
            'service_name' => 'subscription',
            'use_yn' => $_POST['use_sbscr'],
            'root_url' => $root_url,
            'client_ip' => $_SERVER['REMOTE_ADDR']
        ));

		// 크론 등록
		$use_sbscr = $_POST['use_sbscr'];
		$wec_acc = new weagleEyeClient($_we, 'mall');
		$wec_acc->call('setAutoSbscr', array('auto_use'=>$use_sbscr));
		if($wec_acc->error) {
			msg($wec_acc->result);
		}

		// 결제설정 일괄결제만 있을 경우 처리
		if($_POST['sbscr_order_all'] != 'Y') $_POST['sbscr_order_all'] = '';
		if($_POST['sbscr_order_split'] != 'Y') $_POST['sbscr_order_split'] = '';
		if($_POST['sbscr_order_all']=='Y' && $_POST['sbscr_order_split']=='') {
			if($_POST['sbscr_type']=='A') {
				if($cfg['sbscr_dlv_type']=='N') msg("일괄결제만 사용할 경우 배송기간의 기간없음을 모두 해제해주시기 바랍니다.");
			}else if($_POST['sbscr_type']=='P') {
				$type_cnt = $pdo->row("select count(*) from $tbl[sbscr_set] where dlv_type='N'");
				if($type_cnt>0) msg("일괄결제만 사용할 경우 세트(배송기간)의 기간없음을 모두 해제해주시기 바랍니다.");
			}
		}
	}

	if($_POST['attendMP']=='P') {
		if($cfg['point_use']!='Y') msg("현재 포인트 사용으로 설정 되어 있지 않습니다.","reload","parent");
	}

	if(!$cfg['all_attend_date']) $_POST['all_attend_date'] = 'Y';

	if(($cfg['attendType'] != $_POST['attendType']) && $_POST['attendType']) {
		$_POST['attend_edate'] = date("Y-m-d");
	}

    $scfg->import($_POST);

    // 카카오싱크 정보 변경
    if ($config_code == 'info' || $config_code == 'member_jumin' || $config_code == '14_joinform') {
        if (
            $scfg->comp('kakao_login_use', 'S') == true
            && $cfg['kakaoSync_StoreKey']
            && $cfg['kakao_rest_api']
        ) {
            $kkosync = new KakaoSync(
                $cfg['kakaoSync_StoreKey'],
                $cfg['kakao_rest_api']
            );
            $ret = $kkosync->modification('edit');
        }
    }

	if(!$no_reload_config) {
		if($mobile_confirm) {
			msg('윙Mobile 쇼핑몰이 생성되었습니다.\n\n윙Mobile 설정페이지로 이동합니다.', '?body=wmb@config', 'top');
		} else {
			if($popup) {
				if($config_code == 'ipay') msg('', 'popup', 'parent.opener');
				else msg($cfg_msg, 'popup');
			} else {
				msg($cfg_msg, 'reload', 'parent');
			}
		}
	}

?>