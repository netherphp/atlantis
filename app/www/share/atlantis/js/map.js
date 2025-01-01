import Vec2 from "../../nui/units/vec2.js";

// 2024-12-31
// this is the old style using an older apple maps which at the time of this
// writing still worked but this is the depreciated stuff. use the things in
// the maps dir instead.

export default class {

	/* this is because a version of safari from 2019 was bugged in
	just a way that made public members not work don't get me started
	on how angry i am about this.

	Config = {
		'Selector': null,
		'MarkerDataMethod': 'GET',
		'MarkerDataURL': null,
		'GeocoderLang': 'en-US',
		'GeocoderCountry': 'US',
		'OnTarget': null,
		'OnTargetNone': null,
		'OnDataset': null
	};

	Container = null;
	Title = null;
	Address = null;
	GeoLat = null;
	GeoLng = null;

	Map = null;
	LoaderData = null;
	LoaderMap = null;

	CalloutDelegate = {
		"calloutElementForAnnotation": null
	};
	*/

	constructor(Opt) {

		this.Config = {
			'Selector': null,
			'MarkerDataMethod': 'GET',
			'MarkerDataURL': null,
			'GeocoderLang': 'en-US',
			'GeocoderCountry': 'US',
			'OnTarget': null,
			'OnTargetNone': null,

			// provide a way for something else on the page to also get
			// access to the map dataset so we don't have to ask the
			// server for it multiple times. should be a function or
			// an array of them.
			'OnDataset': null
		};

		this.Container = null;
		this.Title = null;
		this.Address = null;
		this.GeoLat = null;
		this.GeoLng = null;

		this.Map = null;
		this.LoaderData = null;
		this.LoaderMap = null;

		this.CalloutDelegate = {
			"calloutElementForAnnotation": null
		};

		Object.assign(
			this.Config,
			Opt
		);

		(this.CalloutDelegate)
		.calloutElementForAnnotation = (Anno => this.BuildMarkerCallout(Anno));

		this.Init();
		return;
	};

	////////////////////////////////
	////////////////////////////////

	Init() {

		this._Init_GetElementData();
		this._Init_StartMap();

		////////

		if(this.Config.MarkerDataMethod === 'RANDOM')
		this._Init_PlotMarkersRandom();

		else if(this.Config.MarkerDataMethod === 'TARGET')
		this._Init_TargetMap();

		else
		this._Init_PlotMarkersData(
			this.Config.MarkerDataMethod,
			this.Config.MarkerDataURL
		);

		////////

		return;
	};

	_Init_GetElementData() {

		this.Container = jQuery(this.Config.Selector);
		this.Token = this.Container.attr('data-apple-map-token');
		this.Title = jQuery.trim(this.Container.attr('data-title'));
		this.Address = jQuery.trim(this.Container.attr('data-address'));
		this.GeoLat = parseFloat(this.Container.attr('data-geo-lat'));
		this.GeoLng = parseFloat(this.Container.attr('data-geo-lng'));

		if(this.Title === '')
		this.Title = null;

		return;
	};

	_Init_StartMap() {

		mapkit.init({
			'authorizationCallback': (Callback => Callback(this.Token))
		});

		this.Map = new mapkit.Map(
			this.Config.Selector.replace('#','')
		);

		this.Map.showsCompass        =
		this.Map.showsZoomControl    =
		this.Map.showsScale          =
		this.Map.showsMapTypeControl = mapkit.FeatureVisibility.Visible;
		this.Map.colorScheme = mapkit.Map.ColorSchemes.Light;
		this.Map.mapType = mapkit.Map.MapTypes.Standard;
		this.Map.tintColor = 'red';

		return;
	};

	_Init_TargetMap() {

		if(this.GeoLat !== 0.0 && this.GeoLng !== 0.0)
		return this.TargetByCoord(new Vec2(this.GeoLat,this.GeoLng,this.Title));

		if(this.Address !== '')
		return this.TargetByAddress(this.Address);

		return;
	};

	_Init_PlotMarkersRandom() {

		let Markers = new Array;
		console.log(this.GeoLat,this.GeoLng);

		for(let Iter = 0; Iter < 20; Iter++) {
			let Marker = new mapkit.MarkerAnnotation(
				new mapkit.Coordinate(
					(this.GeoLat + (Math.random()*0.5) * ((Math.random() > 0.4)?1:-1)),
					(this.GeoLng + (Math.random()*0.5) * ((Math.random() > 0.4)?1:-1))
				),
				{
					'title': `Test Property ${Iter}`,
					'animates':false,
					'color': '#EE3224',
					'glyphColor': '#ffffff',
					'glyphImage': { '1': '/share/gfx/pin.png' },
					'data': {
						'Title': `Test Property ${Iter}`,
						'ImageURL': `https://picsum.photos/640/360?v=${Iter}`,
						'Info': 'Made in the interiors of collapsing stars are creatures of the cosmos take root and flourish colonies birth courage of our questions. Orion\'s sword white dwarf rich in heavy atoms the only home we\'ve ever known inconspicuous motes of rock and gas star stuff harvesting star light.',
					},
					'calloutEnabled': true,
					'callout': this.CalloutDelegate
				}
			);

			Marker.titleVisibility = mapkit.FeatureVisibility.Hidden;
			Marker.subtitleVisibility = mapkit.FeatureVisibility.Hidden;
			Markers.push(Marker);

			jQuery('body')
			.append(
				jQuery('<img />')
				.attr('src',`https://picsum.photos/640/360?v=${Iter}`)
				.css({'width':'0px','height':'0px'})
			);
		}

		this.Map.showItems(Markers);
		return;
	};

	_Init_PlotMarkersData(DataMethod,DataURL,DataFilter) {
		this.QueryPlot(DataMethod,DataURL,DataFilter)
		return;
	};

	////////////////////////////////
	////////////////////////////////

	Target(Input) {

		if(Input instanceof Vec2)
		return this.TargetByCoord(Input);

		if(Array.isArray(Input) && Input.length === 2)
		return this.TargetByCoord(new Vec2(Input[0],Input[1]));

		return this.TargetByAddress(Input);
	};

	TargetByCoord(Point) {

		let Meta = this;
		let Colour = '#B22234';
		let Span = (!Point.Name)?(12.0):(0.02);

		let Here = new mapkit.Coordinate(Point.X,Point.Y);
		let View = new mapkit.CoordinateSpan(Span,Span);
		let Location = new mapkit.CoordinateRegion(Here,View);
		let Marker = null;

		this.Map.removeAnnotations(this.Map.annotations);
		this.Map.region = Location;

		if(!Point.Name) {
			this._Target_EventOnTarget(null,Point,null);
			return;
		}

		////////

		Marker = new mapkit.MarkerAnnotation(
			Here,
			{
				'animates': true,
				'calloutEnabled': false,
				'data': Meta,
				'color': Colour,
				'callout': this.CalloutDelegate
			}
		);

		Marker.titleVisibility = mapkit.FeatureVisibility.Hidden;
		Marker.subtitleVisibility = mapkit.FeatureVisibility.Hidden;
		this.Map.addAnnotations([Marker]);

		if(typeof this.Config.OnTarget === 'function')
		(this.Config.OnTarget)(null,Point,Marker);

		return;
	};

	TargetByAddress(Address) {

		let Geocoder = new mapkit.Geocoder({
			'language': this.Config.GeocoderCountry,
			'getsUserLocation': false
		});

		this.Map.removeAnnotations(this.Map.annotations);

		Geocoder.lookup(
			Address,
			(function(Error,Data){

				if(Error)
				return console.log(Error);

				if(Data.results.length < 1) {
					this._Target_EventOnTargetNone(Address);
					return;
				}

				let Location = new mapkit.CoordinateRegion(
					Data.results[0].coordinate,
					new mapkit.CoordinateSpan(0.02,0.02)
				);

				this.Map.region = Location;

				let Meta = this;
				let Colour = '#B22234';
				let Marker = null;

				Marker = new mapkit.MarkerAnnotation(
					Data.results[0].coordinate,
					{
						'animates': true,
						'calloutEnabled': false,
						'data': Meta,
						'color': Colour,
						'callout': this.CalloutDelegate
					}
				);

				Marker.titleVisibility = mapkit.FeatureVisibility.Hidden;
				Marker.subtitleVisibility = mapkit.FeatureVisibility.Hidden;
				this.Map.addAnnotations([Marker]);

				this._Target_EventOnTarget(
					Address,
					new Vec2(Data.results[0].coordinate.latitude,Data.results[0].coordinate.longitude),
					Marker
				);

				return;
			}).bind(this),
			{ 'limitToCountries': this.Config.GeocoderCountry }
		);

		return;
	};

	_Target_EventOnTarget(Address,Coord,Marker) {

		if(typeof this.Config.OnTarget === 'function')
		(this.Config.OnTarget)(Address,Coord,Marker,this);

		return;
	};

	_Target_EventOnTargetNone(Address) {

		console.log(`no results: ${Address}`);

		if(typeof this.Config.OnTargetNone === 'function')
		(this.Config.OnTargetNone)(Address,this);

		return;
	};

	////////////////////////////////
	////////////////////////////////

	QueryPlot(Method,URL,Dataset) {

		let self = this;

		this.Map.removeAnnotations(this.Map.annotations);

		(new Promise((function(Next,Halt){
			Toaster.Request({
				'Method': Method,
				'URL': URL,
				'Data': Dataset,
				'OnSuccess': Result => Next(Result)
			});
		}).bind(this)))
		.then((function(Result){
			return new Promise(function(Next, Fail){
				if(self.Config.OnDataset !== null)
				setTimeout(
					(function(){

						if(Array.isArray(self.Config.OnDataset)) {
							for(const Func of self.Config.OnDataset)
							Func(Result);
						}

						if(typeof self.Config.OnDataset === 'function')
						self.Config.OnDataset(Result);

						return;
					}),
					25
				);

				Next(Result);
				return;
			});
		}))
		.then((function(Result){

			let Markers = new Array;

			(Result.Payload)
			.forEach((function(Item){
				let Marker = new mapkit.MarkerAnnotation(
					new mapkit.Coordinate(Item.GeoLat,Item.GeoLng),
					{
						'title': Item.Name,
						'animates': false,
						'color': '#EE3224',
						'glyphColor': '#ffffff',
						'glyphImage': { '1': '/share/gfx/pin.png' },
						'data': Item,
						'calloutEnabled': true,
						'callout': this.CalloutDelegate
					}
				);

				Marker.titleVisibility = mapkit.FeatureVisibility.Hidden;
				Marker.subtitleVisibility = mapkit.FeatureVisibility.Hidden;
				Markers.push(Marker);

				return;
			}).bind(this));

			this.Map.showItems(Markers);
			return;
		}).bind(this));

		return;
	};

	////////////////////////////////
	////////////////////////////////

	BuildMarkerCallout(Anno) {

		let Output;
		let ImageBox;
		let ContentBox;
		let Address = '';

		// format the address.

		if(Anno.data.Address)
		Address += Anno.data.Address + ' ';

		if(Anno.data.Unit)
		Address += Anno.data.Unit + ' ';

		Address += '<br />';

		if(Anno.data.City)
		Address += Anno.data.City + ' ';

		if(Anno.data.State)
		Address += Anno.data.State + ' ';

		if(Anno.data.Zip)
		Address += Anno.data.Zip + ' ';

		Address = jQuery.trim(Address);

		Output = (
			jQuery('<div />')
			.css({
				'background': '#fff',
				'border-radius': '6px',
				'box-shadow': '0px 0px 10px #000f',
				'padding': '4px',
				'max-width': '300px',
				'min-width': '300px'
			})
			.append(
				jQuery('<div />')
				.addClass('row tight')
				.append(
					ImageBox = (
						jQuery('<div />')
						.addClass('col-12')
					)
				)
				.append(
					ContentBox = (
						jQuery('<div />')
						.addClass('col-12')
					)
				)
			)
		);

		// image

		if(Anno.data.PhotoURL) {
			ImageBox.append(
				jQuery('<div />')
				.addClass('WallpaperedBox SixteenByNine mb-2')
				.css({
					'background-image': 'url(' + Anno.data.PhotoURL + ')',
					'min-width': '100px'
				})
			);
		}

		else {
			ImageBox.addClass('d-none');
		}

		// title / link

		ContentBox
		.append(
			jQuery('<div />')
			.addClass('text-center mb-2')
			.append(
				jQuery('<span />')
				.addClass('font-weight-bold')
				.text(Anno.data.Name)
			)
		)
		.append(
			jQuery('<div />')
			.addClass('text-center mb-2')
			.html(Address)
		);

		// location

		ContentBox.append(
			jQuery('<div />')
			.addClass('mb-2')
			.append(
				jQuery('<span />')
				.addClass('text-size-small text-muted font-italic')
				.text(Anno.data.Location)
			)
		);

		// view button

		if(Anno.data.DownloadURL !== null)
		Output.append(
			jQuery('<div />')
			.addClass('mt-2')
			.append(
				jQuery('<a />')
				.addClass('btn btn-local-primary btn-block btn-sm')
				.attr('href',Anno.data.DownloadURL)
				.attr('target','')
				.text('More Info')
			)
		);

		return Output[0];
	};

	Escape(Input) {

		let Baddies = {
			'<': '&lt;',
			'>': '&gt;',
			'"': '&quot;',
			'\'': '&#39;',
			'&': '&amp;'
		};

		return Input.replace(
			/[<>"'&]/g,
			(Key => Baddies[Key])
		);
	};

};
