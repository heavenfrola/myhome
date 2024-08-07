<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  string getClickPrd(void) - 최근클릭상품 쿠키 파싱
	' +----------------------------------------------------------------------------------------------+*/
	function getClickPrd($data = null) {
		if(is_null($data)) $data = $_COOKIE['click_prd'];

		$click_prd = array();
		$data = trim($data, '_');
		if($data) {
			$click_prd = explode('_', $data);
			$click_prd = array_unique($click_prd);
			$click_prd = array_reverse($click_prd);
		}
		return $click_prd;
	}


	/* +----------------------------------------------------------------------------------------------+
	' |  array clickPrdLoop(int 제목길리, int 이미지가로, Int 이미제세로, int 출력개수, int 이미지(대/중/소) - 최근본 상품 리스트 출력
	' +----------------------------------------------------------------------------------------------+*/
	function clickPrdLoop($title_cut="",$w=60,$h=60,$limit=4,$img_no=1) {
		global $tbl,$_click_prd,$cpii,$r_click_prd,$cfg, $pdo;

		if($cpii+1>$limit) return;

		if(is_array($_click_prd)) {
            $no = current($_click_prd);
            next($_click_prd);
		}

		if(!$no) return;

		if(!function_exists('prdOneData')) {
			include $GLOBALS['engine_dir'].'/_engine/include/shop.lib.php';
		}

		$data = $pdo->assoc("select * from $tbl[product] where no='$no'");
		$data = prdOneData($data, $w, $h, 3, $title_cut);

		$data['link'] = $GLOBALS['root_url'].'/shop/detail.php?pno='.$data['hash'];
		$data['name_link']="<a href=\"".$data['link']."\">".$data['name']."</a>";
		$data['imgr_link']="<a href=\"".$data['link']."\"><img src='$data[img]' $data[imgstr]></a>";

		return $data;
	}

	$_cp_name=array();
	$_cp_name['link']='링크';
	$_cp_name['img']='이미지';
	$_cp_name['imgstr']='이미지사이즈';
	$_cp_name['name']='상품명';
	$_cp_name['sell_prc']='상품가격';


	/* +----------------------------------------------------------------------------------------------+
	' |  string clickPrdLoop2() - 최근본상품 출력(js)
	' +----------------------------------------------------------------------------------------------+*/
	function clickPrdLoop2($mode=1,$img_no=3) {
		global $tbl,$_click_prd,$cpii,$r_click_prd,$root_dir,$cfg,$_cp_name,$_click_prd_cache, $pdo;

		$title_cut=$cfg['today_click_title_cut'];
		$w=$cfg['today_click_img_width'];
		$h=$cfg['today_click_img_height'];
		$limit=$cfg['today_cilck_limit'];

		if($mode==1 && $cpii+1>$limit) return;

		if(is_array($_click_prd)) {
			if(!$r_click_prd) {
				$r_click_prd=array_reverse($_click_prd);
			}
			$no = current($_click_prd);
			next($_click_prd);
		}

		if(!$no) return;

		$data = $pdo->assoc("select * from {$tbl['product']} where no='$no'");

		$data['name']=stripslashes($data['name']);
		if($title_cut>0) $data['name']=cutStr($data['name'],$title_cut);
		$data['sell_prc']=number_format($data['sell_prc']);

		$img=prdImg($img_no,$data,$w,$h);
		$data['img']=$img[0];
		$data['imgstr']=$img[1];
		$data['imgr']="<img src=\"$img[0]\" $img[1]>";

		$data['link']=$GLOBALS['root_url']."/shop/detail.php?pno={$data['hash']}&cno1={$data['big']}"; // 2007-04-30 cno1 추가

		$tmp_file=file($root_dir."/_include/click_prd.php");
		foreach($tmp_file as $key=>$val) {
			$r.=$val;
		}

		foreach($_cp_name as $key=>$val) {
			$r=str_replace("{".$val."}",$data[$key],$r);
		}

		if($mode==1) {
			$r="<div id=\"click_prd_title".$cpii."\">".$r."</div>";
		}
		else {
			$r=str_replace("\n","",$r);
			$r=str_replace("\r","",$r);
			$r=addslashes($r);
		}

		$cpii++;
		return $r;
	}

?>