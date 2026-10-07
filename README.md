# PHP XML and HTML Parsing and Managing Library

## License

This software's source files are licensed under the Apache-2.0 license.

`SPDX-License-Identifier: Apache-2.0`

## Description

This library is built on top of PHP's SAX parser, offering a flexible way to process and manipulate XML and HTML data.
By leveraging the advantages of the SAX parser, it efficiently parses huge files by processing them in chunks, minimizing memory usage.
It provides a structured approach to parsing, managing, and transforming both XML and HTML documents, supporting attributes, child elements, and customizable data handling.

### Main Features

 - Converts XML documents into object DOM trees.
 - Supports custom templated elements with:
    - Expected values
    - Processors
    - Validators
 - Provides support for chunk parsing (for valid XML documents), enabling efficient processing of large XML files.
 - Offers callbacks for specified elements, allowing define custom actions during parsing.
 - Allows removal of elements from the DOM tree after finalization, resulting in persistent and low memory consumption on huge XML files.
 - Supports main standard methods:
     - [Document](https://developer.mozilla.org/en-US/docs/Web/API/Document/Document)
     - [Element](https://developer.mozilla.org/en-US/docs/Web/API/Element)

## Installation

### Using Composer
```console
composer require arturaskaukenas/dom
```

### Manual Inclusion
```php
require_once("{path_to_library}/src/load.php");
```
Replace `{path_to_library}` with the actual path to the library's source files.

## Examples

### Basic parsing
```php
use \ArturasKaukenas\DOM;

$content = \file_get_contents("./data/books.xml");
$parser = new DOM\XML\Parser();
$xml = $parser->fullParse($content);
```

### Working with elements

 - [INodeActions](#inodeactions-interface)
 - [INodeBasic](#inodebasic-interface)
 
```php
use \ArturasKaukenas\DOM\XML\Parser;

$content = \file_get_contents("./data/books.xml");
$parser = new Parser();

$full_result = $parser->fullParse($content);

$node = $full_result->getChild(0)->getChild(0);

\count($result->getElementsByTagName("publish_date"))
$full_result->getElementsByTagName("id")[1]->getTextContents();

$full_result->getElementById("bk103");

$full_result->getElementById("text_content_test")->setTextContents("2000-12-15");

//Basic
$node->hasAttributes();
$node->hasAttribute("testAttribute");
$node->getAttributeNames()[1];
$node->getAttribute("testattribute");
$node->setAttribute("test", "2");
$node->removeAttribute("testAttribute");

//Child elements
$result = $full_result->getChild(0);
$result->currentChild()->getAttribute("id"); //Child 0
$result->nextChild()->getAttribute("id"); //Child 1
$result->nextChild()->getAttribute("id"); //Child 2

$result->resetChild();

$result->iterateChild()->getAttribute("id"); //Child 0
$result->iterateChild()->getAttribute("id"); //Child 1
$result->iterateChild()->getAttribute("id"); //Child 2

$result->endChild()->getAttribute("id");

//Append child
$test_node = new XML\StdNode;
$test_node->setName("TEST_NODE_1");
$test_node->setAttributes(array("id" => "test_node_id_1"));
$result->appendChild($test_node);

//Remove child
$result->removeChild($test_node);

```

### Chunk parsing

 - [IParser](#iparser-interface)

```php
use \ArturasKaukenas\DOM;

$parser = new DOM\XML\Parser();
$parser
    ->onFinalizeNode(
		"BOOK",
		function(DOM\INode $node) {
			/*
				$node->AUTHOR ..
				$node->TITLE ..
			*/
		}
	);
	$parser->prepare();

	$handle = \fopen($folder."books.xml", "r");
	while (!\feof($handle)) {
		$buffer = \fgets($handle, 4096);
		$parser->getCallBack()($buffer);
	}
	\fclose($handle);
```

### Templated element with processors and validators 

 - [IParser](#iparser-interface)
 - [Expected](#expected-class)

```php
use \ArturasKaukenas\DOM;

class BOOK extends DOM\XML\Node {
	public function __construct() {
		$this->
			expects(
				(new DOM\Expected\Element("author", DOM\NodeDataTypes::T_STRING))->
					process(
						function ($value) : string {
							return \strtoupper((string) $value);
						}
					)
			)->
			setExpectedValue("title", DOM\NodeDataTypes::T_STRING)->
			setExpectedValue("genre", DOM\NodeDataTypes::T_STRING)->
			expects(
				(new DOM\Expected\Element("price", DOM\NodeDataTypes::T_FLOAT))->
					validate(//Example
						function (float $value) {
							if ($value > 10) {
								return "Should be cheaper than 10";
							}

							return true;
						}
					)
			)->
			setExpectedValue(
				"publish_date",
				DOM\NodeDataTypes::T_MIXED,
				function($value) {
					$timestamp = \strtotime((string) $value);
					if ($timestamp === false) {
						return null;
					}

					return (new \DateTime())->setTimestamp($timestamp);
				}
			)->
			setExpectedValue("description", DOM\NodeDataTypes::T_STRING);
	}
}

$parser = new DOM\XML\Parser();
$parser
    ->onFinalizeNode(
		"BOOK",
		function(DOM\INode $node) {
			echo "Title: ".$node->TITLE."\n";
			echo "Author: ".$node->AUTHOR."\n";
			echo "Price: ".$node->PRICE."\n";
			echo "Date: ".$node->PUBLISH_DATE->format(DateTime::ATOM)."\n";
		}
	);
	$parser->prepare();

	$handle = \fopen($folder."books.xml", "r");
	while (!\feof($handle)) {
		$buffer = \fgets($handle, 4096);
		$parser->getCallBack()($buffer);
	}
	\fclose($handle);
```

## Classes

### Node Class

The `Node` class is an abstract representation of a DOM node, implementing the [INode](#inode-interface) interface.

#### Properties

- **`?string $nodeName`** *(read-only)*: The name of the node.
- **`?INode $parentNode`** *(read-only)*: The parent node of this node.
- **`int $childElementsQty`** *(read-only)*: The number of child elements.
- **`bool $dataAsChildren`** *(read-only)*: Indicates whether data should be treated as children.
- **`bool $ignoreChildren`** *(read-only)*: Indicates whether child nodes should be ignored.
- **`bool $cleanOnFinalize`** *(read/write)*: Indicates whether the node should be cleaned upon finalization.

---

### INode Interface
`INode` extends `INodeBasic` and `INodeActions` and provides methods to define and manage expected values, templates, and processing rules for child elements.

#### Methods

##### `expects`
```php
public function expects(DOM\Expected\IExpected $expected): INode;
```
- **Description:** Sets expected values, templates, or processing rules for a child element.
- **Parameters:**
  - `IExpected $expected`: Rules class.
- **Returns:**
  - `INode`: The current node instance.

---

##### `setExpectedObject`
```php
public function setExpectedObject(string $name, $object, bool $isArray = false): INode;
```
- **Description:** Sets an expected node template for a child element.
- **Parameters:**
  - `string $name`: The name of the expected element.
  - `string|\ReflectionClass $object`: The class name or `\ReflectionClass` instance representing the expected object.
  - `bool $isArray`: Indicates whether the expected element is an array or a single element.
- **Returns:**
  - `INode`: The current node instance.

---

##### `setExpectedValue`
```php
public function setExpectedValue(string $name, int $type, ?callable $processFunction = null): INode;
```
- **Description:** Sets an expected value based on a child element.
- **Parameters:**
  - `string $name`: The name of the expected element.
  - `int $type`: The data type of the expected value (use constants from the `NodeDataTypes` class).
  - `?callable $processFunction`: (Optional) The function to process the value before setting it.
- **Returns:**
  - `INode`: The current node instance.

---

##### `useDataParser`
```php
public function useDataParser(callable $callback): void;
```
- **Description:** Sets a callback to parse data.
- **Parameters:**
  - `callable $callback`: The parser callback function.
- **Returns:**
  - `void`

---

##### `setIgnoreChildren`
```php
public function setIgnoreChildren(bool $value): void;
```
- **Description:** Sets whether child elements should be ignored or not.
- **Parameters:**
  - `bool $value`
- **Returns:**
  - `void`

---

##### `setIgnoreChildrenIfNotExists`
```php
public function setIgnoreChildrenIfNotExists(bool $value): void;
```
- **Description:** Sets whether child elements should be ignored or not only when initial value not set.
- **Parameters:**
  - `bool $value`
- **Returns:**
  - `void`

---

##### `setDataAsChildren`
```php
public function setDataAsChildren(bool $value): void;
```
- **Description:** Configures whether to treat data as child elements.
- **Parameters:**
  - `bool $value`
- **Returns:**
  - `void`

---

##### `expectedElementExists`
```php
public function expectedElementExists(string $name) : bool;
```
- **Description:** Checks if the expected element exists.
- **Parameters:**
  - `string $name`
- **Returns:**
  - `bool`

---

##### `setExpectedElement`
```php
public function setExpectedElement(string $name, $value) : void;
```
- **Description:** Sets an expected element value. In case the element is an array, it will append the value.
- **Parameters:**
  - `string $name`
  - `mixed $value`
- **Returns:**
  - `void`

---

##### `__get`
```php
public function __get(string $name) : mixed;
```
- **Description:** Gets the value of the expected element.
- **Parameters:**
  - `string $name`
- **Returns:**
  - `mixed`

---

##### `__set`
```php
public function __set(string $name, $value) : void;
```
- **Description:** Sets an expected element value.
- **Parameters:**
  - `string $name`
  - `mixed $value`
- **Returns:**
  - `void`

---

##### `__isset`
```php
public function __isset(string $name) : bool;
```
- **Description:** Checks if the expected element exists.
- **Parameters:**
  - `string $name`
- **Returns:**
  - `bool`

---

### INodeActions Interface
`INodeActions` defines methods for managing attributes, navigating child nodes, and handling node content.

#### Methods

##### `hasAttributes`
```php
public function hasAttributes(): bool;
```
- **Description:** Checks if the node has any attributes.
- **Link:** [MDN Reference](https://developer.mozilla.org/en-US/docs/Web/API/Element/hasAttributes)
- **Returns:**
  - `bool`: `true` if the node has attributes, `false` otherwise.

---

##### `hasAttribute`
```php
public function hasAttribute(string $name): bool;
```
- **Description:** Checks if the node has the specified attribute.
- **Link:** [MDN Reference](https://developer.mozilla.org/en-US/docs/Web/API/Element/hasAttribute)
- **Parameters:**
  - `string $name`: The name of the attribute to check.
- **Returns:**
  - `bool`: `true` if the node has the attribute, `false` otherwise.

---

##### `getAttributeNames`
```php
public function getAttributeNames(): array;
```
- **Description:** Retrieves an array of attribute names.
- **Link:** [MDN Reference](https://developer.mozilla.org/en-US/docs/Web/API/Element/getAttributeNames)
- **Returns:**
  - `array<string>`: An array containing the names of all attributes on the node.

---

##### `getAttribute`
```php
public function getAttribute(string $name): ?string;
```
- **Description:** Retrieves the value of the specified attribute.
- **Link:** [MDN Reference](https://developer.mozilla.org/en-US/docs/Web/API/Element/getAttribute)
- **Parameters:**
  - `string $name`: The name of the attribute.
- **Returns:**
  - `?string`: The value of the attribute, or `null` if it does not exist.

---

##### `setAttribute`
```php
public function setAttribute(string $key, $value): void;
```
- **Description:** Sets the value of the specified attribute.
- **Link:** [MDN Reference](https://developer.mozilla.org/en-US/docs/Web/API/Element/setAttribute)
- **Parameters:**
  - `string $key`: The name of the attribute.
  - `mixed $value`: The value to assign. Non-string values are converted to strings (`null` -> 'null', `true` -> 'true', `false` -> 'false').
- **Returns:**
  - `void`

---

##### `removeAttribute`
```php
public function removeAttribute(string $name): void;
```
- **Description:** Removes the specified attribute.
- **Link:** [MDN Reference](https://developer.mozilla.org/en-US/docs/Web/API/Element/removeAttribute)
- **Parameters:**
  - `string $name`: The name of the attribute to remove.
- **Returns:**
  - `void`
- **Throws:**
  - `\Exception`: If the name is not a valid XML name.

---

##### `getChild`
```php
public function getChild(int $num): ?INode;
```
- **Description:** Retrieves the child node at the specified index.
- **Parameters:**
  - `int $num`: The index of the child node.
- **Returns:**
  - `?INode`: The child node at the specified index, or `null` if out of range.

---

##### `currentChild`
```php
public function currentChild(): ?INode;
```
- **Description:** Retrieves the currently selected child node.
- **Returns:**
  - `?INode`: The currently selected child node, or `null` if none.

---

##### `nextChild`
```php
public function nextChild(): ?INode;
```
- **Description:** Moves the internal pointer forward and returns the next child node.
- **Returns:**
  - `?INode`: The next child node, or `null` if none.

---

##### `iterateChild`
```php
public function iterateChild(): ?INode;
```
- **Description:** Retrieves the current child node and moves the internal pointer forward.
- **Returns:**
  - `?INode`: The current child node, or `null` if none.

---

##### `endChild`
```php
public function endChild(): ?INode;
```
- **Description:** Retrieves the last child node.
- **Returns:**
  - `?INode`: The last child node, or `null` if none.

---

##### `resetChild`
```php
public function resetChild(): ?INode;
```
- **Description:** Resets the internal pointer to the first child node.
- **Returns:**
  - `?INode`: The first child node, or `null` if none.

---

##### `getElementsByTagName`
```php
public function getElementsByTagName(string $tagName, bool $clean = true): array;
```
- **Description:** Retrieves elements with the specified tag name.
- **Parameters:**
  - `string $tagName`: A string representing the name of the elements.
  - `bool $clean`: Automatically trims and uppercases the tag name.
- **Returns:**
  - `array`: An array of found elements in the order they appear in the tree.

---

##### `getElementById`
```php
public function getElementById(string $id): ?INode;
```
- **Description:** Retrieves an element by its `id` attribute.
- **Parameters:**
  - `string $id`: The value of the `id` attribute to search for.
- **Returns:**
  - `?INode`: The element with the specified ID, or `null` if not found.

---

##### `getTextContents`
```php
public function getTextContents(): string;
```
- **Description:** Retrieves the text content of the node.
- **Link:** [MDN Reference](https://developer.mozilla.org/en-US/docs/Web/API/Node/textContent)
- **Returns:**
  - `string`: The text content of the node.

---

##### `setTextContents`
```php
public function setTextContents(?string $data): void;
```
- **Description:** Sets the text content of the node.
- **Link:** [MDN Reference](https://developer.mozilla.org/en-US/docs/Web/API/Node/textContent)
- **Parameters:**
  - `?string $data`: The text content to set.
- **Returns:**
  - `void`

---

##### `getInner`
```php
public function getInner(): string;
```
- **Description:** Retrieves the content and all tags of the node.
- **Returns:**
  - `string`: The content of the node.

---

##### `setInner`
```php
public function setInner(?string $data): void;
```
- **Description:** Sets the content and all tags of the node and triggers the parser.
- **Parameters:**
  - `?string $data`: The XML data to set.
- **Returns:**
  - `void`

---

### INodeBasic Interface

`INodeBasic` defines the basic operations for a node, such as managing attributes, appending and removing child nodes, and setting a parser instance.

#### Methods

##### `setName`
```php
public function setName(string $name, bool $clean) : void;
```
- **Description:** Sets the name of the node (`nodeName`).
- **Parameters:**
  - `string $name`: The name to set.
  - `bool $clean`: Automatically trim and uppercase the provided node name.
- **Returns:** `void`

---

##### `setAttributes`
```php
public function setAttributes(array $attributes) : void;
```
- **Description:** Sets the attributes for the node.
- **Parameters:**
  - `array $attributes`: An array containing the attributes to set (key-value pairs).
- **Returns:** `void`

---

##### `appendChild`
```php
public function appendChild(INode $child) : INode;
```
- **Description:** Appends a child node to the end of the list of children of the current node.
- **Parameters:**
  - `INode $child`: The child node to append.
- **Returns:** `INode`: The appended child node.
- **Links:**
  - [MDN: Node.appendChild](https://developer.mozilla.org/en-US/docs/Web/API/Node/appendChild)

---

##### `removeChild`
```php
public function removeChild(INode $childNode) : INode;
```
- **Description:** Removes a child node from the current node.
- **Parameters:**
  - `INode $childNode`: The child node to remove.
- **Returns:** `INode`: The removed child node.
- **Links:**
  - [MDN: Node.removeChild](https://developer.mozilla.org/en-US/docs/Web/API/Node/removeChild)

---

##### `remove`
```php
public function remove() : void;
```
- **Description:** Removes the current node from its parent node, if it exists.
- **Returns:** `void`
- **Links:**
  - [MDN: Element.remove](https://developer.mozilla.org/en-US/docs/Web/API/Element/remove)

---

##### `cleanChildren`
```php
public function cleanChildren() : void;
```
- **Description:** Cleans all child nodes of the current node.
- **Returns:** `void`

---

##### `setParser`
```php
public function setParser(IParser $parser) : void;
```
- **Description:** Sets the parser instance for the node.
- **Parameters:**
  - `IParser $parser`: The parser instance.
- **Returns:** `void`

---

##### `getExpected`
```php
public function getExpected() : array;
```
- **Description:** Retrieves the expected structure of the node.
- **Returns:** `array<Expected\IExpected>`: The expected structure of the node.

---

##### `getExpectedElements`
```php
public function getExpectedElements() : array;
```
- **Description:** Retrieves the expected child elements of the node.
- **Returns:** `array<Expected\Element>`: The expected structure of the node.

---

##### `getExpectedAttributes`
```php
public function getExpectedAttributes() : array;
```
- **Description:** Retrieves the expected attributes of the node.
- **Returns:** `array<Expected\Attribute>`: The expected structure of the node.

---

##### `getData`
```php
public function getData() : ?string;
```
- **Description:** Retrieves the node's data.
- **Returns:** `?string`

---

### XML\INode Interface

`XML\INode` extends `INode` and provides additional methods to manage XML-specific content for the node.

#### Methods

##### `getInner`
```php
public function getInner() : string;
```
- **Description:** Retrieves the inner XML content of the node, including child nodes and data.
- **Returns:** `string`: The inner XML content of the node.

---

##### `getInnerXML`
```php
public function getInnerXML() : string;
```
- **Description:** Retrieves the inner XML content of the node, including child nodes and data.
- **Returns:** `string`: The inner XML content of the node.

---

##### `setInner`
```php
public function setInner(?string $data) : void;
```
- **Description:** Sets the inner content of the node with the provided data.
- **Parameters:**
  - `?string $data`: The data to set as inner content.
- **Returns:** `void`

---

##### `setInnerXML`
```php
public function setInnerXML(?string $data) : void;
```
- **Description:** Sets the inner content of the node with the provided data.
- **Parameters:**
  - `?string $data`: The data to set as inner content.
- **Returns:** `void`

---

##### `errorsExists`
```php
public function errorsExists() : bool;
```
- **Description:** Checks if there are any errors.
- **Returns:** `bool`

---

##### `getErrors`
```php
public function getErrors() : array;
```
- **Description:** Retrieves an array of errors.
- **Returns:** `array<string>`

### IParser Interface

`IParser` represents a parser.

#### Methods

##### `init`
```php
public function init(?INode $currentScope = null, bool $skipParser = false) : void;
```
- **Description:** Initializes the parser.
- **Parameters:**
  - `?INode $currentScope`: The current parsing scope.
  - `bool $skipParser`: Whether to skip initializing the XML parser.
- **Returns:** `void`

---

##### `parse`
```php
public function parse(string $data) : bool;
```
- **Description:** Parses the provided data.
- **Parameters:**
  - `string $data`: The data to parse.
- **Returns:** `bool`: True if parsing was successful, false otherwise.

---

##### `finalize`
```php
public function finalize() : void;
```
- **Description:** Finalizes parsing and cleans up resources.
- **Returns:** `void`

---

##### `errorsExists`
```php
public function errorsExists() : bool;
```
- **Description:** Checks if any parsing errors occurred.
- **Returns:** `bool`.

---

##### `getErrors`
```php
public function getErrors() : array;
```
- **Description:** Retrieves parsing errors.
- **Returns:** `array<string>`.

---

##### `clean`
```php
public function clean() : void;
```
- **Description:** Cleans up resources and resets the parser state.
- **Returns:** `void`

---

##### `registerNode`
```php
public function registerNode(\ReflectionClass $class) : IParser;
```
- **Description:** Registers a custom node template for parsing.
- **Parameters:**
  - `\ReflectionClass $class`: The class to register.
- **Returns:** `IParser`: The parser instance.

---

##### `isNodeRegistered`
```php
public function isNodeRegistered(string $elementName) : bool;
```
- **Description:** Checks if a node template is registered.
- **Parameters:**
  - `string $elementName`: The name of the node template.
- **Returns:** `bool`: True if the node class is registered, false otherwise.

---

##### `onFinalizeNode`
```php
public function onFinalizeNode(string $name, callable $callback) : IParser;
```
- **Description:** Registers a callback to be executed when finalizing a specific node type.
- **Parameters:**
  - `string $name`: The name of the node type.
  - `callable $callback`: The callback function.
- **Returns:** `IParser`: The parser instance for method chaining.

---

### Expected Class

The `Expected` class represents the expected properties of a node, including its name, type, validation, processing, and prototype settings.

#### Properties

- **`string $name`** *(read/write)*: The name of the expected property.
- **`value-of<NodeDataTypes> $type`** *(read/write)*: The data type of the expected property.
- **`bool $usePreValidate`** *(read-only)*: Indicates if pre-validation is enabled.
- **`?callable $preValidateFunction`** *(read/write)*: The function for pre-validation.
- **`bool $useProcess`** *(read-only)*: Indicates if processing is enabled.
- **`?callable $processFunction`** *(read/write)*: The function for processing.
- **`bool $usePrototype`** *(read-only)*: Indicates if a prototype is used.
- **`?string $prototypeName`** *(read/write)*: The name of the prototype.
- **`?\ReflectionClass $prototypeClass`** *(read/write)*: The class of the prototype.

#### Methods

##### `__construct`
```php
public function __construct(string $name, int $type = NodeDataTypes::T_MIXED);
```
- **Description:** Constructs a new instance of the `Expected` class.
- **Parameters:**
  - `string $name`: The name of the expected property.
  - `int $type`: The data type of the expected property (default: `NodeDataTypes::T_MIXED`).
- **Returns:** `void`

---

##### `typeOf`
```php
public function typeOf(int $type);
```
- **Description:** Specifies the data type of the expected property.
- **Parameters:**
  - `int $type`: The data type to set (use constants from `NodeDataTypes`).
- **Returns:** `$this`

---

##### `preValidate`
```php
public function preValidate(callable $function) : Expected;
```
- **Description:** Specifies a function for pre-validation.
- **Parameters:**
  - `callable $function`: The function for pre-validation. The function should accept the value of the property as its only parameter and return either a boolean or a string describing the reason for validation failure.
- **Returns:** `Expected`: The current instance for method chaining.

---

##### `process`
```php
public function process(callable $function) : Expected;
```
- **Description:** Specifies a function for processing.
- **Parameters:**
  - `callable $function`: The function for processing. The function should accept the value of the property as its only parameter and return the processed value.
- **Returns:** `Expected`: The current instance for method chaining.

---

##### `validate`
```php
public function validate(callable $function) : Expected;
```
- **Description:** Specifies a function for validation.
- **Parameters:**
  - `callable $function`: The function for validation. The function should accept the value of the property as its only parameter and return either a boolean or a string describing the reason for validation failure.
- **Returns:** `Expected`: The current instance for method chaining.

---

##### `prototypeOfClassName`
```php
public function prototypeOfClassName(string $name) : Expected;
```
- **Description:** Specifies the prototype of the expected child by class name.
- **Parameters:**
  - `string $name`: The name of the class.
- **Returns:** `Expected`: The current instance for method chaining.

---

##### `prototypeOfObject`
```php
public function prototypeOfObject(\ReflectionClass $class) : Expected;
```
- **Description:** Specifies the prototype of the expected child by class.
- **Parameters:**
  - `\ReflectionClass $class`: The reflection class of the prototype.
- **Returns:** `Expected`: The current instance for method chaining.

---

Вот преобразованный PHP код в формат Markdown с учётом комментариев:

### NodeDataTypes Class

The `NodeDataTypes` class defines constants representing different data types for node properties.

#### Constants

- **`T_UNDEFINED`** (`int`): Represents an undefined data type. Value: `-10`
- **`T_PROTO`** (`int`): Represents the prototype data type. Value: `-1`
- **`T_MIXED`** (`int`): Represents a mixed data type. Value: `0`
- **`T_ARRAY`** (`int`): Represents an array data type. Value: `1`
- **`T_STRING`** (`int`): Represents a string data type. Value: `2`
- **`T_INT`** (`int`): Represents an integer data type. Value: `3`
- **`T_FLOAT`** (`int`): Represents a float data type. Value: `4`
- **`T_BOOL`** (`int`): Represents a boolean data type. Value: `5`

#### Methods

##### `getTypeName`
```php
public static function getTypeName(int $type);
```
- **Description:** Retrieves the name of the data type constant based on the given type value.
- **Parameters:**
  - `int $type`: The type constant value to retrieve the name for.
- **Returns:** `string`: The name of the type (e.g., `T_STRING`, `T_INT`), or `"T_UNDEFINED"` if the type is not found.

---

