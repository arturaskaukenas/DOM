#!/usr/bin/php -d display_errors=1
<?php
	require_once(__DIR__.\DIRECTORY_SEPARATOR."..".\DIRECTORY_SEPARATOR."load.php");
	$test_case = new \ArturasKaukenas\SimpleTest\TestCase(2, "Registered node, processors, validators");

	require_once(__DIR__."/src/book.php");

	use \ArturasKaukenas\DOM;

	$content = \file_get_contents("./data/books.xml");
	$parser = new DOM\XML\Parser();
	$parser
		->registerNode(new \ReflectionClass("\BOOK"))
		->onFinalizeNode(
			"BOOK",
			function(DOM\INode $node) {
				\assert(\is_int($node->ID));
				if ($node->ID === 1) {
					\assert($node->AUTHOR === \strtoupper("Gambardella, Matthew"));
					\assert($node->TITLE === "XML Developer's Guide");
					\assert($node->PRICE === null);
					\assert($node->getErrors()[0] === "'PRICE' validation failed: Should be cheaper than 10");
				} else if ($node->ID === 2) {
						\assert($node->AUTHOR === \strtoupper("Ralls, Kim"));
						\assert($node->PUBLISH_DATE instanceof \DateTime);
				}

				echo "\n\nBook:";
				echo "\nErrors:";
				var_dump($node->getErrors());
				echo "\n\n";
				echo "Title: ".$node->TITLE."\n";
				echo "Author: ".$node->AUTHOR."\n";
				echo "Price: ".$node->PRICE."\n";
				if ($node->PUBLISH_DATE instanceof \DateTime) {
					echo "publish_date: ".$node->PUBLISH_DATE->format(DateTime::ATOM)."\n";
				}
			}
		);
	$parser->fullParse($content);

	$test_case->done();