<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SecurityControllerTest extends WebTestCase
{
    /**
     * Test de l'affichage de la page de connexion.
     */
    public function testDisplayLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('h1', 'Please sign in');
        $this->assertSelectorNotExists('.alert.alert-danger');
    }

    /**
     * Test de la connexion avec des mauvaises informations.
     */
    public function testLoginWithBadCredentials()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Sign in')->form(
            [
                'email' => 'johnDoe@doe.fr',
                'password' => 'fakePassword',
            ]);
        $client->submit($form);
        $this->assertResponseRedirects('/login');
        $client->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger');
    }

    /**
     * Test de la connexion avec des bonnes informations.
     */
    public function testSucessfulLogin()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Sign in')->form(
            [
                'email' => 'admin@todolist.com',
                'password' => 'password',
            ]);
        $client->submit($form);
        $this->assertResponseRedirects('/');
        $this->assertResponseStatusCodeSame(302);
        $client->followRedirect();

    }

    /**
     * Test de la déconnexion.
     */
    public function testLogout()
    {
        $client = static::createClient();
        $userRepository = $client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $testUser = $userRepository->findOneBy(['email' => 'admin@todolist.com']);
        $client->loginUser($testUser);

        $client->request('GET', '/logout');

        $client->followRedirect();

        $this->assertRouteSame('homepage');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

    }

}
