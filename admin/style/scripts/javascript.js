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
 	var d = new Date(d.toString().substr(0, 4), d.toString().substr(4, 2) - 1, d.toString().substr(6, 2), d.toString().substr(8, 2), d.toString().substr(10, 2), d.toString().substr(12, 2));
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
	xhr.open('POST', 'commentaires.php');

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

function handleTags(form, inField, outField, tagList) {
	document.getElementById(form).addEventListener('submit', function(e){
		var liste = document.getElementById(tagList).getElementsByTagName('li');
		for (var i = 0, len=liste.length, iTag='' ; i<len ; i++) {
			iTag += liste[i].getElementsByTagName('span')[0].textContent+", ";
		}
		document.getElementById(outField).value = iTag.substr(0, iTag.length-2);
	});

	document.getElementById(inField).addEventListener('keydown', function(e){
		if (e.keyCode == 13 && this.value !== '') {
			e.preventDefault();
			document.getElementById(tagList).innerHTML += '<li class="tag"><span>'+document.getElementById(inField).value+'</span><a href="javascript:void(0)" onclick="this.parentNode.parentNode.removeChild(this.parentNode); return false;">×</a></li>';
			this.value = '';
			return false;
		}
	});

	document.getElementById(inField).addEventListener('blur', function(e){
		if (this.value !== '') {
			e.preventDefault();
			document.getElementById(tagList).innerHTML += '<li class="tag"><span>'+document.getElementById(inField).value+'</span><a href="javascript:void(0)" onclick="this.parentNode.parentNode.removeChild(this.parentNode); return false;">×</a></li>';
			this.value = '';
			return false;
		}
	});
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
	this.imgsList = document.getElementById('image-wall');

	// put some listeners
	// init the « folders sorting » buttons.
	document.querySelectorAll('#list-albums > button:not([id])').forEach(function(item) {
		item.addEventListener('click', function(e) { _this.albumSort(e); });
	});

	// "load all" button
	document.getElementById('load_all').addEventListener('click', function(e) { _this.loadAll(e); });

	// built page
	window.addEventListener("load", function() {
		_this.rebuiltWall();
	});

	// get Templates
	this.imgTemplate = this.imgsList.removeChild(this.imgsList.firstElementChild);

	/***********************************
	** The HTML tree builder :
	*/

	// Rebuilts the whole list of thumbnails
	this.rebuiltWall = function() {
		// empties the actual list
		//while (this.imgsList.firstChild) {
		//	 this.imgsList.removeChild(this.imgsList.firstChild);
		//}

		var countImg = this.imgList.length;
		if (0 === countImg) return false;

		var divList = document.createDocumentFragment();
		var listAlbums = document.getElementById('list-albums');

		// populates the new list
		this.imgList.forEach(function(item) {
			if (item.action == "deleteImg") return;

			var div = _this.imgTemplate.cloneNode(true);
			div.id = 'bloc_' + item.id;

			div.setAttribute('data-folder', item.folder);
			var tempImg = new Image();
			tempImg.onload = function() {
				div.removeAttribute('hidden');
				div.querySelector('img').src = this.src;
			};
			tempImg.src = item.thbPath;

			div.querySelector('img').width = item.w;
			div.querySelector('img').height = item.h;
			div.querySelector('a.imgShow').href = item.absPath + item.fileName;
			div.querySelector('a.imgEdit').href = 'fichiers.php?file_id='+item.id;
			div.querySelector('a.imgDL').href = item.absPath + item.fileName;
			div.querySelector('a.imgDL').download = item.fileName;

			div.querySelector('button.imgSuppr').addEventListener('click', function(e){ _this.deleteFile(item); } );
			div.addEventListener('click', function(e){ this.classList.toggle('show-buttons'); } );

			// populate .data-folder buttons
			listAlbums.querySelector('button[data-folder=""]').dataset.count++
			if (item.folder !== "") {
				var button = null;
				if (button = listAlbums.querySelector('button[data-folder="'+item.folder+'"]')) {
					button.dataset.count++
				} else {
					var button = document.createElement('BUTTON');
					button.dataset.count = 1;
					button.dataset.folder = item.folder;
					button.textContent = item.folder;
					button.addEventListener('click', function(e) { _this.albumSort(e); });
					listAlbums.insertBefore(button, document.getElementById('load_all'));
				}
			}
			divList.appendChild(div);
		});

		this.imgsList.appendChild(divList);
	}

	this.loadAll = function(e) {
		// prepare XMLHttpRequest
		var xhr = new XMLHttpRequest();
		xhr.open('POST', 'ajax/files.ajax.php');
		xhr.onload = function() {
			var newImgs = JSON.parse(this.responseText);

			// if not empty, add items to DomList
			var oldList = _this.imgList;
			_this.imgList = newImgs;
			_this.rebuiltWall();

			// and to logical JSON list
			_this.imgList = oldList;
			if (0 != newImgs.length) {
				for (var i = 0, len = newImgs.length ; i < len ; i++) {
					_this.imgList.push(newImgs[i]);
				}
			}

			// remove button.
			e.target.parentNode.removeChild(e.target);
		};
		// prepare and send FormData
		var formData = new FormData();
		formData.append('do', 'loadall');
		xhr.send(formData);

	}


	/***********************************
	** Sors the images with respect to the folders,
	*/
	this.albumSort = function (e) {
		var folder = e.target.dataset.folder;

		if ("" === folder) {
			this.imgsList.querySelectorAll(':scope > div').forEach(function(item) {
				item.removeAttribute('hidden');
			})
		}
		else {
			this.imgsList.querySelectorAll(':scope > div').forEach(function(item) {
				if (item.dataset.folder === folder) {
					item.removeAttribute('hidden');
				} else {
					item.setAttribute('hidden', "");
				}
			})			
		}

		document.querySelector('#list-albums > button.current').classList.remove('current');
		e.target.classList.add('current');
	}


	/***********************************
	** Sends a "delete" request to server,
	*/
	this.deleteFile = function (item) {
		// ask for popup confirmation
		if (!window.confirm(BTlang.questionSupprFichier)) { return false; }
		// prepare XMLHttpRequest
		var xhr = new XMLHttpRequest();
		xhr.open('POST', 'ajax/files.ajax.php');
		xhr.onload = function() {
			if (this.responseText == 'success') {
				_this.imgList.action = 'deleteImg';
				_this.imgsList.removeChild(_this.imgsList.getElementById('bloc_'.concat(item.id)));
			} else {
				alert(this.responseText+' '+item.id);
			}
		};
		// prepare and send FormData
		var formData = new FormData();
		formData.append('file_id', item.id);
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
	this.docList = JSON.parse(document.getElementById('json_docs').textContent);
	if (typeof this.docList == 'undefined' || !this.docList.length) return;


	// get some DOM elements
	this.docsList = document.getElementById('file-list').tBodies[0];


	// put some listeners
	// init the « folders sorting » buttons.
	document.querySelectorAll('#list-types > button').forEach(function(item) {
		item.addEventListener('click', function(e) { _this.typeSort(e); });
	});

	// built page
	window.addEventListener("load", function() {
		_this.rebuiltTable();
	});

	// get Templates
	this.docTemplate = this.docsList.removeChild(this.docsList.firstElementChild); this.docTemplate.removeAttribute('hidden');


	/***********************************
	** The HTML tree builder :
	*/

	// Rebuilts the whole list of files.
	this.rebuiltTable = function() {
		// empties the actual list
		while (this.docsList.firstChild) {
			this.docsList.removeChild(this.docsList.firstChild);
		}

		var countDoc = this.docList.length;
		if (0 === countDoc) return false;

		var rowList = document.createDocumentFragment();

		// populates the new list
		this.docList.forEach(function(item) {

			let row = _this.docTemplate.cloneNode(true);
			row.id = 'bloc_' + item.id;

			row.dataset.type = item.fileType;
			row.querySelector('img').alt = 'icon.';
			row.querySelector('img').src = 'style/imgs/filetypes/'+item.fileType+'.png';
			row.querySelector('td:nth-of-type(2) > a').textContent = item.fileName;
			row.querySelector('td:nth-of-type(2) > a').href = '?file_id='+item.id+'&amp;edit';
			row.querySelector('td:nth-of-type(3)').textContent = item.fileSize;
			row.querySelector('td:nth-of-type(4)').textContent = Date.dateFromYMDHIS(item.id).toLocaleString('fr', {year: "numeric", weekday: "short", month: "short", day: "numeric"});
			row.querySelector('td:nth-of-type(5) > a').href = item.absPath + item.fileName;
			row.querySelector('td:nth-of-type(5) > a').download = item.fileName;

			row.querySelector('td:nth-of-type(6) > a').addEventListener('click', function(e) {
				_this.deleteFile(item);
				e.preventDefault();
			});

			rowList.appendChild(row);
		});

		this.docsList.appendChild(rowList);
	}

	/***********************************
	** Sors the documents with respect to their type,
	*/
	this.typeSort = function (e) {
		var type = e.target.dataset.type;

		if ("" === type) {
			this.docsList.querySelectorAll(':scope > tr').forEach(function(item) {
				item.removeAttribute('hidden');
			})
		}
		else {
			this.docsList.querySelectorAll(':scope > tr').forEach(function(item) {
				if (item.dataset.type === type) {
					item.removeAttribute('hidden');
				} else {
					item.setAttribute('hidden', "");
				}
			});
		}

		document.querySelector('#list-types > button.current').classList.remove('current');
		e.target.classList.add('current');
	}


	/***********************************
	** Sends a "delete" request to server,
	*/
	this.deleteFile = function (item) {
		// ask for popup confirmation
		if (!window.confirm(BTlang.questionSupprFichier)) { return false; }
		// prepare XMLHttpRequest
		var xhr = new XMLHttpRequest();
		xhr.open('POST', 'ajax/files.ajax.php');
		xhr.onload = function() {
			if (this.responseText == 'success') {
				// remove image form page
				_this.docList.action = 'deleteFile';
				_this.docsList.removeChild(document.getElementById('bloc_'.concat(item.id)));
			} else {
				alert(this.responseText+' '+item.id);
			}
		};
		// prepare and send FormData
		var formData = new FormData();
		formData.append('file_id', item.id);
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

	// hasUpdated flag
	this.hasUpdated = false;

	/***********************************
	** Some properties & misc actions
	*/
	// init JSON List
	//var theJSON = JSON.parse(document.getElementById('json_rss').textContent);
	//this.feedList = theJSON.posts;
	//this.siteList = theJSON.sites;
	this.feedList = new Array();
	this.siteList = new Array();

	// init local "mark as read" buffer
	this.readQueue = {"count": "0", "urlList": []};

	// get some DOM elements
	this.notifNode = document.getElementById('message-return');
	this.domPage = document.getElementById('page');
	this.postsList = document.getElementById('post-list');
	this.feedsList = document.getElementById('feed-list');

	// get edit-popup template
	this.editFeedPopupTemplate = document.getElementById('popup-wrapper').parentNode.removeChild(document.getElementById('popup-wrapper'));
	this.editFeedPopupTemplate.removeAttribute('hidden');

	// get post/folder/site templates
	this.postTemplate = this.postsList.removeChild(this.postsList.firstElementChild);                 this.postTemplate.removeAttribute('hidden');
	this.siteTemplate = this.feedsList.removeChild(this.feedsList.querySelector('.feed-site'));       this.siteTemplate.removeAttribute('hidden');
	this.folderTemplate = this.feedsList.removeChild(this.feedsList.querySelector('.feed-folder'));   this.folderTemplate.removeAttribute('hidden');

	// init the « open-all » toogle-button.
	this.openAllButton = document.getElementById('openallitemsbutton');
	this.openAllButton.addEventListener('click', function(){ _this.openAll(); });

	// init the « list-all / list-favs / filst-today » events
	this.feedsList.querySelectorAll('.special > ul > li').forEach(function(li) {
		li.addEventListener('click', function(e) { _this.sortElements(e); });
	});

	// init the « hide feed-list » button
	document.getElementById('hide-side-nav').addEventListener('click', function(){ _this.feedsList.classList.toggle('hidden-list'); });

	// init the « mark as read » button.
	document.getElementById('markasread').addEventListener('click', function(){ _this.markAsRead(); });

	// init the « refresh all » button event
	document.getElementById('refreshFeeds').addEventListener('click', function(e){ _this.refreshAllFeeds(e); });

	// init the « reload JSON » button event
	document.getElementById('reloadFeeds').addEventListener('click', function(e){ _this.reloadJsonData(e); });

	// init the « delete old » button
	document.getElementById('deleteOld').addEventListener('click', function(){ _this.deleteOldFeeds(); });

	// init the « add new feed » button
	document.getElementById('fab').addEventListener('click', function(){ _this.addNewFeed(); });


	// Global Page listeners
	// onkeydown : detect "open next/previous" action with keyboard
	window.addEventListener('keydown', function(e) {
		_this.kbActionHandle(e);
	});

	// beforeunload : to send a "mark as read" request before unloading the page
	window.addEventListener("beforeunload", function() {
		if (_this.readQueue.urlList.length == 0) return true;
		var formData = new FormData();
		formData.append('token', token);
		formData.append('mark-as-read', 'postlist');
		formData.append('mark-as-read-data', JSON.stringify(_this.readQueue.urlList));
		navigator.sendBeacon('ajax/rss.ajax.php', formData);
	});

	// if the tab is hidden (or if the browser is hidden (on mobile): send a "mark as read" request)
	document.addEventListener("visibilitychange", function() {
		if (document.visibilityState == 'hidden' && _this.readQueue.urlList.length !== 0) {
			_this.markAsReadXHR('postlist', JSON.stringify(_this.readQueue.urlList));
		}
	});

	// allows a "popup close" when the user goes back 1 time in history (esp. on Android)
	window.addEventListener("popstate", function(e) {
		_this.closePopup();
	});

	// built page
	window.addEventListener("load", function() {
		_this.reloadJsonData();
	});


	/* Feedback notif form scripts (handles the popup state: visible, hiding…) */
	this.backgroundWorkingPopup = function(state) {
		var notifPopup = document.getElementById('popup-notif');
		var spinner = document.getElementById('counter');

		// started : popup shows + spinner is running
		if (state === 'started') {
			notifPopup.classList.add('visible');
			spinner.classList.add('rotating');
		}

		// finished : working is done. Hide popup.
		if (state === 'finished') {
			spinner.classList.remove('rotating');
			notifPopup.classList.remove('visible');
		}

		// waiting for dissapearing
		if (state === 'waiting') {
			spinner.classList.remove('rotating');
			notifPopup.classList.add('fading');
			notifPopup.addEventListener('animationend', function(e) {
				notifPopup.classList.remove('fading');
				_this.backgroundWorkingPopup('finished');
			}, {'once': true});

		}
	}

	/***********************************
	** The HTML builder methods
	*/

	// builts the whole list of posts.
	this.rebuiltPostsTree = function() {
		// empties the actual list
		while (this.postsList.firstChild) {
			 this.postsList.removeChild(this.postsList.firstChild);
		}
		var countPosts = this.feedList.length;
		if (0 === countPosts) return false;

		var liList = document.createDocumentFragment();

		var dateTimeToday = new Date(); dateTimeToday.setHours(0); dateTimeToday.setMinutes(0); dateTimeToday.setSeconds(0);
		var dateTimeFormat = new Intl.DateTimeFormat('fr', {year: "numeric", weekday: "short", month: "short", day: "numeric", hour: "numeric", minute: "numeric"});
		var timeFormat = new Intl.DateTimeFormat('fr', {hour: "numeric", minute: "numeric"});

		// populates the new list
		this.feedList.forEach(function(item) {

			var li = _this.postTemplate.cloneNode(true);
			var dateTimeArticle = Date.dateFromYMDHIS(item.datetime);

			li.id = 'i_'+item.id;
			li.setAttribute('data-id', item.id);
			li.setAttribute('data-folder', item.folder);
			li.setAttribute('data-datetime', item.datetime);
			li.setAttribute('data-sitehash', item.feedhash);
			li.setAttribute('data-is-fav', item.fav);
			if (0 === item.statut) { li.classList.add('read'); }
			li.querySelector('.post-head > .folder').textContent = item.folder;
			li.querySelector('.post-head > .post-title').href = item.link;
			li.querySelector('.post-head > .post-title').title = item.title;
			li.querySelector('.post-head > .post-title').textContent = item.title;
			li.querySelector('.post-head > .meta > .site').textContent = item.sitename;
			li.querySelector('.post-head > .meta > .share > .lien-share').href = 'links.php?url='+encodeURIComponent(item.link);
			li.querySelector('.post-head > .meta > .share > .lien-open').href = item.link;
			li.querySelector('.post-head > .meta > .share > .lien-mail').href = 'mailto:?&subject='+ encodeURIComponent(item.title) + '&body=' + encodeURIComponent(item.link);

			if (dateTimeArticle < dateTimeToday) {
				li.querySelector('.post-head > .meta > .date').textContent = dateTimeFormat.format(dateTimeArticle);
			} else {
				li.querySelector('.post-head > .meta > .date').textContent = timeFormat.format(dateTimeArticle);
			}

			li.querySelector('.post-head > .post-title').addEventListener('click', function(e){ if(!_this.openThisItem(item)) e.preventDefault(); } );
			li.querySelector('.post-head > .lien-fav').addEventListener('click', function(e){ _this.markAsFav(item); e.preventDefault(); } );
			
			//li.querySelector('.rss-item-content').appendChild(document.createComment(item.content));
			liList.appendChild(li);
		});

		this.postsList.appendChild(liList);

		// displays the number of items (local counter)
		var count = document.querySelector('#post-counter');
		count.textContent = countPosts;

		return false;
	}

	// builts the whole list of sites
	this.rebuiltSitesTree = function() {
		// remove existing entries (if any)
		this.feedsList.querySelectorAll(':scope > li:not(.special)').forEach(function (li) {
			li.parentNode.removeChild(li);
		});

		var ulList = document.createDocumentFragment();

		// populates the new list
		this.siteList.forEach(function(item) {

			var li = _this.siteTemplate.cloneNode(true);
			li.style.backgroundImage = "url(../favatar.php?w=favicon&q="+((new URL(item.link)).hostname)+')';
			li.setAttribute('data-nbrun', item.nbrun);
			li.setAttribute('data-feed-hash', item.id);
			if (0 !== item.iserror) { li.classList.add('feed-error'); }
			li.appendChild(document.createTextNode(item.title));

			li.addEventListener('click', function(e) { _this.sortElements(e); });
			li.querySelector(':scope > button').addEventListener('click', function(e) { e.stopPropagation(); _this.showFeedEditPopup(item); });

			if ("" !== item.folder) {
				// check if folder UL already exists
				var folderUl = ulList.querySelector('li[data-folder="'+item.folder+'"]');
				if (!folderUl) {
					// if not create it
					var folderUl = _this.folderTemplate.cloneNode(true);
					folderUl.addEventListener('click', function(e) { _this.sortElements(e); });
					folderUl.querySelector('.unfold').addEventListener('click', function(e) { 
						e.stopPropagation();
						this.parentNode.classList.toggle('open');
					 } ) ;

					folderUl.setAttribute('data-folder', item.folder);
					folderUl.setAttribute('data-nbrun', 0);
					folderUl.insertBefore(document.createTextNode(item.folder), folderUl.firstElementChild);

					var beforeNode = ulList.firstChild;

					// place new folder such as forders get sorted.
					while (beforeNode && beforeNode.classList.contains('feed-folder')) {
						if (beforeNode.getAttribute('data-folder') < item.folder) {
							beforeNode = beforeNode.nextElementSibling;
						} else break;
					}

					ulList.insertBefore(folderUl, beforeNode);

				}
				// if exists, append site to folder
				folderUl.querySelector('ul').appendChild(li);

				folderUl.setAttribute('data-nbrun', parseInt(folderUl.getAttribute('data-nbrun'), 10)+parseInt(item.nbrun, 10));

			}
			// else, append to normal list
			else {
				ulList.appendChild(li);
			}
		});
		this.feedsList.appendChild(ulList);
	
		return false;
	}

	/************************************
	** Methos to handle popup
	*/

	// show the "edit feed" popup
	this.showFeedEditPopup = function (item) {

		// new popup
		var popupWrapper = this.editFeedPopupTemplate.cloneNode(true);
		popupWrapper.querySelector('.popup-edit-feed').id = 'popup';
		var popup = popupWrapper.querySelector('#popup');
		popup.removeAttribute('hidden');

		// this allows closing the popup with the "back" button (espacially on Android)
		if (history.state === null) history.pushState({'popupOpen': true}, null, window.location.pathname + '#popup');

		popupWrapper.addEventListener('click', function(e) {
			// clic is on wrapper (back drop) but not the popup
			if (e.target == this) {
				_this.closePopup();
			}
		});

		document.body.classList.add('noscroll');

		popup.querySelector('.feed-content-error').textContent = item.iserror || '';
		popup.querySelector('.feed-content-lastpost > time').textContent = Date.dateFromYMDHIS(item.time).toLocaleDateString('fr', {weekday: "long", year: "numeric", month: "long", day: "numeric", hour: "numeric", minute: "numeric"});
		popup.querySelector('.feed-content input[name="feed-url"]').value = item.link;
		popup.querySelector('.feed-content input[name="feed-url"]').style.backgroundImage = "url(../../../favatar.php?w=favicon&q="+((new URL(item.link)).hostname)+')';
		popup.querySelector('.feed-content input[name="feed-title"]').value = item.title;
		popup.querySelector('.feed-content input[name="feed-folder"]').value = item.folder;

		popup.querySelector('.popup-title > .button-cancel').addEventListener('click', function() {
			_this.closePopup();
		});

		popup.querySelector('.feed-footer > .button-submit').addEventListener('click', function() {
			_this.saveEditFeed(item);
			_this.closePopup();
		});

		popup.querySelector('.feed-footer > .button-delete').addEventListener('click', function() {
			if (!window.confirm(BTlang.questionSupprFlux)) { return false; }
			_this.deleteFeed(item);
			_this.closePopup();
		});

		this.domPage.appendChild(popupWrapper);
	}


	// close actual popup
	this.closePopup = function() {
		var popupWrapper = document.getElementById('popup-wrapper');
		if (popupWrapper) popupWrapper.parentNode.removeChild(popupWrapper);
		document.body.classList.remove('noscroll');
	}

	/***********************************
	** Methods to "open/close" elements (all, one, next…)
	*/

	// close item 
	this.closeThisPost = function(post) {
		var contentDiv = post.querySelector('.rss-item-content');
		if (contentDiv.firstChild) {
			while (contentDiv.firstChild) { contentDiv.removeChild(contentDiv.firstChild); }
		}
		post.classList.remove('open-post');
	}

	// open ALL the items
	this.openAll = function() {
		var posts = this.postsList.querySelectorAll('li:not([hidden])');

		// unfold items
		if (!this.openAllButton.classList.contains('unfold')) {
			this.feedList.forEach(function(item) {
				var post = _this.postsList.querySelector('li[data-id="'+item.id+'"]:not([hidden])');
				if (post) {
					// opens this post
					post.classList.add('open-post');
					// append content
					post.querySelector('.rss-item-content').innerHTML = item.content;
				}
			});
			this.openAllButton.classList.add('unfold');
		}
		// refold them back
		else {
			var posts = this.postsList.querySelectorAll('li.open-post');
			posts.forEach(function(post) {
				_this.closeThisPost(post);
			});
			this.openAllButton.classList.remove('unfold');
		}



		/*
		if (!this.openAllButton.classList.contains('unfold')) {
			posts.forEach(function(post) {
				post.classList.add('open-post');
				var content = post.querySelector('.rss-item-content');
				if (content.childNodes[0] && content.childNodes[0].nodeType == 8) {
					content.innerHTML = content.childNodes[0].data;
				}
			});
			this.openAllButton.classList.add('unfold');
		} else {
			posts.forEach(function(post) {
				post.classList.remove('open-post');
			});
			this.openAllButton.classList.remove('unfold');
		}*/
		return false;
	}

	// open clicked item
	this.openThisItem = function(item) {
		var post = this.postsList.querySelector('li[data-id="'+item.id+'"]');
		if (post.classList.contains('open-post')) { return true; }

		// close previously opened posts
		this.postsList.querySelectorAll('.open-post').forEach(function(post) {
			_this.closeThisPost(post);
		});		
		this.openAllButton.classList.remove('unfold');

		// opens this post
		post.classList.add('open-post');

		// unveil the content
		var content = post.querySelector('.rss-item-content');
		//if (content.childNodes[0].nodeType == 8) {
		//	content.innerHTML = content.childNodes[0].data;
		//}
		content.innerHTML = item.content;

		// jump to post (anchor + 120px)
		var rect = post.getBoundingClientRect();
		var isVisible = ( (rect.top < 144) || (rect.bottom > window.innerHeight) ) ? false : true ;
		if (!isVisible) {
			window.location.hash = post.id;
			window.scrollBy(0, -144);
		}

		// mark as read in DOM and saves for mark as read in DB
		if (!post.classList.contains('read')) {
			this.markAsReadPost(item);
			post.classList.add('read');
		}
		return false;
	}

	// handle keyboard actions
	this.kbActionHandle = function(e) {
		// down
		if (e.keyCode == '40' && e.ctrlKey) {
			e.preventDefault();

			// first post to open
			var toOpenPost = this.postsList.querySelector('li.open-post ~ li:not([hidden])');
			// ... or first post if none are open
			if (!toOpenPost) { var toOpenPost = this.postsList.querySelector('li:not([hidden])'); }
			// ... or return if no post in list
			if (!toOpenPost) return false;

			// find item
			var item = this.feedList.find(function(i) {
				return (i.id == toOpenPost.dataset.id);
			});

			this.openThisItem(item);
		}
		// up
		if (e.keyCode == '38' && e.ctrlKey) {
			e.preventDefault();
			// actually open post
			var theOpenPost = this.postsList.querySelector('li.open-post');
			// ... or return if no open post yet
			if (!theOpenPost) return false;
			// finds the previous non-hidden post
			while (theOpenPost.previousSibling && theOpenPost.previousSibling.hasAttribute('hidden')) {
				theOpenPost = theOpenPost.previousSibling;
			}

			if (theOpenPost.previousSibling) {
				toOpenPost = theOpenPost.previousSibling;

				// find item
				var item = this.feedList.find(function(i) {
					return (i.id == toOpenPost.dataset.id);
				});

				this.openThisItem(item);
			}
		}
	}


	/***********************************
	** Method to "sort" elements (by site, folder, favs…)
	*/

	this.sortElements = function (e) {
		// prevent a clic on a "site" to go to a parent "folder"
		e.stopPropagation();

		// sort all feeds
		if (e.target.classList.contains('all-feeds')) {
			this.postsList.querySelectorAll('#post-list > li').forEach(function(post) {
				_this.closeThisPost(post);
				post.removeAttribute('hidden');
			});
		}

		// sort by site
		else if (e.target.classList.contains('feed-site')) {
			var theSite = e.target.getAttribute('data-feed-hash');
			this.postsList.querySelectorAll('#post-list > li').forEach(function(post) {
				_this.closeThisPost(post);
				if (post.getAttribute('data-sitehash') === theSite) {
					post.removeAttribute('hidden');
				} else {
					post.setAttribute('hidden', '');
				}
			});
		}

		// sort by folder
		else if (e.target.classList.contains('feed-folder')) {
			var theFolder = e.target.getAttribute('data-folder');
			this.postsList.querySelectorAll('#post-list > li').forEach(function(post) {
				_this.closeThisPost(post);
				if (post.getAttribute('data-folder') === theFolder) {
					post.removeAttribute('hidden');
				} else {
					post.setAttribute('hidden', '');
				}
			});
		}

		// sort favs
		else if (e.target.classList.contains('fav-feeds')) {
			this.postsList.querySelectorAll('#post-list > li').forEach(function(post) {
				_this.closeThisPost(post);
				if (post.getAttribute('data-is-fav') == 1) {
					post.removeAttribute('hidden');
				} else {
					post.setAttribute('hidden', '');
				}
			});
		}

		// sort today
		else if (e.target.classList.contains('today-feeds')) {

			this.postsList.querySelectorAll('#post-list > li').forEach(function(post) {
				_this.closeThisPost(post);
					var d = new Date();
				var ymd000 = '' + d.getFullYear() + ('0' + (d.getMonth()+1)).slice(-2) + ('0' + d.getDate()).slice(-2) + '000000';

				if (post.getAttribute('data-datetime') >= ymd000) {
					post.removeAttribute('hidden');
				} else {
					post.setAttribute('hidden', '');
				}
			});
		}

		if (this.feedsList.querySelector('.active-site')) {
			this.feedsList.querySelector('.active-site').classList.remove('active-site');
		}

		e.target.classList.add('active-site');
		window.location.hash = '';
		this.openAllButton.classList.remove('unfold');
		this.feedsList.classList.remove('hidden-list'); // on mobile: hide the sites 
	}


	/***********************************
	** Methods to "mark as read" item in the local list and on screen
	*/
	this.markAsRead = function() {
		var markWhat = document.querySelector('.active-site');

		// Mark ALL as read.
		if (markWhat.classList.contains('all-feeds')) {
			// for "all" feeds, ask confirmation
			if (!confirm("Tous les éléments seront marqués comme lus ?")) { // TODO : $lang
				return false;
			}
			// send XHR
			if (!this.markAsReadXHR('all', 'all')) return false;

			// mark items as read in list
			this.feedList.forEach(function(item) {
				item.statut = 0;
			});

		}

		// Mark one FOLDER as read
		else if (markWhat.classList.contains('feed-folder')) {
			var folder = markWhat.dataset.folder;

			// send XHR
			if (!this.markAsReadXHR('folder', folder)) return false;

			// mark 0 for that folder
			markWhat.dataset.nbrun = 0;

			// mark 0 for the sites in that folder
			markWhat.querySelectorAll('li.feed-site:not([data-nbrun="0"])').forEach(function(site) {
				site.dataset.nbrun = 0;
			});

			// mark items as "read" in list
			for (var i = 0, len = this.feedList.length ; i < len ; i++) {
				if (this.feedList[i].folder == folder) this.feedList[i].statut = 0;
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

			// mark 0 for that sites
			markWhat.dataset.nbrun = 0;

			// mark items as "read" in list
			for (var i = 0, len = this.feedList.length ; i < len ; i++) {
				if (this.feedList[i].feedhash == siteHash) this.feedList[i].statut = 0;
			}

		}

		// mark items as "read" on screen
		this.postsList.querySelectorAll('#post-list > li:not([hidden])').forEach(function(post) {
			post.classList.add('read');
		});

	}

	// This is called when a post is opened (for the first time)
	this.markAsReadPost = function(item) {
		// add thePost to local read posts buffer
		this.readQueue.urlList.push(item.id);
		// if 10 items in queue, send XHR request and reset list to zero.
		if (this.readQueue.urlList.length >= 10) {
			var list = this.readQueue.urlList;
			this.markAsReadXHR('postlist', JSON.stringify(list));
			this.readQueue.urlList = [];
		}

		// mark as read in list
		item.statut = 0;

		// decrement site "unread"
		this.feedsList.querySelector('li[data-feed-hash="'+item.feedhash+'"]').dataset.nbrun -= 1;

		// decrement folder (if any)
		if (item.folder !== "") {
			this.feedsList.querySelector('li[data-folder="'+item.folder+'"]').dataset.nbrun -= 1;
		}
	}


	/***********************************
	** Methods to init and send the XHR request
	*/
	// Mark as read by user input.
	this.markAsReadXHR = function(marType, marWhat) {
		this.backgroundWorkingPopup('started');
		var xhr = new XMLHttpRequest();
		xhr.open('POST', 'ajax/rss.ajax.php', true);

		// onload
		xhr.onload = function() {
			_this.backgroundWorkingPopup('finished');
			_this.notifNode.textContent = '';
		};

		// onerror
		xhr.onerror = function(e) {
			_this.notifNode.textContent = 'Error ' + e.target.status + ' ' +this.responseText;
			_this.backgroundWorkingPopup('finished');
		};

		// prepare and send FormData
		var formData = new FormData();
		formData.append('token', token);
		formData.append('mark-as-read', marType);
		formData.append('mark-as-read-data', marWhat);
		xhr.send(formData);

		return true;
	}

	// Mark a post a favorite
	this.markAsFav = function(item) {
		this.backgroundWorkingPopup('started');

		item.fav = 1 - item.fav;
		this.postsList.querySelector('li[data-id="'+item.id+'"]').dataset.isFav = item.fav;

		var xhr = new XMLHttpRequest();
		xhr.open('POST', 'ajax/rss.ajax.php');

		// onload
		xhr.onload = function() {
			_this.backgroundWorkingPopup('finished');
			_this.notifNode.textContent = '';
		};

		// onerror
		xhr.onerror = function(e) {
			_this.notifNode.textContent = 'Error ' + e.target.status + ' ' +this.responseText;
			_this.backgroundWorkingPopup('finished');
		};

		// prepare and send FormData
		var formData = new FormData();
		formData.append('token', token);
		formData.append('mark-as-fav', 1);
		formData.append('url', item.id);
		xhr.send(formData);
	}


	// This requests the server to download the feeds and send the new ones to browser
	// This call is long, also it updates gradually on screen.
	this.refreshAllFeeds = function(e) {
		var _refreshButton = e.target;
		// if refresh ongoing : abbord !
		if (_refreshButton.dataset.refreshOngoing == 1) {
			return false;
		} else {
			_refreshButton.dataset.refreshOngoing = 1;
		}
		// else refresh
		this.backgroundWorkingPopup('started');

		// prepare XMLHttpRequest
		var xhr = new XMLHttpRequest();
		xhr.open('POST', 'ajax/rss.ajax.php');

		// Counts the feeds that have been updated already and displays it like « 10/42 feeds »
		var glLength = 0;
		xhr.onprogress = function() {
			if (glLength != this.responseText.length) {
				var posSpace = (this.responseText.substr(0, this.responseText.length-1)).lastIndexOf(" ");
				_this.notifNode.textContent = this.responseText.substr(posSpace);
				glLength = this.responseText.length;
			}
		}

		// when finished : displays amount of items gotten.
		xhr.onload = function(e) {
			var resp = this.responseText;


			// grep new feeds
			var newJson = JSON.parse(resp.substr(resp.indexOf("{")));
			var newFeeds = newJson.posts;
			this.siteList = newJson.sites

			// update status
			_this.notifNode.textContent = newFeeds.length+' nouveaux éléments'; // TODO $[lang]

			// if not empty, add items to list
			if (0 != newFeeds.length) {
				for (var i = 0, len = newFeeds.length ; i < len ; i++) {
					_this.feedList.unshift(newFeeds[len-1-i]); // "len-1-i" for reverse order
				}

				// rebuilt Ul-Li to display the new elements.
				_this.rebuiltPostsTree();
				_this.rebuiltSitesTree();

				// hide all items but the recently added ones
				_this.postsList.querySelectorAll('#post-list > li').forEach(function(post) {
					_this.closeThisPost(post);
					post.setAttribute('hidden', '');

					var item = newFeeds.find(function(i) {
						return (i.id === post.getAttribute('data-id'));
					});
					if (item) post.removeAttribute('hidden');

				});
			}

			_refreshButton.dataset.refreshOngoing = 0;
			_this.backgroundWorkingPopup('waiting');
		};

		xhr.onerror = function(e) {
			_this.notifNode.textContent = 'Error ' + e.target.status + ' ' +this.responseText;
			_refreshButton.dataset.refreshOngoing = 0;
			_this.backgroundWorkingPopup('waiting');
		};

		// prepare and send FormData
		var formData = new FormData();
		formData.append('token', token);
		formData.append('refresh_all', 1);
		xhr.send(formData);
	}

	// This requests the server to send it the latest feeds it has in DB.
	// It’s a two time request : first, the meta-data is retreived, then the contents of the feeds.
	//  This behaviour makes it look/feel faster.
	this.reloadJsonData = function(e) {
		this.backgroundWorkingPopup('started');

		if (this.readQueue.urlList.length !== 0) {
			this.markAsReadXHR('postlist', JSON.stringify(this.readQueue.urlList));
		}

		var xhr = new XMLHttpRequest();
		xhr.open('POST', 'ajax/rss.ajax.php'+window.location.search);
		var formData = new FormData();
		formData.append('token', token);
		formData.append('get_initial_data', 1);

		// when finished : builts wall of objects
		xhr.onload = function() {
			//var resp = this.responseText;
			var resp = (JSON.parse(this.responseText));
			_this.feedList = resp.posts;
			_this.siteList = resp.sites;

			_this.rebuiltSitesTree();
			_this.rebuiltPostsTree();


			// secondth request once the first is loaded
			var xhr2 = new XMLHttpRequest();
			xhr2.open('POST', 'ajax/rss.ajax.php');

			xhr2.onload = function() {
				//var resp = this.responseText;
				var resp = (JSON.parse(this.responseText));


				var feedsContents = resp.posts;

				for (var i = 0, len = _this.feedList.length ; i < len ; i++) {

					_this.feedList[i].content = feedsContents.find(function(post) {
						if (post.id == _this.feedList[i].id) {
							return post.content;
						}
					});

					_this.feedList[i].content = feedsContents[i].content;

				}

				_this.notifNode.textContent = '';
				_this.backgroundWorkingPopup('finished');
			}

			var formData2 = new FormData();
			formData2.append('token', token);
			formData2.append('get_initial_data', 1);
			formData2.append('only_content', 1);
			xhr2.send(formData2);

		};

		xhr.onerror = function(e) {
			_this.notifNode.textContent = 'Error ' + e.target.status + ' ' +this.responseText;
			_this.backgroundWorkingPopup('finished');
		};

		xhr.send(formData);
	}


	// Method to delete old feeds from DB
	this.deleteOldFeeds = function() {
		if (!confirm("Les vieilles entrées seront supprimées ?")) {
			return false;
		}

		this.backgroundWorkingPopup('started');
		var xhr = new XMLHttpRequest();
		xhr.open('POST', 'ajax/rss.ajax.php');

		xhr.onload = function() {
			var resp = this.responseText;
			if (resp.indexOf("Success") == 0) {
				_this.notifNode.textContent = BTlang.confirmFeedClean;
			}
			_this.backgroundWorkingPopup('waiting');
		};
		xhr.onerror = function(e) {
			_this.notifNode.textContent = 'Error ' + e.target.status + ' ' +this.responseText;
			_this.backgroundWorkingPopup('waiting');
		};

		// prepare and send FormData
		var formData = new FormData();
		formData.append('token', token);
		formData.append('delete_old', 1);
		xhr.send(formData);
	}

	// Method to add a new feed (promt for URL and send to server)
	this.addNewFeed = function() {
		var newLink = window.prompt(BTlang.rssJsAlertNewLink, '');
		// if empty string : stops here
		if (!newLink) return false;
		// ask folder
		var newFolder = window.prompt(BTlang.rssJsAlertNewLinkFolder, '');

		this.backgroundWorkingPopup('started');

		var xhr = new XMLHttpRequest();
		xhr.open('POST', 'ajax/rss.ajax.php');

		xhr.onload = function(e) {
			var resp = this.responseText;
			// if error : stops
			if (resp.indexOf("Success") !== -1) {
				_this.notifNode.textContent = 'FLux ajouté.';
			} else {
				_this.notifNode.textContent = 'Error ' + e.target.status + ' ' +resp;
			}
			_this.backgroundWorkingPopup('waiting');
		};

		xhr.onerror = function(e) {
			_this.notifNode.textContent = 'Error ' + e.target.status + ' ' +this.responseText;
			_this.backgroundWorkingPopup('waiting');
		};

		// prepare and send FormData
		var formData = new FormData();
		formData.append('token', token);
		formData.append('add-feed', newLink);
		formData.append('add-feed-folder', newFolder);
		xhr.send(formData);
	}

	this.saveEditFeed = function(item) {
		var popup = document.getElementById('popup');
		this.backgroundWorkingPopup('started');

		var xhr = new XMLHttpRequest();
		xhr.open('POST', 'ajax/rss.ajax.php');

		var toSaveFeed = new Object();
		toSaveFeed.id = item.id;
		toSaveFeed.folder = popup.querySelector('.feed-content input[name="feed-folder"]').value
		toSaveFeed.title = popup.querySelector('.feed-content input[name="feed-title"]').value
		toSaveFeed.link = popup.querySelector('.feed-content input[name="feed-url"]').value
		toSaveFeed.action = 'edited'

		// make a string out of it
		var feedDataText = JSON.stringify(toSaveFeed);

		xhr.onload = function(e) {
			var resp = this.responseText;
			if (resp.indexOf("Success") != -1) {
				_this.notifNode.textContent = 'FLux édité.';
			} else {
				_this.notifNode.textContent = 'Error ' + e.target.status + ' ' +resp;
			}
			_this.backgroundWorkingPopup('waiting');

			// update info in list
			var oldFolder = item.folder;
			var oldTitle = item.title;
			item.folder = popup.querySelector('.feed-content input[name="feed-folder"]').value;
			item.title = popup.querySelector('.feed-content input[name="feed-title"]').value;
			item.link =  popup.querySelector('.feed-content input[name="feed-url"]').value;

			// if item has been edited, rebuilt sites/post trees
			if (oldFolder !== item.folder || oldTitle !== item.title) {
				_this.rebuiltSitesTree();
				// todo : change sitename / folder on posts (if changed)
				//_this.rebuiltPostsTree();
			}

			_this.backgroundWorkingPopup('waiting');
		};

		xhr.onerror = function(e) {
			_this.notifNode.textContent = 'Error ' + e.target.status + ' ' +this.responseText;
			_this.backgroundWorkingPopup('waiting');
		};
		// prepare and send FormData
		var formData = new FormData();
		formData.append('token', token);
		formData.append('edit-feed-list', feedDataText);
		xhr.send(formData);
	}

	this.deleteFeed = function(item) {
		var popup = document.getElementById('popup');
		this.backgroundWorkingPopup('started');

		var xhr = new XMLHttpRequest();
		xhr.open('POST', 'ajax/rss.ajax.php');

		var toSaveFeed = new Object();
		toSaveFeed.id = item.id;
		toSaveFeed.action = 'delete'

		// make a string out of it
		var feedDataText = JSON.stringify(toSaveFeed);

		xhr.onload = function(e) {
			var resp = this.responseText;
			// if error : stops
			if (resp.indexOf("Success") == 0) {
				_this.notifNode.textContent = 'FLux supprimé.';
			} else {
				_this.notifNode.textContent = 'Error ' + e.target.status + ' ' +resp;
			_this.backgroundWorkingPopup('waiting');
			}
	
			// delete feed in list
			_this.siteList.splice(_this.siteList.indexOf(item), 1);

			// delete feed on screen
			var li = _this.feedsList.querySelector('li.feed-site[data-feed-hash="' + item.id + '"]');
			if (li.parentNode.parentNode.dataset.folder) { li.parentNode.parentNode.dataset.nbrun -= item.nbrun; }
			li.parentNode.removeChild(li);

			_this.postsList.querySelectorAll('#post-list > li[data-sitehash="'+item.id+'"]').forEach(function(post) {
				post.parentNode.removeChild(post);
			});
			// todo  :remove posts from list object (and then rebuilt the li.list). On rebuilst, test for "current active site"
			_this.backgroundWorkingPopup('waiting');
		};

		xhr.onerror = function(e) {
			_this.backgroundWorkingPopup('waiting');
			// adding notif
			_this.notifNode.textContent = 'Error ' + e.target.status + ' ' +this.responseText;
		};
		// prepare and send FormData
		var formData = new FormData();
		formData.append('token', token);
		formData.append('edit-feed-list', feedDataText);
		xhr.send(formData);
	}

	/**********************
	* Registers service worker (for offline capability)
	*/
	//if ('serviceWorker' in navigator) {
	//	navigator.serviceWorker
	//		.register('service-worker.js')
	//		.then(function() { console.log('Service Worker Registered'); });
	//}
};



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

	// get popup wrapper template
	this.notePopupTemplate = document.getElementById('popup-wrapper').parentNode.removeChild(document.getElementById('popup-wrapper'));
	this.notePopupTemplate.removeAttribute('hidden');

	// buttons
	document.getElementById('post-new-note').addEventListener('click', function(e) { _this.addNewNote(); });

	// Global Page listeners
	// beforeunload : warns the user if some data is not saved
	window.addEventListener("beforeunload", function(e) {
		if (_this.hasUpdated) {
			e.returnValue = BTlang.questionQuitPage;
		}
		else { return true; }
	});

	window.addEventListener("popstate", function(e) {
		_this.closePopup();
	});

	// built page
	window.addEventListener("load", function() {
		_this.rebuiltNotesWall(_this.notesList);
	});

	/* Feedback notif form scripts (handles the popup state: visible, hiding…) */
	this.backgroundWorkingPopup = function(state) {
		var notifPopup = document.getElementById('popup-notif');
		var spinner = document.getElementById('counter');

		// started : popup shows + spinner is running
		if (state === 'started') {
			notifPopup.classList.add('visible');
			spinner.classList.add('rotating');
		}

		// finished : working is done. Hide popup.
		if (state === 'finished') {
			spinner.classList.remove('rotating');
			notifPopup.classList.remove('visible');
		}

		// waiting for dissapearing
		if (state === 'waiting') {
			spinner.classList.remove('rotating');
			notifPopup.classList.add('fading');
			notifPopup.addEventListener('animationend', function(e) {
				notifPopup.classList.remove('fading');
				_this.backgroundWorkingPopup('finished');
			}, {'once': true});

		}
	}

	/***********************************
	** The HTML tree builder :
	** Builts the whole list of notes.
	*/
	this.rebuiltNotesWall = function(NotesData) {
		if (0 === NotesData.length) return false;

		var notesPinned = document.getElementById('are-pinned');
		var notesUnPinned = document.getElementById('are-unpinned');

		// populates the new list
		for (let i = 0, len = NotesData.length ; i < len ; i++) {
			let item = NotesData[i];

			var div = this.noteTemplate.cloneNode(true);
			div.dataset.updateAction = item.action;
			div.dataset.ispinned = item.ispinned;
			div.dataset.isarchived = item.isstatut;
			div.style.backgroundColor = item.color;
			div.dataset.id = item.id;
			div.querySelector('.title > h2').textContent = item.title;
			div.querySelector('.content').textContent = item.content;
			div.addEventListener('click', function(e) {
				_this.showNotePopup(item);
			});

			if (item.ispinned == 1) {
				notesPinned.appendChild(div);
			} else {
				notesUnPinned.appendChild(div);
			}
		}

	}

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

	/**************************************
	 * Popup handling
	*/
	this.showNotePopup = function(item) {

		// new popup
		var popupWrapper = this.notePopupTemplate.cloneNode(true);

		// avoid background scrolling when popup is "full screen"
		document.body.classList.add('noscroll');

		// this allows closing the popup with the "back" button (espacially on Android)
		if (history.state === null) history.pushState({'popupOpen': true}, null, window.location.pathname + '#popup');

		popupWrapper.addEventListener('click', function(e) {
			// clic is outside popup: closes popup
			if (e.target == this) {
				_this.closePopup();
			}
		});

		// note info
		popupWrapper.querySelector('#popup').style.backgroundColor = item.color;
		popupWrapper.querySelector('#popup').dataset.ispinned = item.ispinned;
		popupWrapper.querySelector('#popup').dataset.isarchived = item.isstatut;
		popupWrapper.querySelector('#popup > .popup-title > h2').textContent = item.title;
		popupWrapper.querySelector('#popup > .popup-content').value = item.content;
		popupWrapper.querySelector('#popup > .popup-footer > .date').textContent = Date.dateFromYMDHIS(item.id).toLocaleDateString('fr', {weekday: "long", month: "long", year: "numeric", day: "numeric"});

		// add events
		popupWrapper.querySelector('#popup > .popup-title > .pinnedIcon').addEventListener('click', function(e) {
			popupWrapper.querySelector('#popup').dataset.ispinned = Math.abs(popupWrapper.querySelector('#popup').dataset.ispinned -1);
		});
		popupWrapper.querySelector('#popup > .popup-title > .archiveIcon').addEventListener('click', function(e) {
			popupWrapper.querySelector('#popup').dataset.isarchived = Math.abs(popupWrapper.querySelector('#popup').dataset.isarchived -1);
		});
		popupWrapper.querySelector('#popup > .popup-footer > .colors').addEventListener('click', function(e) {
			_this.changeColor(e);
		});
		popupWrapper.querySelector('#popup > .popup-footer > .supprIcon').addEventListener('click', function(e) {
			_this.markAsDeleted(item);
			_this.closePopup();
		});
		popupWrapper.querySelector('#popup > .popup-footer > .submit-bttns > .button-cancel').addEventListener('click', function(e) {
			_this.closePopup();
		});
		popupWrapper.querySelector('#popup > .popup-footer > .submit-bttns > .button-submit').addEventListener('click', function(e) {
			_this.markAsEdited(item);
			_this.closePopup();
		});

		// add popup-wrapper to page
		this.domPage.appendChild(popupWrapper);
		popupWrapper.querySelector('#popup > .popup-content').focus();
	}


	this.closePopup = function() {
		var popupWrapper = document.getElementById('popup-wrapper');
		if (popupWrapper) popupWrapper.parentNode.removeChild(popupWrapper);
		document.body.classList.remove('noscroll');
	}

	/**************************************
	 * Mark a note as having been edited
	*/
	this.markAsEdited = function(item) {
		var popup = document.getElementById('popup');

		item.content = popup.querySelector('.popup-content').value;
		item.title = popup.querySelector('.popup-title > h2').textContent;
		item.color = window.getComputedStyle(popup).backgroundColor;
		item.ispinned = popup.dataset.ispinned;
		item.isstatut = popup.dataset.isarchived;
		item.action = item.action || 'updateNote';

		// it’s an edit of actual note
		if (this.notesList.includes(item)) {
			var theNote = this.noteContainer.querySelector('.notebloc[data-id="'+item.id+'"')

			// if note has been archived : hide it from this list
			if (popup.dataset.isarchived == 0 && popup.dataset.isarchived != item.isstatut && document.querySelector('select[name="filtre"]').value != 'archived') {
				theNote.classList.add('deleteFadeOutH');
				theNote.addEventListener('animationend', function(e){e.target.parentNode.removeChild(event.target);}, false);
			}

			theNote.style.backgroundColor = item.color;
			theNote.querySelector('.content').textContent = item.content;
			theNote.querySelector('h2').textContent = item.title;
	
			// if pined/unpinned : move note in proper section
			if (theNote.dataset.ispinned != item.ispinned) {
				theNote.dataset.ispinned = item.ispinned;
				if (item.ispinned == 1) document.getElementById('are-pinned').appendChild(theNote);
				else document.getElementById('are-unpinned').appendChild(theNote);

			}
		}

		// note is new: append it to list
		else {
			this.rebuiltNotesWall([item]); // append it to #notes-list
			this.notesList.push(item);     // append it to the main List
		}

		// raises global "updated" flag and saves
		this.raiseUpdateFlag(true);
		this.saveNotesXHR();
	}

	/**************************************
	 * Mark a note as having been deleted
	*/
	this.markAsDeleted = function(item) {
		if (!window.confirm(BTlang.questionSupprNote)) { return false; }

		// mark as removed
		item.action = 'deleteNote';
		// remove item from page, with a little animation
		var theNote = this.noteContainer.querySelector('.notebloc[data-id="'+item.id+'"')

		theNote.classList.add('deleteFadeOutH');
		theNote.addEventListener('animationend', function(event){event.target.parentNode.removeChild(event.target);}, false);
		
		// raises global "updated" flag.
		this.raiseUpdateFlag(true);
		this.saveNotesXHR();
	}


	/**************************************
	 * Change the color of a note
	*/
	this.changeColor = function(e) {
		if (e.target.tagName !== 'LI') return;
		document.getElementById('popup').style.backgroundColor = window.getComputedStyle(e.target).backgroundColor;;
	}


	/**************************************
	 * Each change triggers a flag. If (flag), the save button displays
	*/
	this.raiseUpdateFlag = function(flagRaised) {
		if (flagRaised) {
			this.hasUpdated = true;
			//document.getElementById('enregistrer').disabled = false;
		} else {
			this.hasUpdated = false;
			//document.getElementById('enregistrer').disabled = true;
		}
	}


	/**************************************
	 * AJAX call to save notes to DB
	*/
	this.saveNotesXHR = function() {
		this.backgroundWorkingPopup('started');

		// only keep modified notes
		var toSaveNotes = Array();
		for (var i=0, len=this.notesList.length; i<len ; i++) {
			if (this.notesList[i].action && 0 !== this.notesList[i].action.length) {
				toSaveNotes.push(this.notesList[i]);
			}
		}

		// make a string out of it
		var notesDataText = JSON.stringify(toSaveNotes);

		// create XHR
		var xhr = new XMLHttpRequest();
		xhr.open('POST', 'ajax/notes.ajax.php');

		// onload
		xhr.onload = function(e) {
			if (this.responseText.indexOf("Success") == 0) {
				_this.raiseUpdateFlag(false);
				// adding notif
				_this.notifNode.textContent = BTlang.confirmNotesSaved;

				// reset flags on notes to "void"
				for (var i=0, len=toSaveNotes.length; i<len ; i++) {
					toSaveNotes[i].action = "";
				}
				_this.backgroundWorkingPopup('waiting');
				return true;
			} else {
				// adding notif
				_this.notifNode.textContent = 'Error ' + e.target.status + ' ' +this.responseText;
				_this.backgroundWorkingPopup('waiting');
				return false;
			}
		};

		// onerror
		xhr.onerror = function(e) {
			// adding notif
			_this.notifNode.textContent = 'Error ' + e.target.status + ' ' +this.responseText;
			_this.backgroundWorkingPopup('waiting');
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

	// buttons events
	document.getElementById('fab').addEventListener('click', function(e) { _this.addNewEvent(); });
	document.getElementById('hide-side-nav').addEventListener('click', function(){ _this.hideSideNav(); });

	// Global Page listeners
	// beforeunload : warns the user if some data is not saved when page closes
	window.addEventListener("beforeunload", function(e) {
		if (_this.hasUpdated) {
			e.returnValue = BTlang.questionQuitPage;
		}
		else { return true; }
	});


	// allows a "popup close" when the user goes back 1 time in history (esp. on Android)
	window.addEventListener("popstate", function(e) {
		_this.closePopup();
	});

	// built page
	window.addEventListener("load", function() {
		_this.rebuiltMiniCal();
		_this.rebuiltMonthlyCal();
		_this.rebuiltEventsWall();
		_this.sortEventByFilter();
	});

	/* Feedback notif form scripts (handles the popup state: visible, hiding…) */
	this.backgroundWorkingPopup = function(state) {
		var notifPopup = document.getElementById('popup-notif');
		var spinner = document.getElementById('counter');

		// started : popup shows + spinner is running
		if (state === 'started') {
			notifPopup.classList.add('visible');
			spinner.classList.add('rotating');
		}

		// finished : working is done. Hide popup.
		if (state === 'finished') {
			spinner.classList.remove('rotating');
			notifPopup.classList.remove('visible');
		}

		// waiting for dissapearing
		if (state === 'waiting') {
			spinner.classList.remove('rotating');
			notifPopup.classList.add('fading');
			notifPopup.addEventListener('animationend', function(e) {
				notifPopup.classList.remove('fading');
				_this.backgroundWorkingPopup('finished');
			}, {'once': true});

		}
	}

	/**************************************
	 * Sort Events by date (sorting)
	*/
	this.sortEventsByDate = function() {
		this.eventsList.sort(function(a, b) {
			if (a.date_start > b.date_start) return 1;
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
			let dateOfCell = new Date(date.getFullYear(), date.getMonth(), cell-(nbDaysPrevMonth-1) );

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
				_this.initDate = dateOfCell;
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
			var eventDateTime = new Date(this.eventsList[i].date_start);

			// is event in different month ? in different year ?
			if ( (eventDateTime.getMonth() !== date.getMonth()) || (eventDateTime.getFullYear() !== date.getFullYear()) ) continue;
			// is event flaged as deleted?
			if (this.eventsList[i].action == "deleteEvent") continue;

			var selectCell = document.getElementById('m' + ("00" + (eventDateTime.getMonth() + 1)).slice(-2) + ("00" + eventDateTime.getDate()).slice(-2));

			selectCell.classList.add('hasEvent');
		}

	}

	/**************************************
	 * Draw the MAIN calendar 
	*/

	 // In « YEAR » display
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
			let dateOfCell = new Date(this.initDate.getFullYear(), cell, 1 );
			td.dataset.datetime = dateOfCell;

			td.appendChild(document.createTextNode( dateOfCell.toLocaleDateString('fr-FR', {month: "short"}) ));

			td.addEventListener('click', function(e){
				_this.eventTable.classList.remove('table-year-mode');
				_this.eventTable.classList.add('table-month-mode');

				_this.initDate = dateOfCell;
				_this.rebuiltMonthlyCal();
			});
		}

	}

	// In « MONTH » display
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
			let dateOfCell = new Date(date.getFullYear(), date.getMonth(), cell-(nbDaysPrevMonth-1) );

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
				_this.initDate = dateOfCell;
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
		for (let i = 0, len = this.eventsList.length ; i < len ; i++) {
			let item = this.eventsList[i];
			var eventDateTime = new Date(item.date_start);

			// is event flaged as deleted?
			if (item.action == "deleteEvent") continue;
			// is event in current month?
			if (!( eventDateTime >= firstDay && eventDateTime <= lastDay ) ) continue;

			var selectCell = document.getElementById('i' + ("00" + (eventDateTime.getMonth() + 1)).slice(-2) + ("00" + eventDateTime.getDate()).slice(-2));

			if (!selectCell.dataset.nbEvents || selectCell.dataset.nbEvents < 5) {
				var span = document.createElement('SPAN');
				span.style.backgroundColor = item.color;
				var time = document.createElement('TIME');
				time.setAttribute('datetime', item.date_start);
				time.textContent = (eventDateTime).toLocaleTimeString('fr-FR', {hour: "2-digit", minute: "2-digit"});
				span.appendChild(time);
				span.appendChild(document.createTextNode(item.title));

				span.classList.add('eventLabel');
				span.dataset.id = item.id;
				if (!isMobile()) {
					span.addEventListener('click', function() {
							_this.showEventPopup(item);
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

	// In DAY » display
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

		// delimiting current day
		var firstHour = (new Date(date.getFullYear(), date.getMonth(), date.getDate(), 0, 0, 0 ));
		var lastHour = (new Date(date.getFullYear(), date.getMonth(), date.getDate(), 23, 59, 59));


		// complete the actual <table>
		for (var cell = 0; cell <= lastHour.getHours() ; cell++) {
			let timeOfCell = new Date(date.getFullYear(), date.getMonth(), date.getDate(), cell, 0, 0 );

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
				_this.initDate = timeOfCell;
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

		// append the events to the calendar
		for (let i = 0, len = this.eventsList.length ; i < len ; i++) {
			let item = this.eventsList[i];
			var eventDateTime = new Date(item.date_start);

			// ignore if event flaged as deleted
			if (item.action == "deleteEvent") continue;
			// ignore if event is not today
			if ( eventDateTime < firstHour || eventDateTime > lastHour ) continue;

			var selectCell = document.getElementById( 'h' + ("00" + (eventDateTime.getHours())).slice(-2) + "00" ).querySelector('td:nth-of-type(2)');
			var span = document.createElement('SPAN');
			span.classList.add('eventLabel');
			span.style.backgroundColor = item.color;
			span.textContent = item.title;
			span.dataset.id = item.id;
			span.addEventListener('click', function() {
				_this.showEventPopup(item);
			});
			// spans the SPAN to give it a height proportionnal to the duration
			var duration = (new Date(item.date_end) - eventDateTime) / 1000 / 60 / 60 ; // in hours
			var parentHeight = selectCell.parentNode.getBoundingClientRect().bottom - selectCell.parentNode.getBoundingClientRect().top;
			span.style.height = (parentHeight * duration  - (2*3)) + 'px';
			span.style.top = parentHeight * eventDateTime.getMinutes() / 60 + 'px';

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

	this.rebuiltEventsWall = function() {
		// empties the node
		if (this.eventContainer.firstChild) {
			while (this.eventContainer.firstChild) {this.eventContainer.removeChild(this.eventContainer.firstChild);}
		}
		// TODO : add "no event" message
		if (0 === this.eventsList.length) return false;

		var date = Date.now();
		var evList = document.createDocumentFragment();

		// populates the new list
		for (let i = 0, len = this.eventsList.length ; i < len ; i++) {
			let item = this.eventsList[i];

			// ignore deleted events
			if (item.action == 'deleteEvent') continue;

			var itemDate = new Date(item.date_start);
			var itemDateEnd = new Date(item.date_end);
			var div = _this.eventTemplate.cloneNode(true);

			div.setAttribute('data-id', item.id);
			div.setAttribute('data-date', item.date_start);
			if (itemDate >= new Date()) { div.classList.add('futureEvent'); }
			else { div.classList.add('pastEvent'); }

			div.querySelector('.eventDate').title = itemDate.toLocaleDateString('fr', {weekday: "long", year: "numeric", month: "long", day: "numeric", hour: "numeric", minute: "numeric"});
			div.querySelector('.event-dd').textContent = itemDate.getDate();
			div.querySelector('.event-mmdd').textContent = itemDate.toLocaleDateString('fr', {month: "short"}) + ", " + itemDate.toLocaleDateString('fr', {weekday: "short"});
			div.querySelector('.event-hhii').textContent = itemDate.toLocaleTimeString('fr', {hour: 'numeric', minute: 'numeric'}) + ' - ' + itemDateEnd.toLocaleTimeString('fr', {hour: 'numeric', minute: 'numeric'});
			div.querySelector('.eventSummary > .color').style.backgroundColor = item.color;
			div.querySelector('.eventSummary > .title').textContent = item.title;
			div.querySelector('.eventSummary > .loc').textContent = item.loc;

			div.addEventListener('click', function() {
				_this.showEventPopup(item);
			} );

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
			var todayDate = new Date();
			switch(selectDate) {
				case 'today':
					return (date.toDateString() == todayDate.toDateString());
					break;
				case 'tomonth':
					return ("" + date.getFullYear() + date.getMonth() == "" + todayDate.getFullYear() + todayDate.getMonth());
					break;
				case 'toyear':
					return (date.getFullYear() == todayDate.getFullYear());
					break;
				case 'past':
					return (date <= todayDate);
					break;
				case 'futur':
					return (date >= todayDate);
					break;
				case 'all':
					return true;
					break;
				default:
					return (date.toDateString() == (new Date(selectDate)).toDateString());
					break;
			}
		}

		// only show element that pass the filter.
		this.eventContainer.querySelectorAll(':scope > div').forEach(function(div) {
			var itemDate = new Date(div.getAttribute('data-date'));
			if ( filter(itemDate) === true ) {
				div.removeAttribute('hidden');
			} else {
				div.setAttribute('hidden', '');
			}
		});
	}

	/**************************************
	 * Popup handling
	*/
	// Displays the "show event" popup
	this.showEventPopup = function(item) {

		// new popup
		var popupWrapper = this.editEventPopupTemplate.cloneNode(true);
		popupWrapper.querySelector('.popup-event').id = 'popup';
		var popup = popupWrapper.querySelector('#popup');
		popup.removeAttribute('hidden');

		// avoid background scrolling when popup is "full screen"
		document.body.classList.add('noscroll');

		// this allows closing the popup with the "back" button (espacially on Android)
		if (history.state === null) history.pushState({'popupOpen': true}, null, window.location.pathname + '#popup');

		popupWrapper.addEventListener('click', function(e) {
			// clic is on wrapper (back drop) but not the popup
			if (e.target == this) {
				_this.closePopup();
			}
		} );

		// fils data in popup
		popup.querySelector('.event-title > .event-color').style.backgroundColor = item.color;
		popup.querySelector('.event-title > .event-name').textContent = item.title;
		popup.querySelector('.event-content > ul > li.event-time > span:nth-of-type(1)').textContent = (new Date(item.date_start)).toLocaleDateString('fr-FR', {weekday: "long", year: "numeric", month: "long", day: "numeric"});
		popup.querySelector('.event-content > ul > li.event-time > span:nth-of-type(2)').textContent = (new Date(item.date_start)).toLocaleTimeString('fr-FR', {hour: '2-digit', minute:'2-digit'}) + '-' + (new Date(item.date_end)).toLocaleTimeString('fr-FR', {hour: '2-digit', minute:'2-digit'});
		popup.querySelector('.event-content > ul > li.event-loc').textContent = item.loc;

		// fill persons
		var persSpan = popup.querySelector('.event-content > ul > li.event-persons').removeChild(popup.querySelector('.event-content > ul > li.event-persons').firstChild);
		item.persons.forEach(function(p) {
			var span = persSpan.cloneNode(true);
			span.textContent = p;
			popup.querySelector('.event-content > ul > li.event-persons').appendChild(span);
		});

		popup.querySelector('.event-content > ul > li.event-description').textContent = item.content;

		// bind events
		popup.querySelector('.event-title > .item-menu-options .button-edit').addEventListener('click', function(e){
			_this.closePopup();
			_this.showEventEditPopup(item);
		});
		popup.querySelector('.event-title > .item-menu-options .button-suppr').addEventListener('click', function(e){
			_this.markAsDeleted(item);
			_this.closePopup();
		});
		popup.querySelector('.event-title > .button-cancel').addEventListener('click', function() {
			_this.closePopup();
		});

		// remove empty nodes
		var nodes = popup.querySelectorAll('.event-content *');
		nodes.forEach(function(node) {
			if (node.textContent.trim().length === 0) node.parentNode.removeChild(node);
		})

		this.domPage.appendChild(popupWrapper);
	}


	// Displays the "Edit event" popup (also for "new" events)
	this.showEventEditPopup = function(item) {
		// new popup
		var popupWrapper = this.editEventPopupTemplate.cloneNode(true);
		popupWrapper.querySelector('.popup-edit-event').id = 'popup';
		var popup = popupWrapper.querySelector('#popup');
		popup.removeAttribute('hidden');

		popupWrapper.addEventListener('click', function(e) {
			// clic is on wrapper (back drop) but not the popup
			if (e.target == this) {
				_this.closePopup();
			}
		});

		document.body.classList.add('noscroll');

		// fill popup data
		popup.querySelector('.event-title > .event-color').style.backgroundColor = item.color;
		popup.querySelector('.event-title > input').value = item.title;
		popup.querySelector('.event-content > .event-content-date #time-start').value = item.date_start.substr(11, 5);
		popup.querySelector('.event-content > .event-content-date #time-end').value = item.date_end.substr(11, 5);
		popup.querySelector('.event-content > .event-content-date #date').value = item.date_start.substr(0, 10);
		popup.querySelector('.event-content input[name="event-loc"]').value = item.loc;
		popup.querySelector('.event-content textarea[name="event-descr"]').value = item.content;

		var liTempl = popup.querySelector('#event-content-persons-selected').removeChild(popup.querySelector('#event-content-persons-selected').firstChild);
		item.persons.forEach(function(pers) {
			var curLiTempl = liTempl.cloneNode(true);
			curLiTempl.querySelector('span').textContent = pers;
			curLiTempl.querySelector('a').addEventListener('click', function(e) {
				this.parentNode.parentNode.removeChild(this.parentNode);
				e.preventDefault();
			});
			popup.querySelector('#event-content-persons-selected').appendChild(curLiTempl);
		});

		popup.querySelector('.event-content input[name="event-persons"]').addEventListener('keydown', function(e){
			if (e.keyCode == 13 && this.value !== '') {
				e.preventDefault();
				var curLiTempl = liTempl.cloneNode(true);
				curLiTempl.querySelector('span').textContent = this.value;
				curLiTempl.querySelector('a').addEventListener('click', function(clic) {
					this.parentNode.parentNode.removeChild(this.parentNode);
					clic.preventDefault();
				});
				popup.querySelector('#event-content-persons-selected').appendChild(curLiTempl);

				this.value = '';
				return false;
			}
		});

		// bind events
		popup.querySelector('.event-title > .colors').addEventListener('click', function(e) {
			if (e.target.tagName == 'LI') _this.changeColor(item, e);
		});

		popup.querySelector('.event-title > .button-cancel').addEventListener('click', function() {
			popupWrapper.parentNode.removeChild(popupWrapper);
			document.body.classList.remove('noscroll');
		});
		popup.querySelector('.event-content > .event-content-date #allDay').addEventListener('change', function() {
			var dateTimeInput = popupWrapper.querySelector('#popup > .event-content .date-time-input');
			if (this.checked) {
				dateTimeInput.classList.add('date-only');
			}
			else {
				dateTimeInput.classList.remove('date-only');
			}
		});

		popup.querySelector('.event-footer > .button-submit').addEventListener('click', function() {
			_this.markAsEdited(item);
			document.body.classList.remove('noscroll');
			popupWrapper.parentNode.removeChild(popupWrapper);
		});

		this.domPage.appendChild(popupWrapper);
	}

	// close actual popup
	this.closePopup = function() {
		var popupWrapper = document.getElementById('popup-wrapper');
		if (popupWrapper) popupWrapper.parentNode.removeChild(popupWrapper);
		document.body.classList.remove('noscroll');
	}


	/**************************************
	 * Mark an Event object as having been edited
	*/
	this.markAsEdited = function(item) {
		var popup = document.getElementById('popup');

		item.color = window.getComputedStyle(popup.querySelector('.event-title .event-color')).backgroundColor;
		item.title = popup.querySelector('.event-title input').value || BTlang.emptyTitle;

		if (popup.querySelector('#allDay').checked ) {
			var newDateStart = new Date(document.getElementById('date').value + " " + "00:00:00");
			var newDateEnd = new Date(document.getElementById('date').value + " " + "23:59:59");
		} else {
			var newDateStart = new Date(document.getElementById('date').value + " " + document.getElementById('time-start').value);
			var newDateEnd = new Date(document.getElementById('date').value + " " + document.getElementById('time-end').value);
		}
		item.date_start = newDateStart.toLocalISOString();
		item.date_end = newDateEnd.toLocalISOString();


		var listPersons = popup.querySelectorAll('#event-content-persons-selected > li > span');
		item.persons = new Array();
		for (var i = 0, len=listPersons.length ; i<len ; i++) {
			item.persons.push(listPersons[i].textContent);
		}

		item.loc = popup.querySelector('.event-content-loc .text').value;

		item.content = popup.querySelector('.event-content-descr .text').value;

		// event is new: append it to the eventsList{}
		if (!this.eventsList.includes(item)) {
			this.eventsList.push(item);
		}

		// re-sort by date
		this.sortEventsByDate();

		// rebuilt Calendar to take changes into account. // TODO: perhaps not rebuilt cal, but only add/move buttons (for perf) ?
		this.rebuiltMiniCal();
		if (this.eventTable.classList.contains('table-day-mode')) this.rebuiltDailyCal();
		if (this.eventTable.classList.contains('table-month-mode')) this.rebuiltMonthlyCal();
		if (this.eventTable.classList.contains('table-year-mode')) this.rebuiltYearlyCal();
		this.rebuiltEventsWall();
		this.sortEventByFilter();

		item.action = item.action || 'updateEvent';

		// raises global "updated" flag.
		this.raiseUpdateFlag(true);
		this.saveEventsXHR();
	}


	/**************************************
	 * Creates a new event, init it, display it and add it to list.
	*/
	this.addNewEvent = function() {
		var date = this.initDate;
		var dateEnd = new Date(date.getTime()); dateEnd.setHours(date.getHours()+1);
		var newEv = {
			"id": new Date().getTime().toString(),
			"date_start": date.toLocalISOString(),
			"date_end": dateEnd.toLocalISOString(),
			"title": '',
			"content": '',
			"color" : '#ff8a80',
			"loc" : '',
			"persons" : [],
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
		this.rebuiltEventsWall();
		this.sortEventByFilter();

		// close popup
		document.getElementById('popup-wrapper').parentNode.removeChild(document.getElementById('popup-wrapper'));

		// raises global "updated" flag.
		this.raiseUpdateFlag(true);
		this.saveEventsXHR();
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
			//document.getElementById('enregistrer').disabled = false;
		} else {
			this.hasUpdated = false;
			//document.getElementById('enregistrer').disabled = true;
		}
	}

	/**************************************
	 * AJAX call to save events to DB
	*/
	this.saveEventsXHR = function() {
		this.backgroundWorkingPopup('started');

		// only keep modified events
		var toSaveEvents = Array();
		for (var i=0, len=this.eventsList.length; i<len ; i++) {
			if (this.eventsList[i].action && 0 !== this.eventsList[i].action.length) {
				var ev = this.eventsList[i];
				toSaveEvents.push(ev);
			}
		}

		// make a string out of it
		var eventsDataText = JSON.stringify(toSaveEvents);

		// create XHR
		var xhr = new XMLHttpRequest();
		xhr.open('POST', 'ajax/agenda.ajax.php');

		// onload
		xhr.onload = function(e) {
			if (this.responseText.indexOf("Success") == 0) {
				_this.notifNode.textContent = BTlang.confirmEventsSaved;
				// reset flags on events (edited only, not deleted)
				for (var i=0, len=toSaveEvents.length; i<len ; i++) {
					if (toSaveEvents[i].action == 'updateEvent' || toSaveEvents[i].action == 'newEvent') {
						toSaveEvents[i].action = "";
					}
				}
				_this.raiseUpdateFlag(false);
				_this.backgroundWorkingPopup('waiting');
				return true;
			} else {
				_this.notifNode.textContent = 'Error ' + e.target.status + ' ' +this.responseText;
				_this.backgroundWorkingPopup('waiting');
				return false;
			}
		};

		// onerror
		xhr.onerror = function(e) {
			_this.notifNode.textContent = 'Error ' + e.target.status + ' ' +this.responseText;
			_this.backgroundWorkingPopup('waiting');
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

	// buttons events
	document.getElementById('fab').addEventListener('click', function(e) { _this.addNewContact(); });
	document.getElementById('enregistrer').addEventListener('click', function() { _this.saveContactsXHR(); } );

	// Global Page listeners
	// beforeunload : warns the user if some data is not saved when page closes
	window.addEventListener("beforeunload", function(e) {
		if (_this.hasUpdated) {
			e.returnValue = BTlang.questionQuitPage;
		}
		else { return true; }
	});

	// built table on page-ready
	window.addEventListener("load", function() {
		_this.rebuiltContactsTable(_this.contactList);
	});

	window.addEventListener("popstate", function(e) {
		_this.closePopup();
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
		for (let i = 0, len = ContactsData.length ; i < len ; i++) {
			if (this.contactList[i].action == "deleteContact") continue;

			let item = ContactsData[i]; // sort in reverse order
			var tr = this.contactTemplate.cloneNode(true);
			tr.setAttribute('data-id', item.id);

			if (item.img != "") {
				tr.querySelector('.icon > span').style.backgroundImage = 'url('+item.img+')';
			} else {
				var color = "#" + hashFnv32a(item.fullname).substring(0, 3);
				tr.querySelector('.icon > span').style.backgroundImage = 'linear-gradient('+color+', '+color+')';
			}

			tr.querySelector('.name').textContent = item.title + ' ' + item.fullname;
			tr.querySelector('.label > span').textContent = item.label;

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


			tr.addEventListener('click', function() { _this.showContactPopup(item); })
			tr.querySelector('.button-edit').addEventListener('click', function(e) {
				e.stopPropagation();
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
	this.showContactPopup = function(item) {
		var popupWrapper = this.contactPopupTemplate.cloneNode(true);
		popupWrapper.addEventListener('click', function(e) {
			// clic is outside popup: closes popup
			if (e.target == this) {
				_this.closePopup();
			}
		});
		popupWrapper.querySelector('.popup-contact').id = 'popup';
		popupWrapper.querySelector('.popup-contact').removeAttribute('hidden');

		document.body.classList.add('noscroll');
		// this allows closing the popup with the "back" button (espacially on Android)
		if (history.state === null) history.pushState({'popupOpen': true}, null, window.location.pathname + '#popup');


		// POPUP TITLE
		var popupTitle = popupWrapper.querySelector('#popup > .contact-title');

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
		popupContent.querySelectorAll('*').forEach(function(node) {
			if (node.textContent.trim().length === 0) node.parentNode.removeChild(node);
		});
		// remove not used section titles
		while (popupContent.lastElementChild.tagName === 'DIV') {
			popupContent.removeChild(popupContent.lastElementChild);
		}
		popupContent.querySelectorAll('div').forEach(function(div) {
			if (div.nextElementSibling.tagName === 'DIV') div.parentNode.removeChild(div);
		});

		// misc events
		popupTitle.querySelector('.item-menu-options > ul > li > a').addEventListener('click', function(e){
			_this.markAsDeleted(item);
			_this.closePopup();
		});
		popupTitle.querySelector('.button-cancel').addEventListener('click', function() {
			_this.closePopup();
		});
		popupTitle.querySelector('.button-edit').addEventListener('click', function() {
			_this.closePopup();
			_this.showContactEditPopup(item);
		});

		this.domPage.appendChild(popupWrapper);
	}

	this.showContactEditPopup = function(item) {

		var popupWrapper = this.contactPopupTemplate.cloneNode(true);
		popupWrapper.addEventListener('click', function(e) {
			// clic is outside popup: closes popup
			if (e.target == this) {
				_this.closePopup();
			}
		});
		popupWrapper.querySelector('.popup-edit-contact').id = 'popup';
		popupWrapper.querySelector('.popup-edit-contact').removeAttribute('hidden');

		document.body.classList.add('noscroll');
		// this allows closing the popup with the "back" button (espacially on Android)
		if (history.state === null) history.pushState({'popupOpen': true}, null, window.location.pathname + '#popup');

		popupWrapper.querySelector('.popup-edit-contact .contact-title > .button-cancel').addEventListener('click', function() {
			_this.closePopup();
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
		if (item.email.length) {
			var labelField = popupWrapper.querySelector('#popup .contact-emails').removeChild(popupWrapper.querySelector('#popup .contact-emails').firstElementChild);
			item.email.forEach(function(m) {
				var curField = labelField.cloneNode(true);
				curField.querySelector('input').value = m;
				popupWrapper.querySelector('#popup .contact-emails').appendChild(curField);
			});
		}

		// phone(s)
		if (item.tel.length) {
			var labelField = popupWrapper.querySelector('#popup .contact-phones').removeChild(popupWrapper.querySelector('#popup .contact-phones').firstElementChild);
			item.tel.forEach(function(t) {
				var curField = labelField.cloneNode(true);
				curField.querySelector('input').value = t;
				popupWrapper.querySelector('#popup .contact-phones').appendChild(curField);
			});
		}

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
		if (item.websites.length) {
			var labelField = popupWrapper.querySelector('#popup .contact-links').removeChild(popupWrapper.querySelector('#popup .contact-links').firstElementChild);
			item.websites.forEach(function(ws) {
				var curField = labelField.cloneNode(true);
				curField.querySelector('input').value = ws;
				popupWrapper.querySelector('#popup .contact-links').appendChild(curField);
			});
		}

		// Social media links(s)
		if (item.social.length) {
			var labelField = popupWrapper.querySelector('#popup .contact-social').removeChild(popupWrapper.querySelector('#popup .contact-social').firstElementChild);
			item.social.forEach(function(s) {
				var curField = labelField.cloneNode(true);
				curField.querySelector('input').value = s;
				popupWrapper.querySelector('#popup .contact-social').appendChild(curField);
			});
		}

		this.showContactEditPopup.duplicateLabelGroup = function() {
			var newLabel = this.parentNode.cloneNode(true);
			newLabel.querySelector('input').value = "";
			newLabel.querySelector('button.add').addEventListener('click', _this.showContactEditPopup.duplicateLabelGroup);
			newLabel.querySelector('button.rem').addEventListener('click', _this.showContactEditPopup.removeLabelGroup);
			this.parentNode.parentNode.appendChild(newLabel);
		}
		this.showContactEditPopup.removeLabelGroup = function() {
			this.parentNode.parentNode.removeChild(this.parentNode);
		}

		// Buttons « + »
		popupWrapper.querySelectorAll('#popup button.add').forEach(function(add) {
			add.addEventListener('click', _this.showContactEditPopup.duplicateLabelGroup);
		});

		// Buttons « × »
		popupWrapper.querySelectorAll('#popup button.rem').forEach(function(rem) {
			rem.addEventListener('click', _this.showContactEditPopup.removeLabelGroup);
		});

		popupWrapper.querySelector('#popup input[name="contact-notes"]').value = item.notes;
		popupWrapper.querySelector('#popup input[name="contact-other"]').value = item.other;

		popupWrapper.querySelector('#popup .contact-footer > .button-cancel').addEventListener('click', function() {
			popupWrapper.querySelectorAll('#popup .contact-content > .onshowmore').forEach(function(hidden) {
				hidden.classList.remove('onshowmore');
			});
			this.style.visibility = "hidden";
		});

		popupWrapper.querySelector('#popup .contact-footer > .button-submit').addEventListener('click', function() {
			_this.markAsEdited(item);
			_this.closePopup();
		});

		this.domPage.appendChild(popupWrapper);
	}

	// close actual popup
	this.closePopup = function() {
		var popupWrapper = document.getElementById('popup-wrapper');
		if (popupWrapper) popupWrapper.parentNode.removeChild(popupWrapper);
		document.body.classList.remove('noscroll');
	}

	/**************************************
	 * Creates a new Contact, init it, display it and add it to list.
	*/
	this.addNewContact = function() {
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
		//document.getElementById('popup-wrapper').parentNode.removeChild(document.getElementById('popup-wrapper'));
		this.closePopup();

		// raises global "updated" flag.
		this.raiseUpdateFlag(true);
	}

	/**************************************
	 * Mark a Contact object as having been edited
	*/
	this.markAsEdited = function(item) {
		var popup = document.getElementById('popup');

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

		if (!this.contactList.includes(item)) {
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
				toSaveContacts.push(ct);
			}
		}

		// make a string out of it
		var contactsDataText = JSON.stringify(toSaveContacts);

		var notifDiv = document.createElement('div');
		// create XHR
		var xhr = new XMLHttpRequest();
		xhr.open('POST', 'ajax/contacts.ajax.php');

		// onload
		xhr.onload = function(e) {
			if (this.responseText.indexOf("Success") == 0) {
				// adding notif
				_this.notifNode.textContent = BTlang.confirmContactsSaved;
				// reset flags on contacts
				for (var i=0, len=toSaveContacts.length; i<len ; i++) {
					toSaveContacts[i].action = "";
				}
				loading_animation('off');
				_this.raiseUpdateFlag(false);
				return true;
			} else {
				_this.notifNode.textContent = 'Error ' + e.target.status + ' ' +this.responseText;
				loading_animation('off');
				return false;
			}
		};

		// onerror
		xhr.onerror = function(e) {
			loading_animation('off');
			// adding notif
			_this.notifNode.textContent = 'Error ' + e.target.status + ' ' +this.responseText;
		};

		// prepare and send FormData
		var formData = new FormData();
		formData.append('token', token);
		formData.append('save_contacts', contactsDataText);
		xhr.send(formData);
	}
}
