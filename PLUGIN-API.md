# Plugin API

## Profiles

### Add Buttons to Profile Admin Menus

```php
namespace Local\Plugin;

use Nether\Atlantis;
use Nether\Common;
use Nether\Atlantis\Plugin\Interfaces\ProfileView;

class ProfileAdminMenuExample
extends Atlantis\Plugin
implements ProfileView\AdminMenuSectionInterface {

	public function
	GetItemsForSection(Atlantis\Profile\Entity $Ent, string $Key):
	Common\Datastore {

		$Items = new Common\Datastore;

		// ...

		return $Items;
	}

};
```
