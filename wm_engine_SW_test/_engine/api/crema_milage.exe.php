<?php
/**
 * 크리마 마일리지 API 연동
 * @박연경 <pyk87@wisa.co.kr>
 * @date 2016-01-20
 */


$urlfix = 'Y';
include $engine_dir."/_engine/include/rest_api.class.php";
include "class/crema.api.php";
include $engine_dir."/_engine/include/common.lib.php";
include $engine_dir."/_engine/include/milage.lib.php";

$product_code = numberOnly($_REQUEST['product_code']);
if($_REQUEST['user_code'] && $_REQUEST['product_code']) {
	$m_data = $pdo->assoc("SELECT no, milage, name, member_id FROM wm_member WHERE member_id='".$_REQUEST['user_code']."'");
	if($m_data['no']) {

		$p_name=$pdo->row("SELECT `name` FROM wm_product WHERE `no`='$product_code'");
		$add_title = $_REQUEST['order_code'] ."|". $p_name;
		ctrlMilage('+', '6',$_REQUEST['amount'],$m_data,$add_title,'','crema');

		$result = array('code'=>'0','msg'=>'');
		echo json_encode($result);
	}else {
		$result = array('code'=>'201','msg'=>'회원 정보 오류!');
		echo json_encode($result);
	}
}else {
	$result = array('code'=>'101','msg'=>'필수 데이터가 없습니다');
	echo json_encode($result);
}

exit;
?>