<?php
/**
 * @class Tester
 * Mini-Unit-Test (in case PhpUnit ist not available)
 * Extend this class for doing the real test. Methods with "test" prefixed get tested. Methods with 'data' prefixed are used as data providers for corresponding "test"-methods. "data"-methods MUST return an array of arrays.
 * This class intentionally has direct HTML output.
 *
 * @author      Frank Bo"es <info@3960.org>
 * @copyright   Creative Commons Attribution 3.0 Unported (CC BY 3.0)
 */

class Tester {
	protected $testsDone = 0;
	protected $testsSuccess = 0;
	protected $testStart = 0;
	protected $testEnd;
	protected $methodsTests = array();
	protected $methodsProviders = array();

	const PREFIX_TEST = 'test';
	const PREFIX_PROVIDER = 'data';

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
			.'table {width:100%;} th,td {border-bottom:1px dotted #ddd;} th {text-align:left;padding-right:1em;} td {text-align:right;}'
			.'.success {color:green;font-weight:bold;} .fail {color:maroon;font-weight:bold;} .result {text-align:right;color:#999;}'
			.'</style>')
		;
		echo('</head>');
		echo('<body>'."\n");
		echo('<h1>'.htmlspecialchars($title).'</h1>'."\n");

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

			foreach ($data as $run => $dataSet) {
				$this->testsDone = 0;
				$this->testsSuccess = 0;
				echo('<div class="test">'."\n");
				echo('  <h2 id="'.htmlspecialchars($m.'-'.$run).'">'.htmlspecialchars($m.': '.($run)).'</h2>'."\n");
				echo('  <table class="assertions">'."\n");
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
				echo('  </table>'."\n");
				echo('  <p class="result">Success / tests: '.(int)$this->testsSuccess.'/'.(int)$this->testsDone.'; duration: '.round($this->testEnd - $this->testStart).' ms</p>'."\n");
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
	public function assertClassHasAttribute($attributeName, $className, $message = 'Expecting class %s to have attribute %s') {
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
			$classVariables = array_keys(get_class_vars($className));
			return $this->assertTrue(in_array($attributeName, $classVariables), sprintf($message,$this->literalize($className), $this->literalize($attributeName)));
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
		$success = $success && empty($errors);

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

		$this->testsDone ++;
		echo('    <tr>');
		echo('<th>'.htmlspecialchars($message).'</th>'. ($condition
			? '<td class="success">Success</td>'
			: '<td class="fail">Fail</td>'
		));
		echo('</tr>'."\n");
		if ($condition) {
			$this->testsSuccess ++;
		}
		return $condition;
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
}