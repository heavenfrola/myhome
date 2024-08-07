$.fn.wingNextPage = function(option) {
	if (!option) option = [];

	let $this = this;
	this.placeholder = $(this); // 붙여넣기 본문 위치
	this.file_name = option.file_name; // 스킨 파일 명
	this.module_name = option.module_name; // 스킨 모듈 명
	this.page = option.page || 1; // 현재 페이지
	this.mode = option.mode || 'append'; // 붙여넣기 모드 append/replace/replace_all
	this.handle = option.handle || null; // 더보기 버튼 (마지막 페이지에서 hidden)
	this.complete = option.complete || null; // 붙여넣기 완료 후 콜백 함수

	if (typeof window.wing_morepages == 'undefined') {
		window.wing_morepages = {};
	}
	window.wing_morepages['module_'+this.module_name] = this;

	$this.load = function(event_type) {
		let param = {
			'exec_file': 'skin_module/moduleloader.php', 
			'file_name': $this.file_name, 
			'module_name': $this.module_name,
			'page': $this.page,
			'uri': location.href,
			'add_mode': $this.mode,
			'event_type': event_type
		}
		$.get(root_url+'/main/exec.php', param, function(ret) {
			if (ret.status == 'success') {
				if ($this.handle) {
					if (ret.html == '' || ret.end_page == $this.page) {
						if ($this.handle && $this.handle.length > 0) {
							$this.handle.hide();
						}
					} else {
						$this.handle.show();
					}
				}

				$this.placeholder.html(ret.html);
				if (typeof $this.complete == 'function') {
					$this.complete();
				}

				if ($this.mode == 'replace' || $this.mode == 'replace_all') {
					document.scrollingElement.scrollTop = $this.placeholder.offset().top-100; // 모바일
					$(document.body).scrollTop($this.placeholder.offset().top-100);
				}
			} else {
				console.log(this.message);
			}
		});
	}

	$this.next = function() {
		let url = location.href.split('#');

		// 현재 모듈을 제외한 기존 파라메터 유지
		let param = '';
		new URLSearchParams(url[1]).forEach(function (val, key) {
			if (key == 'module_'+$this.module_name) return;
			if (param) param += '&';
			param += key+'='+val;
		});

		// 현재모듈의 링크 추가
		$this.page = parseInt($this.page)+1;
		if (param) param += '&';
		param += 'module_'+$this.module_name+'='+$this.page;

		location.href = '#'+param;
	}

	$this.param = function(p) {
		let url = location.href.split('#');

		// 페이지 초기화
		$this.page = 0;

		// 현재 모듈을 제외한 기존 파라메터 유지
		let param = '';
		loop1:new URLSearchParams(url[1]).forEach(function (val, key) {
			for (k in p) {
				if (key == k) return loop1;
			}
			if (key == 'module_'+$this.module_name) val = 1;
			if (param) param += '&';
			param += key+'='+val;
		});

		for (k in p) {
			if (param) param += '&';
			param += k+'='+p[k]; 
		}
		location.href = '#'+param;
	}

	return this;
}

$(window).on('hashchange load', function(event) {
	let url = location.href.split('#');
	let entries = new URLSearchParams(url[1]);

	for (let key in window.wing_morepages) {
		let module = window.wing_morepages[key];
		let page = entries.get(key);
		if (page == null) page = 1;
		module.page = page;
		module.load(event.type);
	}
});