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
 * Class Parser
 *
 * Abstract class providing basic parsing functions.
 *
 * @package ArturasKaukenas\DOM
 */

abstract class Parser implements IParser {

	/**
     * @var resource|null The XML parser resource.
     */
	private $parser = null;

	/**
     * @var int The depth of the current parsing context.
     */
	private int $depth = -1;

	/**
     * @var array<string> An array containing the names of elements encountered during parsing.
     */
	private array $currentElementsNameTrace = [];

	/**
     * @var ?INode The root node of the parsed XML document.
     */
	private ?INode $result = null;

	/**
     * @var array<INode> An array containing references to currently parsed node objects.
     */
	private array $currentObjectsTrace = [];

	/**
     * @var ?INode The current parsing scope.
     */
	private ?INode $currentScope = null;

	/**
     * @var array<string, \ReflectionClass> An associative array mapping node names to their instances.
     */
	private array $registeredNodes = [];

	/**
     * @var ?IDataNode A reference to the latest data node encountered during parsing.
     */
	private ?IDataNode $latestDataElementReference = null;

	/**
     * @var array<string> An array containing parsing errors.
     */
	private array $errors = [];

	 /**
     * @var bool Indicates whether parsing errors exist.
     */
	private bool $errorsExists = false;

	/**
     * @var array<string, callable> An associative array mapping node names and callbacks to be executed upon finalizing the node.
     */
	private array $onFinalizeNode = [];

	public function init(?INode $currentScope = null, bool $skipParser = false) : void {
		if ($this->parser !== null) {
			$this->clean();
		}

		$this->result = $this->newRootNode();
		if ($currentScope !== null) {
			$this->result = $currentScope;
		}
		$this->result->setParser($this);

		$this->currentObjectsTrace[] = $this->result;
		$this->currentScope = $this->result;
		
		if ($skipParser) {
			return;
		}

		$this->parser = \xml_parser_create();
		\xml_parser_set_option($this->parser, \XML_OPTION_CASE_FOLDING, true);
		\xml_parser_set_option($this->parser, \XML_OPTION_TARGET_ENCODING, "UTF-8");
		\xml_set_element_handler(
			parser: $this->parser,
			start_handler: function($parser, string $name, array $attributes) : void {
				$this->startTagHandler($parser, $name, $attributes);
			},
			end_handler: function($parser, string $name) : void {
				$this->endTagHandler($parser, $name);
			}
		);

		\xml_set_character_data_handler(
			parser: $this->parser,
			handler: function($parser, string $data) : void {
				$this->dataHandler($parser, $data);
			}
		);
	}

	public function parse(string $data) : bool {
		$ret = \xml_parse($this->parser, $data);
		if ($ret === 0) {
			$error_text = (string) \xml_error_string(\xml_get_error_code($this->parser));
			$this->errors[] = $error_text;
			$this->errorsExists = true;
			\trigger_error("XML parsing error: ".$error_text, \E_USER_WARNING);
			return false;
		}

		return true;
	}

	public function finalize() : void {
		\xml_parse($this->parser, "", true);
	}

	protected function _getResult() : INode {
		return $this->result;
	}

	public function errorsExists() : bool {
		return $this->errorsExists;
	}

	public function getErrors() : array {
		return $this->errors;
	}

	public function clean() : void {
		if ($this->parser !== null) {
			\xml_parser_free($this->parser);
		}

		$this->parser = null;
		$this->depth = -1;
		$this->result = null;
		$this->currentElementsNameTrace = [];
		$this->currentObjectsTrace = [];
		$this->currentScope = null;
		$this->latestDataElementReference = null;
		$this->errors = [];
		$this->errorsExists = false;
	}

	public function registerNode(\ReflectionClass $class) : IParser {
		$name = $class->getConstant("NODE_NAME");
		if (!\is_string($name)) {
			$name = $class->getShortName();
		}

		if ($this->isNodeRegistered($name)) {
			return $this;
		}

		$this->registeredNodes[$name] = $class;

		return $this;
	}

	public function isNodeRegistered(string $elementName) : bool {
		if (isset($this->registeredNodes[$elementName])) {
			return true;
		}

		return false;
	}

	public function onFinalizeNode(string $name, callable $callback) : IParser {
		$name = \strtoupper($name);
		if (!isset($this->onFinalizeNode[$name])) {
			$this->onFinalizeNode[$name] = [];
		}

		$this->onFinalizeNode[$name][] = $callback;

		return $this;
	}

	protected function newRootNode() : INode {
		return new RootNode;
	}

	protected function newStdNode() : INode {
		return new StdNode;
	}

	protected function newDataNode() : INode {
		return new DataNode;
	}

	private function createNodeInstance(string $elementName) : INode {
		return $this->registeredNodes[$elementName]->newInstanceArgs();
	}

	private function startTagHandler($parser, string $name, array $attributes) : void {
		$this->closeDataElement();

		$this->depth++;
		$this->currentElementsNameTrace[] = $name;
		$obj = $this->prepareCurrentScope($name, $attributes);
		if ($obj === null) {
			return;
		}

		$this->currentObjectsTrace[] = $obj;
		$this->currentScope = $obj;
	}
	
	private function getCurrentScope() : INode {
		if ($this->currentScope !== null) {
			return $this->currentScope;
		}

		$this->currentScope = \end($this->currentObjectsTrace);

		return $this->currentScope;
	}

	private function prepareCurrentScope(string $name, array $attributes) : ?INode {
		$scope = $this->getCurrentScope();
		$obj = $this->determinateAndCreateNode($scope, $name, $attributes);
		if ($obj === null) {
			throw new \Exception("Unable to create Node");
		}

		return $obj;
	}
	
	private function determinateAndCreateNode(INode $scope, string $name, array $attributes) : ?INode {
		$result = $this->performExpectedObject($scope, $name, $attributes);

		if ($result instanceof INode) {
			return $result;
		}

		return $this->createNode($scope, $name, $attributes);
	}

	private function performExpectedObject(INode $scope, string $name, array $attributes) : ?INode {
		$expects = $scope->getExpectedElements();
		if (!\array_key_exists($name, $expects)) {
			return null;
		}

		if (!$expects[$name]->isPrototype()) {
			return null;
		}

		if ($expects[$name]->getPrototypeClass() !== null) {
			$obj = $this->getExpectedObjectByClass($expects[$name]->getPrototypeClass());
		} else if ($expects[$name]->getPrototypeName() !== null) {
			$obj = $this->getExpectedObjectByName($expects[$name]->getPrototypeName());
		} else {
				return null;
		}

		$ignoreChildren = false;
		if (isset($scope->ignoreChildren)) {
			if ($scope->ignoreChildren) {
				$ignoreChildren = true;
			}
		}

		$obj->setName($name, false);
		$obj->setAttributes($attributes);
		$obj->setParentNode($scope);
		$obj->setIgnoreChildrenIfNotExists($ignoreChildren);

		if (!$scope->expectedElementExists($name)) {
			throw new \Exception("Element '".$name."' not exists in Node class");
		}

		$scope->setExpectedElement($name, $obj);

		if ($ignoreChildren) {
			return $obj;
		}

		$scope->appendChild($obj);

		return $obj;
	}

	private function getExpectedObjectByName(string $name) : INode {
		if (!$this->isNodeRegistered($name)) {
			throw new \Exception("Node: '".$name."' not registered");
		}

		return $this->createNodeInstance($name);
	}

	private function getExpectedObjectByClass(\ReflectionClass $class) : INode {
		return $class->newInstanceArgs();
	}

	private function createNode(
		INode $scope,
		string $name,
		array $attributes
	) : INode {
		$className_f = null;
		$className = $this->prepareClassName($this->currentElementsNameTrace); //TO-DO: TEST
		if ($this->isNodeRegistered($className)) {
			$className_f = $className;
		} else {
				$className = $this->prepareClassNameString($name);  //TO-DO: TEST
				if ($this->isNodeRegistered($className)) {
					$className_f = $className;
				}
		}

		if ($className_f !== null) {
			$obj = $this->getExpectedObjectByName($className_f);
		} else {
				$obj = $this->newStdNode();
		}

		$obj->setParser($this);

		$ignoreChildren = false;
		if (isset($scope->ignoreChildren)) {
			if ($scope->ignoreChildren) {
				$ignoreChildren = true;
			}
		}

		$obj->setName($name, false);
		$obj->setAttributes($attributes);
		$obj->setParentNode($scope);
		$obj->setIgnoreChildrenIfNotExists($ignoreChildren);

		if ($ignoreChildren) {
			return $obj;
		}

		$scope->appendChild($obj);

		return $obj;
	}

	private function prepareClassName(array $path) : string {
		$className = \implode("_", $path);
		return $this->prepareClassNameString($className);
	}
	
	private function prepareClassNameString(string $className) : string {
		$className = \str_replace(":", "_", $className);//TODO: Documentation
		$className = \str_replace("-", "_", $className);//TODO: Documentation
		return $className;
	}

	private function endTagHandler($parser, string $name) : void {
		$this->closeDataElement();
		$this->closeNode();

		$this->latestDataElementReference = null;
		\array_pop($this->currentElementsNameTrace);
		\array_pop($this->currentObjectsTrace);
		$this->currentScope = null;
		$this->depth--;
	}

	private function closeDataElement() : void {
		if ($this->latestDataElementReference !== null) {
			if (!$this->latestDataElementReference->finalize()) {
				$this->latestDataElementReference->remove();
			}
		}

		$this->latestDataElementReference = null;
	}
	
	public function finalizeNode() : void {
		$this->closeNode();
	}

	private function closeNode() : void {
		$scope = $this->getCurrentScope();
		$remove = false;
		if (!$scope->finalize()) {
			$remove = true;
		}

		$name = $scope->nodeName;
		if (isset($this->onFinalizeNode[$name])) {
			foreach ($this->onFinalizeNode[$name] as $callback) {
				$callback($scope);
			}
		}
		
		if ($remove) {
			$scope->remove();
		}
	}

	public function setData(string $value) : void {
		$this->dataHandler(null, $value);
	}

	private function dataHandler($parser, string $value) : void {
		if (\trim($value) === "") {
			return;
		}

		$scope = $this->getCurrentScope();
		$result = $this->extractValues($scope, $scope->nodeName, $value);
		if ($result === null) {
			return;
		}

		if (isset($scope->dataAsChildren)) {
			if ($scope->dataAsChildren) {
				$this->appendDataAsChildren($scope, $value);
				return;
			}
		}

		$scope->setData($value);
	}

	private function extractValues(INode $scope, string $name, string $value) : ?bool {
		if (!$scope instanceof INode) {
			return false;
		}

		if ($scope->parentNode === null) {
			return false;
		}

		$expects = $scope->parentNode->getExpectedElements();
		if (!\array_key_exists($name, $expects)) {
			return false;
		}

		if ($expects[$name]->isPrototype()) {
			return false;
		}

		$scope->parentNode->appendExpectedValue($name, $value);

		return true;
	}

	private function appendDataAsChildren(INode $scope, string $data) : void {
		if ($this->latestDataElementReference === null) {
			$this->latestDataElementReference = $this->newDataNode();
			$this->latestDataElementReference->setParser($this);
			$this->latestDataElementReference->setParentNode($scope);
			$scope->appendChild($this->latestDataElementReference);
		}

		$this->latestDataElementReference->appendData($data);
	}
}