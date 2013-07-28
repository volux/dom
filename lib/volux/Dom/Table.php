<?php
namespace volux\Dom;

use volux\Dom;

    {

        /**
         * Class Set
         * @package volux\Dom
         * @author  Andrey Skulov <andrey.skulov@gmail.com>
         */
        class Set extends \ArrayIterator implements \RecursiveIterator
        {
            /**
             * @var Dom|Html
             */
            protected $ownerDocument;
            /**
             * @param \DOMNodelist|\DOMNamedNodeMap|Set|array $nodeList
             * @param Dom $dom
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
             * @return Dom|Html
             */
            public function owner()
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
                return new self($this->current()->childNodes, $this->owner());
            }

            /**
             * @param $callable
             *
             * @return Set
             */
            public function each($callable)
            {
                $index = 0;
                foreach ($this as $node) {
                    call_user_func($callable, $node, $index++);
                }
                return $this;
            }

            /**
             * <code> while ($node = $set->sequent()) { $node } </code>
             * @return Element|Tag|Attr|Text|Cdata
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
             * @param $offset
             *
             * @return Element|Tag|Attr|Text|Cdata
             */
            public function eq($offset)
            {
                if ($this->count() < $offset) {
                    return $this->owner()->notEmpty(false, '=Set::eq()');
                }
                return $this->offsetGet($offset);
            }

            /**
             * @return Element|Tag|Attr|Text|Cdata
             */
            public function first()
            {
                if ($this->count() == 0) {
                    return $this->owner()->notEmpty(false, '=Set::first()');
                }
                return $this->offsetGet(0);
            }

            /**
             * @return Element|Tag|Attr
             */
            public function last()
            {
                if ($this->count() == 0) {
                    return $this->owner()->notEmpty(false, '=Set::last()');
                }
                $offset = $this->count()-1;
                return $this->offsetGet($offset);
            }

            /**
             * @return Set
             */
            protected function removeAll()
            {
                for ($this->rewind(); $this->valid(); $this->offsetUnset($this->key())) {
                    $this->offsetGet($this->key())->remove();
                }
                return $this;
            }

            /**
             * @param null $key
             *
             * @return Set
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
             * @param string|Element|Tag $target
             *
             * @return $this
             */
            public function appendTo(&$target)
            {
                if (is_string($target)) {
                    $target = $this->owner()->createElement($target);
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
             * @return Set
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
             * @param $selector
             *
             * @return Set
             */
            public function filter($selector)
            {
                $nodeset = array();
                foreach ($this as $node) {
                    /** @var $node Element|Tag|Attr|Text|Cdata */
                    if ($node->is($selector)) {
                        $nodeset[] = $node;
                    }
                }
                return new self($nodeset, $this->owner());
            }

            /**
             * also used like $this->names('attr', 'id')
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
                    /** @var $node Element|Tag|Attr|Text|Cdata */
                    $array[$node->$key()] = $node->$method($arg);
                }
                return new \ArrayIterator($array);
            }

            /**
             * @param string $xslFile
             * @param array $xsltParameters
             *
             * @return $this
             */
            public function transform($xslFile, $xsltParameters = array())
            {
                foreach ($this as $node) {
                    $this->owner()->transform($xslFile, $xsltParameters, $node);
                }
                return $this;
            }

            /**
             * Invoke $method for each node in Set
             *
             * @param $method
             * @param array $args
             *
             * @return Set
             */
            public function __call($method, array $args)
            {
                foreach ($this as $node) {
                    /** @var $node Element|Tag|Attr|Text|Cdata */
                    call_user_func_array(array($node, $method), $args);
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
                    /** @var $node Element|Tag|Attr|Text|Cdata */
                    $string .= (string)$node. PHP_EOL . PHP_EOL;
                }
                return $string;
            }
        }
    }