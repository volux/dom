<?php
namespace volux\Dom;

{
    /**
     * @package volux.pro
     * @author  Andrey Skulov <andrey.skulov@gmail.com>
     *
     * not finalized. code moved to the Dom.php
     **/
    class XPath {

        /**
         * @param string $expr
         * @param string $axis
         * @return string
         */
        public static function fromCSS($expr, $axis)
        {
            $exprArray = explode('|', str_replace(',', '|', $expr));
            $result = array();
            foreach ($exprArray as $expr) {
                $result[] = $axis . self::transform($expr);
            }
            $expr = join('|', $result);

            $correct = array(
                '**' => '*',
            );
            return str_replace(array_keys($correct), array_values($correct), $expr);
        }

        /**
         * Prepare xPath predicates from css selectors
         *
         * @todo need tests for complex selectors
         * @see https://code.google.com/p/css2xpath/source/browse/trunk/src/css2xpath.js
         *
         * @param string $expr
         * @return string
         */
        protected static function transform($expr)
        {
            $patterns = array(
                '`["\']`' => '',
                '`\s{2,}|\n`' => ' ',
                '`(?:^|,)\.`' => '*.',
                '`(?:^|,)#`' => '*#',
                '`:(link|visited|active|hover|focus)`' => '.\1',
                '`\[(.*)]`e' => '"[".str_replace(".","`","\1")."]"',
                // add @ for attribs
                '`\[([^\]~\$\*\^\|\!]+)(=[^\]]+)?\]`' => "[@\${1}\${2}]",
                // , + ~ >
                '`\s*(\+|~|>)\s*`' => "\${1}",
#                '`>`' => '/',
                //* ~ + >
                '`([a-zA-Z0-9\_\-\*])~([a-zA-Z0-9\_\-\*])`' => "\${1}/following-sibling::\${2}",
                '`([a-zA-Z0-9\_\-\*])\+([a-zA-Z0-9\_\-\*])`' => "\${1}/following-sibling::*[1]/self::\${2}",
                '`([a-zA-Z0-9\_\-\*])>([a-zA-Z0-9\_\-\*])`' => "\${1}/\${2}",
                // all unescaped stuff escaped
                '`\[([^=]+)=([^\' | "][^\]]*)\]`' => "[\${1}=\"\${2}\"]",
                // all descendant or self to //
                '`(^|[^a-zA-Z0-9\_\-\*])(#|\.)([a-zA-Z0-9\_\-]+)`' => "\${1}*\${2}\${3}",
                '`([\>\+\|\~\,\s])([a-zA-Z\*]+)`' => "\${1}//\${2}",
                '`\s+\/\/`' => '//',

                '`([a-zA-Z0-9\_\-\*]+):first-child`' => "*[1]/self::\${1}",
                '`([a-zA-Z0-9\_\-\*]+):last-child`' => "\${1}[not(following-sibling::*)]",
                '`([a-zA-Z0-9\_\-\*]+):only-child`' => "*[last()=1]/self::\${1}",
                '`([a-zA-Z0-9\_\-\*]+):empty`' => "\${1}[not(*) and not(normalize-space())]",
            );
            $expr = preg_replace(array_keys($patterns), array_values($patterns), trim($expr));

            // :not
            $expr = preg_replace_callback(
                '`([a-zA-Z0-9\_\-\*]+):not\(([^\)]*)\)`',
                function ($matches) {
                    return $matches[1] . "[not(" . preg_replace('`^[^\[]+\[([^\]]*)\].*$`', "\${1}", self::transform($matches[2])) . ")]";
                },
                $expr
            );
            // :nth-child
            $expr = preg_replace_callback(
                '`([a-zA-Z0-9\_\-\*]+):nth-child\(([^\)]*)\)`',
                function ($matches) {

                    $a = $matches[1];
                    $b = $matches[2];
                    switch ($b) {

                        case "n":
                            return $a;

                        case "even":
                            return "*[position() mod 2=0 and position()>=0]/self::" . $a;

                        case "odd":
                            return $a . "[(count(preceding-sibling::*) + 1) mod 2=1]";

                        default:
                            $b = $b || "0";
                            $b = preg_replace('`^([0-9]*)n.*?([0-9]*)$`', "\${1}+\${2}", $b);
                            $b = explode(" + ", $b);
                            $b[1] = $b[1] || "0";
                            return "*[(position()-" . $b[1] . ") mod " . $b[0] . "=0 and position()>=" . $b[1] . "]/self::" . $matches[1];
                    }
                },
                $expr
            );
            $patterns = array(
                // :contains(selectors)
                '`:contains\(([^\)]*)\)`' => "[contains(string(.),\"\${1}\")]",
                // |= attrib
                '`\[([a-zA-Z0-9\_\-]+)\|=([^\]]+)\]`' => "[@\${1}=\${2} or starts-with(@\${1},concat(\${2},\"-\"))]",
                // *= attrib
                '`\[([a-zA-Z0-9\_\-]+)\*=([^\]]+)\]`' => "[contains(@\${1},\${2})]",
                // ~= attrib
                '`\[([a-zA-Z0-9\_\-]+)~=([^\]]+)\]`' => "[contains(concat(\" \", normalize-space(@\${1}),\" \"),concat(\" \",\${2},\" \"))]",
                // ^= attrib
                '`\[([a-zA-Z0-9\_\-]+)\^=([^\]]+)\]`' => "[starts-with(@\${1},\${2})]",
            );
            $expr = preg_replace(array_keys($patterns), array_values($patterns), $expr);
            // $= attrib
            $expr = preg_replace_callback(
                '`\[([a-zA-Z0-9\_\-]+)\$=([^\]]+)\]`',
                function ($matches) {
                    return "[substring(@" . $matches[1] . ",string-length(@" . $matches[1] . ")-" . (strlen($matches[2])-3) . ")=" . $matches[2] . "]";
                },
                $expr
            );
            $patterns = array(
                // != attrib
                '`\[([a-zA-Z0-9\_\-]+)\!=([^\]]+)\]`' => "[not(@\${1}) or @\${1}!=\${2}]",
                // ids and classes
                '`#([a-zA-Z0-9\_\-]+)`' => "[@id=\"\${1}\"]",
                '`\.([a-zA-Z0-9\_\-]+)`' => "[contains(concat(\" \", normalize-space(@class),\" \"),\" \${1} \")]",
                // local-name
                '`(^([a-z][a-z0-9]*))`i' => "[local-name()=\"\${1}\"]",
                // normalize multiple filters
                '`\]\[([^\]]+)`' => " and (\${1})",
                '/`/' => '.',
            );
            return preg_replace(array_keys($patterns), array_values($patterns), $expr);
        }
    }
}