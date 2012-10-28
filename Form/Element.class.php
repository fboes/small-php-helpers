<?php
/**
 * @class FormElement
 * Build form element
 *
 *
 * @author      Frank Bo"es <info@3960.org>
 * @copyright   Creative Commons Attribution 3.0 Unported (CC BY 3.0)
 */
class FormElement {
	public $html;
	public $attributes;
	public $options;
	public $hasErrors = FALSE;

	/**
	 * [__construct description]
	 * @param string $html    [description]
	 * @param string $parseableHtml
	 * @param array  $options [description]
	 */
	public function __construct ($html, $parseableHtml = '', array $options = array()) {
		$attributes = $this->parseTag($parseableHtml);
		// convert numerical keys for values to value of values
		if (!empty($options) && empty($attributes['data-preservekeys'])) {
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
		$this->html = $html;
		$this->attributes = $attributes;
		$this->options = $options;
	}


	/**
	 * [parseTag description]
	 * @param  string $html [description]
	 * @return array       [description]
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
			$attributes[Form::ATTRIBUTE_CONTENT] = htmlspecialchars_decode($matches[1]);
		}
		if (!empty($attributes['required'])) {
			$this->addClass($attributes,'required');
		}
		return $attributes;
	}

	/**
	 * [setOnEmpty description]
	 * @param string $classname          [description]
	 * @return FormElement
	 */
	public function addClass ($classname) {
		if (empty($this->attributes['class'])) {
			$this->attributes['class'] = array($classname);
		}
		else {
			if (!in_array($classname, $this->attributes)) {
				$this->attributes['class'][] = $classname;
			}
		}
		return $this;
	}

	/**
	 * [setOnEmpty description]
	 * @param string $basicId e.g. ID of form
	 * @return FormElement
	 */
	public function makeId ($basicId = '') {
		if (empty($this->attributes['id'])) {
			if (empty($this->attributes['name'])) {
				throw new Exception('Missing attribute "id" or "name"');
			}
			$this->attributes['id'] = (!empty($basicId) ? $basicId.'-' : '') . $this->attributes['name'];
		}
		return $this;
	}

	/**
	 * [setOnEmpty description]
	 * @param [type] $key          [description]
	 * @param [type] $defaultValue [description]
	 * @return FormElement
	 */
	public function setOnEmpty ($key, $defaultValue) {
		if (Form::is_blank($this->attributes[$key]) && !Form::is_blank($defaultValue)) {
			$this->attributes[$key] = $defaultValue;
		}
		return $this;
	}

	/**
	 * [throwExceptionOnEmpty description]
	 * @param  [type] $key   [description]
	 * @return FormElement
	 */
	public function throwExceptionOnEmpty ($key) {
		if (Form::is_blank($this->attributes[$key])) {
			throw new Exception('Missing attribute "'.$key.'"');
		}
		return $this;
	}

	/**
	 * Add error if field value is required but none given
	 * @return bool FALSE in case anything went wrong
	 */
	public function addErrorsOnRequired () {
		if ($this->attributes['required']) && Form::is_blank($this->attributes['value'])) {
			$this->addClass('error');
			$this->error = TRUE;
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Return HTML for a single form element
	 * @param string $htmlFieldWrapper
	 * @param string $htmlLabelWrapper
	 * @param string $htmlLabelRequired
	 * @return string HTML
	 */
	public function returnHtml ($htmlFieldWrapper = "<span>%1\$s%2\$s</span>\n", $htmlLabelWrapper = "%s", $htmlLabelRequired = " *") {
		if (!empty($this->attributes)) {
			// get form field
			switch ($this->html) {
				case Form::HTML_SELECT:
					$attributes = $this->returnAttributesAsHtml($this->attributes, array('value'));
					$formElement = sprintf($this->html, $attributes, $this->makeOptions($this));
					break;
				case Form::HTML_CHECKBOXES:
					$attributes = $this->returnAttributesAsHtml($this->attributes, array('value','name'));
					$formElement = sprintf($this->html, $attributes, $this->makeOptions($this));
					break;
				case Form::HTML_TEXTAREA:
					$attributes = $this->returnAttributesAsHtml($this->attributes, array('value'));
					$formElement = sprintf($this->html, $attributes, !empty($this->attributes['value']) ? $this->attributes['value'] : '');
					break;
				case Form::HTML_BUTTON:
					$attributes = $this->returnAttributesAsHtml($this->attributes);
					$formElement = sprintf($this->html, $attributes, $this->attributes[Form::ATTRIBUTE_CONTENT]);
					break;
				default:
					$attributes = $this->returnAttributesAsHtml($this->attributes);
					$formElement = sprintf($this->html, $attributes);
					if (!empty($this->options)) {
						$formElement .= $this->makeOptions($this);
					}
					break;
			}
			// get label
			switch ($this->html) {
				case Form::HTML_INPUT:
				case Form::HTML_TEXTAREA:
				case Form::HTML_SELECT:
					if (empty($this->attributes['type']) || $this->attributes['type'] != 'hidden') {
						$formLabel = !empty($this->attributes['data-label']) ? '<label for="'.htmlspecialchars($this->attributes['id']).'">'.$this->makeLabelText($htmlLabelWrapper, $htmlLabelRequired).'</label>' : '';
					}
					break;
				case Form::HTML_CHECKBOXES:
					$formLabel = !empty($this->attributes['data-label']) ? '<span class="label">'.$this->makeLabelText($htmlLabelWrapper, $htmlLabelRequired).'</span>' : '';
					break;
				case Form::HTML_BUTTON:
					$formLabel = '';
					break;
			}
		}
		else {
			$formElement = $this->html;
		}

		return (!empty($htmlFieldWrapper) && isset($formLabel))
			? sprintf($htmlFieldWrapper, $formLabel, $formElement)
			: $formElement
		;
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
	 * [makeLabel description]
	 * @param string $htmlLabelWrapper
	 * @param string $htmlLabelRequired
	 * @return string HTML
	 */
	protected function makeLabelText ($htmlLabelWrapper, $htmlLabelRequired) {
		$html = sprintf($htmlLabelWrapper,htmlspecialchars($this->attributes['data-label']));
		if (!empty($htmlLabelRequired) && !empty($this->attributes['required'])) {
			$html .= $htmlLabelRequired;
		}
		return $html;
	}

	/**
	 * [makeOptions description]
	 * @return string HTML
	 */
	protected function makeOptions () {
		$html = '';
		if (!empty($this->options)) {
			switch ($this->html) {
				case Form::HTML_INPUT:
					$html .= '<datalist id="'.htmlspecialchars($this->attributes['list']).'">';
					foreach ($this->options as $id => $option) {
						$html .= '<option value="'.htmlspecialchars($id).'" />';
					}
					$html .= '</datalist>';
					break;
				case Form::HTML_SELECT:
					foreach ($this->options as $id => $option) {
						$checked = ($this->isChecked ($id)) ? ' selected="selected"' : '';
						$html .= '<option value="'.htmlspecialchars($id).'"'.$checked.'>'.htmlspecialchars($option).'</option>';
					}
					break;
				case Form::HTML_CHECKBOXES:
					$attributes = $this->returnAttributesAsHtml($this->attributes, array('id'));
					foreach ($this->options as $id => $option) {
						$checked = ($this->isChecked ($id)) ? ' checked="checked"' : '';
						$html .= '<label><input value="'.htmlspecialchars($id).'"'.$checked.$attributes.' /> <span>'.htmlspecialchars($option).'</span></label>';
					}
					break;
			}
		}
		return $html;
	}

	/**
	 * [isChecked description]
	 * @return boolean           [description]
	 */
	public function isChecked ($value) {
		if (!Form::is_blank($value)) {
			if (is_array($this->attributes['value'])) {
				return !Form::is_blank($this->attributes['value']) && in_array($value, $this->attributes['value']);
			}
			else {
				return !Form::is_blank($this->attributes['value']) && $value == $this->attributes['value'];
			}
		}
		return FALSE;
	}
}