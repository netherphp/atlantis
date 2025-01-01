////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class Vec3 {

	constructor(x=0, y=0, z=0, unit=null) {

		this.x = x;
		this.y = y;
		this.z = z;
		this.unit = unit;

		return;
	};

	add(addval) {

		if(typeof addval === 'number') {
			this.x += addval;
			this.y += addval;
			this.z += addval;
			return this;
		}

		////////

		if(typeof addval === 'object')
		if(typeof addval.x === 'number' || typeof addval.y === 'number' || typeof addval.z === 'number') {
			this.x += addval.x;
			this.y += addval.y;
			this.z += addval.z;
			return this;
		}

		////////

		throw 'expects number or Vec3';

		return this;
	};

	sub(subval) {

		if(typeof subval === 'number') {
			this.x -= subval;
			this.y -= subval;
			this.z -= subval;
			return this;
		}

		////////

		if(typeof subval === 'object')
		if(typeof subval.x === 'number' || typeof subval.y === 'number' || typeof subval.z === 'number') {
			this.x -= subval.x;
			this.y -= subval.y;
			this.z -= subval.z;
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
			this.z *= multval;
			return this;
		}

		////////

		if(typeof multval === 'object')
		if(typeof multval.x === 'number' || typeof multval.y === 'number' || typeof multval.z === 'number') {
			this.x *= multval.x;
			this.y *= multval.y;
			this.z *= multval.z;
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

		if(typeof input.z !== 'number')
		return false;

		return true;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static FromVec2(v2, z) {

		return new Vec3(v2.x, v2.y, z);
	};

	static FromKind(vectorLike) {

		if(!Vec3.IsThisOne(vectorLike))
		throw 'input must be simliar to Vec3';

		////////

		return new Vec3(vectorLike.x, vectorLike.y, vectorLike.z);
	};

	static FromOffset(jOffset) {

		return new Vec3(jOffset.left, jOffset.top, jOffset.zIndex);
	};

};

////////////////////////////////////////////////////////////////////////////////
export default Vec3; ///////////////////////////////////////////////////////////
