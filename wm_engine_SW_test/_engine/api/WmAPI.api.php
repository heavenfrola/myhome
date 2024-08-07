<?php
Class WMAPI {

    private $member_id;
    private $pdo;

	function __construct($key, $user = null){
        $this->pdo = $GLOBALS['pdo'];
        $this->wmAPI($key, $user);
	}

    function wmAPI($key, $user = null) {
        global $tbl;

        $this->pdo = $GLOBALS['pdo'];

		header('Content-type:text/xml; charset=utf-8;');

		if($GLOBALS['hash'] != $key) {
            $is_active = $this->pdo->row("select is_active from {$tbl['erp_api']} where apikey=?", array($GLOBALS['hash']));
            if ($is_active == false) {
    			$this->result(null, '인증되지 않은 키 정보입니다.');
            }
            if ($is_active == 'N') {
    			$this->result(null, '사용이 중지된 키 정보입니다.');
            }
		}
		$this->member_id = $user;
    }

	function result($data, $msg = null) {
		if(headers_sent() == false) {
			header('Content-type:text/xml; charset=utf-8');
		}
		$xml  = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
		$xml .= $this->makeXML($data);

		exit($xml);
	}

	function makeXML($data, $map = null) {
		//if(!$data) return;

		if(!is_array($data)) {
			if($data != iconv('utf-8', 'utf-8', $data)) {
				$data = iconv('euc-kr', 'utf-8', stripslashes($data));
			}
			if(preg_match("/('|\"| |\r|\n|&|<|>)/", $data)) $data = "<![CDATA[$data]]>";
			return $data;
		}

		if(is_array($map)) {
			foreach($map as $key => $val) {
				if(is_array($val)) {
					if($data[$key][0]) {
						foreach($data[$key] as $idx => $sub) {
							$xml .= "<$key idx='".($idx+1)."'>";
							$xml .= $this->makeXML($sub, $val);
							$xml .= "</$key>";
						}
					} else {
						$xml .= "<$key>";
						$xml .= $this->makeXML($data[$key], $val);
						$xml .= "</$key>";
					}
				} else {
					$el = $data[$val];
					$xml .= "<$key>";
					$xml .= $this->makeXML($el, $val);
					$xml .= "</$key>";
				}
			}
		} else {
			foreach($data as $key => $val) {
				$xml .= "<$key>";
				$xml .= $this->makeXML($val);
				$xml .= "</$key>";
			}
		}

		return $xml;
	}


	// member api

	function setMilage($param, $tile = '') {
        global $tbl;

		include_once __ENGINE_DIR__.'/_engine/include/milage.lib.php';

		$member = $this->pdo->assoc("select * from {$tbl['member']} where member_id='$this->member_id'");

		if(!$member['no']) {
			$this->result(array('milage'=>array()), '존재하지 않는 회원정보입니다.');
		}

		$param = numberOnly($param);
		if($param < 0 && $member['milage'] < abs($param)) {
			$this->result(array('milage'=>array()), '차감할 적립금이 남은 적립금보다 많습니다.');
		}

		$ctype = $param > 0 ? '+' : '-';

		ctrlMilage($ctype, 3, abs($param), $member , $tile);

		$mode = $param > 0 ? '적립' : '차감';

		return $milage;
	}


	// product api

	function parseProduct($prd) {
		global $root_url;

		$prd['name'] = stripslashes($prd['name']);
		$prd['detail_url'] = $root_url.'/shop/detail.php?pno='.$prd['hash'];
		$prd['content2'] = stripslashes($prd['content2']);

		if(!$this->file_url) $this->file_url = getFileDir($prd['updir']);

		if($prd['upfile1']) $prd['upfile1_path'] = $this->file_url.'/'.$prd['updir'].'/'.$prd['upfile1'];
		if($prd['upfile2']) $prd['upfile2_path'] = $this->file_url.'/'.$prd['updir'].'/'.$prd['upfile2'];
		if($prd['upfile3']) $prd['upfile3_path'] = $this->file_url.'/'.$prd['updir'].'/'.$prd['upfile3'];

		$prd['categories'] = $prd['big'];
		if($prd['mid'] > 0) $prd['categories'] .= '_'.$prd['mid'];
		if($prd['small'] > 0) $prd['categories'] .= '_'.$prd['small'];

		return $prd;
	}

	// order api

	function parseOrder($data) {
		return $data;
	}
}
?>