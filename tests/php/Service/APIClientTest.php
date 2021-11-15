<?php

namespace SilverStripe\CKANRegistry\Tests\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use SilverStripe\CKANRegistry\Model\Resource;
use SilverStripe\CKANRegistry\Service\APIClient;
use SilverStripe\Dev\SapphireTest;

class APIClientTest extends SapphireTest
{
    /**
     * @var Client
     */
    protected $guzzleClient;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var StreamInterface
     */
    protected $mockBody;

    /**
     * @var Resource
     */
    protected $resource;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guzzleClient = $this->createMock(Client::class);
        $this->response = $this->createMock(Response::class);
        $this->mockBody = $this->createMock(StreamInterface::class);
        $this->resource = new Resource();
    }

    public function testExceptionThrownOnInvalidHttpStatusCode()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('CKAN API is not available. Error code 123');
        $this->guzzleClient->expects($this->once())->method('send')->willReturn($this->response);
        $this->response->expects($this->once())->method('getStatusCode')->willReturn(123);

        $client = new APIClient();
        $client->setGuzzleClient($this->guzzleClient);
        $client->getData($this->resource);
    }

    public function testExceptionThrownOnNonJsonResponse()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('CKAN API returns an invalid response: Content-Type is not JSON');
        $this->guzzleClient->expects($this->once())->method('send')->willReturn($this->response);
        $this->response->expects($this->once())->method('getStatusCode')->willReturn(200);
        $this->response->expects($this->once())->method('getHeader')->with('Content-Type')->willReturn(['junk']);

        $client = new APIClient();
        $client->setGuzzleClient($this->guzzleClient);
        $client->getData($this->resource);
    }

    public function testExceptionThrownOnUnsuccessfulResponse()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('CKAN API returns an invalid response: Responded as invalid');
        $this->guzzleClient->expects($this->once())->method('send')->willReturn($this->response);
        $this->response->expects($this->once())->method('getStatusCode')->willReturn(200);
        $this->response->expects($this->once())->method('getHeader')->willReturn(['application/json']);
        $this->response->expects($this->once())->method('getBody')->willReturn($this->mockBody);
        $this->mockBody->expects($this->once())->method('getContents')->willReturn('{
            "success": false
        }');

        $client = new APIClient();
        $client->setGuzzleClient($this->guzzleClient);
        $client->getData($this->resource);
    }

    public function testReturnsResponseData()
    {
        $this->guzzleClient->expects($this->once())->method('send')->willReturn($this->response);
        $this->response->expects($this->once())->method('getStatusCode')->willReturn(200);
        $this->response->expects($this->once())->method('getHeader')->willReturn(['application/json']);
        $this->response->expects($this->once())->method('getBody')->willReturn($this->mockBody);
        $this->mockBody->expects($this->once())->method('getContents')->willReturn('{
            "success": true,
            "data": "test"
        }');

        $client = new APIClient();
        $client->setGuzzleClient($this->guzzleClient);
        $result = $client->getData($this->resource);

        $this->assertSame('test', $result['data'], 'Raw response body should be returned');
    }
}
