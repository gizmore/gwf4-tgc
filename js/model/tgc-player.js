function TGC_Player(json) {

	this.user = new GWF_User(json);
	this.position = null;
	this.hasMoved = false;
	this.JSON = json;
	this.LAST_JSON = {};
	
	this.name = function() { return this.user.name(); };
	this.displayName = function() { return this.user.displayName(); };
	
	this.lat = function() { return this.position.lat(); };
	this.lng = function() { return this.position.lng(); };
	this.moveTo = function(lat, lng) { this.position = new google.maps.LatLng({lat: lat, lng: lng}); };
	this.latLng = function() { return this.position; };
	this.hasPosition = function() { return this.position !== null; };
	this.hasStats = function() { return this.JSON.fl !== undefined; };

	this.id = function(id) { if (id) this.JSON.p_uid = id; return this.JSON.p_uid; };
	this.isOwn = function() { return this.user.name() === GWF_USER.name(); };

	this.hash = function(hash) { if (hash) this.JSON.hash = hash; return this.JSON.hash; };
	
	this.hp = function() { return this.JSON.hp; };
	this.maxHP = function() { return this.JSON.mhp; };
	this.mp = function() { return this.JSON.mp; };
	this.maxMP = function() { return this.JSON.mmp; };
	
	this.giveHP = function(hp) { this.JSON.hp = clamp(this.JSON.hp + hp, 0, this.maxHP()); };
	this.giveMP = function(mp) { this.JSON.mp = clamp(this.JSON.mp + mp, 0, this.maxMP()); };

	this.mode = function(mode) { if (mode) this.JSON.m = mode; return this.JSON.m; };
	this.color = function(color) { if (color) this.JSON.c = color; return this.JSON.c; };
	this.skill = function(skill) { if (skill) this.JSON.s = skill; return this.JSON.s; };
	this.element = function(element) { if (element) this.JSON.e = element; return this.JSON.e; };
	
	this.strength = function() { return this.JSON.as; };
	this.dexterity = function() { return this.JSON.ad; };
	this.wisdom = function() { return this.JSON.aw; };
	this.intelligence = function() { return this.JSON.ai; };
	
	this.lastStrength = function() { return this.LAST_JSON.as; };
	this.lastDexterity = function() { return this.LAST_JSON.ad; };
	this.lastWisdom = function() { return this.LAST_JSON.aw; };
	this.lastIntelligence = function() { return this.LAST_JSON.ai; };
	
	this.fighterLevel = function(level) { if (level) this.JSON.fl = level; return this.JSON.fl; };
	this.ninjaLevel = function(level) { if (level) this.JSON.nl = level; return this.JSON.nl; };
	this.priestLevel = function(level) { if (level) this.JSON.pl = level; return this.JSON.pl; };
	this.wizardLevel = function(level) { if (level) this.JSON.wl = level; return this.JSON.wl; };
	
	this.lastFighterLevel = function() { return this.LAST_JSON.fl; };
	this.lastNinjaLevel = function() { return this.LAST_JSON.nl; };
	this.lastPriestLevel = function() { return this.LAST_JSON.pl; };
	this.lastWizardLevel = function() { return this.LAST_JSON.wl; };
	
	this.fighterLevelName = function() { return this.levelName(this.fighterLevel()); };
	this.ninjaLevelName = function() { return this.levelName(this.ninjaLevel()); };
	this.priestLevelName = function() { return this.levelName(this.priestLevel()); };
	this.wizardLevelName = function() { return this.levelName(this.wizardLevel()); };

	this.lastFighterLevelName = function() { return this.levelName(this.lastFighterLevel()); };
	this.lastNinjaLevelName = function() { return this.levelName(this.lastNinjaLevel()); };
	this.lastPriestLevelName = function() { return this.levelName(this.lastPriestLevel()); };
	this.lastWizardLevelName = function() { return this.levelName(this.lastWizardLevel()); };

	this.lastModeChange = function(lastChange) { if (lastChange) this.JSON.mc = lastChange; return this.JSON.mc };
	this.lastColorChange = function(lastChange) { if (lastChange) this.JSON.cc = lastChange; return this.JSON.cc };
	this.lastSkillChange = function(lastChange) { if (lastChange) this.JSON.sc = lastChange; return this.JSON.sc };
	this.lastElementChange = function(lastChange) { if (lastChange) this.JSON.ec = lastChange; return this.JSON.ec };
	
	this.levelName = function(level) {
		return window.TGC_CONFIG.levels[level];
	};
	
	this.lastSlap = function() {};
	
	this.update = function(json) {
		for (var i in this.JSON) {
			if (this.JSON.hasOwnProperty(i)) {
				this.LAST_JSON[i] = JSON[i];
			}
		}
		for (var i in json) {
			if (json.hasOwnProperty(i)) {
				if (i !== 'lat' && i !== 'lng') {
					this.JSON[i] = json[i];
				}
			}
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
	
	/** SPELLS **/
	this.NO_SCROLL_LOCK = undefined;
	this.EXTEND_MIN_ZOOM = 0;
	this.EXTEND_MAX_ZOOM = 0;
	this.DRUNK = undefined;
};
