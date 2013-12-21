var ColorPicker = new Class({
	getOptions: function(){ return {cellWidth:5, cellHeight:10, top:20, left:-100, transition:true, stylattr:"backgroundColor"}; },
	initialize: function(el,options){
		this.setOptions(this.getOptions(), options);
		this.el = document.id(el);
		this.el.addEvent("focus", function(e){ this.openPicker();}.bind(this));
		this.el.addEvent("change", function(e){ /*this.validate();*/ this.closePicker();}.bind(this));
		this.el.addEvent('keydown', function(e){ if (this.active) this.closePicker(); }.bind(this));
		this.el.addEvent('keyup', function(e){ try{ this.colorPanel.setStyle(this.options.stylattr, this.el.value); } catch(e){}}.bind(this));

		this.height = this.options.cellHeight * 8 + this.options.top;
		this.active = false;

		this.container = new Element("div");
		this.container.style.paddingTop="2px";

		this.el.parentNode.insertBefore(this.container, this.el);
		this.container.appendChild(this.el);
		this.el.setStyle("float","left");

		if (this.options.dispelem) {
			this.colorPanel = this.options.dispelem;
		} else {
			this.colorPanel = new Element("input");
			this.colorPanel.setAttribute("size","2");
			this.colorPanel.setAttribute("type","text");
			this.colorPanel.setAttribute("readonly","readonly");
			this.colorPanel.setStyle(this.options.stylattr,this.el.value);
			this.colorPanel.setStyle("cursor","pointer");
			this.colorPanel.setStyle("float","left");

			this.colorPanel.addEvent("focus", function(e){ this.openPicker();}.bind(this));
			this.container.appendChild(this.colorPanel);
		}

		//color chart container
		this.chartContainer = new Element("div");
		this.chartContainer.setStyles({position:"relative", "z-index": 100, cursor: "pointer","background-color":"#000000", float:"left","overflow":"visible"});
		this.chartContainer.addEvent('blur', function(e){ this.closePicker(); }.bind(this));

		this.container.parentNode.insertBefore(this.chartContainer, this.container);

		this.colorTable = new Element("table");
		this.colorTable.setAttribute("border","1");
		this.colorTable.setAttribute("bordercolor","silver");
		this.colorTable.setAttribute("cellpadding","0");
		this.colorTable.setAttribute("cellspacing","0");
		this.colorTable.setStyles({"background-color":"#000000", "margin":"4px",visibility:"visible", position:"absolute", top: this.options.top + "px", left: this.options.left + "px", "z-index": 100, cursor: "pointer"});
		var tabBody = new Element("tbody");
		this.colorTable.appendChild(tabBody);

		var colorArray = ["00", "33", "66", "99", "CC", "FF"];
		for (var i=0; i< colorArray.length; i++) {
			var currRow = new Element("tr");
			tabBody.appendChild(currRow);
			for ( var j=0; j< colorArray.length; j++) {
				for ( var k=0; k< colorArray.length; k++) {
					var currColor = "#"+colorArray[i]+colorArray[j]+colorArray[k];
					var currCell = new Element("td");
					currCell.innerHTML = '<div width="'+  this.options.cellWidth +'px" height="'+ this.options.cellHeight +'px" style="width:'+ this.options.cellWidth +'px;height:'+ this.options.cellHeight +'px;">&nbsp;</div>';
					currCell.setStyle("backgroundColor",currColor);
					currCell.addEvent('click', function(currColor){ this.el.value = currColor; this.closePicker();}.pass(currColor, this));
					currCell.addEvent('mouseover', function(currColor){ this.colorPanel.setStyle(this.options.stylattr, currColor); }.pass(currColor, this));
					currCell.setStyles({"padding":"0px"});
					currRow.appendChild(currCell);
				}
			}
		}

		this.fader = null;
		if (this.options.transition) {
			this.fader = new Fx.Tween(this.colorTable,'opacity', {duration:500});
		}

		this.chartContainer.addEvent('mouseout', function(currColor){ try{this.colorPanel.setStyle(this.options.stylattr, this.el.value); } catch(e){} }.pass(currColor, this));
		this.chartContainer.appendChild(this.colorTable);

		$(document).addEvent('click', function(e){ if ((e.target != this.el) && (e.target != this.colorPanel)) {this.closePicker();} }.bind(this));
		this.hidePicker();
	},
	closePicker: function(){
		this.colorTable.setStyle("visibility","hidden");
		this.colorPanel.setStyle(this.options.stylattr,this.el.value);
		if (this.options.transition && this.active) {
			this.colorTable.fade('show');
			this.colorTable.fade('out');
		}
		this.active = false;
	},
	openPicker: function(){
		this.colorTable.setStyle("visibility","visible");
		if (this.options.transition) {
			this.colorTable.fade('hide');
			this.colorTable.fade('in');
		}
		this.active = true;
	},
	hidePicker: function(){
		this.colorTable.setStyle("visibility","hidden");
		this.colorPanel.setStyle(this.options.stylattr,this.el.value);
	},
	validate: function(){
		var pattern = /#[0-9A-Fa-f]{6}/;
		if (pattern.test(this.el.value)) { return; }

		var stringVal = new String(this.el.value);
		if (stringVal.charAt(0) != '#') {
			stringVal = '#' + stringVal;
		}

		var pattern2 = /[^#A-Fa-f0-9]/g;
		stringVal = stringVal.replace(pattern2, '');

		var l = 7 - stringVal.length; //extra 0s to pad
		for (var i=0; i<l; i++) {
			stringVal = stringVal + '0';
		}

		stringVal = stringVal.substr(0,7);

		//finally retest
		if (!pattern.test(stringVal)) {
			stringVal = '#ffffff';  
		}

		this.el.value = stringVal;
		
	}
});

ColorPicker.implement(new Events);
ColorPicker.implement(new Options);
