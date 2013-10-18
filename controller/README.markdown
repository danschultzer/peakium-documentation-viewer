# Controller directory

You can create custom controllers to change the default behavior.

It might look like the following.

`index.php`

```php
namespace Controller;

class Index extends \Controller
{
	public $format = 'view';
	public $reference ='pages/index';

	public function get()
	{
		...
	}
}

```

You can modify the controller to act exactly as you need it to, allowing for very flexible documentation presentation.