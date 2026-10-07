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

namespace ArturasKaukenas\DOM;

trait NodeActions {
	private $childIterator = 0;

	public function hasAttributes() : bool {
		return (\count($this->attributes) > 0);
	}

	public function hasAttribute(string $name) : bool {
		$name = \strtolower($name);
		return isset($this->attributes[$name]);
	}

	public function getAttributeNames() : array {
		return \array_keys($this->attributes);
	}

	public function getAttribute(string $name) : ?string {
		$name = \strtolower($name);
		if (isset($this->attributes[$name])) {
			return (string) $this->attributes[$name];
		}

		return null;
	}

	public function setAttribute(string $name, $value) : void {
		$valueT = (string) $value;

		if ($value === null) {
			$valueT = "null";
		}

		if ($value === true) {
			$valueT = "true";
		}

		if ($value === false) {
			$valueT = "true";
		}

		/*
			//https://www.w3.org/TR/REC-xml/#NT-NameStartChar
			[4]   	NameStartChar	::=   	":" | [A-Z] | "_" | [a-z] | [#xC0-#xD6] | [#xD8-#xF6] | [#xF8-#x2FF] | [#x370-#x37D] | [#x37F-#x1FFF] | [#x200C-#x200D] | [#x2070-#x218F] | [#x2C00-#x2FEF] | [#x3001-#xD7FF] | [#xF900-#xFDCF] | [#xFDF0-#xFFFD] | [#x10000-#xEFFFF]
			[4a]   	NameChar		::=   	NameStartChar | "-" | "." | [0-9] | #xB7 | [#x0300-#x036F] | [#x203F-#x2040]
			[5]   	Name	   		::=   	NameStartChar (NameChar)*
		*/

		$NameStartChar = ":|[A-Z]|_|[a-z]|[\x{C0}-\x{D6}]|[\x{D8}-\x{F6}]|[\x{F8}-\x{2FF}]|[\x{370}-\x{37D}]|[\x{37F}-\x{1FFF}]|[\x{200C}-\x{200D}]|[\x{2070}-\x{218F}]|[\x{2C00}-\x{2FEF}]|[\x{3001}-\x{D7FF}]|[\x{F900}-\x{FDCF}]|[\x{FDF0}-\x{FFFD}]";
		$NameChar = $NameStartChar."|-|\.|[0-9]|\x{B7}|[\x{0300}-\x{036F}]|[\x{203F}-\x{2040}]";
		$Name = "(".$NameStartChar.")(".$NameChar.")*";

		if (\preg_match("/^".$Name."$/u", $name) !== 1) {
			throw new \Exception("Failed to execute 'setAttribute' on 'Node': '".$name."' is not a valid attribute name.");
		}

		$this->attributes[$name] = $valueT;
	}

	public function removeAttribute(string $name) : void {
		$name = \strtolower($name);
		if (!isset($this->attributes[$name])) {
			return;
		}

		unset($this->attributes[$name]);
	}

	public function getChild(int $num) : ?INode {
		if (!isset($this->children[$num])) {
			return null;
		}

		return $this->children[$num];
	}

	public function currentChild() : ?INode {
		return $this->getChild($this->childIterator);
	}

	public function nextChild() : ?INode {
		if ($this->childIterator > $this->childElementsQty) {
			return null;
		}

		$this->childIterator++;
		return $this->currentChild();
	}

	public function iterateChild() : ?INode {
		$child = $this->currentChild();
		if ($this->childIterator <= $this->childElementsQty) {
			$this->childIterator++;
		}

		return $child;
	}

	public function endChild() : ?INode {
		return $this->getChild($this->childElementsQty - 1);
	}

	public function resetChild() : ?INode {
		$this->childIterator = 0;
		return $this->currentChild();
	}

	public function getElementsByTagName(string $tagName, bool $clean = true) : array {
		if ($clean) {
			$tagName = \trim(\strtoupper($tagName));
		}

		$result = [];
		for ($i = 0; $i < $this->childElementsQty; $i++) {
			if ($this->children[$i]->nodeName === $tagName) {
				$result[] = $this->children[$i];
			}

			$result = \array_merge($result, $this->children[$i]->getElementsByTagName($tagName, false));
		}
		
		return $result;
	}

	public function getElementById(string $id) : ?INode {
		$result = [];
		for ($i = 0; $i < $this->childElementsQty; $i++) {
			if ($this->children[$i]->getAttribute("id") === $id) {
				return $this->children[$i];
			}
			
			$tmp_el = $this->children[$i]->getElementById($id);
			if ($tmp_el !== null) {
				return $tmp_el;
			}
		}

		return null;
	}

	public function getTextContents() : string {
		$result = (string) $this->data;
		for ($i = 0; $i < $this->childElementsQty; $i++) {
			$result = $result.$this->children[$i]->getTextContents();
		}

		return $result;
	}

	public function setTextContents(?string $data) : void {
		$this->cleanChildren();
		$this->setData(null);

		$this->parser->clean();
		$this->parser->init($this, true);
		$this->parser->setData($data);
		$this->parser->finalizeNode();
		$this->parser->clean();		
	}

	public function getInner() : string {
		throw new \Exception("Not implemented");
		return "";
	}

	public function setInner(?string $data) : void {
		throw new \Exception("Not implemented");
	}
}