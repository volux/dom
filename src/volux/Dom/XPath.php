<?php
/**
 * volux\Dom
 *
 * @link http://github.com/volux/dom
 */
namespace volux\Dom;

use volux\Dom;
/**
 * Class XPath
 * @package volux\Dom
 * @author  Andrey Skulov <andrey.skulov@gmail.com>
 */
class XPath extends \DOMXPath {

    /**
     * @param string|array $expression
     * @param null|\DOMNode|\DOMDocument|Dom|Html|Table|Form|Attr|Element|Tag|Field|Text|Cdata|Comment $contextNode
     *
     * @return \DOMNodeList
     */
    public function query($expression, $contextNode = null)
    {
        return parent::query($this->expression($expression), $contextNode);
    }

    /**
     * @param string|array $expression
     * @param string $axis
     * @return string
     */
    public function expression($expression, $axis = 'descendant::*')
    {
        if (is_array($expression)) {
            $axis = $expression[1];
            $expression = $expression[0];
        }
        $expression = explode('|', str_replace(',', '|', $expression));
        $result = array();
        foreach ($expression as $selector) {
            $result[] = $axis . $this->prepare($selector);
        }
        $correct = array(
            '**' => '*',
        );
        return str_replace(array_keys($correct), array_values($correct), join('|', $result));
    }

    /**
     * Prepare xPath predicates from css selectors
     *
     * @todo need tests for complex selectors
     * @see https://code.google.com/p/css2xpath/source/browse/trunk/src/css2xpath.js
     *
     * @param string $selector
     *
     * @return string
     */
    protected function prepare($selector)
    {
        $patterns = array(
            '`["\']`' => '',
            '`\s{2,}|\n`' => ' ',
            '`(?:^|,)\.`' => '*.',
            '`(?:^|,)#`' => '*#',
            '`:(link|visited|active|hover|focus)`' => '.\1',
            '`\[(.*)]`e' => '"[".str_replace(".","`","\1")."]"',
            /* add @ for attribs */
            '`\[([^\]~\$\*\^\|\!]*)(=[^\]]+)?\]`' => "[@\${1}\${2}]",
            /* , + ~ > */
            '`\s*(\+|~|>)\s*`' => "\${1}",
            /* * ~ + > */
            '`([a-z0-9\_\-\*]*)~([a-z0-9\_\-\*]*)`i' => "\${1}/following-sibling::\${2}",
            '`([a-z0-9\_\-\*]*)\+([a-z0-9\_\-\*]*)`i' => "\${1}/following-sibling::*[1]/self::\${2}",
            '`([a-z0-9\_\-\*]*)>([a-z0-9\_\-\*]*)`i' => "\${1}/\${2}",
            /* all unescaped stuff escaped */
            '`\[([^=]+)=([^\' | "][^\]]*)\]`' => "[\${1}=\"\${2}\"]",
            /* all descendant or self to // */
            '`(^|[^a-z0-9\_\-\*])(#|\.)([a-z0-9\_\-]+)`i' => "\${1}*\${2}\${3}",
            '`([\>\+\|\~\,\s])([a-z\*]*)`i' => "\${1}//\${2}",
            '`\s+\/\/`' => "//",

            '`([a-z0-9\_\-\*]*):first-child`i' => "*[1]/self::\${1}",
            '`([a-z0-9\_\-\*]*):last-child`i' => "\${1}[not(following-sibling::*)]",
            '`([a-z0-9\_\-\*]*):only-child`i' => "*[last()=1]/self::\${1}",
            '`([a-z0-9\_\-\*]*):empty`i' => "\${1}[not(*) and not(normalize-space())]",
        );
        $selector = preg_replace(array_keys($patterns), array_values($patterns), trim($selector));
        $_this = $this;
        /* :not */
        $selector = preg_replace_callback(
            '`([a-z0-9\_\-\*]*):not\(([^\)]*)\)`i',
            function ($matches) use ($_this) {
                return $matches[1] . "[not(" . preg_replace('`^[^\[]+\[([^\]]*)\].*$`', "\${1}", $_this->prepare($matches[2])) . ")]";
            },
            $selector
        );
        /* :nth-child */
        $selector = preg_replace_callback(
            '`([a-z0-9\_\-\*]*):nth-child\(([^\)]*)\)`i',
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
            $selector
        );
        $patterns = array(
            /* :contains(selectors) */
            '`:contains\(([^\)]*)\)`' => "[contains(string(.),\"\${1}\")]",
            /* |= attrib */
            '`\[([a-z0-9\_\-]*)\|=([^\]]+)\]`i' => "[@\${1}=\${2} or starts-with(@\${1},concat(\${2},\"-\"))]",
            /* *= attrib */
            '`\[([a-z0-9\_\-]*)\*=([^\]]+)\]`i' => "[contains(@\${1},\${2})]",
            /* ~= attrib */
            '`\[([a-z0-9\_\-]*)~=([^\]]+)\]`i' => "[contains(concat(\" \", normalize-space(@\${1}),\" \"),concat(\" \",\${2},\" \"))]",
            /* ^= attrib */
            '`\[([a-z0-9\_\-]*)\^=([^\]]+)\]`i' => "[starts-with(@\${1},\${2})]",
        );
        $selector = preg_replace(array_keys($patterns), array_values($patterns), $selector);
        /* $= attrib */
        $selector = preg_replace_callback(
            '`\[([a-z0-9\_\-]*)\$=([^\]]+)\]`i',
            function ($matches) {
                return "[substring(@" . $matches[1] . ",string-length(@" . $matches[1] . ")-" . (strlen($matches[2])-3) . ")=" . $matches[2] . "]";
            },
            $selector
        );
        $patterns = array(
            /* != attrib */
            '`\[([a-z0-9\_\-]*)\!=([^\]]+)\]`i' => "[not(@\${1}) or @\${1}!=\${2}]",
            /* ids and classes */
            '`#([a-z0-9\_\-]*)`i' => "[@id=\"\${1}\"]",
            '`\.([a-z0-9\_\-]*)`i' => "[contains(concat(\" \", normalize-space(@class),\" \"),\" \${1} \")]",
            /* local-name */
            '`(^([a-z][a-z0-9]*))`i' => "[local-name()=\"\${1}\"]",
            /* normalize multiple predicates */
            '`\]\[([^\]]+)`' => " and (\${1})",
            '/`/' => '.',
        );
        return preg_replace(array_keys($patterns), array_values($patterns), $selector);
    }
}
