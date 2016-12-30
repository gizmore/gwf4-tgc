'use strict';
angular.module('gwf4')
.service('LevelUtil', function() {
	
	var LevelUtil = TGC_Player.LevelUtil = this;
	
	LevelUtil.levelValues = function() { return TGC_CONFIG.levels; }
	LevelUtil.levels = function() { return jQuery.map(LevelUtil.levelValues(), function(xp, name) { return name; }); };
	LevelUtil.maxLevel = function() { return LevelUtil.levels().length - 1; };
	LevelUtil.level = function(level) { return clamp(parseInt(level), 0, LevelUtil.maxLevel()); }; 
	LevelUtil.levelXP = function(level) { return LevelUtil.levelValues()[LevelUtil.levelName(level)]; }; 
	LevelUtil.levelName = function(level) { return LevelUtil.levels()[LevelUtil.level(level)]; }; 
	LevelUtil.displayLevel = function(level) { return LevelUtil.displayLevelName(LevelUtil.levelName(level)); };
	LevelUtil.displayLevelName = function(name) { return name; };
	
	LevelUtil.percentCompleted = function(xp) {
		var level = LevelUtil.levelForXP(xp);
		if (level === LevelUtil.maxLevel()) {
			return 100.0;
		}
		var base = LevelUtil.levelXP(level);
		var next = LevelUtil.levelXP(level+1);
		var need = next - base;
		var have = xp - base;
		var result = have / need * 100.0;
//		console.log('LevelUtil.percentCompleted()', xp, level, base, next, need, have, result);
		return Math.round(result, 1);
	};
	
	LevelUtil.levelForXP = function(xp) {
		var level = -1;
		var levels = LevelUtil.levelValues();
		for (var name in levels) {
			var neededXP = levels[name];
			if (xp < neededXP) {
				break;
			}
			level++;
		}
		return level;
	};
	
	return LevelUtil;
});
