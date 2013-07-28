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
         * @param string   $filename
         * @param int|null $options
         *
         * @return bool
         */
        public function load($filename, $options = LIBXML_NOCDATA)
        {
            $xsl = @file_get_contents($filename, FILE_USE_INCLUDE_PATH);
            if ($xsl) {
                return $this->loadXML($xsl, $options);
            }
            return false;
        }

        /**
         * @param string   $source
         * @param int|null $options
         *
         * @return bool
         */
        public function loadXML($source, $options = LIBXML_NOCDATA)
        {
            /**
             * @todo point to precompile xslt
             */
            $result = parent::loadXML(html_entity_decode($source, ENT_NOQUOTES, $this->xmlEncoding), $options);
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
         * @param string $xslFile
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