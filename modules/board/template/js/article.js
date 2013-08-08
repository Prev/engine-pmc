var cElement;
var cContent;
var cUpdateBtn;
var modifyWrapper;
var last_commentIndex;

var commentLists;
var rUpdateBtn;
var replyWrapper;
var last_replyIndex;


function toggleUpdateComment(index) {
	if (cContent) cContent.style.display = "block";
	if (modifyWrapper) modifyWrapper.style.display = "none";
	if (cUpdateBtn) cUpdateBtn.innerHTML = "수정";

	if (last_commentIndex == index) {
		last_commentIndex = null;
		cContent = null;
		modifyWrapper = null;
		cUpdateBtn = null;
		return;
	}

	cElement = document.getElementById("comment" + index); //comment element
	cContent = cElement.getElementsByClassName("comment-content")[0];
	cUpdateBtn = cElement.getElementsByClassName("comment-update-a")[0];
	modifyWrapper = cElement.getElementsByClassName("modify-comment-wrap")[0];

	cContent.style.display = "none";
	modifyWrapper.style.display = "block";
	cUpdateBtn.innerHTML = "수정 취소";

	last_commentIndex = index;
}


function toggleReplyComment(index, parentId) {
	var nextParentElement = document.getElementById("comment" + (index+1)); //comment element

	if (last_replyIndex == index) {
		last_replyIndex = null;
		rUpdateBtn.innerHTML = "답글";
		commentLists.removeChild(replyWrapper);
		replyWrapper = null;
		return;
	}

	if (replyWrapper) {
		rUpdateBtn.innerHTML = "답글";
		commentLists.removeChild(replyWrapper);
		replyWrapper = null;
	}
	
	var tElement = document.getElementById("comment" + index);
	rUpdateBtn = tElement.getElementsByClassName("comment-reply-a")[0];
	topId = tElement.getElementsByClassName("top-id")[0].value;

	replyWrapper = document.createElement("div");
	replyWrapper.setAttribute("class", "reply-comment-wrap");
	replyWrapper.innerHTML = document.getElementById("reply-comment-wrap-ex").innerHTML;

	if (nextParentElement) commentLists.insertBefore(replyWrapper, nextParentElement);
	else commentLists.appendChild(replyWrapper);

	rUpdateBtn.innerHTML = "답글 취소";
	replyWrapper.getElementsByClassName("parent-id")[0].value = parentId;
	replyWrapper.getElementsByClassName("top-id")[0].value = topId ? topId : parentId;

	last_replyIndex = index;
}

window.addEventListener("load", function (e) {
	commentLists = document.getElementById("comment-list");
})