<?php
/**
 * volux\Dom
 *
 * @link http://github.com/volux/dom
 */
namespace volux\Dom;

use volux\Dom;
/**
 * Class Set
 * @package volux\Dom
 * @author  Andrey Skulov <andrey.skulov@gmail.com>
 */
class Set extends \ArrayIterator implements \RecursiveIterator
{
    /**
     * @var Dom|Html|Form
     */
    protected $ownerDocument;
    /**
     * @param \DOMNodelist|\DOMNamedNodeMap|Set|array $nodeList
     * @param Dom|Html|Form $dom
     */
    public function __construct($nodeList, Dom &$dom)
    {
        $this->ownerDocument = $dom;
        $nodes = array();
        foreach ($nodeList as $node) {
            $nodes[] = $node;
        }
        parent::__construct($nodes);
    }

    /**
     * @return Dom|Html|Form
     */
    public function doc()
    {
        return $this->ownerDocument;
    }

    /**
     * @return \RecursiveIteratorIterator
     */
    public function getRecursiveIterator()
    {
        return new \RecursiveIteratorIterator($this, \RecursiveIteratorIterator::SELF_FIRST);
    }

    /**
     * @return bool
     */
    public function hasChildren()
    {
        return $this->current()->hasChildNodes();
    }

    /**
     * @return Set
     */
    public function getChildren()
    {
        return new self($this->current()->childNodes, $this->doc());
    }

    /**
     * @return Attr|Element|Tag|Field|Text|Cdata|Comment
     */
    public function end()
    {
        return $this->doc()->context();
    }

    /**
     * @param $callable
     *
     * @return $this|Set
     */
    public function each($callable)
    {
        $index = 0;
        foreach ($this as $node) {
            $callable($node, $index++);
        }
        return $this;
    }

    /**
     * <code> while ($node = $set->sequent()) { $node } </code>
     * @return Attr|Element|Tag|Field|Text|Cdata|Comment
     */
    public function sequent()
    {
        $this->next();
        if ($this->valid()) {
            return $this->current();
        }
        $this->rewind();
        return false;
    }

    /**
     * @param int $offset
     *
     * @return Attr|Element|Tag|Field|Text|Cdata|Comment
     */
    public function eq($offset)
    {
        if ($this->count() < $offset) {
            return $this->doc()->notEmpty(false, '=Set::eq()');
        }
        return $this->offsetGet($offset);
    }

    /**
     * @return Attr|Element|Tag|Field|Text|Cdata|Comment
     */
    public function first()
    {
        if ($this->count() == 0) {
            return $this->doc()->notEmpty(false, '=Set::first()');
        }
        return $this->offsetGet(0);
    }

    /**
     * @return Attr|Element|Tag|Field|Text|Cdata|Comment
     */
    public function last()
    {
        if ($this->count() == 0) {
            return $this->doc()->notEmpty(false, '=Set::last()');
        }
        $offset = $this->count()-1;
        return $this->offsetGet($offset);
    }

    /**
     * @return $this|Set
     */
    protected function removeAll()
    {
        for ($this->rewind(); $this->valid(); $this->offsetUnset($this->key())) {
            $this->offsetGet($this->key())->remove();
        }
        return $this;
    }

    /**
     * @param int|null $key
     *
     * @return $this|Set
     */
    public function remove($key = null)
    {
        if (is_null($key)) {
            return $this->removeAll();
        }
        if (false === $key) {
            return $this;
        }
        if ($this->offsetExists($key)) {
            $this->offsetGet($key)->remove();
            $this->offsetUnset($key);
            $this->rewind();
        }
        return $this;
    }

    /**
     * @param string|Element|Tag|Field $target
     *
     * @return $this|Set
     */
    public function appendTo(&$target)
    {
        if (is_string($target)) {
            $target = $this->doc()->createElement($target);
        }
        foreach ($this as $node) {
            $target->append($node);
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return ($this->count() === 0);
    }

    /**
     * @param string $xPath to child nodes like "date" or "@date"
     *
     * @return $this|Set
     */
    public function sortBy($xPath)
    {
        $comparator = function (Element $a, Element $b) use ($xPath) {
            $aValue = $a->find($xPath, 0)->text();
            $bValue = $b->find($xPath, 0)->text();
            if (!is_numeric($aValue)) {
                return strcasecmp($aValue, $bValue);
            }
            if ($aValue == $bValue) {
                return 0;
            }
            return ($aValue < $bValue) ? -1 : 1;
        };
        $this->uasort($comparator);
        return $this;
    }

    /**
     * @param string|callable $selector css selector or callable
     *
     * @return Set
     */
    public function filter($selector)
    {
        $nodeset = array();
        if (is_string($selector)) {
            foreach ($this as $node) {
                /** @var $node Attr|Element|Tag|Field|Text|Cdata|Comment */
                if ($node->is($selector)) {
                    $nodeset[] = $node;
                }
            }
        } else
            if (is_callable($selector)) {
                foreach ($this as $index => $node) {
                    /** @var $node Attr|Element|Tag|Field|Text|Cdata|Comment */
                    if ($selector($node, $index)) {
                        $nodeset[] = $node;
                    }
                }
            } else
                if ($selector instanceof \DOMNode) {
                    foreach ($this as $node) {
                        /** @var $node Attr|Element|Tag|Field|Text|Cdata|Comment */
                        if ($node->isSameNode($selector)) {
                            $nodeset[] = $node;
                        }
                    }
                }
        return new self($nodeset, $this->doc());
    }

    /**
     * also used like $this->map('attr', 'id')
     *
     * @param string $method
     * @param string $key
     * @param null $arg
     *
     * @return \ArrayIterator
     */
    public function map($method = 'text', $key = 'getNodePath', $arg = null)
    {
        $array = array();
        foreach ($this as $node) {
            /** @var $node Attr|Element|Tag|Field|Text|Cdata|Comment */
            $array[$node->$key()] = $node->$method($arg);
        }
        return new \ArrayIterator($array);
    }

    /**
     * @param string $xslFile
     * @param array $xsltParameters
     *
     * @return $this|Set
     */
    public function xslt($xslFile, $xsltParameters = array())
    {
        foreach ($this as $node) {
            $this->doc()->xslt($xslFile, $xsltParameters, $node);
        }
        return $this;
    }

    /**
     * Invoke $method for each node in Set if it present
     *
     * @param $method
     * @param array $args
     *
     * @return $this|Set
     */
    public function __call($method, array $args)
    {
        $newSet = array();
        foreach ($this as $element) {
            if (method_exists($element, $method)) {
                /** @var $element Attr|Element|Tag|Field|Text|Cdata|Comment */
                $result = call_user_func_array(array($element, $method), $args);
                if ($result instanceof Set) {
                    foreach ($result as $item) {
                        $newSet[] = $item;
                    }
                } else {
                    $newSet[] = $result;
                }
            }
        }
        if ($newSet) {
            return new self($newSet, $this->doc());
        }
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $string = '';
        foreach ($this as $node) {
            /** @var $node Attr|Element|Tag|Field|Text|Cdata|Comment */
            $string .= (string)$node;
        }
        return $string;
    }
}
