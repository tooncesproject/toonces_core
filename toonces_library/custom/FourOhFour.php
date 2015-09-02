<?php

include_once ROOTPATH.'/ViewElement.php';
include_once ROOTPATH.'/Element.php';
include_once ROOTPATH.'/abstract/PageBuilder.php';
include_once ROOTPATH.'/BlogReader.php';
include_once ROOTPATH.'/DivElement.php';
include_once ROOTPATH.'/TagElement.php';

class FourOhFour extends PageBuilder {
	/*
	var $elementArray = array();
	private $containerHTML;
	private $bodyHTML;
	var $view;
	
	function getElementArray() {
		return $elementArray;
	}
	*/
	function buildPage() {
		
		$view = new ViewElement();
		$view->setHtmlHeader('<div class="main_container">');
		$view->setHtmlFooter('</div>');
		
		$contentElement = new Element();
		
		$contentElement->setHTML(file_get_contents(ROOTPATH.'/static_data/mainpage_content.html'));
		
		$view->addElement($contentElement);
		
		
		$topDivider = new DivElement('section_divider');
		$topDivider->setHTML('404 MOTHERFUCKER');
		$view->addElement($topDivider);
		
		array_push($this->elementArray,$view);
		
		$FourOhFourMessage = new Element();
		
		$FourOhFourMessage->setHTML('SORRY DUDE WRONG PAGE');
		
		$view->addElement($FourOhFourMessage);
		
		$vidDivider = new DivElement('section_divider');
		$vidDivider->setHTML('Get WEIRD!!!');
		$view->addElement($vidDivider);
		
		
		$video = new DivElement('');
		
		$video->setHTML('<iframe width="420" height="315" src="https://www.youtube.com/embed/NjVugzSR7HA" frameborder="0" allowfullscreen></iframe>');
		$view->addElement($video);
		
		
		$footer = new TagElement('footer');
		
		$footer->setHTML('Copyright (C) 2015 by Centagon Records. All rights reserved.');
		
		$view->addElement($footer);
		
		return $this->elementArray;
		
	}
	
}

?>