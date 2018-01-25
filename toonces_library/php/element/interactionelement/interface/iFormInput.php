<?php
/*
 * iFormInput
 * Initial commit: Paul Anderson, 9/2/2016
 * 
 * Interface defining FormInput objects (i.e., FormElementInput)
 * 
 */

interface iFormInput
{

	// Stores a response message to the session (so the page can display a message after POST, redirect and GET) 
	public function storeMessage($paramMessage);

	// Stores a signal in the session whether or not to render the input
	public function storeRenderSignal($paramRenderSignal);

	// Stores a signal in the session whether or not to hide the entire element
	public function storeHideSignal($paramHideSignal);

	// Stores a string in the session so the form can display a value previously input by the user 
	public function storeValue($paramFormValue);

	// Generates the input HTML.
	// $message is a string to be displayed, optionally
	// $renderInput is a boolean determining whether to create an input
	// $messageClass is the CSS class of the message, defaults to form_message_notification
	public function getResource($renderInput, $message = NULL, $messageClass = NULL);

}
