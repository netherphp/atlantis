import AtlBlobEntity from './js/ents/blob.js';
import AtlBlobGroup  from './js/ents/blob-group.js';
import AtlToggleBtn  from './js/ui/togglebtn.js';
import AtlCollapser  from '../nui/util/collapser.js';

import Photo   from './photo.js';
import Profile from './profile.js';
import VideoTP from './video-tp.js';

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

jQuery(function() {

	AtlBlobEntity.WhenDocumentReady();
	AtlBlobGroup.WhenDocumentReady();
	AtlToggleBtn.WhenDocumentReady();
	AtlCollapser.WhenDocumentReady();

	Photo.WhenDocumentReady();
	Profile.WhenDocumentReady();
	VideoTP.WhenDocumentReady();

	return;
});

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

export default {
	"Blob":    { 'Entity': AtlBlobEntity, 'Group': AtlBlobGroup },
	"Photo":   Photo,
	"Profile": Profile,
	"VideoTP": VideoTP
};
