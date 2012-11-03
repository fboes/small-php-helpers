<?php
/**
 * @class Tester
 * Mini-Unit-Test (in case PhpUnit ist not available)
 * Extend this class for doing the real test. Methods with "test" prefixed get tested
 *
 * @author      Frank Bo"es <info@3960.org>
 * @copyright   Creative Commons Attribution 3.0 Unported (CC BY 3.0)
 */

class Tester {
	protected $testsDone = 0;
	protected $testsSuccess = 0;
	protected $testStart = 0;
	protected $testEnd;

	public function __construct () {
	}

	/**
	 * Will invoke this class and call doTests()
	 */
	public static function doTest () {
		$test = new static();
		$test->doTests();
	}

	/**
	 * Perform all tests, calling all object methods prefixed with "test". Will output a test protocol in HTML
	 */
	public function doTests () {
		$title = get_class($this);

		echo('<!DOCTYPE html>'."\n");
		echo('<html>');
		echo('<head>');
		echo('<title>'.htmlspecialchars($title).'</title>');
		echo(
			'<style>'
			.'body {font:80% sans-serif;}'
			.'h1,h2 {margin-bottom:0.5em;}'
			.'dl {overflow:hidden;} dt,dd {float:left;border-bottom:1px dotted #ddd;} dt {clear:left;min-width:18em;display:inline-block;padding-right:0.5em;} dd {margin-left:0;min-width:5em;text-align:right;}'
			.'.success {color:green;font-weight:bold;} .fail {color:maroon;font-weight:bold;}'
			.'</style>')
		;
		echo('</head>');
		echo('<body>'."\n");
		echo('<h1>'.htmlspecialchars($title).'</h1>'."\n");

		$methods = get_class_methods($this);
		foreach ($methods as $m) {
			if (strpos($m, 'test') === 0) {
				$this->testsDone = 0;
				$this->testsSuccess = 0;
				echo('<div class="test">'."\n");
				echo('  <h2 id="'.htmlspecialchars($m).'">'.htmlspecialchars($m).'</h2>'."\n");
				echo('  <dl class="assertions">'."\n");
				$this->testStart = microtime(TRUE);
				$this->$m();
				$this->testEnd = microtime(TRUE);
				echo('  </dl>'."\n");
				echo('  <p>Success / tests: '.(int)$this->testsSuccess.'/'.(int)$this->testsDone.'; duration: '.round($this->testEnd - $this->testStart).' ms</p>'."\n");
				echo('</div>'."\n");
			}
		}

		echo('</body>');
		echo('</html>');
	}

	/**
	 * [assertEquals description]
	 * @param  mixed $a       [description]
	 * @param  mixed $b       [description]
	 * @param  string $message [description]
	 * @return bool          [description]
	 */
	public function assertEquals ($a, $b, $message = 'Expecting %s to match %s') {
		return $this->assertTrue($a === $b, sprintf($message, $this->literalize($a), $this->literalize($b)));
	}

	/**
	 * [assertNotEquals description]
	 * @param  mixed $a       [description]
	 * @param  mixed $b       [description]
	 * @param  string $message [description]
	 * @return bool          [description]
	 */
	public function assertNotEquals ($a, $b, $message = 'Expecting %s not to match %s') {
		return $this->assertTrue($a !== $b, sprintf($message, $this->literalize($a), $this->literalize($b)));
	}

	/**
	 * [assertRegExp description]
	 * @param  string $regExp       [description]
	 * @param  string $value       [description]
	 * @param  string $message [description]
	 * @return bool          [description]
	 */
	public function assertRegExp ($regExp, $value, $message = 'Expecting %s to match regular expression %s') {
		if (!is_string($regExp)) {
			throw new Exception('Malformed test expression used in assertRegExp');
		}
		elseif (!is_string($value)) {
			return $this->assertTrue(FALSE, 'Value given to assertRegExp is not a string');
		}
		return $this->assertTrue(preg_match($regExp, $value), sprintf($message, $this->literalize($value), $this->literalize($regExp)));
	}

	/**
	 * [assertTrue description]
	 * @param  bool $success [description]
	 * @param  string $message [description]
	 * @return bool          [description]
	 */
	public function assertTrue ($success, $message = 'Expecting %s to be TRUE') {
		$message = sprintf($message, $this->literalize($success));

		$this->testsDone ++;
		echo('    <dt>'.htmlspecialchars($message).'</dt>'. ($success
			? '<dd class="success">Success</dd>'
			: '<dd class="fail">Fail</dd>'
		)."\n");
		if ($success) {
			$this->testsSuccess ++;
		}
		return $success;
	}

	/**
	 * Convert given variable to string expression describing it
	 * @param  mixed $mixed [description]
	 * @return string        [description]
	 */
	protected function literalize ($mixed) {
		if (is_scalar($mixed)) {
			return $mixed;
		}
		elseif (is_bool($mixed)) {
			return $mixed ? 'TRUE' : 'FALSE';
		}
		#elseif (is_array($mixed)) {

		#}
		return preg_replace('#\s+#',' ',print_r($mixed,1));
	}
}