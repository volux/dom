<?php
namespace volux\Dom;

use volux\Dom;

    {
        /**
         * @package volux\Dom
         * @author  Andrey Skulov <andrey.skulov@gmail.com>
         **/
        class Set extends \ArrayIterator implements \RecursiveIterator
        {
            /**
             * @var Dom
             */
            protected $dom;
            /**
             * @param \DOMNodelist|\DOMNamedNodeMap|Set|array $nodeList
             * @param Dom $dom
             */
            public function __construct($nodeList, Dom &$dom)
            {
                $this->dom = $dom;
                $nodes = array();
                foreach ($nodeList as $node) {
                    $nodes[] = $node;
                }
                parent::__construct($nodes);
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
                return new Set($this->current()->childNodes, $this->dom);
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
             * @return Tag|Node|Attr
             */
            public function sequent()
            {
                if ($this->valid()) {
                    $next = $this->current();
                    $this->next();
                    return $next;
                }
                $this->rewind();
                return false;
            }

            /**
             * @param $offset
             *
             * @return Tag|Node|Attr
             */
            public function eq($offset)
            {
                if ($this->count() < $offset) {
                    return $this->dom->notEmpty(false, 'Set::eq()');
                }
                return $this->offsetGet($offset);
            }

            /**
             * @return Tag|Node|Attr
             */
            public function first()
            {
                if ($this->count() == 0) {
                    return $this->dom->notEmpty(false, 'Set::first()');
                }
                return $this->offsetGet(0);
            }

            /**
             * @return Tag|Node|Attr
             */
            public function last()
            {
                if ($this->count() == 0) {
                    return $this->dom->notEmpty(false, 'Set::last()');
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
                    #var_dump($aValue);
                    $bValue = $b->find($xPath, 0)->text();
                    #var_dump($bValue);
                    if (!is_numeric($aValue)) {
                        return strcasecmp($aValue, $bValue);
                    }
                    if ($aValue == $bValue) {
                        return 0;
                    }
                    return ($aValue < $bValue) ? -1 : 1;
                };
                #var_dump($iterator);
                $this->uasort($comparator);
                return $this;
            }

            /**
             * @param string $xPath
             * @param string $method
             * @param bool $compare
             *
             * @return Set
             */
            public function select($xPath, $method = 'node', $compare = null)
            {
                $nodeset = array();
                foreach ($this as $node) {
                    /** @var $select Node */
                    $select = $node->$method($xPath);
                    if (is_object($select)) {
                        if (!$select->isEmpty()) {
                            $nodeset[] = $node;
                        }
                    } else {
                        if ($compare == $select) {
                            $nodeset[] = $node;
                        }
                    }
                }
                return new Set($nodeset, $this->dom);
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
            public function map($method = 'text', $key = 'name', $arg = null)
            {
                $array = array();
                foreach ($this as $node) {
                    $array[$node->$key()] = $node->$method($arg);
                }
                return new \ArrayIterator($array);
            }

            /**
             * @param $name
             * @param array $args
             *
             * @return Set
             */
            public function __call($name, array $args)
            {
                foreach ($this as $node) {
                    call_user_func_array(array($node, $name), $args);
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
                    $string .= (string)$node. PHP_EOL . PHP_EOL;
                }
                return $string;
            }
        }
    }