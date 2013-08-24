<?php
/**
 * volux\Dom
 *
 * @link http://github.com/volux/dom
 */
namespace volux\Dom;
/**
 * Class Element
 * @package volux\Dom
 * @author  Andrey Skulov <andrey.skulov@gmail.com>
 */
class Element extends \DOMElement
{
    /**
     * @param null|string $xml
     *
     * @return string|Element|Tag|Field
     */
    public function xml($xml = null)
    {
        if (!is_null($xml)) {
            $this->clear();
            return $this->append($xml);
        }
        return (string)$this->children();
    }

    /**
     * @return Document|Html|Table|Form
     */
    public function doc()
    {
        return $this->ownerDocument;
    }

    /**
     * @param string $expr
     *
     * @return bool
     */
    public function is($expr)
    {
        return !$this->doc()->find(array($expr, $this->getNodePath()))->isEmpty();
    }

    /**
     * @param string|array $expr name of child node or attribute
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
     * @param string|Element|Tag|Field $child
     *
     * @return Element|Tag|Field
     */
    public function append($child)
    {
        if (is_string($child)) {
            $child = $this->doc()->createElement($child);
        }
        if ($child instanceof Document) {
            $child = $child->documentElement;
        }
        return $this->appendChild($this->importNode($child));
    }

    /**
     * @param string|Element|Tag|Field|\DOMNode $target css selector or Element
     *
     * @return Element|Tag|Field
     */
    public function appendTo($target)
    {
        static $find = array();
        if (is_string($target)) {
            if (!isset($find[$target])) {
                $find[$target] = $this->doc()->find($target, 0);
            }
            $target = $find[$target];
        }
        /** @var $target Element|Tag|Field */
        return $target->appendChild($target->ownerDocument->importNode($this, true));
    }

    /**
     * @param string|Element|Tag|Field $child
     *
     * @return Element|Tag|Field
     */
    public function prepend($child)
    {
        if (is_string($child)) {
            $child = $this->doc()->createElement($child);
        }
        return $this->insertBefore($this->importNode($child), $this->firstChild);
    }

    /**
     * @param string|Element|Tag|Field|\DOMNode $target css selector or Element
     *
     * @return Element|Tag|Field
     */
    public function prependTo($target)
    {
        static $find = array();
        if (is_string($target)) {
            if (!isset($find[$target])) {
                $find[$target] = $this->doc()->find($target, 0);
            }
            $target = $find[$target];
        }
        /** @var $target Element|Tag|Field */
        return $target->insertBefore($target->ownerDocument->importNode($this, true), $target->firstChild);
    }

    /**
     * @param null|string $selector
     *
     * @return Element|Text|Tag|Field|Set
     */
    public function next($selector = null)
    {
        if (is_null($selector)) {
            return $this->nextSibling;
        }
        return $this->find(array($selector, 'following-sibling::*'));
    }

    /**
     * @param null|string $selector
     *
     * @return Element|Text|Tag|Field|Set
     */
    public function prev($selector = null)
    {
        if (is_null($selector)) {
            return $this->previousSibling;
        }
        return $this->find(array($selector, 'preceding-sibling::*'));
    }

    /**
     * @param null|string $sibling
     *
     * @return Element|Tag|Field
     */
    public function after($sibling = null)
    {
        $next = $this->nextSibling;
        if (is_null($sibling)) {
            if ($next) {
                return $next;
            }
            return $this;
        }
        if (is_string($sibling)) {
            $sibling = $this->doc()->createElement($sibling);
        }
        if (!$next) {
            return $this->parentNode->appendChild($this->importNode($sibling));
        }
        return $this->parentNode->insertBefore($this->importNode($sibling), $next);
    }

    /**
     * @param null|string $sibling
     *
     * @return Element|Tag|Field
     */
    public function before($sibling = null)
    {
        $prev = $this->previousSibling;
        if (is_null($sibling)) {
            if ($prev) {
                return $prev;
            }
            return $this;
        }
        if (is_string($sibling)) {
            $sibling = $this->doc()->createElement($sibling);
        }
        return $this->parentNode->insertBefore($this->importNode($sibling), $this);
    }

    /**
     * @param \DOMNode|Element|Tag $newNode
     *
     * @return Element|Tag|Field
     */
    public function replace(\DOMNode $newNode)
    {
        $this->parentNode->replaceChild($this->importNode($newNode), $this);
        return $newNode;
    }

    /**
     * @param string $xslFile absolute o relative path to XSL file used include path
     * @param array $xsltParameters
     *
     * @return $this|Element|Tag|Field
     */
    public function xslt($xslFile, $xsltParameters = array())
    {
        return $this->doc()->xslt($xslFile, $xsltParameters, $this);
    }

    /**
     * @param string|Element|Tag $wrapper
     *
     * @return Element|Tag|Field
     */
    public function wrap($wrapper)
    {
        if (is_string($wrapper)) {
            $wrapper = $this->doc()->createElement($wrapper);
        }
        $parent = $this->parentNode;
        $wrapper = $parent->appendChild($wrapper);
        $wrapper->appendChild($this->cloneNode(true));
        $parent->replaceChild($wrapper, $this);
        return $wrapper;
    }

    /**
     * @param int|null $to
     *
     * @return Element|Tag|Field
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
     * @param bool $returnSwap
     *
     * @return Element|Tag|Field
     */
    public function up($returnSwap = false)
    {
        /** @var $node Element */
        $node = $this->before()->before($this);
        if ($returnSwap) {
            return $node->nextSibling;
        }
        return $node;
    }

    /**
     * @param bool $returnSwap
     *
     * @return Element|Tag|Field
     */
    public function down($returnSwap = false)
    {
        /** @var $node Element */
        $node = $this->after()->after($this);
        if ($returnSwap) {
            return $node->previousSibling;
        }
        return $node;
    }

    /**
     * @return Element|Tag|Field|Document
     */
    public function parent()
    {
        return $this->parentNode;
    }

    /**
     * @return Set
     */
    public function parents()
    {
        return $this->find(array('', 'ancestor::*'));
    }

    /**
     * @param string $expr css selector
     *
     * @return Element|Tag|Field
     */
    public function closest($expr)
    {
        return $this->find(array($expr, 'ancestor::*'))->last();
    }

    /**
     * @param bool $asXPath
     *
     * @return string|Element|Tag|Field
     */
    public function path($asXPath = false)
    {
        if ($asXPath) {
            return $this->getNodePath();
        }
        if ($this->isEmpty()) {
            return $this;
        }
        $path = $this->doc()->createElement('path');
        $parents = $this->parents();
        while ($parent = $parents->sequent()) {
            $path->append($parent->copy(false));
        }
        $path->append($this->copy(false));
        return $path;
    }

    /**
     * @return Element|Tag|Text|Cdata|Comment|Field
     */
    public function end()
    {
        return $this->doc()->context();
    }

    /**
     * @return Element|Tag|Field
     */
    public function remove()
    {
        return $this->parentNode->removeChild($this);
    }

    /**
     * @param string|null $selector css selector or n or "[n]"
     *
     * @return Set|Element|Tag|Text|Cdata|Comment|Field
     */
    public function children($selector = null)
    {
        if (is_null($selector)) {
            return $this->doc()->set($this->childNodes);
        }
        if (is_numeric($selector)) {
            return $this->childNodes->item((int)$selector);
        }
        return $this->find(array($selector, 'child::*'));
    }

    /**
     * @param null|string $selector css selector
     *
     * @return Set|Element|Tag|Text|Cdata|Comment|Field
     */
    public function siblings($selector = null)
    {
        return $this->parent()->children($selector);
    }

    /**
     * @return Set
     */
    public function attributes()
    {
        return $this->doc()->set($this->attributes);
    }

    /**
     * empty - reserved word
     * @return Element|Tag|Field
     */
    public function clear()
    {
        $this->children()->remove();
        return $this;
    }

    /**
     * @param string|array|Set $name
     * @param null|string|bool $value
     *
     * @return $this|Element|Tag|Field|Attr
     */
    public function attr($name, $value = null)
    {
        if ($name instanceof Set) {
            foreach ($name as $a) {
                /** @var $a Attr */
                $this->setAttributeNode($a);
                if (Document::ID_ATTR === $a->name) {
                    $this->setIdAttributeNode($a, true);
                }
            }
            return $this;
        }
        if (!is_null($value)) {
            if (false === $value) {
                $this->removeAttribute($name);
            } else
                if (true === $value) {
                    $this->setAttribute($name, $name);
                } else {
                    $this->setAttribute($name, $value);
                    if (Document::ID_ATTR === $name) {
                        $this->setIdAttribute($name, true);
                    }
                }
            return $this;
        }
        if (is_array($name)) {
            foreach ($name as $attr=>$val) {
                $this->attr($attr, $val);
            }
            return $this;
        }
        $attr = $this->getAttributeNode($name);
        if (!$attr) {
            $attr = $this->doc()->createAttr($name, null);
        }
        return $attr;
    }

    /**
     * @param string $attr name
     * @param string $items space separated
     * @param bool $add if true, false - remove
     *
     * @return $this|Element|Tag|Field
     */
    protected function attrItems($attr, $items, $add)
    {
        if ($items and $this->hasAttribute($attr)) {
            $exists = array_filter(explode(' ', $this->attr($attr)));
            $items = array_filter(explode(' ', $items));
            sort($exists);
            sort($items);
            if ($add) {
                $items = implode(' ', array_unique(array_merge($exists, $items)));
            } else {
                $items = implode(' ', array_diff($exists, $items));
            }
        }
        return $this->attr($attr, ($items)? $items : false);
    }

    /**
     * @param null|string $name
     *
     * @return string|Element|Tag|Field
     */
    public function name($name = null)
    {
        if (!empty($name)) {
            return $this->rename($name);
        }
        return $this->nodeName;
    }

    /**
     * @param string $name
     * @param bool   $native
     * @param string $ns
     *
     * @return $this|Element|Tag|Field
     */
    public function rename($name, $native = true, $ns = '')
    {
        if (!empty($name)) {
            if ($native) {
                $this->doc()->renameNode($this, $ns, $name);
                return $this;
            }
            $name = (string)$name;
            $old = $this->copy();
            $new = $this->doc()->createElement($name);
            $new->attr($old->attributes());
            $children = $old->children();
            foreach ($children as $child) {
                $new->append($child);
            }
            $this->parentNode->replaceChild($new, $this);
            return $new;
        }
        return $this;
    }

    /**
     * @param bool $deep
     *
     * @return Element|Tag|Field
     */
    public function copy($deep = true)
    {
        return $this->cloneNode($deep);
    }

    /**
     * @param null|string $newText
     * @param bool $replace
     *
     * @return $this|Element|Tag|Field|Set
     */
    public function text($newText = null, $replace = false)
    {
        if (!is_null($newText)) {
            $newTextNode = $this->doc()->createText($newText);
            if ($replace) {
                $this->clear();
            }
            $this->appendChild($newTextNode);
            return $this;
        }
        return $this->find(array('/text()', '.'));
    }

    /**
     * @param string|array $expr css selector or xPath
     * @param null|int $index
     * @param null|Element|Tag $context
     *
     * @return Element|Tag|Field|Attr|Text|Cdata|Comment|Set
     */
    public function find($expr, $index = null, $context = null)
    {
        if (is_null($context)) {
            $context = $this;
        }
        return $this->doc()->find($expr, $index, $context);
    }

    /**
     * @param string $source filename or uri
     * @param callable|null $callback
     *
     * @return $this|Element|Tag|Field
     */
    public function load($source, $callback = null)
    {
        if (is_file($source)) {
            $source = file_get_contents($source, FILE_USE_INCLUDE_PATH);
        }
        if ($source) {
            $appended = $this->append($source);
            if (is_callable($callback)) {
                $callback($appended);
            }
        }
        return $this;
    }

    /**
     * Shortcut to append and fill
     *
     * @param string $name node name
     * @param array $attr
     * @param string $text
     *
     * @return $this|Element|Tag|Field
     */
    public function a($name, array $attr = array(), $text = '')
    {
        if (empty($name)) {
            return $this;
        }
        return $this->appendChild($this->doc()->createElement($name)->attr($attr)->text($text));
    }

    /**
     * Shortcut to prepend and fill
     *
     * @param string $name node name
     * @param array $attr
     * @param string $text
     *
     * @return $this|Element|Tag|Field
     */
    public function p($name, array $attr = array(), $text = '')
    {
        if (empty($name)) {
            return $this;
        }
        return $this->insertBefore($this->doc()->createElement($name)->attr($attr)->text($text), $this->firstChild);
    }

    /**
     * Helper for append list of nodes
     *
     * @example for simple list (<li>, <ol> etc.): array('foo', 'bar', ...)
     * @example for <img>: array(array('src'=>'foo'), ...)
     * @example for <input>: array(array('value'=>'bar', 'type'=>'hidden'), ...)
     * @example for <a>: array(array('a-text' => array('href'=>'bar', 'class'=>'a-class')), ...)
     * @example for <option>: array(array('option-text' => array('value'=>'foo', 'selected'=>'selected')), ...)
     *
     * @param string $name
     * @param array $fill
     *
     * @return $this|Element|Tag|Field
     */
    public function l($name, array $fill)
    {
        foreach ($fill as $items) {
            if (!is_array($items)) {
                $this->a($name, array(), $items);
            } else {
                if (isset($items['>'])) {
                    $sub = array_shift($items);
                    $text = array_shift($items);
                    $this->a($name)->a($sub, $items, $text);
                } else {
                    foreach ($items as $text=>$attr) {
                        if (empty($attr)) {
                            $attr = array();
                        }
                        if (is_integer($text)) {
                            $text = false;
                        }
                        $this->a($name, $attr, $text);
                    }
                }
            }
        }
        return $this;
    }

    /**
     * @param \DOMNode $newNode
     *
     * @return Element|Tag|Field
     */
    protected function importNode($newNode)
    {
        return $this->doc()->importNode($newNode);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->doc()->toArray($this);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->ownerDocument->saveXML($this);
    }
}
