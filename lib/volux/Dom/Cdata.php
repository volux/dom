<?php
namespace volux\Dom;

use volux\Dom;

{

    /**
     * Class Cdata
     * @package volux\Dom
     * @author  Andrey Skulov <andrey.skulov@gmail.com>
     */
    class Cdata extends \DOMCdataSection
    {
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
            return (Dom::NAME_NOT_MATCHED === $this->name()) or ('' === $this->nodeValue);
        }

        /**
         * @return Dom
         */
        public function owner()
        {
            return $this->ownerDocument;
        }

        /**
         * @return Element|Tag
         */
        public function parent()
        {
            return $this->parentNode;
        }

        /**
         * @return Element|Tag
         */
        public function end()
        {
            return $this->owner()->context();
        }

        /**
         * @param bool $deep
         *
         * @return Cdata
         */
        public function copy($deep = true)
        {
            return $this->cloneNode($deep);
        }

        /**
         * @return Element|Tag
         */
        public function remove()
        {
            return $this->parentNode->removeChild($this);
        }

        /**
         * @param $expr
         * @param null $index
         * @param null $context
         *
         * @return Element|Tag|Attr|Text|Set
         */
        public function find($expr, $index = null, $context = null)
        {
            if (is_null($context)) {
                $context = $this;
            }
            return $this->owner()->find($expr, $index, $context);
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
         * @param int $to
         *
         * @return int|$this|Cdata
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
         * @param null $newText
         * @param bool $add
         *
         * @return $this|Cdata|string
         */
        public function text($newText = null, $add = false)
        {
            if (!is_null($newText)) {
                $exitsText = $this->nodeValue;
                if ($exitsText && $add) {
                    $this->nodeValue = $exitsText.$newText;
                    return $this;
                }
                $this->nodeValue = $newText;
                return $this;
            }
            return $this->nodeValue;
        }

        /**
         * @param string|null $name
         *
         * @return string|Element|Tag
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
