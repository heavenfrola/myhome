<?PHP

	function cutFile($file,$f1,$f2) {
		$pos_s = strpos($file,$f1)+strlen($f1);
		$file = substr($file, $pos_s);
		$pos_e = strpos($file, $f2);
		$file = substr($file, 0, $pos_e);

		return $file;
	}

	function removeSel($file,$st_char,$fn_char) {
		if($st_char && $fn_char) {
			$st_pos=strpos($file,$st_char);
			$file1=substr($file,0,$st_pos);
			$fn_pos=strpos($file,$fn_char)+strlen($fn_char);
			$file2=substr($file,$fn_pos);
			if($st_pos && $fn_pos) {
				$file=$file1.$file2;
			}
		}
		return $file;
	}

?>