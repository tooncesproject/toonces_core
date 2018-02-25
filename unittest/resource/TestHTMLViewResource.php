<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../toonces_library/php/toonces.php';


class TestHTMLViewResource extends TestCase
{
    public function testAddElement()
    {
        // arrange
        $pageID = 1;
        $pageView = new HTMLPageView($pageID);
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
        $pageID = 1;
        $pageView = new HTMLPageView($pageID);
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