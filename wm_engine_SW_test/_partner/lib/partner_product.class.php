<?PHP

	class PartnerProduct {

        private $pdo;

        public function __construct() {
            $this->pdo = $GLOBALS['pdo'];
        }

		// 상품 등록/수정 로그 저장
		public function setLog($data) {
			global $tbl, $admin, $now;

			$data['content'] = addslashes(trim($data['content']));
			$data['content2'] = addslashes(trim($data['content2']));
			$data['req_stat'] = numberOnly($data['req_stat']);
			if($admin['level'] == 4 && ($data['req_stat'] != '1' && $data['req_stat'] != '5')) {
                $data['req_stat'] = 1;
            }
			$stat = 1;

			$no = $this->pdo->row("select no from $tbl[partner_product_log] where pno='$data[pno]' and (stat=1 or stat=5)");
			if($no > 0) {
				if($admin['level'] < 4) $asql .= ", content2='$data[content2]'";
				if($data['req_stat'] == 2) $asql .= ", confirm_date='$now'";
				$this->pdo->query("
					update $tbl[partner_product_log] set
						name='$data[name]', content='$data[content]', stat='$data[req_stat]', reg_date='$now' $asql
						where no='$no'
				");

				if($admin['level'] < 4 && $data['req_stat'] == 2) {
					$stat = numberOnly($_POST['stat']);
				}
			} else {
				$this->pdo->iterator("
					insert into $tbl[partner_product_log]
						(partner_no, pno, stat, name, content, admin_id, reg_date)
						values
						('$admin[partner_no]', '$data[pno]', '$data[req_stat]', '$data[name]', '$data[content]', '$admin[admin_id]', '$now')
				");

				// 기존 편집 데이터가 있을 경우 삭제
				$tmp = $this->pdo->lastInsertId();
				$res = $this->pdo->iterator("select no from $tbl[partner_product_log] where pno='$data[pno]' and no!='$tmp' and stat=1");
                foreach ($res as $olddata) {
					$this->pdo->query("update $tbl[partner_product_log] set stat=4 where no='$olddata[no]'");
				}
			}
			$this->pdo->query("update $tbl[product] set stat=$stat, partner_stat='$data[req_stat]' where no='$data[pno]'");
		}
	}

?>