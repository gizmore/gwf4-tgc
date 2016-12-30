'use strict';
angular.module('gwf4')
.service('RandUtil', function() {
	
	var RandUtil = this;

	RandUtil.frand = function(min=0, max=1) {
		if (max > min) {
			return RandUtil.frand(max, min);
		}
		var rng = max - min;
		return min + rng * Math.random();
	};

	RandUtil.rand = function(min=0, max=1) {
		return Math.round(MathUtil.frand(min, max));
	};
	
	return RandUtil;
});
