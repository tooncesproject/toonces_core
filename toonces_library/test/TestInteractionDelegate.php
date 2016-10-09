<?php
require_once LIBPATH.'toonces.php';

class TestInteractionDelegate extends InteractionDelegate implements iInteractionDelegate
{
	
	public function buildInputArray() {
		// This function holds customizations for building the form array.
		// Its responsibility is to add members to the formArray[] instance variable.
		// FormElementInput objects will be rendered in the order they are added here.
		
		$testElement = new FormInput('test', 'text', $this->formName);
		$this->inputArray['test'] = $testElement;

		$submitElement = new FormInput('submit', 'submit', $this->formName);
		$submitElement->formValue = $this->submitName;
		$this->inputArray['submit'] = $submitElement;

	}
	
	// Method handling form data processing
	public function processFormData() {
		
		$msg = $this->inputArray['test']->postData;

		$this->inputArray['test']->storeMessage($msg);
		$this->inputArray['test']->storeValue($msg);
		$this->send303();

	}
	
}