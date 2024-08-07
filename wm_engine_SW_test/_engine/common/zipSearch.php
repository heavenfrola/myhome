<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  모바일 버전 우편번호 검색
	' +----------------------------------------------------------------------------------------------+*/

	include $engine_dir."/_engine/include/common.lib.php";

    if ($cfg['juso_api_use'] == 'D') {
        $_line = getModuleContent(__ENGINE_DIR__.'/_engine/skin_module/default/MODULE/zip_addr_list.wsm', true);
        echo listContentSetting('<tr><tr>', $_line);

        return;
    }

	//파라미터 초기화
	$sido = addslashes(trim($_GET['sido']));
	$gugun = addslashes(trim($_GET['gugun']));
	$search = trim(addslashes($_GET['search']));
	$zip_mode = numberOnly($_GET['zip_mode']);
	$form_nm = trim(preg_replace('/[^a-z0-9_]/i', '', $_GET['form_nm']));
	$zip_nm = trim(preg_replace('/[^a-z0-9_]/i', '', $_GET['zip_nm']));
	$addr1_nm = trim(preg_replace('/[^a-z0-9_]/i', '', $_GET['addr1_nm']));
	$addr2_nm = trim(preg_replace('/[^a-z0-9_]/i', '', $_GET['addr2_nm']));
	$page = numberOnly($_GET['page']);

	//행자부 주소API 사용
	if($cfg['juso_api_use'] == "Y") {
		$juso_api['result']  = false;
		$juso_api['message'] = '';


		//검색어 미 입력시
		if(!$search) {
			if($zip_mode == 2) {
				$juso_api['message'] = "찾고자 하는 주소지의 도로명을 입력하세요.";
			} else {
				$juso_api['message'] = "찾고자 하는 주소지의 읍/면/동/리를 입력하세요.";
			}
			echo json_encode($juso_api);
			exit();
		}

		//페이징
		include_once $engine_dir."/_engine/include/paging.php";
		$row = 10;
		if($page <= 1) $page = 1;
		if(!$block) $block = 5;
		$QueryString = "&urlfix=Y&search=$search&form_nm=$form_nm&zip_nm=$zip_nm&addr1_nm=$addr1_nm&addr2_nm=$addr2_nm&zip_mode=".$zip_mode;


		$juso_confmKey = $cfg['juso_api_key'];
		$tempurl = $cfg['juso_api_url']."?currentPage=".$page."&countPerPage=".$row."&keyword=".urlencode($search);
		if($cfg['juso_api_server'] == 2) {
			$tempurl .= "&confmKey=".$juso_confmKey;
		}
		$sult = comm($tempurl);
		$sult = str_replace('&', '&amp;', $sult);
		$sult_xml = simplexml_load_string($sult, 'SimpleXMLElement', LIBXML_NOCDATA);
		$sult_json = json_encode($sult_xml);
		$sult_json = json_decode($sult_json,TRUE);
		if($sult_json['common']['errorCode']=='0') {
			//JSON파싱중 데이터가 1(row)일때는 2차 배열이 아님
			$NumTotalRec = intval($sult_json['common']['totalCount']);
			$total_page   = ceil($NumTotalRec / $row);
			$total_remainder = $NumTotalRec % $row;

			$res =array();
			if($total_page==$page && $total_remainder == 1) array_push($res,$sult_json['juso']);
			else $res = $sult_json['juso'];

			$PagingInstance = new Paging($NumTotalRec, $page, $row, $block,'loadAJAXPaging_juso');
			$PagingInstance->addQueryString($QueryString);
			$PagingResult = $PagingInstance->result('loadAJAXPaging');
			$pg_res = $PagingResult['PageLink'];

			if($res && is_array($res)){
				//엔진 스킨 사용
				$_zip_skin_name = 'zip_addr_list';
				$_replace_datavals['engine_skin'][$_zip_skin_name] = '우편번호:zipcode;주소:address;링크:link;';
				$_tmp = "";
				$_skin = getSkinCfg();

				//사용자 스킨 있을경우
				if(file_exists($_skin['folder']."/MODULE/".$_zip_skin_name.".".$_skin_ext['m'])) {
					$_line=getModuleContent($_zip_skin_name);
				} else {
					$module_src = $engine_dir."/_engine/skin_module/default/MODULE/".$_zip_skin_name.".wsm";
					$_line = getModuleContent($module_src,true);
				}
				foreach ($res as $data) {
					if($zip_mode == 2) {
						$data['address'] = $data['roadAddr'];
					} else {
						$data['address'] = $data['jibunAddr'];
					}
					$data['zipcode'] = $data['zipNo'];
					$data['link'] = "zipInputLayer('{$form_nm}','".addslashes($data['address'])."','{$data[zipNo]}');return false;";
					$_tmp .= lineValues($_zip_skin_name, $_line, $data,'engine_skin');
				}
				$_tmp = listContentSetting($_tmp, $_line);
				$juso_api['result']  = true;
				$juso_api['data']  = $_tmp.$pg_res;
			} else {
				$juso_api['message'] = "해당 검색어에 대한 주소지가 없습니다.";
			}
		} else {
			$juso_api['message'] = "[ERROR]".$sult_json['common']['errorMessage'];
		}
		echo json_encode($juso_api);
		exit();
	} else {
        // 기본 우편번호 API 미사용 시 이 위치에 커스텀
        return;
	}
?>