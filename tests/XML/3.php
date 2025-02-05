#!/usr/bin/php -d display_errors=1
<?php
	require_once(__DIR__."/../load.php");
	$test_case = new ArturasKaukenas\SimpleTest\TestCase(3, "INode attributes");

	use \ArturasKaukenas\DOM\XML\Parser;

	$content = \file_get_contents("./data/books.xml");
	$parser = new Parser();
	$node = $parser->fullParse($content)->getChild(0)->getChild(0);

	//hasAttributes
	$test_case->part("hasAttributes");
	\assert($node->hasAttributes());
	\assert(!$node->getChild(0)->hasAttributes()); //id

	//hasAttribute
	$test_case->part("hasAttribute");
	\assert($node->hasAttribute("testAttribute"));
	\assert($node->hasAttribute("testattribute"));
	\assert(!$node->hasAttribute("wrongAttribute"));

	//getAttributeNames
	$test_case->part("getAttributeNames");
	\assert($node->getAttributeNames()[1] === \strtolower("testAttribute"));
	\assert(!isset($node->getChild(0)->getAttributeNames()[1]));

	//getAttribute
	$test_case->part("getAttribute");
	\assert($node->getAttribute("testattribute") === "test");
	\assert($node->getAttribute("wrongAttribute") === null);
	
	//setAttribute
	$test_case->part("setAttribute");
	$node->setAttribute("test", "2");
	\assert($node->getAttribute("test") === "2");

	$node->setAttribute("test", 2);
	\assert($node->getAttribute("test") === "2");

	$node->setAttribute("test", null);
	\assert($node->getAttribute("test") === "null");

	try {
		$node->setAttribute("*test", null);
		assert(false);
	} catch (\Exception $e) {
		assert($e->getMessage() === "Failed to execute 'setAttribute' on 'Node': '*test' is not a valid attribute name.");
	}

	try {
		$node->setAttribute("-test", null);
		assert(false);
	} catch (\Exception $e) {}

	$node->setAttribute("_-test", null);
	\assert($node->getAttribute("_-test") === "null");

	//removeAttribute
	$test_case->part("testAttribute");
	$node->removeAttribute("testAttribute");
	\assert($node->getAttribute("testAttribute") === null);

	$test_case->done();