<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  게시판 에디터 첨부파일 처리
	' +----------------------------------------------------------------------------------------------+*/

    use Wing\common\EditorFile;

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/file.lib.php";

	if ($_REQUEST['wmode'] == 'upload') {
		$neko_gr = addslashes(trim($_POST['neko_gr']));
		$neko_id = addslashes(trim($_POST['neko_id']));

        if ($_POST['base64']) { // 붙여넣기 및 드래그
            $_FILES[0] = array('base64' => $_POST['base64']);
        }

		foreach($_FILES as $files) {
			$files['name'] = iconv('utf-8', _BASE_CHARSET_, $files['name']);

            if ($files['base64']) {
                $tmp = explode(';base64,', $files['base64']);
                $ext = str_replace('data:image/', '', $tmp[0]);
                if ($ext == 'jpeg') $ext = 'jpg';
                $files['name'] = md5(microtime(true)).'.'.$ext;
                $files['tmp_name'] = $root_dir.'/_data/'.$files['name'];
                $files['size'] = strlen($tmp[1]);
                $file_content = base64_decode($tmp[1]);

                if (function_exists('getimagesizefromstring') == true) {
                    $check = getimagesizefromstring($file_content); // 이미지만 업로드 가능
                    if ($check == false) {
                        msg('잘못된 파일형식입니다.');
                    }
                }

                fwriteTo('/_data/'.$files['name'], $file_content);
            }

			$updir=$pdo->row("select `updir` from `$tbl[neko]` where `neko_id` = '$neko_id'");
			if(!$updir) {
				switch($neko_gr) {
					case 'mail' :
						$updir = $dir['upload'].'/'.$neko_gr;
						$file_size_limit = $up_cfg['prdCommon']['filesize'];
					break;
					case 'product_review' :
					case 'product_qna' :
						$updir = $dir['upload'].'/editor_attach/'.$neko_gr.'/'.date('Ym/d');
					break;
					case 'content' :
						$updir = $dir['upload'].'/content/'.preg_replace('/^content_/', '', $neko_id);
					break;
					default :
						$updir = $dir['upload'].'/editor_attach/'.$neko_gr.'/'.$neko_id;
				}
			}

			$updir = preg_replace('@'.$root_dir.'/?@', '', $updir);
			$ck = getimagesize($files['tmp_name']);
			if(!$ck) {
                unlink($files['tmp_name']);
                exit('이미지만 업로드 할수있습니다');
            }

			$ext = getExt($files['name']);
			$filename = $files['name']; // 새파일명
			$filesize = filesize($files['tmp_name']);
			if($file_size_limit > 0 && $filesize > ($file_size_limit*1024)) {
				exit(iconv('EUC-KR', 'UTF-8', '업로드 가능한 파일크기가 초과되었습니다.'));
			}
            if (in_array(strtolower($ext), array('jpg', 'jpeg', 'png', 'gif')) == false) {
				exit('업로드 불가능한 파일 확장자입니다.');
            }

			makeFullDir($updir);
			$up_info = uploadFile($files, md5($files['name'].$now.rand(0,99999)), $updir);
			$filename = $up_info[0];
            if (file_exists($files['tmp_name']) == true) {
                unlink($files['tmp_name']);
            }

			$pdo->query("insert into `$tbl[neko]` (`member_id`,`neko_id`,`neko_gr`,`updir`,`filename`,`size`,`width`,`height`,`lock`,`regdate`) values ('$member[member_id]','$neko_id','$neko_gr','$updir','$filename','$filesize','$ck[0]','$ck[1]','N','$now')");

            $completed[] = array(
                'no' => $pdo->lastInsertId(),
                'name' => getListImgURL($updir, $filename)
            );
		}

		header('Content-type:application/json');
		exit(json_encode(array('count' => count($completed), 'files' => $completed)));
	}

	if($_REQUEST['wmode'] == 'delete') {
		$no = numberOnly($_GET['no']);

        $editor_file = new EditorFile();
        if ($editor_file->remove($no) == true) {
            exit('OK');
        }
        exit('DB Fail');
	}

?>