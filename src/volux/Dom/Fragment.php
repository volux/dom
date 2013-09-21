<?php
/**
 * volux\Dom
 *
 * @link http://github.com/volux/dom
 */
namespace volux\Dom;

use volux\Dom;
/**
 * Class Fragment
 * @todo think about it dummy :)
 * @package volux\Dom
 * @author  Andrey Skulov <andrey.skulov@gmail.com>
 */
class Fragment extends \DOMDocumentFragment
{

    /** @var  Set */
    protected $subset;

    /**
     * for future
     *
     * @param Set $set
     *
     * @return Set
     */
    public function &subset(Set &$set = null)
    {
        if (is_null($set)) {
            return $this->subset;
        }
        $this->subset = $set;
        return $this->subset;
    }

    /**
     * @return Document|Html|Table|Form
     */
    public function doc()
    {
        return $this->ownerDocument;
    }

    /**
     * @return $this
     */
    public function append()
    {
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->ownerDocument->saveXML($this);
    }
}