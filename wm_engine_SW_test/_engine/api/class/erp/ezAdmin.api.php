<?PHP

use Wing\common\Xml;

ini_set('memory_limit', '512M');

Class ezAdmin extends erpAPI {

	private $pdo;

	function __construct($key) {
		$this->bendor = 'ezAdmin';
		$this->timing = 4;
        $this->pdo = $GLOBALS['pdo'];

		$this->erpApi($key);
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  override
	' +----------------------------------------------------------------------------------------------+*/
	function result($data, $msg = null) {
		if(is_array($data)) {
			$temp = array_keys($data);
			$root = $temp[0];
		} else {
			$data = array('error'=>array());
			$root = 'error';
		}

		if($root === 'error') {
			$data[$root]['return_code'] = count($data[$root]) < 1 ? 'false' : 'true';
			$data[$root]['return_msg'] = $msg;

			parent::result($data, $msg);
		}
		else {
			header('Content-type:text/xml;');
			$map = $msg;
			$xml  = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
			$xml .= "<$GLOBALS[action]>\n";
			$xml .= $this->makeXML($data, $msg);
			$xml .= "</$GLOBALS[action]>\n";

			echo $xml;
		}
	}


	/* +----------------------------------------------------------------------------------------------+
	' |  상품정보 제공
	' +----------------------------------------------------------------------------------------------+*/
	function getProduct($idx, $opno = null) {
		global $tbl;

		$sdate = numberOnly($_REQUEST['sdate']);
		$edate = numberOnly($_REQUEST['edate']);
		$sedate = numberOnly($_REQUEST['sedate']);
		$eedate = numberOnly($_REQUEST['eedate']);

		if($sdate && $edate) {
			$sdate = strtotime(preg_replace('/^([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})$/', '$1-$2-$3 $4:$5:$6', $sdate));
			$edate = strtotime(preg_replace('/^([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})$/', '$1-$2-$3 $4:$5:$6', $edate));
			$w .= " and reg_date between $sdate and $edate";
		} elseif($sedate > 0 && $eedate) {
			$sedate = strtotime(preg_replace('/^([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})$/', '$1-$2-$3 $4:$5:$6', $sedate));
			$eedate = strtotime(preg_replace('/^([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})$/', '$1-$2-$3 $4:$5:$6', $eedate));
			$w .= " and edt_date between $sedate and $eedate";
		} else {
			$w .= " and no='$idx'";
		}

		$array = array();
		$array['data_type'] = 'xml';
		$array['mall_id'] = $this->account;
		$array['auth_code'] = $this->api_key;

		$res = $this->pdo->iterator("select * from $tbl[product] where stat!=1 and wm_sc=0 $w order by no asc");
        foreach ($res as $data) {
			$data = shortCut($data);

			if($data['ea_type'] ==1 ) {
				$tmp = $this->pdo->iterator("select no, name from $tbl[product_option_set] where pno='$data[no]' and stat=2 order by sort asc");
                foreach ($tmp as $opdata) {
					$data['item_options'] .= ",$opdata[name]";
				}
				$data['item_options'] = substr($data['item_options'], 1);
				$option = $this->pdo->iterator("select opts, curr_stock(complex_no) as stock, complex_no as inos, barcode from erp_complex_option a where pno='$data[no]' and del_yn='N'");
				if($option) {
					$data['item_cnt'] = $option->rowCount();
                    foreach ($option as $odata) {
						$odata['inames'] = ($odata['opts']) ? getComplexOptionName($odata['opts']):"옵션없음";
						$data['options'][] = $odata;
					}
				}
			} else {
				$opt_data = array();
				$ores = $this->pdo->iterator("select no, name from ".$tbl['product_option_set']." where pno='$data[parent]' and necessary in ('Y', 'C') and otype!='4B' order by sort asc");
                foreach ($ores as $oset) {
					$data['item_options'] .= ",$oset[name]";

					$_temp = $opt_data;
					$res2 = $this->pdo->iterator("select * from ".$tbl['product_option_item']." where pno='$data[parent]' and opno='$oset[no]' order by sort asc");
       				foreach ($res2 as $odata) {
						$iname = stripslashes($odata['iname']);
						if($odata['ori_no']) $odata['no'] = $odata['ori_no'];
						if(count($opt_data) == 0) {
							$_temp[$odata['no']] = $iname;
						} else {
							foreach($opt_data as $key => $val) {
								$_temp[$key.'_'.$odata['no']] = $val.'<ss>'.$iname;
								unset($_temp[$key]);
							}
						}
					}
					$opt_data = $_temp;
				}
				$data['item_options'] = substr($data['item_options'], 1);
				if(count($opt_data) > 0) {
					$data['item_cnt'] = count($opt_data);
					foreach($opt_data as $opts => $nm) {
						$data['options'][] = array(
							'inames' => $nm,
							'inos' => $opts,
							'stock' => 9999
						);
					}
				} else {
					$data['item_cnt'] = 1;
					$data['options'][] = array(
						'inames' => '옵션없음',
						'inos' => $data['no'],
						'stock' => 9999
					);
				}
			}

			if($data['seller_idx'] > 0) {
				$seller = $this->pdo->assoc("select * from $tbl[provider] where no='$data[seller_idx]'");
				$data['pcell'] = $seller['pcell'];
				$data['ptel'] = $seller['ptel'];
				$data['pposition'] = $seller['arcade'];
				if($seller['floor']) $data['pposition'] .= ' '.$seller['floor'].'층';
				if($seller['plocation']) $data['pposition'] .= ' '.$seller['plocation'];
			}

			$array['prd_list']['prd'][] = $this->parseProduct($data);
		}

		$mapping = array(
			'data_type' => 'data_type',
			'mall_id' => 'mall_id',
			'auth_code' => 'auth_code',
			'prd_list' => array(
				'prd' => array(
					'prd_code' => 'no',
					'style_cd' => 'code',
					'prd_name' => 'name',
					'sale_price' => 'sell_prc',
					'prd_price' => 'normal_prc',
					'sup_price' => 'origin_prc',
					'milage_value' => 'milage',
					'buy_min' => 'min_ord',
					'buy_max' => 'max_ord',
					'supp_name' => 'seller',
					'supp_code' => 'seller_idx',
					'supp_pname' => 'origin_name',
					'supp_cell' => 'pcell',
					'supp_tel' => 'ptel',
					'supp_location' => 'pposition',
					'image' => array(
						'main' => 'main',
						'list' => 'list',
						'big' => 'big',
					),
					'disp_flag' => 'disp_flag',
					'sell_flag' => 'sell_flag',
					'cpm_cate_code' => 'categories',
					'detail_url' => 'detail_url',
					'item_cnt' => 'item_cnt',
					'item_options' => 'item_options',
					'item_list' => array(
						'item' => array(
							'name'=>'inames',
							'code'=>'inos',
							'stock'=>'stock',
						)
					),
					'reg_date'=>'reg_date_nstr',
					'mod_date'=>'edt_date_nstr'
				)
			)
		);

		return $this->result($array, $mapping);
	}

	function parseProduct($prd) {
		$prd = parent::parseProduct($prd);

		$prd['disp_flag'] = $prd['stat'] == 3 ? 'F' : 'T';
		$prd['sell_flag'] = $prd['stat'] == 2 ? 'T' : 'F';
		$prd['image']['main'] = $prd['upfile2_path'];
		$prd['image']['list'] = $prd['upfile3_path'];
		$prd['image']['big'] = $prd['upfile1_path'];
		$prd['reg_date_nstr'] = date('YmdHis', $prd['reg_date']);
		$prd['edt_date_nstr'] = date('YmdHis', $prd['edt_date']);
		$prd['item_list']['item'] = $prd['options'];

		return $prd;
	}


	/* +----------------------------------------------------------------------------------------------+
	' |  주문정보 제공
	' +----------------------------------------------------------------------------------------------+*/
	function getOrder() {
		$this->getOrderProc('2,3');
	}

	function getPayedOrder() {
		$this->getOrderProc(2);
	}

	function getShippingOrder() {
		$this->getOrderProc(3);
	}

	function getCompletedOrder() {
		$this->getOrderProc(5);
	}

	function getCanceledOrder() {
		$this->getOrderProc();
	}

	function getOrderProc($stat = null) {
		global $tbl, $_order_stat;

		if(!$_REQUEST['sdate'] || !$_REQUEST['edate']) {
			$this->result(null, '검색범위를 입력해 주세요.');
		}

		$sdate = strtotime(preg_replace('/^([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})$/', '$1-$2-$3 $4:$5:$6', $_REQUEST['sdate']));
		$edate = strtotime(preg_replace('/^([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})$/', '$1-$2-$3 $4:$5:$6', $_REQUEST['edate']));

		$idx = 0;
		$array = array();

        if ($stat == null) {
			$w .= " and b.stat > 11";
			$datetype = 'ext_date';
        } else {
			$w .= " and b.`stat` in ($stat)";
			$datetype = 'date1';
        }

		$res = $this->pdo->iterator("select a.* from `$tbl[order]` a inner join $tbl[order_product] b using(ono) where 1 $w and a.$datetype between '$sdate' and '$edate' group by ono order by a.$datetype asc");
        foreach ($res as $data) {
			$data['idx'] = ++$idx;
			$data = $this->parseOrder($data);
			$data['cancel_stat'] = $_order_stat[$data['stat']];
			$data['product_list'] = array();

			if(!$data['date2'] && $data['date3']) {
				$data['date2'] = $data['date3'];
			}

            if (defined('__USE_PDION__') == true) {
                $data['buyer_name'] = str_replace("'", '', $data['buyer_name']);
            }

			$res2 = $this->pdo->iterator("select * from $tbl[order_product] b where ono='$data[ono]' $w");
            foreach ($res2 as $prd) {
				$prd['name'] = stripslashes(strip_tags(strip_tags($prd['name'])));
				$prd['origin_name'] = stripslashes(strip_tags(strip_tags($prd['origin_name'])));
				$prd['option'] = $prd['option'];
				$prd['option'] = str_replace('<split_big>', ',', $prd['option']);
				$prd['option'] = str_replace('<split_small>', ':', $prd['option']);
				$prd['cancel_stat'] = $_order_stat[$prd['stat']];
				if($prd['complex_no'] < 1) {
					$prd['complex_no'] = str_replace('<split_big>', ',', $prd['option_idx']);
					$prd['complex_no'] = str_replace('<split_small>', ':', $prd['complex_no']);
				}
                $prd['total_prc'] = parsePrice($prd['total_prc']);
                $prd['pay_prc'] = parsePrice($prd['total_prc']-getOrderTotalSalePrc($prd));
				$data['product_list']['Product'][] = $prd;
			}

			if(str_replace('@', '',$data['order_gift'])){
				$_ord_gift = explode('@', $data['order_gift']);
				for($ii=0; $ii < count($_ord_gift); $ii++) {
					if($_ord_gift[$ii]) {
						$gift = $this->pdo->assoc("select * from `$tbl[product_gift]` where no='$_ord_gift[$ii]'");
						$gift['name'] = strip_tags(stripslashes($gift['name']));
						$prd = array(
							'name' => strip_tags(stripslashes($gift['name'])),
							'ogigin_name' => strip_tags(stripslashes($gift['name'])),
							'buy_ea' => 1,
							'total_prc' => 0,
                            'pay_prc' => 0
						);
						$data['product_list']['Product'][] = $prd;
					}
				}
			}

			$array[] = $data;
		}

        /*
		if($stat < 1) {
			$res = $this->pdo->iterator("select a.* from $tbl[order] a inner join $tbl[order_product] b using(ono) where a.stat < 6 and b.stat > 11 and b.repay_date between '$sdate' and '$edate'");
            foreach ($res as $data) {
				$data['idx'] = ++$idx;
				$data = $this->parseOrder($data);
				$data['cancel_stat'] = $_order_stat[$data['stat']];
				$data['product_list'] = array();

				$res2 = $this->pdo->iterator("select * from $tbl[order_product] b where ono='$data[ono]' $w");
                foreach ($res2 as $prd) {
					$prd['name'] = stripslashes(strip_tags(strip_tags($prd['name'])));
					$prd['origin_name'] = stripslashes(strip_tags(strip_tags($prd['origin_name'])));
					$prd['option'] = $prd['option'];
					$prd['option'] = str_replace('<split_big>', ',', $prd['option']);
					$prd['option'] = str_replace('<split_small>', ':', $prd['option']);
					$prd['cancel_stat'] = $_order_stat[$prd['stat']];
					if($prd['complex_no'] < 1) {
						$prd['complex_no'] = str_replace('<split_big>', ',', $prd['option_idx']);
						$prd['complex_no'] = str_replace('<split_small>', ':', $prd['complex_no']);
					}
                    $prd['pay_prc'] = parsePrice($prd['total_prc']-getOrderTotalSalePrc($prd));
					$data['product_list']['Product'][] = $prd;
				}

				$array[] = $data;
			}
		}
        */

		$mapping = array(
			'order_idx' => array(
				'idx' => 'idx',
				'ord_no' => 'ono',
				'ord_date' => 'date1',
				'settle_date' => 'date2',
				'ord_name' => 'buyer_name',
				'recv_name' => 'addressee_name',
				'recv_zipcode' => 'addressee_zip',
				'recv_addr1' => 'addressee_addr1',
				'recv_addr2' => 'addressee_addr2',
				'recv_tel' => 'addressee_phone',
				'recv_cell' => 'addressee_cell',
				'settle_price' => 'pay_prc',
				'ord_memo' => 'dlv_memo',
				'sale_price' => 'total_prc',
				'ship_fee' => 'ship_fee',
				'paymethod' => 'pay_type',
				'bank_name' => 'bank',
				'bank_account' => 'bank',
				'bank_income' => 'bank_name',
				'is_checkout' => 'checkout',
				'product_list' => array(
					'Product' => array(
						'ord_prd_no' => 'no',
						'prd_name' => 'name',
						'prd_code' => 'pno',
						'item_name' => 'option',
						'item_code' => 'complex_no',
						'item_cnt' => 'buy_ea',
						'item_price' => 'total_prc',
                        'item_sell_price' => 'pay_prc',
					)
				)
			)
		);

		if ($stat == null) {
			$mapping['order_idx']['cancel_stat'] = 'cancel_stat';
			$mapping['order_idx']['product_list']['Product']['cancel_stat'] = 'cancel_stat';
		}

		return $this->result(array('order_idx'=>$array), $mapping);
	}

	function parseOrder($ord) {
		$ord = parent::parseProduct($ord);
		$ord['date1'] = date('Y-m-d H:i:s', $ord['date1']);
		$ord['date2'] = date('Y-m-d H:i:s', $ord['date2']);

		switch($ord['pay_type']) {
			case 2 : $ord['pay_type'] = 'R'; break;
			default: $ord['pay_type'] = 'C'; break;
		}

		return $ord;
	}


	/* +----------------------------------------------------------------------------------------------+
	' |  배송 처리
	' +----------------------------------------------------------------------------------------------+*/
	function getDlvno($code) {
		global $cfg, $tbl;

		switch($code) {
			case '0003' : $str = 'CJ대한통운'; break;
			case '0004' : $str = '로젠'; break;
			case '0056' : $str = 'SC로지스'; break;
			case '0012' : $str = '우체국택배'; break;
			case '0008' : $str = '옐로우캡 택배'; break;
			case '0022' : $str = 'KGB 택배'; break;
			case '0018' : $str = '한진택배'; break;
			case '0006' : $str = 'CJGLS'; break;
			case '0019' : $str = '롯데택배'; break;
			case '0051' : $str = '하나로 로지스'; break;
			case '0020' : $str = '동부 익스프레스'; break;
		}

		$dlv_no = $this->pdo->row("select no from $tbl[delivery_url] where name='$str' and (partner_no=0 or partner_no='')");

		return $dlv_no;
	}

	function setShipping($param) {
		$this->setDeliveryProc(3, $param);
	}

	function setDelivery($param) {
		$this->setDeliveryProc(4, $param);
	}

	function setDeliveryProc($stat, $xml) {
		global $tbl, $engine_dir, $root_dir, $smartstore, $sms_case_admin;

		include $engine_dir.'/_manage/delivery.lib.php';

		ob_start();
		$xml = new XML(comm($xml));
		ob_end_clean();

		if(!is_object($xml->arr)) {
			$this->result(null, 'xml 오류');
		}

		$array = array();

		foreach($xml->arr->order_list[0]->order_idx as $key => $val) {
			$ono =$val->order_no[0];
			$dlv_no = $this->getDlvno($val->trans_code[0]);
			$dlv_code = numberOnly($val->invoke_no[0]);
			$dlv_date = strtotime(preg_replace('/^([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})$/', '$1-$2-$3 $4:$5:$6', $val->shipped_date[0]));

			$temp = array();
			$temp['ono'] = $ono;
			$temp['return_code'] = '0000';

			$_pno = array();
			$data = $this->pdo->assoc("select ono from $tbl[order] where ono='$ono'");
			foreach($val->order_prd_list[0]->order_prd as $oprd) {
				$pno  = $oprd->ord_prd_no[0];
				$temp['ord_prd_list'][] = array('no' => $pno);
				$_pno[] = $pno;
			}
			$pno = implode(',', $_pno);

			$return = orderDelivery($stat, $ono, $pno, $dlv_no, $dlv_code, $dlv_date);

            if ($return == 'OK') {
                $temp['return_msg'] = '요청하신 작업이 정상적으로 처리되었습니다.';
            } elseif ($return == 'DUPLICATED') {
                $temp['return_msg'] = '이미 변경된 내역이거나 해당되는 내역이 없습니다.';
            } else {
                $temp['return_code'] = '1000';
                $temp['return_msg'] = $return;
            }

			$array[] = $temp;
		}

		$mapping = array(
			'order_idx' => array(
				'order_no' => 'ono',
				'ord_prd_list' => array(
					'oprd_prd_no' => 'no'
				),
				'return_code' => 'return_code',
				'return_msg' => 'return_msg'
			)
		);

		return $this->result(array('order_idx'=>$array), $mapping);
	}


	/* +----------------------------------------------------------------------------------------------+
	' |  재고조회
	' +----------------------------------------------------------------------------------------------+*/
	function getStock($param) {
		global $tbl;

		if(preg_match('/[^0-9,]/', $param) == true) {
			$this->result(null, '파라메터 형식이 잘못되었습니다.');
		}
		$param = preg_replace('/^,/', '', $param);

		$array = array();
		$res = $this->pdo->iterator("select no, name from $tbl[product] where no in ($param) and ea_type=1");
        foreach ($res as $key => $data) {
			$data['name'] = stripslashes($data['name']);
			$ores = $this->pdo->iterator("select opt_name(a.opt1, a.opt2) as inames, curr_stock(complex_no) as stock, complex_no as inos from erp_complex_option a where pno='$data[no]'");
            foreach ($ores as $odata) {
				$data['item_list'][] = $odata;
			}

			$array[] = $data;
		}

		$mapping = array(
			'product_idx' => array(
				'prd_code' => 'no',
				'prd_name' => 'name',
				'item_list' => array(
					'name'=>'inames',
					'code'=>'inos',
					'stock'=>'stock',
				)
			)
		);

		return $this->result(array('product_idx'=>$array), $mapping);
	}

	function setStock($param) {
		global $engine_dir;

		ob_start();
		include_once $engine_dir.'/_engine/include/xml.class.php';
		$xml = new XML(comm($param));
		ob_end_clean();

		if(!is_object($xml->arr)) {
			$this->result(null, 'xml 오류');
		}

		$array = array();

		foreach($xml->arr->stock_list[0]->product as $key => $val) {
			$pno = $val->prd_code[0];
			$complex_no = $val->complex_no[0];

			$data  = array();
			$data['prd_code'] = $pno;
			$data['complex_no'] = $complex_no;
			$exists = $this->pdo->row("select count(*) from erp_complex_option where pno='$pno' and complex_no='$complex_no'");

			if(!$pno || !$complex_no || !$exists) {
				$data['return_code'] = '1111';
				$data['return_msg'] = '상품정보가 존재하지 않습니다.';
			} else {
				$stock = $val->stock[0];
				$ip = $_SERVER['REMOTE_ADDR'];
				$reg_date = date('Y-m-d H:i:s');
				$qty = $this->pdo->row("select curr_stock($complex_no)");
				$gap = $qty - $stock;

				if($gap == 0) {
					$data['return_code'] = '1111';
					$data['return_msg'] = '변경전과 변경후 재고가 같습니다.';
				} else {
					$inout_kind = $gap > 0 ? 'P' : 'U';
					$gap = abs($gap);
					$this->pdo->query("insert into erp_inout (complex_no, inout_kind, qty, remark, reg_user, reg_date, remote_ip) values ('$complex_no', '$inout_kind', '$gap', 'from ezadmin', '', '$reg_date', '$ip')");

					if($this->pdo->lastRowCount() > 0) {
						$data['return_code'] = '0000';
						$data['return_msg'] = '요청하신 작업이 정상적으로 처리되었습니다.';
					} else {
						$data['return_code'] = '1111';
						$data['return_msg'] = '처리된 내역이 없습니다.';
					}
				}
			}

			$array[] = $data;
		}

		$mapping = array(
			'stock_list' => array(
				'prd_code' => 'prd_code',
				'complex_no' => 'complex_no',
				'return_code' => 'return_code',
				'return_msg' => 'return_msg'
			)
		);

		return $this->result(array('stock_list'=>$array), $mapping);
	}
}
?>