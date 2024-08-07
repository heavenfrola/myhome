<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  지역별 추가배송비 간편설정 처리
	' +----------------------------------------------------------------------------------------------+*/

	checkBasic();

	$no_qcheck = true;
	set_time_limit(0);
	ini_set('memory_limit', '-1');

	$result_data = array();
	function makeCSVLog($ono, $data, $msg) {
		global $result_data, $total;

		javac("parent.$('.process').html('<strong>$ono</strong> 배송지 처리 중...');");
		ob_flush();
		flush();

		$result_data[$ono][] = array(
			'data' => $data,
			'msg' => $msg,
			'name' => $data[1]
		);
	}

	$exec=$_POST['exec'];
	if($exec == "area"){
		$no = $_POST['no'];
		$area = $_POST['area'];
		$price = $_POST['price'];
		$pdo->query("delete from $tbl[delivery_area] where partner_no = '$admin[partner_no]'");
		for($ii=0; $ii<sizeof($area); $ii++){
			$_no = $no[$ii];
			$area[$ii] = addslashes($area[$ii]);
			$price[$ii] = (int)$price[$ii];
			$area[$ii]=",".$area[$ii].",";
			if($area[$ii] && $price[$ii]) {
				$pdo->query("insert into `$tbl[delivery_area]`(area, price, partner_no) values('$area[$ii]', '$price[$ii]', '$admin[partner_no]')");
			}
		}

		if(!$_POST[free_delivery_area]) $_POST[free_delivery_area]="N";
        if ($admin['partner_no'] > 0) {
            include $engine_dir . '/_partner/config/config.exe.php';
        } else {
            include $engine_dir . '/_manage/config/config.exe.php';
        }
	}else if ($exec == 'register') {
        if (empty($_POST['name']) == true) exit('배송업체를 선택해주세요.');
        if (empty($_POST['url']) == true) exit('화물추적 URL을 입력해주세요.');
        if (is_null($admin['partner_no']) == true) $admin['partner_no'] = 0;

        $bind = array(
            ':no' => ($_POST['no'] > 0) ? $_POST['no'] : ($pdo->row("select max(no) from ${tbl['delivery_url']}")+1),
            ':name' => $_POST['name'],
            ':url' => $_POST['url'],
            ':overseas_delivery' => $_POST['overseas_delivery'],
            ':partner_no' => $admin['partner_no'],
        );
		if ($_POST['no']) {
            $r = $pdo->query("update {$tbl['delivery_url']} set name=:name, url=:url, overseas_delivery=:overseas_delivery, partner_no=:partner_no where no=:no", $bind);
		} else {
            $sort = $pdo->row("select max(sort) from {$tbl['delivery_url']}");
            if(is_null($sort) == true) $sort = 0;
            $sort++;

            $r = $pdo->query("
                insert into {$tbl['delivery_url']}
                    (no, name, url, overseas_delivery, partner_no, sort)
                    values (:no, :name, :url, :overseas_delivery, :partner_no, :sort)", array_merge($bind, array(
                ':sort' => $sort
            )));
		}
        exit(($r == true) ? 'success' : 'error');

		if($overseas_delivery == 'O'){
			## 해외 배송비 기본세팅 (EMS 기준)
			if(!$no){
				include_once $engine_dir.'/_config/set.country.php'; // 국가정보

				$delivery_sample_data = unserialize($delivery_sample_data);

				$i=1;
				// 지역, 금액
				$area_arr = array();

				foreach($delivery_sample_title as $k=>$v){
					$max_no = $pdo->row("select max(no) from ${tbl['os_delivery_area']}");
					$max_no++;
					$area_arr[$k] = $max_no;

					$pdo->query("insert into ${tbl['os_delivery_area']} set no='${max_no}', delivery_com='${delivery_no}', name='".addslashes($v)."', `order`='${i}'");

					foreach($delivery_sample_data as $sk=>$sv){
						$pdo->query("insert into ${tbl['os_delivery_prc']} set area_no='${max_no}', delivery_com='${delivery_no}', weight='${sv['weight']}', price='".$sv[$k]."', `order`='".($sk+1)."'");
					}
					$i++;
				}

				// 국가
				foreach($delivery_sample_country as $k=>$v){
					$country_code = explode(',',$v);
					foreach($country_code as $sk=>$sv){
						$pdo->query("insert into ${tbl['os_delivery_country']} set area_no='".$area_arr[$k]."', delivery_com='${delivery_no}', country_code='${sv}'");
					}
				}
			}
		}
    } else if ($exec == 'remove') {
        $no = implode(',', numberOnly($_POST['no']));
        $pdo->query("delete from {$tbl['delivery_url']} where no in ($no)");
        exit;
    } else if ($exec == 'sort') {
        switch($_POST['step']) {
            case '-1' :
                $direction = '<';
                $order = 'desc';
                break;
            case '1' :
                $direction = '>';
                $order = 'asc';
                break;
        }
        $partner_no = (int) $admin['partner_no'];
        $source = $pdo->assoc("select no, sort from {$tbl['delivery_url']} where no=:no", array(':no' => $_POST['no']));
        $target = $pdo->assoc("select no, sort from {$tbl['delivery_url']} where partner_no='$partner_no' and sort $direction {$source['sort']} order by sort $order limit 1");

        if ($source['no'] > 0 && $target['no'] > 0) {
            $pdo->query("update {$tbl['delivery_url']} set sort='{$target['sort']}' where no='{$source['no']}'");
            $pdo->query("update {$tbl['delivery_url']} set sort='{$source['sort']}' where no='{$target['no']}'");
        }

        require_once 'delivery_prv.php';
	} else if($exec == 'get') {
		header('Content-type:application/json; charset='._BASE_CHARSET_);
		$no = numberOnly($_POST['no']);
		$data = $pdo->assoc("select * from $tbl[delivery_url] where no='$no'");
		exit(json_encode($data));
	} else if ($exec == 'excel') {

		$file = $_FILES['excel_file'];

		$ext = getExt($file['name']);
		if($ext != 'csv')  msg('업로드 가능한 확장자(.csv)가 아닙니다.');

		// 엑셀파일 업로드
		if($file['size'] > 0) {
			$upname = time();
			$excel_file = $upname.'.'.getExt($file['name']);
			$excel_path = $root_url.'/_data/config/'.$excel_file;

			move_uploaded_file($file['tmp_name'], $root_dir.'/_data/config/'.$excel_file);
		} else {
			msg('업로드할 파일을 선택해주세요.');
		}

		$csv_data = array();

		if(!$excel_file) msg("파일에 업로드가 정상 처리되지 않았습니다.");

		if($excel_file){
			$cnt = 0;
			$handle = fopen($root_dir.'/_data/config/'.$excel_file, "r");

			ob_end_clean();
			$total = $success = 0;

			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				//if($cnt==0) continue;

				$num = count($data);
				if(!$num || $num == 0) msg("파일에 데이터가 존재하지 않습니다.");
				for ($c=0; $c < $num; $c++) {
					$data[$c] = iconv('EUC-KR','UTF-8',$data[$c]);
				}
				if($cnt > 0) {
					$total++;
					if($cfg['adddlv_type'] == 2) {
						$data[1] = addslashes($data[1]);
						$data[2] = addslashes(trim($data[2]));
						$data[3] = addslashes(trim($data[3]));
						$data[4] = addslashes(trim($data[4]));
						$data[5] = addslashes($data[5]);
						$data[6] = addslashes($data[6]);
						$data[7] = addslashes($data[7]);
						$data[8] = addslashes($data[8]);
						if(in_array($data[2], array("서울특별시", "서울시")) !== false) $data[2] = "서울";
						if(in_array($data[2], array("세종특별시", "세종시")) !== false) $data[2] = "세종";
						if(in_array($data[2], array("부산광역시", "부산시")) !== false) $data[2] = "부산";
						if(in_array($data[2], array("대구광역시", "대구시")) !== false) $data[2] = "대구";
						if(in_array($data[2], array("인천광역시", "인천시")) !== false) $data[2] = "인천";
						if(in_array($data[2], array("광주광역시", "광주시")) !== false) $data[2] = "광주";
						if(in_array($data[2], array("대전광역시", "대전시")) !== false) $data[2] = "대전";
						if(in_array($data[2], array("울산광역시", "울산시")) !== false) $data[2] = "울산";
						if(in_array($data[2], array("경기도")) !== false) $data[2] = "경기";
						if(in_array($data[2], array("강원도")) !== false) $data[2] = "강원";
						if(in_array($data[2], array("충청북도")) !== false) $data[2] = "충북";
						if(in_array($data[2], array("충청남도")) !== false) $data[2] = "충남";
						if(in_array($data[2], array("전라북도")) !== false) $data[2] = "전북";
						if(in_array($data[2], array("전라남도")) !== false) $data[2] = "전남";
						if(in_array($data[2], array("경상북도")) !== false) $data[2] = "경북";
						if(in_array($data[2], array("경상남도")) !== false) $data[2] = "경남";
						if(in_array($data[2], array("제주특별자치도", "제주도")) !== false) $data[2] = "제주";
						if(!$data[1]) {
							makeCSVLog($data[0], $data, '배송지 별칭이 입력되지 않았습니다.');
							continue;
						}
						if(!$data[6]) {
							makeCSVLog($data[0], $data, '추가 배송비가 입력되지 않았습니다.');
							continue;
						}
						if(strpos(getAddr('sido'), '<label>'.$data[2].'</label>') === false) {
							makeCSVLog($data[0], $data, '시/도가 잘못 입력되었습니다.'.getAddr('sido').$data[2]);
							continue;
						}
						if(strpos(getAddr('gugun', $data[2]), '<label>'.$data[3].'</label>') === false && $data[3]) {
							makeCSVLog($data[0], $data, '구/군이 잘못 입력되었습니다.');
							continue;
						}
						if(strpos($data[4], ",") !== false) {
							$dong = explode(',', $data[4]);
							foreach($dong as $key => $val) {
								$val = trim($val);
								if(strpos(getAddr('dong', $data[2], $data[3]), $val.'</label>') === false && $data) {
									makeCSVLog($data[0], $data, '동이 잘못 입력되었습니다.');
									continue 2;
								}
							}
						} else {
						   	if(strpos(getAddr('dong', $data[2], $data[3]), $data[4].'</label>') === false && $data[4]) {
								makeCSVLog($data[0], $data, '동이 잘못 입력되었습니다.');
								continue;
							}
						}
						if(strpos($data[5], ",") !== false) {
							$ri = explode(',', $data[5]);
							foreach($ri as $key => $val) {
								$val = trim($val);
								if(strpos(getAddr('ri', $data[2], $data[3], $data[4]), $val.'</label>') === false && $data) {
									makeCSVLog($data[0], $data, '리가 잘못 입력되었습니다.');
									continue 2;
								}
							}
						} else {
						   	if(strpos(getAddr('ri', $data[2], $data[3], $data[4]), $data[5].'</label>') === false && $data[5]) {
								makeCSVLog($data[0], $data, '리가 잘못 입력되었습니다.');
								continue;
							}
						}
						$existence = $pdo->assoc("select addprc, no from ${tbl['delivery_area_detail']} where sido='".$data[2]."'and gugun='".$data[3]."'and dong='".$data[4]."'and ri='".$data[5]."'");
						if($existence['no']) {
							if($existence['addprc'] == $data[6]) {
								makeCSVLog($data[0], $data, '이미 추가한 배송지입니다.');
								continue;
							} else {
								$success++;
								makeCSVLog($data[1], $data, 'OK');
							    $pdo->query("update ${tbl['delivery_area_detail']} set addprc='".$data[6]."' where no='".$existence[no]."' ");
								continue;
							}
						}
						$success++;
						makeCSVLog($data[1], $data, 'OK');
						$pdo->query("insert into ${tbl['delivery_area_detail']} set name='".$data[1]."', sido='".$data[2]."', gugun='".$data[3]."', dong='".$data[4]."', ri='".$data[5]."', addprc='".$data[6]."', sort='".$data[7]."', reg_date='".$now."', partner_no='".$admin[partner_no]."'");
					} else {
						$data[1] = addslashes($data[1]);
						$data[2] = addslashes($data[2]);
					    if(!$data[1]) {
							makeCSVLog($data[0], $data, '지역명이 입력되지 않았습니다.');
							continue;
						}
						if(!$data[2]) {
							makeCSVLog($data[0], $data, '추가 배송비가 입력되지 않았습니다');
							continue;
						}
						$area = ','.$data[1].',';
						$existence = $pdo->assoc("select no, price from ${tbl['delivery_area']} where area='".$area."'");
						if($existence['no']) {
							if($existence['price'] == $data[2]) {
								makeCSVLog($data[0], $data, '이미 추가한 배송지입니다.');
								continue;
							} else {
								$success++;
								makeCSVLog($data[0], $data, 'OK');
							    $pdo->query("update ${tbl['delivery_area']} set price='".$data[2]."' where no='".$existence[no]."' ");
								continue;
							}
						}
						$success++;
						makeCSVLog($data[0], $data, 'OK');
						$pdo->query("insert into ${tbl['delivery_area']} set area='".$area."', price='".$data[2]."', partner_no='".$admin[partner_no]."'");
					}
				}
				$cnt++;
			}
			fclose($handle);


			if(count($data) == 0) msg("파일에 데이터가 존재하지 않습니다.");

			$result = json_encode(array(
				'total' => $total,
				'success' => $success,
				'datas' => $result_data
			));
		}
	}

	if($exec != 'excel') msg("","reload","parent");

?>
<form id="resultFrm" method="post" action="?body=config@delivery_fileinput_result" target="_parent">
	<input type="hidden" name="result" value="<?=htmlspecialchars($result)?>">
</form>
<script type="text/javascript">
document.querySelector('#resultFrm').submit();
</script>