<?PHP

	common_header();

	if($cfg[design_version] == "V3") ob_start();

	include $skin_path."file_top.php";

	if($no) {
		$cdir="/board/_data/$db/$no";

		$data=$pdo->assoc("select * from `$mari_set[mari_board]` where `no`='$no' and `db`='$db'");
		if(!$data[no]) msg(__lang_board_error_noParent__, "back");
	} elseif($tmp_no) {
		$cdir="/board/_data/$db/$tmp_no";
	} else {
		msg(__lang_common_error_required__, "/");
	}

	fsConFolder($cdir);
	if ($file_server_num) {
		fileServerCon($file_server_num);
		makeFullDir($cdir);
		$ci=0;

		$url = $file_server[$file_server_num][url];
		$dirname = $file_server[$file_server_num][file_dirname];
		ftp_chdir($fs_ftp_con, "/");
		ftp_chdir($fs_ftp_con, $dirname.$cdir);
		$open_dir=ftp_nlist($fs_ftp_con, ".");

		foreach ( $open_dir as $cfile) {
			if($cfile!="." && $cfile!="..") {
				$cimg=$url.$cdir."/".$cfile;
				include $skin_path."file_loop.php";
				$ci++;
				if($ci%5==0) {
					echo "</tr><tr>";
				}
			}
		}
	} else {
		makeFullDir($cdir);
		$ci=0;
		$open_dir=opendir($root_dir."/".$cdir);
		if(!function_exists("getModuleContent")) include_once $engine_dir."/_engine/include/design.lib.php";
		while($cfile=readdir($open_dir)){
			if($cfile!="." && $cfile!="..") {
				$cimg=$cdir."/".$cfile;
				if($cfg[design_version] == "V3"){
					$data[img]=$root_url.$cimg;
					$data[imgr]="<a href=\"$data[img]\" target=\"_blank\"><img src=\"$data[img]\" width=\"100\" border=\"0\" alt=\"\"></a>";
					$data[insert_url]="javascript:parent.copyTo('$data[img]');";
					$data[del_url]="javascript:imgDel('$no','$tmp_no','$cfile');";
					if($_filine == ""){
						$_filine=getModuleContent($skin_path."file_loop.php", 1);
						$_replace_datavals[$_file_name][board_img_list_vals]="첨부이미지경로:img;첨부이미지:imgr;첨부이미지삽입경로:insert_url;첨부이미지삭제경로:del_url;";
					}
					echo lineValues("board_img_list_vals", $_filine, $data);
				}else include $skin_path."file_loop.php";
				$ci++;
				if($ci%5==0) {
					echo "</tr><tr>";
				}
			}
		}

		closedir($open_dir);
	}

	while($ci%5!=0) {
		$ci++;
		echo "<td width=\"20%\">&nbsp;</td>";
	}

	include $skin_path."file_bottom.php";

	if($cfg[design_version] == "V3"){
		$content=ob_get_contents();
		ob_end_clean();
		$_file_name="board_index.php";
		include_once $engine_dir."/_manage/skin_module/_skin_module.php";
		include_once $engine_dir."/_engine/skin_module/_skin_module.php";
		$content=contentReset($content, $_file_name);
		echo $content;
	}

?>
<script type='text/javascript'>
if(!parent.document) {
	window.alert(_lang_pack.common_error_illegalConn__);
}

function imgDel(no,tno,fname){
	if(!confirm(_lang_pack.common_confirm_delete)) {
		return;
	}

	var f = byName('mfup_frm');
	f.del_file.value=fname;
	f.mari_mode.value='write@file_exec@delete';
	f.submit();
}
</script>