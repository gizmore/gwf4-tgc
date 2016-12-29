function RadiusOverlay(player) {
	this.PLAYER = player;
	this.calcBounds = function() {
		var p = this.PLAYER;
		var r = p.radius();
		return new google.maps.LatLngBounds(
				new google.maps.LatLng(p.lat()-r, p.lng()-r),
				new google.maps.LatLng(p.lat()+r, p.lng()+r));
	};
	this.BOUNDS = this.calcBounds();
}
RadiusOverlay.prototype = new google.maps.OverlayView();
