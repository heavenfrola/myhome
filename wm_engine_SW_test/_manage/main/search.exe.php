<?PHP

    use wing\common\SimpleXMLExtended;

	$st = explode(' ', microtime());

	$type = $_GET['type'];
	$search = str_replace(' ', '', $_GET['search']);
	$start = numberOnly($_GET['start']);
	if(empty($start) == true) $start = 0;

	// 메뉴 검색
	if(empty($type) == true || $type == 'menu') {
		$cnt = 0;
		$list = '';
		$xml = new SimpleXMLExtended($xml_menu_source);
		$small = $xml->xpath('//small');
		foreach($small as $_small) {
			$name = $_small->name->__toString();
			$_search = preg_quote($search);
			if($search && preg_match('/'.$_search.'/i', str_replace(' ', '', $name)) == true) {
				if($_small->hidden->__toString() == 'Y') continue;
				$cnt++;
				if(empty($type) == true && $cnt > 3) continue;;

				$link = $_small->link->__toString();
				$link = (preg_match('/^http/', $link) == true) ? $link : '?body='.$link;
				$target = $_small->target->__toString();
				$onclick = $_small->onclick->__toString();
				if($onclick) {
					$link = '#';
					$onlick .= '; return false;';
				}

				$tmp	= $_small->xpath('../../@name');
				$mpath  = $tmp[0]->name->__toString();
				$tmp	= $_small->xpath('../@name');
				$mpath .= ' > '.$tmp[0]->name->__toString();
				$mpath .= ' > '.$name;

				$name = preg_replace('/('.$_search.')/i', '<strong>$1</strong>', $name);
				$list .= "<li><a href='$link' target='$target' onclick='$onclick'>$name <span>$mpath</span></a></li>";
			}
		}
		$menu = array(
			'cnt' => $cnt,
			'list' => $list
		);
	}

	// 매뉴얼, FAQ
	if(empty($type) == true || $type == 'manual' || $type == 'faq') {
		$wem = new WeagleEyeClient($_we, 'Etc');
		$json = $wem->call('searchManual', array(
			'type' => $type,
			'pgcode_big' => $_GET['pgcode_big'],
			'search' => mb_convert_encoding($search, 'EUC-KR', 'UTF-8')
		));
		$json = json_decode($json);

		$manual_list = array();
		foreach(array('manual', 'faq') as $cate) {
			$list = '';
			if($json->{$cate}->cnt > 0) {
				foreach($json->{$cate}->list as $key => $val) {
					$_link = $val->link;
					$_title = preg_replace('/^Q\. /', '', trim($val->title));
					if($search) $_title = preg_replace('/('.$search.')/i', '<strong>$1</strong>', $_title);
					$list .= "<li><a href='#' onclick=\"goManual('$_link'); return false;\">$_title</a></li>";
				}
			}
			${$cate} = array(
				'cnt' => number_format($json->{$cate}->cnt),
				'list' => $list
			);
		}
	}

	// 회원
	if($admin['level'] < 4 && (empty($type) == true || $type == 'member')) {
		$member_cnt = 0;
		$limit = (empty($type) == true) ? ' limit 3' : " limit $start, 30";

		$list = '';
		if(mb_strlen($search, 'UTF-8') > 1) {
			$where = " and m.name like '$search%' or m.member_id like '$search%' and m.withdraw in ('N', 'D1') ";
			$member_cnt = $pdo->row("select count(*) from {$tbl['member']} m where 1 $where");
			$res = $pdo->iterator("select m.no, m.member_id, m.name, m.cell, g.name as groupname from {$tbl['member']} m inner join {$tbl['member_group']} g on m.level=g.no where 1 $where order by name asc, no asc $limit");
            foreach ($res as $data) {
				$_name = str_replace($search, "<strong>$search</strong>", $data['name']);
				$_member_id = str_replace($search, "<strong>$search</strong>", $data['member_id']);
				$_cell = str_replace($search, "<strong>$search</strong>", $data['cell']);

				$list .= "
					<li>
						<a href=\"#\" onclick=\"viewMember('{$data['no']}','{$data['member_id']}')\">
							<span class=\"name\">$_name($_member_id)</span>
							<span>{$data['groupname']}</span>
							<span>{$_cell}</span>
						</a>
					</li>
				";
				$start++;
			}
		}
		$member = array(
			'cnt' => $member_cnt,
			'list' => $list,
		);
	}

	$ed = explode(' ', microtime());

	header('Content-type:application/json;');
	exit(json_encode(array(
		'menu' => $menu,
		'manual' => $manual,
		'faq' => $faq,
		'member' => $member,
		'search' => $search,
		'elapsed' => ($ed[0]-$st[0])+($ed[1]-$st[1]),
		'paging' => $start,
	)));

?>