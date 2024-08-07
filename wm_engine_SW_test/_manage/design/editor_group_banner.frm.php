<?PHP

/**
 * 사용자 모듈 편집 / 그룹배너
 **/

use Wing\Design\BannerGroup;

$bn = new BannerGroup($type, $new_code, $cursor);
$json = $bn->getData();

// 링크 타겟
$_targets = array(
    '_self' => '같은 창',
    '_blank' => '새창',
    '_parent' => '부모',
    '_top' => '최상',
);

$upload_max_filesize = (int)ini_get('upload_max_filesize');

?>
<table class="tbl_row tbl_group_banner" style="width:900px; border-top: none;">
	<caption class="hidden">사용자 생성 코드</caption>
	<colgroup>
		<col style="width:18%">
		<col>
		<col style="width:121px">
	</colgroup>
	<tr>
		<td colspan="3"><b>이미지</b></td>
	</tr>
	<tr>
		<td colspan="3">
			<ul class="thumb_list">
				<?php while ($thumb = $bn->parse()) { ?>
				<li data-id="<?=$thumb['id']?>" class="<?=$thumb['is_active']?> banner_<?=$thumb['id']?>">
					<div class="img">
						<img src="<?=$thumb['front_image_url']?>">
						<a href="#" onclick="removeUserImage('<?=$thumb['id']?>'); return false;" class="delete" title="삭제"></a>
						<p class="move"><span>드래그로 순서변경</span></p>
					</div>
					<label><input type="checkbox" name="gb_hidden[<?=$thumb['id']?>]" value="Y" <?=checked($thumb['hidden'], 'Y')?>> 숨김</label>
				</li>
				<?php } ?>
				<li class="add">
					<div class="img">
						<i class="xi-plus-circle-o" style="font-size: 50px; margin: 25px 0;"></i>
						<input
							type="file"
							id="add_image"
							style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity:0"
							multiple
							onchange="uploadGroupBanner(this);"
						>
					</div>
				</li>
			</ul>
		</td>
		<?php while ($thumb = $bn->parse()) { ?>
		<tr class="group_banner_info banner_<?=$thumb['id']?> <?=$thumb['is_active']?>">
			<th rowspan="4">이미지 정보</th>
			<td>
				일반 이미지 : <input type="file" name="front_image[<?=$thumb['id']?>]">
			</td>
			<td style="border-left: solid 1px #d6d6d6;">
                <div class="thumb_over">
    				<img src="<?=$thumb['front_image_url']?>" class="thumb_preview">
                </div>
			</td>
		</tr>
		<tr class="group_banner_info banner_<?=$thumb['id']?> <?=$thumb['is_active']?>">
			<td>
				오버 이미지 : <input type="file" name="rollover_image[<?=$thumb['id']?>]">
			</td>
			<td style="border-left: solid 1px #d6d6d6;">
				<?php if($thumb['rollover_image']) {?>
                <div class="thumb_over">
    				<img src="<?=$thumb['rollover_image_url']?>" class="thumb_preview">
                    <a href="#" onclick="removeUserImage('<?=$thumb['id']?>', 'removeRollover'); return false;" class="delete" title="삭제"></a>
                </div>
				<?php } ?>
			</td>
		</tr>
		<tr class="group_banner_info banner_<?=$thumb['id']?> <?=$thumb['is_active']?>">
			<td colspan="2">
				링크
				<?=selectArray($_targets, 'target['.$thumb['id'].']', null, null, $thumb['target'])?>
				<input type="text" name="link[<?=$thumb['id']?>]" value="<?=inputText($thumb['link'])?>" class="input" size="50">
			</td>
		</tr>
		<tr class="group_banner_info banner_<?=$thumb['id']?> <?=$thumb['is_active']?>">
			<td colspan="2">
                <textarea name="text[<?=$thumb['id']?>]" class="txta" placeholder="추가 텍스트"><?=inputText($thumb['text'])?></textarea>
			</td>
		</tr>
		<?php } ?>
	</tr>
</table>
<script type="text/javascript">
var cursor = null;
function attachGroupBanner() {
	$('.thumb_list').sortable({
		'placeholder': 'placeholder',
		'cursor':'all-scroll',
		'scroll': false,
		'items': '> li:not(.add)',
		'update': function() {
			sortUserImage();
		}
	});

	$('.thumb_list>li:not(.add)').on('click', function() {
		cursor = $(this).data('id');
		$('.group_banner_info, .thumb_list>li').removeClass('active');
		$('.banner_'+cursor).addClass('active');
	});
	$('.banner_'+cursor).addClass('active');
}

function uploadGroupBanner(o) {
	if(o.files.length < 1) return;

	printLoading();

	var total_file_size = 0;
	var param = {'exec':'upload', 'type':'<?=$type?>', 'code':'<?=$new_code?>', 'cursor':cursor};
	var ret = [];
	var fd = new FormData();
	for(var i = 0; i < o.files.length; i++) {
		for(var key in param) {
			fd.append(key, param[key]);
		}
		fd.append("upfile"+i, o.files[i]);
		fd.append('from_ajax', 'true');
		total_file_size += o.files[i].size;
	}

	if((total_file_size/1024/1024) > <?=$upload_max_filesize?>) {
		removeLoading();
		window.alert('업로드 이미지들의 총 사이즈는 <?=$upload_max_filesize?>MB를 넘을 수 없습니다.');
		return false;
	}

	$.ajax({
		'url': './index.php?body=design@editor_group_banner.exe',
		'type':'post',
		'contentType': false,
		'processData': false,
		'async': false,
		'data': fd,
		'success': function(r) {
			reloadGroupBanner(r);
		},
	});
}

function reloadGroupBanner(r) {
	removeLoading();

	$('#add_image').val('');
	if(r.status == 'success') {
		$('.tbl_group_banner').html($(r.html).html());
		cursor = r.cursor;
		attachGroupBanner();
	} else {
		window.alert(r);
	}
}

function sortUserImage() {
	printLoading();

	var sort = '';
	$('.thumb_list>li:not(.add)').each(function() {
		sort += '@'+$(this).data('id');
	});
	$.post('./index.php?body=design@editor_group_banner.exe', {'exec':'sort', 'type':'<?=$type?>', 'code':'<?=$new_code?>', 'sort':sort, 'cursor':cursor}, function(r) {
		reloadGroupBanner(r);
	});
}

function removeUserImage(id, exec) {
	if(confirm('선택한 이미지를 삭제하시겠습니까?') == true) {
		printLoading();

        if (typeof exec == 'undefined') exec = 'remove';

		if(id == cursor) cursor = null;
		$.post('./index.php?body=design@editor_group_banner.exe', {'exec':exec, 'type':'<?=$type?>', 'code':'<?=$new_code?>', 'cursor':cursor, 'id':id}, function(r) {
			reloadGroupBanner(r);
		});
	}
}

$(function() {
	attachGroupBanner();
});
</script>
<style type="text/css">
.thumb_list {width:100%;}
.thumb_list li {margin:4px; cursor:pointer;}
.thumb_list li img {max-width:100px; max-height:100px; background: #f2f2f2; opacity:.5; filter:grayscale(100%);}
.thumb_list li.active img {opacity:1; filter:grayscale(0);}
.thumb_list li:hover img {opacity:1; filter:grayscale(0);}

.thumb_over {position:relative; width:100px; height:100px; background:#f8f8f8; text-align:center; vertical-align:middle;}
.thumb_over .delete {display:none; position:absolute; right:0; top:0; z-index:5; width:15px; height:15px; background:url('../image/common/icon_close.gif') no-repeat center #303742; color:#fff; text-align:center; line-height:15px; cursor:pointer;}
.thumb_over:hover .delete {display:inline-block;}

.group_banner_info {display:none;}
.group_banner_info.active {display:table-row;}
.thumb_preview {max-width:100px; max-height:100px;}
</style>