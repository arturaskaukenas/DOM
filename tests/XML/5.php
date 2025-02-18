#!/usr/bin/php -d display_errors=1
<?php
	require_once(__DIR__.\DIRECTORY_SEPARATOR."..".\DIRECTORY_SEPARATOR."load.php");
	$test_case = new \ArturasKaukenas\SimpleTest\TestCase(5, "INode get elements and set text contents");

	use \ArturasKaukenas\DOM\XML\Parser;

	$tick = new \stdClass;
	$tick->tick = false;

	$content = \file_get_contents("./data/books.xml");
	$parser = new Parser();
	$parser
		->onFinalizeNode(
			"finalize-node-test",
			function(\ArturasKaukenas\DOM\INode $node) use ($tick) {
				$tick->tick = true;
			}
		);
	$full_result = $parser->fullParse($content);
	$result = $full_result->getChild(0);

	//getElementsByTagName
	$test_case->part("getElementsByTagName");
	\assert(\count($result->getElementsByTagName("publish_date")) === 3);
	\assert(\count($result->getElementsByTagName("pubLish_date ")) === 3);
	\assert(\count($result->getElementsByTagName("wrong-tag")) === 0);
	\assert((int) $full_result->getElementsByTagName("id")[1]->getTextContents() === 2);
	\assert((int) $result->getElementsByTagName("id")[1]->getTextContents() === 2);
	\assert((int) $result->getElementsByTagName("book")[2]->getElementsByTagName("id")[0]->getTextContents() === 3);

	//getElementById
	$test_case->part("getElementById");
	\assert($full_result->getElementById("bk103")->getElementsByTagName("publish_date")[0]->getTextContents() === "2000-11-17");
	\assert($result->getElementById("bk103")->getElementsByTagName("publish_date")[0]->getTextContents() === "2000-11-17");

	//getTextContents
	$test_case->part("getTextContents");
	\assert($full_result->getElementById("text_content_test")->getTextContents() === "2000-12-16");

	//setTextContents
	$test_case->part("setTextContents");
	$full_result->getElementById("text_content_test")->setTextContents("2000-12-15");
	\assert($full_result->getElementById("text_content_test")->getTextContents() === "2000-12-15");
	$full_result->getElementById("text_content_test")->setTextContents("2000-12-16");
	\assert($full_result->getElementById("text_content_test")->getTextContents() === "2000-12-16");

	//setTextContents->finalizeNode
	$test_case->part("setTextContents->finalizeNode");
	$tick->tick = false;
	$full_result->getElementsByTagName("finalize-node-test")[0]->setTextContents("B");
	\assert($tick->tick);

	$test_case->done();