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
     * @var Document|Html|Table|Form
     */
    protected $ownerDocument;
    /**
     * @param \DOMNodelist|\DOMNamedNodeMap|Set|array $list
     * @param Document|Html|Table|Form $dom
     */
    public function __construct($list, Document $dom)
    {
        parent::__construct();
        $this->ownerDocument = $dom;
        foreach ($list as $entry) {
            $this->append($entry);
        }
    }

    /**
     * @param mixed $value
     *
     * @return $this
     */
    public function append($value) {
        if (is_scalar($value)) {
            $value = $this->ownerDocument->createText($value);
        }
        parent::append($value);
        return $this;
    }

    /**
     * @return Document|Form|Html|Table
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
        return new self($this->current()->childNodes, $this->ownerDocument);
    }

    /**
     * @return Attr|Element|Tag|Field|Text|Cdata|Comment
     */
    public function end()
    {
        return $this->ownerDocument->context();
    }

    /**
     * @param \Closure $closure
     *
     * @return Set
     */
    public function each(\Closure $closure)
    {
        foreach ($this as $index=>$node) {
            if (false === $closure($node, $index)) {
                return $this;
            }
        }
        return $this;
    }

    /**
     * <code> while ($node = $set->sequence()) { $node } </code>
     * @return Attr|Element|Tag|Field|Text|Cdata|Comment
     */
    public function sequence()
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
        $count = $this->count();
        if ($offset < 0) {
            $offset = $count - $offset;
        }
        if ($count < $offset) {
            $offset = $count - 1;
        }
        if ($this->offsetExists($offset)) {
            return $this->offsetGet($offset);
        }
        return $this->ownerDocument->notEmpty(false, 'Set::eq('.$offset.')');
    }

    /**
     * @param $offset
     *
     * @return Attr|Cdata|Comment|Element|Field|Tag|Text
     */
    public function item($offset)
    {
        return $this->eq($offset);
    }

    /**
     * @return Attr|Element|Tag|Field|Text|Cdata|Comment
     */
    public function first()
    {
        if ($this->count() == 0) {
            return $this->ownerDocument->notEmpty(false, 'Set::first()');
        }
        return $this->offsetGet(0);
    }

    /**
     * @return Attr|Element|Tag|Field|Text|Cdata|Comment
     */
    public function last()
    {
        if ($this->count() == 0) {
            return $this->ownerDocument->notEmpty(false, 'Set::last()');
        }
        $offset = $this->count()-1;
        return $this->offsetGet($offset);
    }

    /**
     * @return $this|Set
     */
    public function andSelf()
    {
        $this->append($this->ownerDocument->context());
        return $this;
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
        return new self($nodeset, $this->ownerDocument);
    }

    /**
     * also used like $this->map('attr', 'id')
     *
     * @param string $method
     * @param null $arg
     * @param string $key
     *
     * @return \ArrayIterator
     */
    public function map($method = 'text', $arg = null, $key = 'getNodePath')
    {
        $array = array();
        foreach ($this as $node) {
            /** @var $node Attr|Element|Tag|Field|Text|Cdata|Comment */
            $array[$node->$key()] = $node->$method($arg);
        }
        return new \ArrayIterator($array);
    }

    /**
     * @param callable $fn who must return boolean true to continue call with $iterator->current()
     * @param array    $args
     * @param int      $count
     *
     * @return $this
     */
    public function apply(\Closure $fn, array $args = array(), &$count = 0)
    {
        $count = iterator_apply($this, $fn, array_merge(array($this), $args));
        return $this;
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
            $this->ownerDocument->xslt($xslFile, $xsltParameters, $node);
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
                if ($result instanceof self) {
                    foreach ($result as $item) {
                        $newSet[] = $item;
                    }
                } else {
                    $newSet[] = $result;
                }
            }
        }
        if ($newSet) {
            return new self($newSet, $this->ownerDocument);
        }
        return $this;
    }

    public function __get($property)
    {
        $entry = $this->first();
        return $entry->$property;
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