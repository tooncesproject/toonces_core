<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../toonces_library/php/toonces.php';


class TestElement extends TestCase
{
    public function testElementConstruct()
    {
        // Arrange
        $pageView = new PageView(0);
        
        
        // Act
        $element = new Element($pageView);
        
        // Assert
        $this->assertInstanceOf(Element::class, $element);
        $this->assertInstanceOf(PageView::class, $pageView);
    }

    public function testGetHTML()
    {
        //arrange
        $pageView = new PageView(0);
        $element = new Element($pageView);
        
        $header = 'Header';
        $footer = 'Footer';
        $HTML = 'HTML';
        
        $element->htmlHeader = $header;
        $element->htmlFooter = $footer;
        $element->html = $HTML;
        
        //act
        $testHTML = $element->getResource();
        
        //assert
        $this->assertEquals($testHTML, $header . PHP_EOL .  $HTML . PHP_EOL . $footer);
    }

}