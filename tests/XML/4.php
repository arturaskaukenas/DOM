#!/usr/bin/php -d display_errors=1
<?php
	require_once(__DIR__."/../load.php");
	$test_case = new \ArturasKaukenas\SimpleTest\TestCase(4, "INode children");

	use \ArturasKaukenas\DOM\XML;

	$content = \file_get_contents("./data/books.xml");
	$parser = new XML\Parser();
	$full_result = $parser->fullParse($content);
	$result = $full_result->getChild(0);

	//getChild
	$test_case->part("getChild");
	\assert($result->getChild(0) !== null);
	\assert($result->getChild(1) !== null);

	//currentChild
	$test_case->part("currentChild");
	\assert($result->currentChild()->getAttribute("id") === "bk101");

	//nextChild
	$test_case->part("nextChild");
	$result->resetChild();
	\assert($result->nextChild()->getAttribute("id") === "bk102");
	\assert($result->nextChild()->getAttribute("id") === "bk103");
	\assert($result->nextChild() === null);

	//iterateChild
	$test_case->part("iterateChild");
	$result->resetChild();

	\assert($result->iterateChild()->getAttribute("id") === "bk101");
	\assert($result->iterateChild()->getAttribute("id") === "bk102");
	\assert($result->iterateChild()->getAttribute("id") === "bk103");
	\assert($result->iterateChild() === null);

	//endChild
	$test_case->part("endChild");
	$result->resetChild();
	\assert($result->endChild()->getAttribute("id") === "bk103");

	//resetChild
	$test_case->part("resetChild");
	\assert($result->resetChild()->getAttribute("id") === "bk101");
	\assert($result->nextChild()->getAttribute("id") === "bk102");

	//appendChild
	$test_case->part("appendChild");
	$test_node = new XML\StdNode;
	$test_node->setName("TEST_NODE_1");
	$test_node->setAttributes(array("id" => "test_node_id_1"));

	$test_node2 = new XML\StdNode;
	$test_node2->setName("TEST_NODE_2");
	$test_node2->setAttributes(array("id" => "test_node_id_2"));

	$action_result = $result->appendChild($test_node);
	\assert($action_result->nodeName === "TEST_NODE_1");
	\assert($result->endChild()->nodeName === "TEST_NODE_1");
	\assert($result->endChild()->getAttribute("id") === "test_node_id_1");

	$action_result = $result->appendChild($test_node2);
	\assert($action_result->nodeName === "TEST_NODE_2");
	\assert($result->endChild()->nodeName === "TEST_NODE_2");
	\assert($result->endChild()->getAttribute("id") === "test_node_id_2");

	//removeChild
	$test_case->part("removeChild");
	$action_result = $result->removeChild($test_node);
	\assert($action_result->nodeName === "TEST_NODE_1");
	\assert($result->endChild()->nodeName === "TEST_NODE_2");
	$action_result = $result->removeChild($result->endChild());
	\assert($result->endChild()->nodeName === "BOOK");

	$test_case->part("removeChild - exception");
	try {
		$action_result = $result->removeChild($test_node);
		\assert(false);
	} catch(\Exception $e) {
			\assert($e->getMessage() === "Failed to execute 'removeChild' on 'Node': The node to be removed is not a child of this node.");
	}

	//remove
	$test_case->part("remove");
	$result->endChild()->remove();
	\assert($result->endChild()->getAttribute("id") === "bk102");

	//setName
	$test_node = new XML\StdNode;
	$test_node->setName("test");
	\assert($test_node->nodeName === "TEST");

	$test_case->done();