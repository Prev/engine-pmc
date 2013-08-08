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

# Install
engine-pmc 설치 및 개발 방법은 **[이 문서](https://github.com/Prev/engine-pmc/wiki)**에 정의되어 있습니다.


#Change Log

#### v 0.3.2
#### v 0.3.1
+ 모바일 지원

#### v 0.3.0
+ global.js 파일 추가
 + getUrl(), getUrlA() 함수 지원
+ 덧글 수정 기능 추가
+ 덧글 답글 기능 추가

#### v 0.2.9
+ 게시판 DB 구조 변경 및 프로세스 개선 (top_no 기본 NULL처리)

#### v 0.2.8
+ 파일 시스템 개선

#### v 0.2.7
+ 게시판 기능 강화
+ 글쓰기 기능 추가
+ 파일 기능 강화
+ ORM 버그 픽스
+ info.json print_alone 속성 추가



####v 0.2.6
+ User 클래스 멤버변수 작성법 변경 -> 언더바 사용 X
+ 각종 버그 픽스

####v 0.2.5
+ 템플릿 문법 추가
 + condtion 태그 업그레이드
 + switch 태그 추가
+ 각종 버그 픽스

####v 0.2.4
+ DB구조 변경
 + menu 테이블 css 속성 제거
 + files 테이블 추가
+ editor 모듈 추가 (다음 오픈 에디터 사용)
+ file 모듈 추가
+ 각종 버그 픽스

####v 0.2.3
+ DB구조 일부 변경
 + menu visible 컬럼 추가
 + board 다국어 지원
+ getUrlA 버그 픽스
+ 오픈소스 라이브러리 페이지 추가
+ 템플릿 문법 <link> 추가
+ common.function.php 주석 추가

####v 0.2.1 ~ 0.2.2
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
