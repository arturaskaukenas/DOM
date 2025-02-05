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

namespace ArturasKaukenas\DOM\HTML;

use ArturasKaukenas\DOM;

/**
 * Parses HTML data into a DOM structure.
 */
class Parser extends DOM\Parser {

	/**
     * Converts HTML data into well-formed XHTML using Tidy.
     *
     * @param string $data	The HTML data to be converted.
     * @return string		The converted XHTML data.
     */
	private function convertHTML(string $data) : string {
		//We need use tidy or DOM extensions. libxml2 SAX + HTML not possible as PHP not using htmlCreatePushParserCtxt.
		$config = array(
			'clean' => true,
			'doctype' => "omit",
			'output-xml' => true,
			'wrap' => 0
		);

		$tidy = new \tidy();
		$tidy->parseString($data, $config);
		$tidy->cleanRepair();

		return (string) $tidy->value;
	}

	/**
     * Parses the provided HTML data and returns the root HTML node.
     *
     * @param string $data	The HTML data to be parsed.
     * @return INode		The root HTML node after parsing.
     */
	public function fullParse(string $data) : INode {
		$this->init();

		$this->parse($this->convertHTML($data));
		$this->finalize();

		return $this->getResult();
	}

	/**
     * Retrieves the result of the HTML parsing process.
     *
     * @return INode.
     */
	public function getResult() : INode {
		$result = parent::_getResult();
		if ($result->HTML !== null) {
			return $result->HTML;
		}

		return $result;
	}

	protected function newRootNode() : DOM\INode {
		return new Document;
	}

	protected function newStdNode() : DOM\INode {
		return new HTMLElement;
	}

	protected function newDataNode() : DOM\INode {
		return new DataNode;
	}
}