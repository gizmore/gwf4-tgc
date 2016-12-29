'use strict';
var TGC = angular.module('gwf4');
TGC.service('SpellDlg', function($q, $mdDialog, ErrorSrvc, TGCCommandSrvc, PlayerSrvc) {
	
	var SpellDlg = this;
	
	SpellDlg.open = function(target, type) {
		console.log('SpellDlg.open()', target, type);
		return $q(function(resolve, reject){
			SpellDlg.show(target, type, resolve, reject);
		});
	};

	SpellDlg.show = function(target, type, resolve, reject) {
		console.log('SpellDlg.show()', PlayerSrvc.OWN);
		function DialogController($scope, $mdDialog, target, type, resolve) {
			$scope.data = {
				player: PlayerSrvc.OWN,
				target: target,
				type: type,
				runes: window.TGC_CONFIG.runes,
				selected: [],
				selectedIDs: [],
			}
			$scope.closeDialog = function() {
				$mdDialog.hide();
//				resolve();
			};
			$scope.magicLevel = function() {
				return PlayerSrvc.OWN.priestLevel() + PlayerSrvc.OWN.wizardLevel(); 
			};
			$scope.brew = function() {
				TGCCommandSrvc.tgcBrew(target, $scope.spelltext()).then($scope.closeDialog);
			};
			$scope.cast = function() {
				if (target.fighterLevelName === undefined) {
					TGCCommandSrvc.tgcCastLatLng(target, $scope.spelltext()).then($scope.closeDialog);
				}
				else {
					TGCCommandSrvc.tgcCast(target, $scope.spelltext()).then($scope.closeDialog);
				}
			};
			$scope.spelltext = function() {
				return $scope.data.selected.join(',');
			};
			$scope.spell = function($event, row, col) {
				var rune = window.TGC_CONFIG.runes[row][col]
				$scope.data.selected = $scope.data.selected.slice(0, row);
				$scope.data.selected.push(rune);
				$scope.data.selectedIDs = $scope.data.selectedIDs.slice(0, row);
				$scope.data.selectedIDs.push(col);
			};
			$scope.numRunes = function() {
				return $scope.data.selected.length;
			};
		}
		var parentEl = angular.element(document.body);
		$mdDialog.show({
			templateUrl: GWF_WEB_ROOT+'module/Tamagochi/js/tpl/spell_dlg.html',
			locals: {
				target: target,
				type: type,
				resolve: resolve,
			},
			controller: DialogController
		});
	};
});
