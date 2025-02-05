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

namespace ArturasKaukenas\DOM\XML;

use \ArturasKaukenas\DOM;

/**
 * XML Parser for parsing XML data and generating DOM nodes.
 *
 * @package ArturasKaukenas\DOM\XML
 */
class Parser extends DOM\Parser {
	/**
     * Parses the entire XML data and returns the root node.
     *
     * @param string $data	The XML data to be parsed.
     * @return INode		The root node of the parsed XML data.
     */
	public function fullParse(string $data) : INode {
		$this->init();

		$this->parse($data);
		$this->finalize();

		return $this->getResult();
	}

	/**
     * Prepares the parser for parsing XML data.
     *
     * @return Parser Returns the parser instance for method chaining.
     */
	public function prepare() : Parser {
		$this->init();

		return $this;
	}

	/**
     * Retrieves the callback function for parsing XML data.
     *
     * @return callable The callback function for parsing XML data.
     */
	public function getCallBack() : callable {
		return function(string $data) : bool {
			return $this->parse($data);
		};
	}

	/**
     * Retrieves the result of the XML parsing process.
     *
     * @return INode
     */
	public function getResult() : INode {
		return parent::_getResult();
	}

	protected function newRootNode() : DOM\INode {
		return new RootNode;
	}

	protected function newStdNode() : DOM\INode {
		return new StdNode;
	}

	protected function newDataNode() : DOM\INode {
		return new DataNode;
	}
}