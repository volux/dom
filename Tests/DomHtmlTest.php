<?php
namespace tests;
use \volux\Dom;

class DomHtmlTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Dom\Document
     */
    private $dom;
    private $text = 'Hello Foo';

    public function setUp()
    {
        $this->dom = new Dom\Html();
        $this->dom->body()->append('<div id="test" class="content">'.$this->text.'</div>');
    }

    public function testDomHtml()
    {
        $this->assertEquals($this->text, $this->dom->find('.content')->text());
        $this->assertEquals($this->text, $this->dom->find('#test')->text());
    }
}
