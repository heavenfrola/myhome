<?PHP

	set_time_limit(0);
	ini_set('memory_limit', -1);
	$urlfix = 'Y';

	header('Content-type:text/html; charset=utf8');

	include_once $engine_dir.'/_engine/include/common.lib.php';
	include_once $engine_dir.'/_config/tbl_schema.php';
	dbcon();

	$admin_no = numberOnly($_SESSION['admin_no']);
	$mng = $pdo->assoc("select level from $tbl[mng] where no='$admin_no'");
	if(!$mng['level'] || $mng['level'] > 2) {
		exit('deny');
	}

	$no_qcheck = true;

	switch($_GET['exec']) {
		case 'u' :
			$res = $pdo->iterator("show tables");
            foreach ($res as $data) {
				$table = $data[0];

				echo "<h1>$table</h1><ul>";
				$pdo->query("alter table $table default character set utf8 collate utf8_general_ci");

				$res2 = $pdo->iterator("show columns from $table");
                foreach ($res2 as $field) {
					if($field['Type'] == 'text' || strpos($field['Type'], 'varchar') !== false || strpos($field['Type'], 'char') !== false || strpos($field['Type'], 'enum') !== false) {
						$pdo->query("alter table $table change `$field[Field]` `$field[Field]` $field[Type] CHARACTER SET utf8 COLLATE utf8_general_ci not null default '$field[Default]'");
						echo $pdo->getError();
						echo "<li>$field[Field] $field[Type] not null default '$field[Default]'</li>";
					}
				}
				echo "</ul>";
			}
		break;
		case 'm' :
			if(file_exists($engine_dir.'/_engine/include/account/v3TableSync.inc.php')) {
				include $engine_dir.'/_engine/include/account/v3TableSync.inc.php';
			} else if(file_exists($engine_dir.'/_engine/include/_account/v3TableSync.inc.php')) {
				include $engine_dir.'/_engine/include/_account/v3TableSync.inc.php';
			} else {
				exit('[v3TableSync.inc.php] is not found');
			}

			// 구버전 설정 마이그레이션
			$old_config = $root_dir.'/_config/config.php';
			if(file_exists($old_config)) {
				if(filesize($old_config) > 0) {
					if(is_writable($old_config)) {
						$pdo->query("truncate table $tbl[config]");

						$cfg = array();
						include $old_config;
						foreach($cfg as $key => $val) {
							if(is_array($val)) continue;
							$val = addslashes($val);
							$pdo->query("insert into $tbl[config] (name, value, reg_date, edt_date, admin_id) values ('$key', '$val', '$now', '$now', 'setup')");
						}

						copy($old_config, $root_dir.'/_data/config.bak.php');
						$fp = fopen($old_config, 'w');
						fwrite($fp, '');
						fclose($fp);
					} else {
						exit($root_dir.'/_config/config.php 에 대한 쓰기권한이 필요합니다.');
					}
				}
			}

			// 윙포스 마이그레이션
			$res = $pdo->iterator("select * from erp_complex_option where opts='' and (opt1 > 0 or opt2 > 0)");
            foreach ($res as $data) {
				$opts = '';
				if($data['opt1']) $opts  = "_$data[opt1]_";
				if($data['opt2']) $opts .= "$data[opt2]_";
				if($opts) {
					$pdo->query("update erp_complex_option set opts='$opts' where complex_no='$data[complex_no]'");
				}
			}
			exit('OK');
		break;
		case 't' :
			if($pdo->row("select count(*) from $tbl[order]") > 0) {
				exit('주문서가 1건 이상 존재할 경우 초기화 하실수 없습니다.');
			}
			$pdo->query("truncate table erp_account");
			$pdo->query("truncate table erp_complex_option");
			$pdo->query("truncate table erp_inout");
			$pdo->query("truncate table erp_order");
			$pdo->query("truncate table erp_order_dtl");
			$pdo->query("truncate table erp_stock");
			$pdo->query("truncate table wm_attend");
			$pdo->query("truncate table wm_attend_day");
			$pdo->query("truncate table wm_attend_member");
			$pdo->query("truncate table wm_biz_member");
			$pdo->query("truncate table wm_blacklist_log");
			$pdo->query("truncate table wm_card");
			$pdo->query("truncate table wm_card_cc_log");
			$pdo->query("truncate table wm_cart");
			$pdo->query("truncate table wm_cash_receipt");
			$pdo->query("truncate table wm_cash_receipt_log");
			$pdo->query("truncate table wm_coupon");
			$pdo->query("truncate table wm_coupon_download");
			$pdo->query("truncate table wm_coupon_log");
			$pdo->query("truncate table wm_cs");
			$pdo->query("truncate table wm_delete_log");
			$pdo->query("truncate table wm_intra_board");
			$pdo->query("truncate table wm_intra_comment");
			$pdo->query("truncate table wm_intra_day_check");
			$pdo->query("truncate table wm_intra_schedule");
			$pdo->query("truncate table wm_ipin_log");
			$pdo->query("truncate table wm_join_sms");
			$pdo->query("truncate table wm_log_agent");
			$pdo->query("truncate table wm_log_count");
			$pdo->query("truncate table wm_log_day");
			$pdo->query("truncate table wm_log_referer");
			$pdo->query("truncate table wm_log_search");
			$pdo->query("truncate table wm_log_search_day");
			$pdo->query("truncate table wm_log_search_engine");
			$pdo->query("truncate table wm_log_server");
			$pdo->query("truncate table wm_log_today");
			$pdo->query("truncate table wm_manage_menu_static_day");
			$pdo->query("truncate table wm_manage_menu_static_total");
			$pdo->query("truncate table wm_member_deleted");
			$pdo->query("truncate table wm_member_log");
			$pdo->query("truncate table wm_member_xls_log");
			$pdo->query("truncate table wm_mng_bookmark");
			$pdo->query("truncate table wm_mng_cs_log");
			$pdo->query("truncate table wm_namecheck_log");
			$pdo->query("truncate table wm_neko");
			$pdo->query("truncate table wm_order");
			$pdo->query("truncate table wm_order_memo");
			$pdo->query("truncate table wm_order_no");
			$pdo->query("truncate table wm_order_payment");
			$pdo->query("truncate table wm_order_product");
			$pdo->query("truncate table wm_order_product_log");
			$pdo->query("truncate table wm_order_stat_log");
			$pdo->query("truncate table wm_pbanner");
			$pdo->query("truncate table wm_pbanner_group");
			$pdo->query("truncate table wm_point");
			$pdo->query("truncate table wm_poll_comment");
			$pdo->query("truncate table wm_poll_config");
			$pdo->query("truncate table wm_poll_item");
			$pdo->query("truncate table wm_popup");
			$pdo->query("truncate table wm_product_log");
			$pdo->query("truncate table wm_provider");
			$pdo->query("truncate table wm_pwd_log");
			$pdo->query("truncate table wm_qna");
			$pdo->query("truncate table wm_review");
			$pdo->query("truncate table wm_review_comment");
			$pdo->query("truncate table wm_session");
			$pdo->query("truncate table wm_social_coupon_code");
			$pdo->query("truncate table wm_social_coupon_info");
			$pdo->query("truncate table wm_social_coupon_log");
			$pdo->query("truncate table wm_social_coupon_use");
			$pdo->query("truncate table wm_tax_receipt");
			$pdo->query("truncate table wm_vbank");
			$pdo->query("truncate table wm_wish");
			exit('OK');
		break;
		case 'e' :
			$res = $pdo->iterator("select no, ctype from $tbl[category] where ctype in (2, 6)");
            foreach ($res as $data) {
				$ebig = $pdo->iterator("select no from $tbl[product] where ebig like '%$data[no]%' order by sort$data[no] asc");
				$idx = 0;
                foreach ($ebig as $prd) {
					$idx++;
					$pdo->query("insert into $tbl[product_link] (ctype, nbig, pno, sort_big) values ('$data[ctype]', '$data[no]', '$prd[no]', '$idx')");
				}
			}
			exit('OK');
		break;
		case 'o' :
			$res = $pdo->iterator("select ono from $tbl[order]");
            foreach ($res as $data) {
				ordChgPart($data['ono']);
			}
			exit('OK');
		break;
		case 'p' :
			$pdo->query("truncate table $tbl[order_payment]");
			$res = $pdo->iterator("select ono, pay_type, bank, bank_name, emoney_prc, milage_prc, pay_prc, dlv_prc from $tbl[order] where stat not in (11, 31) order by no asc");
            foreach ($res as $data) {
				$cpn_no = $pdo->row("select no from $tbl[coupon_download] where ono='$data[ono]'");
				$payment_no = createPayment(array(
					'type' => 0,
					'ono' => $data['ono'],
					'pno' => explode(',', $pdo->row("select group_concat(no) from $tbl[order_product] where ono='$data[ono]'")),
					'pay_type' => $data['pay_type'],
					'amount' => $data['pay_prc'],
					'bank' => $data['bank'],
					'bank_name' => $data['bank'],
					'dlv_prc' => $data['dlv_prc'],
					'emoney_prc' => $data['emoney_prc'],
					'milage_prc' => $data['milage_prc'],
					'cpn_no' => $cpn_no,
				), 2);
			}
		break;
		case 'i' :
			$res = $pdo->iterator("show tables");
            foreach ($res as $data) {
				$table = $data[0];
				$pdo->query("alter table $table engine=InnoDB;");
			}
		break;
		case '4' :
			addField($tbl['product'], 'depth4', 'int(10) not null default 0 after small');
			addField($tbl['product'], 'sortdepth4', 'int(5) not null default 0 after sortsmall');
			addField($tbl['product'], 'xdepth4', 'int(10) not null default 0 after xsmall');
			addField($tbl['product'], 'ydepth4', 'int(10) not null default 0 after ysmall');
			$pdo->query("alter table $tbl[product] add index depth4(depth4)");

			addField($tbl['product_link'], 'ndepth4', 'int(5) not null default 0 after nsmall');
			addField($tbl['product_link'], 'sort_depth4', 'int(5) not null default 0 after sort_small');
			$pdo->query("alter table $tbl[product_link] add index ndepth4(ndepth4)");

			$pdo->query("insert into $tbl[config] (name, value) values ('max_cate_depth', '4')");

			echo '처리완료';
		break;
		case 'c' :
			$res = $pdo->iterator("select * from $tbl[coupon] where is_type='B'");
            foreach ($res as $data) {
  				$auth_code = explode('@', trim($data['auth_code'], '@'));
				foreach($auth_code as $code) {
					$pdo->query("
						insert into $tbl[coupon_auth_code] (cno, auth_code) values ('$data[no]', '$code')
					");
				}
			}
		break;
		default :
			?>
			<ul>
				<li><a href='?exec_file=cron/migration.php&exec=u'>DB collation 변경(to utf8)</a></li>
				<li><a href='?exec_file=cron/migration.php&exec=m'>DB/Config 마이그레이션</a></li>
				<li><a href='?exec_file=cron/migration.php&exec=t'>테이블 초기화</a></li>
				<li><a href='?exec_file=cron/migration.php&exec=e'>구 기획전 마이그레이션</a></li>
				<li><a href='?exec_file=cron/migration.php&exec=o'>주문 stat2 값 마이그레이션</a></li>
				<li><a href='?exec_file=cron/migration.php&exec=p'>order payment 생성</a></li>
				<li><a href='?exec_file=cron/migration.php&exec=i'>innodb로 변경</a></li>
				<li><a href='?exec_file=cron/migration.php&exec=4'>4depth 카테고리 사용</a></li>
				<li><a href='?exec_file=cron/migration.php&exec=c'>시리얼 쿠폰코드 생성</a></li>
			</ul>
			<?
		break;
	}

?>