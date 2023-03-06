<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FrontControllerVideoTest extends WebTestCase
{
    public function testSearchNoResultsFound(): void
    {
        $client = static::createClient();
        $client->followRedirects();

        $crawler = $client->request('GET', '/');
        $form = $crawler->selectButton('Search video')->form();
        $crawler = $client->submit($form,["query"=>"aaa"]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'No results');
    }

    
}
