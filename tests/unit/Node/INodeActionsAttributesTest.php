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
use ArturasKaukenas\DOM\INode;
use ArturasKaukenas\DOM\XML\Parser;
use ArturasKaukenas\tests\TestCase;

/**
 * Ported from simple_tests/XML/3.php ("3. INode attributes").
 */
final class INodeActionsAttributesTest extends TestCase {
	private INode $node;

	protected function setUp() : void {
		$content = $this->fixture("XML".\DIRECTORY_SEPARATOR."books.xml");

		$parser = new Parser();
		$this->node = $parser->fullParse($content)->getChild(0)->getChild(0);
	}

	#[Test]
	#[TestDox("hasAttributes")]
	public function hasAttributes() : void {
		$this->assertTrue($this->node->hasAttributes());
		$this->assertFalse($this->node->getChild(0)->hasAttributes());
	}

	#[Test]
	#[TestDox("hasAttribute")]
	public function hasAttribute() : void {
		$this->assertTrue($this->node->hasAttribute("testAttribute"));
		$this->assertTrue($this->node->hasAttribute("testattribute"));
		$this->assertFalse($this->node->hasAttribute("wrongAttribute"));
	}

	#[Test]
	#[TestDox("getAttributeNames")]
	public function getAttributeNames() : void {
		$this->assertSame(\strtolower("testAttribute"), $this->node->getAttributeNames()[1]);
		$this->assertArrayNotHasKey(1, $this->node->getChild(0)->getAttributeNames());
	}

	#[Test]
	#[TestDox("getAttribute")]
	public function getAttribute() : void {
		$this->assertSame("test", $this->node->getAttribute("testattribute"));
		$this->assertNull($this->node->getAttribute("wrongAttribute"));
	}

	#[Test]
	#[TestDox("setAttribute")]
	public function setAttribute() : void {
		$this->node->setAttribute("test", "2");
		$this->assertSame("2", $this->node->getAttribute("test"));

		$this->node->setAttribute("test", 2);
		$this->assertSame("2", $this->node->getAttribute("test"));

		$this->node->setAttribute("test", null);
		$this->assertSame("null", $this->node->getAttribute("test"));

		try {
			$this->node->setAttribute("*test", null);
			$this->fail("Expected exception was not thrown.");
		} catch (\Exception $e) {
			$this->assertSame(
				"Failed to execute 'setAttribute' on 'Node': '*test' is not a valid attribute name.",
				$e->getMessage()
			);
		}

		try {
			$this->node->setAttribute("-test", null);
			$this->fail("Expected exception was not thrown.");
		} catch (\Exception $e) {
		}

		$this->node->setAttribute("_-test", null);
		$this->assertSame("null", $this->node->getAttribute("_-test"));
	}

	#[Test]
	#[TestDox("testAttribute")]
	public function removeAttribute() : void {
		$this->node->removeAttribute("testAttribute");
		$this->assertNull($this->node->getAttribute("testAttribute"));
	}
}
