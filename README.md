# php-model-controller

## About this Project

This project is a sample lightweight model controller for REST APIs

## How to install:

- Setup php and mysql server
- open database/config_template.php
- Fill in credentials for the MYSQL database
- Save file as "config.php"
- Setup Composer:

```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === 'c31c1e292ad7be5f49291169c0ac8f683499edddcfd4e42232982d0fd193004208a58ff6f353fde0012d35fdd72bc394') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"

php composer.phar install
```

Optional: Install Phpmyadmin (to see what's going on)

- Download phpmyadmin from https://www.phpmyadmin.net/
- place folder into the root folder of this project

## Models

The ModelBase class implements the basic database interaction functions for any php class.
If a php class extends Model, some basic database interaction functions are added, such as:
retreive(\$id), save(), update(), delete(), is_valid()

Some (experimental) database actions are added such as:
create_db_table() => Creates a table in the database representing the child class
TODO: update_db_table() => Update talbe structure according to child class

The ModelsBase class implements the helper functions for any php class.
If a php class extends Models, some basic database helper functions are added, such as:
get_by_id(\$id), get_all(), count()

For example the cat object:

```php
require_once __DIR__ . '/ModelBase.php';
require_once __DIR__ . '/ModelsBase.php';

//The Cat class represents the Cat object stored in the databsae
class Cat extends ModelBase
{
    //properties
    public $id;                 //int(11)
    public $name;               //text
    public $gender;             //text
    public $date_of_birth;      //date
    public $weight;             //decimal(9,2)
    public $pretty;             //bool
    public $teeth;              //int(11)
    //endproperties


    public function __construct($object)
    {
        if ($object) {
            $this->overwrite_atributes($object);
        }
        parent::__construct("cats");
    }

    //Add custom cat funtions here:
    public function increase_weight($delta)
    {
        $sql = "UPDATE `cats` SET `weight` = weight + $delta WHERE `cats`.`id` = $this->id; ";
        return $this->query($sql);
    }
}

//The Cats class represents the Cat table stored in the databsae
class Cats extends ModelsBase
{
    protected static $table_name = "cats";
    protected static $object_name = "Cat";

    //Add custom cats funtions here...
}

```

You can deal with your cat like this:

```php
$cat = new Cat([]);
$cat->name = "TestCat";
$cat->gender = "male";
$cat->date_of_birth = "2014-02-03";
$cat->weight = 5.0;
$cat->pretty = 1;
$cat->teeth = 22;

//Saves the cat into the DB
$cat_id = $cat->save();
```

And with your Cats likt that:

```php
$cats = Cats::get_all();
$cat = Cats::get_by_id($cat_id);
$cat->name = "New Cat Name";
$cat->update();
```

How to deal with models?
=> Check example.php (https://github.com/Florian-Bolli/php-model-controller-api/blob/master/example.php)

## Controllers

The base Controller class implements the basic API endpoints for any model controller.
If a class extends Controller, some basic API functions are implemented such as: get, index, create, update, delete

For example checkout cat_controller.php (https://github.com/Florian-Bolli/php-model-controller-api/blob/master/controllers/cat_controller.php)

## API

The API requests are handled by index.php in the following way:
https://api.example.ch/{object}/{id}/{function}

For example checkout index.php (https://github.com/Florian-Bolli/php-model-controller-api/blob/master/index.php)

## Tests

Run the tests with phpunit

```bash
./vendor/bin/phpunit tests
```
