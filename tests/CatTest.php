<?php

declare(strict_types=1);
require_once 'models/cat.php';

use PHPUnit\Framework\TestCase;

global $cat_id;
global $count;

final class CatTest extends TestCase
{

    public function testCountCats_1(): void
    {
        global $count;
        $count = Cats::count();
        $this->assertIsInt($count);
    }

    public function testSaveCat(): void
    {
        global $cat_id;

        $cat = new Cat([]);
        $cat->name = "TestCat";
        $cat->gender = "male";
        $cat->date_of_birth = "2014-02-03";
        $cat->weight = 5.0;
        $cat->pretty = 1;
        $cat->teeth = 22;


        $this->assertEquals(
            $cat->text(),
            '{"id":null,"name":"TestCat","gender":"male","date_of_birth":"2014-02-03","weight":5,"pretty":1,"teeth":22,"table_name":"cats"}'
        );

        $cat_id = $cat->save();

        $this->assertIsInt($cat_id);
    }


    public function testCountCats_2(): void
    {
        global $count;
        $count_new = Cats::count();
        $this->assertEquals($count_new, $count + 1);
    }


    public function testRetreiveCat(): void
    {
        global $cat_id;

        $cat = null;
        $this->assertIsInt($cat_id);
        $cat = Cats::get_by_id($cat_id);
        $this->assertEquals(
            $cat->name,
            "TestCat"
        );
    }

    public function testUpdateCat(): void
    {
        global $cat_id;

        $cat = Cats::get_by_id($cat_id);
        $cat->name = "UpdatedTestCat";
        $cat->weight = 3.5;

        $cat->update();

        $catUpdated = Cats::get_by_id($cat_id);
        $this->assertEquals(
            $catUpdated->name,
            "UpdatedTestCat"
        );
        $this->assertEquals(
            $catUpdated->weight,
            3.5
        );
    }

    public function testGetAllCats(): void
    {
        global $count;

        $cats = Cats::get_all();
        $this->assertIsArray($cats);

        $cat = $cats[0];
        $className = get_class($cat);
        $this->assertEquals($className, "Cat");

        $this->assertEquals(count($cats), $count + 1);
    }


    public function testDeleteCatById(): void
    {
        global $cat_id;

        $cat = Cats::get_by_id($cat_id);
        $new_id = $cat->save();

        Cats::delete_by_id($new_id);
        $cat = Cats::get_by_id($new_id);
        $this->assertFalse($cat);
    }

    public function testDeleteCat(): void
    {
        global $cat_id;

        $cat = new Cat([]);
        $cat->id = $cat_id;
        $cat->delete();
        $this->assertNull($cat->name);

        $cat = Cats::get_by_id($cat_id);
        $this->assertFalse($cat);
    }



    public function testCountCats_3(): void
    {
        global $count;
        $count_new = Cats::count();
        $this->assertEquals($count_new, $count);
    }
}