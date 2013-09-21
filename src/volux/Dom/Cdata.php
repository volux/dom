<?php
/**
 * volux\Dom
 *
 * @link http://github.com/volux/dom
 */
namespace volux\Dom;

use volux\Dom;
/**
 * Class Cdata
 * @package volux\Dom
 * @author  Andrey Skulov <andrey.skulov@gmail.com>
 */
class Cdata extends \DOMCdataSection
{
    /** @var Document|Html|Table|Form */
    public $ownerDocument;

    /**
     * @param string $expr css selector
     *
     * @return bool
     */
    public function is($expr)
    {
        return !$this->ownerDocument->find(array($this->getNodePath(), $expr))->isEmpty();
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
        return (Document::NAME_NOT_MATCHED === $this->name());
    }

    /**
     * @return bool
     */
    public function isClean()
    {
        return ('' === $this->nodeValue);
    }

    /**
     * @return Document|Html|Table|Form
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
        if ($this->parentNode instanceof \DOMNode) {
            return $this->parentNode;
        }
        $this->ownerDocument->debug($this);
        $parent = $this->ownerDocument->createElement('fix_parent');
        $parent->appendChild($this);
        return $parent;
    }

    /**
     * @return Element|Tag|Field
     */
    public function end()
    {
        return $this->ownerDocument->context();
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
     * @return Element|Tag|Field
     */
    public function remove()
    {
        return $this->parent()->removeChild($this);
    }

    /**
     * @param string|array $expr css selector (if in array: with axis)
     * @param null|int $index
     * @param null|\DOMNode|Element|Tag|Field|Attr|Text|Cdata $context
     *
     * @return Element|Tag|Field|Attr|Text|Set
     */
    public function find($expr, $index = null, $context = null)
    {
        if (is_null($context)) {
            $context = $this;
        }
        return $this->ownerDocument->find($expr, $index, $context);
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
     * @param int|null $to
     *
     * @return int|$this|Cdata
     */
    public function position($to = null)
    {
        if (is_null($to)) {
            return $this->find(array('preceding-sibling::', '*'))->count() + 1;
        }
        if ($this->siblings()->count() < $to) {
            return $this;
        }
        $this->siblings($to + 1)->before($this);
        return $this;
    }

    /**
     * @param null|string $newText
     * @param bool $replace
     *
     * @return $this|Cdata|string
     */
    public function text($newText = null, $replace = true)
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
        $next = $this->nextSibling;
        if (!$next) {
            return $this->parent()->appendChild($this->ownerDocument->importNode($this->ownerDocument->check($element)));
        }
        return $this->parent()->insertBefore($this->ownerDocument->importNode($this->ownerDocument->check($element)), $next);
    }

    /**
     * @param $element
     *
     * @return Element|Field|Tag
     */
    public function before($element)
    {
        return $this->parent()->insertBefore($this->ownerDocument->importNode($this->ownerDocument->check($element)), $this);
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
        $parent = $this->parent();
        $wrapper = $parent->append($wrapper);
        $wrapper->appendChild($this->cloneNode(true));
        $parent->replaceChild($wrapper, $this);
        return $wrapper;
    }

    /**
     * @param null|string $charList
     *
     * @return $this
     */
    public function trim($charList = null)
    {
        $this->nodeValue = trim($this->nodeValue, $charList);
        return $this;
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