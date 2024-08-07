<?PHP

	namespace Wing\HTTP;

	class CurlConnection {
		private $curl;
		private $url;
		private $method;
		private $args;
		private $agent;
		private $result;
		private $info;

		public function __construct($url, $method = 'GET', $args = null) {
			$this->url = $url;
			$this->method = $method;
			$this->args = $args;

			$this->curl = curl_init();
			$this->agent = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 2.0.50727; .NET CLR 1.1.4322; .NET CLR 3.0.04506.30)';
		}

		public function setHeader($header) {
			curl_setopt($this->curl, CURLOPT_HTTPHEADER, $header);
		}

		public function saveCookie($path = 'cookie.txt') {
			curl_setopt($this->curl, CURLOPT_COOKIEJAR, $path);
		}

		public function loadCookie($path = 'cookie.txt') {
			curl_setopt($this->curl, CURLOPT_COOKIEFILE, $path);
		}

		public function setReferer($refer) {
            curl_setopt($this->curl, CURLOPT_REFERER, $refer);
		}

		public function setAgent($angent) {
			$this->agent = $angent;
		}

		public function setopt($field, $value) {
			curl_setopt($this->curl, $field, $value);
		}

		public function exec() {
			curl_setopt($this->curl, CURLOPT_URL, $this->url);
			curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($this->curl, CURLOPT_HEADER, false);
			curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 30);
			curl_setopt($this->curl, CURLOPT_TIMEOUT, 30);
			curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($this->curl, CURLOPT_USERAGENT, $this->agent);
			curl_setopt($this->curl, CURLOPT_VERBOSE, true);

			$url = parse_url($this->url);
			if($url['scheme'] == 'https'){
				curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);
			}

			if($this->method == 'POST') {
				curl_setopt($this->curl, CURLOPT_POST, true);
		        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->args);
			}

            if ($this->method == 'PUT') {
                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "PUT");
                if (is_array($this->args) == true) {
    		        curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($this->args));
                }
            }

			$this->result = curl_exec($this->curl);
			$this->info = curl_getinfo($this->curl);

			return $this->getResult();
		}

		public function close() {
			curl_close($this->curl);
		}

		public function getResult($get_all = false) {
			if($this->info['http_code'] == '200' || $get_all == true) {
				return $this->result;
			} else {
				return false;
			}
		}

		public function getInfo() {
			if(is_array($this->info)) {
				return $this->info;
			}
			return false;
		}
	}

?>