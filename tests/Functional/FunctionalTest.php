<?php
/**
 * Created by PhpStorm.
 * User: maarek
 * Date: 09/04/2014
 * Time: 08:56
 */

namespace Jma\GaufretteRemoteAdapter\Tests\Functional;


use Gaufrette\Filesystem;
use GuzzleHttp\Adapter\MockAdapter;
use GuzzleHttp\Adapter\TransactionInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;
use Jma\GaufretteRemoteAdapter\Adapter\Adapter;
use Silex\Application;
use Silex\WebTestCase;
use GuzzleHttp\Stream;

/**
 * Class FunctionalTest
 * @package Jma\GaufretteRemoteAdapter\Tests\Functional
 *

 */
class FunctionalTest extends WebTestCase
{
    /**
     * @var \Symfony\Component\HttpKernel\Client
     */
    protected $silex;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    public function setUp()
    {
        parent::setUp();

        $this->silex = $this->createClient([
            'PHP_AUTH_USER' => 'test',
            'PHP_AUTH_PW' => 'test'
        ]);

        $mockAdapter = new MockAdapter([$this, 'onGuzzleRequest']);

        $client = new Client([
            'adapter' => $mockAdapter,
            'auth' => ['test', 'test']
        ]);

        $adapter = new Adapter($client);
        $this->filesystem = new Filesystem($adapter);
    }

    public function onGuzzleRequest(TransactionInterface $trans)
    {
        $request = $trans->getRequest();
        $method = $request->getMethod();
        $url = $request->getUrl();
        $body = $request->getBody() !== null ? $request->getBody()->__toString() : null;
        $params = array();
        parse_str($body, $params);

        $this->silex->request($method, $url, $params, [], [], $body);

        $sres = $this->silex->getResponse();

        return new Response(
            $sres->getStatusCode(),
            iterator_to_array($sres->headers->getIterator()),
            Stream\create($sres->getContent())
        );
    }

    public function createApplication()
    {
        $app = new Application();

        require __DIR__ . '/config.php';
        require __DIR__ . '/../../src/app.php';

        return $app;
    }

    public function testApiWithNotAuth()
    {
        $client = $this->createClient();
        $client->request('GET', '/keys');
        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }

    public function testApiWithAuthNotValid()
    {
        $client = $this->createClient([
            'PHP_AUTH_USER' => 'notvalid',
            'PHP_AUTH_PW' => 'notvalid'
        ]);
        $client->request('GET', '/keys');
        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }

    public function testKeys()
    {
        $this->assertEquals(['file1', 'file2', 'file3'], $this->filesystem->keys());
    }

    public function testExists()
    {
        $this->assertTrue($this->filesystem->has('file1'));
    }

    public function testExistsOnKeyNotExists()
    {
        $this->assertFalse($this->filesystem->has('notexists'));
    }


    public function testRead()
    {
        $this->assertEquals('file1', $this->filesystem->read('file1'));
    }

    /**
     * @expectedException \Gaufrette\Exception\FileNotFound
     */
    public function testReadOnKeyNotExists()
    {
        $this->assertEquals('file1', $this->filesystem->read('notexists'));
    }

    public function testWrite()
    {
        $this->assertEquals(10, $this->filesystem->write('newfile', '0123456890'));
        $this->assertEquals('0123456890', $this->filesystem->read('newfile'));
    }

    /**
     * @expectedException \Gaufrette\Exception\FileAlreadyExists
     */
    public function testWriteOnAlreadyExists()
    {
        $this->filesystem->write('file1', '0123456890');
    }

    public function testWriteOnAlreadyExistsForce()
    {
        $this->assertEquals(10, $this->filesystem->write('file1', '0123456890', 1));
        $this->assertEquals('0123456890', $this->filesystem->read('file1'));
    }

    public function testRename()
    {
        $this->assertEquals('file1', $this->filesystem->read('file1'));
        $this->assertTrue($this->filesystem->rename('file1', 'newfile'));
        $this->assertEquals('file1', $this->filesystem->read('newfile'));
        $this->assertFalse($this->filesystem->has('file1'));
    }

    /**
     * @expectedException \Gaufrette\Exception\FileNotFound
     */
    public function testRenameOnSourceKeyNotExists()
    {
        $this->filesystem->rename('notexists', 'notexists2');
    }

    /**
     * @expectedException \Gaufrette\Exception\UnexpectedFile
     */
    public function testRenameOnTargetKeyExists()
    {
        $this->filesystem->rename('file1', 'file2');
    }

    public function testDownloadAction()
    {
        $this->silex->request('GET', 'download/file1');
        $this->assertEquals('file1', $this->silex->getInternalResponse()->getContent());
    }

    public function testDelete()
    {
        $this->assertTrue($this->filesystem->has('file1'));
        $this->assertTrue($this->filesystem->delete('file1'));
        $this->assertFalse($this->filesystem->has('file1'));
    }

    /**
     * @expectedException \Gaufrette\Exception\FileNotFound
     */
    public function testDeleteOnKeyNotExists()
    {
        $this->assertFalse($this->filesystem->has('notexists'));
        $this->filesystem->delete('notexists');
    }
}
 