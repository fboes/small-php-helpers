<?php
namespace fboes\SmallPhpHelpers\Form;

use fboes\SmallPhpHelpers\Form;

/**
 * @class Element
 * Build form element
 *
 *
 * @author      Frank Bo"es <info@3960.org>
 * @copyright   MIT License (MIT)
 */
class Element
{
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
    public function __construct($html, $parseableHtml = '', array $options = array())
    {
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
    protected function parseTag($html)
    {
        $attributes = array();
        if (preg_match_all('#([\w\-]+)="([^"]*?)"#', $html, $parts)) {
            foreach ($parts[1] as $key => $keyName) {
                $attributes[$keyName] = $this->convertAttribute($keyName, $parts[2][$key]);
            }
        }
        if (preg_match('#>(.+)<#', $html, $matches)) {
            $attributes[Form::ATTRIBUTE_CONTENT] = htmlspecialchars_decode($matches[1]);
        }
        if (!empty($attributes['required'])) {
            $this->addClass('required');
        }
        return $attributes;
    }

    /**
     * [setAttributesByArray description]
     * @param array $attributes [description]
     */
    public function setAttributesByArray(array $attributes)
    {
        foreach ($attributes as $attribute => $value) {
            $this->setOnEmpty($attribute, $value);
        }
    }

    /**
     * [convertAttribute description]
     * @param  mixed $name  [description]
     * @param  mixed $value [description]
     * @return mixed        [description]
     */
    public function convertAttribute($name, $value)
    {
        if ($name == 'class') {
            return explode(' ', $value);
        } elseif (strpos($name, 'data-json-') === 0) {
            return json_decode($value);
        } else {
            return $value;
        }
    }

    /**
     * [setOnEmpty description]
     * @param string $classname          [description]
     * @return Element
     */
    public function addClass($classname)
    {
        if (empty($this->attributes['class'])) {
            $this->attributes['class'] = array($classname);
        } else {
            if (!in_array($classname, $this->attributes['class'])) {
                $this->attributes['class'][] = $classname;
            }
        }
        return $this;
    }

    /**
     * [addDefaultValue description]
     * @param string $value [description]
     * @return Element
     */
    public function addDefaultValue($value = '')
    {
        if (Form::isBlank($this->attributes['value'])) {
            if (!Form::isBlank($value)) {
                $this->attributes['value'] = $value;
            } elseif (!Form::isBlank($this->attributes['default'])) {
                $this->attributes['value'] = $this->attributes['default'];
            }
        }
        if (!Form::isBlank($this->attributes['type'])
            && !Form::isBlank($this->attributes['value']) && $this->attributes['type'] === 'file'
        ) {
            $this->setOnEmpty('data-output', $this->attributes['value']);
            unset($this->attributes['value']);
        }
        return $this;
    }

    /**
     * [setOnEmpty description]
     * @param string $basicId e.g. ID of form
     * @return $this
     * @throws \Exception
     */
    public function makeId($basicId = '')
    {
        if (empty($this->attributes['id'])) {
            if (empty($this->attributes['name'])) {
                throw new \Exception('Missing attribute "id" or "name"');
            }
            $this->attributes['id'] = (!empty($basicId) ? $basicId.'-' : '') . $this->attributes['name'];
        }
        $this->attributes['id'] = Form::makeId($this->attributes['id']);
        return $this;
    }

    /**
     * [setOnEmpty description]
     * @param [type] $key          [description]
     * @param [type] $defaultValue [description]
     * @return Element
     */
    public function setOnEmpty($key, $defaultValue)
    {
        if (Form::isBlank($this->attributes[$key]) && !Form::isBlank($defaultValue)) {
            $this->attributes[$key] = $this->convertAttribute($key, $defaultValue);
        }
        return $this;
    }

    /**
     * [throwExceptionOnEmpty description]
     * @param  [type] $key   [description]
     * @return $this
     * @throws \Exception
     */
    public function throwExceptionOnEmpty($key)
    {
        if (Form::isBlank($this->attributes[$key])) {
            throw new \Exception('Missing attribute "'.$key.'"');
        }
        return $this;
    }

    /**
     * Add error if field value is required but none given
     * @return bool false in case anything went wrong
     */
    public function addErrorsOnRequired()
    {
        if (!Form::isBlank($this->attributes['required']) && Form::isBlank($this->attributes['value'])) {
            $this->addError('required', _('This form field is required.'));
            return false;
        }
        return true;
    }

    /**
     * [addError description]
     * @param string $type [description]
     * @param string $msg  [description]
     * @return  Element    [description]
     */
    public function addError($type, $msg)
    {
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
     * @param string $htmlErrorWrapper
     * @return string HTML
     */
    public function returnHtml(
        $htmlFieldWrapper = "<span>%1\$s%2\$s</span>\n",
        $htmlLabelWrapper = "%s",
        $htmlLabelRequired = " *",
        $htmlErrorWrapper = '<span class="invalid">%s</span>'
    ) {
        $useOptgroup = !empty($this->attributes['data-optgroup']);
        $formError = null;
        $hint = null;
        if (!empty($this->attributes)) {
            // get form field
            switch ($this->html) {
                case Form::HTML_SELECT:
                    $attributes = $this->returnAttributesAsHtml($this->attributes, array('value'));
                    $formElement = sprintf(
                        $this->html,
                        $attributes,
                        $this->makeOptgroup(
                            Form::HTML_SELECT_OPTION,
                            Form::HTML_SELECT_OPTIONS_WRAPPER,
                            Form::HTML_SELECT_OPTIONS_GROUP,
                            $useOptgroup
                        )
                    );
                    break;
                case Form::HTML_CHECKBOXES:
                    $attributes = $this->returnAttributesAsHtml($this->attributes, array('value','name'));
                    $formElement = sprintf(
                        $this->html,
                        $attributes,
                        $this->makeOptgroup(
                            Form::HTML_CHECKBOXES_OPTION,
                            Form::HTML_CHECKBOXES_OPTIONS_WRAPPER,
                            Form::HTML_CHECKBOXES_OPTIONS_GROUP,
                            $useOptgroup,
                            'checked="checked"'
                        )
                    );
                    break;
                case Form::HTML_TEXTAREA:
                    $attributes = $this->returnAttributesAsHtml($this->attributes, array('value'));
                    $formElement = sprintf(
                        $this->html,
                        $attributes,
                        !empty($this->attributes['value']) ? $this->attributes['value'] : ''
                    );
                    break;
                case Form::HTML_BUTTON:
                    $attributes = $this->returnAttributesAsHtml($this->attributes);
                    $formElement = sprintf($this->html, $attributes, $this->attributes[Form::ATTRIBUTE_CONTENT]);
                    break;
                case Form::HTML_INPUT_OPTIONS_WRAPPER:
                    $attributes = $this->returnAttributesAsHtml($this->attributes);
                    $formElement = sprintf($this->html, $this->makeOptgroup(), $attributes);
                    break;
                default:
                    $attributes = $this->returnAttributesAsHtml($this->attributes);
                    $formElement = sprintf($this->html, $attributes);
                    if (!empty($this->options)) {
                        $formElement .= $this->makeOptgroup(
                            Form::HTML_INPUT_OPTION,
                            Form::HTML_INPUT_OPTIONS_WRAPPER,
                            Form::HTML_INPUT_OPTIONS_GROUP,
                            $useOptgroup
                        );
                    }
                    if (!Form::isBlank($this->attributes['data-output'])) {
                        $formElement .=
                            '<output name="'.htmlspecialchars($this->attributes['id'].'-output').'" for="'
                            .htmlspecialchars($this->attributes['id']).'">'
                            .htmlspecialchars($this->attributes['data-output']).'</output>'
                        ;
                    }
                    break;
            }
            // get label
            switch ($this->html) {
                case Form::HTML_INPUT:
                case Form::HTML_TEXTAREA:
                case Form::HTML_SELECT:
                    if (empty($this->attributes['type']) || $this->attributes['type'] != 'hidden') {
                        $formLabel =
                            !empty($this->attributes['data-label'])
                                ? '<label for="'.htmlspecialchars($this->attributes['id']).'">'.
                                    $this->makeLabelText($htmlLabelWrapper, $htmlLabelRequired).'</label>'
                                : ''
                        ;
                    }
                    break;
                case Form::HTML_CHECKBOXES:
                    $formLabel =
                        !empty($this->attributes['data-label'])
                            ? '<label>'.$this->makeLabelText($htmlLabelWrapper, $htmlLabelRequired).'</label>'
                            : ''
                        ;
                    break;
                case Form::HTML_BUTTON:
                    $formLabel = '';
                    break;
            }
            // get errors
            if (!empty($this->errors)) {
                $formError = sprintf($htmlErrorWrapper, htmlspecialchars(implode(" ", $this->errors))); # TODO
            }
            // get hint
            $hint = !empty($this->attributes['data-hint'])
                ? '<span class="form-field-hint">'.htmlspecialchars($this->attributes['data-hint']).'</span>'
                : ''
            ;
        } else {
            $formElement = $this->html;
        }

        return (!empty($htmlFieldWrapper) && isset($formLabel))
            ? sprintf($htmlFieldWrapper, $formLabel, $formElement, $formError, $hint)
            : $formElement
        ;
    }

    /**
     * [returnAttributesAsHtml description]
     * @param  array  $attributes        [description]
     * @param  array  $forbiddenAttributes [description]
     * @return string HTML
     */
    protected function returnAttributesAsHtml(array $attributes, array $forbiddenAttributes = array())
    {
        $html = '';
        $forbiddenAttributes = array_merge(
            $forbiddenAttributes,
            array('data-label','data-hint', 'default', 'data-optgroup')
        );
        foreach ($attributes as $key => $value) {
            if (!Form::isBlank($value)
                && (empty($forbiddenAttributes) || !in_array($key, $forbiddenAttributes) && strpos($key, '_') !== 0)
            ) {
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
    protected function makeLabelText($htmlLabelWrapper, $htmlLabelRequired)
    {
        $html = sprintf($htmlLabelWrapper, htmlspecialchars($this->attributes['data-label']));
        if (!empty($htmlLabelRequired) && !empty($this->attributes['required'])) {
            $html .= $htmlLabelRequired;
        }
        return $html;
    }

    /**
     * [makeOptgroup description]
     * @param string $htmlOption
     * @param string $htmlOptionsWrapper
     * @param string $htmlOptionGroup
     * @param bool   $useOptgroup
     * @param string $htmlSelected
     * @return string
     */
    protected function makeOptgroup(
        $htmlOption = '<option%1$s>%2$s</option>',
        $htmlOptionsWrapper = '%1$s',
        $htmlOptionGroup = '%2$s',
        $useOptgroup = false,
        $htmlSelected = 'selected="selected"'
    ) {
        if (!$useOptgroup) {
            return $this->makeOptions($this->options, $htmlOption, $htmlOptionsWrapper, $htmlSelected);
        }
        $html = '';
        if (!empty($this->options)) {
            foreach ($this->options as $label => $options) {
                $html .= sprintf(
                    $htmlOptionGroup,
                    $label,
                    $this->makeOptions($options, $htmlOption, $htmlOptionsWrapper, $htmlSelected)
                );
            }
        }
        return $html;
    }

    /**
     * [makeOptions description]
     * @param        $options
     * @param string $htmlOption
     * @param string $htmlOptionsWrapper
     * @param string $htmlSelected
     * @return string
     */
    protected function makeOptions(
        $options,
        $htmlOption = '<option%1$s>%2$s</option>',
        $htmlOptionsWrapper = '%1$s',
        $htmlSelected = 'selected="selected"'
    ) {
        $html = '';
        if (!empty($options)) {
            switch ($this->html) {
                case Form::HTML_INPUT:
                case Form::HTML_INPUT_OPTIONS_WRAPPER:
                    foreach ($options as $id => $option) {
                        $checked = ($this->isChecked($id)) ? ' '.$htmlSelected : '';
                        $label = ($option != $id) ?  ' label="'.htmlspecialchars($option).'"' : '';
                        $html .= sprintf(
                            $htmlOption,
                            (($id != $option) ? ' value="'.htmlspecialchars($id).'"' : '').$checked.$label,
                            htmlspecialchars($option)
                        );
                    }
                    $html = sprintf(
                        $htmlOptionsWrapper,
                        $html,
                        !empty($this->attributes['list'])
                            ? ' id="'.htmlspecialchars($this->attributes['list']).'"'
                            : null
                    );
                    break;
                case Form::HTML_SELECT:
                    foreach ($options as $id => $option) {
                        $checked = ($this->isChecked($id)) ? ' '.$htmlSelected : '';
                        $html .= sprintf(
                            $htmlOption,
                            (($id != $option) ? ' value="'.htmlspecialchars($id).'"' : '').$checked,
                            htmlspecialchars($option)
                        );
                    }
                    break;
                case Form::HTML_CHECKBOXES:
                    $attributes = $this->returnAttributesAsHtml($this->attributes, array('id','value'));
                    foreach ($options as $id => $option) {
                        $checked = ($this->isChecked($id)) ? ' '.$htmlSelected : '';
                        $html .= sprintf(
                            $htmlOption,
                            ' value="'.htmlspecialchars($id).'"'.$checked.$attributes,
                            htmlspecialchars($option)
                        );
                    }
                    $html = sprintf($htmlOptionsWrapper, $html, ' id="'.htmlspecialchars($this->attributes['id']).'"');
                    break;
                default:
                    foreach ($options as $id => $option) {
                        $checked = ($this->isChecked($id)) ? ' '.$htmlSelected : '';
                        $html .= sprintf(
                            $htmlOption,
                            (($id != $option) ? ' value="'.htmlspecialchars($id).'"' : '').$checked,
                            htmlspecialchars($option)
                        );
                    }
                    break;
            }
        }
        return $html;
    }

    /**
     * @param $value
     * @return bool
     */
    public function isChecked($value)
    {
        if (!Form::isBlank($value) && !Form::isBlank($this->attributes['value'])) {
            if (is_array($this->attributes['value'])) {
                return in_array($value, $this->attributes['value']);
            } else {
                return $value == $this->attributes['value'];
            }
        }
        return false;
    }
}
