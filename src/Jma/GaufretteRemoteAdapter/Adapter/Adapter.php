<?php
/**
 * Created by PhpStorm.
 * User: maarek
 * Date: 07/04/2014
 * Time: 22:06
 */

namespace Jma\GaufretteRemoteAdapter\Adapter;


use GuzzleHttp\Client;

class Adapter implements \Gaufrette\Adapter
{
    protected $client;
    protected $baseUrl;

    public function __construct($baseUrl, $username, $password)
    {
        $this->baseUrl = $baseUrl;

        $this->client = new Client(array(
            'defaults' => array(
                'auth' => array($username, $password)
            )
        ));
    }

    /**
     * Reads the content of the file
     *
     * @param string $key
     *
     * @return string|boolean if cannot read content
     */
    public function read($key)
    {
        try {
            $res = $this->client->get($this->baseUrl . '/read/' . $key)->json();
            return base64_decode($res['content']);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Writes the given content into the file
     *
     * @param string $key
     * @param string $content
     *
     * @return integer|boolean The number of bytes that were written into the file
     */
    public function write($key, $content)
    {
        try {
            $res = $this->client->post($this->baseUrl . '/write', array(
                'body' => array(
                    'key' => $key,
                    'content' => $content
                )
            ))->json();
            return $res['write'];
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            return false;
        }
    }

    /**
     * Indicates whether the file exists
     *
     * @param string $key
     *
     * @return boolean
     */
    public function exists($key)
    {
        try {
            $this->client->get($this->baseUrl . '/exists/' . $key)->json();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Returns an array of all keys (files and directories)
     *
     * @return array
     */
    public function keys()
    {
        try {
            return $this->client->get($this->baseUrl . '/keys')->json();
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            return array();
        }
    }

    /**
     * Returns the last modified time
     *
     * @param string $key
     *
     * @return integer|boolean An UNIX like timestamp or false
     */
    public function mtime($key)
    {
        try {
            $res = $this->client->get($this->baseUrl . '/meta/' . $key)->json();
            return $res['mtime'];
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Deletes the file
     *
     * @param string $key
     *
     * @return boolean
     */
    public function delete($key)
    {
        try {
            $this->client->post($this->baseUrl . '/delete', array(
                'body' => array(
                    'key' => $key
                )
            ))->json();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Renames a file
     *
     * @param string $sourceKey
     * @param string $targetKey
     *
     * @return boolean
     */
    public function rename($sourceKey, $targetKey)
    {
        try {
            $this->client->post($this->baseUrl . '/rename', array(
                'body' => array(
                    'sourceKey' => $sourceKey,
                    'targetKey' => $targetKey
                )
            ))->json();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if key is directory
     *
     * @param string $key
     *
     * @return boolean
     */
    public function isDirectory($key)
    {
        try {
            $res = $this->client->get($this->baseUrl . '/meta/' . $key)->json();
            return $res['isDirectory'];
        } catch (\Exception $e) {
            return false;
        }
    }
}