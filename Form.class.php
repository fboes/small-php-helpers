<?php
/**
 * @class Form
 * Build simple forms
 *
 * Element special attributes:
 * - data-label: Add label to element
 * - data-preservekeys: Keep keys for numerical values
 * - default: Set value if no other value is present
 *
 * Other element attributes (see http://www.whatwg.org/specs/web-apps/current-work/multipage/association-of-controls-and-forms.html & http://baymard.com/labs/touch-keyboard-types):
 * - accept
 * - autocapitalize="on|off"
 * - autocomplete="on|off"
 * - autocorrect="on|off"
 * - autofocus="autofocus"
 * - dirname
 * - disabled="disabled"
 * - inputmode
 * - max="\d"
 * - maxlength="\d"
 * - min="\d"
 * - multiple="multiple"
 * - name
 * - novalidate="novalidate"
 * - pattern
 * - placeholder
 * - readonly="readonly"
 * - step="\d"
 *
 * @author      Frank Bo"es <info@3960.org>
 * @copyright   MIT License (MIT)
 */

require_once('Form/Element.class.php');

class Form {
	public $defaultElementAttributes = array();
	public $formElements = array();
	protected $formStart;

	protected $htmlFieldWrapper;
	protected $htmlLabelWrapper;
	protected $htmlLabelRequired;
	protected $htmlErrorWrapper;

	const HTML_FORM                       = '<form%s>';
	const HTML_INPUT                      = '<input%1$s />';
	const HTML_INPUT_OPTION               = '<option%1$s>%2$s</option>';
	const HTML_INPUT_OPTIONS_WRAPPER      = '<datalist%2$s>%1$s</datalist>';
	const HTML_TEXTAREA                   = '<textarea%1$s>%2$s</textarea>';
	const HTML_SELECT                     = '<select%1$s>%2$s</select>';
	const HTML_SELECT_OPTION              = '<option%1$s>%2$s</option>';
	const HTML_SELECT_OPTIONS_WRAPPER     = '%1$s';
	const HTML_CHECKBOXES                 = '%2$s';
	const HTML_CHECKBOXES_OPTION          = "<li><label><input%1\$s /> <span>%2\$s</span></label></li>\n";
	const HTML_CHECKBOXES_OPTIONS_WRAPPER = '<ul class="form-optionlist"%2$s>%1$s</ul>';
	const HTML_BUTTON                     = '<button%1$s>%2$s</button>';

	const ATTRIBUTE_CONTENT      = '_content';

	/**
	 * Initiate form
	 * @param array $defaultValues Set default values for all form fields, e.g. text=test for <input name="test" value="text" />
	 */
	public function __construct(array $defaultValues = array()) {
		if (!empty($defaultValues)) {
			foreach ($defaultValues as $key => $value) {
				if (empty($this->defaultElementAttributes[$key])) {
					$this->defaultElementAttributes[$key] = array();
				}
				$this->defaultElementAttributes[$key]['value'] = $value;
			}
		}
		$this->setFieldWrapper();
		$this->setLabelWrapper();
		$this->setLabelRequired();
		$this->setErrorWrapper();
	}

	/**
	 * Static constructor for chaining
	 * @param  array  $defaultValues [description]
	 * @return Form       [description]
	 */
	public static function init (array $defaultValues = array()) {
		return new static($defaultValues);
	}

	/**
	 * Set attributes for form elements not via HTML, but from a configuration array
	 * @param array $elements with FIELD_NAME = array(FIELD_ATTRIBUTE => FIELD_ATTRIBUTE_VALUE)
	 * @return Form       [description]
	 */
	public function setDefaultElementAttributes (array $elements) {
		foreach ($elements as $fieldname => $attributes) {
			if (is_array($attributes)) {
				if (empty($this->defaultElementAttributes[$fieldname])) {
					$this->defaultElementAttributes[$fieldname] = $attributes;
				}
				else {
					$this->defaultElementAttributes[$fieldname] = array_merge($attributes,$this->defaultElementAttributes[$fieldname]);
				}
			}
			else {
				throw new Exception("Attributes need to be given as array");
			}
		}
		return $this;
	}

	/**
	 * Add HTML to wrap around every form field, but not free-form html.
	 * @param string $html with %1$s being the label, %2$s being the actual form field, %3$s being the optional error message
	 */
	public function setFieldWrapper ($html = "<div class=\"form-field\">%1\$s%2\$s%3\$s</div>\n") {
		if (strpos($html,'%1$s') !== FALSE && strpos($html,'%2$s') !== FALSE) {
			$this->htmlFieldWrapper = $html;
		}
		else {
			throw new Exception('Wrong format for HTML field wrapper, missing "%1$s" or "%2$s"');
		}
		return $this;
	}

	/**
	 * Set HTML to wrap around label text, e.g. "%s:"
	 * @param string $html with %s being the actual label text
	 */
	public function setLabelWrapper ($html = "%s") {
		if (strpos($html,'%s') !== FALSE) {
			$this->htmlLabelWrapper = $html;
		}
		else {
			throw new Exception('Wrong format for HTML label wrapper, missing "%s"');
		}
		return $this;
	}

	/**
	 * Set HTML to wrap around error messages, e.g. "%s:"
	 * @param string $html with %s being the actual label text
	 */
	public function setErrorWrapper ($html = '<span class="invalid">%s</span>') {
		if (strpos($html,'%s') !== FALSE) {
			$this->htmlErrorWrapper = $html;
		}
		else {
			throw new Exception('Wrong format for HTML Error wrapper, missing "%s"');
		}
		return $this;
	}

	/**
	 * Additional HTML to add to label text for form fields which are required
	 * @param string $html [description]
	 */
	public function setLabelRequired ($html = " *") {
		$this->htmlLabelRequired = $html;
		return $this;
	}

	/**
	 * Start form
	 * @param  string $html like '<form>'
	 * @return Form       [description]
	 */
	public function start ($html = '<form>') {
		$element = new FormElement(self::HTML_FORM, $html);
		$element->setOnEmpty('action', $_SERVER['PHP_SELF']);
		$element->setOnEmpty('method', 'post');

		return $this->storeElement($element);
	}

	/**
	 * End form
	 * @param  string $html like '</form>'
	 * @return Form       [description]
	 */
	public function end ($html = '</form>') {
		return $this->html($html);
	}

	/**
	 * Add input field. SOm einput types will spawn a data-pattern-attribute containing rules to validate this field via JavaScript.
	 * Supported input types:
	 * - color
	 * - date
	 * - datetime
	 * - datetime-local
	 * - email
	 * - file
	 * - hidden
	 * - month
	 * - number
	 * - password
	 * - range
	 * - search
	 * - tel
	 * - text
	 * - time
	 * - url
	 * - week
	 *
	 * @param  string $html like '<input name="test" />'
	 * @return Form       [description]
	 */
	public function input ($html, array $options = array()) {
		$element = new FormElement(self::HTML_INPUT, $html, $options);
		$element->throwExceptionOnEmpty('name');
		$element->setAttributesByArray($this->getdefaultElementAttributes($element->attributes['name']));
		$element->addDefaultValue();

		// Manipulate attributes
		$element->makeId(!empty($this->formStart->attributes['id']) ? $this->formStart->attributes['id'] : '');
		$element->setOnEmpty('type', 'text');
		$element->addClass  ('input');
		$element->addClass  ($element->attributes['type']);
		$element->addErrorsOnRequired();
		if (!Form::is_blank($element->attributes['maxlength'])) {
			if (!Form::is_blank($element->attributes['value']) && mb_strlen($element->attributes['value']) > (int)$element->attributes['maxlength']) {
				$element->addError('maxlength',_('Field data is to long.'));
			}
		}
		switch ($element->attributes['type']) {
			case 'color':
				$element->setOnEmpty('data-pattern', '#[A-Fa-f0-9]{6}');
				$element->setOnEmpty('title', _('Expecting web color'));
				break;
			case 'date':
				$element->setOnEmpty('data-pattern', '[\d]{4}-[0-1][\d]-[0-3][\d]');
				$element->setOnEmpty('title', _('Expecting date like 2020-12-31'));
				break;
			case 'time':
				$element->setOnEmpty('data-pattern', '[0-2][\d]:[0-5][\d](:[0-5]\d(\.\d)?)?');
				$element->setOnEmpty('title', _('Expecting time like 23:59'));
				break;
			case 'datetime':
				$element->setOnEmpty('data-pattern', '[\d]{4}-[0-1][\d]-[0-3][\d]T[0-2]\d:[0-5]\d(:[0-5]\d(\.\d)?)?Z');
				$element->setOnEmpty('title', _('Expecting date like 2020-12-31T23:59Z'));
				break;
			case 'week':
				$element->setOnEmpty('data-pattern', '[\d]{4}-W[0-5][\d]');
				$element->setOnEmpty('title', _('Expecting week like 2020-W52'));
				break;
			case 'month':
				$element->setOnEmpty('data-pattern', '[\d]{4}-[0-1][\d]');
				$element->setOnEmpty('title', _('Expecting month like 2020-12'));
				break;
			case 'datetime-local':
				$element->setOnEmpty('data-pattern', '[\d]{4}-[0-1][\d]-[0-3][\d]T[0-2]\d:[0-5]\d(:[0-5]\d(\.\d)?)?');
				$element->setOnEmpty('title', _('Expecting date like 2020-12-31T23:59'));
				break;
			case 'email':
				$element->setOnEmpty('data-pattern', '\S+@\S+');
				$element->setOnEmpty('title', _('Expecting valid email address'));
				break;
			case 'number':
			case 'range':
				$element->setOnEmpty('data-pattern', '\-?(\d+)?(\.)?\d+([eE]\-?\d+)?');
				$element->setOnEmpty('title', _('Expecting numerical value'));
				break;
			case 'url':
				$element->setOnEmpty('data-pattern', 'http(s)?://\S+');
				$element->setOnEmpty('title', _('Expecting valid URL, starting with http'));
				break;
		}
		if (!Form::is_blank($element->attributes['value'])) {
			if (!Form::is_blank($element->attributes['pattern']) || !Form::is_blank($element->attributes['data-pattern'])) {
				$pattern = !Form::is_blank($element->attributes['pattern']) ? $element->attributes['pattern'] : $element->attributes['data-pattern'];
				$pattern = '#^'.str_replace('#','\#',$pattern).'$#';
				if (!preg_match($pattern, $element->attributes['value'])) {
					$element->addError('pattern',_('Field value does not match expectations for this field.'));
				}
			}
			if (!Form::is_blank($element->attributes['max']) && (float)$element->attributes['value'] > (float)$element->attributes['max']) {
					$element->addError('max',_('Field value is to big.'));
			}
			if (!Form::is_blank($element->attributes['min']) && (float)$element->attributes['value'] < (float)$element->attributes['min']) {
					$element->addError('min',_('Field value is to small.'));
			}
		}
		if (!Form::is_blank($element->attributes['pattern']) || !Form::is_blank($element->attributes['data-pattern'])) {
			$element->addClass  ('pattern');
		}
		if (!empty($options)) {
			$element->addClass  ('datalist');
			$element->attributes['list'] = $element->attributes['id'].'-datalist';
		}

		switch ($element->attributes['type']) {
			case 'file':
				$this->formStart->setOnEmpty('enctype', 'multipart/form-data');
				break;
		}

		return $this->storeElement($element);
	}

	/**
	 * Add textarea field
	 * @param  string $html like '<textarea name="test" />'
	 * @return Form       [description]
	 */
	public function textarea ($html) {
		$element = new FormElement(self::HTML_TEXTAREA, $html);
		$element->throwExceptionOnEmpty('name');
		$element->setAttributesByArray($this->getdefaultElementAttributes($element->attributes['name']));
		$element->addDefaultValue((!empty($element->attributes[self::ATTRIBUTE_CONTENT]))
			? $element->attributes[self::ATTRIBUTE_CONTENT]
			: NULL
		);

		// Manipulate attributes
		$element->makeId(!empty($this->formStart->attributes['id']) ? $this->formStart->attributes['id'] : '');
		if (!Form::is_blank($element->attributes['maxlength'])) {
			if (!Form::is_blank($element->attributes['value']) && mb_strlen($element->attributes['value']) > (int)$element->attributes['maxlength']) {
				$element->addError('maxlength',_('Field data is to long.'));
			}
		}
    	$element->addErrorsOnRequired();

		return $this->storeElement($element);
	}

	/**
	 * Add select field
	 * @param  string $html like '<textarea name="test" />'
	 * @return Form       [description]
	 */
	public function select ($html, array $options) {
		$element = new FormElement(self::HTML_SELECT, $html, $options);
		$element->throwExceptionOnEmpty('name');
		if (empty($options)) {
			throw new Exception ('No options given');
		}
		if (!empty($element->attributes['multiple'])) {
			$element->attributes['name'] .= '[]';
		}
		$element->setAttributesByArray($this->getdefaultElementAttributes($element->attributes['name']));
		$element->addDefaultValue();

		// Manipulate attributes
		$element->makeId(!empty($this->formStart->attributes['id']) ? $this->formStart->attributes['id'] : '');
		$element->addErrorsOnRequired();

		return $this->storeElement($element);
	}

	/**
	 * Add checkboxes / radiobuttons
	 * @param  string $html like '<textarea name="test" />'
	 * @return Form       [description]
	 */
	public function checkbox ($html, array $options) {
		$element = new FormElement(self::HTML_CHECKBOXES, $html, $options);
		$element->throwExceptionOnEmpty('name');
		if (empty($options)) {
			throw new Exception ('No options given');
		}
		$element->setOnEmpty('type', 'checkbox');
		if ($element->attributes['type'] == 'checkbox') {
			$element->attributes['name'] .= '[]';
		}
		$element->setAttributesByArray($this->getdefaultElementAttributes($element->attributes['name']));
		$element->addDefaultValue();

		// Manipulate attributes
		$element->makeId(!empty($this->formStart->attributes['id']) ? $this->formStart->attributes['id'] : '');
		$element->addClass  ('checkbox');
		$element->addClass  ('checkbox-'.$element->attributes['type']);
		$element->addErrorsOnRequired();
		return $this->storeElement($element);
	}

	/**
	 * Add button
	 * @param  string $html like '<input name="test" />'
	 * @return Form       [description]
	 */
	public function button ($html) {
		$element = new FormElement(self::HTML_BUTTON, $html);
		$element->throwExceptionOnEmpty(self::ATTRIBUTE_CONTENT);
		$element->setOnEmpty('type', 'submit');

		return $this->storeElement($element);
	}

	/**
	 * Use free form html
	 * @param  string $html like '<form>'
	 * @return Form       [description]
	 */
	public function html ($html) {
		return $this->storeElement(new FormElement ($html));
	}

	/**
	 * Get default attribute array as given in setDefaultElementAttributes for the form field with the corresponding name.
	 * @param  string $name [description]
	 * @return array       [description]
	 */
	public function getdefaultElementAttributes ($name) {
		if (preg_match('#^(.+)(\[\])$#',$name, $nameParts)) {
			return (!self::is_blank($this->defaultElementAttributes[$nameParts[1]])) ? $this->defaultElementAttributes[$nameParts[1]] : array();
		}
		else {
			return (!self::is_blank($this->defaultElementAttributes[$name])) ? $this->defaultElementAttributes[$name] : array();
		}
	}

	/**
	 * Get default attribute value as given in constructor for the form field with the corresponding name.
	 * @param  string $name [description]
	 * @return string       [description]
	 */
	public function getdefaultElementAttribute ($name, $key = 'value') {
		$attributes = $this->getdefaultElementAttributes($name);
		return (!self::is_blank($attributes[$key]))
			? $attributes[$key]
			: NULL
		;
	}

	/**
	 * Store single element to form
	 * @param  FormElement $element
   * @return Form       [description]
	 */
	protected function storeElement (FormElement $element) {
		$this->formElements[] = $element;

		// write form start for reference
		if ($element->html == self::HTML_FORM) {
			$this->formStart = &$this->formElements[key($this->formElements)];
		}
		return $this;
	}

	/**
	 * Echo HTML for all form fields present. Uses returnHtml
	 * @return Form       [description]
	 */
	public function echoHtml () {
		echo ($this->returnHtml());
		return $this;
	}

	/**
	 * Return HTML for all form fields present. Uses returnHtmlForElement
	 * @return string HTML
	 */
	public function returnHTML () {
		$return = '';
		foreach ($this->formElements as $id => $element) {
			$return .= $element->returnHtml($this->htmlFieldWrapper, $this->htmlLabelWrapper, $this->htmlLabelRequired, $this->htmlErrorWrapper);
		}
		return $return;
	}

	/**
	 * Check if any form element has errors
	 * @return int number of form elements with errors, which means 0 / FALSE means no errors.
	 */
	public function hasErrors () {
		$errors = 0;
		foreach ($this->formElements as $element) {
			if (!empty($element->errors)) {
				$errors += count($element->errors);
			}
		}
		return $errors;
	}

	/**
	 * Checks if a scalar value is FALSE, without content or only full of
	 * whitespaces.
	 * For non-scalar values will evaluate if value is empty().
	 *
	 * @param   mixed	$v	to test
	 * @return	bool	if $v is blank
	 */
	public static function is_blank (&$v) {
		if (function_exists('is_blank')) {
			return is_blank($v);
		}
		else {
			return !isset($v) || (is_scalar($v) ? (trim($v) === '') : empty($v));
		}
	}

	/**
	* Convert string to proper IID / Name-Attribute
	* @param  string $str [description]
	* @return string      [description]
	*/
	public static function make_id ($str)	{
		if (function_exists('make_id')) {
			return make_id($str);
		}
		else {
			if (!preg_match('#^[A-Za-z][A-Za-z0-9\-_\:\.]*$#', $str)) {
				$str = preg_replace(
					array('#^[^A-Za-z]#','#[^A-Za-z0-9\-_\:\.]#', '#(_)_+#'),
					array('id_$0',       '_',                     '$1'),
					$str
				);
			}
			return $str;
		}
	}
}