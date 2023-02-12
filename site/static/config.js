//'use strict';

var USched = function() {
	let nxid = -1;

	const sampElm = (elm) => {
		let selm = elm.parentElement;
		let samp;
		for (let i=0; i<3; i++) {
			selm = selm.nextElementSibling;
			if (selm.classList.contains('catsamp')) {
				samp = selm;
				break;
			}
		}
		return samp;
	};

	return {
		addAlertee: (elm) => {
			let htm = '<input type="hidden" name="alertee_id[]" value="-1" />\
			<span><input type="text" name="alertee_name[]" value="" class="aename" /></span>\
			<span><input type="text" name="alertee_email[]" value="" class="aeemail" /></span>\
			<span><input type="text" name="alertee_sms[]" value="" class="aesms" /></span><span></span>';
			elm.previousElementSibling.innerHTML += htm;
		},
		addCategory: (elm) => {
			let htm = '<input type="hidden" name="category_id[]" value="'+nxid+'" />\
			<span><input type="text" name="category_name[]" value="New Category" class="ecname" /></span>\
			<span class="gcent"><input type="color" name="category_txcolor[]" value="#000000" oninput="show_tx(this)" onchange="show_tx(this)" /></span>\
			<span class="gcent"><input type="color" name="category_bgcolor[]" value="#FFFFFF" oninput="show_bg(this)" onchange="show_bg(this)" /></span>\
			<span></span>\
			<span class="catsamp">New Category</span>';
			elm.previousElementSibling.innerHTML += htm;
			nxid--;
		},
		show_tx: (elm) => { sampElm(elm).style.color = elm.value; },
		show_bg: (elm) => { sampElm(elm).style.backgroundColor = elm.value; },
		openTab: (evt,tabId) => {
			let i, tabcontent, tablinks;
			tabcontent = document.getElementsByClassName("tabcontent");
			for (i = 0; i < tabcontent.length; i++) {
				tabcontent[i].style.display = "none";
			}
			tablinks = document.getElementsByClassName("tablinks");
			for (i = 0; i < tablinks.length; i++) {
				tablinks[i].className = tablinks[i].className.replace(" active", "");
			}
			document.getElementById(tabId).style.display = "block";
			evt.currentTarget.className += " active";
		}
	}

}();
