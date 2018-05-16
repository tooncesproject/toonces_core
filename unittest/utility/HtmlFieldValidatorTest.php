<?php
/**
 * @author paulanderson
 * HtmlFieldValidatorTest.php
 * Initial commit: Paul Anderson, 5/16/2018
 * 
 * Unit tests for the HtmlFieldValidator class.
 * 
 */

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../toonces_library/php/toonces.php';


class HtmlFieldValidatorTest extends TestCase {
    
    function testValidateData() {
        // ARRANGE
        $validator = new HtmlFieldValidator();
        // Some HTML with Javascript:
        $jsHtml = '<div><script type="text/javascript">document.write("This is some JS")</script></div>';
        $plainHtml = '<p>Foo!</p>';
        $notHtml = 69;
        
        // ACT
        $invalidResult = $validator->validateData($notHtml);
        $jsResult = $validator->validateData($jsHtml);
        $validResult = $validator->validateData($plainHtml);
        
        // ASSERT
        $this->assertFalse($invalidResult);
        $this->assertFalse($jsResult);
        $this->assertTrue($validResult);
        
    }
    

    function testDetectScripts() {
        // ARRANGE
        $validator = new HtmlFieldValidator();
        // Some HTML with Javascript:
        $jsHtml = '<script type="text/javascript">document.write("This is some JS")</script>';
        $plainHtml = '<p>Foo!</p>';

        // ACT
        $jsResult = $validator->detectScripts($jsHtml);
        $validResult = $validator->detectScripts($plainHtml);
        
        // ASSERT
        $this->assertFalse($jsResult);
        $this->assertTrue($validResult);
    }

}
