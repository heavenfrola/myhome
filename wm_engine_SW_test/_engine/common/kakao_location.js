/**
 * 카카오지도 API
 */
let markers = [];
let overlays = []; //오버레이
let clusterers = null; //클러스터
let selectMarkers = null; //마커 선택된 항목

//매장 지도 리스트
function kakaoMapList(option) {

	let param = {};
	if(option) param = option;

	param['search_key'] = $('select[name=search_key]').val();
	param['search_str'] = $('#search_str').val();
	param['exec'] = 'kakaoList';

	// 해당 리스트 tap에 클래스명으로 판단해서 값을 넘긴다[선호하는 매장 개발]
	if($('.list_tap ').hasClass('on') == true) param['type'] = 'wishList';

	fetch(root_url + '/main/exec.php?exec_file=common/kakaoInfo.inc.php', {
		method: "POST",
		headers: {
			"Content-Type": "application/json",
		},
		body: JSON.stringify(param),
	})
	.then((response) => response.json())
	.then(function (data) {

		//초기화
		let map_arr = [];
		$('.map_list ').empty('');

		let sList = data.store_data;
		let shtml = data.store_list;

		sList.forEach(function (key, index) {
			map_arr.push(key);
		});

		$('.map_list ').append(shtml);
		$('#storeTotal').html(data.store_total_count); //매장 총 개수
		addMarker(map_arr);

		//첫번째 행 자동 선택
		if(data.store_total_count>0) kakaoMapOpen(data.store_data[0]);
	})
}

// 마커를 생성하고 지도위에 표시하는 함수입니다
function addMarker(option) {

	//초기화
	setMarkers();
	setOverlays();
	overlays = [];
	markers = [];
	selectMarkers = null;

	for (var i = 0, len = option.length; i < len; i++) {
		let position = new kakao.maps.LatLng(option[i].lat, option[i].lng);
		let markerImage = imgurl = '';

		if(store_marker_yn == 'Y' && store_marker_upfile1) {
			imgurl = root_url +'/'+ store_marker_updir + store_marker_upfile1;
		} else {
			//기본 마커 출력
			imgurl = root_url +'/wm_engine_SW/_manage/image/store/wisa_marker.png';
			store_marker_w = 29;
			store_marker_h = 42;
		}

		if(imgurl) {
			let imageSrc = imgurl, // 마커이미지의 주소입니다
				imageSize = new kakao.maps.Size(store_marker_w, store_marker_h) // 마커이미지의 크기입니다

			markerImage = new kakao.maps.MarkerImage(imageSrc, imageSize);
		}

		// 마커를 생성합니다
		let marker = new kakao.maps.Marker({
			position: position,
			image: markerImage
		});

		if(store_marker_clusterer == 'N') marker.setMap(map);
		markers.push(marker);

		//이름 표시
		overlays.push(kakaoOverlays(option[i]));

		let idx = markers.length - 1;
		marker.idx = idx;
		marker.store_no = option[i].no;
		marker.lng = option[i].lng;
		marker.lat = option[i].lat;

		kakao.maps.event.addListener(marker, 'click', function () {
			setOverlays();

			//커스텀 오버레이 표시
			let customOverlays = overlays[this.idx];
			customOverlays.setMap(map);

			//선택 된 마커
			selectMarkers = marker;
			$('#customoverlay').show();

			// let tabs = $('.map_tabs').offset();
			// let map_list = $('#map_list'+marker.store_no);
			// let offset = map_list.offset();

			map.setCenter(new kakao.maps.LatLng(marker.lat, marker.lng));
		});
	}

	if(store_marker_clusterer == 'Y') {
		//클러스터 초기화
		if (clusterers) clusterers.clear();

		//클러스터 설정
		clusterers = ClusterConfig(markers);
	}
}

//매장 상세 레이어
function ClusterConfig(moption) {

	//클러스터 CSS 변경
	normalStyle = {
		width: '36px',
		height: '36px',
		background: store_marker_clusterer_color,
		opacity: '1',
		borderRadius: '50%',
		color: '#fff',
		textAlign: 'center',
		lineHeight: '34px',
		fontSize: '14px',
		outline: '10px solid rgba(226, 93, 93,0.24)',
	}

	 clusterers = new kakao.maps.MarkerClusterer({
		map: map, // 마커들을 클러스터로 관리하고 표시할 지도 객체
		markers:moption,
		averageCenter: true, // 클러스터에 포함된 마커들의 평균 위치를 클러스터 마커 위치로 설정
		minLevel: 8, // 클러스터 할 최소 지도 레벨
		disableClickZoom: true, // 클러스터 마커를 클릭했을 때 지도가 확대되지 않도록 설정한다
		styles: [normalStyle]
	});

	//클러스터 클릭 시 실행
	kakao.maps.event.addListener(clusterers, 'clusterclick', function(cluster) {
		// 현재 지도 레벨에서 1레벨 확대한 레벨
		let level = map.getLevel()-1;

		// 지도를 클릭된 클러스터의 마커의 위치를 기준으로 확대합니다
		map.setLevel(level, {anchor: cluster.getCenter()});

		//.Ma: 위도, .La : 경도
		map.setCenter(new kakao.maps.LatLng(cluster.getCenter().Ma,cluster.getCenter().La));
	});

	//최종 클러스터 실행
	kakao.maps.event.addListener(clusterers, 'clustered', (cluster) => {
		let inClustered = {
			selected: false
		};

		cluster.forEach((clus) => {
			let clusMarkers = clus.getMarkers();
			clusMarkers.forEach((cs) => {
				//커스텀 오버레이 제어
				if(inClustered.selected == true) return;

				if(selectMarkers != null && (selectMarkers.store_no != cs.store_no)) {
					inClustered.selected = false;
				} else {
					inClustered.selected = true;
				}
			});
		});


		//클러스터 안에 포함 될 경우 오버레이 제어
		if(inClustered.selected == true) {
			$('#customoverlay').hide();
		} else {
			//$('#customoverlay').show();
		}
	});

	kakao.maps.event.addListener(map, 'zoom_changed', function() {

		if(selectMarkers != null) {
			$('#customoverlay').show();
		}
	});

	return clusterers;
}
//매장 상세 레이어
function kakaoOverlays(option) {

	// 커스텀 오버레이 사용
	let content = option.overlay;

	let customOverlay = new kakao.maps.CustomOverlay({
		position: new kakao.maps.LatLng(option.lat, option.lng),
		content: content,
		zIndex: 2
	});

	return customOverlay;
}

//매장 상세 레이어
function kakaoInfoLayer(option) {
	let param = {};
	if(option) param = option;

	toggle_view('shop_detail');

	param['exec'] = 'layer';

	fetch(root_url + '/main/exec.php?exec_file=common/kakaoInfo.inc.php', {
		method: "POST",
		headers: {
			"Content-Type": "application/json",
		},
		body: JSON.stringify(param),
	})
		.then((response) => response.json())
		.then(function (data) {
			if(data) $('#shop_detail').html(data);
		})
}

//매장 지도 리스트
function kakaoMapOpen(option) {
	setOverlays();

	$('[id^=map_list]').removeClass('on');
	$('#map_list'+option.no).addClass('on')

	//선택된 마커 저장
	let position = new kakao.maps.LatLng(option.lat, option.lng);
	let marker = new kakao.maps.Marker({
		position: position
	});
	marker.store_no = option.no;
	selectMarkers=marker;

	overlays[option.seq].setMap(map);


	map.setLevel(4);
	map.setCenter(position);
	$('#customoverlay').show();

}

// 마커 초기화
function setMarkers(map=null) {
	for (let i = 0; i < markers.length; i++) {
		markers[i].setMap(map);
	}
}

// 커스텀 오버레이 초기화
function setOverlays(map = null) {
	for (let i = 0; i < overlays.length; i++) {
		overlays[i].setMap(map);
	}
}

// 커스텀 오버레이 닫기
function OverlaysClose() {
	selectMarkers  = null;
	$('#customoverlay').hide();
}

function ZoomIn() {
	// 현재 지도의 레벨을 얻어옵니다
	var level = map.getLevel();

	// 지도를 1레벨 내립니다 (지도가 확대됩니다)
	map.setLevel(level - 1);
}

function ZoomOut() {
	// 현재 지도의 레벨을 얻어옵니다
	var level = map.getLevel();

	// 지도를 1레벨 올립니다 (지도가 축소됩니다)
	map.setLevel(level + 1);
}

// 매장 필터 ON/OFF 기능
function filterSearch(type) {

	let param = {};

	//초기화
	if(type == 'C') {
		$('.filter_body .list li').find('input').each(function() {
			$(this).attr('checked',false);
		});
	}

	//[시설안내]
	let facility_arr = [];
	$('.filter_body .list li').each(function() {
		if($(this).find('input').is(':checked') == true) {
			facility_arr.push($(this).find('input').val());
		}
	});

	if (facility_arr.length>0) {
		param['facility'] = facility_arr.join(',');
	}

	param['type'] = 'list';

	gpsKakaoMap(param);
}


//매장 지도 리스트
function kakaoMapView(option) {

	let param = [];
	if(option) param[0] = option;
	addMarker(param);

	//위치 이동
	let position = new kakao.maps.LatLng(option.lat, option.lng);
	map.setLevel(4);
	map.setCenter(position);
}

//[앱에서만 실행] 실질적으로 실행되는 함수
function getLocation(lat, lng) {
	let param = {};

	param['gps_lat'] = lat;
	param['gps_lng'] = lng;

	gpsKakaoMapList(param);
}

//gps 콜백 함수 error
function handleError(err) {

	let param = {};

	//시청 기준
	let lat = 126.9779451;
	let lng = 37.5662952;

	//중심 좌표 설정 값
	if (gps_center_lat && gps_center_lng) {
		lat = gps_center_lat;
		lng = gps_center_lng;
	}

	param['gps_lat'] = lat;
	param['gps_lng'] = lng;
	gpsKakaoMapList(param);
}

function gpsKakaoMap(option) {

	let param = {};
	if(option) param = option;

	if(store_location_gps != 'Y') {
		//시청 기준
		let lat = 126.9779451;
		let lng = 37.5662952;

		//중심 좌표 설정 값
		if(gps_center_lat && gps_center_lng ) {
			lat = gps_center_lat;
			lng = gps_center_lng;
		}

		param['gps_lat'] = lat;
		param['gps_lng'] = lng;
		gpsKakaoMapList(param);
	} else {
		navigator.geolocation.getCurrentPosition(showPosition, handleError);
	}
}

//PC,모바일 에서만 작동
function showPosition(position) {

	let param = {};

	param['gps_lat'] = position.coords.latitude;
	param['gps_lng'] = position.coords.longitude;

	gpsKakaoMapList(param);
}


//매장 지도 리스트
function gpsKakaoMapList(option) {

	let param = {};
	if(option) param = option;

	//중심 위치로 이동
	if(param.type == 'm') {
		let moveLocation = new kakao.maps.LatLng(param.gps_lat, param.gps_lng);
		map.setCenter(moveLocation);
		map.setLevel(6);
	} else {
		kakaoMapList(param);
	}
}

//웹 & 앱 확인
function userAgentApp() {
	const userAgent = window.navigator.userAgent.toLowerCase();

	let safari = /safari/.test(userAgent);
	let ios = /iphone|ipod|ipad/.test(userAgent);
	let android = /android/.test(userAgent);
	let wisaapp = /wisaapp/.test(userAgent);
	let wisaapp_ios = /ios/.test(userAgent);
	let wisaapp_aos = /android/.test(userAgent);

	if (wisaapp && wisaapp_aos) {
		return 'magic_android';
	} else if (wisaapp && wisaapp_ios) {
		return 'magic_ios';
	}
	if( userAgent.indexOf("edg/") > -1) {
		return "Edge (chromium)";
	}
	if( userAgent.indexOf("chrome") > -1 && !!window.chrome) {
		return "chrome";
	}
	if( userAgent.indexOf("mac/") > -1) {
		return "Mac";
	}
	if (ios) {
		return 'ios';
	}
	if (android) {
		return 'Android';
	}
	if (safari) {
		return 'Safari';
	}
	return 'undefined'
}

function operateWish(no) {

	let param = {};

	param['sno'] = no;
	param['exec'] = 'wishlist';

	fetch(root_url + '/main/exec.php?exec_file=store/location.exe.php', {
		method: "POST",
		headers: {
			"Content-Type": "application/json",
		},
		body: JSON.stringify(param),
	})
		.then((response) => response.json())
		.then(function (data) {
			if(data.type == 'nonkey' || data.type == 'nonlogin' ) {
				window.alert(data.msg);
				location.href= data.return_url;
			} else if(data.type == 'success') {
				if($('.location_wish').hasClass('on') == true) {
					$('.location_wish').removeClass('on');
				} else {
					$('.location_wish').addClass('on');
				}
			}
		})
}