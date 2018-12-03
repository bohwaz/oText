
function writeForm() {
	var _this = this;

	/* misc DOM Nodes */

	// Getting the entire form
	this.formatbutNode = document.querySelector('.formatbut');
	if (this.formatbutNode.length == 0) return;

	var form = this.formatbutNode.parentNode;
	while (form.tagName !== "FORM") { form = form.parentNode;}
	this.theForm = form;

	// getting the textarea field were the tags have to be put.
	var field = this.formatbutNode.nextSibling;
	while (field.tagName !== "TEXTAREA") { field = field.nextSibling; }
	this.theTargetField = field;


	this.insertTag = function () {
		// the button we did click
		var button = this;

		// the bars with all the buttons
		var bar = _this.formatbutNode
		// the textarea field were the tags have to be put.
		var targetField = _this.theTargetField;

		// the tags
		var x = button.dataset.tag.indexOf('|');//      ↓ if no "|" is found         ↓ unescape \n
		var startTag = button.dataset.tag.substring(0, ((x === -1) ? undefined : x)).replace(/\\n/g, "\n") || "";
		var endTag = button.dataset.tag.substring((x+1 || button.dataset.tag.length)).replace(/\\n/g, "\n") || "";

		// the real job is done here
		var scroll = targetField.scrollTop;
		targetField.focus();
		var beforeSelection  = targetField.value.substring(0, targetField.selectionStart);
		var currentSelection = targetField.value.substring(targetField.selectionStart, targetField.selectionEnd);
		var afterSelection   = targetField.value.substring(targetField.selectionEnd);

		if (currentSelection.length == 0) {
			if (endTag != "") {
				currentSelection = "TEXT";
			}
		}
		targetField.value = beforeSelection + startTag + currentSelection + endTag + afterSelection;

		targetField.focus();
		targetField.setSelectionRange(beforeSelection.length + startTag.length, beforeSelection.length + startTag.length + currentSelection.length);
		targetField.scrollTop = scroll;
	}


	this.buttons = this.theForm.querySelectorAll('.formatbut button:not(.js-action)');

	for (var button of this.buttons) {
		button.addEventListener('click', _this.insertTag);
	}


}
new writeForm();

function reply(code) {
	var field = document.getElementById('form-commentaire').getElementsByTagName('textarea')[0];
	field.focus();
	if (field.value !== '') {
		field.value += '\n\n';
	}
	field.value += code;
	field.scrollTop = 10000;
	field.focus();
}

function displayMenu(e) {
	var button = e.target;
	var menu = document.getElementById('sidenav');
	button.classList.toggle('active');
	menu.classList.toggle('shown');
}

if (document.getElementById('erreurs')) {
	window.location.hash = 'erreurs';
	window.scrollBy(0, -100);
}
