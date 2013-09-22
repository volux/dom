<?php
/**
* volux\Dom
*
* @link http://github.com/volux/dom
*/
namespace volux\Dom;
/**
 * Class Document
 * @package volux\Dom
 * @author  Andrey Skulov <andrey.skulov@gmail.com>
 */
class Document extends \DOMDocument
{
    const
        NAME_NOT_MATCHED = 'not_matched',
        VERSION = '1.0',
        ENCODING = 'UTF-8',
        ELEMENT_CLASS = '\volux\Dom\Element',
        ATTR_CLASS = '\volux\Dom\Attr',
        FRAGMENT_CLASS = '\volux\Dom\Fragment',
        TEXT_CLASS = '\volux\Dom\Text',
        CDATA_CLASS = '\volux\Dom\Cdata',
        COMMENT_CLASS = '\volux\Dom\Comment',
        ID_ATTR = 'id',
        CLASS_ATTR = 'class',
        MATCH_ONCE_WORD = '`^\w+$`'
    ;

    /**
     * @var Element|Tag|Field
     */
    public $documentElement;
    /**
     * @var XPath
     */
    public $xPath;

    /**
     * @var bool|\Closure
     */
    public $debug = false;
    /**
     * @var Element|Tag|Field
     */
    protected $contextElement;

    public function __construct($version = self::VERSION, $encoding = self::ENCODING)
    {
        parent::__construct($version, $encoding);

        $this->registerClasses($this);

        $this->innerSetting();
    }

    protected function innerSetting()
    {
        $this->preserveWhiteSpace = true;
        $this->strictErrorChecking = false;
        $this->substituteEntities = true;
        $this->formatOutput = true;
        return $this;
    }

    /**
     * @param \DOMDocument $doc
     */
    protected function registerClasses(\DOMDocument $doc)
    {
        $doc->registerNodeClass('\DOMElement', static::ELEMENT_CLASS);
        $doc->registerNodeClass('\DOMAttr', static::ATTR_CLASS);
        $doc->registerNodeClass('\DOMText', static::TEXT_CLASS);
        $doc->registerNodeClass('\DOMCdataSection', static::CDATA_CLASS);
        $doc->registerNodeClass('\DOMComment', static::COMMENT_CLASS);
        #$doc->registerNodeClass('\DOMDocumentFragment', static::FRAGMENT_CLASS);
    }

    /**
     * @param $source
     *
     * @return string
     */
    public function convertEntities($source)
    {
        return mb_convert_encoding((string)$source, 'HTML-ENTITIES');
    }

    /**
     * @param string|File $source
     * @param int|null $options
     * @param bool     $result
     *
     * @return $this|Document
     */
    public function load($source, $options = LIBXML_NOCDATA, &$result = false)
    {
        $file = new File($source);
        return $this->loadXML($file->getContents(), $options, $result);
    }

    /**
     * @param string   $source
     * @param int|null $options
     * @param bool     $result
     *
     * @return $this|Document
     */
    public function loadXML($source, $options = LIBXML_NOCDATA, &$result = false)
    {
        $this->innerSetting();
        $this->recover = true;
        $result = parent::loadXML($this->convertEntities($source), $options);
        $this->xPath = null;
        return $this;
    }

    /**
     * @param string $source
     *
     * @return $this|Document
     */
    public function loadNsXML($source)
    {
        $file = new File($source);
        $xml = new \SimpleXMLElement($file->getContents());
        $ns = $xml->getDocNamespaces(true);
        $xml = dom_import_simplexml($xml);
        foreach ($ns as $prefix => $uri) {
            $xml->removeAttributeNS($uri, $prefix);
        }
        $this->append($xml)->rename($xml->localName);
        #$this->normalizeDocument();
        $this->xPath = null;
        return $this;
    }

    /**
     * @param string $source or to string convertible
     * @param bool   $result
     *
     * @return $this|Document
     */
    public function loadHTML($source, &$result = false)
    {
        libxml_use_internal_errors(true); /* ??? */
        $this->innerSetting();
        $this->recover = true;
        $result = parent::loadHTML($this->convertEntities($source));
        $this->xPath = null;
        return $this;
    }

    /**
     * @param string $filename
     * @param bool   $result
     *
     * @return $this|Document
     */
    public function loadHTMLFile($filename, &$result = false)
    {
        $file = new File($filename);
        return $this->loadHTML($file->getContents(), $options);
    }

    /**
     * @param \DOMNode $node
     *
     * @return string
     */
    public function saveHTML(\DOMNode $node = null)
    {
        if (!is_null($node)) {
            if (PHP_RELEASE_VERSION < 6) {
                $fix = self::doc();
                $fix->appendChild($fix->importNode($node, true));
                return rawurldecode($fix->saveHTML());
            }
            return rawurldecode(parent::saveHTML($node));
        }
        return rawurldecode(parent::saveHTML());
    }

    /**
     * @param string $version
     * @param string $encoding
     *
     * @return Document
     */
    public static function doc($version = self::VERSION, $encoding = self::ENCODING)
    {
        $dom = new self($version, $encoding);
        return $dom;
    }

    /**
     * @param $name
     *
     * @return Element|Tag|Field
     */
    public function root($name = null)
    {
        if (!is_null($name)) {
            if (is_null($this->documentElement)) {
                $this->append($name);
            } else {
                $this->documentElement->rename($name);
            }
        }
        if (is_null($this->documentElement)) {
            return $this->root('document');
        }
        return $this->documentElement;
    }

    /**
     * @param $child
     *
     * @return Element|Tag|Field
     */
    public function append($child)
    {
        if (!$this->documentElement) {
            return $this->appendChild($this->importNode($this->check($child)));
        }
        return $this->documentElement->appendChild($this->importNode($this->check($child)));
    }

    /**
     * @param $node Element
     * @return Element|Tag|Field
     */
    public function appendTo(Element $node)
    {
        return $node->append($this->root());
    }

    /**
     * @return XPath
     */
    public function xPath()
    {
        if (is_null($this->xPath)) {
            $this->xPath = new XPath($this);
        }
        return $this->xPath;
    }

    /**
     * @param \DOMNode|bool $node
     * @param null|string $expr
     *
     * @return Element|Tag|Field
     */
    public function notEmpty($node, $expr = null)
    {
        if (empty($node)) {
            $contextPath = $this->context()->getNodePath();
            if (!is_array($expr)) {
                $expr = $contextPath. '?' . $expr;
            } else {
                $expr = $contextPath. join('?', $expr);
            }
            $this->debug(self::NAME_NOT_MATCHED . ' ( ' . $expr . ' )');
            $node = $this->createElement(self::NAME_NOT_MATCHED, $expr);
        }
        return $node;
    }

    /**
     * @param      $expr
     * @param null $context
     *
     * @return mixed
     */
    public function evaluate($expr, $context = null)
    {
        return $this->xPath()->evaluate($expr, $context);
    }

    /**
     * @param string|array $expr
     * @param null $index
     * @param null $context
     *
     * @return Element|Tag|Field|Set
     */
    public function find($expr, $index = null, $context = null)
    {
        if (is_null($context)) {
            $context = $this->root();
            if (!is_array($expr) and preg_match(self::MATCH_ONCE_WORD, $expr)) {
                /**
                 * @todo test speed
                 */
                return $context->getElementsByTagName($expr, $index);
            }
        }
        $this->contextElement = $context;
        if (is_null($index)) {
            return $this->xPath()->query($expr, $context);
        }
        return $this->notEmpty($this->xPath()->query($expr, $context)->item($index), $expr);
    }

    /**
     * @param string $name
     * @param int $index
     *
     * @return Set
     */
    public function findByTag($name, $index = null)
    {
        return $this->getElementsByTagName($name, $index);
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
        return $this->notEmpty($this->set(parent::getElementsByTagName($name))->item($index), $name);
    }

    /**
     * @param string $id
     * @param bool $internal
     *
     * @return Element|Tag|Field
     */
    public function findById($id, $internal = true)
    {
        if ($internal) {
            return $this->getElementById($id);
        }
        return $this->find('#'.$id, 0);
    }

    /**
     * @param string $id
     *
     * @return \DOMElement|Element|Field|Tag
     */
    public function getElementById($id)
    {
        return $this->notEmpty(parent::getElementById($id), '#'.$id);
    }

    /**
     * @return Element|Tag|Field|$this|Document
     */
    public function context()
    {
        if ($this->contextElement) {
            return $this->contextElement;
        }
        return $this;
    }

    /**
     * @param string|callable $xslFile absolute o relative path to XSL file used include path
     * @param array $xsltParameters
     * @param null|\DOMNode|Set  $element
     *
     * @return Element|Tag|Field
     */
    public function xslt($xslFile, $xsltParameters = array(), &$element = null)
    {
        if (is_null($element)) {
            $element = $this->documentElement;
        }
        return Xslt::doc()->transform($xslFile, $xsltParameters, $element);
    }

    /**
     * @param array|\DOMNamedNodeMap|\DOMNodeList $list
     *
     * @return Set
     */
    public function set($list)
    {
        return new Set($list, $this);
    }

    /**
     * @param string|File $name node name or well formed xml fragment or text or File
     * @param null|string $value
     *
     * @return Element|Tag|Field
     */
    public function createElement($name, $value = null)
    {
        if (preg_match(self::MATCH_ONCE_WORD, $name)) {
            $node = parent::createElement($name);
            if (!is_null($value)) {
                $node->nodeValue = (string)$value;
            }
            return $node;
        }
        if (!$this->createFragment($name, $node)) {
            $node = $this->createText($name);
            if (!is_null($value)) {
                $node->append($value);
            }
        }
        return $node;
    }

    /**
     * @param string $xml possible well formed xml
     * @param $fragment
     *
     * @return bool
     */
    public function createFragment($xml, &$fragment)
    {
        /** @var Fragment $fragment */
        $fragment = $this->createDocumentFragment();

        $fix = self::doc()->loadHTML($xml)->find('body', 0);

        if ($xml{0} !== '<') {
            $xml = (string)$this->set($fix->childNodes)->getChildren();
        } else
        if ($fix->childNodes->length) {
            $xml = (string)$this->set($fix->childNodes);
        } else {
            $xml = (string)$fix;
        }
        return @$fragment->appendXML($xml);
    }

    /**
     * @param string $name attribute name
     * @param string $value
     *
     * @return Attr
     */
    public function createAttr($name, $value = '')
    {
        $attr = $this->createAttribute($name);
        $attr->nodeValue = $value;
        return $attr;
    }

    /**
     * @param string $string or convertible to string
     *
     * @return Text
     */
    public function createText($string)
    {
        return $this->createTextNode((string)$string);
    }

    /**
     * @param string $string or convertible to string
     *
     * @return Cdata
     */
    public function createCData($string)
    {
        return $this->createCDATASection((string)$string);
    }

    /**
     * @param string $string or convertible to string
     *
     * @return Comment
     */
    public function createComment($string)
    {
        return parent::createComment(' '.(string)$string.' ');
    }

    /**
     * @param string $uri
     * @param string $type
     *
     * @return $this|Document
     */
    public function stylesheet($uri, $type = 'xsl')
    {
        $this->appendChild($this->createProcessingInstruction('xml-stylesheet', 'type="text/' . $type . '" href="' . $uri . '"'));
        return $this;
    }

    /**
     * @param \DOMNode $importedNode
     * @param bool $deep
     *
     * @return Element|Tag|Field
     */
    public function importNode(\DOMNode $importedNode, $deep = true)
    {
        if ($this == $importedNode->ownerDocument) {
            return $importedNode;
        }
        return $this->notEmpty(parent::importNode($importedNode, $deep));
    }

    /**
     * Do not use incomplete method:
     * json_decode(json_encode((array)simplexml_import_dom($this)), true)
     *
     * @param \DOMNode $node
     *
     * @return array
     */
    public function toArray(\DOMNode $node = null)
    {
        if (is_null($node)) {
            $node = $this;
        }
        $result = array();

        if ($node->hasAttributes()) {
            foreach ($node->attributes as $attr) {
                $result[$node->nodeName]['@'][$attr->nodeName] = $attr->nodeValue;
            }
        }
        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $child) {
                /** @var $child Element|Tag|Attr|Text|Cdata|Comment */
                $result[$node->nodeName][] = $child->toArray();
            }
        } else {
            $result[$node->nodeName] = $node->nodeValue;
        }
        return $result;
    }

    /**
     * @param array $array
     *
     * @return Document|Element|Tag|Field
     */
    public function createFromArray(array $array)
    {
        return $this->createDomFromArray($array, self::doc());
    }

    /**
     * @param array $array
     * @param       $node
     *
     * @return Document|Element|Tag|Field
     */
    protected function createDomFromArray(array $array, $node)
    {
        foreach ($array as $key=>$value) {
            if (!is_integer($key)) {
                switch ($key) {
                    case '#document':
                        $key = 0;
                        break;

                    case '#text':
                        /** @var Element|Tag $node */
                        return $node->text($value, false);
                        break;

                    case '#comment':
                        /** @var Element|Tag $node */
                        $node = $node->appendChild($node->ownerDocument->createComment($value));
                        break;

                    case '#cdata-section':
                        /** @var Element|Tag $node */
                        $node = $node->appendChild($node->ownerDocument->createCData($value));
                        break;

                    case '@':
                        /** @var Element|Tag $node */
                        $node->attr($value);
                        $value = false;
                        $key = 0;
                        break;

                    default:
                        /** @var Element|Tag $node */
                        $node = $node->append($key);
                }
            }
            if (is_array($value)) {
                $node = $this->createDomFromArray($value, $node);
            }
            if (!is_integer($key)) {
                $node = $node->parent();
            }
        }
        return $node;
    }

    /**
     * @return string
     */
    protected function headString()
    {
        return '<?xml version="'.$this->xmlVersion.'" encoding="'.$this->xmlEncoding.'"?>';
    }

    /**
     * @param bool $format
     *
     * @return $this
     */
    public function format($format = true)
    {
        $this->formatOutput = $format;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->saveXML();
    }

    /**
     * @param $mixed
     */
    public function debug($mixed)
    {
        if ($this->debug) {
            if (is_callable($this->debug)) {
                call_user_func($this->debug, $mixed);
            } else {
                $this->context()->prepend($this->createComment(var_export($mixed, 1)));
            }
        }
        return $mixed;
    }

    /**
     * @todo resolve in check() Set or \DOMNodeList with importNode
     * @param string|Element|Field|Tag|Document|Html|Table|Form|File $data
     *
     * @return Element|Field|Tag
     */
    public function check($data)
    {
        if ($data instanceof File) {
            $data = $data->getContents();
        }
        if ($data instanceof self) {
            $data = $data->documentElement;
        }
        if (is_string($data)) {
            $data = $this->createElement($data);
        }
        return $data;
    }

}