<?php
namespace Tests\Integration\Security;

use Tests\TestCase;
use Tests\Helpers\RequestHelper;
use App\Shared\Core\Database;

class FileUploadSecurityTest extends TestCase {

    public function testRejectsPhpFilesDespiteMimeSpoofing() {
        $adminId = 1;
        $session = [
            'admin_id' => $adminId,
            'admin_role' => 'admin',
            'csrf_token' => 'dummy_token'
        ];
        
        $postData = [
            'csrf_token' => 'dummy_token',
            'branch_id' => 999
        ];
        
        // Create a temporary malicious PHP file
        $tmpFile = tempnam(sys_get_temp_dir(), 'malicious_');
        file_put_contents($tmpFile, '<?php echo "Hacked"; ?>');
        
        $fileData = [
            'certificate' => [
                'name' => 'certificate.php',
                'type' => 'image/jpeg', // Spoofed MIME
                'tmp_name' => $tmpFile,
                'error' => UPLOAD_ERR_OK,
                'size' => filesize($tmpFile)
            ]
        ];
        
        try {
            RequestHelper::postJson('api/v1/uploads/branch-cert', $postData, $session, $fileData);
            $this->fail("File upload succeeded despite malicious payload");
        } catch (\App\Shared\Core\Exceptions\TestResponseException $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertContains($response['status'], [400, 422]);
        } finally {
            @unlink($tmpFile);
        }
    }
}
