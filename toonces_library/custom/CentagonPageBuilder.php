<?php

include_once ROOTPATH.'/ViewElement.php';
include_once ROOTPATH.'/Element.php';
include_once ROOTPATH.'/abstract/PageBuilder.php';
include_once ROOTPATH.'/BlogReader.php';
include_once ROOTPATH.'/DivElement.php';
include_once ROOTPATH.'/TagElement.php';

class CentagonPageBuilder extends PageBuilder {
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
		
		$soundCloudEmbed = new DivElement('');
		$soundCloudEmbed->setHTML('<iframe width="100%" height="166" scrolling="no" frameborder="no" src="https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/51488040&amp;color=ff5500&amp;auto_play=false&amp;hide_related=false&amp;show_comments=true&amp;show_user=true&amp;show_reposts=false"></iframe>');
		//$view->addElement($soundCloudEmbed);
		
		$topDivider = new DivElement('section_divider');
		$topDivider->setHTML('News and Announcements');
		$view->addElement($topDivider);
		
		array_push($this->elementArray,$view);
		
		$blogReader = new BlogReader();
		
		$view->addElement($blogReader);
		
		$vidDivider = new DivElement('section_divider');
		$vidDivider->setHTML('Get WEIRD!!!');
		$view->addElement($vidDivider);
		
		
		$video = new DivElement('');
		
		$video->setHTML('<iframe width="420" height="315" src="https://www.youtube.com/embed/NjVugzSR7HA" frameborder="0" allowfullscreen></iframe>');
		//$view->addElement($video);
		
		
		$footer = new TagElement('footer');
		
		$footer->setHTML('Copyright (C) 2015 by Centagon Records. All rights reserved.');
		
		$view->addElement($footer);
		
		return $this->elementArray;
		
	}
	
}

?>