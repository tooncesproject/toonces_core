<?php
require_once LIBPATH.'php/toonces.php';

class TestInteractionElement extends StandardPageBuilder
{

	function createContentElement() {

		// Insert code here to create a content element
		// $this->contentElement = new Element($this->pageViewReference);
		$this->contentElement = new InteractionElement($this->pageViewReference);
		$this->contentElement->formName = 'dank';

		// needs controller element
		$controllerElement = new TestInteractionDelegate('dank', $this->contentElement);
		$controllerElement->buildInputArray();
		$this->contentElement->interactionDelegate = $controllerElement;

	}

}