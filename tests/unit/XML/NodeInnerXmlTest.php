<?php
/*

Copyright 2024 Artūras Kaukėnas

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

*/

namespace ArturasKaukenas\tests\unit\XML;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use ArturasKaukenas\DOM;
use ArturasKaukenas\DOM\XML\Parser;
use ArturasKaukenas\DOM\XML\StdNode;
use ArturasKaukenas\tests\Fixtures\XML\Book;
use ArturasKaukenas\tests\TestCase;

/**
 * Ported from simple_tests/XML/6.php ("6. Advanced actions").
 *
 * Each part below is an independent PHPUnit test (fresh setUp() per test).
 * setInnerXML() always discards the node's previous children/data before
 * re-parsing, so a part only needs to replay whichever earlier-part
 * mutation would otherwise have changed what it asserts against (e.g. the
 * removeAttribute("test_attribute") call that originally preceded "getInnerXML
 * - CDATA" and everything after it).
 */
final class NodeInnerXmlTest extends TestCase {
	private DOM\INode $result;
	private StdNode $container;
	private \stdClass $tracker;

	protected function setUp() : void {
		$this->tracker = new \stdClass;
		$this->tracker->tick = false;

		$content = $this->fixture("XML".\DIRECTORY_SEPARATOR."books.xml");

		$parser = new Parser();
		$parser
			->registerNode(new \ReflectionClass(Book::class))
			->onFinalizeNode(
				"BOOK",
				function (DOM\INode $node) : void {
					$this->tracker->tick = true;
				}
			);

		$this->result = $parser->fullParse($content);

		$this->container = new StdNode;
		$this->container->setName("CONTAINER");
		$this->container->setAttributes(
			[
				"id" => "CONTAINER",
				"Test_Attribute" => "'\"",
			]
		);
		$this->result->appendChild($this->container);
	}

	#[Test]
	#[TestDox("6.1 getInnerXML - basic")]
	public function getInnerXmlBasic() : void {
		$XML = <<<END
<CONTAINER id="CONTAINER" test_attribute="&apos;&quot;"></CONTAINER>
END;

		$this->assertSame($XML, $this->container->getInnerXML());
	}

	#[Test]
	#[TestDox("6.2 getInnerXML - CDATA")]
	public function getInnerXmlCdata() : void {
		$this->container->removeAttribute("test_attribute");
		$this->container->setTextContents("<TAG data=\"data\"></TAG>");

		$XML = <<<END
<CONTAINER id="CONTAINER"><![CDATA[<TAG data="data"></TAG>]]></CONTAINER>
END;

		$this->assertSame($XML, $this->container->getInnerXML());
	}

	#[Test]
	#[TestDox("6.3 setInnerXML")]
	public function setInnerXml() : void {
		$this->container->removeAttribute("test_attribute");

		// 1
		$XML = <<<END
<CONTAINER-CHILD id="CONTAINER-CHILD" attribute="a"><![CDATA[<TAG data="data"></TAG>]]></CONTAINER-CHILD>
END;
		$this->container->setInnerXML($XML);
		$XML = <<<END
<CONTAINER id="CONTAINER"><CONTAINER-CHILD id="CONTAINER-CHILD" attribute="a"><![CDATA[<TAG data="data"></TAG>]]></CONTAINER-CHILD></CONTAINER>
END;
		$this->assertSame($XML, $this->container->getInnerXML());

		// 2
		$XML = <<<END
<CONTAINER id="CONTAINER"></CONTAINER>
END;
		$this->result->getElementById("CONTAINER-CHILD")->remove();
		$this->assertSame($XML, $this->container->getInnerXML());

		// 3
		$XML = <<<END
<CONTAINER-CHILD id="CONTAINER-CHILD" attribute="'&quot;a"><![CDATA[<TAG data="data"></TAG>]]></CONTAINER-CHILD>
END;
		$this->container->setInnerXML($XML);
		$XML = <<<END
<CONTAINER id="CONTAINER"><CONTAINER-CHILD id="CONTAINER-CHILD" attribute="&apos;&quot;a"><![CDATA[<TAG data="data"></TAG>]]></CONTAINER-CHILD></CONTAINER>
END;
		$this->assertSame($XML, $this->container->getInnerXML());
	}

	#[Test]
	#[TestDox("6.4 setInnerXML templated node - triggered")]
	public function setInnerXmlTemplatedNodeTriggered() : void {
		$this->container->setInnerXML($this->dummyBookXML());
		$this->result->getElementById("dummy_book");

		$this->assertTrue($this->tracker->tick);
	}

	#[Test]
	#[TestDox("6.5 setInnerXML templated node - validate triggered")]
	public function setInnerXmlTemplatedNodeValidateTriggered() : void {
		$node = $this->dummyBookNode();

		$this->assertNull($node->PRICE);
		$this->assertSame("'PRICE' validation failed: Should be cheaper than 10", $node->getErrors()[0]);
	}

	#[Test]
	#[TestDox("6.6 setInnerXML templated node - type cast")]
	public function setInnerXmlTemplatedNodeTypeCast() : void {
		$node = $this->dummyBookNode();

		$node->getElementsByTagName("price")[0]->setTextContents("6");
		$this->assertSame(6.0, $node->PRICE);
	}

	#[Test]
	#[TestDox("6.7 setInnerXML templated node - process")]
	public function setInnerXmlTemplatedNodeProcess() : void {
		$node = $this->dummyBookNode();

		$this->assertSame(\strtoupper("Surname, Name"), $node->AUTHOR);
		$node->getElementsByTagName("author")[0]->setTextContents("Dummy Author");
		$this->assertSame(\strtoupper("Dummy Author"), $node->AUTHOR);
	}

	private function dummyBookXML() : string {
		return <<<END
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
	}

	private function dummyBookNode() : DOM\INode {
		$this->container->setInnerXML($this->dummyBookXML());

		return $this->result->getElementById("dummy_book");
	}
}
