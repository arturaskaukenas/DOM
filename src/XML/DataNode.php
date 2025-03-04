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

use ArturasKaukenas\DOM;

final class DataNode extends Node implements DOM\IDataNode {
	const NODE_NAME = "DataNode";

	public $dataAsChildren = false;

	public function getExpected() : array {
		return array();
	}

	public function expects(DOM\Expected $expected) : DOM\INode {
		throw new \Exception("Unable to use 'expects' for data element");
		return $this;
	}

	public function setName(string $name, bool $clean = true) : void {
		throw new \Exception("Unable to set name for data element");
	}

	public function setAttributes(array $attributes) : void {
		throw new \Exception("Unable to set attributes for data element");
	}

	public function setIgnoreChildren(bool $value) : void {
		throw new \Exception("Unable to set setIgnoreChildren for data element");
	}

	public function setIgnoreChildrenIfNotExists(bool $value) : void {
		throw new \Exception("Unable to set setIgnoreChildrenIfNotExists for data element");
	}

	public function setDataAsChildren(bool $value) : void {
		throw new \Exception("Unable to set setDataAsChildren for data element");
	}

	public function appendChild(DOM\INode $child) : DOM\INode {
		throw new \Exception("Unable to add elements to data element");
	}
	
	public function appendData(string $data) : void {
		if ($this->data === null) {
			$this->data = "";
		}

		$this->data = $this->data.$data;
	}
}