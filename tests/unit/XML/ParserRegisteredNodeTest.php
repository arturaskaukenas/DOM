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
use ArturasKaukenas\tests\Fixtures\XML\Book;
use ArturasKaukenas\tests\TestCase;

/**
 * Ported from simple_tests/XML/2.php ("2. Registered node, processors, validators").
 */
final class ParserRegisteredNodeTest extends TestCase {
	#[Test]
	#[TestDox("2 Registered node, processors, validators")]
	public function registeredBookNodeIsProcessedValidatedAndCast() : void {
		$parser = new Parser();
		$parser
			->registerNode(new \ReflectionClass(Book::class))
			->onFinalizeNode(
				"BOOK",
				function (DOM\INode $node) : void {
					$this->assertIsInt($node->ID);

					if ($node->ID === 1) {
						$this->assertSame(\strtoupper("Gambardella, Matthew"), $node->AUTHOR);
						$this->assertSame("XML Developer's Guide", $node->TITLE);
						$this->assertNull($node->PRICE);
						$this->assertSame("'PRICE' validation failed: Should be cheaper than 10", $node->getErrors()[0]);
					} else if ($node->ID === 2) {
							$this->assertSame(\strtoupper("Ralls, Kim"), $node->AUTHOR);
							$this->assertInstanceOf(\DateTime::class, $node->PUBLISH_DATE);
					}
				}
			);

		$parser->fullParse($this->fixture("XML".\DIRECTORY_SEPARATOR."books.xml"));
	}
}
