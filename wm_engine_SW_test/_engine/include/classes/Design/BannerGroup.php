<?php

namespace Wing\Design;

require_once __ENGINE_DIR__."/_engine/include/img_ftp.lib.php";
require_once __ENGINE_DIR__."/_engine/include/file.lib.php";

/**
 * BannerGroup
 *
 * @package  Wing\Design
 */
class BannerGroup
{
	private $browser_type;
	private $skin_name;
	private $skin_dir;
	private $code;
	private $updir;
	private $resource = null;
	private $nidx = 0;

	/**
	 * constructor
	 * @param $type		pc 또는 mobile
	 * @param $code		사용자모듈 번호
	 * @param $cursor	현재 편집 화면에서의 커서 위치
	 * @return void
	 */
	public function __construct($type, $code, $cursor = '')
    {
		global $root_dir, $design;

		require_once $root_dir.'/_skin/'.($type == 'mobile' ? 'm' : '').'config.cfg';

		$this->browser_type = $type;
		$this->skin_name = $design['edit_skin'];
		$this->skin_dir = $root_dir.'/_skin/'.$this->skin_name;
		$this->code = $code;
		$this->cursor = $cursor;
        $this->internal_dir = '_skin/'.$this->skin_name.'/img/user_group_banner/'.$this->code;
		$this->external_dir = '_data/banner/user_group_banner/'.$this->skin_name.'/'.$this->code;
	}

	/**
	 * add				현재 사용자 코드에 배너를 한 장 추가한다
	 * @param $files	$_FILES 데이터
	 */
	public function add($files)
    {
		// 업로드 폴더 생성
		if (is_dir($this->skin_dir.'/img/user_group_banner')  == false) {
			ftpMakeDir($this->skin_dir.'/img', 'user_group_banner');
		}
		if (is_dir($this->skin_dir.'/img/user_group_banner/'.$this->code) == false) {
			ftpMakeDir($this->skin_dir.'/img', 'user_group_banner/'.$this->code);
		}
		makeFullDir($this->external_dir);

		// 파일 업로드
		$json = $this->getData();
        if (count($json) == 0) {
            $json['creator'] = session_id();
        }
		foreach ($files as $key => $file) {
			$up_filename = md5($file['name'].rand(0,9999).microtime().$key);
            $obj = array(
				'name' => $up_filename.'.'.getExt($file['name']),
				'type' => $file['type'],
				'tmp_name' => $file['tmp_name'],
				'size' => $file['size'],
			);

            // 로컬 폴더
            ftpUploadFile($this->internal_dir, $obj, 'jpg|jpeg|png|gif|webp');

            // 리모트 폴더
			$res = uploadFile($obj, $up_filename, $this->external_dir, 'jpg|jpeg|png|gif|webp');

			// 그룹에 새로운 배너 추가
			$new_id = 'b'.$this->code.time().$key;
			$json[$new_id] = array(
				'front_image' => $res[0],
				'rollover_image' => '',
				'link' => '',
				'target' => '',
                'text' => '',
                'src_front_image' => getListImgURL($this->external_dir, $res[0])
			);
		}
		$this->make($json);
		$this->cursor = $new_id;
	}

	/**
	 * sorting			현재 코드 내에 있는 배너들의 순서를 재배열한다.
	 * @param $sort		@ 로 구분된 배너들의 ID 문자열
	 */
	public function sorting($sort)
    {
		$json = $this->getData();

		$tmp = array();
		$sort = explode('@', trim($_POST['sort'], '@'));
		foreach ($sort as $val) {
			$tmp[$val] = $json[$val];
		}
		$this->make($tmp);
	}

	/**
	 * removeItem				현재 코드 내의 배너를 한 장 삭제한다.
	 *
	 * @param $id				삭제할 배너의 아이디
	 * @param $is_code_remove	코드 자체를 삭제해야할 경우 일일히 make 를 실행하지 않도록 컨트롤
	 */
	public function removeItem($id, $is_code_remove = false) {
		$json = $this->getData();

		if ($json[$id]['front_image']) {
            deleteAttachFile($this->external_dir, $json[$id]['front_image']);
            deleteAttachFile($this->internal_dir, $json[$id]['front_image']);
        }
		if ($json[$id]['rollover_image']) {
            deleteAttachFile($this->external_dir, $json[$id]['rollover_image']);
            deleteAttachFile($this->internal_dir, $json[$id]['rollover_image']);
        }
		unset($json[$id]);

		if ($is_code_remove == false) $this->make($json); // 코드 전체 삭제 시 필요 없음
	}

	/**
	 * removeRollover   		현재 코드 내의 롤오버 이미지를 한 장 삭제한다
	 *
	 * @param $id				삭제할 배너의 아이디
	 */
	public function removeRollover($id) {
		$json = $this->getData();

		if ($json[$id]['rollover_image']) {
            deleteAttachFile($this->external_dir, $json[$id]['rollover_image']);
            deleteAttachFile($this->internal_dir, $json[$id]['rollover_image']);
        }
		unset($json[$id]['rollover_image']);

        $this->make($json);
	}

	/**
	 * modify					각 배너 정보 수정
	 */
	public function modify()
    {
		$json = $this->getData();

		foreach ($json as $key => $val) {
			$val['target'] = $_POST['target'][$key];
			$val['link'] =  $_POST['link'][$key];
			$val['text'] = $_POST['text'][$key];
			$val['hidden'] = $_POST['gb_hidden'][$key];

			foreach (array('front_image', 'rollover_image') as $nm) {
				if ($_FILES[$nm]['size'][$key] > 0) {
					if ($val[$nm]) deleteAttachFile($this->external_dir, $val[$nm]);
					$up_filename = md5($_FILES[$nm]['name'][$key].rand(0,9999).microtime());
                    $obj = array(
						'name' => $up_filename.'.'.getExt($_FILES[$nm]['name'][$key]),
						'type' => $_FILES[$nm]['type'][$key],
						'tmp_name' => $_FILES[$nm]['tmp_name'][$key],
						'size' => $_FILES[$nm]['size'][$key],
					);

                    // 로컬 폴더
                    ftpUploadFile($this->internal_dir, $obj, 'jpg|jpeg|png|gif|webp');

                    // 리모트 폴더
                    $res = uploadFile($obj, $up_filename, $this->external_dir, 'jpg|jpeg|png|gif|webp');

					$val[$nm] = $res[0];
                    $val['src_'.$nm] = getListImgURL($this->external_dir, $res[0]);
				} else if ($_POST['remove_'.$nm][$key] == 'Y') {
					deleteAttachFile($this->external_dir, $val[$nm]);
					$val[$nm] = '';
				}
			}
			$json[$key] = $val;
		}
		$this->make($json);
	}

	/**
	 * getData	그룹 배너 정보를 읽어서 array 형태로 반환.
	 *
	 * @return	코드 내의 모든 낱개 배너 정보를 배열로 반환
	 */
	public function getData()
    {
		$json_file = $this->skin_dir.'/user_group_banner_'.$this->code.'.json';
		if (is_file($json_file) == true) {
			$json = file_get_contents($json_file);
			$json = json_decode($json, true);
		} else {
			$json = array();
		}
		return $json;
	}

	/**
	 * make			편집된 배열 정보를 배너 정보 json 파일에 갱신
	 *
	 * @param $json	편집된 배너 배열 데이터
	 */
	private function make($json)
    {
		global $root_dir;

		$fp = fopen($root_dir.'/_data/json.tmp', 'w');
		fwrite($fp, json_encode_pretty($json));
		fclose($fp);

		ftpUploadFile($this->skin_dir, array(
			'name' => 'user_group_banner_'.$this->code.'.json',
			'tmp_name' => $root_dir.'/_data/json.tmp',
			'size' => filesize($root_dir.'/_data/json.tmp')
		), 'json');
		unlink($root_dir.'/_data/json.tmp');
	}

	/**
	 * reload	그룹배너 관리 화면을 새로고침 하기 위해 관리자 화면 로딩
	 */
	public function reload()
    {
		$type = $this->browser_type;
		$new_code = $this->code;
		$cursor = $this->cursor;

		ob_start();
		include __ENGINE_DIR__.'/_manage/design/editor_group_banner.frm.php';
		$list = ob_get_clean();

		header('Content-type:application/json;');
		exit(json_encode(array(
			'status' => 'success',
			'html' => $list,
			'cursor' => $this->cursor
		)));
	}

	/**
	 * toggle	코드의 사용여부를 토글
	 *
	 * @return	변경된 코드 사용 여부를 Y 또는 N 으로 출력
	 */
	public function toggle()
    {
		require $this->skin_dir.'/user_code.cfg';

		$use_yn = ($_user_code[$this->code]['use_yn'] == 'N') ? 'Y' : 'N';
		$_user_code[$this->code]['use_yn'] = $use_yn;

		$this->saveCfg($_user_code);

		return $use_yn;
	}

	/**
	 * removeCode	코드 내의 모든 배너를 삭제하고 코드 전체를 삭제
	 */
	public function removeCode()
    {
		global $root_dir, $_skin_ext, $ftp_ftp_con;

		require $this->skin_dir.'/user_code.cfg';

		while($data = $this->parse()) { // 첨부이미지 삭제
			$this->removeItem($data['id'], true);
		}
		ob_start();
        // wsm 파일 삭제
		ftpDeleteFile($root_dir.'/_skin/'.$this->skin_name.'/MODULE/', 'user'.$this->code.'_list.'.$_skin_ext['m']);
        // json 파일 삭제
        ftpChangeDir($root_dir.'/_skin/'.$this->skin_name);
        ftp_delete($ftp_ftp_con, 'user_group_banner_'.$this->code.'.json');
		ob_end_clean();

		unset($_user_code[$this->code]);
		$this->saveCfg($_user_code);
	}

	/**
	 * saveCfg				사용자 모듈 설정을 업데이트 한다.
	 *
	 * @param $_user_code	편집된 사용자 모듈 배열
	 */
	private function saveCfg($_user_code)
    {
		global $root_dir, $_skin_ext, $admin;

		$file_content = "<?PHP\n// 사용자 코드 설정파일 : ".date("Y-m-d H:i", time())." 변경됨 - ".$admin['admin_id']."\n\n";
		foreach ($_user_code as $key=>$val){
			foreach ($_user_code[$key] as $key2=>$val2){
				if (!$val2) continue;
				$val2 = addslashes(stripslashes($val2));
				$val2 = str_replace("\$", "\\$", $val2);
				$file_content .= "\$_user_code[$key]['$key2']=\"".$val2."\";\n";
			}
			$file_content .= "\n";
		}
		$file_content .= "?>";

		$_filebakdir = '_data/user_code_tmp.'.$_skin_ext['g'];
		fwriteTo($_filebakdir, $file_content, 'w');

		ftpUploadFile($root_dir.'/_skin/'.$this->skin_name, array(
			'name' => 'user_code.'.$_skin_ext['g'],
			'tmp_name' => $root_dir.'/'.$_filebakdir
		), $_skin_ext['g']);
		unlink($root_dir.'/'.$_filebakdir);
	}

	/**
	 * parse	코드 내의 배너 정보를 루프 형태로 파싱
	 *
	 * @return	파싱된 개별 배너 내용을 배열로 리턴
	 */
	public function parse()
    {
		if ($this->resource == null) {
			$this->resource = $this->getData();
		}

		$id = key($this->resource);
        if ($id == 'creator') {
            next($this->resource);
            $id = key($this->resource);
        }
		$data = $this->resource[$id];
		if ($data == false) {
			unset($this->resource);
			$this->nidx = 0;
			return false;
		}

		$data['nidx'] = $this->nidx;
		$data['id'] = $id;
		$data['updir'] = $this->external_dir;
		$data['upfile1'] = $data['front_image'];
		$data['upfile2'] = $data['rollover_image'];
		$data['front_image_url'] = getListImgURL('_data/banner/user_group_banner/'.$this->skin_name.'/'.$this->code, $data['front_image']);
		$data['rollover_image_url'] = getListImgURL('_data/banner/user_group_banner/'.$this->skin_name.'/'.$this->code, $data['rollover_image']);
		$data['is_active'] = ($this->cursor === $id || ($this->cursor == null && $this->nidx == 0)) ? 'active' : '';

		$this->nidx++;
		next($this->resource);

		return $data;
	}

    /**
     * migration    스킨 폴더에 있는 이미지파일을 파일서버로 업로드
     **/
    public function migration()
    {
        global $root_dir;

        makeFullDir($this->external_dir);

        $internal_dir = $root_dir.'/'.$this->internal_dir;
        $imgs = opendir($internal_dir);
        while ($file = readdir($imgs)) {
            if (is_file($internal_dir.'/'.$file) == true) {
                $info = getimagesize($internal_dir.'/'.$file);
                $up_filename = preg_replace('/\..*$/', '', $file);

                uploadFile(
                    array(
                        'tmp_name' => $internal_dir.'/'.$file,
                        'name' => $file,
                        'type' => $info['mime'],
                        'size' => filesize($internal_dir.'/'.$file)
                    ),
                    $up_filename, $this->external_dir, 'jpg|jpeg|gif|png'
                );
            }
        }
    }
}