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

final class INodeActionsElementsAndTextContenCaseInsensitiveTest extends TestCase {
	private DOM\INode $fullResult;
	private DOM\INode $result;
	private \stdClass $tracker;

	protected function setUp() : void {
		$this->tracker = new \stdClass;
		$this->tracker->tick = false;

		$content = $this->fixture("XML".\DIRECTORY_SEPARATOR."books.xml");

		$parser = new Parser(false);
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
		$this->assertSame(2, (int) $this->result->getElementsByTagName("Id")[1]->getTextContents());
		$this->assertSame(3, (int) $this->result->getElementsByTagName("bOok")[2]->getElementsByTagName("id")[0]->getTextContents());
	}
}
