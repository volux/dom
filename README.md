## volux\Dom

PHP version >= 5.3.6 extended DOM Objects via \DOMDocument::registerNodeClass with jQuery-like functionality:
    - volux\Dom > \DOMDocument, volux\Attr > \DOMAttr, volux\Tag > volux\Element > \DOMElement, volux\Text > \DOMText, volux\Comment > \DOMComment, volux\Cdata > \DOMCdataSection;
    - extended \DOMXPath via volux\XPath with converting CSS selectors to XPath expression;
    - volux\Xslt class implement shadow load XSLT file or XSLT string (including from lambda function) and transformation with replacing target element;
    - wrapped DOMNodelist and DOMNamedNodeMap with volux\Set who inplement \ArrayIterator and \RecursiveIterator interfaces.

### Build html example

```php
<?php
use volux\Dom\Html;

$html = new Html();
$html
	->root()->attr(array('lang' => 'en'));
$html
	->meta(array('charset' => 'UTF-8'))
	->meta(array(
		'http-equiv' => 'X-UA-Compatible',
		'content' => 'IE=edge,chrome=1',
	))
	->meta(array(
		'name' => 'viewport',
		'content' => 'width=device-width,user-scalable=0',
	))
	->title('Build HTML Test')
	->stylesheet('//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-combined.no-icons.min.css')
	->script('//cdnjs.cloudflare.com/ajax/libs/jquery/1.9.1/jquery.min.js')
	->stylesheet('/css/app.css')
	->script('/js/app.js');

$navBarInner = $html->body()
	->a('header', array('class' => 'navbar navbar-fixed-top'))
		->a('div', array('class' => 'navbar-inner'))
			->a('a', array(
				'class' => 'brand image',
				'href' => '/',
				'title' => 'Test')
			)->add('<strong>Test</strong> app')
		->parent()
		->a('ul', array('class'=>'nav'))
			->a('li', array('class'=>'divider-vertical'), false)
		->parent()
	->parent();

$html->body()
	->append('div')->attr('id', 'main')
	->addClass('some test content') # for example
	->removeClass('test some') # for example
	->append('p')
		->attr('class', '3')
		->text('1. Text with <em>entity</em><br>and ')
		->add('<a href="#"><i class="icon-2"> </i>valid xml	1</a>.')
		->text(' And<br>any more text 1.') /* text is added by default */
		->before('h1')
			->add('Value with<br><span class=test>not valid xml</span>');

echo $html;
/**
* you can use $navBarInner for add some element later:
*
* for example $navBarInner->add(file_get_contents($templateDir.'menu.html'))
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
<html lang="en">
  	<head>
    	<meta charset="UTF-8"/>
    	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    	<meta name="viewport" content="width=device-width,user-scalable=0"/>
    	<title>Build HTML Test</title>
    	<link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-combined.no-icons.min.css" rel="stylesheet"/>
    	<link href="/css/app.css" rel="stylesheet"/>
  	</head>
  	<body>
    	<header class="navbar navbar-fixed-top"><div class="navbar-inner"><a class="brand image" href="/" title="Test"><strong>Test</strong> app</a><ul class="nav"><li class="divider-vertical"></li></ul></div></header>
    	<div id="main" class="content">
      		<h1>Value with&lt;br&gt;&lt;span class=test&gt;not valid xml&lt;/span&gt;</h1>
      		<p class="3">1. Text with &lt;em&gt;entity&lt;/em&gt;&lt;br&gt;and <a href="#"><i class="icon-2"> </i>valid xml	1</a>. And&lt;br&gt;any more text 1.</p>
    	</div>
    	<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    	<script src="/js/app.js"></script>
  	</body>
</html>
```

### Parse external html example

```php
<?php
use volux\Dom;

$htmlResult = new Dom\Html();

$request = file_get_contents('http://news.google.com/news');
/** You can use any transport for retrieve outer site content like curl */

$htmlResult = new Dom\Html();
$htmlResult->title('Google News Test');

if ($request) {

	$htmlRequest = new Dom\Html();
	$htmlRequest->html($request)
	    ->find('.titletext')
	        ->each(function (Dom\Tag $node, $index) use ($htmlResult) {
	            /** @var $node Dom\Tag */
	            $htmlResult->body()
	                ->append('p')
	                    ->append('span')->text(($index + 1) . ': ')
	                ->parent()
	                    ->append('a')->attr('href', $node->parent()->attr('href'))->text($node->text());
	        });

} else {

	$htmlResult->body()->append('div', array('class'=>'empty'), 'Request to Google is empty');
}

echo $htmlResult;
```

### XSLT transform example

```php
<?php

use volux\Dom;

$html = new Dom\Html();

$html->load('example.html');
$html->find('.content')->transform('xslt/content.xsl'); /* each tags with class="content" will be transformed and replaced */

$html->saveHTMLfile('transformed.html');
```
