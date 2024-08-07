/**
 * 에디터 body에 이벤트 추가
 **/
function setBodyEvent(body) {
    jQuery(body).bind({
        'drop' : function(e) {
            var data = (e.dataTransfer || e.originalEvent.dataTransfer).items;
            for (index in data) {
                var item = data[index];
                if (item.kind == 'file' && /^image\//.test(item.type) == true) {
                    if (typeof parent.pasteUpload == 'function') {
                        parent.pasteUpload(item, image_group);
                    } else {
                        pasetUpload(item);
                    }
                    return false;
                } else {
					window.alert('잘못된 파일형식입니다.');
					return false;
				}
            }
        },
        'paste' : function(e) {
            var data = (e.clipboardData || e.originalEvent.clipboardData).items;
            for (index in data) {
                var item = data[index];
                if (item.kind == 'file' && /^image\//.test(item.type) == true) {
                    if (typeof parent.pasteUpload == 'function') {
                        parent.pasteUpload(item, image_group);
                    } else {
                        pasetUpload(item);
                    }
                    return false;
                }
            }
        }
    });
}


/**
 * 붙여넣기 및 드래그로 받은 이미지를 실제 업로드
 **/
function pasetUpload(item)
{
	var dialog = document.querySelector('#se2_img_preview_frm');
	if (!dialog) {
		window.alert('현재 편집 중인 에디터에서는 이미지를 업로드 하실 수 없습니다.');
		return false;
	}
    var blob = item.getAsFile();
    var reader = new FileReader();
    reader.onload = function(event) {
        parent.commonAjaxUpload(
            null,
            root_url+'/main/exec.php',
            {
                'exec_file': 'smartEditor/upload/upload.exe.php',
                'wmode': 'upload',
                'neko_gr': image_group,
                'neko_id': editor_code,
                'base64': event.target.result
            },
            function(r) {
                if(r.files) {
                    dialog.contentWindow.location.reload();
                    for (var key in r.files) {
                        parent.parent.appendUploadedImage(contentId, r.files[key].no, r.files[key].name);
                    }
                } else {
                    window.alert(r);
                }
            }
        );
    }
    reader.readAsDataURL(blob);
}


/**
 * HTML5 업로더
 **/
function HTML5Uploader() {
	if(typeof window.FileReader != 'function') return false;

	var button = document.createElement('div');
	button.style.position = 'relative';
	button.style.width = '48px';
	button.style.height = '29px';
	button.style.background = 'url("'+engine_url+'/_engine/smartEditor/img/upload.png")';

	var input = document.createElement('input');
	input.type = 'file';
	input.multiple = true;
	input.style.position = 'absolute';
	input.style.width = '48px';
	input.style.height = '29px';
	input.style.opacity = 0;
	input.onchange = function() {
		parent.commonAjaxUpload(
			input, 
			root_url+'/main/exec.php', 
			{
				'exec_file': 'smartEditor/upload/upload.exe.php', 
				'wmode': 'upload', 
                'neko_gr': image_group,
                'neko_id': editor_code,
			}, 
			function(r) {
				if(r.files) {
					for (var key in r.files) {
						parent.parent.appendUploadedImage(contentId, r.files[key].no, r.files[key].name);
					}
					document.querySelector('#se2_img_preview_frm').contentWindow.location.reload();
				} else {
					window.alert(r);
				}
			}
		);
	}
	document.querySelector('#se2_uploader').appendChild(button);
	button.appendChild(input);
}