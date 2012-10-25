<?php
/**
 * @class Form
 * Build simple forms
 *
 * Special attributes:
 * - data-label: Add label to element
 * - data-preservekeys: Keep keys for numerical values
 *
 * @author      Frank Bo"es <info@3960.org>
 * @copyright   Creative Commons Attribution 3.0 Unported (CC BY 3.0)
 */
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
		$attributes = $this->parseTag($html);
		$this->setOnEmpty($attributes, 'action', $_SERVER['PHP_SELF']);
		$this->setOnEmpty($attributes, 'method', 'post');

		return $this->storeElement(self::HTML_FORM, $attributes);
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
		$attributes = $this->parseTag($html);
		$this->throwExceptionOnEmpty($attributes, 'name');
		$this->makeId($attributes);
		$this->setOnEmpty($attributes, 'type', 'text');
		$this->addClass  ($attributes, 'form-input');
		$this->addClass  ($attributes, 'form-input-'.$attributes['type']);
		$this->setOnEmpty($attributes, 'value', $this->getdefaultFormData($attributes['name']));
		if (!empty($options)) {
			$this->addClass  ($attributes, 'form-input-datalist');
			$attributes['list'] = $attributes['id'].'-datalist';
		}

		switch ($attributes['type']) {
			case 'file':
					$this->setOnEmpty($this->formStart->attributes, 'enctype', 'multipart/form-data');
				break;
		}

		return $this->storeElement(self::HTML_INPUT, $attributes, $options);
	}

	/**
	 * Add textarea field
	 * @param  string $html like '<textarea name="test" />'
	 * @return Form       [description]
	 */
	public function textarea ($html) {
		$attributes = $this->parseTag($html);
		$this->throwExceptionOnEmpty($attributes, 'name');
		$this->makeId($attributes);
		if (!empty($attributes[self::ATTRIBUTE_CONTENT])) {
			$this->setOnEmpty($attributes, 'value', $attributes[self::ATTRIBUTE_CONTENT]);
		}
		else {
			$this->setOnEmpty($attributes, 'value', $this->getdefaultFormData($attributes['name']));
		}


		return $this->storeElement(self::HTML_TEXTAREA, $attributes);
	}

	/**
	 * Add select field
	 * @param  string $html like '<textarea name="test" />'
	 * @return Form       [description]
	 */
	public function select ($html, array $options) {
		$attributes = $this->parseTag($html);
		$this->throwExceptionOnEmpty($attributes, 'name');
		if (empty($options)) {
			throw new Exception ('No options given');
		}
		$this->makeId($attributes);
		if (!empty($attributes['multiple'])) {
			$attributes['name'] .= '[]';
		}
		$this->setOnEmpty($attributes, 'value', $this->getdefaultFormData($attributes['name']));

		return $this->storeElement(self::HTML_SELECT, $attributes, $options);
	}

	/**
	 * Add checkboxes / radiobuttons
	 * @param  string $html like '<textarea name="test" />'
	 * @return Form       [description]
	 */
	public function checkbox ($html, array $options) {
		$attributes = $this->parseTag($html);
		$this->throwExceptionOnEmpty($attributes, 'name');
		$this->makeId($attributes);
		if (empty($options)) {
			throw new Exception ('No options given');
		}
		$this->setOnEmpty($attributes, 'type', 'checkbox');
		$this->addClass  ($attributes, 'form-checkbox');
		$this->addClass  ($attributes, 'form-checkbox-'.$attributes['type']);
		if ($attributes['type'] == 'checkbox') {
			$attributes['name'] .= '[]';
		}
		$this->setOnEmpty($attributes, 'value', $this->getdefaultFormData($attributes['name']));

		return $this->storeElement(self::HTML_CHECKBOXES, $attributes, $options);
	}

	/**
	 * Add button
	 * @param  string $html like '<input name="test" />'
	 * @return Form       [description]
	 */
	public function button ($html) {
		$attributes = $this->parseTag($html);
		$this->throwExceptionOnEmpty($attributes, self::ATTRIBUTE_CONTENT);
		$this->setOnEmpty($attributes, 'type', 'submit');

		return $this->storeElement(self::HTML_BUTTON, $attributes);
	}

	/**
	 * Use free form html
	 * @param  string $html like '<form>'
	 * @return Form       [description]
	 */
	public function html ($html) {
		return $this->storeElement($html);
	}

	/**
	 * [getdefaultFormData description]
	 * @param  string $name [description]
	 * @return string       [description]
	 */
	public function getdefaultFormData ($name) {
			if (preg_match('#^(.+)(\[\])$#',$name, $nameParts)) {
				return (!$this->is_blank($this->defaultFormData[$nameParts[1]])) ? $this->defaultFormData[$nameParts[1]] : NULL;
			}
			else {
				return (!$this->is_blank($this->defaultFormData[$name])) ? $this->defaultFormData[$name] : NULL;
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
	protected function storeElement ($html, array $elementAttributes = array(), array $options = array()) {
		// convert numerical keys for values to value of values
		if (!empty($options) && empty($elementAttributes['data-preservekeys'])) {
			$keys = array_keys($options);
			if ($keys[0] === 0) {
				$tmp = array();
				foreach ($options as $option) {
					$tmp[$option] = $option;
				}
				$options = $tmp;
			}
		}

		// store element
		$this->formElements[] = (object) array(
			'html' => $html,
			'attributes' => $elementAttributes,
			'options' => $options,
		);

		// write form start for reference
		if ($html == self::HTML_FORM) {
			$this->formStart = &$this->formElements[key($this->formElements)];
		}
		return $this;
	}

	/**
	 * [parseTag description]
	 * @param  [type] $html [description]
	 * @return [type]       [description]
	 */
	protected function parseTag ($html) {
		$attributes = array();
		if (preg_match_all('#([\w\-]+)="([^"]*?)"#', $html, $parts)) {
			foreach ($parts[1] as $key => $keyName) {
				$attributes[$keyName] = ($keyName == 'class')
					? explode(' ',$parts[2][$key])
					: $parts[2][$key]
				;
			}
		}
		if (preg_match('#>(.+)<#',$html,$matches)) {
			$attributes[self::ATTRIBUTE_CONTENT] = htmlspecialchars_decode($matches[1]);
		}
		if (!empty($attributes['required'])) {
			$this->addClass($attributes,'required');
		}
		return $attributes;
	}

	/**
	 * [setOnEmpty description]
	 * @param [type] $array        [description]
	 * @param [type] $classname          [description]
	 */
	protected function addClass (array &$array, $classname) {
		if (empty($array['class'])) {
			$array['class'] = array($classname);
		}
		else {
			if (!in_array($classname, $array)) {
				$array['class'][] = $classname;
			}
		}
	}

	/**
	 * [setOnEmpty description]
	 * @param [type] $array        [description]
	 * @param [type] $classname          [description]
	 */
	protected function makeId (array &$array) {
		if (empty($array['id'])) {
			if (empty($array['name'])) {
				throw new Exception('Missing attribute "id" or "name"');
			}
			$array['id'] = (!empty($this->formStart->attributes['id']) ? $this->formStart->attributes['id'].'-' : '') . $array['name'];
		}
	}

	/**
	 * [setOnEmpty description]
	 * @param [type] $array        [description]
	 * @param [type] $key          [description]
	 * @param [type] $defaultValue [description]
	 */
	protected function setOnEmpty (array &$array, $key, $defaultValue) {
		if ($this->is_blank($array[$key]) && !$this->is_blank($defaultValue)) {
			$array[$key] = $defaultValue;
		}
	}

	/**
	 * [throwExceptionOnEmpty description]
	 * @param  [type] $array [description]
	 * @param  [type] $key   [description]
	 * @return [type]        [description]
	 */
	protected function throwExceptionOnEmpty (&$array, $key) {
		if ($this->is_blank($array[$key])) {
			throw new Exception('Missing attribute "'.$key.'"');
		}
	}

	/**
	 * -----------------------------------------------
	 *  OUTPUT
	 * -----------------------------------------------
	 */

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
			$return .= $this->returnHtmlForElement($element);
		}
		return $return;
	}

	/**
	 * -----------------------------------------------
	 *  OUTPUT HELPERS
	 * -----------------------------------------------
	 */

	/**
	 * Return HTML for a single form element
	 * @param  stdClass $element [description]
	 * @return string HTML
	 */
	protected function returnHtmlForElement (stdClass $element) {
		if (!empty($element->attributes)) {
			// get form field
			switch ($element->html) {
				case self::HTML_SELECT:
					$attributes = $this->returnAttributesAsHtml($element->attributes, array('value'));
					$formElement = sprintf($element->html, $attributes, $this->makeOptions($element));
					break;
				case self::HTML_CHECKBOXES:
					$attributes = $this->returnAttributesAsHtml($element->attributes, array('value','name'));
					$formElement = sprintf($element->html, $attributes, $this->makeOptions($element));
					break;
				case self::HTML_TEXTAREA:
					$attributes = $this->returnAttributesAsHtml($element->attributes, array('value'));
					$formElement = sprintf($element->html, $attributes, !empty($element->attributes['value']) ? $element->attributes['value'] : '');
					break;
				case self::HTML_BUTTON:
					$attributes = $this->returnAttributesAsHtml($element->attributes);
					$formElement = sprintf($element->html, $attributes, $element->attributes[self::ATTRIBUTE_CONTENT]);
					break;
				default:
					$attributes = $this->returnAttributesAsHtml($element->attributes);
					$formElement = sprintf($element->html, $attributes);
					if (!empty($element->options)) {
						$formElement .= $this->makeOptions($element);
					}
					break;
			}
			// get label
			switch ($element->html) {
				case self::HTML_INPUT:
				case self::HTML_TEXTAREA:
				case self::HTML_SELECT:
					if (empty($element->attributes['type']) || $element->attributes['type'] != 'hidden') {
						$formLabel = !empty($element->attributes['data-label']) ? '<label for="'.htmlspecialchars($element->attributes['id']).'">'.$this->makeLabelText($element).'</label>' : '';
					}
					break;
				case self::HTML_CHECKBOXES:
					$formLabel = !empty($element->attributes['data-label']) ? '<span class="label">'.$this->makeLabelText($element).'</span>' : '';
					break;
				case self::HTML_BUTTON:
					$formLabel = '';
					break;
			}
		}
		else {
			$formElement = $element->html;
		}

		return (!empty($this->htmlFieldWrapper) && isset($formLabel))
			? sprintf($this->htmlFieldWrapper, $formLabel, $formElement)
			: $formElement
		;
	}

	/**
	 * [makeLabel description]
	 * @param  stdClass $element [description]
	 * @return string HTML
	 */
	protected function makeLabelText (stdClass $element) {
		$html = sprintf($this->htmlLabelWrapper,htmlspecialchars($element->attributes['data-label']));
		if (!empty($this->htmlLabelRequired) && !empty($element->attributes['required'])) {
			$html .= $this->htmlLabelRequired;
		}
		return $html;
	}

	/**
	 * [makeOptions description]
	 * @param  stdClass $element [description]
	 * @return string HTML
	 */
	protected function makeOptions (stdClass $element) {
		$html = '';
		if (!empty($element->options)) {
			switch ($element->html) {
				case self::HTML_INPUT:
					$html .= '<datalist id="'.htmlspecialchars($element->attributes['list']).'">';
					foreach ($element->options as $id => $option) {
						$html .= '<option value="'.htmlspecialchars($id).'" />';
					}
					$html .= '</datalist>';
					break;
				case self::HTML_SELECT:
					foreach ($element->options as $id => $option) {
						$checked = ($this->isChecked ($element, $id)) ? ' selected="selected"' : '';
						$html .= '<option value="'.htmlspecialchars($id).'"'.$checked.'>'.htmlspecialchars($option).'</option>';
					}
					break;
				case self::HTML_CHECKBOXES:
					$attributes = $this->returnAttributesAsHtml($element->attributes, array('id'));
					foreach ($element->options as $id => $option) {
						$checked = ($this->isChecked ($element, $id)) ? ' checked="checked"' : '';
						$html .= '<label><input value="'.htmlspecialchars($id).'"'.$checked.$attributes.' /> <span>'.htmlspecialchars($option).'</span></label>';
					}
					break;
			}
		}
		return $html;
	}

	/**
	 * [returnAttributesAsHtml description]
	 * @param  array  $attributes        [description]
	 * @param  array  $forbiddenAttributes [description]
	 * @return string HTML
	 */
	protected function returnAttributesAsHtml (array $attributes, array $forbiddenAttributes = array()) {
		$html = '';
		foreach ($attributes as $key => $value) {
			if (empty($forbiddenAttributes) || !in_array($key, $forbiddenAttributes) && strpos($key, '_') !== 0) {
				if (is_array($value)) {
					$value = implode(' ', $value);
				}
				$html .= ' '.htmlspecialchars($key).'="'.htmlspecialchars($value).'"';
			}
		}
		return $html;
	}

	/**
	 * [isChecked description]
	 * @param  stdClass $element [description]
	 * @return boolean           [description]
	 */
	protected function isChecked (stdClass $element, $value) {
		if (!$this->is_blank($value)) {
			if (is_array($element->attributes['value'])) {
				return !$this->is_blank($element->attributes['value']) && in_array($value, $element->attributes['value']);
			}
			else {
				return !$this->is_blank($element->attributes['value']) && $value == $element->attributes['value'];
			}
		}
		return FALSE;
	}

	/**
	 * Checks if a scalar value is FALSE, without content or only full of
	 * whitespaces.
	 * For non-scalar values will evaluate if value is empty().
	 *
	 * @param   mixed	$v	to test
	 * @return	bool	if $v is blank
	 */
	public function is_blank (&$v) {
	    return !isset($v) || (is_scalar($v) ? (trim($v) === '') : empty($v));
	}
}