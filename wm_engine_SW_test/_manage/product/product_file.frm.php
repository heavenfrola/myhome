<style type="text/css" title="">
body {background:none;}
</style>
<?PHP

	$stat = numberOnly($_GET['stat']);
	$pno = numberOnly($_GET['pno']);
	$filetype = numberOnly($_GET['filetype']);
	$content_id = $_GET['content_id'];

	checkBlank($stat,"필수값(stat)을 입력해주세요.");
	checkBlank($pno,"필수값(pno)을 입력해주세요.");
	$sort_field = fieldExist($tbl['product_image'], "sort");

	if($filetype == 7 || $filetype == 9) {
		$xmlPath = urlencode("/main/exec.php?exec_file=_uploader/upload_product_wdisk.xml.php");
		include_once $engine_dir.'/_manage/product/product_hdd.exe.php';
		if(!$wdisk[0]->img_limit[0]) {
			echo "<span>윙Disk가 신청되지 않았거나 이용기간이 만료되었습니다.</span><span class='box_btn_s blue'><a href='./?body=wing@service_account' target='_blank'>윙Disk 신청</a></span>";
			return;
		}
	}
	else if($filetype == '8') {
		$xmlPath = urlencode("/main/exec.php?exec_file=_uploader/upload_product_wdisk_attach.xml.php");
		include_once $engine_dir.'/_manage/product/product_hdd.exe.php';
		if(!$wdisk[0]->img_limit[0]) {
			echo "<span>윙Disk가 신청되지 않았거나 이용기간이 만료되었습니다.</span><span class='box_btn_s blue'><a href='./?body=wing@service_account' target='_blank'>윙Disk 신청</a></span>";
			return;
		}
	} else $xmlPath = urlencode("/main/exec.php?exec_file=_uploader/upload_product_file.xml.php");

	switch($filetype) {
		case '2' :
			$kwd = 'up_aimg';
			$img_type = 'aimg';
			$up_body = 'product@product_file.exe';
			if($_SESSION['mall_goods_idx'] >= 4) {
				$filetypes = '2, 8';
			}
		break;
		case '3' :
			$kwd = 'up_fdisk';
			$img_type = 'dimg';
			$up_body = 'product@product_file.exe';
			if($_SESSION['mall_goods_idx'] >= 4) {
				$filetypes = '3, 9';
			}
		break;
		case '6' :
			$kwd = 'm_up_fdisk';
			$img_type = 'dimg';
			$up_body = 'product@product_file.exe';
			if($_SESSION['mall_goods_idx'] >= 4) {
				$filetypes = '6, 7';
			}
		break;
		case '4' :
			$sno = numberOnly($_GET['sno']);
			$ino = numberOnly($_GET['ino']);
			$ores = $pdo->iterator("select no, iname from $tbl[product_option_item] where opno='$sno' order by sort asc");
            foreach ($ores as $idata) {
				if(!$ino) $ino = $idata['no'];
				$option_items[$idata['no']] = stripslashes($idata['iname']);
			}
			$kwd = 'up_aimg_'.$sno;
			$img_type = 'aimg';
			$up_body = 'product@product_file.exe';
			$file_w = " and option_item_no='$ino'";
		break;
		case '7' :
			$kwd = 'm_up_wdisk';
			$img_type = 'dimg';
			$up_body = 'product@product_wdisk.exe';
		break;
		case '8' :
			$kwd = 'up_aimg_wdisk';
			$up_body = 'product@product_wdisk_attach.exe';
			$img_type = 'aimg';
		break;
		case '9' :
			$kwd = 'up_wdisk';
			$img_type = 'dimg';
			$up_body = 'product@product_wdisk.exe';
		break;
		default  : $kwd = '';
	}
	if(isset($filetypes) == false) $filetypes = $filetype;

	preg_match('/MSIE ([0-9.]+)/', $_SERVER['HTTP_USER_AGENT'], $agent);
	if($agent[0]) {
		$ie_ver = floor($agent[1]);
		if($ie_ver > 0 && $ie_ver < 10) $_COOKIE['mode_'.$kwd] = 1;
	}

	if($_COOKIE['mode_'.$kwd] == 1) {
		$form1 = 'block';
		$form2 = 'none';
	} else {
		$form1 = 'none';
		$form2 = 'block';
	}

?>
<script type="text/javascript">
new Clipboard('.clipboard');
</script>
<div id="productUploader" style="background:#fff;">
	<div id="fileProgressBar" style="position:absolute; height:20px;"></div>
	<?php if (is_array($option_items) == true && count($option_items) > 0) { ?>
	<div style="margin: 5px;">
		<select name="ino" onchange="location.href='?body=<?=$_GET['body']?>&filetype=4&sno=<?=$sno?>&stat=<?=$stat?>&pno=<?=$pno?>&ino='+this.value">
			<?php foreach ($option_items as $key => $val) { ?>
			<option value="<?=$key?>" <?=checked($key, $ino, true)?>><?=$val?></option>
			<?php } ?>
		</select>
		<ul class="list_msg">
			<li>부가 이미지를 업로드할 옵션아이템을 선택해 주세요.</li>
		</ul>
	</div>
	<?php } ?>
	<table width="99%">
		<tr>
			<td style="vertical-align:top;">
				<form name="sortPrdImgFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
				<input type="hidden" name="body" value="product@product_file_sort.exe">
				<input type="hidden" name="pno" value="<?=$pno?>">
					<ul id="icon_list" class="thumb_list thumb_list_<?=$img_type?>">
						<?PHP

							if($cfg['use_cdn'] == 'Y') {
								for($i = 1; $i <= $cfg['file_server_ea']; $i++) {
									if(in_array('attach', $file_server[$i]['file_type'])) {
										$file_server_url = $file_server[$i]['url'];
									}
								}
							} else {
								if($cfg['ssl_type'] == 'Y' || preg_match('/mywisa\.com/', $manage_url)) {
                                    //SSL사용중이거나, 임대몰 관리자인 경우
									$file_server_url = "https://img.mywisa.com/img/".$account_id;
								} else {
									$file_server_url = "http://".$account_id.".img."._BASE_DOM_SUFFIX_;
								}
							}

							$ii=0;
							if($sort_field) {
								$orderby = "order by `sort` asc, `no` desc";
							} else {
								$orderby = "order by `no` desc";
							}
							$res = $pdo->iterator("select * from `".$tbl['product_image']."` where `pno`='$pno' and `filetype` in ($filetypes) $file_w $orderby");
                            foreach ($res as $data) {
								if($data['filetype'] == 7 || $data['filetype'] == 8 || $data['filetype'] == 9) $file_dir = $file_server_url;
								else $file_dir = ($cfg['use_icb_storage'] == 'Y' && $data['upurl']) ? $data['upurl'] : getFileDir($data['updir']);

								$upfile = $file_dir.preg_replace('/\/+/', '/', "/$data[updir]/".rawurlencode($data['filename']));
								$escape = $file_dir.preg_replace('/\/+/', '/', "/$data[updir]/".rawurlencode($data['filename']));
								$ext = getExt($data['filename']);

								$iinfo = setImageSize($data['width'], $data['height'], 100, 100);
								$ii++;

						?>
						<li>
							<input type="hidden" name="no[]" value="<?=$data['no']?>">
							<input type="hidden" name="sort_now[]" value="<?=$data['sort']?>">
							<div class="img">
								<a href="<?=$upfile?>" target="_blank" ><img src="<?=$upfile?>" alt="" style="width:<?=$iinfo[0]?>px; height:<?=$iinfo[1]?>"></a>
								<a href="javascript:delPrdAttatch('<?=$data['no']?>')" class="delete" title="삭제"></a>
								<?php if($sort_field) { ?>
								<p class="move"><a href="<?=$upfile?>" target="_blank"><span>드래그로 순서변경</span></a></p>
								<?php } ?>
							</div>
							<?php if ($filetype != 2 && $filetype != 4 && $filetype != 8) { ?>
							<div class="btn">
								<a href="#" onclick="return false;" class="clipboard" data-clipboard-text="<img src='<?=$escape?>'>">태그</a> |
                                <a
                                    href="#"
                                    onclick="appendUploadedImage('<?=$content_id?>', '<?=$data['no']?>', '<?=$escape?>'); return false;">
                                    삽입
                                </a>
							</div>
							<?php } ?>
						</li>
						<?php } ?>
					</ul>
				</form>
			</td>
			<td style="width:<?php if($form1 == 'block' ) { ?>310px<?php } else { ?>100px<?php } ?>; text-align:right; vertical-align:top;">
				<div style="display:<?=$form1?>">
					<form method="post" enctype="multipart/form-data" target="hidden<?=$now?>">
						<input type="hidden" name="body" value="<?=$up_body?>">
						<input type="hidden" name="pno" value="<?=numberOnly($_GET['pno'])?>">
						<input type="hidden" name="upload_one" value="Y">
						<input type="hidden" name="filetype" value="<?=numberOnly($_GET['filetype'])?>">
						<input type="hidden" name="ino" value="<?=$ino?>">
						<input type="file" name="upfile" class="input" size="20">
						<span class="box_btn_s blue"><input type="submit" value="업로드"></span>
					</form>
				</div>
				<div class="uploadBtnArea" style="display:<?=$form2?>">
					<span class="box_btn_up large">
						<input type="file" name="upfile[]" multiple onchange="frameUpload(this);">
					</span>
				</div>
				<?php if (!$ie_ver || $ie_ver >= 10) { ?>
				<div style="padding-top:10px; text-align:right;">
					<span class="box_btn_s">
						<?php $_ubt = $_COOKIE['mode_'.$kwd] == '' ? '일반업로드로 변경' : '멀티업로드로 변경';?>
						<input type="button" id="btn_up_aimg" value="<?=$_ubt?>" onclick="setPrdUpbt('<?=$kwd?>','<?=$_COOKIE['mode_'.$kwd]?>')">
					</span>
				</div>
				<?php } ?>
			</td>
		</tr>
	</table>
</div>

<script type="text/javascript">
	var filetype = <?=$filetype?>;
	var pno = '<?=$pno?>';
	var logcode = '<?=$now?>';

	$(window).ready(
		function() {
			$('input[id^=btn_up_]').mouseover(function() {
				var msg  = '<strong>'+this.value+'</strong>합니다.<br>';
					msg += (this.value == '멀티업로드로 변경') ? '동시에 여러개의 이미지를 업로드 할 수 있습니다.' : '동시에 하나의 이미지만 업로드 가능합니다.<br>HTML5가 작동하지 않는 브라우저에서 사용해주세요.';

				if(this.tullTipObject) parent.$('#tullTip_'+this.id).html(msg);
				else new R2Tip(this, msg, '', event);
			});

			var de = document.getElementById('productUploader');
			var up = document.getElementById('uploader');
			var il = document.getElementById('thumb_list');

			parent.$('#<?=$kwd?>').height(de.scrollHeight)
			<?php if ($filetype == 4) { ?>
			$('iframe[name=optFrame]', parent.parent.document).height(0).height($('body', parent.document).prop('scrollHeight'));				;
			<?php }?>
			if(filetype && filetype != 2) showDiskspace();

            parent.removeLoading();
		}
	);

	function showDiskspace() {
		var graph = parent.document.getElementById('hdd_filetype'+filetype);
		if(graph) {
			var percent = $.get(manage_url+'/_manage/?body=product@product_hdd.exe&viewper=true&filetype='+filetype, function(percent) {
				percent = percent.split("\n");
				graph.children[0].innerHTML = percent[0];
				graph.children[1].style.width = percent[1].toNumber()+'px';
			});
		}
	}

	function setPrdUpbt(cname) {
		var cookie = getCookie('mode_'+cname);
		var val = cookie ? '' : '1';
		setCookie('mode_'+cname, val, 365);

		parent.document.getElementById(cname).contentWindow.document.location.reload();
		$('#btn_'+cname).val(val == 1 ? '멀티업로드로 변경' : '일반업로드로 변경');
	}

    // 개별 파일 업로드 완료 후 액션
	function uploadComplete(r) {
		if(r) {
			if(r && typeof r == 'object') { // 업로드 이미지 자동 삽입
				try {
					if(filetype == 3 || filetype  == 9) {
                        appendUploadedImage('content2', r.files[0].no, r.files[0].name);
					}
					if(filetype == 6 || filetype  == 7) {
                        appendUploadedImage('m_content', r.files[0].no, r.files[0].name);
					}
				} catch(ex) {
					//
				}
			} else {
                var msg = r.replace(/\\n/g, "\n").replace(/\\t/g, "")+"\n\n";
                if (msg != window.fileMessage) {
                    window.fileMessage += msg;
                }
			}
		}
	}

    // 이미지 파일 선택 시 액션
    var uploadChanged = function() {
        window.fileMessage = '';
        parent.printLoading();
    }

    // 모든 파일 업로드 이후 액션
    var uploadCompleted = function() {
        if (window.fileMessage) {
            window.alert(window.fileMessage);
        }
		location.reload();
    }

    /**
     * 업로드
     **/
    function frameUpload(o, base64)
    {
        if (!base64) base64 = '';

        commonAjaxUpload(
            o,
            '?body=<?=$up_body?>',
            {'filetype': '<?=$filetype?>', 'pno': '<?=$pno?>', 'ino': '<?=$ino?>', 'base64': base64, 'from_ajax': 'true', 'changed':uploadChanged, 'completed':uploadCompleted},
            uploadComplete
        )
    }

	<?php if($sort_field) { ?>
	$("#icon_list").sortable({
		'placeholder': 'placeholder',
		'cursor':'all-scroll',
		'scroll': false,
		'update': function(r) {
			$.post('./index.php', $('form[name=sortPrdImgFrm]').serialize());
		}
	});
	<?php } ?>
</script>