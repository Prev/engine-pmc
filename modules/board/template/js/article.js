var cElement;
var cContent;
var cUpdateBtn;
var modifyWrapper;
var last_commentIndex;

var commentLists;
var rUpdateBtn;
var replyWrapper;
var last_replyIndex;

var REPLY_COMMENT_INNER_HTML;

function toggleUpdateComment(index) {
	if (cContent) cContent.style.display = "block";
	if (modifyWrapper) modifyWrapper.style.display = "none";
	if (cUpdateBtn) cUpdateBtn.innerHTML = fetchLocale({"en":"modify", "ko":"수정"});

	if (last_commentIndex == index) {
		last_commentIndex = null;
		cContent = null;
		modifyWrapper = null;
		cUpdateBtn = null;
		return;
	}

	cElement = document.getElementById("comment" + index); //comment element
	cContent = document.getElementById("comment"+index+"-content");
	cUpdateBtn = document.getElementById("comment"+index+"-update-a");
	modifyWrapper = document.getElementById("modify-comment"+index+"-wrap");
	
	var isSecret = parseInt(document.getElementById("comment"+index+"-is-secret").value);

	cContent.style.display = "none";
	modifyWrapper.style.display = "block";
	cUpdateBtn.innerHTML = fetchLocale({"en":"cancle modify", "ko":"수정 취소"});

	document.getElementById("modify-comment"+index+"-secret-checkbox").checked = isSecret;

	last_commentIndex = index;
}


function toggleReplyComment(index, parentId) {
	var nextParentElement = document.getElementById("comment" + (index+1)); //comment element

	if (last_replyIndex == index) {
		last_replyIndex = null;
		rUpdateBtn.innerHTML = fetchLocale({"en":"add reply", "ko":"답글"});
		commentLists.removeChild(replyWrapper);
		replyWrapper = null;
		return;
	}

	if (replyWrapper) {
		rUpdateBtn.innerHTML = fetchLocale({"en":"add reply", "ko":"답글"});
		commentLists.removeChild(replyWrapper);
		replyWrapper = null;
	}
	
	var tElement = document.getElementById("comment" + index);
	rUpdateBtn = document.getElementById("comment"+index+"-reply-a");

	var topId = document.getElementById("comment"+index+"-top-id").value;
	var topIsSecret = parseInt(document.getElementById("comment" + index + "-is-secret").value);

	replyWrapper = document.createElement("div");
	replyWrapper.setAttribute("class", "reply-comment-wrap");
	replyWrapper.innerHTML = REPLY_COMMENT_INNER_HTML;

	if (nextParentElement) commentLists.insertBefore(replyWrapper, nextParentElement);
	else commentLists.appendChild(replyWrapper);


	var cb = document.getElementById("reply-secret-comment");

	if (topIsSecret) {
		cb.checked = true;
		cb.disabled = true;
		cb.className = "disabled";
		document.getElementById("reply-textarea").value = "";
		document.getElementById("reply-textarea").placeholder = fetchLocale({"en":"Input reply (only secret)", "ko":"답글을 입력하세요 (비밀 답글만 작성이 가능합니다)"});
	}else {
		cb.checked = false;
		cb.disabled = false;
		cb.className = "";
	}

	rUpdateBtn.innerHTML = fetchLocale({"en":"cancle reply", "ko":"답글 취소"});
	document.getElementById("reply-parent-id").value = parentId;
	document.getElementById("reply-top-id").value = topId ? topId : parentId;

	last_replyIndex = index;
}

window.addEventListener("load", function (e) {
	commentLists = document.getElementById("comment-list");
	
	var replyWrapperEx = document.getElementById("reply-comment-wrap-ex");

	REPLY_COMMENT_INNER_HTML = replyWrapperEx.innerHTML;


	document.getElementsByClassName("article-comment-wrap")[0].removeChild(replyWrapperEx);
})