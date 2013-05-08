Dom
===

php 5.3 extended DOM Objects (DOMAttr and DOMElement, wrap DOMNodelist and DOMNamedNodeMap) via DOMDocument::registerNodeClass with jQuery-like functionality.

Build html example
------------------

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
		->title('Test')
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
	/**
	* you can use $navBarInner for add some element later:
	* for example $navBarInner->add(file_get_contents($templateDir.'menu.html'))
	* or $navBarInner->a('ul', array('class' => 'nav'))
	* 		->l('li', array(
	*      	array('>' => 'a', 'First', 'href' => '/item1'),
	*      	array('>' => 'a', 'Second', 'href' => '/item2'),
	*  	));
	*/
	$html->body()
		->append('div')->attr(array('id'=>'main', 'class'=>'content'))
		->addClass('some test content')
		->removeClass('test some')
		->append('p')
			->attr('class', '3')
			->text('1. Text with <em>entity</em><br>and ')
			->add('<a href="#"><i class="icon-2"> </i>valid xml	1</a>.')
			->text(' And<br>any more text 1.', true)
			->before('h1')
				->add('Value with<br><span class=test>not valid xml</span>');
	
	echo $html;

Result:

	<!DOCTYPE html>
	<html lang="en">
  	<head>
	    	<meta charset="UTF-8"/>
	    	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
	    	<meta name="viewport" content="width=device-width,user-scalable=0"/>
	    	<title>Test</title>
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

Parse html example
------------------

Coming soon...
