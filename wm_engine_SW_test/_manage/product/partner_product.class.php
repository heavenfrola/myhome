<?PHP

	class PartnerProduct {

        private $pdo;

        public function __construct() {
            $this->pdo = $GLOBALS['pdo'];
        }

		// 편집중인 템플릿 체크
		public function is_exists($pno) {
			global $tbl, $admin;

			$exists = $this->pdo->assoc("select no, partner_stat from $tbl[product] where partner_no='$admin[partner_no]' and ori_no='$pno' and stat=1");
			if($exists) return $exists['no'];

			return $this->copyProduct($pno);
		}

		// 상품 복사본 생성
		public function copyProduct($pno) {
			global $tbl, $admin, $now;

			// 상품
			$new_pno = $this->pdo->row("select max(no) from $tbl[product]")+1;
			$hash = strtoupper(md5($new_pno));
			$this->sqlCopy($tbl['product'], "and no='$pno'", array('no'=>$new_pno, 'hash'=>$hash, 'reg_date'=>$now, 'partner_no'=>$admin['partner_no'], 'stat'=>1));

			$this->sqlCopy($tbl['product_refprd'], "and pno='$pno'", array('no'=>null, 'pno'=>$new_pno));
			$this->sqlCopy($tbl['product_filed'], "and pno='$pno'", array('no'=>null, 'pno'=>$new_pno));
			$this->sqlCopy($tbl['product_image'], "and pno='$pno'", array('no'=>null, 'pno'=>$new_pno));
			$this->sqlCopy($tbl['product_option_set'], "and pno='$pno'", array('no'=>null, 'pno'=>$new_pno));

			$optres = $this->pdo->iterator("select * from $tbl[product_option_set] where pno='$new_pno'");
            foreach ($optres as $optset) {
				$this->sqlCopy($tbl['product_option_item'], "and pno='$pno' and opno='$optset[ori_no]'", array('no'=>null, 'pno'=>$new_pno, 'opno'=>$optset['no']));
			}

			return $new_pno;
		}

		// 복사 쿼리 생성
		public function sqlCopy($tbl, $where, $distinct = array()) {
			$res = $this->pdo->iterator("select * from $tbl where 1 $where");
            foreach ($res as $data) {
				$sql_1 = "ori_no";
				$sql_2 = "'$data[no]'";

				foreach($data as $key => $val) {
					if($key == 'ori_no') continue;
					if(array_key_exists($key, $distinct)) {
						if(is_null($distinct[$key]) == false) $val = $distinct[$key];
						else continue;
					}

					$sql_1 .= ",`$key`";
					$sql_2 .= ",'$val'";
				}
				$this->pdo->query("insert into $tbl ($sql_1) values ($sql_2)");
			}
		}

	}

?>