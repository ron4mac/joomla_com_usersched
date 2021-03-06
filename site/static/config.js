function addAlertee (elem) {
	var pg = document.createElement("p");
	pg.innerHTML = '<input type="hidden" name="alertee_id[]" value="-1" />\
	<span class="col1"><input type="text" name="alertee_name[]" value="" class="aename" /></span>\
	<span class="col2"><input type="text" name="alertee_email[]" value="" class="aeemail" /></span>\
	<span class="col3"><input type="text" name="alertee_sms[]" value="" class="aesms" /></span>';
	elem.parentNode.insertBefore(pg,elem);
}

function addCategory (elem) {
	var pg = document.createElement("p");
	pg.innerHTML = '<input type="hidden" name="category_id[]" value="'+addCategory.sid+'" />\
	<span class="col1"><input type="text" name="category_name[]" value="New Category" class="ecname" /></span>\
	<span class="col2"><input class="minicolors" type="text" name="category_txcolor[]" data-cid="tx.'+addCategory.sid+'" data-control="wheel" data-position="bottom" value="" /></span>\
	<span class="col3"><input class="minicolors" type="text" name="category_bgcolor[]" data-cid="bg.'+addCategory.sid+'" data-control="wheel" data-position="bottom" value="" /></span>\
	<span class="col4"><div>&nbsp;</div></span>\
	<span class="catsamp" id="catsamp_'+addCategory.sid+'">New Category</span>';
	elem.parentNode.insertBefore(pg,elem);
	jQuery(pg).find('input.minicolors').each(function() {
			jQuery(this).minicolors({
				control: jQuery(this).attr('data-control') || 'hue',
				position: jQuery(this).attr('data-position') || 'right',
				theme: 'bootstrap'
			});
		//	attachMiniColorPicker(addCategory.sid, this);
		});
	attachColorPicker(addCategory.sid, pg);
	addCategory.sid--;
//	elem.parentNode.insertBefore(pg,elem);
//	attachColorPicker(pg);
}
addCategory.sid = -1;

function attachColorPicker (ix, elm) {
	var inps = jQuery(elm).find('span input');
	var spns = jQuery(elm).find('span.catsamp');
	attachMiniColorPicker(ix, inps[1]);
	attachMiniColorPicker(ix, inps[2]);
	jQuery(inps[0]).data().samplem = spns[0];
	jQuery(inps[0]).on('keyup',function() { jQuery(this).data().samplem.innerHTML = jQuery(this).val(); });
//	inps[1].addEvent("keyup", function(e){ this.innerHTML = e.target.value;}.bind(spns[4]));
}

function attachColorPickers () {
	var pees = jQuery("div.ectable p");
	for (var i=1; i<pees.length; i++) {
		attachColorPicker(i, pees[i]);
	}
}

function attachMiniColorPicker (ix, elm) {
	var jqe = jQuery(elm);
	var acid = jqe.data().cid.split('.');
	jqe.data().sampelm = document.getElementById('catsamp_'+acid[1]);
	if (acid[0]=='tx') {
		jqe.data().minicolorsSettings.change = setCat_tx;
	} else {
		jqe.data().minicolorsSettings.change = setCat_bg;
	}
}

function attachMiniColorPickers () {
	var cins = jQuery('input.minicolors');
	cins.each(attachMiniColorPicker);
}

function setCat_tx (hex, opa) {
	this.data().sampelm.style.color = hex;
}
function setCat_bg (hex, opa) {
	this.data().sampelm.style.backgroundColor = hex;
}


var tabberOptions = {
	'manualStartup': true,
//	'onLoad': function(argsObj) {
//	},
	'onClick': function(argsObj) {
		var i = argsObj.index;
	}
};

function tabberObj (argsObj) {
	var arg;
	this.div = null;
	this.classMain = "stabber";
	this.classMainLive = "stabberlive";
	this.classTab = "stabbertab";
	this.classTabDefault = "stabbertabdefault";
	this.classNav = "stabbernav";
	this.classTabHide = "stabbertabhide";
	this.classNavActive = "stabberactive";
	this.titleElements = ['h2', 'h3', 'h4', 'h5', 'h6'];
	this.titleElementsStripHTML = true;
	this.removeTitle = true;
	this.addLinkId = false;
	this.linkIdFormat = '<tabberid>nav<tabnumberone>';
	for (arg in argsObj) {
		this[arg] = argsObj[arg];
	}
	this.REclassMain = new RegExp('\\b' + this.classMain + '\\b', 'gi');
	this.REclassMainLive = new RegExp('\\b' + this.classMainLive + '\\b', 'gi');
	this.REclassTab = new RegExp('\\b' + this.classTab + '\\b', 'gi');
	this.REclassTabDefault = new RegExp('\\b' + this.classTabDefault + '\\b', 'gi');
	this.REclassTabHide = new RegExp('\\b' + this.classTabHide + '\\b', 'gi');
	this.tabs = [];
	if (this.div) {
		this.init(this.div);
		this.div = null;
	}
}

tabberObj.prototype.init = function(e) {
	var childNodes, i, i2, t, defaultTab = 0,
		DOM_ul, DOM_li, DOM_a, aId, headingElement;
	if (!document.getElementsByTagName) {
		return false;
	}
	if (e.id) {
		this.id = e.id;
	}
	this.tabs.length = 0;
	childNodes = e.childNodes;
	for (i = 0; i < childNodes.length; i++) {
		if (childNodes[i].className && childNodes[i].className.match(this.REclassTab)) {
			t = {};
			t.div = childNodes[i];
			this.tabs[this.tabs.length] = t;
			if (childNodes[i].className.match(this.REclassTabDefault)) {
				defaultTab = this.tabs.length - 1;
			}
		}
	}
	DOM_ul = document.createElement("ul");
	DOM_ul.className = this.classNav;
	for (i = 0; i < this.tabs.length; i++) {
		t = this.tabs[i];
		t.headingText = t.div.title;
		if (this.removeTitle) {
			t.div.title = '';
		}
		if (!t.headingText) {
			for (i2 = 0; i2 < this.titleElements.length; i2++) {
				headingElement = t.div.getElementsByTagName(this.titleElements[i2])[0];
				if (headingElement) {
					t.headingText = headingElement.innerHTML;
					if (this.titleElementsStripHTML) {
						t.headingText.replace(/<br>/gi, " ");
						t.headingText = t.headingText.replace(/<[^>]+>/g, "");
					}
					break;
				}
			}
		}
		if (!t.headingText) {
			t.headingText = i + 1;
		}
		DOM_li = document.createElement("li");
		t.li = DOM_li;
		DOM_a = document.createElement("a");
		DOM_a.appendChild(document.createTextNode(t.headingText));
		DOM_a.href = "javascript:void(null);";
		DOM_a.title = t.headingText;
		DOM_a.onclick = this.navClick;
		DOM_a.tabber = this;
		DOM_a.tabberIndex = i;
		if (this.addLinkId && this.linkIdFormat) {
			aId = this.linkIdFormat;
			aId = aId.replace(/<tabberid>/gi, this.id);
			aId = aId.replace(/<tabnumberzero>/gi, i);
			aId = aId.replace(/<tabnumberone>/gi, i + 1);
			aId = aId.replace(/<tabtitle>/gi, t.headingText.replace(/[^a-zA-Z0-9\-]/gi, ''));
			DOM_a.id = aId;
		}
		DOM_li.appendChild(DOM_a);
		DOM_ul.appendChild(DOM_li);
	}
	e.insertBefore(DOM_ul, e.firstChild);
	e.className = e.className.replace(this.REclassMain, this.classMainLive);
	this.tabShow(defaultTab);
	if (typeof this.onLoad == 'function') {
		this.onLoad({
			tabber: this
		});
	}
	return this;
};

tabberObj.prototype.navClick = function(event) {
	var rVal, a, self, tabberIndex, onClickArgs;
	a = this;
	if (!a.tabber) {
		return false;
	}
	self = a.tabber;
	tabberIndex = a.tabberIndex;
	a.blur();
	if (typeof self.onClick == 'function') {
		onClickArgs = {
			'tabber': self,
			'index': tabberIndex,
			'event': event
		};
		if (!event) {
			onClickArgs.event = window.event;
		}
		rVal = self.onClick(onClickArgs);
		if (rVal === false) {
			return false;
		}
	}
	self.tabShow(tabberIndex);
	return false;
};

tabberObj.prototype.tabHideAll = function() {
	var i;
	for (i = 0; i < this.tabs.length; i++) {
		this.tabHide(i);
	}
};

tabberObj.prototype.tabHide = function(tabberIndex) {
	var div;
	if (!this.tabs[tabberIndex]) {
		return false;
	}
	div = this.tabs[tabberIndex].div;
	if (!div.className.match(this.REclassTabHide)) {
		div.className += ' ' + this.classTabHide;
	}
	this.navClearActive(tabberIndex);
	return this;
};

tabberObj.prototype.tabShow = function(tabberIndex) {
	var div;
	if (!this.tabs[tabberIndex]) {
		return false;
	}
	this.tabHideAll();
	div = this.tabs[tabberIndex].div;
	div.className = div.className.replace(this.REclassTabHide, '');
	this.navSetActive(tabberIndex);
	if (typeof this.onTabDisplay == 'function') {
		this.onTabDisplay({
			'tabber': this,
			'index': tabberIndex
		});
	}
	return this;
};

tabberObj.prototype.navSetActive = function(tabberIndex) {
	this.tabs[tabberIndex].li.className = this.classNavActive;
	return this;
};

tabberObj.prototype.navClearActive = function(tabberIndex) {
	this.tabs[tabberIndex].li.className = '';
	return this;
};

function tabberAutomatic (tabberArgs) {
	var tempObj, divs, i;
	if (!tabberArgs) {
		tabberArgs = {};
	}
	tempObj = new tabberObj(tabberArgs);
	divs = document.getElementsByTagName("div");
	for (i = 0; i < divs.length; i++) {
		if (divs[i].className && divs[i].className.match(tempObj.REclassMain)) {
			tabberArgs.div = divs[i];
			divs[i].tabber = new tabberObj(tabberArgs);
		}
	}
	return this;
}

function tabberAutomaticOnLoad (tabberArgs) {
	var oldOnLoad;
	if (!tabberArgs) {
		tabberArgs = {};
	}
	oldOnLoad = window.onload;
	if (typeof window.onload != 'function') {
		window.onload = function() {
			tabberAutomatic(tabberArgs);
		};
	} else {
		window.onload = function() {
			oldOnLoad();
			tabberAutomatic(tabberArgs);
		};
	}
}

if (typeof tabberOptions == 'undefined') {
	tabberAutomaticOnLoad();
} else {
	if (!tabberOptions.manualStartup) {
		tabberAutomaticOnLoad(tabberOptions);
	}
}
