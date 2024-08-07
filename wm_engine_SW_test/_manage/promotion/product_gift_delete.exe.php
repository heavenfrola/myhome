<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  사은품 삭제 처리
	' +----------------------------------------------------------------------------------------------+*/

	checkBasic();
	$gno = numberOnly($_POST['gno']);
	checkBlank($gno,"필수값을 입력해주세요.");
	$data=get_info($tbl['product_gift'],"no",$gno);
	checkBlank($data[no],"원본 자료를 입력해주세요.");

	deleteAttachFile($data[updir], $data[upfile]);

	$pdo->query("update `".$tbl[product_gift]."` set `delete`='Y',`use`='N' where `no`='$gno'");

	msg("사은품이 삭제되었습니다.","reload","parent");

?>