<?php
/**
 * volux\Dom
 *
 * @link http://github.com/volux/dom
 */
namespace volux\Dom;

use volux\Dom\Document;
/**
 * Class Form
 * @package volux\Dom
 * @author  Andrey Skulov <andrey.skulov@gmail.com>
 */
class Form extends Document
{
    const
        ELEMENT_CLASS = '\volux\Dom\Field',
        HEAD_HTML = ''
    ;

    /**
     * @var Element|Tag|Field
     */
    public $documentElement;
    /**
     * @var Element|Tag|Field
     */
    public $to;

    /**
     * @param string $version
     * @param string $encoding
     */
    public function __construct($version = self::VERSION, $encoding = self::ENCODING)
    {
        parent::__construct($version, $encoding);
        $this->root('form')->text('');
        $this->to(true);
    }

    /**
     * @param null|string $id
     *
     * @return string|Element|Field|Tag
     */
    public function id($id = null)
    {
        return $this->root()->id($id);
    }

    /**
     * @param bool|string $uri string or false
     *
     * @return $this|Form|Document
     */
    public function action($uri = false)
    {
        $this->root()->formAction($uri);
        return $this;
    }

    /**
     * @param string|bool $method 'get'|'post'|false
     *
     * @return $this|Form|Document
     */
    protected function method($method = 'get')
    {
        $this->root()->formMethod($method);
        return $this;
    }

    /**
     * @param string $name
     *
     * @return $this|Form|Document
     */
    public function name($name)
    {
        $this->root()->attr('name', $name);
        return $this;
    }

    /**
     * @param string|bool $target 'name'|'_self'|'_blank'|'_parent'|'_top'|false
     *
     * @return $this|Form|Document
     */
    public function target($target = '_self')
    {
        $this->root()->formTarget($target);
        return $this;
    }

    /**
     * @param bool $is
     *
     * @return $this|Form|Document
     */
    public function noValidate($is = true)
    {
        $this->root()->formNoValidate($is);
        return $this;
    }

    /**
     * @param string|bool $type 'app'|'multi'|'plain'|false
     *
     * @return $this|Form|Document
     */
    public function encodeType($type = 'app')
    {
        $this->root()->formEncodeType($type);
        return $this;
    }

    /**
     * @param string $charset
     *
     * @return $this|Form|Document
     */
    public function acceptCharset($charset = self::ENCODING)
    {
        $this->root()->attr('accept-charset', $charset);
        return $this;
    }

    /**
     * @param string|bool $is 'on'|'off'|false
     *
     * @return $this|Form|Document
     */
    public function autocomplete($is = 'on')
    {
        $this->root()->attr('autocomplete', $is);
        return $this;
    }

    /**
     * @param bool|Field|Tag|Element $root
     *
     * @return $this|Form|Document|Element|Field|Tag
     */
    public function to($root = false)
    {
        if (true === $root) {
            $this->to = $this->documentElement;
            return $this;
        } else
            if ($root instanceof Field) {
                $this->to = $root;
            }

        return $this->to;
    }

    /**
     * @param string $legend
     * @param bool|string $title
     *
     * @return Element|Tag|Field
     */
    public function fieldSet($legend, $title = false)
    {
        $this->to($this->root()->append('fieldset'))
            ->attr('title', $title)
            ->append('legend')
            ->text($legend)
        ;
        return $this->to;
    }

    /**
     * @param string|Element|Tag|Field $value string as well formed fragment
     * @param bool $asText
     *
     * @return Element|Tag|Field button
     */
    public function button($value, $asText = true)
    {
        $button = $this->to()
            ->append('button')->attr('type', 'button');
        if ($asText) {
            $button->text($value);
        } else {
            $button->append($value);
        }
        return $button;
    }

    /**
     * @param string|Element|Tag|Field $value string as well formed fragment
     * @param bool $asText
     *
     * @return Element|Tag|Field button
     */
    public function buttonReset($value, $asText = true)
    {
        $button = $this->to()
            ->append('button')->attr('type', 'reset');
        if ($asText) {
            $button->text($value);
        } else {
            $button->append($value);
        }
        return $button;
    }

    /**
     * @param string|Element|Tag|Field $value string as well formed fragment
     * @param bool $asText
     *
     * @return Element|Field|Tag button
     */
    public function buttonSubmit($value, $asText = true)
    {
        $button = $this->to()
            ->append('button')->attr('type', 'submit');
        if ($asText) {
            $button->text($value);
        } else {
            $button->append($value);
        }
        return $button;
    }

    /**
     * @param string $uri to image
     * @param bool|string $value string or false
     *
     * @return Element|Field|Tag input
     */
    public function inputImage($uri, $value = false)
    {
        $input = $this->to()->append('input');
        $input->attr('type', 'image')->attr('src', $uri)->val($value);
        return $input;
    }

    /**
     * @param string $value
     * @param string $type 'text'|'password'|'radio'|'checkbox'|'hidden'|'button'|'submit'|'reset'|'file'|'image'
     *
     * @return Element|Tag|Field input
     */
    public function input($value = '', $type = 'text')
    {
        $input = $this->to()->append('input');
        $input->attr('type', $type)->val($value);
        return $input;
    }

    /**
     * @param string $type
     * @param string $name
     * @param array $values
     * @param bool|string $default
     *
     * @return Element|Tag|Field last input in group
     */
    protected function inputGroup($type, $name, array $values, $default = false)
    {
        $input = $this->createElement('input');
        if (!$values) {
            return $this->to()
                ->append($input)
                ->attr('type', $type)
                ->label('Error: empty $values!', $type);
        }
        foreach ($values as $key => $value) {
            if ('radio' == $type) {
                $input = $this->input($key, $type)->name($name);
            } else {
                $input = $this->input('1', $type)->name($name.'_'.$key);
            }
            if ($value === $default) {
                $input->checked();
            }
            $input->label($value, $type);
        }
        return $input;
    }

    /**
     * @param string $name
     * @param array $values
     * @param bool|string $default
     *
     * @return Element|Tag|Field
     */
    public function checkbox($name, array $values, $default = false)
    {
        return $this->inputGroup('checkbox', $name, $values, $default);
    }

    /**
     * @param string $name
     * @param array $values
     * @param bool|string $default
     *
     * @return Element|Tag|Field
     */
    public function radio($name, array $values, $default = false)
    {
        return $this->inputGroup('radio', $name, $values, $default);
    }

    /**
     * @param string $class
     *
     * @return Element|Field|Tag
     */
    public function section($class = '')
    {
        $this->to($this->root()->append('div'))->addClass($class);
        return $this->to;
    }

    /**
     * @param array $values
     * @param bool|string $default string or false
     *
     * @return Element|Field|Tag select
     */
    public function select(array $values, $default = false)
    {
        $select = $this->to()->append('select');
        foreach ($values as $value => $label) {
            $option = $select->append('option')->val($value)->text($label);
            if ((string)$value == (string)$default) {
                $option->attr('selected', true);
            }
        }
        return $select;
    }

    /**
     * @param string $axis
     *
     * @return Element|Field|Tag
     */
    public function space($axis = '')
    {
        switch ($axis) {
            case 'h':
                $tag = '<span>&nbsp;</span>';
                break;
            case 'v':
                $tag = '<div><br/></div>';
                break;
            default:
                $tag = '<hr/>';
        }
        return $this->to()->append($tag);
    }

    /**
     * @param string $text
     * @param array $attr
     *
     * @return Element|Field|Tag textarea
     */
    public function textarea($text, $attr = array())
    {
        return $this->to()->append('textarea')->attr($attr)->val($text);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->saveXML($this->documentElement) . PHP_EOL;
    }
}
