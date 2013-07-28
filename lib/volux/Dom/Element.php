<?php
namespace volux\Dom;

use volux\Dom;

    {

        /**
         * Class Element
         * @package volux\Dom
         * @author  Andrey Skulov <andrey.skulov@gmail.com>
         */
        class Element extends \DOMElement
        {
            /**
             * @param null $xml
             *
             * @return string|Element
             */
            public function xml($xml = null)
            {
                if (!is_null($xml)) {
                    $this->clear();
                    return $this->add($xml);
                }
                return (string)$this->children();
            }

            /**
             * @return Dom
             */
            public function owner()
            {
                return $this->ownerDocument;
            }

            /**
             * @param $expr
             *
             * @return bool
             */
            public function is($expr)
            {
                return !$this->owner()->find(array($expr, $this->getNodePath()))->isEmpty();
            }

            /**
             * name of child node or attribute
             * @param $expr
             *
             * @return bool
             */
            public function has($expr)
            {
                return !$this->find($expr)->isEmpty();
            }

            /**
             * @return bool
             */
            public function isEmpty()
            {
                return (Dom::NAME_NOT_MATCHED === $this->name());
            }

            /**
             * @param $child
             *
             * @return Element|Tag
             */
            public function append($child)
            {
                if (is_string($child)) {
                    $child = $this->owner()->createElement($child);
                }
                return $this->appendChild($this->importNode($child));
            }

            /**
             * @param $target
             *
             * @return Element|Tag
             */
            public function appendTo($target)
            {
                static $find = array();
                if (is_string($target)) {
                    if (!isset($find[$target])) {
                        $find[$target] = $this->owner()->find($target, 0);
                    }
                    $target = $find[$target];
                }
                /** @var $target Element */
                return $target->appendChild($target->ownerDocument->importNode($this, true));
            }

            /**
             * @param $child
             *
             * @return Element|Tag
             */
            public function prepend($child)
            {
                if (is_string($child)) {
                    $child = $this->owner()->createElement($child);
                }
                return $this->insertBefore($this->importNode($child), $this->firstChild);
            }

            /**
             * @param $target
             *
             * @return Element|Tag
             */
            public function prependTo($target)
            {
                static $find = array();
                if (is_string($target)) {
                    if (!isset($find[$target])) {
                        $find[$target] = $this->owner()->find($target, 0);
                    }
                    $target = $find[$target];
                }
                /** @var $target Element */
                return $target->prepend($this);
            }

            /**
             * @param null $selector
             *
             * @return Element|Text|Tag|Set
             */
            public function next($selector = null)
            {
                if (is_null($selector)) {
                    return $this->nextSibling;
                }
                return $this->find(array($selector, 'following-sibling::*'));
            }

            /**
             * @param null $selector
             *
             * @return Element|Text|Tag|Set
             */
            public function prev($selector = null)
            {
                if (is_null($selector)) {
                    return $this->previousSibling;
                }
                return $this->find(array($selector, 'preceding-sibling::*'));
            }

            /**
             * @param $sibling
             *
             * @return Element|Tag
             */
            public function after($sibling = null)
            {
                $next = $this->nextSibling;
                if (is_null($sibling)) {
                    if ($next) {
                        return $next;
                    }
                    return $this;
                }
                if (is_string($sibling)) {
                    $sibling = $this->owner()->createElement($sibling);
                }
                if (!$next) {
                    return $this->parentNode->appendChild($this->importNode($sibling));
                }
                return $this->parentNode->insertBefore($this->importNode($sibling), $next);
            }

            /**
             * @param $sibling
             *
             * @return Element|Tag
             */
            public function before($sibling = null)
            {
                $prev = $this->previousSibling;
                if (is_null($sibling)) {
                    if ($prev) {
                        return $prev;
                    }
                    return $this;
                }
                if (is_string($sibling)) {
                    $sibling = $this->owner()->createElement($sibling);
                }
                return $this->parentNode->insertBefore($this->importNode($sibling), $this);
            }

            /**
             * @param \DOMNode $newNode
             *
             * @return Element|Tag
             */
            public function replace(\DOMNode $newNode)
            {
                $this->parentNode->replaceChild($this->importNode($newNode), $this);
                return $newNode;
            }

            /**
             * @param string $xslFile
             * @param array $xsltParameters
             *
             * @return $this|Element|Tag
             */
            public function transform($xslFile, $xsltParameters = array())
            {
                return $this->owner()->transform($xslFile, $xsltParameters, $this);
            }

            /**
             * @param $wrapper
             *
             * @return Element|Tag
             */
            public function wrap($wrapper)
            {
                if (is_string($wrapper)) {
                    $wrapper = $this->owner()->createElement($wrapper);
                }
                $parent = $this->parentNode;
                $wrapper = $parent->appendChild($wrapper);
                $wrapper->appendChild($this->cloneNode(true));
                $parent->replaceChild($wrapper, $this);
                return $wrapper;
            }

            /**
             * @param int $to
             *
             * @return Element|Tag
             */
            public function position($to = null)
            {
                if (is_null($to)) {
                    return $this->find(array('', 'preceding-sibling::*'))->count() + 1;
                }
                if ($this->siblings()->count() < $to) {
                    return $this;
                }
                $this->siblings($to+1)->before($this);
                return $this;
            }

            /**
             * @param bool $returnSwap
             *
             * @return Element|Tag
             */
            public function up($returnSwap = false)
            {
                /** @var $node Element */
                $node = $this->before()->before($this);
                if ($returnSwap) {
                    return $node->nextSibling;
                }
                return $node;
            }

            /**
             * @param bool $returnSwap
             *
             * @return Element|Tag
             */
            public function down($returnSwap = false)
            {
                /** @var $node Element */
                $node = $this->after()->after($this);
                if ($returnSwap) {
                    return $node->previousSibling;
                }
                return $node;
            }

            /**
             * @return Element|Tag|Dom
             */
            public function parent()
            {
                return $this->parentNode;
            }

            /**
             * @return Set
             */
            public function parents()
            {
                return $this->find(array('', 'ancestor::*'));
            }

            /**
             * @param $expr
             *
             * @return Element|Tag
             */
            public function closest($expr)
            {
                return $this->find(array($expr, 'ancestor::*'))->last();
            }

            /**
             * @param bool $asXPath
             *
             * @return string|Element|Tag
             */
            public function path($asXPath = false)
            {
                if ($asXPath) {
                    return $this->getNodePath();
                }
                if ($this->isEmpty()) {
                    return $this;
                }
                $path = $this->owner()->createElement('path');
                $parents = $this->parents();
                while ($parent = $parents->sequent()) {
                    $path->append($parent->copy(false));
                }
                $path->append($this->copy(false));
                return $path;
            }

            /**
             * @return Element|Tag|Text|Cdata|Comment
             */
            public function end()
            {
                return $this->owner()->context();
            }

            /**
             * @return Element|Tag
             */
            public function remove()
            {
                return $this->parentNode->removeChild($this);
            }

            /**
             * @param $selector
             *
             * @return Set|Element|Tag|Text|Cdata|Comment
             */
            public function children($selector = null)
            {
                if (is_null($selector)) {
                    return $this->owner()->set($this->childNodes);
                }
                if (is_numeric($selector)) {
                    return $this->childNodes->item((int)$selector);
                }
                return $this->find(array($selector, 'child::*'));
            }

            /**
             * @param null $selector
             *
             * @return Set|Element|Tag|Text|Cdata|Comment
             */
            public function siblings($selector = null)
            {
                return $this->parent()->children($selector);
            }

            /**
             * @return Set
             */
            public function attributes()
            {
                return $this->owner()->set($this->attributes);
            }

            /**
             * empty - reserved word
             * @return Element|Tag
             */
            public function clear()
            {
                $this->children()->remove();
                return $this;
            }

            /**
             * @param $name
             * @param null $value
             *
             * @return $this|Element|Tag|Attr
             */
            public function attr($name, $value = null)
            {
                if ($name instanceof Set) {
                    foreach ($name as $a) {
                        /** @var $a Attr */
                        $this->setAttributeNode($a);
                        if (Dom::ID_ATTR === $a->name) {
                            $this->setIdAttributeNode($a, true);
                        }
                    }
                    return $this;
                }
                if (!is_null($value)) {
                    if (false === $value) {
                        $this->removeAttribute($name);
                    } else
                        if (true === $value) {
                            $this->setAttribute($name, $name);
                        } else {
                            $this->setAttribute($name, $value);
                            if (Dom::ID_ATTR === $name) {
                                $this->setIdAttribute($name, true);
                            }
                        }
                    return $this;
                }
                if (is_array($name)) {
                    foreach ($name as $attr=>$val) {
                        $this->attr($attr, $val);
                    }
                    return $this;
                }
                $attr = $this->getAttributeNode($name);
                if (!$attr) {
                    $attr = $this->owner()->createAttr($name, null);
                }
                return $attr;
            }

            /**
             * @param string $attr name
             * @param string $items space separated
             * @param bool $add if true, false - remove
             *
             * @return $this|Element|Tag
             */
            protected function attrItems($attr, $items, $add)
            {
                if ($items and $this->hasAttribute($attr)) {
                    $exists = array_filter(explode(' ', $this->attr($attr)));
                    $items = array_filter(explode(' ', $items));
                    sort($exists);
                    sort($items);
                    if ($add) {
                        $items = implode(' ', array_unique(array_merge($exists, $items)));
                    } else {
                        $items = implode(' ', array_diff($exists, $items));
                    }
                }
                return $this->attr($attr, ($items)? $items : false);
            }

            /**
             * @param null $name
             *
             * @return string|Element|Tag
             */
            public function name($name = null)
            {
                if (!empty($name)) {
                    $name = (string)$name;
                    $old = $this->copy();
                    $new = $this->owner()->createElement($name);
                    $new->attr($old->attributes());
                    $children = $old->children();
                    foreach ($children as $child) {
                        $new->append($child);
                    }
                    $this->parentNode->replaceChild($new, $this);
                    return $new;
                }
                return $this->nodeName;
            }

            /**
             * @param bool $deep
             *
             * @return Element|Tag
             */
            public function copy($deep = true)
            {
                return $this->cloneNode($deep);
            }

            /**
             * @param null $newText
             * @param bool $replace
             *
             * @return $this|Element|Tag|string
             */
            public function text($newText = null, $replace = false)
            {
                if (!is_null($newText)) {
                    $newTextNode = $this->owner()->createText($newText);
                    if ($replace) {
                        $this->clear();
                    }
                    $this->appendChild($newTextNode);
                    return $this;
                }
                return $this->find(array('/text()', '.'));
            }

            /**
             * @param $xml
             *
             * @return $this|Element|Tag
             */
            public function add($xml)
            {
                $xml = (string)$xml;
                if ($this->owner()->createFragment($xml, $fragment)) {
                    $this->append($fragment);
                    return $this;
                }
                return $this->text($xml);
            }

            /**
             * @param $expr
             * @param null $index
             * @param null $context
             *
             * @return Element|Tag|Attr|Text|Cdata|Comment|Set
             */
            public function find($expr, $index = null, $context = null)
            {
                if (is_null($context)) {
                    $context = $this;
                }
                return $this->owner()->find($expr, $index, $context);
            }

            /**
             * @param $name
             * @param array $attr
             * @param string $text
             *
             * @return $this|Element|Tag
             */
            public function a($name, array $attr = array(), $text = '')
            {
                if (empty($name)) {
                    return $this;
                }
                return $this->appendChild($this->owner()->createElement($name)->attr($attr)->add($text));
            }

            /**
             * @param $name
             * @param array $attr
             * @param string $text
             *
             * @return $this|Element|Tag
             */
            public function p($name, array $attr = array(), $text = '')
            {
                if (empty($name)) {
                    return $this;
                }
                return $this->insertBefore($this->owner()->createElement($name)->attr($attr)->add($text), $this->firstChild);
            }

            /**
             * @example for simple list (<li>, <ol> etc.): array('foo', 'bar', ...)
             * @example for <img>: array(array('src'=>'foo'), ...)
             * @example for <input>: array(array('value'=>'bar', 'type'=>'hidden'), ...)
             * @example for <a>: array(array('a-text' => array('href'=>'bar', 'class'=>'a-class')), ...)
             * @example for <option>: , array(array('option-text' => array('value'=>'foo', 'selected'=>'selected')), ...)
             *
             * @param $name
             * @param array $fill
             *
             * @return $this|Element|Tag
             */
            public function l($name, array $fill)
            {
                foreach ($fill as $items) {
                    if (!is_array($items)) {
                        $this->a($name, array(), $items);
                    } else {
                        if (isset($items['>'])) {
                            $sub = array_shift($items);
                            $text = array_shift($items);
                            $this->a($name)->a($sub, $items, $text);
                        } else {
                            foreach ($items as $text=>$attr) {
                                if (empty($attr)) {
                                    $attr = array();
                                }
                                if (is_integer($text)) {
                                    $text = false;
                                }
                                $this->a($name, $attr, $text);
                            }
                        }
                    }
                }
                return $this;
            }

            /**
             * @param $newNode
             *
             * @return Element|Tag
             */
            protected function importNode($newNode)
            {
                return $this->owner()->importNode($newNode);
            }

            /**
             * @return array
             */
            public function toArray()
            {
                return $this->owner()->toArray($this);
            }

            /**
             * @return string
             */
            public function __toString()
            {
                return $this->ownerDocument->saveXML($this);
            }
        }
    }
