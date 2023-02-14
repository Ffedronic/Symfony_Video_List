<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categories = [["Electronics", 1], ["Toys", 2], ["Books", 3], ["Movies", 4]];
        $electronicSubcategories = [["Cameras", 5], ["Computers", 6], ["Cell Phones", 7]];
        $computersSubcategories = [["Laptops", 8], ["Desktops", 9]];
        $laptopSubcategories = [["Apple", 10], ["Asus", 11], ["Dell", 12], ["Lenovo", 13], ["HP", 14]];
        $booksSubcategories = [["Children\'s Books", 15], ["Kindle", 16]];
        $moviesSubcategories = [["Family", 17], ["Romance", 18]];
        $romanceSubcategories = [["Romantic Comedy", 19], ["Romantic Drama", 20]];

        $this->createCategories($manager, $categories);
        $this->createSubcategories($manager, $electronicSubcategories, 1);
        $this->createSubcategories($manager, $computersSubcategories, 6);
        $this->createSubcategories($manager, $laptopSubcategories, 8);
        $this->createSubcategories($manager, $booksSubcategories, 3);
        $this->createSubcategories($manager, $moviesSubcategories, 4);
        $this->createSubcategories($manager, $romanceSubcategories, 18);
    }

    private function createSubcategories($manager, $subcategories, $parent_id)
    {
        foreach ($subcategories as [$value]) {
            $parent = $manager->getRepository(Category::class)->find($parent_id);
            $category = new Category();
            $category->setName($value);
            $category->setParent($parent);
            $manager->persist($category);
            $manager->flush();
        }
    }

    private function createCategories($manager, $categories)
    {
        foreach ($categories as [$value]) {
            $category = new Category();
            $category->setName($value);
            $manager->persist($category);
            $manager->flush();
        }
    }
}
