<?php
/**
 * volux\Dom
 *
 * @link http://github.com/volux/dom
 */
namespace volux\Dom;

use volux\Dom;

    {
        /**
         * Class Table
         * @package volux\Dom
         * @author  Andrey Skulov <andrey.skulov@gmail.com>
         */
        class Table extends Dom
        {
            const
                ELEMENT_CLASS = '\volux\Dom\Tag',
                HEAD_HTML = ''
            ;

            /**
             * @var Tag|Element
             */
            protected $head;
            /**
             * @var Tag|Element
             */
            protected $body;

            /**
             * @var Tag|Element
             */
            public $documentElement;

            /**
             * @param string $version
             * @param string $encoding
             */
            public function __construct($version = self::VERSION, $encoding = self::ENCODING)
            {
                parent::__construct($version, $encoding);
                $this->root('table');
                $this->head = $this->root()->append('thead')->text(false);
                $this->body = $this->root()->append('tbody')->text(false);
            }

            /**
             * @param array $head
             *
             * @return $this|Table|Dom
             */
            public function head(array $head)
            {
                $row = $this->head->append('tr');
                foreach($head as $value) {
                    $row->append('th')->append($value);
                }
                return $this;
            }

            /**
             * @param array $body
             *
             * @return $this|Table|Dom
             */
            public function body(array $body)
            {
                foreach($body as $rowValues) {
                    $row = $this->body->append('tr');
                    foreach($rowValues as $value) {
                        $row->append('td')->append($value);
                    }
                }
                return $this;
            }

            /**
             * @return string
             */
            public function __toString()
            {
                return $this->saveXML($this->documentElement) . PHP_EOL;
            }
        }
    }