//'use strict';

var USched = function() {
	let nxid = -1;

	const sampElm = (elm) => {
		let selm = elm.parentElement;
		let samp;
		for (let i=0; i<4; i++) {
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
			<span><input type="text" name="category_name[]" value="New Category" class="ecname" oninput="USched.show_cat(this)" onchange="USched.show_cat(this)" /></span>\
			<span class="gcent"><input type="color" name="category_txcolor[]" value="#000000" oninput="USched.show_tx(this)" onchange="USched.show_tx(this)" /></span>\
			<span class="gcent"><input type="color" name="category_bgcolor[]" value="#FFFFFF" oninput="USched.show_bg(this)" onchange="USched.show_bg(this)" /></span>\
			<span></span>\
			<span class="catsamp">New Category</span>';
			elm.previousElementSibling.innerHTML += htm;
			nxid--;
		},
		show_tx: (elm) => { sampElm(elm).style.color = elm.value; },
		show_bg: (elm) => { sampElm(elm).style.backgroundColor = elm.value; },
		show_cat: (elm) => { console.log(elm.value); sampElm(elm).innerText = elm.value; },
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
		},
		showSMS: () => {
			const modalElement = document.getElementById('dynamicContentModal');
			if (!modalElement) return;
		
			const modalTitle = modalElement.querySelector('.modal-title');
			const modalBody = document.getElementById('modalBody');
		
			// Instantiate Bootstrap modal instance
			const bootstrapModal = new bootstrap.Modal(modalElement);
			const fetchUrl = Joomla.getOptions('Usersched').rawURL+'&task=Raw.showsms';
			
			// 1. Reset state to showing loading indicator
			modalTitle.textContent = 'Please Wait';
			modalBody.innerHTML = `
				<div class="text-center py-3">
					<div class="spinner-border text-primary" role="status"></div>
					<p class="mt-2">Fetching content...</p>
				</div>`;
			
			// Open the modal container right away
			bootstrapModal.show();

			try {
				fetch(fetchUrl, {method: 'GET'})
				.then(resp => { if (!resp.ok) throw new Error(`HTTP ${resp.status} `+resp.headers.get('errmsg','')); return resp.json() })
				.then(data => {
					console.log(data);
					modalTitle.textContent = data.title || 'Details';
					modalBody.innerHTML = data.html || data.message || data.error || 'No content returned.';
				})
				.catch(err => alert('Failure: '+err));
			} catch (error) {
				// Handle fetch failures gracefully
				modalTitle.textContent = 'Error';
				modalBody.innerHTML = `<div class="alert alert-danger">Failed to load content. Please try again.</div>`;
				console.error('Fetch Error:', error);
			}
		}
	};

}();
