<?php
/**
 * @class FormElement
 * Build form element
 *
 *
 * @author      Frank Bo"es <info@3960.org>
 * @copyright   MIT License (MIT)
 */
class FormElement {
	public $html;
	public $attributes;
	public $options;
	public $errors = array();

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
				$attributes[$keyName] = $this->convertAttribute($keyName, $parts[2][$key]);
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

	public function setAttributesByArray (array $attributes) {
		foreach ($attributes as $attribute => $value) {
			$this->setOnEmpty($attribute, $value);
		}
	}

	/**
	 * [convertAttribute description]
	 * @param  [type] $name  [description]
	 * @param  [type] $value [description]
	 * @return [type]        [description]
	 */
	public function convertAttribute ($name, $value) {
		if ($name == 'class') {
			return
			 explode(' ',$value);
		}
		elseif (strpos($name, 'data-') === 0 && strpos($value, '[') === 0) {
			return
			 json_decode($value);
		}
		else {
			return $value;
		}
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
			if (!in_array($classname, $this->attributes['class'])) {
				$this->attributes['class'][] = $classname;
			}
		}
		return $this;
	}

	/**
	 * [addDefaultValue description]
	 * @param string $value [description]
	 * @return FormElement
	 */
	public function addDefaultValue ($value = '') {
		if (Form::is_blank($this->attributes['value'])) {
			if (!Form::is_blank($value)) {
				$this->attributes['value'] = $value;
			}
			elseif (!Form::is_blank($this->attributes['default'])) {
				$this->attributes['value'] = $this->attributes['default'];
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
		$this->attributes['id'] = Form::make_id($this->attributes['id']);
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
			$this->attributes[$key] = $this->convertAttribute($key, $defaultValue);
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
		if (!Form::is_blank($this->attributes['required']) && Form::is_blank($this->attributes['value'])) {
			$this->addError('required', _('This form field is required.'));
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * [addError description]
	 * @param string $msg [description]
	 * @return  FormElement [description]
	 */
	public function addError ($type, $msg) {
		$this->addClass('invalid');
		$this->addClass('invalid-'.$type);
		$this->errors[] = $msg;
		return $this;
	}

	/**
	 * Return HTML for a single form element
	 * @param string $htmlFieldWrapper
	 * @param string $htmlLabelWrapper
	 * @param string $htmlLabelRequired
	 * @return string HTML
	 */
	public function returnHtml ($htmlFieldWrapper = "<span>%1\$s%2\$s</span>\n", $htmlLabelWrapper = "%s", $htmlLabelRequired = " *", $htmlErrorWrapper = '<span class="invalid">%s</span>') {
		if (!empty($this->attributes)) {
			// get form field
			switch ($this->html) {
				case Form::HTML_SELECT:
					$attributes = $this->returnAttributesAsHtml($this->attributes, array('value'));
					$formElement = sprintf($this->html, $attributes, $this->makeOptions(Form::HTML_SELECT_OPTION, Form::HTML_SELECT_OPTIONS_WRAPPER));
					break;
				case Form::HTML_CHECKBOXES:
					$attributes = $this->returnAttributesAsHtml($this->attributes, array('value','name'));
					$formElement = sprintf($this->html, $attributes, $this->makeOptions(Form::HTML_CHECKBOXES_OPTION, Form::HTML_CHECKBOXES_OPTIONS_WRAPPER, 'checked="checked"'));
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
						$formElement .= $this->makeOptions(Form::HTML_INPUT_OPTION, Form::HTML_INPUT_OPTIONS_WRAPPER);
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
					$formLabel = !empty($this->attributes['data-label']) ? '<label>'.$this->makeLabelText($htmlLabelWrapper, $htmlLabelRequired).'</label>' : '';
					break;
				case Form::HTML_BUTTON:
					$formLabel = '';
					break;
			}
			// get errors
			$formError = NULL;
			if (!empty($this->errors)) {
				$formError = sprintf($htmlErrorWrapper, htmlspecialchars(implode(" ",$this->errors))); # TODO
			}
		}
		else {
			$formElement = $this->html;
		}

		return (!empty($htmlFieldWrapper) && isset($formLabel))
			? sprintf($htmlFieldWrapper, $formLabel, $formElement, $formError)
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
		$forbiddenAttributes = array_merge($forbiddenAttributes, array('data-label', 'default'));
		foreach ($attributes as $key => $value) {
			if (!Form::is_blank($value) && (empty($forbiddenAttributes) || !in_array($key, $forbiddenAttributes) && strpos($key, '_') !== 0)) {
				if (is_array($value)) {
					switch ($key) {
						case 'class':
							$value = implode(' ', $value);
							break;
						default:
							$value = json_encode($value);
							break;
					}
				}
				$html .= ' '.htmlspecialchars($key).'="'.htmlspecialchars($value).'"';
			}
		}
		return $html;
	}

	/**
	 * [makeLabel description]
	 * @param  string $htmlLabelWrapper
	 * @param  string $htmlLabelRequired
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
	 * @param  string $htmlOption         [description]
	 * @param  string $htmlOptionsWrapper [description]
	 * @param  string $htmlSelected       [description]
	 * @return string                     HTML
	 */
	protected function makeOptions ($htmlOption = '<option%1$s>%2$s</option>', $htmlOptionsWrapper = '%1$s', $htmlSelected = 'selected="selected"') {
		$html = '';
		if (!empty($this->options)) {
			switch ($this->html) {
				case Form::HTML_INPUT:
					foreach ($this->options as $id => $option) {
						$checked = ($this->isChecked ($id)) ? ' '.$htmlSelected : '';
						$label = ($option != $id) ?  ' label="'.htmlspecialchars($option).'"' : '';
						$html .= sprintf($htmlOption, ' value="'.htmlspecialchars($id).'"'.$label.$checked, NULL);
					}
					$html = sprintf($htmlOptionsWrapper, $html, ' id="'.htmlspecialchars($this->attributes['list']).'"');
					break;
				case Form::HTML_SELECT:
					foreach ($this->options as $id => $option) {
						$checked = ($this->isChecked ($id)) ? ' '.$htmlSelected : '';
						$html .= sprintf($htmlOption, ' value="'.htmlspecialchars($id).'"'.$checked, htmlspecialchars($option));
					}
					break;
				case Form::HTML_CHECKBOXES:
					$attributes = $this->returnAttributesAsHtml($this->attributes, array('id','value'));
					foreach ($this->options as $id => $option) {
						$checked = ($this->isChecked ($id)) ? ' '.$htmlSelected : '';
						$html .= sprintf($htmlOption, ' value="'.htmlspecialchars($id).'"'.$checked.$attributes, htmlspecialchars($option));
					}
					$html = sprintf($htmlOptionsWrapper, $html, ' id="'.htmlspecialchars($this->attributes['id']).'"');
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