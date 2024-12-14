
class Vec2 {

	constructor(x=0, y=0, unit=null) {

		this.x = x;
		this.y = y;
		this.unit = unit;

		return;
	};

	add(otherVec2) {

		this.x += otherVec2.x;
		this.y += otherVec2.y;

		return this;
	};

	sub(otherVec2) {

		this.x -= otherVec2.x;
		this.y -= otherVec2.y;

		return this;
	};

};

export default Vec2;
