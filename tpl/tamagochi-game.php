<div id="TGCMAPDIV" layout="column" layout-align="top center" ng-controller="TGCMapCtrl"><div id="TGCMAP"></div></div>

<tgc-game-action-bar-shadow></tgc-game-action-bar-shadow>
<tgc-game-action-bar ng-controller="TGCCtrl">
	<md-button ng-click="doSelf()">Self</md-button>
	<md-button ng-click="doPause()">Rest</md-button>
	<md-button ng-click="doBrew()">Brew</md-button>
	<md-button ng-click="doCast()">Cast</md-button>
</tgc-game-action-bar>
