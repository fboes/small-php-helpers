<?php
/**
 * @class Form
 * Build simple forms
 *
 * Element special attributes:
 * - data-label: Add label to element
 * - data-preservekeys: Keep keys for numerical values
 *
 * @author      Frank Bo"es <info@3960.org>
 * @copyright   Creative Commons Attribution 3.0 Unported (CC BY 3.0)
 */

require_once('Form/Element.class.php');

class Form {
	public $defaultFormData = array();
	public $formElements = array();
	protected $formStart;

	protected $htmlFieldWrapper;
	protected $htmlLabelWrapper;
	protected $htmlLabelRequired;

	const HTML_FORM       = '<form%s>';
	const HTML_INPUT      = '<input%1$s />';
	const HTML_TEXTAREA   = '<textarea%1$s>%2$s</textarea>';
	const HTML_SELECT     = '<select%1$s>%2$s</select>';
	const HTML_CHECKBOXES = '<span%1$s>%2$s</span>';
	const HTML_BUTTON     = '<button%1$s>%2$s</button>';

	const ATTRIBUTE_CONTENT = '_content';

	/**
	 * Initiate form
	 * @param array $defaultFormData Set default values for all form fields, e.g. text=test for <input name="test" value="text" />
	 */
	public function __construct(array $defaultFormData = array()) {
		$this->defaultFormData = $defaultFormData;
		$this->setFieldWrapper();
		$this->setLabelWrapper();
		$this->setLabelRequired();
	}

	/**
	 * Static constructor for chaining
	 * @param  array  $defaultFormData [description]
	 * @return Form       [description]
	 */
	public static function init (array $defaultFormData = array()) {
		return new self($defaultFormData);
	}

	/**
	 * [setFieldWrapper description]
	 * @param string $html [description]
	 */
	public function setFieldWrapper ($html = "<span>%1\$s%2\$s</span>\n") {
		if (strpos($html,'%1$s') !== FALSE && strpos($html,'%2$s') !== FALSE) {
			$this->htmlFieldWrapper = $html;
		}
		else {
			throw new Exception('Wrong format for HTML field wrapper, missing "%1$s" or "%2$s"');
		}
		return $this;
	}

	/**
	 * [setLabelWrapper description]
	 * @param string $html [description]
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
	 * [setLabelWrapper description]
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
	 * Add input field
	 * @param  string $html like '<input name="test" />'
	 * @return Form       [description]
	 */
	public function input ($html, array $options = array()) {
		$element = new FormElement(self::HTML_INPUT, $html, $options);
		$element->throwExceptionOnEmpty('name');
		$element->makeId(!empty($this->formStart->attributes['id']) ? $this->formStart->attributes['id'] : '');
		$element->setOnEmpty('type', 'text');
		$element->addClass  ('form-input');
		$element->addClass  ('form-input-'.$element->attributes['type']);
		$element->setOnEmpty('value', $this->getdefaultFormData($element->attributes['name']));
		if (!empty($options)) {
			$element->addClass  ('form-input-datalist');
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
		$element->makeId(!empty($this->formStart->attributes['id']) ? $this->formStart->attributes['id'] : '');
		if (!empty($element->attributes[self::ATTRIBUTE_CONTENT])) {
			$element->setOnEmpty('value', $element->attributes[self::ATTRIBUTE_CONTENT]);
		}
		else {
			$element->setOnEmpty('value', $this->getdefaultFormData($element->attributes['name']));
		}

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
		$element->makeId(!empty($this->formStart->attributes['id']) ? $this->formStart->attributes['id'] : '');
		if (!empty($element->attributes['multiple'])) {
			$element->attributes['name'] .= '[]';
		}
		$element->setOnEmpty('value', $this->getdefaultFormData($element->attributes['name']));

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
		$element->makeId(!empty($this->formStart->attributes['id']) ? $this->formStart->attributes['id'] : '');
		if (empty($options)) {
			throw new Exception ('No options given');
		}
		$element->setOnEmpty('type', 'checkbox');
		$element->addClass  ('form-checkbox');
		$element->addClass  ('form-checkbox-'.$element->attributes['type']);
		if ($element->attributes['type'] == 'checkbox') {
			$element->attributes['name'] .= '[]';
		}
		$element->setOnEmpty('value', $this->getdefaultFormData($element->attributes['name']));

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
	 * [getdefaultFormData description]
	 * @param  string $name [description]
	 * @return string       [description]
	 */
	public function getdefaultFormData ($name) {
			if (preg_match('#^(.+)(\[\])$#',$name, $nameParts)) {
				return (!self::is_blank($this->defaultFormData[$nameParts[1]])) ? $this->defaultFormData[$nameParts[1]] : NULL;
			}
			else {
				return (!self::is_blank($this->defaultFormData[$name])) ? $this->defaultFormData[$name] : NULL;
			}
	}

	/**
	 * -----------------------------------------------
	 *  TAG CONSTRUCTION HELPERS
	 * -----------------------------------------------
	 */

	/**
	 * Store single element to form
	 * @param  [type] $html              [description]
	 * @param  array  $elementAttributes [description]
	 * @return [type]                    [description]
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
			$return .= $element->returnHtml($this->htmlFieldWrapper, $this->htmlLabelWrapper, $this->htmlLabelRequired);
		}
		return $return;
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
	    return !isset($v) || (is_scalar($v) ? (trim($v) === '') : empty($v));
	}
}