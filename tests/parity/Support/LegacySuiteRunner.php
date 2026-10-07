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

/**
 * Runs one of the legacy simple_tests/*.php entry points as a subprocess and
 * extracts the structured, leaf level test results from its console report.
 *
 * The legacy ArturasKaukenas\SimpleTest\Tests runner prints one line per
 * TestCase/part, e.g.:
 *   [SUCCESS] 3. INode attributes
 *   	[SUCCESS] 3.1. hasAttributes
 * A numbered test case that has parts (sub-tests) only ever asserts within
 * those parts, so its own top level line is treated as a rollup header and
 * excluded from the leaf results. A numbered test case without any part()
 * calls has no sub-tests, so its own line is the leaf.
 */
final class LegacySuiteRunner {
	private const TOP_LEVEL_PATTERN = '/^\[(SUCCESS|FAILED)\]\s+(\d+)\.\s+(.+)$/';
	private const SUB_PART_PATTERN = '/^\[(SUCCESS|FAILED)\]\s+(\d+)\.(\d+)\.\s+(.+)$/';

	/**
     * @param 	string $relativeScriptPath		Path to the legacy entry point, relative to the project root (e.g. "simple_tests/xml.php").
     * @return 	array{success: bool, leaves: array<int, array{id: string, name: string, success: bool}>}
     */
	public function run(string $relativeScriptPath) : array {
		$root = \dirname(__DIR__, 3);
		$scriptPath = $root.\DIRECTORY_SEPARATOR.$relativeScriptPath;

		$command = \implode(
			" ",
			[
				\escapeshellarg(\PHP_BINARY),
				\escapeshellarg($scriptPath),
			]
		);

		$output = [];
		$exitCode = 0;
		\exec($command." 2>&1", $output, $exitCode);

		return [
			"success" => ($exitCode === 0),
			"leaves" => $this->extractLeaves($output),
		];
	}

	/**
     * @param 	array<int, string> $lines
     * @return 	array<int, array{id: string, name: string, success: bool}>
     */
	private function extractLeaves(array $lines) : array {
		$tops = [];
		$subs = [];
		$parentsWithSubs = [];

		foreach ($lines as $line) {
			$line = \trim($this->stripAnsiCodes($line));

			if (\preg_match(self::SUB_PART_PATTERN, $line, $m) === 1) {
				$subs[] = [
					"id" => $m[2].".".$m[3],
					"name" => \trim($m[4]),
					"success" => ($m[1] === "SUCCESS"),
				];
				$parentsWithSubs[$m[2]] = true;
				continue;
			}

			if (\preg_match(self::TOP_LEVEL_PATTERN, $line, $m) === 1) {
				$tops[$m[2]] = [
					"id" => $m[2],
					"name" => \trim($m[3]),
					"success" => ($m[1] === "SUCCESS"),
				];
			}
		}

		$leaves = [];
		foreach ($tops as $num => $top) {
			if (isset($parentsWithSubs[$num])) {
				continue;
			}

			$leaves[] = $top;
		}

		foreach ($subs as $sub) {
			$leaves[] = $sub;
		}

		return $leaves;
	}

	private function stripAnsiCodes(string $text) : string {
		return (string) \preg_replace('/\x1b\[[0-9;]*m/', "", $text);
	}
}
