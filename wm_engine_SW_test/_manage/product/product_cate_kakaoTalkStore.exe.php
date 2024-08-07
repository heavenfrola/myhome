<?PHP

	use Wing\API\Kakao\KakaoTalkStore;

	header('Content-type:application/json;');

	$kts = new KakaoTalkStore();
	exit($kts->getSubCategories($_GET['categoryId']));

?>