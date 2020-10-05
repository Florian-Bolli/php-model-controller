# php-model-controller


## About this Project ##
This project is a sample model controller for REST APIs

The base Model class implements the basic database interaction functions for any php class.
If a php class extends Model

1) some basic database interaction functions are added, such as:
retreive($id), save(), update(), delete(), is_valid()

2) some basic database helper functions are added, such as:
get_by_id($id), get_all(), count()

3) Some (experimental) database actions are added such as:
create_db_table() => Creates a table in the database representing the child class
TODO: update_db_table() => Update talbe structure according to child class

There is also a simple set of API endpoints (./cats/) for demonstration purposes.

## How to install: ##
 - open database/config_template.php
 - Fill in credentials for the MYSQL database
 - Save file as "config.php"

Optional: Install Phpmyadmin (to see what's going on)
 - Download phpmyadmin from https://www.phpmyadmin.net/
 - place folder into the root folder of this project


