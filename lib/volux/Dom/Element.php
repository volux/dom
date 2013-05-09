<?php
namespace volux\Dom;

use volux\Dom;
{
    /**
     * @package volux.pro
     * @author  Andrey Skulov <andrey.skulov@gmail.com>
     **/
    class Element extends \DOMElement
    {
        /**
         * @param $expr
         *
         * @return bool
         */
        public function is($expr)
        {
            return !$this->owner()->find(Dom::FORCE_PREFIX.$this->getNodePath() . '{'. $this->owner()->xPathExpr($expr).'}')->isEmpty();
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
         * @return Dom
         */
        public function owner()
        {
            return $this->ownerDocument;
        }

        /**
         * @param $child
         *
         * @return Element
         */
        public function append($child)
        {
            if (is_string($child)) {
                $child = $this->owner()->createNode($child);
            }
            return $this->appendChild($this->importNode($child));
        }

        /**
         * @param $target
         *
         * @return Element
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
            return $target->appendChild($this);
        }

        /**
         * @param $child
         *
         * @return Element
         */
        public function prepend($child)
        {
            if (is_string($child)) {
                $child = $this->owner()->createNode($child);
            }
            return $this->insertBefore($this->importNode($child), $this->firstChild);
        }

        /**
         * @param $target
         *
         * @return Element
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
         * @return Element|Set
         */
        public function next($selector = null)
        {
            if (is_null($selector)) {
                return $this->nextSibling;
            }
            return $this->find(Dom::FORCE_PREFIX . 'following-sibling::' . $this->owner()->xPathExpr($selector));
        }

        /**
         * @param null $selector
         *
         * @return Element|Set
         */
        public function prev($selector = null)
        {
            if (is_null($selector)) {
                return $this->previousSibling;
            }
            return $this->find(Dom::FORCE_PREFIX . 'preceding-sibling::'. $this->owner()->xPathExpr($selector));
        }

        /**
         * @param $sibling
         *
         * @return Element
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
                $sibling = $this->owner()->createNode($sibling);
            }
            if (!$next) {
                return $this->parentNode->appendChild($this->importNode($sibling));
            }
            return $this->parentNode->insertBefore($this->importNode($sibling), $next);
        }

        /**
         * @param $sibling
         *
         * @return Element
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
                $sibling = $this->owner()->createNode($sibling);
            }
            return $this->parentNode->insertBefore($this->importNode($sibling), $this);
        }

        /**
         * @param $wrapper
         *
         * @return Element
         */
        public function wrap($wrapper)
        {
            if (is_string($wrapper)) {
                $wrapper = $this->owner()->createNode($wrapper);
            }
            $parent = $this->parentNode;
            $wrapper = $parent->appendChild($wrapper);
            $wrapper->appendChild($this->cloneNode(true));
            $parent->replaceChild($wrapper, $this);
            return $wrapper;
        }

        /**
         * @todo implement move in position $to
         *
         * @param int $to
         *
         * @return Element
         */
        public function position($to = null)
        {
            if (is_null($to)) {
                return $this->find(Dom::FORCE_PREFIX.'preceding-sibling::*')->count() + 1;
            }
            return $this;
        }

        /**
         * @param bool $returnSwap
         *
         * @return Element
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
         * @return Element
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
         * @return Element
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
            return $this->find(Dom::FORCE_PREFIX.'ancestor::*');
        }

        /**
         * @param $expr
         *
         * @return Element
         */
        public function closest($expr)
        {
            return $this->find(Dom::FORCE_PREFIX.'ancestor::' . $this->owner()->xPathExpr($expr))->last();
        }

        /**
         * @param bool $asXPath
         *
         * @return string|Element
         */
        public function path($asXPath = false)
        {
            if ($asXPath) {
                return $this->getNodePath();
            }
            if ($this->isEmpty()) {
                return $this;
            }
            $path = $this->owner()->createNode('path');
            $parents = $this->parents();
            while ($parent = $parents->sequent()) {
                $path->append($parent->copy(false));
            }
            $path->append($this->copy(false));
            return $path;
        }

        /**
         * @return Element
         */
        public function end()
        {
            return $this->owner()->context();
        }

        /**
         * @return Element
         */
        public function remove()
        {
            return $this->parentNode->removeChild($this);
        }

        /**
         * @param $selector
         *
         * @return Set|Element
         */
        public function children($selector = null)
        {
            if (is_null($selector)) {
                return $this->owner()->set($this->childNodes);
            }
            if (is_numeric($selector)) {
                return $this->childNodes->item((int)$selector);
            }
            return $this->find(Dom::FORCE_PREFIX.'child::' . $this->owner()->xPathExpr($selector));
        }

        /**
         * @param null $selector
         *
         * @return Set|Element
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
         * @return Element
         */
        public function clear()
        {
            $children = $this->children();
            foreach ($children as $child) {
                $this->removeChild($child);
            }
            return $this;
        }

        /**
         * @param $name
         * @param null $value
         *
         * @return Element|Node|Tag|Attr
         */
        public function attr($name, $value = null)
        {
            if ($name instanceof Set) {
                foreach ($name as $a) {
                    /** @var $a Attr */
                    $this->setAttributeNode($a);
                    if ('id' === $a->name) {
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
                    if ('id' === $name) {
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
         * @param null $name
         *
         * @return string|Element
         */
        public function name($name = null)
        {
            if (!empty($name)) {
                $name = (string)$name;
                $old = $this->copy();
                $new = $this->owner()->createNode($name);
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
         * @return Element
         */
        public function copy($deep = true)
        {
            return $this->cloneNode($deep);
        }

        /**
         * @param null $text
         * @param bool $add
         *
         * @return Element|string
         */
        public function text($text = null, $add = false)
        {
            if (!is_null($text)) {
                $exitsTextNode = $this->firstChild;
                $newTextNode = $this->owner()->createText($text);
                if ($exitsTextNode && !$add && $exitsTextNode->nodeType === XML_TEXT_NODE) {
                    $this->replaceChild($newTextNode, $exitsTextNode);
                    return $this;
                }
                $this->appendChild($newTextNode);
                return $this;
            }
            return $this->textContent;
        }

        /**
         * @param $xml
         *
         * @return Element
         */
        public function add($xml)
        {
            $xml = (string)$xml;
            if ($this->owner()->createFragment($xml, $fragment)) {
                $this->append($fragment);
                return $this;
            }
            return $this->text($xml, true);
        }

        /**
         * @param $expr
         * @param null $index
         * @param null $context
         *
         * @return Element|Set
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
         * @return Element
         */
        public function a($name, array $attr = array(), $text = '')
        {
            if (empty($name)) {
                return $this;
            }
            return $this->appendChild($this->owner()->createNode($name)->attr($attr)->add($text));
        }

        /**
         * @param $name
         * @param array $attr
         * @param string $text
         *
         * @return Element
         */
        public function p($name, array $attr = array(), $text = '')
        {
            if (empty($name)) {
                return $this;
            }
            return $this->insertBefore($this->owner()->createNode($name)->attr($attr)->add($text), $this->firstChild);
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
         * @return Element
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
         * @return Element
         */
        protected function importNode($newNode)
        {
            return $this->owner()->importNode($newNode);
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
