#!/usr/bin/php -d display_errors=1
<?php
	require_once(__DIR__."/../load.php");
	$test_case = new \ArturasKaukenas\SimpleTest\TestCase(2, "Huge XML, NODE_NAME, clean after parse.");

	require_once(__DIR__."/src/book.php");

	use \ArturasKaukenas\DOM;

	$folder = "./data/big_xml/";

	//https://stackoverflow.com/questions/15188033/human-readable-file-size
	function byteConvert($bytes) {
		if ($bytes == 0)
			return "0.00 B";

		$s = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
		$e = floor(log($bytes, 1024));

		return round($bytes/pow(1024, $e), 2).$s[$e];
	}

	class test4_BOOK extends BOOK {
		const NODE_NAME = "BOOK";

		public $cleanOnFinalize = true;

		public function __construct() {
			parent::__construct();
		}
	}

	$gate = new \stdClass;
	$gate->qty = 0;

	$parser = new DOM\XML\Parser();
	$parser
		->registerNode(new \ReflectionClass("test4_BOOK"))
		->onFinalizeNode(
			"BOOK",
			function(DOM\INode $node) use ($gate) {
				$gate->qty++;
			}
		);
	$parser->prepare();

	$total_bytes = 0;
	$chunk_len = 4096;
	$end = 10 * 1024 * 1024 * 1024;

	$starttime = \microtime(true);
	
	$handle = \fopen($folder."start.xml", "r");
	while (!\feof($handle)) {
		$buffer = \fgets($handle, $chunk_len);
		$total_bytes = $total_bytes + $chunk_len;
		$parser->getCallBack()($buffer);
	}
	\fclose($handle);
	
	$prg = 0;

	while ($total_bytes < $end) {
		$handle = \fopen($folder."data.xml", "r");
		$cb = $parser->getCallBack();
		while (!\feof($handle)) {
			$buffer = \fgets($handle, $chunk_len);
			$total_bytes = $total_bytes + $chunk_len;
			$cb($buffer);
		}
		\fclose($handle);

		$prg++;
		if ($prg > 100) {
			echo "Done: ".byteConvert($total_bytes)."\n";
			echo "Total memory usage: ".byteConvert(\memory_get_usage());
			echo "\n\n";
			$prg = 0;
		}
	}

	$handle = \fopen($folder."end.xml", "r");
	while (!\feof($handle)) {
		$buffer = \fgets($handle, $chunk_len);
		$total_bytes = $total_bytes + $chunk_len;
		$parser->getCallBack()($buffer);
	}
	\fclose($handle);
	$parser->finalize();

	$test_case->echo("Total books: ".(string) $gate->qty);
	$test_case->echo("File size:".byteConvert($total_bytes));
	$test_case->echo("Time:". (string) ((\microtime(true) - $starttime))." sec");
	$test_case->echo("Peak memory usage: ".byteConvert(\memory_get_peak_usage()));

	$test_case->done();