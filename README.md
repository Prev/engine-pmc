#engine-pmc

"engine pmc" 는 웹사이트의 개발속도, 유지보수, 공동 작업등을 쉽게 해 주는 모듈 기반의 MVC 코어입니다.

"engine pmc" 는 MVC 기능 외에 다양한 편의 기능을 제공합니다.
+ 유지보수의 편의성
+ CSS/JS 자동 캐시 컨트롤
+ less css 지원
+ 다국어 지원
+ 같은 코드로 다양한 서버에서 구동 가능
+ 다양한 config 변수 제공
+ 디버깅을 위한 var_dump2 함수


#Change Log

###v 0.1.4
+ MVC 전반적 구조 변경
++ 다중 Model, Controller, View 지원
++ action 별 MVC 지정 가능
++ default action 정의 필수로 변경
+ 모듈 MVC 작명 규칙 변경
++ ex) IndexModel, IndexController, IndexView, IndexCreditView
+ conf/info.json 파일 상위폴더로 이동 및 명령어 변경
++ action별 model, view, controller 지정 가능
++ default_model, default_view, default_controller 지정 가능
+SSO 버그 픽스

###v 0.1.3
+ 다중 모듈 지원
+ Module, View, Controller 클래스 추가
+ 템플릿 데이터 view에서 가져올 수 있게 수정
+ MVC 관련 각종 프로세스 수정
+ 템플릿 함수 Context 에서 CacheHandler 처리로 변경

###v 0.1.2
+ Context, ModuleHandler 클래스 인스턴스화
+ var_dump2, getUrlA 함수 버그픽스

###v 0.1.1
+ var_dump2 함수 추가

###v 0.1.0
