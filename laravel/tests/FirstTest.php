<?php

use Goutte\Client;

class FirstTest extends TestCase
{
    private $token;

    public function __construct()
    {
        $token = null;
    }

    public function test_redirect_main()
    {
        $response = $this->call('GET', '/');
        $this->assertRedirectedTo('/adjudicator');
    }

    public function test_redirect()
    {
        $response = $this->call('GET', '/adjudicator/round');
        $this->assertRedirectedTo('/adjudicator/login');
        $response = $this->call('GET', '/admin/round');
        $this->assertRedirectedTo('/admin/login');
    }

    public function test_admin_login_view()
    {
        $response = $this->call('GET', '/admin/login');
        $this->assertResponseOk();
        $this->assertContains('username', $response->getContent());
        $this->assertContains('password', $response->getContent());
    }

    public function test_judge_login_view()
    {
        $response = $this->call('GET', '/adjudicator/login');
        // print($response->getContent());
        $this->assertViewHas('judges');
        $judges = $response->original['judges'];
        // $this->assertCount(6, $judges);
    }

    public function test_admin_login()
    {
        $client = new Client;
        $crawler = $client->request('GET', '/ptt/admin/login');
        // var_dump($client->getResponse()->getContent());
        // var_dump($crawler);
        $form = $crawler->selectButton('Login')->form();
        // var_dump($form);
        $form['username'] = 'admin';
        $form['password'] = 'admin';
        $crawler = $client->submit($form);
        // var_dump($client->getResponse());

        // $this->assertContains('dziowie', $client->getResponse()->getContent());

        // $crawler->filter('')->each(function ($node) {
        //	echo $node->text();
        //	});

        // foreach($crawler as $domElement) {
        //    print $domElement->nodeName;
        // }

        $this->assertGreaterThan(0, $crawler->filter('html:contains("Sędziowie")')->count());

        // $this->assertTrue($client->getResponse()->getHeaders()->contains('charset', 'utf-8'));
        var_dump($client->getResponse()->headers);

        // $client = new Client(['base_url' => 'http://localhost/ptt']);
        // $client = new Client();
        // $crawler = $client->request('GET', '/admin/login');

    }

    public function test_new_competition()
    {
        if (! token) {
            testAdminLogin();
        }

        $client = new Client;
        $crawler = $client->request('GET', '/ptt/admin');
        $this->assertContains('Zmiana turnieju', $client->getResponse()->getContent());

    }
}
