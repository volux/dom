<?php
namespace volux\Dom;

use volux\Dom;

    {
        /**
         * @package volux\Dom
         * @author  Andrey Skulov <andrey.skulov@gmail.com>
         **/
        class Tag extends Element
        {

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
             * @param $class
             *
             * @return Tag
             */
            public function addClass($class)
            {
                if ($this->hasAttribute('class')) {
                    $existsClasses = array_filter(explode(' ', $this->attr('class')));
                    $addClasses = array_filter(explode(' ', $class));
                    sort($existsClasses);
                    sort($addClasses);

                    $class = implode(' ', array_unique(array_merge($existsClasses, $addClasses)));
                }
                return $this->attr('class', $class);
            }

            /**
             * @param bool $class
             *
             * @return Tag
             */
            public function removeClass($class = false)
            {
                if ($this->hasAttribute('class')) {
                    $existsClasses = array_filter(explode(' ', $this->attr('class')));
                    $removeClasses = array_filter(explode(' ', $class));
                    sort($existsClasses);
                    sort($removeClasses);
                    $stayClasses = array_diff($existsClasses, $removeClasses);
                    if (empty($stayClasses)) {
                        $this->removeAttribute('class');
                    } else {
                        $this->attr('class', implode(' ', $stayClasses));
                    }
                }
                return $this;
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
                    return $this->add($html);
                }
                return (string)$this->children();
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
