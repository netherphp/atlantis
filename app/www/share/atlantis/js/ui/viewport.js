import PinchZoom from '/share/atlantis/lib/ui/pinchzoom.js?v=2';

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class Viewport {

	constructor(selector='#Viewport') {

		this.element = jQuery(selector);
		this.id = this.element.attr('id');

		this.pinch = null;
		this.surface = null;

		this.hudZoom = null;
		this.hudPosX = null;
		this.hudPosY = null;
		this.hudReset = null;

		this.initHudElements();
		this.initPinchLibrary();

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	initHudElements() {

		let self = this;

		////////

		this.surface = this.element.find('.vpsurface');
		this.hudReset = jQuery(`[data-viewport="${this.id}"][data-viewport-cmd="reset"]`);
		this.hudZoom = jQuery(`[data-viewport="${this.id}"][data-viewport-hud="zoom"]`);
		this.hudPosX = jQuery(`[data-viewport="${this.id}"][data-viewport-hud="x"]`);
		this.hudPosY = jQuery(`[data-viewport="${this.id}"][data-viewport-hud="y"]`);

		if(this.hudReset.length)
		this.hudReset.on('click', function(){
			self.pinch.zoomOutAnimation();
			return;
		});

		return;
	};

	initPinchLibrary() {

		let self = this;

		////////

		this.pinch = new PinchZoom(this.surface.get(0), {
			'minZoom': 0.20,
			'maxZoom': 5.0,
			'useMouseWheel': true,
			'mouseWheelSens': 500,
			'animationDuration': 250,
			'verticalPadding': 150,
			'horizontalPadding': 150,
			'use2d': false,
			'draggableUnzoomed': true,
			'zoomOutFactor': 0.0,
			'onZoomUpdate': function(o, ev){
				self.hudZoom.text(Math.round(self.pinch.zoomFactor * 100, 2) / 100);
				return;
			},
			'onDragUpdate': function(o, ev){
				self.hudPosX.text(Math.round(self.pinch.offset.x * 100, 2) / 100);
				self.hudPosY.text(Math.round(self.pinch.offset.y * 100, 2) / 100);
				return;
			},
			'onReady': function(pz) {
				jQuery('.atl-viewport').removeClass('o-0');
				return;
			}
		});

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	getZoomLevel() {

		let t = this.surface.css('transform');

		////////

		if(typeof t === 'undefined')
		return 1.0;

		if(t === 'none')
		return 1.0;

		////////

		let m = t.match(/matrix\(([\d\.\-]+), ([\d\.\-]+), ([\d\.\-]+), ([\d\.\-]+), ([\d\.\-]+), ([\d\.\-]+)\)/);

		return parseFloat(m[1]);
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

export default Viewport;
