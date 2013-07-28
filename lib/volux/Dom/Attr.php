<?php
namespace volux\Dom;

use volux\Dom;

    {

        /**
         * Class Attr
         * @package volux\Dom
         * @author  Andrey Skulov <andrey.skulov@gmail.com>
         */
        class Attr extends \DOMAttr
        {

            /**
             * @param $expr
             *
             * @return bool
             */
            public function is($expr)
            {
                if ($expr{0} == '@') {
                    return ($expr === '@'.$this->nodeName);
                }
                return ($expr === $this->nodeValue);
            }

            /**
             * @return bool
             */
            public function isEmpty()
            {
                return (Dom::NAME_NOT_MATCHED === $this->nodeName) or ($this->nodeValue === '');
            }

            /**
             * @return Dom
             */
            protected function owner()
            {
                return $this->ownerElement->ownerDocument;
            }

            /**
             * @return Element|Tag
             */
            protected function parent()
            {
                return $this->ownerElement;
            }

            /**
             * @param bool $deep
             *
             * @return Attr
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
                $parent = $this->parent();
                $parent->removeAttributeNode($this);
                return $parent;
            }

            /**
             * @param null $newText
             * @param bool $add
             *
             * @return Attr|string
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