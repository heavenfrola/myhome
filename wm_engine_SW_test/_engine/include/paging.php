<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  리스트 페이징 클래스
	' +----------------------------------------------------------------------------------------------+*/

	if(defined("_page_inc")) return;
	else define("_page_inc",true);

	class Paging {
		var $total;        // number of row == count(*)
		var $row;        // display number of row
		var $block;      // display number of page block
		var $totalblock;     // total number of block
		var $current;    // current page number
		var $end;        // end page number (= total number of page)
		var $prev;       // number of previous page block
		var $next;       // number of next page block

		var $url;          // self filename == $_SERVER[PHP_SELF]
		var $param;    // parameter name of page variable
		var $qstr;        // added query string (other parameter of HTTP GET method)
		var $deco;      // decorative string of this object's shapeXXX functions
		var $alink_class; //class of a_link

		var $a_ary_Result;    // return result associative array

		// initiate member variable
		function __construct($total, $current=1, $row=10, $block=20, $alink_class='', $fname='', $id_no='')
		{
			$this->total = $total;
			$this->row = $row;
			$this->block = $block;
			$this->current = $current;
			$this->end = ceil($total/$row);
			$this->totalblock = ceil($this->end/$block);
			$this->prev = $block * (ceil($current/$block) - 1);
			$this->next = $block * (ceil($current/$block)) + 1;
			$this->fname = $fname;
			$this->idno = $id_no;
	//     $this->url = $_SERVER[PHP_SELF];
			$this->param = "page";
			$this->alink_class = $alink_class;

			$this->a_ary_Result['NumRec'] = $this->total;
			$this->a_ary_Result['NumBlock'] = $this->totalblock;
			$this->a_ary_Result['CurrentPage'] = $this->current;
			$this->a_ary_Result['EndPage'] = $this->end;
			$this->a_ary_Result['LimitIndex'] = $this->row * ($this->current - 1);
			$this->a_ary_Result['LimitNum'] = ($this->current < $this->end+1) ? $this->row : ($this->total % $this->row);
			$this->a_ary_Result['LimitQuery'] = " Limit ". $this->a_ary_Result['LimitIndex'] .", ". $this->a_ary_Result['LimitNum'];

            if (defined('__MODULE_LOADER__') == true) {
                $this->a_ary_Result['LimitQuery'] = " Limit 0, ".($this->row*$this->current);
            }

			return true;
		}

		// add query string this format : &name1=value1&name2=value2
		function addQueryString($qstr)
		{
			$this->qstr .= strip_tags($qstr);
			return true;
		}

		// set parameter name
		function setParamName($param)
		{
			$this->param = $param;
			return true;
		}

		// decorate page-number string : ... 3 4 [5] 6 7 ...
		function decoNumber($cl, $cr, $ol, $or)
		{
			$this->deco['c']['l'] = $cl;
			$this->deco['c']['r'] = $cr;
			$this->deco['o']['l'] = $ol;
			$this->deco['o']['r'] = $or;
			return true;
		}

		// decorate prefix, suffix and between page-numbers
		function decoBlock($prefix, $midfix, $suffix)
		{
			$this->deco['b']['p'] = $prefix;
			$this->deco['b']['m'] = $midfix;
			$this->deco['b']['s'] = $suffix;
			return true;
		}

		// set character which jump page-block : [<<] ... 4 5 6 7 ... [>>]
		function setJumpChar($prev, $next, $start, $end)
		{
			$this->deco['j']['p'] = $prev;
			$this->deco['j']['n'] = $next;
			$this->deco['j']['s'] = $start;
			$this->deco['j']['e'] = $end;
			return true;
		}

		// return result associative array
		function result($dmode = "")
		{
			$this->setDesign($dmode);
			$this->setPageLink();

			return $this->a_ary_Result;
		}

		// set Design
		function setDesign($dmode)
		{
			global $root_dir,$pg_dsn_file,$cfg,$_skin;

			if($cfg['design_version'] == "V3" && $_skin['pageres_design_use'] == "Y" && $dmode!="admin" && $dmode!="ajax_admin" && $dmode!="ajax_admin2"){
				if($_skin['pageres_font_size']) $_font_style="font-size:".$_skin['pageres_font_size']."pt";
				if($_skin['pageres_font_color']) $_font_color=" color=".$_skin['pageres_font_color'];
				if($_skin['pageres_this_size']) $_this_style="font-size:".$_skin['pageres_this_size']."pt";
				if($_skin['pageres_this_color']) $_this_color=" color=".$_skin['pageres_this_color'];
				if($_skin['pageres_year_size']) $_year_style=" font-size:".$_skin['pageres_year_size'].'pt';
				if($_skin['pageres_year_color']) $_year_style.=";color:".$_skin['pageres_year_color'];

				$_btn_arr=array("prev"=>$_skin['pageres_prev_text'], "next"=>$_skin['pageres_next_text'], "start"=>$_skin['pageres_start_text'], "end"=>$_skin['pageres_end_text']);
				foreach($_btn_arr as $key=>$val){
					${'_'.$key.'_btn'}=$val;
					if($_skin['pageres_'.$key.'_type'] == "img" && $_skin['pageres_'.$key.'_img']){
						${'_'.$key.'_btn'}="<img src=\"".$_skin['url']."/img/".$_skin['pageres_'.$key.'_img']."\">";
					}
				}

				$this->decoNumber("<font style=\"".$_this_style."\"".$_this_color.">".$_skin['pageres_this_deco1'], $_skin['pageres_this_deco2']."</font>", "<font style=\"".$_font_style."\"".$_font_color.">".$_skin['pageres_font_deco1'], $_skin['pageres_font_deco2']."</font>");
				$this->decoBlock("<TABLE border=0 cellspacing=0 cellpadding=3 align=center><TR><TD>", "</TD><TD>", "</TD></TR></TABLE>");
				$this->setJumpChar("<font class=small>◀</font>", "<font class=small>▶</font>", "<font class=small>FIRST</font>", "<font class=small>END</font>");
				$this->deco['j']['prev'] = "<A href=\"".$this->url."?".$this->param."=".$this->prev.$this->qstr."\" class=\"page\">".$_prev_btn."</A>";
				$this->deco['j']['next'] = "<A href=\"".$this->url."?".$this->param."=".$this->next.$this->qstr."\" class=\"page\">".$_next_btn."</A>";
				$this->deco['j']['start'] = "<A href=\"".$this->url."?".$this->param."=1".$this->qstr."\" class=\"page\">".$_start_btn."</A> ";
				$this->deco['j']['end']  = " <A href=\"".$this->url."?".$this->param."=".$this->end.$this->qstr."\" class=\"page\">".$_end_btn."</A>";
				$this->deco['c']['ThisPage'] = $this->deco['c']['l'] . $this->current . $this->deco['c']['r'];
				$this->deco['c']['PageLink'] = "<A href=\"".$this->url."?".$this->param."=@@i@@".$this->qstr."\" class=\"page\">".$this->deco['o']['l']."@@i@@".$this->deco['o']['r']."</A>";
				$this->deco['c']['year_l'] = "<a href='$this->url?$this->param=1$this->qstr&year=@@i@@' style='$_year_style' class='yearsplit'>$_skin[pageres_year_deco1]";
				$this->deco['c']['year_r'] = "$_skin[pageres_year_deco2]</a>\n";

				return true;
			}

			if(!$pg_dsn_file) $pg_dsn_file='paging.php';
			$abs_pg_dsn_file=$root_dir.'/_include/'.$pg_dsn_file;
			if(is_file($abs_pg_dsn_file) && $dmode!="admin" && $dmode!="ajax_admin" && $dmode!="loadAJAXPaging" && $dmode!="ajax_admin2") {
				include $abs_pg_dsn_file;
			}
			else {
				switch ($dmode)
				{
					case "admin":
						$this->decoNumber("\n<span class=\"now\">", "</span>", "", "");
						$this->decoBlock("\n<div class=\"paging\">", "", "\n</div>\n");
						$this->setJumpChar("이전", "다음", "처음", "마지막");
						$this->deco['j']['prev'] = "\n<a href=\"".$this->url."?".$this->param."=".$this->prev.$this->qstr."\" class=\"prev\"><span>".$this->deco['j']['p']."</span></a>";
						$this->deco['j']['next'] = "\n<a href=\"".$this->url."?".$this->param."=".$this->next.$this->qstr."\" class=\"next\"><span>".$this->deco['j']['n']."</span></a>";
						$this->deco['j']['start'] = "\n<a href=\"".$this->url."?".$this->param."=1".$this->qstr."\" class=\"first-child\">".$this->deco['j']['s']."</a>";
						$this->deco['j']['end']  = "\n<a href=\"".$this->url."?".$this->param."=".$this->end.$this->qstr."\" class=\"last-child\">".$this->deco['j']['e']."</a>";
						$this->deco['c']['ThisPage'] = $this->deco['c']['l'] . $this->current . $this->deco['c']['r'];
						$this->deco['c']['PageLink'] = "\n<a href=\"".$this->url."?".$this->param."=@@i@@".$this->qstr."\">".$this->deco['o']['l']."@@i@@".$this->deco['o']['r']."</a>";
						break;
					case "ajax_admin":
						$this->decoNumber("\n<span class=\"now\">", "</span>", "", "");
						$this->decoBlock("\n<div class=\"paging\">", "", "\n</div>\n");
						$this->setJumpChar("이전", "다음", "처음", "마지막");
						$this->deco['j']['prev'] = "\n<a href=\"javascript:\" class=\"prev\" onclick=\"".$this->fname."(".$this->row.",".$this->prev.")\"><span>".$this->deco['j']['p']."</span></a>";
						$this->deco['j']['next'] = "\n<a href=\"javascript:\" class=\"next\" onclick=\"".$this->fname."(".$this->row.",".$this->next.")\"><span>".$this->deco['j']['n']."</span></a>";
						$this->deco['j']['start'] = "\n<a href=\"javascript:\" class=\"first-child\" onclick=\"".$this->fname."(".$this->row.", 1)\">".$this->deco['j']['s']."</a>";
						$this->deco['j']['end']  = "\n<a href=\"javascript:\" class=\"last-child\" onclick=\"".$this->fname."(".$this->row.",".$this->end.")\">".$this->deco['j']['e']."</a>";
						$this->deco['c']['ThisPage'] = $this->deco['c']['l'] . $this->current . $this->deco['c']['r'];
						$this->deco['c']['PageLink'] = "\n<a href=\"javascript:\" onclick=\"".$this->fname."(".$this->row.", @@i@@)\">".$this->deco['o']['l']."@@i@@".$this->deco['o']['r']."</a>";
						break;
					case "ajax_admin2":
						$this->decoNumber("\n<span class=\"now\">", "</span>", "", "");
						$this->decoBlock("\n<div class=\"paging\">", "", "\n</div>\n");
						$this->setJumpChar("이전", "다음", "처음", "마지막");
						$this->deco['j']['prev'] = "\n<a href=\"javascript:\" class=\"prev\" onclick=\"".$this->fname."(".$this->row.",".$this->prev.",".$this->idno.")\"><span>".$this->deco['j']['p']."</span></a>";
						$this->deco['j']['next'] = "\n<a href=\"javascript:\" class=\"next\" onclick=\"".$this->fname."(".$this->row.",".$this->next.",".$this->idno.")\"><span>".$this->deco['j']['n']."</span></a>";
						$this->deco['j']['start'] = "\n<a href=\"javascript:\" class=\"first-child\" onclick=\"".$this->fname."(".$this->row.", 1,".$this->idno.")\">".$this->deco['j']['s']."</a>";
						$this->deco['j']['end']  = "\n<a href=\"javascript:\" class=\"last-child\" onclick=\"".$this->fname."(".$this->row.",".$this->end.",".$this->idno.")\">".$this->deco['j']['e']."</a>";
						$this->deco['c']['ThisPage'] = $this->deco['c']['l'] . $this->current . $this->deco['c']['r'];
						$this->deco['c']['PageLink'] = "\n<a href=\"javascript:\" onclick=\"".$this->fname."(".$this->row.", @@i@@,".$this->idno.")\">".$this->deco['o']['l']."@@i@@".$this->deco['o']['r']."</a>";
						break;
					case "loadAJAXPaging":
						$this->decoNumber("<strong>", "</strong>", "", "");
						$this->decoBlock("<ul class=\"layer_zip_paging\">\n<li>", "</li>\n<li>", "</li>\n</ul>");
						$this->setJumpChar("&lt", "&gt", "&lt&lt", "&gt&gt");
						$this->deco['j']['prev'] = "<a class=\"".$this->alink_class."\" href=\"".$this->url."?".$this->param."=".$this->prev.$this->qstr."\">".$this->deco['j']['p']."</a>";
						$this->deco['j']['next'] = "<a class=\"".$this->alink_class."\" href=\"".$this->url."?".$this->param."=".$this->next.$this->qstr."\">".$this->deco['j']['n']."</a>";
						//$this->deco['j'][start] = "<a class=\"".$this->alink_class."\" href=\"".$this->url."?".$this->param."=1".$this->qstr."\">".$this->deco['j'][s]."</a>";
						//$this->deco['j'][end]  = "<a class=\"".$this->alink_class."\" href=\"".$this->url."?".$this->param."=".$this->end.$this->qstr."\">".$this->deco['j']['e']."</a>";
						$this->deco['c']['ThisPage'] = $this->deco['c']['l'] . $this->current . $this->deco['c']['r'];
						$this->deco['c']['PageLink'] = "<a class=\"".$this->alink_class."\" href=\"".$this->url."?".$this->param."=@@i@@".$this->qstr."\">".$this->deco['o']['l']."@@i@@".$this->deco['o']['r']."</a>";
						break;
					case "wonhwa":
						$this->decoNumber("<B> ", " </B>", "", "");
						$this->decoBlock("<TABLE border=0 cellspacing=0 cellpadding=3><TR><TD>", "</TD><TD>|</TD><TD>", "</TD></TR></TABLE>");
						$this->setJumpChar("<img src=\"/img/common/main_m_btn_prev.gif\" align=\"absmiddle\">", "<img src=\"/img/common/main_m_btn_next.gif\" align=\"absmiddle\">", "<<", ">>");
						$this->deco['j']['prev'] = "<A href=\"".$this->url."?".$this->param."=".$this->prev.$this->qstr."\">".$this->deco['j']['p']."</A>";
						$this->deco['j']['next'] = "<A href=\"".$this->url."?".$this->param."=".$this->next.$this->qstr."\">".$this->deco['j']['n']."</A>";
						$this->deco['j']['start'] = "<A href=\"".$this->url."?".$this->param."=1".$this->qstr."\">".$this->deco['j']['s']."</A> ";
						$this->deco['j']['end']  = " <A href=\"".$this->url."?".$this->param."=".$this->end.$this->qstr."\">".$this->deco['j']['e']."</A>";
						$this->deco['c']['ThisPage'] = $this->deco['c']['l'] . $this->current . $this->deco['c']['r'];
						$this->deco['c']['PageLink'] = "<A href=\"".$this->url."?".$this->param."=@@i@@".$this->qstr."\">".$this->deco['o']['l']."@@i@@".$this->deco['o']['r']."</A>";
						break;
					case "fm":
						$this->decoNumber("<B>[", "]</B>", "", "");
						$this->decoBlock("<TABLE align='center' cellspacing='10' cellpadding='0'><TR><TD>", "</TD><TD>", "</TD></TR></TABLE>");
						$this->setJumpChar("<img src=\"/_image/common/lt.gif\">", "<img src=\"/_image/common/gt.gif\">", "", "");
						$this->deco['j']['prev'] = "<A href=\"".$this->url."?".$this->param."=".$this->prev.$this->qstr."\">".$this->deco['j']['p']."</A>";
						$this->deco['j']['next'] = "<A href=\"".$this->url."?".$this->param."=".$this->next.$this->qstr."\">".$this->deco['j']['n']."</A>";
						$this->deco['j']['start'] = "<A href=\"".$this->url."?".$this->param."=1".$this->qstr."\">".$this->deco['j']['s']."</A>";
						$this->deco['j']['end']  = "<A href=\"".$this->url."?".$this->param."=".$this->end.$this->qstr."\">".$this->deco['j']['e']."</A>";
						$this->deco['c']['ThisPage'] = $this->deco['c']['l'] . $this->current . $this->deco['c']['r'];
						$this->deco['c']['PageLink'] = "<A href=\"".$this->url."?".$this->param."=@@i@@".$this->qstr."\">".$this->deco['o']['l']."@@i@@".$this->deco['o']['r']."</A>";
						break;
					case "low":
						$this->decoNumber("<B> ", " </B>", "", "");
						$this->decoBlock("<TABLE border=0 cellspacing=0 cellpadding=3><TR><TD>", "</TD><TD><font color='#ffffff'>", "</font></TD></TR></TABLE>");
						$this->setJumpChar("◀", "▶", "", "");
						$this->deco['j']['prev'] = "<A href=\"".$this->url."?".$this->param."=".$this->prev.$this->qstr."\">".$this->deco['j']['p']."</A>";
						$this->deco['j']['next'] = "<A href=\"".$this->url."?".$this->param."=".$this->next.$this->qstr."\">".$this->deco['j']['n']."</A>";
						$this->deco['j']['start'] = "<A href=\"".$this->url."?".$this->param."=1".$this->qstr."\">".$this->deco['j']['s']."</A> ";
						$this->deco['j']['end']  = " <A href=\"".$this->url."?".$this->param."=".$this->end.$this->qstr."\">".$this->deco['j']['e']."</A>";
						$this->deco['c']['ThisPage'] = $this->deco['c']['l'] . $this->current . $this->deco['c']['r'];
						$this->deco['c']['PageLink'] = "<A href=\"".$this->url."?".$this->param."=@@i@@".$this->qstr."\">".$this->deco['o']['l']."@@i@@".$this->deco['o']['r']."</A>";
						break;
					case "fm_gaoo":
						$this->decoNumber("<B>[", "]</B>", "", "");
						$this->decoBlock("<TABLE align='center' cellspacing='10' cellpadding='0'><TR><TD>", "</TD><TD>", "</TD></TR></TABLE>");
						$this->setJumpChar("[이전]", "[다음]", "", "");
						$this->deco['j']['prev'] = "<A href=\"".$this->url."?".$this->param."=".$this->prev.$this->qstr."\">".$this->deco['j']['p']."</A>";
						$this->deco['j']['next'] = "<A href=\"".$this->url."?".$this->param."=".$this->next.$this->qstr."\">".$this->deco['j']['n']."</A>";
						$this->deco['j']['start'] = "<A href=\"".$this->url."?".$this->param."=1".$this->qstr."\">".$this->deco['j']['s']."</A>";
						$this->deco['j']['end']  = "<A href=\"".$this->url."?".$this->param."=".$this->end.$this->qstr."\">".$this->deco['j']['e']."</A>";
						$this->deco['c']['ThisPage'] = $this->deco['c']['l'] . $this->current . $this->deco['c']['r'];
						$this->deco['c']['PageLink'] = "<A href=\"".$this->url."?".$this->param."=@@i@@".$this->qstr."\">".$this->deco['o']['l']."@@i@@".$this->deco['o']['r']."</A>";
						break;
					case "cutie":
						$this->decoNumber("<font style=color:#fc1c1a;font-weight:bold;>", "</font>", "", "");
						$this->decoBlock("<ul style=\"text-align:center;padding:10px;\"><li class=\"inline\">", "</li><li class=\"inline\" style=\"padding:0 5px;\">", "</li></ul>");
						$this->setJumpChar("<img src=\"/_image/common/lt.gif\">", "<img src=\"/_image/common/gt.gif\">", "", "");
						$this->deco['j']['prev'] = "<A href=\"".$this->url."?".$this->param."=".$this->prev.$this->qstr."\">".$this->deco['j']['p']."</A>";
						$this->deco['j']['next'] = "<A href=\"".$this->url."?".$this->param."=".$this->next.$this->qstr."\">".$this->deco['j']['n']."</A>";
						$this->deco['j']['start'] = "<A href=\"".$this->url."?".$this->param."=1".$this->qstr."\">".$this->deco['j']['s']."</A>";
						$this->deco['j']['end']  = "<A href=\"".$this->url."?".$this->param."=".$this->end.$this->qstr."\">".$this->deco['j']['e']."</A>";
						$this->deco['c']['ThisPage'] = $this->deco['c']['l'] . $this->current . $this->deco['c']['r'];
						$this->deco['c']['PageLink'] = "<A href=\"".$this->url."?".$this->param."=@@i@@".$this->qstr."\">".$this->deco['o']['l']."@@i@@".$this->deco['o']['r']."</A>";
						break;
					case "new":
						$this->decoNumber("<strong>[", "]</strong>", "", "");
						$this->decoBlock("<ul style=\"text-align:center;padding:10px;\"><li class=\"inline\">", "</li><li class=\"inline\" style=\"padding:0 5px;\">", "</li></ul>");
						$this->setJumpChar("<img src=\"/_image/common/lt.gif\">", "<img src=\"/_image/common/gt.gif\">", "", "");
						$this->deco['j']['prev'] = "<A href=\"".$this->url."?".$this->param."=".$this->prev.$this->qstr."\">".$this->deco['j']['p']."</A>";
						$this->deco['j']['next'] = "<A href=\"".$this->url."?".$this->param."=".$this->next.$this->qstr."\">".$this->deco['j']['n']."</A>";
						$this->deco['j']['start'] = "<A href=\"".$this->url."?".$this->param."=1".$this->qstr."\">".$this->deco['j']['s']."</A>";
						$this->deco['j']['end']  = "<A href=\"".$this->url."?".$this->param."=".$this->end.$this->qstr."\">".$this->deco['j']['e']."</A>";
						$this->deco['c']['ThisPage'] = $this->deco['c']['l'] . $this->current . $this->deco['c']['r'];
						$this->deco['c']['PageLink'] = "<A href=\"".$this->url."?".$this->param."=@@i@@".$this->qstr."\">".$this->deco['o']['l']."@@i@@".$this->deco['o']['r']."</A>";
						break;
					case "new_nopadding":
						$this->decoNumber("<strong>[", "]</strong>", "", "");
						$this->decoBlock("<ul style=\"text-align:center;\"><li class=\"inline\">", "</li><li class=\"inline\" style=\"padding:0 5px;\">", "</li></ul>");
						$this->setJumpChar("<img src=\"/_image/common/lt.gif\">", "<img src=\"/_image/common/gt.gif\">", "", "");
						$this->deco['j']['prev'] = "<A href=\"".$this->url."?".$this->param."=".$this->prev.$this->qstr."\">".$this->deco['j']['p']."</A>";
						$this->deco['j']['next'] = "<A href=\"".$this->url."?".$this->param."=".$this->next.$this->qstr."\">".$this->deco['j']['n']."</A>";
						$this->deco['j']['start'] = "<A href=\"".$this->url."?".$this->param."=1".$this->qstr."\">".$this->deco['j']['s']."</A>";
						$this->deco['j']['end']  = "<A href=\"".$this->url."?".$this->param."=".$this->end.$this->qstr."\">".$this->deco['j']['e']."</A>";
						$this->deco['c']['ThisPage'] = $this->deco['c']['l'] . $this->current . $this->deco['c']['r'];
						$this->deco['c']['PageLink'] = "<A href=\"".$this->url."?".$this->param."=@@i@@".$this->qstr."\">".$this->deco['o']['l']."@@i@@".$this->deco['o']['r']."</A>";
						break;
					 case "dh":
						$this->decoNumber("<B>[", "]</B>", "", "");
						$this->decoBlock("<TABLE align='center' cellspacing='10' cellpadding='0'><TR><TD>", "</TD><TD>", "</TD></TR></TABLE>");
						$this->setJumpChar("<img src=\"/_image/common/lt.gif\">", "<img src=\"/_image/common/gt.gif\">", "", "");
						$this->deco['j']['prev'] = "<A href=\"".$this->url."?".$this->param."=".$this->prev.$this->qstr."\">".$this->deco['j']['p']."</A>";
						$this->deco['j']['next'] = "<A href=\"".$this->url."?".$this->param."=".$this->next.$this->qstr."\">".$this->deco['j']['n']."</A>";
						$this->deco['j']['start'] = "<A href=\"".$this->url."?".$this->param."=1".$this->qstr."\">".$this->deco['j']['s']."</A>";
						$this->deco['j']['end']  = "<A href=\"".$this->url."?".$this->param."=".$this->end.$this->qstr."\">".$this->deco['j']['e']."</A>";
						$this->deco['c']['ThisPage'] = $this->deco['c']['l'] . $this->current . $this->deco['c']['r'];
						$this->deco['c']['PageLink'] = "<A href=\"".$this->url."?".$this->param."=@@i@@".$this->qstr."\">".$this->deco['o']['l']."@@i@@".$this->deco['o']['r']."</A>";
						break;
					case "bs":
						$this->decoNumber("<font color='#44D4D6'><B><I>", "</I></B></font>", "", "");
						$this->decoBlock("<TABLE align='center' cellspacing='10' cellpadding='0'><TR><TD><I><font color='#7A7A7A'>", "</font></I></TD><TD><I>", "</I></TD></TR></TABLE>");
						$this->setJumpChar("<img src=\"/_image/common/lt.gif\">", "<img src=\"/_image/common/gt.gif\">", "", "");
						$this->deco['j']['prev'] = "<A href=\"".$this->url."?".$this->param."=".$this->prev.$this->qstr."\">".$this->deco['j']['p']."</A>";
						$this->deco['j']['next'] = "<A href=\"".$this->url."?".$this->param."=".$this->next.$this->qstr."\">".$this->deco['j']['n']."</A>";
						$this->deco['j']['start'] = "<A href=\"".$this->url."?".$this->param."=1".$this->qstr."\">".$this->deco['j']['s']."</A>";
						$this->deco['j']['end']  = "<A href=\"".$this->url."?".$this->param."=".$this->end.$this->qstr."\">".$this->deco['j']['e']."</A>";
						$this->deco['c']['ThisPage'] = $this->deco['c']['l'] . $this->current . $this->deco['c']['r'];
						$this->deco['c']['PageLink'] = "<A class=\"pa\" href=\"".$this->url."?".$this->param."=@@i@@".$this->qstr."\">".$this->deco['o']['l']."@@i@@".$this->deco['o']['r']."</A>";
						break;
					case "kz":
						$this->decoNumber("<B>", "</B>", "", "");
						$this->decoBlock("<TABLE align='center' cellspacing='10' cellpadding='0'><TR><TD>", "</TD><TD>", "</TD></TR></TABLE>");
						$this->setJumpChar("<img src=\"/_image/common/lt.gif\" align='absmiddle'>", "<img src=\"/_image/common/gt.gif\" align='absmiddle'>", "", "");
						$this->deco['j']['prev'] = "<A href=\"".$this->url."?".$this->param."=".$this->prev.$this->qstr."\">".$this->deco['j']['p']."</A>";
						$this->deco['j']['next'] = "<A href=\"".$this->url."?".$this->param."=".$this->next.$this->qstr."\">".$this->deco['j']['n']."</A>";
						$this->deco['j']['start'] = "<A href=\"".$this->url."?".$this->param."=1".$this->qstr."\">".$this->deco['j']['s']."</A>";
						$this->deco['j']['end']  = "<A href=\"".$this->url."?".$this->param."=".$this->end.$this->qstr."\">".$this->deco['j']['e']."</A>";
						$this->deco['c']['ThisPage'] = $this->deco['c']['l'] . $this->current . $this->deco['c']['r'];
						$this->deco['c']['PageLink'] = "<A href=\"".$this->url."?".$this->param."=@@i@@".$this->qstr."\">".$this->deco['o']['l']."@@i@@".$this->deco['o']['r']."</A>";
						break;
					default:
						$this->decoNumber("<B>[", "]</B>", "", "");
						$this->decoBlock("<TABLE align='center'><TR><TD>", "</TD><TD>", "</TD></TR></TABLE>");
						$this->setJumpChar("◀", "▶", "[처음]", "[끝]");
						$this->deco['j']['prev'] = "<A href=\"".$this->url."?".$this->param."=".$this->prev.$this->qstr."\">".$this->deco['j']['p']."</A>";
						$this->deco['j']['next'] = "<A href=\"".$this->url."?".$this->param."=".$this->next.$this->qstr."\">".$this->deco['j']['n']."</A>";
						$this->deco['j']['start'] = "<A href=\"".$this->url."?".$this->param."=1".$this->qstr."\">".$this->deco['j']['s']."</A>";
						$this->deco['j']['end']  = "<A href=\"".$this->url."?".$this->param."=".$this->end.$this->qstr."\">".$this->deco['j']['e']."</A>";
						$this->deco['c']['ThisPage'] = $this->deco['c']['l'] . $this->current . $this->deco['c']['r'];
						$this->deco['c']['PageLink'] = "<A href=\"".$this->url."?".$this->param."=@@i@@".$this->qstr."\">".$this->deco['o']['l']."@@i@@".$this->deco['o']['r']."</A>";
						break;
				}
			}
			return true;
		}

		// set page link
		function setPageLink()
		{
			$pagelink = $this->deco['b']['p'];

			if($this->year_next) {
				$pagelink .= str_replace('@@i@@', $this->year_next, $this->deco['c']['year_l'].$this->year_next.$this->deco['c']['year_r']);
			}

			// [첫화면버튼] [이전블록버튼] 설정
				if ($this->prev>0) { $pagelink .= $this->deco['j']['start'] . $this->deco['j']['prev'] .""; }

			// 페이지링크 설정
				$EndLoopIndex = (($this->next - 1) > $this->end) ? ($this->end + 1) : $this->next;
				if($this->total == 0) $EndLoopIndex = 2;
				for ($i=$this->prev+1; $i < $EndLoopIndex; $i++)
				{
					$pagelink .= $this->deco['b']['m'];
					$pagelink .= ($i==$this->current) ? $this->deco['c']['ThisPage'] : str_replace("@@i@@", $i, $this->deco['c']['PageLink']);
				}
				$pagelink .= $this->deco['b']['m'];

			// [다음블록버튼] [마지막화면버튼] 설정
				if ($this->end>=$this->next) { $pagelink .= "". $this->deco['j']['next'] . $this->deco['j']['end']; }

			if($this->year_prev) {
				$pagelink .= str_replace('@@i@@', $this->year_prev, $this->deco['c']['year_l'].$this->year_prev.$this->deco['c']['year_r']);
			}

			$this->a_ary_Result['PageLink'] = $pagelink . $this->deco['b']['s'];
			return true;
		}
	}

?>