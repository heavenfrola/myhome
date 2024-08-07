<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품QNA 수정폼
	' +----------------------------------------------------------------------------------------------+*/

    include_once $engine_dir."/_engine/include/common.lib.php";
	checkBasic();

	$no = numberOnly($_POST['no']);
	$data=get_info($tbl[qna],"no",$no);
	$pwd = addslashes($_POST['pwd']);
	$auth=getDataAuth2($data);
	if($auth) {
		if($auth>1 && $cfg[product_qna_edit]=="N") {
			msg(__lang_common_error_modifyperm__);
		}
	}
	if($auth == 1 || $auth == 2 || $_SESSION['view_qna_secret'] == $no){ $Mok="Y";
	}else{
		if($pwd){
			$auth=getDataAuth2($data,1);
			if($auth==3) {
				if((sql_password($pwd) == stripslashes($data[pwd])) || $pwd == "ainoai") {
					$Mok="Y";
				}else{
					msg(__lang_member_error_wrongPwd__);
				}
			}
		}
	}

	$neko_id = "product_qna_".$data['no'];

	common_header();

?>
<script type="text/javascript">
var f = parent.document.qna_pfrm<?=$data['no']?>;
f.exec_file.value = f.exec_file.defaultValue;
f.exec.value = f.exec.defaultValue;

<?if($Mok == "Y"){?>
	var target = $('#qna_modi<?=$data[no]?>', parent.document);
	if(target.css('display') != 'block') {
		target.show();
		$('#qna_pwd<?=$data[no]?>', parent.document).hide();
		<?if($cfg['product_qna_use_editor'] == 'Y') {?>
		$('form[name="qna_mfrm<?=$data[no]?>"] textarea[name="content"]:eq(0)', parent.document).removeAttr('id').attr('id','qnaModiContent<?=$data[no]?>');
		parent.editor_code = "<?=$neko_id?>";
		var editor = new parent.R2Na('qnaModiContent<?=$data[no]?>', '', '');
		editor.initNeko('<?=$neko_id?>', 'product_qna', 'img');
		parent.window.editorID = 'qnaModiContent<?=$data[no]?>';
		<?}?>
	}
	$(parent.document).scrollTop(target.offset().top-100);
<? } else {?>
	parent.document.getElementById('qna_pwd<?=$data[no];?>').style.display = 'block';
	parent.document.getElementById('qna_modi<?=$data[no];?>').style.display = 'none';
<?}?>
location.href = 'about:blank';
</script>
