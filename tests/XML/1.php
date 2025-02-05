#!/usr/bin/php -d display_errors=1
<?php
	require_once(__DIR__."/../load.php");
	$test_case = new \ArturasKaukenas\SimpleTest\TestCase(1, "Basic");

	use \ArturasKaukenas\DOM;

	$content = \file_get_contents("./data/books.xml");
	$parser = new DOM\XML\Parser();
	$parser->fullParse($content);
	$test_case->done();