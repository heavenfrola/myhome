<?PHP

	if(!$skin) $skin = $_GET['skin'];

?>
<form name="file_frm" action="<?=$PHP_SELF?>" method="post" enctype="multipart/form-data" target="hidden<?=$now?>" onsubmit="return ckFrm(this);">
<input type="hidden" name="body" value="design@image_ftp.exe">
<input type="hidden" name="exec" value="upload">
<input type="hidden" name="skin" value="<?=$skin?>">
<input type="hidden" name="rdir" value="">
	<?if(!$pageIn){ ?>
	<div class="box_title first angle">
		<h2>이미지 FTP 접속</h2>
	</div>
	<?}?>
	<div class="box_middle">
		<ul class="list_msg left">
			<li>웹 FTP 접속을 실행합니다</li>
			<li>폴더를 삭제할 경우, 폴더안에 파일이나 디렉토리 존재시에 삭제되지 않습니다.</li>
		</ul>
	</div>
	<div id="file_list" class="box_middle left"></div>
	<div class="box_middle">
		파일업로드 : <input type="file" name="upfile" style="width:550px;" class="input"> <span class="box_btn_s"><input type="submit" value="파일올리기"></span>
	</div>
	<div class="pop_bottom top_line">
		<span class="box_btn gray"><input type="button" value="새로고침" onclick="getFileList(_rdir);"></span>
		<span class="box_btn gray"><input type="button" value="새폴더" onclick="getFileList('', '&exec=newdir&rdir='+_rdir);"></span>
		<span class="box_btn gray"><input type="button" value="창닫기" onclick="window.close();"></span>
	</div>
</form>

<script type="text/javascript">
	var _rdir="";
	var _skin='<?=$skin?>';
	function getFileList(rdir,add){
		if(!rdir) rdir="";
		if(!add) add="";

		_rdir=rdir;
		$.get("./?body=design@image_ftp.exe&skin="+_skin+"&rdir="+rdir+add, function(ajax) {
			if(add){
				var dir = ajax.split("=");
				var msg = dir[0].split("-");
				if(msg[0] == "error"){
					window.alert("FTP 실행이 실패하였습니다 - [F00"+msg[1]+"]   \n\n   1:1고객센터 문의 글로 접수 바랍니다.");
				}
				getFileList(dir[1]);
				return;
			}
			$('#file_list').html(ajax);
		});
	}
	function fileEdit(m,w,n){
		w1=document.getElementById("s"+n);
		w2=document.getElementById("h"+n);
		if(m == "modify"){
			w1.style.display="none";
			w2.style.visibility="visible";
			w2.select();
		}
		if(m == "delete"){
			if(!confirm("삭제하시겠습니까?")) return;
			getFileList('', '&exec='+m+'&w='+w);
		}
	}
	getFileList(_rdir);
	function ckFrm(f){
		if(!f.upfile.value){
			alert("업로드하실 파일을 선택하세요");
			return false;
		}
		f.rdir.value=_rdir;
	}
	function copyImgUrl(w){
		w="<img src=\""+w+"\">";
		window.clipboardData.setData('Text',w);
		alert("복사되었습니다. 원하시는 위치에 붙여넣기 하시기 바랍니다   ");
	}

	new Clipboard('.clipboard').on('success', function(e) {
		window.alert('코드가 복사되었습니다.');
	});
</script>