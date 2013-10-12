/**
 * url 관련 정보를 반환
 *
 * @param $module 정의시 해당 모듈의 절대경로를 반환함
	 * @param $action 까지 정의시 action이 포함된 모듈의 절대경로 반환
 * @param $queryParame 까지 정의시 module, action 파라미터 뒤에 추가 파라미터값을 넣어 반환
 * @param $url 까지 정의시 RELAVITE_URL이 아닌 $url을 기반으로 파라미터값을 더해서 반환
 *		* $url에서 미리 정의된 파라미터 이름과 $queryParam 등에서 정의된 파라미터 이름이 겹칠 시 $queryParam 등에서 정의된 파라미터 값이 우선
 *		* 우선 순위는 module==action > queryParam > url
 * @param $module이 NULL일시 @param $action은 무시됨
 */
function getUrl(module, action, queryParam, url) {
	if (!module) module = "";
	if (!url) url = RELATIVE_URL;
	if (!action) action = null;
	if (!queryParam) queryParam = "";

	var urlFragment = (url.indexOf("#") != -1) ? url.split("#")[1] : "";
	var urlQuery = (url.indexOf("?") != -1) ? (url.split("#")[0]).split("?")[1] : "";
	var queryObj = {};

	if (urlQuery) {
		queryObj = urlQueryToObject(urlQuery);
	}

	if (typeof queryParam == 'string') queryParam = urlQueryToObject(queryParam);
	for (key in queryParam)
		queryObj[key] = queryParam[key];

	queryParam = objectToUrlQuery(queryObj);
	
	if (module) {
		if (action) queryParam = 'action=' + action + '&' + queryParam;
		queryParam = 'module=' + module + '&' + queryParam;
	}

	url = url.split("?")[0].split("#")[0];
	if (url.lastIndexOf("/") != url.length - 1) url += "/";
	if (queryParam.lastIndexOf("&") == queryParam.length - 1) queryParam = queryParam.substr(0, queryParam.length-1);
	
	return url + (queryParam ? "?" + queryParam : "") + (urlFragment ? "#" + urlFragment : "");
}

/**
 * getUrl함수에서 @param $module, @param $action이 빠진 하수
 */
function getUrlA(queryParam, url) {
	return getUrl(null, null, queryParam, url);
}

/**
 * {'param1':'val1', 'param2':'val2'} 같은 객체을 'param1=val1&param2=val2' 의 string값으로 변환함
 */
function objectToUrlQuery(array) {
	if (!array) return null;
	else {
		var tempArr = new Array();
		for (key in array) {
			var value = array[key];

			if (!key) continue;
			else if (key && value==null) tempArr.push(key);
			else tempArr.push(key + '=' + value);
		}
		return tempArr.join("&");
	}
}

/**
 * objectToUrlQuery() 의 역함수
 * 'param1=val1&param2=val2' 같은 문자열을 {'param1':'val1', 'param2':'val2'} 같은 Object 값으로 변환함
 */
function urlQueryToObject(query) {
	var arr = new Object();
	var tempArr = query.split("&");

	for (var i=0; i<tempArr.length; i++) {
		if (tempArr[i].indexOf("=") === -1)
			arr[tempArr[i]] = null;
		else {
			var tempArr2 = tempArr[i].split("=");
			if (tempArr2[0])
				arr[tempArr2[0]] = tempArr2[1];
		}
	}
	return arr;
}

/**
 * 현재 url 반환
 */
function getCurrentUrl() {
	return REAL_URL;
}


/**
 * 실제 url 반환
 * @param $module이 NULL일시 현재 url을 반환하며, NULL이 아닐시 해당 모듈의 실제 url을 반환
 */
function getRealUrl(module) {
	if (!module)
		return REAL_URL;
	else
		return RELATIVE_URL + '/modules/' + module;
}


/**
 * 현재 설정 언어 정보를 출력
 * @param $compareLocal에 값을 지정시 현재 언어정보와 같은지 비교한 값을 반환 (bool type)
 */
function getLocale(compareLocale) {
	if (compareLocale)
		return locale == compareLocale.toLowerCase();
	else
		return locale;
}

/**
 * 언어를 파싱함
 * @param $data 에는 object, array, string(json), string(raw) 등이 올 수 있음
 * @param $data 에 raw string 값이 올 시 그대로 출력함
 * 
 * object: (object) array('en' => 'Freeboard', 'ko' => '자유게시판')
 * array: array('en' => 'Freeboard', 'ko' => '자유게시판')
 * string(json): {"en":"Freeboard", "ko":"자유게시판"}
 * string(raw): "자유게시판"
 */
function fetchLocale(data) {
	if (typeof data == 'object' || typeof data == 'array') {
		if (data[locale])
			return data[locale];
		else if (data[DEFAULT_LOCALE])
			return data[DEFAULT_LOCALE];
		else {
			for (var i in data)
				return data[i];
		}
	}
	else 
		return data;	
}