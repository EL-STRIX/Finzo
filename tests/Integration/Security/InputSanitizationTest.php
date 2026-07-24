<?php
namespace Tests\Integration\Security;

use Tests\TestCase;
use App\Shared\Security\SecurityService;

class InputSanitizationTest extends TestCase {

    protected SecurityService $security;

    protected function setUp(): void {
        parent::setUp();
        $this->security = new SecurityService();
    }

    public function testSanitizesBasicXssPayloadsOnOutput() {
        $payload = "<script>alert('xss');</script>";
        $clean = html_encode($payload);
        
        $this->assertStringNotContainsString('<script>', $clean);
        $this->assertEquals("&lt;script&gt;alert(&#039;xss&#039;);&lt;/script&gt;", $clean);
    }

    public function testSanitizesHtmlAttributesXssOnOutput() {
        $payload = '" onmouseover="alert(1)"';
        $clean = html_encode($payload);
        
        $this->assertStringNotContainsString('"', $clean);
        $this->assertEquals("&quot; onmouseover=&quot;alert(1)&quot;", $clean);
    }

    public function testCleanInputOnlyTrimsWhitespace() {
        $payloads = [
            'name' => '   BoldName   ',
            'details' => [
                'bio' => '   <iframe src="javascript:alert(1)"></iframe>   '
            ]
        ];

        $clean = $this->security->cleanInput($payloads);

        $this->assertEquals("BoldName", $clean['name']);
        // Ensures input data isn't mangled on save
        $this->assertEquals("<iframe src=\"javascript:alert(1)\"></iframe>", $clean['details']['bio']);
    }

    public function testLeavesSafeDataIntact() {
        $safeData = "This is a completely normal string 123 !@#.";
        $clean = html_encode($safeData);
        $this->assertEquals($safeData, $clean);
    }
}
