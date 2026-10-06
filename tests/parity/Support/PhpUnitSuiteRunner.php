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
 * Runs a named PHPUnit testsuite as a subprocess (so it gets a report
 * independent from the parity test's own run) and parses its JUnit log
 * into a flat "Class::method" => passed map.
 */
final class PhpUnitSuiteRunner {
	/**
     * @param 	string $testsuite	The testsuite name declared in phpunit.xml (e.g. "unit").
     * @return 	array{success: bool, tests: array<string, bool>}
     */
	public function run(string $testsuite) : array {
		$root = \dirname(__DIR__, 3);
		$phpunitBin = $root.\DIRECTORY_SEPARATOR."vendor".\DIRECTORY_SEPARATOR."phpunit".\DIRECTORY_SEPARATOR."phpunit".\DIRECTORY_SEPARATOR."phpunit";
		$configPath = $root.\DIRECTORY_SEPARATOR."phpunit.xml";
		$logPath = (string) \tempnam(\sys_get_temp_dir(), "phpunit_junit_");

		$command = \implode(
			" ",
			[
				\escapeshellarg(\PHP_BINARY),
				\escapeshellarg($phpunitBin),
				"--configuration", \escapeshellarg($configPath),
				"--testsuite", \escapeshellarg($testsuite),
				"--log-junit", \escapeshellarg($logPath),
			]
		);

		$output = [];
		$exitCode = 0;
		\exec($command." 2>&1", $output, $exitCode);

		$tests = $this->parseJUnitLog($logPath);
		\unlink($logPath);

		return [
			"success" => ($exitCode === 0),
			"tests" => $tests,
		];
	}

	/**
     * @return 	array<string, bool>
     */
	private function parseJUnitLog(string $path) : array {
		if (!\is_file($path)) {
			throw new \RuntimeException("PHPUnit did not produce a JUnit log at '".$path."'.");
		}

		$content = (string) \file_get_contents($path);
		if (\trim($content) === "") {
			throw new \RuntimeException("PHPUnit produced an empty JUnit log at '".$path."'.");
		}

		$xml = new \SimpleXMLElement($content);

		$tests = [];
		foreach ($xml->xpath("//testcase") as $testCase) {
			$class = (string) $testCase["class"];
			$name = (string) $testCase["name"];
			$hasFailed = (\count($testCase->xpath("failure")) > 0) || (\count($testCase->xpath("error")) > 0);

			$tests[$class."::".$name] = !$hasFailed;
		}

		return $tests;
	}
}
