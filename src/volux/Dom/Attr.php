<?php
/**
 * volux\Dom
 *
 * @link http://github.com/volux/dom
 */
namespace volux\Dom;

/**
 * Class Attr
 * @package volux\Dom
 * @author  Andrey Skulov <andrey.skulov@gmail.com>
 */
class Attr extends \DOMAttr
{

    /**
     * @param string $expr attr name or 'value' or part of value '~[ ]part'
     *
     * @return bool
     */
    public function is($expr)
    {
        if ($expr{0} == '@') {
            return ($expr === '@'.$this->nodeName);
        }
        if ($expr{0} == '~') {
            return !is_bool(strpos($this->nodeName, trim(substr($expr, 1))));
        }
        return ($expr === $this->nodeValue);
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return (Document::NAME_NOT_MATCHED === $this->nodeName) or ($this->nodeValue === '');
    }

    /**
     * @return Document|Html|Table|Form
     */
    protected function doc()
    {
        return $this->ownerElement->ownerDocument;
    }

    /**
     * @return Element|Tag|Field
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
     * @return Element|Tag|Field
     */
    public function remove()
    {
        $parent = $this->parent();
        $parent->removeAttributeNode($this);
        return $parent;
    }

    /**
     * @param null|string $newText
     * @param bool $replace
     *
     * @return Attr|string
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
