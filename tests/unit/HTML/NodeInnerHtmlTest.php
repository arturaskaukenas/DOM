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

namespace ArturasKaukenas\tests\unit\HTML;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use ArturasKaukenas\DOM\HTML;
use ArturasKaukenas\DOM\HTML\Parser;
use ArturasKaukenas\DOM\HTML\HTMLElement;
use ArturasKaukenas\tests\Fixtures\HTML\Script;
use ArturasKaukenas\tests\TestCase;

/**
 * Ported from simple_tests/HTML/2.php ("2. Advanced actions").
 *
 * Each part below is an independent PHPUnit test (fresh setUp() per test).
 * setInnerHTML() always discards the node's previous children/data before
 * re-parsing, so a part only needs to replay whichever earlier-part
 * mutation would otherwise have changed what it asserts against (e.g. the
 * removeAttribute("test_attribute") call that originally preceded
 * "getInnerHTML - data" and "setInnerHTML").
 */
final class NodeInnerHtmlTest extends TestCase {
	private HTML\INode $document;
	private HTMLElement $container;
	private \stdClass $tracker;

	protected function setUp() : void {
		$this->tracker = new \stdClass;
		$this->tracker->tick = false;

		$parser = new Parser();
		$parser
			->registerNode(new \ReflectionClass(Script::class))
			->onFinalizeNode(
				"SCRIPT",
				function (HTML\INode $node) : void {
					$this->tracker->tick = true;
					$node->js = $node->getData();
				}
			);

		$this->document = $parser->fullParse($this->fixture("HTML".\DIRECTORY_SEPARATOR."w3c.html"));

		$this->container = new HTMLElement;
		$this->container->setName("CONTAINER");
		$this->container->setAttributes(
			[
				"id" => "CONTAINER",
				"Test_Attribute" => "'\"",
			]
		);
		$this->document->appendChild($this->container);
	}

	#[Test]
	#[TestDox("getInnerHTML - basic")]
	public function getInnerHtmlBasic() : void {
		$HTML = <<<END
<CONTAINER id="CONTAINER" test_attribute="&apos;&quot;"></CONTAINER>
END;

		$this->assertSame($HTML, $this->container->getInnerHTML());
	}

	#[Test]
	#[TestDox("getInnerHTML - data")]
	public function getInnerHtmlData() : void {
		$this->container->removeAttribute("test_attribute");
		$this->container->setTextContents("<TAG data=\"data\"></TAG>");

		$HTML = <<<END
<CONTAINER id="CONTAINER">&lt;TAG data=&quot;data&quot;&gt;&lt;/TAG&gt;</CONTAINER>
END;

		$this->assertSame($HTML, $this->container->getInnerHTML());
	}

	#[Test]
	#[TestDox("setInnerHTML")]
	public function setInnerHtml() : void {
		$this->container->removeAttribute("test_attribute");

		// 1
		$HTML = <<<END
<CONTAINER-CHILD id="CONTAINER-CHILD" attribute="a">&lt;TAG data=&quot;data&quot;&gt;&lt;/TAG&gt;</CONTAINER-CHILD>
END;
		$this->container->setInnerHTML($HTML);
		$HTML = <<<END
<CONTAINER id="CONTAINER"><CONTAINER-CHILD id="CONTAINER-CHILD" attribute="a">&lt;TAG data=&quot;data&quot;&gt;&lt;/TAG&gt;</CONTAINER-CHILD></CONTAINER>
END;
		$this->assertSame($HTML, $this->container->getInnerHTML());

		// 2
		$HTML = <<<END
<CONTAINER id="CONTAINER"></CONTAINER>
END;
		$this->document->getElementById("CONTAINER-CHILD")->remove();
		$this->assertSame($HTML, $this->container->getInnerHTML());

		// 3
		$HTML = <<<END
<CONTAINER-CHILD id="CONTAINER-CHILD" attribute="'&quot;a">&lt;TAG data=&quot;data&quot;&gt;&lt;/TAG&gt;</CONTAINER-CHILD>
END;
		$this->container->setInnerHTML($HTML);
		$HTML = <<<END
<CONTAINER id="CONTAINER"><CONTAINER-CHILD id="CONTAINER-CHILD" attribute="&apos;&quot;a">&lt;TAG data=&quot;data&quot;&gt;&lt;/TAG&gt;</CONTAINER-CHILD></CONTAINER>
END;
		$this->assertSame($HTML, $this->container->getInnerHTML());
	}

	#[Test]
	#[TestDox("setInnerHTML templated node - triggered")]
	public function setInnerHtmlTemplatedNodeTriggered() : void {
		$this->container->setInnerHTML('<script id="test_script">alert(1);</script>');

		$this->assertTrue($this->tracker->tick);
	}

	#[Test]
	#[TestDox("setInnerHTML templated node - onFinalizeNode")]
	public function setInnerHtmlTemplatedNodeOnFinalizeNode() : void {
		$this->container->setInnerHTML('<script id="test_script">alert(1);</script>');
		$node = $this->document->getElementById("test_script");

		$this->assertSame("alert(1);", $node->js);
		$this->assertSame("alert(1);", $node->getData());
	}
}
