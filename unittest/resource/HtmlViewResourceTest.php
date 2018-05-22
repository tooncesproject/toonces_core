<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../toonces_library/php/toonces.php';


class HtmlViewResourceTest extends TestCase
{
    public function testAddElement()
    {
        // arrange
        $pageId = 1;
        $pageView = new HTMLPageView($pageId);
        $viewElement = new HTMLViewResource($pageView);
        $element = new HTMLResource($pageView);
        
        // act
        $viewElement->addElement($element);
        
        // assert
        $pageElements = $viewElement->pageElements;
        $this->assertContains($element, $pageElements);
    }
    
    public function testGetHTML()
    {
        // Arrange
        $pageId = 1;
        $pageView = new HTMLPageView($pageId);
        $viewElement = new HTMLViewResource($pageView);
        $element = new HTMLResource($pageView);
        
        $html = '</html>';
        $element->html = $html;
        $viewElement->addElement($element);
        
        // act
        $htmlOut = $viewElement->getResource();
        
        // assert
        $this->assertEquals(preg_replace('(\n)', '', $htmlOut), $html);
    }
}