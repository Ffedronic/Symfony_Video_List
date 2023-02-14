<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categories = array("Electronics", "Toys", "Books", "Movies");

        foreach ($categories as $value) {
            $category = new Category();
            $category->setName($value);
            $manager->persist($category);
            $manager->flush();
        }

    }
}
