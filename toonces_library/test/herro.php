<?php

include_once LIBPATH.'interfaces/iElement.php';

class herro implements iElement
{
	public function getHTML() {
		return 'HERRRO WORLD<BR>';
	}
	
}

		
?>