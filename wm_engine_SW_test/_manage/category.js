function convevent (ev) {
	var e = (window.event) ? window.event : ev;
	return e;
}

function mobileSelectCat(no) {
	if(document.getElementById('no'+no).value == 'N') { 
		var noStr='Y';
		var classStr='m_category p_cursor';
	} else { 
		var noStr='N';
		var classStr='m_categoryOn p_cursor';
	}

	document.getElementById('no'+no).value=noStr;
	document.getElementById('img'+no).className=classStr;
}

function moveCat(no, type) {
	if(no != "" || no == "0") {
		var type=(type && typeof type != undefined) ? type : '';
		open_cat(no,1)
		click_category(no)
		if(type != 'M') {
			$.get(manage_url+"/_manage/index.php?body=product@catework_content.frm&execmode=ajax&no="+no+"&ctype="+ctype, function(r) {
				$('#categoryContent').html(r);

				var cf = document.cateFrm;
				countCateLines(cf,0);
				cateAccess(cf,0);
			});
		}
	}
}

var selected_category;
function click_category(no){
	sc = selected_category;
	if(sc) sc.className = '';

	temp = document.getElementById("name_"+no)

	if(temp) {
		temp.className='selected';
		selected_category = temp;
	}
}

function open_cat(no,option){
	if(no == 0) return false;

	temp = document.getElementById ("cat_"+no);
	temp2 = document.getElementById ("div_"+no);
	if(temp && temp2){

		if((temp.style.display == "none" || option == 1) && option != 2 ) {
			temp.style.display = "block";
			document.getElementById("ic_"+no).src = engine_url+"/_manage/image/icon/ic_minus.gif";
			document.getElementById("folder_"+no).src = engine_url+"/_manage/image/icon/ic_folder_o.gif";
		}
		else if((temp.style.display == "block" || option == 2) && option != 1) {
			temp.style.display = "none";
			document.getElementById("ic_"+no).src = engine_url+"/_manage/image/icon/ic_plus.gif";
			document.getElementById("folder_"+no).src = engine_url+"/_manage/image/icon/ic_folder_c.gif";
		}

	}
}

function reloadTree(pr) {
	var treediv = document.getElementById ("category_tree");
	$.post(manage_url+"/_manage/index.php?body=product@catework_add.exe", {execmode:"ajax", wmode:"reload", ctype:ctype, "no":pr}, function(tree) {
		treediv.innerHTML = tree;
		open_cat(pr,1);
		click_category(pr);
	});
}

function ckall(nm, val) {
	var ck = document.getElementsByName (nm);
	len = ck.length;
	for(i = 0; i < len; i++) {
		ck[i].checked = val;
	}
}

function categoryAll(stat) {
	var cates = document.getElementsByTagName ("UL");
	for(i = 0; i < cates.length; i++) {
		if(cates[i].id.substring(0,4) == "cat_" ) {
			no = parseInt(cates[i].id.substring(4));
			open_cat(no,stat);
		}
	}
}

function controlByajex(dir,pr,no,c) {
	if(c) {
		if(!confirm("선택하신것을 삭제하시겠습니까?")) return;
	}

	$.post(manage_url+"/_manage/index.php?body="+dir, {execmode:"ajax", ctype:ctype}, function(ajax) {
		if(ajax.substring (0,5) == "ERROR") {
			window.alert (ajax.substring(5));
			return;	
		}

		var contarea = document.getElementById ("categoryContent");
		contarea.innerHTML = ajax;

		if(pr != null && no != null ) {
			var _pr = $('#cat_'+pr);
			$('#cat_'+no, _pr).remove();
			$('#div_'+no, _pr).remove();
		}
	});
}

// user
var onInput = 0;
function moveParent(e) {
	ev = convevent(e);
	if(!ev) return;
	if(ev.keyCode == "8" && onInput == 0) {
		sb = document.getElementById ("searchbar");

		if(sb){
			switch (sb.level.value) {
				case "3" : no = sb.mid.value; break;
				case "2" : no = sb.big.value; break;
				case "1" : no = 0; break;
				default: return false;
			}

			moveCat(no);
			return false;
		}
	}
}

function makeitems() {
	var cats = document.getElementById("cat_list");
	var vals = "";

	for(i = 0; i < cats.length ; i++){
		el = cats.elements[i];
		if(el.type == "checkbox" && el.name == "cno[]" && el.checked == true) vals += "&cno[]="+el.value;
	}

	return vals;
}

function modifyItems(pr) {
	var vals = makeitems();

	if(vals) {
		controlByajex("product@catework_mod.frm&parent_no="+pr+vals)
	}
	else window.alert ("선택된 항목이 없습니다");

	return false;
}

function deleteItems(pr) {
	var vals = makeitems();

	if(vals) {
		conf = confirm ("해당 분류에 등록된 상품이 있을시 삭제되지 않습니다.\n\n선택된 분류를 삭제하시겠습니까?");
		if(conf) {
			controlByajex("product@catework_del.exe&parent="+pr+vals)
			reloadTree (pr);
		}
	}
	else window.alert ("선택된 항목이 없습니다");

	return false;
}

function insertcatIcon (no,parent,text) {
	moveCat(parent);

	temp = document.createElement ('DIV');
	temp.id = 'div_'+no;
	temp.className = 'cat_item';

	temp.innerHTML = text;

	pr = document.getElementById ('cat_'+parent);
	pr.appendChild(temp);

	temp2 = document.createElement ('DIV');
	temp2.id = 'cat_'+no;
	temp2.style.display = 'none';
	temp2.style.paddingLeft = '15px';
	
	pr.appendChild(temp2);
}


// 수정폼
function cateAccess(f,loop){

	var access_limit = document.getElementsByName ("access_limit["+loop+"]");
	var access_member = document.getElementsByName ("access_member["+loop+"]");
	var no_access_page =document.getElementsByName ("no_access_page["+loop+"]");
	no_access_page = no_access_page[0];
	if(!access_limit); return;

	if(access_limit[0].checked==true) {
		for(i=0; i<access_member.length; i++) {
			access_member[i].disabled=true;
		}
		no_access_page.disabled=true;
		no_access_page.style.backgroundColor = "#eee";
	} else {
		for(i=0; i<access_member.length; i++) {
			access_member[i].disabled=false;
		}
		no_access_page.disabled=false;
		no_access_page.style.backgroundColor = "";
	}
}


function countCateLines(f,loop){
	var rows = document.getElementsByName ("rows["+loop+"]")[0];
	var cols = document.getElementsByName ("cols["+loop+"]")[0];
	var lines = document.getElementsByName ("lines["+loop+"]")[0];

	if(!rows || !cols) return;

	r=eval(rows.value);
	c=eval(cols.value);
	if(!r) r=4;
	if(!c) c=4;
	rows.value=r;
	cols.value=c;
	lines.value=r*c;
}

function open_topDesigner (obj,neko_id) {
	var no = obj.value;
	var cont = document.getElementById("tr_designer_control_"+no);
	var main = document.getElementById("tr_designer_main_"+no);
	var text = document.getElementsByName("content2["+no+"]")[0];

	if(obj.checked == true) {
		main.style.display = "block";
		cont.style.display = "none";

		var editor = new R2Na ("content2["+no+"]");
		//editor.DOMNavigator (true); Dom navigator 사용시

		if(!neko_mode) neko_mode = "file";

		editor.initNeko (neko_id, "category", neko_mode);
		editor.printNeko ("nekopos_"+no);
	}
}


// Order Editor
function catework_order(){
	var f = document.getElementById ("ordFrm");

	var list = "";
	for(i = 0; i < f.order.length; i++){
		list += "@"+f.order.options[i].value;
	}

	f.cat_list.value = list;
	f.submit();
}