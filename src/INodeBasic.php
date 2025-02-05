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

interface INodeBasic {
	/**
     * Sets the name of the node (nodeName).
     *
     * @param string $name		The name to set.
     * @param bool $clean		Automatically trim and uppercase provided node name.
     * @return void
     */
	public function setName(string $name, bool $clean) : void;

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
     * Removes the current node from its parent node, if it exists.
     *
	 * @link https://developer.mozilla.org/en-US/docs/Web/API/Element/remove
	 *
     * @return void
     */
	public function remove() : void;

	/**
     * Cleans all children nodes of the current node.
     *
     * @return void
     */
	public function cleanChildren() : void;

	/**
     * Sets the parser instance for the node.
     *
     * @param IParser $parser The parser instance.
     * @return void
     */
	public function setParser(IParser $parser) : void;

	/**
     * Retrieves the expected structure of the node.
     *
     * @return array The expected structure of the node.
     */
	public function getExpected() : array;

	public function setData(?string $data) : void;

	public function appendExpectedValue(string $name, string $value) : void;

	public function setParentNode(INode $parentNode) : void;

	public function setParserReference(INode $node, ?IParser $parser);

	public function updateChildrenParser() : void;
	
	public function finalize() : bool;
}