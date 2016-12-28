'use strict';
var TGC = angular.module('gwf4');
TGC.service('PlayerDlg', function($q, $mdDialog, ErrorSrvc, TGCCommandSrvc, PlayerSrvc, SpellDlg) {
	
	var PlayerDlg = this;
	
	PlayerDlg.open = function($event, player) {
		console.log('PlayerDlg.open()', player);
		return $q(function(resolve, reject){
			PlayerSrvc.withStats(player).then(function(player) {
				PlayerDlg.show(player, resolve, reject);
			});
		});
	};

	PlayerDlg.show = function(player, resolve, reject) {
		function DialogController($scope, $mdDialog, player) {
			$scope.player = player;
			$scope.closeDialog = function() {
				$mdDialog.hide();
				resolve();
			};
			$scope.slapTitle = function(data) {
				switch (data.type) {
				case 'fighter': return "Fight";
				case 'ninja': return "Attack";
				case 'priest': return "Potion";
				case 'wizard': return "Spell";
				}
			};
			$scope.slapMessage = function(data) {
				var damage = data.critical ? sprintf('<critical>%s damage</critical>', data.damage) : sprintf('This caused %s damage.', data.damage);
				damage = data.killed ? sprintf('<b>Killed</b> with %s!') : damage;
				return sprintf('%s %s %s %s with %s %s.<br/>%s', data.attacker, data.adverb, data.verb, data.defender, data.adjective, data.noun, damage);
			}
			$scope.afterFight = function(result) {
				console.log('PlayerDlg.afterFight()', result);
				if (!result.startsWith('ERR')) {
					$scope.closeDialog();
					var data = JSON.parse(result);
					ErrorSrvc.showMessage($scope.slapMessage(data), $scope.slapTitle(data));
				}
			};
			$scope.fight = function() {
				TGCCommandSrvc.tgcFight(player).then($scope.afterFight);
			};
			$scope.attack = function() {
				TGCCommandSrvc.tgcAttack(player).then($scope.afterFight);
			};
			$scope.brew = function() {
				$scope.closeDialog();
				SpellDlg.show(player, 'brew');
			};
			$scope.cast = function() {
				$scope.closeDialog();
				SpellDlg.show(player, 'cast');
			};
			
		}
		var parentEl = angular.element(document.body);
		$mdDialog.show({
//			parent: document.getElementById('TGCMAP'),
//			targetEvent: $event,
			templateUrl: GWF_WEB_ROOT+'module/Tamagochi/js/tpl/player_dlg.html',
			locals: {
				player: player
			},
			controller: DialogController
		});
	};
});
