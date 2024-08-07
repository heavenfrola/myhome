<?PHP

    use Wing\HTTP\CurlConnection;

    include_once __ENGINE_DIR__.'/_engine/include/shop_detail.lib.php';
    include_once __ENGINE_DIR__.'/_engine/include/file.lib.php';
    include_once __ENGINE_DIR__.'/_engine/include/milage.lib.php';
    include_once __ENGINE_DIR__.'/_engine/sms/sms_module.php';

	class Openapi {

		private $action;
		private $hash;
		private $mhash;
		private $db;
		private $mng;
		private $member;

		public function __construct() {
            global $scfg;

			$this->action = $_REQUEST['action'];
			$this->hash = addslashes(trim($_REQUEST['hash']));
			$this->mhash = addslashes(trim($_REQUEST['mhash']));
			$this->db = $GLOBALS['pdo'];

			$GLOBALS['urlfix'] = 'Y';

            if ($scfg->comp('use_openapi', 'Y') == false) {
                global  $_we, $root_url;

                $wec = new weagleEyeClient($_we, 'Etc');
                $wec->call('setExternalService', array(
                    'service_name' => 'openapi',
                    'use_yn' => 'Y',
                    'root_url' => $root_url
                ));
                $scfg->import(array(
                    'use_openapi' => 'Y'
                ));
            }
		}

		// 로그인 세션 생성
		function login() {
			global $tbl, $engine_dir, $root_dir, $_we, $cfg;

			$mng_id = addslashes(trim($_REQUEST['mng_id']));
			$mng_pw = sql_password(trim($_REQUEST['mng_pw']));
			$is_auto_login = ($_REQUEST['is_auto_login'] == 'Y') ? 'Y' : 'N';

			$sitekey = @file($root_dir.'/_config/site_key.php');
			$sitekey = trim($sitekey[2]);

			if(file_exists($engine_dir.'/_engine/include/account/ssoLogin.inc.php')) { // 임대형
				// 위사 로그인 체크
				$ret = comm(sprintf('http://www.wisa.co.kr/login/loginExe?contentType=json&login_id=%s&login_pwd=%s&site_key=%s', $mng_id, trim(urlencode($_REQUEST['mng_pw'])), $sitekey));
				$ret = json_decode($ret);
				if($ret->status != 'Y') {
					$this->error($ret->result);
				}
				$mng_id = addslashes(trim($ret->member_id));
				$this->mng = $this->db->assoc("select * from $tbl[mng] where admin_id='$mng_id'");
				if(!$this->mng['no']) $this->error('관리자 아이디 에러');
			} else { // 독립형
				$this->mng = $this->db->assoc("select * from $tbl[mng] where admin_id='$mng_id'");
				$this->mng_auth = $this->db->assoc("select * from {$tbl['mng_auth']} where admin_no='{$this->mng['no']}'");
				if(!$this->mng['no']) $this->error('관리자 아이디 에러');
				if($mng_pw != $this->mng['pwd']) $this->error('관리자 패스워드 에러');
			}

			$hash = session_id();
			if($is_auto_login == 'Y') {
				$this->db->query("alter table $tbl[mng] add hash varchar(64) not null");
				$this->db->query("update $tbl[mng] set hash='$hash' where no='{$this->mng[no]}'");
			}

			// 관리자 앱 사용 여부 체크
			if($cfg['use_mng_app'] != 'Y' && $_REQUEST['_target'] == 'app') {
				$this->db->query("
					insert into $tbl[config] (name, value, admin_id) values ('use_mng_app', 'Y', 'api')
				");
			}

			$weca = new weagleEyeClient($_we, 'account');
			$ret = $weca->call('getAccountByAPI', array('target'=>$_REQUEST['_target'], 'device'=>$_REQUEST['_device'], 'mng_id'=>$mng_id));
			$ret = json_decode($ret);

			$_SESSION['admin_no'] = $this->mng['no'];

			$dashboard = ($this->mng['level'] == 1 || $this->mng['level'] == 2 || strpos($this->mng['auth'], '@main') > -1) ? 'true' : 'false';
			$this->result('Y', array(
                'hash' => $hash,
                'account' => $ret,
                'admin' => array('no' => $this->mng['no'], 'level' => $this->mng['level'], 'partner_no' => $this->mng['partner_no']),
                'dashboard' => $dashboard, 'api_version'=>'2.91.3')
            );
		}

		// 로그인 여부 출력
		public function loginCheck() {
			if($this->checkhash()) {
				$this->result('Y', null);
			} else {
				$this->result('N', null);
			}
		}

		// 회원 로그인 세션 생성
		function memberLogin() {
			global $tbl;

			$member_id = addslashes(trim($_REQUEST['member_id']));
			$password = sql_password(trim($_REQUEST['password']));

			$this->member = $this->db->assoc("select * from $tbl[member] where member_id='$member_id'");
			if(!$this->member ['no']) $this->error('존재하지 않는 회원 아이디 입니다.');
			if($password != $this->member['pwd']) $this->error('비밀번호가 일치하지 않습니다.');

			$hash = session_id();

			$this->db->query("alter table $tbl[member] add hash varchar(64) not null");
			$this->db->query("update $tbl[member] set hash='' where hash='$hash'");
			$this->db->query("update $tbl[member] set hash='$hash' where no='{$this->member[no]}'");

			$this->result('Y', array('hash' => $hash));
		}

		// 메인 대시보드
		public function dashboard() {
			global $tbl;

            $this->checkPermission('main');

			$timestamp = strtotime(date('Y-m-d 00:00:00'));
			$today = $this->db->assoc("select sum(total_prc) as prc, count(*) as cnt from $tbl[order] where stat<11 and date1>='$timestamp'");
			$join = $this->db->row("select count(*) from $tbl[member] where reg_date>='$timestamp'");
			$crm_cnt = $this->db->row("select count(*) from $tbl[qna] where answer_date < 1 or answer_date is null");

			list($yy, $mm, $dd) = explode('-', date('Y-m-d'));
			$access_today = $this->db->row("select hit from $tbl[log_day] where yy='$yy' and mm='$mm' and dd='$dd'");

			$this->result('Y', array(
				'today_prc' => $today['prc'],
				'today_cnt' => $today['cnt'],
				'today_join' => $join,
				'today_visit' => $access_today,
				'crm_cnt' => $crm_cnt,
			));
		}

		// 부가서비스 현황
		public function service() {
			global $_we, $wec, $tbl, $cfg;

            $this->checkPermission('main');

			$weca = new weagleEyeClient($_we, 'account');
			$asvcs = $weca->call('getSvcs', array('key_code' => $wec->config['wm_key_code'], 'use_cdn' => $cfg['use_cdn']));
			$account = $wec->get('410', '', true);

			$disk_free_limit = $this->db->row("select sum(filesize) from $tbl[product_image] where filetype not in (7, 8, 9)");
			if($asvcs[0]->wdisk_finish[0] < time()) {
				$asvcs[0]->wdisk[0] = 0;
			}
			$disk_expire = ($asvcs[0]->wdisk_finish[0] > 0) ? date('Y-m-d H:i:s', $asvcs[0]->wdisk_finish[0]) : 'null';
			$bank_expire = ($asvcs[0]->bankda_fin[0]) ? $asvcs[0]->bankda_fin[0] : 'null';

			if(file_exists($engine_dir.'/_engine/include/account/setHosting.inc.php') == false) {
				/*
				$asvcs[0]->img_limit[0] = 0;
				$disk_free_limit = 0;
				*/
			}

			// 기본 디스크
			$bimg_limit = ($asvcs[0]->img_limit[0]) ? $asvcs[0]->img_limit[0] : '무제한';
			if($bimg_limit != '무제한') {
				$bimg_limit = ($_bimg_limit >= 1000) ? ($bimg_limit/1000).'G' : $bimg_limit.'M';
			}
            $bimg_finish = 0;
            if ($asvcs[0]->img_finish[0]) {
    			$bimg_finish = ($asvcs[0]->img_finish[0] == '무제한') ? '무제한' : date('Y-m-d H:i:s', $asvcs[0]->img_finish[0]);
            }
            if ($asvcs[0]->mall_hosting_expire[0]) {
                $asvcs[0]->mall_hosting_expire[0] = date('Y-m-d H:i:s', $asvcs[0]->mall_hosting_expire[0]);
            }

			# CDN
			$cdn_use = ($asvcs[0]->cdn_use[0] == 'Y') ? 'Y' : 'N';
			if($cdn_use == 'Y') {
				if($asvcs[0]->mall_goods_idx[0] == '4') {
					$bimg_limit = $asvcs[0]->cdn_limit[0].'G';
				}
				$bimg_finish = date('Y-m-d H:i:s', $asvcs[0]->cdn_expire[0]);
			}

			$this->result('Y', array(
				'disk_free_limit' => $bimg_limit,
				'disk_free_used' => $disk_free_limit,
				'disk_limit' => ($asvcs[0]->wdisk[0]*1024*1024),
				'disk_used' => ($asvcs[0]->img_used[0]),
				'disk_expire' => $disk_expire,
				'disk_name' => $asvcs[0]->disk_svc_name[0],
                'disk_goods_name' => $asvcs[0]->cdn_goods_name[0],
				'sms_point' => numberOnly($asvcs[0]->sms_rest[0]),
				'email_point' => $account[0]->mail_rest,
				'bank_ea' => numberOnly($asvcs[0]->bankda_accounts[0]),
				'bank_expire' => $bank_expire,
				'mall_goods_idx' => $asvcs[0]->mall_goods_idx[0],
				'mall_goods_name' => $asvcs[0]->mall_goods_name[0],
                'mall_hosting_name' => $asvcs[0]->mall_hosting_name[0],
                'mall_hosting_expire' => $asvcs[0]->mall_hosting_expire[0],
				'mall_img_limit' => $bimg_limit,
				'mall_img_expire' => $bimg_finish,
				'mall_img_cdn' => $cdn_use,
				'mall_img_traffic' => $asvcs[0]->cdn_traffic_day[0],
				'mall_img_warning' => $asvcs[0]->img_warning[0]
			));
		}

		// 공통정보
		public function common() {
			global $tbl, $_we, $root_url, $_mng_levels;

			if($this->checkhash() == false) {
				$this->error('관리자 로그인 오류');
			}
            if ($this->mng['level'] > 3) {
                return $this->checkPermission('deny');
            }

			// 멀티샵
            $weca = new weagleEyeClient($_we, 'account');
			$multishop = array();
			$_multishop = $weca->call('getMyAccounts');
			foreach($_multishop as $key => $val) {
				if(!$val->domain[0] && !$val->domains[0]) continue;
				$domain = $val->domain[0];
				if(!$domain) {
					$domains = explode('>,<', preg_replace('/^<|>$/', '', $val->domains[0]));
					$domain = $domains[0];
				}
				if(!preg_match('/^https?:\/\//', $domain)) $domain = 'http://'.$domain;
				//$domain = preg_replace('@(https?://)(www\.)?@', '$1m.', $domain);
				$multishop[] = array(
					'name' => $val->site_name[0],
					'flag' => $val->flag_url[0],
					'account_id' => $val->account_id[0],
					'domain' => $domain,
                    'hashurl' => $domain.'/main/exec.php?'.http_build_query(array(
                        'exec_file' => 'common/ssoLogin2.php',
                        'admin_no' => $this->mng['no'],
                        'skey' => $this->hash,
                        'ret_url' => $root_url,
                        'site_key' => trim($GLOBALS['_site_key_file_info'][2]),
                        'contentType' => 'json',
                    )),
                    'service_type' => $val->mall_goods_idx[0],
				);
			}

			// 위사 고객센터
			$customer = array();
            if ($this->checkPermission('customer', '', 'return') == true) {
                $_customer = $weca->get(161, null, 1);
                if(count($_customer) > 0) {
                    foreach($_customer as $key => $val) {
                        if(gettype($_customer[$key]) == 'object') {
                            $customer[] = array(
                                'name' => $val->name[0],
                                'title' => stripslashes($val->title[0]),
                                'stat' => ($val->stat[0] == 103 || $val->stat[0] == 104) ? '처리완료' : '처리중',
                            );
                        }
                    }
                }
            }

			// 위사 업데이트 / 공지
			$notice = array();
			$_notice = @simplexml_load_string(comm(_HOSTING_NOTICE_XML_, 'ver=wing'));
			$_notice = $_notice->notice_main->article;
			if(count($_notice) > 0) {
				foreach($_notice as $key=>$val) {
					$notice[] = array(
						'no' => $val->no->__toString(),
						'category' => $val->bbs->__toString(),
						'title' => $val->title->__toString(),
						'content' => $val->content->__toString(),
						'reg_date' => $this->getDateStr($val->reg_date->__toString())
					);
				}
			}

			// 1대1 고객센터 미답변 글 수
            $today = strtotime(date('Y-m-d 00:00:00'));
			$cs_no_answerd = $this->db->row("select count(*) from {$tbl['cs']} where (reply_ok='N' or reply_ok is null)");
			$qna_no_answerd = $this->db->row("select count(*) from {$tbl['qna']} where notice='N' and (answer_ok='N' or answer_ok is null)");
			$cs_no_answerd_today = $this->db->row("select count(*) from {$tbl['cs']} where (reply_ok='N' or reply_ok is null) and reg_date >= $today");
			$qna_no_answerd_today = $this->db->row("select count(*) from {$tbl['qna']} where notice='N' and (answer_ok='N' or answer_ok is null) and reg_date >= $today");

            // 관리자 정보
            $mng = ($this->mng) ? $this->mng : $this->getAdmin();

			$crm_cnt = $this->db->row("select count(*) from $tbl[qna] where answer_date < 1 or answer_date is null");
			$this->result('Y', array(
				'crm_cnt' => $crm_cnt,
				'multishop' => $multishop,
				'customer' => $customer,
				'notice' => $notice,
				'cs' => array(
					'no_answerd_cnt' => $cs_no_answerd,
					'no_answerd_cnt_today' => $cs_no_answerd_today
				),
				'qna' => array(
					'no_answerd_cnt' => $qna_no_answerd,
					'no_answerd_cnt_today' => $qna_no_answerd_today
				),
                'current_admin' => array(
                    'level' => array(
                        'code' => $mng['level'],
                        'name_kr' => $_mng_levels[$mng['level']]
                    ),
                    'name' => $mng['name']
                )
			));
		}

        /**
         * 스마트앱 메뉴 권한 체크
         **/
        public function permissionList()
        {
			if($this->checkhash() == false) {
				$this->error('관리자 로그인 오류');
			}
            $mng = $this->getAdmin();
            $mng['auth'] = str_replace('@auth_detail', '', $mng['auth']);

            $this->result('Y', array(
                'level' => $mng['level'],
                'auth' => array(
                    'dashboard' => $this->checkPermission('main', '', 'return'),
                    'count_log' => $this->checkPermission('log', 'C0128', 'return'),
                    'product_list' => $this->checkPermission('product', 'C0004', 'return'),
                    'product_memo_list' => $this->checkPermission('product', 'C0079', 'return'),
                    'order_list' => $this->checkPermission('order', 'C0021', 'return'),
                    'member_list' => $this->checkPermission('member', 'C0035', 'return'),
                    'milage_list' => $this->checkPermission('member', 'C0045', 'return'),
                    'emoney_list' => $this->checkPermission('member', 'C0046', 'return'),
                    'coupon_list' => $this->checkPermission('promotion', 'C0152', 'return'),
                    'cs_list' => $this->checkPermission('member', 'C0041', 'return'),
                    'income_basic' => $this->checkPermission('income', 'C0118', 'return'),
                    'review_list' => $this->checkPermission('member', 'C0039', 'return'),
                    'qna_list' => $this->checkPermission('member', 'C0038', 'return'),
                    'member_memo_list' => $this->checkPermission('member', 'C0245', 'return'),
                    'order_memo_list' => $this->checkPermission('order', 'C0183', 'return'),
                    'board_content_list' => $this->checkPermission('board', 'C0113', 'return'),
                    'customer' => $this->checkPermission('customer', '', 'return'),
                    'service' => $this->checkPermission('wing', '', 'return'),
                )
            ));
        }

		// 상품리스트
		function productList() {
			global $tbl, $cfg, $root_url, $_cate_colname;

			// 페이징
			list($limit, $page, $page_size) = $this->getPageLimit();

            // 카테고리명 캐시
            getCategoriesCache();

			// 검색
			$w = '';
			$search_str = addslashes(trim($_REQUEST['search_str']));
			if($search_str) {
				$search_str = urldecode($search_str);
				$w .= " and (name like '%$search_str%' or keyword like '%$search_str%')";
			}
			$prd_stat = numberOnly($_REQUEST['prd_stat']);
			if($prd_stat > 1) {
				$w .= " and stat='$prd_stat'";
			}
            $code = addSlashes(trim($_REQUEST['code']));
            if ($code) {
                $w .= " and code='$code'";
            }
            switch($_REQUEST['shortcut']) {
                case 'Y' :
                    $w .= " and wm_sc>0";
                    break;
                case 'N' :
                    $w .= " and wm_sc=0";
                    break;
            }

			$stat = array(2, 3);
			if($this->checkhash() == true) {
				$this->checkPermission('product', 'C0004');
				$stat[] = 4;
			}
            $is_mng = $this->getAdmin();

			$stat = implode(',', $stat);
			$w .= " and stat in ($stat)";

            foreach(array(1, 4, 5) as $_ctype) {
                foreach ($_cate_colname[$_ctype] as $_cname) {
                    $_cno = (int) $_REQUEST[$_cname];
                    if ($_cno > 0) {
                        $w .= " and $_cname='$_cno'";
                    }
                }
            }

            if (isset($_REQUEST['ebig']) == true) {
                $ebig = numberOnly($_REQUEST['ebig']);
                $w .= " and ebig like '@$ebig@'";
            }
            if (isset($_REQUEST['mbig']) == true) {
                $ebig = numberOnly($_REQUEST['mbig']);
                $w .= " and mbig like '@$mbig@'";
            }

            if (empty($_REQUEST['sdate1']) == false && empty($_REQUEST['sdate2']) == false) {
                $sdate1 = strtotime($_REQUEST['sdate1']);
                $sdate2 = strtotime($_REQUEST['sdate2'])+86399;
                $w .= " and reg_date between '$sdate1' and '$sdate2'";
            }

            // 입점사 검색 조건
            $pw = '';
            if ($this->mng['level'] == 4) {
                $partner_no = (int) $this->mng['partner_no'];
                $pw .= " and partner_no='$partner_no'";
            }

			$product_list = array();
			$img_url = getFileDir('_data/product');
			$attach_url = getFileDir('_data/attach');
			$rows = 0;
			$add_field = '';
			if($cfg['use_m_content_product'] == 'Y') {
				$add_field .= ", use_m_content, m_content";
			}
            if ($cfg['max_cate_depth'] == 4) {
                $add_field .= ", depth4, xdepth4, ydepth4";
            }
            if(fieldExist($tbl['product'], 'oversea_free_delivery') == true){
                $add_field .= ', oversea_free_delivery';
            }
            if($cfg['use_no_mile/cpn'] == 'Y') {
                $add_field .= ", no_milage, no_cpn";
            }
            switch ($_REQUEST['sort']) {
                case '2' :
                    $sort = 'edt_date desc';
                    break;
                case '3' :
                    $sort = 'hit_view desc';
                    break;
                case '4' :
                    $sort = 'hit_cart desc';
                    break;
                case '5' :
                    $sort = 'hit_order desc';
                    break;
                default :
                    $sort = 'no desc';
            }

			$res = $this->db->iterator("select no, hash, code, name, keyword, stat, big, mid, small, xbig, xmid, xsmall, ybig, ymid, ysmall, ebig, mbig, updir, upfile3, normal_prc, sell_prc, hit_view, hit_cart, hit_wish, hit_order, qna_cnt, reg_date, content2, wm_sc, event_sale, member_sale, free_delivery, checkout, dlv_alone, tax_free $add_field from $tbl[product] where 1 $w $pw order by $sort $limit");
			foreach ($res as $data) {
				$data = shortCut($data);
				if($data['no'] != $data['parent']) $data['wm_sc'] = $data['parent'];
                if (is_null($data['code']) == true) $data['code'] = '';

				// 부가 이미지
				$addimg = array();
				$sort = ($cfg['up_aimg_sort'] == 'Y') ? 'order by sort asc' : '';
				$ares = $this->db->iterator("select no, updir, filename from $tbl[product_image] where pno='$data[no]' and filetype in (2, 8) $sort");
                foreach ($ares as $img) {
					$addimg[] = array(
						'img_no' => $img['no'],
						'name' => $attach_url.'/'.$img['updir'].'/'.$img['filename']
					);
				}

				if($cfg['mobile_use'] == 'Y' && $data['use_m_content'] == 'Y' && trim(strip_tags($data['m_content'], '<img><iframe><video><embed><object>'))) {
					$data['m_content_tmp'] = $data['m_content'];
				}

				if($_REQUEST['is_content'] != 'Y') unset($data['content2']);

                $attr = array();
                if($data['event_sale'] == 'Y') $attr[] = '이벤트';
                if($data['member_sale'] == 'Y') $attr[] = '회원혜택';
                if($data['free_delivery'] == 'Y') $attr[] = '무료배송';
                if($data['oversea_free_delivery'] == 'Y') $attr[] = '해외무료배송';
                if($data['dlv_alone'] == 'Y') $attr[] = '단독배송';
                if($data['checkout'] == 'Y') $attr[] = '네이버페이';
                if($data['tax_free'] == 'Y') $attr[] = '비과세';
                if($data['no_milage'] == 'Y') $attr[] = '적립금사용불가';
                if($data['no_cpn'] == 'Y') $attr[] = '쿠폰사용불가';
    			$attr = implode(',', $attr);

				$prd = array(
					'pno' => $data['no'],
                    'code' => $data['code'],
                    'system_code' => $data['hash'],
					'title' => stripslashes($data['name']),
					'keyword' => stripslashes($data['keyword']),
					'stat' => $this->getPrdStat($data['stat']),
					'cate1' => $this->getCateName($data['big']),
					'cate2' => $this->getCateName($data['mid']),
					'cate3' => $this->getCateName($data['small']),
					'cate4' => $this->getCateName($data['depth4']),
                    'xcate1' => $this->getCateName($data['xbig']),
                    'xcate2' => $this->getCateName($data['xmid']),
                    'xcate3' => $this->getCateName($data['xsmall']),
                    'xcate4' => $this->getCateName($data['xdepth4']),
                    'ycate1' => $this->getCateName($data['ybig']),
                    'ycate2' => $this->getCateName($data['ymid']),
                    'ycate3' => $this->getCateName($data['ysmall']),
                    'ycate4' => $this->getCateName($data['ydepth4']),
                    'big' => $data['big'],
                    'mid' => $data['mid'],
                    'small' => $data['small'],
                    'depth4' => $data['depth4'],
                    'xbig' => $data['ybig'],
                    'xmid' => $data['ymid'],
                    'xsmall' => $data['ysmall'],
                    'xdepth4' => $data['xdepth4'],
                    'ybig' => $data['ybig'],
                    'ymid' => $data['ymid'],
                    'ysmall' => $data['ysmall'],
                    'xdepth4' => $data['xdepth4'],
                    'ebig' => $this->getEventNames($data['ebig']),
                    'mbig' => $this->getEventNames($data['mbig']),
                    'ebig_no' => str_replace('@', ',', trim($data['ebig'], '@')),
                    'mbig_no' => str_replace('@', ',', trim($data['mbig'], '@')),
                    'attr' => $attr,
					'photo' => $img_url.'/'.$data['updir'].'/'.$data['upfile3'],
                    'detail_photo' => $img_url.'/'.$data['updir'].'/'.$data['upfile2'],
					'normal_prc' => parsePrice($data['normal_prc']),
					'sell_prc' => parsePrice($data['sell_prc']),
					'addimg' => $addimg,
					'reg_date' => $this->getDateStr($data['reg_date']),
					'content2' => stripslashes($data['content2']),
					'content2_m' => stripslashes($data['m_content_tmp']),
                    'detailLink' => $root_url.'/shop/detail.php?pno='.$data['hash']
				);
                if ($is_mng) {
                    $prd = array_merge($prd, array(
                        'hit_view' => $data['hit_view'],
                        'hit_cart' => $data['hit_cart'],
                        'hit_wish' => $data['hit_wish'],
                        'hit_order' => $data['hit_order'],
                        'qna_cnt' => $data['qna_cnt'],
                        'wm_sc' => $data['wm_sc'],
                    ));
                }
                $product_list[] = $prd;

			}

			if(count($product_list)) {
				// 상태별 통계
				$_tabcnt = array('total' => 0, 2 => 0, 3 => 0, 4 => 0);
				$wt = preg_replace("/ and stat='[0-9]'/", '', $w);
				$_tmpres = $this->db->iterator("select stat, count(*) as cnt from $tbl[product] where 1 $wt $pw group by stat");
                foreach ($_tmpres as $_tmp) {
					$_tabcnt[$_tmp['stat']] = $_tmp['cnt'];
					$_tabcnt['total'] += $_tmp['cnt'];
				}

				$this->result('Y', array(
					'total_rows' => $this->db->row("select count(*) from $tbl[product] where stat between 2 and 4 $pw"),
					'rows' => $this->db->row("select count(*) from $tbl[product] where stat between 2 and 4 $w $pw"),
					'rows_2' => $_tabcnt[2],
					'rows_3' => $_tabcnt[3],
					'rows_4' => $_tabcnt[4],
					'product' => $product_list,
				));
			} else {
				$this->error('검색된 상품이 없습니다.');
			}
		}

		// 상품 상세정보
		function product() {
			global $tbl, $cfg, $prd;

			$stat = array(2, 3);
			if($this->checkhash() == true) {
				$stat[] = 4;
			}
			$stat = implode(',', $stat);

            // 입점사 검색 조건
            $pw = '';
            if ($this->mng['level'] == 4) {
                $partner_no = (int) $this->mng['partner_no'];
                $pw .= " and partner_no='$partner_no'";
            }

			$pno = numberOnly($_REQUEST['pno']);
			$data = $this->db->assoc("select * from {$tbl['product']} where no='$pno' and stat in ($stat) $pw");
			if(!$data) {
				$this->error('조회된 상품이 없습니다.');
			}
			$data = array_map('stripslashes', $data);
			$data = shortCut($data);
			$data['timesale'] = 'N';
			if($data['ts_use'] == 'Y' && $data['ts_dates'] <= time() && $data['ts_datee'] >= time()) {
				$data['timesale'] = 'Y';
			}

			if($this->checkhash() == false) {
				$data['keyword'] = null;
				$data['ea_type'] = null;
			} else {
				if($data['ebig']) {
					$ebig = $this->getEventNames($data['ebig']);
				}
				if($data['mbig']) {
					$mbig = $this->getEventNames($data['mbig']);
				}
				$ebig_no = trim(str_replace('@', ',', $data['ebig']), ',');
				$mbig_no = trim(str_replace('@', ',', $data['mbig']), ',');
			}

			if($cfg['mobile_use'] == 'Y' && $data['use_m_content'] == 'Y' && trim(strip_tags($data['m_content'], '<img><iframe><video><embed><object>'))) {
				$data['m_content_tmp'] = $data['m_content'];
			}

            if (is_null($data['code']) == true) $data['code'] = '';

			$attr = array();
			if($data['event_sale'] == 'Y') $attr[] = '이벤트';
			if($data['member_sale'] == 'Y') $attr[] = '회원혜택';
			if($data['free_delivery'] == 'Y') $attr[] = '무료배송';
			if($data['oversea_free_delivery'] == 'Y') $attr[] = '해외무료배송';
			if($data['dlv_alone'] == 'Y') $attr[] = '단독배송';
			if($data['checkout'] == 'Y') $attr[] = '네이버페이';
			if($data['tax_free'] == 'Y') $attr[] = '비과세';
			if($data['no_milage'] == 'Y') $attr[] = '적립금사용불가';
			if($data['no_cpn'] == 'Y') $attr[] = '쿠폰사용불가';
			$attr = implode(',', $attr);

			$milage = 0;
			if($cfg['milage_use'] == 1) {
				$milage = ($cfg['milage_type'] == 1) ? $data['milage'] : getPercentage($data['sell_prc'], $cfg['milage_type_per'], 0, $cfg['currency_decimal']);
			}

			$memos = array();
            if ($this->checkPermission('product', 'C0079', 'return') == true) {
                $mres = $this->db->iterator("select * from $tbl[order_memo] where type=3 and ono='$data[no]'");
                foreach ($mres as $mdata) {
                    $memos[] = array(
                        'no' => $mdata['no'],
                        'admin_id' => $mdata['admin_id'],
                        'content' => stripslashes($mdata['content']),
                        'reg_date' => $this->getDateStr($mdata['reg_date'])
                    );
                }
            }

            $delivery_type = 'basic';
            if ($data['free_delivery'] == 'Y') $delivery_type = 'free_delivery';
            if ($data ['delivery_set'] > 0) {
                $data['free_delivery'] = 'N';
                $delivery_type = 'product';
            }

            // 상품 정보고시
            $prd['parent'] = $data['parent'];
            $definition = array();
            while ($field = prdFiledList($data['no'], $data['fieldset'])) {
                $definition[] = array(
                    'idx' => $field['no'],
                    'subject' => stripslashes($field['name']),
                    'value' => stripslashes($field['value'])
                );
            }

            // 부가이미지
            $images = array();
            $ires = $this->db->iterator("select no, updir, filename from {$tbl['product_image']} where pno='$pno' and filetype=2");
            foreach ($ires as $img) {
                $images[] = array(
                    'img_no' => $img['no'],
                    'name' => getListImgURL($img['updir'], $img['filename'])
                );
            }

			$this->result('Y', array(
                'pno' => $data['no'],
                'code' => $data['code'],
                'system_code' => $data['hash'],
				'name' => $data['name'],
				'stat' => $this->getPrdStat($data['stat']),
				'cate1' => $this->getCateName($data['big'], false),
				'cate2' => $this->getCateName($data['mid'], false),
				'cate3' => $this->getCateName($data['small'], false),
				'cate4' => $this->getCateName($data['depth4'], false),
				'xcate1' => $this->getCateName($data['xbig'], false),
				'xcate2' => $this->getCateName($data['xmid'], false),
				'xcate3' => $this->getCateName($data['xsmall'], false),
				'xcate4' => $this->getCateName($data['xdepth4'], false),
				'ycate1' => $this->getCateName($data['ybig'], false),
				'ycate2' => $this->getCateName($data['ymid'], false),
				'ycate3' => $this->getCateName($data['ysmall'], false),
				'ycate4' => $this->getCateName($data['ydepth4'], false),
				'big' => $data['big'],
				'mid' => $data['mid'],
				'small' => $data['small'],
				'depth4' => $data['depth4'],
				'xbig' => $data['ybig'],
				'xmid' => $data['ymid'],
				'xsmall' => $data['ysmall'],
				'xdepth4' => $data['xdepth4'],
				'ybig' => $data['ybig'],
				'ymid' => $data['ymid'],
				'ysmall' => $data['ysmall'],
				'ydepth4' => $data['ydepth4'],
                'upfile1' => getListImgURL($data['updir'], $data['upfile1']),
                'upfile2' => getListImgURL($data['updir'], $data['upfile2']),
                'upfile3' => getListImgURL($data['updir'], $data['upfile3']),
                'images' => $images,
				'content1' => $data['content1'],
				'content2' => $data['content2'],
				'content2_m' => $data['m_content_tmp'],
				'keyword' => $data['keyword'],
				'timesale' => $data['timesale'],
				'ebig' => $ebig,
				'mbig' => $mbig,
				'ebig_no' => $ebig_no,
				'mbig_no' => $mbig_no,
				'attr' => $attr,
				'normal_prc' => $data['normal_prc'],
				'sell_prc' => $data['sell_prc'],
				'sell_prc9' => $data['sell_prc9'],
				'sell_prc8' => $data['sell_prc8'],
				'sell_prc7' => $data['sell_prc7'],
				'sell_prc6' => $data['sell_prc6'],
				'sell_prc5' => $data['sell_prc5'],
				'sell_prc4' => $data['sell_prc4'],
				'sell_prc3' => $data['sell_prc3'],
				'sell_prc2' => $data['sell_prc2'],
				'sell_prc_consultation' => $data['sell_prc_consultation'],
				'milage' => parsePrice($milage),
				'ea_type' => $data['ea_type'],
				'min_ord' => $data['min_ord'],
				'max_ord' => $data['max_ord'],
				'content1' => $data['content1'],
				'content2' => $data['content2'],
				'hit_cart' => $data['hit_view'],
				'hit_cart' => $data['hit_cart'],
				'hit_order' => $data['hit_order'],
				'hit_wish' => $data['hit_wish'],
				'mng_memo' => $memos,
                'stock_type' => ($data['ea_type'] == 1) ? 'Y' : 'N',
                'delivery_type' => $delivery_type,
                'delivery_set_no' => $data['delivery_set'],
                'definition' => $definition
			));
		}

		// 상품 공급사 정보
		function productVendor() {
			global $tbl;

			$this->checkPermission('product', 'C0005');

			$pno = numberOnly($_REQUEST['pno']);

            $this->getAdmin();
            $pw = '';
            if ($this->mng['level'] == 4) {
                $pw = " and a.partner_no='{$this->mng['partner_no']}'";
            }

			$data = $this->db->assoc("select b.* from $tbl[product] a inner join $tbl[provider] b on a.seller_idx=b.no where a.no='$pno' and a.stat in (2, 3, 4) $pw");
			if(is_array($data)) {
				$data = array_map('stripslashes', $data);
			}
			$prd = $this->db->assoc("select origin_prc, origin_name from $tbl[product] where no='$pno'");

			$this->result('Y', array(
				'provider' => $data['provider'],
				'arcade' => $data['arcade'],
				'floor' => $data['floor'],
				'plocation' => $data['plocation'],
				'ptel' => $data['ptel'],
				'pcell' => $data['pcell'],
				'origin_prc' => parsePrice($prd['origin_prc']),
				'origin_name' => stripslashes($prd['origin_name']),
			));
		}

		// 상품 아이콘 정보
		function productIcon() {
			global $tbl;

			$pno = numberOnly($_REQUEST['pno']);
			$icons = $this->db->row("select icons from $tbl[product] where no='$pno' and stat in (2, 3)");
			$icons = explode('@', trim($icons, '@'));
			$url = getFileDir('_data/icon');

			$icon_list = array();
			$res = $this->db->iterator("select * from $tbl[product_icon] where itype=''");
            foreach ($res as $data) {
				$icon_list[] = array(
					'no' =>  $data['no'],
					'icon' => $url.'/_data/icon/'.$data['upfile'],
					'use' => (in_array($data['no'], $icons)) ? 'Y' : 'N'
				);
			}

			$this->result('Y', array(
				'icons' => $icon_list,
			));
		}

		// 관련상품 정보
		function productRefprd() {
			global $tbl, $cfg;

			if($this->checkhash() == true) {
				$sstat = '2,3,4';
			} else {
				$sstat = '2,3';
			}

			$pno = numberOnly($_REQUEST['pno']);

            $this->getAdmin();
            $pw = '';
            if ($this->mng['level'] == 4) {
                $pw = " and a.partner_no='{$this->mng['partner_no']}'";
            }

			$ref_list = array();
			$img_url = getFileDir('_data/product');
			$res = $this->db->iterator("
                select b.name, b.origin_prc, b.normal_prc, b.sell_prc, b.milage, b.updir, b.upfile3, b.stat
                from $tbl[product_refprd] a inner join $tbl[product] b on a.refpno=b.no where a.pno='$pno' and b.stat in ($sstat) $pw
                and a.group!=99 $pw
            ");
            foreach ($res as $data) {
				$milage = 0;
				if($cfg['milage_use'] == 1) {
					$milage = ($cfg['milage_type'] == 1) ? $data['milage'] : getPercentage($data['sell_prc'], $cfg['milage_type_per'], 0, $cfg['currency_decimal']);
				}

				$ref_list[] = array(
					'name' => stripslashes($data['name']),
					'origin_prc' => parsePrice($data['origin_prc']),
					'normal_prc' => parsePrice($data['normal_prc']),
					'sell_prc' => parsePrice($data['sell_prc']),
					'milage' => $milage,
					'stat' => $this->getPrdStat($data['stat']),
					'photo' => $img_url.'/'.$data['updir'].'/'.$data['upfile3'],
					'hidden' => ($data['stat'] == 4) ? 'Y' : 'N',
				);
			}

			$this->result('Y', array(
				'refprd' => $ref_list,
			));
		}

		// 상품 옵션 정보
		function productOption($opno = null) {
			global $tbl, $_otype;

            if ($opno > 0) {
                $w ="no='$opno'";
            } else {
    			$pno = numberOnly($_REQUEST['pno']);
                $w = "pno='$pno'";
            }


			$res = $this->db->query("select no, name, necessary, otype from $tbl[product_option_set] where $w order by sort asc");
			foreach ($res as $data) {
				$items = $items_list = $add_price = array();
				$ires = $this->db->iterator("select no, iname, add_price from $tbl[product_option_item] where opno='$data[no]' order by sort asc");
				foreach ($ires as $item) {
					$items[] = stripslashes(str_replace(',', '', $item['iname']));
					$add_price[] = parsePrice($item['add_price']);
                    $items_list[] = array(
                        'no' => $item['no'],
                        'name' => stripslashes($item['iname']),
                        'add_price' => $item['add_price']
                    );
				}
				$opt_list[] = array(
                    'opno' => $data['no'],
					'name' => stripslashes($data['name']),
					'otype' => $_otype[$data['otype']],
					'necessary' => $data['necessary'],
					'items' => implode(',', $items),
					'add_price' => implode(',', $add_price),
                    'items_list' => $items_list,
				);
			}

			$this->result('Y', array(
				'options' => $opt_list,
			));
		}

		// 상품 재고 정보
		function productComplexOption() {
			global $tbl, $_erp_force_stat;

            $pno = numberOnly($_REQUEST['pno']);
            $barcode = addslashes($_REQUEST['barcode']);
            if (empty($pno) == true && empty($barcode) == true) {
                $this->error('상품번호나 바코드를 입력해주세요.');
            }

            if ($pno) $w .= " and p.no='$pno'";
            if ($barcode) $w .= " and c.barcode='$barcode'";

            $this->getAdmin();
            if ($this->mng['level'] == 4) {
                $w .= " and p.partner_no='{$this->mng['partner_no']}'";
            }

            $complex_list = array();
			$res = $this->db->iterator("
                select
                    c.pno, c.complex_no, c.barcode, c.opts, c.qty, c.force_soldout,
                    p.name, p.updir, p.upfile3
                from erp_complex_option c inner join {$tbl['product']} p on c.pno=p.no
                where c.del_yn='N' $w
            ");
            foreach ($res as $data) {
				$complex_list[] = array(
					'complex_no' => $data['complex_no'],
					'barcode' => $data['barcode'],
                    'pno' => $data['pno'],
                    'product_name' => $data['name'],
                    'image' => getListImgURL($data['updir'], $data['upfile3']),
					'item_name' => getComplexOptionName($data['opts']),
					'qty' => $data['qty'],
					'type' => $data['force_soldout'],
                    'type_str' => $_erp_force_stat[$data['force_soldout']],
				);
			}

			$this->result('Y', array(
				'complexOptions' => $complex_list,
			));
		}

        // 상품정보고시 조회
        function definitionSet() {
            global $tbl;

			if($this->checkhash() == false) {
				$this->error('관리자 로그인 오류');
			}

            $definition = array();
            $res = $this->db->iterator("select no, code, name from {$tbl['category']} where ctype=3 order by sort asc");
            foreach ($res as $data) {
                $items = array();
                $field = $this->db->iterator("select no, name, default_value from {$tbl['product_field_set']} where category='{$data['no']}' order by sort asc");
                foreach ($field as $fdata) {
                    $items[] = array(
                        'idx' => $fdata['no'],
                        'subject' => stripslashes($fdata['name']),
                        'default' => stripslashes($fdata['default_value']),
                    );
                }
                $definition[]  = array(
                    'no' => $data['no'],
                    'type' => $data['code'],
                    'name' => stripslashes($data['name']),
                    'items' => $items
                );
            }

            $this->result('Y', array(
                'definition' => $definition
            ));
        }

        // 상품정보고시 등록
        function setDefinition() {
            global $tbl;

			if($this->checkhash() == false) {
				$this->error('관리자 로그인 오류');
			}

            $pno = numberOnly($_REQUEST['pno']);
            $fieldset = numberOnly($_REQUEST['fieldset']);
            $datas = json_decode($_REQUEST['datas']);

            // 입점사 검색 조건
            if ($this->mng['level'] == 4) {
                $partner_no = (int) $this->mng['partner_no'];
                $pw .= " and partner_no='$partner_no'";
            }

            if (empty($pno) == true) $this->error('상품번호를 입력해주세요.');
            if (empty($fieldset) == true) $this->error('정보고시 번호를 입력해주세요.');
            if (is_null($datas) || count($datas) == 0) $this->error('정보고시 내용을 입력해주세요.');
            if ($this->db->row("select count(*) from {$tbl['product']} where no='$pno' $pw") == 0) {
                $this->error('상품을 찾을수 없습니다.');
            }

            $this->db->query("update {$tbl['product']} set fieldset='$fieldset' where no='$pno'");
            foreach ($datas as $val) {
                $value = addslashes($val->value);
                if ($this->db->row("select count(*) from {$tbl['product_field']} where pno='$pno' and fno='$val->idx'") > 0) {
                    $this->db->query("update {$tbl['product_field']} set value='$value' where pno='$pno' and fno='$val->idx'");
                } else {
                    $this->db->query("
                        insert into {$tbl['product_field']}
                            (pno, fno, value) values ('$pno', '$val->idx', '$value')
                    ");
                }
            }
            $this->result('Y', null);
        }

		public function getCategory() {
			global $tbl, $_cate_colname;

			$ctype = numberOnly($_REQUEST['ctype']);
			$parent = numberOnly($_REQUEST['parent']);

			if(empty($ctype) == true) {
				$this->error('조회할 카테고리 종류를 선택해주세요.');
			}

            $where = '';
			if($parent > 0) {
				$cdata = $this->db->assoc("select * from $tbl[category] where ctype='$ctype' and no='$parent'");
				$_nlevel = ($cdata['level']+1);
				$_parent = $_cate_colname[1][$cdata['level']];

				$where .= " and level='$_nlevel' and $_parent='$parent'";
			} else {
				$where .= " and level=1";
			}

			$mng = $this->getAdmin();
			if(!$mng['no']) {
				$where .= " and hidden='N'";
			}

			$datas = array();
			$res = $this->db->iterator("select no, name from $tbl[category] where ctype='$ctype' $where order by sort asc");
            foreach ($res as $data) {
				$datas[] = $data;
			}

			$this->result('Y', array('data' => $datas));
		}

		// 상품 상태 변경
		public function setProduct() {
			global $tbl, $engine_dir, $admin;

			$this->checkPermission('product', 'C0005');

			$pno = numberOnly($_REQUEST['pno']);
			$stat = $_REQUEST['stat'];

			if(!$pno) $this->error('상품정보 오류');
			if(!$stat || in_array($stat, array('2','3','4','D')) == false) $this->error('처리코드 오류');

			$prd = $this->db->assoc("select * from $tbl[product] where no='$pno' $pw");

            // 입점사 권한 체크
            $this->getAdmin();
			if ($this->mng['level'] == 4 && $prd['partner_no'] != $this->mng['partner_no']) {
                return $this->error(__lang_common_error_noperm__);
            }

			if(!$prd['no']) $this->error('존재하지 않는 상품코드입니다.');
			if($prd['stat'] == $stat) $this->error('변경할 상태가 현재상태와 동일합니다.');

			if($stat == 'D') {
				$admin = $mng;
				$_bak = $_SESSION['admin_no'];
				$_SESSION['admin_no'] = $admin['no'];

				$cache_goods = true;
				include $engine_dir.'/_manage/manage.lib.php';
				delPrd($pno);

				$_SESSION['admin_no'] = $_bak;
			} else {
				$this->db->query("update $tbl[product] set stat='$stat' where no='$pno'");
				prdStatLogw($pno, $stat, $prd['stat']);
			}

			$this->result('Y', '처리되었습니다.');
		}

		public function productRegist() {
			global $tbl, $cfg, $_cate_colname;

			$this->checkPermission('product', 'C0005');

			$tmpkey = trim(addslashes($_REQUEST['tmpkey']));
			$pno = numberOnly($_REQUEST['pno']);
            $code = trim(addslashes($_REQUEST['code']));
			$name = trim(addslashes($_REQUEST['name']));
			$sell_prc = numberOnly($_REQUEST['sell_prc']);
			$normal_prc = numberOnly($_REQUEST['normal_prc']);
			$content2 = trim(addslashes($_REQUEST['content2']));
            $m_content = trim(addslashes($_REQUEST['m_content']));
			$keyword = trim(addslashes($_REQUEST['keyword']));
			$stat = numberOnly($_REQUEST['stat']);
            $free_delivery = ($_REQUEST['free_delivery'] == 'Y') ? 'Y' : 'N';
            $delivery_set = numberOnly($_REQUEST['delivery_set']);
            $ea_type = numberOnly($_REQUEST['ea_type']);
			if(in_array($stat, array(2, 3, 4)) == false) $stat = 4;
			$now = time();

			//if(!$tmpkey) $this->error('임시코드 입력오류');
			if(!$name) $this->error('상품명을 입력해주세요.');
			if(!$sell_prc) $this->error('판매가를 입력해주세요.');
			//if(!$content2) $this->error('상품설명을 입력해주세요.');

            $sql1 = $sql2 = $sql3 = '';
            if($cfg['use_prd_dlvprc'] == 'Y') {
                $sql1 .= ", delivery_set='$delivery_set'";
                $sql2 .= ", delivery_set";
                $sql3 .= ", '$delivery_set'";
            }

            if ($_REQUEST['ea_type']) {
                $sql1 .= ", ea_type='$ea_type'";
                $sql2 .= ", ea_type";
                $sql3 .= ", '$ea_type'";
            }

            if ($cfg['use_m_content_product'] == 'Y' && isset($_REQUEST['m_content']) == true) {
                $use_m_content = (empty($m_content) == true) ? 'N' : 'Y';
                $sql1 .= ", m_content='$m_content', use_m_content='$use_m_content'";
                $sql2 .= ", m_content, use_m_content";
                $sql3 .= ", '$m_content', '$use_m_content'";
            }

            // 입점사 권한 처리
            if ($this->mng['level'] == 4) {
                if ($cfg['partner_prd_accept'] != 'N') {
                    $this->error('입점몰 설정에서 입점사의 상품 등록이 제한되었습니다.');
                }
                $partner_no = (int) $this->mng['partner_no'];

                // 등록
                $sql2 .= ", partner_no";
                $sql3 .= ", '$partner_no'";

                // 수정
                if($pno > 0) {
                    $prd = $this->db->assoc("select partner_no from {$tbl['product']} where no='$pno'");
                    if ($prd['partner_no'] != $partner_no) {
                        $this->error(__lang_common_error_modifyperm__);
                    }
                }
            }

            // 기본 매장분류
            $__cate = array('1' => $_cate_colname[1], '4' => $_cate_colname[4], '5' => $_cate_colname[5]);
            foreach ($__cate as $_ctype => $cdata) {
                $csql = '';
                foreach ($cdata as $_level => $_cname) {
                    if (isset($_REQUEST[$_cname]) == true && ($_level == 1 || isset($_REQUEST[$_cate_colname[$_ctype][($_level-1)]]))) {
                        $_cno = (int) $_REQUEST[$_cname];

                        if ($this->db->row("select count(*) from {$tbl['category']} where no=? and level=? and ctype=? $csql", array($_cno, $_level, $_ctype)) == 1) {
                            $sql1 .= ", $_cname='$_cno'";
                            $sql2 .= ", $_cname";
                            $sql3 .= ", '$_cno'";

                            $csql .= " and ".$_cate_colname[1][$_level]."='$_cno'";
                        }
                    } else {
                        if (isset($_REQUEST[$_cate_colname[$_ctype][1]])) {
                            $sql1 .= ", $_cname='0'";
                        }
                    }
                }
            }

            if($pno > 0) {
                $qry = "update $tbl[product] set code='$code', name='$name', keyword='$keyword', stat='$stat', sell_prc='$sell_prc', normal_prc='$normal_prc', content2='$content2', edt_date='$now', free_delivery='$free_delivery' $sql1 where no='$pno'";

                $prd = $this->db->assoc("select updir from {$tbl['product']} where no='$pno'");
            } else {
                // 상품진열 순서
                $_sort_info = $this->db->assoc("select max(sortbig) as sortbig, max(sortmid) as sortmid, max(sortsmall) as sortsmall from {$tbl['product']}");
                $_sortbig = $_sort_info['sortbig']+1;
                $_sortmid = $_sort_info['sortmid']+1;
                $_sortsmall = $_sort_info['sortsmall']+1;
                $sql2 .= ", sortbig, sortmid, sortsmall";
                $sql3 .= ", '$_sortbig', '$_sortmid', '$_sortsmall'";

                $pno = $this->db->row("select max(no) from $tbl[product]")+1;
                $hash = strtoupper(md5($pno));
                $qry = "insert into $tbl[product] (no, hash, code, stat, name, keyword, sell_prc, normal_prc, content2, edt_date, reg_date, free_delivery $sql2) values ($pno, '$hash', '$code', '$stat', '$name', '$keyword', '$sell_prc', '$normal_prc', '$content2', '$edt_date', '$now', '$free_delivery' $sql3)";
            }

			// 부가이미지 삭제
			$del_addimg = explode(',', $_REQUEST['del_addimg']);
			if(count($del_addimg) > 0) {
				foreach($del_addimg as $imgno) {
					$imgno = numberOnly($imgno);
					$img = $this->db->assoc("select no, updir, filename from $tbl[product_image] where pno='$pno' and no='$imgno'");
					deleteAttachFile($img['updir'], $img['filename']);
					$this->db->query("delete from $tbl[product_image] where pno='$pno' and no='$imgno'");
				}
			}

			$r = $this->db->query($qry);
			if($r) {
				$sort_addimg = explode(',', trim($_REQUEST['sort_addimg']));
				foreach($sort_addimg as $key => $imgno) {
					$sort = $key+1;
					$imgno = numberOnly($imgno);
					$this->db->query("update $tbl[product_image] set sort='$sort' where pno='$pno' and no='$imgno'");
				}

                // 이미지 업로드
                if ($tmpkey > 0) {
                    $this->setImage($tmpkey, $pno);
                } else {
                    // 대중소 이미지
                    $file_sql = '';
                    for($i = 1; $i <= 3; $i++) {
                        $filename = $_REQUEST['upfile'.$i];
                        if ($filename) {
                            $updir = (isset($prd['updir']) == true && $prd['updir']) ? $prd['updir'] : '_data/product/'.date('Ym/d');
                            if (!$file_sql) $file_sql = "updir='$updir'";
                            $upinfo = $this->upload($filename, $updir);
                            if ($upinfo != false) {
                                $file_sql .= ", upfile{$i}='{$upinfo['name']}', w{$i}='{$upinfo['width']}', h{$i}='{$upinfo['height']}'";
                            }
                        }
                    }
                    if ($file_sql) {
                        $this->db->query("update {$tbl['product']} set $file_sql where no='$pno'");
                    }

                    // 부가이미지
                    $attach_img = json_decode($_REQUEST['attach_img']);
                    if (is_array($attach_img) == true) {
                        foreach ($attach_img as $filename) {
                            $updir = (isset($prd['updir']) == true && $prd['updir']) ? $prd['updir'] : '_data/product/'.date('Ym/d');
                            $upinfo = $this->upload($filename, $updir);
                            if($upinfo != false) {
                                $sort = $this->db->row("select max(sort) from {$tbl['product_image']} where pno='$pno' and filetype=2")+1;
                                $this->db->query("
                                    insert into {$tbl['product_image']}
                                        (pno, filetype, updir, filename, stat, reg_date, width, height, filesize, sort)
                                        values
                                        ('$pno', '2', '$updir', '{$upinfo['name']}', '2', unix_timestamp(now()), '{$upinfo['width']}', '{$upinfo['height']}', '{$upinfo['size']}', '$sort')
                                ");
                            }
                        }
                    }
                }

                $this->result('Y', array(
                    'message' => '상품 저장이 완료되었습니다.',
                    'pno' => $pno,
                ));
			} else {
				$this->error($this->db->geterror());
			}
		}

        public function upload($source, $updir) {
            global $root_dir;

            $name_prefix = md5($source.rand(0,9999));
            $tmp_name = $name_prefix.'.'.getExt(basename($source));

            $file = new CurlConnection($source);
            $file->exec();
            fwriteTo('_data/'.$tmp_name, $file->getResult());

            $img = getimagesize($root_dir.'/_data/'.$tmp_name);
            if (in_array($img[2], array(1, 2, 3)) == true) {
                if ($updir) makeFullDir($updir);

                $filesize = filesize($root_dir.'/_data/'.$tmp_name);
                uploadFile(array(
                    'name' => $tmp_name,
                    'tmp_name' => $root_dir.'/_data/'.$tmp_name,
                    'size' => $filesize,
                ), $name_prefix, $updir);

                if (file_exists($root_dir.'/_data/'.$tmp_name) == true) {
                    unlink($root_dir.'/_data/'.$tmp_name);
                }

                return array(
                    'name' => $tmp_name,
                    'width' => $img[0],
                    'height' => $img[1],
                    'size' => $filesize
                );
            }

            if (file_exists($root_dir.'/_data/'.$tmp_name) == true) {
                unlink($root_dir.'/_data/'.$tmp_name);
            }
            return false;
        }

		// 상품 상세정보 수정
		public function updateProduct() {
			global $tbl, $cfg;

			$this->checkPermission('product', 'C0005');

			$pno = numberOnly($_REQUEST['pno']);
			$req = json_decode($_REQUEST['params']);
			$status = 'N';

            // 입점사 권한 처리
            if ($this->mng['level'] == 4) {
                $partner_no = (int) $this->mng['partner_no'];

                $prd = $this->db->assoc("select partner_no from {$tbl['product']} where no='$pno'");
                if ($prd['partner_no'] != $partner_no) {
                    $this->error(__lang_common_error_modifyperm__);
                }
            }

			$editable = array(
				'code', 'stat', 'name', 'big', 'mid', 'small', 'depth4', 'ebig', 'mbig',
				'event_sale', 'member_sale', 'free_delivery', 'dlv_alone', 'checkout', 'tax_free', 'no_milage', 'no_cpn',
				'content1', 'content2', 'keyword',
				'sell_prc', 'normal_prc', 'icons'
			);
            if (isset($req->free_delivery) == true) {
                if ($req->free_delivery != 'Y') {
                    $req->free_delivery = 'N';
                }
            }
            if ($cfg['use_prd_dlvprc'] == 'Y') {
                $editable[] = 'delivery_set';
                if ($req->free_delivery == 'Y') {
                    $req->delivery_set = 0;
                }
            }

			if(!$pno) {
				$message = '상품번호를 입력해주세요.';
			} else {
				if(is_object($req) == true) {
					$sql = '';
					foreach($req as $key => $val) {
						if(in_array($key, $editable) == false) {
							$message = "처리할수 없는 데이터 '$key'";
							break;
						}

						switch($key) {
							case 'ebig' :
                                $this->setCategoryLink($pno, 2, $val);
								$val = '@'.trim(str_replace(',', '@', $val), '@').'@';
								break;
							case 'mbig' :
                                $this->setCategoryLink($pno, 6, $val);
								$val = '@'.trim(str_replace(',', '@', $val), '@').'@';
								break;
							case 'icons' :
								$val = '@'.trim(str_replace(',', '@', $val), '@').'@';
								break;
							case 'event_sale' :
							case 'member_sale' :
							case 'free_delivery' :
							case 'dlv_alone' :
							case 'checkout' :
								if($val != 'Y') $val = 'N';
								break;
						}

						if($sql) $sql .= ', ';
						$val = addslashes(trim($val));
						$sql .= "`$key`='$val'";
					}

                    $ori_stat = $this->db->row("select stat from {$tbl['product']} where no='$pno'");
					if($this->db->query("update $tbl[product] set $sql where no='$pno'")) {
						$this->db->query("update $tbl[product] set stat='$req->stat' where wm_sc='$pno'");

                        if ($ori_stat != $req->stat) {
                            prdStatLogw($pno, $req->stat, $ori_stat, array(
                                'no' => $this->mng['no'],
                                'admin_id' => $this->mng['admin_id'].'(APP)'
                            ));
                        }

                        if (isset($req->ebig) == true) $this->setCategoryLink($pno, 2, $req->ebig);
                        if (isset($req->mbig) == true) $this->setCategoryLink($pno, 6, $req->mbig);

						$status = 'Y';
					} else {
						$message = '데이터 저장 중 오류가 발생하였습니다.';
					}
				} else {
					$message = '변경 내역이 없습니다.';
				}
			}

			$this->result($status, array(
				'message' => $message,
			));
		}

        /**
         * 기획전 카테고리 링크 정리
         **/
        public function setCategoryLink($pno, $ctype, $items)
        {
            global $tbl;

            $items = explode('@', trim($items, '@'));
            $items_n = implode(',', $items);

            // 빠진 카테고리 삭제
            $asql = ($items_n) ? " and nbig not in ($items_n)" : "";
            $res = $this->db->query("delete from {$tbl['product_link']} where ctype='$ctype' and pno='$pno' $asql");

            // 새로운 카테고리 추가
            foreach ($items as $cno) {
                $this->db->query("update {$tbl['product_link']} set sort_big=sort_big+1 where nbig='$cno'");
                $this->db->query("insert into {$tbl['product_link']} (ctype, nbig, pno, sort_big) values ('$ctype', '$cno', '$pno', '1')");
            }
        }


        // 상품별 옵션 정보 등록
        public function setProductOption() {
			global $tbl;

            $this->checkPermission('product', 'C0005');

            $pno = numberOnly($_REQUEST['pno']);
            $opno = numberOnly($_REQUEST['opno']);
            $name = addslashes(trim($_REQUEST['name']));
            $items = json_decode($_REQUEST['items']);
            $items_list = '';
            foreach ($items as $key => $item) {
                if ($items_list) $items_list .= '@';
                $items_list .= $item->name;
                if (empty($item->name)) $this->error(($key+1).'번째 옵션의 옵션명이 없습니다.');
            }
            if (empty($pno) ==  true) $this->error('상품번호를 입력해주세요.');
            if (empty($name) ==  true) $this->error('옵션명을 입력해주세요.');
            if (empty($opno) ==  true && count($items) == 0) $this->error('하위 옵션항목을 입력해주세요.');

            $prd = $this->db->assoc("select * from {$tbl['product']} where no='$pno'");
            if ($prd == false) {
                $this->error('존재하지 않는 상품번호입니다.');
            }
            if ($this->mng['level'] == 4) {
                if ($prd['partner_no'] != $this->mng['partner_no']) {
                    return $this->error(__lang_common_error_noperm__);
                }
            }

            // 옵션 등록
            if ($opno > 0) {
                $this->db->query("update {$tbl['product_option_set']} set name='$name' where no='$opno' and pno='$pno'");
            } else {
                $opno = $this->db->row("select max(no) from {$tbl['product_option_set']}")+1;
                $this->db->query("
                    insert into {$tbl['product_option_set']}
                        (no, name, necessary, otype, items, pno, stat, reg_date)
                        values ('$opno', '$name', 'Y', '2A', '$items_list', '$pno', '2', unix_timestamp(now()))
                ");
            }

            if (empty($opno)) {
                $this->error('옵션세트 정보를 찾을수 없습니다.');
            }

            // 옵션 아이템 등록
            foreach ($items as $val) {
                $item_no = numberOnly($val->no);
                $name = addslashes($val->name);
                $add_price = numberOnly($val->add_price);

                if ($item_no > 0) {
                    $this->db->query("update {$tbl['product_option_item']} set iname='$name', add_price='$add_price' where no='$item_no' and opno='$opno'");
                } else {
                    $sort = $this->db->row("select max(sort) from {$tbl['product_option_item']} where pno='$pno'");
                    $sort = (is_null($sort) == true) ? 1 : $sort+1;
                    $this->db->query("
                        insert into {$tbl['product_option_item']}
                            (pno, opno, iname, add_price, sort, reg_date)
                            values ('$pno', '$opno', '$name', '$add_price', '$sort', unix_timestamp(now()))
                    ");
                }
            }

			$this->productOption($opno);
        }

        // 옵션정보 삭제
        public function removeProductOption() {
            global $tbl;

            $this->checkPermission('product', 'C0005');

            $pno = numberOnly($_REQUEST['pno']);
            $opno = numberOnly($_REQUEST['opno']);
            $item_no = numberOnly($_REQUEST['item_no']);
            $deleted = 0;

            if ($pno < 1) $this->error('상품 번호를 입력해주세요.');
            if ($opno < 1) $this->error('옵션 세트 번호를 입력해주세요.');

            $prd = $this->db->assoc("select * from {$tbl['product']} where no='$pno'");
            if ($prd == false) {
                $this->error('존재하지 않는 상품번호입니다.');
            }
            if ($this->mng['level'] == 4) {
                if ($prd['partner_no'] != $this->mng['partner_no']) {
                    return $this->error(__lang_common_error_noperm__);
                }
            }

            if ($item_no > 0) {
                $this->db->query("delete from {$tbl['product_option_item']} where pno='$pno' and opno='$opno' and no='$item_no'");
                $deleted = $this->db->lastRowCount();
                if ($deleted > 0) {
                    $this->db->query("update erp_complex_option set del_yn='Y' where opts like '%#_{$item_no}#_%' ESCAPE '#'");
                } else {
                    $this->error('삭제할 옵션 아이템이 없습니다.');
                }
            } else if ($opno > 0) {
                $this->db->query("delete from {$tbl['product_option_set']} where pno='$pno' and no='$opno'");
                if($this->db->lastRowCount() > 0) {
                    $res = $this->db->query("select no from {$tbl['product_option_item']} where pno='$pno' and opno='$opno'");
                    foreach ($res as $data) {
                        $this->db->query("delete from {$tbl['product_option_item']} where pno='$pno' and opno='$opno' and no='{$data['no']}'");
                        if ($this->db->lastRowCount() > 0) {
                            $this->db->query("update erp_complex_option set del_yn='Y' where opts like '%#_{{$data['no']}}#_%' ESCAPE '#'");
                            $deleted += $deleted_one;
                        }
                    }
                } else {
                    $this->error('삭제할 옵션세트가 없습니다.');
                }
            }

            $this->result('OK', $deleted);
        }

        // 바코드재고 생성
        public function setProductStock() {
			global $tbl;

			if($this->checkhash() == false) {
				$this->error('처리 권한이 없습니다.');
			}

            if ($this->mng['level'] < 4) {
                if (
                    $this->checkPermission('erp', 'E0003', null) == false &&
                    $this->checkPermission('erp', 'E0004', null) == false &&
                    $this->checkPermission('erp', 'E0005', null) == false &&
                    $this->checkPermission('erp', 'E0024', null) == false
                ) return $this->error('permission_denied');
            }

            $pno = numberOnly($_REQUEST['pno']);
            $complex_no = numberOnly($_REQUEST['complex_no']);
            $items_no = addslashes($_REQUEST['items_no']);
            $soldout_type = $_REQUEST['soldout_type'];
            $qty = numberOnly($_REQUEST['qty']);

            // 상품 체크
            $prd = $this->db->assoc("select * from {$tbl['product']} where no='$pno'");
            if ($prd == false) {
                $this->error('존재하지 않는 상품번호입니다.');
            }
            if ($this->mng['level'] == 4) {
                if ($prd['partner_no'] != $this->mng['partner_no']) {
                    return $this->error(__lang_common_error_noperm__);
                }
            }

            // 옵션 체크
            if (!$complex_no) {
                $opts = ($items_no) ? explode(',', $items_no) : array();
                $complex_key = makeComplexKey($opts);
                $option_ea = $this->db->row("select count(*) from {$tbl['product_option_set']} where pno='$pno' and necessary='Y'");
                if (count($opts) != $option_ea) {
                    $this->error('옵션 갯수가 일치하지 않습니다.');
                }
                if (count($opts) > 0 && count($opts) != $this->db->row("select count(*) from {$tbl['product_option_item']} where pno='$pno' and no in ($items_no)")) {
                    $this->error('옵션번호 데이터가 부정확합니다. 없는 옵션번호이거나 다른 상품의 옵션번호입니다.');
                }

                // 재고 입력상태 확인
                $erp = $this->db->assoc("
                    select complex_no, qty, force_soldout from erp_complex_option
                        where pno='$pno' and opts='$complex_key' and del_yn='N'
                ");
            } else {
                // 재고 입력상태 확인
                $erp = $this->db->assoc("
                    select complex_no, qty, force_soldout from erp_complex_option
                        where pno='$pno' and complex_no='$complex_no' and del_yn='N'
                ");
            }

            // 품절타입 체크
            if (empty($soldout_type) == true) {
                $soldout_type = $erp['force_soldout'];
            }
            if (in_array($soldout_type, array('Y', 'N', 'L')) == false) {
                $this->error('품절 타입 값이 정확하지 않습니다.(Y/N/L)');
            }

            // 수량 입력 체크
            if (strlen($qty) == 0) {
                $this->error('변경할 재고 수량을 입력해주세요.');
            }

            // 상품을 '재고 사용함'으로 변경
            $this->db->query("update {$tbl['product']} set ea_type=1 where no='$pno'");

            if ($erp['complex_no'] > 0) { // 재고 수정
                $complex_no = $erp['complex_no'];
                $qty_gap = ($qty-$erp['qty']);
                $kind = ($qty_gap > 0) ? 'U' : 'O';
                $remote_ip = $_SERVER['REMOTE_ADDR'];
                if ($qty_gap != 0) {
                    $qty_gap = abs($qty_gap);
                    $this->db->query("
                        insert into erp_inout (complex_no, inout_kind, qty, remark, reg_user, reg_date, remote_ip)
                        values ('$complex_no', '$kind', '$qty_gap', 'API 수정', '', now(), '$remote_ip')
                    ");
                }
                $this->db->query("
                    update erp_complex_option
                        set qty=curr_stock(complex_no), force_soldout='$soldout_type'
                        where complex_no='$complex_no'
                ");
            } else { // 신규 등록
                $complex_no = createComplex(
                    $pno,
                    $opts,
                    null,
                    $qty,
                    'API 생성',
                    $soldout_type
                );
            }

			$this->result('OK', array(
                'complex_no' => $complex_no,
                'barcode' => $this->db->row("select barcode from erp_complex_option where complex_no='$complex_no'")
            ));
        }


        // 배송비 정보 출력
        public function deliverySet() {
            global $tbl, $cfg, $_delivery_types;

            $no = numberOnly($_REQUEST['set_no']);
            if ($no > 0) { // 개별 배송비
                $data = $this->db->assoc("select * from {$tbl['product_delivery_set']} where no='$no'");
                $data['delivery_free_limit'] = json_decode($data['delivery_free_limit']);
            } else { // 기본 배송비
                switch($cfg['delivery_type']) {
                    case '1' : // 무료 배송
                        $datas = array(array(0, 0, 0));
                        break;
                    case '2' : // 착불 배송
                        $datas = array(array(0, 0, $cfg['dlv_fee2']));
                        break;
                    case '3' : // 금액별 배송
                        $datas = array(
                            array(0, $cfg['delivery_free_limit'], $cfg['delivery_fee']),
                            array($cfg['delivery_free_limit'], $cfg['delivery_free_limit'], 0)
                        );
                        break;
                }

    			$data = array(
                    'set_name' => '기본 배송비',
                    'delivery_type' => $cfg['delivery_type'],
                    'delivery_type_str' => $_delivery_types[$cfg['delivery_type']],
                    'delivery_base' => $cfg['delivery_base'],
                    'delivery_loop_type' => 'N',
                    'delivery_free_limit' => $datas,
                    'free_delivery_area' => $cfg['free_delivery_area'],
                    'free_yn' => 'Y'
                );
            }

           $this->result('OK', array(
                'name' => $data['set_name'],
                'type' => $data['delivery_type'],
                'type_str' => $_delivery_types[$data['delivery_type']],
                'base' => ($data['delivery_base'] == 1) ? 'total' : 'pay',
                'loop_type' => $data['delivery_loop_type'],
                'datas' => $data['delivery_free_limit'],
                'free_area' => $data['free_delivery_area'],
                'free_yn' => $data['free_yn']
            ));
        }

        public function deliverySetList() {
            global $tbl, $scfg;

			if($this->checkhash() == false) {
				$this->error('처리 권한이 없습니다.');
			}

            if ($this->checkPermission('config', 'C0281') == false) {
                return $this->error(__lang_common_error_noperm__);
            }

            $pw = '';
            if ($scfg->comp('use_partner_shop', 'Y') == true && $scfg->comp('use_partner_delivery', 'Y') == true) {
                if ($this->mng['level'] == 4) {
                    $partner_no = (int) $this->mng['partner_no'];
                    $pw .= " and partner_no='$partner_no'";
                } else {
                    $pw .= " and partner_no=0";
                }
            }
            $res = $this->db->query("select no, set_name, delivery_type from {$tbl['product_delivery_set']} where 1 $pw order by no asc");
            $this->result('Y', array(
                'datas' => $res->fetchAll(PDO::FETCH_ASSOC)
            ));
        }


		// 메모 등록/수정
		public function memoUpdate() {
			global $tbl, $connect;

			if($this->checkhash() == false) {
				$this->error('처리 권한이 없습니다.');
			}
			$mng = $this->getAdmin();

			$no = numberOnly($_REQUEST['no']);
			$target = addslashes(trim($_REQUEST['target']));
			$content = addslashes(trim($_REQUEST['content']));
			$now = time();

			if(!$content) $this->error('메모 내용을 입력해주세요.');

			if($no > 0) { // 수정
				// 메모 존재여부 확인
				$memo = $this->db->assoc("select no, admin_id from $tbl[order_memo] where no='$no'");
				if(!$memo['no']) $this->error('메모코드가 정확하지 않습니다.');

				// 처리 권한 확인
				if($mng['level'] > 2 && $memo['admin_id'] != $mng['admin_id']) {
					$this->error('처리 권한이 없는 메모입니다.');
				}

				$r = $this->db->query("update $tbl[order_memo] set content='$content' where no='$no'");
			} else { // 등록
				switch($_REQUEST['type']) {
					case 'order' :
						$type = 1;
						$ord = $this->db->assoc("select ono from $tbl[order] where ono='$target'");
						if(!$ord['ono']) $this->error('존재하지 않는 주문번호입니다.');
                        if ($this->mng['level'] == 4) {
                            $cnt = $this->db->row("select count(*) from {$tbl['order_product']} where ono=? and partner_no=?", array(
                                $target, $this->mng['partner_no']
                            ));
                            if ($cnt == 0) {
                                return $this->error(__lang_common_error_noperm__);
                            }
                        }
					break;
					case 'member' :
                        if ($this->mng['level'] == 4) {
                            return $this->error(__lang_common_error_noperm__);
                        }
						$type = 2;
						$mem = $this->db->assoc("select no from $tbl[member] where member_id='$target'");
						if(!$mem['no']) $this->error('존재하지 않는 회원아이디입니다.');
					break;
					case 'product' :
						$type = 3;
						if(!$target) $this->error('상품번호를 입력해주세요.');
						$prd = $this->db->assoc("select * from $tbl[product] where no='$target'");
						if(!$prd['no']) $this->error('존재하지 않는 상품코드입니다.');
                        if ($this->mng['level'] == 4) {
                            if ($prd['partner_no'] != $this->mng['partner_no']) {
                                return $this->error(__lang_common_error_noperm__);
                            }
                        }
					break;
				}
				if(!$type) {
					$this->error('메모종류를 선택해주세요.');
				}

				addField($tbl['order_memo'], 'importance', 'char(1) not null default "1"');
				$r = $this->db->query("insert into $tbl[order_memo] (admin_no, admin_id, ono, content, type, importance, reg_date) values ('$mng[no]', '$mng[admin_id]', '$target', '$content', '$type', '1', '$now')", $connect);
				$no = $this->db->insert_id;
			}

			$result = ($r == true) ? 'Y' : 'N';
			$this->result($result, array(
				'no' => $no
			));
		}

		// 메모 삭제
		public function memoDelete() {
			global $tbl, $connect;

			if($this->checkhash() == false) {
				$this->error('처리 권한이 없습니다.');
			}
			$mng = $this->getAdmin();

			$no = numberOnly($_REQUEST['no']);
			if(!$no) $this->error('처리할 메모 코드를 입력해주세요.');

			// 메모 존재여부 확인
			$memo = $this->db->assoc("select no, admin_id from $tbl[order_memo] where no='$no'");
			if(!$memo['no']) $this->error('메모코드가 정확하지 않습니다.');

			// 처리 권한 확인
			if($mng['level'] > 2 && $memo['admin_id'] != $mng['admin_id']) {
				$this->error('처리 권한이 없는 메모입니다.');
			}

			$r = $this->db->query("delete from $tbl[order_memo] where no='$no'");

			$result = ($r == true) ? 'Y' : 'N';
			$this->result($result, array(
				'no' => $no
			));
		}


		// 주문리스트
		public function orderList() {
			global $tbl, $cfg, $_order_sales;

            $is_mng = $this->getAdmin();
            $is_mem = $this->checkMhash();

            if ($is_mng == false && $is_mem == false) {
                exit('permission_denied');
            }

            $w = $w2 = $afield = '';
            $sdate1 = trim($_REQUEST['sdate1']);
            $sdate2 = trim($_REQUEST['sdate2']);
			$sortOrder = ($_REQUEST['sortOrder'] == 'asc') ? "asc" : "desc";
            if ($sdate1 && $sdate2) {
                $sdate1 = strtotime($sdate1);
                $sdate2 = strtotime($sdate2.' 23:59:59');
                $w .= " and o.date1 between $sdate1 and $sdate2";
            } else {
                $sdate = strtotime('-3 months', strtotime(date('Y-m-d 00:00:00')));
                $w .= " and o.date1 >= $sdate";
            }

			$search_str = addslashes(trim($_REQUEST['search_str']));
			if($search_str) {
				$search_str = urldecode($search_str);
				$w .= " and (o.ono like '%$search_str%' or o.buyer_name like '%$search_str%')";
			}

			$stat = $_REQUEST['stat'];
			if($stat) {
                $stat = explode(',', $stat);
                $_tmp1 = $_tmp2 = '';
                foreach ($stat as $_stat) {
                    $_tmp1 .= " or o.stat2 like '%@$_stat@%'";
                    $_tmp2 .= " or o.stat='$stat'";
                }
				$w .= " and (".substr($_tmp1, 3).")";
                $w2 .= " and (".substr($_tmp2, 3).")";
			}

			$member_id = addslashes(trim($_REQUEST['member_id']));
			if($member_id) {
				$w .= " and o.member_id='$member_id'";
			}

            // 판매채널 검색
            switch ($_REQUEST['channel']) {
                case 'SELF' :
                    $w .= " and o.checkout!='Y'";
                    if ($cfg['use_kakaoTalkStore'] == 'Y') $w .= " and o.talkstore='N'";
                    if ($cfg['n_smart_store'] == 'Y') $w .= " and o.smartstore='N'";
            		if ($cfg['use_talkpay'] == 'Y') $w .= " and o.external_order=''";
                    break;
                case 'NP' :
                    $w .= " and o.checkout='Y'";
                    break;
                case 'TS' :
                    $w .= " and o.talkstore='Y'";
                    break;
                case 'SS' :
                    if ($cfg['n_smart_store'] == 'Y') {
                        $w .= " and o.smartstore='Y'";
                    }
                    break;
                case 'TB' :
                    if ($cfg['use_talkpay'] == 'Y') {
                        $w .= " and o.external_order='talkpay'";
                    }
                    break;
            }
            if ($cfg['use_kakaoTalkStore'] == 'Y') $afield .= ", o.talkstore";
            if ($cfg['n_smart_store'] == 'Y') $afield .= ", o.smartstore";
            if ($cfg['use_talkpay'] == 'Y') $afield .= ", o.external_order";

            // 오프라인 매장 주문 제외
            if ($cfg['use_erp_interface'] == 'Y' && $cfg['erp_interface_name'] == 'dooson') {
                $w .= " and o.x_order_id!='OFF'";
            }

            // 입점사 검색
            $pw = '';
            if ($this->mng['level'] == 4) {
                $pw .= " and op.partner_no='".(int) $this->mng['partner_no']."'";
            } else if ($is_mem == true) { // 로그인 회원 검색
                $pw .= " and o.member_id='{$this->member['member_id']}'";
            }

            // 정렬 조건
            if (isset($_GET['order_by']) == true) {
                $order_by = (int) $_GET['order_by'];
            }
            if ($order_by > 5 || $order_by < 1) {
                $order_by = 1;
            }

			// 페이징
			list($limit, $page, $page_size) = $this->getPageLimit();

			$img_url = getFileDir('_data/product');
			$order_list = array();
			$res = $this->db->iterator("
                select
                    distinct o.ono,
                    o.date1, o.pay_type, o.member_id, o.stat, o.stat2, o.pay_prc, o.checkout, o.ip,
                    o.buyer_name, o.buyer_cell, o.buyer_phone, buyer_email,
                    o.addressee_name, o.addressee_cell, o.addressee_phone, o.addressee_zip, o.addressee_addr1, o.addressee_addr2
                    $afield
                from
                $tbl[order] o inner join {$tbl['order_product']} op using(ono)
                where o.stat not in (11,31) $w $pw
                group by ono
                order by o.date{$order_by} $sortOrder $limit
            ");
            foreach ($res as $data) {
                $products = array();
                $title = '';
                $bfield = ', ' . getOrderSalesField('a');
                $pres = $this->db->iterator("
                    select
                        a.no, a.pno, a.buy_ea, a.r_zip, a.r_addr1, a.r_addr2, a.dlv_no, a.dlv_code,
                        a.total_prc, a.stat,
                        b.name, b.updir, b.upfile3, b.hash $bfield
                    from {$tbl['order_product']} a
                        inner join $tbl[product] b on a.pno=b.no
                    where
                        a.ono='{$data['ono']}'
                ");
                foreach ($pres as $pdata) {
                    $discount = array();
                    foreach ($_order_sales as $sk => $sn) {
                        if ($pdata[$sk] > 0) {
                            $discount[$sn] = parsePrice($pdata[$sk]);
                        }
                    }
                    $products[] = array(
                        'order_product_no' => $pdata['no'],
                        'pno' => $pdata['pno'],
                        'name' => $pdata['name'],
                        'buy_ea' => $pdata['buy_ea'],
                        'total_prc' => parsePrice($pdata['total_prc']),
                        'sale_prc' => getOrderTotalSalePrc($pdata),
                        'discount' => $discount,
                        'photo' => $img_url.'/'.$pdata['updir'].'/'.$pdata['upfile3'],
                        'r_zip' => $pdata['r_zip'],
                        'r_addr1' => $pdata['r_addr1'],
                        'r_addr2' => $pdata['r_addr2'],
                        'dlv_no' => $pdata['dlv_no'],
                        'dlv_code' => $pdata['dlv_code']
                    );

                    if ($title) $title .= ' / ';
                    $title .= $pdata['name'];
                }

				$stat2 = '';
				$_tmp = explode('@', trim($data['stat2'], '@'));
				foreach($_tmp as $val) {
					if($stat2) $stat2 .= ',';
					$stat2 .= $this->getOrdStat($val);
				}

				$data = array_map('stripslashes', $data);
				$order_list[] = array(
					'ono' => $data['ono'],
					'title' => $title,
					'date1' => $this->getDateStr($data['date1']),
					'pay_type' => $this->getOrdPayType($data['pay_type']),
					'buyer_name' => $data['buyer_name'],
					'buyer_cell' => $data['buyer_cell'],
					'buyer_phone' => $data['buyer_phone'],
					'buyer_email' => $data['buyer_email'],
					'addressee_name' => $data['addressee_name'],
					'addressee_phone' => $data['addressee_phone'],
					'addressee_cell' => $data['addressee_cell'],
                    'addressee_zip' => $data['addressee_zip'],
					'addressee_addr1' => $data['addressee_addr1'],
					'addressee_addr2' => $data['addressee_addr2'],
					'member_id' => $data['member_id'],
					'stat' => $this->getOrdStat($data['stat']),
					'stat2' => $stat2,
					'pay_prc' => parsePrice($data['pay_prc']),
					'photo' => $img_url.'/'.$pdata['updir'].'/'.$pdata['upfile3'],
                    'channel' => $this->parseChannel($data),
                    'ip' => $data['ip'],
                    'products' => $products
				);
			}

			if(count($order_list)) {
				$this->result('Y', array(
					'total_rows' => $this->db->row("select count(*) from $tbl[order] where stat not in (11, 31)"),
					'rows' => $this->db->row("select count(*) from $tbl[order] o where stat not in (11, 31) $w"),
					'orders' => $order_list,
				));
			} else {
				$this->error('검색된 주문이 없습니다.');
			}
		}

		// 주문상세
		public function order() {
			global $tbl, $root_dir, $_ord_add_info, $_order_sales, $_order_stat;

            $is_mng = $this->checkhash();
            $is_mem = $this->checkMhash();

			if(
                ($this->checkhash() == true && $this->checkPermission('order', 'C0021') == false) ||
                ($is_mng == false && $is_mem == false)
            ) {
				return $this->error(__lang_common_error_noperm__);
			}

			$ono = addslashes(trim($_REQUEST['ono']));

			$data = $this->db->assoc("select * from $tbl[order] where ono='$ono' and stat not in (11, 31)");
			if(!$data) {
				$this->error('조회된 주문이 없습니다.');
			}
			$data = array_map('stripslashes', $data);

            // 입점사 체크
            if ($this->mng['level'] == 4) {
                $my_rows = $this->db->row("select count(*) from {$tbl['order_product']} where ono=? and partner_no=?", array(
                    $ono, $this->mng['partner_no']
                ));
                if ($my_rows == 0) return $this->error(__lang_common_error_noperm__);
            }

            // 회원 체크
            if ($is_mem == true) {
                if ($data['member_id'] != $this->member['member_id'] || $data['member_no'] != $this->member['no']) {
                    return $this->error(__lang_common_error_noperm__);
                }
            }

			// 주문 상품
			$img_url = getFileDir('_data/product');
			$order_product_list = array();
			$res = $this->db->iterator("select o.*, o.no as opno, p.updir, p.upfile3, p.code, o.stat from $tbl[order_product] o left join $tbl[product] p on o.pno=p.no where o.ono='$ono'");
            foreach ($res as $pdata) {
				// 할인가격 계산 (차후 업데이트 대비)
				$sale_prc = 0;
				foreach($pdata as $key => $val) {
					if(preg_match('/^sale[0-9]+$/', $key)) {
						$sale_prc += $val;
					}
				}

				$discount = array();
				foreach($_order_sales as $key => $val) {
					if($pdata[$key] > 0) {
						$discount[$val] = parsePrice($pdata[$key]);
					}
				}

                // 입점사 검색
                $pw = '';
                settype($pdata['partner_no'], 'integer');
                if ($this->mng['level'] == 4 && $pdata['partner_no'] != $this->mng['partner_no']) {
                    $order_product_list[] = array(
                        'order_product_no' => $pdata['opno'],
                        'name' => '타사상품',
                        'stat' => $pdata['stat'],
                    );
                } else {
                    $order_product_list[] = array(
                        'order_product_no' => $pdata['opno'],
                        'pno' => $pdata['pno'],
                        'code' => $pdata['code'],
                        'name' => stripslashes($pdata['name']),
                        'option' => stripslashes($pdata['option']),
                        'photo' => ($pdata['upfile3'] ? $img_url.'/'.$pdata['updir'].'/'.$pdata['upfile3'] : ''),
                        'buy_ea' => $pdata['buy_ea'],
                        'total_prc' => parsePrice($pdata['total_prc']),
                        'sale_prc' => parsePrice($sale_prc),
                        'total_milage' => parsePrice($pdata['total_milage']),
                        'dlv_no' => $this->getDlvName($pdata['dlv_no']),
                        'dlv_code' => $pdata['dlv_code'],
                        'stat' => $pdata['stat'],
                        'stat_str' => $_order_stat[$pdata['stat']],
                        'discount' => $discount,
                        'complex_no' => $pdata['complex_no'],
                        'partner_no' => $pdata['partner_no'],
                        'partner_name' => $this->getPartnerName((int) $pdata['partner_no']),
                        'dlv_type' => ($pdata['partner_no'] == 0 || $pdata['dlv_type'] == '1') ? 'mall' : 'partner'
                    );
                }
			}

			// 주문 로그
			$logs = array();
			$res = $this->db->iterator("select stat, ori_stat, reg_date from $tbl[order_stat_log] where ono='$ono' and stat < 100 order by no asc");
            foreach ($res as $logdata) {
				$summary = '';
				if($logdata['ori_stat'] > 0) {
					$summary = $this->getOrdStat($logdata['ori_stat']).'에서 ';
				}
				$summary .= $this->getOrdStat($logdata['stat']).'으로 변경';

				$logs[] = array(
					'date' => $this->getDateStr($logdata['reg_date']),
					'summary' => $summary,
				);
			}

			// 추가항목
			$add_fields = array();
			if(file_exists($root_dir."/_config/order.php")){
				include_once $root_dir."/_config/order.php";
				if(is_array($_ord_add_info)) {
					foreach($_ord_add_info as $key => $val) {
						$add_fields[] = array(
							'name' => $val['name'],
							'value' => orderAddFrm($key, 0, $data)
						);
					}
				}
			}

			// 카드정보
			$card_tbl = ($data['pay_type'] == 4) ? $tbl['vbank'] : $tbl['card'];
			$card = $this->db->assoc("select * from $card_tbl where wm_ono='$ono'");
			if($data['pay_type'] == 4) {
				$card_info = stripslashes($card['bank_name'].' '.$card['account'].' '.$card['depositor']);
			} else {
				$card_info = stripslashes($card['card_name'].' '.$card['quota']);
			}

			// 현금영수증
			$cash = $this->db->assoc("select stat from $tbl[cash_receipt] where ono='$ono' order by no desc limit 1");
			$cash_receipt = $this->getCashReceiptStat($cash['stat']);

			// 관리자 메모
			$memos = array();
            if ($this->checkPermission('order', 'C0183', 'return') == true) {
                $memo_sql = '';
                if ($this->mng['level'] == 4) {
                    $memo_sql = " and admin_no='{$this->mng['no']}' and admin_id='{$this->mng['admin_id']}'";
                }
                $mres = $this->db->iterator("select * from $tbl[order_memo] where type=1 and ono='$ono' $memo_sql");
                foreach ($mres as $mdata) {
                    $attachs = $this->db->iterator("select updir, filename from {$tbl['neko']} where neko_gr=? and neko_id=?", array(
                        'memo1', 'memo_1_'.$mdata['no']
                    ));
                    $attach = array();
                    foreach ($attachs as $file) {
                        $attach[] = getListImgURL($file['updir'], $file['filename']);
                    }

                    $memos[] = array(
                        'no' => $mdata['no'],
                        'admin_id' => $mdata['admin_id'],
                        'content' => stripslashes($mdata['content']),
                        'reg_date' => $this->getDateStr($mdata['reg_date']),
                        'attach' => $attach
                    );
                }
            }

            // 할인 정보
            $discount = array();
            foreach($_order_sales as $key => $val) {
                if($data[$key] > 0) {
                    $discount[$val] = parsePrice($data[$key]);
                }
            }
            if ($data['sale5'] > 0 || $data['sale7'] > 0) {
                $coupon = $this->db->assoc("select cno, name from {$tbl['coupon_download']} where ono='{$data['ono']}'");
            }

			$overseas_addressee = '';
			if (isset($data['addressee_addr3']) == true) {
				$overseas_addressee .= ' '.$data['addressee_addr3'];
			}
			if (isset($data['addressee_addr4']) == true) {
				$overseas_addressee .= ' '.$data['addressee_addr4'];
			}

			$this->result('Y', array(
				'ono' => $data['ono'],
				'products' => $order_product_list,
				'date1' => $this->getDateStr($data['date1']),
				'date5' => $this->getDateStr($data['date5']),
				'pay_type' => $this->getOrdPayType($data['pay_type']),
				'bank' => $data['bank'],
				'bank_name' => $data['bank_name'],
				'pay_prc' => parsePrice($data['pay_prc']),
				'dlv_prc' => parsePrice($data['dlv_prc']),
				'milage_prc' => parsePrice($data['milage_prc']),
				'emoney_prc' => parsePrice($data['emoney_prc']),
				'total_milage' => parsePrice($data['total_milage']),
				'card_info' => $card_info,
				'cash_receipt' => $cash_receipt,
				'stat' => $this->getOrdStat($data['stat']),
				'date5' => $this->getDateStr($data['date5']),
				'stat_log' => $logs,
				'buyer_name' => $data['buyer_name'],
				'member_group' => $this->getMemberGroup($data['member_id']),
				'member_no' => $data['member_no'],
				'member_id' => $data['member_id'],
				'buyer_phone' => $data['buyer_phone'],
				'buyer_cell' => $data['buyer_cell'],
				'buyer_email' => $data['buyer_email'],
				'addr_name' => $data['addressee_name'],
                'addr_zip' => $data['addressee_zip'],
				'addr_addr' => trim($data['addressee_addr1'].' '.$data['addressee_addr2'].$overseas_addressee),
				'addr_phone' => $data['addressee_phone'],
				'addr_cell' => $data['addressee_cell'],
				'addr_msg' => $data['dlv_memo'],
				'add_fields' => $add_fields,
				'mng_memo' => $memos,
                'discount' => $discount,
                'coupon' => $coupon,
                'channel' => $this->parseChannel($data),
                'ip' => $data['ip'],
				'dlv_code' => $data['dlv_code'],
				'dlv_no' => $data['dlv_no'],
				'dlv_name' => $this->getDlvName($data['dlv_no'])
			));
		}

        private function parseChannel($data)
        {
            if ($data['checkout'] == 'Y') return array(
                'code' => 'NP',
                'name' => '네이버페이',
            );

            if ($data['smartstore'] == 'Y') return array(
                'code' => 'SS',
                'name' => '스마트스토어',
            );

            if ($data['talkstore'] == 'Y') return array(
                'code' => 'TS',
                'name' => '카카오톡스토어',
            );

            if ($data['external_order'] == 'talkpay') return array(
                'code' => 'TB',
                'name' => '카카오페이구매',
            );
        }

		public function setOrderStat() {
			global $tbl, $cfg, $scfg, $engine_dir, $admin, $pdo, $wec,
				   $exec, $repay_no, $pno, $email_checked, $sms_replace, $_order_color_def,
                   $data, $ext, $asql;

			if($this->checkhash() == false) {
				$this->error('관리자 로그인 오류');
			}

			$this->checkPermission('order', 'C0021');
            $admin = $this->mng;

			$ono = addslashes(trim($_REQUEST['ono']));
			$stat = numberOnly($_REQUEST['stat']);

			if(!$ono) $this->error('주문번호를 입력해주세요.');
			if(!$stat || $stat == 11) $this->error('변경할 상태를 입력해주세요.');

            $pasql = '';
            if ($_REQUEST['order_product_no']) {
                $order_product_no = trim($_REQUEST['order_product_no'], ',');
                if (empty($order_product_no) == true || preg_match('/[^0-9,]/', $order_product_no) == true) {
                    $this->error('order_product_no 오류');
                }
                $pasql .= " and no in ($order_product_no)";
            }
            if ($this->mng['level'] == 4) {
                $partner_no = (int) $this->mng['partner_no'];
                $pasql .= " and partner_no='$partner_no'";
            }
            $pnos = $this->db->row("select group_concat(no) from $tbl[order_product] where ono='$ono' $pasql");
            if (is_null($pnos) == true) {
                return $this->error(__lang_common_error_noperm__);
            }
			$pnos = explode(',', $pnos);

			$cache_goods = true;
			include $engine_dir.'/_manage/manage.lib.php';
			$_REQUEST['from_ajax'] = 'true';
			if($stat > 10) {
				$GLOBALS['sso'] = $GLOBALS['ssldata'] = 'tmp';
                $_POST['ono'] = $ono;
				$_POST['exec'] = $exec = 'process';
				$_POST['repay_no'] = $repay_no = $pnos;
				$_POST['pno'] = $pno = $pnos;
				$_POST['stat'] = $stat;
				$_POST['reason'] = 'openAPI';
				include $engine_dir.'/_manage/order/order_prd_stat.exe.php';
			} else {
                $_POST['ono'] = $ono;
				$_POST['exec'] = 'process';
				$_POST['pno'] = $pnos;
				$_POST['stat'] = $stat;
				include $engine_dir.'/_manage/order/order_prd_dlv.exe.php';
			}

			$this->result('Y', '주문상태가 변경되었습니다.');
		}

        public function setDelivery() {
            global $tbl, $engine_dir, $root_dir, $scfg, $smartstore, $sms_case_admin;

			if($this->checkhash() == false) {
				$this->error('관리자 로그인 오류');
			}

            include __ENGINE_DIR__.'/_manage/delivery.lib.php';

            $ono = addslashes($_REQUEST['ono']);
            $order_product_no = (isset($_REQUEST['order_product_no']) == true) ? preg_replace('/[^0-9,]/', '', $_REQUEST['order_product_no']) : '';
            $provider = $_REQUEST['provider'];
            $dlv_code = $_REQUEST['dlv_code'];
            $dlv_date = (isset($_REQUEST['dlv_date']) == true) ? $_REQUEST['dlv_date'] : '';

            // 입점사 검색 조건
            $pw = '';
            if ($this->mng['level'] == 4) {
                $partner_no = (int) $this->mng['partner_no'];
                $pw .= " and partner_no='$partner_no'";
            }

            // 택배사 입점코드 체크
            if ($scfg->comp('use_partner_shop', 'Y') == true && $scfg->comp('use_partner_delivery', 'Y') == true) {
                $delivery_info = $this->db->assoc("select * from {$tbl['delivery_url']} where name=? $pw ", array($provider));
                $provider = $delivery_info['no'];
                if ($delivery_info['partner_no'] != $this->mng['partner_no']) {
                    return $this->error('택배사 코드 오류');
                }
            }

            $return = orderDelivery(
                4,
                $ono,
                $order_product_no,
                $provider,
                $dlv_code,
                $dlv_date,
                " and stat in (1, 2, 3) $pw"
            );

            return $this->result(
                ($return == 'OK') ? 'Y' : 'N',
                $return

            );
        }

        /**
         * 판매채널 목록
         **/
        public function salesChannels()
        {
            global $cfg;

			if($this->checkhash() == false) {
				$this->error('관리자 로그인 오류');
			}
            exit(json_encode(array(
                'status' => 'Y',
                'channels' => array(
                    'checkout' => ($cfg['checkout_id']) ? 'Y' : 'N',
                    'talkstore' => ($cfg['use_kakaoTalkStore'] == 'Y') ? 'Y' : 'N',
                    'smartstore' => ($cfg['n_smart_store'] == 'Y') ? 'Y' : 'N',
                    'kakaopaybuy' => ($cfg['use_talkpay'] == 'Y') ? 'Y' : 'N',
                )
            )));

        }

        /**
         * 주문서 수동 생성
         **/
        function createOrder()
        {
            global $engine_dir, $tbl, $member, $_order_sales, $_order_color_def;

            $this->checkPermission('order', 'C0182');

            include_once __ENGINE_DIR__.'/_manage/manage.lib.php';

            // 회원
            if ($_REQUEST['member_id']) {
                $member = $this->db->assoc(
                    "select no, level, member_id, milage, emoney from {$tbl['member']} where member_id=?",
                    array($_REQUEST['member_id'])
                );
            }
            if (empty($_REQUEST['buyer_name']) == true) {
                $this->error('주문자명을 입력해주세요.');
            }

            // 사용 적립금, 예치금
            $milage_prc = (isset($_REQUEST['milage_prc']) == true) ? (int) $_REQUEST['milage_prc'] : 0;
            $emoney_prc = (isset($_REQUEST['emoney_prc']) == true) ? (int) $_REQUEST['emoney_prc'] : 0;
            if ($milage_prc > 0 && $milage_prc > $member['milage']) {
                $this->error('보유 적립금이 부족합니다.');
            }
            if ($emoney_prc > 0 && $emoney_prc > $member['emoney']) {
                $this->error('보유 예치금이 부족합니다.');
            }

            // 주문 기본 정보
            $ono = makeOrdNo();
            $s_order_id = (isset($_REQUEST['s_order_id']) == true) ? $_REQUEST['s_order_id'] : '__created__';
            $pay_type = (isset($_REQUEST['pay_type']) == true) ? $_REQUEST['pay_type'] : 1;

            // 장바구니 생성
            $order_products = json_decode($_REQUEST['order_products'], true);
            $orderCart = new OrderCart();
            if (isset($_REQUEST['skip_dlv']) == true) {
                $orderCart->skip_dlv = $_REQUEST['skip_dlv'];
            }
            if ($_REQUEST['cno']) { // 쿠폰 사용
                $cpn = $this->db->assoc("
                    select d.*, c.attachtype,c.attach_items
                    from {$tbl['coupon_download']} as d inner join {$tbl['coupon']} as c on d.cno=c.no
                    where d.member_no='{$member['no']}' and d.ono='' and d.no=?
                ", array($_REQUEST['cno']));
                $orderCart->setCoupon($cpn);
            }
            foreach ($order_products as $cno => $cart) {
                $prd = $this->db->assoc("select * from {$tbl['product']} where no=?", array($cart['pno']));
                if ($prd == false) {
                    $this->error("[{$cart['pno']}] 존재하지 않는 상품코드입니다.");
                }

                $complex = $this->db->assoc("select opts from erp_complex_option where complex_no=?", array($cart['complex_no']));
                if ($complex == false) {
                    $this->error("[{$cart['complex_no']}] 존재하지 않는 재고코드입니다.");
                }

                // 옵션 추가 금액
                $_add_price = 0;
                if ($complex['opts']) {
                    $opts = str_replace('_', ', ', trim($complex['opts'], '_'));
                    $_add_price = $this->db->row("select sum(add_price) from {$tbl['product_option_item']} where no in ($opts)");
                    $prd['sell_prc'] += $_add_price;
                }

                $cart = array_merge($cart, $prd);
                $cart['cno'] = $cno;
                $cart['option'] = getComplexOptionName($complex['opts']);
                $cart['option_prc'] = $_add_price;

                $orderCart->addCart($cart);
            }
            $orderCart->complete();

            // 주문 상품 저장
            while($obj = $orderCart->loopCart()) {
                $data = $obj->data;
                $values = array(
                    'ono' => $ono,
                    'pno' => $data['pno'],
                    'name' => $data['name'],
                    'sell_prc' => (int) $data['sell_prc'],
                    'milage' => (int) $data['milage'],
                    'buy_ea' => (int) $data['buy_ea'],
                    'total_prc' => $obj->getData('sum_prd_prc'),
                    'total_milage' => (int) $cart['total_milage'],
                    'member_milage' => (int) $cart['member_milage'],
                    'event_milage' => (int) $cart['event_milage'],
                    'option' => $data['option'],
                    'option_prc' => (int) $data['option_prc'],
                    'complex_no' => (int) $data['complex_no'],
                    'stat' => 11
                );
                $asql1 = $asql2 = '';
                foreach ($_order_sales as $key => $val) {
                    $__sale = (int) $obj->getData($key);
                    $asql1 .= ", {$key}";
                    $asql2 .= ", '$__sale'";
                }

                $this->db->query("
                    insert into {$tbl['order_product']}
                      (
                        ono, pno, name, sell_prc, milage, buy_ea, total_prc, total_milage, member_milage, event_milage,
                        `option`, option_prc, complex_no, stat
                        $asql1
                        )
                      values
                      (
                        :ono, :pno, :name, :sell_prc, :milage, :buy_ea, :total_prc, :total_milage, :member_milage, :event_milage,
                        :option, :option_prc, :complex_no, :stat
                        $asql2
                      )
                ", $values);
            }

            // 주문서 저장
            $title = makeOrderTitle($ono);
            $total_prc = $orderCart->getData('total_order_price');
            $pay_prc = $orderCart->getData('pay_prc');
        	$prd_prc = $orderCart->getData('sum_prd_prc');
        	$dlv_prc = $orderCart->getData('dlv_prc');
            $ord_fd = array(
                'buyer_name', 'buyer_email', 'buyer_phone', 'buyer_cell',
                'addressee_name', 'addressee_phone', 'addressee_cell', 'addressee_zip', 'addressee_addr1', 'addressee_addr2'
            );
            foreach ($ord_fd as $val) {
                if (isset($_REQUEST[$val]) == false) {
                    ${$val} = '';
                    continue;
                }
                ${$val} = addslashes(trim($_REQUEST[$val]));
            }
            $aisql1 = $aisql2 = '';
            foreach ($_order_sales as $key => $val) {
                $__sale = (int) $orderCart->getData($key);
                $aisql1 .= ", {$key}";
                $aisql2 .= ", '$__sale'";
            }
            $this->db->query("
                insert into {$tbl['order']}
                (
                    ono, s_order_id, title, date1, stat, pay_type, member_no, member_id,
                    buyer_name, buyer_email, buyer_phone, buyer_cell,
                    addressee_name, addressee_phone, addressee_cell, addressee_zip, addressee_addr1, addressee_addr2,
                    total_prc, pay_prc, prd_prc, dlv_prc, milage_prc, emoney_prc, total_milage
                    $aisql1
                ) values (
                    '$ono', '$s_order_id', '$title', UNIX_TIMESTAMP(), '11', '$pay_type', '{$member['no']}','{$member['member_id']}',
                    '$buyer_name', '$buyer_email', '$buyer_phone', '$buyer_cell',
                    '$addressee_name', '$addressee_phone', '$addressee_cell', '$addressee_zip', '$addressee_addr1', '$addressee_addr2',
                    '$total_prc', '$pay_prc', '$prd_prc', '$dlv_prc', '$milage_prc', '$emoney_prc', '$ord_total_milage'
                    $aisql2
                )
            ");
            if ($this->db->geterror()) {
                $this->error('주문서 생성 중 오류가 발생하였습니다.');
            }

            // 카드 테이블 생성
            if ($pay_type  == '1') {
                $GLOBALS['ono'] = $ono;
                cardDataInsert($tbl['card']);
            }

            // 할인 정보
            $discount = array();
            foreach ($_order_sales as $key => $val) {
                if ($orderCart->getData($key) > 0) {
                    $discount[] = array(
                        'name' => $val,
                        'value' => $orderCart->getData($key)
                    );
                }
            }

            $this->result('Y', array(
                'ono' => $ono,
                'total_prc' => $total_prc,
                'dlv_prc' => $dlv_prc,
                'pay_prc' => $pay_prc,
                'discount' => $discount,
                'milage_prc' => $milage_prc,
                'emoney_prc' => $emoney_prc
            ));
        }

        /**
         * createOrder에서 생성한 주문을 최종 승인 처리
         **/
        public function approveOrder()
        {
            global $engine_dir, $tbl, $pdo, $_order_stat, $scfg;

            $this->checkPermission('order', 'C0182');

            $ono = (string) $_REQUEST['ono'];
            $force_stat = (int) $_REQUEST['stat'];
            $card_name = (string) $_REQUEST['card_name'];
            $quota = (string) $_REQUEST['quota'];
            $res_msg = (string) $_REQUEST['res_msg'];

            $pg_note_url = true;
            $_REQUEST['accept_json'] = 'Y';

            $ord = $this->db->assoc("select stat from {$tbl['order']} where ono=?", array($ono));
            if (!$ord) {
                $this->error('존재하지 않는 임시주문번호입니다.');
            }
            if ($ord['stat'] != '11') {
                $this->error('이미 처리 된 주문서입니다.');
            }
            if (!$force_stat) {
                $this->error('주문상태를 입력해주세요.');
            }
            if ($force_stat < 1 || $force_stat > 6) {
                $this->error('처리할수 없는 주문상태입니다.');
            }

            include __ENGINE_DIR__.'/_engine/order/order2.exe.php';

            if ($pay_type == '1') {
                $this->db->query("
                    update {$tbl['card']} set card_name=?, quota=?, res_msg=?
                    where wm_ono=?
                ", array(
                    $card_name, $quota, $res_msg, $ono
                ));
            }

            $this->result('Y', array(
                'stat' => array(
                    'value' => $stat,
                    'name' => $_order_stat[$stat]
                )
            ));
        }

		public function memberList() {
			global $tbl;

			if($this->checkhash() == false) {
				$this->error('관리자 로그인 오류');
			}

			$this->checkPermission('member', 'C0035');

			// 페이징
			list($limit, $page, $page_size) = $this->getPageLimit();

			// 검색
			$search_str = addslashes(trim($_REQUEST['search_str']));
			if($search_str) {
				$search_str = urldecode($search_str);
				$w = " and (name like '%$search_str%' or member_id like '%$search_str%' or phone like '%$search_str%' or cell like '%$search_str%' or email like '%$search_str%')";
			}

            $sdate1 = trim($_REQUEST['sdate1']);
            $sdate2 = trim($_REQUEST['sdate2']);
            if ($sdate1 && $sdate2) {
                $sdate1 = strtotime($sdate1);
                $sdate2 = strtotime($sdate2.' 23:59:59');
                $w .= " and reg_date between $sdate1 and $sdate2";
            }

            $level = numberOnly($_REQUEST['level']);
            if ($level) {
                $w .= " and level='$level'";
            }

            // sns login 검색
            $sns_type = $_REQUEST['sns_type'];
            if($sns_type) {
                $sns_type = explode(',', $sns_type);
                foreach($sns_type as $val) {
                    $_w2[] = "`login_type` like '%@$val%'";
                }
                $w .= " and (".implode(" or ", $_w2).")";
            }

			$members = array();
			$res = $this->db->iterator("select * from $tbl[member] where withdraw in ('N', 'D1') $w order by no desc $limit");
            foreach ($res as $data) {
				$members[] = array(
					'no' => $data['no'],
					'name' => stripslashes($data['name']),
					'member_id' => $data['member_id'],
                    'group' => getGroupName($data['level']),
					'email' => $data['email'],
					'birth' => $data['birth'],
					'gender' => $data['sex'],
                    'milage' => $data['milage'],
                    'emoney' => $data['emoney'],
					'reg_date' => $this->getDateStr($data['reg_date']),
                    'last_con' => $this->getDateStr($data['last_con']),
                    'sns_type' => $this->parseSNSLogin($data),
					'sms' => $data['sms'],
                    'mailling' => $data['mailing'],
                    'whole' => $data['whole_mem'],
					'cell' => $data['cell'],
					'phone' => $data['phone'],
				);
			}

			if(count($members)) {
				$this->result('Y', array(
					'rows' => $this->db->row("select count(*) from {$tbl['member']} where withdraw!='Y' $w"),
					'members' => $members,
				));
			} else {
				$this->error('검색된 회원이 없습니다.');
			}
		}

		public function member() {
			global $tbl;

			if($this->checkhash() == false) {
				$this->error('관리자 로그인 오류');
			}

			$this->checkPermission('member', 'C0035');

			$mno = numberOnly($_REQUEST['mno']);
			$mid = addslashes(trim($_REQUEST['mid']));

			$w = '';
			if($mno > 0) $w .= " and no='$mno'";
			if($mid) $w .= " and member_id='$mid'";
			if(!$w) {
				$this->error('필수정보가 없습니다.');
			}

			$data = $this->db->assoc("select * from $tbl[member] where 1 $w");
			if(!$data['no']) {
				$this->error('검색된 데이터가 없습니다.');
			}
			$level = stripslashes($this->db->row("select name from $tbl[member_group] where no='$data[level]'"));

			$memos = array();
            if ($this->checkPermission('member', 'C0245', 'return') == true) {
                $mres = $this->db->iterator("select * from $tbl[order_memo] where type=2 and ono='$data[member_id]'");
                foreach ($mres as $mdata) {
                    $attachs = $this->db->iterator("select updir, filename from {$tbl['neko']} where neko_gr=? and neko_id=?", array(
                        'memo2', 'memo_2_'.$mdata['no']
                    ));
                    $attach = array();
                    foreach ($attachs as $file) {
                        $attach[] = getListImgURL($file['updir'], $file['filename']);
                    }

                    $memos[] = array(
                        'admin_id' => $mdata['admin_id'],
                        'content' => stripslashes($mdata['content']),
                        'reg_date' => $this->getDateStr($mdata['reg_date']),
                        'attach' => $attach
                    );
                }
            }

			$this->result('Y', array(
				'no' => $data['no'],
				'member_id' => $data['member_id'],

			'name' => $data['name'],
				'nick' => $data['nick'],
				'level' => $level,
				'birth' => $data['birth'],
				'gender' => $data['sex'],
				'cell' => $data['cell'],
				'phone' => $data['phone'],
				'addr1' => $data['addr1'],
				'addr2' => $data['addr2'],
				'email' => $data['email'],
				'recom_member' => $data['recom_member'],
				'milage' => $data['milage'],
				'emoney' => $data['emoney'],
				'reg_date' => $this->getDateStr($data['reg_date']),
				'last_con' => $this->getDateStr($data['last_con']),
				'total_ord' => $data['total_ord'],
				'memo' => $memos,
                'sns_type' => $this->parseSNSLogin($data),
                'sms' => $data['sms'],
                'mailling' => $data['mailing'],
                'whole' => $data['whole_mem'],
			));
		}

        /**
         * SNS 로그인 정보 parse
         **/
        private function parseSNSLogin($data)
        {
            global $_sns_type, $_sns_type_info;

            $__sns_type = array_flip($_sns_type);
            $ret = array();
            $login_type = explode('@', trim($data['login_type'], '@'));
            foreach ($login_type as $code) {
                if (!$code) continue;
                $ret[] = array(
                    'code' => $code,
                    'name' => $_sns_type_info[$__sns_type[$code]]['name']
                );
            }

            return $ret;
        }

        /**
         * SNS 로그인 목록
         **/
        public function getSNSLogin()
        {
            global $_sns_type_info;

            $ret = array();
            foreach ($_sns_type_info as $key => $data) {
                $ret[] = array(
                    'code' => $data['name_en'],
                    'name' => $data['name']
                );
            }
            $this->result('Y', array('data' => $ret));
        }

        public function memberJoinSimple()
        {
            global $tbl;

			if($this->checkhash() == false) {
				$this->error('관리자 로그인 오류');
			}

            if ($this->mng['level'] > 3) {
                return $this->error(__lang_common_error_noperm__);
            }

            $cell = numberOnly($_REQUEST['cell']);
            $name = trim($_REQUEST['name']);
            $member_id = $cell;

            if (empty($cell) == true) {
                return $this->error('휴대폰 번호를 입력해주세요.');
            }
            if (empty($name) == true) {
                return $this->error('이름을 입력해주세요.');
            }

            $exists = $this->db->row("select count(*) from {$tbl['member']} where cell=?", array($cell));
            if ($exists > 0) {
                return $this->error('이미 가입된 휴대폰번호입니다.');
            }
            $this->db->query("
                insert into {$tbl['member']} (member_id, level, cell, name, reg_date) values (?, ?, ?, ?, unix_timestamp())
            ", array(
                $member_id, 9, $cell, $name
            ));

            return $this->result('Y', array(
                'member_no' => $this->db->lastInsertId()
            ));
        }

        /**
         * 가입 인증 문자 발송
         **/
        function pushRegSMS($phone = null, $postdata = null)
        {
            global $tbl, $cfg, $sms_replace;

            if ($_REQUEST['phone']) $phone = $_REQUEST['phone'];

            $phone = str_replace('-', '', trim($phone));
            $reg_code = rand(100000, 999999); // 인증 번호
            $limit = time()-3600; // 제한시간 1시간

            $reg_code_enc = aes128_encode($reg_code, 'join');

            $is_mng = $this->checkhash();
            if (($is_mng == false || $this->mng['level'] > 3) && $_REQUEST['guest_id'] != session_id()) {
                return $this->error(__lang_common_error_noperm__);
            }

            if (!$phone) {
                return $this->error(__lang_member_input_cell__);
            }

            // 중복 가입 체크
            if (!$postdata) {
                if ($cfg['join_check_cell'] == 'Y') {
                    $exists = $this->db->row("select count(*) from {$tbl['member']} where cell=?", array(
                        $phone
                    ));
                    if ($exists > 0) {
                        return $this->error(__lang_member_error_existsCell__);
                    }
                }
            }

            // 관리자 중요설정 2차 인증 사용 시
            if($postdata) {
                $reg_cell = $this->db->row("select count(*) from {$tbl['mng']} where cfg_confirm='Y' and replace(cell, '-', '')='$phone'");
                if ($reg_cell == 0) {
                    return $this->error('등록된 인증 휴대폰 번호와 일치하지 않습니다.');
                }
            }

            // 인증 코드 저장
            if(!istable($tbl['join_sms'])) {
                include_once __ENGINE_DIR__.'/_config/tbl_schema.php';
                $this->db->query($tbl_schema['join_sms']);
            }
            $this->db->query("delete from {$tbl['join_sms']} where phone=? or reg_date < ?", array(
                $phone, $limit
            ));
            $this->db->query("insert into {$tbl['join_sms']} (phone, reg_code, reg_date) values (?, ?, ?)", array(
                $phone, $reg_code_enc, time()
            ));
            if ($this->db->lastRowCount() == 0) {
                return $this->error(__lang_member_error_cellAuth__);
            }

            // 문자 발송
            include_once __ENGINE_DIR__."/_engine/sms/sms_module.php";
            $sms_replace['pwd'] = $reg_code;
            $ret = SMS_send_case(22, $phone);
            $ret = mb_convert_encoding($ret, 'utf8', 'euckr');
            if($ret != true) {
                return $this->error(__lang_member_error_sendSms__);
            }

            return $this->result('Y', $ret);
        }

        /**
         * 인증문자 확인
         **/
        public function confirmRegSMS($phone = null, $reg_code = null, $postdata = null)
        {
            global $tbl;

            if ($_REQUEST['phone']) $phone = $_REQUEST['phone'];
            if ($_REQUEST['reg_code']) $reg_code = $_REQUEST['reg_code'];

            if (empty($phone) == true) return $this->error(__lang_member_input_cell__);
            if (empty($reg_code) == true) return $this->error('인증번호를 입력해주세요.');

            $reg_code_enc = aes128_encode($reg_code, 'join');

            $data = $this->db->assoc("select * from {$tbl['join_sms']} where phone=?", array(
                $phone
            ));

            if ($postdata) {
                $this->db->query("delete from {$tbl['join_sms']} where no='{$data['no']}'");

                if (time()-$data['reg_date'] > 300) {
                    return $this->error('입력 시간이 초과되었습니다. 인증번호를 다시 받아주세요.');
                } else if ($reg_code_enc != $data['reg_code']) {
                    return $this->error(__lang_member_error_deffAuthcode__);
                }
            } else {
                if ($reg_code_enc != $data['reg_code']) {
                    return $this->error(__lang_member_error_wrongAuthcode__);
                }
            }

            $this->db->query("delete from {$tbl['join_sms']} where no='{$data['no']}'");

            // 인증 성공
            return $this->result('Y', array(
                'msg' => 'success',
                'reg_code' => $reg_code
            ));
        }

		public function memberGroup() {
			global $tbl;

			$res = $this->db->iterator("select no, name from $tbl[member_group] where no=1 or use_group='Y' order by no asc");
            foreach ($res as $data) {
				$group_list[] = array(
					'level' => $data['no'],
					'name' => stripslashes($data['name']),
				);
			}

			$this->result('Y', array(
				'groups' => $group_list,
			));
		}

		public function milageList() {
			global $tbl, $milage_title;

			if($this->checkhash() == false) {
				$this->error('관리자 로그인 오류');
			}

			$this->checkPermission('member', 'C0045');

			// 페이징
			list($limit, $page, $page_size) = $this->getPageLimit();

			// 검색
			$member_id = addslashes(trim($_REQUEST['member_id']));
			if($member_id) {
				$w = " and member_id='$member_id'";
			}

			$articles = array();
			$rows = 0;
			$res = $this->db->iterator("select * from $tbl[milage] where 1 $w order by no desc $limit");
            foreach ($res as $data) {
				$articles[] = array(
					'member_id' => $data['member_id'],
					'name' => $data['member_name'],
					'title' => $data['title'],
					'ctype' => $data['ctype'],
					'mtype' => $milage_title[$data['mtype']],
					'amount' => $data['amount'],
					'member_milage' => $data['member_milage'],
					'reg_date' => $this->getDateStr($data['reg_date']),
				);
			}

			if(count($articles)) {
				$this->result('Y', array(
					'rows' => $this->db->row("select count(*) from {$tbl['milage']} where 1 $w"),
					'members' => $articles,
				));
			} else {
				$this->error('검색된 내역이 없습니다.');
			}
		}

		// 예치금 리스트
		public function emoneyList() {
			global $tbl, $milage_title;

			if($this->checkhash() == false) {
				$this->error('관리자 로그인 오류');
			}

			$this->checkPermission('member', 'C0046');

			// 페이징
			list($limit, $page, $page_size) = $this->getPageLimit();

			// 검색
			$member_id = addslashes(trim($_REQUEST['member_id']));
			if($member_id) {
				$w = " and member_id='$member_id'";
			}

			$articles = array();
			$res = $this->db->iterator("select * from $tbl[emoney] where 1 $w order by no desc $limit");
            foreach ($res as $data) {
				$articles[] = array(
					'member_id' => $data['member_id'],
					'name' => $data['member_name'],
					'title' => $data['title'],
					'ctype' => $data['ctype'],
					'mtype' => $milage_title[$data['mtype']],
					'amount' => $data['amount'],
					'member_emoney' => $data['member_emoney'],
					'reg_date' => $this->getDateStr($data['reg_date']),
				);
			}

			if(count($articles)) {
				$this->result('Y', array(
					'rows' => $this->db->row("select count(*) from {$tbl['emoney']} where 1 $w"),
					'articles' => $articles,
				));
			} else {
				$this->error('검색된 내역이 없습니다.');
			}
		}

		// 쿠폰발급 리스트
		public function cpnList() {
			global $tbl, $milage_title, $_cpn_stype, $_cpn_sale_type;

			if($this->checkhash() == false) {
				$this->error('관리자 로그인 오류');
			}

			$this->checkPermission('promotion', 'C0152');

			// 페이징
			list($limit, $page, $page_size) = $this->getPageLimit();

			// 검색
			$member_id = addslashes(trim($_REQUEST['member_id']));
			if($member_id) {
				$w = " and member_id='$member_id'";
			}

			$articles = array();
			$res = $this->db->iterator("select * from $tbl[coupon_download] where 1 $w order by no desc $limit");
            foreach ($res as $data) {
				$articles[] = array(
                    'cno' => $data['no'],
					'member_id' => $data['member_id'],
					'name' => $data['member_name'],
					'cpn_name' => $data['name'],
                    'stype' => array(
                        'code' => $data['stype'],
                        'name_kr' => $_cpn_stype[$data['stype']],
                    ),
                    'sale_prc' => $data['sale_prc'],
                    'sale_type' => $_cpn_sale_type[$data['sale_type']],
					'use_date' => $data['use_date'],
					'ono' => $data['ono'],
					'finish_date' => ($data['udate_type'] != 1) ? $data['ufinish_date'].' 23:59:59' : 'null'
				);
			}

			if(count($articles)) {
				$this->result('Y', array(
					'rows' => $this->db->row("select count(*) from {$tbl['coupon_download']} where 1 $w"),
					'articles' => $articles,
				));
			} else {
				$this->error('검색된 내역이 없습니다.');
			}
		}

		// 상품후기 리스트
		public function reviewList() {
			global $tbl, $milage_title;

			// 페이징
			list($limit, $page, $page_size) = $this->getPageLimit();

			// 검색
			$member_id = addslashes(trim($_REQUEST['member_id']));
			if($member_id) {
				$w = " and r.member_id='$member_id'";
			}
			$pno = numberOnly($_REQUEST['pno']);
			if($pno > 0) {
				$w .= " and r.pno='$pno'";
			}
			$search_str = addslashes(trim($_REQUEST['search_str']));
			if($search_str) {
				$w .= " and (r.title like '%$search_str%' or r.content like '%$search_str%')";
			}
            $cate = addslashes($_REQUEST['cate']);
            if ($cate) {
                $w .= " and r.cate='$cate'";
            }
            $stat = addslashes($_REQUEST['stat']);
            if (strlen($stat) > 0) {
                $w .= " and r.stat='$stat'";
            }
            $notice = addslashes($_REQUEST['notice']);
            if ($notice) {
                $w .= " and r.notice='$notice'";
            }
            if (empty($_REQUEST['sdate1']) == false && empty($_REQUEST['sdate2']) == false) {
                $sdate1 = strtotime($_REQUEST['sdate1']);
                $sdate2 = strtotime($_REQUEST['sdate2'])+86399;
                $w .= " and r.reg_date between '$sdate1' and '$sdate2'";
            }
            if ($this->checkhash() == false) {
                $w .= " and r.stat!=1";
            } else {
                $this->checkPermission('member', 'C0039');

                if ($this->mng['level'] == 4) {
                    $w .= " and p.partner_no='{$this->mng['partner_no']}'";
                }
            }

			$articles = array();
			$res = $this->db->iterator("select r.*, p.hash, p.name as pname from $tbl[review] r left join $tbl[product] p on r.pno=p.no where 1 $w order by no desc $limit");
            foreach ($res as $data) {
                // 본문 이미지 추출
                $dom = new DomDocument();
                @$dom->loadHTML(
                    '<meta http-equiv="Content-Type" content="text/html; charset='._BASE_CHARSET_.'">' .
                    $data['content']
                );
                $imgs = $dom->getElementsByTagName('img');
                $images = [];
                if ($imgs->length > 0) {
                    foreach ($imgs as $img) {
                        array_push($images, $img->getAttribute('src'));
                    }
                }

				$data = array_map('stripslashes', $data);
				$articles[] = array(
					'no' => $data['no'],
					'name' => $data['name'],
					'member_id' => $data['member_id'],
					'pname' => $data['pname'],
					'pno' => $data['pno'],
                    'system_code' => $data['hash'],
					'title' => $data['title'],
                    'cate' => $data['cate'],
					'pts' => $data['rev_pt'],
					'recommend_y' => $data['recommend_y'],
					'recommend_n' => $data['recommend_n'],
					'stat' => ($data['notice'] == 'Y') ? '0' : $data['stat'],
                    'photo1' => $data['upfile1'] ? getListImgURL($data['updir'], $data['upfile1']) : '',
                    'photo2' => $data['upfile2'] ? getListImgURL($data['updir'], $data['upfile2']) : '',
                    'images' => $images,
                    'content' => $data['content'],
					'reg_date' => $this->getDateStr($data['reg_date']),
				);
			}

			// 상태별 통계
			$_tabcnt = array('total'=> 0, 0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0);
			$_tmpres = $this->db->query("select notice, r.stat,count(distinct r.no) as cnt from $tbl[review] r left join $tbl[product] p on r.pno=p.no where 1 $w group by r.notice, r.stat");
            foreach ($_tmpres as $_tmp) {
				if($_tmp['notice'] == 'Y') $_tmp['stat'] = 0;
				$_tabcnt[$_tmp['stat']] = $_tmp['cnt'];
				$_tabcnt['total'] += $_tmp['cnt'];
			}

			if(count($articles)) {
				$this->result('Y', array(
					'total_rows' => $this->db->row("select count(*) from $tbl[review]"),
					'rows' => $_tabcnt['total'],
					'rows_0' => $_tabcnt[0],
					'rows_1' => $_tabcnt[1],
					'rows_2' => $_tabcnt[2],
					'rows_3' => $_tabcnt[3],
					'rows_4' => $_tabcnt[4],
					'articles' => $articles,
				));
			} else {
				$this->error('검색된 내역이 없습니다.');
			}
		}

		// 상품후기
		function review() {
			global $tbl;

			$rno = numberOnly($_REQUEST['no']);

			$data = $this->db->assoc("select r.*, p.name as pname from $tbl[review] r left join $tbl[product] p on r.pno=p.no where r.no='$rno'");
			if(!$data) {
				$this->error('조회된 상품후기가 없습니다.');
			}
            if ($data['stat'] == '1') {
                if($this->checkhash() == false) {
                    $this->error('관리자 로그인 오류');
                }

                $this->checkPermission('member', 'C0039');
            }
			$data = array_map('stripslashes', $data);

			$img_url = getFileDir($data['updir']);
			if($data['upfile1']) {
				$img1 = $img_url.'/'.$data['updir'].'/'.$data['upfile1'];
			}
			if($data['upfile2']) {
				$img2 = $img_url.'/'.$data['updir'].'/'.$data['upfile2'];
			}

			$this->result('Y', array(
				'pno' => $data['pno'],
				'pname' => $data['pname'],
				'title' => $data['title'],
				'pts' => $data['rev_pt'],
				'recommend_y' => $data['recommend_y'],
				'recommend_n' => $data['recommend_n'],
				'name' => $data['name'],
				'member_id' => $data['member_id'],
				'reg_date' => $this->getDateStr($data['reg_date']),
				'content' => $data['content'],
				'photo1' => $img1,
				'photo2' => $img2,
			));
		}

		// 상품후기 코멘트
		public function reviewCommentList() {
			global $tbl;

			$no = numberOnly($_REQUEST['no']);
			if(!$no) $this->error('후기번호 오류');

			$cmt_list = array();
			$res = $this->db->iterator("select * from $tbl[review_comment] where ref='$no' order by no asc");
            foreach ($res as $data) {
				$cmt_list[] = array(
					'no' => $data['no'],
					'member_id' => $data['member_id'],
					'name' => stripslashes($data['name']),
					'comment' => stripslashes($data['content']),
					'reg_date' => $this->getDateStr($data['reg_date']),
				);
			}

			$this->result('Y', array(
				'rows' => $this->db->row("select count(*) from {$tbl['review_comment']} where ref='$no'"),
				'comments' => $cmt_list,
			));
		}

		// 상품후기 코멘트 작성
		public function reviewComment() {
			global $tbl, $cfg;

			$is_mng = $this->checkhash();
			$this->checkMhash();

			$no = numberOnly($_REQUEST['no']);
			$comment = addslashes(trim($_REQUEST['comment']));
			$name = addslashes(trim($_REQUEST['name']));
			$member_id = $this->member['member_id'];
			$member_no = $this->member['no'];

			if(!$no) $this->error('상품번호 오류');
			if(!$comment) $this->error('코멘트 내용을 입력해 주세요.');

			if($cfg['product_review_comment'] == '1') {
				if($is_mng == false) {
					$this->error('코멘트 작성 권한이 없습니다.');
				}
				$this->checkPermission('promotion', 'C0040');
			}

			$review = $this->db->assoc("select no from $tbl[review] where no='$no'");
			if(!$review['no']) $this->error('존재하지 않는 후기코드입니다.');

			$now = time();

			if(!$name) {
				if($this->member['name']) {
					$name = $this->member['name'];
				} elseif($is_mng == true) {
					$name = $cfg['admin_nick'];
				} else {
					$this->error('작성자명을 입력해 주세요.');
				}
			}

			$this->db->query("
				insert into $tbl[review_comment] (ref, name, member_id, member_no, content, ip, reg_date)
				values ('$no', '$name', '$member_id', '$member[no]', '$comment', '$_SERVER[REMOTE_ADDR]', '$now')
			");
			$this->db->query("update $tbl[review] set total_comment=total_comment+1 where no='$no'");

			$this->result('Y', '코멘트 작성이 완료되었습니다.');
		}

        // 상품후기 카테고리 출력
        public function reviewCateList()
        {
            global $cfg;

            $data = array();
            $cate = explode(',', $cfg['product_review_cate']);
            foreach ($cate as $val) {
                $data[] = array('name' => $val);
            }

            $this->result('Y', array(
                'data' => $data
            ));
        }

		// 상품문의 리스트
		public function qnaList() {
			global $tbl;

			// 페이징
			list($limit, $page, $page_size) = $this->getPageLimit();

			// 검색
			$w = '';
			$member_id = addslashes(trim($_REQUEST['member_id']));
			if($member_id) {
				$w .= " and r.member_id='$member_id'";
			}
			$pno = numberOnly($_REQUEST['pno']);
			if($pno > 0) {
				$w .= " and r.pno='$pno'";
			}
			$search_str = addslashes(trim($_REQUEST['search_str']));
			if($search_str) {
				$w .= " and (r.title like '%$search_str%' or r.content like '%$search_str%' or r.answer like '%$search_str%')";
			}
            if (empty($_REQUEST['sdate1']) == false && empty($_REQUEST['sdate2']) == false) {
                $sdate1 = strtotime($_REQUEST['sdate1']);
                $sdate2 = strtotime($_REQUEST['sdate2'])+86399;
                $w .= " and r.reg_date between '$sdate1' and '$sdate2'";
            }
            $cate = addslashes($_REQUEST['cate']);
            if ($cate) {
                $w .= " and cate='$cate'";
            }
            $is_answerd = $_REQUEST['is_answerd'];
            if ($is_answerd == 'N' || $is_answerd == 'Y') {
                $w .= " and answer_ok='$is_answerd' and notice='N'";
            }
            $is_notice = $_REQUEST['is_notice'];
            if ($is_notice == 'N' || $is_notice == 'Y') {
                $w .= " and notice='$is_notice'";
            }

			$is_mng = $this->checkhash();
			$is_mem = $this->checkMhash();

			if($is_mng == true) {
				if ($this->checkPermission('member', 'C0038') == false) {
                    $w .= " and secret='N'";
                }
			} else if ($is_mem == true) {
                $w .= " and r.member_no='{$this->member['no']}' and r.member_id='{$this->member['member_id']}'";
            } else {
                $w .= " and r.member_no=0";
            }
            if ($is_mng == true && $this->mng['level'] == 4) {
                $w .= " and p.partner_no='{$this->mng['partner_no']}'";
            }

			$articles = array();
			$res = $this->db->iterator("select r.*, p.name as pname, p.code from $tbl[qna] r left join $tbl[product] p on r.pno=p.no where 1 $w order by no desc $limit");
            foreach ($res as $data) {
				if($is_mng == false && (!$data['member_id'] || $data['member_id'] != $this->member['member_id'])) {
					$data['title'] = '상품문의';
					$data['content'] = $data['answer'] = '';
				}

				if($data['notice'] == 'Y') $stat = 0;
				else $stat = ($data['answer_date']) ? 2 : 1;

				$data = array_map('stripslashes', $data);
				$articles[] = array(
					'no' => $data['no'],
					'name' => $data['name'],
					'member_id' => $data['member_id'],
					'pname' => $data['pname'],
					'pno' => $data['pno'],
                    'code' => $data['code'],
					'cate' => $data['cate'],
					'is_answerd' => ($data['answer_date'] > 0) ? 'Y' : 'N',
					'secret' => $data['secret'],
					'title' => $data['title'],
					'content' => $data['content'],
					'answer' => $data['answer'],
					'answer_id' => $data['answer_id'],
					'answer_date' => $this->getDateStr($data['answer_date']),
					'stat' => $stat,
					'reg_date' => $this->getDateStr($data['reg_date']),
				);
			}

			// 상태별 통계
			$_tabcnt = array('total' => 0, 1 => 0, 2 => 0, 3 => 0);
			$_tmpres = $this->db->iterator("select notice, answer_ok, count(distinct r.no) as cnt from $tbl[qna] r left join $tbl[product] p on r.pno=p.no where 1 $w group by notice, answer_ok");
            foreach ($_tmpres as $_tmp) {
				$_rstat = ($_tmp['answer_ok'] == 'Y') ? '2' : '1';
				if($_tmp['notice'] == 'Y') $_rstat = 0;
				$_tabcnt[$_rstat] = $_tmp['cnt'];
				$_tabcnt['total'] += $_tmp['cnt'];
			}

			if(count($articles)) {
				$this->result('Y', array(
					'total_rows' => $this->db->row("select count(*) from $tbl[qna]"),
					'rows' => $_tabcnt['total'],
					'rows_0' => $_tabcnt[0],
					'rows_1' => $_tabcnt[1],
					'rows_2' => $_tabcnt[2],
					'articles' => $articles,
				));
			} else {
				$this->error('검색된 내역이 없습니다.');
			}
		}

		public function qnaAnswer() {
			global $tbl;

			if($this->checkhash() == false) {
				$this->error('관리자 로그인 오류');
			}

			$no = numberOnly($_REQUEST['no']);
			$answer = addslashes(trim($_REQUEST['answer']));
			$mng_memo = addslashes(trim($_REQUEST['mng_memo']));

			if(!$no) $this->error('상품번호 오류');
            if (empty($answer) == true) return $this->error('답변내용을 입력해주세요.');

			$qna = $this->db->assoc("select no, answer_date, pno from $tbl[qna] where no='$no'");
			if(!$qna['no']) $this->error('존재하지 않는 문의코드입니다.');

            if ($this->mng['level'] == 4) {
                $partner_no = $this->db->row("select partner_no from {$tbl['product']} where no='{$qna['pno']}'");
                if ($this->mng['partner_no'] != $partner_no) {
                    return $this->error(__lang_board_info_auth3__);
                }
            } else {
    			$this->checkPermission('member', 'C0038');
            }

			$add_sql = '';
			if($answer) {
				$mng = $this->getAdmin(true);
				$add_sql .= ", answer='$answer', answer_id='$mng[admin_id]', answer_ok='Y'";
				if(!$qna['answer_date']) $add_sql .= ", answer_date=".time();
			} else {
				$add_sql .= ", answer='', answer_id='', answer_date='0', answer_ok='N'";
			}

			if($mng_memo) {
				$add_sql .= ", mng_memo='$mng_memo'";
			} else {
				$add_sql .= ", mng_memo=''";
			}

			$add_sql = substr($add_sql, 1);
			$this->db->query("update $tbl[qna] set $add_sql where no='$no'");

			$this->result('Y', '답변 작성이 완료되었습니다.');
		}

        // 상품문의 카테고리 출력
        public function qnaCateList()
        {
            global $cfg;

            $data = array();
            $cate = explode(',', $cfg['product_qna_cate']);
            foreach ($cate as $val) {
                $data[] = array('name' => $val);
            }

            $this->result('Y', array(
                'data' => $data
            ));
        }

		// 상품문의
		function qna() {
			global $tbl;

			$this->checkhash();

			$qno = numberOnly($_REQUEST['no']);
			$data = $this->db->assoc("select q.*, p.name as pname from $tbl[qna] q left join $tbl[product] p on q.pno=p.no where q.no='$qno'");

			if(!$data) {
				$this->error('조회된 상품문의가 없습니다.');
			}
			if($data['secret'] == 'Y' && $this->checkPermission('member', 'C0038') == false && (!$data['member_id'] || $data['member_id'] != $this->member['member_id'])) {
				$this->error('상품문의를 조회할 권한이 없습니다.');
			}
            if ($this->mng['level'] == 4 && $this->mng['partner_no'] != $data['partner_no']) {
                return $this->error('상품문의를 조회할 권한이 없습니다.');
            }

			$data = array_map('stripslashes', $data);

			$img_url = getFileDir($data['updir']);
			if($data['upfile1']) {
				$img1 = $img_url.'/'.$data['updir'].'/'.$data['upfile1'];
			}
			if($data['upfile2']) {
				$img2 = $img_url.'/'.$data['updir'].'/'.$data['upfile2'];
			}

			$this->result('Y', array(
				'pno' => $data['pno'],
				'pname' => $data['pname'],
				'title' => $data['title'],
				'cate' => $data['cate'],
				'name' => $data['name'],
				'member_id' => $data['member_id'],
				'reg_date' => $this->getDateStr($data['reg_date']),
				'content' => $data['content'],
				'answer' => $data['answer'],
				'photo1' => $img1,
				'photo2' => $img2,
				'memo' => $data['mng_memo'],
			));
		}

		public function counselList() {
			global $tbl, $_cust_cate;

			// 페이징
			list($limit, $page, $page_size) = $this->getPageLimit();

			$is_mng = $this->checkhash();
			$is_mem = $this->checkMhash();

			if($is_mng == false) {
				if($is_mem == true) {
					$_REQUEST['member_id'] = $this->member['member_id'];
				} else {
					$this->error('로그인 오류');
				}
			} else {
				$this->checkPermission('member', 'C0041');
			}

			// 검색
			$w = '';
			$member_id = addslashes(trim($_REQUEST['member_id']));
			if($member_id) {
				$w .= " and member_id='$member_id'";
			}
			$no = numberOnly(trim($_REQUEST['no']));
			if($no > 0) {
				$w .= " and no='$no'";
			}
			$search_str = addslashes(trim($_REQUEST['search_str']));
			if($search_str) {
				$w .= " and (title like '%$search_str%' or content like '%$search_str%' or reply like '%$search_str%')";
			}
            if (empty($_REQUEST['sdate1']) == false && empty($_REQUEST['sdate2']) == false) {
                $sdate1 = strtotime($_REQUEST['sdate1']);
                $sdate2 = strtotime($_REQUEST['sdate2'])+86399;
                $w .= " and reg_date between '$sdate1' and '$sdate2'";
            }
            $cate1 = addslashes($_REQUEST['cate1']);
            $cate2 = addslashes($_REQUEST['cate2']);
            if (strlen($cate1) > 0 && strlen($cate2) > 0) {
                $w .= " and cate1='$cate1' and cate2='$cate2'";
            }
            $ono = addslashes($_REQUEST['ono']);
            if ($ono) {
                $w .= " and ono='$ono'";
            }
            $is_answerd = $_REQUEST['is_answerd'];
            if ($is_answerd == 'N') {
                $w .= " and reply_date=0";
            } else if ($is_answerd == 'Y') {
                $w .= " and reply_date>0";
            }

			$articles = array();
			$res = $this->db->iterator("select * from $tbl[cs] where 1 $w order by no desc $limit");
            foreach ($res as $data) {
				$data = array_map('stripslashes', $data);
				$articles[] = array(
					'no' => $data['no'],
					'name' => $data['name'],
					'member_id' => $data['member_id'],
					'ono' => $data['ono'],
					'cate' => $_cust_cate[$data['cate1']][$data['cate2']],
                    'cate1' => $data['cate1'],
                    'cate2' => $data['cate2'],
					'is_answerd' => ($data['reply_date'] > 0) ? 'Y' : 'N',
					'title' => $data['title'],
					'content' => $data['content'],
                    'upfile1' => ($data['upfile1']) ? getListImgURL($data['updir'], $data['upfile1']) : null,
                    'upfile2' => ($data['upfile2']) ? getListImgURL($data['updir'], $data['upfile2']) : null,
					'answer' => $data['reply'],
					'mng_memo' => $data['mng_memo'],
					'answer_date' => $this->getDateStr($data['reply_date']),
					'reg_date' => $this->getDateStr($data['reg_date']),
				);
			}

			// 상태별 통계
			$_tabcnt = array('total' => 0, 1 => 0, 2 => 0);
			$_tmpres = $this->db->iterator("select reply_ok, count(*) as cnt from $tbl[cs] where 1 $w group by reply_ok");
            foreach ($_tmpres as $_tmp) {
				$_rstat = ($_tmp['reply_ok'] == 'Y') ? '2' : '1';
				$_tabcnt[$_rstat] = $_tmp['cnt'];
				$_tabcnt['total'] += $_tmp['cnt'];
			}

			if(count($articles)) {
				$this->result('Y', array(
					'total_rows' => $this->db->row("select count(*) from $tbl[cs]"),
					'rows' => $_tabcnt['total'],
					'rows_1' => $_tabcnt[1],
					'rows_2' => $_tabcnt[2],
					'articles' => $articles,
				));
			} else {
				$this->error('검색된 내역이 없습니다.');
			}
		}

		public function counselAnswer() {
			global $tbl;

			$this->checkPermission('member', 'C0041');

			$no = numberOnly($_REQUEST['no']);
			$answer = addslashes(trim($_REQUEST['answer']));
			$mng_memo = addslashes(trim($_REQUEST['mng_memo']));

			if(!$no) $this->error('문의번호 오류');

			if($this->checkhash() == false) {
				$this->error('관리자 로그인 오류');
			}

			$cs = $this->db->assoc("select no, reply_date from $tbl[cs] where no='$no'");
			if(!$cs['no']) $this->error('존재하지 않는 문의코드입니다.');

			$add_sql = '';
			if($answer) {
				$mng = $this->getAdmin(true);
				$add_sql .= ", reply='$answer', reply_id='$mng[admin_id]', reply_ok='Y'";
				if(!$cs['reply_date']) $add_sql .= ", reply_date=".time();
			} else {
				$add_sql .= ", reply='', reply_id='', reply_date='0', reply_ok='N'";
			}

			if($mng_memo) {
				$add_sql .= ", mng_memo='$mng_memo'";
			} else {
				$add_sql .= ", mng_memo=''";
			}

			$add_sql = substr($add_sql, 1);
			$this->db->query("update $tbl[cs] set $add_sql where no='$no'");

			$this->result('Y', '답변 작성이 완료되었습니다.');
		}

        // 1대1문의 카테고리 출력
        public function counselCateList()
        {
            global $_cust_cate;
            $data = array();
            foreach ($_cust_cate as $cate1 => $val1) {
                foreach ($val1 as $cate2 => $val2) {
                    $data[] = array(
                        'cate1' => $cate1,
                        'cate2' => $cate2,
                        'name' => $val2
                    );
                }
            }

            $this->result('Y', array(
                'data' => $data
            ));
        }

		// 게시판 리스트
		public function boardList() {
			global $tbl, $milage_title;

			// 페이징
			list($limit, $page, $page_size) = $this->getPageLimit();

			// 검색
			$w = '';
			$search_str = addslashes(trim($_REQUEST['search_str']));
			if($search_str) {
				$search_str = urldecode($search_str);
				$w = " and (b.title like '%$search_str%' or b.content like '%$search_str%')";
			}

			// 게시판 검색
			$db = addslashes(trim($_REQUEST['db']));
			if($db) {
				$w .= " and db='$db'";
			}

            if ($this->checkPermission('board', 'C0113', 'return') == false) {
                if (fieldExist('mari_board', 'hidden') == true) {
                    $w .= " and hidden='N'";
                }
            }

			$articles = array();
			$res = $this->db->iterator("select b.no, b.db, b.cate, b.name, b.member_id, b.title, b.reg_date, b.secret, c.title as board from mari_board b inner join mari_config c using(db) where 1 $w order by b.no desc $limit");
            foreach ($res as $data) {
				$data = array_map('stripslashes', $data);
				$articles[] = array(
					'no' => $data['no'],
					'db' => $data['db'],
					'board' => $data['board'],
					'cate' => $this->getBoardCateName($data['cate']),
					'name' => $data['name'],
					'member_id' => $data['member_id'],
					'title' => $data['title'],
					'secret' => $data['secret'],
					'reg_date' => $this->getDateStr($data['reg_date']),
				);
			}

			if(count($articles)) {
				$this->result('Y', array(
					'rows' => $this->db->row("select count(*) from mari_board b where 1 $w"),
					'articles' => $articles,
				));
			} else {
				$this->error('검색된 내역이 없습니다.');
			}
		}

		// 게시판 상세
		function board() {
			global $tbl;

			$no = numberOnly($_REQUEST['no']);
			$data = $this->db->assoc("select * from mari_board where no='$no'");
			if(!$data) {
				$this->error('조회된 게시물이 없습니다.');
			}

			$is_mng = $this->checkhash();
			$is_mem = $this->checkMhash();
			if($data['secret'] == 'Y' && $this->checkPermission('board', '6010', null) == false && (!$data['member_id'] || $data['member_id'] != $this->member['member_id'])) {
				$this->error('게시물을 조회할 권한이 없습니다.');
			}

			$data = array_map('stripslashes', $data);

			$img_url = getFileDir('board/'.$data['up_dir']);
			if($data['upfile1']) {
				$file1 = $img_url.'/board/'.$data['up_dir'].'/'.$data['upfile1'];
			}
			if($data['upfile2']) {
				$file2 = $img_url.'/board/'.$data['up_dir'].'/'.$data['upfile2'];
			}

			$comments = array();
			$res = $this->db->iterator("select name, content, reg_date from mari_comment where ref='$no' order by no desc limit 10");
            foreach ($res as $cmt) {
				$comments[] = array(
					'name' => stripslashes($cmt['name']),
					'content' => stripslashes($cmt['content']),
					'reg_date' => $this->getDateStr($cmt['reg_date']),
				);
			}

			$this->result('Y', array(
				'no' => $data['no'],
				'title' => $data['title'],
				'name' => $data['name'],
				'member_id' => $data['member_id'],
				'cate' => $this->getBoardCateName($data['cate']),
				'hit' => $data['hit'],
				'content' => $data['content'],
				'file1' => $file1,
				'file2' => $file2,
				'reg_date' => $this->getDateStr($data['reg_date']),
				'comments' => $comments
			));
		}

        public function boardDBList()
        {
            $data = array();
            $res = $this->db->iterator("select db, title from mari_config");
            foreach ($res as $val) {
                $data[] = array(
                    'db' => $val['db'],
                    'name' => stripslashes($val['title'])
                );
            }
			$this->result('Y', array(
                'data' => $data
            ));
        }

		// 위사 고객센터 리스트
		public function customerList() {
			global $tbl, $_we;

			if($this->checkhash() == false) {
				$this->error('관리자 로그인 오류');
			}
            if ($this->mng['level'] > 3) {
                return $this->error(__lang_board_info_auth1__);
            }

			list($limit, $page, $page_size) = $this->getPageLimit();

			$wec = new weagleEyeClient($_we, 'etc');
			$res = $wec->call('getCustomerList', array('limit' => $limit, 'charset' => _BASE_CHARSET_));
			$res = json_decode($res);

			$articles = array();
			$rows = 0;
			foreach($res as $key => $val) {
				$rows++;
				$val->reg_date = $this->getDateStr($val->reg_date);
				$val->answer_date = $this->getDateStr($val->answer_date);
				$articles[] = $val;
			}

			if($rows > 0) {
				$this->result('Y', array(
					'rows' => $rows,
					'articles' => $articles
				));
			} else {
				$this->error('검색된 내역이 없습니다.');
			}

		}

		// 공지 리스트
		public function noticeList() {
			global $tbl, $_we;

			if($this->checkhash() == false) {
				$this->error('관리자 로그인 오류');
			}
            if ($this->mng['level'] > 3) {
                return $this->error(__lang_board_info_auth1__);
            }

			list($limit, $page, $page_size) = $this->getPageLimit();

			$wec = new weagleEyeClient($_we, 'etc');
			$res = $wec->call('getNoticeList', array('limit' => $limit, 'charset' => _BASE_CHARSET_));
			$res = json_decode($res);

			$articles = array();
			$rows = 0;
			foreach($res as $key => $val) {
				$rows++;

				$articles[] = array(
					'no' => $val->idx,
					'category' => $val->tname,
					'title' => $val->title,
					'content' => $val->content,
					'reg_date' => $this->getDateStr($val->reg_date)
				);
			}

			if($rows > 0) {
				$this->result('Y', array(
					'rows' => $rows,
					'articles' => $articles
				));
			} else {
				$this->error('검색된 내역이 없습니다.');
			}
		}

		// 공지/업데이트 상세
		public function notice() {
			global $tbl, $_we;

			if($this->checkhash() == false) {
				$this->error('관리자 로그인 오류');
			}
            if ($this->mng['level'] > 3) {
                return $this->error(__lang_board_info_auth1__);
            }

			$no = numberOnly($_REQUEST['no']);
			$wec = new weagleEyeClient($_we, 'etc');
			$res = $wec->call('getNotice', array('no' => $no));
			$val = json_decode($res);

			if($val->idx > 0) {
				$this->result('Y', array(
					'no' => $val->idx,
					'category' => $val->tname,
					'title' => $val->title,
					'content' => stripslashes($val->content),
					'reg_date' => $this->getDateStr($val->reg_date)
				));
			} else {
				$this->error('검색된 내역이 없습니다.');
			}

		}

		public function statusList() {
			global $tbl;

            $this->checkPermission('income', 'C0118');

			$year = numberOnly($_REQUEST['year']);
			$month = numberOnly($_REQUEST['month']);
			$day = numberOnly($_REQUEST['day']);

			if($year < 1) {
				$this->error('검색기간을 입력해주세요.');
			}

			$uunit = '%m';
			$uunit_min = 1;
			$uunit_max = 12;
			$statdate1 = strtotime(date("$year-01-01"));
			$statdate2 = strtotime("+1 years", $statdate1)-1;
			if($month > 0) {
				$uunit = '%d';
				$uunit_min = 1;
				$uunit_max = date('t', strtotime("$yy-$mm-01"));
				$statdate1 = strtotime(date("$year-$month-01"));
				$statdate2 = strtotime("+1 months", $statdate1)-1;
			}
			if($month > 0 && $day > 0) {
				$uunit = '%H';
				$uunit_min = 0;
				$uunit_max = 23;
				$statdate1 = strtotime(date("$year-$month-$day"));
				$statdate2 = $statdate1+86399;
			}

			$result = $status_list = array();
			$res = $this->db->iterator("select sum(pay_prc+point_use) as pay_prc, sum(total_prc-repay_prc) as total_prc, from_unixtime(date1, '$uunit') as unit from $tbl[order] where date1 between $statdate1 and $statdate2 and stat between 2 and 5 and (x_order_id in ('', 'checkout', 'talkstore') or x_order_id is null) group by unit order by unit asc");

            foreach ($res as $data) {
				$result[$data['unit']] = array(
					parsePrice($data['total_prc']),
					parsePrice($data['pay_prc']),
				);
			}
			for($i = $uunit_min; $i <= $uunit_max; $i++) {
				$i = sprintf('%02d', $i);
				if(!$result[$i][0]) $result[$i][0] = 0;
				if(!$result[$i][1]) $result[$i][1] = 0;

				$status_list[] = array(
					'unit' => $i,
					'total_prc' => $result[$i][0],
					'pay_prc' => $result[$i][1],
				);
			}

			$this->result('Y', array(
				'min_year' => $this->db->row("select from_unixtime(min(date1), '%Y') from $tbl[order] where date1 > 0"),
				'datas' => $status_list
			));
		}

		public function countList() {
			global $tbl;

			$this->checkPermission('log', 'C0128');

			$year = numberOnly($_REQUEST['year']);
			$month = numberOnly($_REQUEST['month']);
			$day = numberOnly($_REQUEST['day']);

			if($year < 1) {
				$this->error('검색기간을 입력해주세요.');
			}

			$uunit = 'mm';
			$uunit_min = 1;
			$uunit_max = 12;
			if($month > 0) {
				$uunit = 'dd';
				$uunit_min = 1;
				$uunit_max = date('t', strtotime("$yy-$mm-01"));
				$w .= " and mm='$month'";
			}
			if($month > 0 && $day > 0) {
				$uunit = 'hh';
				$uunit_min = 0;
				$uunit_max = 23;
				$w .= " and mm='$month' and dd='$day'";
			}

			$result = $status_list = array();
			if($uunit == 'hh') {
				$data = $this->db->assoc("select * from $tbl[log_day] where yy='$year' $w");
				for($i = 0; $i <= 23; $i++) {
					$result[sprintf('%02d',$i)] = $data['h'.$i];
				}
			} else {
				$res = $this->db->iterator("select yy, mm, dd, sum(hit) as hit from $tbl[log_day] where yy='$year' $w group by $uunit");
                foreach ($res as $data) {
					$result[sprintf('%02d',$data[$uunit])] = $data['hit'];
				}
			}
			for($i = $uunit_min; $i <= $uunit_max; $i++) {
				$i = sprintf('%02d', $i);
				if(!$result[$i]) $result[$i] = 0;

				$status_list[] = array(
					'unit' => $i,
					'count' => $result[$i],
				);
			}

			$this->result('Y', array(
				'min_year' => $this->db->row("select min(yy) from $tbl[log_day]"),
				'datas' => $status_list
			));
		}

		public function searchKeyword() {
			global $tbl;

			$kwd_list = array();
			$res = $this->db->iterator("select sum(hit) as shit, keyword from $tbl[log_search_day] group by keyword order by shit desc limit 20");
            foreach ($res as $data) {
				$kwd_list[] = array(
					'keyword' => stripslashes($data['keyword']),
					'hit' => $data['shit'],
				);
			}

			$this->result('Y', array(
				'keywords' => $kwd_list
			));
		}

		// 비밀번호 찾기
		public function findPassword() {
			global $tbl, $cfg, $engine_dir,
				   $mail_case, $email_checked, $mail_title,
				   $_we, $sms_replace, $root_url;

			$type = $_REQUEST['type'];
			$keyword = addslashes(trim($_REQUEST['keyword']));
			$keyword_name = ($type == 1) ? '이메일주소' : '휴대폰번호';

			if($type != 1 && $type != 2) $this->error('비밀번호 찾기 방식을 선택해 주세요.');
			if(!$keyword) $this->error('조회 할 '.$keyword_name.' 정보를 입력해 주세요.');

			if($type == 1) {
				$asql = " and email='$keyword' and (reg_email='Y' or (reg_email='N' and reg_sms='N'))";
			} else {
				$keyword_n = str_replace('-', '', $keyword);
				$asql = " and (cell='$keyword' or cell='$keyword_n') and (reg_sms='Y' or (reg_email='N' and reg_sms='N'))";
			}

			$data = $this->db->assoc("select * from $tbl[member] where 1 $asql");
			if(!$data['no']) $this->error('일치하는 회원이 없습니다.');

			$now = time();

			if($type == 1) {
				$key = md5($data['member_id'].$now);

				$mail_case = 16;
				include_once $engine_dir.'/_engine/include/mail.lib.php';
				$email = $data['email'];
				$r = sendMailContent($mail_case, $data['name'], $email);
				if(!$r) $this->error('비밀번호 이메일 발송이 실패되었습니다.');

				$result = '비밀번호 변경 이메일이 발송되었습니다.';
			} else {
				$key = mt_rand(1111111, 9999999);
				$title = '임시비밀번호 안내';
				$msg = "[$cfg[company_mall_name]] 임시 비밀번호는 '$key' 입니다.";
				$call_back = ($cfg['config_sms_send'] == '2') ? $cfg['config_sms_send_num'] : $cfg['company_phone'];

				$we_mms = new WeagleEyeClient($_we, $cfg['sms_module']);
				$we_mms->queue('mms_send', 'wing', $_we->config['account_idx'], $keyword, 1, $call_back, $title, $msg, '', $root_url, null, 7);
				$we_mms->send_clean();

				if($we_mms->result != 'OK') $this->error(iconv('euc-kr', _BASE_CHARSET_, $we_mms->result));

				$pwd = sql_password($key);
				$this->db->query("update $tbl[member] set pwd='$pwd' where no='$data[no]'");
				$result = '임시 비밀번호가 SMS로 발송되었습니다.';
			}

			$this->db->query("update $tbl[pwd_log] set stat=2 where member_id='$data[member_id]'");
            $key_enc = aes128_encode($key, 'pwd_log');
			$this->db->query("
				insert into $tbl[pwd_log]
					(stat, member_no, member_id, member_name, email, key, ip, reg_date)
					values
					('1', '$data[no]', '$data[member_id]', '$data[name]', '$keyword', '$key_enc', '$_SERVER[REMOTE_ADDR]', '$now')
			");

			$this->result('Y', $result);
		}

		// 쇼핑몰 설정정보 반환
		public function mallconfig() {
			global $cfg, $root_url;
			$_skin = getSkinCfg();

			if($this->checkhash() == false) {
				$this->error('관리자 로그인 오류');
			}

			$this->result('Y', array(
				'shopname' => $cfg['company_mall_name'],
				'logoimg' => $_skin['url'].'/img/logo/logo.gif',
			));
		}

		// 쇼핑몰명 변경
		public function setMallName() {
			global $tbl;

			$this->checkPermission('config', 'C0068');

			$name = addslashes(trim($_REQUEST['name']));
			if(!$name) $this->result('Y', '변경할 쇼핑몰명을 입력해주세요.');

			$this->db->query("update $tbl[config] set value='$name' where name='company_mall_name'");

			$this->result('Y', '쇼핑몰명이 변경되었습니다.');
		}

		// 공통 이미지 업로드 (바쇼 전용. 이미지 용량 제한 없음)
		public function uploadImage() {
			global $tbl, $engine_dir, $root_dir;

			if($this->checkhash() == false) {
				$this->error('처리 권한이 없습니다.');
			}

			$tmpkey = trim(addslashes($_REQUEST['tmpkey']));
			$imgtype = numberOnly($_REQUEST['imgtype']);
			$no = numberOnly($_REQUEST['no']);
			$image = $_FILES['image'];

			if(!$tmpkey) $this->error('이미지 업로드코드를 입력해주세요.');
			if(!$imgtype) $this->error('업로드 할 이미지 종류를 선택해주세요.');
			if($image['size'] < 1) $this->error('이미지를 업로드해주세요.');

			if($imgtype == 1) $updir = '_data/product/bs'.date('ym/d');
			elseif($imgtype == 2) $updir = '_data/attach/bs'.date('ym/d');
			elseif($imgtype == 3) {
				$skin_cfg = getSkinCfg();
				include_once $engine_dir."/_engine/include/img_ftp.lib.php";
				$image['name'] = 'logo.gif';
				ftpUploadFile($skin_cfg['folder'].'/img/logo', $image, 'gif|jpg|jpeg|png');

				$this->result('Y', 'OK');
			}

			$filename = md5($image['name'].time().rand(0,9999));

			ob_start();
			include_once $engine_dir.'/_engine/include/file.lib.php';
			makeFullDir($updir);
			makeThumb($image['tmp_name'], $image['tmp_name'], 1080, 100000);
			$upload = uploadFile($image, $filename, $updir, 'jpg|jpeg|gif|png');
			$filesize = filesize($image['tmp_name']);
			$img = @getImagesize($image['tmp_name']);
			$errmsg = ob_get_clean();

			if(!$upload[0]) $this->error($errmsg);

			if(isTable($tbl['product_image_tmp']) == false) {
				include $engine_dir.'/_config/tbl_schema.php';
				$this->db->query($tbl_schema['product_image_tmp']);
			}

			$this->db->query("insert into $tbl[product_image_tmp] (tmpkey, imgtype, updir, filename, width, height, size, stat) values ('$tmpkey', '$imgtype', '$updir', '$upload[0]', '$img[0]', '$img[1]', '$filesize', 1)");

			$this->result('Y', $this->db->insert_id);
		}

		// 계좌번호 목록
		public function bankList() {
			global $tbl;

			$res = $this->db->iterator("select * from $tbl[bank_account] where type=1 order by sort asc");
            foreach ($res as $data) {
				$data = array_map('stripslashes', $data);
				$account_list[] = array(
					'no' => $data['no'],
					'bank' => $data['bank'],
					'account' => $data['account'],
					'owner' => $data['owner'],
				);
			}

			$this->result('Y', array(
				'accounts' => $account_list
			));
		}

		// 계좌번호 등록/수정
		public function setBank() {
			global $tbl;

			$this->checkPermission('config', 'C0233');

			$no = numberOnly($_REQUEST['no']);
			$bank = addslashes(trim($_REQUEST['bank']));
			$accountno = addslashes(trim($_REQUEST['accountno']));
			$owner = addslashes(trim($_REQUEST['owner']));

			if(!$bank) $this->error('은행명을 입력해주세요.');
			if(!$accountno) $this->error('계좌번호를 입력해주세요.');
			if(!$owner) $this->error('예금주명을 입력해주세요.');

			if($no > 0) {
				$qry = "update $tbl[bank_account] set bank='$bank', account='$accountno', owner='$owner' where no='$no'";
			} else {
				$sort = $this->db->row("select max(sort) from $tbl[bank_account]");
				$qry = "insert into $tbl[bank_account] (bank, account, owner, sort, type) values ('$bank', '$account', '$owner', '$sort', '1')";
			}
			$r = $this->db->query($qry);
			if($r) $this->result('Y', '계좌번호 저장이 완료되었습니다.');
			else $this->error($this->db->errmsg);
		}

		// 계좌번호 삭제
		public function removeBank() {
			global $tbl;

			$this->checkPermission('config', 'C0233');

			$no = numberOnly($_REQUEST['no']);
			$row = $this->db->row("select no from $tbl[bank_account] where no='$no'");
			if($row > 0) {
				$this->db->query("delete from $tbl[bank_account] where no='$no'");
				$this->result('Y', '선택한 계좌가 삭제되었습니다.');
			} else {
				$this->result('N', '존재하지 않는 계좌 코드입니다.');
			}
		}

		// 위사타그램
		public function getWisatagram() {
			global $_we;

            $this->checkPermission('main');

			$weca = new weagleEyeClient($_we, 'etc');
			$ret = $weca->call('getWisatagram', array('admin_id' => $this->mng['admin_id']));
			$data = json_decode($ret);

			$this->result('Y', array(
				'article' => $data,
			));
		}

		public function setWisatagramLike() {
			global $_we;

            $this->checkPermission('main');

			$idx = numberOnly($_REQUEST['idx']);
			$mng = $this->getAdmin();

			$weca = new weagleEyeClient($_we, 'etc');
			$ret = $weca->call('setWisatagramLike', array('admin_id' => $mng['admin_id'], 'idx' => $idx, 'stat' => $_REQUEST['stat']));

			$this->result('Y', array(
				'result' => $ret,
			));
		}

        // 메시지 발송 제한 설정 열람
        public function getMessageNightTime()
        {
            global $scfg, $tbl, $sms_case_title, $sms_case_admin;

            if ($this->checkPermission('config', 'C0103') == false && $this->checkPermission('member', 'C0102') == false) {
                return $this->error(__lang_common_error_noperm__);
            }

            if (function_exists('SMS_send_case') == false) {
                require __ENGINE_DIR__.'/_engine/sms/sms_module.php';
            }

            $cases = array();
            $res = $this->db->iterator("select `case`, sms_night from {$tbl['sms_case']} where use_check='Y'");
            foreach ($res as $data) {
                $cases[] = array(
                    'case' => $data['case'],
                    'name' => $sms_case_title[$data['case']],
                    'sms_night' => $data['sms_night'],
                    'is_admin' => (in_array($data['case'], $sms_case_admin) == true) ? 'Y' : 'N'
                );
            }

            $this->result('Y', array(
                'night_sms_start' => $scfg->get('night_sms_start'),
                'night_sms_end' => $scfg->get('night_sms_end'),
                'case_no' => $cases
            ));
        }

        // 메시지 발송 제한 설정
        public function setMessageNightTime()
        {
            global $scfg;

            if ($this->checkPermission('config', 'C0103') == false && $this->checkPermission('member', 'C0102') == false) {
                return $this->error(__lang_common_error_noperm__);
            }

            if (is_null($_REQUEST['start_time']) == true) {
                $this->error('시작 시간을 입력해주세요.');
            }
            if (is_null($_REQUEST['end_time']) == true) {
                $this->error('종료 시간을 입력해주세요.');
            }

            $night_sms_start = (int) $_REQUEST['start_time'];
            $night_sms_end = (int) $_REQUEST['end_time'];

            if ($night_sms_start > 23 || $night_sms_start < 0) {
                $this->error('시작 시간이 잘못 설정되었습니다.');
            }
            if ($night_sms_end > 23 || $night_sms_end < 0) {
                $this->error('종료 시간이 잘못 설정되었습니다.');
            }
            if (
                ($night_sms_start < $night_sms_end && abs($night_sms_start-$night_sms_end) < 2) ||
                ($night_sms_start > $night_sms_end && abs($night_sms_start-($night_sms_end+24)) < 2) ||
                $night_sms_start == $night_sms_end
            ) {
                $this->error('발송제한 시작시간과 종료시간의 차이를 최소 2시간 이상으로 설정해 주세요.');
            }

            $scfg->import(array(
                'night_sms_start' => $night_sms_start,
                'night_sms_end' => $night_sms_end
            ));

            return $this->getMessageNightTime();
        }

        // 메시지별 발송 제한 액션 설정
        public function setMessageNightType()
        {
            global $tbl;

            if ($this->checkPermission('config', 'C0103') == false && $this->checkPermission('member', 'C0102') == false) {
                return $this->error(__lang_common_error_noperm__);
            }

            $case = (int) $_REQUEST['case'];
            $type = (string) $_REQUEST['type'];

            if (in_array($type, array('Y', 'N', 'H')) == false) {
                return $this->error('정상적인 메시지 타입이 아닙니다.');
            }

            $data = $this->db->assoc("select * from {$tbl['sms_case']} where `case`=?", array($case));
            if ($data == false) {
                return $this->error('정상적인 메시지 코드가 아닙니다.');
            }

            $this->db->query("update {$tbl['sms_case']} set sms_night=? where `case`=?", array(
                $type, $case
            ));
            if ($this->db->getError()) {
                $msg = '업데이트중 오류가 발생하였습니다.';
            } else if ($this->db->lastRowCount() == 0) {
                $msg = '변경된 내역이 없습니다.';
            } else {
                $msg = 'success';
            }

            return $this->result('Y', $msg);
        }

		public function getConfig() {
			global $_we, $cfg;

			$weca = new weagleEyeClient($_we, 'etc');
			$ret = $weca->call('checkSiteKey', array('site_key' => $_REQUEST['site_key']));
			$ret = json_decode($ret);
			if($ret->result == 'true') {
				$this->result('Y', array(
					'result' => $cfg
				));
			} else {
				$this->result('N', array(
					'result' => '사이트키 오류'
				));
			}
		}

		// 로그인 세션 검증
		private function checkhash() {
			global $tbl, $db_session_handler;

			if(!$this->hash) return false;

            $mng = $this->getAdmin();
            if (is_array($mng) == true && $this->hash == $mng['hash']) {
                return true;
            }

            return $db_session_handler->exists($this->hash);
		}

		private function checkMhash() {
			global $tbl;

			if(!$this->mhash) return false;

			$member = $this->db->assoc("select no, member_id, name, nick, level from $tbl[member] where hash='$this->mhash'");
			if(!$member['no']) {
				return false;
			}
			$this->member = $member;

			return true;
		}

		private function getAdmin($deny = false) {
			global $tbl, $db_session_handler;

            if (empty($this->hash) == true) {
                return false;
            }

            $data = $db_session_handler->parse($this->hash);
			$admin_no = $data['admin_no'];
			if(!$admin_no) {
                $admin_no = $this->db->row("select no from {$tbl['mng']} where hash=?", array($this->hash));
                if ($admin_no == false) {
                    if($deny == true) $this->error('관리자 로그인 오류');
                    return false;
                }
			}

			$admin = $this->db->assoc("select * from $tbl[mng] where no='$admin_no'");
			$admin['name'] = stripslashes($admin['name']);
            setType($admin['level'], 'integer');
            if (isset($admin['partner_no']) == true) {
                setType($admin['partner_no'], 'integer');
            } else {
                $admin['partner_no'] = 0;
            }

			if(strpos($admin['auth'], 'auth_detail')) {
				$auth = $this->db->assoc("select * from {$tbl['mng_auth']} where admin_no='{$admin['no']}'");
				if(is_array($auth)) {
					array_shift($auth);
					$admin = array_merge($admin, $auth);
				}
			}
            // 입점사 관리자 권한 강제 입력
            if ($admin['level'] == '4') {
                $admin['auth'] = 'config@product@order@member@auth_detail';
                $admin['product'] = '@C0004@C0005@C0079';
                $admin['order'] = '@C0021';
                $admin['member'] = '@C0038@C0039';
                $admin['config'] = '@C0281';
            }

			$this->mng = $admin;
            $this->db->query("SET @admin_id=?", array($admin['admin_id']));

			return $admin;
		}

		private function getPageLimit() {
			$limit = '';
			$page = numberOnly($_REQUEST['page']);
			$page_size = numberOnly($_REQUEST['page_size']);
			if($page < 1) $page = 1;
			if($page_size < 1) $page_size = 20;

			$limit = sprintf(" limit %d, %d", ($page-1)*$page_size, $page_size);

			return array($limit, $page, $page_size);
		}

		// 상품 상태
		private function getPrdStat($stat) {
			return $GLOBALS['_prd_stat'][$stat];
		}

		// 주문 결제방법
		private function getOrdPayType($type) {
			return $GLOBALS['_pay_type'][$type];
		}

		// 주문 상태
		private function getOrdStat($stat) {
			return $GLOBALS['_order_stat'][$stat];
		}

		// 회원 그룹명
		private function getMemberGroup($member_id) {
			global $tbl;
			return stripslashes($this->db->row("select b.name from $tbl[member] a inner join $tbl[member_group] b on a.level=b.no where a.member_id='$member_id'"));
		}

		// 상품 카테고리명
		private function getCateName($cno, $use_cache = true) {
			global $tbl, $__cate_cache;

            if ($cno == 0) return '';
			if ($use_cache == false || count($__cate_cache) == 0) {
                $cate_where = '';
				if ($use_cache == false) {
					$cno = numberOnly($cno);
					$cate_where .= " and no='$cno'";
				}
				$res = $this->db->iterator("select no, name from $tbl[category] where ctype in (1, 2, 4, 5, 6) $cate_where");
				foreach ($res as $data) {
					$__cate_cache[$data['no']] = stripslashes($data['name']);
				}
			}

			return ($__cate_cache[$cno]) ? $__cate_cache[$cno] : '';
		}

		private function getEventNames($codes) {
			global $tbl;

			$str = '';
			$codes = explode('@', trim($codes, '@'));
            foreach ($codes as $val) {
                if ($str) $str .= ',';
                $str .= $this->getCateName($val);
            }

			return $str;
		}

		// 게시판 카테고리명
		private function getBoardCateName($cno, $use_cache = true) {
			global $__bbs_cate_cache;

			if($cno < 1) return '';

			if(!$__bbs_cate_cache[$cno]) {
				if($use_cache != true) {
					$cno = numberOnly($cno);
					$cate_where = " and no='$cno'";
				}
				$res = $this->db->iterator("select no, name from mari_cate where 1 $cate_where");
                foreach ($res as $data) {
					$__bbs_cate_cache[$data['no']] = stripslashes($data['name']);
				}
			}

			return $__bbs_cate_cache[$cno];
		}

		// 현금영수증 상태
		private function getCashReceiptStat($stat) {
            if (!$stat) return false;
			return $GLOBALS['_order_cash_stat'][$stat];
		}

		// 택배사명
		private function getDlvName($dlv_no) {
			global $tbl, $__dlv_cache;

			if(!$__dlv_cache[$dlv_no]) {
				$dlv = $this->db->assoc("select name from $tbl[delivery_url] where no='$dlv_no'");
				$__dlv_cache[$dlv_no] = stripslashes($dlv['name']);
			}

			return $__dlv_cache[$dlv_no];
		}

        // 입점사명
        private function getPartnerName($partner_no)
        {
            global $cfg, $tbl;

            if ($partner_no === 0) return $cfg['company_mall_name'];
            return $this->db->row("select corporate_name from {$tbl['partner_shop']} where no=?", array($partner_no));
        }

		// 날짜형식
		private function getDateStr($timestamp) {
			$timestamp = numberOnly($timestamp);
			if($timestamp < 1) return 0;

			return date('Y-m-d H:i:s', $timestamp);
		}

		// 임시 이미지 이동
		private function setImage($tmpkey, $key) {
			global $tbl, $engine_dir;

			if(isTable($tbl['product_image_tmp']) == false) {
				include $engine_dir.'/_config/tbl_schema.php';
				$this->db->query($tbl_schema['product_image_tmp']);
			}

			include_once $engine_dir.'/_engine/include/file.lib.php';

			$prd_asql = '';

			$res = $this->db->iterator("select * from $tbl[product_image_tmp] where tmpkey='$tmpkey' and stat=1 order by no asc");
            foreach ($res as $data) {
				switch($data['imgtype']) {
					case '1' : // 소이미지
						$prd = $this->db->assoc("select updir, upfile2, upfile3 from $tbl[product] where no='$key'");
						deletePrdImage($prd, 2, 3);

						$this->db->query("update $tbl[product] set updir='$data[updir]', upfile2='$data[filename]', upfile3='$data[filename]', w2='$data[width]', w3='$data[width]', h2='$data[height]', h3='$data[height]' where no='$key'");
					break;
					case '2' : // 부가이미지
						$sort = $this->db->row("select max(sort) from $tbl[product_image] where pno='$key' and filetype=2")+1;
						$this->db->query("
							insert into $tbl[product_image]
								(pno, filetype, updir, filename, stat, reg_date, width, height, filesize, sort)
								values
								('$key', '2', '$data[updir]', '$data[filename]', '2', unix_timestamp(now()), '$data[width]', '$data[height]', '$data[size]', '$sort')
						");
					break;
				}
				$this->db->query("update $tbl[product_image_tmp] set stat=2 where no='$data[no]'");
			}
		}

		// 접근 권한 체크
		function checkPermission($code, $detail = null, $return_type = 'json') {
			if($this->checkhash() == false) {
				$this->error('관리자 로그인 오류');
			}

            $mng = ($this->mng) ? $this->mng : $this->getAdmin();
            if (!$mng) {
                $this->error('관리자 로그인 오류');
            }

			if($mng['level'] == 1 || $mng['level'] == 2) return true;

			$auth = explode('@', trim($mng['auth'], '@'));
			if(in_array('auth_detail', $auth) && $mng[$code]) { // 세부 권한
				$auth_detail = explode('@', trim($mng[$code], '@'));
				if(in_array($code, $auth) == false || ($detail && in_array($detail, $auth_detail) == false)) {
                    if ($return_type == 'json') {
    					exit(json_encode(array('status'=>'permission_denied')));
                    }
                    return false;
				}
			} else { // 전체권한
				if(in_array($code, $auth) == false) {
                    if ($return_type == 'json') {
    					exit(json_encode(array('status'=>'permission_denied')));
                    }
                    return false;
				}
			}
            return true;
		}

		// 결과 json 출력
		private function result($status, $_ret) {
			$result = array('status' => $status);
			if(is_array($_ret)) {
				$result = array_merge($result, $_ret);
			} else {
				$result['msg'] = $_ret;
			}

			if(defined('JSON_UNESCAPED_UNICODE') == false) {
				define('JSON_UNESCAPED_UNICODE', null);
			}
			if(defined('JSON_PRETTY_PRINT') == false) {
				define('JSON_PRETTY_PRINT', null);
			}
			header('Content-type:application/json; charset='._BASE_CHARSET_);
			exit(json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		}

		// 에러결과 json 출력
		private function error($errmsg) {
			$this->result('N', $errmsg);
		}

	}

?>