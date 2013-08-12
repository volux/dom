<?php
/**
 * volux\Dom
 *
 * @link http://github.com/volux/dom
 */
namespace volux;

{

    /**
     * Class Dom
     * @package volux\Dom
     * @author  Andrey Skulov <andrey.skulov@gmail.com>
     */
    class Dom extends \DOMDocument
    {
        const
            NAME_NOT_MATCHED = 'not.matched',
            VERSION = '1.0',
            ENCODING = 'UTF-8',
            ELEMENT_CLASS = '\volux\Dom\Element',
            ATTR_CLASS = '\volux\Dom\Attr',
            TEXT_CLASS = '\volux\Dom\Text',
            CDATA_CLASS = '\volux\Dom\Cdata',
            COMMENT_CLASS = '\volux\Dom\Comment',
            ID_ATTR = 'id'
        ;

        /**
         * @var Dom\Element|Dom\Tag|Dom\Field
         */
        public $documentElement;
        /**
         * @var Dom\XPath
         */
        public $xPath;
        /**
         * @var Dom\Element|Dom\Tag|Dom\Field
         */
        protected $contextElement;

        protected $ns;

        public function __construct($version = self::VERSION, $encoding = self::ENCODING)
        {
            parent::__construct($version, $encoding);

            $this->registerClasses($this);

            $this->preserveWhiteSpace = false;
            $this->strictErrorChecking = false;
            $this->substituteEntities = true;
            $this->formatOutput = true;
            $this->setXPath($this);
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
        }

        /**
         * @param null $namespaceURI
         * @param null $qualifiedName
         * @param null $docType
         *
         * @return \DOMDocument
         */
        public function implementation($namespaceURI = null, $qualifiedName = null, $docType = null)
        {
            $imp = new \DOMImplementation();
            $doc = $imp->createDocument($namespaceURI, $qualifiedName, $imp->createDocumentType($docType));

            $this->registerClasses($doc);

            $doc->formatOutput = true;
            $doc->preserveWhiteSpace = false;
            $doc->substituteEntities = true;

            return $doc;
        }

        /**
         * @param Dom $doc
         *
         * @return Dom
         */
        protected function setXPath(Dom $doc)
        {
            $doc->xPath = new Dom\XPath($doc);
            if ($doc->namespaceURI) {
                $doc->ns = $doc->lookupNamespaceUri($doc->namespaceURI);
                $doc->xPath->registerNamespace('x', $doc->ns);
            }
            return $doc;
        }

        /**
         * @param string   $source
         * @param int|null $options
         * @param bool     $result
         *
         * @return $this|Dom
         */
        public function load($source, $options = LIBXML_NOCDATA, &$result = false)
        {
            if (is_file($source)) {
                $source = file_get_contents($source, FILE_USE_INCLUDE_PATH);
            }
            if ($source) {
                return $this->loadXML($source, $options, $result);
            }
            return $this;
        }

        /**
         * @param string   $source
         * @param int|null $options
         * @param bool     $result
         *
         * @return $this|Dom
         */
        public function loadXML($source, $options = LIBXML_NOCDATA, &$result = false)
        {
            $this->preserveWhiteSpace = false;
            $this->recover = true;
            $result = parent::loadXML(mb_convert_encoding((string)$source, 'HTML-ENTITIES', $this->xmlEncoding), $options);
            return $this->setXPath($this);
        }

        /**
         * @param string $source
         *
         * @return $this|Dom
         */
        public function loadNsXML($source)
        {
            $simpleXML = new \SimpleXMLElement($source);
            $ns = $simpleXML->getDocNamespaces(true);
            $this->appendChild($this->importNode(dom_import_simplexml($simpleXML), true));
            foreach ($ns as $prefix => $uri) {
                $this->documentElement->removeAttributeNS($uri, $prefix);
            }
            #$this->normalizeDocument();
            return $this->setXPath($this);
        }

        /**
         * @param string $source or to string convertible
         * @param bool   $result
         *
         * @return $this|Dom
         */
        public function loadHTML($source, &$result = false)
        {
            libxml_use_internal_errors(true); /* ??? */
            $this->preserveWhiteSpace = false;
            $this->recover = true;
            $result = parent::loadHTML(mb_convert_encoding((string)$source, 'HTML-ENTITIES', $this->xmlEncoding));
            return $this->setXPath($this);
        }

        /**
         * @param string $filename
         * @param bool   $result
         *
         * @return $this|Dom
         */
        public function loadHTMLFile($filename, &$result = false)
        {
            if (is_file($filename)) {
                $source = file_get_contents($filename, FILE_USE_INCLUDE_PATH);
                if ($source) {
                    return $this->loadHTML($source, $options);
                }
            }
            return $this;
        }

        /**
         * @param string $version
         * @param string $encoding
         *
         * @return Dom
         */
        public static function doc($version = self::VERSION, $encoding = self::ENCODING)
        {
            $dom = new self($version, $encoding);
            return $dom;
        }

        /**
         * @param $name
         *
         * @return Dom\Element|Dom\Tag|Dom\Field
         */
        public function root($name = null)
        {
            if (!is_null($name)) {
                if (!$this->documentElement) {
                    $this->appendChild($this->createElement($name));
                } else {
                    $this->documentElement->name($name);
                }
            }
            return $this->documentElement;
        }

        /**
         * @param $child
         *
         * @return Dom\Element|Dom\Tag|Dom\Field
         */
        public function append($child)
        {
            if (is_string($child)) {
                $child = $this->createElement($child);
            }
            return $this->appendChild($this->importNode($child));
        }

        /**
         * @param $node Dom\Element
         * @return Dom\Element|Dom\Tag|Dom\Field
         */
        public function appendTo(Dom\Element $node)
        {
            return $node->append($this->root());
        }

        /**
         * @param string|array $expr
         * @param null $index
         * @param null $context
         *
         * @return Dom\Element|Dom\Tag|Dom\Field|Dom\Set
         */
        public function find($expr, $index = null, $context = null)
        {
            if (is_null($context)) {
                $context = $this->documentElement;
            }
            $this->contextElement = $context;
            if (is_null($index)) {
                return $this->set($this->xPath->query($expr, $context));
            }
            return $this->notEmpty($this->xPath->query($expr, $context)->item($index), $expr);
        }

        /**
         * @param \DOMNode|bool $node
         * @param null|string $expr
         *
         * @return Dom\Element|Dom\Tag|Dom\Field
         */
        public function notEmpty($node, $expr = null)
        {
            if (empty($node)) {
                $contextPath = $this->context()->getNodePath();
                $this->contextElement->prepend($this->createComment(self::NAME_NOT_MATCHED . ' by "' . $contextPath . '/'. $expr . '"'));
                $node = $this->createElement(self::NAME_NOT_MATCHED, $contextPath . $expr);
            }
            return $node;
        }

        /**
         * @param string $name
         *
         * @return Dom\Set
         */
        public function findByTag($name)
        {
            return $this->set($this->getElementsByTagName($name));
        }

        /**
         * @param string $id
         * @param bool $internal
         *
         * @return Dom\Element|Dom\Tag|Dom\Field
         */
        public function findById($id, $internal = true)
        {
            if ($internal) {
                return $this->notEmpty($this->getElementById($id), '#'.$id);
            }
            return $this->find('#'.$id, $this, 0);
        }

        /**
         * @return Dom\Element|Dom\Tag|Dom\Field|$this|Dom
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
         * @param null|\DOMNode  $element
         *
         * @return Dom\Element|Dom\Tag|Dom\Field
         */
        public function xslt($xslFile, $xsltParameters = array(), $element = null)
        {
            if (is_null($element)) {
                $element = $this->documentElement;
            }
            $newTree = Dom\Xslt::doc()->transform($xslFile, $xsltParameters, $element);
            if ($newTree) {
                return $element->replace($newTree->documentElement);
            }
            $element->after($this->createComment($element->getNodePath().' not transformed with '.$xslFile));
            return $element;
        }

        /**
         * @param array|\DOMNamedNodeMap|\DOMNodeList $list
         *
         * @return Dom\Set
         */
        public function set($list)
        {
            return new Dom\Set($list, $this);
        }

        /**
         * @param string $name node name or well formed xml fragment or text
         * @param null|string $value
         *
         * @return Dom\Element|Dom\Tag|Dom\Field
         */
        public function createElement($name, $value = null)
        {
            if (preg_match('/<.*?>/im', $name) or preg_match('/&.*?;/im', $name)) {
                if (!$this->createFragment($name, $node)) {
                    $node = $this->createText($name);
                }
            } else {
                $node = parent::createElement($name);
            }
            if (!is_null($value)) {
                $node->nodeValue = (string)$value;
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
            $fix = self::doc()->loadHTML($xml)->find('body', 0);

            if ($xml{0} !== '<') {
                $xml = (string)$this->set($fix->childNodes)->getChildren();
            } else
                if ($fix->childNodes->length) {
                    $xml = (string)$this->set($fix->childNodes);
                } else {
                    $xml = (string)$fix;
                }

            $fragment = $this->createDocumentFragment();

            return @$fragment->appendXML($xml);
        }

        /**
         * @param string $name attribute name
         * @param string $value
         *
         * @return Dom\Attr
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
         * @return Dom\Text
         */
        public function createText($string)
        {
            return $this->createTextNode((string)$string);
        }

        /**
         * @param string $string or convertible to string
         *
         * @return Dom\Cdata
         */
        public function createCData($string)
        {
            return $this->createCDATASection((string)$string);
        }

        /**
         * @param string $string or convertible to string
         *
         * @return Dom\Comment
         */
        public function createComment($string)
        {
            return parent::createComment(' '.(string)$string.' ');
        }

        /**
         * @param string $uri
         * @param string $type
         *
         * @return $this|Dom
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
         * @return Dom\Element|Dom\Tag|Dom\Field
         */
        public function importNode(\DOMNode $importedNode, $deep = true)
        {
            if ($this === $importedNode->ownerDocument) {
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
                    /** @var $child Dom\Element|Dom\Tag|Dom\Attr|Dom\Text|Dom\Cdata|Dom\Comment */
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
         * @return Dom|Dom\Element|Dom\Tag|Dom\Field
         */
        public function createFromArray(array $array)
        {
            return $this->createDomFromArray($array, self::doc());
        }

        /**
         * @param array $array
         * @param       $node
         *
         * @return Dom|Dom\Element|Dom\Tag|Dom\Field
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
                            /** @var Dom\Element|Dom\Tag $node */
                            $node->text($value);
                            return $node;
                            break;

                        case '#comment':
                            /** @var Dom\Element|Dom\Tag $node */
                            $node = $node->append($this->createComment($value));
                            break;

                        case '#cdata-section':
                            /** @var Dom\Element|Dom\Tag $node */
                            $node = $node->append($this->createCData($value));
                            break;

                        case '@':
                            /** @var Dom\Element|Dom\Tag $node */
                            $node->attr($value);
                            $value = false;
                            $key = 0;
                            break;

                        default:
                            /** @var Dom\Element|Dom\Tag $node */
                            $node = $node->append($this->createElement($key));
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
    }
}