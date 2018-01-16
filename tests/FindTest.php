<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;

class QueryDataTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testFindAll()
    {
		$this->json('GET', '/find', [])
             ->seeJson([
                'name' => "Concorde Hotel",
             ]);
			 
        //$this->get('/');

        /*$this->assertEquals(
            $this->app->version(), $this->response->getContent()
        );*/
    }
	
	public function testCallApi()
    {
 
        // Creating a Mock Response 
        $mockResponse = json_encode(["status" => "successful"]);
        
        // Creating a Mock Handler
        $mock = new MockHandler([
            new Response(200, [], $mockResponse)
        ]);
 
        // Creating a Hanlder Stack
        $handler = HandlerStack::create($mock); 
        
        // Creating Gizzle Client with Mock Handler Stack
        $client = new Client(['handler' => $handler]);
 
        // Binding the object instance to Laravel IoC 
        $this->app->instance(\GuzzleHttp\Client::class, $client);
        
        $this->get('/find');
        
        $this->seeJsonEquals(['status' => 'successful']);
    }
}
