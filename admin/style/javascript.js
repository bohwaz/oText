// *** LICENSE ***
// oText is free software.
//
// By Fred Nassar (2006) and Timo Van Neerden (since 2010)
// See "LICENSE" file for info.
// *** LICENSE ***

"use strict";

/* reproduces the PHP « date(#, 'c') » output format */
Date.prototype.dateToISO8601String  = function() {
	var padDigits = function padDigits(number, digits) {
		return Array(Math.max(digits - String(number).length + 1, 0)).join(0) + number;
	}

	var offsetMinutes = - this.getTimezoneOffset();
	var offsetHours = offsetMinutes / 60;
	var offset= "Z";
	if (offsetHours < 0)
		offset = "-" + padDigits((offsetHours.toString()).replace("-","") + ":00", 5);
	else if (offsetHours > 0)
		offset = "+" + padDigits(offsetHours  + ":00", 5);


	return this.getFullYear()
		+ "-" + padDigits((this.getMonth()+1),2)
		+ "-" + padDigits(this.getDate(),2)
		+ "T"
		+ padDigits(this.getHours(),2)
		+ ":" + padDigits(this.getMinutes(),2)
		+ ":" + padDigits(this.getSeconds(),2)
		//+ "." + padDigits(this.getMilliseconds(),2)
		+ offset;


}

/*Date.dateFromISO8601 = function(isoDateString) {
	var parts = isoDateString.match(/\d+/g);
	var isoTime = Date.UTC(parts[0], parts[1] - 1, parts[2], parts[3], parts[4], parts[5]);
	var isoDate = new Date(isoTime);
	return isoDate;       
}*/

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
	var openMenu = document.querySelectorAll('#top > [id] > ul.visible');
	for (var i=0, len=openMenu.length ; i<len ; i++) {
		if (!openMenu[i].parentNode.contains(target)) openMenu[i].classList.remove('visible');
	}
}

window.addEventListener('click', function(e) {
	var openMenu = document.querySelectorAll('#top > [id] > ul.visible');
	// no open menus: abord
	if (!openMenu.length) return;
	// open menus ? close them.
	else closeOpenMenus(null);
});

// add "click" listeners on the list of menus
['nav', 'nav-acc', 'notif-icon'].forEach(function(elem) {
	document.getElementById(elem).addEventListener('click', function(e) {
		closeOpenMenus(e.target);
		var menu = document.getElementById(elem).querySelector('ul');
		if (this === (e.target)) menu.classList.toggle('visible');
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
*/

function unfold(button) {
	var elemOnForground = document.querySelector('.commentbloc.foreground');
	var elemToForground = document.getElementById(button.dataset.comDomAnchor);

	if (elemOnForground == elemToForground) {
		elemOnForground.classList.remove('foreground');
		return false;
	}

	elemToForground.classList.add('foreground');
	elemToForground.getElementsByTagName('textarea')[0].focus();
	return false;
}


// deleting a comment
function suppr_comm(button) {
	var notifDiv = document.createElement('div');
	var reponse = window.confirm(BTlang.questionSupprComment);
	var div_bloc = document.getElementById(button.dataset.comDomAnchor);

	if (reponse == true) {
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
				div_bloc.classList.add('deleteFadeOut');
				div_bloc.addEventListener('animationend', function(event){event.target.parentNode.removeChild(event.target);}, false);
				// adding notif
				notifDiv.textContent = BTlang.confirmCommentSuppr;
				notifDiv.classList.add('confirmation');
				document.getElementById('top').appendChild(notifDiv);
			} else {
				// adding notif
				notifDiv.textContent = this.responseText;
				notifDiv.classList.add('no_confirmation');
				document.getElementById('top').appendChild(notifDiv);
			}
			div_bloc.classList.remove('ajaxloading');
		};
		xhr.onerror = function(e) {
			notifDiv.textContent = BTlang.errorCommentSuppr + e.target.status;
			notifDiv.classList.add('no_confirmation');
			document.getElementById('top').appendChild(notifDiv);
			div_bloc.classList.remove('ajaxloading');
		};

		// prepare and send FormData
		var formData = new FormData();
		formData.append('token', csrf_token);
		formData.append('_verif_envoi', 1);
		formData.append('com_supprimer', button.dataset.commId);
		formData.append('com_article_id', button.dataset.commArtId);

		xhr.send(formData);

	}
	return reponse;
}


// hide/unhide a comm
function activate_comm(button) {
	var notifDiv = document.createElement('div');
	var div_bloc = document.getElementById(button.dataset.comDomAnchor);
	div_bloc.classList.toggle('ajaxloading');

	var xhr = new XMLHttpRequest();
	xhr.open('POST', 'commentaires.php', true);

	xhr.onprogress = function() {
		div_bloc.classList.add('ajaxloading');
	}

	xhr.onload = function() {
		var resp = this.responseText;
		if (resp.indexOf("Success") == 0) {
			csrf_token = resp.substr(7, 40);
			button.textContent = ((button.textContent === BTlang.activer) ? BTlang.desactiver : BTlang.activer );
			div_bloc.classList.toggle('privatebloc');

		} else {
			notifDiv.textContent = BTlang.errorCommentValid + ' ' + resp;
			notifDiv.classList.add('no_confirmation');
			document.getElementById('top').appendChild(notifDiv);
		}
		div_bloc.classList.remove('ajaxloading');
	};
	xhr.onerror = function(e) {
		notifDiv.textContent = BTlang.errorCommentSuppr + ' ' + e.target.status + ' (#com-activ-H28)';
		notifDiv.classList.add('no_confirmation');
		document.getElementById('top').appendChild(notifDiv);
		div_bloc.classList.remove('ajaxloading');
	};

	// prepare and send FormData
	var formData = new FormData();
	formData.append('token', csrf_token);
	formData.append('_verif_envoi', 1);

	formData.append('com_activer', button.dataset.commId);
	formData.append('com_bt_id', button.dataset.commBtid);
	formData.append('com_article_id', button.dataset.commArtId);

	xhr.send(formData);

	return false;
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
		for (var i = 0 ; i<len ; i++) { iTag += liste[i].getElementsByTagName('span')[0].innerHTML+", "; }
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
function handleDragEnd(e) {
	document.getElementById('dragndrop-area').classList.remove('fullpagedrag');
}

function handleDragLeave(e) {
	if ('WebkitAppearance' in document.documentElement.style) { // Chromium old bug #131325 since 2013.
		if (e.pageX > 0 && e.pageY > 0) {
			return false;
		}
	}
	document.getElementById('dragndrop-area').classList.remove('fullpagedrag');
}

function handleDragOver(e) {
	var dndArea = document.getElementById('dragndrop-area');
//	if (e.dataTransfer.files) {
		dndArea.classList.add('fullpagedrag');
//	} else {
//		dndArea.classList.remove('fullpagedrag');
//	}

}

// process bunch of files
function handleDrop(event) {
	event.preventDefault();
	// detects if drag contains files.
	if (!event.dataTransfer.files) {
		console.log('no-files');
		return false;
	}
	else {
		console.log('files');
	}
	var result = document.getElementById('result');
	document.getElementById('dragndrop-area').classList.remove('fullpagedrag');

	if (nbDraged === false) { nbDone = 0; }

	var filelist = event.dataTransfer.files;
	if (!filelist || !filelist.length) { return false; }

	for (var i = 0, nbFiles = filelist.length ; i < nbFiles && i < 500; i++) { // limit is for not having an infinite loop
		var rand = 'i_'+Math.random()
		filelist[i].locId = rand;
		list.push(filelist[i]);
		var div = document.createElement('div');
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
		div.classList.add('pending');
		div.classList.add('fileinfostatus');
		div.id = rand;

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

// upload file
function uploadFile(file) {
	// prepare XMLHttpRequest
	var xhr = new XMLHttpRequest();
	xhr.open('POST', '_files.ajax.php');

	xhr.onload = function() {
		var respdiv = document.getElementById(file.locId);
		// need "try/catch/finally" because of "JSON.parse", that might return errors (but should not, since backend is clean)
		try {
			var resp = JSON.parse(this.responseText);
			respdiv.classList.remove('pending');

			if (resp !== null) {
				// renew token
				document.getElementById('token').value = resp.token;

				respdiv.querySelector('.uploadstatus').innerHTML = resp.status;

				if (resp.status == 'success') {
					respdiv.classList.add('success');
					respdiv.querySelector('.filelink').href = resp.url;
					respdiv.querySelector('.uploadstatus').innerHTML = 'Uploaded';
					// replace file name with a link
					respdiv.querySelector('.filelink').innerHTML = respdiv.querySelector('.filename').innerHTML;
					respdiv.removeChild(respdiv.querySelector('.filename'));
				}
				else {
					respdiv.classList.add('failure');
					respdiv.querySelector('.uploadstatus').innerHTML = 'Upload failed';
				}

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

	xhr.onerror = function() {
		uploadNext();
	};

	// prepare and send FormData
	var formData = new FormData();
	formData.append('token', document.getElementById('token').value);
	formData.append('do', 'upload');
	formData.append('upload', '1');

	formData.append('fichier', file);
	formData.append('statut', ((document.getElementById('statut').checked === false) ? '' : 'on'));

	formData.append('description', document.getElementById('description').value);
	formData.append('nom_entree', document.getElementById('nom_entree').value);
	formData.append('dossier', document.getElementById('dossier').value);
	xhr.send(formData);
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
			uploadFile(nextFile);
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
	if (typeof images == 'undefined' || !images.list.length) return;
	var _this = this;

	/***********************************
	** Some properties & misc actions
	*/
	// init JSON List
	this.imgList = images.list;

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
			bloc.dataset.folder = item.folder;

			var imgThb = document.createElement('img');
			imgThb.src = item.thbPath;
			imgThb.alt = '#';
			bloc.appendChild(imgThb);

			var spanBtns = document.createElement('span');

			var btnShow = document.createElement('a')
			btnShow.classList.add('vignetteAction', 'imgShow');
			btnShow.href = item.absPath;
			spanBtns.appendChild(btnShow);

			var btnEdit = document.createElement('a')
			btnEdit.classList.add('vignetteAction', 'imgEdit');
			btnEdit.href = 'fichiers.php?file_id='+item.id;
			spanBtns.appendChild(btnEdit);

			var btnDL = document.createElement('a')
			btnDL.classList.add('vignetteAction', 'imgDL');
			btnDL.href = item.absPath;
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
		xhr.open('POST', '_files.ajax.php');
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
	if (typeof docs == 'undefined' || !docs.list.length) return;
	var _this = this;

	/***********************************
	** Some properties & misc actions
	*/
	// init JSON List
	this.docsList = docs.list;

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
	this.rebuiltTable = function(docsList, limit) {

		// empties the actual list
		while (this.docsDomTable.firstChild) {
			 this.docsDomTable.removeChild(this.docsDomTable.firstChild);
		}

		if (0 === docsList.length) return false;

		// populates the new list
		for (var i = 0, len = docsList.length ; i < (Math.min(len, limit)) ; i++) {
			var item = docsList[i];


			var row = document.createElement('tr');
			row.id = 'bloc_' + item.id;
			row.dataset.type = item.fileType;

			var cellIcon = document.createElement('td');
			var icon = document.createElement('img');
			icon.id = item.id;
			icon.alt = item.fileName;
			icon.src = 'style/filetypes/'+item.fileType+'.png';
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
			cellDate.appendChild(document.createTextNode(Date.dateFromYMDHIS(item.id).toLocaleString('fr', {weekday: "short", month: "short", day: "numeric"})));
			row.appendChild(cellDate);

			var cellDwnd = document.createElement('td');
			var fileDL = document.createElement('a');
			fileDL.appendChild(document.createTextNode('DL'));
			fileDL.href = item.absPath;
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
	this.rebuiltTable(this.docsList, 25);




	/***********************************
	** Sends a "delete" request to server,
	*/
	this.deleteFile = function (id) {
		// ask for popup confirmation
		if (!window.confirm(BTlang.questionSupprFichier)) { return false; }
		// prepare XMLHttpRequest
		var xhr = new XMLHttpRequest();
		xhr.open('POST', '_files.ajax.php');
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

/* open-close rss-folder */
function hideFolder(btn) {
	btn.parentNode.parentNode.classList.toggle('open');
	return false;
}



function RssReader() {
	var _this = this;

	/***********************************
	** Some properties & misc actions
	*/
	// init JSON List
	this.feedList = rss_entries.list;

	// init local "mark as read" buffer
	this.readQueue = {"count": "0", "urlList": []};

	// get some DOM elements
	this.postsList = document.getElementById('post-list');
	this.feedsList = document.getElementById('feed-list');
	this.notifNode = document.getElementById('message-return');

	// init the « open-all » toogle-button.
	this.openAllButton = document.getElementById('openallitemsbutton');
	this.openAllButton.addEventListener('click', function(){ _this.openAll(); });

	// init the « mark as read button ».
	this.markAsReadButton = document.getElementById('markasread');
	this.markAsReadButton.addEventListener('click', function(){ _this.markAsRead(); });

	// init the « refresh all » button event
	this.refreshButton = document.getElementById('refreshAll');
	this.refreshButton.addEventListener('click', function(){ _this.refreshAllFeeds(); });

	// init the « delete old » button event
	this.deleteButton = document.getElementById('deleteOld');
	this.deleteButton.addEventListener('click', function(){ _this.deleteOldFeeds(); });

	// init the « add new feed » button event
	this.fabButton = document.getElementById('fab');
	this.fabButton.addEventListener('click', function(){ _this.addNewFeed(); });

	// Global Page listeners
	// onkeydown : detect "open next/previous" action with keyboard
	window.addEventListener('keydown', function(e) { _this.kbActionHandle(e); } );

	// beforeunload : to send a "mark as read" request when closing the tab or reloading whole page
	window.addEventListener("beforeunload", function(e) { _this.markAsReadOnUnloadXHR(); } );

	var DateTimeFormat = new Intl.DateTimeFormat('fr', {weekday: "short", month: "short", day: "numeric", hour: "numeric", minute: "numeric"});

	var d = new Date();
	this.ymd000 = '' + d.getFullYear() + ('0' + (d.getMonth()+1)).slice(-2) + ('0' + d.getDate()).slice(-2) + '000000';


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

		// populates the new list
		for (var i = 0, len = RssPosts.length ; i < len ; i++) {
			var item = RssPosts[i];

			// new list element
			var li = document.createElement("li");
			li.id = 'i_'+item.id;
			li.dataset.sitehash = item.feedhash;
//			li.dataset.postdate = item.datetime;
			if (0 === item.statut) { li.classList.add('read'); }

			// li-head: head-block
			var postHead = document.createElement("div");
			postHead.classList.add('post-head');

			var favBtn = document.createElement("a");
			favBtn.href = '#';
			favBtn.classList.add("lien-fav");
			favBtn.dataset.isFav = item.fav;
			favBtn.dataset.favId = item.id;
			favBtn.addEventListener('click', function(e){ _this.markAsFav(this); e.preventDefault(); } );
			postHead.appendChild(favBtn);

			// site name
			var site = document.createElement("div");
			site.classList.add('site');
			site.appendChild(document.createTextNode(item.sitename));
			postHead.appendChild(site);

			// post folders labels
			if (item.folder) {
				var folder = document.createElement("div");
				folder.classList.add('folder');
				folder.appendChild(document.createTextNode(item.folder));
				postHead.appendChild(folder);
			}
			
			// post title
			var titleLink = document.createElement("a");
			titleLink.href = item.link;
			titleLink.title = item.title;
			titleLink.classList.add('post-title');
			titleLink.target = "_blank";
			titleLink.appendChild(document.createTextNode(item.title));
			titleLink.dataset.id = li.id;
			titleLink.addEventListener('click', function(e){ if(!_this.openThisItem(document.getElementById(this.dataset.id))) e.preventDefault(); } );
			postHead.appendChild(titleLink);

			// post date
			var date = document.createElement("div");
			date.classList.add('date');
			date.appendChild(document.createTextNode(DateTimeFormat.format(Date.dateFromYMDHIS(item.datetime))));
			postHead.appendChild(date);

			// hover buttons (share link, tweet…)
			var share = document.createElement("div");
			share.classList.add('share');
			// share, in linx
			var shareLink = document.createElement("a");
			shareLink.href = 'links.php?url='+encodeURIComponent(item.link);
			shareLink.target = "_blank";
			shareLink.classList.add("lien-share");
			share.appendChild(shareLink);
			// open in new tab
			var openLink = document.createElement("a");
			openLink.href = item.link;
			openLink.target = "_blank";
			openLink.classList.add("lien-open");
			share.appendChild(openLink);
			// mail link
			var mailLink = document.createElement("a");
			mailLink.href = 'mailto:?&subject='+ encodeURIComponent(item.title) + '&body=' + encodeURIComponent(item.link);
			mailLink.target = "_blank";
			mailLink.classList.add("lien-mail");
			share.appendChild(mailLink);
			// tweet link
			var tweetLink = document.createElement("a");
			tweetLink.href = 'https://twitter.com/intent/tweet?text='+ encodeURIComponent(item.title) + '&amp;url=' + encodeURIComponent(item.link);
			tweetLink.target = "_blank";
			tweetLink.classList.add("lien-tweet");
			share.appendChild(tweetLink);
			// G+ link
			var gplusLink = document.createElement("a");
			gplusLink.href = 'https://plus.google.com/share?url=' + encodeURIComponent(item.link);
			gplusLink.target = "_blank";
			gplusLink.classList.add("lien-gplus");
			share.appendChild(gplusLink);

			postHead.appendChild(share);
			li.appendChild(postHead);

			// bloc with main content of feed in a comment (it’s uncomment when open, to defer media loading).
			var content = document.createElement("div");
			content.classList.add('rss-item-content');
			var comment = document.createComment(item.content);
			content.appendChild(comment);
			li.appendChild(content);

			var hr = document.createElement("hr");
			hr.classList.add('clearboth');
			li.appendChild(hr);

			this.postsList.appendChild(li);
		}

		// displays the number of items (local counter)
		var count = document.querySelector('#post-counter');
		if (count.firstChild) {
			count.firstChild.nodeValue = RssPosts.length;
			//count.dataset.nbrun = RssPosts.length;
		} else {
			count.appendChild(document.createTextNode(RssPosts.length));
			//count.dataset.nbrun = RssPosts.length;
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
		var isVisible = ( (rect.top < 120) || (rect.bottom > window.innerHeight) ) ? false : true ;
		if (!isVisible) {
			window.location.hash = theItem.id;
			window.scrollBy(0, -120);
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



	/***********************************
	** Methods to "sort" elements (by site, folder, favs…)
	*/
	// create list of items matching the selected site
	this.sortItemsBySite = function(theSite) {
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
		return false;
	}

	// create list of items matching the selected folder
	this.sortItemsByFolder = function(theFolder) {
		var newList = new Array();
		for (var i = 0, len = this.feedList.length ; i < len ; i++) {
			if (this.feedList[i].folder == theFolder) {
				newList.push(this.feedList[i]);
			}
		}
		// unhighlight previously highlighted site
		if (document.querySelector('.active-site')) { document.querySelector('.active-site').classList.remove('active-site'); }
		// highlight selected folder
		document.querySelector('#feed-list li[data-folder="'+theFolder+'"]').classList.add('active-site');
		window.location.hash = '';
		this.rebuiltTree(newList);
		this.openAllButton.classList.remove('unfold');
		return false;
	}


	// rebuilt the list with all the items
	this.sortAll = function() {
		// unhighlight previously selected site
		document.querySelector('.active-site').classList.remove('active-site');
		// highlight favs
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
		var markWhat = this.feedsList.querySelector('.active-site');

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

			// recount unread items in the list of sites/folders
//			for (var i = 0, liList = document.querySelectorAll('#feed-list li:not(.fav-feeds)'), len = liList.length ; i < len ; i++) {
//				liList[i].dataset.nbrun = 0;
//				liList[i].dataset.nbtoday = 0;
//				liList[i].querySelector('.counter').firstChild.nodeValue = '(0)';
//			}

			this.sortAll();
		}

		// Mark one FOLDER as read
		else if (markWhat.classList.contains('feed-folder')) {
			var folder = markWhat.dataset.folder;

			// send XHR
			if (!this.markAsReadXHR('folder', folder)) return false;

			// update GLOBAL counter by substracting unread items from the folder

//			var gcount = document.getElementById('global-post-counter');
//			gcount.dataset.nbrun -= markWhat.dataset.nbrun;
//			gcount.firstChild.nodeValue = '('+gcount.dataset.nbrun+')';

			// update TODAY counter by substracting unread items from the folder
//			var todayCount = document.getElementById('today-post-counter');
//			todayCount.dataset.nbrun -= markWhat.dataset.nbtoday;
//			todayCount.firstChild.nodeValue = '('+todayCount.dataset.nbrun+')';

			// mark 0 for that folder
			markWhat.dataset.nbrun = 0;
//			markWhat.dataset.nbtoday = 0;
//			markWhat.querySelector('.counter').firstChild.nodeValue = '(0)';

			// mark 0 for the sites in that folder
			var sitesInFolder = markWhat.querySelectorAll('ul > li');
			for (var i = 0, len = sitesInFolder.length ; i < len ; i++) {
				sitesInFolder[i].dataset.nbrun = 0;
//				sitesInFolder[i].querySelector('.counter').firstChild.nodeValue = '(0)';
			}

			// mark items as read in list
			for (var i = 0, len = this.feedList.length ; i < len ; i++) {
				if (this.feedList[i].folder == folder) {
					this.feedList[i].statut = 0;
				}
			}

			this.sortItemsByFolder(folder);
		}

		// else… mark one SITE as read
		else if (markWhat.classList.contains('feed-site')) {
			var siteHash = markWhat.dataset.feedHash;
			var site = this.feedsList.querySelector('li[data-feed-hash="'+siteHash+'"]').title;

			// send XHR
			if (!this.markAsReadXHR('site', site)) return false;

			// update global counter by substracting unread items from the site
			var gcount = document.getElementById('global-post-counter');
			gcount.dataset.nbrun -= markWhat.dataset.nbrun;
//			gcount.firstChild.nodeValue = '('+gcount.dataset.nbrun+')';

			// update TODAY counter by substracting unread items from the site
//			var todayCount = document.getElementById('today-post-counter');
//			todayCount.dataset.nbrun -= markWhat.dataset.nbtoday;
//			todayCount.firstChild.nodeValue = '('+todayCount.dataset.nbrun+')';

			// if site is in a folder, update amount of unread for that folder too
			var parentFolder = markWhat.parentNode.parentNode;
			if (parentFolder.dataset.folder) {
				parentFolder.dataset.nbrun -= markWhat.dataset.nbrun;
//				parentFolder.dataset.nbtoday -= markWhat.dataset.nbtoday;
//				parentFolder.querySelector('.counter').firstChild.nodeValue = '('+parentFolder.dataset.nbrun+')';
			}

			// mark items as read in list
			for (var i = 0, len = this.feedList.length ; i < len ; i++) {
				if (this.feedList[i].feedhash == siteHash) {
					this.feedList[i].statut = 0;
				}
			}

			// mark 0 for that folder folder’s unread counters
			markWhat.dataset.nbrun = markWhat.dataset.nbtoday = 0;
//			markWhat.querySelector('.counter').firstChild.nodeValue = '(0)';

			this.sortItemsBySite(siteHash);
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
		var gcount = document.getElementById('global-post-counter');
		gcount.dataset.nbrun -= 1;
//		gcount.firstChild.nodeValue = '('+gcount.dataset.nbrun+')';
		// decrement site & site.today counter
		var site = this.feedsList.querySelector('li[data-feed-hash="'+thePost.dataset.sitehash+'"]');
		site.dataset.nbrun -= 1;

//		if (thePost.dataset.postdate >= this.ymd000) {
//			site.dataset.nbtoday -= 1;
//			var todayCount = document.getElementById('today-post-counter');
//			todayCount.dataset.nbrun -= 1;
//			todayCount.firstChild.nodeValue = '('+todayCount.dataset.nbrun+')';
//		}

//		site.querySelector('.counter').firstChild.nodeValue = '('+site.dataset.nbrun+')';
		// decrement folder (if any)
		var parentFolder = site.parentNode.parentNode;
		if (parentFolder.dataset.folder) {
			parentFolder.dataset.nbrun -= 1;
//			parentFolder.querySelector('.counter').firstChild.nodeValue = '('+parentFolder.dataset.nbrun+')';
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
		xhr.open('POST', '_rss.ajax.php', true);

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
		xhr.open('POST', '_rss.ajax.php', false);

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
		var favCounter = document.getElementById('favs-post-counter')
		favCounter.dataset.nbrun = parseInt(favCounter.dataset.nbrun) + ((thePost.dataset.isFav == 1) ? 1 : -1 );
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
		xhr.open('POST', '_rss.ajax.php', true);

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
	this.refreshAllFeeds = function() {
		var _refreshButton = this.refreshButton;
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
		xhr.open('POST', '_rss.ajax.php', true);

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
		xhr.open('POST', '_rss.ajax.php', true);

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
		xhr.open('POST', '_rss.ajax.php');

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


/* in RSS config : mark a feed as "to remove" */
function markAsRemove(link) {
	var li = link.parentNode.parentNode;
	li.classList.add('to-remove');
	li.getElementsByClassName('remove-feed')[0].value = 0;
}
function unMarkAsRemove(link) {
	var li = link.parentNode.parentNode;
	li.classList.remove('to-remove');
	li.getElementsByClassName('remove-feed')[0].value = 1;
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
	this.notesList = Notes.list;
	// init to "false" a flag aimed to determine if changed have yet to be saved to server
	this.hasUpdated = false;

	// get some DOM elements
	this.noteContainer = document.getElementById('list-notes');
	this.domPage = document.getElementById('page');
	this.notifNode = document.getElementById('message-return');

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

		// populates the new list
		for (var i = 0, len = NotesData.length ; i < len ; i++) {
			var item = NotesData[i];

			// note block
			var divNote = document.createElement('div');
			divNote.id = 'i_' + item.id;
			divNote.dataset.updateAction = item.action;
			divNote.classList.add('notebloc');
			divNote.style.backgroundColor = item.color;
			divNote.dataset.indexId = i;
			divNote.addEventListener('click',
			function(e) {
				_this.showNotePopup(NotesData[this.dataset.indexId]);
			} );

			// note title
			var title = document.createElement('div');
			title.classList.add('title');
			var h2 = document.createElement('h2');
			h2.appendChild(document.createTextNode(item.title));
			title.appendChild(h2);
			divNote.appendChild(title);

			// note main content
			var divContent = document.createElement('div');
			divContent.classList.add('content');
			divContent.appendChild(document.createTextNode(item.content));
			divContent.dataset.id = item.id;
			divNote.appendChild(divContent);

			// add to page
			this.noteContainer.appendChild(divNote);

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
			"action": 'newNote',
		};

		this.showNotePopup(newNote);
	}


	this.showNotePopup = function(item) {
		if (document.getElementById('i_' + item.id) ) {
			var noteNode = document.getElementById('i_' + item.id);
			noteNode.style.opacity = 0;
		}
		var popupWrapper = document.createElement('div');
		popupWrapper.id = 'popup-wrapper';

		// TODO : make this a "form" and put this.markAsEdit() on the "onsubmit" action.
		var popup = document.createElement('div');
		popup.id = 'popup';
		popup.classList.add('popup-note');
		popup.style.backgroundColor = item.color;

		popupWrapper.appendChild(popup);
		popupWrapper.addEventListener('click',
			function(e) {
				// clic is outside popup: closes popup
				if (e.target == this) {
					popupWrapper.parentNode.removeChild(popupWrapper);
					if (noteNode) noteNode.style.opacity = null;
				}
			} );


		// note title
		var title = document.createElement('div');
		title.classList.add('title');
		// h2 title
		var h2 = document.createElement('h2');
		h2.contentEditable = true;
		h2.dataset.id = item.id;
		h2.appendChild(document.createTextNode(item.title));
		title.appendChild(h2);
		popup.appendChild(title);

		// note main content
		var textarea = document.createElement('textarea');
		textarea.classList.add('content');
		textarea.appendChild(document.createTextNode(item.content));
		textarea.cols = 30;
		textarea.rows = 8;
		textarea.dataset.id = item.id;
		textarea.placeholder = 'Content';
		popup.appendChild(textarea);


		// date
		var noteDate = document.createElement('div');
		noteDate.classList.add('date');
		noteDate.appendChild(document.createTextNode(BTlang.createdOn + ' ' + Date.dateFromYMDHIS(item.id).toLocaleDateString('fr', {weekday: "long", month: "long", day: "numeric"}) ));
		popup.appendChild(noteDate);

		// note buttons
		var ctrls = document.createElement('div');
		ctrls.classList.add('noteCtrls');
		// color button
		var colorBtn = document.createElement('button');
		colorBtn.type = 'button';

		colorBtn.classList.add('colorIcon');
		ctrls.appendChild(colorBtn);
		var colorLst = document.createElement('ul');
		colorLst.dataset.id = item.id;
		colorLst.addEventListener('click',
			function(e) {
				if (e.target.tagName == 'LI') {
					_this.changeColor(item, e);
				}
			});
		colorLst.classList.add('colors');
		var colorsSet = ['#ffffff', '#FF8A80', '#FFD180', '#FFFF8D', '#CCFF90', '#A7FFEB', '#80D8FF', '#82B1FF', '#F8BBD0', '#CFD8DC'];
		for (var ili=0; ili<9; ili++) {
			var li = document.createElement('li');
			li.style.backgroundColor = colorsSet[ili];
			colorLst.appendChild(li);
		}
		ctrls.appendChild(colorLst);
		// suppr button
		var supprBtn = document.createElement('button');
		supprBtn.type = 'button';
		supprBtn.classList.add('supprIcon');
		supprBtn.dataset.id = item.id;
		supprBtn.addEventListener('click',
			function() {
				_this.markAsDeleted(item);
			});
		ctrls.appendChild(supprBtn);

		// save button
		var span = document.createElement('span');
		span.classList.add('submit-bttns');

		var button = document.createElement('button');
		button.classList.add('submit', 'button-cancel');
		button.type = "button";
		button.addEventListener('click',
			function() {
				// closes popup
				popupWrapper.parentNode.removeChild(popupWrapper);
				if (noteNode) noteNode.style.opacity = null;
			})
		button.appendChild(document.createTextNode(BTlang.cancel));
		span.appendChild(button);

		var button = document.createElement('button');
		button.classList.add('submit', 'button-submit');
		button.dataset.id = item.id;
		button.type = "button";
		button.name = "editer";
		button.addEventListener('click',
			function() {
				// mark as edited
				_this.markAsEdited(item);
				// closes popup
				popupWrapper.parentNode.removeChild(popupWrapper);
				if (noteNode) noteNode.style.opacity = null;

			})
		button.appendChild(document.createTextNode(BTlang.save));
		span.appendChild(button);

		ctrls.appendChild(span);
		popup.appendChild(ctrls);

		// add to page
		this.domPage.appendChild(popupWrapper);

		textarea.focus();
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

		item.content = popup.querySelector('.content').value;
		item.title = popup.querySelector('h2').firstChild.nodeValue;
		item.color = window.getComputedStyle(popup).backgroundColor;

		// note is new:
		if (!isEdit) {
			this.rebuiltNotesWall([item]); // append it to #notes-list
			this.notesList.push(item);     // append it to the main List
		}

		// note is only edited
		else {
			var theNote = document.getElementById('i_'+item.id);
			theNote.style.backgroundColor = item.color;
			theNote.querySelector('.content').firstChild.nodeValue = item.content;
			theNote.querySelector('h2').firstChild.nodeValue = item.title;
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

		// remove item from page too, with little animation
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
		/*if (document.getElementById('i_' + item.id)) {
			this.noteContainer.querySelector('#i_'+item.id + "> .notebloc").style.backgroundColor = newColor;
		}*/
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
		xhr.open('POST', '_notes.ajax.php', true);

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

	// init JSON List
	this.eventsList = Events.list;
	// init a flag aimed to determine if changes have yet to be pushed
	this.hasUpdated = false;

	// get some DOM elements
	this.calWrap = document.getElementById('calendar-wrapper');
	this.domPage = document.getElementById('page');
	this.notifNode = document.getElementById('message-return');

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

	/**************************************
	 * Draw the MONTHLY calendar
	*/
	this.rebuiltMonthlyCal = function() {
			// empties the node
			if (document.getElementById('calendar-table')) {
				this.calWrap.removeChild(document.getElementById('calendar-table'));
			}

			// reference datetime
			var date = initDate;
			var dateToday = new Date();

			/*******************
			** the frame + thead
			*/
			// the calendar block
			var calendar = document.createElement('table');
			calendar.id = 'calendar-table';
			calendar.classList.add('monthDisplay');

			// thead-tr with prev-next buttons
			var calThead = calendar.createTHead();
			var tr = calThead.insertRow();
			tr.classList.add('monthrow');

			td = tr.insertCell();
			td.id = 'year';
			td.colSpan = 3;

			var button = document.createElement('button');
			button.id = 'show-full-year';
			button.addEventListener('click', function(e){ _this.rebuiltYearlyCal(); });
			td.appendChild(button);
			td.appendChild( (document.createElement('span')).appendChild(document.createTextNode(date.getFullYear())).parentNode);

			td = tr.insertCell();
			td.id = 'month';
			td.colSpan = 4;
			var button = document.createElement('button');
			button.id = 'prev-month';
			button.addEventListener('click',
				function(e){
					initDate = new Date(initDate.getFullYear(), initDate.getMonth()-1, initDate.getDate());
					_this.rebuiltMonthlyCal();
				});
			td.appendChild(button);
			td.appendChild( (document.createElement('span')).appendChild(document.createTextNode( date.toLocaleDateString('fr-FR', {month: "long"}) )).parentNode);



			var button = document.createElement('button');
			button.id = 'next-month';
			button.addEventListener('click',
				function(e){
					initDate = new Date(initDate.getFullYear(), initDate.getMonth()+1, initDate.getDate());
					_this.rebuiltMonthlyCal();
				});
			td.appendChild(button);

			// thead-tr with date abbr
			var tr = calThead.insertRow();
			tr.classList.add('dayAbbr');
			for (var jour of (BTlang.lmmjvsd).split('')) {
				tr.appendChild((document.createElement('th')).appendChild(document.createTextNode(jour)).parentNode);
			}

			var calBody = document.createElement('tbody');
			calendar.appendChild(calBody);


			/*******************
			** the days
			*/
			var firstDay = (new Date(date.getFullYear(), date.getMonth(), 1));
			var lastDay = (new Date(date.getFullYear(), date.getMonth() + 1, 0));

			// if month is not a complet <table>, complete <table> with days from prev/next month
			// in JS Sunday = 0th day of week. I need 7th, since sunday is last collumn in table
			var nbDaysPrevMonth = (firstDay.getDay() == 0) ? 7 : firstDay.getDay();
			var nbDaysNextMonth = 7 - ((lastDay.getDay() == 0) ? 7 : lastDay.getDay());

			var firstDayOfCal = new Date(firstDay); firstDayOfCal.setDate(-nbDaysPrevMonth+2);
			var lastDayOfCal = new Date(lastDay); lastDayOfCal.setDate(lastDay.getDate()+nbDaysNextMonth);

			for (var cell = 1; cell < lastDay.getDate() + nbDaysPrevMonth + nbDaysNextMonth ; cell++) {
				var dateOfCell = new Date(date.getFullYear(), date.getMonth(), cell-(nbDaysPrevMonth-1) );

				// starts new line every %7 days
				if (cell % 7 == 1) {
					var tr = calBody.appendChild(document.createElement("tr"));
				}


				var td = document.createElement('td');
				if (!td.previousSibling) td.dataset.week = dateOfCell.getWeekNumber();


				td.id = 'i' + dateOfCell.getMonth() + dateOfCell.getDate();

				if (dateOfCell.getDate() == (dateToday.getDate())) {
					td.classList.add('isToday');
				}
				if (dateOfCell < dateToday) {
					td.classList.add('isPast');
				}
				if (dateOfCell < firstDay) {
					td.classList.add('isPrevMonth');
				}
				if (dateOfCell > lastDay) {
					td.classList.add('isNextMonth');
				}


				var button = document.createElement('button');
				button.appendChild(document.createTextNode( dateOfCell.getDate() ) );
				button.dataset.date = dateOfCell.dateToISO8601String();
				button.addEventListener('click',
					function(e){
						var oldInitDate = initDate;
						initDate = new Date(this.dataset.date);
						if (oldInitDate.getMonth() != initDate.getMonth() ) {
							_this.rebuiltMonthlyCal();
						}
					});
				td.appendChild(button);
				tr.appendChild(td);

			}

			this.calWrap.appendChild(calendar);

			/*******************
			** append the events to the calendar
			*/
			for (var i = 0, len = this.eventsList.length ; i < len ; i++) {
				var eventDateTime = new Date(this.eventsList[i].date);

				// is event flaged as deleted?
				if (this.eventsList[i].action == "deleteEvent") continue;
				// is event in currently displayed month?
				if (!( eventDateTime >= firstDayOfCal && eventDateTime <= lastDayOfCal ) ) continue;

				var selectCell = document.getElementById('i' + eventDateTime.getMonth() + eventDateTime.getDate());
				if (selectCell.classList.contains('hasEvent')) {
					selectCell.dataset.nbEvents++;
				}
				else {
					selectCell.dataset.nbEvents = 1;

					selectCell.classList.add('hasEvent');
					selectCell.firstChild.addEventListener('click',
						function() {
							_this.rebuiltDailySched(this.dataset.date);
						})
				}
			}

			// saves calendar size
			this.calWrap.dataset.calendarSizeW = calendar.getBoundingClientRect().width;
			this.calWrap.dataset.calendarSizeH = calendar.getBoundingClientRect().height;
	}
	// Init events lists (default in "calendar" view)
	this.rebuiltMonthlyCal();


	/**************************************
	 * Draw the YEARLY calendar
	*/
	this.rebuiltYearlyCal = function() {
			// empties the node
			if (document.getElementById('calendar-table')) {
				this.calWrap.removeChild(document.getElementById('calendar-table'));
			}

			// reference datetime
			var date = initDate;
			var dateToday = new Date();
			var tempDate = new Date();

			/*******************
			** the frame + thead
			*/
			// the calendar block
			var calendar = document.createElement('table');
			calendar.id = 'calendar-table';
			calendar.classList.add('yearDisplay');

			// thead-tr with prev-next buttons
			var calThead = calendar.createTHead();
			var tr = calThead.insertRow();
			tr.classList.add('monthrow');

			var td = tr.insertCell();
			td.id = 'year';
			td.colSpan = 4;
			var button = document.createElement('button');
			button.id = 'prev-year';
			button.addEventListener('click',
				function(e){
					initDate = new Date(initDate.getFullYear()-1, initDate.getMonth(), initDate.getDate());
					_this.rebuiltYearlyCal();
				});
			td.appendChild(button);
			td.appendChild( (document.createElement('span')).appendChild(document.createTextNode( date.getFullYear() )).parentNode);

			var button = document.createElement('button');
			button.id = 'next-year';
			button.addEventListener('click',
				function(e){
					initDate = new Date(initDate.getFullYear()+1, initDate.getMonth(), initDate.getDate());
					_this.rebuiltYearlyCal();
				});
			td.appendChild(button);

			var calBody = document.createElement('tbody');
			calendar.appendChild(calBody);

			calendar.style.height = this.calWrap.dataset.calendarSizeH + 'px';
			calendar.style.width = this.calWrap.dataset.calendarSizeW + 'px';

			/*******************
			** the Months
			*/

			for (var cell = 0; cell < 12 ; cell++) {

				// starts new line every %4 months
				if (cell % 4 == 0) {
					var tr = calBody.appendChild(document.createElement("tr"));
				}

				var td = tr.appendChild(document.createElement('td'));
				td.dataset.datetime = (new Date(date.getFullYear(), cell, date.getDate() ) );
				if (cell == (dateToday.getMonth())) {
					td.classList.add('isToday');
				}
				var button = document.createElement('button');
				tempDate.setMonth(cell);
				button.appendChild(document.createTextNode( tempDate.toLocaleDateString('fr-FR', {month: "short"}) ));

				button.addEventListener('click', function(e){
					initDate = new Date( this.parentNode.dataset.datetime );
					_this.rebuiltMonthlyCal();
				});
				td.appendChild(button);
			}
			this.calWrap.appendChild(calendar);
	}


	this.rebuiltDailySched = function(date) {
		date = new Date(date);
		var dewNode = document.getElementById('daily-events-wrapper');
		// empties the node
		if (dewNode) {
			while (dewNode.firstChild) {dewNode.removeChild(dewNode.firstChild);}
		}


		var dailyEvs = document.createElement('div');
		dailyEvs.id = 'daily-events';

		for (var i = 0, len = this.eventsList.length ; i < len ; i++) {
			var item = this.eventsList[len-1-i];
			if (item.action == 'deleteEvent') continue;
			var itemDate = new Date(item.date); itemDate.setHours(0, 0, 0, 0);

			// if the event is today, add a row to div.
			if (itemDate.toDateString() == date.toDateString()) {
				var itemDiv = document.createElement("div");
				//itemDiv.dataset.id = item.id;
				itemDiv.dataset.indexId = len-1-i;
				itemDiv.addEventListener('click',
					function() {
						_this.showEventPopup(_this.eventsList[this.dataset.indexId]);
					} );

				if (new Date(item.date) >= new Date()) {
					itemDiv.classList.add('futureEvent');
				} else {
					itemDiv.classList.add('pastEvent');
				}
				var itemDateEvent = document.createElement("div");
				itemDateEvent.classList.add('eventDate');
				itemDateEvent.appendChild(document.createTextNode(new Date(item.date).toLocaleTimeString('fr', {hour: 'numeric', minute: 'numeric'})));
				itemDiv.appendChild(itemDateEvent);

				var itemSummaryEvent = document.createElement("div");
				itemSummaryEvent.classList.add('eventSummary');
				itemSummaryEvent.appendChild(document.createElement('h2').appendChild(document.createTextNode(item.title)).parentNode );
				if (item.content) {
					itemSummaryEvent.appendChild(document.createElement('div').appendChild(document.createTextNode(item.content)).parentNode );
				}

				itemDiv.appendChild(itemSummaryEvent);

				dailyEvs.appendChild(itemDiv);
			}
		}

		if (dailyEvs.firstChild) {
			dewNode.appendChild( document.createElement('p').appendChild(document.createTextNode(date.toLocaleDateString('fr', {weekday: "long", month: "long", day: "numeric"}) + ' :' )).parentNode );
			dewNode.appendChild(dailyEvs);
		}
	}


	/**************************************
	 * Built the event wall (bellow the calendar)
    * TODO : add buttons (like in 'fichiers') to filter events : past, today, to come…
	*/
	this.rebuiltEventsWall = function(EventsData) {
		if (0 === EventsData.length) return false;

		// populates the new list
		for (var i = 0, len = EventsData.length ; i < len ; i++) {
			var item = EventsData[i];
			var dateToday = new Date();
			var row = document.createElement('tr');
			var dateItem = new Date(item.date);

			if (dateToday > dateItem) {
				row.classList.add('pastEvent', 'pastEventHidden');
			}

			row.id = 'i_' + item.id;
			//row.dataset.date = item.date;
			row.dataset.j = i;
			row.addEventListener('click',
				function(){
					_this.showEventPopup(_this.eventsList[this.dataset.j]);
				} );

			var cellDate = document.createElement('td');
			cellDate.appendChild(document.createTextNode( dateItem.toLocaleDateString('fr-FR', {weekday: "short", month: "short", day: "numeric"}) ) );

			row.appendChild(cellDate);

			var cellName = document.createElement('td');
			cellName.appendChild(document.createTextNode(item.title));
			row.appendChild(cellName);

			var cellDescr = document.createElement('td');
			cellDescr.appendChild(document.createTextNode(item.content));
			row.appendChild(cellDescr);

			document.getElementById('event-list').getElementsByTagName('tbody')[0].appendChild(row);

		}

		if (document.querySelector('#event-list .pastEventHidden')) {
			var button = document.createElement('button');
			button.appendChild(document.createTextNode(BTlang.questionPastEvents));
			button.classList.add('submit', 'button-cancel');
			button.addEventListener('click',
				function() {
					var pastEvents = document.querySelectorAll('#event-list .pastEventHidden');
					for (var i=0, len = pastEvents.length ; i < len ; i++) pastEvents[i].classList.remove('pastEventHidden');
					button.parentNode.removeChild(button);
				});
			document.getElementById('events-section').appendChild(button);
		}
		return false;
	}
	// init the whole DOM list
	this.rebuiltEventsWall(this.eventsList);


	/**************************************
	 * Displays the "show event" popup
	*/
	this.showEventPopup = function(item) {
		var popupWrapper = document.createElement('div');
		popupWrapper.id = 'popup-wrapper';

		var popup = document.createElement('div');

		popup.id = 'popup';
		popup.classList.add('popup-event');
		popupWrapper.appendChild(popup);

		popupWrapper.addEventListener('click',
			function(e){
				// clic is outside popup: closes popup
				if (e.target == this) {
					popupWrapper.parentNode.removeChild(popupWrapper);
				}
			} );

		// Popup > Title
		var title = document.createElement('div');
		title.classList.add('event-title');
		title.appendChild( (document.createElement('span')).appendChild(document.createTextNode(item.title)).parentNode );

		// Popup > Title > menu options
		var options = document.createElement('div');
		options.classList.add('item-menu-options');
		var optionsUl = document.createElement('ul');
		options.appendChild(optionsUl);
		var optionsUlLi = document.createElement('li');
		optionsUl.appendChild(optionsUlLi);
		var optionsUlLiA = document.createElement('a');
		optionsUlLiA.appendChild(document.createTextNode(BTlang.supprimer));
		optionsUlLiA.addEventListener('click',
			function(e){
				_this.markAsDeleted(item);
			} );
		optionsUlLi.appendChild(optionsUlLiA);
		title.appendChild(options);

		// Popup > Title > Cancel button
		var button = document.createElement('button');
		button.classList.add('submit', 'button-cancel');
		button.type = "button";
		button.addEventListener('click',
			function() {
				popupWrapper.parentNode.removeChild(popupWrapper);
			} );
		title.appendChild(button);

		// Popup > Title > Édit Button
		var editButton = document.createElement('button');
		editButton.classList.add('button-edit');
		editButton.addEventListener('click',
			function() {
				_this.showEventEditPopup(item);
			});
		title.appendChild(editButton);

		popup.appendChild(title);

		// Popup > event info
		var content = document.createElement('div');
		content.classList.add('event-content');

		var ul = document.createElement('ul');
		var li = document.createElement('li');
		li.classList.add('event-time');

		li.appendChild( (document.createElement('span')).appendChild(document.createTextNode( (new Date(item.date)).toLocaleDateString('fr-FR', {weekday: "long", year: "numeric", month: "long", day: "numeric"}) )).parentNode );
		li.appendChild( (document.createElement('span')).appendChild(document.createTextNode( (new Date(item.date)).toLocaleTimeString('fr-FR', {hour: '2-digit', minute:'2-digit'} ) )).parentNode );
		ul.appendChild(li);

		if (item.loc) {
			var li = document.createElement('li');
			li.classList.add('event-loc');
			li.appendChild(document.createTextNode(item.loc));
			ul.appendChild(li);
		}

		var li = document.createElement('li');
		li.classList.add('event-description');
		li.appendChild(document.createTextNode(item.content));
		ul.appendChild(li);

		content.appendChild(ul);
		popup.appendChild(content);

		this.domPage.appendChild(popupWrapper);
	}


	/**************************************
	 * Displays the "Edit event" popup (also for "new" events)
	*/
	this.showEventEditPopup = function(item) {
		// if any popup : remove it first
		if (document.getElementById('popup-wrapper')) {
			document.getElementById('popup-wrapper').parentNode.removeChild(document.getElementById('popup-wrapper'));
		}

		var popupWrapper = document.createElement('div');
		popupWrapper.id = 'popup-wrapper';

		var popup = document.createElement('form');
		popup.id = 'popup';
		popup.classList.add('popup-edit-event');
		//popup.dataset.id = item.id;
		popup.addEventListener('submit',
			function() {
				_this.markAsEdited(item);
				// closes popup
				popupWrapper.parentNode.removeChild(popupWrapper);
			})
		popupWrapper.appendChild(popup);

		popupWrapper.addEventListener('click',
			function(e){
				// clic is outside popup: closes popup
				if (e.target == this) {
					popupWrapper.parentNode.removeChild(popupWrapper);
				}
			} );

		// title block
		var title = document.createElement('div');
		title.classList.add('event-title');

		// cancel button
		var button = document.createElement('button');
		button.classList.add('submit', 'button-cancel');
		button.type = "button";
		button.addEventListener('click',
			function() {
				popupWrapper.parentNode.removeChild(popupWrapper);
			} );
		title.appendChild(button);


		// save button
		var button = document.createElement('button');
		button.classList.add('submit', 'button-submit');
		button.type = "submit";
		button.name = "editer";
		button.appendChild(document.createTextNode(BTlang.save));

		title.appendChild(button);

		var titleInput = document.createElement('input');
		titleInput.value = item.title;
		titleInput.type = 'text';
		titleInput.classList.add('text');
		titleInput.name = 'itemTitle';
		titleInput.required = 'required';
		titleInput.placeholder = BTlang.add_title;

		title.appendChild(titleInput);
		popup.appendChild(title);

		var contentDate = document.createElement('div');
		contentDate.classList.add('event-content');
		contentDate.classList.add('event-content-date');

		// "all day" form
		var p = document.createElement('p');

		var checkbox = document.createElement('input');
		checkbox.type = "checkbox";
		checkbox.name = "allDay";
		checkbox.id = "allDay";
		checkbox.addEventListener('change',
			function() {
				var timeInput = document.getElementById('time');
				if (this.checked) {
					timeInput.classList.add('hidden');
					timeInput.value = '00:00';
				}
				else {
					timeInput.classList.remove('hidden');
				}
			} );


		checkbox.checked = '';
		checkbox.classList.add('checkbox-toggle');
		var label = document.createElement('label').appendChild(document.createTextNode(BTlang.entireDay)).parentNode;
		label.htmlFor = "allDay"
		p.appendChild(checkbox)
		p.appendChild(label)

		contentDate.appendChild(p);

		// date & time
		var p = document.createElement('p');
		var inputT = document.createElement('input');
		inputT.classList.add('text');
		inputT.type = 'time';
		inputT.required = 'required';
		inputT.value = item.date.substr(11, 5); // FIXME don’t do SUBSTR : use date()
		inputT.name = 'time';
		inputT.id = 'time';

		var inputD = document.createElement('input');
		inputD.classList.add('text');
		inputD.type = 'date';
		inputD.required = 'required';
		inputD.value = item.date.substr(0, 10); // FIXME don’t do SUBSTR : use date()
		inputD.name = 'date';
		inputD.id = 'date';

		p.appendChild(inputD);
		p.appendChild(inputT);
		contentDate.appendChild(p);

		var contentLoc = document.createElement('div');
		contentLoc.classList.add('event-content');
		contentLoc.classList.add('event-content-loc');
		var locInput = document.createElement('input');
		locInput.placeholder = BTlang.add_location;
		locInput.type = 'text';
		locInput.classList.add('text');
		locInput.name = 'loc';
		locInput.value = item.loc;
		contentLoc.appendChild(locInput);


		var contentDescr = document.createElement('div');
		contentDescr.classList.add('event-content');
		contentDescr.classList.add('event-content-descr');
		var descrInput = document.createElement('textarea');
		descrInput.placeholder = BTlang.add_description;
		descrInput.cols = "30"; descrInput.rows = "3";
		descrInput.classList.add('text');
		descrInput.name = 'descr';
		descrInput.value = item.content;
		contentDescr.appendChild(descrInput);


		popup.appendChild(contentDate);
		popup.appendChild(contentLoc);
		popup.appendChild(contentDescr);

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

		item.title = popup.querySelector('.event-title input').value;;
		item.content = popup.querySelector('.event-content-descr .text').value;
		item.loc = popup.querySelector('.event-content-loc .text').value;
		var newDate = new Date(document.getElementById('date').value + " " + document.getElementById('time').value);
		item.date = newDate.dateToISO8601String();

		// event is new:
		if (!isEdit) {
			item.id = item.date.substr(0,19).replace(/[:T-]/g, ''); // give it an ID
			// TODO : place it in at the right place in the table.
			this.rebuiltEventsWall([item]);                         // append it to #event-list
			this.eventsList.push(item);                             // append it to the eventsList{}

		}

		// event is only edited
		else {
			// update display in #event-list // TODO : also update "hasEvents" in calendar.
			var theRow = document.getElementById('i_'+ item.id);
			theRow.getElementsByTagName('td')[0].firstChild.nodeValue = newDate.toLocaleDateString('fr-FR', {weekday: "short", month: "short", day: "numeric"})
			theRow.getElementsByTagName('td')[1].firstChild.nodeValue = item.title;
			theRow.getElementsByTagName('td')[2].firstChild.nodeValue = item.content;
			if (newDate < new Date()) {
				theRow.classList.add('pastEvent');
			} else {
				theRow.classList.remove('pastEvent');
			}
		}

		// hide from daily schedule (if sched is displayed for that day, only)
		this.rebuiltDailySched(item.date);

		// rebuilt Calendar to take changes into account. // TODO: perhaps not rebuilt cal, but only add/move buttons (for perf) ?
		this.rebuiltMonthlyCal();

		item.action = item.action || 'updateEvent';

		// raises global "updated" flag.
		this.raiseUpdateFlag(true);
	}


	/**************************************
	 * Creates a new event, init it, display it and add it to list.
	*/
	this.addNewEvent = function() {
		var date = initDate;
		var newEv = {
			"id": '',
			"date": date.dateToISO8601String(),
			"title": '',
			"content": '',
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
		// hide from list
		var theRow = document.getElementById('i_'+ item.id);
		theRow.parentNode.removeChild(theRow);

		// hide from daily schedule (if sched is displayed for that day, only)
		if (initDate.getDate() == new Date(item.date).getDate()) {
			this.rebuiltDailySched(item.date);
		}

		// rebuilt Calendar to take changes into account. // TODO: perhaps not rebuilt cal, but only add/move buttons (for perf) ?
		this.rebuiltMonthlyCal();

		// close popup
		document.getElementById('popup-wrapper').parentNode.removeChild(document.getElementById('popup-wrapper'));

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
	 * AJAX call to save events to DB
	*/
	this.saveEventsXHR = function() {
		loading_animation('on');
		// only keep modified events
		var toSaveEvents = Array();
		for (var i=0, len=this.eventsList.length; i<len ; i++) {
			if (this.eventsList[i].action && 0 !== this.eventsList[i].action.length) {
				var ev = this.eventsList[i];
				ev.ymdhisDate = ev.date.substr(0,19).replace(/[:T-]/g, '');
				toSaveEvents.push(ev);
			}
		}

		// make a string out of it
		var eventsDataText = JSON.stringify(toSaveEvents);

		var notifDiv = document.createElement('div');
		// create XHR
		var xhr = new XMLHttpRequest();
		xhr.open('POST', '_agenda.ajax.php', true);

		// onload
		xhr.onload = function() {
			if (this.responseText.indexOf("Success") == 0) {
				loading_animation('off');
				_this.raiseUpdateFlag(false);
				// adding notif
				notifDiv.textContent = BTlang.confirmEventsSaved;
				notifDiv.classList.add('confirmation');
				document.getElementById('top').appendChild(notifDiv);

				// resetq flags on events
				for (var i=0, len=toSaveEvents.length; i<len ; i++) {
					toSaveEvents[i].action = "";
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

































