<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Helpers\SecurityHelper;

class SecurityHelperTest extends TestCase {
    
    public function testEscapeHtml() {
        $input = '<script>alert("XSS")</script>';
        $expected = '&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;';
        $this->assertEquals($expected, SecurityHelper::escape($input));
    }

    public function testCleanInput() {
        $input = '   <p>Hello World</p>   ';
        $expected = 'Hello World';
        $this->assertEquals($expected, SecurityHelper::cleanInput($input));
    }

    public function testCleanInputWithNull() {
        $this->assertEquals('', SecurityHelper::cleanInput(null));
    }
}
