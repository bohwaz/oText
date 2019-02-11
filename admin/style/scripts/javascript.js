// *** LICENSE ***
// oText is free software.
//
// By Fred Nassar (2006) and Timo Van Neerden (since 2010)
// See "LICENSE" file for info.
// *** LICENSE ***

"use strict";

/* l10n */
if (document.getElementById('jsonLang')) {
	var BTlang = JSON.parse(document.getElementById('jsonLang').textContent);
}

// mobile test, based on computed CSS
function isMobile() {
	var domEl = document.getElementById('top');
	var style = window.getComputedStyle(domEl, null).paddingLeft;

	if (style === '8px') {
		return true;
	}
	return false;
}

// simple FNV hash from string (8char long)
function hashFnv32a(str) {
	var i, l, hval = 0x811c9dc5;
	for (i = 0, l = str.length; i < l; i++) {
		hval ^= str.charCodeAt(i);
		hval += (hval << 1) + (hval << 4) + (hval << 7) + (hval << 8) + (hval << 24);
	}
	return ("0000000" + (hval >>> 0).toString(16)).substr(-8);
}

// Date.toISOString() returns a UTC related time (with "Z" timezone offset). This one is local time (without timezone offset)
Date.prototype.toLocalISOString  = function() {
	var tzoffset = this.getTimezoneOffset() * 60000; //offset in milliseconds
	var localISOTime = (new Date(this - tzoffset)).toISOString().slice(0, -1);
	return localISOTime;
}

Date.prototype.ymdhis = function() {
	var y = this.getFullYear();
	var m = ("00" + (this.getMonth() + 1)).slice(-2); // 0-11
	var d = ("00" + (this.getDate())).slice(-2);
	//var h = ("00" + (this.getHours())).slice(-2);
	//var i = ("00" + (this.getMinutes())).slice(-2);
	//var s = ("00" + (this.getSeconds())).slice(-2);

	return "".y + m + d;
}

Date.prototype.getWeekNumber = function () {
    var target  = new Date(this.valueOf());
    var dayNr   = (this.getDay() + 6) % 7;
    target.setDate(target.getDate() - dayNr + 3);
    var firstThursday = target.valueOf();
    target.setMonth(0, 1);
    if (target.getDay() != 4) {
        target.setMonth(0, 1 + ((4 - target.getDay()) + 7) % 7);
    }
    return 1 + Math.ceil((firstThursday - target) / 604800000);
}

/* date from YYYYMMDDHHIISS format */
Date.dateFromYMDHIS = function(d) {
	var d = new Date(d.substr(0, 4), d.substr(4, 2) - 1, d.substr(6, 2), d.substr(8, 2), d.substr(10, 2), d.substr(12, 2));
	//var d = d.substr(0, 4) + '' + d.substr(4, 2) - 1 + d.substr(6, 2) + d.substr(8, 2) + d.substr(10, 2) + d.substr(12, 2);
	return d;
}

/*
	menu icons : onclick.
*/

// close already open menus, but not the current menu
function closeOpenMenus(target) {
	// close already open menus, but not the current menu
	var openMenu = document.querySelectorAll('#top > .visible');
	for (var i=0, len=openMenu.length ; i<len ; i++) {
		if (!openMenu[i].contains(target)) openMenu[i].classList.remove('visible');
	}
}

// add "click" listeners on the list of menus
['nav', 'nav-acc', 'notif-icon'].forEach(function(elem) {
	document.getElementById(elem).addEventListener('click', function(e) {
		closeOpenMenus(e.target);
		var menu = document.getElementById(elem);
		if (this === (e.target)) menu.classList.toggle('visible');
		window.addEventListener('click', function(e) {
			var openMenu = document.querySelectorAll('#top > .visible');
			// no open menus: abord
			if (!openMenu.length) return;
			// open menus ? close them.
			else closeOpenMenus(null);
		}, {once: true});

		e.stopPropagation();
	});
});


/*
	cancel button on forms.
*/
function goToUrl(pagecible) {
	window.location = pagecible;
}

/*
	On article or comment writing: insert a BBCode Tag or a Unicode char.
*/

function insertTag(e, startTag, endTag) {
	var seekField = e;
	while (!seekField.classList.contains('formatbut')) {
		seekField = seekField.parentNode;
	}
	while (!seekField.tagName || seekField.tagName != 'TEXTAREA') {
		seekField = seekField.nextSibling;
	}

	var field = seekField;
	var scroll = field.scrollTop;
	field.focus();
	var startSelection   = field.value.substring(0, field.selectionStart);
	var currentSelection = field.value.substring(field.selectionStart, field.selectionEnd);
	var endSelection     = field.value.substring(field.selectionEnd);
	if (currentSelection == "") { currentSelection = "TEXT"; }
	field.value = startSelection + startTag + currentSelection + endTag + endSelection;
	field.focus();
	field.setSelectionRange(startSelection.length + startTag.length, startSelection.length + startTag.length + currentSelection.length);
	field.scrollTop = scroll;
}

function insertChar(e, ch) {
	var seekField = e;
	while (!seekField.classList.contains('formatbut')) {
		seekField = seekField.parentNode;
	}
	while (!seekField.tagName || seekField.tagName != 'TEXTAREA') {
		seekField = seekField.nextSibling;
	}

	var field = seekField;

	var scroll = field.scrollTop;
	field.focus();

	var bef_cur = field.value.substring(0, field.selectionStart);
	var aft_cur = field.value.substring(field.selectionEnd);
	field.value = bef_cur + ch + aft_cur;
	field.focus();
	field.setSelectionRange(bef_cur.length + ch.toString.length +1, bef_cur.length + ch.toString.length +1);
	field.scrollTop = scroll;
}

/*
	Used in file upload: converts bytes to kB, MB, GB…
*/
function humanFileSize(bytes) {
	var e = Math.log(bytes)/Math.log(1e3)|0,
	nb = (e, bytes/Math.pow(1e3,e)).toFixed(1),
	unit = (e ? 'KMGTPEZY'[--e] : '') + 'B';
	return nb + ' ' + unit
}



/*
	in page maintenance : switch visibility of forms.
*/

function switch_form(activeForm) {
	var form_export = document.getElementById('form_export');
	var form_import = document.getElementById('form_import');
	var form_optimi = document.getElementById('form_optimi');
	form_export.style.display = form_import.style.display = form_optimi.style.display = 'none';
	document.getElementById(activeForm).style.display = 'block';
}

function switch_export_type(activeForm) {
	var e_json = document.getElementById('e_json');
	var e_html = document.getElementById('e_html');
	var e_zip = document.getElementById('e_zip');
	e_json.style.display = e_html.style.display = e_zip.style.display = 'none';
	document.getElementById(activeForm).style.display = 'block';
}

function hide_forms(blocs) {
	var radios = document.getElementsByName(blocs);
	var e_json = document.getElementById('e_json');
	var e_html = document.getElementById('e_html');
	var e_zip = document.getElementById('e_zip');
	var checked = false;
	for (var i = 0, length = radios.length; i < length; i++) {
		if (!radios[i].checked) {
			var cont = document.getElementById('e_'+radios[i].value);
			while (cont.firstChild) {cont.removeChild(cont.firstChild);}
		}
	}
}


function rmArticle(button) {
	if (window.confirm(BTlang.questionSupprArticle)) {
		button.type= 'submit';
		return true;
	}
	return false;
}

function rmFichier(button) {
	if (window.confirm(BTlang.questionSupprFichier)) {
		button.type='submit';
		return true;
	}
	return false;
}

/**************************************************************************************************************************************
	COMM MANAGEMENT
**************************************************************************************************************************************/

/*
	on comment : reply link « @ » quotes le name.
*/

function reply(code) {
	var field = document.querySelector('#form-commentaire textarea');
	field.focus();
	if (field.value !== '') {
		field.value += '\n';
	}
	field.value += code;
	field.scrollTop = 10000;
	field.focus();
}


/*
	unfold comment edition bloc.
	(using a floating #div that we attach to a comment)
*/

function unfold(e) {
	// the comment form
	var theForm = document.getElementById('form-commentaire');

	// get the parent node where the from should be placed
	var theComm = e.parentNode;
	while (!theComm.classList.contains('comm-main-frame')) { theComm = theComm.parentNode; }

	// if the form is allready opened, we put it back at the bottom of the page
	if (e.classList.contains('button-cancel')) {
		theComm = document.getElementById('post-nv-commentaire');
	}

	// attach the form to the comm.
	theComm.appendChild(theForm);

	// update the from data
	var comm_data = JSON.parse(theComm.querySelector('script[id]').textContent);
	theForm.querySelector('[name="commentaire"]').value = comm_data.wiki;
	theForm.querySelector('[name="auteur"]').value = comm_data.auth;
	theForm.querySelector('[name="email"]').value = comm_data.mail;
	theForm.querySelector('[name="webpage"]').value = comm_data.webp;
	theForm.querySelector('[name="comment_id"]').value = comm_data.btid;


	return false;
}


function commAction(action, button) {
	if (action == 'delete') {
		var reponse = window.confirm(BTlang.questionSupprComment);
		if (reponse == false) { return; }
	}

	var notifDiv = document.createElement('div');
	var div_bloc = button.parentNode;
	while (!div_bloc.classList.contains('commentbloc')) { div_bloc = div_bloc.parentNode; }

	div_bloc.classList.add('ajaxloading');
	var xhr = new XMLHttpRequest();
	xhr.open('POST', 'commentaires.php', true);

	xhr.onprogress = function() {
		div_bloc.classList.add('ajaxloading');
	}

	xhr.onload = function() {
		var resp = this.responseText;
		if (resp.indexOf("Success") == 0) {
			csrf_token = resp.substr(7, 40);
			if (action == 'delete') {
				div_bloc.classList.add('deleteFadeOut');
				div_bloc.addEventListener('animationend', function(event){event.target.parentNode.removeChild(event.target);}, false);
				notifDiv.textContent = BTlang.confirmCommentSuppr;
				notifDiv.classList.add('confirmation');
				document.getElementById('top').appendChild(notifDiv);
			}
			button.textContent = ((button.textContent === BTlang.activer) ? BTlang.desactiver : BTlang.activer );			
			div_bloc.classList.toggle('privatebloc');			
			// adding notif
		} else {
			// adding notif
			notifDiv.textContent = this.responseText;
			notifDiv.classList.add('no_confirmation');
			document.getElementById('top').appendChild(notifDiv);
		}
		div_bloc.classList.remove('ajaxloading');
	};
	xhr.onerror = function(e) {
		notifDiv.textContent = BTlang.errorCommentSuppr + ' ' + e.target.status;
		notifDiv.classList.add('no_confirmation');
		document.getElementById('top').appendChild(notifDiv);
		div_bloc.classList.remove('ajaxloading');
	};

	// prepare and send FormData
	var formData = new FormData();
	formData.append('token', csrf_token);
	formData.append('_verif_envoi', 1);

	if (action == 'delete') {
		formData.append('com_supprimer', button.dataset.commBtid);
	}
	else if (action == 'activate') {
		formData.append('com_activer', button.dataset.commBtid);
	}

	xhr.send(formData);

	return reponse;
}


/**************************************************************************************************************************************
	***********    *********   *********      ***   *********      ***********   
	***********   **********   **********     ***   **********     ***********   
	***           ****         ***     ***          ***     ***    ***           
 	***           ***          ***     ***    ***   ***     ***    ***           
	******        ***          **********     ***   **********     ******        
	******        ***          *********      ***   *********      ******        
	***           ***          ***   ****     ***   ***   ****     ***           
	***           ****         ***    ***     ***   ***    ***     ***           
	***********   **********   ***     ***    ***   ***     ***    ***********   
	***********    *********   ***     ****   ***   ***     ****   ***********   

	LINKS AND ARTICLE FORMS : TAGS HANDLING
**************************************************************************************************************************************/


function writeForm() {
	var _this = this;

	/* misc DOM Nodes */

	// Getting the entire form
	this.formatbutNode = document.querySelector('.formatbut');
	if (null === this.formatbutNode) return;

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

	if (this.theForm.querySelector('.formatbut .toggleAutoCorrect')) {
		this.theForm.querySelector('.formatbut .toggleAutoCorrect').addEventListener('click', function() {
			if (_this.theForm.spellcheck != true) {
				_this.theForm.setAttribute('spellcheck', true);
			}
			else {
				_this.theForm.setAttribute('spellcheck', false);
			}
			_this.theTargetField.focus();
		});
	}

	if (this.theForm.querySelector('.formatbut .toggleFullScreen')) {
		this.theForm.querySelector('.formatbut .toggleFullScreen').addEventListener('click', function() {
			_this.theForm.classList.toggle('fullscreen_text');
			_this.theTargetField.focus();
		});
	}

	this.theForm.addEventListener('submit', function() {
		this.removeAttribute('data-edited');
	});

}






/**************************************************************************************************************************************
	***           ***   ***     ***   ***    ***       ****
	***           ***   ****    ***   ***   ***      ********
	***                 *****   ***   ***  ***     ***      ***
 	***           ***   ******  ***   *** ***      ***
	***           ***   *** *** ***   *******       **********
	***           ***   ***  ******   **** ***      **********
	***           ***   ***   *****   ***   ***             ***
	***           ***   ***    ****   ***    ***   ***      ***
	***********   ***   ***     ***   ***     ***    ********
	***********   ***   ***     ***   ***      ***     ****

	LINKS AND ARTICLE FORMS : TAGS HANDLING
**************************************************************************************************************************************/

/* Adds a tag to the list when we hit "enter" */
/* validates the tag and move it to the list */
function moveTag() {
	console.log("SUBMIT");
	var iField = document.getElementById('type_tags');
	var oField = document.getElementById('selected');
	var fField = document.getElementById('categories');

	// if something in the input field : enter == add word to list of tags.
	if (iField.value.length != 0) {
		oField.innerHTML += '<li class="tag"><span>'+iField.value+'</span><a href="javascript:void(0)" onclick="removeTag(this.parentNode)">×</a></li>';
		iField.value = '';
		iField.blur(); // blur+focus needed in Firefox 48 for some reason…
		iField.focus();
		return false;
	}
	// else : real submit : seek in the list of tags, extract the tags and submit these.
	else {
		var liste = oField.getElementsByTagName('li');
		var len = liste.length;
		var iTag = '';
		for (var i = 0 ; i<len ; i++) { iTag += liste[i].getElementsByTagName('span')[0].textContent+", "; }
		fField.value = iTag.substr(0, iTag.length-2);
		return true;
	}
}

/* remove a tag from the list */
function removeTag(tag) {
	tag.parentNode.removeChild(tag);
	return false;
}













/* for links : hide the FAB button when focus on link field (more conveniant for mobile UX) */
function hideFAB() {
	if (document.getElementById('fab')) {
		document.getElementById('fab').classList.add('hidden');
	}
}
function unHideFAB() {
	if (document.getElementById('fab')) {
		document.getElementById('fab').classList.remove('hidden');
	}
}

/* for several pages: eventlistener to show/hide FAB on scrolling (avoids FAB from beeing in the way) */
function scrollingFabHideShow() {
	if ((document.body.getBoundingClientRect()).top > scrollPos) {
		unHideFAB();
	} else {
		hideFAB();
	}
	scrollPos = (document.body.getBoundingClientRect()).top;
}










/**************************************************************************************************************************************
	***********   ***   ***           *********       ****
	***********   ***   ***           *********      ********
	***                 ***           ***          ***      ***
 	********      ***   ***           *******      ***
	********      ***   ***           *******       **********
	***           ***   ***           ***           **********
	***           ***   ***           ***                   ***
	***           ***   ***           ***          ***      ***
	***           ***   ***********   **********     ********
	***           ***   ***********   **********       ****

	FILE UPLOADING : DRAG-N-DROP
**************************************************************************************************************************************/

/* Drag and drop event handlers */
function handleDragEnd() {
	document.getElementById('dragndrop-area').classList.remove('fullpagedrag');
}

function handleDragLeave(e) {
	console.log('leave');
	if (document.getElementById('dragndrop-area').classList.contains('fullpagedrag')) {
		document.getElementById('dragndrop-area').classList.remove('fullpagedrag');
	}
}

function handleDragOver(e) {
	e.preventDefault();
}

function handleDragEnter(e) {
	e.preventDefault();

    if (e.dataTransfer.types) {
        for (var i=0; i<e.dataTransfer.types.length; i++) {
            if (e.dataTransfer.types[i] == "Files") {
				document.getElementById('dragndrop-area').classList.add('fullpagedrag');
                return true;
            }
        }
    }
    
    return false;

}

// process bunch of files
function handleDrop(event) {
	event.preventDefault();
	console.log('drag drop')
	document.getElementById('dragndrop-area').classList.remove('fullpagedrag');
	// detects if drag contains files.
	if (!event.dataTransfer.files  || !event.dataTransfer.files.length) return false;
	var filelist = event.dataTransfer.files;

	var result = document.getElementById('result');
	document.getElementById('dragndrop-area').classList.remove('fullpagedrag');

	if (nbDraged === false) { nbDone = 0; }


	for (var i = 0, nbFiles = filelist.length ; i < nbFiles && i < 500; i++) { // limit is for not having an infinite loop
		var rand = 'i_'+Math.random()
		filelist[i].locId = rand;
		list.push(filelist[i]);
		var div = document.createElement('div');
			div.classList.add('pending');
			div.classList.add('fileinfostatus');
			div.id = rand;
		var fname = document.createElement('span');
		    fname.classList.add('filename');
		    fname.textContent = escape(filelist[i].name);
		var flink = document.createElement('a');
		    flink.classList.add('filelink');
		var fsize = document.createElement('span');
		    fsize.classList.add('filesize');
		    fsize.textContent = '('+humanFileSize(filelist[i].size)+')';
		var fstat = document.createElement('span');
		    fstat.classList.add('uploadstatus');
		    fstat.textContent = 'Ready';

		div.appendChild(fname);
		div.appendChild(flink);
		div.appendChild(fsize);
		div.appendChild(fstat);

		result.appendChild(div);
	}
	nbDraged = list.length;
	// deactivate the "required" attribute of file (since no longer needed)
	document.getElementById('fichier').required = false;
}

// OnSubmit for files dragNdrop.
function submitdnd(event) {
	// files have been dragged (means also that this is not a regulat file submission)
	if (nbDraged != 0) {
		// proceed to upload
		uploadNext();
		event.preventDefault();
	}
}


// upload next file
function uploadNext() {
	if (list.length) {
		document.getElementById('count').classList.add('spinning');
		var nextFile = list.shift();
		if (nextFile.size >= BTlang.maxFilesSize) {
			var respdiv = document.getElementById(nextFile.locId);
			respdiv.querySelector('.uploadstatus').textContent = 'File too big';
			respdiv.classList.remove('pending');
			respdiv.classList.add('failure');
			uploadNext();
		} else {
			var respdiv = document.getElementById(nextFile.locId);
			respdiv.querySelector('.uploadstatus').textContent = 'Uploading';

			// prepare XMLHttpRequest
			var xhr = new XMLHttpRequest();
			xhr.open('POST', 'ajax/files.ajax.php');

			// if request itself is OK
			xhr.onload = function() {
				var respdiv = document.getElementById(nextFile.locId);
				// need "try/catch/finally" because of "JSON.parse", that might return errors (but should not, since backend is clean)
				try {
					var resp = JSON.parse(this.responseText);
					respdiv.classList.remove('pending');

					if (resp !== null) {
						// renew token
						document.getElementById('token').value = resp.token;

						respdiv.querySelector('.uploadstatus').innerHTML = resp.status;
						respdiv.classList.add(resp.status);

						if (resp.status == 'success') {
							respdiv.querySelector('.filelink').href = resp.url;
							respdiv.querySelector('.uploadstatus').innerHTML = 'Uploaded';
							// replace file name with a link
							respdiv.querySelector('.filelink').innerHTML = respdiv.querySelector('.filename').innerHTML;
							respdiv.removeChild(respdiv.querySelector('.filename'));
						}
						/*
						else {
							respdiv.querySelector('.uploadstatus').innerHTML = 'Upload failed';
						}
						*/
						nbDone++;
						document.getElementById('count').innerHTML = +nbDone+'/'+nbDraged;
					} else {
						respdiv.classList.add('failure');
						respdiv.querySelector('.uploadstatus').innerHTML = 'PHP or Session error';
					}

				} catch(e) {
					console.log(e);
				} finally {
					uploadNext();
				}

			};

			// if request is failed, proeced to next file
			xhr.onerror = function() {
				uploadNext();
			};

			// prepare and send FormData
			var formData = new FormData();
			formData.append('token', document.getElementById('token').value);
			formData.append('do', 'upload');
			formData.append('upload', '1'); // ?

			formData.append('fichier', nextFile);
			formData.append('statut', ((document.getElementById('statut').checked === false) ? '' : 'on'));

			formData.append('description', document.getElementById('description').value);
			formData.append('nom_entree', document.getElementById('nom_entree').value);
			formData.append('dossier', document.getElementById('dossier').value);
			xhr.send(formData);

		}
	} else {
		document.getElementById('count').classList.remove('spinning');
		nbDraged = false;
		// reactivate the "required" attribute of file input
		document.getElementById('fichier').required = true;
	}
}



/* switches between the FILE upload, URL upload and Drag'n'Drop */
function switchUploadForm(where) {
	var link = document.getElementById('click-change-form');
	var input = document.getElementById('fichier');

	if (input.type == "file") {
		link.innerHTML = link.dataset.langFile;
		input.placeholder = "http://example.com/image.png";
		input.type = "url";
		input.focus();
	}
	else {
		link.innerHTML = link.dataset.langUrl;
		input.type = "file";
		input.placeholder = null;
	}
	return false;
}



/* Same as folder_sort(), but for filetypes (.doc, .xls, etc.) */
function type_sort(type, button) {
	// finds the matching files
	var files = document.querySelectorAll('#file-list tbody tr');
	for (var i=0, sz = files.length; i<sz; i++) {
		var file = files[i];
		if ((file.getAttribute('data-type') != null) && file.getAttribute('data-type').search(type) != -1) {
			file.style.display = '';
		} else {
			file.style.display = 'none';
		}
	}
	var buttons = document.getElementById('list-types').childNodes;
	for (var i = 0, nbbut = buttons.length ; i < nbbut ; i++) {
		if (buttons[i].nodeName=="BUTTON") buttons[i].className = '';
	}
	document.getElementById(button).className = 'current';
}




function imgListWall() {
	var _this = this;

	/***********************************
	** Some properties & misc actions
	*/
	// init JSON List
	this.imgList = JSON.parse(document.getElementById('json_images').textContent);
	if (typeof this.imgList == 'undefined' || !this.imgList.length) return;


	// get some DOM elements
	this.imgDomWall = document.getElementById('image-wall');

	// put som listeners
	// init the « folders sorting » buttons.
	this.showAllButton = document.getElementById('butIdAll');
	this.showAllButton.addEventListener('click', function(){ _this.albumSort(""); });
	this.showFolders = document.querySelectorAll('#list-albums > button:not([id])');
	for (var i = 0, len = this.showFolders.length ; i < len ; i++) {
		this.showFolders[i].addEventListener('click', function() { _this.albumSort(this); });
	}


	/***********************************
	** The HTML tree builder :
	** Rebuilts the whole list of thumbnails.
	*/
	this.rebuiltWall = function(imgList, limit) {
		// empties the actual list
		while (this.imgDomWall.firstChild) {
			 this.imgDomWall.removeChild(this.imgDomWall.firstChild);
		}

		if (0 === imgList.length) return false;

		// populates the new list
		for (var i = 0, len = imgList.length ; i < (Math.min(len, limit)) ; i++) {
			var item = imgList[i];

			var bloc = document.createElement('div');
			bloc.id = 'bloc_' + item.id;
			bloc.classList.add('image_bloc');
			bloc.addEventListener('click', function(e){ this.classList.toggle('show-buttons'); } );
			bloc.dataset.folder = item.folder;

			var imgThb = document.createElement('img');
			imgThb.src = item.thbPath;
			imgThb.alt = '#';
			imgThb.width = item.w;
			imgThb.height = item.h;
			bloc.appendChild(imgThb);


			var spanBtns = document.createElement('span');

			var btnShow = document.createElement('a')
			btnShow.classList.add('vignetteAction', 'imgShow');
			btnShow.href = item.absPath + item.fileName;
			spanBtns.appendChild(btnShow);

			var btnEdit = document.createElement('a')
			btnEdit.classList.add('vignetteAction', 'imgEdit');
			btnEdit.href = 'fichiers.php?file_id='+item.id;
			spanBtns.appendChild(btnEdit);

			var btnDL = document.createElement('a')
			btnDL.classList.add('vignetteAction', 'imgDL');
			btnDL.href = item.absPath + item.fileName;
			btnDL.download = item.fileName;
			spanBtns.appendChild(btnDL);

			var btnSuppr = document.createElement('button')
			btnSuppr.classList.add('vignetteAction', 'imgSuppr');
			btnSuppr.dataset.id = item.id;
			btnSuppr.addEventListener('click', function(e){ _this.deleteFile(this.dataset.id); } );

			spanBtns.appendChild(btnSuppr);

			bloc.appendChild(spanBtns);

			this.imgDomWall.appendChild(bloc);
		}
		return false;
	}
	// init the whole DOM list
	this.rebuiltWall(this.imgList, 25);



	/***********************************
	** Sors the images with respect to the folders,
	** then rebuilts the list of thumbnails.
	*/
	this.albumSort = function (button) {

		if (button != "") {
			var newList = new Array();
			for (var i = 0, len = this.imgList.length ; i < len ; i++) {
				if ((this.imgList[i].folder != 'null') && (this.imgList[i].folder).search(button.dataset.folder) != -1 ) { // if match
					newList.push(this.imgList[i]);
				}
			}
			this.rebuiltWall(newList, newList.length);
		}
		else {
			this.rebuiltWall(this.imgList, this.imgList.length);
		}
		for (var i = 0, len = this.showFolders.length ; i < len ; i++) {
			if (this.showFolders[i].nodeName=="BUTTON") this.showFolders[i].classList.remove('current');
		}

		if (button.classList) { button.classList.add('current'); }
	}


	/***********************************
	** Sends a "delete" request to server,
	*/
	this.deleteFile = function (id) {
		// ask for popup confirmation
		if (!window.confirm(BTlang.questionSupprFichier)) { return false; }
		// prepare XMLHttpRequest
		var xhr = new XMLHttpRequest();
		xhr.open('POST', 'ajax/files.ajax.php');
		xhr.onload = function() {
			if (this.responseText == 'success') {
				// remove image form page
				_this.imgDomWall.removeChild(document.getElementById('bloc_'.concat(id)));
			} else {
				alert(this.responseText+' '+id);
			}
		};
		// prepare and send FormData
		var formData = new FormData();
		formData.append('file_id', id);
		formData.append('do', 'delete');
		xhr.send(formData);
	}

}


function docListWall() {
	var _this = this;

	/***********************************
	** Some properties & misc actions
	*/
	// init JSON List
	this.docsList = JSON.parse(document.getElementById('json_docs').textContent);
	if (typeof this.docsList == 'undefined' || !this.docsList.length) return;


	// get some DOM elements
	this.docsDomTable = document.getElementById('file-list').getElementsByTagName('tbody')[0];

	// put som listeners
	// init the « folders sorting » buttons.
	this.showAllButton = document.getElementById('butIdAllFiles');
	this.showAllButton.addEventListener('click', function(){ _this.albumSort(""); }); // TODO
	this.showFolders = document.querySelectorAll('#list-types > button:not([id])');
	for (var i = 0, len = this.showFolders.length ; i < len ; i++) {
		this.showFolders[i].addEventListener('click', function() { _this.albumSort(this); });
	}



	/***********************************
	** The HTML tree builder :
	** Rebuilts the whole list of files.
	*/
	this.rebuiltTable = function(docsList) {

		// empties the actual list
		while (this.docsDomTable.firstChild) {
			 this.docsDomTable.removeChild(this.docsDomTable.firstChild);
		}

		if (0 === docsList.length) return false;

		// populates the new list
		for (var i = 0, len = docsList.length ; i < len ; i++) {
			var item = docsList[i];


			var row = document.createElement('tr');
			row.id = 'bloc_' + item.id;
			row.dataset.type = item.fileType;

			var cellIcon = document.createElement('td');
			var icon = document.createElement('img');
			icon.id = item.id;
			icon.alt = item.fileName;
			icon.src = 'style/imgs/filetypes/'+item.fileType+'.png';
			cellIcon.appendChild(icon);
			row.appendChild(cellIcon);

			var cellName = document.createElement('td');
			var fileLink = document.createElement('a');
			fileLink.appendChild(document.createTextNode(item.fileName));
			fileLink.href = '?file_id='+item.id+'&amp;edit';
			cellName.appendChild(fileLink);
			row.appendChild(cellName);

			var cellSize = document.createElement('td');
			cellSize.appendChild(document.createTextNode(item.fileSize));
			row.appendChild(cellSize);

			var cellDate = document.createElement('td');
			cellDate.appendChild(document.createTextNode(Date.dateFromYMDHIS(item.id).toLocaleString('fr', {year: "numeric", weekday: "short", month: "short", day: "numeric"})));
			row.appendChild(cellDate);

			var cellDwnd = document.createElement('td');
			var fileDL = document.createElement('a');
			fileDL.appendChild(document.createTextNode('DL'));
			fileDL.href = item.absPath + item.fileName;
			fileDL.download = item.fileName;
			cellDwnd.appendChild(fileDL);
			row.appendChild(cellDwnd);

			var cellSupr = document.createElement('td');
			var fileRM = document.createElement('a');
			fileRM.appendChild(document.createTextNode('DEL'));
			fileRM.href = '#';
			fileRM.dataset.id = item.id;
			fileRM.addEventListener('click', function(e){ _this.deleteFile(this.dataset.id); e.preventDefault(); } );
			cellSupr.appendChild(fileRM);
			row.appendChild(cellSupr);

			this.docsDomTable.appendChild(row);
		}
		return false;
	}
	// init the whole DOM table
	this.rebuiltTable(this.docsList);




	/***********************************
	** Sends a "delete" request to server,
	*/
	this.deleteFile = function (id) {
		// ask for popup confirmation
		if (!window.confirm(BTlang.questionSupprFichier)) { return false; }
		// prepare XMLHttpRequest
		var xhr = new XMLHttpRequest();
		xhr.open('POST', 'ajax/files.ajax.php');
		xhr.onload = function() {
			if (this.responseText == 'success') {
				// remove image form page
				_this.docsDomTable.removeChild(document.getElementById('bloc_'.concat(id)));
			} else {
				alert(this.responseText+' '+id);
			}
		};
		// prepare and send FormData
		var formData = new FormData();
		formData.append('file_id', id);
		formData.append('do', 'delete');
		xhr.send(formData);
		return false;
	}


}









/**************************************************************************************************************************************
	*********        ****          ****
	***********    ********      ********
	***     ***  ***      ***  ***      ***
 	***     ***  ***           ***
	**********    **********    **********
	********      **********    **********
	***  ***              ***           ***
	***   ***    ***      ***  ***      ***
	***    ***     ********      ********
	***     ***      ****          ****

	RSS PAGE HANDLING
**************************************************************************************************************************************/

// animation loading (also used in images wall/slideshow)
function loading_animation(onoff) {
	var notifNode = document.getElementById('counter');
	if (onoff == 'on') {
		notifNode.style.display = 'inline-block';
	}
	else {
		notifNode.style.display = 'none';
	}
	return false;
}

function RssReader() {
	var _this = this;

	/***********************************
	** Some properties & misc actions
	*/
	// init JSON List
	this.feedList = JSON.parse(document.getElementById('json_rss').textContent);

	// init local "mark as read" buffer
	this.readQueue = {"count": "0", "urlList": []};

	// get some DOM elements
	this.postsList = document.getElementById('post-list');
	this.feedsList = document.getElementById('feed-list');
	this.notifNode = document.getElementById('message-return');

	// init the « open-all » toogle-button.
	this.openAllButton = document.getElementById('openallitemsbutton');
	this.openAllButton.addEventListener('click', function(){ _this.openAll(); });

	// init the « hide feed-list » button
	document.getElementById('hide-side-nav').addEventListener('click', function(){ _this.hideFeedList(); });

	// init the « mark as read » button.
	document.getElementById('markasread').addEventListener('click', function(){ _this.markAsRead(); });

	// init the « refresh all » button event
	document.getElementById('refreshAll').addEventListener('click', function(e){ _this.refreshAllFeeds(e); });

	// init the « list all feeds » button
	document.getElementById('global-post-counter').addEventListener('click', function(){ _this.sortAll(); });

	// init the « list today posts » button
	document.getElementById('today-post-counter').addEventListener('click', function() { _this.sortToday(); });

	// init the « list favorits posts » button
	document.getElementById('favs-post-counter').addEventListener('click', function() { _this.sortFavs(); });

	// init the « delete old » button
	document.getElementById('deleteOld').addEventListener('click', function(){ _this.deleteOldFeeds(); });

	// init the « add new feed » button
	document.getElementById('fab').addEventListener('click', function(){ _this.addNewFeed(); });

	// add click events on Sites
	this.allSites = this.feedsList.querySelectorAll('.feed-site');
	for (var liSite of this.allSites) { liSite.addEventListener('click', function(e){ _this.sortItemsBySite(e); } )};

	// add click events on Folders
	this.allFolders = this.feedsList.querySelectorAll('.feed-folder');
	for (var liFolder of this.allFolders) { liFolder.addEventListener('click', function(e){ _this.sortItemsByFolder(e); } )};

	// add click events on "open-folder" button
	this.allUnfoldButton = this.feedsList.querySelectorAll('.feed-folder > .unfold');
	for (var button of this.allUnfoldButton) { button.addEventListener('click', function(e){ _this.openFolder(e);} )};


	// Global Page listeners
	// onkeydown : detect "open next/previous" action with keyboard
	window.addEventListener('keydown', function(e) { _this.kbActionHandle(e); } );

	// beforeunload : to send a "mark as read" request when closing the tab or reloading whole page
	window.addEventListener("beforeunload", function(e) { _this.markAsReadOnUnloadXHR(); } );

	var DateTimeFormat = new Intl.DateTimeFormat('fr', {weekday: "short", month: "short", day: "numeric", hour: "numeric", minute: "numeric"});

	var d = new Date();
	this.ymd000 = '' + d.getFullYear() + ('0' + (d.getMonth()+1)).slice(-2) + ('0' + d.getDate()).slice(-2) + '000000';


	this.postTemplate = this.postsList.firstElementChild.parentNode.removeChild(this.postsList.firstElementChild);
	this.postTemplate.removeAttribute('hidden');

	/***********************************
	** The HTML tree builder :
	** Rebuilts the whole list of posts.
	*/

	this.rebuiltTree = function(RssPosts) {
		// empties the actual list
		while (this.postsList.firstChild) {
			 this.postsList.removeChild(this.postsList.firstChild);
		}

		if (0 === RssPosts.length) return false;

		var liList = document.createDocumentFragment();
		var begin = Date.now();

		// populates the new list
		for (var i = 0, len = RssPosts.length ; i < len ; i++) {
			var item = RssPosts[i];
			var li = this.postTemplate.cloneNode(true);
			li.id = 'i_'+item.id;
			li.setAttribute('data-sitehash', item.feedhash);
			if (0 === item.statut) { li.classList.add('read'); }
			li.querySelector('.post-head > .lien-fav').setAttribute('data-is-fav', item.fav);
			li.querySelector('.post-head > .lien-fav').setAttribute('data-fav-id', item.id);
			li.querySelector('.post-head > .lien-fav').addEventListener('click', function(e){ _this.markAsFav(this); e.preventDefault(); } );
			li.querySelector('.post-head > .site').textContent = item.sitename;
			if (item.folder) { li.querySelector('.post-head > .folder').textContent = item.folder; }
			else { li.querySelector('.post-head').removeChild(li.querySelector('.folder')); }
			li.querySelector('.post-head > .post-title').href = item.link;
			li.querySelector('.post-head > .post-title').title = item.title;
			li.querySelector('.post-head > .post-title').setAttribute('data-id', li.id);
			li.querySelector('.post-head > .post-title').textContent = item.title;
			li.querySelector('.post-head > .post-title').addEventListener('click', function(e){ if(!_this.openThisItem(document.getElementById(this.dataset.id))) e.preventDefault(); } );
			li.querySelector('.post-head > .share > .lien-share').href = 'links.php?url='+encodeURIComponent(item.link);
			li.querySelector('.post-head > .share > .lien-open').href = item.link;
			li.querySelector('.post-head > .share > .lien-mail').href = 'mailto:?&subject='+ encodeURIComponent(item.title) + '&body=' + encodeURIComponent(item.link);
			li.querySelector('.post-head > .date').textContent = DateTimeFormat.format(Date.dateFromYMDHIS(item.datetime));
			li.querySelector('.rss-item-content').appendChild(document.createComment(item.content));

			liList.appendChild(li);

		}

		this.postsList.appendChild(liList);

		// displays the number of items (local counter)
		var count = document.querySelector('#post-counter');
		if (count.firstChild) {
			count.firstChild.nodeValue = RssPosts.length;
		} else {
			count.appendChild(document.createTextNode(RssPosts.length));
		}

		return false;
	}
	// init the whole DOM list
	this.rebuiltTree(this.feedList);



	/***********************************
	** Methods to "open" elements (all, one, next…)
	*/
	// open ALL the items
	this.openAll = function() {
		var posts = this.postsList.querySelectorAll('li');
		if (!this.openAllButton.classList.contains('unfold')) {
			for (var i=0, len=posts.length ; i<len ; i++) {
				posts[i].classList.add('open-post');
				var content = posts[i].querySelector('.rss-item-content');
				if (content.childNodes[0] && content.childNodes[0].nodeType == 8) {
					content.innerHTML = content.childNodes[0].data;
				}
			}
			this.openAllButton.classList.add('unfold');
		} else {
			for (var i=0, len=posts.length ; i<len ; i++) {
				posts[i].classList.remove('open-post');
			}
			this.openAllButton.classList.remove('unfold');
		}
		return false;
	}

	// open clicked item
	this.openThisItem = function(theItem) {
		if (theItem.classList.contains('open-post')) { return true; }
		// close open posts
		var posts = this.postsList.querySelectorAll('.open-post');
		for (var i=0, len=posts.length ; i<len ; i++) {
			posts[i].classList.remove('open-post');
		}
		this.openAllButton.classList.remove('unfold');
		// open this post
		theItem.classList.add('open-post');

		// unhide the content
		var content = theItem.querySelector('.rss-item-content');
		if (content.childNodes[0].nodeType == 8) {
			content.innerHTML = content.childNodes[0].data;
		}

		// jump to post (anchor + 120px)
		var rect = theItem.getBoundingClientRect();
		var isVisible = ( (rect.top < 144) || (rect.bottom > window.innerHeight) ) ? false : true ;
		if (!isVisible) {
			window.location.hash = theItem.id;
			window.scrollBy(0, -144);
		}

		// mark as read in DOM and saves for mark as read in DB
		if (!theItem.classList.contains('read')) {
			this.markAsReadPost(theItem);
			theItem.classList.add('read');
		}
		return false;
	}


	// handle keyboard actions
	this.kbActionHandle = function(e) {
		// first actual open item
		var openPost = this.postsList.querySelector('li.open-post');
		// ... or first post if list is empty
		if (!openPost) { openPost = this.postsList.querySelector('li'); var isFirst = true; }
		// ... or return if no post in list
		if (!openPost) return false;

		// down
		if (e.keyCode == '40' && e.ctrlKey && openPost.nextSibling) {
			if (isFirst)
				this.openThisItem(openPost);
			else
				this.openThisItem(openPost.nextSibling);
			e.preventDefault();
		}
		// up
		if (e.keyCode == '38' && e.ctrlKey && openPost.previousSibling) {
			this.openThisItem(openPost.previousSibling);
			e.preventDefault();
		}
	}

	// open Folder
	this.openFolder = function(e) {
		e.stopPropagation();
		e.target.parentNode.classList.toggle('open');
	}


	this.hideFeedList = function() {
		this.feedsList.classList.toggle('hidden-list');
	}

	/***********************************
	** Methods to "sort" elements (by site, folder, favs…)
	*/
	// create list of items matching the selected site
	this.sortItemsBySite = function(e) {
		e.stopPropagation();
		var theSite = e.target.getAttribute('data-feed-hash');
		var newList = new Array();
		for (var i = 0, len = this.feedList.length ; i < len ; i++) {
			if (this.feedList[i].feedhash == theSite) { // if match
				newList.push(this.feedList[i]);
			}
		}
		// unhighlight previously highlighted site
		if (document.querySelector('.active-site')) { document.querySelector('.active-site').classList.remove('active-site'); }
		// and highlight new site
		document.querySelector('#feed-list li[data-feed-hash="'+theSite+'"]').classList.add('active-site');
		window.location.hash = '';
		this.rebuiltTree(newList);
		this.openAllButton.classList.remove('unfold');
	}

	// create list of items matching the selected folder
	this.sortItemsByFolder = function(e) {
		e.stopPropagation();
		var theFolder = e.target.getAttribute('data-folder');
		var newList = new Array();
		for (var i = 0, len = this.feedList.length ; i < len ; i++) {
			if (this.feedList[i].folder == theFolder) {
				newList.push(this.feedList[i]);
			}
		}
		// unhighlight previously highlighted site
		if (document.querySelector('.active-site')) { document.querySelector('.active-site').classList.remove('active-site'); }
		// highlight selected folder
		this.feedsList.querySelector('li[data-folder="'+theFolder+'"]').classList.add('active-site');
		window.location.hash = '';
		this.rebuiltTree(newList);
		this.openAllButton.classList.remove('unfold');
	}

	// rebuilt the list with all the items
	this.sortAll = function() {
		// unhighlight previously selected site
		document.querySelector('.active-site').classList.remove('active-site');
		// highlight "all" button.
		document.querySelector('.all-feeds').classList.add('active-site');

		window.location.hash = '';
		this.rebuiltTree(this.feedList);
		this.openAllButton.classList.remove('unfold');
		return false;
	}

	// Create list with the favs
	this.sortFavs = function() {
		var newList = new Array();
		// create list of items that are favs
		for (var i = 0, len = this.feedList.length ; i < len ; i++) {
			if (this.feedList[i].fav == 1) {
				newList.push(this.feedList[i]);
			}
		}
		// unhighlight previously selected site
		if (document.querySelector('.active-site')) { document.querySelector('.active-site').classList.remove('active-site'); }
		// highlight favs
		document.querySelector('.fav-feeds').classList.add('active-site');
		window.location.hash = '';
		this.rebuiltTree(newList);
		this.openAllButton.classList.remove('unfold');
		return false;
	}

	// Create list with today's posts
	this.sortToday = function() {
		var newList = new Array();
		// create list of items that have been posted today

		for (var i = 0, len = this.feedList.length ; i < len ; i++) {
			if (this.feedList[i].datetime >= this.ymd000) {
				newList.push(this.feedList[i]);
			}
		}
		// unhighlight previously selected site
		if (document.querySelector('.active-site')) { document.querySelector('.active-site').classList.remove('active-site'); }
		// highlight favs
		document.querySelector('.today-feeds').classList.add('active-site');
		window.location.hash = '';
		this.rebuiltTree(newList);
		this.openAllButton.classList.remove('unfold');
		return false;
	}


	/***********************************
	** Methods to "mark as read" item in the local list and on screen
	*/
	this.markAsRead = function() {
		var markWhat = document.querySelector('.active-site');

		// Mark ALL as read.
		if (markWhat.classList.contains('all-feeds')) {
			// ask confirmation
			if (!confirm("Tous les éléments seront marqués comme lus ?")) {
				loading_animation('off');
				return false;
			}
			// send XHR
			if (!this.markAsReadXHR('all', 'all')) return false;

			// mark items as read in list
			for (var i = 0, len = this.feedList.length ; i < len ; i++) { this.feedList[i].statut = 0; }

			this.sortAll();
		}

		// Mark one FOLDER as read
		else if (markWhat.classList.contains('feed-folder')) {
			var folder = markWhat.dataset.folder;

			// send XHR
			if (!this.markAsReadXHR('folder', folder)) return false;

			// update GLOBAL counter by substracting unread items from the folder

			// mark 0 for that folder
			markWhat.dataset.nbrun = 0;

			// mark 0 for the sites in that folder
			var sitesInFolder = this.feedsList.querySelectorAll('li[data-feed-folder="' + folder + '"]');
			for (var i = 0, len = sitesInFolder.length ; i < len ; i++) {
				sitesInFolder[i].dataset.nbrun = 0;
			}

			// mark items as "read" in list
			for (var i = 0, len = this.feedList.length ; i < len ; i++) {
				if (this.feedList[i].folder == folder) {
					this.feedList[i].statut = 0;
				}
			}

			// mark items as "read" on screen
			for (var node of this.postsList.querySelectorAll('#post-list > li')) {
				node.classList.add('read');
			}

		}

		// else… mark one SITE as read
		else if (markWhat.classList.contains('feed-site')) {
			var siteHash = markWhat.dataset.feedHash;

			// send XHR
			if (!this.markAsReadXHR('site', siteHash)) return false;

			// if site is in a folder, update amount of unread for that folder too
			var parentFolder = markWhat.parentNode.parentNode;
			if (parentFolder.dataset.folder) {
				parentFolder.dataset.nbrun -= markWhat.dataset.nbrun;
			}

			// mark items as "read" in list
			for (var i = 0, len = this.feedList.length ; i < len ; i++) {
				if (this.feedList[i].feedhash == siteHash) {
					this.feedList[i].statut = 0;
				}
			}
			// mark items as "read" on screen
			for (var node of this.postsList.querySelectorAll('#post-list > li')) {
				node.classList.add('read');
			}

			// mark 0 for that folder folder’s unread counters
			markWhat.dataset.nbrun = markWhat.dataset.nbtoday = 0;

		}
	}

	// This is called when a post is opened (for the first time)
	// counters are updated here
	this.markAsReadPost = function(thePost) {
		// add thePost to local read posts buffer, to be send as XHR when full
		this.readQueue.urlList.push(thePost.id.substr(2));
		// if 10 items in queue, send XHR request and reset list to zero.
		if (this.readQueue.urlList.length >= 10) {
			var list = this.readQueue.urlList;
			this.markAsReadXHR('postlist', JSON.stringify(list));
			this.readQueue.urlList = [];
		}

		// mark a read in list
		for (var i = 0, len = this.feedList.length ; i < len ; i++) {
			if (this.feedList[i].id == thePost.id.substr(2)) {
				this.feedList[i].statut = 0;
				break;
			}
		}
		// decrement global counter
		// var gcount = document.getElementById('global-post-counter');
		// gcount.dataset.nbrun -= 1;
		// decrement site & site.today counter
		var site = this.feedsList.querySelector('li[data-feed-hash="'+thePost.dataset.sitehash+'"]');
		site.dataset.nbrun -= 1;

		// decrement folder (if any)
		var parentFolder = site.parentNode.parentNode;
		if (parentFolder.dataset.folder) {
			parentFolder.dataset.nbrun -= 1;
		}
	}

	/***********************************
	** Methods to init and send the XHR request
	*/
	// Mark as read by user input.
	this.markAsReadXHR = function(marType, marWhat) {
		loading_animation('on');

		var notifDiv = document.createElement('div');

		var xhr = new XMLHttpRequest();
		xhr.open('POST', 'ajax/rss.ajax.php', true);

		// onload
		xhr.onload = function() {
			var resp = this.responseText;
			loading_animation('off');
			return (resp.indexOf("Success") == 0);
		};

		// onerror
		xhr.onerror = function(e) {
			loading_animation('off');
			notifDiv.appendChild(document.createTextNode('AJAX Error ' +e.target.status));
			notifDiv.classList.add('no_confirmation');
			document.getElementById('top').appendChild(notifDiv);
			notfiNode.appendChild(document.createTextNode(resp));
		};

		// prepare and send FormData
		var formData = new FormData();
		formData.append('token', token);
		formData.append('mark-as-read', marType);
		formData.append('mark-as-read-data', marWhat);
		xhr.send(formData);

		return true;
	}

	// mark as read on page-unload (transparent for user)
	this.markAsReadOnUnloadXHR = function() {
		if (this.readQueue.urlList.length == 0) return true;

		var xhr = new XMLHttpRequest();
		xhr.open('POST', 'ajax/rss.ajax.php', false);

		// onload
		xhr.onload = function() {
			var resp = this.responseText;
			return (resp.indexOf("Success") == 0);
		};

		// prepare and send FormData
		var formData = new FormData();
		formData.append('token', token);
		formData.append('mark-as-read', 'postlist');
		formData.append('mark-as-read-data', JSON.stringify(this.readQueue.urlList));
		xhr.send(formData);
		return true;
	}

	/***********************************
	** Methods to mark a post a favorite
	*/
	this.markAsFav = function(thePost) {

		// mark as fav on screen and in favCounter
		thePost.dataset.isFav = 1 - parseInt(thePost.dataset.isFav);
		//var favCounter = document.getElementById('favs-post-counter')
		//favCounter.dataset.nbrun = parseInt(favCounter.dataset.nbrun) + ((thePost.dataset.isFav == 1) ? 1 : -1 );
		//favCounter.firstChild.nodeValue = '('+favCounter.dataset.nbrun+')';

		// mark as fav in local list
		for (var i = 0, len = this.feedList.length ; i < len ; i++) {
			if (this.feedList[i].id == thePost.dataset.favId) {
				this.feedList[i].fav = thePost.dataset.isFav;
				break;
			}
		}

		// mark as fav in DB (with XHR)
		loading_animation('on');

		var notifDiv = document.createElement('div');

		var xhr = new XMLHttpRequest();
		xhr.open('POST', 'ajax/rss.ajax.php', true);

		// onload
		xhr.onload = function() {
			var resp = this.responseText;
			loading_animation('off');
			return (resp.indexOf("Success") == 0);
		};

		// onerror
		xhr.onerror = function(e) {
			var resp = this.responseText;
			loading_animation('off');
			notifDiv.appendChild(document.createTextNode('AJAX Error ' +e.target.status));
			notifDiv.classList.add('no_confirmation');
			document.getElementById('top').appendChild(notifDiv);
			notfiNode.appendChild(document.createTextNode(resp));
			return false;
		};

		// prepare and send FormData
		var formData = new FormData();
		formData.append('token', token);
		formData.append('mark-as-fav', 1);
		formData.append('url', thePost.dataset.favId);
		xhr.send(formData);
		return false;
	}



	/***********************************
	** Methods to refresh the feeds
	** This call is long, also it updates gradually on screen.
	**
	*/
	this.refreshAllFeeds = function(e) {
		var _refreshButton = e.target;
		// if refresh ongoing : abbord !
		if (_refreshButton.dataset.refreshOngoing == 1) {
			return false;
		} else {
			_refreshButton.dataset.refreshOngoing = 1;
		}
		// else refresh
		loading_animation('on');

		// prepare XMLHttpRequest
		var xhr = new XMLHttpRequest();
		xhr.open('POST', 'ajax/rss.ajax.php', true);

		// Counts the feeds that have been updated already and displays it like « 10/42 feeds »
		var glLength = 0;
		_this.notifNode.appendChild(document.createTextNode(''));
		xhr.onprogress = function() {
			if (glLength != this.responseText.length) {
				var posSpace = (this.responseText.substr(0, this.responseText.length-1)).lastIndexOf(" ");
				_this.notifNode.firstChild.nodeValue = this.responseText.substr(posSpace);
				glLength = this.responseText.length;
			}
		}
		// when finished : displays amount of items gotten.
		xhr.onload = function() {
			var resp = this.responseText;

			// grep new feeds
			var newFeeds = JSON.parse(resp.substr(resp.indexOf("Success")+7));

			// update status
			_this.notifNode.firstChild.nodeValue = newFeeds.length+' new feeds'; // TODO $[lang]

			// in not empty, add them to list & display them
			if (0 != newFeeds.length) {
				_this.rebuiltTree(newFeeds);
				for (var i = 0, len = newFeeds.length ; i < len ; i++) {
					_this.feedList.unshift(newFeeds[i]); // TODO : recount elements (site, folder, total)
				}

			}

			_refreshButton.dataset.refreshOngoing = 0;
			loading_animation('off');
			return false;
		};

		xhr.onerror = function() {
			_this.notifNode.appendChild(document.createTextNode(this.responseText));
			loading_animation('off');
			_refreshButton.dataset.refreshOngoing = 0;
		};

		// prepare and send FormData
		var formData = new FormData();
		formData.append('token', token);
		formData.append('refresh_all', 1);
		xhr.send(formData);
		return false;
	}


	/***********************************
	** Method to delete old feeds from DB
	*/
	this.deleteOldFeeds = function() {
		// ask confirmation
		if (!confirm("Les vieilles entrées seront supprimées ?")) {
			loading_animation('off');
			return false;
		}

		loading_animation('on');
		var notifDiv = document.createElement('div');

		var xhr = new XMLHttpRequest();
		xhr.open('POST', 'ajax/rss.ajax.php', true);

		xhr.onload = function() {
			var resp = this.responseText;
			if (resp.indexOf("Success") == 0) {
				// adding notif
				notifDiv.textContent = BTlang.confirmFeedClean;
				notifDiv.classList.add('confirmation');
			} else {
				notifDiv.textContent = 'Error: '+resp;
				notifDiv.classList.add('no_confirmation');
			}
			document.getElementById('top').appendChild(notifDiv);
			loading_animation('off');
		};
		xhr.onerror = function(e) {
			loading_animation('off');
			// adding notif
			notifDiv.textContent = BTlang.errorPhpAjax + e.target.status;
			notifDiv.classList.add('no_confirmation');
			document.getElementById('top').appendChild(notifDiv);
		};

		// prepare and send FormData
		var formData = new FormData();
		formData.append('token', token);
		formData.append('delete_old', 1);
		xhr.send(formData);
		return false;
	}



	/***********************************
	** Method to add a new feed (promt for URL and send to server)
	*/
	this.addNewFeed = function() {
		var newLink = window.prompt(BTlang.rssJsAlertNewLink, '');
		// if empty string : stops here
		if (!newLink) return false;
		// ask folder
		var newFolder = window.prompt(BTlang.rssJsAlertNewLinkFolder, '');

		var notifDiv = document.createElement('div');
		loading_animation('on');

		var xhr = new XMLHttpRequest();
		xhr.open('POST', 'ajax/rss.ajax.php');

		xhr.onload = function() {
			var resp = this.responseText;
			// if error : stops
			if (resp.indexOf("Success") == -1) {
				loading_animation('off');
				_this.notifNode.appendChild(document.createTextNode(this.responseText));
				return false;
			}

			// recharge la page en cas de succès
			loading_animation('off');
			_this.notifNode.appendChild(document.createTextNode('FLux ajouté, rechargez la page.'));
			return false;
		};

		xhr.onerror = function(e) {
			loading_animation('off');
			// adding notif
			notifDiv.textContent = 'Une erreur PHP/Ajax s’est produite :'+e.target.status;
			notifDiv.classList.add('no_confirmation');
			document.getElementById('top').appendChild(notifDiv);
		};
		// prepare and send FormData
		var formData = new FormData();
		formData.append('token', token);
		formData.append('add-feed', newLink);
		formData.append('add-feed-folder', newFolder);
		xhr.send(formData);
		return false;
	}

};

function RssConfig() {
	var _this = this;

	// hasUpdated flag
	this.hasUpdated = false;

	// the table with the feeds-info
	this.feedTable = document.getElementById('rss-feed').tBodies[0];

	// Global Page listeners
	// beforeunload : warns the user if some data is not saved
	window.addEventListener("beforeunload", function(e) {
		if (_this.hasUpdated) {
			var confirmationMessage = BTlang.questionQuitPage;
			(e || window.event).returnValue = confirmationMessage;	//Gecko + IE
			return confirmationMessage;								// Webkit : ignore this.
		}
		else { return true; }
	});

	// Save button
	document.getElementById('enregistrer').addEventListener('click', function() { _this.saveFeedsXHR(); } );

	// add events for edition & deletion
	this.feedTableTR = this.feedTable.querySelectorAll('tr');
	for (var i = 0, len = this.feedTableTR.length; i < len; i++) {
		var tr = this.feedTableTR[i];

		// "input" / edit event on row
		tr.addEventListener('input', function(e) {
			this.classList.add('edited');
			_this.raiseUpdateFlag(true);
		}, {once: true});

		// "delete" event on button
		tr.querySelector('td.suppr > button').addEventListener('click', function(e) {
			if (!window.confirm(BTlang.questionSupprFlux)) { return false; }
			this.parentNode.parentNode.classList.add('deleted');
			_this.raiseUpdateFlag(true);
		});
	}





	/**************************************
	 * AJAX call to save changes to DB
	*/
	this.saveFeedsXHR = function() {
		loading_animation('on');
		// only keep modified notes
		var toSaveFeeds = Array();
		for (var i=0, len=this.feedTableTR.length; i<len ; i++) {

			if (this.feedTableTR[i].classList.contains('edited') || this.feedTableTR[i].classList.contains('deleted')) {

				// mark for removal
				if (this.feedTableTR[i].classList.contains('deleted')) {
					var feedObj = {
						id: this.feedTableTR[i].getAttribute('data-feed-hash'),
						action: 'delete'
					};
				}
				// mark for edit
				else {
					var feedObj = {
						id: this.feedTableTR[i].getAttribute('data-feed-hash'),
						action: 'edited',
						title: this.feedTableTR[i].querySelector('.title').textContent,
						link: this.feedTableTR[i].querySelector('.link').textContent,
						folder: this.feedTableTR[i].querySelector('.folder').textContent
					};
				}
			
				toSaveFeeds.push(feedObj);

			}
		}

		// make a string out of it
		var feedsDataText = JSON.stringify(toSaveFeeds);

		var notifDiv = document.createElement('div');
		// create XHR
		var xhr = new XMLHttpRequest();
		xhr.open('POST', 'ajax/rss.ajax.php', true);

		// onload
		xhr.onload = function() {
			if (this.responseText.indexOf("Success") == 0) {
				loading_animation('off');
				_this.raiseUpdateFlag(false);
				// adding notif
				notifDiv.textContent = BTlang.confirmFeedSaved;
				notifDiv.classList.add('confirmation');
				document.getElementById('top').appendChild(notifDiv);

				// reset flags on tableTR

				for (var i=0, len=_this.feedTableTR.length; i<len ; i++) {
					var tr = _this.feedTableTR[i];
					// mark for removal
					if (tr.classList.contains('deleted')) {
						tr.parentNode.removeChild(tr);
					}
					// mark for edit
					if (tr.classList.contains('edited')) {
						tr.classList.remove('edited')
					}
				
				}
				return true;
			} else {
				loading_animation('off');
				// adding notif
				notifDiv.textContent = this.responseText;
				notifDiv.classList.add('no_confirmation');
				document.getElementById('top').appendChild(notifDiv);
				return false;
			}
		};

		// onerror
		xhr.onerror = function(e) {
			loading_animation('off');
			// adding notif
			notifDiv.textContent = 'AJAX Error ' +e.target.status;
			notifDiv.classList.add('no_confirmation');
			document.getElementById('top').appendChild(notifDiv);
			_this.notifNode.appendChild(document.createTextNode(this.responseText));
		};

		// prepare and send FormData
		var formData = new FormData();
		formData.append('token', token);
		formData.append('edit-feed-list', feedsDataText);
		xhr.send(formData);
	}





	this.raiseUpdateFlag = function(flagRaised) {
		if (flagRaised) {
			this.hasUpdated = true;
			document.getElementById('enregistrer').disabled = false;
		} else {
			this.hasUpdated = false;
			document.getElementById('enregistrer').disabled = true;
		}
	}


}


/**************************************************************************************************************************************
	CANVAS FOR index.php GRAPHS
**************************************************************************************************************************************/
function respondCanvas(){
	for (var i=0, len=containers.length; i<len ; i++) {
		containers[i].querySelector('canvas').width = parseInt(containers[i].querySelector('.graphique').getBoundingClientRect().width);
		draw(containers[i]);
	}
}

function draw(container) {
	var c = container.querySelector('canvas');
	var months = container.querySelectorAll('.graphique .month');
	var ctx = c.getContext("2d");
	var cont = {
		x:container.getBoundingClientRect().left,
		y:container.getBoundingClientRect().top
	};

	// strokes the background lines at 0%, 25%, 50%, 75% and 100%.
	ctx.beginPath();
	for (var i=months.length-1 ; i>=0 ; i--) {
		if (months[i].getBoundingClientRect().top < months[0].getBoundingClientRect().bottom) {
			var topLeft = months[i].getBoundingClientRect().left -15;
			break;
		}
	}

	var coordScale = { x:topLeft, xx:months[1].getBoundingClientRect().left };
	for (var i = 0; i < 5 ; i++) {
		ctx.moveTo(coordScale.x, i*c.height/4 +1);
		ctx.lineTo(coordScale.xx, i*c.height/4 +1);
		ctx.strokeStyle = "rgba(0, 0, 0, .05)";
	}
	ctx.stroke();

	// strokes the lines of the chart
	ctx.beginPath();
	for (var i=1, len=months.length ; i<len ; i++) {
		var coordsNew = months[i].getBoundingClientRect();
		if (i == 1) {
			ctx.moveTo(coordsNew.left - cont.x + coordsNew.width/2, coordsNew.top - cont.y);
		} else {
			if (coordsNew.top - cont.y <= 150)
			ctx.lineTo(coordsNew.left - cont.x + coordsNew.width/2, coordsNew.top - cont.y);
		}
	}
	ctx.lineWidth = 2;
	ctx.strokeStyle = "rgba(33,150,243,1)";
	ctx.stroke();
	ctx.closePath();

	// fills the chart
	ctx.beginPath();
	for (var i=1, len=months.length ; i<len ; i++) {
		var coordsNew = months[i].getBoundingClientRect();
		if (i == 1) {
			ctx.moveTo(coordsNew.left - cont.x + coordsNew.width/2, 150);
			ctx.lineTo(coordsNew.left - cont.x + coordsNew.width/2, coordsNew.top - cont.y);
		} else {
			if (coordsNew.top - cont.y <= 150) {
				ctx.lineTo(coordsNew.left - cont.x + coordsNew.width/2, coordsNew.top - cont.y);
				var coordsOld = coordsNew;
			}
		}
	}
	ctx.lineTo(coordsOld.left - cont.x + coordsOld.width/2, 150);
	ctx.fillStyle = "rgba(33,150,243,.2)";
	ctx.fill();
	ctx.closePath();
}








/**************************************************************************************************************************************
	***     ***   ************   ***********    *********      ****     
	****    ***   ************   ***********    *********    ********   
	*****   ***   ***      ***       ***        ***        ***      *** 
	******  ***   ***      ***       ***        *******    ***          
	*** *** ***   ***      ***       ***        *******     **********  
	***  ******   ***      ***       ***        ***         **********  
	***   *****   ***      ***       ***        ***                 *** 
	***    ****   ***      ***       ***        ***        ***      *** 
	***     ***   ************       ***        **********   ********   
	***     ***   ************       ***        **********     ****     
	NOTES MANAGEMENT
**************************************************************************************************************************************/
function NoteBlock() {
	var _this = this;

	/***********************************
	** Some properties & misc actions
	*/
	// init JSON List
	this.notesList = JSON.parse(document.getElementById('json_notes').textContent);
	// init to "false" a flag aimed to determine if changed have yet to be saved to server
	this.hasUpdated = false;

	// get some DOM elements
	this.noteContainer = document.getElementById('list-notes');
	this.domPage = document.getElementById('page');
	this.notifNode = document.getElementById('message-return');

	// get note template
	this.noteTemplate = this.noteContainer.firstElementChild.parentNode.removeChild(this.noteContainer.firstElementChild);
	this.noteTemplate.removeAttribute('hidden');

	this.notePopupTemplate = document.getElementById('popup-wrapper').parentNode.removeChild(document.getElementById('popup-wrapper'));
	this.notePopupTemplate.removeAttribute('hidden');

	document.getElementById('post-new-note').addEventListener('click', function(e) { _this.addNewNote(); });

	// Global Page listeners
	// beforeunload : warns the user if some data is not saved
	window.addEventListener("beforeunload", function(e) {
			if (_this.hasUpdated) {
				var confirmationMessage = BTlang.questionQuitPage;
				(e || window.event).returnValue = confirmationMessage;	//Gecko + IE
				return confirmationMessage;										// Webkit : ignore this.
			}
			else { return true; }
		});

	// Save button
	document.getElementById('enregistrer').addEventListener('click', function() { _this.saveNotesXHR(); } );

	/***********************************
	** The HTML tree builder :
	** Builts the whole list of noteq.
	*/
	this.rebuiltNotesWall = function(NotesData) {
		if (0 === NotesData.length) return false;

		var notesPinned = document.createDocumentFragment();
		var notesUnPinned = document.createDocumentFragment();

		// "pinnedNotes" <h2>
		var pinnedTitle = document.getElementById('are-pinned');

		// populates the new list
		for (var i = 0, len = NotesData.length ; i < len ; i++) {
			var item = NotesData[i];

			var div = this.noteTemplate.cloneNode(true);
			div.id = 'i_' + item.id;
			div.dataset.updateAction = item.action;
			div.dataset.ispinned = item.ispinned;
			div.dataset.isarchived = item.isstatut;
			div.style.backgroundColor = item.color;
			div.dataset.indexId = i;
			div.querySelector('.title > h2').textContent = item.title;
			div.querySelector('.content').textContent = item.content;
			div.addEventListener('click', function(e) {
				_this.showNotePopup(NotesData[this.dataset.indexId]);
			});

			if (item.ispinned == 1) {
				notesPinned.appendChild(div);
			} else {
				notesUnPinned.appendChild(div);
			}

		}

		// add to page
		if (0 !== notesUnPinned.children.length) {
			this.noteContainer.append(notesUnPinned);
		}
		if (0 !== notesPinned.children.length) {
			pinnedTitle.removeAttribute('hidden');
			this.noteContainer.insertBefore(notesPinned, pinnedTitle.nextSibling);
		}

		return false;
	}
	// init the whole DOM list
	this.rebuiltNotesWall(this.notesList);

	/**************************************
	 * Init a new note, and add it to page
	*/
	this.addNewNote = function() {
		var date = new Date();
		var newNote = {
			"id": date.toISOString().substr(0,19).replace(/[:T-]/g, ''),
			"title": BTlang.notesLabelTitle,
			"content": '',
			"color": '#ffffff',
			"ispinned": '0',
			"isstatut": '1',
			"action": 'newNote',
		};

		this.showNotePopup(newNote);
	}


	this.showNotePopup = function(item) {
		if (document.getElementById('i_' + item.id) ) {
			var noteNode = document.getElementById('i_' + item.id);
			noteNode.style.opacity = 0;
		}

		var popupWrapper = this.notePopupTemplate.cloneNode(true);
		document.body.classList.add('noscroll');

		popupWrapper.addEventListener('click', function(e) {
			// clic is outside popup: closes popup
			if (e.target == this) {
				popupWrapper.parentNode.removeChild(popupWrapper);
				document.body.classList.remove('noscroll');
				if (noteNode) noteNode.style.opacity = null;
			}
		} );

		popupWrapper.querySelector('#popup').style.backgroundColor = item.color;
		popupWrapper.querySelector('#popup').dataset.ispinned = item.ispinned;
		popupWrapper.querySelector('#popup').dataset.isarchived = item.isstatut;
		popupWrapper.querySelector('#popup > .popup-title > h2').textContent = item.title;
		popupWrapper.querySelector('#popup > .popup-title > .pinnedIcon').addEventListener('click', function(e) {
			popupWrapper.querySelector('#popup').dataset.ispinned = Math.abs(popupWrapper.querySelector('#popup').dataset.ispinned -1);
		});
		popupWrapper.querySelector('#popup > .popup-title > .archiveIcon').addEventListener('click', function(e) {
			popupWrapper.querySelector('#popup').dataset.isarchived = Math.abs(popupWrapper.querySelector('#popup').dataset.isarchived -1);
		});
		popupWrapper.querySelector('#popup > .popup-content').value = item.content;
		popupWrapper.querySelector('#popup > .popup-footer > .date').textContent = Date.dateFromYMDHIS(item.id).toLocaleDateString('fr', {weekday: "long", month: "long", year: "numeric", day: "numeric"});
		popupWrapper.querySelector('#popup > .popup-footer > .colors').addEventListener('click', function(e) {
			if (e.target.tagName == 'LI') _this.changeColor(item, e);
		});
		popupWrapper.querySelector('#popup > .popup-footer > .supprIcon').addEventListener('click', function(e) {
			_this.markAsDeleted(item);
		});
		popupWrapper.querySelector('#popup > .popup-footer > .submit-bttns > .button-cancel').addEventListener('click', function(e) {
			popupWrapper.parentNode.removeChild(popupWrapper);
			document.body.classList.remove('noscroll');
			if (noteNode) noteNode.style.opacity = null;
		});
		popupWrapper.querySelector('#popup > .popup-footer > .submit-bttns > .button-submit').addEventListener('click', function(e) {
			_this.markAsEdited(item);
			popupWrapper.parentNode.removeChild(popupWrapper);
			document.body.classList.remove('noscroll');
			if (noteNode) noteNode.style.opacity = null;
		});

		// add to page
		this.domPage.appendChild(popupWrapper);

		popupWrapper.querySelector('#popup > .popup-content').focus();
	}


	/**************************************
	 * Mark a note as having been edited
	*/
	this.markAsEdited = function(item) {
		var popup = document.getElementById('popup');
		// is Edit ?
		// search item in notesList.
		var isEdit = false;
		for (var i = 0, len = this.notesList.length ; i < len ; i++) {
			if (item.id == this.notesList[i].id) {
				var isEdit = true;
				break;
			}
		}

		item.content = popup.querySelector('.popup-content').value;
		item.title = popup.querySelector('.popup-title > h2').textContent;
		item.color = window.getComputedStyle(popup).backgroundColor;
		item.ispinned = popup.dataset.ispinned;

		// if not has been archvied
		if (popup.dataset.isarchived == 0 && popup.dataset.isarchived != item.isstatut && document.querySelector('select[name="filtre"]').value != 'archived') {
			var theNote = document.getElementById('i_'+item.id);
			theNote.classList.add('deleteFadeOutH');
			theNote.addEventListener('animationend', function(event){event.target.parentNode.removeChild(event.target);}, false);
		}

		item.isstatut = popup.dataset.isarchived;

		// note is new:
		if (!isEdit) {
			this.rebuiltNotesWall([item]); // append it to #notes-list
			this.notesList.push(item);     // append it to the main List
		}

		// note is only edited
		else {
			var theNote = document.getElementById('i_'+item.id);
			theNote.style.backgroundColor = item.color;
			theNote.querySelector('.content').textContent = item.content;
			theNote.querySelector('h2').textContent = item.title;
			var oldPinnedState = theNote.dataset.ispinned;
			theNote.dataset.ispinned = item.ispinned;

			// if pined/unpinned : move note in proper section
			if (oldPinnedState != theNote.dataset.ispinned) {
				var pinnedTitle = document.getElementById('are-pinned');
				if (item.ispinned == 1) {
					this.noteContainer.insertBefore(theNote, pinnedTitle.nextSibling);
				}
				else {
					this.noteContainer.appendChild(theNote);
				}
				// if no pinned : hide <h2> (this is not yet possible in CSS only…)
				if (pinnedTitle.nextElementSibling.tagName === 'H2') {
					pinnedTitle.setAttribute('hidden', '');
				} else {
					pinnedTitle.removeAttribute('hidden');
				}
			}
		}
		item.action = item.action || 'updateNote';

		// raises global "updated" flag.
		this.raiseUpdateFlag(true);
	}


	/**************************************
	 * Mark a note as having been deleted
	*/
	this.markAsDeleted = function(item) {
		if (!window.confirm(BTlang.questionSupprNote)) { return false; }
		// mark as removed
		item.action = 'deleteNote';
		// close popup
		document.getElementById('popup-wrapper').parentNode.removeChild(document.getElementById('popup-wrapper'));
		// remove item from page too, with a little animation
		var theNote = document.getElementById('i_'+item.id);
		theNote.classList.add('deleteFadeOutH');
		theNote.addEventListener('animationend', function(event){event.target.parentNode.removeChild(event.target);}, false);
		
		// raises global "updated" flag.
		this.raiseUpdateFlag(true);
	}


	/**************************************
	 * Change the color of a note
	*/
	this.changeColor = function(item, e) {
		var newColor = window.getComputedStyle(e.target).backgroundColor;
		document.getElementById('popup').style.backgroundColor = newColor;
		e.preventDefault();
	}


	/**************************************
	 * Each change triggers a flag. If (flag), the save button displays
	*/
	this.raiseUpdateFlag = function(flagRaised) {
		if (flagRaised) {
			this.hasUpdated = true;
			document.getElementById('enregistrer').disabled = false;
		} else {
			this.hasUpdated = false;
			document.getElementById('enregistrer').disabled = true;
		}
	}


	/**************************************
	 * AJAX call to save notes to DB
	*/
	this.saveNotesXHR = function() {
		loading_animation('on');
		// only keep modified notes
		var toSaveNotes = Array();
		for (var i=0, len=this.notesList.length; i<len ; i++) {
			if (this.notesList[i].action && 0 !== this.notesList[i].action.length) {
				toSaveNotes.push(this.notesList[i]);
			}
		}

		// make a string out of it
		var notesDataText = JSON.stringify(toSaveNotes);

		var notifDiv = document.createElement('div');
		// create XHR
		var xhr = new XMLHttpRequest();
		xhr.open('POST', 'ajax/notes.ajax.php', true);

		// onload
		xhr.onload = function() {
			if (this.responseText.indexOf("Success") == 0) {
				loading_animation('off');
				_this.raiseUpdateFlag(false);
				// adding notif
				notifDiv.textContent = BTlang.confirmNotesSaved;
				notifDiv.classList.add('confirmation');
				document.getElementById('top').appendChild(notifDiv);

				// reset flags on notes to "void"
				for (var i=0, len=toSaveNotes.length; i<len ; i++) {
					toSaveNotes[i].action = "";
				}
				return true;
			} else {
				loading_animation('off');
				// adding notif
				notifDiv.textContent = this.responseText;
				notifDiv.classList.add('no_confirmation');
				document.getElementById('top').appendChild(notifDiv);
				return false;
			}
		};

		// onerror
		xhr.onerror = function(e) {
			loading_animation('off');
			// adding notif
			notifDiv.textContent = 'AJAX Error ' +e.target.status;
			notifDiv.classList.add('no_confirmation');
			document.getElementById('top').appendChild(notifDiv);
			_this.notifNode.appendChild(document.createTextNode(this.responseText));
		};

		// prepare and send FormData
		var formData = new FormData();
		formData.append('token', token);
		formData.append('save_notes', notesDataText);
		xhr.send(formData);
	}

}







/**************************************************************************************************************************************
       ***        ************   *********   ***     ***   **********          ***      
      *****       ************   *********   ****    ***   ***********        *****     
     *** ***      ***            ***         *****   ***   ***     ****      *** ***   
     *** ***      ***            *******     ******  ***   ***      ***      *** ***   
    ***   ***     ***            *******     *** *** ***   ***      ***     ***   ***   
    *********     ***   ******   ***         ***  ******   ***      ***     *********  
   ***********    ***   ******   ***         ***   *****   ***      ***    *********** 
   ***     ***    ***      ***   ***         ***    ****   ***     ****    ***     *** 
  ***       ***   ************   **********  ***     ***   ***********    ***       ***
  ***       ***   ************   **********  ***     ***   **********     ***       ***

	AGENDA MANAGEMENT
**************************************************************************************************************************************/


function EventAgenda() {
	var _this = this;

	/***********************************
	** Some properties & misc actions
	*/
	this.initDate = new Date();

	// init JSON List
	this.eventsList = JSON.parse(document.getElementById('json_agenda').textContent);

	// init a flag aimed to determine if changes have yet to be pushed
	this.hasUpdated = false;

	// get some DOM elements
	this.calWrap = document.getElementById('calendar-wrapper');
	this.domPage = document.getElementById('page');
	this.notifNode = document.getElementById('message-return');
	this.switchCalSize = document.getElementById('cal-size');
	this.switchCalSize.addEventListener('change', function(e) { _this.switchCalSizeDisplay(e); });

	this.sideNav = document.getElementById('side-nav');
	this.miniCal = document.getElementById('mini-calendar-table');
	// add events on 2 buttons
	this.miniCal.querySelector('thead #mini-prev-month').addEventListener('click', function(e){
		_this.initDate = new Date(_this.initDate.getFullYear(), _this.initDate.getMonth()-1, 1);
		_this.rebuiltMiniCal();
		});
	this.miniCal.querySelector('thead #mini-next-month').addEventListener('click', function(e){
		_this.initDate = new Date(_this.initDate.getFullYear(), _this.initDate.getMonth()+1, 1);
		_this.rebuiltMiniCal();
		});

	this.eventFilter = document.getElementById('filter-events');
	this.eventFilter.addEventListener('change', function(e) { _this.sortEventByFilter(); });

	this.eventContainer = document.getElementById('daily-events');
	this.eventTable = document.getElementById('calendar-table');

	// add events on buttons in table > thead
	this.eventTable.querySelector('thead.year-mode #year > #prev-year').addEventListener('click', function(e){
		_this.initDate = new Date(_this.initDate.getFullYear()-1, _this.initDate.getMonth(), 1);
		_this.rebuiltYearlyCal();
	});
	this.eventTable.querySelector('thead.year-mode #year > #next-year').addEventListener('click', function(e){
		_this.initDate = new Date(_this.initDate.getFullYear()+1, _this.initDate.getMonth(), 1);
		_this.rebuiltYearlyCal();
	});

	this.eventTable.querySelector('thead.month-mode #month > #prev-month').addEventListener('click', function(e){
		_this.initDate = new Date(_this.initDate.getFullYear(), _this.initDate.getMonth()-1, 1);
		_this.rebuiltMonthlyCal();
	});
	this.eventTable.querySelector('thead.month-mode #month > #next-month').addEventListener('click', function(e){
		_this.initDate = new Date(_this.initDate.getFullYear(), _this.initDate.getMonth()+1, 1);
		_this.rebuiltMonthlyCal();
	});

	this.eventTable.querySelector('thead.day-mode #day > #prev-day').addEventListener('click', function(e){
		_this.initDate = new Date(_this.initDate.getFullYear(), _this.initDate.getMonth(), _this.initDate.getDate()-1);
		_this.rebuiltDailyCal();
	});
	this.eventTable.querySelector('thead.day-mode #day > #next-day').addEventListener('click', function(e){
		_this.initDate = new Date(_this.initDate.getFullYear(), _this.initDate.getMonth(), _this.initDate.getDate()+1);
		_this.rebuiltDailyCal();
	});


	this.eventTable.querySelector('thead.month-mode #changeYear > button').addEventListener('click', function(e){
		_this.eventTable.classList.remove('table-month-mode');
		_this.eventTable.classList.add('table-year-mode');
		_this.rebuiltYearlyCal();
	});
	this.eventTable.querySelector('thead.day-mode #changeMonth > button').addEventListener('click', function(e){
		_this.eventTable.classList.remove('table-day-mode');
		_this.eventTable.classList.add('table-month-mode');
		_this.rebuiltMonthlyCal();
	});

	// get event template
	this.eventTemplate = this.eventContainer.firstElementChild.parentNode.removeChild(this.eventContainer.firstElementChild);
	this.eventTemplate.removeAttribute('hidden');

	// get edit-popup template
	this.editEventPopupTemplate = document.getElementById('popup-wrapper').parentNode.removeChild(document.getElementById('popup-wrapper'));
	this.editEventPopupTemplate.removeAttribute('hidden');

	document.getElementById('fab').addEventListener('click', function(e) { _this.addNewEvent(); });

	// Global Page listeners
	// beforeunload : warns the user if some data is not saved when page closes
	window.addEventListener("beforeunload", function(e) {
			if (_this.hasUpdated) {
				var confirmationMessage = BTlang.questionQuitPage;
				(e || window.event).returnValue = confirmationMessage;	//Gecko + IE
				return confirmationMessage;								// Webkit: ignore this shit.
			}
			else { return true; }
		});

	// Save button
	document.getElementById('enregistrer').addEventListener('click', function() { _this.saveEventsXHR(); } );

	// init the « hide sidenav » button
	document.getElementById('hide-side-nav').addEventListener('click', function(){ _this.hideSideNav(); });

	/**************************************
	 * Sort Events by date (sorting)
	*/
	this.sortEventsByDate = function() {
		this.eventsList.sort(function(a, b) {
			if (a.date.start > b.date.start) return 1;
			return -1;
		});
	}

	this.switchCalSizeDisplay = function(e) {
		var cs = document.getElementById('cal-sizer');
		while (cs.classList.length > 0) {
		   cs.classList.remove(cs.classList.item(0));
		}

		switch (e.target.value) {
			case 'eventCalendar':
				cs.classList.add('eventCalendar');
				break;

			case 'eventlist':
				cs.classList.add('eventlist');
			break;
		}
	}



	/**************************************
	 * Draw the mini calendar in the side nav
	*/
	this.rebuiltMiniCal = function() {
		// reference datetime
		var date = this.initDate;
		var dateToday = new Date(); dateToday.setHours(0); dateToday.setMinutes(0); dateToday.setSeconds(0); dateToday.setMilliseconds(0);

		// update Month name on display
		this.miniCal.querySelector('thead span').textContent = this.initDate.toLocaleDateString('fr-FR', {month: "long", year: "numeric"});

		// the <td> with the dates are rebuilt each time (much simplier to handle). So when building, first remove old <td>
		var miniCalBody = this.miniCal.querySelector('tbody');
		if (miniCalBody.firstChild) {
			while (miniCalBody.firstChild) { miniCalBody.removeChild(miniCalBody.firstChild); }
		}

		/* the days */
		var firstDay = (new Date(date.getFullYear(), date.getMonth(), 1, 0, 0, 0));
		var lastDay = (new Date(date.getFullYear(), date.getMonth() + 1, 0, 23, 59, 59));

		// if month is not a full table, complete <table> pad() it with empty cells
		var nbDaysPrevMonth = (firstDay.getDay() == 0) ? 7 : firstDay.getDay();
		var nbDaysNextMonth = 7 - ((lastDay.getDay() == 0) ? 7 : lastDay.getDay());

		// complete the actual <table>
		for (var cell = 1; cell < lastDay.getDate() + nbDaysPrevMonth + nbDaysNextMonth ; cell++) {
			var dateOfCell = new Date(date.getFullYear(), date.getMonth(), cell-(nbDaysPrevMonth-1) );

			// starts new line every %7 days
			if (cell % 7 == 1) {
				var tr = miniCalBody.appendChild(document.createElement("tr"));
				tr.dataset.week = dateOfCell.getWeekNumber();
			}

			var td = document.createElement('td');

			td.id = 'm' + ("00" + (dateOfCell.getMonth() + 1)).slice(-2) + ("00" + dateOfCell.getDate()).slice(-2);

			if (dateOfCell.getDate() === dateToday.getDate()) {
				td.classList.add('isToday');
			}
			if (dateOfCell < dateToday) {
				td.classList.add('isPast');
			}
			if (dateOfCell >= firstDay && dateOfCell <= lastDay) {
				td.appendChild(document.createTextNode( dateOfCell.getDate() ) );
				td.dataset.date = dateOfCell.toLocalISOString();
			}

			td.addEventListener('click', function(e) {
				var oldInitDate = _this.initDate;
				_this.initDate = new Date(this.dataset.date);
				_this.eventTable.classList.remove('table-month-mode');
				_this.eventTable.classList.add('table-day-mode');
				_this.rebuiltDailyCal();
			});

			td.addEventListener('dblclick', function(e) {
				_this.addNewEvent();
			});

			tr.appendChild(td);
		}

		/*******************
		** append the events to the calendar
		*/
		for (var i = 0, len = this.eventsList.length ; i < len ; i++) {
			var eventDateTime = new Date(this.eventsList[i].date.start);

			// is event in different month ? in different year ?
			if ( (eventDateTime.getMonth() !== date.getMonth()) || (eventDateTime.getFullYear() !== date.getFullYear()) ) continue;
			// is event flaged as deleted?
			if (this.eventsList[i].action == "deleteEvent") continue;

			var selectCell = document.getElementById('m' + ("00" + (eventDateTime.getMonth() + 1)).slice(-2) + ("00" + eventDateTime.getDate()).slice(-2));

			selectCell.classList.add('hasEvent');
		}

	}
	// Init events lists (default in "calendar" view)
	this.rebuiltMiniCal();



	/**************************************
	 * Draw the MAIN calendar 
	*/

	/**************************************
	 * In « YEAR » display
	*/
	this.rebuiltYearlyCal = function() {
		this.eventTable.querySelector('thead.year-mode #year > span').textContent = this.initDate.getFullYear();

		// the <td>s with the dates are rebuilt each time (much simplier to handle). So when building, first remove old <td>
		var calBody = this.eventTable.querySelector('tbody.year-mode');
		if (calBody.firstChild) {
			while (calBody.firstChild) {calBody.removeChild(calBody.firstChild);}
		}

		/* the Months */
		for (var cell = 0; cell < 12 ; cell++) {

			// starts new line every %4 months
			if (cell % 4 == 0) {
				var tr = calBody.appendChild(document.createElement("tr"));
			}

			var td = tr.appendChild(document.createElement('td'));
			td.dataset.datetime = (new Date(this.initDate.getFullYear(), cell, 1 ) );

			td.appendChild(document.createTextNode( (new Date(this.initDate.getFullYear(), cell, 1)).toLocaleDateString('fr-FR', {month: "short"}) ));

			td.addEventListener('click', function(e){
				_this.eventTable.classList.remove('table-year-mode');
				_this.eventTable.classList.add('table-month-mode');

				_this.initDate = new Date( this.dataset.datetime );
				_this.rebuiltMonthlyCal();
			});
		}

	}

	/**************************************
	 * In « MONTH » display
	*/
	this.rebuiltMonthlyCal = function() {
		// reference datetime
		var date = this.initDate;
		var dateToday = new Date(); dateToday.setHours(0); dateToday.setMinutes(0); dateToday.setSeconds(0); dateToday.setMilliseconds(0);

		// update Month name on display
		this.eventTable.querySelector('thead.month-mode #month > span').textContent = this.initDate.toLocaleDateString('fr-FR', {month: "long", year: "numeric"});

		// the <td> with the dates are rebuilt each time (much simplier to handle). So when building, first remove old <td>
		var calBody = this.eventTable.querySelector('tbody.month-mode');
		if (calBody.firstChild) {
			while (calBody.firstChild) {calBody.removeChild(calBody.firstChild);}
		}

		/* the days */
		var firstDay = (new Date(date.getFullYear(), date.getMonth(), 1, 0, 0, 0));
		var lastDay = (new Date(date.getFullYear(), date.getMonth() + 1, 0, 23, 59, 59));

		// if month is not a complete square, complete <table> with days from prev/next month
		// in JS Sunday = 0th day of week. I need 7th, since sunday is last collumn in table
		var nbDaysPrevMonth = (firstDay.getDay() == 0) ? 7 : firstDay.getDay();
		var nbDaysNextMonth = 7 - ((lastDay.getDay() == 0) ? 7 : lastDay.getDay());

		// complete the actual <table>
		for (var cell = 1; cell < lastDay.getDate() + nbDaysPrevMonth + nbDaysNextMonth ; cell++) {
			var dateOfCell = new Date(date.getFullYear(), date.getMonth(), cell-(nbDaysPrevMonth-1) );

			// starts new line every %7 days
			if (cell % 7 == 1) {
				var tr = calBody.appendChild(document.createElement("tr"));
				tr.dataset.week = dateOfCell.getWeekNumber();
			}

			var td = document.createElement('td');

			td.id = 'i' + ("00" + (dateOfCell.getMonth() + 1)).slice(-2) + ("00" + dateOfCell.getDate()).slice(-2);

			tr.appendChild(td);

			if (dateOfCell.getDate() === dateToday.getDate()) {
				td.classList.add('isToday');
			}
			if (dateOfCell < dateToday) {
				td.classList.add('isPast');
			}
			if (dateOfCell < firstDay) {
				td.classList.add('isPrevMonth');
				continue;
			}
			if (dateOfCell > lastDay) {
				td.classList.add('isNextMonth');
				continue;
			}

			// ad day nummber to cell
			td.appendChild(document.createTextNode( dateOfCell.getDate() ) );
			td.dataset.date = dateOfCell.toLocalISOString();

			td.addEventListener('click', function(e) {
				if (!isMobile()) {
					if (this !== e.target) return;
				}
				var oldInitDate = _this.initDate;
				_this.initDate = new Date(this.dataset.date);
				_this.eventTable.classList.remove('table-month-mode');
				_this.eventTable.classList.add('table-day-mode');

				_this.rebuiltDailyCal();
			});

			td.addEventListener('dblclick', function(e) {
				_this.addNewEvent();
			});
		}

		/*******************
		** append the events to the calendar
		*/
		for (var i = 0, len = this.eventsList.length ; i < len ; i++) {
			var item = this.eventsList[i];
			var eventDateTime = new Date(item.date.start);

			// is event flaged as deleted?
			if (item.action == "deleteEvent") continue;
			// is event in current month?
			if (!( eventDateTime >= firstDay && eventDateTime <= lastDay ) ) continue;

			var selectCell = document.getElementById('i' + ("00" + (eventDateTime.getMonth() + 1)).slice(-2) + ("00" + eventDateTime.getDate()).slice(-2));

			if (!selectCell.dataset.nbEvents || selectCell.dataset.nbEvents < 5) {
				var span = document.createElement('SPAN');
				span.style.backgroundColor = item.color;
				var time = document.createElement('TIME');
				time.setAttribute('datetime', item.date.start);
				time.textContent = (eventDateTime).toLocaleTimeString('fr-FR', {hour: "2-digit", minute: "2-digit"});
				span.appendChild(time);
				span.appendChild(document.createTextNode(item.title));

				span.classList.add('eventLabel');
				span.dataset.id = item.id;
				if (!isMobile()) {
					span.addEventListener('click', function() {
							_this.showEventPopup(this.dataset.id);
					} );
				}
				selectCell.appendChild(span);
			}
			
			if (selectCell.classList.contains('hasEvent')) {
				selectCell.dataset.nbEvents++;
			}
			else {
				selectCell.dataset.nbEvents = 1;
				selectCell.classList.add('hasEvent');
			}
		}

	}
	// Init events lists
	this.rebuiltMonthlyCal();


	/**************************************
	 * In DAY » display
	*/
	this.rebuiltDailyCal = function() {
		// reference datetime
		var date = this.initDate;
		var dateNow = new Date();
		// update Month name on display
		this.eventTable.querySelector('thead.day-mode #day > span').textContent = this.initDate.toLocaleDateString('fr-FR', {weekday: "long", day: "numeric", month: "long", year: "numeric"});

		// the <td> with the dates are rebuilt each time (much simplier to handle). So when building, first remove old <td>
		var calBody = this.eventTable.querySelector('tbody.day-mode');
		if (calBody.firstChild) {
			while (calBody.firstChild) {calBody.removeChild(calBody.firstChild);}
		}

		/* the days */
		var firstHour = (new Date(date.getFullYear(), date.getMonth(), date.getDate(), 0, 0, 0 ));
		var lastHour = (new Date(date.getFullYear(), date.getMonth(), date.getDate(), 23, 0, 0));


		// complete the actual <table>
		for (var cell = 0; cell <= lastHour.getHours() ; cell++) {
			var timeOfCell = new Date(date.getFullYear(), date.getMonth(), date.getDate(), cell, 0, 0 );

			var tr = calBody.appendChild(document.createElement("tr"));
			tr.id = 'h' + ("00" + (timeOfCell.getHours())).slice(-2) + "00";

			var td = document.createElement('td');
			if (timeOfCell.getHours() === dateNow.getHours()) {
				td.classList.add('isNow');
			}
			if (timeOfCell < dateNow) {
				td.classList.add('isPast');
			}

			td.addEventListener('click', function() {
				var oldInitDate = _this.initDate;
				_this.initDate = new Date(this.dataset.date);
			});

			td.addEventListener('dblclick', function(e) {
				_this.addNewEvent();
			});

			// add hour to cell
			var spanHour = document.createElement('SPAN');
			spanHour.appendChild(document.createTextNode( ("00" + (timeOfCell.getHours())).slice(-2) + ":" + "00" ));
			td.appendChild(spanHour);
			td.dataset.date = timeOfCell.toLocalISOString();
			tr.appendChild(td);

			var td = document.createElement('td');
			tr.appendChild(td);

		}
		/*******************
		** append the events to the calendar
		*/
		for (var i = 0, len = this.eventsList.length ; i < len ; i++) {
			var item = this.eventsList[i];
			var eventDateTime = new Date(item.date.start);

			// is event flaged as deleted?
			if (item.action == "deleteEvent") continue;
			// is event in current month?
			if (!( eventDateTime >= firstHour && eventDateTime <= lastHour ) ) continue;

			var selectCell = document.getElementById( 'h' + ("00" + (eventDateTime.getHours())).slice(-2) + "00" ).querySelector('td:nth-of-type(2)');

			var span = document.createElement('SPAN');
			span.classList.add('eventLabel');
			span.style.backgroundColor = item.color;
			span.textContent = item.title;
			span.dataset.id = item.id;
			span.addEventListener('click', function() {
				_this.showEventPopup(this.dataset.id);
			});
			// spans the SPAN to give it a height proportionnal to the duration
			var duration = (new Date(item.date.end) - eventDateTime) / 1000 / 60 / 60 ; // in hours
			var parentHeight = selectCell.parentNode.getBoundingClientRect().bottom - selectCell.parentNode.getBoundingClientRect().top;
			span.style.height = (parentHeight * duration  - (2*3)) + 'px';

			if (duration < 23) {
				span.style.marginLeft = duration * 2 + '%';
			} else {
				span.style.marginLeft = "80%";
				span.style.marginRight = "0%";
				span.style.width = 'auto';
			}

			selectCell.appendChild(span);
		}

	}

	this.rebuiltEventsWall = function(EventsData) {
		// empties the node
		if (this.eventContainer.firstChild) {
			while (this.eventContainer.firstChild) {this.eventContainer.removeChild(this.eventContainer.firstChild);}
		}
		// TODO : add "no event" message
		if (0 === EventsData.length) return false;

		var date = Date.now();
		var evList = document.createDocumentFragment();

		// populates the new list
		for (var i = 0, len = EventsData.length ; i < len ; i++) {
			var item = EventsData[i];
			var itemDate = new Date(item.date.start);
			var div = this.eventTemplate.cloneNode(true);
			// ignore deleted events
			if (item.action == 'deleteEvent') continue;
			div.dataset.id = item.id;
			div.addEventListener('click', function() {
					_this.showEventPopup(this.dataset.id);
				} );

			if (itemDate >= new Date()) {
				div.classList.add('futureEvent');
			} else {
				div.classList.add('pastEvent');
			}
			div.querySelector('.eventDate').title = itemDate.toLocaleDateString('fr', {weekday: "long", year: "numeric", month: "long", day: "numeric", hour: "numeric", minute: "numeric"});
			div.querySelector('.event-dd').textContent = itemDate.getDate();
			div.querySelector('.event-mmdd').textContent = itemDate.toLocaleDateString('fr', {month: "short"}) + ", " + itemDate.toLocaleDateString('fr', {weekday: "short"});
			div.querySelector('.event-hhii').textContent = itemDate.toLocaleTimeString('fr', {hour: 'numeric', minute: 'numeric'});
			div.querySelector('.eventSummary > .color').style.backgroundColor = item.color;
			div.querySelector('.eventSummary > .title').textContent = item.title;
			div.querySelector('.eventSummary > .loc').textContent = item.loc;

			evList.appendChild(div);
		}

		this.eventContainer.appendChild(evList);

	}


	// the "hide side nav" button in sub-menu
	this.hideSideNav = function() {
		this.sideNav.classList.toggle('hidden-sidenav');
	}


	/**************************************
	 * Sorting functions
	*/

	// sort Event according to the "select" element status.
	this.sortEventByFilter = function() {
		// init sorting mode from the <select> form value
		var selectDate = this.eventFilter.value;

	
		var filter = function(date) {
			switch(selectDate) {

				case 'today':
					if (date.toDateString() == (new Date()).toDateString()) {
						return true;
					}
					break;

				case 'tomonth':
					var newD = new Date();
					if ("" + date.getFullYear() + date.getMonth() == "" + newD.getFullYear() + newD.getMonth()) {
						return true;
					}
					break;

				case 'toyear':
					if (date.getFullYear() == (new Date()).getFullYear()) {
						return true;
					}
					break;

				case 'past':
					if (date <= new Date()) {
						return true;
					}
					break;

				case 'futur':
					if (date >= new Date()) {
						return true;
					}
					break;

				case 'all':
					return true;
					break;

				default:
					selectDate = new Date(selectDate);
					if (date.toDateString() == selectDate.toDateString()) {
						return true;
					}
					break;

			}
			return false;

		}

		var newList = new Array();

		for (var i = 0, len = this.eventsList.length ; i < len ; i++) {
			var item = this.eventsList[i];
			if (item.action == 'deleteEvent') continue;
			var itemDate = new Date(item.date.start);

			// if the event is today, add a row to div.
			if ( filter(itemDate) === true ) {
				newList.push(item);
			}
		}
		this.rebuiltEventsWall(newList);
	}
	// init the whole DOM list
	this.sortEventByFilter();


	/**************************************
	 * Displays the "show event" popup
	*/
	this.showEventPopup = function(id) {
		for (var i = 0, len = this.eventsList.length ; i < len ; i++) {
			if (this.eventsList[i].id === id) {
				var item = this.eventsList[i];
				break;
			}
		}
		var popupWrapper = this.editEventPopupTemplate.cloneNode(true);
		popupWrapper.addEventListener('click', function(e) {
			// clic is outside popup: closes popup
			if (e.target == this) {
				popupWrapper.parentNode.removeChild(popupWrapper);
				document.body.classList.remove('noscroll');
			}
		});
		popupWrapper.querySelector('.popup-event').id = 'popup';
		popupWrapper.querySelector('.popup-event').removeAttribute('hidden');
		document.body.classList.add('noscroll');

		popupWrapper.querySelector('#popup > .event-title > .event-color').style.backgroundColor = item.color;
		popupWrapper.querySelector('#popup > .event-title > .event-name').textContent = item.title;

		popupWrapper.querySelector('#popup > .event-title > .item-menu-options .button-edit').addEventListener('click', function(e){
			popupWrapper.parentNode.removeChild(popupWrapper);
			document.body.classList.remove('noscroll');
			_this.showEventEditPopup(item);
		});
		popupWrapper.querySelector('#popup > .event-title > .item-menu-options .button-suppr').addEventListener('click', function(e){
			_this.markAsDeleted(item);
			document.body.classList.remove('noscroll');
		});

		popupWrapper.querySelector('#popup > .event-title > .button-cancel').addEventListener('click', function() {
			popupWrapper.parentNode.removeChild(popupWrapper);
			document.body.classList.remove('noscroll');
		});

		popupWrapper.querySelector('#popup > .event-content > ul > li.event-time > span:nth-of-type(1)').textContent = (new Date(item.date.start)).toLocaleDateString('fr-FR', {weekday: "long", year: "numeric", month: "long", day: "numeric"});
		popupWrapper.querySelector('#popup > .event-content > ul > li.event-time > span:nth-of-type(2)').textContent = (new Date(item.date.start)).toLocaleTimeString('fr-FR', {hour: '2-digit', minute:'2-digit'}) + '-' + (new Date(item.date.end)).toLocaleTimeString('fr-FR', {hour: '2-digit', minute:'2-digit'});
		popupWrapper.querySelector('#popup > .event-content > ul > li.event-loc').textContent = item.loc;
		popupWrapper.querySelector('#popup > .event-content > ul > li.event-description').textContent = item.content;


		// remove empty nodes
		var nodes = popupWrapper.querySelectorAll('#popup > .event-content *');
		for (let node of nodes) {
			if (node.textContent.trim().length === 0) node.parentNode.removeChild(node);
		}

		this.domPage.appendChild(popupWrapper);
	}


	/**************************************
	 * Displays the "Edit event" popup (also for "new" events)
	*/
	this.showEventEditPopup = function(item) {
		var popupWrapper = this.editEventPopupTemplate.cloneNode(true);
		popupWrapper.addEventListener('click', function(e) {
			// clic is outside popup: closes popup
			if (e.target == this) {
				popupWrapper.parentNode.removeChild(popupWrapper);
				document.body.classList.remove('noscroll');
			}
		});

		popupWrapper.querySelector('.popup-edit-event').id = 'popup';
		popupWrapper.querySelector('.popup-edit-event').removeAttribute('hidden');
		document.body.classList.add('noscroll');

		popupWrapper.querySelector('#popup > .event-title > .event-color').style.backgroundColor = item.color;

		popupWrapper.querySelector('#popup > .event-title > .colors').addEventListener('click', function(e) {
			if (e.target.tagName == 'LI') _this.changeColor(item, e);
		});

		popupWrapper.querySelector('#popup > .event-title > input').value = item.title;
		popupWrapper.querySelector('#popup > .event-title > .button-cancel').addEventListener('click', function() {
			popupWrapper.parentNode.removeChild(popupWrapper);
			document.body.classList.remove('noscroll');
		});
		popupWrapper.querySelector('#popup > .event-content > .event-content-date #allDay').addEventListener('change', function() {
			var dateTimeInput = popupWrapper.querySelector('#popup > .event-content .date-time-input');
			if (this.checked) {
				dateTimeInput.classList.add('date-only');
			}
			else {
				dateTimeInput.classList.remove('date-only');
			}
		});

		popupWrapper.querySelector('#popup > .event-content > .event-content-date #time-start').value = item.date.start.substr(11, 5);
		popupWrapper.querySelector('#popup > .event-content > .event-content-date #time-end').value = item.date.end.substr(11, 5);
		popupWrapper.querySelector('#popup > .event-content > .event-content-date #date').value = item.date.start.substr(0, 10);

		popupWrapper.querySelector('#popup > .event-content > .event-content-loc input').value = item.loc;
		popupWrapper.querySelector('#popup > .event-content > .event-content-descr textarea').value = item.content;

		popupWrapper.querySelector('#popup > .event-footer > .button-submit').addEventListener('click', function() {
			_this.markAsEdited(item);
			document.body.classList.remove('noscroll');
			popupWrapper.parentNode.removeChild(popupWrapper);
		});

		this.domPage.appendChild(popupWrapper);
	}


	/**************************************
	 * Mark an Event object as having been edited
	*/
	this.markAsEdited = function(item) {
		var popup = document.getElementById('popup');

		// is Edit ?
		// search item in eventsList.
		// Can’t test on "item.action == newEvent", since an edited-new event remains "new" (not "edited").
		var isEdit = false;
		for (var i = 0, len = this.eventsList.length ; i < len ; i++) {
			if (item.id == this.eventsList[i].id) {
				var isEdit = true;
				break;
			}
		}

		item.title = popup.querySelector('.event-title input').value || BTlang.emptyTitle;
		item.color = window.getComputedStyle(popup.querySelector('.event-title .event-color')).backgroundColor;

		item.content = popup.querySelector('.event-content-descr .text').value;
		item.loc = popup.querySelector('.event-content-loc .text').value;
		if (popup.querySelector('#allDay').checked ) {
			var newDateStart = new Date(document.getElementById('date').value + " " + "00:00:00");
			var newDateEnd = new Date(document.getElementById('date').value + " " + "23:59:59");
		} else {
			var newDateStart = new Date(document.getElementById('date').value + " " + document.getElementById('time-start').value);
			var newDateEnd = new Date(document.getElementById('date').value + " " + document.getElementById('time-end').value);
		}
		item.date = {"start": newDateStart.toLocalISOString(), "end": newDateEnd.toLocalISOString()};

		// event is new:
		if (!isEdit) {
			this.eventsList.push(item);     // append it to the eventsList{}
		}

		// re-sort by date
		this.sortEventsByDate();


		// rebuilt Calendar to take changes into account. // TODO: perhaps not rebuilt cal, but only add/move buttons (for perf) ?
		this.rebuiltMiniCal();
		if (this.eventTable.classList.contains('table-day-mode')) this.rebuiltDailyCal();
		if (this.eventTable.classList.contains('table-month-mode')) this.rebuiltMonthlyCal();
		if (this.eventTable.classList.contains('table-year-mode')) this.rebuiltYearlyCal();
		this.sortEventByFilter();

		item.action = item.action || 'updateEvent';

		// raises global "updated" flag.
		this.raiseUpdateFlag(true);
	}


	/**************************************
	 * Creates a new event, init it, display it and add it to list.
	*/
	this.addNewEvent = function() {
		var date = this.initDate;
		var newEv = {
			"id": new Date().getTime().toString(),
			"date": {'start':date.toLocalISOString(),'end':date.toLocalISOString()},
			"title": '',
			"content": '',
			"color" : '#ff8a80',
			"loc" : '',
			"action": 'newEvent',
		};

		// opens freshly created event popup
		this.showEventEditPopup(newEv);
	}


	/**************************************
	 * Deletes an event
	*/
	this.markAsDeleted = function(item) {
		if (!window.confirm(BTlang.questionSupprEvent)) { return false; }
		item.action = 'deleteEvent';

		this.rebuiltMiniCal();
		if (this.eventTable.classList.contains('table-day-mode')) this.rebuiltDailyCal();
		if (this.eventTable.classList.contains('table-month-mode')) this.rebuiltMonthlyCal();
		if (this.eventTable.classList.contains('table-year-mode')) this.rebuiltYearlyCal();
		this.sortEventByFilter();

		// close popup
		document.getElementById('popup-wrapper').parentNode.removeChild(document.getElementById('popup-wrapper'));

		// raises global "updated" flag.
		this.raiseUpdateFlag(true);
	}


	/**************************************
	 * Change the color of a note
	*/
	this.changeColor = function(item, e) {
		var newColor = window.getComputedStyle(e.target).backgroundColor;
		document.querySelector('#popup > .event-title > .event-color').style.backgroundColor = newColor;
		e.preventDefault();
	}

	/**************************************
	 * Each change triggers a flag. If is(flag) : the save button displays
	*/
	this.raiseUpdateFlag = function(flagRaised) {
		if (flagRaised) {
			this.hasUpdated = true;
			document.getElementById('enregistrer').disabled = false;
		} else {
			this.hasUpdated = false;
			document.getElementById('enregistrer').disabled = true;
		}
	}



	/**************************************
	 * AJAX call to save events to DB
	*/
	this.saveEventsXHR = function() {
		loading_animation('on');
		// only keep modified events
		var toSaveEvents = Array();
		for (var i=0, len=this.eventsList.length; i<len ; i++) {
			if (this.eventsList[i].action && 0 !== this.eventsList[i].action.length) {
				var ev = this.eventsList[i];
				//ev.ymdhisDate = ev.date.substr(0,19).replace(/[:T-]/g, '');
				toSaveEvents.push(ev);
			}
		}

		// make a string out of it
		var eventsDataText = JSON.stringify(toSaveEvents);

		var notifDiv = document.createElement('div');
		// create XHR
		var xhr = new XMLHttpRequest();
		xhr.open('POST', 'ajax/agenda.ajax.php', true);

		// onload
		xhr.onload = function() {
			if (this.responseText.indexOf("Success") == 0) {
				loading_animation('off');
				_this.raiseUpdateFlag(false);
				// adding notif
				notifDiv.textContent = BTlang.confirmEventsSaved;
				notifDiv.classList.add('confirmation');
				document.getElementById('top').appendChild(notifDiv);

				// resetq flags on events (but not those that are deleted)
				for (var i=0, len=toSaveEvents.length; i<len ; i++) {
					if (toSaveEvents[i].action == 'updateEvent' || toSaveEvents[i].action == 'newEvent') {
						toSaveEvents[i].action = "";
					}
				}
				return true;
			} else {
				loading_animation('off');
				// adding notif
				notifDiv.textContent = this.responseText;
				notifDiv.classList.add('no_confirmation');
				document.getElementById('top').appendChild(notifDiv);
				return false;
			}
		};

		// onerror
		xhr.onerror = function(e) {
			loading_animation('off');
			// adding notif
			notifDiv.textContent = 'AJAX Error ' +e.target.status;
			notifDiv.classList.add('no_confirmation');
			document.getElementById('top').appendChild(notifDiv);
			_this.notifNode.appendChild(document.createTextNode(this.responseText));
		};

		// prepare and send FormData
		var formData = new FormData();
		formData.append('token', token);
		formData.append('save_events', eventsDataText);
		xhr.send(formData);
	}
}





/**************************************************************************************************************************************
   ***********   ************   ***     ***   ***********        ***        ***********   ***********       ****      
   ***********   ************   ****    ***   ***********       *****       ***********   ***********     ********    
   ***           ***      ***   *****   ***       ***          *** ***      ***               ***       ***      *** 
   ***           ***      ***   ******  ***       ***          *** ***      ***               ***       ***          
   ***           ***      ***   *** *** ***       ***         ***   ***     ***               ***        **********   
   ***           ***      ***   ***  ******       ***         *********     ***               ***        **********  
   ***           ***      ***   ***   *****       ***        ***********    ***               ***                *** 
   ***           ***      ***   ***    ****       ***        ***     ***    ***               ***       ***      *** 
   ***********   ************   ***     ***       ***       ***       ***   ***********       ***         ********   
   ***********   ************   ***     ***       ***       ***       ***   ***********       ***           ****     

	CONTACTS MANAGEMENT
**************************************************************************************************************************************/


function ContactsList() {
	var _this = this;

	/***********************************
	** Some properties & misc actions
	*/
	// init JSON List
	this.contactList = JSON.parse(document.getElementById('json_contacts').textContent);

	// init a flag aimed to determine if changes have yet to be pushed
	this.hasUpdated = false;

	// get some DOM elements
	this.domPage = document.getElementById('page');
	this.notifNode = document.getElementById('message-return');
	this.contactTable = document.getElementById('table-contacts');
	this.contactTBody = document.getElementById('table-contacts').tBodies[0];

	// get contact row template
	this.contactTemplate = this.contactTBody.removeChild(this.contactTBody.firstElementChild);
	this.contactTemplate.removeAttribute('hidden');


	// get popup template
	this.contactPopupTemplate = this.domPage.firstElementChild.parentNode.removeChild(this.domPage.firstElementChild);
	this.contactPopupTemplate.removeAttribute('hidden');

	document.getElementById('fab').addEventListener('click', function(e) { _this.addNewContact(); });

	// Global Page listeners
	// beforeunload : warns the user if some data is not saved when page closes
	window.addEventListener("beforeunload", function(e) {
			if (_this.hasUpdated) {
				var confirmationMessage = BTlang.questionQuitPage;
				(e || window.event).returnValue = confirmationMessage;	//Gecko + IE
				return confirmationMessage;								// Webkit: ignore this shit.
			}
			else { return true; }
		});

	// Save button
	document.getElementById('enregistrer').addEventListener('click', function() { _this.saveContactsXHR(); } );

	// built table on page-ready
	window.addEventListener("load", function() {
		_this.rebuiltContactsTable(_this.contactList);
	});


	this.rebuiltContactsTable = function(ContactsData) {
		// empties the node
		if (this.contactTBody.firstChild) {
			while (this.contactTBody.firstChild) {this.contactTBody.removeChild(this.contactTBody.firstChild);}
		}
		// TODO : add "no contact" message
		if (0 === ContactsData.length) return false;

		var ctList = document.createDocumentFragment();

		// populates the new list
		for (var i = 0, len = ContactsData.length ; i < len ; i++) {
			if (this.contactList[i].action == "deleteContact") continue;

			var item = ContactsData[i]; // sort in reverse order
			var tr = this.contactTemplate.cloneNode(true);
			tr.setAttribute('data-id', item.id);

			if (item.img != "") {
				tr.querySelector('.icon > span').style.backgroundImage = 'url('+item.img+')';
			} else {
				var color = "#" + hashFnv32a(item.fullname).substring(0, 3);
				tr.querySelector('.icon > span').style.backgroundImage = 'linear-gradient('+color+', '+color+')';
			}

			tr.querySelector('.name').textContent = item.title + ' ' + item.fullname;

			tr.querySelector('.icon').addEventListener('click', function() { _this.showContactPopup(this.parentNode.dataset.id); })
			tr.querySelector('.name').addEventListener('click', function() { _this.showContactPopup(this.parentNode.dataset.id); })

			var aTempl = tr.querySelector('.tel').removeChild(tr.querySelector('.tel').firstChild);
			item.tel.forEach(function(tel) {
				var a = aTempl.cloneNode(true);
				a.textContent = tel;
				a.href = 'tel:' + tel;
				tr.querySelector('.tel').appendChild(a);
			});


			var aTempl = tr.querySelector('.email').removeChild(tr.querySelector('.email').firstChild);
			item.email.forEach(function(mail) {
				var a = aTempl.cloneNode(true);
				a.textContent = mail;
				a.href = 'mailto:' + mail;
				tr.querySelector('.email').appendChild(a);
			});


			tr.querySelector('.label > span').textContent = item.label;
			if (item.label.trim().length === 0) tr.querySelector('.label').removeChild(tr.querySelector('.label > span'));

			tr.querySelector('.button-edit').addEventListener('click', function() {
				_this.showContactEditPopup(item);
			});

			ctList.appendChild(tr);
		}

		this.contactTBody.appendChild(ctList);

		// if a search is made and only one contact is displayed, open popup right away.
		if (1 === ContactsData.length && window.location.href.match(/(\?|&)q=/i)) {
			this.showContactPopup(item.id);
		}

	}

	/**************************************
	 * Displays the "show contact" popup
	*/
	this.showContactPopup = function(id) {
		for (var i = 0, len = this.contactList.length ; i < len ; i++) {
			if (this.contactList[i].id === id) {
				var item = this.contactList[i];
				break;
			}
		}

		var popupWrapper = this.contactPopupTemplate.cloneNode(true);
		popupWrapper.addEventListener('click', function(e) {
			// clic is outside popup: closes popup
			if (e.target == this) {
				popupWrapper.parentNode.removeChild(popupWrapper);
				document.body.classList.remove('noscroll');
			}
		});
		popupWrapper.querySelector('.popup-contact').id = 'popup';
		popupWrapper.querySelector('.popup-contact').removeAttribute('hidden');
		document.body.classList.add('noscroll');

		// POPUP TITLE
		var popupTitle = popupWrapper.querySelector('#popup > .contact-title');

		// misc events
		popupTitle.querySelector('.item-menu-options > ul > li > a').addEventListener('click', function(e){
			document.body.classList.remove('noscroll');
			_this.markAsDeleted(item);
		});
		popupTitle.querySelector('.button-cancel').addEventListener('click', function() {
			popupWrapper.parentNode.removeChild(popupWrapper);
			document.body.classList.remove('noscroll');
		});
		popupTitle.querySelector('.button-edit').addEventListener('click', function() {
			popupWrapper.parentNode.removeChild(popupWrapper);
			document.body.classList.remove('noscroll');
			_this.showContactEditPopup(item);
		});

		// icon
		if (item.img != "") {
			popupTitle.querySelector('.contact-img').style.backgroundImage = 'url('+item.img+')';
		} else {
			var color = "#" + hashFnv32a(item.fullname).substring(0, 3);
			popupTitle.querySelector('.contact-img').style.backgroundImage = 'linear-gradient('+color+', '+color+')';
		}

		// name & label
		popupTitle.querySelector('.contact-name').textContent = item.title + ' ' + item.fullname;
		popupTitle.querySelector('.contact-label').textContent = item.label;
		if (item.label.trim().length === 0) popupTitle.querySelector('.contact-label').parentNode.removeChild(popupTitle.querySelector('.contact-label'));

		// POPUP CONTENT
		// name + pseudo
		var popupContent = popupWrapper.querySelector('#popup > .contact-content');
		popupContent.querySelector('.contact-names > span').textContent = item.fullname;
		if (item.pseudo != "") {
			var s = popupContent.querySelector('.contact-names > span').cloneNode(false);
			s.textContent = item.pseudo;
			popupContent.querySelector('.contact-names').appendChild(s);
		}

		// email(s)
		var aT = popupContent.querySelector('.contact-emails').removeChild(popupContent.querySelector('.contact-emails').firstChild);
		item.email.forEach(function(m) {
			var a = aT.cloneNode(true);
			a.textContent = m;
			a.href = 'mailto:' + m;
			popupContent.querySelector('.contact-emails').appendChild(a);
		});

		// phone number(s)
		var aT = popupContent.querySelector('.contact-phones').removeChild(popupContent.querySelector('.contact-phones').firstChild);
		item.tel.forEach(function(t) {
			var a = aT.cloneNode(true);
			a.textContent = t;
			a.href = 'tel:' + t;
			popupContent.querySelector('.contact-phones').appendChild(a);
		});

		// address
		popupContent.querySelector('.contact-address > span:nth-of-type(1)').textContent = item.address.nb + " " + item.address.st;
		popupContent.querySelector('.contact-address > span:nth-of-type(2)').textContent = item.address.co;
		popupContent.querySelector('.contact-address > span:nth-of-type(3)').textContent = item.address.cp + " " + item.address.ci;
		popupContent.querySelector('.contact-address > span:nth-of-type(4)').textContent = item.address.sa + " " + item.address.cn;

		// birthday
		if (item.birthday != "") {
			popupContent.querySelector('.contact-birthday').textContent = Date.dateFromYMDHIS(item.birthday).toLocaleDateString('fr', {year: "numeric", month: "long", day: "numeric"});
		}

		// websites link(s)
		var aT = popupContent.querySelector('.contact-links').removeChild(popupContent.querySelector('.contact-links').firstChild);
		item.websites.forEach(function(s) {
			var a = aT.cloneNode(true);
			a.textContent = s;
			a.href = s;
			popupContent.querySelector('.contact-links').appendChild(a);
		});

		// social media link(s)
		var aT = popupContent.querySelector('.contact-social').removeChild(popupContent.querySelector('.contact-social').firstChild);
		item.social.forEach(function(s) {
			var a = aT.cloneNode(true);
			a.textContent = s;
			a.href = s;
			popupContent.querySelector('.contact-social').appendChild(a);
		});

		// notes & other
		popupContent.querySelector('.contact-notes').textContent = item.notes;
		popupContent.querySelector('.contact-other').textContent = item.other;

		// remove empty nodes
		var nodes = popupContent.querySelectorAll('*');
		for (let node of nodes) {
			if (node.textContent.trim().length === 0) node.parentNode.removeChild(node);
		}
		// remove not used section titles
		while (popupContent.lastElementChild.tagName === 'DIV') {
			popupContent.removeChild(popupContent.lastElementChild);
		}
		var divs = popupContent.querySelectorAll('div');
		for (let div of divs) {
			if (div.nextElementSibling.tagName === 'DIV') div.parentNode.removeChild(div);
		}

		this.domPage.appendChild(popupWrapper);
	}

	this.showContactEditPopup = function(item) {

		var popupWrapper = this.contactPopupTemplate.cloneNode(true);
		popupWrapper.addEventListener('click', function(e) {
			// clic is outside popup: closes popup
			if (e.target == this) {
				popupWrapper.parentNode.removeChild(popupWrapper);
				document.body.classList.remove('noscroll');
			}
		});
		popupWrapper.querySelector('.popup-edit-contact').id = 'popup';
		popupWrapper.querySelector('.popup-edit-contact').removeAttribute('hidden');
		document.body.classList.add('noscroll');

		popupWrapper.querySelector('.popup-edit-contact .contact-title > .button-cancel').addEventListener('click', function() {
			popupWrapper.parentNode.removeChild(popupWrapper);
			document.body.classList.remove('noscroll');
		});

		popupWrapper.querySelector('#popup input[name="contact-title"]').value = item.title;
		popupWrapper.querySelector('#popup input[name="contact-fullname"]').value = item.fullname;
		popupWrapper.querySelector('#popup input[name="contact-label"]').value = item.label;

		if (item.img != "") {
			popupWrapper.querySelector('#popup .contact-img').style.backgroundImage = 'url('+item.img+')';
		} else {
			var color = "#" + hashFnv32a(item.fullname).substring(0, 3);
			popupWrapper.querySelector('#popup .contact-img').style.backgroundImage = 'linear-gradient('+color+', '+color+')';
		}


		popupWrapper.querySelector('#popup .contact-img').addEventListener('click', function(e) {
			_this.loadContactImage(e);
			item.imgIsNew = true;
		});


		// email(s)
		var labelField = popupWrapper.querySelector('#popup label[for="contact-email"]');
		item.email.forEach(function(m) {
			labelField.querySelector('input').value = m;
			var fieldParent = labelField.parentNode;
			labelField = labelField.cloneNode(true);
			labelField.querySelector('input').value = "";
			fieldParent.appendChild(labelField);
		});
		// phone(s)
		var labelField = popupWrapper.querySelector('#popup label[for="contact-phone"]');
		item.tel.forEach(function(t) {
			labelField.querySelector('input').value = t;
			var fieldParent = labelField.parentNode;
			labelField = labelField.cloneNode(true);
			labelField.querySelector('input').value = "";
			fieldParent.appendChild(labelField);
		});

		popupWrapper.querySelector('#popup input[name="contact-nb"]').value = item.address.nb;
		popupWrapper.querySelector('#popup input[name="contact-st"]').value = item.address.st;
		popupWrapper.querySelector('#popup input[name="contact-co"]').value = item.address.co;
		popupWrapper.querySelector('#popup input[name="contact-cp"]').value = item.address.cp;
		popupWrapper.querySelector('#popup input[name="contact-ci"]').value = item.address.ci;
		popupWrapper.querySelector('#popup input[name="contact-sa"]').value = item.address.sa;
		popupWrapper.querySelector('#popup input[name="contact-cn"]').value = item.address.cn;
		popupWrapper.querySelector('#popup input[name="contact-birthday"]').value = item.birthday.replace(/(....)(..)(..)/, "$1-$2-$3");
		popupWrapper.querySelector('#popup input[name="contact-surname"]').value = item.pseudo;


		// website(s)
		var labelField = popupWrapper.querySelector('#popup label[for="contact-links"]');
		item.websites.forEach(function(s) {
			labelField.querySelector('input').value = s;
			var fieldParent = labelField.parentNode;
			labelField = labelField.cloneNode(true);
			labelField.querySelector('input').value = "";
			fieldParent.appendChild(labelField);
		});

		// Social media links(s)
		var labelField = popupWrapper.querySelector('#popup label[for="contact-social"]');
		item.social.forEach(function(s) {
			labelField.querySelector('input').value = s;
			var fieldParent = labelField.parentNode;
			labelField = labelField.cloneNode(true);
			labelField.querySelector('input').value = "";
			fieldParent.appendChild(labelField);
		});

		this.showContactEditPopup.duplicateLabelGroup = function(e) {
			var newLabel = e.parentNode.cloneNode(true);
			newLabel.querySelector('input').value = "";
			newLabel.querySelector('button.add').addEventListener('click', function() {
				_this.showContactEditPopup.duplicateLabelGroup(this);
			});
			newLabel.querySelector('button.rem').addEventListener('click', function() {
				_this.showContactEditPopup.removeLabelGroup(this);
			});
			e.parentNode.parentNode.appendChild(newLabel);
		}
		this.showContactEditPopup.removeLabelGroup = function(e) {
			e.parentNode.parentNode.removeChild(e.parentNode);
		}

		// Buttons « + »
		popupWrapper.querySelectorAll('#popup button.add').forEach(function(add) {
			add.addEventListener('click', function() {
				_this.showContactEditPopup.duplicateLabelGroup(this);
			});
		});

		// Buttons « × »
		popupWrapper.querySelectorAll('#popup button.rem').forEach(function(rem) {
			rem.addEventListener('click', function() {
				_this.showContactEditPopup.removeLabelGroup(this);
			});
		});

		popupWrapper.querySelector('#popup input[name="contact-notes"]').value = item.notes;
		popupWrapper.querySelector('#popup input[name="contact-other"]').value = item.other;

		popupWrapper.querySelector('#popup .contact-footer > .button-showmore').addEventListener('click', function() {
			popupWrapper.querySelectorAll('#popup .contact-content > .onshowmore').forEach(function(hidden) {
				hidden.classList.remove('onshowmore');
			});
			this.style.visibility = "hidden";
		});

		popupWrapper.querySelector('#popup .contact-footer > .button-submit').addEventListener('click', function() {
			_this.markAsEdited(item);
			popupWrapper.parentNode.removeChild(popupWrapper);
			document.body.classList.remove('noscroll');
		});

		this.domPage.appendChild(popupWrapper);
	}


	/**************************************
	 * Creates a new Contact, init it, display it and add it to list.
	*/
	this.addNewContact = function() {
		var date = this.initDate;
		var newCt = {
			"id": (new Date()).toLocalISOString().substr(0,19).replace(/[:T-]/g, ''),
			"title": '',
			"type": 'person',
			"fullname": '',
			"pseudo": '',
			"tel": [],
			"email": [],
			"address": {'nb':'','st':'','co':'','cp':'','ci':'','sa':'','cn':''},
			"birthday": '',
			"websites": [],
			"social": [],
			"label": '',
			"star": '0',
			"notes": '',
			"other": '',
			"img": '',
			"imgIsNew": '',
			"action": 'newContact'
		};


		// opens freshly created event popup
		this.showContactEditPopup(newCt);
	}



	/**************************************
	 * Deletes a Contact
	*/
	this.markAsDeleted = function(item) {
		if (!window.confirm(BTlang.questionSupprContact)) { return false; }
		item.action = 'deleteContact';

		// rebuilt table with contacts
		this.rebuiltContactsTable(this.contactList);

		// close popup
		document.getElementById('popup-wrapper').parentNode.removeChild(document.getElementById('popup-wrapper'));

		// raises global "updated" flag.
		this.raiseUpdateFlag(true);
	}


	/**************************************
	 * Mark a Contact object as having been edited
	*/
	this.markAsEdited = function(item) {
		var popup = document.getElementById('popup');

		// is Edit ?
		// search item in eventsList.
		// Can’t test on "item.action == newEvent", since an edited-new event remains "new" (not "edited").
		var isEdit = false;
		for (var i = 0, len = this.contactList.length ; i < len ; i++) {
			if (item.id == this.contactList[i].id) {
				var isEdit = true;
				break;
			}
		}

		item.title = popup.querySelector('input[name="contact-title"]').value;
		item.fullname = popup.querySelector('input[name="contact-fullname"]').value;
		item.label = popup.querySelector('input[name="contact-label"]').value;

		item.tel = new Array();
		popup.querySelectorAll('input[name="contact-phone"]').forEach(function(t) {
			if (t.value.trim().length !== 0) item.tel.push(t.value);
		});

		item.email = new Array();
		popup.querySelectorAll('input[name="contact-email"]').forEach(function(em) {
			if (em.value.trim().length !== 0) item.email.push(em.value);
		});

		item.address = {
			'nb': popup.querySelector('input[name="contact-nb"]').value,
			'st': popup.querySelector('input[name="contact-st"]').value,
			'co': popup.querySelector('input[name="contact-co"]').value,
			'cp': popup.querySelector('input[name="contact-cp"]').value,
			'ci': popup.querySelector('input[name="contact-ci"]').value,
			'sa': popup.querySelector('input[name="contact-sa"]').value,
			'cn': popup.querySelector('input[name="contact-cn"]').value,
		};
		item.birthday = popup.querySelector('input[name="contact-birthday"]').value.replace(/(....)\-(..)\-(..)/, "$1$2$3");
		item.pseudo = popup.querySelector('input[name="contact-surname"]').value;

		item.websites = new Array();
		popup.querySelectorAll('input[name="contact-links"]').forEach(function(li) {
			if (li.value.trim().length !== 0) item.websites.push(li.value);
		});
		item.social = new Array();
		popup.querySelectorAll('input[name="contact-social"]').forEach(function(so) {
			if (so.value.trim().length !== 0) item.social.push(so.value);
		});

		item.label = popup.querySelector('input[name="contact-label"]').value;
		item.star = '' // TODO
		item.notes = popup.querySelector('input[name="contact-notes"]').value;
		item.other = popup.querySelector('input[name="contact-other"]').value;

		// check image : image is only sent to server if it has been changer (since is is quite a bit of data)
		if (item.imgIsNew === true) {
			if ( (null !== popup.querySelector('.contact-img').style.backgroundImage.match(/^url\(\"(.*)\"\)$/)) 
			&& (undefined !== popup.querySelector('.contact-img').style.backgroundImage.match(/^url\(\"(.*)\"\)$/)[1]) ) {
				item.img = popup.querySelector('.contact-img').style.backgroundImage.match(/^url\(\"(.*)\"\)$/)[1];
			} else {
				item.imgIsNew = false;
			}
		}

		if (!isEdit) {
			this.contactList.push(item); // append it to the contactList
		}

		// rebuilt table with contacts
		this.rebuiltContactsTable(this.contactList);

		item.action = item.action || 'updateContact';

		// raises global "updated" flag.
		this.raiseUpdateFlag(true);
	}



	/**************************************
	 * Each change triggers a flag. If is(flag) : the save button displays
	*/
	this.raiseUpdateFlag = function(flagRaised) {
		if (flagRaised) {
			this.hasUpdated = true;
			document.getElementById('enregistrer').disabled = false;
		} else {
			this.hasUpdated = false;
			document.getElementById('enregistrer').disabled = true;
		}
	}


	/**************************************
	 * Loads an image for a contact.
	*/
	this.loadContactImage = function(e) {
		// create a virtual file input
		var input = document.createElement('input');
		input.type = 'file';

		// add .onchange()
		input.addEventListener('change', function() {
	
			// create a virtual image		
			var img = new Image();
			// on image load, we use canvas to resize image
			img.onload = function () {
				// we resize the image using Canvas.
				// smoth scalling is not yet fully supported. So we use multiple steps-down scalling (emulating bi-cubic filtering).
			 	//octx.imageSmoothingEnabled = true;
			 	//octx.imageSmoothingQuality = "high"

				// step 1
				var oc = document.createElement('canvas');
				oc.width = oc.height = 320;
				var octx = oc.getContext('2d');
				octx.drawImage(img, 0, 0, oc.width, oc.height);
				// step 2
				var oc2 = document.createElement('canvas');
				oc2.width = oc2.height = oc.height * 0.5;
				var octx2 = oc2.getContext('2d');
				octx2.drawImage(oc, 0, 0, oc2.width, oc2.height);
				// step 3
				var oc3 = document.createElement('canvas');
				oc3.width = oc3.height = oc2.height * 0.5;
				var octx3 = oc3.getContext('2d');
				octx3.drawImage(oc2, 0, 0, oc3.width, oc3.height);

				// we put the image (using base64) on the real <img> on the screen.
				e.target.style.backgroundImage = 'url('+oc3.toDataURL('image/jpeg', .8)+')';
			}

			var reader = new FileReader();
			reader.onload = function() {
				var dataURL = reader.result;
				img.src = dataURL;
			}
			reader.readAsDataURL(this.files[0]);
		});
		input.click();

	}

	/**************************************
	 * AJAX call to save events to DB
	*/
	this.saveContactsXHR = function() {
		loading_animation('on');
		// only keep modified contacts
		var toSaveContacts = Array();
		for (var i=0, len=this.contactList.length; i<len ; i++) {
			if (this.contactList[i].action && 0 !== this.contactList[i].action.length) {
				var ct = this.contactList[i];
				//ct.ymdhisDate = ev.date.substr(0,19).replace(/[:T-]/g, '');
				toSaveContacts.push(ct);
			}
		}

		// make a string out of it
		var contactsDataText = JSON.stringify(toSaveContacts);

		var notifDiv = document.createElement('div');
		// create XHR
		var xhr = new XMLHttpRequest();
		xhr.open('POST', 'ajax/contacts.ajax.php', true);

		// onload
		xhr.onload = function() {
			if (this.responseText.indexOf("Success") == 0) {
				loading_animation('off');
				_this.raiseUpdateFlag(false);
				// adding notif
				notifDiv.textContent = BTlang.confirmContactsSaved;
				notifDiv.classList.add('confirmation');
				document.getElementById('top').appendChild(notifDiv);

				// reset flags on contacts
				for (var i=0, len=toSaveContacts.length; i<len ; i++) {
					toSaveContacts[i].action = "";
				}
				return true;
			} else {
				loading_animation('off');
				// adding notif
				notifDiv.textContent = this.responseText;
				notifDiv.classList.add('no_confirmation');
				document.getElementById('top').appendChild(notifDiv);
				return false;
			}
		};

		// onerror
		xhr.onerror = function(e) {
			loading_animation('off');
			// adding notif
			notifDiv.textContent = 'AJAX Error ' +e.target.status;
			notifDiv.classList.add('no_confirmation');
			document.getElementById('top').appendChild(notifDiv);
			_this.notifNode.appendChild(document.createTextNode(this.responseText));
		};

		// prepare and send FormData
		var formData = new FormData();
		formData.append('token', token);
		formData.append('save_contacts', contactsDataText);
		xhr.send(formData);
	}


}
