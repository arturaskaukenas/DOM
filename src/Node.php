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

/**
 * Class Node
 * @package ArturasKaukenas\DOM
 *
 * @property-read 	?string					$nodeName			The name of the node.
 * @property-read 	?INode					$parentNode			The parent node.
 * @property-read 	int						$childElementsQty	The number of child elements.
 * @property-read 	bool					$dataAsChildren		Flag indicating whether to treat data as children.
 * @property-read 	bool					$ignoreChildren		Flag indicating whether to ignore children.
 * @property 		bool					$cleanOnFinalize	Flag indicating whether to clean the node on finalize.
 */

abstract class Node implements INode {
	use NodeActions;

	public ?string $nodeName = null;
	public ?INode $parentNode = null;
	public int $childElementsQty = 0;
	public bool $dataAsChildren = true;
	public bool $ignoreChildren = false;
	public bool $cleanOnFinalize = false;

	protected array $attributes = [];
	protected ?string $data = null;
	protected array $children = [];
	protected $dataParser = null;
	protected array $expects = [];
	protected array $expectsElements = [];
	protected array $expectsAttributes = [];
	protected array $expectedValues = [];

	protected bool $errorsExists = false;
	protected array $errors = [];

	protected ?IParser $parser = null;

	public function setParser(IParser $parser) : void {
		$this->parser = $parser;
	}

	public function getExpected() : array {
		return \array_merge($this->getExpectedElements(), $this->getExpectedAttributes());
	}

	public function getExpectedElements() : array {
		if (!isset($this->expectsElements)) {
			return [];
		}

		return $this->expectsElements;
	}

	public function getExpectedAttributes() : array {
		if (!isset($this->expectsAttributes)) {
			return [];
		}

		return $this->expectsAttributes;
	}

	public function expects(Expected\IExpected $expected) : INode {
		$value = null;
		if ($expected->isArray()) {
			$value = [];
		}

		$name = $expected->getName();

		if (isset($this->expects[$name])) {
			throw new \Exception("key '".$name."' already expected");
		}

		$this->expects[$name] = $expected;

		if ($expected instanceof Expected\Element) {
			$this->expectsElements[$name] = $this->expects[$name];
		} else if ($expected instanceof Expected\Attribute) {
				$this->expectsAttributes[$name] = $this->expects[$name];
		} else {
				unset($this->expects[$name]);
				throw new \Exception("expected element should be Element or Attribute");
		}

		$this->expectedValues[$name] = $value;

		return $this;
	}

	public function setExpectedObject(string $name, $object, bool $isArray = false) : INode {
		$type = NodeDataTypes::T_PROTO;
		if ($isArray) {
			$type = NodeDataTypes::T_ARRAY_OF_PROTO;
		}

		$expected = new Expected\Element($name, $type);

		if (\is_string($object)) {
			$expected->prototypeOfClassName($object);
		} else if ($object instanceof \ReflectionClass) {
			$expected->prototypeOfObject($object);
		} else {
				throw new \Exception("object should be typeof 'string' or '\ReflectionClass'");
		}

		return $this->expects($expected);
	}

	public function setExpectedValue(string $name, int $type = NodeDataTypes::T_MIXED, ?callable $processFunction = null) : INode {
		$expected = new Expected\Element($name, $type);
		if ($processFunction !== null) {
			$expected->process($processFunction);
		}

		return $this->expects($expected);
	}

	public function appendExpectedValue(string $name, string $value) : void {
		if (!isset($this->expects[$name])) {
			throw new \Exception("key '".$name."' not expected");
		}

		$expects = $this->expects[$name];

		if ($expects->isPreValidationEnabled()) {
			$f = $expects->getPreValidateFunction();
			$res = $f($value);
			if (\is_string($res)) {
				$this->appendError("'".$name."' pre-validate failed: ".$res);
				return;
			} else {
					if (!$res) {
						$this->appendError("'".$name."' pre-validate failed");
						return;
					}
			}
		}

		if ($expects->isPreProcessEnabled()) {
			$f = $expects->getPreProcessFunction();
			$value = $f($value);
		}

		$value = $this->cast($value, $expects->getType());

		if ($expects->isValidationEnabled()) {
			$f = $expects->getValidateFunction();
			$res = $f($value);
			if (\is_string($res)) {
				$this->appendError("'".$name."' validation failed: ".$res);
				return;
			} else {
					if (!$res) {
						$this->appendError("'".$name."' validation failed");
						return;
					}
			}
		}

		if ($expects->isArray()) {
			if ($this->expectedValues[$name] === null) {
				$this->expectedValues[$name] = [];
			}

			//TODO: add more types (T_ARRAY_OF_*)
			$this->expectedValues[$name][] = $value;
		} else {
				$this->expectedValues[$name] = $value;
		}
	}

	public function __get(string $name): mixed {
		return $this->expectedValues[$name];
	}

	public function __set(string $name, $value) : void {
		$this->expectedValues[$name] = $value;
	}

	public function __isset(string $name) : bool {
		return $this->expectedElementExists($name);
	}

	public function expectedElementExists(string $name) : bool {
		return \array_key_exists($name, $this->expectedValues);
	}

	public function setExpectedElement(string $name, $value) : void {
		if (\is_array($this->expectedValues[$name])) {
			$this->expectedValues[$name][] = $value;
		}

		$this->expectedValues[$name] = $value;
	}

	private function cast($value, $type): mixed {
		//TODO: add more types (T_ARRAY_OF_*)
		switch($type) {
			case NodeDataTypes::T_STRING;
				$value = (string) $value;
			break;

			case NodeDataTypes::T_INT;
				$value = (int) $value;
			break;

			case NodeDataTypes::T_FLOAT;
				$value = (float) $value;
			break;

			case NodeDataTypes::T_BOOL;
				$value = $this->castAsBool($value);
			break;
		}

		return $value;
	}

	private function castAsBool(string $value) : ?bool {
		$value = \strtolower($value);
		switch($value) {
			case "1";
			case "true";
			case "yes";
			case "y";
				return true;
			break;

			case "0";
			case "false";
			case "no";
			case "n";
				return false;
			break;
		}
		
		return null;
	}

	public function useDataParser(callable $callback) : void {
		$this->dataParser = $callback;
	}

	public function setName(string $name, bool $clean = true) : void {
		if ($clean) {
			$name = \trim(\strtoupper($name));
		}

		$this->nodeName = $name;
	}

	public function setAttributes(array $attributes) : void {
		if (\count($attributes) === 0) {
			return;
		}

		$expects = $this->getExpectedAttributes();

		foreach ($attributes as $key => $value) {
			$key = \strtolower($key);
			$this->attributes[$key] = $value;

			if (\array_key_exists($key, $expects)) {
				$this->appendExpectedValue($key, $value);
			}
		}
	}

	public function setData(?string $data) : void {
		$this->data = $data;
	}

	public function getData() : ?string {
		return $this->data;
	}
	
	public function setIgnoreChildren(bool $value) : void {
		$this->ignoreChildren = $value;
	}

	public function setIgnoreChildrenIfNotExists(bool $value) : void {
		if (isset($this->ignoreChildren)) {
			return;
		}

		$this->ignoreChildren = $value;
	}

	public function setDataAsChildren(bool $value) : void {
		if ($this->ignoreChildren) {
			throw new \Exception("Unable to set data as children ad children ignored");
		}

		$this->dataAsChildren = $value;
	}

	public function cleanChildren() : void {
		$this->children = [];
		$this->childElementsQty = 0;
	}

	public function appendChild(INode $child) : INode {
		$this->children[] = $child;
		$this->childElementsQty++;
		$child->setParserReference($child, $this->parser);
		return $child;
	}

	public function setParserReference(INode $node, ?IParser $parser) : void {
		$node->parser = $parser;
		$node->updateChildrenParser();
	}

	public function updateChildrenParser() : void {
		foreach ($this->children as $child) {
			$child->parser = $this->parser;
		}
	}

	public function setParentNode(INode $parentNode) : void {
		$this->parentNode = $parentNode;
	}

	public function removeChild(INode $childNode) : INode {
		$count = \count($this->children);
		for ($i = 0; $i < $count; $i++) {
			if ($this->children[$i] === $childNode) {
				\array_splice($this->children, $i, 1);
				$this->childElementsQty--;
				$childNode->parentNode = null;
				return $childNode;
			}
		}

		throw new \Exception("Failed to execute 'removeChild' on 'Node': The node to be removed is not a child of this node.");
	}

	public function remove() : void {
		if ($this->parentNode === null) {
			return;
		}

		$count = \count($this->parentNode->children) - 1;
		for ($i = $count; $i >= 0; $i--) {
			if ($this->parentNode->children[$i] === $this) {
				\array_splice($this->parentNode->children, $i, 1);
				$this->parentNode->childElementsQty--;
				$this->parentNode = null;
				return;
			}
		}
	}

	public function finalize() : bool {
		$this->performDataParser();

		if (!$this->validate()) {
			return false;
		}

		if (!$this->postProcess()) {
			return false;
		}
		
		if ($this->cleanOnFinalize) {
			return false;
		}

		return true;
	}

	private function performDataParser() : void {
		if ($this->dataParser === null) {
			return;
		}
		$callable = $this->dataParser;
		$parsedData = $callable((string) $this->data);
		if (!$parsedData instanceof INode) {
			throw new \Exception("Failed to parse data using provided parser. Parser should return 'INode'");
		}

		$this->appendChild($parsedData);
	}

	protected function validate() : bool {
		return true;
	}

	protected function postProcess() : bool {
		return true;
	}

	protected function appendError(string $text) : void {
		$this->errors[] = $text;
		$this->errorsExists = true;
	}

	public function errorsExists() : bool {
		return $this->errorsExists;
	}

	public function getErrors() : array {
		return $this->errors;
	}
}