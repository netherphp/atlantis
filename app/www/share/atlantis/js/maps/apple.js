import MapBase from './base.js';
import Vec2 from '../../../nui/units/vec2.js';
import Vec3 from '../../../nui/units/vec3.js';

// assume metric when working with apple.
// for example, set camera distance is elevation in meters. only mentioning
// this because apple's documentation couldn't be arsed to tell you.

let TemplateCallout = `
<div class="d-flex g-2 gap-2" style="text-shadow:none;max-width:400px;">
	<div class="flex-grow-0">
		<div class="ratiobox rounded square wallpapered bg-grey-dk" style="width:100px;"></div>
	</div>
	<div class="flex-grow-1 tc-black">
		I can do whatever I want in here.
		<div class="fs-smallerer ff-mono fw-bold">Custom Data: <span></span></div>
	</div>
</div>
`;

class AppleMap
extends MapBase {

	constructor(selector) {

		super(selector);
		this.initAppleMaps();

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	initAppleMaps() {

		this.map = new mapkit.Map(
			this.getElementNative(),
			{ }
		);

		this.boundMapZoomed = this.onMapZoomed.bind(this);
		this.boundMapMoved = this.onMapMoved.bind(this);

		this.map.addEventListener('zoom-end', this.boundMapZoomed);
		this.map.addEventListener('scroll-end', this.boundMapMoved);

		return this;
	};

	////////////////////////////////////////////////////////////////
	// abstract implementations ////////////////////////////////////

	gotoCoord(lat, lng, elev=null, { anim=false }={}) {

		console.log(`[AppleMap.gotoCoord] ${lat} ${lng} ${elev} ${anim}`);

		////////

		if(typeof lat === 'number' && typeof lng === 'number')
		this.map.setCenterAnimated(
			(new mapkit.Coordinate(lat, lng)),
			anim
		);

		if(typeof elev === 'number')
		this.map.setCameraDistanceAnimated(
			elev, anim
		);

		////////

		return this;
	};

	addPinCoord(lat, lng, { title=null, subtitle=null, callout=false, bgc=null, tc=null, icon=null, iconSelected=null, data=null }={}) {

		console.log(`[AppleMap.addPinCoord] ${lat} ${lng}`);

		let c = new mapkit.Coordinate(lat, lng);

		console.log(this.pinColourBG);
		console.log(bgc);

		let m = new mapkit.MarkerAnnotation(c, {
			'draggable': false,
			'animates': false,
			'title': title,
			'subtitle': subtitle,
			'color': this.pinColourBG,
			'glyphColor': this.pinColourText,
			'titleVisibility': mapkit.FeatureVisibility.Hidden,
			'subtitleVisibility': mapkit.FeatureVisibility.Hidden,
			'data': data
		});

		////////

		if(bgc !== null)
		m.color = bgc;

		if(tc)
		m.glyphColor = tc;

		if(icon)
		m.glyphImage = { 1: icon };

		if(iconSelected)
		m.selectedGlyphImage = { 1: iconSelected };

		if(typeof callout === 'function')
		m.callout = {
			calloutContentForAnnotation:
			callout
		};

		if(callout === true)
		m.callout = {
			calloutContentForAnnotation:
			this.onPinCallout.bind(this)
		};

		////////

		this.map.addAnnotation(m);

		return this;
	};

	onPinCallout(marker) {

		// the input is the apple marker annotation object.
		// opt.data supplied to addPinCoord is on marker.data

		let box = jQuery(TemplateCallout);

		box.find('span').text(JSON.stringify( marker.data ));

		return box.get(0);
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static FromElement(selector, onReadyFunc) {

		let el = jQuery(selector);
		let token = el.attr('data-apple-map-token');
		let mkjs = jQuery('#AppleMapLibrary');

		// first map on the page will load the scripting first.

		if(mkjs.length === 0) {
			AppleMap.InjectMapKitScript(token, selector, onReadyFunc);
			return;
		}

		// other maps can just launch themselves.

		AppleMap.OnReadyNewMap(selector, onReadyFunc);

		return;
	};

	static InjectMapKitScript(token, selector, onReadyFunc) {

		// pretty sure i cannot get apple's data-callback system to work
		// within an es6 module the way i want so this kicks off the
		// loading of their script from within so we can listen to the
		// load event instead.

		let mkjs = jQuery('<script />');

		mkjs.attr('id', 'AppleMapLibrary');
		mkjs.attr('type', 'text/javascript');
		mkjs.attr('src', 'https://cdn.apple-mapkit.com/mk/5.x.x/mapkit.js');
		mkjs.attr('crossorigin', 'anonymous');
		mkjs.attr('data-language', 'en-US');
		mkjs.attr('data-token', token);
		mkjs.attr('data-libraries', 'services,full-map,geojson');
		mkjs.on('load', function() {
			AppleMap.OnReadyNewMap(selector, onReadyFunc);
			return;
		});

		document.head.appendChild(mkjs.get(0));

		return;
	};

	static OnReadyNewMap(selector, onReadyFunc) {

		let map = new AppleMap(selector);

		onReadyFunc(map);

		return;
	};

};

export default AppleMap;
