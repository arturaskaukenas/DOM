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

interface INodeActions {
	/**
     * Checks if the node has any attributes.
	 *
	 * @link https://developer.mozilla.org/en-US/docs/Web/API/Element/hasAttributes
	 *
     * @return	bool		True if the node has attributes, false otherwise.
     */
	public function hasAttributes() : bool;


	/**
     * Checks if the node has the specified attribute.
     *
	 * @link https://developer.mozilla.org/en-US/docs/Web/API/Element/hasAttribute
	 *
     * @param string $name	The name of the attribute to check.
     * @return bool			True if the node has the attribute, false otherwise.
     */
	public function hasAttribute(string $name) : bool;


	/**
     * Retrieves an array of attribute names.
     *
	 * @link https://developer.mozilla.org/en-US/docs/Web/API/Element/getAttributeNames
	 *
     * @return array<string>	An array containing the names of all attributes on the node.
     */
	public function getAttributeNames() : array;


	/**
     * Retrieves the value of the specified attribute.
     *
	 * @link https://developer.mozilla.org/en-US/docs/Web/API/Element/getAttribute
	 *
     * @param string $name		is the name of the attribute whose value you want to get.
     * @return ?string			The value of the attribute, or null if the attribute does not exist.
     */
	public function getAttribute(string $name) : ?string;


	/**
     * Sets the value of the specified attribute.
     *
	 * @link https://developer.mozilla.org/en-US/docs/Web/API/Element/setAttribute
	 *
     * @param string $key		A string specifying the name of the attribute whose value is to be set.
								The attribute name is automatically converted to all lower-case.
     * @param mixed $value		A string containing the value to assign to the attribute.
								Any non-string value specified is converted automatically into a string.
								Non-string conversion: null -> 'null', true -> 'true', false -> 'false'.
     * @return void
     */
	public function setAttribute(string $key, $value) : void;

	/**
     * Removes the specified attribute.
     *
	 * @link https://developer.mozilla.org/en-US/docs/Web/API/Element/removeAttribute
	 *
     * @param	string	$name	A string specifying the name of the attribute to remove from the element.
								If the specified attribute does not exist, removeAttribute() returns without generating an error.
     * @return void
	 * @throws \Exception 		Thrown exception if the name value is not a valid XML name (https://www.w3.org/TR/REC-xml/#dt-name);
								for example, it starts with a number, a hyphen, or a period, or contains characters other than alphanumeric characters, underscores, hyphens, or periods..
     */
	public function removeAttribute(string $name) : void;
	
	/**
     * Retrieves the child node at the specified index.
     *
     * @param int $num			The index of the child node.
     * @return ?INode			The child node at the specified index, or null if the index is out of range.
     */
	public function getChild(int $num) : ?INode;

	/**
     * Retrieves the child node that's currently being pointed to by the internal pointer.
     *
     * @return ?INode			The currently selected child node, or null if there are no more children.
     */
	public function currentChild() : ?INode;

	/**
     * Move internal pointer one place forward and return child node.
     *
     * @return ?INode			The next child node, or null if there are no more children.
     */
	public function nextChild() : ?INode;

	/**
     * Get current child node and move internal pointer one place forward.
     *
     * @return ?INode			The currently selected child node, or null if there are no more children.
     */
	public function iterateChild() : ?INode;

	/**
     * Retrieves the last child node.
     *
     * @return ?INode			The last child node, or null if there are no children.
     */
	public function endChild() : ?INode;

	/**
     * Resets the internal pointer.
     *
     * @return ?INode			The first child node, or null if there are no children.
     */
	public function resetChild() : ?INode;

	/**
     * Retrieves a collection of elements with the specified tag name.
     *
     * @param string $tagName	A string representing the name of the elements.
     * @param bool $clean		Automatically trim and uppercase provided tag name.
     * @return array			An array of found elements in the order they appear in the tree.
     */
	public function getElementsByTagName(string $tagName, bool $clean = true) : array;

	/**
     * Retrieves an element by its 'id' attribute.
     *
     * @param string $id		The value of the ID attribute to search for.
     * @return ?INode			The element with the specified ID, or null if no such element exists.
     */
	public function getElementById(string $id) : ?INode;

	/**
     * Retrieves the text content of the node.
     *
	 * @link https://developer.mozilla.org/en-US/docs/Web/API/Document/getTextContents
	 *
     * @return string			The text content of the node.
     */
	public function getTextContents() : string;

	/**
     * Sets the text content of the node.
	 * The finalizeNode internal method will be triggered.
     *
	 * @link https://developer.mozilla.org/en-US/docs/Web/API/Node/textContent
	 *
     * @param ?string $data		The text content to set.
     * @return void
     */
	public function setTextContents(?string $data) : void;

	 /**
     * Retrieves the content and all tags of the node.
     *
	 * This method is not implemented in the basic interface and should be implemented
	 * in classes that implement this interface, if necessary.
	 *
     * @return string The content of the node.
     */
	public function getInner() : string;

	/**
     * Sets the content and all tags of the node and trigger parser.
	 *
	 * This method is not implemented in the basic interface and should be implemented
	 * in classes that implement this interface, if necessary.
     *
     * @param ?string $data 	The XML data to set.
     * @return void
     */
	public function setInner(?string $data) : void;
}