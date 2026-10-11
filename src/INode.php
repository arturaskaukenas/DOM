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

interface INode extends INodeBasic, INodeActions {
	/** The number of child elements. */
	public int $childElementsQty { get; }

	/** Flag indicating whether to treat data as children. */
	public bool $dataAsChildren { get; set; }

	/** Flag indicating whether to ignore children. */
	public bool $ignoreChildren { get; set; }

	/** Flag indicating whether to clean the node on finalize. */
	public bool $cleanOnFinalize { get; set; }

	/** Whether attribute names are case-sensitive (true for XML, false for HTML). */
	public bool $caseSensitive { get; }

	/** The ID of the node. */
	public ?string $nodeId { get; }

	/** The name of the node. */
	public ?string $nodeName { get; }

	/** The parent node. */
	public ?INode $parentNode { get; }

	/**
     * Sets the name of the node (nodeName).
     *
     * @param string $name		The name to set.
     * @return void
     */
	public function setName(string $name) : void;

	/**
     * Sets the attributes for the node.
     *
     * @param array $attributes<string, mixed>	An array containing the attributes to set.
     * @return void
     */
	public function setAttributes(array $attributes) : void;

	/**
     * Appends a child node to the end of the list of children of a current node.
     *
	 * @link https://developer.mozilla.org/en-US/docs/Web/API/Node/appendChild
	 *
     * @param INode $child	The child node to append.
     * @return INode		The appended child node.
     */
	public function appendChild(INode $child) : INode;

	/**
     * Removes a child node from the current node.
     *
	 * @link https://developer.mozilla.org/en-US/docs/Web/API/Node/removeChild
	 *
     * @param INode $childNode The child node to remove.
     * @return INode The removed child node.
     */
	public function removeChild(INode $childNode) : INode;

	/**
     * Cleans all children nodes of the current node.
     *
     * @return void
     */
	public function cleanChildren() : void;

	/**
     * Retrieves the expected structure of the node.
     *
     * @return array<Expected\IExpected> The expected structure of the node.
     */
	public function getExpected() : array;

     /**
     * Retrieves the expected child elements of the node.
     *
     * @return array<Expected\Element> The expected structure of the node.
     */
     public function getExpectedElements() : array;

     /**
     * Retrieves the expected attributes of the node.
     *
     * @return array<Expected\Attribute> The expected structure of the node.
     */
     public function getExpectedAttributes() : array;

	/**
     * Sets an expected values, templates, processing rules for child element.
     *
     * @param 	Expected\IExpected $expected    				Rules class.
     */
	public function expects(Expected\IExpected $expected) : INode;

	/**
     * Sets an expected node template for child element.
     *
     * @param 	string $name    					The name of the expected element.
     * @param 	string|\ReflectionClass $object		The class name or \ReflectionClass instance representing the expected object.
     * @param 	bool $isArray						Indicates whether the expected element is an array or single element.
     * @return INode 							The node instance.
     */
	public function setExpectedObject(string $name, string|\ReflectionClass $object, bool $isArray = false) : INode;

	/**
     * Sets an expected value based on a child element.
     *
     * @param 	string		$name            	     The name of the expected element.
     * @param 	NodeDataTypes	$type              	 	The data type of the expected value.
     * @param 	?callable	[$processFunction]            The function to process the value before setting it.
     * @return INode						     The node instance.
     */
	public function setExpectedValue(string $name, NodeDataTypes $type, ?callable $processFunction = null) : INode;

	/**
     * Sets a callback to parse data.
     *
     * @param 	callable	$callback 			The parser callback function.
     * @return void
     */
	public function useDataParser(callable $callback) : void;

	/**
     * Sets whether child elements should be ignored or not.
     *
     * @param 	bool		$value
     * @return void
     */
	public function setIgnoreChildren(bool $value) : void;

	/**
     * Sets whether child elements should be ignored or not only when initial value not set.
     *
     * @param 	bool		$value
     * @return void
     */
	public function setIgnoreChildrenIfNotExists(bool $value) : void;

	/**
     * Configures whether to treat inner content as child elements.
     *
     * @param 	bool		$value
     * @return void
     */
	public function setDataAsChildren(bool $value) : void;

	/**
     * Checks if the expected element exists.
     *
     * @param 	string		$name
     * @return bool
     */
	public function expectedElementExists(string $name) : bool;

	/**
     * Sets an expected element value. In case the element is an array, it will append the value.
     *
     * @param 	string		$name
     * @param 	mixed		$value
     * @return void
     */
	public function setExpectedElement(string $name, $value) : void;

	/**
     * Gets the value of the expected element.
     *
     * @param 	string		$name
     * @return mixed
     */
	public function __get(string $name);

	/**
     * Sets an expected element value.
     *
     * @param 	string		$name
     * @param 	mixed		$value
     * @return void
     */
	public function __set(string $name, $value) : void;

	/**
     * Checks if the expected element exists.
     *
     * @param  string		$name
     * @return bool
     */
	public function __isset(string $name): bool;

     /**
      * Checks if there are any errors.
      *
      * @return bool
      */
	public function errorsExists() : bool;

     /**
      * Retrieves an array of errors.
      *
      * @return array
      */
      public function getErrors() : array;
}