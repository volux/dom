<?php
/**
 * volux\Dom
 *
 * @link http://github.com/volux/dom
 */
namespace volux\Dom;
/**
 * Class Tag
 * @package volux\Dom
 * @author  Andrey Skulov <andrey.skulov@gmail.com>
 */
class Tag extends Element
{
    const CLASS_ATTR = 'class';

    /**
     * @param string $name data key
     * @param null|string|array|object $value
     *
     * @return mixed|Element|Tag|Field
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
     * @return string|Element|Tag|Field
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
     * @param string|null $id
     *
     * @return string|Element|Tag|Field
     */
    public function id($id = null)
    {
        if (is_null($id)) {
            return $this->attr('id')->text();
        }
        return $this->attr('id', $id);
    }

    /**
     * @param string $classes separated with space
     *
     * @return Element|Tag|Field
     */
    public function addClass($classes)
    {
        return $this->attrItems(self::CLASS_ATTR, $classes, true);
    }

    /**
     * @param bool|string $classes separated with space
     *
     * @return Element|Tag|Field
     */
    public function removeClass($classes = false)
    {
        return $this->attrItems(self::CLASS_ATTR, $classes, false);
    }

    /**
     * @param null|string $html well formed fragment
     *
     * @return string|Element|Tag|Field
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
     * @param string $html well formed html
     *
     * @return $this|Element|Tag|Field
     */
    public function replaceWith($html)
    {
        if ($this->doc()->createFragment($html, $fragment)) {
            return $this->replace($fragment);
        }
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->ownerDocument->saveHTML($this);
    }
}
