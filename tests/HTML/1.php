#!/usr/bin/php -d display_errors=1
<?php
	require_once(__DIR__.\DIRECTORY_SEPARATOR."..".\DIRECTORY_SEPARATOR."load.php");
	$test_case = new \ArturasKaukenas\SimpleTest\TestCase(1, "Basic");

	use \ArturasKaukenas\DOM;

	$content = \file_get_contents("./data/w3c.html");

	$test_case->part("Parsing");
	$parser = new DOM\HTML\Parser(false);
	$document = $parser->fullParse($content);

	$test_case->part("Basic actions - head");
	$elements = $document->getElementsByTagName("meta");
	$found = false;
	foreach ($elements as $element) {
		if (($element->getAttribute("property") === "og:title") && ($element->getAttribute("content") === "W3C")) {
			$found = true;
			break;
		}
	}
	\assert($found);

	\assert($document->getElementById("advanced-stylesheet")->getAttribute("href") === "/assets/website-2021/styles/advanced.css?ver=1.4");
	$document->getElementById("advanced-stylesheet")->setAttribute("href", null);
	\assert($document->getElementById("advanced-stylesheet")->getAttribute("href") === "null");

	$test_case->part("document->HEAD");
	\assert(\count($document->HEAD->getElementsByTagName("title")) === 1);

	$test_case->part("document->TITLE");
	\assert($document->TITLE === "W3C");

	$test_case->part("Basic actions - body");

	$test_case->part("document->BODY");
	if (\count($document->HEAD->getElementsByTagName("div")) !== 0) {
		throw new Exception("Wrong test data");
	}

	\assert(\count($document->getElementsByTagName("div")) > 0);
	\assert(\count($document->getElementsByTagName("div")) === \count($document->BODY->getElementsByTagName("div")));

	$test_case->done();