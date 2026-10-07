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

namespace ArturasKaukenas\tests\parity;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use ArturasKaukenas\tests\parity\Support\LegacySuiteRunner;
use ArturasKaukenas\tests\parity\Support\PhpUnitSuiteRunner;
use ArturasKaukenas\tests\parity\Support\Mapping;
use ArturasKaukenas\tests\TestCase;

/**
 * Regression test for the simpletest -> PHPUnit migration itself.
 *
 * It runs the legacy simple_tests/*.php suite and the ported tests/unit
 * suite as separate subprocesses, then uses Support\Mapping to verify
 * that:
 *   - both suites are fully green ("output" parity);
 *   - every legacy leaf test case is accounted for in the mapping, under
 *     its exact legacy name, and nothing in the mapping is stale ("names"
 *     and "qty" parity);
 *   - every PHPUnit test the mapping points at exists, passed, and is
 *     itself referenced by at least one legacy case (no untracked tests).
 *
 * Deliberately kept out of the default "unit" testsuite (it is comparatively
 * slow, it shells out to run PHP multiple times) - run it explicitly with
 * `composer test:parity` / `phpunit --testsuite parity`.
 */
final class SimpleTestToPhpUnitParityTest extends TestCase {
	/** @var array{success: bool, leaves: array<int, array{id: string, name: string, success: bool}>} */
	private static array $xmlLegacyResult;

	/** @var array{success: bool, leaves: array<int, array{id: string, name: string, success: bool}>} */
	private static array $htmlLegacyResult;

	/** @var array{success: bool, tests: array<string, bool>} */
	private static array $phpUnitResult;

	/** @var array<string, array<string, array{name: string, test: string, ignore?: bool}>> */
	private static array $mapping;

	public static function setUpBeforeClass() : void {
		$legacyRunner = new LegacySuiteRunner();
		self::$xmlLegacyResult = $legacyRunner->run("simple_tests".\DIRECTORY_SEPARATOR."xml.php");
		self::$htmlLegacyResult = $legacyRunner->run("simple_tests".\DIRECTORY_SEPARATOR."html.php");

		self::$phpUnitResult = (new PhpUnitSuiteRunner())->run("unit");

		self::$mapping = Mapping::get();
	}

	#[Test]
	#[TestDox("simple_tests/xml.php is fully green")]
	public function legacyXmlSuiteIsFullyGreen() : void {
		$this->assertTrue(self::$xmlLegacyResult["success"], "simple_tests/xml.php reported at least one failure.");
	}

	#[Test]
	#[TestDox("simple_tests/html.php is fully green")]
	public function legacyHtmlSuiteIsFullyGreen() : void {
		$this->assertTrue(self::$htmlLegacyResult["success"], "simple_tests/html.php reported at least one failure.");
	}

	#[Test]
	#[TestDox("the ported 'unit' PHPUnit testsuite is fully green")]
	public function portedUnitSuiteIsFullyGreen() : void {
		$this->assertTrue(self::$phpUnitResult["success"], "The ported 'unit' PHPUnit testsuite reported at least one failure.");
	}

	#[Test]
	#[TestDox("every legacy leaf test is mapped under its exact name and passed")]
	public function everyLegacyLeafIsMappedUnderItsExactNameAndPassed() : void {
		foreach ($this->legacyLeavesBySuite() as $suite => $leaves) {
			foreach ($leaves as $leaf) {
				$this->assertArrayHasKey(
					$leaf["id"],
					self::$mapping[$suite],
					"Legacy test '".$suite." ".$leaf["id"]."' ('".$leaf["name"]."') has no entry in Support\\Mapping."
				);

				$this->assertSame(
					$leaf["name"],
					self::$mapping[$suite][$leaf["id"]]["name"],
					"Support\\Mapping's name for '".$suite." ".$leaf["id"]."' no longer matches the legacy test's name."
				);

				$this->assertTrue(
					$leaf["success"],
					"Legacy test '".$suite." ".$leaf["id"]."' ('".$leaf["name"]."') did not pass."
				);
			}
		}
	}

	#[Test]
	#[TestDox("Support\\Mapping has no stale entries")]
	public function mappingHasNoStaleEntries() : void {
		foreach ($this->legacyLeavesBySuite() as $suite => $leaves) {
			$legacyIds = \array_column($leaves, "id");

			foreach (self::$mapping[$suite] as $id => $entry) {
				// PHP casts purely numeric string keys (e.g. "1", "2") to int, so compare as strings here.
				$id = (string) $id;

				$this->assertContains(
					$id,
					$legacyIds,
					"Support\\Mapping references '".$suite." ".$id."' ('".$entry["name"]."'), but simple_tests no longer reports that test case."
				);
			}
		}
	}

	#[Test]
	#[TestDox("the legacy suite and Support\\Mapping report the same test quantity")]
	public function legacyAndMappingTestQuantitiesMatch() : void {
		$legacyCount = \count(self::$xmlLegacyResult["leaves"]) + \count(self::$htmlLegacyResult["leaves"]);
		$mappingCount = \count(self::$mapping["XML"]) + \count(self::$mapping["HTML"]);

		$this->assertSame(
			$legacyCount,
			$mappingCount,
			"simple_tests reports ".$legacyCount." leaf test case(s) but Support\\Mapping has ".$mappingCount." entry/entries. ".
				"Keep them in sync whenever a legacy test is added, removed or split."
		);
	}

	#[Test]
	#[TestDox("every PHPUnit test Support\\Mapping references exists and passed")]
	public function everyMappedPhpUnitTestExistsAndPassed() : void {
		foreach ($this->allMappedPhpUnitTests() as $testId) {
			$this->assertArrayHasKey(
				$testId,
				self::$phpUnitResult["tests"],
				"Support\\Mapping references PHPUnit test '".$testId."', but it was not found in the 'unit' testsuite run."
			);

			$this->assertTrue(
				self::$phpUnitResult["tests"][$testId],
				"PHPUnit test '".$testId."' (ported from a legacy simple_tests case) did not pass."
			);
		}
	}

	#[Test]
	#[TestDox("every ported PHPUnit test is referenced by Support\\Mapping")]
	public function everyPortedPhpUnitTestIsReferencedByTheMapping() : void {
		$mapped = \array_flip($this->allMappedPhpUnitTests());

		foreach (\array_keys(self::$phpUnitResult["tests"]) as $testId) {
			$this->assertArrayHasKey(
				$testId,
				$mapped,
				"PHPUnit test '".$testId."' is not referenced by Support\\Mapping. ".
					"Either it has no corresponding legacy case (fine - add it to the mapping with a new entry) ".
					"or the mapping fell out of date."
			);
		}
	}

	/**
     * @return 	array<string, array<int, array{id: string, name: string, success: bool}>>
     */
	private function legacyLeavesBySuite() : array {
		return [
			"XML" => self::$xmlLegacyResult["leaves"],
			"HTML" => self::$htmlLegacyResult["leaves"],
		];
	}

	/**
     * Entries flagged "ignore" (e.g. a legacy "Parsing" case that has no
     * dedicated ported test because parsing is already exercised by every
     * other test's setUp() in that class) are intentionally excluded: they
     * are not required to point at an existing, passing PHPUnit test.
     *
     * @return 	array<int, string>
     */
	private function allMappedPhpUnitTests() : array {
		$tests = [];
		foreach (self::$mapping as $suite) {
			foreach ($suite as $entry) {
				if ($entry["ignore"] ?? false) {
					continue;
				}

				$tests[$entry["test"]] = true;
			}
		}

		return \array_keys($tests);
	}
}
