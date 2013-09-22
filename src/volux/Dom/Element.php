<?php
/**
* volux\Dom
*
* @link http://github.com/volux/dom
*/
namespace volux\Dom;

use volux\Dom;
/**
 * Class Element
 * @package volux\Dom
 * @author  Andrey Skulov <andrey.skulov@gmail.com>
 */
class Element extends \DOMElement
{
    /** @var Document|Html|Table|Form */
    public $ownerDocument;

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
        return !$this->ownerDocument->find(array($this->getNodePath().'/self::', $expr))->isEmpty();
    }

    /**
     * @param string|array $expr name of child node or attribute
     *
     * @return bool
     */
    public function has($expr)
    {
        return !$this->find(array('/descendant-or-self::', $expr))->isEmpty();
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return (Document::NAME_NOT_MATCHED === $this->name());
    }

    /**
     * @todo override appendChild() with importNode()
     * @param string|Element|Tag|Field $child
     *
     * @return Element|Tag|Field
     */
    public function append($child)
    {
        $child = $this->ownerDocument->check($child);
        if ($child instanceof Set) {
            foreach ($child as $node) {
                $this->appendChild($this->ownerDocument->importNode($node));
            }
            return $this;
        }
        return $this->appendChild($this->ownerDocument->importNode($child));
    }

    /**
     * @param Element|Tag|Field $target css selector or Element
     *
     * @return \DOMNode|Element|Tag|Field
     */
    public function appendTo(Element $target)
    {
        return $target->appendChild($target->ownerDocument->importNode($this, true));
    }

    /**
     * @todo override insertBefore() with importNode()
     * @param string|Element|Tag|Field $child
     *
     * @return Element|Tag|Field
     */
    public function prepend($child)
    {
        $child = $this->ownerDocument->check($child);
        if ($child instanceof Set) {
            foreach ($child as $node) {
                $this->insertBefore($this->ownerDocument->importNode($node), $this->firstChild);
            }
            return $this;
        }
        return $this->insertBefore($this->ownerDocument->importNode($child), $this->firstChild);
    }

    /**
     * @param string|Element|Tag|Field|\DOMNode $target css selector or Element
     *
     * @return Element|Tag|Field
     */
    public function prependTo($target)
    {
        return $target->insertBefore($target->ownerDocument->importNode($this, true), $target->firstChild);
    }

    /**
     * @param null|string $selector
     *
     * @return \DOMElement|Attr|Cdata|Comment|Element|Field|Set|Tag|Text
     */
    public function next($selector = null)
    {
        if (is_null($selector)) {
            return $this->nextSibling;
        }
        return $this->find(array('following-sibling::', $selector), 0);
    }

    /**
     * @param null|string $selector
     *
     * @return Set
     */
    public function nextAll($selector = '*')
    {
        return $this->find(array('following-sibling::', $selector));
    }

    /**
     * @param null $selector
     *
     * @return \DOMElement|Attr|Cdata|Comment|Element|Field|Set|Tag|Text
     */
    public function prev($selector = null)
    {
        if (is_null($selector)) {
            return $this->previousSibling;
        }
        return $this->find(array('preceding-sibling::', $selector))->last();
    }

    /**
     * @param null|string $selector
     *
     * @return Set
     */
    public function prevAll($selector = '*')
    {
        return $this->find(array('preceding-sibling::', $selector));
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
        if (!$next) {
            return $this->parent()->appendChild($this->ownerDocument->importNode($this->ownerDocument->check($sibling)));
        }
        return $this->parent()->insertBefore($this->ownerDocument->importNode($this->ownerDocument->check($sibling)), $next);
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
        return $this->parent()->insertBefore($this->ownerDocument->importNode($this->ownerDocument->check($sibling)), $this);
    }

    /**
     * @param \DOMNode|Element|Tag|Field|string $newNode
     * @param \DOMNode|Element|Tag|null|boolean $oldNode
     *
     * @return Element|Tag|Field
     */
    public function replace($newNode, &$oldNode = null)
    {
        $newNode = $this->before($newNode);
        $oldNode = $this->parent()->replaceChild($newNode, $this);
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
        return $this->ownerDocument->xslt($xslFile, $xsltParameters, $this);
    }

    /**
     * @param string|Element|Tag $wrapper
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
     * @param int|null $to
     *
     * @return Element|Tag|Field
     */
    public function position($to = null)
    {
        if (is_null($to)) {
            return $this->find(array('preceding-sibling::', '*'))->count() + 1;
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
        if ($this->parentNode instanceof \DOMNode) {
            return $this->parentNode;
        }
        $this->ownerDocument->debug($this);
        $parent = $this->ownerDocument->createElement('fix_parent');
        $parent->appendChild($this);
        return $parent;
    }

    /**
     * @return Set
     */
    public function parents()
    {
        return $this->find(array('ancestor::', '*'));
    }

    /**
     * @param string $expr css selector
     *
     * @return Element|Tag|Field
     */
    public function closest($expr)
    {
        return $this->find(array('ancestor::', $expr))->last();
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
        $path = $this->ownerDocument->createElement('path');
        $this->parents()->andSelf()->each(function(Element $element) use (&$path) {
            $path->append($element->copy(false));
        });
        return $path;
    }

    /**
     * @return Element|Tag|Text|Cdata|Comment|Field
     */
    public function end()
    {
        return $this->ownerDocument->context();
    }

    /**
     * @return Element|Tag|Field
     */
    public function remove()
    {
        $parent = $this->parent();
        return $parent->removeChild($this);
    }

    /**
     * @param string|null $selector css selector or n or "[n]"
     *
     * @return Set|Element|Tag|Text|Cdata|Comment|Field
     */
    public function children($selector = null)
    {
        if (is_null($selector)) {
            return $this->set($this->childNodes);
        }
        if (is_numeric($selector)) {
            return $this->childNodes->item((int)$selector);
        }
        return $this->find(array('child::', $selector));
    }

    /**
     * @return Element|Tag|Text|Cdata|Comment|Field
     */
    public function firstChild()
    {
        return $this->firstChild;
    }

    /**
     * @return Element|Tag|Text|Cdata|Comment|Field
     */
    public function lastChild()
    {
        return $this->lastChild;
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
        return $this->set($this->attributes);
    }

    /**
     * empty - reserved word
     * @return Element|Tag|Field
     */
    public function clear()
    {
        $this->nodeValue = '';
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
        if ($name instanceof Attr) {
            $this->setAttributeNode($name);
            if (Document::ID_ATTR === $name->name) {
                $this->setIdAttributeNode($name, true);
            }
            return $this;
        }
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
                    $this->setAttribute($name, (string)$value);
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
            $attr = $this->ownerDocument->createAttr($name);
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
     * @param string $classes separated with space
     *
     * @return Element|Tag|Field
     */
    public function addClass($classes)
    {
        return $this->attrItems(Document::CLASS_ATTR, $classes, true);
    }

    /**
     * @param bool|string $classes separated with space
     *
     * @return Element|Tag|Field
     */
    public function removeClass($classes = false)
    {
        return $this->attrItems(Document::CLASS_ATTR, $classes, false);
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
        return $this->localName;
    }

    /**
     * @param string $name
     * @param string $ns
     * @param bool   $native
     *
     * @return $this|Element|Tag|Field
     */
    public function rename($name, $ns = '', $native = false)
    {
        if (!empty($name)) {
            if ($native) {
                /** not implement yet */
                $this->ownerDocument->renameNode($this, $ns, $name);
                return $this;
            }
            $name = (string)$name;

            $old = $this->copy();
            $new = $this->ownerDocument->createElement($name);
            $new->attr($old->attributes());
            $children = $old->children();
            foreach ($children as $child) {
                $new->append($child);
            }
            $this->parent()->replaceChild($new, $this);
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
     * @return $this|Element|Tag|Field|Set|Text
     */
    public function text($newText = null, $replace = true)
    {
        if (!is_null($newText)) {
            if ($replace) {
                $this->children()->remove();
                if ($newText instanceof Set) {
                    foreach ($newText as $node) {
                        $this->nodeValue.= $node->nodeValue;;
                    }
                    return $this;
                }
                if ($newText instanceof \DOMNode) {
                    $this->nodeValue = $newText->nodeValue;
                    return $this;
                }
                $this->nodeValue = (string)$newText;
                return $this;
            }
            if ($newText instanceof Set) {
                foreach ($newText as $entry) {
                    /** @var $entry Element|Attr|Text|Cdata|Comment */
                    $this->text($entry->text());
                }
                return $this;
            }
            if ($newText instanceof \DOMNode) {
                $newTextNode = $this->ownerDocument->createText($newText->nodeValue);
            } else {
                $newTextNode = $this->ownerDocument->createText($newText);
            }
            $this->appendChild($newTextNode);
            return $this;
        }
        return $this->nodeValue;
    }

    /**
     * @return Set|Text
     */
    public function content()
    {
        return $this->find(array('self::', 'text()'));
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
            if (!is_array($expr) and preg_match(Document::MATCH_ONCE_WORD, $expr)) {
                /**
                 * @todo test speed
                 */
                return $context->getElementsByTagName($expr, $index);
            }
        }
        return $this->ownerDocument->find($expr, $index, $context);
    }

    /**
     * @param $expr
     *
     * @return mixed
     */
    public function evaluate($expr)
    {
        return $this->ownerDocument->evaluate($expr, $this);
    }

    /**
     * @param string $name
     * @param int    $index
     *
     * @return Set
     */
    public function getElementsByTagName($name, $index = null)
    {
        if (is_null($index)) {
            return $this->set(parent::getElementsByTagName($name));
        }
        return $this->ownerDocument->notEmpty($this->set(parent::getElementsByTagName($name))->item($index), $name);
    }

    /**
     * @param string $source filename or uri
     * @param callable|null $callback
     *
     * @return Element|Tag|Field
     */
    public function load($source, $callback = null)
    {
        $file = new File($source);
        $appended = $this->append($file);
        if (is_callable($callback)) {
            $callback($appended);
        }
        return $appended;
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
        return $this->appendChild($this->ownerDocument->createElement($name)->attr($attr)->text($text));
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
        return $this->insertBefore($this->ownerDocument->createElement($name)->attr($attr)->text($text), $this->firstChild);
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
     * @param array|\DOMNamedNodeMap|\DOMNodeList $list
     *
     * @return Set
     */
    public function set($list)
    {
        return new Set($list, $this->ownerDocument);
    }

    public function in(Set $set)
    {

    }
    /**
     * @return array
     */
    public function toArray()
    {
        return $this->ownerDocument->toArray($this);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->ownerDocument->saveXML($this);
    }
}