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
         * Class Tag
         * @package volux\Dom
         * @author  Andrey Skulov <andrey.skulov@gmail.com>
         */
        class Field extends Tag
        {
            /**
             * @param bool $root
             *
             * @return Dom|Form|Element|Field|Tag
             */
            public function add($root = false)
            {
                if ($root) {
                    return $this->doc()->to($root);
                }
                return $this->doc();
            }

            /**
             * @return Form|Dom
             */
            public function form()
            {
                return $this->add(true);
            }

            /**
             * @param string $mimeType 'image/*'|'video/*'|'audio/*'|etc.
             * @link http://htmlbook.ru/html/value/mime
             *
             * @return $this|Element|Field|Tag
             */
            public function accept($mimeType)
            {
                if ($this->attr('type') !== 'file') {
                    return $this;
                }
                return $this->attr('accept', $mimeType);
            }

            /**
             * @param string $text
             *
             * @return $this|Element|Field|Tag
             */
            public function alt($text)
            {
                if ($this->attr('type') !== 'image') {
                    return $this;
                }
                return $this->attr('alt', $text);
            }

            /**
             * @param string $key keyboard char
             *
             * @return $this|Element|Tag|Field
             */
            public function accessKey($key)
            {
                return $this->attr('accesskey', $key);
            }

            /**
             * @param bool $is
             *
             * @return $this|Element|Field|Tag
             */
            public function autoFocus($is = true)
            {
                return $this->attr('autofocus', $is);
            }

            /**
             * @param string|int|bool $cols numeric or false
             *
             * @return $this|Element|Field|Tag
             */
            public function cols($cols = '20')
            {
                if ($this->nodeName == 'textarea') {
                    return $this->numericOrFalse('cols', $cols);
                }
                return $this;
            }

            /**
             * @param bool $is
             *
             * @return $this|Element|Field|Tag
             */
            public function disabled($is = true)
            {
                return $this->attr('disabled', $is);
            }

            /**
             * @return bool
             */
            protected function alternateCheck()
            {
                return ($this->nodeName == 'form' or $this->nodeName == 'button' or $this->nodeName == 'input');
            }

            /**
             * @return string
             */
            protected function isForm()
            {
                if ($this->nodeName !== 'form') {
                    return 'form';
                }
                return '';
            }

            /**
             * @todo move it method to separate class helper Bootstrap
             * @param string $prepend
             * @param string $append
             * @param string $type
             *
             * @return $this|Element|Field|Tag
             */
            public function buttons($prepend = '', $append = '', $type = 'button')
            {
                if ($this->isPointInput()) {
                    return $this;
                }
                $wrapper = $this->wrap('div');
                if ($prepend) {
                    $wrapper->addClass('input-prepend');
                    $this->before('button')->attr('type', $type)->addClass('btn')->text($prepend);
                }
                if ($append) {
                    $wrapper->addClass('input-append');
                    $this->after('button')->attr('type', $type)->addClass('btn')->text($append);
                }
                return $this;
            }

            /**
             * @todo move it method to separate class helper Bootstrap
             * @param string $prepend
             * @param string $append
             *
             * @return $this|Element|Field|Tag
             */
            public function marks($prepend = '', $append = '')
            {
                if ($this->isPointInput()) {
                    return $this;
                }
                $wrapper = $this->wrap('div');
                if ($prepend) {
                    $wrapper->addClass('input-prepend');
                    $this->before('span')->addClass('add-on')->text($prepend);
                }
                if ($append) {
                    $wrapper->addClass('input-append');
                    $this->after('span')->addClass('add-on')->text($append);
                }
                return $this;
            }

            /**
             * @param string|bool $uri string or false
             *
             * @return $this|Element|Field|Tag
             */
            public function formAction($uri)
            {
                if ($this->alternateCheck()) {
                    return $this;
                }
                return $this->attr($this->isForm().'action', $uri);
            }

            /**
             * @param string $type 'app'|'multi'|'plain'
             *
             * @return $this|Element|Field|Tag
             */
            public function formEncodeType($type = 'app')
            {
                $types = array(
                    'app' => 'application/x-www-form-urlencoded',
                    'multi' => 'multipart/form-data',
                    'plain' => 'text/plain'
                );
                if (!$this->alternateCheck()) {
                    return $this;
                }
                return $this->attr($this->isForm().'enctype', $types[$type]);
            }

            /**
             * @param string $method 'get'|'post'
             *
             * @return $this|Element|Field|Tag
             */
            public function formMethod($method = 'get')
            {
                if (!$this->alternateCheck()) {
                    return $this;
                }
                return $this->attr($this->isForm().'method', $method);
            }

            /**
             * @param bool $is
             *
             * @return $this|Element|Field|Tag
             */
            public function formNoValidate($is = true)
            {
                if (!$this->alternateCheck()) {
                    return $this;
                }
                return $this->attr($this->isForm().'novalidate', $is);
            }

            /**
             * @param string $target 'frameName'|'_self'|'_blank'|'_parent'|'_top'
             *
             * @return $this|Element|Field|Tag
             */
            public function formTarget($target = '_self')
            {
                if (!$this->alternateCheck()) {
                    return $this;
                }
                return $this->attr($this->isForm().'target', $target);
            }

            /**
             * @param bool $is
             *
             * @return $this|Element|Field|Tag
             */
            public function checked($is = true)
            {
                if ($this->isPointInput()) {
                    return $this->attr('checked', $is);
                }
                return $this;
            }

            /**
             * @return bool
             */
            protected function isPointInput()
            {
                return ($this->attr('type') == 'radio' or $this->attr('type') == 'checkbox');
            }

            /**
             * @param array $list
             *
             * @return $this|Element|Field|Tag
             */
            public function dataList($list = array())
            {
                $id = 'dl_'.$this->name();
                $this->attr('list', $id);
                $dataList = $this->doc()->createElement('datalist');
                foreach ($list as $label) {
                    $dataList->append('option')->text($label);
                }
                $this->after($dataList->attr('id', $id));
                return $this;
            }

            /**
             * @param string $label
             * @param string $class
             *
             * @return $this|Element|Field|Tag
             */
            public function label($label, $class = '')
            {
                if ('checkbox' == $class or 'radio' == $class) {
                    $label = $this->wrap('label')->text(' '.$label)->addClass($class);
                } else {
                    $label = $this->before('label')->text($label)->addClass($class);
                }
                $id = $this->id();
                if ($id) {
                    $label->attr('for', $id);
                }
                return $this;
            }

            /**
             * @param string $helpText
             * @param string $class
             *
             * @return $this|Element|Field|Tag
             */
            public function help($helpText, $class = '')
            {
                $this->after('span')->text($helpText)->addClass($class);
                return $this;
            }

            /**
             * @param bool|int|string $length false or numeric
             *
             * @return $this|Element|Field|Tag
             */
            public function maxLength($length = false)
            {
                if ($this->inputTextCheck()) {
                    return $this->numericOrFalse('maxlength', $length);
                }
                return $this;
            }

            /**
             * For type="number|range". If "date" use maxDate()
             * @param bool|number $max numeric or false
             *
             * @return $this|Element|Field|Tag
             */
            public function maxValue($max = false)
            {
                return $this->numericOrFalse('max', $max);
            }

            /**
             * For type="number|range". If "date" use minDate()
             * @param bool|number $min numeric or false
             *
             * @return $this|Element|Field|Tag
             */
            public function minValue($min = false)
            {
                return $this->numericOrFalse('min', $min);
            }

            /**
             * @param bool $is
             *
             * @return $this|Element|Field|Tag
             */
            public function multiple($is = true)
            {
                return $this->attr('multiple', $is);
            }

            /**
             * @param null|string $name
             *
             * @return $this|string|Element|Field|Tag
             */
            public function name($name = null)
            {
                if (is_null($name)) {
                    return $this->attr('name')->text();
                }
                return $this->attr('name', $name);
            }

            /**
             * @param string $attr attr name
             * @param bool|int|string $value numeric or false
             *
             * @return $this|Element|Field|Tag
             */
            protected function numericOrFalse($attr, $value)
            {
                if (!is_numeric($value)) {
                    if (false === $value) {
                        $this->removeAttribute($attr);
                    }
                    return $this;
                }
                return $this->attr($attr, (string)$value);
            }

            /**
             * @link http://htmlbook.ru/html/input/pattern
             * @param $pattern
             *
             * @return $this|Element|Field|Tag
             */
            public function pattern($pattern)
            {
                if ($this->nodeName !== 'input') {
                    return $this;
                }
                return $this->attr('pattern', $pattern);
            }

            /**
             * @param string $text
             *
             * @return $this|Element|Field|Tag
             */
            public function placeholder($text)
            {
                if ($this->inputTextCheck()) {
                    return $this->attr('placeholder', $text);
                }
                return $this;
            }

            /**
             * @param bool $is
             *
             * @return $this|Element|Field|Tag
             */
            public function readOnly($is = true)
            {
                if ($this->inputTextCheck()) {
                    return $this->attr('readonly', $is);
                }
                return $this;
            }

            /**
             * @param bool $is
             *
             * @return $this|Element|Field|Tag
             */
            public function required($is = true)
            {
                if ($this->inputTextCheck()) {
                    return $this->attr('required', $is);
                }
                return $this;
            }

            /**
             * @param string|int|bool $rows numeric or false
             *
             * @return $this|Element|Field|Tag
             */
            public function rows($rows = '2')
            {
                if ($this->nodeName == 'textarea') {
                    return $this->numericOrFalse('rows', $rows);
                }
                return $this;
            }

            /**
             * @param string|bool $size number or false
             *
             * @return $this|Element|Field|Tag
             */
            public function size($size = '20')
            {
                if ($this->nodeName == 'input') {
                    return $this->numericOrFalse('size', $size);
                }
                return $this;
            }

            /**
             * @param bool|number $step numeric or false
             *
             * @return $this|Element|Field|Tag
             */
            public function stepValue($step = false)
            {
                return $this->numericOrFalse('step', $step);
            }

            /**
             * @param string|int|bool $index numeric or false
             *
             * @return $this|Element|Field|Tag
             */
            public function tabIndex($index)
            {
                return $this->numericOrFalse('tabindex', $index);
            }

            /**
             * @param string $is 'soft'|'hard'|'off'
             *
             * @return $this|Element|Field|Tag
             */
            public function textWrap($is = 'soft')
            {
                if ($this->nodeName == 'textarea') {
                    return $this->attr('wrap', $is);
                }
                return $this;
            }

            /**
             * @param null|string $value
             *
             * @return $this|string|Element|Field|Tag
             */
            public function val($value = null)
            {
                if ($this->nodeName == 'textarea') {
                    if (!is_null($value)) {
                        return $this->text($value);
                    }
                    return (string)$this->text();
                }
                if (!is_null($value)) {
                    return $this->attr('value', $value);
                }
                return $this->attr('value')->text();
            }

            /**
             * @return bool
             */
            protected function inputTextCheck()
            {
                return ($this->nodeName == 'textarea' or $this->nodeName == 'input');
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
