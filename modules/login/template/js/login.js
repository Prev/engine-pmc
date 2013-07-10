var form;
var rsa;

window.addEventListener("load", function () {
	form = document.forms.login_form;
	rsa = new RSA("10001", null, "801d5852519f4382e8faa29ae15222d");
	
	toggleSecureLogin(form.secure_login);
});

function toggleSecureLogin(target) {
	if (target.checked)
		form.action = form.action.split("procLogin").join("procSecureLogin");
	else
		form.action = form.action.split("procSecureLogin").join("procLogin");
}

function procLogin() {
	try {
		if (!form.id.value) { alert("아이디를 입력 해 주세요."); return false; }
		if (!form.pw.value) { alert("비밀번호를 입력 해 주세요."); return false; }
		
		if (!form.secure_login.checked) return true;
		else {
			var enc_id = rsa.encrypt(form.id.value);
			var enc_pw = rsa.encrypt(form.pw.value);
			
			form.enc_id.value = enc_id;
			form.enc_pw.value = enc_pw;
			form.check_sum.value = md5(form.id.value + form.pw.value);
			
			form.id.disabled = true;
			form.pw.disabled = true;
			
			return true;
		}
	}catch (e) {
		alert("오류가 발생했습니다.");
		console.dir(e);
		return false;
	}
}