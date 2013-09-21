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
     * @var \DOMDocument|Document|Html|Table|Form
     */
    protected $doc;

    /**
     * @var array
     */
    protected $cache = array();

    public function __construct(Document $doc)
    {
        parent::__construct($doc);
        $this->doc = $doc;
        if ($doc->prefix) {
            $doc->xPath->registerNamespace('x', $doc->lookupNamespaceUri($doc->prefix));
        }
        $this->readyToCache();
    }

    /**
     * @return \DOMDocument|Document|Html|Table|Form
     */
    public function doc()
    {
        return $this->doc;
    }

    /**
     * @param string|array $expression
     * @param null|\DOMNode|\DOMDocument|Document|Html|Table|Form|Attr|Element|Tag|Field|Text|Cdata|Comment $contextnode
     * @param bool $registerNodeNS
     *
     * @return Set
     */
    public function query($expression, \DOMNode $contextnode = null, $registerNodeNS = true)
    {
        $prepared = $this->expression($expression);
        $result = @parent::query($this->expression($expression), $contextnode, $registerNodeNS);
        $error = error_get_last();
        if (!is_null($error)) {
            $this->doc->debug(array($expression, $prepared, $error));
        }
        return $this->doc->set($result);
    }

    /**
     * @param string|array $expression
     * @param string $axis
     * @return string
     */
    public function expression($expression, $axis = 'descendant::')
    {
        if ($expression == '') {
            return $axis.'*';
        }
        if (is_array($expression)) {
            $axis = array_shift($expression);
            $expression = array_shift($expression);
        }
        $input = $expression;
        if ($this->inCache($expression)) {
            return $this->cached($expression);
        }
        if (preg_match(Dom\Document::MATCH_ONCE_WORD, $expression)) {
            $expression = $axis.'*[local-name()="'.$expression.'"]';
            return $this->toCache($input, $expression);
        }
        $expression = explode('%', str_replace(',', '%', $expression));
        $result = array();
        foreach ($expression as $selector) {
            $prepared = $this->prepare(trim($selector));
            $axisEnd = $axis{strlen($axis)-1};
            switch ($prepared{0}) {
                case '[':
                    if ($axisEnd !== '*') {
                        $axis.= '*';
                    }
                    break;
                case '*':
                    if ($axisEnd !== ':') {
                        $prepared = substr($prepared, 1);
                    } else {
                        $axis.= '*';
                    }
                    break;
                case '/':
                    $axis = '';
                    break;
            }
            $result[] = $axis . $prepared;
        }
        $correct = array(
            '/[' => '/*[',
            '::[' => '::*[',
            '.=' => 'text()=',
            '][' => ' and ',
            ' and [' => ' and *[',
            '([' => '(',
            '])' => ')',
            #'/'.$this->document->prefix.':' => '/x:',
        );
        return str_replace(array_keys($correct), array_values($correct), join('|', $result));
    }

    /**
     * @return $this
     */
    protected function readyToCache()
    {
        $ready = array(
            '' => '',
            '.' => '/text()',
            '*' => '*',
            '/text()' => '/text()',
        );
        $this->cache = $ready;
        return $this;
    }

    /**
     * @param string $input
     *
     * @return bool
     */
    protected function inCache($input)
    {
        return isset($this->cache[$input]);
    }

    /**
     * @param string $input
     *
     * @return string
     */
    protected function cached($input)
    {
        return $this->cache[$input];
    }

    /**
     * @param string $input
     * @param string $prepared
     *
     * @return string
     */
    protected function toCache($input, $prepared)
    {
        return $this->cache[$input] = $prepared;
    }

    /**
     * @param array $patterns
     * @param string $selector
     *
     * @return string
     */
    protected function replace(array $patterns, $selector)
    {
        return preg_replace(array_keys($patterns), array_values($patterns), $selector);
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
    public function prepare($selector)
    {
        $easy = array(
            0 => array(
                '`\*`' => '*',
                '`\.`' => '/text()',
            ),
            1 => array(
                '`\.(_?[a-z]+[\w]?(?:(?:-+[\w]+)|(?:[\w]?)))`' => "[contains(concat(\" \",normalize-space(@class),\" \"),concat(\" \",\"\${1}\",\" \"))]",
                '`#(_?[a-z]+[\w]?(?:(?:-+[\w]+)|(?:[\w]?)))`' => "[@id=\"\${1}\"]",
                '`(?:[/\*]+)(_?[a-z]+[\w]?(?:(?:-+[\w]+)|(?:[\w]?))(?!\w*(?:[\(\]-]|(?:.)?=|::)))`' => "[local-name()=\"\${1}\"]",
            ),
        );

        $words = str_word_count($selector);

        if (isset($easy[$words])) {

            $selector = $this->replace($easy[$words], $selector);
            return $selector;
        }
        $patterns = array(
            /* double spaces to one */
            '`\s{2,}|\n(?=(?:[^"]*"[^"]*")*[^"]*$)`' => ' ',
            '`\s*(=)\s*(?=(?:[^"]*"[^"]*")*[^"]*$)`' => "\${1}",
            '`(?<!/)@(?=(?:[^"]*"[^"]*")*[^"]*$)`' => "",
            /* wrap in quotes all unquoted stuff - reset*/
            '`(?<!\()["\'](?!\))`' => '',
            /* attr */
            '`(?<=[\s\[])@?(_?[a-z]+[\w]?(?:(?:-+[\w]+)|(?:[\w]?))(?=(?:[^"]*"[^"]*")*[^"]*$))(?=(?:\]|.?=))(?:(.?=)?([^\]]*)?)`i' => "@\${1}\${2}\${3}",
            /* wrap in quotes all unquoted stuff - set*/
            '`=([^\'|"]?[^\]]+)`' => "=\"\${1}\"",
            /* ids */
            '`(#)(?<=^#|\w[^=]#|\w#)(_?[a-z]+[\w]?(?:(?:-+[\w]+)|(?:[\w]?))(?=(?:[^"]*"[^"]*")*[^"]*$))`i' => "[@id=\"\${2}\"]",
            /* classes */
            '`(\.)(?<=\.)(_?[a-z]+[\w]?(?:(?:-+[\w]+)|(?:[\w]?))(?=(?:[^"]*"[^"]*")*[^"]*$))`i' => "[@class~=\"\${2}\"]",
            /* tag (no attr, no funcs, no pseudo, no axis) to local-name() */
            '`(?<=^|::|[\s\[/\(])(_?[a-z]+[\w]?(?:(?:-+[\w]+)|(?:[\w]?))(?=(?:[^"]*"[^"]*")*[^"]*$))(?!\w*(?:[\(\]-]|(?:.)?=|::))`i' => "[local-name()=\"\${1}\"]",

            /* short axis */
            /* * to \\*\* */
            '`(?<![=])(\s)+(\*)(\s)+(?![=])(?=(?:[^"]*"[^"]*")*[^"]*$)`' => "//\${2}/\${2}",
            /* lock + ~ > */
            '`(?<![=])(\s)?(\+|~|>|\s)(\s)?(?![=])(?=(?:[^"]*"[^"]*")*[^"]*$)`' => "|\${2}|",
            /* unlock * */
            '`\|(\s)\|`' => '//',
            /* unlock > */
            '`\|(>)\|`' => '/',
            /* unlock ~ */
            '`\|(~)\|`' => "/following-sibling::",
            /* unlock + */
            '`\|(\+)\|`' => "/following-sibling::*[1]/self::",

            /* pseudos and func */
            /* pseudo classes to short attr (exists or not) or class*/
            '`:(disabled|checked|selected|link|visited|hover|active|focus)`' => "[(@\${1} or @class~=\"\${1}\")]",
            /* :first-line - i think it usable in browser only */
            /* :first-letter - think about it and implement later */
            /* :lang(c) - think about it and implement later */
            '`(\*?\[[^\]]+\]):first-child`' => "*[1]/self::\${1}",
            '`(\*?\[[^\]]+\]):last-child`' => "\${1}[not(following-sibling::*)]",
            '`(\*?\[[^\]]+\]):only-child`' => "*[last()=1]/self::\${1}",
            '`(\*?\[[^\]]+\]):empty`' => "\${1}[not(*) and not(normalize-space())]",
            /* :not */
            '`:not\((\(?(.*)\)?)\)`' => "[not(\${1})]",
            /* :contains(selectors) */
            '`:contains\((\(?(.*)\)?)\)`' => "[contains(string(.),\${1})]",

            /* ~= attribute */
            '`((?<=[\s\[])@?(_?[a-z]+[\w]?(?:(?:-+[\w]+)|(?:[\w]?))(?=(?:[^"]*"[^"]*")*[^"]*$))(?=(?:\]|.?=))(?:(.?=)?([^\]]*)?))(\~=)([^\]]+)`i' => "contains(concat(\" \",normalize-space(\${1}),\" \"),concat(\" \",\${6},\" \"))",
            /* |= attribute */
            '`((?<=[\s\[])@?(_?[a-z]+[\w]?(?:(?:-+[\w]+)|(?:[\w]?))(?=(?:[^"]*"[^"]*")*[^"]*$))(?=(?:\]|.?=))(?:(.?=)?([^\]]*)?))(\|=)([^\]]+)`i' => "\${1}=\${6} or starts-with(\${1},concat(\${6},\"-\"))",
            /* *= attribute */
            '`((?<=[\s\[])@?(_?[a-z]+[\w]?(?:(?:-+[\w]+)|(?:[\w]?))(?=(?:[^"]*"[^"]*")*[^"]*$))(?=(?:\]|.?=))(?:(.?=)?([^\]]*)?))(\*=)([^\]]+)`i' => "contains(\${1},\${6})",
            /* ^= attribute */
            '`((?<=[\s\[])@?(_?[a-z]+[\w]?(?:(?:-+[\w]+)|(?:[\w]?))(?=(?:[^"]*"[^"]*")*[^"]*$))(?=(?:\]|.?=))(?:(.?=)?([^\]]*)?))(\^=)([^\]]+)`i' => "starts-with(\${1},\${6})",
            /* != attribute */
            '`((?<=[\s\[])@?(_?[a-z]+[\w]?(?:(?:-+[\w]+)|(?:[\w]?))(?=(?:[^"]*"[^"]*")*[^"]*$))(?=(?:\]|.?=))(?:(.?=)?([^\]]*)?))(\!=)([^\]]+)`i' => "(not(\${1}) or \${1}!=\${6})",

        );
        $selector = $this->replace($patterns, $selector);
        $selector = preg_replace_callback(
        /* $= attribute */
            '`((?<=[\s\[])@?(_?[a-z]+[\w]?(?:(?:-+[\w]+)|(?:[\w]?))(?=(?:[^"]*"[^"]*")*[^"]*$))(?=(?:\]|.?=))(?:(.?=)?([^\]]*)?))\$=([^\]]+)`i',
            function ($matches) {
                return "substring(" . $matches[1] . ",string-length(" . $matches[1] . ")-" . (strlen($matches[5])-2) . ")=" . $matches[5];
            },
            $selector
        );
        $selector = preg_replace_callback(
        /* :nth-child */
            '`(\*?\[[^\]]+\]):nth-child\((.*)\)`',
            function ($matches) {
                $a = $matches[1];
                $b = trim($matches[2], '"');
                switch ($b) {

                    case "n":
                        return $a;

                    case "even":
                        return "[position() mod 2=0 and position()>=0]/self::*" . $a;

                    case "odd":
                        return $a . "[(count(preceding-sibling::*) + 1) mod 2=1]";

                    default:
                        $b = $b || "0";
                        $b = preg_replace('`^([0-9]*)n.*?([0-9]*)$`', "\${1}+\${2}", $b);
                        $b = explode(" + ", $b);
                        $b[1] = $b[1] || "0";
                        return "*[(position()-" . $b[1] . ") mod " . $b[0] . "=0 and position()>=" . $b[1] . "]/self::*" . $a;
                }
            },
            $selector
        );
        return $selector;
    }
}