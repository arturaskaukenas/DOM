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

namespace ArturasKaukenas\tests\unit\Node;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use ArturasKaukenas\DOM;
use ArturasKaukenas\DOM\XML\Parser;
use ArturasKaukenas\tests\TestCase;

/**
 * Ported from simple_tests/XML/5.php ("5. INode get elements and set text contents").
 */
final class INodeActionsElementsAndTextContentTest extends TestCase {
	private DOM\INode $fullResult;
	private DOM\INode $result;
	private \stdClass $tracker;

	protected function setUp() : void {
		$this->tracker = new \stdClass;
		$this->tracker->tick = false;

		$content = $this->fixture("XML".\DIRECTORY_SEPARATOR."books.xml");

		$parser = new Parser();
		$parser->onFinalizeNode(
			"finalize-node-test",
			function (DOM\INode $node) : void {
				$this->tracker->tick = true;
			}
		);

		$this->fullResult = $parser->fullParse($content);
		$this->result = $this->fullResult->getChild(0);
	}

	#[Test]
	#[TestDox("getElementsByTagName")]
	public function getElementsByTagName() : void {
		$this->assertCount(3, $this->result->getElementsByTagName("publish_date"));
		$this->assertCount(3, $this->result->getElementsByTagName("pubLish_date "));
		$this->assertCount(0, $this->result->getElementsByTagName("wrong-tag"));
		$this->assertSame(2, (int) $this->fullResult->getElementsByTagName("id")[1]->getTextContents());
		$this->assertSame(2, (int) $this->result->getElementsByTagName("id")[1]->getTextContents());
		$this->assertSame(3, (int) $this->result->getElementsByTagName("book")[2]->getElementsByTagName("id")[0]->getTextContents());
	}

	#[Test]
	#[TestDox("getElementById")]
	public function getElementById() : void {
		$this->assertSame(
			"2000-11-17",
			$this->fullResult->getElementById("bk103")->getElementsByTagName("publish_date")[0]->getTextContents()
		);
		$this->assertSame(
			"2000-11-17",
			$this->result->getElementById("bk103")->getElementsByTagName("publish_date")[0]->getTextContents()
		);
	}

	#[Test]
	#[TestDox("getTextContents")]
	public function getTextContents() : void {
		$this->assertSame("2000-12-16", $this->fullResult->getElementById("text_content_test")->getTextContents());
	}

	#[Test]
	#[TestDox("setTextContents")]
	public function setTextContents() : void {
		$this->fullResult->getElementById("text_content_test")->setTextContents("2000-12-15");
		$this->assertSame("2000-12-15", $this->fullResult->getElementById("text_content_test")->getTextContents());

		$this->fullResult->getElementById("text_content_test")->setTextContents("2000-12-16");
		$this->assertSame("2000-12-16", $this->fullResult->getElementById("text_content_test")->getTextContents());
	}

	#[Test]
	#[TestDox("setTextContents->finalizeNode")]
	public function setTextContentsFinalizeNode() : void {
		$this->tracker->tick = false;
		$this->fullResult->getElementsByTagName("finalize-node-test")[0]->setTextContents("B");

		$this->assertTrue($this->tracker->tick);
	}
}
