<?php
namespace volux\Dom;

use volux\Dom;

    {

        /**
         * Class Tag
         * @package volux\Dom
         * @author  Andrey Skulov <andrey.skulov@gmail.com>
         */
        class Tag extends Element
        {
            const CLASS_ATTR = 'class';

            /**
             * @param $name
             * @param null $value
             *
             * @return mixed|string|Tag
             */
            public function data($name, $value = null)
            {
                $name = 'data-' . $name;
                if (is_null($value)) {
                    return json_decode($this->attr($name)->text());
                }
                return $this->attr($name, json_encode($value));
            }

            /**
             * attribute for AngularJS directive
             * @param $name
             * @param null $value
             *
             * @return mixed|string|Tag
             */
            public function ng($name, $value = null)
            {
                $name = 'ng-' . $name;
                if (is_null($value)) {
                    return $this->attr($name)->text();
                }
                return $this->attr($name, $value);
            }

            /**
             * @param $classes
             *
             * @return Tag
             */
            public function addClass($classes)
            {
                return $this->attrItems(self::CLASS_ATTR, $classes, true);
            }

            /**
             * @param bool $classes
             *
             * @return Tag
             */
            public function removeClass($classes = false)
            {
                return $this->attrItems(self::CLASS_ATTR, $classes, false);
            }

            /**
             * @param null $html
             *
             * @return string|Tag
             */
            public function html($html = null)
            {
                if (!is_null($html)) {
                    $this->clear();
                    return $this->append($html);
                }
                return (string)$this->children();
            }

            /**
             * @param string $xml
             *
             * @return $this|Tag
             */
            public function replaceWith($xml)
            {
                if ($this->owner()->createFragment($xml, $fragment)) {
                    return $this->replace($fragment);
                }
                return $this;
            }

            /**
             * @return Html
             */
            public function owner()
            {
                return $this->ownerDocument;
            }

            /**
             * @return string
             */
            public function __toString()
            {
                return $this->ownerDocument->saveHTML($this);
            }
        }

    }
