<?php
namespace tests;

use volux\Dom\Html;

class DomHtmlTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Html
     */
    private $dom;
    private $text = 'Hello Foo';

    public function setUp()
    {
        $this->dom = new Html();
        $this->dom->body()->append('<div id="test" class="content">'.$this->text.'</div>');
    }

    public function testDomHtml()
    {
        $this->assertEquals($this->text, $this->dom->find('.content')->text());
        $this->assertEquals($this->text, $this->dom->find('#test')->text());
    }
}
