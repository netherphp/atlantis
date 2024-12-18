////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class Vec2 {

	constructor(x=0, y=0, unit=null) {

		this.x = x;
		this.y = y;
		this.unit = unit;

		return;
	};

	add(addval) {

		if(typeof addval === 'number') {
			this.x += addval;
			this.y += addval;
			return this;
		}

		////////

		if(typeof addval === 'object')
		if(typeof addval.x === 'number' || typeof addval.y === 'number') {
			this.x += addval.x;
			this.y += addval.y;
			return this;
		}

		////////

		throw 'expects number or Vec2';

		return this;
	};

	sub(subval) {

		if(typeof subval === 'number') {
			this.x -= subval;
			this.y -= subval;
			return this;
		}

		////////

		if(typeof subval === 'object')
		if(typeof subval.x === 'number' || typeof subval.y === 'number') {
			this.x -= subval.x;
			this.y -= subval.y;
			return this;
		}

		////////

		throw 'expects number or Vec2';

		return this;
	};

	mult(multval) {

		if(typeof multval === 'number') {
			this.x *= multval;
			this.y *= multval;
			return this;
		}

		////////

		if(typeof multval === 'object')
		if(typeof multval.x === 'number' || typeof multval.y === 'number') {
			this.x *= multval.x;
			this.y *= multval.y;
			return this;
		}

		////////

		throw 'expects number or Vec2';

		return this;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static IsThisOne(input) {

		if(typeof input !== 'object')
		return false;

		if(typeof input.x !== 'number')
		return false;

		if(typeof input.y !== 'number')
		return false;

		return true;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static FromKind(vectorLike) {

		if(!Vec2.IsThisOne(vectorLike))
		throw 'input must be simliar to Vec2';

		////////

		return new Vec2(vectorLike.x, vectorLike.y);
	};

	static FromOffset(jOffset) {

		return new Vec2(jOffset.left, jOffset.top);
	};

};

////////////////////////////////////////////////////////////////////////////////
export default Vec2; ///////////////////////////////////////////////////////////
