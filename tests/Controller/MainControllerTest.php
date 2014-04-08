<?php
/**
 * Created by PhpStorm.
 * User: maarek
 * Date: 08/04/2014
 * Time: 15:27
 */

namespace Jma\GaufretteRemoteAdapter\Tests\Controller;

use Gaufrette\Adapter\InMemory;
use Gaufrette\Adapter;
use Jma\GaufretteRemoteAdapter\Controller\MainController;

class MainControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Adapter
     */
    protected $adapter;

    /**
     * @var MainController
     */
    protected $controller;

    protected function setUp()
    {
        $this->adapter = new InMemory(array(
            'file1' => 'file1',
            'file2' => 'file2',
            'file3' => 'file3',
        ));

        $this->controller = new MainController($this->adapter);
    }


    public function testReadAction()
    {
        $results = json_decode($this->controller->readAction('file1')->getContent(), true);

        $this->assertEquals('file1', $results['key']);
        $this->assertEquals('file1', base64_decode($results['content']));
        $this->assertFalse($results['isDirectory']);
        $this->assertArrayHasKey('mtime', $results);
    }

    public function testMetaAction()
    {
        $results = json_decode($this->controller->metaAction('file1')->getContent(), true);

        $this->assertEquals('file1', $results['key']);
        $this->assertFalse($results['isDirectory']);
        $this->assertArrayHasKey('mtime', $results);
    }

    public function testWriteAction()
    {
        $results = json_decode($this->controller->writeAction('fileWrite', 'fileWrite')->getContent(), true);

        $this->assertEquals('fileWrite', $results['key']);
        $this->assertEquals(9, $results['write']);
        $this->assertEquals('fileWrite', $this->adapter->read('fileWrite'));
    }

    public function testExistsAction()
    {
        $results = json_decode($this->controller->existsAction('file1')->getContent(), true);

        $this->assertEquals('file1', $results['key']);
        $this->assertTrue($this->adapter->exists('file1'));
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testNotExistsKey()
    {
        $this->assertFalse($this->adapter->exists('notexists'));
        json_decode($this->controller->existsAction('notexists')->getContent(), true);
    }

    public function testRenameAction()
    {
        $initialContent = $this->adapter->read('file1');

        $results = json_decode($this->controller->renameAction('file1', 'fileTarget')->getContent(), true);

        $this->assertEquals('file1', $results['sourceKey']);
        $this->assertEquals('fileTarget', $results['targetKey']);

        $this->assertFalse($this->adapter->exists('file1'));
        $this->assertTrue($this->adapter->exists('fileTarget'));
        $this->assertEquals($initialContent, $this->adapter->read('fileTarget'));
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function testRenameOnSourceKeyNotExists()
    {
        $this->assertFalse($this->adapter->exists('file1aze'));
        json_decode($this->controller->renameAction('file1aze', 'fileTarget')->getContent(), true);
    }

    public function testRenameOnTargetKeyAlreadyExists()
    {
        $this->assertTrue($this->adapter->exists('file2'));
        json_decode($this->controller->renameAction('file1', 'file2')->getContent(), true);
    }

    public function testDeleteAction()
    {
        $results = json_decode($this->controller->deleteAction('file1')->getContent(), true);

        $this->assertEquals('file1', $results['key']);
        $this->assertFalse($this->adapter->exists('file1'));
    }

    public function testKeysAction()
    {
        $expected = array_values(array_unique($this->adapter->keys()));
        $results = json_decode($this->controller->keysAction()->getContent(), true);

        $this->assertEquals($expected, $results);
    }

    public function testDownloadAction()
    {
        $response = $this->controller->downloadAction('file1', false);

        ob_start();
        $response->sendContent();
        $content = ob_get_contents();
        ob_end_clean();

        $expected = $this->adapter->read('file1');
        $this->assertEquals($expected, $content);

        $this->assertFalse($response->headers->has('Content-Disposition'));
    }

    public function testForceDownload()
    {
        $response = $this->controller->downloadAction('file1', true);

        ob_start();
        $response->sendContent();
        $content = ob_get_contents();
        ob_end_clean();

        $expected = $this->adapter->read('file1');
        $this->assertEquals($expected, $content);

        $this->assertTrue($response->headers->has('Content-Disposition'));
        $this->assertEquals('attachment; filename=file1;', $response->headers->get('Content-Disposition'));
    }
}
 