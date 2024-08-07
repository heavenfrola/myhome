<?PHP

	function write_log ($strLogMsg) {
		global $root_dir;

		$log_dir = $root_dir.'/_data/kspay_log';
		makeFullDir('_data/kspay_log');

		$strLogFile = $log_dir.'/kspay_'.date('YmdHis').'.log';

		$strRecord = '['.date('Y-m-d H:i:s').']'.$strLogMsg."\n";

		$fp	= fopen($strLogFile, 'a+');
		fwrite($fp,	$strRecord);
		fclose($fp);
	}

	class KSPayWebHost {
		var $payKey;
		var $rparams;
		var $mtype;

		var $rnames		= array();
		var $rvalues	= array();

		var $DEFAULT_DELIM = "`";
		var $DEFAULT_RPARAMS = "authyn`trno`trddt`trdtm`amt`authno`msg1`msg2`ordno`isscd`aqucd`result`halbu`cbtrno`cbauthno";


		function KSPayWebHost($_payKey, $_rparams) {
			$this->__construct = $_payKey;

			if(empty($_rparams) || false === strpos($_rparams,$this->DEFAULT_DELIM)) {
				$this->rparams	= $this->DEFAULT_RPARAMS;
			} else {
				$this->rparams	= $_rparams;
			}
			$this->rnames = split($this->DEFAULT_DELIM, $this->rparams);
		}

		function kspay_get_value($pname) {
			if(empty($pname) || !is_array($this->rnames) || !is_array($this->rvalues) || count($this->rnames) != count($this->rvalues)) return null;
			return $this->rvalues[$pname];
		}

		function kspay_send_msg($_mtype) {
			$this->mtype = $_mtype;
			$rmsg = $this->send_url();

			if(false === strpos($rmsg,$this->DEFAULT_DELIM)) return false;

			$tmpvals = split($this->DEFAULT_DELIM, $rmsg);

			if(count($this->rnames) < count($tmpvals))
			{
				for($i=0; $i<count($this->rnames); $i++)
				{
					$this->rvalues[$this->rnames[$i]] = $tmpvals[$i+1];
				}
				return true;
			}
		}

		var $KSPAY_WEBHOST_URI	= "/store/KSPayFlashV1.3/web_host/recv_post.jsp";
		var $KSPAY_WEBHOST_HOST	= "kspay.ksnet.to";
		var $KSPAY_WEBHOST_IP	= "210.181.28.137";

		//var $KSPAY_WEBHOST_HOST	= "210.181.28.116";
		//var $KSPAY_WEBHOST_IP	= "210.181.28.116";

		function send_url() {
			$post_msg = "sndCommConId=" . $this->payKey . "&sndActionType=" . $this->mtype . "&sndRpyParams=" . urlencode($this->rparams);

			$req_msg  = "POST " . $this->KSPAY_WEBHOST_URI . " HTTP/1.0\r\n";
			$req_msg .= "Host: " . $this->KSPAY_WEBHOST_HOST . "\r\n";
			$req_msg .= "Accept-Language: ko\r\n";
			$req_msg .= "User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)\r\n";
			$req_msg .= "Content-type: application/x-www-form-urlencoded\r\n";
			$req_msg .= "Content-length: ".strlen($post_msg)."\r\n";
			$req_msg .= "Connection: close\r\n";
			$req_msg .= "\r\n";
			$req_msg .= $post_msg;

			$kspay_ipaddr = gethostbyname($this->KSPAY_WEBHOST_HOST);
			$kspay_port   = 80;

			if($kspay_ipaddr == $this->KSPAY_WEBHOST_HOST)
			{
				$kspay_ipaddr = $this->KSPAY_WEBHOST_IP;
			}

			$fp_socket = fsockopen($kspay_ipaddr, $kspay_port, $errno, $errstr, 60);
			if($fp_socket) {
				fwrite($fp_socket, $req_msg, strlen($req_msg));
				fflush($fp_socket);
				while(!feof($fp_socket)) {
					$rpy_msg .= fread($fp_socket, 8192);
				}
			}
			fclose($fp_socket);

			$rtn_msg = "";
			$rpos = strpos($rpy_msg,"\r\n\r\n");

			if($rpos !== false) $rtn_msg = substr($rpy_msg, $rpos+4);

			return $rtn_msg;
		}
	}

?>