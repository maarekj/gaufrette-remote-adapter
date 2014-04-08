<?php
/**
 * Created by PhpStorm.
 * User: maarek
 * Date: 07/04/2014
 * Time: 22:06
 */

namespace Jma\GaufretteRemoteAdapter\Tests\Adapter;

use GuzzleHttp\Adapter\MockAdapter;
use GuzzleHttp\Adapter\TransactionInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream;
use Jma\GaufretteRemoteAdapter\Adapter\Adapter;

class AdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Adapter
     */
    protected $adapter;

    /**
     * @var
     */
    protected $mockAdapter;

    public function createAdapter(callable $callback)
    {
        $mockAdapter = new MockAdapter($callback);
        $client = new Client(['adapter' => $mockAdapter]);
        return new Adapter($client);
    }

    public function testRead()
    {
        $adapter = $this->createAdapter(function (TransactionInterface $trans) {
            $content = [
                'key' => 'file1', 'content' => base64_encode("content")
            ];
            return new Response(200, array(), Stream\create(json_encode($content)));
        });

        $this->assertEquals('content', $adapter->read('file1'));
    }

    public function testReadOnKeyNotExists()
    {
        $adapter = $this->createAdapter(function (TransactionInterface $trans) {
            return new Response(404);
        });

        $this->assertFalse($adapter->read('file1'));
    }

    public function testWrite()
    {
        $adapter = $this->createAdapter(function (TransactionInterface $trans) {
            $content = ['key' => 'file1', 'write' => 7];
            return new Response(200, array(), Stream\create(json_encode($content)));
        });

        $this->assertEquals(7, $adapter->write('file1', 'content'));
    }

    public function testWriteError()
    {
        $adapter = $this->createAdapter(function (TransactionInterface $trans) {
            return new Response(400);
        });

        $this->assertFalse($adapter->write('file1', 'content'));
    }

    public function testExists()
    {
        $adapter = $this->createAdapter(function (TransactionInterface $trans) {
            return new Response(200);
        });

        $this->assertTrue($adapter->exists('file1'));
    }

    public function testExistsOnKeyNotExists()
    {
        $adapter = $this->createAdapter(function (TransactionInterface $trans) {
            return new Response(404);
        });

        $this->assertFalse($adapter->exists('file1'));
    }

    public function testKeys()
    {
        $content = ['file1', 'file2', 'file3', 'file4', 'file5'];
        $adapter = $this->createAdapter(function (TransactionInterface $trans) use ($content) {
            return new Response(200, array(), Stream\create(json_encode($content)));
        });

        $this->assertEquals($content, $adapter->keys());
    }

    public function testKeysError()
    {
        $adapter = $this->createAdapter(function (TransactionInterface $trans) {
            return new Response(400);
        });

        $this->assertEquals(array(), $adapter->keys());
    }

    public function testMtime()
    {
        $content = ["key" => 'file1', "mtime" => 1234567];
        $adapter = $this->createAdapter(function (TransactionInterface $trans) use ($content) {
            return new Response(200, array(), Stream\create(json_encode($content)));
        });

        $this->assertEquals(1234567, $adapter->mtime("file1"));
    }

    public function testMtimeOnKeyNotExists()
    {
        $adapter = $this->createAdapter(function (TransactionInterface $trans) {
            return new Response(404);
        });

        $this->assertFalse($adapter->mtime("file1"));
    }

    public function testIsDirectory()
    {
        $content = ["key" => 'file1', "isDirectory" => true];
        $adapter = $this->createAdapter(function (TransactionInterface $trans) use ($content) {
            return new Response(200, array(), Stream\create(json_encode($content)));
        });

        $this->assertTrue($adapter->isDirectory("file1"));
    }

    public function testIsNotDirectory()
    {
        $content = ["key" => 'file1', "isDirectory" => false];
        $adapter = $this->createAdapter(function (TransactionInterface $trans) use ($content) {
            return new Response(200, array(), Stream\create(json_encode($content)));
        });

        $this->assertFalse($adapter->isDirectory("file1"));
    }

    public function testIsDirectoryOnKeyNotExists()
    {
        $adapter = $this->createAdapter(function (TransactionInterface $trans) {
            return new Response(404);
        });

        $this->assertFalse($adapter->isDirectory("file1"));
    }

    public function testDelete()
    {
        $adapter = $this->createAdapter(function (TransactionInterface $trans) {
            return new Response(200);
        });
        $this->assertTrue($adapter->delete("file1"));
    }

    public function testDeleteOnKeyNotExists()
    {
        $adapter = $this->createAdapter(function (TransactionInterface $trans) {
            return new Response(404);
        });
        $this->assertFalse($adapter->delete("file1"));
    }

    public function testRename()
    {
        $adapter = $this->createAdapter(function (TransactionInterface $trans) {
            return new Response(200);
        });
        $this->assertTrue($adapter->rename("file1", "file2"));
    }

    public function testRenameOnKeyNotExists()
    {
        $adapter = $this->createAdapter(function (TransactionInterface $trans) {
            return new Response(404);
        });
        $this->assertFalse($adapter->rename("file1", "file2"));
    }
}