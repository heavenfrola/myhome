/* +----------------------------------------------------------------------------------------------+
' |  셀렉트박스 컨트롤 클래스
' +----------------------------------------------------------------------------------------------+*/
R2Select = function(sId) {
	this.select = this.get(sId);
	if(!this.select) return;
}

R2Select.prototype.get = function(sId) { // select 를 구해서 리턴
	var select = document.getElementById(sId);

	if(!select) return false;
	if(select.tagName != 'SELECT') return false;

	return select;
}

R2Select.prototype.add = function(text, value, pos) { // select 에 새로운 option을 추가
	if(!this.select) return false;

	if(!value) value = '';
	if(!text && value) text = value;

	var newOption = document.createElement('OPTION');
	newOption.text = text;
	newOption.value = value;

	if(pos) this.select.insertbefore(newOption, this.select.options[pos]);
	else this.select.add(newOption);
}

R2Select.prototype.remove = function() { // option 을 삭제
	if(!this.select) return false;

	var option = null;
	for(var i = (this.select.length-1); i >= 0; i--) {
		option = this.select.options[i];

		if(option.selected == true) {
			if(/\s?essencial\s?/.test(option.className) && $('[value='+option.value+']', this.select).length == 1) {
				window.alert('선택한 옵션은 필수 옵션이므로 삭제하실 수 없습니다.');
				return false;
			}
			this.select.remove(i);
		}
	}
}

R2Select.prototype.move = function(step) { // option 의 위치를 이동
	if(!this.select) return false;
	if(this.select.selectedIndex < 0) return false;

	var opts = [];
	var nopt = null;
	if(step > 0 ) { // Down
		for(var i = this.select.length-1; i >= 0; i--) {
			sopt = this.select.options[i];
			nopt = this.select.options[i+1];

			if(sopt.selected == true) {
				if(!nopt) return;
				$(sopt).insertAfter(nopt)
			}
		}
	} else {
		for(var i = 0; i < this.select.length; i++) {
			sopt = this.select.options[i];
			nopt = this.select.options[i-1];

			if(sopt.selected == true) {
				if(!nopt) return;
				this.select.insertBefore(sopt, nopt);
			}
		}
	}
}

R2Select.prototype.getOpt = function(pos) { // option 의 value 와 text 를 객체형태로 리턴
	if(!this.select) return false;

	if(!pos) pos = this.select.selectedIndex;
	if(pos > -1) {
		var result = new Object();
		var opt = this.select.options[pos];
		result.value = opt.value;
		result.text = opt.text;

		return opt;
	}

	return false;
}

R2Select.prototype.addFromSelect = function(sel, pass) { // 다른 셀렉트에서 값을 가져와 추가
	if(sel.select.selectedIndex < 0) {
		window.alert('추가 할 항목을 선택 해 주십시오.');
		return false;
	}
	if(sel.select.value == "memo") {
		window.alert('주문메모 필드의 경우 1회 최대 100개 이하의 주문서 다운로드 시에만 출력됩니다.');
	}

	for(var i = 0; i < sel.select.length; i++) {
		if(sel.select.options[i].selected == true) {
			opt = sel.select.options[i];
			this.add(opt.text, opt.value);
		}
	}
}