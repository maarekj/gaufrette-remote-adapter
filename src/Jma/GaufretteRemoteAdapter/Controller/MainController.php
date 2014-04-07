<?php

namespace Jma\GaufretteRemoteAdapter\Controller;

use Gaufrette\Adapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class MainController
{
    protected $adapter;

    function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }


    public function keysAction()
    {
        $keys = array_values(array_unique($this->adapter->keys()));

        return new JsonResponse($keys);
    }

    public function readAction($key)
    {
        return new JsonResponse(array(
            'key' => $key,
            'content' => base64_encode($this->adapter->read($key)),
            'mtime' => $this->adapter->mtime($key),
            'isDirectory' => $this->adapter->isDirectory($key),
        ));
    }

    public function metaAction($key)
    {
        return new JsonResponse(array(
            'key' => $key,
            'mtime' => $this->adapter->mtime($key),
            'isDirectory' => $this->adapter->isDirectory($key),
        ));
    }

    public function existsAction($key)
    {
        $exists = $this->adapter->exists($key);
        if (true === $exists) {
            return new JsonResponse(array('key' => $key));
        } else {
            throw new NotFoundHttpException("Le fichier '$key' n'existe pas");
        }
    }

    public function writeAction($key, $content)
    {
        $write = $this->adapter->write($key, $content);
        if ($write !== false) {
            return new JsonResponse(array('key' => $key, 'write' => $write));
        } else {
            throw new BadRequestHttpException();
        }
    }

    public function renameAction($source, $target)
    {
        $rename = $this->adapter->rename($source, $target);
        if ($rename === true) {
            return new JsonResponse(array('sourceKey' => $source, 'targetKey' => $target));
        } else {
            throw new BadRequestHttpException();
        }
    }

    public function deleteAction($key)
    {
        $delete = $this->adapter->delete($key);
        if ($delete === true) {
            return new JsonResponse(array('key' => $key));
        } else {
            throw new BadRequestHttpException();
        }
    }

    /**
     * @param $key
     * @param $force
     * @return StreamedResponse
     */
    public function downloadAction($key, $force)
    {
        $callback = function () use ($key) {
            echo $this->adapter->read($key);
        };

        $header = array();

        if (false !== $force) {
            $header['Content-Disposition'] = sprintf('attachment; filename=%s;', $key);
        }

        return new StreamedResponse($callback, 200, $header);
    }
}