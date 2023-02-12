/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2023 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
'use strict';

var TPER = (function() {
	return {
		mul: function (elm) {
			let vel = elm.querySelector('.tper-valu');
			let numb = +elm.querySelector('.tper-num').value;
			let shft = +elm.querySelector('.tper-mul').value;
			vel.value = numb * shft;
		}
	};
})();
