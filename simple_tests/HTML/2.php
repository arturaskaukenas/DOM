#!/usr/bin/php -d display_errors=1
<?php
	require_once(__DIR__.\DIRECTORY_SEPARATOR."..".\DIRECTORY_SEPARATOR."load.php");
	$test_case = new \ArturasKaukenas\SimpleTest\TestCase(2, "Advanced actions");

	use \ArturasKaukenas\DOM\HTML;
	use \ArturasKaukenas\DOM;

	$tracker = new \stdClass;
	$tracker->tick = false;

	$content = \file_get_contents("./data/w3c.html");
	
	class SCRIPT extends HTML\HTMLElement {
		public bool $dataAsChildren = false;
	}

	$test_case->part("Parsing");
	$parser = new HTML\Parser(false);
	$parser
		->registerNode(new \ReflectionClass("\SCRIPT"))
		->onFinalizeNode(
			"SCRIPT",
			function(HTML\INode $node) use ($tracker) {
				$tracker->tick = true;
				$node->js = $node->getData();
			}
		);
	$document = $parser->fullParse($content);

	$container = new HTML\HTMLElement;
	$container->setName("CONTAINER");
	$container->setAttributes(
		array(
			"id" => "CONTAINER",
			"Test_Attribute" => "'\""
		)
	);
	$document->appendChild($container);

	//getInnerHTML - basic
$test_case->part("getInnerHTML - basic");
	$HTML = <<<END
<CONTAINER id="CONTAINER" test_attribute="&apos;&quot;"></CONTAINER>
END;
	assert($container->getInnerHTML() === $HTML);

	//getInnerHTML - data
	$container->removeAttribute("test_attribute");
	$test_case->part("getInnerHTML - data");
	$container->setTextContents("<TAG data=\"data\"></TAG>");
	$HTML = <<<END
<CONTAINER id="CONTAINER">&lt;TAG data=&quot;data&quot;&gt;&lt;/TAG&gt;</CONTAINER>
END;
	assert($container->getInnerHTML() === $HTML);

	//setInnerHTML
	$test_case->part("setInnerHTML");
	//1
	$HTML = <<<END
<CONTAINER-CHILD id="CONTAINER-CHILD" attribute="a">&lt;TAG data=&quot;data&quot;&gt;&lt;/TAG&gt;</CONTAINER-CHILD>
END;
	$container->setInnerHTML($HTML);
$HTML = <<<END
<CONTAINER id="CONTAINER"><CONTAINER-CHILD id="CONTAINER-CHILD" attribute="a">&lt;TAG data=&quot;data&quot;&gt;&lt;/TAG&gt;</CONTAINER-CHILD></CONTAINER>
END;
	assert($container->getInnerHTML() === $HTML);

	//2
$HTML = <<<END
<CONTAINER id="CONTAINER"></CONTAINER>
END;
	$document->getElementById("CONTAINER-CHILD")->remove();
	assert($container->getInnerHTML() === $HTML);

	//3
	$HTML = <<<END
<CONTAINER-CHILD id="CONTAINER-CHILD" attribute="'&quot;a">&lt;TAG data=&quot;data&quot;&gt;&lt;/TAG&gt;</CONTAINER-CHILD>
END;
	$container->setInnerHTML($HTML);
$HTML = <<<END
<CONTAINER id="CONTAINER"><CONTAINER-CHILD id="CONTAINER-CHILD" attribute="&apos;&quot;a">&lt;TAG data=&quot;data&quot;&gt;&lt;/TAG&gt;</CONTAINER-CHILD></CONTAINER>
END;
	assert($container->getInnerHTML() === $HTML);

	//setInnerHTML templated node - triggered
	$test_case->part("setInnerHTML templated node - triggered");
	$tracker->tick = false;

	$HTML = <<<END
<script id="test_script">alert(1);</script>
END;
	$container->setInnerHTML($HTML);
	\assert($tracker->tick);

	//setInnerHTML templated node - onFinalizeNode
	$test_case->part("setInnerHTML templated node - onFinalizeNode");
	$node = $document->getElementById("test_script");
	\assert($node->js === "alert(1);");
	\assert($node->getData() === "alert(1);");

	$test_case->done();