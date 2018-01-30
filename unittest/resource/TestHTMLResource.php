<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../toonces_library/php/toonces.php';


class TestHTMLResource extends TestCase
{
    public function testElementConstruct()
    {
        // Arrange
        $pageView = new HTMLPageVew(0);
        
        
        // Act
        $element = new HTMLResource($pageView);
        
        // Assert
        $this->assertInstanceOf(Element::class, $element);
        $this->assertInstanceOf(HTMLPageVew::class, $pageView);
    }

    public function testGetHTML()
    {
        //arrange
        $pageView = new HTMLPageVew(0);
        $element = new HTMLResource($pageView);
        
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