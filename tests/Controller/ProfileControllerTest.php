<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProfileControllerTest extends WebTestCase
{
    public function testProfilePageRequiresLogin()
    {
        $client = static::createClient();
        $client->request('GET', '/candidate/profile');

        // Should redirect to login if not authenticated
        $this->assertResponseRedirects('/login');
    }

    public function testLoginPageLoads()
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[type="email"]');
        $this->assertSelectorExists('input[type="password"]');
    }
}

