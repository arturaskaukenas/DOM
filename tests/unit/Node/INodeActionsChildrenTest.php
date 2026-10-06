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
use ArturasKaukenas\DOM\XML\StdNode;
use ArturasKaukenas\tests\TestCase;

/**
 * Ported from simple_tests/XML/4.php ("4. INode children").
 */
final class INodeActionsChildrenTest extends TestCase {
	private INode $result;

	protected function setUp() : void {
		$content = $this->fixture("XML".\DIRECTORY_SEPARATOR."books.xml");

		$parser = new Parser();
		$fullResult = $parser->fullParse($content);
		$this->result = $fullResult->getChild(0);
	}

	#[Test]
	#[TestDox("getChild")]
	public function getChild() : void {
		$this->assertNotNull($this->result->getChild(0));
		$this->assertNotNull($this->result->getChild(1));
	}

	#[Test]
	#[TestDox("currentChild")]
	public function currentChild() : void {
		$this->assertSame("bk101", $this->result->currentChild()->getAttribute("id"));
	}

	#[Test]
	#[TestDox("nextChild")]
	public function nextChild() : void {
		$this->result->resetChild();
		$this->assertSame("bk102", $this->result->nextChild()->getAttribute("id"));
		$this->assertSame("bk103", $this->result->nextChild()->getAttribute("id"));
		$this->assertNull($this->result->nextChild());
	}

	#[Test]
	#[TestDox("iterateChild")]
	public function iterateChild() : void {
		$this->result->resetChild();

		$this->assertSame("bk101", $this->result->iterateChild()->getAttribute("id"));
		$this->assertSame("bk102", $this->result->iterateChild()->getAttribute("id"));
		$this->assertSame("bk103", $this->result->iterateChild()->getAttribute("id"));
		$this->assertNull($this->result->iterateChild());
	}

	#[Test]
	#[TestDox("endChild")]
	public function endChild() : void {
		$this->result->resetChild();
		$this->assertSame("bk103", $this->result->endChild()->getAttribute("id"));
	}

	#[Test]
	#[TestDox("resetChild")]
	public function resetChild() : void {
		$this->assertSame("bk101", $this->result->resetChild()->getAttribute("id"));
		$this->assertSame("bk102", $this->result->nextChild()->getAttribute("id"));
	}

	#[Test]
	#[TestDox("appendChild")]
	public function appendChild() : void {
		$testNode = new StdNode;
		$testNode->setName("TEST_NODE_1");
		$testNode->setAttributes(["id" => "test_node_id_1"]);

		$testNode2 = new StdNode;
		$testNode2->setName("TEST_NODE_2");
		$testNode2->setAttributes(["id" => "test_node_id_2"]);

		$actionResult = $this->result->appendChild($testNode);
		$this->assertSame("TEST_NODE_1", $actionResult->nodeName);
		$this->assertSame("TEST_NODE_1", $this->result->endChild()->nodeName);
		$this->assertSame("test_node_id_1", $this->result->endChild()->getAttribute("id"));

		$actionResult = $this->result->appendChild($testNode2);
		$this->assertSame("TEST_NODE_2", $actionResult->nodeName);
		$this->assertSame("TEST_NODE_2", $this->result->endChild()->nodeName);
		$this->assertSame("test_node_id_2", $this->result->endChild()->getAttribute("id"));
	}

	#[Test]
	#[TestDox("removeChild")]
	public function removeChild() : void {
		[$testNode] = $this->appendTwoTestNodes();

		$actionResult = $this->result->removeChild($testNode);
		$this->assertSame("TEST_NODE_1", $actionResult->nodeName);
		$this->assertSame("TEST_NODE_2", $this->result->endChild()->nodeName);

		$this->result->removeChild($this->result->endChild());
		$this->assertSame("BOOK", $this->result->endChild()->nodeName);
	}

	#[Test]
	#[TestDox("removeChild - exception")]
	public function removeChildException() : void {
		[$testNode] = $this->appendTwoTestNodes();
		$this->result->removeChild($testNode);
		$this->result->removeChild($this->result->endChild());

		try {
			$this->result->removeChild($testNode);
			$this->fail("Expected exception was not thrown.");
		} catch (\Exception $e) {
			$this->assertSame(
				"Failed to execute 'removeChild' on 'Node': The node to be removed is not a child of this node.",
				$e->getMessage()
			);
		}
	}

	#[Test]
	#[TestDox("remove")]
	public function remove() : void {
		$this->result->endChild()->remove();
		$this->assertSame("bk102", $this->result->endChild()->getAttribute("id"));

		$testNode = new StdNode;
		$testNode->setName("test");
		$this->assertSame("TEST", $testNode->nodeName);
	}

	/**
     * Replays part 4.7 (appendChild) so a later part can exercise removeChild()
     * against the exact TEST_NODE_1/TEST_NODE_2 pair the legacy script built.
     *
     * @return 	array{0: StdNode, 1: StdNode}
     */
	private function appendTwoTestNodes() : array {
		$testNode = new StdNode;
		$testNode->setName("TEST_NODE_1");
		$testNode->setAttributes(["id" => "test_node_id_1"]);

		$testNode2 = new StdNode;
		$testNode2->setName("TEST_NODE_2");
		$testNode2->setAttributes(["id" => "test_node_id_2"]);

		$this->result->appendChild($testNode);
		$this->result->appendChild($testNode2);

		return [$testNode, $testNode2];
	}
}
