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
use ArturasKaukenas\tests\TestCase;

/**
 * Ported from simple_tests/HTML/1.php ("1. Basic").
 */
final class ParserBasicTest extends TestCase {
	private HTML\INode $document;

	protected function setUp() : void {
		$parser = new Parser();
		$this->document = $parser->fullParse($this->fixture("HTML".\DIRECTORY_SEPARATOR."w3c.html"));
	}

	#[Test]
	#[TestDox("Parsing")]
	public function parsing() : void {
		$this->expectNotToPerformAssertions();

		$parser = new Parser();
		$parser->fullParse($this->fixture("HTML".\DIRECTORY_SEPARATOR."w3c.html"));
	}

	#[Test]
	#[TestDox("Basic actions - head")]
	public function basicActionsHead() : void {
		$elements = $this->document->getElementsByTagName("meta");
		$found = false;
		foreach ($elements as $element) {
			if (
				($element->getAttribute("property") === "og:title") && ($element->getAttribute("content") === "W3C")
			) {
				$found = true;
				break;
			}
		}

		$this->assertTrue($found);

		$this->assertSame(
			"/assets/website-2021/styles/advanced.css?ver=1.4",
			$this->document->getElementById("advanced-stylesheet")->getAttribute("href")
		);
		$this->document->getElementById("advanced-stylesheet")->setAttribute("href", null);
		$this->assertSame("null", $this->document->getElementById("advanced-stylesheet")->getAttribute("href"));
	}

	#[Test]
	#[TestDox("document->HEAD")]
	public function documentHead() : void {
		$this->assertCount(1, $this->document->HEAD->getElementsByTagName("title"));
	}

	#[Test]
	#[TestDox("document->TITLE")]
	public function documentTitle() : void {
		$this->assertSame("W3C", $this->document->TITLE);
	}

	#[Test]
	#[TestDox("document->BODY")]
	public function documentBody() : void {
		if (\count($this->document->HEAD->getElementsByTagName("div")) !== 0) {
			throw new \Exception("Wrong test data");
		}

		$this->assertGreaterThan(0, \count($this->document->getElementsByTagName("div")));
		$this->assertSame(
			\count($this->document->getElementsByTagName("div")),
			\count($this->document->BODY->getElementsByTagName("div"))
		);
	}
}
