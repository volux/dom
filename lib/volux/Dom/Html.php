<?php
namespace volux\Dom;

use volux\Dom;

    {

        /**
         * @package volux\Dom
         * @author  Andrey Skulov <andrey.skulov@gmail.com>
         **/
        class Html extends Dom
        {
            const
                ELEMENT_CLASS = '\volux\Dom\Tag',
                HEAD_HTML = '<!DOCTYPE html>'
            ;

            protected $head;
            protected $body;
            protected $scripts;
            protected $trash;
            protected $ajax;

            public function __construct($version = self::VERSION, $encoding = self::ENCODING)
            {
                parent::__construct($version, $encoding);
                $this->root('html');
                $this->head = $this->root()->append('head')->text(false);
                $this->body = $this->root()->append('body')->text(false);
                $this->scripts = $this->createNode('scripts');
                $this->trash = $this->createNode('trash');
                $this->ajax = $this->createNode('reply');
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
             * @param null $htmlString
             * @param bool $normalize output doctype defined document
             * @param bool $asXml
             *
             * @return Html|string
             */
            public function html($htmlString = null, $normalize = false, $asXml = false)
            {
                if (!is_null($htmlString)) {
                    libxml_use_internal_errors(true);
                    $this->preserveWhiteSpace = false;
                    $this->recover = true;
                    $this->loadHTML(mb_convert_encoding((string)$htmlString, 'HTML-ENTITIES', self::ENCODING));
                    return $this->setXPath();
                }
                if ($normalize) {
                    $imp = new \DOMImplementation();
                    $doc = $imp->createDocument(null, 'html', $imp->createDocumentType('html'));
                    $doc->formatOutput = true;
                    $doc->preserveWhiteSpace = false;
                    $doc->encoding = self::ENCODING;
                    foreach ($this->documentElement->childNodes as $node) {
                        $doc->documentElement->appendChild($doc->importNode($node, true));
                    }
                    foreach ($this->documentElement->attributes as $attr) {
                        /** @var $attr Attr */
                        $doc->documentElement->setAttribute($attr->name, $attr->value);
                    }
                    if ($asXml) {
                        return $doc->saveXML();
                    }
                    return $doc->saveHTML();
                }
                /* or easy way: */
                return self::HEAD_HTML.PHP_EOL.$this->saveHTML($this->root()) . PHP_EOL;
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
             * @return Html
             */
            public function meta(array $content)
            {
                $this->head->append('meta')->attr($content);
                return $this;
            }

            /**
             * @param $title
             *
             * @return Html
             */
            public function title($title)
            {
                $this->head->append('title')->text($title);
                return $this;
            }

            /**
             * @param $uri
             *
             * @return Html
             */
            public function stylesheet($uri)
            {
                $this->head->append('link')->attr(array('href' => $uri, 'rel' => 'stylesheet'));
                return $this;
            }

            /**
             * @return Html
             */
            public function favicon()
            {
                $this->head->append('link')->attr(array('href' => '/favicon.ico', 'rel' => 'shortcut icon', 'type' => 'image/x-icon'));
                return $this;
            }

            /**
             * @param $uri
             * @param null $code
             *
             * @return Html
             */
            public function script($uri, $code = null)
            {
                if (!empty($uri)) {
                    $this->scripts->append('script')->attr('src', $uri)->text(false);
                }
                if (!is_null($code)) {
                    $this->scripts->append('script')->add(PHP_EOL.$code.PHP_EOL);
                }
                return $this;
            }

            /**
             * @return string
             */
            public function __toString()
            {
                if ($this->ajax->childNodes->length) {
                    $this->root()->append($this->ajax);
                    return $this->saveXML($this->ajax);
                }
                if ($this->scripts->childNodes->length) {
                    $this->scripts->children()->appendTo('body');
                }
                return $this->html(null, true, true);
            }
        }
    }