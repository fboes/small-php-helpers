<?php
# namespace fboes\SmallPhpHelpers;
# use fboes\SmallPhpHelpers\Form\Element;

require_once('Form/Element.php');

/**
 * @class Form
 * Build simple forms
 *
 * Element special attributes:
 * - data-label: Add label to element
 * - data-hint: Add extra hint to element
 * - data-preservekeys: Keep keys for numerical values
 * - data-output: Generate an <output> behind this field and show value
 * - data-pattern: Pattern for client side validation used for non-HTML5-compatible browsers
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
			$this->setDefaultValues($defaultValues);
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
	 * Set default values for form
	 * @param array $defaultValues Set default values for all form fields, e.g. text=test for <input name="test" value="text" />
	 */
	public function setDefaultValues ($defaultValues) {
		$newValues = array();
		foreach ($defaultValues as $key => $value) {
			$newValues[$key] = array('value' => $value);
		}
		return $this->setDefaultElementAttributes($newValues);
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
				throw new \Exception("Attributes need to be given as array");
			}
		}
		foreach ($this->formElements as &$element) {
			if (!empty($element->attributes['name'])) {
				$element->setAttributesByArray($this->getdefaultElementAttributes($element->attributes['name']));
				$element->addDefaultValue();
			}
		}
		return $this;
	}

	/**
	 * Add HTML to wrap around every form field, but not free-form html.
	 * @param string $html with %1$s being the label, %2$s being the actual form field, %3$s being the optional error message, %4$s being an optional hint
	 */
	public function setFieldWrapper ($html = "<div class=\"form-field\">%1\$s%4\$s%2\$s%3\$s</div>\n") {
		if (strpos($html,'%1$s') !== FALSE && strpos($html,'%2$s') !== FALSE) {
			$this->htmlFieldWrapper = $html;
		}
		else {
			throw new \Exception('Wrong format for HTML field wrapper, missing "%1$s" or "%2$s"');
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
			throw new \Exception('Wrong format for HTML label wrapper, missing "%s"');
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
			throw new \Exception('Wrong format for HTML Error wrapper, missing "%s"');
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
	 * Add input field. Some input types will spawn a data-pattern-attribute containing rules to validate this field via JavaScript.
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
	 * There are also some special types (see http://baymard.com/labs/touch-keyboard-types):
	 *
	 * - address
	 * - bic
	 * - creditcard-number
	 * - currency
	 * - iban
	 * - username
	 *
	 * Instead of "step" it is possible to use "decimals". So 'decimals="2"' translates to 'step="0.1"'
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
		if (!empty($element->attributes['decimals'])) {
			switch ((int)$element->attributes['decimals']) {
				case 0:
					$element->attributes['step'] = 1;
					break;
				case 1:
					$element->attributes['step'] = 0.1;
					break;
				case 2:
					$element->attributes['step'] = 0.01;
					break;
				default:
					$element->attributes['step'] = 10 / (pow(10, $decimals + 1));
					break;
			}
			unset($element->attributes['decimals']);
		}
		switch ($element->attributes['type']) {
			case 'color':
				$element->setOnEmpty('data-pattern', '#[A-Fa-f0-9]{6}');
				$element->setOnEmpty('title', _('Expecting web color'));
				$element->setOnEmpty('maxlength', 7);
				break;
			case 'date':
				$element->setOnEmpty('data-pattern', '[\d]{4}-[0-1][\d]-[0-3][\d]');
				$element->setOnEmpty('title', sprintf(_('Expecting date like %s'),'2020-12-31'));
				$element->setOnEmpty('maxlength', 10);
				break;
			case 'datetime':
				$element->setOnEmpty('data-pattern', '[\d]{4}-[0-1][\d]-[0-3][\d]T[0-2]\d:[0-5]\d(:[0-5]\d(\.\d)?)?Z');
				$element->setOnEmpty('title', sprintf(_('Expecting date like %s'),'2020-12-31T23:59Z'));
				$element->setOnEmpty('maxlength', 10 + 2 + 5);
				break;
			case 'datetime-local':
				$element->setOnEmpty('data-pattern', '[\d]{4}-[0-1][\d]-[0-3][\d]T[0-2]\d:[0-5]\d(:[0-5]\d(\.\d)?)?');
				$element->setOnEmpty('title', sprintf(_('Expecting date like %s'),'2020-12-31T23:59'));
				$element->setOnEmpty('maxlength', 10 + 1 + 5);
				break;
			case 'email':
				$element->setOnEmpty('data-pattern', '[^@\s]+@[^@\s]+\.[^@\s]+');
				$element->setOnEmpty('title', _('Expecting valid email address'));
				$element->setOnEmpty('autocapitalize', 'off');
				$element->setOnEmpty('autocorrect', 'off');
				break;
			case 'month':
				$element->setOnEmpty('data-pattern', '[\d]{4}-[0-1][\d]');
				$element->setOnEmpty('title', sprintf(_('Expecting month like %s'),'2020-12'));
				$element->setOnEmpty('maxlength', 7);
				break;
			case 'number':
			case 'range':
				$element->setOnEmpty('data-pattern', '\-?(\d+)?(\.)?\d+([eE]\-?\d+)?');
				$element->setOnEmpty('title', _('Expecting numerical value'));
				break;
			case 'time':
				$element->setOnEmpty('data-pattern', '[0-2][\d]:[0-5][\d](:[0-5]\d(\.\d)?)?');
				$element->setOnEmpty('title', sprintf(_('Expecting time like %s'),'23:59'));
				$element->setOnEmpty('maxlength', 8);
				break;
			case 'url':
				$element->setOnEmpty('data-pattern', 'http(s)?://\S+');
				$element->setOnEmpty('title', _('Expecting valid URL, starting with http'));
				$element->setOnEmpty('autocapitalize', 'off');
				$element->setOnEmpty('autocorrect', 'off');
				break;
			case 'week':
				$element->setOnEmpty('data-pattern', '[\d]{4}-W[0-5][\d]');
				$element->setOnEmpty('title', sprintf(_('Expecting week like %s'),'2020-W52'));
				$element->setOnEmpty('maxlength', 8);
				break;

			// Special cases
			case 'address':
				$element->attributes['type'] = 'text';
				$element->setOnEmpty('autocorrect', 'off');
				break;
			case 'bic':
				$element->attributes['type'] = 'text';
				$element->setOnEmpty('pattern', '[a-zA-Z]{6}[a-zA-Z0-9]{2}([a-zA-Z0-9]{3})?');
				$element->setOnEmpty('maxlength', 11); // 8 or 11 chars are valid
				$element->setOnEmpty('autocorrect', 'off');
				$element->setOnEmpty('autocapitalize', 'off');
				$element->setOnEmpty('title', _('Expecting BIC, starting with six letters'));
				break;
			case 'creditcard-number':
				$element->attributes['type'] = 'text';
				$element->setOnEmpty('pattern', '\d{16}');
				$element->setOnEmpty('maxlength', 16);
				$element->setOnEmpty('title', _('Expecting valid credit card number'));
				#$element->setOnEmpty('novalidate', 'novalidate');
				$element->setOnEmpty('autocorrect', 'off');
				$element->setOnEmpty('title', _('Expecting 16-digit credit card number without any whitespaces'));
				break;
			case 'currency':
				$element->attributes['type'] = 'number';
				$element->setOnEmpty('step', '0.01');
				$element->setOnEmpty('date-pattern', '\d+(\.\d{2})?');
				$element->setOnEmpty('title', _('Expecting amount'));
				$element->setOnEmpty('min', 0);
				break;
			case 'iban':
				$element->attributes['type'] = 'text';
				$element->setOnEmpty('pattern', '[A-Za-z]{2}\d{2}[a-zA-Z0-9]{2,30}');
				$element->setOnEmpty('maxlength', 34); // 4 - 34 characters are valid
				$element->setOnEmpty('autocorrect', 'off');
				$element->setOnEmpty('autocapitalize', 'off');
				$element->setOnEmpty('title', _('Expecting IBAN, starting with two letters'));
				break;
			case 'username':
				$element->attributes['type'] = 'text';
				$element->setOnEmpty('pattern', '\S\S+');
				$element->setOnEmpty('autocapitalize', 'off');
				$element->setOnEmpty('autocorrect', 'off');
				break;
		}
		if (!Form::is_blank($element->attributes['maxlength'])) {
			if (!Form::is_blank($element->attributes['value']) && mb_strlen($element->attributes['value']) > (int)$element->attributes['maxlength']) {
				$element->addError('maxlength',
					sprintf(ngettext(
						'Field data is too long, maximum length is %s character.',
						'Field data is too long, maximum length is %s characters.',
						(int)$element->attributes['maxlength']
					),(int)$element->attributes['maxlength'])
				);
			}
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
					$element->addError('max',sprintf(_('Field value is to big, maximum is %s.'),$element->attributes['max']));
			}
			if (!Form::is_blank($element->attributes['min']) && (float)$element->attributes['value'] < (float)$element->attributes['min']) {
					$element->addError('min',sprintf(_('Field value is to small, minimum is %s.'),$element->attributes['min']));
			}
		}
		if (!Form::is_blank($element->attributes['pattern']) || !Form::is_blank($element->attributes['data-pattern'])) {
			$element->addClass  ('pattern');
		}
		if (!empty($options)) {
			$element->addClass  ('datalist');
			$element->attributes['list'] = $element->attributes['id'].'-datalist';
			$element->setOnEmpty('autocomplete', 'off');
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
				$element->addError('maxlength',
					sprintf(ngettext(
						'Field data is too long, maximum length is %s character.',
						'Field data is too long, maximum length is %s characters.',
						(int)$element->attributes['maxlength']
					),(int)$element->attributes['maxlength'])
				);
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
			throw new \Exception ('No options given');
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
			throw new \Exception ('No options given');
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

	/**
	 * Get an array of all form-elements with their respective pattern-attribute
	 * @return array with NAME => ATTRIBUTE-VALUE
	 */
	public function getNamesForPattern () {
		return $this->getNamesForAttribute('pattern');
	}

	/**
	 * Get an array of all form-elements with their respective required-attribute
	 * @return array with NAME => ATTRIBUTE-VALUE
	 */
	public function getNamesForRequired () {
		return $this->getNamesForAttribute('required');
	}

	/**
	 * Get an array of all form-elements with their respective pattern-attribute
	 * @param  string $attribute attribute name
	 * @return array             with NAME => ATTRIBUTE-VALUE
	 */
	public function getNamesForAttribute ($attribute) {
		$elements = array();
		foreach ($this->formElements as $e) {
			if (!empty($e->attributes['name'])) {
				$name = $e->attributes['name'];
				$elements[$name] = !empty($e->attributes[$attribute]) ? $e->attributes[$attribute] : NULL;
			}
		}
		return $elements;
	}

	/**
	 * Generat an array suitable for optionlists with dates to suggest for a datepicker
	 * @param  string $dateFormat should match the date format used for input type, defaults to 'Y-m-d'
	 * @return array              with date => label
	 */
	public static function getDateOptionslist ($dateFormat = 'Y-m-d') {
		$now = time();
		$thisYear = (int)date('Y');

		$christmas = strtotime($thisYear.'-12-24');
		if ($christmas < $now) {
			$christmas = strtotime(($thisYear+1).'-12-24');
		}

		$easter = easter_date($thisYear);
		if ($easter < $now) {
			$easter = easter_date(($thisYear+1));
		}

		return array(
			date($dateFormat,strtotime('next saturday')) => _('Next saturday'),
			date($dateFormat,strtotime('next sunday')) => _('Next sunday'),
			date($dateFormat,$christmas) => _('Next Christmas Eve'),
			date($dateFormat,strtotime(($thisYear+1).'-01-01')) => _('Next New Year\'s Day'),
			date($dateFormat,$easter) => _('Next Easter'),
		);
	}
}