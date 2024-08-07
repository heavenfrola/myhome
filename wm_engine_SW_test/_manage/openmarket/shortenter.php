<?PHP

	if(!isTable($tbl['urlshortenter'])) {
		include_once $engine_dir.'/_config/tbl_schema.php';
		$pdo->query($tbl_schema['urlshortenter']);
	}

	$sql = "select * from $tbl[urlshortenter] where 1 $w order by no desc";

	include $engine_dir."/_engine/include/paging.php";
	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);

	if($page<=1) $page=1;
	if(!$row) $row=10;
	$block=20;
	foreach($_GET as $key => $val) {
		if($key == 'page') continue;
		$QueryString .= "&$key=".urlencode($val);
	}

	$NumTotalRec = $pdo->row("select count(*) from $tbl[urlshortenter] where 1 $w");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql .= $PagingResult['LimitQuery'];

	$pg_res = $PagingResult[PageLink];
	$res = $pdo->iterator($sql);
	$idx = $NumTotalRec-($row*($page-1));

	function parseShortUrl($url) {
		$url = parse_url($url);
		return $url['host'].$url['path'];
	}

?>
<div class="msg_topbar sub quad warning">
	구글 단축URL 서비스 종료 안내<br><br>
	2019년 3월 31일 구글 단축URL 서비스 공식 종료에 따라 단축URL 등록(Google)이 삭제될 예정입니다.<br>
	이에 따른 Bitly 단축URL 서비스를 제공하오니 참고 부탁드립니다. <a href="?body=openmarket@bitly_shortenter" class="list_move">바로가기</a><br>
	<a onclick="$('.msg_topbar').slideUp('fast');" class="close">닫기</a>
</div>

<table class="tbl_col">
    <caption>단축URL 목록(Google)</caption>
	<colgroup>
		<col style="width:150px">
		<col>
		<col>
		<col style="width:80px">
		<col style="width:160px">
		<col style="width:140px">
	</colgroup>
	<thead>
		<tr>
			<th scope="col">페이지명</th>
			<th scope="col">단축 대상 URL</th>
			<th scope="col">단축 URL</th>
			<th scope="col">접속통계</th>
			<th scope="col">작성자</th>
			<th scope="col">생성일시</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($res as $data) {?>
		<tr>
			<td><?=stripslashes($data['title'])?></td>
			<td class="left"><a href="<?=$data['longUrl']?>" target="_blank"><?=$data['longUrl']?></a></td>
			<td class="left">
				<a href="#copy" class="box_btn_s clipboard btnp" data-clipboard-text="<?=$data['shortUrl']?>"><span>복사하기</span></a>
				<a href="<?=$data['shortUrl']?>" target="_blank"><?=$data['shortUrl']?></a>
			</td>
			<td><a href="https://goo.gl/#analytics/<?=parseShortUrl($data['shortUrl'])?>/all_time" target="_blank" class="box_btn_s"><span>보기</span></a></td>
			<td><?=$data['admin_id']?></td>
			<td><?=date('Y-m-d H:i', $data['reg_date'])?></td>
		</tr>
		<?}?>
	</tbody>
</table>

<div class="box_bottom">
	<?=$pg_res?>
</div>
<script type="text/javascript">
new Clipboard('.clipboard').on('success', function(e) {
	window.alert('코드가 복사되었습니다.');
});
</script>