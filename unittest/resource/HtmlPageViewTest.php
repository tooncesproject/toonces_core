<?php
/**
 * @author paulanderson
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../toonces_library/php/toonces.php';

class HtmlPageViewTest extends TestCase
{
    
    public function testConstructHTMLPageView()
    {
        // arrange
        $pageID = 1;
        // act
        $pageView = new HTMLPageView($pageID);
        
        // assert
        $this->assertInstanceOf(HTMLPageView::class, $pageView);
    }
    
    public function testAddElement()
    {
        // arrange
        $pageID = 1;
        $pageView = new HTMLPageView($pageID);
        $element = new HTMLResource($pageView);
        
        // act
        $pageView->addElement($element);

        // assert
        $pageElements = $pageView->pageElements;
        $this->assertContains($element, $pageElements);
    }
 
    public function testGetHTML()
    {
        // arrange
        $pageID = 1;
        $pageView = new HTMLPageView($pageID);
        $element = new HTMLResource($pageView);
        
        $html = '</html>';
        $element->html = $html;
        $pageView->addElement($element);
        
        // act
        $htmlOut = $pageView->getResource();
        
        // assert
        $this->assertEquals(preg_replace('(\n)', '', $htmlOut), $html);
    }
}