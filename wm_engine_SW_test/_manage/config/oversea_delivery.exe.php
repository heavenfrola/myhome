<?php
	include_once $engine_dir.'/_config/set.country.php'; // 국가정보
	asort($_nations);

	checkBasic();

	$exec=$_POST['exec'];

	switch($exec){
		case 'area':

            if (empty($_POST['name']) == true) exit('배송지 별칭을 입력해주세요.');
            if (isset($_POST['nations']) == false || count($_POST['nations']) < 1) exit('배송지에 포함할 국가를 선택해 주세요.');

            $no = $_POST['no'];
			$delivery_com = $_POST['delivery_com'];

			// 지역 추가
            if ($no > 0) {
                $r = $pdo->query("update ${tbl['os_delivery_area']} set name=? where no=?", array(
                    $_POST['name'],
                    $no
                ));
            } else {
                $order = $pdo->row("select max(`order`) from {$tbl['os_delivery_area']} where delivery_com=?", array($delivery_com))+1;

                $r = $pdo->query("insert into ${tbl['os_delivery_area']} (delivery_com, name, `order`) values (?, ?, ?)", array(
                    $delivery_com,
                    $_POST['name'],
                    $order
                ));
                $no = $pdo->lastInsertId();
            }
            if ($r == false) exit('배송지 저장중 오류가 발생하였습니다.');

			// 지역에 국가 추가
            $pdo->query("start transaction");
            foreach ($_POST['nations'] as $val) {
                $exists = null;
                if ($no > 0) {
                    $exists = $pdo->row("select no from {$tbl['os_delivery_country']} where area_no=? and country_code=?", array(
                        $no,
                        $val
                    ));
                    if($exists != false) continue;
                }
                $r = $pdo->query("insert into {$tbl['os_delivery_country']} (delivery_com, area_no, country_code) values (?, ?, ?)", array(
                    $delivery_com,
                    $no,
                    $val
                ));
                if ($r == false) {
                    $pdo->query("rollback");
                    exit('배송지 저장중 오류가 발생하였습니다.');
                }
            }
            $pdo->query("commit");

            exit('success');
        case 'remove' :
            $no = implode(',', numberOnly($_POST['no']));
            $pdo->query("delete from {$tbl['os_delivery_area']} where no in ($no)");
            $pdo->query("delete from {$tbl['os_delivery_country']} where area_no in ($no)");
            break;
        case 'removeNation' :
            $pdo->query("delete from ${tbl['os_delivery_country']} where no=?", array($_POST['no']));
            break;
		case 'excel':

			$file = $_FILES['excel_file'];

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
				$row = 1;
				$handle = fopen($root_dir.'/_data/config/'.$excel_file, "r");

				while (($data = fgetcsv($handle, 40960, ",")) !== FALSE) {
					$num = count($data);

					if(!$num || $num == 0) msg("파일에 데이터가 존재하지 않습니다.");

					for ($c=0; $c < $num; $c++) {
						$csv_data[$row][] = iconv('EUC-KR','UTF-8',$data[$c]);
					}
					$row++;
				}
				fclose($handle);


				if(count($csv_data) == 0) msg("파일에 데이터가 존재하지 않습니다.");

				if(count($csv_data) > 0){
					// 기존 데이터 삭제
					$pdo->query("delete from ${tbl['os_delivery_area']} where delivery_com='${_POST['delivery_com']}'");
					$pdo->query("delete from ${tbl['os_delivery_country']} where delivery_com='${_POST['delivery_com']}'");

					// 지역명 입력
					$_no_arr = array();
					foreach($csv_data[1] as $k=>$v){
						$area_no = $pdo->row("select max(no) from ${tbl['os_delivery_area']}");
						$area_no++;
						$pdo->query("insert into ${tbl['os_delivery_area']} set no='${area_no}', delivery_com='${_POST['delivery_com']}', name='".addslashes($v)."', `order`='".($k+1)."'");
						$_no_arr[$k] = $area_no;
					}
					// 나라 코드입력
					foreach($csv_data as $k=>$v){
						if($k <= 1) continue;

						if(count($v) > 0){
							foreach($v as $sk=>$sv){
								$area_no = $_no_arr[$sk];
								if($sv) $pdo->query("insert into ${tbl['os_delivery_country']} set delivery_com='${_POST['delivery_com']}', area_no='${area_no}',country_code='".$sv."'");
							}
						}
					}
					msg("정상 업로드 되었습니다.","reload","parent");
				}
			}
			break;

		case 'excel_price':

			$file = $_FILES['excel_file'];

			// 엑셀파일 업로드
			if($file['size'] > 0) {
				$upname = time()."_price";
				$excel_file = $upname.'.'.getExt($file['name']);
				$excel_path = $root_url.'/_data/config/'.$excel_file;

				move_uploaded_file($file['tmp_name'], $root_dir.'/_data/config/'.$excel_file);
			} else {
				msg('업로드할 파일을 선택해주세요.');
			}

			$csv_data = array();

			if(!$excel_file) msg("파일에 업로드가 정상 처리되지 않았습니다.");

			if($excel_file){
				$row = 1;
				$handle = fopen($root_dir.'/_data/config/'.$excel_file, "r");

				$area_count = $pdo->row("select count(*) from ${tbl['os_delivery_area']} where delivery_com='${_POST['delivery_com']}'");

				while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
					$num = count($data);

					if(($area_count+1) != $num) msg("엑셀 데이터의 열이 일치하지 않습니다.");

					for ($c=0; $c < $num; $c++) {
						$csv_data[$row][] = iconv('EUC-KR','UTF-8',$data[$c]);
					}
					$row++;
				}
				fclose($handle);

				if(count($csv_data) == 0) msg("파일에 데이터가 존재하지 않습니다.");

				if(count($csv_data) > 0){

					// 기존 데이터 삭제
					$pdo->query("delete from ${tbl['os_delivery_prc']} where delivery_com='${_POST['delivery_com']}'");


					// 금액 코드입력
					$title_arr = array();

					// 지역 no 구해오기 ## 급해서 일단 아래와 같이 했으나 보완이 필요할듯.
					foreach($csv_data[2] as $k=>$v){
						if($k == 0) continue;
						$title_arr[$k] = $pdo->row("select no from ${tbl['os_delivery_area']} where `order`='${k}' and  delivery_com='${_POST['delivery_com']}'");
					}

					$i=1;
					foreach($csv_data as $k=>$v){
						if($k <= 2) continue;

						if(count($v) > 0){
							$weight = $v[0];
							foreach($v as $sk=>$sv){
								if($sk == 0) continue;

								$sv = str_replace(',','',$sv);
								$pdo->row("insert into ${tbl['os_delivery_prc']} set delivery_com='${_POST['delivery_com']}', area_no='".$title_arr[$sk]."', weight='${weight}', price='${sv}', `order`='${i}'");
							}
						}
						$i++;
					}
					msg("정상 업로드 되었습니다.","reload","parent");
				}
			}

			break;

		case 'prc':

			addField($tbl['os_delivery_area'],"oversea_dlv_free","enum('N','Y') default 'N'");
			addField($tbl['os_delivery_area'],"oversea_dlv_free_limit","double(10,2)");

			$pdo->query("update ${tbl['os_delivery_area']} set oversea_dlv_free='N', oversea_dlv_free_limit='0' where delivery_com='${_POST['delivery_com']}'");
			if(count($_POST['oversea_dlv_free']) > 0){
				foreach($_POST['oversea_dlv_free'] as $k=>$v){
					$pdo->query("update ${tbl['os_delivery_area']} set oversea_dlv_free='".$v."', oversea_dlv_free_limit='".numberOnly($_POST['oversea_dlv_free_limit'][$k],$cfg['m_currency_decimal'])."' where delivery_com='${_POST['delivery_com']}' and no='".$k."'");
				}
			}


			$pdo->query("delete from ${tbl['os_delivery_prc']} where delivery_com='${_POST['delivery_com']}'");

			if(count($_POST['weight']) > 0){

				foreach($_POST['weight'] as $k=>$v){
					if(count($_POST['price']) > 0){
						foreach($_POST['price'] as $sk=>$sv){
							$price = numberOnly($sv[$k],true);
							$pdo->query("insert into ${tbl['os_delivery_prc']} set delivery_com='${_POST['delivery_com']}', area_no='${sk}', weight='${v}', price='".$price."', `order`='".($k+1)."'");

						}
					}
				}
			}

			msg("입력되었습니다.","reload","parent");

			break;

		case 'transfer':

			if($cfg['m_currency_type'] && $cfg['currency_type'] != $cfg['m_currency_type']){

				$transfer_price = $pdo->row("select transfer_price from ${tbl['delivery_url']} where no='${_POST['delivery_com']}'");

				$sql = "select * from ${tbl['os_delivery_prc']} where delivery_com='${_POST['delivery_com']}'";
				$res = $pdo->iterator($sql);
				$manage_price = numberOnly($cfg['cur_manage_price'],$cfg['m_currency_decimal']);
				$sell_price = numberOnly($cfg['cur_sell_price'],$cfg['currency_decimal']);

				$cnt = $res->rowCount();
				if($transfer_price == 'N' || $transfer_price == 'W'){
                    foreach ($res as $data) {
						$price = ($data["price"]/$manage_price) * $sell_price;
						$pdo->query("update ${tbl['os_delivery_prc']} set price='${price}' where no='${data['no']}'");
					}

					$pdo->query("update ${tbl['delivery_url']} set transfer_price='Y' where no='${_POST['delivery_com']}'");
				}else{
                    foreach ($res as $data) {
						$price = ($data["price"]/$sell_price) * $manage_price;
//						$price = ceil($price/100)*100;
						$pdo->query("update ${tbl['os_delivery_prc']} set price='${price}' where no='${data['no']}'");
					}

					$pdo->query("update ${tbl['delivery_url']} set transfer_price='N' where no='${_POST['delivery_com']}'");

				}
				msg("환산이 완료되었습니다.","reload","parent");

			}else{
				msg("환산을 할수 없습니다. 관리화폐가 사용안함 이거나 결제화폐와 관리화폐가 동일합니다.");
			}

			break;

		case 'county_sample':

			$file_name="contry_code_sample_".date("Y_m_d",$now);

			header( "Content-type: application/vnd.ms-excel;charset=KSC5601" );
			header( "Content-Disposition: attachment; filename=".$file_name.".csv" );
			header( "Content-Description: Wisamall Excel Data" );

			$csv[0] = "국가코드";
			$csv[1] = "국가명";

			echo fputcsvtmp($csv);
			unset($csv);

			$i=0;
			foreach($_nations as $k=>$v){
				echo fputcsvtmp(array($k,$v));
				$i++;
			}

			break;

	}
?>