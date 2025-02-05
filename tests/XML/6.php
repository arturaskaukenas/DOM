#!/usr/bin/php -d display_errors=1
<?php
	require_once(__DIR__."/../load.php");
	$test_case = new \ArturasKaukenas\SimpleTest\TestCase(6, "Advanced actions");

	require_once(__DIR__."/src/book.php");

	use \ArturasKaukenas\DOM;

	$tracker = new \stdClass;
	$tracker->tick = false;

	$content = \file_get_contents("./data/books.xml");
	$parser = new DOM\XML\Parser();
	$parser
		->registerNode(new \ReflectionClass("\BOOK"))
		->onFinalizeNode(
			"BOOK",
			function(DOM\INode $node) use ($tracker) {
				$tracker->tick = true;
			}
		);
	$result = $parser->fullParse($content);

	$container = new DOM\XML\StdNode;
	$container->setName("CONTAINER");
	$container->setAttributes(
		array(
			"id" => "CONTAINER",
			"Test_Attribute" => "'\""
		)
	);
	$result->appendChild($container);

	//getInnerXML - basic
$test_case->part("getInnerXML - basic");
	$XML = <<<END
<CONTAINER id="CONTAINER" test_attribute="&apos;&quot;"></CONTAINER>
END;
	assert($container->getInnerXML() === $XML);

	//getInnerXML - CDATA
	$container->removeAttribute("test_attribute");
	$test_case->part("getInnerXML - CDATA");
	$container->setTextContents("<TAG data=\"data\"></TAG>");
	$XML = <<<END
<CONTAINER id="CONTAINER"><![CDATA[<TAG data="data"></TAG>]]></CONTAINER>
END;
	assert($container->getInnerXML() === $XML);

	//setInnerXML
	$test_case->part("setInnerXML");
	//1
	$XML = <<<END
<CONTAINER-CHILD id="CONTAINER-CHILD" attribute="a"><![CDATA[<TAG data="data"></TAG>]]></CONTAINER-CHILD>
END;
	$container->setInnerXML($XML);
$XML = <<<END
<CONTAINER id="CONTAINER"><CONTAINER-CHILD id="CONTAINER-CHILD" attribute="a"><![CDATA[<TAG data="data"></TAG>]]></CONTAINER-CHILD></CONTAINER>
END;
	assert($container->getInnerXML() === $XML);

	//2
$XML = <<<END
<CONTAINER id="CONTAINER"></CONTAINER>
END;
	$result->getElementById("CONTAINER-CHILD")->remove();
	assert($container->getInnerXML() === $XML);

	//3
	$XML = <<<END
<CONTAINER-CHILD id="CONTAINER-CHILD" attribute="'&quot;a"><![CDATA[<TAG data="data"></TAG>]]></CONTAINER-CHILD>
END;
	$container->setInnerXML($XML);
$XML = <<<END
<CONTAINER id="CONTAINER"><CONTAINER-CHILD id="CONTAINER-CHILD" attribute="&apos;&quot;a"><![CDATA[<TAG data="data"></TAG>]]></CONTAINER-CHILD></CONTAINER>
END;
	assert($container->getInnerXML() === $XML);

	//setInnerXML templated node - triggered
	$test_case->part("setInnerXML templated node - triggered");
	$tracker->tick = false;

	$XML = <<<END
<book id="dummy_book">
	<id>99</id>
	<author>Surname, Name</author>
	<title>Dummy Title</title>
	<genre>Dummy Genre</genre>
	<price>15</price>
	<publish_date>2024-03-24</publish_date>
	<description>Dummy Description</description>
	<finalize-node-test2>A</finalize-node-test2>
</book>
END;
	$container->setInnerXML($XML);
	$node = $result->getElementById("dummy_book");
	\assert($tracker->tick);

	//setInnerXML templated node - validate triggered
	$test_case->part("setInnerXML templated node - validate triggered");
	\assert($node->PRICE === null);
	\assert($node->errors[0] === "'PRICE' validate failed: Should be cheaper than 10");

	//setInnerXML templated node - type cast
	$test_case->part("setInnerXML templated node - type cast");
	$node->getElementsByTagName("price")[0]->setTextContents("6");
	\assert($node->PRICE === (float) 6);

	//setInnerXML template node - process
	$test_case->part("setInnerXML templated node - process");
	\assert($node->AUTHOR === \strtoupper("Surname, Name"));
	$node->getElementsByTagName("Author")[0]->setTextContents("Dummy Author");
	\assert($node->AUTHOR === \strtoupper("Dummy Author"));

	$test_case->done();