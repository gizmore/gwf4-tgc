function TGC_Player(json) {

	this.user = new GWF_User(json);
	this.position = null;
	this.hasMoved = false;
	this.JSON = json;
	this.LAST_JSON = {};

//	this.hash = function(hash) { if (hash) this.JSON.hash = hash; return this.JSON.hash; };
	
	// ID
	this.id = function() { return this.user.id(); };
	this.isOwn = function() { return this.user.name() === GWF_USER.name(); };
	this.name = function() { return this.user.name(); };
	this.displayName = function() { return this.user.displayName(); };
	this.displayRace = function() { return this.JSON.race; };
	this.displayGender = function() { return this.user.displayGender(); };
	this.levelName = function(level) { return TGC_Player.LevelUtil.displayLevel(level); };

	// Pos
	this.hasPosition = function() { return this.position !== null; };
	this.hasStats = function() { return this.JSON.fl !== undefined; };
	this.radius = function() { return this.JSON.r || 10; };
	this.lat = function() { return this.position.lat(); };
	this.lng = function() { return this.position.lng(); };
	this.moveTo = function(lat, lng) { this.position = new google.maps.LatLng({lat: lat, lng: lng}); };
	this.latLng = function() { return this.position; };
	
	// Mode
	this.mode = function() { return this.JSON.am; };
	this.color = function() { return this.JSON.ac; };
	this.skill = function() { return this.JSON.as; };
	this.element = function() { return this.JSON.ae; };
	
	// HPMP
	this.hp = function() { return this.JSON.hp; };
	this.maxHP = function() { return this.JSON.mhp; };
	this.giveHP = function(hp) { this.JSON.hp = clamp(this.JSON.hp + hp, 0, this.maxHP()); };
	this.mp = function() { return this.JSON.mp; };
	this.maxMP = function() { return this.JSON.mmp; };
	this.giveMP = function(mp) { this.JSON.mp = clamp(this.JSON.mp + mp, 0, this.maxMP()); };

	// Loot
	this.water = function() { return this.JSON.w; }
	this.food = function() { return this.JSON.f; }
	this.gold = function() { return this.JSON.g; }
	this.giveWater = function(w) { this.JSON.w += w; }
	this.giveFood = function(f) { this.JSON.f += f; }
	this.giveGold = function(g) { this.JSON.g += g; }

	// Attributes
	this.strength = function() { return this.JSON.eas; };
	this.dexterity = function() { return this.JSON.ead; };
	this.wisdom = function() { return this.JSON.eaw; };
	this.intelligence = function() { return this.JSON.eai; };
	this.strengthLevel = function() { return this.JSON.bas; };
	this.dexterityLevel = function() { return this.JSON.bad; };
	this.wisdomLevel = function() { return this.JSON.baw; };
	this.intelligenceLevel = function() { return this.JSON.bai; };
	// Last Attributes
	this.lastStrengthLevel = function() { return this.LAST_JSON.bas; };
	this.lastDexterityLevel = function() { return this.LAST_JSON.bad; };
	this.lastWisdomLevel = function() { return this.LAST_JSON.baw; };
	this.lastIntelligenceLevel = function() { return this.LAST_JSON.bai; };

	// Skills
	this.fighter = function() { return this.JSON.elf; };
	this.ninja = function() { return this.JSON.eln; };
	this.priest = function() { return this.JSON.elp; };
	this.wizard = function() { return this.JSON.elw; };
	this.fighterLevel = function() { return this.JSON.blf; };
	this.ninjaLevel = function() { return this.JSON.bln; };
	this.priestLevel = function() { return this.JSON.blp; };
	this.wizardLevel = function() { return this.JSON.blw; };
	this.fighterLevelName = function() { return this.levelName(this.fighterLevel()); };
	this.ninjaLevelName = function() { return this.levelName(this.ninjaLevel()); };
	this.priestLevelName = function() { return this.levelName(this.priestLevel()); };
	this.wizardLevelName = function() { return this.levelName(this.wizardLevel()); };
	// Last Skills
	this.lastMaxHP = function() { return this.LAST_JSON.mhp; };
	this.lastMaxMP = function() { return this.LAST_JSON.mmp; };
	this.lastFighterLevel = function() { return this.LAST_JSON.blf; };
	this.lastNinjaLevel = function() { return this.LAST_JSON.bln; };
	this.lastPriestLevel = function() { return this.LAST_JSON.blp; };
	this.lastWizardLevel = function() { return this.LAST_JSON.blf; };
	this.lastFighterLevelName = function() { return this.levelName(this.lastFighterLevel()); };
	this.lastNinjaLevelName = function() { return this.levelName(this.lastNinjaLevel()); };
	this.lastPriestLevelName = function() { return this.levelName(this.lastPriestLevel()); };
	this.lastWizardLevelName = function() { return this.levelName(this.lastWizardLevel()); };
	
//	this.levelBaseXP = function(field) {
//		
//	};
//	<tgc-stat-bar class="fighter-bar" ng-if="player.fighterLevel()" ng-min="player.levelBaseXP('xf')" ng-value="player.xpFighter()" ng-max="player.levelNextXP('xf')">{{player.fighterLevelName()}} Fighter</tgc-stat-bar>

	// Update
	this.update = function(json, lvlupstamp) {
		console.log('TGC_Player.update()', json);
		if (lvlupstamp) {
			for (var i in this.JSON) {
				this.LAST_JSON[i] = this.JSON[i];
			}
		}
		for (var i in json) {
//			if (json.hasOwnProperty(i)) {
//				if (i !== 'lat' && i !== 'lng') {
					this.JSON[i] = json[i];
//				}
//			}
		}
		this.moveValidTo(json);
		return this;
	};

	this.moveValidTo = function(json) {
		if (json.lat && json.lng) {
			this.moveTo(json.lat, json.lng);
			this.moved = true;
		}
	};
	
	/** Init **/
	this.moveValidTo(json);
};

