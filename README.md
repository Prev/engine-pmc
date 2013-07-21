#engine-pmc

"engine pmc" 는 웹사이트의 개발속도, 유지보수, 공동 작업등을 쉽게 해 주는 모듈 기반의 MVC 코어입니다.

"engine pmc" 는 MVC 기능 외에 다양한 편의 기능을 제공합니다.
+ 유지보수의 편의성
+ 다중 모듈 지원
+ CSS/JS 자동 캐시 컨트롤
+ less css 지원
+ 다국어 지원
+ 같은 코드로 다양한 서버에서 구동 가능
+ 다양한 config 변수 제공
+ 디버깅의 편리

[데모사이트](http://engine-pmc.parameter.kr)

#Install
설정할 대부분의 파일은 `config` 폴더안에 있습니다.


#####먼저 `config/server-info.json` 파일을 자신의 서버와 맞게 설정하세요
만약 서버 도메인이 `localhost` 이고 `/pmc` 라는 하위 경로를 사용 할 경우 아래처럼 사용하시면 됩니다.
```json
{
 "type":"test",
 "protocol":"http",
 "host":"localhost",
 "session_domain":"localhost",
 "uri":"/pmc"
}
```
테스트 서버의 경에는 `type`을 `test`로, 실제 운영할 서버의 경우에는 `running` 으로 설정 해주시기 바랍니다.

#####둘째, `config/database.php` 파일에서 데이터베이스 관련 정보를 업데이트 하십시오
```php
return (object) array(
 'type' => 'mysqli',
 'username' => '%접속아이디%',
 'password' => '%접속비밀번호%',
 'database_name' => '%데이터베이스 이름%',
 'prefix' => 'pmc_'
);
```

#####셋째, cache 생성 문제
cache 생성이 제대로 되지 않을 경우 cache 폴더를 생성한 후 파일 생성 및 수정 권한을 추가하십시오

#####넷째, 보안관련 키 문제
이 프로젝트는 오픈소스로 관리되므로 보안관련 커스텀 마이징이 필요합니다.
보안 관련 키 설정은 `config/secure-keys.php` 에 정의되어 있습니다.
+ `RSA_*` 는 로그인 보안에 관련된 키로 RSA 비대칭 암호화에 관련된 키입니다.
+ `SSO_AGENT_KEY` 는 SSO와 관련된 키인데, 이 키를 바꾸실 경우에는 `/sso-server/conf.config.php` 파일도 같이 수정 해 주시길 바랍니다.

#####기타
+ 현재 데이터베이스 자동 초기화 기능이 없으므로 `config/initialize.sql` 에서 SQL을 복사 후 실행하여 사용하시길 바랍니다.
+ 이 밖에 각종 설정은 `config/config.php` 에서 하실 수 있습니다.


#Change Log

####v 0.2.1
+ 모듈 관련 기능 변경
 + default 모듈 작성규칙 (ModuleName)Module.class.php 로 변경
 + 모듈 폴더에 default 모듈 php가 없을시 기본 Module 클래스 불러옴
+ User 클래스 checkGroup 메소드 추가

####v 0.2.0
+ SSO 처리 방법 수정 > /sso-server 로 분리

####v 0.1.9
+ SSO 처리 방법 수정
+ User 클래스 추가
+ 2단계 메뉴 지원
+ page module 추가


####v 0.1.8
+ lesscss 기능 개선
 + url() absolute path replace 처리
 + CacheHandler 에서 처리하도록 변경
+ CacheHandler 개선


####v 0.1.7
+ default layout form 디자인 개선
+ RSA key config/confing.php 에서 설정하도록 변경
+ CacheHandler에서 템플릿 컴파일 기능 TemplateHandler 로 분리
+ <import> 태그, Context::addHeaderFile 에서 requiredAgent 속성 추가
+ 로그인페이지 디자인 개선

####v 0.1.6
+ ORM 기능 개선
+ ORM 사용시 테이블 자동 prefix 탑재
+ ORM 적용

####v 0.1.5
+ strict 모드에서 동작하도록 업데이트
 + isset 설정, split deprecated 문제 개선
+ SHORT_URL 모드 해제시 버그 개선
+ 웹사이트별 다른 캐시폴더를 사용도록 업데이트

####v 0.1.4
+ MVC 전반적 구조 변경
 + 다중 Model, Controller, View 지원
 + action 별 MVC 지정 가능
 + default action 정의 필수로 변경
+ 모듈 MVC 작명 규칙 변경
 + ex) IndexModel, IndexController, IndexView, IndexCreditView
+ config/info.json 파일 상위폴더로 이동 및 명령어 변경
 + action별 model, view, controller 지정 가능
 + default_model, default_view, default_controller 지정 가능
+SSO 버그 픽스

####v 0.1.3
+ 다중 모듈 지원
+ Module, View, Controller 클래스 추가
+ 템플릿 데이터 view에서 가져올 수 있게 수정
+ MVC 관련 각종 프로세스 수정
+ 템플릿 함수 Context 에서 CacheHandler 처리로 변경

####v 0.1.2
+ Context, ModuleHandler 클래스 인스턴스화
+ var_dump2, getUrlA 함수 버그픽스

####v 0.1.1
+ var_dump2 함수 추가
