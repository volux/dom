<?php
namespace volux\Dom;

use volux\Dom;

    {
        /**
         * @package volux\Dom
         * @author  Andrey Skulov <andrey.skulov@gmail.com>
         **/
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
                    return ($expr === $this->name);
                }
                return ($expr === $this->value);
            }

            /**
             * @return bool
             */
            public function isEmpty()
            {
                return (Dom::NAME_NOT_MATCHED === $this->name);
            }

            /**
             * @return Dom
             */
            protected function owner()
            {
                return $this->ownerElement->ownerDocument;
            }

            /**
             * @return Element
             */
            protected function parent()
            {
                return $this->ownerElement;
            }

            /**
             * @return Element
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
                    $exitsText = $this->value;
                    if ($exitsText && $add) {
                        $this->value = $exitsText.$newText;
                        return $this;
                    }
                    $this->value = $newText;
                    return $this;
                }
                return $this->value;
            }

            /**
             * @return string
             */
            public function __toString()
            {
                return $this->value;
            }
        }
    }