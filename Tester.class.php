<?php
/**
 * @class Tester
 * Mini-Unit-Test (in case PhpUnit ist not available)
 * Extend this class for doing the real test. Methods with "test" prefixed get tested. Methods with 'data' prefixed are used as data providers for corresponding "test"-methods. "data"-methods MUST return an array of arrays.
 * This class intentionally has direct HTML output.
 *
 * @author      Frank Bo"es <info@3960.org>
 * @copyright   MIT License (MIT)
 */

class Tester {
	protected $assertionsDone = 0;
	protected $assertionsSuccess = 0;
	protected $globalAssertionsDone = 0;
	protected $globalAssertionsSuccess = 0;
	protected $testStart = 0;
	protected $testEnd;
	protected $methodsTests = array();
	protected $methodsProviders = array();
	protected $cli = FALSE;
	protected $colors = TRUE;

	const PREFIX_TEST = 'test';
	const PREFIX_PROVIDER = 'data';
	const PARAM_FORCE_CLI = 'cli';

	public function __construct () {
		$this->cli    = (php_sapi_name() === 'cli'    ||  isset($_GET[self::PARAM_FORCE_CLI]));
		$this->colors = (php_uname('s') !== 'Windows' && !isset($_GET[self::PARAM_FORCE_CLI]));
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

		if ($this->cli) {
			header('Content-type: text/plain');
			echo("\n".$this->coloredString($title."\n================================================", 1)."\n");
		}
		else {
			echo('<!DOCTYPE html>'."\n");
			echo('<html>');
			echo('<head>');
			echo('<title>'.htmlspecialchars($title).'</title>');
			echo(
				'<style>'
				.'body {font:80% sans-serif;}'
				.'h1,h2 {margin-bottom:0.5em;}'
				.'table {width:100%;} th,td {border-bottom:1px dotted #ddd;font-weight:normal;} th {text-align:left;padding-right:1em;}'
				.'.success {color:green;font-weight:bold;text-align:right;} .fail {color:maroon;font-weight:bold;text-align:right;} .result {text-align:right;color:#999;}'
				.'#test-summary{border-top:2px solid #aaa; margin-top:2em;padding-top:1em;}'
				.'</style>')
			;
			echo('</head>');
			echo('<body>'."\n");
			echo('<h1>'.htmlspecialchars($title).'</h1>'."\n");
		}

		// get all tests and providers
		$methods = get_class_methods($this);
		$this->methodsProviders = array();
		$this->methodsTests = array();
		foreach ($methods as $m) {
			if (strpos($m, self::PREFIX_TEST) === 0) {
				$this->methodsTests[] = $m;
			}
			elseif (strpos($m, self::PREFIX_PROVIDER) === 0) {
				$this->methodsProviders[] = $m;
			}
		}

		// do all tests
		foreach ($this->methodsTests as $m) {
			$data = array(array());
			$possibleProvider = str_replace(self::PREFIX_TEST, self::PREFIX_PROVIDER, $m);
			if (method_exists($this, $possibleProvider)) {
				$data = $this->$possibleProvider();
				if (!is_array($data)) {
					throw new Exception('Wrong return value in '.$possibleProvider);
				}
			}

			$id = 0;
			foreach ($data as $run => $dataSet) {
				$this->assertionsDone = 0;
				$this->assertionsSuccess = 0;
				if ($this->cli) {
					echo("\n".$this->coloredString($m.': '.($run)."\n------------------------------------------------", 1)."\n\n");
				}
				else {
					echo('<div class="test">'."\n");
					echo('  <h2 id="'.htmlspecialchars($m.'-'.$id).'">'.htmlspecialchars($m.': '.($run)).'</h2>'."\n");
					echo('  <table class="assertions">'."\n");
				}
				$this->testStart = microtime(TRUE);

				if (!empty($dataSet)) {
					if (!is_array($dataSet)) {
						throw new Exception('Wrong return value in '.$possibleProvider);
					}
					call_user_func_array(array($this, $m), $dataSet);
				}
				else {
					$this->$m();
				}
				$this->testEnd = microtime(TRUE);
				if ($this->cli) {
					echo("\n".'Success / tests: '.(int)$this->assertionsSuccess.'/'.(int)$this->assertionsDone.'; duration: '.round($this->testEnd - $this->testStart).' ms'."\n");
				}
				else {
					echo('  </table>'."\n");
					echo('  <p class="result">Success / tests: '.(int)$this->assertionsSuccess.'/'.(int)$this->assertionsDone.'; duration: '.round($this->testEnd - $this->testStart).' ms</p>'."\n\n");
					echo('</div>'."\n");
				}

				$this->globalAssertionsSuccess += $this->assertionsSuccess;
				$this->globalAssertionsDone    += $this->assertionsDone;
				$id ++;
			}
		}

		if ($this->cli) {
			echo("\n".$this->coloredString("Summary\n------------------------------------------------", 1)."\n\n");
		}
		else {
			echo('  <h2 id="test-summary">Summary</h2>');
			echo('  <table class="assertions">'."\n");
		}
		$this->assertTrue($this->globalAssertionsSuccess == $this->globalAssertionsDone, 'Expecting all tests to succeed');
		if ($this->cli) {
			echo("\n".'Sum success / tests: '.(int)$this->globalAssertionsSuccess.'/'.(int)$this->globalAssertionsDone."\n");
		}
		else {
			echo('  </table>'."\n");
			echo('  <p class="result">Sum success / tests: '.(int)$this->globalAssertionsSuccess.'/'.(int)$this->globalAssertionsDone.'</p>'."\n");

			echo('</body>');
			echo('</html>');
		}
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
			throw new Exception('Malformed test expression used in '.__METHOD__);
		}
		elseif (!is_string($value)) {
			return $this->assertTrue(FALSE, 'Expecting %s to be a string', $this->literalize($value));
		}
		return $this->assertTrue((bool)preg_match($regExp, $value), sprintf($message, $this->literalize($value), $this->literalize($regExp)));
	}

	/**
	 * [assertFunctionExists description]
	 * @param  string $functionName       [description]
	 * @param  string $value       [description]
	 * @param  string $message [description]
	 * @return bool          [description]
	 */
	public function assertFunctionExists ($functionName, $message = 'Expecting function %s() to exist') {
		if (!is_string($functionName)) {
			throw new Exception('Malformed function name used in '.__METHOD__);
		}
		return $this->assertTrue(function_exists($functionName), sprintf($message, $functionName));
	}

	/**
	 * [assertFunctionExists description]
	 * @param  string $attributeName       [description]
	 * @param  string $className [description]
	 * @param  string $message [description]
	 * @return bool          [description]
	 */
	public function assertMethodExists ($methodName, $className, $message = 'Expecting class %s to have method %s') {
		if (!is_string($methodName)) {
			throw new Exception('Malformed method name used in '.__METHOD__);
		}
		if (!is_string($className)) {
			throw new Exception('Malformed class name used in '.__METHOD__);
		}
		if (!class_exists($className)) {
			return $this->assertTrue(FALSE, sprintf('Expecting %s to be a classname', $this->literalize($className)));
		}
		else {
			return $this->assertTrue(method_exists($className, $methodName), sprintf($message,$this->literalize($className), $this->literalize($methodName)));
		}
	}

	/**
	 * [assertFunctionExists description]
	 * @param  string $attributeName       [description]
	 * @param  string $className [description]
	 * @param  string $message [description]
	 * @return bool          [description]
	 */
	public function assertClassHasAttribute ($attributeName, $className, $message = 'Expecting class %s to have attribute %s') {
		if (!is_string($attributeName)) {
			throw new Exception('Malformed attribute name used in '.__METHOD__);
		}
		if (!is_string($className)) {
			throw new Exception('Malformed class name used in '.__METHOD__);
		}
		if (!class_exists($className)) {
			return $this->assertTrue(FALSE, sprintf('Expecting %s to be a classname', $this->literalize($className)));
		}
		else {
			return $this->assertTrue(property_exists($className, $attributeName), sprintf($message,$this->literalize($className), $this->literalize($attributeName)));
		}
	}

	/**
	 * [assertFunctionExists description]
	 * @param  string $xml       [description]
	 * @param  string $message [description]
	 * @return bool          [description]
	 */
	public function assertValidXml ($xml, $xsdUri = NULL, $message = 'Expecting string to be valid XML') {
		if (!is_string($xml)) {
			throw new Exception('XML is not a string in '.__METHOD__);
		}

		libxml_use_internal_errors(TRUE);
		$tempDom = new DOMDocument();
		$success = $tempDom->loadXML($xml);

		if ($success && !empty($xsdUri)) {
			if (!is_string($xsdUri)) {
				throw new Exception('XSD-URI is not a string in '.__METHOD__);
			}
			$success = $tempDom->schemaValidate($xsdUri);
			return $this->assertTrue(FALSE, sprintf('Expecting XML to validate against XSD %s', $this->literalize($xsdUri)));

		}

		$errors = libxml_get_errors();
		libxml_clear_errors();
		$success = $success && empty($errors);

		if (!empty($errors)) {
			$this->outputLine($errors);
		}

		return $this->assertTrue($success, sprintf($message,$this->literalize($xml)));
	}

	/**
	 * [assertFunctionExists description]
	 * @param  string $html       [description]
	 * @param  string $message [description]
	 * @return bool          [description]
	 */
	public function assertValidHtml ($html, $message = 'Expecting string to be valid HTML (snippet)') {
		if (!is_string($html)) {
			throw new Exception('HTML is not a string in '.__METHOD__);
		}

		libxml_use_internal_errors(TRUE);
		$tempDom = new DOMDocument();
		$success = $tempDom->loadHTML($html);
		$errors = libxml_get_errors();
		libxml_clear_errors();
		$success = $success && empty($errors);

		if (!empty($errors)) {
			$this->outputLine($errors);
		}

		return $this->assertTrue($success, sprintf($message,$this->literalize($html)));
	}

	/**
	 * [assertTrue description]
	 * @param  bool $success [description]
	 * @param  string $message [description]
	 * @return bool          [description]
	 */
	public function assertTrue ($condition, $message = 'Expecting %s to be TRUE') {
		$message = sprintf($message, $this->literalize($condition));
		$condition = is_bool($condition) && $condition;

		$this->assertionsDone ++;
		if ($this->cli) {
			echo('* '. ($condition
				? $this->coloredString("SUCCESS",32)
				: $this->coloredString("FAIL",31)
			).': '.$message."\n");
		}
		else {
			echo('    <tr>');
			echo('<th>'.htmlspecialchars($message).'</th>'. ($condition
				? '<td class="success">Success</td>'
				: '<td class="fail">Fail</td>'
			));
			echo('</tr>'."\n");
		}
		if ($condition) {
			$this->assertionsSuccess ++;
		}
		return $condition;
	}

	/**
	 * [outputLine description]
	 * @param  mixed $string [description]
	 * @return bool TRUE
	 */
	public function outputLine ($mixed) {
		if ($this->cli) {
			print_r($mixed);
			echo("\n");
		}
		else {
			echo('    <tr>');
			echo('<td colspan="2"><pre>'.htmlspecialchars(print_r($mixed,1)).'</pre></td>');
			echo('</tr>'."\n");
		}
		return TRUE;
	}

	/**
	 * Convert given variable to string expression describing it
	 * @param  mixed $mixed [description]
	 * @return string        [description]
	 */
	protected function literalize ($mixed) {
		if (is_string($mixed)) {
			return "'".$mixed."'";
		}
		elseif (is_bool($mixed)) {
			return $mixed ? 'TRUE' : 'FALSE';
		}
		elseif (is_scalar($mixed)) {
			return $mixed;
		}
		#elseif (is_array($mixed)) {

		#}
		return preg_replace('#\s+#',' ',print_r($mixed,1));
	}

	protected function coloredString ($string, $code = 0) {
		return ($this->colors)
			? "\033[".$code."m".$string."\033[0m"
			: $string
		;
	}
}