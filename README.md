## volux\Dom [![Build Status](https://secure.travis-ci.org/volux/dom.png?branch=master)](http://travis-ci.org/volux/dom)

PHP version >= 5.3.6 extended DOM Objects via \DOMDocument::registerNodeClass with jQuery-like functionality:
 + volux\Dom\Html > volux\Dom\Document > \DOMDocument, volux\Dom\Attr > \DOMAttr, volux\Dom\Tag > volux\Dom\Element > \DOMElement, volux\Dom\Text > \DOMText, volux\Dom\Comment > \DOMComment, volux\Dom\Cdata > \DOMCdataSection;
 + volux\Dom\Table and volux\Dom\Form (with volux\Dom\Field) helpers;
 + extended \DOMXPath via volux\Dom\XPath with ***converting CSS selectors to XPath expression***;
 + volux\Dom\Xslt class implement shadow load XSLT file or XSLT string (including from lambda function) and transformation with replacing target element;
 + wrapped DOMNodelist and DOMNamedNodeMap with volux\Set who implement \ArrayIterator and \RecursiveIterator interfaces.

### Links

[volux\Dom Wiki](https://github.com/volux/dom/wiki)

### Install

Add to your composer.json
```json
{
    "require": {
        "volux/dom": "dev-master"
    }
}
```
or copy ``src\volux`` to ``vendor`` directory and use your PRS-0 autoloader.

### Parse external html example

```php
<?php
use volux\Dom;

$htmlResult = new Dom\Html();

$htmlResult->title('Google News Test');

Html::doc()->load('http://news.google.com/news')
    ->find('.titletext')
        ->each(function ($node, $index) use ($htmlResult) {
            $htmlResult->body()
                ->append('p')
                    ->append('span')->text(($index + 1) . ': ')
                ->parent()
                    ->append('a')->attr('href', $node->parent()->attr('href'))->text($node->text());
        });

echo $htmlResult;
```

### XSL transform example

```php
<?php

use volux\Dom;

Dom\Document::doc()->load('example.xml')
    ->find('.content')->xslt('content.xsl')
        ->end()
    ->saveHTMLfile('transformed.html');

/* each tags with class="content" will be transformed and replaced */
```

### Build form example

```php
use volux\Dom;

$form = new Dom\Form();
$form
    ->fieldSet('Group 1')
        ->add()
            ->input()->name('name')
                ->placeholder('Name')
                ->required()
                ->dataList(array('Nike', 'Mike', 'Fred'))
        ->add()
            ->select(array('1'=>'One','2'=>'Two','3'=>'Three'), '2')
            ->name('sel')
            ->label('Select item')
        ->add()
            ->input('0a0b0c0d', 'hidden')->name('token')
->form()
    ->fieldSet('Group 2')
        ->add()
            ->radio('choose', array('r'=>'Red', 'g'=>'Green', 'b'=>'Blue'))
        ->add()
            ->space()
        ->add()
            ->textarea('', array('name'=>'desc'))->label('Description')
                ->rows(5)->addClass('span4')
                ->help('Input short description about it', 'help-block')
->form()
    ->checkbox('confirm', array('Confirm'))
->form()
    ->section('form-actions')
        ->add()
            ->buttonSubmit('Send as GET')
                ->addClass('btn btn-primary')
        ->add()
            ->space('h')
        ->add()
            ->buttonSubmit('Send as POST')
                ->addClass('btn btn-success')
                ->formMethod('post')
;

$html = new Dom\Html();
$html
    ->root()->attr(array('lang' => 'en'));
$html
    ->meta(array('charset' => Html::ENCODING))
    ->title('volux\Dom\Form Test')
    ->stylesheet('//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.no-icons.min.css')
    ->body()
        ->append('div')->attr('class', 'row')
            ->append('div')->attr('class', 'span4 offset1')
                ->append($form) /* point to add builded form to main html */
;

echo $html;
```

### Build html complex example

```php
<?php
use volux\Dom;

$html = new Dom\Html();
$html
    ->root()->attr('lang', 'en');
$html
    ->meta(array('charset' => 'utf-8'))
    ->meta(array(
        'http-equiv' => 'X-UA-Compatible',
        'content'    => 'IE=edge,chrome=1',
    ))
    ->meta(array(
        'name'    => 'viewport',
        'content' => 'user-scalable=no, width=device-width, initial-scale=1, maximum-scale=1',
    ))
    ->title('volux\Dom\Html Test')
    ->stylesheet('//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-combined.no-icons.min.css')
    ->script('//cdnjs.cloudflare.com/ajax/libs/jquery/1.9.1/jquery.min.js')
    ->stylesheet('/css/app.css')
    ->script('/js/app.js');

$navBarInner = $html
    ->body()
        ->a('header', array('class' => 'navbar navbar-fixed-top'))
            ->a('div', array('class' => 'navbar-inner'))
                ->a('a', array(
                        'class' => 'brand image',
                        'href'  => '/',
                        'title' => 'volux\Dom\Html Test')
                    )
                    ->append('<strong>volux\Dom\Html Test</strong>')
                ->parent()
            ->parent()
                ->a('ul', array('class' => 'nav'))
                    ->a('li', array('class' => 'divider-vertical'), false)
                ->parent()
            ->parent();

$html->body()->append('div')
    ->id('main')
    ->addClass('some test content') /* for example */
    ->removeClass('test some') /* for example */
        ->append('p')
            ->attr('class', 'p3')
            ->append('1. Text with <em>entity</em> &copy;<br>and ')
                ->append('<a href="#"><i class="icon-2"> </i>valid xml </a>')
            ->parent()
                ->append(' And<br>any more text.')
            ->parent()
        ->before('h1')
            ->append('Value with<br><span class=test>not valid xml');

echo $html;
/**
* you can use $navBarInner for add some element later:
*
* for example $navBarInner->load($templateDir.'menu.html')
*
* or $navBarInner->a('ul', array('class' => 'nav'))
*     ->l('li', array(
*      	  array('>' => 'a', 'First', 'href' => '/item1'),
*      	  array('>' => 'a', 'Second', 'href' => '/item2'),
*     ));
*/
```
Result:
```html
<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1, maximum-scale=1">
        <title>volux\Dom\Html Test</title>
        <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-combined.no-icons.min.css" rel="stylesheet">
        <link href="/css/app.css" rel="stylesheet">
    </head>
    <body>
        <header class="navbar navbar-fixed-top">
            <div class="navbar-inner">
                <a class="brand image" href="/" title="volux\Dom\Html Test"><strong>volux\Dom\Html Test</strong></a>
                <ul class="nav"><li class="divider-vertical"></li></ul>
            </div>
        </header>
        <div id="main" class="content">
            <h1>Value with<br><span class="test">not valid xml</span></h1>
            <p class="p3">1. Text with <em>entity</em> ©<br>and <a href="#"><i class="icon-2"> </i>valid xml </a>And<br>any more text.</p>
        </div>
        <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script><script src="/js/app.js"></script>
    </body>
</html>
```

### TODO

Implement most important & relevant functional in jQuery API Manipulation & Traversing sections:
 + .contents()
 + .css()
 + .detach()
 + .index()
 + .nextAll()
 + .nextUntil()
 + .not()
 + .slice()
 + .toggleClass()
 + .wrapAll()
 + .wrapInner()
 + e.t.c.
