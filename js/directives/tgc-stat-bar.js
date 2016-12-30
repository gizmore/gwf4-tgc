angular.module('gwf4')
.directive('tgcStatBar', function() {
	return {
		restrict: 'E',
		transclude: true,
		link: function ($scope, element, attrs) {
			function updated() {
				var min = attrs.min || 0;
				var val = attrs.value || 0;
				var max = attrs.max || val;
				if (min > max)
				{
					var t = max;
					max = min;
					min = t;
				}
				if (max == min)
				{
					min -= 1;
				}
				val = clamp(val, min, max);
				var left = (val - min) / (max - min) * 100.0;
				var right = 100.0 - left;
				element.html(sprintf('<left><label>%s</label><right style="width: %s%%;"></right></left>', attrs.label, right));
			}
			$scope.$watch(function() { return attrs.max; }, updated, true);
			$scope.$watch(function() { return attrs.value; }, updated, true);
			updated();
		},
	};
});