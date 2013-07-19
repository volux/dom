<?php
namespace volux\Dom;

use volux\Dom;

    {

        /**
         * Class Xml
         * @package volux\Dom
         * @author  Andrey Skulov <andrey.skulov@gmail.com>
         */
        class Xml extends Dom
        {
            const
                ELEMENT_CLASS = '\volux\Dom\Node',
                HEAD_XML = '<?xml version="1.0" encoding="UTF-8"?>'
            ;

            /**
             * @var Dom\Node
             */
            public $documentElement;


            /**
             * @param string $version
             * @param string $encoding
             *
             * @return Xml
             */
            public static function doc($version = self::VERSION, $encoding = self::ENCODING)
            {
                $xml = new self($version, $encoding);
                return $xml;
            }

            /**
             * @param $xmlString
             * @param bool $normalize clear namespaces
             *
             * @return Xml|string
             */
            public function xml($xmlString = null, $normalize = false)
            {
                if (!is_null($xmlString)) {
                    if ($normalize) {
                        $simpleXML = new \SimpleXMLElement($xmlString);
                        $ns = $simpleXML->getDocNamespaces(true);
                        $this->appendChild($this->importNode(dom_import_simplexml($simpleXML), true));
                        foreach ($ns as $prefix => $uri) {
                            $this->documentElement->removeAttributeNS($uri, $prefix);
                        }
                        #$this->normalizeDocument();
                        return $this->setXPath();
                    }
                    $this->loadXML($xmlString);
                    return $this->setXPath();
                }
                return (string)$this;
            }

            /**
             * @param $expr
             * @param null $context
             *
             * @return Dom\Node
             */
            public function node($expr, $context = null)
            {
                return $this->find($expr, 0, $context);
            }

            /**
             * @param $expr
             * @param null $context
             *
             * @return Dom\Set
             */
            public function nodeset($expr, $context = null)
            {
                return $this->find($expr, null, $context);
            }

            /**
             * @param $path
             * @param string $type
             *
             * @return Xml
             */
            public function stylesheet($path, $type = 'xsl')
            {
                $this->appendChild($this->createProcessingInstruction('xml-stylesheet', 'type="text/' . $type . '" href="' . $path . '"'));
                return $this;
            }

        }
    }