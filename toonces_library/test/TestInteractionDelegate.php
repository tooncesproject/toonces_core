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

	}
	
	// Method handling form data processing
	public function processFormData() {
		
		$msg = $this->inputArray['test']->postData;
		// echo var_dump($this->inputArray['test']);
		
		
		$this->inputArray['test']->storeMessage($msg);
		
		$this->interactionElement->generateFormHTML();
	}
	
}