<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../toonces_library/php/toonces.php';

class TestPageView extends TestCase
{
    
    public function testConstructPageView()
    {
        // arrange
        $pageID = 1;
        // act
        $pageView = new PageView($pageID);
        
        // assert
        $this->assertInstanceOf(PageView::class, $pageView);
    }
    
    public function testAddElement()
    {
        // arrange
        $pageID = 1;
        $pageView = new PageView($pageID);
        $element = new Element($pageView);
        
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
        $pageView = new PageView($pageID);
        $element = new Element($pageView);
        
        $html = '</html>';
        $element->html = $html;
        $pageView->addElement($element);
        
        // act
        $htmlOut = $pageView->getHTML();
        
        // assert
        $this->assertEquals(preg_replace('(\n)', '', $htmlOut), $html);
    }
}