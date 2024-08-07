<?PHP

	$_talkstore_announce = array(
		'WEAR' => array(
			'name' => '의류',
			'fields' => array(
				'material' => '제품 소재',
				'color' => '색상',
				'size' => '치수',
				'manufacturer' => '제조사',
                'country' => '제조국',
				'caution' => '세탁방법/취급시 주의사항',
				'manufacturedDate' => '제조연월',
				'warrantyPolicy' => '품질보증기준',
				'afterServiceDirector' => 'A/S 책임자',
			)
		),
		'SHOES' => array(
			'name' => '구두/신발',
			'fields' => array(
				'material' => '제품 소재',
				'color' => '색상',
				'size' => '치수',
				'manufacturer' => '제조사',
                'country' => '제조국',
				'caution' => '세탁방법/취급시 주의사항',
				'warrantyPolicy' => '품질보증기준',
				'afterServiceDirector' => 'A/S 책임자',
			)
		),
		'BAG' => array(
			'name' => '가방',
			'fields' => array(
				'type' => '종류',
				'material' => '소재',
				'color' => '색상',
				'size' => '크기',
				'manufacturer' => '제조사',
                'country' => '제조국',
				'caution' => '취급시 주의사항',
				'warrantyPolicy' => '품질보증기준',
				'afterServiceDirector' => 'A/S 책임자',
			)
		),
		'FASHION_ITEM' => array(
			'name' => '패션잡화',
			'fields' => array(
				'type' => '종류',
				'material' => '소재',
				'size' => '치수',
				'manufacturer' => '제조사',
                'country' => '제조국',
				'caution' => '취급시 주의사항',
				'warrantyPolicy' => '품질보증기준',
				'afterServiceDirector' => 'A/S 책임자',
			)
		),
		'BEDDING_CURTAIN' => array(
			'name' => '침구류/커튼',
			'fields' => array(
				'material' => '제품 소재',
				'color' => '색상',
				'size' => '치수',
				'component' => '제품구성',
				'manufacturer' => '제조사',
                'country' => '제조국',
				'caution' => '세탁방법/취급시 주의사항',
				'warrantyPolicy' => '품질보증기준',
				'afterServiceDirector' => 'A/S 책임자',
			)
		),
		'FURNITURE' => array(
			'name' => '가구',
			'fields' => array(
				'name' => '품명',
				'certification' => 'KC 인증 필 유무',
				'color' => '색상',
				'component' => '구성품',
				'material' => '주요 소재',
				'manufacturer' => '제조사',
				'country' => '제조국',
				'size' => '크기',
				'installationCharge' => '배송 설치 비용',
				'warrantyPolicy' => '품질보증기준',
				'afterServiceDirector' => 'A/S 책임자',
			)
		),
		'IMAGE_APPLIANCE' => array(
			'name' => '영상가전',
			'fields' => array(
				'name' => '품명 및 모델명',
				'certification' => 'KC 인증 필 유무',
                'energySpec' => '정격전압/소비전력',
				'energySpec' => '에너지소비효율등급',
				'releasedDate' => '출시년월',
				'manufacturer' => '제조사',
				'country' => '제조국',
				'size' => '크기',
				'displaySpecification' => '화면사양',
				'warrantyPolicy' => '품질보증기준',
				'afterServiceDirector' => 'A/S 책임자',
			)
		),
		'HOME_APPLIANCE' => array(
			'name' => '가정용전기제품',
			'fields' => array(
				'name' => '품명 및 모델명',
				'certification' => 'KC 인증 필 유무',
				'powerConsumption' => '정격전압/소비전력',
				'energySpec' => '에너지소비효율등급',
				'releasedDate' => '출시년월',
				'manufacturer' => '제조사',
				'country' => '제조국',
				'size' => '크기',
				'warrantyPolicy' => '품질보증기준',
				'afterServiceDirector' => 'A/S 책임자',
			)
		),
		'SEASONAL_APPLIANCE' => array(
			'name' => '계절가전',
			'fields' => array(
				'name' => '품명 및 모델명',
				'certification' => 'KC 인증 필 유무',
				'powerConsumption' => '정격전압/소비전력',
				'energySpec' => '에너지소비효율등급',
				'releasedDate' => '출시년월',
				'manufacturer' => '제조사',
				'country' => '제조국',
				'size' => '크기',
				'airConditionedArea' => '냉난방면적',
				'installationCharge' => '추가설치비용',
				'warrantyPolicy' => '품질보증기준',
				'afterServiceDirector' => 'A/S 책임자',
			)
		),
		'OFFICE_EQUIPMENT' => array(
			'name' => '사무용기기',
			'fields' => array(
				'name' => '품명 및 모델명',
				'certification' => 'KC 인증 필 유무',
				'powerConsumption' => '정격전압/소비전력',
				'energySpec' => '에너지소비효율등급',
				'releasedDate' => '출시년월',
				'manufacturer' => '제조사',
				'country' => '제조국',
				'size' => '크기',
				'weight' => '무게',
				'majorSpecification' => '주요 사양',
				'warrantyPolicy' => '품질보증기준',
				'afterServiceDirector' => 'A/S 책임자',
			)
		),
		'OPTICS_EQUIPMENT' => array(
			'name' => '광학기기',
			'fields' => array(
				'name' => '품명 및 모델명',
				'certification' => 'KC 인증 필 유무',
				'releasedDate' => '출시년월',
				'manufacturer' => '제조사',
				'country' => '제조국',
				'sizeAndWeight' => '크기,무게',
				'majorSpecification' => '주요 사양',
				'warrantyPolicy' => '품질보증기준',
				'afterServiceDirector' => 'A/S 책임자',
			)
		),
		'MICROELECTRONICS' => array(
			'name' => '소형전자',
			'fields' => array(
				'name' => '품명 및 모델명',
				'certification' => 'KC 인증 필 유무',
				'voltageAndPower' => '정격전압/소비전력',
				'releasedDate' => '출시년월',
				'manufacturer' => '제조사',
				'country' => '제조국',
				'sizeAndWeight' => '크기,무게',
				'majorSpecification' => '주요 사양',
				'warrantyPolicy' => '품질보증기준',
				'afterServiceDirector' => 'A/S 책임자',
			)
		),
		'CELLPHONE' => array(
			'name' => '휴대폰',
			'fields' => array(
				'name' => '품명 및 모델명',
				'certification' => 'KC 인증 필 유무',
				'releasedDate' => '출시년월',
				'manufacturer' => '제조사',
				'country' => '제조국',
				'sizeAndWeight' => '크기,무게',
				'telecomJoinCondition' => '이동통신 가입조건',
				'majorSpecification' => '주요 사양',
				'warrantyPolicy' => '품질보증기준',
				'afterServiceDirector' => 'A/S 책임자',
			)
		),
		'NAVIGATION' => array(
			'name' => '내비게이션',
			'fields' => array(
				'name' => '품명 및 모델명',
				'certification' => 'KC 인증 필 유무',
				'voltageAndPower' => '정격전압/소비전력',
				'releasedDate' => '출시년월',
				'manufacturer' => '제조사',
				'country' => '제조국',
				'sizeAndWeight' => '크기,무게',
				'majorSpecification' => '주요 사양',
				'appUpdateCharge' => '앱 업데이트 비용/무상기간',
				'warrantyPolicy' => '품질보증기준',
				'afterServiceDirector' => 'A/S 책임자',
			)
		),
		'CAR_EQUIPMENT' => array(
			'name' => '자동차용품',
			'fields' => array(
				'name' => '품명 및 모델명',
				'releasedDate' => '출시년월',
				'certification' => 'KC 인증 필 유무',
				'manufacturer' => '제조사',
				'country' => '제조국',
				'size' => '크기',
				'applicableModel' => '적용차종',
				'caution' => '제품사용으로 인한 위험 및 유의사항',
				'warrantyPolicy' => '품질보증기준',
				'afterServiceDirector' => 'A/S 책임자',
                'testLicenseNumber' => '검사합격증번호',
			)
		),
		'MEDICAL_EQUIPMENT' => array(
			'name' => '의료기기',
			'fields' => array(
				'name' => '품명 및 모델명',
				'certification' => '의료기기법상 허가 신고 번호 및 광고사전심의필 유무',
				'voltageAndPower' => '정격전압/소비전력',
				'releasedDate' => '출시년월',
				'manufacturer' => '제조사',
				'country' => '제조국',
				'usage' => '제품의 사용목적 및 사용방법',
				'caution' => '취급시 주의사항',
				'warrantyPolicy' => '품질보증기준',
				'afterServiceDirector' => 'A/S 책임자',
			)
		),
		'KITCHEN_UTENSILS' => array(
			'name' => '주방용품',
			'fields' => array(
				'name' => '품명 및 모델명',
				'material' => '재질',
				'component' => '구성품',
				'size' => '크기',
				'releasedDate' => '출시년월',
				'manufacturer' => '제조사',
				'country' => '제조국',
				'importDeclaration' => '식품위생법에 따른 수입신고',
				'warrantyPolicy' => '품질보증기준',
				'afterServiceDirector' => 'A/S 책임자',
			)
		),
		'COSMETIC' => array(
			'name' => '화장품',
			'fields' => array(
				'capacity' => '용량 또는 중량',
				'majorSpecification' => '제품 주요 사양',
				'expirationDate' => '사용기한 또는 개봉 후 사용기간',
				'usage' => '사용방법',
				'manufacturer' => '제조사 및 제조판매업자',
				'country' => '제조국',
				'majorIngredient' => '모든성분',
				'certification' => '기능성 화장품의 경우 화장품법에 따른 식품의약품안전처 심사필 유무',
				'caution' => '사용할 때 주의사항',
				'warrantyPolicy' => '품질보증기준',
				'afterServiceDirector' => 'A/S 책임자',
			)
		),
		'JEWELRY' => array(
			'name' => '귀금속/보석/시계류',
			'fields' => array(
				'material' => '소재/순도/밴드재질(시계의 경우)',
				'weight' => '중량',
				'manufacturer' => '제조자',
				'country' => '제조국',
				'size' => '치수',
				'caution' => '착용 시 주의사항',
				'majorSpecification' => '주요 사양',
				'warrantyProvided' => '보증서 제공 여부',
				'warrantyPolicy' => '품질보증기준',
				'afterServiceDirector' => 'A/S 책임자',
			)
		),
		'FOOD' => array(
			'name' => '식품',
			'fields' => array(
				'weight' => '포장단위별 용량(중량), 수량, 크기',
				'producer' => '생산자',
				'originArea' => '농수산물의 원산지 표시에 관한 법률에 따른 원산지',
				'producedDate' => '제조연월일(포장일 또는 생산연도), 유통기한 또는 품질유지기한',
				'labellingRequirement' => '관련법상 표시사항',
				'component' => '상품구성',
				'usage' => '보관방법 또는 취급방법',
				'consumerServicePhoneNumber' => '소비자 상담 관련 전화번호',
                'consumerSafetyNotice' => '소비자 안전을 위한 주의사항',
			)
		),
		'PROCESSED_FOOD' => array(
			'name' => '가공식품',
			'fields' => array(
                'name' => '제품명',
                'producer' => '수입품의 경우 생산자, 수입자 및 제조국',
				'type' => '식품의 유형',
				'manufacturerAndLocation' => '제조업소의 명칭과 소재지',
				'producedDate' => '제조연월일(포장일 또는 생산연도), 유통기한 또는 품질유지기한',
				'capacity' => '포장단위별 용량 및 수량',
				'originalMaterialContent' => '원재료 및 함량',
				'nutritionalContents' => '영양정보',
				'geneFood' => '유전자 변형 식품의 경우 표시',
				'consumerSafetyNotice' => '소비자 안전을 위한 주의사항',
				'imported' => '수입식품인 경우 `식품위생법에 따른 수입신고를 필함`의 문구',
				'consumerServicePhoneNumber' => '소비자 상담 관련 전화번호',
			)
		),
		'HEALTH_FUNCTIONAL_FOOD' => array(
			'name' => '건강기능식품',
			'fields' => array(
                'name' => '제품명',
				'type' => '식품의 유형',
				'manufacturerAndLocation' => '제조업소의 명칭과 소재지',
				'function' => '기능정보',
				'caution' => '섭취량, 섭취방법, 섭취시 주의사항 및 부작용 발생 가능성',
				'producedDate' => '제조연월일(포장일 또는 생산연도), 유통기한 또는 품질유지기한',
				'capacity' => '포장단위별 용량 및 수량',
				'originalMaterialContent' => '원재료명 및 함량',
				'nutritionalContents' => '영양정보',
				'noMedicalDescription' => '질병의 예방 및 치료를 위한 의약품이 아니라는 내용의 표현',
				'geneFood' => '유전자 변형 식품의 경우 표시',
				'certification' => '표시광고 사전심의필',
				'imported' => '수입식품인 경우 `식품위생법에 따른 수입신고를 필함`의 문구',
				'consumerServicePhoneNumber' => '소비자 상담 관련 전화번호',
                'consumerSafetyNotice' => '소비자 안전을 위한 주의사항',
			)
		),
		'KIDS' => array(
			'name' => '영유아용품',
			'fields' => array(
				'name' => '품명 및 모델명',
				'color' => '색상',
				'material' => '재질',
				'manufacturer' => '제조사',
                'country' => '제조국',
				'ageOrWeight' => '사용연령 또는 체중범위',
				'releasedDate' => '출시년월',
				'caution' => '취급방법 및 취급시 주의사항, 안전표시 (주의, 경고 등)',
				'sizeAndWeight' => '크기, 중량',
				'certification' => 'KC 인증 필 유무',
				'warrantyPolicy' => '품질보증기준',
				'afterServiceDirector' => 'A/S 책임자',
			)
		),
		'MUSICAL_INSTRUMENT' => array(
            'name' => '악기',
            'fields' => array(
				'name' => '품명 및 모델명',
				'color' => '색상',
				'material' => '재질',
				'manufacturer' => '제조사',
                'country' => '제조국',
				'size' => '크기',
				'component' => '제품구성',
				'detailSpecification' => '상품별 세부사양',
				'releasedDate' => '출시년월',
				'warrantyPolicy' => '품질보증기준',
				'afterServiceDirector' => 'A/S 책임자',
			)
		),
		'SPORTS_EQUIPMENT' => array(
			'name' => '스포츠용품',
			'fields' => array(
				'name' => '품명 및 모델명',
				'color' => '색상',
				'material' => '재질',
				'manufacturer' => '제조사',
                'country' => '제조국',
				'sizeAndWeight' => '크기,중량',
				'component' => '제품구성',
				'detailSpecification' => '상품별 세부사양',
				'releasedDate' => '출시년월',
				'warrantyPolicy' => '품질보증기준',
				'afterServiceDirector' => 'A/S 책임자',
			)
		),
		'BOOK' => array(
			'name' => '서적',
			'fields' => array(
				'name' => '도서명',
				'author' => '저자',
				'publisher' => '출판사',
				'size' => '크기',
				'page' => '쪽수',
				'component' => '제품구성',
				'publishedDate' => '출간일',
				'introduction' => '목차 또는 책소개',
			)
		),
		'HOTEL_PENSION_BOOKING' => array(
			'name' => '호텔/펜션예약',
			'fields' => array(
				'countryOrLocation' => '국가 또는 지역명',
				'accommodationType' => '숙소형태',
				'roomGrade' => '등급,객실타입',
				'availablePerson' => '사용가능 인원,인원 추가 시 비용',
				'facility' => '부대시설, 제공서비스(조식 등)',
				'cancellationPolicy' => '취소 규정(환불, 위약금 등)',
				'contact' => '예약담당 연락처',
			)
		),
		'TRAVEL_PACKAGE' => array(
			'name' => '여행상품',
			'fields' => array(
				'agency' => '여행사',
				'flight' => '이용항공편',
				'schedule' => '여행기간 및 일정',
				'totalPerson' => '총 예정 인원, 출발 가능 인원',
				'accommodation' => '숙박정보',
				'included' => '포함내역',
				'additionalCharge' => '추가 경비 항목과 금액',
				'cancellationPolicy' => '취소 규정(환불, 위약금 등)',
				'alertLevel' => '해외여행의 경우 외교통상부가 지정하는 여행경보단계',
				'contact' => '예약담당 연락처',
			)
		),
		'AIRLINE_TICKET' => array(
			'name' => '항공권',
			'fields' => array(
				'fareCondition' => '요금조건, 왕복/편도 여부',
				'validDate' => '유효기간',
				'limitation' => '제한사항',
				'seatType' => '좌석종류',
				'ticketDeliveryMean' => '티켓수령 방법',
				'notIncluded' => '가격에 포함되지 않은 내역 및 금액',
				'cancellationPolicy' => '취소 규정(환불, 위약금 등)',
				'contact' => '예약담당 연락처',
			)
		),
		'RENT_CAR' => array(
			'name' => '자동차대여서비스(렌터카)',
			'fields' => array(
				'modelName' => '차종',
				'ownershipTransfer' => '소유권 이전 조건',
				'refundPolicy' => '예약 취소 또는 중도 해약 시 환불 기준',
				'fuelChargeMean' => '차량 반환 시 연료대금 정산 방법',
				'additionalCharge' => '추가 선택 시 비용(자차면책제도, 내비게이션 등)',
				'lossOrDamage' => '차량의 고장, 훼손 시 소비자 책임',
				'consumerServicePhoneNumber' => '소비자상담 관련 전화번호',
			)
		),
		'RENTAL_HA' => array(
			'name' => '물품대여서비스(정수기,비데,공기청정기 등)',
			'fields' => array(
				'name' => '품명 및 모델명',
				'specification' => '제품 사양(용량, 소비전력 등)',
				'lossOrDamage' => '상품의 고장, 분실, 훼손 시 소비자 책임',
				'refundPolicy' => '중도 해약 시 환불 기준',
				'ownershipTransfer' => '소유권 이전 조건',
				'maintenancePolicy' => '유지보수 조건',
				'consumerServicePhoneNumber' => '소비자상담 관련 전화번호',
			)
		),
		'RENTAL_ETC' => array(
			'name' => '물품대여서비스(서적,유아용품,행사용품 등)',
			'fields' => array(
				'name' => '품명 및 모델명',
				'ownershipTransfer' => '소유권 이전 조건',
				'lossOrDamage' => '상품의 고장, 분실, 훼손 시 소비자 책임',
				'refundPolicy' => '중도 해약 시 환불 기준',
				'consumerServicePhoneNumber' => '소비자상담 관련 전화번호',
			)
		),
		'DIGITAL_CONTENTS' => array(
			'name' => '디지털콘텐츠(음원,게임,인터넷강의 등)',
			'fields' => array(
				'manufacturer' => '제작자 또는 공급자',
				'termsAndPeriod' => '이용조건, 이용기간',
				'medium' => '상품 제공 방식',
				'minimumSpecificationAndSoftware' => '최소 시스템 사양/필수 소프트웨어',
				'cancellationPolicy' => '청약철회 및 계약의 해제, 해지에 따른 효과',
				'consumerServicePhoneNumber' => '소비자상담 관련 전화번호',
			)
		),
		'GIFTCARD_COUPON' => array(
			'name' => '상품권/쿠폰',
			'fields' => array(
				'publisher' => '발행자',
				'validPeriod' => '유효기간, 이용조건',
				'availableShop' => '이용 가능 매장',
				'refundPolicy' => '잔액 환급 조건',
				'consumerServicePhoneNumber' => '소비자상담 관련 전화번호',
			)
		),
		'MOBILE_COUPON' => array(
			'name' => '모바일쿠폰',
			'fields' => array(
				'publisher' => '발행자',
				'validPeriod' => '유효기간, 이용조건',
				'terms' => '이용조건',
				'availableShop' => '이용 가능 매장',
				'refundPolicy' => '환불조건 및 방법',
				'consumerServicePhoneNumber' => '소비자상담 관련 전화번호',
			)
		),
		'MOVIE_CONCERT' => array(
			'name' => '영화/공연',
			'fields' => array(
				'hostOrAgency' => '주최 또는 기획',
				'leadingActor' => '주연',
				'grade' => '관람등급',
				'time' => '상연/공연시간',
				'place' => '상연/공연장소',
				'cancellationPolicy' => '예매 취소 조건',
				'cancellationRefundMean' => '취소/환불방법',
				'consumerServicePhoneNumber' => '소비자상담 관련 전화번호',
			)
		),
		'ETC_SERVICE' => array(
			'name' => '기타 용역',
			'fields' => array(
				'provider' => '서비스 제공 사업자',
				'certification' => '법에 의한 인증, 허가 등을 받았음을 확인할 수 있는 경우 그에 대한 사항',
				'terms' => '이용조건',
				'cancellationPolicy' => '취소, 중도해약, 해지 조건 및 환불기준',
				'cancellationAndRefundMean' => '취소, 환불방법',
				'consumerServicePhoneNumber' => '소비자상담 관련 전화번호',
			)
		),
		'ETC_PRODUCT' => array(
			'name' => '기타 재화',
			'fields' => array(
				'name' => '품명 및 모델명',
				'certification' => '법에 의한 인증, 허가 등을 받았음을 확인할 수 있는 경우 그에 대한 사항',
				'manufacturer' => '제조사',
				'contact' => 'A/S 책임자와 전화번호 또는 소비자상담 관련 전화번호',
			)
		),
	);

?>