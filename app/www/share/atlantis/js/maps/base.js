import Vec2 from '../../../nui/units/vec2.js';
import Vec3 from '../../../nui/units/vec3.js';

class MapBase {

	static get CordContAmericaNorth() { return new Vec2(39.83303361158351, -95.64536071542578); }
	static get CordContAmericaSouth() { return new Vec2(-23.901523921083545, -61.225399493041635); }
	static get CoordContAfrica()      { return new Vec2(1.9975632625438837, 17.6886579436443); }
	static get CoordContEurope()      { return new Vec2(48.5387479118523, 18.212876024861743); }
	static get CoordContAsia()        { return new Vec2(38.93510078290302, 86.87817691598644); }
	static get CoordContAustralia()   { return new Vec2(-26.30324821144853, 133.8402365183465); }

	static get DistOrbitLow()        { return 2_000_000; }
	static get DistOrbitMedium()     { return 5_700_000; }
	static get DistOrbitMediumHigh() { return 9_882_543; }
	static get DistOrbitHigh()       { return 35_123_428; } // apple map caps at 26581442.113281302

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	constructor(selector) {

		this.selector = selector;
		this.center = null;
		this.element = null;
		this.map = null;

		this.pinColourBG = 'green';
		this.pinColourText = 'white';

		this.boundMapMoved = null;
		this.boundMapZoomed = null;

		this.initElement();

		return;
	};

	setPinBackgroundColour(c) {

		this.pinColourBG = c;

		return this;
	};

	setPinTextColour(c) {

		this.pinColourText = c;

		return this;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	gotoCoord(lat, lng, elev=null, { anim=false }={}) {

		console.log(`[MapBase.gotoCoord] this method must be implemented by the map subclass`);

		return this;
	};

	gotoVec2(v, z=null, { anim=false }={}) {

		if(Vec2.IsThisOne(v))
		return this.gotoCoord(v.x, v.y, z, { anim: anim });

		console.log(`[MapBase.gotoVec2] must be Vec2-ish`);

		return this;
	};

	gotoVec3(v, { anim=false }={}) {

		if(Vec3.IsThisOne(v))
		return this.gotoCoord(v.x, v.y, v.z, { anim: anim });

		console.log(`[MapBase.gotoVec3] must be Vec3-ish`);

		return this;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	addPinCoord(lat, lng, title) {

		console.log(`[MapBase.addPinCoord] this method must be implemented by the map subclass`);

		return this;
	};

	addPinVec2(v, title) {

		return this.addPinCoord(v.x, v.y, title);
	};

	addPinVec3(v, title) {

		return this.addPinCoord(v.x, v.y, title);
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	onMapZoomed(ev) {

		console.log(`[MapBase.onMapZoomed] ${this.map.cameraDistance}`);

		return;
	};

	onMapMoved(ev) {

		console.log(`[MapBase.onMapMoved] ${this.map.center.latitude} ${this.map.center.longitude}`);

		return;
	};

	onPinCallout() {

		console.log(`[MapBase.onPinCallout] this method must be implemented by the map subclass`);

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	initElement() {

		this.element = jQuery(this.selector);

		return this;
	};

	getElementNative() {

		return this.element.get(0);
	};

};

export default MapBase;
