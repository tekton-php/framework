<?php namespace Tekton\Contracts;

interface ResourceManagerInterface
{
    public function setRootPath($path);

    public function getRootPath($path = null);

    public function getPath($name);

    public function setPath($name, $path = null);

    public function setRootUrl($url);

    public function getRootUrl($url = null);

    public function getUrl($name);

    public function setUrl($name, $url = null);
}
