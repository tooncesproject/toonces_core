<?php
/*
 * TextareaFormInput
 * Initial commit: Paul Anderson 9/5/16
 *
 * Versioned from FormElementInput to simplify implementation.
 * This class holds inputs to be handled by the FormElement class
 * The responsibilities of the inputs are:
 * 		Generate html for the input within the form
 * 		Gather the postdata from the input
 * 		Set the type of the input
 * 		Display input-level responses
 *
 */

class TextareaFormInput extends FormInput implements iFormInput
{

	public $rows;
	public $cols;

	function __construct($paramName,$paramInteractionDelegate) {

		// Check to ensure paramInteractionDelegate is a subclass of InteractionDelegate
		if (!is_subclass_of($paramInteractionDelegate, 'InteractionDelegate'))
			throw new Exception('TextareaFormInput exception: interaction delegate parameter must be an InteractionDelegate subclass.');

		$this->name = $paramName;
		$this->inputType = 'hidden';
		$this->interactionDelegate = $paramInteractionDelegate;
		$this->parentFormName = $this->interactionDelegate->formName;

		// Add self to the delegate's array of FormInput objects.
		$this->interactionDelegate->inputArray[$this->name] = $this;

		$this->acquireSignals();
	}


	// $message is a string to be displayed, optionally
	// $renderInput is a boolean determining whether to create an input
	// $messageClass is the CSS class of the message, defaults to form_message_notification
	public function getHTML($renderInput, $message = NULL, $messageClass = NULL) {

		$this->html = '';
		$classHTML = '';
		$messageHTML = '';
		$formHTML = '';
		$displayNameHTML = '';
		$sizeHTML = '';
		$formValueHTML = '';

		// If no hide input signal, generate the HTML.
		if ($this->hideInput == false) {
			if (isset($this->message)) {
				$messageHTML = '<div class="'.$this->messageClass.'">'.$this->message.'</div>';
			}

			if(isset($this->displayName))
				$displayNameHTML = '<div class="input_display_name">'.$this->displayName.'</div>';

			if (isset($this->cssClass))
				$classHTML = ' class="'.$this->cssClass.'"';

			if (isset($this->size))
				$sizeHTML = ' size="'.$this->size.'"';

			if (isset($this->formValue))
				$formValueHTML = ' value="'.$this->formValue.'"';

			$this->html = $this->html.$messageHTML.PHP_EOL;

			if ($this->renderInput == true)
				$this->html = $this->html.'<textarea name="'.$this->name.'" rows="'.$this->rows.'" cols="'.$this->cols.'" form="'.$this->parentFormName.'">'.PHP_EOL.$this->formValue.'</textarea>'.PHP_EOL;
		}

		return $this->html;

	}


}