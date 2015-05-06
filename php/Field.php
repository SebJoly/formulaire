<?php

namespace formulaire\php;

/**
 * Classe "Field".
 * @author Sébastien JOLY
 * @author Jonathan SANTONI
 */
class Field {
	
	// Attributs du champs
	public $form 	= null;
	public $table 	= null;
	public $label 	= "";
	public $name 	= "";
	public $type 	= "";
	public $defaultValue	= "";
	
	public $primaryKey = "";
	public $required = true;
	public $hidden = false;
	public $disabled = false;
	
	// Constructeur
	function __construct($form, $table, $name, $label, $type, $primaryKey) {
		$this->form = $form;
		$this->table = $table;
		$this->name = $name;
		$this->label = $label;
		$this->type = $type;
		$this->primaryKey = $primaryKey;
	}
	
	/**
	 * Retourne une représentation HTML du champs
	 */ 
	public function showField() {
		
		$hidden = "";
		if ($this->hidden) { $hidden = ' style="display:none;" '; }
		
		$html = '<div class="form-group "'.$hidden.'>';
		$html .= '<label class="'. $this->form->getLabelSize() .' control-label" for="'.$this->name.'">'.$this->label;
		if($this->required) {
			$html .= '<span style="color:red;"> *</span>';
		}
		$html .= '</label>';
		$html .= '<div class="'. $this->form->getInputSize() .'">';
		$html .= $this->showInput();
		$html .= '</div>';
		$html .= '</div>';
		return $html;
	}
	
	/**
	 * Retourne le code HTML de l'input adéquat selon le type du champs
	 */
	public function showInput() {
	
		// (VAR)CHAR
		if (stristr($this->type,"char")) {
			$html = '<input type="text" class="form-control field" id="'.$this->name.'" placeholder="Saisir '.lcfirst($this->label).'" value="'.$this->defaultValue.'"'.$this->showOptions().'/>';
		// [...]TEXT
		}elseif (stristr($this->type,"text")) {
			$html = '<textarea id="'.$this->name.'" placeholder="Saisir '.lcfirst($this->label).'" class="form-control field" rows="3"'.$this->showOptions().'>'.$this->defaultValue.'</textarea>';
		// DATE
		}elseif (stristr($this->type,"date")) {
			$html = '<input type="text" class="form-control field datepicker" id="'.$this->name.'" value="'.$this->defaultValue.'" '.$this->showOptions().'/>';
		// BOOLEAN
		}elseif (stristr($this->type,"boolean") || stristr($this->type,"tinyint")) {
			if ($this->defaultValue === true || $this->defaultValue == 1) {
				$html = '<label class="radio-inline"><input type="radio" class="field" name="'.$this->name.'" id="'.$this->name.'" value="true" checked />Oui</label>';
				$html .= '<label class="radio-inline"><input type="radio" class="field" name="'.$this->name.'" id="'.$this->name.'" value="false" />Non</label>';
			}elseif ($this->defaultValue === false || $this->defaultValue == 0) {
				$html = '<label class="radio-inline"><input type="radio" class="field" name="'.$this->name.'" id="'.$this->name.'" value="true" />Oui</label>';
				$html .= '<label class="radio-inline"><input type="radio" class="field" name="'.$this->name.'" id="'.$this->name.'" value="false" checked />Non</label>';
			}else{
				$html = '<label class="radio-inline"><input type="radio" class="field" name="'.$this->name.'" id="'.$this->name.'" value="true" />Oui</label>';
				$html .= '<label class="radio-inline"><input type="radio" class="field" name="'.$this->name.'" id="'.$this->name.'" value="false" />Non</label>';
			}
		// [...]INT - FLOAT - DOUBLE - DECIMAL - REAL
		}elseif (stristr($this->type,"int")) {
			$html = '<input type="number" class="form-control field" id="'.$this->name.'" value="'.$this->defaultValue.'" '.$this->showOptions().'/>';
		// ENUM
		}elseif (stristr($this->type,"enum")) {
			$options = str_replace('enum(','',$this->type);
			$options = str_replace(')','',$options);
			$options = str_replace('\'','',$options);
			$options = explode(',',$options);
			$html = '<select id="'.$this->name.'" class="form-control field"'.$this->showOptions().'>';
			$html .= '<option>-- Choisir --</option>';
			foreach($options as $option) {
				if ($option == $this->defaultValue) 
					$html .= '<option selected>'.$option.'</option>';
			}
			$html .= '</select>';
		// AUTRE
		}else{
			$html = '<input type="text" class="form-control field" id="'.$this->name.'" placeholder="Saisir '.lcfirst($this->label).'" value="'.$this->defaultValue.'" '.$this->showOptions().'>';
		}
		return $html;
	}
	
	public function showOptions() {
		$options = "";
		
		// Required
		if ($this->required) { $options .= " required "; }
		
		// Disabled
		if ($this->disabled) { $options .= " disabled "; }
		
		return $options;
	}
}