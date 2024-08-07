<?PHP

	use Wing\common\Xml;

	if(!$cfg['logger_heatmap_cusId'] || !$cfg['logger_heatmap_PASSWORD']) msg('서비스 신청 후 이용해 주세요.', '?body=log@heatmap_apply');

	$args  = 'param=paymentList';
	$args .= '&cusId='.$cfg['logger_heatmap_cusId'];
	$args .= '&cusPw='.$cfg['logger_heatmap_PASSWORD'].'_'.time();
	$result = comm('http://www.heatmap.co.kr/heatmap/register.do', $args);

	$xml = new Xml($result);
	$data = $xml->arr->heatmap[0];

	if($data->result[0] != 'SUCCESS') {
		?>
		<div class="alertBox">
			설정 데이터를 가져오는데 실패하였습니다.
			<a href="#" onclick='location.reload(); return false;' class='sclink blank'>새로고침</a>
		</div>
		<?
		return;
	}

	foreach($data as $key => $val) {
		echo "$key => $val<br />";
	}

?>