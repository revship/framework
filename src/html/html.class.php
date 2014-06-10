<?php
/**
 * CHtml class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CHtml is a class that provides a collection of helper methods for creating HTML views.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CHtml.php 2618 2010-11-04 04:33:25Z qiang.xue $
 * @package system.web.helpers
 * @since 1.0
 */
class Revship_Html
{
    const ID_PREFIX = 'rs';
    /**
     * @var string the CSS class for displaying error summaries (see {@link errorSummary}).
     */
    public $errorSummaryCss = 'errorSummary';
    /**
     * @var string the CSS class for displaying error messages (see {@link error}).
     */
    public $errorMessageCss = 'errorMessage';
    /**
     * @var string the CSS class for highlighting error inputs. Form inputs will be appended
     * with this CSS class if they have input errors.
     */
    public $errorCss = 'error';
    /**
     * @var string the CSS class for required labels. Defaults to 'required'.
     * @see label
     */
    public $requiredCss = 'required';
    /**
     * @var string the HTML code to be prepended to the required label.
     * @see label
     */
    public $beforeRequiredLabel = '';
    /**
     * @var string the HTML code to be appended to the required label.
     * @see label
     */
    public $afterRequiredLabel = ' <span class="required">*</span>';
    /**
     * @var integer the counter for generating automatic input field names.
     * @since 1.0.4
     */
    public $count = 0;
    
    /**
     * Encodes special characters into HTML entities.
     * The {@link CApplication::charset application charset} will be used for encoding.
     * @param string $text data to be encoded
     * @return string the encoded data
     * @see http://www.php.net/manual/en/function.htmlspecialchars.php
     */
    public function encode($text){
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Encodes special characters in an array of strings into HTML entities.
     * Both the array keys and values will be encoded if needed.
     * If a value is an array, this method will also encode it recursively.
     * The {@link CApplication::charset application charset} will be used for encoding.
     * @param array $data data to be encoded
     * @return array the encoded data
     * @see http://www.php.net/manual/en/function.htmlspecialchars.php
     * @since 1.0.4
     */
    public function encodeArray($data){
        $d = array ();
        foreach ( $data as $key => $value ) {
            if (is_string($key))
                $key = htmlspecialchars($key, ENT_QUOTES, 'UTF-8');
            if (is_string($value))
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            else if (is_array($value))
                $value = $this->encodeArray($value);
            $d [$key] = $value;
        }
        return $d;
    }
    
    /**
     * Generates an HTML element.
     * @param string $tag the tag name
     * @param array $htmlOptions the element attributes. The values will be HTML-encoded using {@link encode()}.
     * Since version 1.0.5, if an 'encode' attribute is given and its value is false,
     * the rest of the attribute values will NOT be HTML-encoded.
     * Since version 1.1.5, attributes whose value is null will not be rendered.
     * @param mixed $content the content to be enclosed between open and close element tags. It will not be HTML-encoded.
     * If false, it means there is no body content.
     * @param boolean $closeTag whether to generate the close tag.
     * @return string the generated HTML element tag
     */
    public function tag($tag, $htmlOptions = array(), $content = false, $closeTag = true){
        $html = '<' . $tag . $this->renderAttributes($htmlOptions);
        if ($content === false)
            return $closeTag ? $html . ' />' : $html . '>';
        else
            return $closeTag ? $html . '>' . $content . '</' . $tag . '>' : $html . '>' . $content;
    }
    
    /**
     * Generates an open HTML element.
     * @param string $tag the tag name
     * @param array $htmlOptions the element attributes. The values will be HTML-encoded using {@link encode()}.
     * Since version 1.0.5, if an 'encode' attribute is given and its value is false,
     * the rest of the attribute values will NOT be HTML-encoded.
     * Since version 1.1.5, attributes whose value is null will not be rendered.
     * @return string the generated HTML element tag
     */
    public function openTag($tag, $htmlOptions = array()){
        return '<' . $tag . $this->renderAttributes($htmlOptions) . '>';
    }
    
    /**
     * Generates a close HTML element.
     * @param string $tag the tag name
     * @return string the generated HTML element tag
     */
    public function closeTag($tag){
        return '</' . $tag . '>';
    }
    
    /**
     * Encloses the given string within a CDATA tag.
     * @param string $text the string to be enclosed
     * @return string the CDATA tag with the enclosed content.
     */
    public function cdata($text){
        return '<![CDATA[' . $text . ']]>';
    }
    
    /**
     * Generates a meta tag that can be inserted in the head section of HTML page.
     * @param string $content content attribute of the meta tag
     * @param string $name name attribute of the meta tag. If null, the attribute will not be generated
     * @param string $httpEquiv http-equiv attribute of the meta tag. If null, the attribute will not be generated
     * @param array $options other options in name-value pairs (e.g. 'scheme', 'lang')
     * @return string the generated meta tag
     * @since 1.0.1
     */
    public function metaTag($content, $name = null, $httpEquiv = null, $options = array()){
        if ($name !== null)
            $options ['name'] = $name;
        if ($httpEquiv !== null)
            $options ['http-equiv'] = $httpEquiv;
        $options ['content'] = $content;
        return $this->tag('meta', $options);
    }
    
    /**
     * Generates a link tag that can be inserted in the head section of HTML page.
     * Do not confuse this method with {@link link()}. The latter generates a hyperlink.
     * @param string $relation rel attribute of the link tag. If null, the attribute will not be generated.
     * @param string $type type attribute of the link tag. If null, the attribute will not be generated.
     * @param string $href href attribute of the link tag. If null, the attribute will not be generated.
     * @param string $media media attribute of the link tag. If null, the attribute will not be generated.
     * @param array $options other options in name-value pairs
     * @return string the generated link tag
     * @since 1.0.1
     */
    public function linkTag($relation = null, $type = null, $href = null, $media = null, $options = array()){
        if ($relation !== null)
            $options ['rel'] = $relation;
        if ($type !== null)
            $options ['type'] = $type;
        if ($href !== null)
            $options ['href'] = $href;
        if ($media !== null)
            $options ['media'] = $media;
        return $this->tag('link', $options);
    }
    
    /**
     * Encloses the given CSS content with a CSS tag.
     * @param string $text the CSS content
     * @param string $media the media that this CSS should apply to.
     * @return string the CSS properly enclosed
     */
    public function css($text, $media = ''){
        if ($media !== '')
            $media = ' media="' . $media . '"';
        return "<style type=\"text/css\"{$media}>\n/*<![CDATA[*/\n{$text}\n/*]]>*/\n</style>";
    }
    /**
     * Links to the specified CSS file.
     * @param string $url the CSS URL
     * @param string $media the media that this CSS should apply to.
     * @return string the CSS link.
     */
    public function cssFile($url, $media = ''){
        if ($media !== '')
            $media = ' media="' . $media . '"';
        return '<link rel="stylesheet" type="text/css" href="' . $this->encode($url) . '"' . $media . ' />';
    }
    
    /**
     * Encloses the given JavaScript within a script tag.
     * @param string $text the JavaScript to be enclosed
     * @return string the enclosed JavaScript
     */
    public function script($text){
        return "<script type=\"text/javascript\">\n/*<![CDATA[*/\n{$text}\n/*]]>*/\n</script>";
    }
    
    /**
     * Includes a JavaScript file.
     * @param string $url URL for the JavaScript file
     * @return string the JavaScript file tag
     */
    public function scriptFile($url){
        return '<script type="text/javascript" src="' . $this->encode($url) . '"></script>';
    }
    
    /**
     * Generates a stateful form tag.
     * A stateful form tag is similar to {@link form} except that it renders an additional
     * hidden field for storing persistent page states. You should use this method to generate
     * a form tag if you want to access persistent page states when the form is submitted.
     * @param mixed $action the form action URL (see {@link normalizeUrl} for details about this parameter.)
     * @param string $method form method (e.g. post, get)
     * @param array $htmlOptions additional HTML attributes (see {@link tag}).
     * @return string the generated form tag.
     */
    public function statefulForm($action = '', $method = 'post', $htmlOptions = array()){
        return $this->form($action, $method, $htmlOptions) . "\n" . $this->tag('div', array (
            
                'style' => 'display:none' 
        ), $this->pageStateField(''));
    }
    
    /**
     * Generates a hyperlink tag.
     * @param string $text link body. It will NOT be HTML-encoded. Therefore you can pass in HTML code such as an image tag.
     * @param mixed $url a URL or an action route that can be used to create a URL.
     * See {@link normalizeUrl} for more details about how to specify this parameter.
     * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
     * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
     * @return string the generated hyperlink
     * @see normalizeUrl
     * @see clientChange
     */
    public function link($text, $url = '#', $htmlOptions = array())
    {
        if ($url !== '')
            $htmlOptions['href'] = $this->normalizeUrl($url);
        //$this->clientChange('click', $htmlOptions);
        return $this->tag('a', $htmlOptions, $text);
    }
    public function normalizeUrl ($url)
    {
        return $url;
    }
    /**
     * Generates a mailto link.
     * @param string $text link body. It will NOT be HTML-encoded. Therefore you can pass in HTML code such as an image tag.
     * @param string $email email address. If this is empty, the first parameter (link body) will be treated as the email address.
     * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
     * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
     * @return string the generated mailto link
     * @see clientChange
     * @since 1.0.1
     */
    public function mailto($text, $email = '', $htmlOptions = array()){
        if ($email === '')
            $email = $text;
        return $this->link($text, 'mailto:' . $email, $htmlOptions);
    }
    
    /**
     * Generates an image tag.
     * @param string $src the image URL
     * @param string $alt the alternative text display
     * @param array $htmlOptions additional HTML attributes (see {@link tag}).
     * @return string the generated image tag
     */
    public function image($src, $alt = '', $htmlOptions = array()){
        $htmlOptions ['src'] = $src;
        $htmlOptions ['alt'] = $alt;
        return $this->tag('img', $htmlOptions);
    }
    
    /**
     * Generates a button.
     * @param string $label the button label
     * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
     * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
     * @return string the generated button tag
     * @see clientChange
     */
    public function button($label = 'button', $htmlOptions = array()){
        if (! isset($htmlOptions ['name']))
            $htmlOptions ['name'] = $this->ID_PREFIX . $this->count ++;
        if (! isset($htmlOptions ['type']))
            $htmlOptions ['type'] = 'button';
        if (! isset($htmlOptions ['value']))
            $htmlOptions ['value'] = $label;
        $this->clientChange('click', $htmlOptions);
        return $this->tag('input', $htmlOptions);
    }
    
    /**
     * Generates a button using HTML button tag.
     * This method is similar to {@link button} except that it generates a 'button'
     * tag instead of 'input' tag.
     * @param string $label the button label. Note that this value will be directly inserted in the button element
     * without being HTML-encoded.
     * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
     * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
     * @return string the generated button tag
     * @see clientChange
     * @since 1.0.8
     */
    public function htmlButton($label = 'button', $htmlOptions = array()){
        if (! isset($htmlOptions ['name']))
            $htmlOptions ['name'] = $this->ID_PREFIX . $this->count ++;
        if (! isset($htmlOptions ['type']))
            $htmlOptions ['type'] = 'button';
        $this->clientChange('click', $htmlOptions);
        return $this->tag('button', $htmlOptions, $label);
    }
    
    /**
     * Generates a submit button.
     * @param string $label the button label
     * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
     * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
     * @return string the generated button tag
     * @see clientChange
     */
    public function submitButton($label = 'submit', $htmlOptions = array()){
        $htmlOptions ['type'] = 'submit';
        return $this->button($label, $htmlOptions);
    }
    
    /**
     * Generates a reset button.
     * @param string $label the button label
     * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
     * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
     * @return string the generated button tag
     * @see clientChange
     */
    public function resetButton($label = 'reset', $htmlOptions = array()){
        $htmlOptions ['type'] = 'reset';
        return $this->button($label, $htmlOptions);
    }
    
    /**
     * Generates an image submit button.
     * @param string $src the button label
     * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
     * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
     * @return string the generated button tag
     * @see clientChange
     */
    public function imageButton($src, $htmlOptions = array()){
        $htmlOptions ['src'] = $src;
        $htmlOptions ['type'] = 'image';
        return $this->button('submit', $htmlOptions);
    }
    
    /**
     * Generates a link submit button.
     * @param string $label the button label
     * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
     * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
     * @return string the generated button tag
     * @see clientChange
     */
    public function linkButton($label = 'submit', $htmlOptions = array()){
        if (! isset($htmlOptions ['submit']))
            $htmlOptions ['submit'] = isset($htmlOptions ['href']) ? $htmlOptions ['href'] : '';
        return $this->link($label, '#', $htmlOptions);
    }
    
    /**
     * Generates a label tag.
     * @param string $label label text. Note, you should HTML-encode the text if needed.
     * @param string $for the ID of the HTML element that this label is associated with.
     * If this is false, the 'for' attribute for the label tag will not be rendered (since version 1.0.11).
     * @param array $htmlOptions additional HTML attributes.
     * Starting from version 1.0.2, the following HTML option is recognized:
     * <ul>
     * <li>required: if this is set and is true, the label will be styled
     * with CSS class 'required' (customizable with CHtml::$requiredCss),
     * and be decorated with {@link CHtml::beforeRequiredLabel} and
     * {@link CHtml::afterRequiredLabel}.</li>
     * </ul>
     * @return string the generated label tag
     */
    public function label($label, $for, $htmlOptions = array()){
        if ($for === false)
            unset($htmlOptions ['for']);
        else
            $htmlOptions ['for'] = $for;
        if (isset($htmlOptions ['required'])) {
            if ($htmlOptions ['required']) {
                if (isset($htmlOptions ['class']))
                    $htmlOptions ['class'] .= ' ' . $this->requiredCss;
                else
                    $htmlOptions ['class'] = $this->requiredCss;
                $label = $this->beforeRequiredLabel . $label . $this->afterRequiredLabel;
            }
            unset($htmlOptions ['required']);
        }
        return $this->tag('label', $htmlOptions, $label);
    }
    
    /**
     * Generates a text field input.
     * @param string $name the input name
     * @param string $value the input value
     * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
     * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
     * @return string the generated input field
     * @see clientChange
     * @see inputField
     */
    public function textField($name, $value = '', $htmlOptions = array()){
        //$this->clientChange('change', $htmlOptions);
        return $this->inputField('text', $name, $value, $htmlOptions);
    }
    
    /**
     * Generates a hidden input.
     * @param string $name the input name
     * @param string $value the input value
     * @param array $htmlOptions additional HTML attributes (see {@link tag}).
     * @return string the generated input field
     * @see inputField
     */
    public function hiddenField($name, $value = '', $htmlOptions = array()){
        return $this->inputField('hidden', $name, $value, $htmlOptions);
    }
    
    /**
     * Generates a password field input.
     * @param string $name the input name
     * @param string $value the input value
     * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
     * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
     * @return string the generated input field
     * @see clientChange
     * @see inputField
     */
    public function passwordField($name, $value = '', $htmlOptions = array()){
        $this->clientChange('change', $htmlOptions);
        return $this->inputField('password', $name, $value, $htmlOptions);
    }
    
    /**
     * Generates a file input.
     * Note, you have to set the enclosing form's 'enctype' attribute to be 'multipart/form-data'.
     * After the form is submitted, the uploaded file information can be obtained via $_FILES[$name] (see
     * PHP documentation).
     * @param string $name the input name
     * @param string $value the input value
     * @param array $htmlOptions additional HTML attributes (see {@link tag}).
     * @return string the generated input field
     * @see inputField
     */
    public function fileField($name, $value = '', $htmlOptions = array()){
        return $this->inputField('file', $name, $value, $htmlOptions);
    }
    
    /**
     * Generates a text area input.
     * @param string $name the input name
     * @param string $value the input value
     * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
     * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
     * @return string the generated text area
     * @see clientChange
     * @see inputField
     */
    public function textArea($name, $value = '', $htmlOptions = array()){
        $htmlOptions ['name'] = $name;
        if (! isset($htmlOptions ['id']))
            $htmlOptions ['id'] = $this->getIdByName($name);
        else if ($htmlOptions ['id'] === false)
            unset($htmlOptions ['id']);
        //$this->clientChange('change', $htmlOptions);
        return $this->tag('textarea', $htmlOptions, isset($htmlOptions ['encode']) && ! $htmlOptions ['encode'] ? $value : $this->encode($value));
    }
    
    /**
     * Generates a radio button.
     * @param string $name the input name
     * @param boolean $checked whether the radio button is checked
     * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
     * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
     * Since version 1.1.2, a special option named 'uncheckValue' is available that can be used to specify
     * the value returned when the radio button is not checked. When set, a hidden field is rendered so that
     * when the radio button is not checked, we can still obtain the posted uncheck value.
     * If 'uncheckValue' is not set or set to NULL, the hidden field will not be rendered.
     * @return string the generated radio button
     * @see clientChange
     * @see inputField
     */
    public function radioButton($name, $checked = false, $htmlOptions = array()){
        if ($checked)
            $htmlOptions ['checked'] = 'checked';
        else
            unset($htmlOptions ['checked']);
        $value = isset($htmlOptions ['value']) ? $htmlOptions ['value'] : 1;
        $this->clientChange('click', $htmlOptions);
        
        if (array_key_exists('uncheckValue', $htmlOptions)) {
            $uncheck = $htmlOptions ['uncheckValue'];
            unset($htmlOptions ['uncheckValue']);
        } else
            $uncheck = null;
        
        if ($uncheck !== null) {
            // add a hidden field so that if the radio button is not selected, it still submits a value
            if (isset($htmlOptions ['id']) && $htmlOptions ['id'] !== false)
                $uncheckOptions = array (
                    
                        'id' => $this->ID_PREFIX . $htmlOptions ['id'] 
                );
            else
                $uncheckOptions = array (
                    
                        'id' => false 
                );
            $hidden = $this->hiddenField($name, $uncheck, $uncheckOptions);
        } else
            $hidden = '';
        
     // add a hidden field so that if the radio button is not selected, it still submits a value
        return $hidden . $this->inputField('radio', $name, $value, $htmlOptions);
    }
    
    /**
     * Generates a check box.
     * @param string $name the input name
     * @param boolean $checked whether the check box is checked
     * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
     * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
     * Since version 1.1.2, a special option named 'uncheckValue' is available that can be used to specify
     * the value returned when the checkbox is not checked. When set, a hidden field is rendered so that
     * when the checkbox is not checked, we can still obtain the posted uncheck value.
     * If 'uncheckValue' is not set or set to NULL, the hidden field will not be rendered.
     * @return string the generated check box
     * @see clientChange
     * @see inputField
     */
    public function checkBox($name, $checked = false, $htmlOptions = array()){
        if ($checked)
            $htmlOptions ['checked'] = 'checked';
        else
            unset($htmlOptions ['checked']);
        $value = isset($htmlOptions ['value']) ? $htmlOptions ['value'] : 1;
        $this->clientChange('click', $htmlOptions);
        
        if (array_key_exists('uncheckValue', $htmlOptions)) {
            $uncheck = $htmlOptions ['uncheckValue'];
            unset($htmlOptions ['uncheckValue']);
        } else
            $uncheck = null;
        
        if ($uncheck !== null) {
            // add a hidden field so that if the radio button is not selected, it still submits a value
            if (isset($htmlOptions ['id']) && $htmlOptions ['id'] !== false)
                $uncheckOptions = array (
                    
                        'id' => $this->ID_PREFIX . $htmlOptions ['id'] 
                );
            else
                $uncheckOptions = array (
                    
                        'id' => false 
                );
            $hidden = $this->hiddenField($name, $uncheck, $uncheckOptions);
        } else
            $hidden = '';
        
     // add a hidden field so that if the checkbox  is not selected, it still submits a value
        return $hidden . $this->inputField('checkbox', $name, $value, $htmlOptions);
    }
    
    /**
     * Generates a drop down list.
     * @param string $name the input name
     * @param string $select the selected value
     * @param array $data data for generating the list options (value=>display).
     * You may use {@link listData} to generate this data.
     * Please refer to {@link listOptions} on how this data is used to generate the list options.
     * Note, the values and labels will be automatically HTML-encoded by this method.
     * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
     * attributes are recognized. See {@link clientChange} and {@link tag} for more details.
     * In addition, the following options are also supported specifically for dropdown list:
     * <ul>
     * <li>encode: boolean, specifies whether to encode the values. Defaults to true. This option has been available since version 1.0.5.</li>
     * <li>prompt: string, specifies the prompt text shown as the first list option. Its value is empty. Note, the prompt text will NOT be HTML-encoded.</li>
     * <li>empty: string, specifies the text corresponding to empty selection. Its value is empty.
     * Starting from version 1.0.10, the 'empty' option can also be an array of value-label pairs.
     * Each pair will be used to render a list option at the beginning. Note, the text label will NOT be HTML-encoded.</li>
     * <li>options: array, specifies additional attributes for each OPTION tag.
     * The array keys must be the option values, and the array values are the extra
     * OPTION tag attributes in the name-value pairs. For example,
     * <pre>
     * array(
     * 'value1'=>array('disabled'=>true, 'label'=>'value 1'),
     * 'value2'=>array('label'=>'value 2'),
     * );
     * </pre>
     * This option has been available since version 1.0.3.
     * </li>
     * </ul>
     * @return string the generated drop down list
     * @see clientChange
     * @see inputField
     * @see listData
     */
    public function dropDownList($name, $select, $data, $htmlOptions = array()){
        $htmlOptions ['name'] = $name;
        if (! isset($htmlOptions ['id']))
            $htmlOptions ['id'] = $this->getIdByName($name);
        else if ($htmlOptions ['id'] === false)
            unset($htmlOptions ['id']);
        $this->clientChange('change', $htmlOptions);
        $options = "\n" . $this->listOptions($select, $data, $htmlOptions);
        return $this->tag('select', $htmlOptions, $options);
    }
    
    /**
     * Generates a list box.
     * @param string $name the input name
     * @param mixed $select the selected value(s). This can be either a string for single selection or an array for multiple selections.
     * @param array $data data for generating the list options (value=>display)
     * You may use {@link listData} to generate this data.
     * Please refer to {@link listOptions} on how this data is used to generate the list options.
     * Note, the values and labels will be automatically HTML-encoded by this method.
     * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
     * attributes are also recognized. See {@link clientChange} and {@link tag} for more details.
     * In addition, the following options are also supported specifically for list box:
     * <ul>
     * <li>encode: boolean, specifies whether to encode the values. Defaults to true. This option has been available since version 1.0.5.</li>
     * <li>prompt: string, specifies the prompt text shown as the first list option. Its value is empty. Note, the prompt text will NOT be HTML-encoded.</li>
     * <li>empty: string, specifies the text corresponding to empty selection. Its value is empty.
     * Starting from version 1.0.10, the 'empty' option can also be an array of value-label pairs.
     * Each pair will be used to render a list option at the beginning. Note, the text label will NOT be HTML-encoded.</li>
     * <li>options: array, specifies additional attributes for each OPTION tag.
     * The array keys must be the option values, and the array values are the extra
     * OPTION tag attributes in the name-value pairs. For example,
     * <pre>
     * array(
     * 'value1'=>array('disabled'=>true, 'label'=>'value 1'),
     * 'value2'=>array('label'=>'value 2'),
     * );
     * </pre>
     * This option has been available since version 1.0.3.
     * </li>
     * </ul>
     * @return string the generated list box
     * @see clientChange
     * @see inputField
     * @see listData
     */
    public function listBox($name, $select, $data, $htmlOptions = array()){
        if (! isset($htmlOptions ['size']))
            $htmlOptions ['size'] = 4;
        if (isset($htmlOptions ['multiple'])) {
            if (substr($name, - 2) !== '[]')
                $name .= '[]';
        }
        return $this->dropDownList($name, $select, $data, $htmlOptions);
    }
    
    
    /**
     * Generates a radio button list.
     * A radio button list is like a {@link checkBoxList check box list}, except that
     * it only allows single selection.
     * @param string $name name of the radio button list. You can use this name to retrieve
     * the selected value(s) once the form is submitted.
     * @param mixed $select selection of the radio buttons. This can be either a string
     * for single selection or an array for multiple selections.
     * @param array $data value-label pairs used to generate the radio button list.
     * Note, the values will be automatically HTML-encoded, while the labels will not.
     * @param array $htmlOptions addtional HTML options. The options will be applied to
     * each radio button input. The following special options are recognized:
     * <ul>
     * <li>template: string, specifies how each radio button is rendered. Defaults
     * to "{input} {label}", where "{input}" will be replaced by the generated
     * radio button input tag while "{label}" will be replaced by the corresponding radio button label.</li>
     * <li>separator: string, specifies the string that separates the generated radio buttons.</li>
     * <li>labelOptions: array, specifies the additional HTML attributes to be rendered
     * for every label tag in the list. This option has been available since version 1.0.10.</li>
     * </ul>
     * @return string the generated radio button list
     */
    public function radioButtonList($name, $select, $data, $htmlOptions = array()){
        $template = isset($htmlOptions ['template']) ? $htmlOptions ['template'] : '{input} {label}';
        $separator = isset($htmlOptions ['separator']) ? $htmlOptions ['separator'] : "<br/>\n";
        unset($htmlOptions ['template'], $htmlOptions ['separator']);
        
        $labelOptions = isset($htmlOptions ['labelOptions']) ? $htmlOptions ['labelOptions'] : array ();
        unset($htmlOptions ['labelOptions']);
        
        $items = array ();
        $baseID = $this->getIdByName($name);
        $id = 0;
        foreach ( $data as $value => $label ) {
            $checked = ! strcmp($value, $select);
            $htmlOptions ['value'] = $value;
            $htmlOptions ['id'] = $baseID . '_' . $id ++;
            $option = $this->radioButton($name, $checked, $htmlOptions);
            $label = $this->label($label, $htmlOptions ['id'], $labelOptions);
            $items [] = strtr($template, array (
                
                    '{input}' => $option, 
                    '{label}' => $label 
            ));
        }
        return implode($separator, $items);
    }
    
    /**
     * Generates a link that can initiate AJAX requests.
     * @param string $text the link body (it will NOT be HTML-encoded.)
     * @param mixed $url the URL for the AJAX request. If empty, it is assumed to be the current URL. See {@link normalizeUrl} for more details.
     * @param array $ajaxOptions AJAX options (see {@link ajax})
     * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
     * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
     * @return string the generated link
     * @see normalizeUrl
     * @see ajax
     */
    public function ajaxLink($text, $url, $ajaxOptions = array(), $htmlOptions = array()){
        if (! isset($htmlOptions ['href']))
            $htmlOptions ['href'] = '#';
        $ajaxOptions ['url'] = $url;
        $htmlOptions ['ajax'] = $ajaxOptions;
        $this->clientChange('click', $htmlOptions);
        return $this->tag('a', $htmlOptions, $text);
    }
    
    /**
     * Generates a push button that can initiate AJAX requests.
     * @param string $label the button label
     * @param mixed $url the URL for the AJAX request. If empty, it is assumed to be the current URL. See {@link normalizeUrl} for more details.
     * @param array $ajaxOptions AJAX options (see {@link ajax})
     * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
     * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
     * @return string the generated button
     */
    public function ajaxButton($label, $url, $ajaxOptions = array(), $htmlOptions = array()){
        $ajaxOptions ['url'] = $url;
        $htmlOptions ['ajax'] = $ajaxOptions;
        return $this->button($label, $htmlOptions);
    }
    
    /**
     * Generates a push button that can submit the current form in POST method.
     * @param string $label the button label
     * @param mixed $url the URL for the AJAX request. If empty, it is assumed to be the current URL. See {@link normalizeUrl} for more details.
     * @param array $ajaxOptions AJAX options (see {@link ajax})
     * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
     * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
     * @return string the generated button
     */
    public function ajaxSubmitButton($label, $url, $ajaxOptions = array(), $htmlOptions = array()){
        $ajaxOptions ['type'] = 'POST';
        $htmlOptions ['type'] = 'submit';
        return $this->ajaxButton($label, $url, $ajaxOptions, $htmlOptions);
    }
    
    /**
     * Generates an input HTML tag.
     * This method generates an input HTML tag based on the given input name and value.
     * @param string $type the input type (e.g. 'text', 'radio')
     * @param string $name the input name
     * @param string $value the input value
     * @param array $htmlOptions additional HTML attributes for the HTML tag (see {@link tag}).
     * @return string the generated input tag
     */
    protected function inputField($type, $name, $value, $htmlOptions){
        $htmlOptions ['type'] = $type;
        $htmlOptions ['value'] = $value;
        $htmlOptions ['name'] = $name;
        if (! isset($htmlOptions ['id']))
            $htmlOptions ['id'] = $this->getIdByName($name);
        else if ($htmlOptions ['id'] === false)
            unset($htmlOptions ['id']);
        return $this->tag('input', $htmlOptions);
    }
    
    /**
     * Generates a label tag for a model attribute.
     * The label text is the attribute label and the label is associated with
     * the input for the attribute (see {@link CModel::getAttributeLabel}.
     * If the attribute has input error, the label's CSS class will be appended with {@link errorCss}.
     * @param CModel $model the data model
     * @param string $attribute the attribute
     * @param array $htmlOptions additional HTML attributes. The following special options are recognized:
     * <ul>
     * <li>required: if this is set and is true, the label will be styled
     * with CSS class 'required' (customizable with CHtml::$requiredCss),
     * and be decorated with {@link CHtml::beforeRequiredLabel} and
     * {@link CHtml::afterRequiredLabel}. This option has been available since version 1.0.2.</li>
     * <li>label: this specifies the label to be displayed. If this is not set,
     * {@link CModel::getAttributeLabel} will be called to get the label for display.
     * If the label is specified as false, no label will be rendered.
     * This option has been available since version 1.0.4.</li>
     * </ul>
     * @return string the generated label tag
     */
    public function activeLabel($model, $attribute, $htmlOptions = array()){
        if (isset($htmlOptions ['for'])) {
            $for = $htmlOptions ['for'];
            unset($htmlOptions ['for']);
        } else
            $for = $this->getIdByName($this->resolveName($model, $attribute));
        if (isset($htmlOptions ['label'])) {
            if (($label = $htmlOptions ['label']) === false)
                return '';
            unset($htmlOptions ['label']);
        } else
            $label = $model->getAttributeLabel($attribute);
        if ($model->hasErrors($attribute))
            $this->addErrorCss($htmlOptions);
        return $this->label($label, $for, $htmlOptions);
    }
    
    /**
     * Generates a label tag for a model attribute.
     * This is an enhanced version of {@link activeLabel}. It will render additional
     * CSS class and mark when the attribute is required.
     * In particular, it calls {@link CModel::isAttributeRequired} to determine
     * if the attribute is required.
     * If so, it will add a CSS class {@link CHtml::requiredCss} to the label,
     * and decorate the label with {@link CHtml::beforeRequiredLabel} and
     * {@link CHtml::afterRequiredLabel}.
     * @param CModel $model the data model
     * @param string $attribute the attribute
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated label tag
     * @since 1.0.2
     */
    public function activeLabelEx($model, $attribute, $htmlOptions = array()){
        $realAttribute = $attribute;
        $this->resolveName($model, $attribute); // strip off square brackets if any
        $htmlOptions ['required'] = $model->isAttributeRequired($attribute);
        return $this->activeLabel($model, $realAttribute, $htmlOptions);
    }
    
    /**
     * Generates a text field input for a model attribute.
     * If the attribute has input error, the input field's CSS class will
     * be appended with {@link errorCss}.
     * @param CModel $model the data model
     * @param string $attribute the attribute
     * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
     * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
     * @return string the generated input field
     * @see clientChange
     * @see activeInputField
     */
    public function activeTextField($model, $attribute, $htmlOptions = array()){
        $this->resolveNameID($model, $attribute, $htmlOptions);
        $this->clientChange('change', $htmlOptions);
        return $this->activeInputField('text', $model, $attribute, $htmlOptions);
    }
    
    /**
     * Generates a hidden input for a model attribute.
     * @param CModel $model the data model
     * @param string $attribute the attribute
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated input field
     * @see activeInputField
     */
    public function activeHiddenField($model, $attribute, $htmlOptions = array()){
        $this->resolveNameID($model, $attribute, $htmlOptions);
        return $this->activeInputField('hidden', $model, $attribute, $htmlOptions);
    }
    
    /**
     * Generates a password field input for a model attribute.
     * If the attribute has input error, the input field's CSS class will
     * be appended with {@link errorCss}.
     * @param CModel $model the data model
     * @param string $attribute the attribute
     * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
     * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
     * @return string the generated input field
     * @see clientChange
     * @see activeInputField
     */
    public function activePasswordField($model, $attribute, $htmlOptions = array()){
        $this->resolveNameID($model, $attribute, $htmlOptions);
        $this->clientChange('change', $htmlOptions);
        return $this->activeInputField('password', $model, $attribute, $htmlOptions);
    }
    
    /**
     * Generates a text area input for a model attribute.
     * If the attribute has input error, the input field's CSS class will
     * be appended with {@link errorCss}.
     * @param CModel $model the data model
     * @param string $attribute the attribute
     * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
     * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
     * @return string the generated text area
     * @see clientChange
     */
    public function activeTextArea($model, $attribute, $htmlOptions = array()){
        $this->resolveNameID($model, $attribute, $htmlOptions);
        $this->clientChange('change', $htmlOptions);
        if ($model->hasErrors($attribute))
            $this->addErrorCss($htmlOptions);
        $text = $this->resolveValue($model, $attribute);
        return $this->tag('textarea', $htmlOptions, isset($htmlOptions ['encode']) && ! $htmlOptions ['encode'] ? $text : $this->encode($text));
    }
    
    /**
     * Generates a file input for a model attribute.
     * Note, you have to set the enclosing form's 'enctype' attribute to be 'multipart/form-data'.
     * After the form is submitted, the uploaded file information can be obtained via $_FILES (see
     * PHP documentation).
     * @param CModel $model the data model
     * @param string $attribute the attribute
     * @param array $htmlOptions additional HTML attributes (see {@link tag}).
     * @return string the generated input field
     * @see activeInputField
     */
    public function activeFileField($model, $attribute, $htmlOptions = array()){
        $this->resolveNameID($model, $attribute, $htmlOptions);
        // add a hidden field so that if a model only has a file field, we can
        // still use isset($_POST[$modelClass]) to detect if the input is submitted
        $hiddenOptions = isset($htmlOptions ['id']) ? array (
            
                'id' => $this->ID_PREFIX . $htmlOptions ['id'] 
        ) : array (
            
                'id' => false 
        );
        return $this->hiddenField($htmlOptions ['name'], '', $hiddenOptions) . $this->activeInputField('file', $model, $attribute, $htmlOptions);
    }
    
    /**
     * Generates a radio button for a model attribute.
     * If the attribute has input error, the input field's CSS class will
     * be appended with {@link errorCss}.
     * @param CModel $model the data model
     * @param string $attribute the attribute
     * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
     * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
     * Since version 1.0.9, a special option named 'uncheckValue' is available that can be used to specify
     * the value returned when the radio button is not checked. By default, this value is '0'.
     * Internally, a hidden field is rendered so that when the radio button is not checked,
     * we can still obtain the posted uncheck value.
     * If 'uncheckValue' is set as NULL, the hidden field will not be rendered.
     * @return string the generated radio button
     * @see clientChange
     * @see activeInputField
     */
    public function activeRadioButton($model, $attribute, $htmlOptions = array()){
        $this->resolveNameID($model, $attribute, $htmlOptions);
        if (! isset($htmlOptions ['value']))
            $htmlOptions ['value'] = 1;
        if (! isset($htmlOptions ['checked']) && $this->resolveValue($model, $attribute) == $htmlOptions ['value'])
            $htmlOptions ['checked'] = 'checked';
        $this->clientChange('click', $htmlOptions);
        
        if (array_key_exists('uncheckValue', $htmlOptions)) {
            $uncheck = $htmlOptions ['uncheckValue'];
            unset($htmlOptions ['uncheckValue']);
        } else
            $uncheck = '0';
        
        $hiddenOptions = isset($htmlOptions ['id']) ? array (
            
                'id' => $this->ID_PREFIX . $htmlOptions ['id'] 
        ) : array (
            
                'id' => false 
        );
        $hidden = $uncheck !== null ? $this->hiddenField($htmlOptions ['name'], $uncheck, $hiddenOptions) : '';
        
        // add a hidden field so that if the radio button is not selected, it still submits a value
        return $hidden . $this->activeInputField('radio', $model, $attribute, $htmlOptions);
    }
    
    /**
     * Generates a check box for a model attribute.
     * The attribute is assumed to take either true or false value.
     * If the attribute has input error, the input field's CSS class will
     * be appended with {@link errorCss}.
     * @param CModel $model the data model
     * @param string $attribute the attribute
     * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
     * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
     * Since version 1.0.2, a special option named 'uncheckValue' is available that can be used to specify
     * the value returned when the checkbox is not checked. By default, this value is '0'.
     * Internally, a hidden field is rendered so that when the checkbox is not checked,
     * we can still obtain the posted uncheck value.
     * If 'uncheckValue' is set as NULL, the hidden field will not be rendered.
     * @return string the generated check box
     * @see clientChange
     * @see activeInputField
     */
    public function activeCheckBox($model, $attribute, $htmlOptions = array()){
        $this->resolveNameID($model, $attribute, $htmlOptions);
        if (! isset($htmlOptions ['value']))
            $htmlOptions ['value'] = 1;
        if (! isset($htmlOptions ['checked']) && $this->resolveValue($model, $attribute) == $htmlOptions ['value'])
            $htmlOptions ['checked'] = 'checked';
        $this->clientChange('click', $htmlOptions);
        
        if (array_key_exists('uncheckValue', $htmlOptions)) {
            $uncheck = $htmlOptions ['uncheckValue'];
            unset($htmlOptions ['uncheckValue']);
        } else
            $uncheck = '0';
        
        $hiddenOptions = isset($htmlOptions ['id']) ? array (
            
                'id' => $this->ID_PREFIX . $htmlOptions ['id'] 
        ) : array (
            
                'id' => false 
        );
        $hidden = $uncheck !== null ? $this->hiddenField($htmlOptions ['name'], $uncheck, $hiddenOptions) : '';
        
        return $hidden . $this->activeInputField('checkbox', $model, $attribute, $htmlOptions);
    }
    
    /**
     * Generates a drop down list for a model attribute.
     * If the attribute has input error, the input field's CSS class will
     * be appended with {@link errorCss}.
     * @param CModel $model the data model
     * @param string $attribute the attribute
     * @param array $data data for generating the list options (value=>display)
     * You may use {@link listData} to generate this data.
     * Please refer to {@link listOptions} on how this data is used to generate the list options.
     * Note, the values and labels will be automatically HTML-encoded by this method.
     * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
     * attributes are recognized. See {@link clientChange} and {@link tag} for more details.
     * In addition, the following options are also supported:
     * <ul>
     * <li>encode: boolean, specifies whether to encode the values. Defaults to true. This option has been available since version 1.0.5.</li>
     * <li>prompt: string, specifies the prompt text shown as the first list option. Its value is empty.  Note, the prompt text will NOT be HTML-encoded.</li>
     * <li>empty: string, specifies the text corresponding to empty selection. Its value is empty.
     * Starting from version 1.0.10, the 'empty' option can also be an array of value-label pairs.
     * Each pair will be used to render a list option at the beginning. Note, the text label will NOT be HTML-encoded.</li>
     * <li>options: array, specifies additional attributes for each OPTION tag.
     * The array keys must be the option values, and the array values are the extra
     * OPTION tag attributes in the name-value pairs. For example,
     * <pre>
     * array(
     * 'value1'=>array('disabled'=>true, 'label'=>'value 1'),
     * 'value2'=>array('label'=>'value 2'),
     * );
     * </pre>
     * This option has been available since version 1.0.3.
     * </li>
     * </ul>
     * @return string the generated drop down list
     * @see clientChange
     * @see listData
     */
    public function activeDropDownList($model, $attribute, $data, $htmlOptions = array()){
        $this->resolveNameID($model, $attribute, $htmlOptions);
        $selection = $this->resolveValue($model, $attribute);
        $options = "\n" . $this->listOptions($selection, $data, $htmlOptions);
        $this->clientChange('change', $htmlOptions);
        if ($model->hasErrors($attribute))
            $this->addErrorCss($htmlOptions);
        if (isset($htmlOptions ['multiple'])) {
            if (substr($htmlOptions ['name'], - 2) !== '[]')
                $htmlOptions ['name'] .= '[]';
        }
        return $this->tag('select', $htmlOptions, $options);
    }
    
    /**
     * Generates a list box for a model attribute.
     * The model attribute value is used as the selection.
     * If the attribute has input error, the input field's CSS class will
     * be appended with {@link errorCss}.
     * @param CModel $model the data model
     * @param string $attribute the attribute
     * @param array $data data for generating the list options (value=>display)
     * You may use {@link listData} to generate this data.
     * Please refer to {@link listOptions} on how this data is used to generate the list options.
     * Note, the values and labels will be automatically HTML-encoded by this method.
     * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
     * attributes are recognized. See {@link clientChange} and {@link tag} for more details.
     * In addition, the following options are also supported:
     * <ul>
     * <li>encode: boolean, specifies whether to encode the values. Defaults to true. This option has been available since version 1.0.5.</li>
     * <li>prompt: string, specifies the prompt text shown as the first list option. Its value is empty. Note, the prompt text will NOT be HTML-encoded.</li>
     * <li>empty: string, specifies the text corresponding to empty selection. Its value is empty.
     * Starting from version 1.0.10, the 'empty' option can also be an array of value-label pairs.
     * Each pair will be used to render a list option at the beginning. Note, the text label will NOT be HTML-encoded.</li>
     * <li>options: array, specifies additional attributes for each OPTION tag.
     * The array keys must be the option values, and the array values are the extra
     * OPTION tag attributes in the name-value pairs. For example,
     * <pre>
     * array(
     * 'value1'=>array('disabled'=>true, 'label'=>'value 1'),
     * 'value2'=>array('label'=>'value 2'),
     * );
     * </pre>
     * This option has been available since version 1.0.3.
     * </li>
     * </ul>
     * @return string the generated list box
     * @see clientChange
     * @see listData
     */
    public function activeListBox($model, $attribute, $data, $htmlOptions = array()){
        if (! isset($htmlOptions ['size']))
            $htmlOptions ['size'] = 4;
        return $this->activeDropDownList($model, $attribute, $data, $htmlOptions);
    }
    
    /**
     * Generates a check box list for a model attribute.
     * The model attribute value is used as the selection.
     * If the attribute has input error, the input field's CSS class will
     * be appended with {@link errorCss}.
     * Note that a check box list allows multiple selection, like {@link listBox}.
     * As a result, the corresponding POST value is an array. In case no selection
     * is made, the corresponding POST value is an empty string.
     * @param CModel $model the data model
     * @param string $attribute the attribute
     * @param array $data value-label pairs used to generate the check box list.
     * Note, the values will be automatically HTML-encoded, while the labels will not.
     * @param array $htmlOptions addtional HTML options. The options will be applied to
     * each checkbox input. The following special options are recognized:
     * <ul>
     * <li>template: string, specifies how each checkbox is rendered. Defaults
     * to "{input} {label}", where "{input}" will be replaced by the generated
     * check box input tag while "{label}" will be replaced by the corresponding check box label.</li>
     * <li>separator: string, specifies the string that separates the generated check boxes.</li>
     * <li>checkAll: string, specifies the label for the "check all" checkbox.
     * If this option is specified, a 'check all' checkbox will be displayed. Clicking on
     * this checkbox will cause all checkboxes checked or unchecked. This option has been
     * available since version 1.0.4.</li>
     * <li>checkAllLast: boolean, specifies whether the 'check all' checkbox should be
     * displayed at the end of the checkbox list. If this option is not set (default)
     * or is false, the 'check all' checkbox will be displayed at the beginning of
     * the checkbox list. This option has been available since version 1.0.4.</li>
     * <li>encode: boolean, specifies whether to encode HTML-encode tag attributes and values. Defaults to true.
     * This option has been available since version 1.0.5.</li>
     * </ul>
     * @return string the generated check box list
     * @see checkBoxList
     */
    public function activeCheckBoxList($model, $attribute, $data, $htmlOptions = array()){
        $this->resolveNameID($model, $attribute, $htmlOptions);
        $selection = $this->resolveValue($model, $attribute);
        if ($model->hasErrors($attribute))
            $this->addErrorCss($htmlOptions);
        $name = $htmlOptions ['name'];
        unset($htmlOptions ['name']);
        
        $hiddenOptions = isset($htmlOptions ['id']) ? array (
            
                'id' => $this->ID_PREFIX . $htmlOptions ['id'] 
        ) : array (
            
                'id' => false 
        );
        return $this->hiddenField($name, '', $hiddenOptions) . $this->checkBoxList($name, $selection, $data, $htmlOptions);
    }
    
    /**
     * Generates a radio button list for a model attribute.
     * The model attribute value is used as the selection.
     * If the attribute has input error, the input field's CSS class will
     * be appended with {@link errorCss}.
     * @param CModel $model the data model
     * @param string $attribute the attribute
     * @param array $data value-label pairs used to generate the radio button list.
     * Note, the values will be automatically HTML-encoded, while the labels will not.
     * @param array $htmlOptions addtional HTML options. The options will be applied to
     * each radio button input. The following special options are recognized:
     * <ul>
     * <li>template: string, specifies how each radio button is rendered. Defaults
     * to "{input} {label}", where "{input}" will be replaced by the generated
     * radio button input tag while "{label}" will be replaced by the corresponding radio button label.</li>
     * <li>separator: string, specifies the string that separates the generated radio buttons.</li>
     * <li>encode: boolean, specifies whether to encode HTML-encode tag attributes and values. Defaults to true.
     * This option has been available since version 1.0.5.</li>
     * </ul>
     * @return string the generated radio button list
     * @see radioButtonList
     */
    public function activeRadioButtonList($model, $attribute, $data, $htmlOptions = array()){
        $this->resolveNameID($model, $attribute, $htmlOptions);
        $selection = $this->resolveValue($model, $attribute);
        if ($model->hasErrors($attribute))
            $this->addErrorCss($htmlOptions);
        $name = $htmlOptions ['name'];
        unset($htmlOptions ['name']);
        
        $hiddenOptions = isset($htmlOptions ['id']) ? array (
            
                'id' => $this->ID_PREFIX . $htmlOptions ['id'] 
        ) : array (
            
                'id' => false 
        );
        return $this->hiddenField($name, '', $hiddenOptions) . $this->radioButtonList($name, $selection, $data, $htmlOptions);
    }
    
    /**
     * Displays the first validation error for a model attribute.
     * @param CModel $model the data model
     * @param string $attribute the attribute name
     * @param array $htmlOptions additional HTML attributes to be rendered in the container div tag.
     * This parameter has been available since version 1.0.7.
     * @return string the error display. Empty if no errors are found.
     * @see CModel::getErrors
     * @see errorMessageCss
     */
    public function error($model, $attribute, $htmlOptions = array()){
        $error = $model->getError($attribute);
        if ($error != '') {
            if (! isset($htmlOptions ['class']))
                $htmlOptions ['class'] = $this->errorMessageCss;
            return $this->tag('div', $htmlOptions, $error);
        } else
            return '';
    }
    
    /**
     * Generates the data suitable for list-based HTML elements.
     * The generated data can be used in {@link dropDownList}, {@link listBox}, {@link checkBoxList},
     * {@link radioButtonList}, and their active-versions (such as {@link activeDropDownList}).
     * Note, this method does not HTML-encode the generated data. You may call {@link encodeArray} to
     * encode it if needed.
     * Please refer to the {@link value} method on how to specify value field, text field and group field.
     * @param array $models a list of model objects. Starting from version 1.0.3, this parameter
     * can also be an array of associative arrays (e.g. results of {@link CDbCommand::queryAll}).
     * @param string $valueField the attribute name for list option values
     * @param string $textField the attribute name for list option texts
     * @param string $groupField the attribute name for list option group names. If empty, no group will be generated.
     * @return array the list data that can be used in {@link dropDownList}, {@link listBox}, etc.
     */
    public function listData($models, $valueField, $textField, $groupField = ''){
        $listData = array ();
        if ($groupField === '') {
            foreach ( $models as $model ) {
                $value = $this->value($model, $valueField);
                $text = $this->value($model, $textField);
                $listData [$value] = $text;
            }
        } else {
            foreach ( $models as $model ) {
                $group = $this->value($model, $groupField);
                $value = $this->value($model, $valueField);
                $text = $this->value($model, $textField);
                $listData [$group] [$value] = $text;
            }
        }
        return $listData;
    }
    
    /**
     * Evaluates the value of the specified attribute for the given model.
     * The attribute name can be given in a dot syntax. For example, if the attribute
     * is "author.firstName", this method will return the value of "$model->author->firstName".
     * A default value (passed as the last parameter) will be returned if the attribute does
     * not exist or is broken in the middle (e.g. $model->author is null).
     * The model can be either an object or an array. If the latter, the attribute is treated
     * as a key of the array. For the example of "author.firstName", if would mean the array value
     * "$model['author']['firstName']".
     * @param mixed $model the model. This can be either an object or an array.
     * @param string $attribute the attribute name (use dot to concatenate multiple attributes)
     * @param mixed $defaultValue the default value to return when the attribute does not exist
     * @return mixed the attribute value
     * @since 1.0.5
     */
    public function value($model, $attribute, $defaultValue = null){
        foreach ( explode('.', $attribute) as $name ) {
            if (is_object($model))
                $model = $model->$name;
            else if (is_array($model) && isset($model [$name]))
                $model = $model [$name];
            else
                return $defaultValue;
        }
        return $model;
    }
    
    /**
     * Generates a valid HTML ID based on name.
     * @param string $name name from which to generate HTML ID
     * @return string the ID generated based on name.
     */
    public function getIdByName($name){
        return $name;
        /*
        return str_replace(array (
                '[]', 
                '][', 
                '[', 
                ']' 
        ), array (
                '', 
                '_', 
                '_', 
                '' 
        ), $name);
        */
    }
    
    /**
     * Generates input field ID for a model attribute.
     * @param CModel $model the data model
     * @param string $attribute the attribute
     * @return string the generated input field ID
     * @since 1.0.1
     */
    public function activeId($model, $attribute){
        return $this->getIdByName($this->activeName($model, $attribute));
    }
    
    /**
     * Generates input field name for a model attribute.
     * Unlike {@link resolveName}, this method does NOT modify the attribute name.
     * @param CModel $model the data model
     * @param string $attribute the attribute
     * @return string the generated input field name
     * @since 1.0.1
     */
    public function activeName($model, $attribute){
        $a = $attribute; // because the attribute name may be changed by resolveName
        return $this->resolveName($model, $a);
    }
    
    /**
     * Generates the list options.
     * @param mixed $selection the selected value(s). This can be either a string for single selection or an array for multiple selections.
     * @param array $listData the option data (see {@link listData})
     * @param array $htmlOptions additional HTML attributes. The following two special attributes are recognized:
     * <ul>
     * <li>encode: boolean, specifies whether to encode the values. Defaults to true. This option has been available since version 1.0.5.</li>
     * <li>prompt: string, specifies the prompt text shown as the first list option. Its value is empty. Note, the prompt text will NOT be HTML-encoded.</li>
     * <li>empty: string, specifies the text corresponding to empty selection. Its value is empty.
     * Starting from version 1.0.10, the 'empty' option can also be an array of value-label pairs.
     * Each pair will be used to render a list option at the beginning. Note, the text label will NOT be HTML-encoded.</li>
     * <li>options: array, specifies additional attributes for each OPTION tag.
     * The array keys must be the option values, and the array values are the extra
     * OPTION tag attributes in the name-value pairs. For example,
     * <pre>
     * array(
     * 'value1'=>array('disabled'=>true, 'label'=>'value 1'),
     * 'value2'=>array('label'=>'value 2'),
     * );
     * </pre>
     * This option has been available since version 1.0.3.
     * </li>
     * <li>key: string, specifies the name of key attribute of the selection object(s).
     * This is used when the selection is represented in terms of objects. In this case,
     * the property named by the key option of the objects will be treated as the actual selection value.
     * This option defaults to 'primaryKey', meaning using the 'primaryKey' property value of the objects in the selection.
     * This option has been available since version 1.1.3.</li>
     * </ul>
     * @return string the generated list options
     */
    public function listOptions($selection, $listData, &$htmlOptions){
        $raw = isset($htmlOptions ['encode']) && ! $htmlOptions ['encode'];
        $content = '';
        if (isset($htmlOptions ['prompt'])) {
            $content .= '<option value="">' . strtr($htmlOptions ['prompt'], array (
                
                    '<' => '&lt;', 
                    '>' => '&gt;' 
            )) . "</option>\n";
            unset($htmlOptions ['prompt']);
        }
        if (isset($htmlOptions ['empty'])) {
            if (! is_array($htmlOptions ['empty']))
                $htmlOptions ['empty'] = array (
                    
                        '' => $htmlOptions ['empty'] 
                );
            foreach ( $htmlOptions ['empty'] as $value => $label )
                $content .= '<option value="' . $this->encode($value) . '">' . strtr($label, array (
                    
                        '<' => '&lt;', 
                        '>' => '&gt;' 
                )) . "</option>\n";
            unset($htmlOptions ['empty']);
        }
        
        if (isset($htmlOptions ['options'])) {
            $options = $htmlOptions ['options'];
            unset($htmlOptions ['options']);
        } else
            $options = array ();
        
        $key = isset($htmlOptions ['key']) ? $htmlOptions ['key'] : 'primaryKey';
        if (is_array($selection)) {
            foreach ( $selection as $i => $item ) {
                if (is_object($item))
                    $selection [$i] = $item->$key;
            }
        } else if (is_object($selection))
            $selection = $selection->$key;
        
        foreach ( $listData as $key => $value ) {
            if (is_array($value)) {
                $content .= '<optgroup label="' . ($raw ? $key : $this->encode($key)) . "\">\n";
                $dummy = array (
                    
                        'options' => $options 
                );
                if (isset($htmlOptions ['encode']))
                    $dummy ['encode'] = $htmlOptions ['encode'];
                $content .= $this->listOptions($selection, $value, $dummy);
                $content .= '</optgroup>' . "\n";
            } else {
                $attributes = array (
                    
                        'value' => ( string ) $key, 
                        'encode' => ! $raw 
                );
                if (! is_array($selection) && ! strcmp($key, $selection) || is_array($selection) && in_array($key, $selection))
                    $attributes ['selected'] = 'selected';
                if (isset($options [$key]))
                    $attributes = array_merge($attributes, $options [$key]);
                $content .= $this->tag('option', $attributes, $raw ? ( string ) $value : $this->encode(( string ) $value)) . "\n";
            }
        }
        
        unset($htmlOptions ['key']);
        
        return $content;
    }
    
    /**
     * Generates input name and ID for a model attribute.
     * This method will update the HTML options by setting appropriate 'name' and 'id' attributes.
     * This method may also modify the attribute name if the name
     * contains square brackets (mainly used in tabular input).
     * @param CModel $model the data model
     * @param string $attribute the attribute
     * @param array $htmlOptions the HTML options
     */
    public function resolveNameID($model, &$attribute, &$htmlOptions){
        if (! isset($htmlOptions ['name']))
            $htmlOptions ['name'] = $this->resolveName($model, $attribute);
        if (! isset($htmlOptions ['id']))
            $htmlOptions ['id'] = $this->getIdByName($htmlOptions ['name']);
        else if ($htmlOptions ['id'] === false)
            unset($htmlOptions ['id']);
    }
    
    /**
     * Generates input name for a model attribute.
     * Note, the attribute name may be modified after calling this method if the name
     * contains square brackets (mainly used in tabular input) before the real attribute name.
     * @param CModel $model the data model
     * @param string $attribute the attribute
     * @return string the input name
     * @since 1.0.2
     */
    public function resolveName($model, &$attribute){
        if (($pos = strpos($attribute, '[')) !== false) {
            if ($pos !== 0) // e.g. name[a][b]
                return get_class($model) . '[' . substr($attribute, 0, $pos) . ']' . substr($attribute, $pos);
            if (($pos = strrpos($attribute, ']')) !== false && $pos !== strlen($attribute) - 1) // e.g. [a][b]name
{
                $sub = substr($attribute, 0, $pos + 1);
                $attribute = substr($attribute, $pos + 1);
                return get_class($model) . $sub . '[' . $attribute . ']';
            }
            if (preg_match('/\](\w+\[.*)$/', $attribute, $matches)) {
                $name = get_class($model) . '[' . str_replace(']', '][', trim(strtr($attribute, array (
                    
                        '][' => ']', 
                        '[' => ']' 
                )), ']')) . ']';
                $attribute = $matches [1];
                return $name;
            }
        } else
            return get_class($model) . '[' . $attribute . ']';
    }
    
    /**
     * Evaluates the attribute value of the model.
     * This method can recognize the attribute name written in array format.
     * For example, if the attribute name is 'name[a][b]', the value "$model->name['a']['b']" will be returned.
     * @param CModel $model the data model
     * @param string $attribute the attribute name
     * @return mixed the attribute value
     * @since 1.1.3
     */
    public function resolveValue($model, $attribute){
        if (($pos = strpos($attribute, '[')) !== false) {
            $name = substr($attribute, 0, $pos);
            $value = $model->$name;
            foreach ( explode('][', rtrim(substr($attribute, $pos + 1), ']')) as $id ) {
                if (is_array($value) && isset($value [$id]))
                    $value = $value [$id];
                else
                    return null;
            }
            return $value;
        } else
            return $model->$attribute;
    }
    
    /**
     * Appends {@link errorCss} to the 'class' attribute.
     * @param array $htmlOptions HTML options to be modified
     */
    protected function addErrorCss(&$htmlOptions){
        if (isset($htmlOptions ['class']))
            $htmlOptions ['class'] .= ' ' . $this->errorCss;
        else
            $htmlOptions ['class'] = $this->errorCss;
    }
    
    /**
     * Renders the HTML tag attributes.
     * Since version 1.1.5, attributes whose value is null will not be rendered.
     * Special attributes, such as 'checked', 'disabled', 'readonly', will be rendered
     * properly based on their corresponding boolean value.
     * @param array $htmlOptions attributes to be rendered
     * @return string the rendering result
     * @since 1.0.5
     */
    public function renderAttributes($htmlOptions){
        $specialAttributes = array (
            
                'checked' => 1, 
                'declare' => 1, 
                'defer' => 1, 
                'disabled' => 1, 
                'ismap' => 1, 
                'multiple' => 1, 
                'nohref' => 1, 
                'noresize' => 1, 
                'readonly' => 1, 
                'selected' => 1 
        );
        
        if ($htmlOptions === array ())
            return '';
        
        $html = '';
        if (isset($htmlOptions ['encode'])) {
            $raw = ! $htmlOptions ['encode'];
            unset($htmlOptions ['encode']);
        } else
            $raw = false;
        
        if ($raw) {
            foreach ( $htmlOptions as $name => $value ) {
                if (isset($specialAttributes [$name])) {
                    if ($value)
                        $html .= ' ' . $name . '="' . $name . '"';
                } else if ($value !== null)
                    $html .= ' ' . $name . '="' . $value . '"';
            }
        } else {
            foreach ( $htmlOptions as $name => $value ) {
                if (isset($specialAttributes [$name])) {
                    if ($value)
                        $html .= ' ' . $name . '="' . $name . '"';
                } else if ($value !== null)
                    $html .= ' ' . $name . '="' . $this->encode($value) . '"';
            }
        }
        return $html;
    }
    
    
    
    /**
     * Generate thead and tfoot tags for table.
     * 
     * $array = array(
     *  'CHECK_ALL', 'Email', 'Name', 'Last Sign in', null
     * );
     */
    public function genTheadTfoot($array, $needTfoot = true)
    {
        if(!is_array($array))
        {
            return false;
        }
        $thsHtml = '';
        foreach ($array as $text)
        {
            $thsHtml .= '<th>'.Revship::l($text).'</th>';
        }
        $html = '<thead><tr>';
        $html .= $thsHtml;
        $html .= '</tr></thead>';
        if($needTfoot)
        {
            $html .= '<tfoot><tr>';
            $html .= $thsHtml;
            $html .= '</tr></tfoot>';
        }
        return $html;
    }
    
}
