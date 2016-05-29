<?php
/*
 * CentagonStandardPageBuilder
 * test extension of StandardPageBuilder
 * 
 */

require_once LIBPATH.'toonces.php';

class CentagonStandardPageBuilder extends StandardPageBuilder
{
	function createContentElement() {

		$this->contentElement = new Element($this->pageViewReference);

		$html = 'hey looky here';

		$this->contentElement->setHTML($html);

	}
}