<?php
namespace volux\Dom;

{

    /**
     * Class Node
     * @package volux\Dom
     * @author  Andrey Skulov <andrey.skulov@gmail.com>
     */
    class Node extends Element
    {
        /**
         * @param null $xml
         *
         * @return string|Node
         */
        public function xml($xml = null)
        {
            if (!is_null($xml)) {
                $this->clear();
                return $this->add($xml);
            }
            return (string)$this->children();
        }

        /**
         * @return Xml
         */
        public function owner()
        {
            return $this->ownerDocument;
        }

        /**
         * @param $expr
         *
         * @return Node
         */
        public function node($expr)
        {
            return $this->owner()->node($expr, $this);
        }

        /**
         * @param $expr
         *
         * @return Set
         */
        public function nodeset($expr)
        {
            return $this->owner()->nodeset($expr, $this);
        }

    }
}
