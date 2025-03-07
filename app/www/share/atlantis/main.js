import AtlBlobEntity from './js/ents/blob.js';
import AtlBlobGroup  from './js/ents/blob-group.js';

import Photo   from './photo.js';
import Profile from './profile.js';
import VideoTP from './video-tp.js';

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

jQuery(function() {

	AtlBlobEntity.WhenDocumentReady();
	AtlBlobGroup.WhenDocumentReady();

	Photo.WhenDocumentReady();
	Profile.WhenDocumentReady();
	VideoTP.WhenDocumentReady();

	return;
});

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

export default {
	"Blob":    AtlBlobEntity,
	"Photo":   Photo,
	"Profile": Profile,
	"VideoTP": VideoTP
};
