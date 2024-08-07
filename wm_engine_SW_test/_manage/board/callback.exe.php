<?PHP

	printAjaxHeader();

	if($_POST['exec'] == 'update') {
		$sql = '';
		for($i = 0; $i <= 1; $i++) {
			$const = ($i == 0) ? 's' : 'm';
			foreach($_POST['use_'.$const.'callback'] as $key => $val) {
				if(strstr($key, 'mari_')) {
					$db = str_replace('mari_', '', $key);
					$pdo->query("update mari_config set use_".$const."callback='$val' where db='$db'");
				} else {
					$_POST[$key.'_'.$const.'callback'] = $val;
				}
			}
		}

		$no_reload_config = true;
		include $engine_dir.'/_manage/config/config.exe.php';

		javac("parent.$('.layerPop').remove();");
		msg('설정이 변경되었습니다.');
	}

	if(!fieldExist('mari_config', 'use_scallback')) {
		addField('mari_config', 'use_scallback', "enum('Y', 'N') default 'N'");
		addField('mari_config', 'use_mcallback', "enum('Y', 'N') default 'N'");
	}

	$boards = $config = array();
	$boards['product_qna'] = '상품Q&A';
	$boards['1to1'] = '1:1상담';
	$boards['product_review'] = '상품후기';
	$config['product_qna_s'] = ($cfg['product_qna_scallback'] == 'Y') ? 'Y' : 'N';
	$config['product_qna_m'] = ($cfg['product_qna_mcallback'] == 'Y') ? 'Y' : 'N';
	$config['1to1_s'] = ($cfg['1to1_scallback'] == 'Y') ? 'Y' : 'N';
	$config['1to1_m'] = ($cfg['1to1_mcallback'] == 'Y') ? 'Y' : 'N';
	$config['product_review_s'] = ($cfg['product_review_scallback'] == 'Y') ? 'Y' : 'N';
	$config['product_review_m'] = ($cfg['product_review_mcallback'] == 'Y') ? 'Y' : 'N';

	$res = $pdo->iterator("select db, title, use_scallback, use_mcallback from mari_config order by title asc");
    foreach ($res as $data) {
		$boards['mari_'.$data['db']] = stripslashes($data['title']);
		$config['mari_'.$data['db'].'_s'] = ($data['use_scallback'] == 'Y') ? 'Y' : 'N';
		$config['mari_'.$data['db'].'_m'] = ($data['use_mcallback'] == 'Y') ? 'Y' : 'N';
	}

	$_use_callback = array('Y'=>'사용함', 'N'=>'사용안함');

?>
<div id="popupContent" class="layerPop popupContent" style="width:420px;">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop">신규게시글 작성 통보 설정</div>
	</div>
	<div id="popupContentArea">
		<div class="list_info">
			<p>게시판 별 SMS/메일 사용여부를 설정할 수 있습니다.</p>
		</div>
		<form method="post" action="./index.php" onsubmit="updateCallback(this);">
			<input type="hidden" name="body" value="board@callback.exe">
			<input type="hidden" name="exec" value="update">
			<table class="tbl_col">
				<caption class="hidden">신규게시글 작성 통보 설정</caption>
				<thead>
					<tr>
						<th scope="col">게시판</th>
						<th scope="col">SMS 알림</th>
						<th scope="col">메일 알림</th>
					</tr>
				</thead>
				<tbody>
					<?foreach($boards as $key => $val) {?>
					<tr>
						<th><?=$val?></th>
						<td><?=selectArray($_use_callback, 'use_scallback['.$key.']', 0, null, $config[$key.'_s'])?></td>
						<td><?=selectArray($_use_callback, 'use_mcallback['.$key.']', 0, null, $config[$key.'_m'])?></td>
					</tr>
					<?}?>
				</tbody>
			</table>
			<div class="pop_bottom">
				<span class="box_btn blue"><input type="submit" value="확인"></span>
				<span class="box_btn gray"><input type="button" value="닫기" onclick="callback.close();"></span>
			</div>
		</form>
	</div>
</div>