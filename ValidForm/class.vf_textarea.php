<?php
/***************************
 * This file is part of ValidForm Builder - build valid and secure web forms quickly
 * <http://code.google.com/p/validformbuilder/>
 * Copyright (c) 2009 Felix Langfeldt
 * 
 * This software is released under the MIT License <http://www.opensource.org/licenses/mit-license.php>
 ***************************/
 
/**
 * VF_Textarea class
 *
 * @package ValidForm
 * @author Felix Langfeldt
 * @version 0.1.1
 */
  
require_once('class.vf_element.php');

class VF_Textarea extends VF_Element {

	public function toHtml($submitted = FALSE, $blnSimpleLayout = FALSE) {
		$blnError = ($submitted && !$this->__validator->validate()) ? TRUE : FALSE;
		
		$strClass = ($this->__validator->getRequired()) ? "vf__required" : "vf__optional";
		$strClass = ($blnError) ? $strClass . " vf__error" : $strClass;		
		$strOutput = "<div class=\"{$strClass}\">\n";
		
		if ($blnError) $strOutput .= "<p class=\"vf__error\">{$this->__validator->getError()}</p>";
				
		$strLabel = (!empty($this->__requiredstyle) && $this->__validator->getRequired()) ? sprintf($this->__requiredstyle, $this->__label) : $this->__label;
		$strOutput .= "<label for=\"{$this->__id}\">{$strLabel}</label>\n";
		$strOutput .= "<textarea name=\"{$this->__name}\" id=\"{$this->__id}\" {$this->__getMetaString()}>{$this->__getValue($submitted)}</textarea>\n";
		if (!empty($this->__tip)) $strOutput .= "<small class=\"vf__tip\">{$this->__tip}</small>\n";
		$strOutput .= "</div>\n";
		
		return $strOutput;
	}
	
	public function toWpHtml($submitted = FALSE, $blnSimpleLayout = FALSE) {
		$strOutput = "<tr>\n<th>\n<label for=\"{$this->__id}\">{$this->__label}</label>\n</th>\n<td>\n";
		
		$strOutput .= "<textarea name=\"{$this->__name}\" id=\"{$this->__id}\" {$this->__getMetaString()}>{$this->__getValue($submitted)}</textarea>\n";
		if (!empty($this->__tip)) $strOutput .= "<br />\n<span class=\"description\">{$this->__tip}</span>\n";
		$strOutput .= "</td>\n</tr>\n";
		
		return $strOutput;
	}
	
	public function toJS() {
		$strCheck = $this->__validator->getCheck();
		$strCheck = (empty($strCheck)) ? "''" : $strCheck;
		$strRequired = ($this->__validator->getRequired()) ? "true" : "false";
		$intMaxLength = ($this->__validator->getMaxLength() > 0) ? $this->__validator->getMaxLength() : "null";
		$intMinLength = ($this->__validator->getMinLength() > 0) ? $this->__validator->getMinLength() : "null";
		$strMaxLengthError = sprintf($this->__validator->getMaxLengthError(), $intMaxLength);
		$strMinLengthError = sprintf($this->__validator->getMinLengthError(), $intMinLength);
		
		return "objForm.addElement('{$this->__id}', '{$this->__name}', {$strCheck}, {$strRequired}, {$intMaxLength}, {$intMinLength}, '" . addslashes($this->__validator->getFieldHint()) . "', '" . addslashes($this->__validator->getTypeError()) . "', '" . addslashes($this->__validator->getRequiredError()) . "', '" . addslashes($this->__validator->getHintError()) . "', '{$strMinLengthError}', '{$strMaxLengthError}');\n";
	}
	
}

?>