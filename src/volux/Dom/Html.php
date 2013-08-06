<?php
namespace volux\Dom;

use volux\Dom;

    {

        /**
         * Class Html
         * @package volux\Dom
         * @author  Andrey Skulov <andrey.skulov@gmail.com>
         */
        class Html extends Dom
        {
            const
                ELEMENT_CLASS = '\volux\Dom\Tag',
                HEAD_HTML = '<!DOCTYPE html>'
            ;

            /** @var Dom\Tag */
            protected $head;
            /** @var Dom\Tag */
            protected $body;
            /** @var Dom\Tag */
            protected $scripts;
            /** @var Dom\Tag */
            protected $trash;
            /** @var Dom\Tag */
            protected $ajax;

            /**
             * @param string $version
             * @param string $encoding
             */
            public function __construct($version = self::VERSION, $encoding = self::ENCODING)
            {
                parent::__construct($version, $encoding);

                $this->root('html');
                $this->head = $this->root()->append('head')->text(false);
                $this->body = $this->root()->append('body')->text(false);
                $this->scripts = $this->createElement('scripts');
                $this->trash = $this->createElement('trash');
                $this->ajax = $this->createElement('reply');
            }

            /**
             * @param null   $ns
             * @param string $qualifiedName
             * @param string $docType
             *
             * @return \DOMDocument
             */
            public function implementation($ns = null, $qualifiedName = 'html', $docType = 'html')
            {
                return parent::implementation(null, $qualifiedName, $docType);
            }

            /**
             * @param string $version
             * @param string $encoding
             *
             * @return Html
             */
            public static function doc($version = self::VERSION, $encoding = self::ENCODING)
            {
                $html = new self($version, $encoding);
                return $html;
            }

            /**
             * @param array $array
             *
             * @return Html|Dom\Tag
             */
            public function createFromArray(array $array)
            {
                $html = self::doc();
                $html->removeChild($html->documentElement);
                $result = $this->createDomFromArray($array, $html);
                if ($result instanceof Html) {
                    $result->head = $result->root()->find('head', 0);
                    $result->body = $result->root()->find('body', 0);
                }
                return $result;
            }

            /**
             * @param null $htmlString
             * @param bool $normalize output doctype defined document
             * @param bool $asXml
             *
             * @return Html|string
             */
            public function html($htmlString = null, $normalize = false, $asXml = false)
            {
                if (!is_null($htmlString)) {
                    $this->loadHTML($htmlString);
                    if ($normalize) {
                        $this->normalizeDocument();
                    }
                    $this->head = $this->root()->find('head', 0);
                    $this->body = $this->root()->find('body', 0);
                    return $this;
                }

                if ($this->ajax->childNodes->length) {

                    $this->scripts->children()->appendTo($this->ajax);

                    $this->documentElement->appendChild($this->importNode($this->ajax, true));

                    if ($asXml) {
                        return $this->saveXML($this->ajax);
                    }
                    return $this->saveHTML($this->ajax);
                }

                $this->scripts->children()->appendTo($this->body);

                if ($normalize) {

                    $doc = $this->implementation();

                    foreach ($this->documentElement->childNodes as $child) {
                        /** @var $child Tag */
                        $doc->documentElement->appendChild($doc->importNode($child, true));
                    }
                    foreach ($this->documentElement->attributes as $attr) {
                        /** @var $attr Attr */
                        $doc->documentElement->setAttribute($attr->name, $attr->value);
                    }
                    if ($asXml) {
                        return $doc->saveXML();
#                    return substr($doc->saveXML(), strlen($this->headString())+1); /** :-/ */
                    }
                    return $doc->saveHTML();
                }
                /* or easy way: */
                if ($asXml) {
                    return self::HEAD_HTML.PHP_EOL.$this->saveXML($this->documentElement);
                }
                return self::HEAD_HTML.PHP_EOL.$this->saveHTML($this->documentElement);
            }

            /**
             * @param string|callable $source
             * @param int|null $options
             *
             * @return bool
             */
            public function load($source, $options = LIBXML_NOCDATA)
            {
                if (is_callable($source)) {
                    /**
                     * @todo point to precompile source
                     */
                    $source = $source();
                } else
                    if (is_file($source)) {
                        $source = file_get_contents($source, FILE_USE_INCLUDE_PATH);
                    }
                if ($source) {
                    return $this->loadHTML($source, $options);
                }
                return false;
            }

            /**
             * @return Tag
             */
            public function head()
            {
                return $this->head;
            }

            /**
             * @return Tag
             */
            public function body()
            {
                return $this->body;
            }

            /**
             * @return Tag
             */
            public function ajax()
            {
                $args = func_get_args();
                foreach ($args as $arg) {
                    $this->ajax->append($arg);
                }
                return $this->ajax;
            }

            /**
             * @param array $content
             *
             * @return $this|Html
             */
            public function meta(array $content)
            {
                $this->head->append('meta')->attr($content);
                return $this;
            }

            /**
             * @param string $titleText
             *
             * @return $this|Html
             */
            public function title($titleText)
            {
                $title = $this->findByTag('title');
                if ($title->isEmpty()) {
                    $this->head->append('title')->text($titleText);
                } else {
                    $title->first()->text($titleText);
                }
                return $this;
            }

            /**
             * @param        $uri
             * @param string $type
             *
             * @return $this|Html
             */
            public function stylesheet($uri, $type = 'stylesheet')
            {
                $this->head->append('link')->attr(array('href' => $uri, 'rel' => 'stylesheet'));
                return $this;
            }

            /**
             * @return $this|Html
             */
            public function favicon()
            {
                $this->head->append('link')->attr(array('href' => '/favicon.ico', 'rel' => 'shortcut icon', 'type' => 'image/x-icon'));
                return $this;
            }

            /**
             * @param $uri
             * @param null $code
             * @param boolean $inHead
             *
             * @return $this|Html
             */
            public function script($uri, $code = null, $inHead = false)
            {
                $to = $this->scripts;
                if ($inHead) {
                    $to = $this->head;
                }
                if (!empty($uri)) {
                    $to->append('script')->attr('src', $uri)->text(false);
                }
                if (!is_null($code)) {
                    $to->append('script')->append($this->createText($code));
                }
                return $this;
            }

            /**
             * @return string
             */
            public function __toString()
            {
                return $this->html(null, false, true);
            }
        }
    }