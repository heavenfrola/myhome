<?php

	switch($_POST['exec']){
		case "delivery_com_tax":

		addField($tbl['delivery_url'],'tax_use',"enum('N','Y') default 'N'");
		$pdo->query("update ${tbl['delivery_url']} set tax_use='N'");

		if(count($_POST['delivery_com']) > 0){
			foreach($_POST['delivery_com'] as $k=>$v){
				$pdo->query("update ${tbl['delivery_url']} set tax_use='Y' where no='${v}'");
			}
		}

		msg("배송사별 관세 사용 여부가 설정 되었습니다.","reload","parent");
		break;
	}
?>