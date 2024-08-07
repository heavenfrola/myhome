<?PHP

	if(!isTable($tbl['bitly_shortenter'])) {
		include_once $engine_dir.'/_config/tbl_schema.php';
		$pdo->query($tbl_schema['bitly_shortenter']);
	}

	$sql = "select * from $tbl[bitly_shortenter] where 1 $w order by no desc";

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

	$NumTotalRec = $pdo->row("select count(*) from $tbl[bitly_shortenter] where 1 $w");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql .= $PagingResult['LimitQuery'];

	$pg_res = $PagingResult[PageLink];
	$res = $pdo->iterator($sql);
	$idx = $NumTotalRec-($row*($page-1));

	function parseShortUrl($url) {
		$url = parse_url($url);
		return $url['path'];
	}

?>
<form method='post' action="./index.php" class="register" target='hidden<?=$now?>' onsubmit="return confirm('등록한 URL의 삭제 및 취소는 불가능 합니다.\n입력하신 내용이 정확합니까?')">
	<input type="hidden" name='body' value='openmarket@shortenter.exe' />
	<input type="hidden" name='exec' value='bitly' />

	<table class="tbl_row">
		<caption>단축URL 등록(Bitly)</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th>Bitly ACCESS TOKEN</th>
			<td>
				<input type="text" name="bitly_token" value="<?=$cfg['bitly_token']?>" class="input" size="50">
				<ul class="list_info">
					<li>
						회원가입 후 Developers Settings > API 페이지 내 Acess token을 발급 하여 입력해 주세요. <a href="https://app.bitly.com/" target="_blank">바로가기</a>
					</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">페이지명</th>
			<td>
				<input type="text" name='title' value='' class='input' size='50' />
			</td>
		</tr>
		<tr>
			<th scope="row">단축 대상 URL</th>
			<td>
				<input type="text" name='longUrl' value='' class='input' size='50' />
				<ul class="list_info">
					<li>동일한 단축 대상 URL로 2개 이상 단축URL 생성은 불가능합니다.</li>
				</ul>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<br>
<table class="tbl_col">
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
			<td><a href="https://app.bitly.com/Bj3b6SfwlVm/bitlinks<?=parseShortUrl($data['shortUrl'])?>" target="_blank" class="box_btn_s"><span>보기</span></a></td>
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