# Add factchecks to your Laravel application

[![Latest Version on Packagist](https://img.shields.io/packagist/v/starfolksoftware/factchecks.svg?style=flat-square)](https://packagist.org/packages/starfolksoftware/factchecks)
[![Build Status](https://img.shields.io/travis/starfolksoftware/factchecks/master.svg?style=flat-square)](https://travis-ci.org/starfolksoftware/factchecks)
[![Total Downloads](https://img.shields.io/packagist/dt/starfolksoftware/factchecks.svg?style=flat-square)](https://packagist.org/packages/starfolksoftware/factchecks)

Add the ability to associate factchecks to your Laravel Eloquent models. The factchecks can be approved and nested.

```php
$post = Post::find(1);

/**
 * Attach a factcheck to this model.
 *
 * @param string $claim
 * @param string $conclusion
 * @return \Illuminate\Database\Eloquent\Model
 */
$post->factcheck('Messi is the great', 'You cant be wrong with that');

/**
 * Attach a factcheck to this model as a specific user.
 *
 * @param Model|null $user
 * @param string $claim
 * @param string $conclusion
 * @return \Illuminate\Database\Eloquent\Model
 */
$post->factcheckAsUser($user, 'Messi is the great', 'You cant be wrong with that');
```

## Installation

You can install the package via composer:

```bash
composer require starfolksoftware/factchecks
```

The package will automatically register itself.

You can publish the migration with:

```bash
php artisan vendor:publish --provider="StarfolkSoftware\Factchecks\FactchecksServiceProvider" --tag="migrations"
```

After the migration has been published you can create the media-table by running the migrations:

```bash
php artisan migrate
```

You can publish the config-file with:

```bash
php artisan vendor:publish --provider="StarfolkSoftware\Factchecks\FactchecksServiceProvider" --tag="config"
```

## Usage

### Registering Models

To let your models be able to receive factchecks, add the `HasFactchecks` trait to the model classes.

``` php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use StarfolkSoftware\Factchecks\Traits\HasFactchecks;

class Post extends Model
{
    use HasFactchecks;
    ...
}
```

### Creating Factchecks

To create a comment on your commentable models, you can use the `comment` method. It receives the string of the comment that you want to store.

```php
$post = Post::find(1);

$factcheck = $post->factcheck(array([
  'claim' => 'Messi is the greatest of all time',
  'conclusion' => 'You cant be wrong with that'
]));
```

The factcheck method returns the newly created factcheck class.

Sometimes you also might want to create factchecks on behalf of other users. You can do this using the `factcheckAsUser` method and pass in your user model that should get associated
with this factcheck:

```php
$post = Post::find(1);

$factcheck = $post->factcheckAsUser($yourUser, array([
  'claim' => 'Messi is the greatest of all time',
  'conclusion' => 'You cant be wrong with that'
]));
```

### Auto Approve Factchecks

If you want to automatically approve a factcheck for a specific user (and optionally model) you can let your User model implement the following interface and method:

```php
namespace App\Models;

use StarfolkSoftware\Factchecks\Contracts\Factchecker;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements Factchecker
{
  /**
   * Check if a comment for a specific model needs to be approved.
   * @param mixed $model
   * @return bool
   */
  public function needsFactcheckApproval($model): bool
  {
    return false;    
  } 
}
```

The `needsFactcheckApproval` method received the model instance that you want to add a factcheck to and you can either return `true` to mark the factcheck as **not** approved, or return `false` to mark the factcheck as **approved**.

### Auto Approve Factchecks

If you want to automatically approve a factcheck for a specific user (and optionally model) you can let your User model implement the following interface and method:

```php
namespace App\Models;

use StarfolkSoftware\Factchecks\Contracts\Factchecker;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements Factchecker
{
  /**
   * Check if a comment for a specific model needs to be approved.
   * @param mixed $model
   * @return bool
   */
  public function needsFactcheckApproval($model): bool
  {
    return false;    
  } 
}
```

The `needsFactcheckApproval` method received the model instance that you want to add a factcheck to and you can either return `true` to mark the factcheck as **not** approved, or return `false` to mark the factcheck as **approved**.

### Submitting Factchecks

By default, all factchecks that you create are saved as draft and not approved - this is just a datetime column called `submitted_at` that you can use in your views/controllers to filter out factchecks that you might not yet want to display.

To submit a single comment, you may use the `submit` method on the Factcheck model like this:

```php
$post = Post::find(1);
$factcheck = $post->factchecks->first();

$factcheck->submit()
```

### Approving Factchecks

After submitting a factcheck, the next stage on the journey to publishing is approval - this is just a datetime column called `approved_at` that you can use in your views/controllers to filter out factchecks that you might not yet want to display.

To approve a single factcheck, you may use the `approve` method on the Factcheck model like this:

```php
$post = Post::find(1);
$factcheck = $post->factchecks->first();

$factcheck->approve();
```

### Publishing Factchecks

After approving a factcheck, the final stage is approval - this is just a datetime column called `published_at` that you can use in your views/controllers to filter out factchecks that you might not yet want to display.

To approve a single factcheck, you may use the `publish` method on the Factcheck model like this:

```php
$post = Post::find(1);
$factcheck = $post->factchecks->first();

$factcheck->publish();
```

### Retrieving Factchecks

The models that use the `HasFactchecks` trait have access to it's factchecks using the `factchecks` relation:

```php

$post = Post::find(1);

// Retrieve all factchecks
$factchecks = $post->factchecks;

// Retrieve only drafted factchecks
$drafts = $post->factchecks()->draft()->get();

// Retrieve only approved factchecks
$approved = $post->factchecks()->approved()->get();

// Retrieve only published factchecks
$published = $post->factchecks()->published()->get();

```

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email frknasir@yahoo.com instead of using the issue tracker.

## Credits

- [Faruk Nasir](https://github.com/frknasir)
- [Marcel Pociot](https://github.com/mpociot)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
