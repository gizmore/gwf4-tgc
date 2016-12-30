angular.module('gwf4')
.directive('tgcStatBar', function() {
	return {
		restrict: 'E',
		link: function ($scope, element, attrs) {
			function updated() {
				var label = sprintf('%d / %d %s', attrs.value, attrs.max, attrs.label);
				var left = ((attrs.value - attrs.min) / (attrs.max - attrs.min)) * 100.0;
				var right = 100.0 - left;
				element.html(sprintf('<left style="background: %s; color: %s;"><label>%s</label><right style="width: %s%%;"></right></left>',
						attrs.background, attrs.color, label, right));
			}
			$scope.$watch(function() { return attrs.max; }, updated, true);
			$scope.$watch(function() { return attrs.value; }, updated, true);
			updated();
		},
	};
});