<?php
/**
 * volux\Dom
 *
 * @link http://github.com/volux/dom
 */
namespace volux\Dom;

use volux\Dom;

    {

        /**
         * Class Comment
         * @package volux\Dom
         * @author  Andrey Skulov <andrey.skulov@gmail.com>
         */
        class Comment extends \DOMComment
        {
            /**
             * @param string $expr css selector
             *
             * @return bool
             */
            public function is($expr)
            {
                return !$this->doc()->find(array($expr, $this->getNodePath()))->isEmpty();
            }

            /**
             * @param string $expr css selector
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
                return (Dom::NAME_NOT_MATCHED === $this->name()) or ('' === $this->nodeValue);
            }

            /**
             * @return Dom|Html|Table|Form
             */
            public function doc()
            {
                return $this->ownerDocument;
            }

            /**
             * @return Element|Tag|Field
             */
            public function parent()
            {
                return $this->parentNode;
            }

            /**
             * @return Element|Tag|Field
             */
            public function end()
            {
                return $this->doc()->context();
            }

            /**
             * @param bool $deep
             *
             * @return Comment
             */
            public function copy($deep = true)
            {
                return $this->cloneNode($deep);
            }

            /**
             * @return Element|Tag|Field
             */
            public function remove()
            {
                return $this->parentNode->removeChild($this);
            }

            /**
             * @param string|array $expr css selector (if in array: with axis)
             * @param null|int $index
             * @param null|\DOMNode|Element|Tag|Field|Attr|Text|Cdata $context
             *
             * @return Element|Tag|Field|Attr|Text|Cdata|Set
             */
            public function find($expr, $index = null, $context = null)
            {
                if (is_null($context)) {
                    $context = $this;
                }
                return $this->doc()->find($expr, $index, $context);
            }

            /**
             * @param null|string $selector css selector
             *
             * @return Set|Element|Tag|Field|Text|Cdata|Comment
             */
            public function siblings($selector = null)
            {
                return $this->parent()->children($selector);
            }

            /**
             * @param null|int $to
             *
             * @return int|$this|Comment
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
             * @param null|string $newText
             * @param bool $replace
             *
             * @return $this|Comment|string
             */
            public function text($newText = null, $replace = false)
            {
                if (!is_null($newText)) {
                    if ($replace) {
                        $this->nodeValue = '';
                    }
                    $this->nodeValue = $this->nodeValue.$newText;
                    return $this;
                }
                return $this->nodeValue;
            }

            /**
             * @param $element
             *
             * @return Element|Field|Tag
             */
            public function append($element)
            {
                return $this->parent()->append($element);
            }

            /**
             * @param $element
             *
             * @return Element|Field|Tag
             */
            public function prepend($element)
            {
                return $this->parent()->prepend($element);
            }

            /**
             * @param $element
             *
             * @return Element|Field|Tag
             */
            public function after($element)
            {
                return $this->parent()->after($element);
            }

            /**
             * @param $element
             *
             * @return Element|Field|Tag
             */
            public function before($element)
            {
                return $this->parent()->before($element);
            }

            /**
             * @param string|null $name
             *
             * @return string|Element|Tag|Field
             */
            public function name($name = null)
            {
                if (!empty($name)) {
                    return $this->wrap((string)$name);
                }
                return $this->nodeName;
            }

            /**
             * @param $wrapper
             *
             * @return Element|Tag|Field
             */
            public function wrap($wrapper)
            {
                if (is_string($wrapper)) {
                    $wrapper = $this->doc()->createElement($wrapper);
                }
                $parent = $this->parentNode;
                $wrapper = $parent->appendChild($wrapper);
                $wrapper->appendChild($this->cloneNode(true));
                $parent->replaceChild($wrapper, $this);
                return $wrapper;
            }

            /**
             * @return array
             */
            public function toArray()
            {
                return array($this->nodeName => $this->nodeValue);
            }

            /**
             * @return string
             */
            public function __toString()
            {
                return $this->nodeValue;
            }

        }
    }
