<?php
/**
 * volux\Dom
 *
 * @link http://github.com/volux/dom
 */

namespace volux\Dom\Helper;


use volux\Dom\Element;
use volux\Dom\Tag;
use volux\Dom\Field;

/**
 * Class FormBootstrap2
 * @package volux\Dom\Helper
 */
class FormBootstrap2 {

    /**
     * @var Field
     */
    protected $field;

    /**
     * @param Field $field
     */
    public function __construct(Field $field)
    {
        $this->field = $field;
    }

    /**
     * @param Field $field
     *
     * @return FormBootstrap2
     */
    public static function help(Field $field)
    {
        return new self($field);
    }

    /**
     * @param string $prepend
     * @param string $append
     * @param string $type
     *
     * @return $this|Element|Field|Tag
     */
    public function buttons($prepend = '', $append = '', $type = 'button')
    {
        if ($this->field->isPointInput()) {
            return $this->field;
        }
        $wrapper = $this->field->wrap('div');
        if ($prepend) {
            $wrapper->addClass('input-prepend');
            $this->field->before('button')->attr('type', $type)->addClass('btn')->text($prepend);
        }
        if ($append) {
            $wrapper->addClass('input-append');
            $this->field->after('button')->attr('type', $type)->addClass('btn')->text($append);
        }
        return $this->field;
    }

    /**
     * @param string $prepend
     * @param string $append
     *
     * @return $this|Element|Field|Tag
     */
    public function marks($prepend = '', $append = '')
    {
        if ($this->field->isPointInput()) {
            return $this->field;
        }
        $wrapper = $this->field->wrap('div');
        if ($prepend) {
            $wrapper->addClass('input-prepend');
            $this->field->before('span')->addClass('add-on')->text($prepend);
        }
        if ($append) {
            $wrapper->addClass('input-append');
            $this->field->after('span')->addClass('add-on')->text($append);
        }
        return $this->field;
    }

}