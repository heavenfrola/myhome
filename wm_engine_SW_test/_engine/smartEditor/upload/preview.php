<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  게시판 에디터 첨부파일 리스트
	' +----------------------------------------------------------------------------------------------+*/

	$urlfix = 'Y';
	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/file.lib.php";

	printAjaxHeader();

?>
<link href="<?=$engine_url?>/_engine/smartEditor/css/smart_editor2.css" rel="stylesheet" type="text/css">
<div id="smart_editor2">
<?PHP

$editor_code = addslashes($_GET['editor_code']);
$contentId = $_GET['contentId'];
$i = 0;
$res=$pdo->iterator("select * from {$tbl['neko']} where `neko_id`='$editor_code'");
foreach ($res as $assoc) {
	if(!$file_url) $server_url = getFileDir($assoc['updir']);
	$file_name=$assoc['filename'];
	$file_url=$server_url."/".$assoc['updir']."/".rawurlencode($assoc['filename']);

	$scale = setImagesize($assoc['width'], $assoc['height'], 60, 60);

	$files[$i]="<p><img src=\"".$file_url."\" class=\"img_obj_".$assoc['no']."\"></p>";
	$i++;
	?>
	<ul>
		<li id="preview<?=$assoc['no']?>" style="float:left; margin:5px 0 5px 6px;">
			<ol>
				<li style="width:75px; height:60px; border:1px solid #b5b5b5; text-align:center; cursor:pointer" onclick="window.open('<?=$file_url?>');"><img src="<?=$file_url?>" <?=$scale[2]?> /></li>
				<li style="margin:5px 0 0 0; text-align:center;">
					<a href="#" onclick="parent.parent.appendUploadedImage('<?=$contentId?>', '<?=$assoc['no']?>', '<?=$file_url?>'); return false;" style="font-size:11px; color:#54730b">삽입</a> |
					<a href="#" onclick="parent.parent.seDelFile('<?=$contentId?>', '<?=$assoc['no']?>'); return false;" style="font-size:11px; color:#54730b">삭제</a>
				</li>
			</ol>
		</li>
	</ul>
	<?php } ?>
</div>