<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  후기수정폼 처리
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	checkBasic();

	$no = numberOnly($_POST['no']);
	$data=get_info($tbl[review],"no",$no);
	$pwd = $_POST['pwd'];
	$auth=getDataAuth2($data);
	if($auth) {
		if($auth>1 && $cfg[product_review_edit]=="N") {
			msg(__lang_common_error_modifyperm__);
		}
	}
	if($auth == 1 || $auth == 2){ $Mok="Y";
	}else{
		$auth=getDataAuth2($data,1);
		if($auth==3) {
			if((sql_password($pwd) == stripslashes($data['pwd']))) {
				$Mok="Y";
				$_SESSION['review_auth'] = $no;
			}else{
				msg(__lang_member_error_wrongPwd__);
			}
		}
	}

	$neko_id = "product_review_".$data['no'];

	if($_POST['exec'] == 'guest_edit') {
		javac("parent.writeReview(true, '{$data['pno']}', '$no', '{$_POST['rev_idx']}')");
		return;
	}

	if($_POST['exec'] == 'guest_delete') {
		$exec = $_POST['exec'] = 'delete';
		require 'review_reg.exe.php';
		return;
	}

	common_header();

?>
<script type="text/javascript">
var f = parent.document.review_pfrm<?=$data['no']?>;
f.exec_file.value = 'shop/review_edit.php';
f.exec.value = '';

<?if($Mok == "Y"){?>
	var target = $('#review_modi<?=$data[no]?>', parent.document);
	if(target.css('display') != 'block') {
		target.show();
		$('#review_pwd<?=$data[no]?>', parent.document).hide();
		<?if($cfg['product_review_use_editor'] == 'Y') {?>
		$('form[name="review_mfrm<?=$data[no]?>"] textarea[name="content"]:eq(0)', parent.document).removeAttr('id').attr('id','revModiContent<?=$data[no]?>');
		parent.editor_code = "<?=$neko_id?>";
		var editor = new parent.R2Na('revModiContent<?=$data[no]?>', '', '');
		editor.initNeko('<?=$neko_id?>', 'product_review', 'img');
		<?}?>
	}
	$(parent.document).scrollTop(target.offset().top-100);
<?} else {?>
parent.document.getElementById('review_pwd<?=$data[no];?>').style.display = 'block';
parent.document.getElementById('review_modi<?=$data[no];?>').style.display = 'none';
<?}?>
location.href = 'about:blank';
</script>
</body>
</html>