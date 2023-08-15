# Credits

Thanks to:

1. https://github.com/tareq1988/wp-eloquent
2. https://github.com/corcel/corcel

# Eloquent Wrapper for WordPress

This is a library package to use Laravel's [Eloquent ORM](http://laravel.com/docs/5.5/eloquent) with WordPress.


## How it Works

 - Eloquent is mainly used here as the query builder
 - [WPDB](http://codex.wordpress.org/Class_Reference/wpdb) is used to run queries built by Eloquent
 - Hence, we have the benfit to use plugins like `debug-bar` or `query-monitor` to get SQL query reporting.
 - It doesn't create any extra MySQL connection

## Minimum Requirement
 - PHP 7.0
 - WordPress 3.6+

## Package Installation

`$ composer require wenprise/eloquent`

# Usage Example

## Basic Usage

```php
$db = \Wenprise\ORM\Eloquent\Database::instance();

var_dump( $db->table('users')->find(1) );
var_dump( $db->select('SELECT * FROM wp_users WHERE id = ?', [1]) );
var_dump( $db->table('users')->where('user_login', 'john')->first() );

// OR with DB facade
use \Wenprise\ORM\Eloquent\Facades\DB;

var_dump( DB::table('users')->find(1) );
var_dump( DB::select('SELECT * FROM wp_users WHERE id = ?', [1]) );
var_dump( DB::table('users')->where('user_login', 'john')->first() );
```

## Posts

```php
use Wenprise\ORM\WP\Post;

// All published posts
$posts = Post::published()->get();
$posts = Post::status('publish')->get();

// A specific post
$post = Post::find(31);
echo $post->post_title;
```

## Pages

Pages are like custom post types. You can use `Post::type('page')` or the `Wenprise\ORM\WP\Page` class.

```php
// Find a page by slug
$page = Page::slug('about')->first(); // OR
$page = Post::type('page')->slug('about')->first();
echo $page->post_title;
```

## Comments

```php
use Wenprise\ORM\WP\Comment;

// Get Comment with id 12345
$comment = Comment::find(12345);

// Get related data
$comment->post;
$comment->author;
$comment->meta
```

## Meta Data (Custom Fields)

You can retrieve meta data from posts too.

```php
// Get a custom meta value (like 'link' or whatever) from a post (any type)
$post = Post::find(31);
echo $post->meta->link; // OR
echo $post->fields->link;
echo $post->link; // OR
```

To create or update meta data form a User just use the `saveMeta()` or `saveField()` methods. They return `bool` like the Eloquent `save()` method.

```php
$post = Post::find(1);
$post->saveMeta('username', 'jgrossi');
```

You can save many meta data at the same time too:

```php
$post = Post::find(1);
$post->saveMeta([
    'username' => 'jgrossi',
    'url' => 'http://jgrossi.com',
]);
```

You also have the `createMeta()` and `createField()` methods, that work like the `saveX()` methods, but they are used only for creation and return the `PostMeta` created instance, instead of `bool`.

```php
$post = Post::find(1);
$postMeta = $post->createMeta('foo', 'bar'); // instance of PostMeta class
$trueOrFalse = $post->saveMeta('foo', 'baz'); // boolean
```

### Querying Posts by Custom Fields (Meta)

There are multiples possibilities to query posts by their custom fields (meta). Just use the `hasMeta()` scope under `Post` (actually for all models using the `HasMetaFields` trait) class:

```php
// Using just one custom field
$post = Post::published()->hasMeta('username', 'jgrossi')->first(); // setting key and value
$post = Post::published()->hasMeta('username'); // setting just the key
```

You can also use the `hasMeta()` scope passing an array as parameter:

```php
$post = Post::hasMeta(['username' => 'jgrossi'])->first();
$post = Post::hasMeta(['username' => 'jgrossi', 'url' => 'jgrossi.com'])->first();
// Or just passing the keys
$post = Post::hasMeta(['username', 'url'])->first();
```

### Fields Aliases

The `Post` class has support to "aliases", so if you check the `Post` class you should note some aliases defined in the static `$aliases` array, like `title` for `post_title` and `content` for `post_content`.

```php
$post = Post::find(1);
$post->title === $post->post_title; // true
```

If you're extending the `Post` class to create your own class you can use `$aliases` too. Just add new aliases to that static property inside your own class and it will automatically inherit all aliases from parent `Post` class:

```php
class A extends Post
{
    protected static $aliases = [
        'foo' => 'post_foo',
    ];
}

$a = A::find(1);
echo $a->foo;
echo $a->title; // from Post class
```

### Custom Scopes

To order posts you can use `newest()` and `oldest()` scopes, for both `Post` and `User` classes:

```php
$newest = Post::newest()->first();
$oldest = Post::oldest()->first();
```

### Pagination

To order posts just use Eloquent `paginate()` method:

```php
$posts = Post::published()->paginate(5);
foreach ($posts as $post) {
    // ...
}
```

To display the pagination links just call the `links()` method:

 ```php
 {{ $posts->links() }}
 ```
 
## Post Taxonomies

You can get taxonomies for a specific post like:

```php
$post = Post::find(1);
$taxonomy = $post->taxonomies()->first();
echo $taxonomy->taxonomy;
```

Or you can search for posts using its taxonomies:

```php
$post = Post::taxonomy('category', 'php')->first();
```


## Categories and Taxonomies

Get a category or taxonomy or load posts from a certain category. There are multiple ways
to achieve it.

```php
// all categories
$cat = Taxonomy::category()->slug('uncategorized')->first()->posts();
echo "<pre>"; print_r($cat->name); echo "</pre>";

// only all categories and posts connected with it
$cat = Taxonomy::where('taxonomy', 'category')->with('posts')->get();
$cat->each(function($category) {
    echo $category->name;
});
```
 
## Attachment and Revision

Getting the attachment and/or revision from a `Post` or `Page`.

```php
$page = Page::slug('about')->with('attachment')->first();
// get feature image from page or post
print_r($page->attachment);

$post = Post::slug('test')->with('revision')->first();
// get all revisions from a post or page
print_r($post->revision);
```

## Users

You can manipulate users in the same manner you work with posts:

```php
// All users
$users = User::get();

// A specific user
$user = User::find(1);
echo $user->user_login;
```

## Options

You can use the `Option` class to get data from `wp_options` table:

```php
$siteUrl = Option::get('siteurl');
```

You can also add new options:

```php
Option::add('foo', 'bar'); // stored as string
Option::add('baz', ['one' => 'two']); // this will be serialized and saved
```

You can get all options in a simple array:

```php
$options = Option::asArray();
echo $options['siteurl'];
```

Or you can specify only the keys you want to get:

```php
$options = Option::asArray(['siteurl', 'home', 'blogname']);
echo $options['home'];
```

## Writing a Model

```php
use \Wenprise\ORM\Eloquent\Model;

class Employee extends Model {

    /**
    * Name for table without prefix, the model can automatic add it
    *
    * @var string
    */
    protected $table = 'table_name';
    
    /**
    * Columns that can be edited
    *
    * @var array
    */
    protected $fillable = [];
    
    /**
    * Disable created_at and update_at columns, unless you have those.
    */
	 public $timestamps = false;
	 
    /**
    * Set primary key as ID, because WordPress
    *
    * @var string
    */
	 protected $primaryKey = 'ID';
    
    /**
     * Make ID guarded -- without this ID doesn't save.
     *
     * @var string
     */
    protected $guarded = [ 'ID' ];
    
    /**
     * The column names allow to be filled 
     * @var array
     */
    protected $fillable = [
        'name',
        'status',
    ];

}

var_dump( Employee::all()->toArray() ); // gets all employees
var_dump( Employee::find(1) ); // find employee with ID 1
```
The class name `Employee` will be translated into `PREFIX_employees` table to run queries. But as usual, you can override the table name.


