<?php
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

            protected $head;
            protected $body;

            /**
             * @var Dom\Tag
             */
            public $documentElement;

            public function __construct($version = self::VERSION, $encoding = self::ENCODING)
            {
                parent::__construct($version, $encoding);
                $this->root('table');
                $this->head = $this->root()->append('thead')->text(false);
                $this->body = $this->root()->append('tbody')->text(false);
            }

            /**
             * @param array $head
             * @return $this
             */
            public function head(array $head)
            {
                $row = $this->head->append('tr');
                foreach($head as $value) {
                    $row->append('th')->add($value);
                }
                return $this;
            }

            /**
             * @param array $body
             * @return $this
             */
            public function body(array $body)
            {
                foreach($body as $rowValues) {
                    $row = $this->body->append('tr');
                    foreach($rowValues as $value) {
                        $row->append('td')->add($value);
                    }
                }
                return $this;
            }

            /**
             * @return string
             */
            public function __toString()
            {
                return $this->saveHTML($this->root()) . PHP_EOL;
            }
        }
    }