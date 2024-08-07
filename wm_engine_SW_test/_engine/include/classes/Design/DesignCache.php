<?PHP

namespace Wing\Design;

use Wing\HTTP\CurlConnection;

Class DesignCache {

	const	STORAGE = '_data/cache';

	private $interval; // 캐시 갱신 주기(분)
	private $type; // 캐시 지원 범위(비회원/회원)
	private $url;
	private $cache_file;
	private $cache_path;

	public function __construct($page, $subpage = null) {
		global $cfg, $root_dir, $root_url, $member;

		if($_REQUEST['makecache'] == 'true') return false; // 캐시 구울 경우 bypass
		if(empty($cfg['cache_'.$page.'_use']) == true || empty($cfg['cache_'.$page.'_interval']) == true || empty($cfg['cache_'.$page.'_type']) == true) return false; // 설정 체크
		if($cfg['cache_'.$page.'_use'] != 'Y') return false; // 캐시 사용하지 않음

		$this->interval = $cfg['cache_'.$page.'_interval'];
		$this->type = $cfg['cache_'.$page.'_type'];

		if($member['no'] > 0 && $this->type != 'Y') {
			return false;
		}

		// 상품 카테고리별 캐시 페이지 별도 생성
		if(is_null($subpage) == false) {
			if(is_array($subpage)) $subpage = trim(implode('_', $subpage), '_');
			$page .= '_'.$subpage;

			// 2페이지는 캐싱하지 않음
			if(isset($_REQUEST['page']) == true && (int)$_REQUEST['page'] > 1) {
				return false;
			}
		}

		$cache_prefix = ($_SESSION['browser_type'] == 'pc') ? '' : 'm_';
		$this->cache_path = $root_dir.'/'.self::STORAGE.'/'.$cache_prefix.'sitecache_'.$page.'.html';

		$this->url = getURL();
		$this->url .= (strpos($this->url, '?') == true) ? '&' : '?';

		if($this->checkInterval() == false) {
			$this->makeCache();
		} else {
			$this->printCache();
		}
	}

	private function checkInterval() {
		if(file_exists($this->cache_path) == false) {
			return false;
		}
		if(strtotime('-'.$this->interval.'mins') > filemtime($this->cache_path)) {
			return false;
		}
		return true;
	}

	private function makeCache() {
		$curl = new CurlConnection($this->url.'striplayout=1&stripheader=1&makecache=true');
		$curl->exec();

		$result = $curl->getInfo();
		if($result['http_code'] == 200) {
			$fp = @fopen($this->cache_path, 'w');
			if($fp) {
				fwrite($fp, $curl->getResult());
				fclose($fp);
				chmod($this->cache_path, 0777);
			}
		}
	}

	private function printCache() {
		global $_add_content_pg;

		define('__WORKING_CACHE_PAGE__', true);
		$_add_content_pg = $this->cache_path;
	}

	public static function reset() {
		global $root_dir;

		$path = $root_dir.'/'.self::STORAGE;
		if(is_dir($path) == false) return false;

		$dir = opendir($path);
		while($name = readdir($dir)) {
			if(preg_match('/^(m_)?sitecache_.*\.html$/', $name)) {
				@unlink($path.'/'.$name);
			}
		}
		return true;
	}

    /**
     * $cfg['cache_domains']내에 등록된 다른 웹서버가 존재하는 경우 캐시 갱신 요청
     */
    public static function syncCacheDomains() {
        global $cfg;
        if ($cfg['cache_domains'][0]) {
            foreach ($cfg['cache_domains'] as $domain) {
                $url = $domain.'/main/exec.php?exec_file=common/resetCache.php&urlfix=Y';
                comm($url, NULL, 1);
            }
        }
    }
}

?>