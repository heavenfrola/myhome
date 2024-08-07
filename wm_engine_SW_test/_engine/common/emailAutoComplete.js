// 이메일 자동완성 다이얼로그 이벤트
window.EAC = {};
function attachEmailAutoComplete() {
	$('input[name="member_id"], input[name="email"]').each(function() {
		window.EAC[this.name] = new emailAutoComplete(this);

		if(typeof window.emailSuffix == 'undefined') { // 이메일 세트 로딩
			$.get('/main/exec.php', {'exec_file':'member/login.exe.php', 'exec':'getEmailSuffix'}, function(r) {
				window.emailSuffix = r;
			});
		}
	});

	$('input[name="member_id"], input[name="email"]').on({
		'keydown' : function(e) { // 엔터키 및 화살표 입력 시
			return window.EAC[this.name].eventKey(e);
		},
		'focus keyup paste' : function(e) { // 일반 키 입력
			window.EAC[this.name].input(e);
		},
		'blur' : function(e) {
			window.EAC[this.name].hide();
		}
	});
}

// 이메일 자동완성 다이얼로그
var emailAutoComplete = function(o) {
	var _this = this;

	this.object = o;
	this.form = $(this.object).parents('form');
	this.dialog = $('.auto_complete_'+this.object.name, this.form).addClass('auto_complete_dialog');

	this.cursor = -1; // 현재 커서 위치
	this.prev_value = null; // 이전 입력 내용

	// 키 입력 수집
	this.input = function(e) {
		if(e.keyCode == 13 || e.keyCode == 38 || e.keyCode == 40) return;

		for(var key in window.EAC) { // 다른 다이얼로그 창이 있을 경우 숨김
			if(this != window.EAC[key]) window.EAC[key].hide();
		}

		if(this.object.value.length < 1) { // 입력 된 내용 없을 경우 숨김
			this.hide();
			return;
		}

		if(this.object.value != this.prev_value) { // 입력 내용이 다를 경우에만 처리
			// 다이얼로그 초기화
			this.prev_value = this.object.value;
			this.cursor = -1;
			this.dialog.html('');

			// 다이얼로그 생성
			var email = this.object.value;		// 입력한 이메일 정보
			var tmp = email.split('@');
			var prefix = tmp[0];		// 입력한 이메일 정보 앞
			var suffix = tmp[1];		// 입력한 이메일 정보 뒤
			var _email = '';			// 새로 조합된 이메일
			var li = null;				// 다이얼로그에 추가할 li element

			for(var key in window.emailSuffix) {
				_suffix = window.emailSuffix[key];
				_email = prefix+'@'+_suffix;

				if(_email == email) continue; // 현재 입력된 이메일과 같을 경우 표시 안함

				if(suffix) { // 이메일 뒷 부분을 입력한 경우 검색기능 작동
					if(new RegExp('^'+suffix.replace('.', '\.')).test(_suffix) == false) continue;
				}

				// 다이얼로그에 객체 추가
				var li = $('<li>'+_email+'</li>');
				li.data('email', _email).click(function() {
					_this.clickItem(this);
				});

				this.dialog.append(li);
				this.dialog.show();
			}
			if (this.dialog.find('li').length == 0) {
				this.dialog.hide();
			}

			return true;
		}
	}

	// 화살표키로 커서 이동 및 아이템 선택 후 엔터키 처리
	this.eventKey = function(e) {
		if(e.keyCode != 13 && e.keyCode != 38 && e.keyCode != 40) return;

		var next_cursor = 0;
		var items = this.dialog.find('li');

		switch(e.keyCode) {
			case 13 : // enter
				e.preventDefault();
				this.dialog.find('li').eq(this.cursor).click();
				return false;
			break;
			case 38 : // up
				next_cursor = this.cursor-1;
			break;
			case 40 : // down
				next_cursor = this.cursor+1;
			break;
		}
		if(next_cursor < 0) return false;

		var target = items.eq(next_cursor);
		if(target.length == 1) {
			items.removeClass('selected');
			target.addClass('selected');
			this.dialog.data('cursor', next_cursor);
			this.cursor = next_cursor;
		}
		return target;
	}

	// 다이얼로그 닫기
	this.hide = function() {
		var _this = this;
		this.dialog.fadeOut(function() {
			_this.cursor = -1;
			_this.prev_value = null;
		});
	}

	// 항목 선택
	this.clickItem = function(li) {
		this.object.value = $(li).data('email');
		if(typeof checkFormResult == 'function') {
			checkFormResult(this.object.name);
		}
		this.dialog.fadeOut(function() {
			_this.cursor = -1;
		});
	}
}