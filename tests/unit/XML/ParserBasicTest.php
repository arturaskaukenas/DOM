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
use ArturasKaukenas\DOM\XML\Parser;
use ArturasKaukenas\tests\TestCase;

/**
 * Ported from simple_tests/XML/1.php ("1. Basic").
 */
final class ParserBasicTest extends TestCase {
	#[Test]
	#[TestDox("Basic")]
	public function fullParseOfCatalogDoesNotThrow() : void {
		$this->expectNotToPerformAssertions();

		$parser = new Parser();
		$parser->fullParse($this->fixture("XML".\DIRECTORY_SEPARATOR."books.xml"));
	}
}
