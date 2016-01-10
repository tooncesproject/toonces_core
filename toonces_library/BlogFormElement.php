<?php
/*
 * 	Hooray
 * 	BlogFormElement PHP Class
 * 	Initial Commit: Paul Anderson 12/27/2015
 * 
 * 	Basic, non-fancy blog post submission form
 * 
 * 
 */
include_once ROOTPATH.'/toonces.php';

class BlogFormElement extends Element
{
	
	// inherited class variables commented out
	//var $html;
	//var $htmlHeader;
	//var $htmlFooter;
	//var $pageViewReference;
	
	var $blogId;
	var $pageBuilderClass;
	
	public function getHTML() {
		
		if (!isset($this->blogId) or !isset($this->pageBuilderClass)) {
			throw new Exception('Blog ID and pageBuilderClass must be set before HTML is generated.');
		} else {	
			$html = sprintf($this->blogFormHTML(),$this->blogId,$this->pageBuilderClass);
		}
	
		return $html;
		
	}
	
	public function blogFormHTML() {
		
		$formHTML = <<<HTML
		<div class="blog_form_element">
			
				<form id="blogsubmission" method="post">
					<input type="hidden" name="blogid" value="%s">
					Title: <input type="text" name="title"> <br> <br>
					Author: <input type="text" name="author"> <br> <br>
					<input type="hidden" name="pageBuilderClass" value="%s">
					<input type="hidden" name="thumbnailImageVector" value="">
					<input type="submit">
				</form>
				
				<textarea name="body" form="blogsubmission"></textarea>
		</div>

HTML;

	return $formHTML;
	}
	
}
