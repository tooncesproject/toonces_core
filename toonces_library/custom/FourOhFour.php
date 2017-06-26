<?php

include_once LIBPATH.'ViewElement.php';
include_once LIBPATH.'Element.php';
include_once LIBPATH.'abstract/PageBuilder.php';
include_once LIBPATH.'BlogReader.php';
include_once LIBPATH.'DivElement.php';
include_once LIBPATH.'TagElement.php';
include_once LIBPATH.'HeadElement.php';
include_once LIBPATH.'PageView.php';

class FourOhFour extends PageBuilder {

	function buildPage() {

		// get static/generic html header, create as element
		$htmlHeaderElement = new Element($this->pageViewReference);
		$htmlHeaderElement->setHTML(file_get_contents(LIBPATH.'html/generic_html_header.html'));

		array_push($this->elementArray, $htmlHeaderElement);

		$headElement = new HeadElement($this->pageViewReference);

		// get head attributes
		$headElement->setPageTitle($this->pageViewReference->getPageTitle());
		$headElement->setStyleSheet($this->pageViewReference->getStyleSheet());

		$headElement->setHeadTags(file_get_contents(LIBPATH.'html/head_tags.html'));

		array_push($this->elementArray, $headElement);

		$headerElement = new Element($this->pageViewReference);

		$headerElement->setHTML(file_get_contents(LIBPATH.'html/body_test.html'));

		array_push($this->elementArray, $headerElement);

		$contentElement = new Element($this->pageViewReference);

		// Content element HTML
		$HTML = <<<HTML
		<div class="copy_block">
				<h1>404</h1>
				<h2>That release is so obscure, it doesn't even exist.</h2>
				<p><a href="/">Back to the comfortable banality of the mainstream Centagon Records home page.</a></p>
				<p>Don't cry. There's always this song.</p>
				<iframe width="420" height="315" src="https://www.youtube.com/embed/NjVugzSR7HA" frameborder="0" allowfullscreen></iframe>
		</div>
HTML;

		$contentElement->setHTML($HTML);
		array_push($this->elementArray, $contentElement);
		
		$footerElement = new Element($this->pageViewReference);

		$footerElement->setHTML(file_get_contents(LIBPATH.'html/real_footer_ish.html'));

		array_push($this->elementArray, $footerElement);

		return $this->elementArray;

	}

}

?>