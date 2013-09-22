<?php
/**
* volux\Dom
*
* @link http://github.com/volux/dom
*/
namespace volux\Dom;

use volux\Dom;
/**
 * Class Xslt
 * @package volux\Dom
 * @author  Andrey Skulov <andrey.skulov@gmail.com>
 */
class Xslt extends Document
{

    /**
     * @var \XSLTProcessor
     */
    protected $processor;

    /**
     * @param string $version
     * @param string $encoding
     *
     * @return Xslt
     */
    public static function doc($version = self::VERSION, $encoding = self::ENCODING)
    {
        $xslt = new self($version, $encoding);
        return $xslt;
    }

    /**
     * @param string   $source
     * @param int|null $options
     * @param bool     $result
     *
     * @return $this|Document|Xslt
     */
    public function loadXML($source, $options = LIBXML_NOCDATA, &$result = false)
    {
        parent::loadXML($source, $options, $result);
        $this->processor();
        return $this;
    }

    /**
     * @return \XSLTProcessor
     */
    protected function processor()
    {
        if (!$this->processor) {
            $this->processor = new \XSLTProcessor;
            if ('php' == $this->root()->attr('exclude-result-prefixes')) {
                $this->processor->registerPHPFunctions();
            }
            $this->processor->importStyleSheet($this);
        }
        return $this->processor;
    }

    /**
     * @param string $xslFile
     * @param array  $xsltParameters
     * @param Element|Tag|Field|Set $element
     * @param string $ns namespace
     *
     * @return \DOMDocument
     */
    public function transform($xslFile, $xsltParameters = array(), &$element, $ns = '')
    {
        $this->load($xslFile, LIBXML_NOCDATA, $result);
        if (!$result) {
            return $element;
        }
        foreach ($xsltParameters as $name => $value) {
            $this->processor->setParameter($ns, $name, $value);
        }
        if ($element instanceof Set) {
            $processor = $this->processor;
            $element->each(function(Element $el) use ($processor, $xslFile) {
                $result = $processor->transformToDoc($el);
                if (!$result) {
                    $el->after($el->ownerDocument->createComment('not transformed with '.$xslFile));
                } else {
                    $el->replace($result->documentElement, $el);
                }
            });
            return $element;
        }
        $result = $this->processor->transformToDoc($element);
        if (!$result) {
            $element->after($element->ownerDocument->createComment('not transformed with '.$xslFile));
        }
        $element->replace($result->documentElement, $element);
        return $element;
    }
}