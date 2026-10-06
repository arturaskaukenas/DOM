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

namespace ArturasKaukenas\tests\parity\Support;

use ArturasKaukenas\tests\unit\Node\INodeActionsAttributesTest;
use ArturasKaukenas\tests\unit\Node\INodeActionsChildrenTest;
use ArturasKaukenas\tests\unit\Node\INodeActionsElementsAndTextContentTest;
use ArturasKaukenas\tests\unit\XML\ParserBasicTest as XMLParserBasicTest;
use ArturasKaukenas\tests\unit\XML\ParserRegisteredNodeTest;
use ArturasKaukenas\tests\unit\XML\NodeInnerXmlTest;
use ArturasKaukenas\tests\unit\HTML\ParserBasicTest as HTMLParserBasicTest;
use ArturasKaukenas\tests\unit\HTML\NodeInnerHtmlTest;

/**
 * Living documentation of which simple_tests/*.php test case (keyed as it is
 * printed by the legacy runner, e.g. "3.1") ended up covered by which
 * tests/unit/*.php test method - each ported test method is a one-to-one
 * translation of the legacy part's assertions, so the mapping is 1:1 too.
 *
 * tests/parity/SimpleTestToPhpUnitParityTest uses this to assert that the
 * migration from simpletest to PHPUnit dropped nothing and added nothing
 * untracked: every legacy leaf test must appear here under its exact name,
 * the test it points at must exist and pass, and every ported PHPUnit test
 * must be referenced from here at least once.
 */
final class Mapping {
	/**
     * @return 	array<string, array<string, array{name: string, test: string}>>
     */
	public static function get() : array {
		return [
			"XML" => [
				"1" => ["name" => "Basic", "test" => XMLParserBasicTest::class."::fullParseOfCatalogDoesNotThrow"],
				"2" => ["name" => "Registered node, processors, validators", "test" => ParserRegisteredNodeTest::class."::registeredBookNodeIsProcessedValidatedAndCast"],
				"3.1" => ["name" => "hasAttributes", "test" => INodeActionsAttributesTest::class."::hasAttributes"],
				"3.2" => ["name" => "hasAttribute", "test" => INodeActionsAttributesTest::class."::hasAttribute"],
				"3.3" => ["name" => "getAttributeNames", "test" => INodeActionsAttributesTest::class."::getAttributeNames"],
				"3.4" => ["name" => "getAttribute", "test" => INodeActionsAttributesTest::class."::getAttribute"],
				"3.5" => ["name" => "setAttribute", "test" => INodeActionsAttributesTest::class."::setAttribute"],
				"3.6" => ["name" => "testAttribute", "test" => INodeActionsAttributesTest::class."::removeAttribute"],
				"4.1" => ["name" => "getChild", "test" => INodeActionsChildrenTest::class."::getChild"],
				"4.2" => ["name" => "currentChild", "test" => INodeActionsChildrenTest::class."::currentChild"],
				"4.3" => ["name" => "nextChild", "test" => INodeActionsChildrenTest::class."::nextChild"],
				"4.4" => ["name" => "iterateChild", "test" => INodeActionsChildrenTest::class."::iterateChild"],
				"4.5" => ["name" => "endChild", "test" => INodeActionsChildrenTest::class."::endChild"],
				"4.6" => ["name" => "resetChild", "test" => INodeActionsChildrenTest::class."::resetChild"],
				"4.7" => ["name" => "appendChild", "test" => INodeActionsChildrenTest::class."::appendChild"],
				"4.8" => ["name" => "removeChild", "test" => INodeActionsChildrenTest::class."::removeChild"],
				"4.9" => ["name" => "removeChild - exception", "test" => INodeActionsChildrenTest::class."::removeChildException"],
				"4.10" => ["name" => "remove", "test" => INodeActionsChildrenTest::class."::remove"],
				"5.1" => ["name" => "getElementsByTagName", "test" => INodeActionsElementsAndTextContentTest::class."::getElementsByTagName"],
				"5.2" => ["name" => "getElementById", "test" => INodeActionsElementsAndTextContentTest::class."::getElementById"],
				"5.3" => ["name" => "getTextContents", "test" => INodeActionsElementsAndTextContentTest::class."::getTextContents"],
				"5.4" => ["name" => "setTextContents", "test" => INodeActionsElementsAndTextContentTest::class."::setTextContents"],
				"5.5" => ["name" => "setTextContents->finalizeNode", "test" => INodeActionsElementsAndTextContentTest::class."::setTextContentsFinalizeNode"],
				"6.1" => ["name" => "getInnerXML - basic", "test" => NodeInnerXmlTest::class."::getInnerXmlBasic"],
				"6.2" => ["name" => "getInnerXML - CDATA", "test" => NodeInnerXmlTest::class."::getInnerXmlCdata"],
				"6.3" => ["name" => "setInnerXML", "test" => NodeInnerXmlTest::class."::setInnerXml"],
				"6.4" => ["name" => "setInnerXML templated node - triggered", "test" => NodeInnerXmlTest::class."::setInnerXmlTemplatedNodeTriggered"],
				"6.5" => ["name" => "setInnerXML templated node - validate triggered", "test" => NodeInnerXmlTest::class."::setInnerXmlTemplatedNodeValidateTriggered"],
				"6.6" => ["name" => "setInnerXML templated node - type cast", "test" => NodeInnerXmlTest::class."::setInnerXmlTemplatedNodeTypeCast"],
				"6.7" => ["name" => "setInnerXML templated node - process", "test" => NodeInnerXmlTest::class."::setInnerXmlTemplatedNodeProcess"],
			],
			"HTML" => [
				"1.1" => ["name" => "Parsing", "test" => HTMLParserBasicTest::class."::parsing"],
				"1.2" => ["name" => "Basic actions - head", "test" => HTMLParserBasicTest::class."::basicActionsHead"],
				"1.3" => ["name" => "document->HEAD", "test" => HTMLParserBasicTest::class."::documentHead"],
				"1.4" => ["name" => "document->TITLE", "test" => HTMLParserBasicTest::class."::documentTitle"],
				// "Basic actions - body" is an empty divider in the legacy script (no assertions
				// of its own) - it shares the same ported test as "document->BODY" below.
				"1.5" => ["name" => "Basic actions - body", "test" => HTMLParserBasicTest::class."::documentBody"],
				"1.6" => ["name" => "document->BODY", "test" => HTMLParserBasicTest::class."::documentBody"],
				"2.1" => ["name" => "Parsing", "test" => NodeInnerHtmlTest::class."::parsing", "ignore" => true],
				"2.2" => ["name" => "getInnerHTML - basic", "test" => NodeInnerHtmlTest::class."::getInnerHtmlBasic"],
				"2.3" => ["name" => "getInnerHTML - data", "test" => NodeInnerHtmlTest::class."::getInnerHtmlData"],
				"2.4" => ["name" => "setInnerHTML", "test" => NodeInnerHtmlTest::class."::setInnerHtml"],
				"2.5" => ["name" => "setInnerHTML templated node - triggered", "test" => NodeInnerHtmlTest::class."::setInnerHtmlTemplatedNodeTriggered"],
				"2.6" => ["name" => "setInnerHTML templated node - onFinalizeNode", "test" => NodeInnerHtmlTest::class."::setInnerHtmlTemplatedNodeOnFinalizeNode"],
			],
		];
	}
}
