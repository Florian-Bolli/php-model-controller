# php-model-controller


## About this Project ##
This project is a sample lightweight model controller for REST APIs

## Models ##
The base Model class implements the basic database interaction functions for any php class.
If a php class extends Model

1) some basic database interaction functions are added, such as:
retreive($id), save(), update(), delete(), is_valid()

2) some basic database helper functions are added, such as:
get_by_id($id), get_all(), count()

3) Some (experimental) database actions are added such as:
create_db_table() => Creates a table in the database representing the child class
TODO: update_db_table() => Update talbe structure according to child class

How to deal with models?
=> Check example.php (https://github.com/Florian-Bolli/php-model-controller-api/blob/master/example.php)

## Controllers ##
The base Controller class implements the basic API endpoints for any model controller.
If a class extends Controller, some basic API functions are implemented such as: get, index, create, update, delete

For example checkout cat_controller.php (https://github.com/Florian-Bolli/php-model-controller-api/blob/master/controllers/cat_controller.php)

## API ##
The API requests are handled by index.php in the following way:
https://api.example.ch/{object}/{id}/{function}

For example checkout index.php (https://github.com/Florian-Bolli/php-model-controller-api/blob/master/index.php)


## How to install: ##
 - Setup php and mysql server
 - open database/config_template.php
 - Fill in credentials for the MYSQL database
 - Save file as "config.php"

Optional: Install Phpmyadmin (to see what's going on)
 - Download phpmyadmin from https://www.phpmyadmin.net/
 - place folder into the root folder of this project


