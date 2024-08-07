<?php

	$type = (isset($_GET['type']) == true) ? $_GET['type'] : '';
	$use_yn = (isset($_GET['use_yn']) == true) ? $_GET['use_yn'] : '';

	// 편집 스킨 가져오기
	require $root_dir.'/_skin/'.($type == 'mobile' ? 'm' : '').'config.cfg';
	$edit_skin = $design['edit_skin'];
	if (empty($edit_skin) == true) $edit_skin = $design['skin'];

	// 사용자 모듈 설정 가져오기
	require $root_dir.'/_skin/'.$edit_skin.'/user_code.cfg';
	$res = array();
	foreach ($_user_code as $code => $val) {
		if ($val['code_type'] != 'is') continue;

		$cnt_all++;
		if ($val['use_yn'] == 'Y') $cnt_use++;
		else $cnt_not_use++;

		if ($use_yn == 'Y' && $val['use_yn'] != 'Y') continue;
		if ($use_yn == 'N' && $val['use_yn'] != 'N') continue;

		$val['code'] = $code;
		$res[] = $val;
	}

	$listURL = makeQueryString(true, 'use_yn');
	${'list_tab_active'.$use_yn} = 'class="active"';

	function parseCode(&$res)
    {
		$data = current($res);
		if ($data == false) return false;

		$data['use_on'] = ($data['use_yn'] == 'Y') ? 'on' : '';

		next($res);

		return $data;
	}

?>
<form id="groupBannerArea" method="POST" action="./index.php" target="hidden<?=$now?>" onsubmit="return removeCode(this)">
	<input type="hidden" name="body" value="design@editor_group_banner.exe" >
	<input type="hidden" name="exec" value="removeCode" >
	<input type="hidden" name="type" value="<?=$type?>" >
	<input type="hidden" name="use_yn" value="<?=$use_yn?>" >

	<div class="box_title first">
		<h2 class="title">그룹배너 관리</h2>
	</div>
	<div class="box_tab" style="margin-top:0">
		<ul>
			<li><a href="<?=$listURL?>" <?=$list_tab_active?>>전체<span><?=number_format($cnt_all)?></span></a></li>
			<li><a href="<?=$listURL?>&use_yn=Y" <?=$list_tab_activeY?>>사용<span><?=number_format($cnt_use)?></span></a></li>
			<li><a href="<?=$listURL?>&use_yn=N" <?=$list_tab_activeN?>>미사용<span><?=number_format($cnt_not_use)?></span></a></li>
		</ul>
	</div>
	<table class="tbl_col">
		<colgroup>
			<col style="width:50px">
			<col style="width:200px">
			<col>
			<col style="width:80px">
			<col style="width:120px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><input type="checkbox" class="all_check"></th>
				<th scope="col">코드</th>
				<th scope="col">설명</th>
				<th scope="col">사용</th>
				<th scope="col">수정/삭제</th>
			</tr>
		</thead>
		<tbody>
			<?php while ($data = parseCode($res)) { ?>
			<tr>
				<td><input type="checkbox" name="code[]" class="list_check" value="<?=$data['code']?>"></td>
				<td>{{$사용자리스트<?=$data['code']?>}}</td>
				<td class="left"><a href="#" onclick="editCode(<?=$data['code']?>); return false;"><strong><?=$data['code_comment']?></strong></a></td>
				<td>
					<div class="switch <?=$data['use_on']?>" onclick="toggleUseCode(<?=$data['code']?>, $(this))" data-expired="<?=$expired?>"></div>
				</td>
				<td>
					<span class="box_btn_s"><input type="button" value="수정" onclick="editCode(<?=$data['code']?>);"></span>
					<span class="box_btn_s gray"><input type="button" value="삭제" onclick="removeCode(<?=$data['code']?>);"></span>
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<div class="box_bottom" style="height:35px;">
		<div class="left_area">
			<span class="box_btn_s icon delete"><input type="submit" value="선택삭제""></span>
		</div>
		<div class="right_area">
			<span class="box_btn_s icon regist"><input type="button" value="등록" onclick="editCode();"></span>
		</div>
	</div>
</form>
<script type="text/javascript">
	var opener_group_banner = true; // 수정 후 새로고침 체크 용

	chainCheckbox($('.all_check'), $('.list_check'));

	function editCode(code) {
		var param = '';
		if (code) param = '&user_code='+code;
		window.open(
			'./pop.php?body=design@editor_user.frm&code_type=is&type=<?=$type?>'+param,
			'editCode',
			'top=10px, left=10px, width=850px, height=900px, status=no, toolbars=no, scrollbars=yes'
		);
	}

	function removeCode(code) {
        if (!code && $('.list_check:checked').length == 0) {
            window.alert('삭제할 그룹배너를 선택해주세요.');
            return false;
        }
		var param = '';
		if (typeof code == 'object') {
			param = $(code).serialize();
		} else {
			param = {'body':'design@editor_group_banner.exe', 'exec':'removeCode', 'type':'<?=$type?>', 'use_yn':'<?=$use_yn?>', 'code':code};
		}

		if (confirm('삭제한 그룹배너는 복구가 불가능하며, 사용중인 그룹배너를 삭제할 경우 이미지 노출되지 않습니다.\n선택하신 배너를 삭제하시겠습니까?') == true) {
			if (confirm('최종 삭제 승인하시겠습니까?\n') == true) {
				printLoading();

				$.post('./index.php', param, function(r) {
					$('#groupBannerArea').html($(r).filter('#groupBannerArea').html());
					removeLoading();
				});
			}
		}
		return false;
	}

	function toggleUseCode(code) {
		$.post('./index.php', {'body':'design@editor_group_banner.exe', 'exec':'toggle', 'type':'<?=$type?>', 'use_yn':'<?=$use_yn?>', 'code':code}, function(r) {
			$('#groupBannerArea').html($(r).filter('#groupBannerArea').html());
		});
	}
</script>