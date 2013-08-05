<?php
namespace volux\Dom;

use volux\Dom;

    {

        /**
         * Class Xslt
         * @package volux\Dom
         * @author  Andrey Skulov <andrey.skulov@gmail.com>
         */
        class Xslt extends Dom
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
                $html = new self($version, $encoding);
                return $html;
            }

            /**
             * @param string   $source
             * @param int|null $options
             *
             * @return bool
             */
            public function loadXML($source, $options = LIBXML_NOCDATA)
            {
                $result = parent::loadXML($source, $options);
                $this->processor();
                return $result;
            }

            /**
             * @return \XSLTProcessor
             */
            public function processor()
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
             * @param string|callable $xslFile
             * @param Set|Element|Tag|Dom $element
             * @param array  $xsltParameters
             * @param string $ns
             *
             * @return \DOMDocument
             */
            public function transform($xslFile, $xsltParameters = array(), $element, $ns = '')
            {
                if (!$this->load($xslFile)) {
                    return false;
                }
                foreach ($xsltParameters as $name => $value) {
                    $this->processor->setParameter($ns, $name, $value);
                }
                return $this->processor->transformToDoc($element);
            }
        }
    }