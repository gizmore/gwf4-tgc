'use strict';
angular.module('gwf4')
.service('LevelupDlg', function($q, $mdDialog, ErrorSrvc) {

	var LevelupDlg = this;
	
	LevelupDlg.open = function(player, data) {
		console.log('LevelupDlg.open()', player, data);
		return $q(function(resolve, reject){
			LevelupDlg.show(player, data, resolve, reject);
		});
	};

	LevelupDlg.show = function(player, data, resolve, reject) {
		function DialogController($scope, $mdDialog, player, data) {
			$scope.player = player;
			$scope.data = data;
			$scope.closeDialog = function() {
				$mdDialog.hide();
				resolve();
			};
			$scope.skillLabel = function(oldName, newName) { return oldName === newName ? oldName : sprintf('<tgc-lvlup>%s => %s</tgc-lvlup>', oldName, newName); };
			$scope.maxHPLabel = function() { return $scope.skillLabel(player.lastMaxHP(), player.maxHP()) + ' HP'; };
			$scope.maxMPLabel = function() { return $scope.skillLabel(player.lastMaxMP(), player.maxMP()) + ' MP'; };
			$scope.fighterLabel = function() { return $scope.skillLabel(player.lastFighterLevelName(), player.fighterLevelName()); };
			$scope.ninjaLabel = function() { return $scope.skillLabel(player.lastNinjaLevelName(), player.ninjaLevelName()); };
			$scope.priestLabel = function() { return $scope.skillLabel(player.lastPriestLevelName(), player.priestLevelName()); };
			$scope.wizardLabel = function() { return $scope.skillLabel(player.lastWizardLevelName(), player.wizardLevelName()); };
			$scope.oldFighter = function() { return player.lastFighterLevel(); };
			$scope.oldNinja = function() { return player.lastNinjaLevel(); };
			$scope.oldPriest = function() { return player.lastPriestLevel(); };
			$scope.oldWizard = function() { return player.lastWizardLevel(); };
			$scope.newFighter = function() { return player.fighterLevel(); };
			$scope.newNinja = function() { return player.ninjaLevel(); };
			$scope.newPriest = function() { return player.priestLevel(); };
			$scope.newWizard = function() { return player.wizardLevel(); };
			$scope.attrLabel = function(oldVal, newVal, label) { return oldVal == newVal ? label : sprintf('<tgc-lvlup>%s %s => %s', label, oldVal, newVal) };
			$scope.strengthLabel = function() { return $scope.attrLabel($scope.oldStrength(), $scope.newStrength(), 'Strength'); };
			$scope.dexterityLabel = function() { return $scope.attrLabel($scope.oldDexterity(), $scope.newDexterity(), 'Dexterity'); };
			$scope.wisdomLabel = function() { return $scope.attrLabel($scope.oldWisdom(), $scope.newWisdom(), 'Wisdom'); };
			$scope.intelligenceLabel = function() { return $scope.attrLabel($scope.oldIntelligence(), $scope.newIntelligence(), 'Intelligence'); };
			$scope.oldStrength = function() { return player.lastStrengthLevel(); };
			$scope.oldDexterity = function() { return player.lastDexterityLevel(); };
			$scope.oldWisdom = function() { return player.lastWisdomLevel(); };
			$scope.oldIntelligence = function() { return player.lastIntelligenceLevel(); };
			$scope.newStrength = function() { return player.strengthLevel(); };
			$scope.newDexterity = function() { return player.dexterityLevel(); };
			$scope.newWisdom = function() { return player.wisdomLevel(); };
			$scope.newIntelligence = function() { return player.intelligenceLevel(); };
		}
		var parentEl = angular.element(document.body);
		$mdDialog.show({
			templateUrl: GWF_WEB_ROOT+'module/Tamagochi/js/tpl/levelup_dlg.html',
			locals: {
				player: player,
				data: data,
			},
			controller: DialogController
		});
	};
	
	return LevelupDlg;
});
