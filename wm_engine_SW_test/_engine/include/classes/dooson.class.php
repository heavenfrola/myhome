<?PHP

	use Wing\DB\Oracle\Oracle;
	use Wing\API\Naver\CheckoutApi4;

	include_once $engine_dir.'/_engine/include/wingPos.lib.php';
	include_once $engine_dir.'/_manage/manage2.lib.php'; // 에스크로 정보 전송을 위해

	class Dooson extends ErpInterface {
		protected $corp_cd;
		protected $lang_cd;
		protected $pgm_id;
		protected $register;
		protected $conn;
        protected $pdo;
		public $db;
		public $regist_shpcd;

		public function __construct($param = array()) {
			$this->corp_cd = $param['dooson_corp_id'];
			$this->lang_cd = $param['dooson_lang_cd'];
			$this->brand_nm = $param['dooson_brand_nm'];
			$this->memprefix = $param['dooson_member_prefix'];
			$this->dlv_lang_cd = $param['dooson_dlv_lang_cd'];
			$this->off_lang_cd = $param['dooson_off_lang_cd'];
			$this->pgm_id = 'SCHEDULER';
			$this->register = 'DSMALL';
			$this->conn = $param['dbinfo'];
            $this->pdo = $GLOBALS['pdo'];
            $this->owner = $param['owner'];
            if (!$this->owner) $this->owner = 'DSASP';
		}

		public function connect() {
			$this->db = new Oracle();
			$this->db->connect($this->conn);
		}


		/* +----------------------------------------------------------------------------------------------+
		' |  상품
		' +----------------------------------------------------------------------------------------------+*/
		public function getChangedProduct($code = null) {
			global $tbl, $now;

			if(!$this->db) $this->connect();

			$admin['admin_id'] = 'dooson_api';
			$remote_ip = $_SERVER['REMOTE_ADDR'];

			if($code) $w = " and style_cd='$code'";
			else {
				$w = " and SEND_YN='N'";
				if($this->brand_nm) {
					if(is_array($this->brand_nm)) {
						$tmp = array();
						foreach($this->brand_nm as $key => $val) {
							$tmp[$key] = "'$val'";
						}
						$tmp = implode(',', $tmp);
						$w .= " and BRAND_NM in ($tmp)";
					} else {
						$w .= " and BRAND_NM='$this->brand_nm'";
					}
				}
			}
			$orderby = " order by MODIFYDT asc";

			$success = 0;

			$res = $this->db->query("select * from {$this->owner}.TPL_MALLSTYLEBLNCINFO where CORP_CD='$this->corp_cd' $w $orderby");
			while($data = oci_fetch_assoc($res)) {

				$data = $this->convertUTF8($data);
				$vals = $ori = array();

				if(!$data['OUTPUT_CDTNM'] && !$data['STYLE_NM']) continue;

				$ori_prd = $this->pdo->assoc("select no, stat, sell_prc, stock_yn from $tbl[product] where code='$data[STYLE_CD]' and stat > 1 and wm_sc=0");

				// 상품 데이터
				$vals['code'] = $data['STYLE_CD'];
				$vals['name'] = $data['OUTPUT_CDTNM'] ? $data['OUTPUT_CDTNM'] : $data['STYLE_NM'];
				$vals['origin_name'] = $data['WARE_NM'];
				$vals['keyword'] = $data['SEARCH_NM'];
				$vals['free_delivery'] = $data['SEND_SEC'] == 1 ? 'Y' : 'N';
				$vals['origin_prc'] = numberOnly($data['COST_AMT']);
				if(!$ori_prd['no'] || !$ori_prd['sell_prc']) { // 몰판매가격 최초 1회만 수집(또는 0에서 변경 될 경우)
					$vals['sell_prc'] = numberOnly($data['SELL_UC']);
				}
				$vals['normal_prc'] = numberOnly($data['TAG_PRC']);
				$vals['reg_date'] = $this->date2timestamp($data['REGISTDT']);
				$vals['edt_date'] = $this->date2timestamp($data['MODIFYDY']);
				$vals['stat'] = $ori_prd['stat'];
				$vals['seller'] = ($data['PROPERTY_04']) ? $data['PROPERTY_04'] : $data['FACTORY_CD'];
				$vals['seller_idx'] = $data['VENDOR_CD'];
				$vals['origin_name'] = $data['WARE_NM'];
				$vals['origin_prc'] = $data['COST_AMT'];
				$vals['mng_memo'] = addslashes($data['SKIN_REMARK']);
				$vals['stock_yn'] = $data['STOCK_YN'];

				if($ori_prd['no']) $vals['no'] = $ori_prd['no'];
				if(!$vals['edt_date']) $vals['edt_date'] = $reg_date;

				// 수정쿼리 생성
				if($vals['no'] > 0) {
					$ori = $this->pdo->assoc("select stat from $tbl[product] where no='$vals[no]'");
					$qry = $this->makeQuery($tbl['product'], $vals, "no='$vals[no]'");
				} else {
					$vals['stat'] = 4; // 신규 상품 숨김으로 등록
					$vals['min_ord'] = 1; // 최소 구매수량 기본 값
					if(!$vals['no'] || $vals['no'] == 'NONE') {
						$vals['no'] = $this->pdo->row("select max(no) from $tbl[product]")+1;
					}
					$vals['hash'] = strtoupper(md5($vals['no']));
					//$vals['big'] = 1544; // 신규 등록시 임시카테고리에 저장

					$qry = $this->makeQuery($tbl['product'], $vals);

					prdStatLogw($vals['no'], $vals['stat'], 1);
				}
				$this->pdo->query($qry);

				if($ori_prd['stat'] == 3 && $data['SOLDOUT_YN'] == 'N') {
					$this->pdo->query("update $tbl[product] set stat='2' where no='$vals[no]' or (wm_sc > 0 && wm_sc='$vals[no]')");
					prdStatLogw($vals['no'], 2, 3);
				}
				if($ori_prd['stat'] == 2 && $data['SOLDOUT_YN'] == 'Y') {
					$this->pdo->query("update $tbl[product] set stat='3' where no='$vals[no]' or (wm_sc > 0 && wm_sc='$vals[no]')");
					prdStatLogw($vals['no'], 3, 2);
				}

                $this->getProductField($data, $vals['no']);

				if(is_null($this->pdo->getError()) == true) { // 두손 서버 업데이트
					$success++;
					$this->db->query("
						update {$this->owner}.TPL_MALLSTYLEBLNCINFO set
							SEND_YN='Y',
							SEND_DT=TO_CHAR(SYSDATE, 'YYYYMMDDHH24MISS'),
							MODIFYDT=TO_CHAR(SYSDATE, 'YYYYMMDDHH24MISS'),
							MODIFYUSER='$this->register',
							MODIFYPGMID='get_product',
							MODIFYIP='$remote_ip'
						where CORP_CD='$this->corp_cd' and STYLE_CD='$data[STYLE_CD]'
					");
				}
			}
			return $success;
		}

		public function getProductOption($code = null) {
			global $tbl;

			if(!$this->db) $this->connect();

			$changed = array();

			if($code) $w = " and style_cd='$code'";
			else $w = " and SEND_YN='N'";

			$res = $this->db->query("select * from {$this->owner}.TPL_MALLSTYLEBLNCOPTION where CORP_CD='$this->corp_cd' $w");
			while($data = oci_fetch_assoc($res)) {
				$is_prd = $this->pdo->row("select code from $tbl[product] where code='$data[STYLE_CD]'");
				if($is_prd) {
					$changed[] = $this->createOption(null, $data);
				}
			}

			// 옵션이 추가, 변경 되었을 경우 실행
			if(count($changed) > 0) {
				$changed = array_unique($changed);
				foreach($changed as $style_cd) {
					$res = $this->db->query("select * from {$this->owner}.TEB_SKU where CORP_CD='$this->corp_cd' and STYLE_CD='$style_cd' order by SEQ asc");
					while($sku = oci_fetch_assoc($res)) {
						$sku = $this->convertUTF8($sku);

						$complex_no = $this->createWingpos($sku);
						$this->db->query("
							update {$this->owner}.TEB_SKU set
								SEND_YN='Y',
								SEND_DT=TO_CHAR(SYSDATE, 'YYYYMMDDHH24MISS'),
								MODIFYDT=TO_CHAR(SYSDATE, 'YYYYMMDDHH24MISS'),
								MODIFYUSER='$this->register',
								MODIFYPGMID='get_option_add',
								MODIFYIP='$remote_ip'
							where CORP_CD='$this->corp_cd' and SKU='$sku[SKU]'
						");
					}
				}
			}
		}


        /**
         * 두손ERP의 상품필드를 추가필드와 매칭
         **/
        public function getProductField($data, $pno)
        {
            global $tbl;

            if (fieldExist($tbl['product_field_set'], 'doosoun_fd') == false) {
                return false;
            }

            // 매칭 테이블 생성
            $field = array();
            $res = $this->pdo->iterator("select no, doosoun_fd from {$tbl['product_field_set']} where doosoun_fd != ''");
            foreach ($res as $fdata) {
                $field[$fdata['no']] = $fdata['doosoun_fd'];
            }
            if (count($field) == 0) return false;

            // 실제 데이터 매칭
            foreach ($field as $key => $val) {
                $val = trim($data[$val]);
                $no = $this->pdo->row("select no from {$tbl['product_field']} where pno='$pno' and fno='$key'");
                if ($no > 0) {
                    $this->pdo->query(
                        "update {$tbl['product_field']} set value=? where no=?",
                        array($val, $no)
                    );
                } else {
                    if ($val) {
                        $this->pdo->query(
                            "insert into {$tbl['product_field']} (pno, fno, value) values (?, ?, ?)",
                            array($pno, $key, $val)
                        );
                    }
                }
            }
            return true;
        }

		public function getSKU() {
			global $tbl;

			if(!$this->db) $this->connect();

			$w = " and SEND_YN='N'";

			$style_cds = array();
			$res = $this->db->query("select * from {$this->owner}.TEB_SKU where CORP_CD='$this->corp_cd' $w");
			while($sku = oci_fetch_assoc($res)) {
				$is_prd = $this->pdo->row("select code from $tbl[product] where code='$sku[STYLE_CD]'");
				if(!$is_prd) continue;

				$complex_no = $this->pdo->row("select complex_no from erp_complex_option where barcode='$sku[SKU]'");
				if($sku['STOCK_YN'] == 'Y' || !$complex_no) {
					$complex_no = $this->createWingpos($sku);
				} else {
					if(!$sku['SOLDOUT_YN']) $sku['SOLDOUT_YN'] = 'N';
					$this->pdo->assoc("update erp_complex_option set force_soldout='$sku[SOLDOUT_YN]' where sku='$sku[SKU]'");
				}

				$style_cds[] = $sku['STYLE_CD'];

				$this->db->query("
					update {$this->owner}.TEB_SKU set
						SEND_YN='Y',
						SEND_DT=TO_CHAR(SYSDATE, 'YYYYMMDDHH24MISS'),
						MODIFYDT=TO_CHAR(SYSDATE, 'YYYYMMDDHH24MISS'),
						MODIFYUSER='$this->register',
						MODIFYPGMID='get_common',
						MODIFYIP='$remote_ip'
					where CORP_CD='$this->corp_cd' and SKU='$sku[SKU]'
				");
			}

			$style_cds = array_unique($style_cds);
			foreach($style_cds as $code) {
				$tmp = array();
				$prd = $this->pdo->assoc("select no, stat from $tbl[product] where code='$code'");
				$res = $this->pdo->iterator("select count(*) as cnt, force_soldout from erp_complex_option where pno='$prd[no]' and del_yn='N' group by force_soldout");
                foreach ($res as $data) {
					$tmp[$data['force_soldout']] = $data['cnt'];
				}
				if($prd['stat'] == 2 && ($tmp['Y'] > 0 && $tmp['N'] < 1 && $tmp['L'] < 1)) {
					$this->pdo->query("update $tbl[product] set stat=3 where no='$prd[no]'");
					prdStatLogw($prd['no'], 3, 2);
				}
			}
		}

		public function createOption($vals, $data) {
			global $tbl, $now;

			if(!$this->db) $this->connect();

			$pno = (!$vals) ? $this->pdo->row("select no from $tbl[product] where code='$data[STYLE_CD]' and wm_sc=0") : $vals['no'];
			if(!$pno) return;

			if($data['OPTION_SEQ'] > 0) $w .= " and OPTION_SEQ='$data[OPTION_SEQ]'";

			$changed = '';
			$res2 = $this->db->query("select * from {$this->owner}.TPL_MALLSTYLEBLNCOPTION where CORP_CD='$this->corp_cd' and STYLE_CD='$data[STYLE_CD]' $w order by OPTION_SEQ asc");
			while($opt = oci_fetch_assoc($res2)) {
				$opt = $this->convertUTF8($opt);

				$oname = trim($opt['OPTION_TTL']);
				$sell_yn = $opt['SELL_YNVAL'];
				$seq = $opt['OPTION_SEQ'];
				$seq1 = $opt['PROPERTY_01'];
				$price = $_prd[$seq];
				$opno = $pno.addZero($seq, 2);
				$option_val = addslashes($opt['OPTION_VAL']);
				$_iname = explode(',', $option_val);
				$_prc = explode(',', $opt['OPTION_PRC']);
				$_iname = explode(',', str_replace('  ', ' ', $opt['OPTION_VAL']));
				$_ino = explode(',', $opt['PROPERTY_02']);
				$_sell_yn = explode(',', $opt['SELL_YNVAL']);

				if(!$option_val) continue;

				// 옵션 세트
				$optionset = $this->pdo->assoc("select no, items from $tbl[product_option_set] where pno='$pno' and no='$opno'");
				if($optionset['no'] > 0) {
					$this->pdo->query("update $tbl[product_option_set] set name='$oname', sort='$seq', items='$option_val' where no='$opno'");
				} else {
					$this->pdo->query("
						insert into $tbl[product_option_set]
						(no, pno, name, necessary, otype, how_cal, sort, reg_date, stat)
						values
						('$opno','$pno', '$oname', 'Y', '2A', '1', '$seq', '$now', '2')
					");
				}

				// 옵션 아이템
				if($optionset['items'] == $opt['OPTION_VAL']) { // 옵션 변경이 없을 경우
					foreach($_iname as $key => $iname) {
						$ino = trim($_ino[$key]);
						$iname = trim($iname);
						$add_price = trim($_prc[$key]);
						$hidden = ($_sell_yn[$key] == 'N') ? 'Y' : 'N';

						$this->pdo->query("update $tbl[product_option_item] set hidden='$hidden', add_price='$add_price' where pno='$pno' and opno='$opno' and iname='$iname'");
					}
				} else { // 신규 혹은 옵션 변경이 있을 경우 SKU 추가 구성
					$changed = $opt['STYLE_CD'];
					foreach($_iname as $key => $iname) {
						$iname = trim($iname);
						$ino = trim($_ino[$key]);
						$add_price = trim($_prc[$key]);
						$hidden = ($_sell_yn[$key] == 'N') ? 'Y' : 'N';
						$sort = $key++;

						$exists = $this->pdo->row("select no from $tbl[product_option_item] where pno='$pno' and opno='$opno' and iname='$iname'");
						if($exists > 0) {
							$this->pdo->query("
								update $tbl[product_option_item] set
									iname='$iname', add_price='$add_price', hidden='$hidden', sort='$sort' ,
									ds_opt = '$oname'
								where no='$exists'
							");
						} else {
							$this->pdo->query("
								insert into $tbl[product_option_item]
								(pno, opno, iname, add_price, hidden, sort, ds_opt, reg_date)
								values
								('$pno', '$opno', '$iname', '$add_price', '$hidden', '$sort', '$oname', '$now')
							");
						}
					}
				}

				$this->db->query("
					update {$this->owner}.TPL_MALLSTYLEBLNCOPTION set SEND_YN='Y'
						, SEND_DT=TO_CHAR(SYSDATE, 'YYYYMMDDHH24MISS')
						, MODIFYDT=TO_CHAR(SYSDATE, 'YYYYMMDDHH24MISS')
						, MODIFYUSER='$this->register'
						, MODIFYPGMID='get_option'
						, MODIFYIP='$_SERVER[REMOTE_ADDR]'
						where CORP_CD='$this->corp_cd'
							and STYLE_CD='$data[STYLE_CD]'
							and STYLE_BLNC='$opt[STYLE_BLNC]'
							and OPTION_SEQ='$opt[OPTION_SEQ]'
				");
			}

			return $changed;
		}

		// 윙포스 데이터 생성
		public function createWingpos($sku, $pno = null) {
			global $tbl, $admin;

			if(!$pno) $pno = $this->pdo->row("select no from $tbl[product] where code='$sku[STYLE_CD]' and wm_sc=0");
			if(!$pno) return;

			if(!$admin['admin_id']) $admin['admin_id'] = 'dooson_api';

			$sku_cd = $sku['SKU'];
			$barcode = $sku['BARCODE'];
			$stock_qty = $sku['STOCK_QTY'];

			$complex_option = $this->pdo->assoc("select complex_no, curr_stock(complex_no) as qty, force_soldout, opts from erp_complex_option where pno='$pno' and sku='$sku_cd' and del_yn='N'");
			$complex_no = $complex_option['complex_no'];

			if($complex_no > 0) {
				$complex_option['opts'] = str_replace('_x_', '_', $complex_option['opts']);
				$opts = str_replace('_', ',', trim($complex_option['opts'], '_'));
				$opts_total = ($opts) ? count(explode(',', $opts)) : 0;

				$check = $this->pdo->row("select count(*) from $tbl[product_option_set] where pno='$pno' and necessary!='P' and otype!='4B'");
				if($opts_total != $check) {
					$this->pdo->query("delete from erp_complex_option where complex_no='$complex_no'");
					$this->pdo->query("delete from erp_complex_option where barcode='$sku[SKU]'");
					unset($complex_no, $complex_option);
				}
			}

			if($complex_no > 0) {
				$order_qty = $this->pdo->row("select sum(buy_ea) from $tbl[order_product] op where complex_no='$complex_option[complex_no]' and s_order_id='' and stat between 1 and 3 and (select count(*) from {$tbl['order']} where ono=op.ono)>0");
				$qty = $sku['STOCK_QTY']-$order_qty;
				if($complex_option['qty'] != $qty) {
					$ea = abs($complex_option['qty']-$qty);
					$kind = $complex_option['qty'] > $qty ? 'P' : 'U';
					$this->pdo->query("
						insert into `erp_inout`
						(`complex_no`, `inout_kind`, `qty`, `remark`, `reg_user`, `reg_date`, `remote_ip`) values
						('$complex_no', '$kind', '$ea', 'dooson에서 업데이트', '$admin[admin_id]', now(), '$remote_ip')
					");
				}
				if(!$sku['SOLDOUT_YN']) $sku['SOLDOUT_YN'] = 'N';
				$this->pdo->query("update erp_complex_option set qty='$qty', force_soldout='$sku[SOLDOUT_YN]' where complex_no='$complex_no'");


			} else {
				$key = 0;
				$res = $this->pdo->iterator("select sort from $tbl[product_option_set] where pno='$pno' order by sort asc");
                foreach ($res as $data) {
					$_seq1[$key] = $data['sort'];
					$key++;
				}
				$opts = array();
				$color_nm = explode(',', $sku['COLOR_NM']);
				$size_nm = trim($sku['SIZE_NM']);

				if($size_nm == 'F') $size_nm = 'FREE';
				if(count($color_nm) > 1) {
					foreach($color_nm as $key => $val) {
						$opno = $pno.addZero($_seq1[$key], 2);
						$val2 = str_replace(' ', '', trim($val));
						$item_no = $this->pdo->row("select no from $tbl[product_option_item] where pno='$pno' and opno='$opno' and (iname='$val' or iname='$val2')");
						if($item_no) $opts[] = $item_no;
						else {
							$opts[] = 'x';
						}
					}
				} else {
					$color_nm[0] = str_replace(' ', '', trim($color_nm[0]));
					$size_nm = str_replace(' ', '', $size_nm);
					$o1 = $this->pdo->row("select no from $tbl[product_option_item] where pno='$pno' and opno='{$pno}01' and replace(iname, ' ', '')='$color_nm[0]'");
					if(!$o1) {
						$_color_nm[0] = preg_replace("/(".$_color_nm[0].")\([^\)+]+\)/i", "$1", $color_nm[0]);
						if($_color_nm[0] == '챠콜') $_color_nm[0] = '차콜';
						$o1 = $this->pdo->row("select no from $tbl[product_option_item] where pno='$pno' and opno='{$pno}01' and replace(iname, ' ', '')='$_color_nm[0]'");
					}
					$opts[] = $o1;
					if($size_nm != 'ZZ') {
						$o2 = $this->pdo->row("select no from $tbl[product_option_item] where pno='$pno' and opno='{$pno}02' and replace(iname, ' ', '')='$size_nm'");
						if(!$o2) {
							$size_nm = (strpos($size_nm, '~')) ? str_replace('~', '-', $size_nm) : str_replace('-', '~', $size_nm);
							$o2 = $this->pdo->row("select no from $tbl[product_option_item] where pno='$pno' and opno='{$pno}02' and iname='$size_nm'");
						}
						if(!$o2 && ($size_nm == 'FREE' || $size_nm === 'F' || $size_nm == 'FREE3')) {
							if($size_nm == 'FREE3') $size_NM = 'F';
							$size_nm = $size_nm == 'FREE' ? 'F' : 'FREE';
							$o2 = $this->pdo->row("select no from $tbl[product_option_item] where pno='$pno' and opno='{$pno}02' and iname='$size_nm'");
						}
						$opts[] = $o2;
					}
				}
				if($sku['STOCK_QTY'] == '-9999') $sku['STOCK_QTY'] = 0;

				$complex_no = createComplex($pno, $opts, $sku['SKU'], $sku['STOCK_QTY'], 'dooson 에서 생성', $sku['SOLDOUT_YN'], $sku);
			}

			return $complex_no;
		}

		public function setProduct($pno, $ori_stat = null) { // abstract method
			global $tbl;

			$prd = $this->pdo->assoc("select updir, upfile1, upfile2, upfile3, content2, code, stat from $tbl[product] where no='$pno'");
			$prd = $this->convertToUTF8($prd);
			for($i = 1; $i <= 3; $i++) {
				if($prd['upfile'.$i]) ${'upfile'.$i} = getFileDir($prd['updir']).'/'.$prd['updir'].'/'.$prd['upfile'.$i];
			}
			$content2 = trim(stripslashes($prd['content2']));
			if(!$content2) $content2 = ' ';

			if(!$this->db) $this->connect();

			$this->db->query("
				update {$this->owner}.TPL_MALLSTYLEBLNCINFO
					set IMAGE_PATH='$upfile2', Image_Path_01='$upfile1', Image_Path_02='$upfile2', Image_Path_03='$upfile3', Commodity_DetDesc=EMPTY_CLOB()
					, MODIFYDT=TO_CHAR(SYSDATE, 'YYYYMMDDHH24MISS')
					, MODIFYUSER='$this->register'
					, MODIFYPGMID='set_product'
					, MODIFYIP='$_SERVER[REMOTE_ADDR]'
				where CORP_CD='$this->corp_cd' and STYLE_CD='$prd[code]' RETURNING Commodity_DetDesc INTO :clob
			", $content2);
		}

		public function getStock($skun = null) { // abstract method
			global $tbl;

			if(!$this->db) $this->connect();

			$w = ($skun) ? " and SKU='$skun'" : " and STOCK_YN='Y'";

			$skus = array();
			$res = $this->db->query("select * from {$this->owner}.TEB_SKU where CORP_CD='$this->corp_cd' $w");
			while($sku = oci_fetch_assoc($res)) {
				$is_prd = $this->pdo->row("select code from $tbl[product] where code='$sku[STYLE_CD]'");
				if(!$is_prd) continue;

				$complex = $this->pdo->assoc("select pno from erp_complex_option where barcode='$sku[SKU]'");

				if($sku['STOCK_QTY'] == '-9999') $sku['STOCK_QTY'] = 0;
				$this->createWingpos($sku, $complex['pno']);

				$skus[] = $sku['STYLE_CD'];

				if(!$style_cd) {
					$this->db->query("
						update {$this->owner}.TEB_SKU set
							STOCK_YN='N'
							, SEND_DT=TO_CHAR(SYSDATE, 'YYYYMMDDHH24MISS')
							, MODIFYDT=TO_CHAR(SYSDATE, 'YYYYMMDDHH24MISS')
							, MODIFYUSER='$this->register'
							, MODIFYPGMID='get_stock'
							, MODIFYIP='$_SERVER[REMOTE_ADDR]'
						where CORP_CD='$this->corp_cd' and SKU='$sku[SKU]'
					");
				}
			}

			$skus = array_unique($skus);
			foreach($skus as $style_cd) {
				$is_qty = 0;
				$prd = $this->pdo->assoc("select no, stat from $tbl[product] where code='$style_cd' and wm_sc=0");
				if(!$prd['no']) continue;

				$res = $this->pdo->iterator("select qty, force_soldout from erp_complex_option where pno='$prd[no]' and del_yn='N'");
                foreach ($res as $data) {
					if($data['force_soldout'] == 'N') $is_qty++;
					if($data['force_soldout'] == 'L' && $data['qty'] > 0) $is_qty++;
				}

				if($prd['stat'] == 3 && $is_qty > 0) {
					$this->pdo->query("update $tbl[product] set stat=2 where no='$prd[no]' or wm_sc='$prd[no]'");
					prdStatLogw($prd[no], 2, 3);
				}

				if($prd['stat'] == 2 && $is_qty < 1) {
					$this->pdo->query("update $tbl[product] set stat=3 where no='$prd[no]' or wm_sc='$prd[no]'");
					prdStatLogw($prd[no], 3, 2);
				}
			}
		}


		/* +----------------------------------------------------------------------------------------------+
		' |  회원
		' +----------------------------------------------------------------------------------------------+*/
		public function setChangedMember($member_id = null, $member_no = null) { // abstract method
			global $tbl;

			if(!$this->db) $this->connect();
			if(!$member_id && !$member_no) return false;

			$w = '';
			if($member_id) $w .= " and member_id='$member_id'";
			if($member_no) $w .= " and no='$member_no'";

			$res = $this->pdo->iterator("select * from $tbl[member] where 1 $w");
            foreach ($res as $data) {
				$data = $this->convertToUTF8($data);
				$data['member_id'] = $this->setMemberPrefix($data['member_id']);

				if(!$data['sms']) $data['sms'] = 'N';

				$vals['CORP_CD'] = $this->corp_cd;
				$vals['MEMBER_NO'] = $data['no'];
				$vals['MEMBER_ID'] = $data['member_id'];
				$vals['PWD'] = $data['pwd'];
				$vals['MEMBER_NAME'] = $data['name'];
				$vals['EMAIL'] = $data['email'];
				$vals['PHONE'] = $data['phone'];
				$vals['CELL'] = $data['cell'];
				$vals['ZIP'] = numberOnly($data['zip']);
				$vals['ADDR1'] = $data['addr1'];
				$vals['ADDR2'] = $data['addr2'];
				$vals['MAILING'] = $data['mailing'];
				$vals['SMS'] = $data['sms'];
				$vals['LEVEL_ID'] = $data['level'];
				$vals['IP'] = $data['ip'];
				$vals['REG_DATE'] = $data['reg_date'];
				$vals['LAST_CON'] = $data['last_con'];
				$vals['TOTAL_CON'] = $data['total_con'];
				$vals['TOTAL_ORD'] = $data['total_ord'];
				$vals['TOTAL_PRC'] = $data['total_prc'];
				if($data['withdraw'] == "D2") {
					$milage = $this->pdo->row("select `milage` from $tbl[member_deleted] where `no`='$data[no]'");
					$vals['MILAGE'] = $milage;
				} else {
				   $vals['MILAGE'] = $data['milage'];
				}

				$vals['EMONEY'] = $data['emoney'];
				$vals['WITHDRAW'] = ($data['withdraw'] == 'N') ? 'N' : 'Y';
				$vals['BIRTH'] = $data['birth'];
				$vals['BIRTH_TYPE'] = $data['birth_type'];
				$vals['GENDER'] = $data['sex'];
				$vals['NICK'] = $data['nick'];
				$vals['MNG_MEMO'] = $data['mng_memo'];
				$vals['BLACKLIST'] = $data['blacklist'];
				$vals['SEND_YN'] = 'N';
				$vals['SEND_DT'] = 'NULL';
				$vals['PROPERTY_01'] = 1;
				/* 불필요필드 */
				$vals['RECOM_MEMBER'] = $data['recom_member'];
				$vals['JOIN_REF'] = 'NULL';
				$vals['POINTAMT'] = $vals['POINTAMT_01'] = $vals['POINTAMT_02'] = $vals['POINTAMT_03'] = $vals['POINTAMT_04'] = '0';
				$vals['CONVERSION'] = 'NULL';
				$vals['REG_EMAIL'] = $vals['REG_SMS'] = $vals['REG_CODE'] = 'N';
				$vals['NATIONS'] = 'NULL';
				$vals['JUMIN'] = '000000-0000000';
				$vals['PROPERTY_02'] = $data['withdraw'] == 'D2' ? 'Y' : '';
				if($this->brand_nm) {
					$vals['LANG_CD'] = ($this->lang_cd) ? $this->lang_cd : 'KOR01';
					$vals['BRAND_NM'] = (is_array($this->brand_nm)) ? $this->brand_nm[0] : $this->brand_nm;
				}
				if($this->memprefix) $vals['REGISTPGMID'] = $this->memprefix;
				$vals['MEM_TYPE'] = 1; // 국내, 해외회원 여부
				if($_SESSION['dooson_cust']['CUST_ID']) {
					$vals['PROPERTY_06'] = $_SESSION['dooson_cust']['CUST_ID'];
				}
				if($this->regist_shpcd) {
					$vals['PROPERTY_09'] = $this->regist_shpcd;
				}

				$exists = $this->db->row("select WARE_RECID from {$this->owner}.TEB_MEMBER where MEMBER_ID='$data[member_id]'");
				foreach($vals as $key => $val) {
					if($val === '' || is_null($val)) $vals[$key] = ' ';
				}

				// 수정쿼리 생성
				if($exists > 0) {
					$qry = $this->makeQuery("{$this->owner}.TEB_MEMBER", $vals, "CORP_CD='$this->corp_cd' and WARE_RECID='$exists'");
				} else {
					$vals['WARE_RECID'] = "{$this->owner}.Seq_CM_TempBillID.NextVal";
					$vals['REGISTUSER'] = $this->register;
					$qry = $this->makeQuery("{$this->owner}.TEB_MEMBER", $vals);
				}
				$this->db->query($qry);
			}
		}

		public function getChangedMember() { // 두손 서버에서 회원 정보를 업데이트
			global $tbl;

			if(!$this->db) $this->connect();

			$res = $this->db->query("select * from {$this->owner}.TEB_MEMBER where CORP_CD='$this->corp_cd' and SEND_YN='N' and PROPERTY_01='3'");
			while($data = oci_fetch_assoc($res)) {
				$data = $this->convertUTF8($data);
				$data['MEMBER_ID'] = $this->setMemberPrefix($data['MEMBER_ID'], false);

				$vals['name'] = $data['MEMBER_NAME'];
				$vals['email'] = $data['EMAIL'];
				$vals['phone'] = $data['PHONE'];
				$vals['cell'] = $data['CELL'];
				$vals['zip'] = $data['ZIP'];
				$vals['addr1'] = $data['ADDR1'];
				$vals['addr2'] = $data['ADDR2'];
				$vals['mailing'] = $data['MAILING'];
				$vals['sms'] = $data['SMS'];
				$vals['level'] = $data['LEVEL_ID'];
				$vals['milage'] = $data['MILAGE'];
				$vals['emoney'] = $data['EMONEY'];
				$vals['nick'] = $data['NICK'];
				$vals['blacklist'] = $data['BLACKLIST'];

				$this->pdo->query(
					$this->makeQuery($tbl['member'], $vals, "member_id='$data[MEMBER_ID]'")
				);

                if(is_null($this->pdo->getError()) == true) {
					$this->db->query("
						update {$this->owner}.TEB_MEMBER set
							SEND_YN='Y',
							SEND_DT=TO_CHAR(SYSDATE, 'YYYYMMDDHH24MISS'),
							PROPERTY_01='4'
						where WARE_RECID='$data[WARE_RECID]' and CORP_ID='$this->corp_id'
					");
				}
			}
		}


		/* +----------------------------------------------------------------------------------------------+
		' |  적립금
		' +----------------------------------------------------------------------------------------------+*/
		public function setMilage($param, $type = 'milage') { // abstract method
			if(!$this->db) $this->connect();

			$param = $this->convertToUTF8($param);
			$param['title'] = addslashes($param['title']);
			$param['member_id'] = $this->setMemberPrefix($param['member_id']);
			if(!$param['admin_id']) $param['admin_id'] = 'system';
			$ip = $_SERVER['REMOTE_ADDR'];

			$etbl = $type == 'milage' ? "{$this->owner}.TEB_MILAGE" : "{$this->owner}.TEB_EMONEY";
			$now_milage = $type == 'milage' ? 'MEMBER_MILAGE' : 'MEMBER_EMONEY';

			$this->db->query("
				insert into $etbl (
					CORP_CD, WARE_RECID, MEMBER_NO, MEMBER_ID, MEMBER_NAME, TITLE,
					AMOUNT, CTYPE, MTYPE, $now_milage, REG_DATE, ADMIN_ID, PROPERTY_02,
					SEND_DT, SEND_YN, PROPERTY_01, REGISTDT, REGISTUSER, REGISTPGMID, REGISTIP
				) values (
					'$this->corp_cd', {$this->owner}.Seq_CM_TempBillID.NextVal, '$param[member_no]', '$param[member_id]', '$param[member_name]', '$param[title]',
					'$param[amount]', '$param[ctype]', '$param[mtype]', '$param[member_milage]', '$param[reg_date]', '$param[admin_id]', '$param[ono]',
					NULL, 'N', '1', TO_CHAR(SYSDATE, 'YYYYMMDDHH24MISS'), '$this->register', '$this->pgm_id', '$ip'
				)
			");
			$this->setChangedMember('', $param['member_no']);
		}

		public function getMilage($type='milage') { // 두손 서버의 적립금/예치금 내역 가져오기
			global $tbl;

			if(!$this->db) $this->connect();

			if($type == 'milage') {
				$etbl = "{$this->owner}.TEB_MILAGE";
				$etbl2 = $tbl['milage'];
				$now_milage = 'MEMBER_MILAGE';
			} else {
				$etbl = "{$this->owner}.TEB_EMONEY";
				$etbl2 = $tbl['emoney'];
				$now_milage = 'MEMBER_EMONEY';
			}

			$res = $this->db->query("select * from $etbl where CORP_CD='$this->corp_cd' and SEND_YN='N' and PROPERTY_01='3'");
			while($data = oci_fetch_assoc($res)) {
				$data = convertUTF8($data);
				$data['MEMBER_ID'] = $this->setMemberPrefix($param['MEMBER_ID'], true);

				$this->pdo->query("
					insert into $tbl[$type] (member_no, member_id, member_name, title, amount, ctype, mtype, member_$type, reg_date)
					values ($data[MEMBER_NO], '$data[MEMBER_ID]', '$data[MEMBER_NAME]', '$data[TITLE]', '$data[AMOUNT]', '$data[CTYPE]', '$data[MTYPE]', '$data[$now_milage]', '$now')
				");

				$this->db->query("update $etbl set SEND_YN='Y' and SEND_DT=TO_CHAR(SYSDATE, 'YYYYMMDDHH24MISS'), PROPERTY_01='4' where CORP_CD='$this->corp_cd' and WARE_RECID='$data[WARE_RECID]'");
			}
		}


		/* +----------------------------------------------------------------------------------------------+
		' |  쿠폰
		' +----------------------------------------------------------------------------------------------+*/
		public function setCoupon($cno) { // abstract method
			global $tbl;

			if(!$this->db) $this->connect();

			$cpn = $this->pdo->assoc("select * from $tbl[coupon_download] where no='$cno' and place!='online' and stype=1");
			if(!$cpn['no']) return false;
			$cpn = $this->convertToUTF8($cpn);

			$vals['CORP_CD'] = $this->corp_cd;
			$vals['MEMBER_NO'] = $cpn['member_no'];
			$vals['MEMBER_ID'] = $this->setMemberPrefix($cpn['member_id']);
			$vals['MEMBER_NAME'] = $cpn['member_name'];
			$vals['CNO'] = $cpn['cno'];
			$vals['CODE'] = $cpn['no'];
			$vals['COUPON_NM'] = addslashes($cpn['name']);
			$vals['SALE_PRC'] = $cpn['sale_prc'];
			$vals['PRC_LIMIT'] = $cpn['prc_limit'];
			$vals['SALE_LIMIT'] = $cpn['sale_limit'];
			$vals['UDATE_TYPE'] = $cpn['udate_type'];
			$vals['USTART_DATE'] = ($cpn['ustart_date'] == '0000-00-00 00:00:00') ? date('Y-m-d') : date('Y-m-d', strtotime($cpn['ustart_date']));
			$vals['UFINISH_DATE'] = ($cpn['ufinish_date'] == '0000-00-00 00:00:00') ? date('Y-m-d') : date('Y-m-d', strtotime($cpn['ufinish_date']));
			if($cpn['udate_type'] == 1) {
				$vals['UFINISH_DATE'] = '29991231';
			}
			if($cpn['udate_type'] == 3) {
				$vals['USTART_DATE'] = date('Y-m-d');
			}
			$vals['SALE_TYPE'] = $cpn['sale_type'];
			$vals['IS_TYPE'] = $cpn['is_type'];
			$vals['PLACE'] = $cpn['place'];
			$vals['DOWN_DATE'] = $cpn['down_date'];
			$vals['USE_DATE'] = $cpn['use_date'];
			$vals['ONO'] = $cpn['ono'];
			$vals['STYPE'] = $cpn['stype'];
			$vals['AUTO_CPN'] = $cpn['auto_cpn'];
			$vals['AUTH_CODE'] = $cpn['auth_code'];
			$vals['USE_LIMIT'] = $cpn['use_limit'] > 0 ? $cpn['use_limit'] : 0;
			$vals['PAY_TYPE'] = $cpn['pay_type'];
			$vals['DEVICE'] = $cpn['device'];
			$vals['SEND_YN'] = 'N';
			//$vals['SEND_DT'] = "TO_CHAR(SYSDATE, 'YYYYMMDDHH24MISS')";
			$vals['SEND_DT'] = "NULL";
			$vals['PROPERTY_01'] = '1';
			$vals['PROPERTY_02'] = $cpn['code'];
			$vals['PROPERTY_03'] = $cpn['weeks'];

			$WARE_RECID = $this->db->row("select WARE_RECID from {$this->owner}.TEB_COUPON_DOWNLOAD where CORP_CD='$this->corp_cd' and CODE='$cno'");
			if($WARE_RECID > 0) { // 수정
				$vals['MODIFYDT'] = "TO_CHAR(SYSDATE, 'YYYYMMDDHH24MISS')";
				$vals['MODIFYUSER'] = $this->pgm_id;
				$vals['MODIFYPGMID'] = $this->pgm_id;
				$vals['MODIFYIP'] = $_SERVER['REMOTE_ADDR'];

				$qry = $this->makeQuery("{$this->owner}.TEB_COUPON_DOWNLOAD", $vals, "CORP_CD='$this->corp_cd' and WARE_RECID='$WARE_RECID'");
			} else { // 신규
				$vals['WARE_RECID'] = "{$this->owner}.Seq_CM_TempBillID.NextVal";
				$vals['REGISTDT'] = "TO_CHAR(SYSDATE, 'YYYYMMDDHH24MISS')";
				$vals['REGISTUSER'] = $this->pgm_id;
				$vals['REGISTPGMID'] = $this->pgm_id;
				$vals['REGISTIP'] = $_SERVER['REMOTE_ADDR'];

				$qry = $this->makeQuery("{$this->owner}.TEB_COUPON_DOWNLOAD", $vals);
			}
			$this->db->query($qry);

			return true;
		}

		function removeCoupon($cno) { // abstract method
			if(!$this->db) $this->connect();

			if(!$cno) return false;

			$ufinish_date = date('Y-m-d', strtotime('-1 days'));

			$WARE_RECID = $this->db->row("select WARE_RECID from {$this->owner}.TEB_COUPON_DOWNLOAD where CORP_CD='$this->corp_cd' and CODE='$cno'");
			if($WARE_RECID) {
				$this->db->query("
					update {$this->owner}.TEB_COUPON_DOWNLOAD set
						PLACE='D', UFINISH_DATE='$ufinish_date', PROPERTY_01='1', SEND_DT=NULL, SEND_YN='N',
						MODIFYDT=TO_CHAR(SYSDATE, 'YYYYMMDDHH24MISS'), MODIFYUSER='$this->pgm_id', MODIFYPGMID='$this->pgm_id', MODIFYIP='$_SERVER[REMOTE_ADDR]'
					where WARE_RECID='$WARE_RECID'
				");
			}

		}


		/* +----------------------------------------------------------------------------------------------+
		' |  주문
		' +----------------------------------------------------------------------------------------------+*/
		function setOrder($ono = null) { // abstract method
			global $tbl, $cfg, $_pay_type;

			if(!$this->db) $this->connect();

			if($ono) $w .= " and ono='$ono'";

			$res = $this->pdo->iterator("select * from $tbl[order] where stat not in (11,31) $w");
            foreach ($res as $data) {
				$data = $this->convertToUTF8($data);

				$data['bank'] = str_replace('-네이버체크아웃결제', '-NPay', $data['bank']);
				$bank = explode(' ', $data['bank']);
				$card_tbl = $data['pay_type'] == 4 ? $tbl['vbank'] : $tbl['card'];
				$card = $this->pdo->assoc("select tno from $card_tbl where wm_ono='$data[ono]' and stat not in (11, 31)");
				$card = $this->convertToUTF8($card);

				$pay_method = explode(' ', $this->convertToUTF8($_pay_type[$data['pay_type']]));

				$dsord = $this->db->assoc("select ORDER_ID, LANG_CD from {$this->owner}.TEB_ORDERS where CORP_CD='$this->corp_cd' and ORDER_ID='$data[ono]' and LANG_CD='$this->lang_cd'");
				if(!$dsord['LANG_CD']) {
					$dsord['LANG_CD'] = $this->lang_cd;
				}

				$vals['CORP_CD'] = $this->corp_cd;
				$vals['ORDER_ID'] = $data['ono'];
				$vals['LANG_CD'] = $dsord['LANG_CD'] ? $dsord['LANG_CD'] : 'KOR01';
				$vals['MEMBER_ID'] = $this->setMemberPrefix($data['member_id']);
				$vals['O_NAME'] = $data['buyer_name'];
				$vals['O_PHONE1'] = $data['buyer_cell'];
				$vals['O_PHONE2'] = $data['buyer_phone'];
				$vals['O_EMAL'] = $data['buyer_email'];
				$vals['O_MESSAGE'] = $data['dlv_memo'];
				$vals['R_NAME'] = $data['addressee_name'];
				$vals['R_ZIPCODE'] = $data['addressee_zip'];
				$vals['R_ADDR1'] = $data['addressee_addr1'];
				$vals['R_ADDR2'] = $data['addressee_addr2'];
				$vals['R_PHONE1'] = $data['addressee_cell'];
				$vals['R_PHONE2'] = $data['addressee_phone'];
				$vals['MILEAGE_USED'] = $data['milage_prc'];
				if($data['milage_down_date']) $vals['MILEAGE_GEN_DATE'] = date('y-m-d H:i:s', $data['milage_down_date']);
				$vals['PAY_DATE'] = $data['date2'] > 0 ? date('y-m-d H:i:s', $data['date2']): '';
				$vals['PAYMETHOD'] = $pay_method[0];
				$vals['P_NAME'] = $data['bank_name'];
				$vals['BANK_CODE'] = $bank[0];
				$vals['BANK_ACC_NO'] = $bank[1];
				$vals['BANK_DEPOSITOR'] = $bank[2];
				$vals['TID'] = $card['tno'];
				$vals['CARD_CANCELED'] = $card['stat'] == 3 ? 'T' : 'F';
				$vals['CARD_CANCEL_DATE'] = null;
				$vals['AUTH_CODE'] = $card['app_no'];
				$vals['IS_PAYED'] = ($data['stat'] > 1 && $data['stat'] < 10) ? 'T' : 'F';
				$vals['PAYED_AMOUNT'] = ($vals['IS_PAYED'] == 'T') ? $data['pay_prc'] : 0;
				$vals['SHIP_FEE'] = $data['dlv_prc'];
				$vals['IS_CANCELED'] = ($data['stat'] == 13 || $data['stat'] == 33) ? 'T' : 'F';
				$vals['IS_EXCHANGED'] = $data['stat'] == 15 ? 'T' : 'F';
				$vals['IS_SHIPED'] = ($data['stat'] == 4 || $data['stat'] == 5) ? 'T' : 'F';
				$vals['ORDER_DATE'] = date('y-m-d H:i:s', $data['date1']);
				$vals['CLIENT_IP'] = $data['ip'];
				$vals['PG_NAME'] = $cfg['card_pg'];
				$vals['ESCROW_TID'] = $card['tno'];
				$vals['USE_COUPON_NO'] = $this->pdo->row("select no from $tbl[coupon_download] where ono='$data[ono]'");
				$vals['COUPON_PRICE'] = $data['sale5'];
				$vals['MEMBER_GROUP_NO'] = $data['sale4'] > 0 ? $this->pdo->row("select level from $tbl[member] where no='$data[member_no]'") : 0;
				$vals['MENBER_GROUP_PRICE'] = $data['sale4'];

				if($dsord['ORDER_ID']) { // 수정
					$vals['MODIFYDT'] = "TO_CHAR(SYSDATE, 'YYYYMMDDHH24MISS')";
					$vals['MODIFYUSER'] = $this->register;
					$vals['MODIFYPGMID'] = $this->pgm_id;
					$vals['MODIFYIP'] = $_SERVER['REMOTE_ADDR'];

					$qry = $this->makeQuery("{$this->owner}.TEB_ORDERS", $vals, "CORP_CD='$this->corp_cd' and ORDER_ID='$data[ono]' and LANG_CD='$vals[LANG_CD]'");
					$this->db->query($qry);
				} else { // 신규
					$vals['PROPERTY_01'] = date('Ymd', $data['date1']);
					$vals['REGISTDT'] = "TO_CHAR(SYSDATE, 'YYYYMMDDHH24MISS')";
					$vals['REGISTUSER'] = $this->register;
					$vals['REGISTPGMID'] = $this->pgm_id;
					$vals['REGISTIP'] = $_SERVER['REMOTE_ADDR'];

					$this->db->query($this->makeQuery("{$this->owner}.TEB_ORDERS", $vals));
				}
				$this->setOrderProduct($data);
				$this->setOrderDelivery($data);
			}
		}

		function setOrderProduct($ord) {
			global $tbl, $cfg, $_cache_catename;

			if(!$this->db) $this->connect();

			if(!is_array($_cache_catename)) { // 카테고리명 캐시
				$_cache_catename = array();
				$res = $this->pdo->iterator("select no, name from $tbl[category] where ctype=1");
                foreach ($res as $data) {
					$_cache_catename[$data['no']] = stripslashes($data['name']);
				}
				$_cache_catename = $this->convertToUTF8($_cache_catename);
			}

			$ono = $ord['ono'];
			$fd = getOrderSalesField(',p', '');
			$res = $this->pdo->iterator("
				select
					p.no, p.ono, p.complex_no, p.`option`, p.option_prc, p.sell_prc, p.pno, p.dooson_pno, p.dlv_hold, p.pno, o.x_order_id, p.buy_ea $fd
				from $tbl[order_product] p inner join $tbl[order] o using(ono) where ono='$ono'
			");
            foreach ($res as $data) {
				$data = $this->convertToUTF8($data);
				$prd = 	$this->pdo->assoc("select p.no, p.big, p.name, p.code, p.normal_prc from $tbl[product] p where p.no='$data[pno]'");
				if($prd['no']) {
					$data = array_merge($prd, $data);
				}

				// 옵션 추가금액
				$option_prc = 0;
				$_tmp = explode('<split_big>', $data['option_prc']);
				foreach($_tmp as $val) {
					$val = explode('<split_small>', $val);
					$option_prc += $val[1];
				}

				$vals['CORP_CD'] = $this->corp_cd;
				$vals['ORDER_ID'] = $data['ono'];
				$vals['PRODUCT_NO'] = $data['ono'].'-'.$data['no'];

				if($data['dooson_pno']) $vals['PRODUCT_NO'] = $data['dooson_pno'];

				$op = $this->db->assoc("select * from {$this->owner}.TEB_ORDER_PRODUCT where CORP_CD='$this->corp_cd' and PRODUCT_NO='$vals[PRODUCT_NO]'");

				$erp = $this->pdo->assoc("select barcode, sku, color_cd, size_cd from erp_complex_option where complex_no='$data[complex_no]'");
				if($erp['sku']) {
					$vals['OPT_ID'] = $erp['sku'];
				} else if($erp['barcode']) {
					$vals['OPT_ID'] = $erp['barcode'];
				}
				$vals['COLOR_CD'] = $erp['color_cd'];
				$vals['SIZE_CD'] = $erp['size_cd'];

				if(!$op['LANG_CD']) {
					$op['LANG_CD'] = $this->lang_cd;
				}
				$vals['LANG_CD'] = $op['LANG_CD'] ? $op['LANG_CD'] : 'KOR01';
				if($data['x_order_id'] == 'OFF') $vals['LANG_CD'] = 'OFF';
				if($data['x_order_id'] == 'OPN') $vals['LANG_CD'] = 'OPN';
				$vals['MAIN_CATE_NAME'] = $_cache_catename[$data['big']];
				$vals['OPT_STR'] = str_replace('<split_small>', ' : ',str_replace('<split_big>', ' / ', $data['option']));
				$vals['OPT_PRICE'] = $option_prc;
				$vals['PRODUCT_NAME'] = strip_tags($data['name']);
				$vals['PRODUCT_CODE'] = $data['code'];
				$vals['PRODUCT_BUY'] = $data['normal_prc'];
				$vals['PRODUCT_PRICE'] = $data['sell_prc']-(getOrderTotalSalePrc($data)/$data['buy_ea']);
				$vals['MILEAGE_GENERATE'] = $ord['milage_down_date'] > 0 ? $data['total_milage'] : 0;
				$vals['MILEAGE_GEN_DATE'] = $ord['milage_down_date'] > 0 ? date('y-m-d H:i:s', $ord['milage_down_date']): '';
				$vals['MAIN_CATE_NO'] = $data['big'];
				$vals['STYLE_CD'] = $data['code'];
				$vals['OUT_HOLD'] = $data['dlv_hold'] == 'Y' ? 'T' : 'F';

				if(!$op['ORDER_ID']) {
					$vals['REGISTDT'] = "TO_CHAR(SYSDATE, 'YYYYMMDDHH24MISS')";
					$vals['REGISTUSER'] = $this->register;
					$vals['REGISTPGMID'] = $this->pgm_id;
					$vals['REGISTIP'] = $_SERVER['REMOTE_ADDR'];

					$qry = $this->makeQuery("{$this->owner}.TEB_ORDER_PRODUCT", $vals);
					$this->db->query($qry);

					$this->pdo->query("update $tbl[order_product] set dooson_pno='$vals[PRODUCT_NO]' where no='$data[no]'");
				} else {

					$vals['MODIFYDT'] = "TO_CHAR(SYSDATE, 'YYYYMMDDHH24MISS')";
					$vals['MODIFYUSER'] = $this->register;
					$vals['MODIFYPGMID'] = $this->pgm_id;
					$vals['MODIFYIP'] = $_SERVER['REMOTE_ADDR'];
					$qry = $this->makeQuery("{$this->owner}.TEB_ORDER_PRODUCT", $vals, "CORP_CD='$this->corp_cd' and PRODUCT_NO='$vals[PRODUCT_NO]' and OPT_ID='$vals[OPT_ID]'");
					$this->db->query($qry);
				}
			}
		}

		function setOrderDelivery($ord) {
			global $tbl, $cfg, $_ord_stat;

			if(!$this->db) $this->connect();

			if(!is_array($ord)) $ord = $this->pdo->assoc("select * from $tbl[order] where ono='$ord'");
			if($ord['ono']) $w .= " and ono='$ord[ono]'";

			$res = $this->pdo->iterator("select * from $tbl[order_product] where complex_no > 0 $w");
            foreach ($res as $data) {
				$product_no = $data['ono'].'-'.$data['no'];
				if($data['dooson_pno']) $product_no = $data['dooson_pno'];
				$action = 'O';

				/*
				O  재고할당
				N1 배송준비중 N2 교환상품 D 배송중 F 배송완료 E 반품 C1 입금전취소 C2 배송전취소
				E1 동일상품교환 E2 타상품교환 F2 취소완료-환불완료 O 기타
				*/
				switch($data['stat']) {
					case 1 :
						$action = 'R';
						$_status = '미결제';
					break;
					case 2 :
					case 20 :
						$action = 'N0';
						$_status = '배송준비중';
					break;
					case 3 :
						if($data['ex_pno']) {
							$action = 'N2';
							$_status = '교환상품';
						} else {
							$action = 'N1';
							$_status = '상품준비중';
						}
						if($data['etc2'] == 'N3') {
							$action = 'N3';
							$_status = '본사고객배송';
						}
						if($data['etc2'] == 'N4') {
							$action = 'N4';
							$_status = '본사매장발송';
						}
					break;
					case 4 :
						$action = 'D';
						$_status = '배송중';
					break;
					case 5 :
						$action = 'F';
						$_status = '배송완료';
					break;
					case 6 :
						$action = 'F';
						$_status = '거래완료';
					break;
					case 12 :
					case 14 :
						if($ord['date2'] > 0) {
							$action = 'C2';
							$_status = '배송전취소';
						} else {
							$action = 'C1';
							$_status = '입금전취소';
						}
					break;
					case 13 :
							$action = 'F2';
							$_status = '취소완료';
					break;
					case 15 :
							$action = 'F2';
							$_status = '환불완료';
					break;
					case 16 :
						$action = 'E';
						$_status = '반품대기';
					break;
					case 17 :
					case 19 :
					case 22 :
					case 23 :
					case 24 :
					case 25 :
					case 26 :
						$action = 'F1';
						$_status = '반품환불완료';
					break;
					case 11 :
					case 31 :
						$action = 'R2';
						$_status = '카드승인대기';
					break;
				}

				if(($action == 'N0' || $action == 'N1' || $action == 'N2') && $data['print'] > 0) {
					//$action = 'P';
				}

				if(!$_status) $_status = '기타';
				$_status = $this->convertToUTF8($_status);

				if(!$action) return false;
				$data = $this->convertToUTF8($data);
				$erp = $this->pdo->assoc("select barcode, sku, color_cd, size_cd from erp_complex_option where complex_no='$data[complex_no]'");
				$om = $this->db->assoc("select LANG_CD, PRODUCT_NO, ACTION, PROPERTY_01, OM_NO from {$this->owner}.TEB_ORDER_MANAGE where CORP_CD='$this->corp_cd' and PRODUCT_NO='$product_no'");
				//if($om['ACTION'] == $action) continue;
				// if($om['PROPERTY_01'] == '4') continue;
                if (($om['ACTION'] == 'S5R1' || $om['ACTION'] == 'S5R2') && $data['stat'] < 4) continue;
				if(!$om['LANG_CD']) {
					$om['LANG_CD'] = $this->lang_cd;
				}

				$vals = array();
				$vals['CORP_CD'] = $this->corp_cd;
				$vals['OM_NO'] = $om['OM_NO'] ? $om['OM_NO'] : $data['no'];
				$vals['ORDER_ID'] = $data['ono'];
				$vals['PRODUCT_NO'] = $product_no;
				if($erp['sku']) $vals['OPT_ID'] = $erp['sku'];
				else if($erp['barcode']) $vals['OPT_ID'] = $erp['barcode'];
				$vals['LANG_CD'] = $om['LANG_CD'] ? $om['LANG_CD'] : 'KOR01';
				if($ord['x_order_id'] == 'OFF') $vals['LANG_CD'] = 'OFF';
				if($ord['x_order_id'] == 'OPN') $vals['LANG_CD'] = 'OPN';
				$vals['QUANTITY'] = $data['buy_ea'];
				$vals['ACTION'] = $action;
				$vals['PLACE_DATE'] = ($ord['date2'] > 0) ? date('y-m-d H:i:s', $ord['date2']) : '';
				$vals['RETURN_DATE'] = ($data['stat'] == 17 || $data['stat'] == 19) ? date('y-m-d H:i:s', $data['repay_date']) : '';
				$vals['CANCEL_DATE'] = ($data['stat'] == 13 || $data['stat'] == 15) ? date('y-m-d H:i:s', $data['repay_date']) : '';
				$vals['SHIP_FEE'] = $ord['dlv_prc'];
				/*
				//$vals['SC_ID'] = $this->getDlvCode($data['dlv_no']);
				$vals['INVOICE_NO'] = $data['dlv_code'];
				*/
				$vals['PRODUCT_CODE'] = 'Trans';
				$vals['PRODUCT_NAME'] = strip_tags($data['name']);
				$vals['CD_MODE'] = $om['PRODUCT_NO'] ? 'U' : 'I';
				$vals['CD_FROM'] = 'W';
				$vals['DT_COMPLETE'] = 'NULL';
				$vals['SHIPPED_QUANTITY'] = ($data['stat'] < 10) ? $data['buy_ea'] : 0;
				$vals['CANCEL_QUANTITY'] = ($data['stat'] == 13) ? $data['buy_ea'] : 0;
				$vals['RETURN_QUANTITY'] = ($data['stat'] == 15) ? $data['buy_ea'] : 0;
				$vals['STATUS'] = $_status;
				$vals['IS_Main'] = 'F';
				$vals['R_NAME'] = $data['r_name'];
				$vals['R_ZIPCODE'] = $data['r_zip'];
				$vals['R_ADDR1'] = $data['r_addr1'];
				$vals['R_ADDR2'] = $data['r_addr2'];
				$vals['R_PHONE1'] = $data['r_cell'];
				$vals['R_PHONE2'] = $data['r_phone'];
				$vals['R_MESSAGE'] = $data['r_message'];

				// 입금완료, 상품준비중으로 바꾸려고 할때, 두손이 이미 배송중인 경우 변경 안함
				if(($om['ACTION'] == 'D' || $om['ACTION'] == 'F') && ($action == 'N0' || $action == 'N1')){
					continue;
				}

				if($vals['CD_MODE'] == 'I') {
					$vals['REGISTDT'] = "TO_CHAR(SYSDATE, 'YYYYMMDDHH24MISS')";
					$vals['REGISTUSER'] = $this->register;
					$vals['REGISTPGMID'] = $this->pgm_id;
					$vals['REGISTIP'] = $_SERVER['REMOTE_ADDR'];
					$qry = $this->makeQuery("{$this->owner}.TEB_ORDER_MANAGE", $vals);
				} else {
					unset($vals['PRODUCT_CODE']); // 2019-01-25 수정일 경우 해당 필드 업데이트 하지 않음

					$vals['MODIFYDT'] = "TO_CHAR(SYSDATE, 'YYYYMMDDHH24MISS')";
					$vals['MODIFYUSER'] = $this->register;
					$vals['MODIFYPGMID'] = $this->pgm_id;
					$vals['MODIFYIP'] = $_SERVER['REMOTE_ADDR'];
					$qry = $this->makeQuery("{$this->owner}.TEB_ORDER_MANAGE", $vals, "ORDER_ID='$data[ono]' and LANG_CD='$vals[LANG_CD]' and CORP_CD='$this->corp_cd' and PRODUCT_NO='$vals[PRODUCT_NO]' and OPT_ID='$vals[OPT_ID]'");

					$this->db->query("
						update {$this->owner}.TEB_ORDER_MANAGE set
							ACTION='$action', STATUS='$_status', CANCEL_DATE='$vals[CANCEL_DATE]', CD_FROM='W',
							MODIFYUSER='$vals[MODIFYUSER]', MODIFYPGMID='$vals[MODIFYPGMID]'
						where ORDER_ID='$data[ono]' and LANG_CD='$vals[LANG_CD]' and CORP_CD='$this->corp_cd' and PRODUCT_NO='$vals[PRODUCT_NO]' and OPT_ID!='$vals[OPT_ID]'
					");
				}

				$this->db->query($qry);
			}
		}

		function bankTimeout($ono) {
			if(!$this->db) $this->connect();

			$this->db->query(trim("
				update {$this->owner}.TEB_ORDER_MANAGE set
					ACTION='F2', STATUS='취소완료',
					MODIFYDT=TO_CHAR(SYSDATE, 'YYYYMMDDHH24MISS'), MODIFYUSER='$this->register',
					MODIFYPGMID='$this->pgm_id', MODIFYIP='$_SERVER[REMOTE_ADDR]'
				where ORDER_ID='$ono' and ACTION='R'
			"));
		}

		function getDelivery($ono = null) {
			global $engine_dir, $tbl, $cfg, $now, $sms_replace, $mail_title, $_mstr, $pdo;

			if(!$this->db) $this->connect();

			if($ono) $w .= " and o.ORDER_ID='$ono'";
			else {
				$w .= " and m.CD_MODE='M' and m.CD_FROM='D' and m.DT_COMPLETE IS null";
				if($this->lang_cd) {
					$get_lang_cd = "'$this->lang_cd'";
					if(is_array($this->dlv_lang_cd)) {
						foreach($this->dlv_lang_cd as $_lang_cd) {
							$get_lang_cd .= ",'$_lang_cd'";
						}
					}
					$w .= " and m.LANG_CD in ($get_lang_cd) and (m.LANG_CD != 'OFF' or o.PROPERTY_04='$this->lang_cd')";
				}
			}

			$ords = $dlv_title = array();
			$res = $this->db->query("
				select m.*
					from {$this->owner}.TEB_ORDER_MANAGE m inner join {$this->owner}.TEB_ORDERS o on m.ORDER_ID=o.ORDER_ID
				where
					m.CORP_CD='$this->corp_cd' $w
			");
			while($data = oci_fetch_assoc($res)) {
				$ono = $data['ORDER_ID'];
				$om_no = $data['OM_NO'];
				$product_no = $data['PRODUCT_NO'];
				$dlv_name = $this->getDlvName($data['SC_ID']);
				$dlv_no = $this->pdo->row("select no from $tbl[delivery_url] where name='$dlv_name' and partner_no in (0, '')");
				$dlv_code = $data['INVOICE_NO'];
				$ip = $_SERVER['REMOTE_ADDR'];
				$dlv_title[$ono][] = stripslashes($data['PRODUCT_NAME']);
                $ord = $this->pdo->assoc("select * from {$tbl['order']} where ono='$ono'");

				// 배송보류
				$om = $this->db->assoc("select OUT_HOLD from {$this->owner}.TEB_ORDER_PRODUCT where CORP_CD='$this->corp_cd' and ORDER_ID='$data[ORDER_ID]' and PRODUCT_NO='$data[PRODUCT_NO]'");
				if($om['OUT_HOLD'] == 'T') {
					$this->pdo->query("update $tbl[order_product] set dlv_hold='Y' where ono='$ono' and dooson_pno='$product_no'");
					if($pdo->lastRowCount() > 0) {
						ordChgHold($oprd['ono']);
					}
				}
				if($om['OUT_HOLD'] == 'F') {
					$this->pdo->query("update $tbl[order_product] set dlv_hold='N' where ono='$ono' and dooson_pno='$product_no'");
					if($pdo->lastRowCount() > 0) {
						ordChgHold($oprd['ono']);
					}
				}

				// 취소상태
				$ext_stat = '';
				if($data['ACTION'] == 'C1') $ext_stat = 12;
				if($data['ACTION'] == 'C2') $ext_stat = 14;
				if($data['ACTION'] == 'E' ) $ext_stat = 16;
				if($data['ACTION'] == 'F1' || $data['ACTION'] == 'E5') $ext_stat = 17;
				if($data['ACTION'] == 'F2') $ext_stat = ($data['SHOPBEGIN_DATE']) ? 17 : 15;
				if($ext_stat) {
					$repay_sql = (($ext_stat%2) == 1) ? ", repay_prc=total_prc, repay_date='$now', repay_milage=total_milage" : "";
					$this->pdo->query("update $tbl[order_product] set stat='$ext_stat' $repay_sql where ono='$ono' and dooson_pno='$product_no'");
				}

				$asql = '';

				// 상품준비중
				if($data['PROPERTY_04'] == 'T') {
					$this->pdo->query("update $tbl[order_product] set print='1' where ono='$ono' and dooson_pno='$product_no'");
					$this->pdo->query("update $tbl[order_product] set stat=3 where ono='$ono' and dooson_pno='$product_no' and stat=2");

					if($ord['checkout'] == 'Y') {
						$checkout_ono = $this->pdo->row("select checkout_ono from $tbl[order_product] where dooson_pno='$product_no'");
						$checkout = new CheckoutApi4();
						$checkout->api('PlaceProductOrder', $checkout_ono);
					}
				} elseif($data['PROPERTY_04'] == 'O') {
					$this->pdo->query("update $tbl[order_product] set print='0' where ono='$ono' and dooson_pno='$product_no'");
					$this->pdo->query("update $tbl[order_product] set stat=3 where ono='$ono' and dooson_pno='$product_no' and stat=2");

					if($ord['checkout'] == 'Y') {
						$checkout_ono = $this->pdo->row("select checkout_ono from $tbl[order_product] where dooson_pno='$product_no'");
						$checkout = new CheckoutApi4();
						$checkout->api('PlaceProductOrder', $checkout_ono);
					}
				} else {
					$this->pdo->query("update $tbl[order_product] set print='0' where ono='$ono' and dooson_pno='$product_no'");
					$this->pdo->query("update $tbl[order_product] set stat=2 where ono='$ono' and dooson_pno='$product_no' and stat=3");
				}
				$print = $this->pdo->row("select count(*) from $tbl[order_product] where ono='$ono' and print > 0");
				$asql .= " , print='$print'";

				// 배송중
				if($data['ACTION'] == 'D') {
					$ords[] = $ord['no'];
					$dlvs[$ord['no']] = $dlv_name;
					$asql .= ", dlv_no='$dlv_no', dlv_code='$dlv_code'";

					$r = $this->pdo->query("update $tbl[order_product] set stat=4, dlv_no='$dlv_no', dlv_code='$dlv_code' where dooson_pno='$product_no' and ono='$ono' and stat in (2,3,4)");

					if($ord['checkout'] == 'Y') {
						$checkout_ono = $this->pdo->row("select checkout_ono from $tbl[order_product] where dooson_pno='$product_no'");
						$checkout = new CheckoutApi4();
						$checkout->delivery($checkout_ono, $dlv_code, $checkout->getDlvCode($dlv_name));
					}

					// 에스크로 배송등록
					if(($ord['pay_type'] == 4 || $ord['pay_type'] == 17) && $dlv_no && $dlv_code) {
						 escDlvRegist($ord, $dlv_no, $dlv_code);
					}
				}

				// 배송완료
				if($data['ACTION'] == 'F') {
					$this->pdo->query("update $tbl[order_product] set stat=5 where ono='$ono' and dooson_pno='$product_no' and stat in (2, 3, 4)");
				}

				// 통합 주문 상태 처리
				$prd_nums = $this->pdo->row("select group_concat(stat) from $tbl[order_product] where ono='$ono'");
				$stat2 = explode(',', $prd_nums);
				$stat = min($stat2);
				$stat2 = '@'.implode('@', $stat2).'@';

				if($ord['date'.$stat] < 1 && $stat < 10) {
					$asql .= ", date{$stat}='$now'";
				}
				$r = $this->pdo->query("update $tbl[order] set stat='$stat', stat2='$stat2' $asql where ono='$ono'");

				$this->getStock($data['OPT_ID']); // 최신재고 확인

				if($ord['stat'] != $stat) {
					$this->pdo->query("
						insert into `$tbl[order_stat_log]`
							(`ono`, `stat`, `ori_stat`, `admin_id`, `admin_no`, `reg_date`, `system`)
							values
							('$ono', '$stat', '$ord[stat]', 'dooson', '0', '$now', 'Y')
					");
				}

				if($r) {
					$this->db->query("
						update {$this->owner}.TEB_ORDER_MANAGE set
							CD_MODE='M', CD_FROM='W',
							MODIFYUSER='DSMALL', MODIFYDT=TO_CHAR(SYSDATE, 'YYYYMMDDHH24MISS'),
							MODIFYPGMID = 'SCHEDULER', MODIFYIP='$ip'
						where
							CORP_CD='$this->corp_cd'
							and OM_NO='$om_no'
							and ORDER_ID='$ono'
					");
				}
			}

			// sms 및 이메일 발송
			if(count($ords) > 0) {
				$ords = array_unique($ords);
				foreach($ords as $key => $val) {
					$ords[$key] = "'$val'";
				}
				$ords = implode(',', $ords);
				$res = $this->pdo->iterator("select no, ono, title, buyer_name, buyer_email, dlv_no, dlv_code, mail_send, sms, buyer_cell, title from $tbl[order] where no in ($ords)");
                foreach ($res as $data) {
					$dlvc = $this->pdo->assoc("select count(*) as cnt, sum(if(stat in (4,5,6),1 , 0)) as completed from $tbl[order_product] where ono='$data[ono]' and stat between 1 and 6");
					$dlv = getDlvUrl($data);

					if(preg_match('/@3@/', $cfg['email_checked'])) {
						$mail_case = 3;
						include $engine_dir."/_engine/include/mail.lib.php";
						if($data['mail_send'] == 'Y') {
							sendMailContent($mail_case, $data['buyer_name'], $data['buyer_email']);
						}
					}

					if($data['sms'] == 'Y') {
						include_once $engine_dir.'/_engine/sms/sms_module.php';
						$sms_replace['ono'] = $data['ono'];
						$sms_replace['buyer_name'] = $data['buyer_name'];
						$sms_replace['dlv_code'] = $data['dlv_code'];
						$sms_replace['dlv_name'] = $dlvs[$data['no']];
						$sms_replace['title'] = (count($dlv_title[$data['ono']]) > 1) ? $dlv_title[$data['ono']][0].' 외 1건' : $dlv_title[$data['ono']][0];
						$sms_replace['dlv_link'] = $dlv['url'];

						if($dlvc['cnt'] > $dlvc['completed']) SMS_send_case(14, $data['buyer_cell']);
						else {
							if(setSmsHistory($data['ono'], 5)) {
								SMS_send_case(5, $data[buyer_cell]);
							}
						}
					}
				}
			}
		}

		function getOrders($ono = null) {
			global $engine_dir, $tbl, $cfg, $_pay_type, $_ord_stat, $now;

			if(!$this->db) $this->connect();

			if($ono) $w = " and ORDER_ID='$ono'";
			else {
				if(is_array($this->off_lang_cd)) {
					$get_lang_cd = '';
					foreach($this->off_lang_cd as $_lang_cd) {
						$get_lang_cd .= ",'$_lang_cd'";
					}
					$get_lang_cd = substr($get_lang_cd, 1);
				}
				if(!$get_lang_cd) $get_lang_cd = "'OFF', 'SBN', 'OPN'";
				$w = " and LANG_CD in ($get_lang_cd) AND CD_FROM = 'D'";
			}
			if($this->brand_nm) {
				if(is_array($this->brand_nm)) {
					$tmp = array();
					foreach($this->brand_nm as $key => $val) {
						$tmp[$key] = "'$val'";
					}
					$tmp = implode(',', $tmp);
					$w .= " and PROPERTY_04 in ($tmp)";
				} else {
					$w .= " and PROPERTY_04='$this->brand_nm'";
				}
			}

			$res = $this->db->query("select * from {$this->owner}.TEB_ORDERS where CORP_CD='$this->corp_cd' $w");
			while($data = oci_fetch_assoc($res)) {
				$data = $this->convertUTF8($data);

				$stat = 1;
				if($data['IS_PAYED'] == 'T') $stat = 2;
				if($data['IS_CANCELD'] == 'T') $stat = 13;
				if($data['IS_EXCHANGED'] == 'T') $stat = 17;
				if($data['IS_SHIPED'] == 'T') $stat = 4;
				if($data['ACTION'] == 'F1') $stat = 15;
				if($data['ACTION'] == 'E5') $stat = 17;

				$ono = $data['ORDER_ID'];
				$member_id = $this->setMemberPrefix($data['MEMBER_ID'], false);
				if($member_id) $member_no = $this->pdo->row("select no from $tbl[member] where member_id='$member_id'");
				$o_name = addslashes($data['O_NAME']);
				$o_phone = $data['O_PHONE2'];
				$o_cell = $data['O_PHONE1'];
				$o_email = $data['O_EMAIL'];
				$r_name = addslashes($data['R_NAME']);
				$r_zip = $data['R_ZIPCODE'];
				$r_addr1 = $data['R_ADDR1'];
				$r_addr2 = $data['R_ADDR2'];
				$r_phone = $data['R_PHONE2'];
				$r_cell = $data['R_PHONE1'];
				if(!$o_phone) $o_phone = $o_cell;
				if(!$r_phone) $r_phone = $r_cell;
				$milage_prc = $data['MILEAGE_USED'];
				$date1 = $data['ORDER_DATE'];
				if(strpos($date1, '오전')) $date1 = str_replace('오전', '', $date1).' am';
				if(strpos($date1, '오후')) $date1 = str_replace('오후', '', $date1).' pm';
				$date1 = strtotime($date1);
				if($data['PAY_DATE']) $date2 = strtotime($data['PAY_DATE']);
				$pay_type = array_search(str_replace('무통장', '무통장 입금', $data['PAYMETHOD']), $_pay_type);
				$bank_name = addslashes($data['P_NAME']);
				$bank = addslashes(trim($data['BANK_CODE'].' '.$data['BANK_ACC_NO'].' '.$data['BANK_DEPOSITOR']));
				$pay_prc = $data['PAYED_AMOUNT'];
				$dlv_prc = $data['SHIP_FEE'];
				$x_order_id = $data['LANG_CD'];
				if($x_order_id == $this->lang_cd) $x_order_id = '';
				$s_order_id = $data['S_ORDER_ID'];
				$ip = $data['CLIENT_IP'];
				if($data['USE_COUPON_NO']) {
					$sale5 = $data['COUPON_PRICE'];
				}
				$sale4 = $data['MENBER_GROUP_PRICE'];
				$total_milage = 0;
				$repay_prc = $repay_date = 0;
				$stats = array();
				$parent = $data['PROPERTY_02'];

				unset($amember);
				if($member_no && $member_id) {
					$this->pdo->query("update $tbl[member] set last_order='$date1' where no='$member_no' and member_id='$member_id'");
					$amember = $this->pdo->assoc("select * from $tbl[member] where no='$member_no'");
				}

				$res2 = $this->db->query("select * from {$this->owner}.TEB_ORDER_PRODUCT where CORP_CD='$this->corp_cd' and ORDER_ID='$data[ORDER_ID]' order by PRODUCT_NO asc");
				while($op = oci_fetch_assoc($res2)) {
					$om = $this->db->assoc("select * from {$this->owner}.TEB_ORDER_MANAGE where CORP_CD='$this->corp_cd' and ORDER_ID='$data[ORDER_ID]' and PRODUCT_NO='$op[PRODUCT_NO]'");

					$op = $this->convertUTF8($op);
					$om = $this->convertUTF8($om);

					$vals = array();
					$vals['ono'] = $ono;
					$vals['option'] = str_replace(' : ', '<split_small>', str_replace(' / ', '<split_big>', $op['OPT_STR']));
					$vals['option_prc'] = $op['OPT_PRICE'];
					$vals['name'] = $op['PRODUCT_NAME'];
					$vals['pno'] = $this->pdo->row("select no from $tbl[product] where code='$op[PRODUCT_CODE]' and wm_sc=0");
					$vals['sell_prc'] = $op['PRODUCT_PRICE'];
					$vals['buy_ea'] = $om['QUANTITY'];
					$vals['total_prc'] = $op['PRODUCT_PRICE']*$om['QUANTITY'];
					if($om['ACTION'] == 'F1' || $om['ACTION'] == 'E5') {
						$vals['sell_prc'] = 0;
						$vals['total_prc'] = 0;
						$vals['repay_prc'] = $op['PRODUCT_PRICE']*$om['QUANTITY'];
						$vals['repay_date'] = $now;
						$repay_prc += $vals['repay_prc'];
						$repay_date = $now;
					}
					$vals['total_milage'] = $op['MILEAGE_GENERATE'];
					$vals['member_milage'] = $op['MILEAGE_GENERATE'];
					$vals['milage'] = $vals['total_milage']/$vals['buy_ea'];
					$vals['complex_no'] = $this->pdo->row("select complex_no from erp_complex_option where sku='$op[OPT_ID]'");
					if($om['PLACE_DATE']) $date3 = strtotime($om['PLACE_DATE']);
					if($om['SHOPBEGIN_DATE']) $date4 = strtotime($om['SHOPBEGIN_DATE']);
					if($om['SHIPEND_DATE']) $date4 = strtotime($om['SHIPEND_DATE']);
					if($om['CANCEL_DATE']) $ext_date = strtotime($om['CANCEL_DATE']);
					$dlv_no = $this->getDlvName($om['SC_ID']);
					if($dlv_no > 0) $dlv_no = $this->pdo->row("select no from $tbl[delivery] where name='$dlv_no'");
					$dlv_code = $om['INVOICE_NO'];
					$vals['dlv_no'] = $dlv_no;
					$vals['dlv_code'] = $dlv_code;
					$vals['dooson_pno'] = $op['PRODUCT_NO'];
					$vals['s_order_id'] = $data['S_ORDER_ID'];
					$vals['dlv_hold'] = $data['OUT_HOLD'] == 'T' ? 'Y' : 'N';
					$vals['etc2'] = $om['ACTION'];
					$vals['print'] = $om['PROPERTY_04'] ? 1 : 0;

					$total_milage += $vals['total_milage'];

					$vals['stat'] = 1;
					switch($om['ACTION']) {
						case 'R' : $vals['stat'] = 1; break;
						case 'N1' : $vals['stat'] = 3; break;
						case 'N2' : $vals['stat'] = 3; break;
						case 'N3' : $vals['stat'] = 3; break;
						case 'N4' : $vals['stat'] = 3; break;
						case 'E1' : $vals['stat'] = 3; break;
						case 'E2' : $vals['stat'] = 3; break;
						case 'D' : $vals['stat'] = 4; break;
						case 'F' : $vals['stat'] = 5; break;
						case 'C' : $vals['stat'] = 17; break;
						case 'C1' : $vals['stat'] = 13; break;
						case 'C2' : $vals['stat'] = 15; break;
						case 'F1' : $vals['stat'] = 17; break;
						case 'F2' : $vals['stat'] = 13; break;
					}
					$stats[] = $vals['stat'];

					$exists = $this->pdo->row("select no from $tbl[order_product] where ono='$ono' and dooson_pno='$op[PRODUCT_NO]'");
					if($exists > 0) {
						$qry = $this->makeQuery($tbl['order_product'], $vals, "dooson_pno='$op[PRODUCT_NO]'");
					} else {
						$qry = $this->makeQuery($tbl['order_product'], $vals);
					}
					$this->pdo->query($qry);
					$opnos[] = $this->pdo->lastInsertId();
				}
				if(min($stats) > 10) $stat = min($stats);
				else $stat = max($stats);
				$stat2 = '@'.implode('@', $stats).'@';

				$date5 = $date4;
				$prd_prc = $this->pdo->row("select sum(total_prc) from $tbl[order_product] where ono='$ono'");
				$repay_prc = $this->pdo->row("select sum(repay_prc) from $tbl[order_product] where ono='$ono' and stat>11");

				$exists = $this->pdo->row("select no from $tbl[order] where ono='$ono'");
				if(!$exists && $data['LANG_CD'] == 'OFF') {
					if($data['USE_COUPON_NO']) {
						$mwhere = ($member_no > 0) ? ", member_id='$member_id', member_no='$member_no'" : '';
						if($om['ACTION'] == 'F1') {
							$this->pdo->query("update $tbl[coupon_download] set ono='', use_date='' $mwhere where no='$data[USE_COUPON_NO]'");
							$this->pdo->query("update $tbl[order] set prd_nums=concat(prd_nums, '@', '$data[USE_COUPON_NO]') where ono='$parent'");
							$this->setCoupon($data['USE_COUPON_NO']);
						} else {
							$this->pdo->query("update $tbl[coupon_download] set ono='$ono', use_date='$date1' $mwhere where no='$data[USE_COUPON_NO]'");
							$this->setCoupon($data['USE_COUPON_NO']);
						}
					}

					if($member_no > 0) $amember = $this->pdo->assoc("select * from $tbl[member] where no='$member_no'");

					if($data['DEPOSIT_AMT'] != 0 && $amember['no']) {
						include_once $engine_dir.'/_engine/include/milage.lib.php';
						if($data['DEPOSIT_AMT'] > 0) {
							ctrlEmoney('-', 11, $data['DEPOSIT_AMT'], $amember, "[$ono] 오프라인 주문 사용", false, $admin['admin_id'], $ono);
						} else {
							ctrlEmoney('+', 8, abs($data['DEPOSIT_AMT']), $amember, "[$ono] 오프라인 주문 취소", false, $admin['admin_id'], $ono);
						}
					}

					if($data['MILEAGE_USED'] != 0 && $amember['no']) {
						include_once $engine_dir.'/_engine/include/milage.lib.php';
						if($data['MILEAGE_USED'] > 0) {
							ctrlMilage('-', 11, $data['MILEAGE_USED'], $amember, '오프라인 주문 사용', false, $admin['admin_id'], $ono);
						} else {
							ctrlMilage('+', 8, abs($data['MILEAGE_USED']), $amember, '오프라인 주문 취소', false, $admin['admin_id'], $ono);
						}
					}
				}

				$milage_prc = $data['MILEAGE_USED'];
				$emoney_prc = $data['DEPOSIT_AMT'];

				$ord = array(
					'ono' => $ono, 'member_id' => $member_id, 'member_no' => $member_no, 'stat' => $stat, 'stat2'=>$stat2,
					'buyer_name' => $o_name, 'buyer_phone' => $o_phone, 'buyer_cell' => $o_cell, 'buyer_email' => $o_email,
					'addressee_name' => $r_name, 'addressee_zip' => $r_zip, 'addressee_addr1' => $r_addr1, 'addressee_addr2' => $r_addr2,
					'addressee_phone' => $r_phone, 'addressee_cell' => $r_cell,
					'milage_prc' => $milage_prc, 'emoney_prc' => $emoney_prc, 'date1' => $date1, 'date2' => $date2, 'date3' => $date3, 'date4' => $date4, 'date5' => $date5,
					'pay_type' => $pay_type, 'bank_name' => $bank_name, 'bank' => $bank, 'pay_prc' => $pay_prc, 'dlv_prc' => $dlv_prc, 'prd_prc' => $prd_prc, 'total_milage' => $total_milage,
					'total_prc' => ($dlv_prc+$prd_prc), 'x_order_id' => $x_order_id, 's_order_id' => $s_order_id, 'ip' => $ip,
					'sale4' => $sale4, 'sale5' => $sale5, 'repay_prc' => $repay_prc, 'repay_date' => $repay_date,
					'repay_prc' => $repay_prc
				);

				if($exists > 0) {
					$qry = $this->makeQuery($tbl['order'], $ord, "ono='$ono'");
				} else {
					$qry = $this->makeQuery($tbl['order'], $ord);

					if($member_no > 0) $this->pdo->query("update `$tbl[member]` set `total_ord`=`total_ord`+1, `total_prc`=`total_prc`+'$pay_prc', last_order='$now' where `no`='$member_no'");

					$payment_no = createPayment(array(
						'type' => ($pay_prc > 0) ? '0' : '1',
						'ono' => $ono,
						'pno' => $opnos,
						'pay_type' => $pay_type,
						'amount' => $pay_prc,
						'reason' => '오프라인주문',
						'emoney_prc' => $data['DEPOSIT_AMT'],
						'milage_prc' => $data['MILEAGE_USED'],
						'cpn_no' => $cpn_no,
					), 2);
				}
				$this->pdo->query($qry);
				ordChgHold($ono);

				if($exists == 0) { // 신규 접수일 경우 적립금 지급 및 취소
					if($parent) { // 원 주문서 적립금 취소
						$content = "오프라인 스토어 취소 전표 생성\n$parent -> $ono";
						if($total_milage) $content .= "\n구매적립금 $total_milage 취소 발생";

						$pord = $this->pdo->assoc("select stat, total_milage, milage_down from $tbl[order] where ono='$parent'");
						if($amember['no'] && $pord['milage_down'] == 'Y') {
							include_once $engine_dir.'/_engine/include/milage.lib.php';
							ctrlMilage('-', 12, abs($total_milage), $amember, "[$ono] 오프라인 주문 취소 / 원주문서 $parent]", false, $admin['admin_id'], $ono);
						}
						if($amember['no'] && $pord['milage_down'] == 'N') {
							$this->pdo->query("update $tbl[order] set total_milage=total_milage+$total_milage where ono='$parent'");
							$this->pdo->query("update $tbl[order] set total_milage=0 where ono='$ono'");
						}
					}

					if($amember['no'] > 0 && $total_milage > 0 && $pay_prc > 0) { // 신규 주문 적립
						include_once $engine_dir.'/_engine/include/milage.lib.php';
						ctrlMilage('+', 0, $total_milage, $amember, "[$ono] 오프라인 적립", false, 'doosoun', $ono);
						$this->pdo->query("update $tbl[order] set milage_down='Y', milage_down_date='$now' where ono='$ono'");
					}
				}

				$this->db->query("update {$this->owner}.TEB_ORDERS set CD_FROM='W' where ORDER_ID='$ono'");
			}
		}

		function removeOrder($ono) {
			if(!$this->db) $this->connect();

			$this->db->query("
				update {$this->owner}.TEB_ORDER_MANAGE set
					ACTION='F2', STATUS='주문서삭제',
					MODIFYDT=TO_CHAR(SYSDATE, 'YYYYMMDDHH24MISS'), MODIFYUSER='$this->register',
					MODIFYPGMID='$this->pgm_id', MODIFYIP='$_SERVER[REMOTE_ADDR]'
				where ORDER_ID='$ono'
			");
		}


		/* +----------------------------------------------------------------------------------------------+
		' |  상점 정보 수집
		' +----------------------------------------------------------------------------------------------+*/
		function getBizMember() {
			global $tbl, $now;

			if(!$this->db) $this->connect();

			$res = $this->db->query("select * from {$this->owner}.TEB_BIZ_MEMBER where corp_cd='$this->corp_cd' and CUSTOMER_SEC in ('M', 'P')");
			while($data = oci_fetch_assoc($res)) {
				$data = $this->convertUTF8($data);
				$shop_nm = addslashes($data['SHOP_NM']);

				// 스토어
				if($data['CUSTOMER_SEC'] == 'M') {
					$shop_no = $this->pdo->row("select no from wm_store where code='$data[SHOP_CD]'");
					if($shop_no > 0) {
						$this->pdo->query("update wm_store set name='$data[SHOP_NM]' where no='$shop_no'");
					} else {
						$this->pdo->query("insert into wm_store (code, name, reg_date) values ('$data[SHOP_CD]', '$data[SHOP_NM]', '$now')");
					}
				}

				// 사입처
				if($data['CUSTOMER_SEC'] == 'P') {
					$m = $this->db->assoc("select * from {$this->owner}.TEB_MEMBER where corp_cd='$this->corp_cd' and WARE_RECID='$data[WARE_RECID]'");

					$addr = trim($m['ADDR1'].' '.$m['ADDR2']);
					$phone = $m['PHONE'];

					$no = preg_replace('/^0+/', '', $data['SHOP_CD']);
					$ceo = $data['OWNER'];

					if($this->pdo->row("select count(*) from $tbl[provider] where no='$no'")) {
						$this->pdo->query("update $tbl[provider] set provider='$shop_nm', pceo='$ceo', arcade='$addr' where no='$no'");
					} else {
						echo "[$shop_nm]<br>";
						$this->pdo->query("insert into $tbl[provider] (no, provider, pceo, arcade) values ('$no', '$shop_nm', '$ceo', '$addr')");
					}

				}
			}

		}

		/* +----------------------------------------------------------------------------------------------+
		' |  Utilities
		' +----------------------------------------------------------------------------------------------+*/
		public function convertUTF8($data) {
			if(_BASE_CHARSET_ == 'utf-8') return $data;
			return $this->convert_encoding($data, _BASE_CHARSET_, 'utf-8');
		}

		protected function convertToUTF8($data) {
			if(_BASE_CHARSET_ == 'utf-8') return $data;
			return $this->convert_encoding($data, 'utf-8', _BASE_CHARSET_);
		}

		private function convert_encoding($data, $a, $b) {
			if(is_array($data)) {
				foreach($data as $key => $val) {
					if(is_object($val)) $data[$key] = null;
					else $data[$key] = mb_convert_encoding(trim($val), $a, $b);
				}
			} else {
				$data = mb_convert_encoding(trim($data), $a, $b);
			}
			return $data;
		}

		protected function date2timestamp($date) {
			if(!$date) return 0;

			$timestamp = strtotime(preg_replace('/^([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})$/', '$1-$2-$3 $4:$5:$6', $date));
			return $timestamp;
		}

		protected function makeQuery($table, $data, $where = null) {
			$type = $where ? 'u' : 'i';

			$func = array(
				'WARE_RECID',
				'REGISTDT',
				'MODIFYDT',
				'SEND_DT',
				'DT_COMPLETE',
			);

			$qry1 = $qry2 = $qry3 = '';
			foreach($data as $fn => $val) {
				$val = in_array($fn, $func) == true ? $val : "'".str_replace("'", "''", trim($val))."'";

				$qry1 .= ",$fn";
				$qry2 .= ",$val";
				$qry3 .= ",$fn=$val";
			}

			if($type == 'i') {
				return "insert into $table (".substr($qry1, 1).") values (".substr($qry2, 1).")";
			} else {
				return "update $table set ".substr($qry3, 1)." where $where";
			}
		}

		function getDlvCode($no) {
			global $tbl;

			$name = $this->pdo->row("select name from $tbl[delivery_url] where no='$no'");
			return $this->getDlvName($name, 2);
		}

		function getDlvName($code, $type = 1) {
			$array = array(
				'1' => '한진택배',
				'2' => '우체국택배',
				'3' => '대한통운',
				'4' => '롯데택배',
				'7' => 'KG로지스',
				'8' => '삼성택배',
				'9' => 'CJ대한통운',
				'10' => '오렌지택배',
				'11' => '로엑스택배',
				'12' => '경동택배',
				'13' => '옐로우캡',
				'15' => 'TNT',
				'16' => '로젠',
				'17' => '이젠택배',
				'18' => 'GSM UK[UK]',
				'19' => '(주)벨익스프레스',
				'20' => '우편등기',
				'21' => '이클라인',
				'22' => '뉴한국택배물류',
				'23' => '우체국',
				'24' => 'EPOST',
				'25' => '후다닥(퀵)',
				'26' => '로지스월드(오렌지)',
				'27' => '동서일개미',
				'28' => '오세기고구려',
				'29' => '천일택배',
				'30' => '중앙택배',
				'31' => '우리택배',
				'32' => '신세계택배',
				'33' => '기타운송수단',
				'34' => '한솔택배',
				'35' => 'B2C택배',
				'36' => '국민통상',
				'37' => '국일물류',
				'38' => '국제특송',
				'39' => '나이스택배',
				'40' => '대명택배',
				'41' => '대성택배',
				'42' => '대신택배',
				'43' => '대양통상',
				'44' => '대일택배',
				'45' => '무빙넷',
				'46' => '건영택배',
				'47' => '삼영물류',
				'48' => '삼영택배',
				'49' => '삼진익스프레스',
				'50' => '삼천리퀵서비스',
				'51' => '스피드라인택배아',
				'52' => '씽씽퀵서비스',
				'53' => '양양택배',
				'54' => '연합특송',
				'55' => '예스퀵코리아',
				'56' => '이트랜스택배',
				'57' => '일양익스프레스',
				'58' => '코리아택배',
				'59' => '파발마',
				'60' => '페덱스',
				'61' => '프랑스직배',
				'62' => 'DHL',
				'63' => '호남택배',
				'64' => '코덱스택배',
				'65' => '직접수령',
				'66' => '네덱스',
				'67' => 'KGB택배',
				'68' => 'GSM UK[ITALY]',
				'69' => '한진택배(사용안함)',
				'70' => '세덱스(신세계익스프레스)',
				'71' => '동원택배',
				'72' => '하나로택배',
				'73' => 'SC로지스',
				'74' => '네덱스택배',
				'75' => '이노지스택배',
				'76' => '일양택배',
				'77' => '우체국(NJ)',
				'78' => '우체국(UK)',
				'79' => '우체국(IT)',
				'80' => '건영택배',
				'82' => '우체국(LA)',
				'83' => '페덱스(JP)',
				'84' => 'GTX로지스',
				'85' => '합동택배',
				'99' => '기타',
			);

			if($type == 1) return $array[$code];
			else if($type == 2) return array_search($code, $array);
		}

		function setMemberPrefix($member_id, $set = true) {
			if(!$member_id) return '';
			if($this->memprefix) {
				if($set == true) {
					$member_id = $this->memprefix.'_'.$member_id;
				}
				if($set == false) {
					$member_id = preg_replace("/^".$this->memprefix."_/", "", $member_id);
				}
			}
			return $member_id;
		}

		public function getCorpCd() {
			return $this->corp_cd;
		}

	}

?>