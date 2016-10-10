<?php
require_once LIBPATH.'toonces.php';

class TestInteractionDelegate extends InteractionDelegate implements iInteractionDelegate
{

	public function buildInputArray() {
		// This function holds customizations for building the form array.
		// Its responsibility is to add members to the formArray[] instance variable.
		// FormElementInput objects will be rendered in the order they are added here.

		// Test with FormInput validation - positive
		$testElement = new FormInput('test', 'text', $this);
		// test FormInput validation - negative
		//$testElement = new FormInput('test', 'barf', $this);

		// text area
		$misterTA = new TextareaFormInput('thetextarea', $this);
		$misterTA->cols = 50;
		$misterTA->rows = 20;

		$submitElement = new FormInput('submit', 'submit', $this);
		$submitElement->formValue = $this->submitName;

	}

	// Method handling form data processing
	public function processFormData() {

		$msg = $this->inputArray['test']->postData;

		$this->inputArray['test']->storeMessage($msg);
		$this->inputArray['test']->storeValue($msg);

		$taMsg = $this->inputArray['thetextarea']->postData;
		$this->inputArray['thetextarea']->storeMessage($taMsg);
		$this->inputArray['thetextarea']->storeValue($taMsg);

		$this->storeMessage('Hi, i\'m a message!');

		$this->send303();


	}

}