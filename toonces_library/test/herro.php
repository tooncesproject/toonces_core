<?php

include_once ROOTPATH.'/interfaces/iElement.php';

class herro implements iElement
{
	public function getHTML() {
		return 'HERRRO WORLD<BR>';
	}
	
}

		
?>