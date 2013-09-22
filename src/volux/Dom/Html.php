<?php
/**
* volux\Dom
*
* @link http://github.com/volux/dom
*/
namespace volux\Dom;

use volux\Dom;
/**
 * Class Html
 * @package volux\Dom
 * @author  Andrey Skulov <andrey.skulov@gmail.com>
 */
class Html extends Document
{
    const
        ELEMENT_CLASS = '\volux\Dom\Tag',
        HEAD_HTML = '<!DOCTYPE html>',
        AJAX_ROOT = 'reply'
    ;

    /** @var Tag */
    protected $head;
    /** @var Tag */
    protected $body;
    /** @var Tag */
    protected $scripts;
    /** @var Tag */
    protected $ajax;

    /** @var  Html */
    protected $container;
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

        $this->container = new Document();
        $this->container->root('keep');
        $this->scripts = $this->container->root()->append('scripts');
        $this->ajax = $this->container->root()->append(self::AJAX_ROOT);
    }

    /**
     * @param string $version
     * @param string $encoding
     *
     * @return Document|Html
     */
    public static function doc($version = self::VERSION, $encoding = self::ENCODING)
    {
        $html = new self($version, $encoding);
        return $html;
    }

    /**
     * @return $this|Document|Html
     */
    protected function fixHead()
    {
        $this->head = $this->root()->find('head', 0);
        if (self::NAME_NOT_MATCHED == $this->head->textContent) {
            $this->head = $this->root()->prepend('head')->next()->remove();
        }
        return $this;
    }

    /**
     * @param array $array
     *
     * @return Html|Tag|Field
     */
    public function createFromArray(array $array)
    {
        $html = self::doc();
        $html->removeChild($html->documentElement);
        $doc = $this->createDomFromArray($array, $html);
        if ($doc instanceof Html) {
            $doc->body = $doc->fixHead()->find('body', 0);
        }
        return $doc;
    }

    /**
     * @param null $htmlString
     * @param bool $asXml
     *
     * @return Html|string
     */
    public function html($htmlString = null, $asXml = false)
    {
        if (!is_null($htmlString)) {
            $this->loadHTML($htmlString);
            $this->body = $this->fixHead()->find('body', 0);
            return $this;
        }
        if ($this->ajax->childNodes->length) {

            $this->ajax->append($this->scripts->children());

            $this->documentElement->appendChild($this->importNode($this->ajax, true));

            if ($asXml) {
                return $this->saveXML($this->ajax);
            }
            return $this->saveHTML($this->ajax);
        }

        $this->body->append($this->scripts->children());

        if ($asXml) {
            return self::HEAD_HTML.PHP_EOL.$this->saveXML($this->documentElement);
        }
        return self::HEAD_HTML.PHP_EOL.$this->saveHTML($this->documentElement);
    }

    /**
     * @param string $source
     * @param int|null $options
     * @param bool     $result
     *
     * @return $this|Html
     */
    public function load($source, $options = LIBXML_NOCDATA, &$result = false)
    {
        $file = new File($source);
        return $this->html($file->getContents());
    }

    /**
     * @return Tag|Field
     */
    public function head()
    {
        return $this->head;
    }

    /**
     * @return Tag|Field
     */
    public function body()
    {
        return $this->body;
    }

    /**
     * @return Tag|Field
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
    public function title($titleText = '')
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
     * @param string $uri
     * @param string $type
     * @param bool|string $media
     *
     * @return $this|Html
     */
    public function stylesheet($uri, $type = 'stylesheet', $media = false)
    {
        $this->head->append('link')->attr(array('href' => $uri, 'rel' => 'stylesheet', 'media' => $media));
        return $this;
    }

    /**
     * @param string $css
     * @param bool $media
     *
     * @return $this|Html
     */
    public function style($css, $media = false)
    {
        $this->head->append('style')->attr('media', $media)->text("\n".$css."\n");
        return $this;
    }

    /**
     * @param string $uri
     *
     * @return $this|Html
     */
    public function favicon($uri = '/favicon.ico')
    {
        $this->head->append('link')->attr(array('href' => $uri, 'rel' => 'shortcut icon', 'type' => 'image/x-icon'));
        return $this;
    }

    /**
     * @param string $uri
     * @param null|string $code
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
        return $this->html();
    }
}