'use strict';
angular.module('gwf4')
.service('AreaDlg', function($q, $mdDialog, ErrorSrvc, SpellDlg, PlayerSrvc) {

	var AreaDlg = this;
	
	AreaDlg.open = function(latLng) {
		console.log('AreaDlg.open()', latLng.lat(), latLng.lng());
		return $q(function(resolve, reject){
			AreaDlg.show(latLng, resolve, reject);
		});
	};

	AreaDlg.show = function(latLng, resolve, reject) {
		function DialogController($scope, $mdDialog, latLng) {
			$scope.latLng = latLng;
			$scope.own_player = PlayerSrvc.OWN;
			$scope.closeDialog = function() {
				$mdDialog.hide();
				resolve();
			};
			$scope.cast = function() {
				$scope.closeDialog();
				SpellDlg.open(latLng, 'cast');
			};
		}
		var parentEl = angular.element(document.body);
		$mdDialog.show({
//			parent: document.getElementById('TGCMAP'),
//			targetEvent: $event,
			templateUrl: GWF_WEB_ROOT+'module/Tamagochi/js/tpl/terrain_dlg.html',
			locals: {
				latLng: latLng,
			},
			controller: DialogController
		});
	};
	
	return AreaDlg;
});
