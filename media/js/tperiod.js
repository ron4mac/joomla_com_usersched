/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.4.1
*/
'use strict';

var TPER = (function() {
	return {
		mul: function (elm) {
			const vel = elm.querySelector('.tper-valu');
			const numb = +elm.querySelector('.tper-num').value;
			const shft = +elm.querySelector('.tper-mul').value;
			vel.value = numb * shft;
		}
	};
})();
