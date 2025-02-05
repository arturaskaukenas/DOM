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
 * @property-read 	?string					$name				The name of the node.
 * @property-read 	array<string, mixed>	$attributes			The attributes of the node.
 * @property	 	?string					$data				The data associated with the node.
 * @property-read 	int						$childElementCount	The number of child elements.
 * @property-read 	?INode					$parentNode			The parent node.
 * @property 		bool					$cleanOnFinalize	Flag indicating whether to clean the node on finalize.
 * @property-read 	bool					$dataAsChildren		Flag indicating whether to treat data as children.
 * @property-read 	bool					$ignoreChildren		Flag indicating whether to ignore children.
 * @property 		?callable				$dataParser			The callback function for parsing data.
 * @property-read 	bool					$errorsExists		Flag indicating whether errors exist.
 * @property-read 	array<string>			$errors				The array of errors.
 * @property-read 	?IParser				$parser				The parser instance associated with the node.
 */

abstract class Node implements INode {
	use NodeActions;

	public $nodeName = null;
	public $attributes = array();
	public $data = null;
	public $childElementCount = 0;
	public $children = array();
	public $parentNode = null;
	public $dataAsChildren = true;
	public $ignoreChildren = false;
	public $cleanOnFinalize = false;

	protected $dataParser = null;

	protected $expected = array();

	public $errorsExists = false;
	public $errors = array();

	protected $parser = null;

	public function setParser(IParser $parser) : void {
		$this->parser = $parser;
	}

	public function getExpected() : array {
		if (!isset($this->expected)) {
			return array();
		}

		return $this->expected;
	}

	/**
     * Specifies the expected element of the node.
     *
     * @param Expected $expected	Definition of expected element.
     * @return INode The node instance.
     */
	public function expects(Expected $expected) : INode {
		$value = null;
		if ($expected->type === NodeDataTypes::T_ARRAY) {
			$value = array();
		}
		
		$name = $expected->name;

		$this->expected[$name] = $expected;
		$this->{$name} = $value;
		
		return $this;
	}
	
	/**
     * Sets an expected node template for child element.
     *
     * @param 	string $name    					The name of the expected element.
     * @param 	string|\ReflectionClass $object		The class name or \ReflectionClass instance representing the expected object.
     * @param 	bool $isArray						Indicates whether the expected element is an array or single element.
     * @return 	INode 								The node instance.
     */
	public function setExpectedObject(string $name, $object, bool $isArray = false) : INode {
		$type = NodeDataTypes::T_PROTO;
		if ($isArray) {
			$type = NodeDataTypes::T_ARRAY;
		}

		$expected = new Expected($name, $type);

		if (\is_string($object)) {
			$expected->prototypeOfClassName($object);
		} else if ($object instanceof \ReflectionClass) {
			$expected->prototypeOfObject($object);
		} else {
				throw new \Exception("object should be typeof 'string' or '\ReflectionClass'");
		}

		return $this->expects($expected);
	}

	/**
     * Sets an expected value based on a child element.
     *
     * @param 	string		$name            	The name of the expected element.
     * @param 	int			$type            	The data type of the expected value (use constants from NodeDataTypes class).
     * @param 	?callable	[$processFunction] 	The function to process the value before setting it.
     * @return 	INode							The node instance.
     */
	public function setExpectedValue(string $name, int $type = NodeDataTypes::T_MIXED, ?callable $processFunction = null) : INode {
		$expected = new Expected($name, $type);
		if ($processFunction !== null) {
			$expected->process($processFunction);
		}

		return $this->expects($expected);
	}

	/**
     * Appends an expected value to the node.
     *
     * @param string $name		The name of the value.
     * @param string $value		The value to append.
     * @return void
     */
	public function appendExpectedValue(string $name, string $value) : void {
		if (!isset($this->expected[$name])) {
			throw new \Exception("Key '".$name."' not expected");
		}

		if ($this->expected[$name]->usePreValidate) {
			$f = $this->expected[$name]->preValidateFunction;
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

		if ($this->expected[$name]->useProcess) {
			$f = $this->expected[$name]->processFunction;
			$value = $f($value);
		}

		$value = $this->cast($value, $this->expected[$name]->type);

		if ($this->expected[$name]->useValidate) {
			$f = $this->expected[$name]->validateFunction;
			$res = $f($value);
			if (\is_string($res)) {
				$this->appendError("'".$name."' validate failed: ".$res);
				return;
			} else {
					if (!$res) {
						$this->appendError("'".$name."' validate failed");
						return;
					}
			}
		}

		if ($this->expected[$name]->type === NodeDataTypes::T_ARRAY) {
			if ($this->{$name} === null) {
				$this->{$name} = array();
			}

			$this->{$name}[] = $value;
		} else {
				$this->{$name} = $value;
		}
	}

	private function cast($value, $type) {
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

		foreach ($attributes as $key => $value) {
			$key = \strtolower($key);
			$this->attributes[$key] = $value;
		}
	}

	public function setData(?string $data) : void {
		$this->data = $data;
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
		$this->children = array();
		$this->childElementCount = 0;
	}

	public function appendChild(INode $child) : INode {
		$this->children[] = $child;
		$this->childElementCount++;
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
				$this->childElementCount--;
				$childNode->parentNode = null;
				return $childNode;
			}
		}

		throw new \Exception("Failed to execute 'removeChild' on 'Node': The node to be removed is not a child of this node.");

		return $childNode;
	}

	public function remove() : void {
		if ($this->parentNode === null) {
			return;
		}

		$count = \count($this->parentNode->children) - 1;
		for ($i = $count; $i >= 0; $i--) {
			if ($this->parentNode->children[$i] === $this) {
				\array_splice($this->parentNode->children, $i, 1);
				$this->parentNode->childElementCount--;
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
}