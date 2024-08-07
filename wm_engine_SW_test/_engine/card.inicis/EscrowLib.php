<?PHP

	class Escrow {

		var $EscrowType;    // 요청형태 (배송등록 - dr, 배송업데이트 - du,  반품등록 - rr, 반품업데이트 - ru)
		var $mid;
		var $inipayhome;      // 이니페이 지불시스템이 설치되어 있는 절대 경로
		var $hanatid;           // 하나은행 거래 TID
		var $sendMsg;         // 서브 요청 메세지
		var $EscrowMsg;     // 요청 메세지
		var $rEscrowMsg;    // 결과 메세지
		var $test;


		function startAction()
		{

			$this->EscrowMsg =
						"inipayhome=" . $this->inipayhome . "^" .
						"test=" .$this->test . "^" .
					"mid=" . $this->mid . "^" .
					"EscrowType=" . $this->EscrowType . "^" .
					"msg=" .$this->sendMsg;
			$this->rEscrowMsg = exec($this->inipayhome . '/phpexec/INIescrow.phpexec \'' . $this->EscrowMsg . '\'');

			if(strlen($this->rEscrowMsg) <= 1)
				$this->rEscrowMsg = "ResultCode=01&ResultMsg=INVOKE ERR : " . $this->inipayhome . '/phpexec/INIescrow.phpexec';

			parse_str($this->rEscrowMsg);
			$this->resultCode = $ResultCode;
			$this->resultMsg = $ResultMsg;

		}


	}

?>