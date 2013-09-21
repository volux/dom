<?php
/**
 * volux\Dom
 *
 * @link http://github.com/volux/dom
 */
namespace volux\Dom;

use volux\Dom;
/**
 * Class Table
 * @package volux\Dom
 * @author  Andrey Skulov <andrey.skulov@gmail.com>
 */
class File extends Document
{
    /**
     * @var  string
     */
    protected $path = '';

    /**
     * @var Cdata
     */
    protected $source;
    /**
     * @var  null|callable|\Closure
     */
    public $loader;
    /**
     * @todo think about $this->loader
     */
    /**
     * @var  null|callable|\Closure
     */
    public $saver;
    /**
     * @todo think about $this->saver
     */

    /**
     * @param string $version
     * @param string $encoding
     */
    public function __construct($version = self::VERSION, $encoding = self::ENCODING)
    {
        $data = '';
        if (is_file($version)) {
            $this->path = $version;
        } else {
            if ($version !== self::VERSION) {
                $data = $version;
                $version = self::VERSION;
            }
        }
        parent::__construct($version, $encoding);
        $this->source = $this->root('file')->attr('path', $this->path)->append($this->createCData($data));
    }

    /**
     * @param string $path
     *
     * @return File
     */
    public function point($path)
    {
        $this->path = $path;
        $this->documentElement->attr('path', $path);
        return $this;
    }

    /**
     * @return string|Cdata
     */
    protected function existContents()
    {
        if ($this->source->isClean()) {
            return $this->saveXML();
        }
        return $this->source->text();
    }

    /**
     * @param $data
     *
     * @return File
     */
    public function setContents($data)
    {
        $this->source->text($data);
        return $this;
    }

    /**
     * @return string
     */
    public function getContents()
    {
        if (is_file($this->path)) {
            $this->setContents('');
            if (is_callable($this->loader)) {
                $this->setContents(call_user_func($this->loader, $this->path));
            } else {
                $this->setContents(file_get_contents($this->path, FILE_USE_INCLUDE_PATH));
            }
        }
        return $this->existContents();
    }

    /**
     * @return bool|int|mixed
     */
    public function putContents()
    {
        $result = false;
        if (is_file($this->path)) {
            if (is_callable($this->saver)) {
                $result = call_user_func($this->saver, $this->path, $this->existContents());
            } else {
                $result = file_put_contents($this->path, $this->existContents());
            }
        }
        return $result;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->existContents();
    }
}