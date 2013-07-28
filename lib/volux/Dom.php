<?php
namespace volux;

{

    /**
     * Class Dom
     * @package volux
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
         * @var Dom\Element|Dom\Tag
         */
        public $documentElement;
        /**
         * @var Dom\XPath
         */
        public $xPath;
        /**
         * @var Dom\Element|Dom\Tag
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
            $doc->xmlEncoding = self::ENCODING;

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
         * @param string $filename
         * @param null   $options
         *
         * @return bool
         */
        public function load($filename, $options = null)
        {
            $this->preserveWhiteSpace = false;
            $this->recover = true;
            $result = parent::load($filename, $options);
            $this->setXPath($this);
            return $result;
        }

        /**
         * @param string $source
         * @param null   $options
         *
         * @return bool
         */
        public function loadXML($source, $options = null)
        {
            $this->preserveWhiteSpace = false;
            $this->recover = true;
            $result = parent::loadXML($source, $options);
            $this->setXPath($this);
            return $result;
        }

        /**
         * @param string $source
         *
         * @return bool
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
         *
         * @return bool
         */
        public function loadHTML($source)
        {
            libxml_use_internal_errors(true); # ???
            $this->preserveWhiteSpace = false;
            $this->recover = true;
            $result = parent::loadHTML(mb_convert_encoding((string)$source, 'HTML-ENTITIES', $this->xmlEncoding));
            $this->setXPath($this);
            return $result;
        }

        /**
         * @param string $filename
         *
         * @return bool
         */
        public function loadHTMLFile($filename)
        {
            $this->preserveWhiteSpace = false;
            $this->recover = true;
            $result = parent::loadHTMLFile($filename);
            $this->setXPath($this);
            return $result;
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
         * @return Dom\Element|Dom\Tag
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
         * @return Dom\Element|Dom\Tag
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
         * @return Dom\Element|Dom\Tag
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
         * @return Dom\Element|Dom\Tag|Dom\Set
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
         * @param $node
         * @param $expr
         *
         * @return Dom\Element|Dom\Tag
         */
        public function notEmpty($node, $expr = null)
        {
            if (empty($node)) {
                $contextPath = $this->context()->getNodePath();
                $this->contextElement->prepend($this->createComment(self::NAME_NOT_MATCHED . ' by "' . $contextPath . SL. $expr . '"'));
                $node = $this->createElement(self::NAME_NOT_MATCHED, $contextPath . $expr);
            }
            return $node;
        }

        /**
         * @param $name
         *
         * @return Dom\Set
         */
        public function findByTag($name)
        {
            return $this->set($this->getElementsByTagName($name));
        }

        /**
         * @param $id
         * @param bool $internal
         *
         * @return Dom\Element|Dom\Tag
         */
        public function findById($id, $internal = true)
        {
            if ($internal) {
                return $this->notEmpty($this->getElementById($id), '#'.$id);
            }
            return $this->find('#'.$id, $this, 0);
        }

        /**
         * @return Dom\Element|Dom\Tag|$this|Dom
         */
        public function context()
        {
            if ($this->contextElement) {
                return $this->contextElement;
            }
            return $this;
        }

        /**
         * @param       $xslFile
         * @param array $xsltParameters
         * @param null  $element
         *
         * @return Dom\Element|Dom\Tag
         */
        public function transform($xslFile, $xsltParameters = array(), $element = null)
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
         * @param $list
         *
         * @return Dom\Set
         */
        public function set($list)
        {
            return new Dom\Set($list, $this);
        }

        /**
         * @param $name
         * @param null $value
         *
         * @return Dom\Element|Dom\Tag
         */
        public function createElement($name, $value = null)
        {
            if (!is_bool(mb_strpos($name, '<'))) {
                $this->createFragment($name, $node);
            } else {
                $node = parent::createElement($name);
            }
            if (!empty($value)) {
                $node->nodeValue = $value;
            }
            return $node;
        }

        /**
         * @param $xml
         * @param $fragment
         *
         * @return bool
         */
        public function createFragment($xml, &$fragment)
        {
            $fragment = $this->createDocumentFragment();
            $result = @$fragment->appendXML(html_entity_decode($xml, ENT_NOQUOTES, $this->xmlEncoding));
            return $result;
        }

        /**
         * @param $name
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
         * @param $string
         *
         * @return Dom\Text
         */
        public function createText($string)
        {
            return $this->createTextNode((string)$string);
        }

        /**
         * @param $string
         *
         * @return Dom\Cdata
         */
        public function createCData($string)
        {
            return $this->createCDATASection((string)$string);
        }

        /**
         * @param $string
         *
         * @return Dom\Comment
         */
        public function createComment($string)
        {
            return parent::createComment(' '.(string)$string.' ');
        }

        /**
         * @param $uri
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
         * @return Dom\Element|Dom\Tag
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
         * @return Dom|Dom\Element
         */
        public function createFromArray(array $array)
        {
            return $this->createDomFromArray($array, self::doc());
        }

        /**
         * @param array $array
         * @param       $node
         *
         * @return Dom|Dom\Element|Dom\Tag
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