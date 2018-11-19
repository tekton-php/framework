<?php namespace Tekton;

use Exception;
use Tekton\Contracts\ResourceManagerInterface;

class ResourceManager implements ResourceManagerInterface
{
    protected $rootPath = '';
    protected $rootUrl = '';
    protected $urls = [];
    protected $paths = [];

    public function __construct($rootPath = '', $rootUrl = '')
    {
        $this->setRootPath($rootPath);
        $this->setRootUrl($rootUrl);
    }

    public function setRootPath($path)
    {
        $this->rootPath = rtrim($path, DS);
    }

    public function getRootPath($path = null)
    {
        return (is_null($path)) ? $this->rootPath : $this->rootPath.DS.$path;
    }

    public function getPath($name)
    {
        if (isset($this->paths[$name])) {
            return $this->paths[$name];
        }
        else {
            throw new Exception("Path hasn't been defined: ".$name);
        }
    }

    public function setPath($name, $path = null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $val) {
                $this->paths[$key] = $val;
            }
        }
        else {
            $this->paths[$name] = $path;
        }
    }

    public function setRootUrl($url)
    {
        $this->rootUrl = rtrim($url, '/');
    }

    public function getRootUrl($url = null)
    {
        return (is_null($url)) ? $this->rootUrl : $this->rootUrl.'/'.$url;
    }

    public function getUrl($name)
    {
        if (isset($this->urls[$name])) {
            return $this->urls[$name];
        }
        else {
            throw new Exception("Url hasn't been defined: ".$name);
        }
    }

    public function setUrl($name, $url = null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $val) {
                $this->urls[$key] = $val;
            }
        }
        else {
            $this->urls[$name] = $url;
        }
    }
}
