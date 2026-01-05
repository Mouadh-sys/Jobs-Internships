<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    public function testRegistrationPageLoads()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[id="registration_form_email"]');
        $this->assertSelectorExists('input[id="registration_form_fullName"]');
    }

    public function testRegistrationFormHasAllFields()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
        // Check form exists
        $form = $crawler->selectButton('Register');
        $this->assertNotNull($form);

        // Check key form fields are present
        $this->assertSelectorExists('input[type="email"]');
        $this->assertSelectorExists('input[type="password"]');
        $this->assertSelectorExists('input[type="checkbox"]');
    }
}

