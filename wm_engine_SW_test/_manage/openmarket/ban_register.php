<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  배너광고코드 등록
	' +----------------------------------------------------------------------------------------------+*/

	if($_GET['no']) {
		$group = $pdo->assoc("select * from `$tbl[pbanner_group]` where `no`='$_GET[no]'");
		$group['name'] = stripslashes($group['name']);

		$banners = $pdo->iterator("select * from `$tbl[pbanner]`	 where `ref`='$_GET[no]' order by `no` asc");
	}

	$_SESSION['listURL'] = getURL();

	// 아이콘 리스트
	$icons = array();
	$dir = opendir($engine_dir.'/_manage/image/icon');
	while($read = readdir($dir)) {
		if(preg_match('/^ic_conv_/', $read)) {
			$icons[] = $read;
		}
	}
	sort($icons);
	foreach($icons as $read) {
		$ck = ($group['icon'] == $read) ? "checked" : "";

		$temp = "<li><label class='p_cursor'><input type='radio' name='icon' value='$read' $ck> <img src='$engine_url/_manage/image/icon/$read'></label></li>";
		if(preg_match('/^ic_conv_p[0-9]+/', $read)) $list_p .= $temp;
		else if(preg_match('/^ic_conv_m[0-9]+/', $read)) $list_m .= $temp;
		else if(preg_match('/^ic_conv_c[0-9]+/', $read)) $list_c .= $temp;
		else $list_etc .= $temp;
	}

?>
<form method="post" target="hidden<?=$now?>" onsubmit="return saveBanner(this)">
	<input type="hidden" name="body" value="openmarket@ban_register.exe">
	<input type="hidden" name="no" value="<?=$_GET['no']?>">
	<input type="hidden" name="listURL" value="<?=$listURL?>">
	<div class="box_title first">
		<h2 class="title">배너광고코드 등록</h2>
	</div>
	<div class="box_bottom top_line left">
		<ul class="list_info">
			<li>온라인 배너광고를 위한 광고를 등록하고, 해당 광고에 대한 배너들을 등록합니다.</li>
			<li>하나의 프로모션명 아래에 여러 개의 배너를 등록할 수 있으며, 각 프로모션 단위로 회원가입, 접속, 주문 효과를 분석하실 수 있습니다.</li>
			<li>프로모션명을 입력해야 하며, 배너광고코드에 사용할 배너광고명 및 연결 페이지 주소를 입력하시기 바랍니다.</li>
			<li>여러 개의 배너코드를 생성할 경우 배너추가를 할 수 있습니다.</li>
		<ul>
	</div>
	<div class="box_title">
		<h2 class="title">프로모션(배너그룹)</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">프로모션(배너그룹)</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">프로모션명</th>
			<td><input type="text" name="name" size="60" class="input" value="<?=$group['name']?>"></td>
		</tr>
		<?if($group['code']){?>
		<tr>
			<th scope="row">배너광고코드</th>
			<td>wsmk=<span class="p_color2"><?=$group['code']?></span></td>
		</tr>
		<?} else {?>
		<tr>
			<th scope="row">코드생성</th>
			<td>
				<label class="p_cursor"><input type="radio" name="codetype" value="1" checked> 자동생성 <span class="p_color2">(추천)</span></label><br>
				<label class="p_cursor"><input type="radio" name="codetype" value="2"> 수동생성</label>
				<input type="text" name="code" class="input" size="15">
				<span class="explain">(다른 광고코드들과 겹치지 않도록 입력해 주십시오.)</span>
			</td>
		</tr>
		<?}?>
		<tr>
			<th scope="row">아이콘 선택</th>
			<td>
				<ul class="icon_list">
					<?=$list_etc?>
				</ul>
				<ul class="icon_list" style="clear:left;">
					<?=$list_c?>
				</ul>
				<ul class="icon_list" style="clear:left;">
					<?=$list_m?>
				</ul>
				<ul class="icon_list" style="clear:left;">
					<?=$list_p?>
				</ul>
			</td>
		</tr>
	</table>
	<div class="box_title">
		<h2 class="title">배너광고코드</h2>
	</div>
	<table id="bannerFrm" class="tbl_col">
		<caption class="hidden">배너광고코드</caption>
		<colgroup>
			<col style="width:350px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col">배너광고명</th>
				<th scope="col">연결 페이지 주소(URL)</th>
			</tr>
		</thead>
		<tbody>
			<?php if($banners) { foreach ($banners as $data) {?>
			<tr>
				<td>
					<input type="hidden" name="bno[]" value="<?=$data['no']?>">
					<input type="text" name="banner[]" class="input" size="40" value="<?=$data['name']?>">
				</td>
				<td class="left">
					<input type="text" name="link[]" class="input" size="60" value="<?=$data['link']?>">
					<span class="box_btn_s"><input type="button" value="-" onclick="removeBanner(this,<?=$data['no']?>)"></span>
				</td>
			</tr>
			<?}} else {?>
			<tr>
				<td>
					<input type="hidden" name="bno[]" value="<?=$data['no']?>">
					<input type="text" name="banner[]" class="input" size="40" value="<?=$data['name']?>">
				</td>
				<td class="left">
					<input type="text" name="link[]" class="input" size="60" value="<?=$data['link']?>">
					<span class="box_btn_s"><input type="button" value="-" onclick="removeBanner(this)"></span>
				</td>
			</tr>
			<?}?>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<span class="box_btn gray"><input type="button" value="취소" onclick="location.href='<?=$listURL?>'"></span>
	</div>
</form>

<script type="text/javascript">
	function saveBanner(f) {
		if(!checkBlank(f.name, '프로모션명을 입력해주세요.')) return false;

		var banners = document.getElementsByName('banner[]');
		var link = document.getElementsByName('link[]');

		if(banners.length == 0) {
			window.alert('등록된 배너가 없습니다\n배너를 하나이상 추가해주십시오.\t');
			return false;
		}

		var check = 0;
		var no_link = 0;
		for(var i = 0; i < banners.length; i++) {
			if(banners[i].value) {
				if(link[i].value == '') no_link++;
				check++;
			}
		}
		if(check == 0) {
			window.alert('등록된 배너가 없습니다\n배너를 하나이상 추가해주십시오.\t');
			return false;
		}

		if(no_link > 0) {
			window.alert('클릭시 연결 URL 지정되지 않은 배너가 있습니다.\t\n링크 주소를 입력해 주십시오');
		}
	}

	function addBanner() {
		var table = $('#bannerFrm').find('tbody');

		var tr = document.createElement('TR');
		var td1 = document.createElement('TD');
		var td2 = document.createElement('TD');

		td1.innerHTML  = "<input type='text' name='banner[]' class='input' size='40'>";
		td2.className = 'left';
		td2.innerHTML  = "<input type='text' name='link[]' class='input' size='60'>";
		td2.innerHTML += " <span class='box_btn_s'><input type='button' value='-' onclick='removeBanner(this)'></span> ";
		tr.appendChild(td1);
		tr.appendChild(td2);
		table.append(tr);

		attachBtn();
	}

	function removeBanner(obj, no) {
		if(confirm('선택하신 배너를 삭제하시겠습니까?')) {
			var tr = obj.parentNode.parentNode.parentNode;
			tr.parentNode.removeChild(tr);

			if(no) {
				$.post('./index.php?body=openmarket@ban_register.exe&exec=bdelete&no='+no);
			}
			attachBtn();
		}
	}

	function attachBtn() {
		$('#addBtn').remove();
		var btn = $('#addBtn');
		if(btn.length == 0) {
			var btn = $(document.createElement('SPAN'));
			btn.attr('id', 'addBtn');
			btn.addClass('box_btn_s');
			btn.html("<input type='button' value='+ 배너추가' onclick='addBanner()'>");
		}

		var last = $('#bannerFrm').find('tr').last().find('td');
		last.last().append(btn);
	}

	document.body.onload = function() {
		attachBtn();
	}
</script>