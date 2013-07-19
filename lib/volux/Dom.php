<?php
namespace volux;

{

    /**
     * Class Dom
     * @author  Andrey Skulov <andrey.skulov@gmail.com>
     * @package volux
     */
    class Dom extends \DOMDocument
    {
        const
            NAME_NOT_MATCHED = 'not.matched',
            VERSION    = '1.0',
            ENCODING   = 'UTF-8',
            ELEMENT_CLASS = '\volux\Dom\Element',
            ATTR_CLASS = '\volux\Dom\Attr'
        ;
        /**
         * @var \DOMXPath
         */
        protected $xPath;
        protected $ns;
        protected $prefixLength;
        /**
         * @var Dom\Element
         */
        protected $contextElement;
        /**
         * @var Dom\Element
         */
        public $documentElement;

        public function __construct($version = self::VERSION, $encoding = self::ENCODING)
        {
            parent::__construct($version, $encoding);

            $this->registerNodeClass('\DOMElement', static::ELEMENT_CLASS);
            $this->registerNodeClass('\DOMAttr', static::ATTR_CLASS);

            $this->preserveWhiteSpace = false;
            $this->strictErrorChecking = false;
            $this->formatOutput = true;
            $this->setXPath();
        }

        /**
         * @return Dom
         */
        protected function setXPath()
        {
            $this->xPath = new \DOMXPath($this);
            if ($this->namespaceURI) {
                $this->ns = $this->lookupNamespaceUri($this->namespaceURI);
                $this->xPath->registerNamespace('x', $this->ns);
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
         * @return Dom\Element
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
         * @param $node Dom\Element
         * @return Dom\Element
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
         * @return Dom\Element|Dom\Set
         */
        public function find($expr, $index = null, $context = null)
        {
            $expr = $this->xPathExpr($expr);
            if (is_null($context)) {
                $context = $this;
            }
            $this->contextElement = $context;
            if (is_null($index)) {
                return $this->set($this->xPath->query($expr, $context));
            }
            return $this->notEmpty($this->xPath->query($expr, $context)->item($index), $expr);
        }

        /**
         * @param string|array $expr
         * @param string $axis
         * @return string
         */
        public function xPathExpr($expr, $axis = 'descendant::*')
        {
            if (is_array($expr)) {
                $axis = $expr[1];
                $expr = $expr[0];
            }
            return Dom\XPath::fromCSS($expr, $axis);
        }

        /**
         * @param $node
         * @param $expr
         *
         * @return Dom\Element
         */
        public function notEmpty($node, $expr = null)
        {
            if (empty($node)) {
                $contextPath = $this->context()->getNodePath();
                $this->root()->append($this->createComment(' '. static::NAME_NOT_MATCHED . ' by "' . $contextPath . $expr . '" '));
                $node = $this->createNode(static::NAME_NOT_MATCHED, $contextPath . $expr);
            }
            return $node;
        }

        /**
         * @param $name
         *
         * @return Dom\Set
         */
        public function byTag($name)
        {
            return $this->set($this->getElementsByTagName($name));
        }

        /**
         * @param $id
         * @param bool $internal
         *
         * @return Dom\Element
         */
        public function byId($id, $internal = true)
        {
            if ($internal) {
                return $this->notEmpty($this->getElementById($id), '#'.$id);
            }
            return $this->find('#'.$id, $this, 0);
        }

        /**
         * @return Dom\Element|Dom
         */
        public function context()
        {
            if ($this->contextElement) {
                return $this->contextElement;
            }
            return $this;
        }

        /*        public function transform($schemeName, $element = null, $xsltParameters = array(), $asFragment = false)
                {
                    if (is_null($element)) {
                        $element = $this;
                    }
                    @todo source!
                    $xslt = new \volux\Dom\Xslt\File(Args::with(array('path' => $schemeName)));
                    return $xslt->transform($element, $asFragment, $xsltParameters);
                }*/

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
         * @todo implement fragment if $name like '<node some="attr"></node>'
         *
         * @param $name
         * @param null $text
         *
         * @return Dom\Element
         */
        public function createNode($name, $text = null)
        {
            if ($name{0} == '<') {
                $this->createFragment($name, $node);
            } else {
                $node = $this->createElement($name);
            }
            if (!empty($text)) {
                $node->nodeValue = $text;
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
            $result = @$fragment->appendXML($xml);
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
            $attr->value = $value;
            return $attr;
        }

        /**
         * @param $text
         *
         * @return \DOMText
         */
        public function createText($text)
        {
            return $this->createTextNode($text);
        }

        /**
         * @param $string
         *
         * @return \DOMCdataSection
         */
        public function createCData($string)
        {
            return $this->createCDATASection($string);
        }

        /**
         * @param \DOMNode $importedNode
         * @param bool|null $deep
         *
         * @return Dom\Element
         */
        public function importNode(\DOMNode $importedNode, $deep = null)
        {
            if ($this === $importedNode->ownerDocument) {
                return $importedNode;
            }
            if (is_null($deep)) {
                $deep = true;
            }
            return $this->notEmpty(parent::importNode($importedNode, $deep));
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