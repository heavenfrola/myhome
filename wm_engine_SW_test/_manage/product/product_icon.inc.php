<?PHP

	function getIconTag($data) {
		global $root_url,$root_dir,$dir, $file_server_num;
		if($data[upfile]) {

			$img="/".$dir['upload']."/".$dir['icon']."/".$data['upfile'];

			fsConFolder($img);
			if ($file_server_num) {
				$file_dir = getFileDir($img);
				$r="<a href=\"".$file_dir.$img."\" target=\"_blank\"><img src=\"".$file_dir.$img."\" style=\"vertical-align:middle\"></a>";
			} else {
				$width=0;
				if(is_file($root_dir.$img)) {
					list($w, $h) = @getImageSize($root_dir.$img);
					$size = setimagesize($w, $h, 100, 30);
				}
				$r="<a href=\"".$root_url.$img."\" target=\"_blank\"><img src=\"".$root_url.$img."\" ".$size[2]." style=\"vertical-align:middle\"></a>";
			}
		}
		return $r;
	}

?>