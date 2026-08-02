<?php

use App\Shared\Core\Router;
use App\Presentation\Api\V1\Admin\AdminManagementController;
use App\Presentation\Api\V1\Admin\AdminOperationsController;
use App\Presentation\Api\V1\Admin\AdminStaffController;
use App\Presentation\Api\V1\Admin\AdminBranchController;
use App\Presentation\Api\V1\Auth\AuthController;
use App\Presentation\Api\V1\Uploads\UploadController;

$router = new Router();

// ==========================================
// V1 API ROUTES (Phase 16.2 & Phase 2.3)
// ==========================================
$router->post('/api/v1/auth/login', [AuthController::class, 'login']);
$router->post('/api/v1/auth/admin-login', [AuthController::class, 'adminLogin']);
$router->post('/api/v1/auth/staff-login', [AuthController::class, 'staffLogin']);
$router->post('/api/v1/auth/logout', [AuthController::class, 'logout']);
$router->post('/api/v1/auth/register', [AuthController::class, 'register']);
$router->post('/api/v1/auth/forgot-password', [AuthController::class, 'forgotPassword']);
$router->post('/api/v1/auth/reset-password', [AuthController::class, 'resetPassword']);
$router->get('/api/v1/auth/csrf-token', [AuthController::class, 'getCsrfToken']);
$router->get('/api/v1/admins', [AdminManagementController::class, 'getAdmins'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/api/v1/admins', [AdminManagementController::class, 'addAdmin'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/v1/admins/locked', [AdminManagementController::class, 'getLockedAdmins'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/v1/admins/next-id', [AdminManagementController::class, 'getNextAdminId'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/v1/admins/profile', [AdminManagementController::class, 'getAdminProfile'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/api/v1/admins/profile', [AdminManagementController::class, 'updateAdminProfile'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]); // PUT/PATCH preferred, but forms use POST
$router->post('/api/v1/admins/profile/photo', [AdminManagementController::class, 'uploadAdminPhoto'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/v1/admins/{id}', [AdminManagementController::class, 'getAdminDetail'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->patch('/api/v1/admins/{id}', [AdminManagementController::class, 'adminAction'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/v1/admin/dashboard', [AdminOperationsController::class, 'dashboard'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/v1/admin/audit-logs', [AdminOperationsController::class, 'auditLog'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/v1/admin/interest', [AdminOperationsController::class, 'interest'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/api/v1/admin/interest', [AdminOperationsController::class, 'interest'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/v1/admin/applications/{id}', [AdminOperationsController::class, 'reviewApplication'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/api/v1/admin/applications/{id}/review', [AdminOperationsController::class, 'reviewApplication'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/v1/users', [AdminOperationsController::class, 'users'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/v1/users/suspended', [AdminOperationsController::class, 'suspendedUsers'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/v1/users/deleted', [AdminOperationsController::class, 'deletedUsers'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/v1/users/locked', [AdminOperationsController::class, 'lockedAccounts'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/v1/users/locked/{id}', [AdminOperationsController::class, 'lockedAccountDetail'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/api/v1/users/locked/{id}/unlock', [AdminOperationsController::class, 'unlockAccount'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/v1/users/pending', [AdminOperationsController::class, 'pendingApprovals'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/v1/users/{id}', [AdminOperationsController::class, 'userDetail'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/v1/users/{id}/kyc', [AdminOperationsController::class, 'viewKycDocument'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->patch('/api/v1/users/{id}', [AdminOperationsController::class, 'userAction'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/api/v1/users/{id}/recover', [AdminOperationsController::class, 'recoverUser'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/v1/transactions', [AdminOperationsController::class, 'transactions'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/api/v1/transactions/{id}/reverse', [AdminOperationsController::class, 'reverseTransaction'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/v1/staff', [AdminStaffController::class, 'getStaffList'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/api/v1/staff', [AdminStaffController::class, 'addStaff'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/v1/staff/suspended', [AdminStaffController::class, 'getSuspendedStaff'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/v1/staff/next-id', [AdminStaffController::class, 'getNextStaffId'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/v1/staff/{id}', [AdminStaffController::class, 'getStaffDetail'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->patch('/api/v1/staff/{id}', [AdminStaffController::class, 'staffAction'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/api/v1/staff/{id}/password/generate', [AdminStaffController::class, 'generateStaffPassword'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/api/v1/staff/{id}/password/reset', [AdminStaffController::class, 'resetStaffPassword'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/api/v1/staff/{id}/photo', [AdminStaffController::class, 'uploadStaffPhoto'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/v1/branches', [AdminBranchController::class, 'getBranches'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->delete('/api/v1/branches/{id}', [AdminBranchController::class, 'deleteBranch'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/v1/user/dashboard', [UserController::class, 'dashboard'], [UserAuthMiddleware::class]);
$router->get('/api/v1/user/accounts', [UserController::class, 'accounts'], [UserAuthMiddleware::class]);
$router->get('/api/v1/user/accounts/lookup', [UserController::class, 'accountLookup'], [UserAuthMiddleware::class]);
$router->post('/api/v1/user/accounts/settings', [UserController::class, 'accountSettings'], [UserAuthMiddleware::class]);
$router->get('/api/v1/user/beneficiaries', [UserController::class, 'beneficiary'], [UserAuthMiddleware::class]);
$router->post('/api/v1/user/beneficiaries', [UserController::class, 'beneficiary'], [UserAuthMiddleware::class]);
$router->get('/api/v1/user/profile', [UserController::class, 'profile'], [UserAuthMiddleware::class]);
$router->post('/api/v1/user/profile', [UserController::class, 'profile'], [UserAuthMiddleware::class]);
$router->post('/api/v1/user/profile/complete', [UserController::class, 'completeProfile'], [UserAuthMiddleware::class]);
$router->get('/api/v1/user/notifications', [UserController::class, 'notifications'], [UserAuthMiddleware::class]);
$router->post('/api/v1/user/notifications', [UserController::class, 'notifications'], [UserAuthMiddleware::class]);
$router->get('/api/v1/user/statement', [UserController::class, 'statement'], [UserAuthMiddleware::class]);
$router->get('/api/v1/user/transactions', [UserController::class, 'transactions'], [UserAuthMiddleware::class]);
$router->post('/api/v1/user/transactions/transfer', [UserController::class, 'transfer'], [UserAuthMiddleware::class]);
$router->post('/api/v1/user/security/change-password', [UserController::class, 'changePassword'], [UserAuthMiddleware::class]);
$router->post('/api/v1/user/security/verify-pin', [UserController::class, 'verifyPin'], [UserAuthMiddleware::class]);
$router->get('/api/v1/public/stats', [PublicController::class, 'chartStats']);
$router->post('/api/v1/uploads/branch-cert', [UploadController::class, 'uploadBranchCert'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/api/v1/uploads/id-document', [UploadController::class, 'uploadIdDocument'], [\App\Presentation\Middleware\UserAuthMiddleware::class]);
$router->get('/api/v1/documents/receipt', [PdfController::class, 'receipt']);
$router->get('/api/v1/documents/staff-statement', [PdfController::class, 'staffStatement']);
$router->get('/api/v1/documents/statement', [PdfController::class, 'statement']);



use App\Presentation\Api\V1\Staff\StaffController;
use App\Presentation\Middleware\StaffAuthMiddleware;

// Staff Portal Routes
$router->get('/api/v1/staff-portal/dashboard', [StaffController::class, 'dashboard'], [StaffAuthMiddleware::class]);
$router->get('/api/v1/staff-portal/account-lookup', [StaffController::class, 'accountLookup'], [StaffAuthMiddleware::class]);
$router->post('/api/v1/staff-portal/deposit', [StaffController::class, 'deposit'], [StaffAuthMiddleware::class]);
$router->post('/api/v1/staff-portal/withdrawal', [StaffController::class, 'withdrawal'], [StaffAuthMiddleware::class]);
$router->get('/api/v1/staff-portal/history', [StaffController::class, 'history'], [StaffAuthMiddleware::class]);
$router->get('/api/v1/staff-portal/receipt', [StaffController::class, 'receipt'], [StaffAuthMiddleware::class]);
$router->get('/api/v1/staff-portal/profile', [StaffController::class, 'profile'], [StaffAuthMiddleware::class]);
$router->post('/api/v1/staff-portal/profile', [StaffController::class, 'profile'], [StaffAuthMiddleware::class]);
$router->post('/api/v1/staff-portal/change-password', [StaffController::class, 'changePassword'], [StaffAuthMiddleware::class]);

use App\Presentation\Api\V1\Docs\SwaggerController;
$router->get('/api/docs', [SwaggerController::class, 'index']);
$router->get('/api/docs/openapi.json', [SwaggerController::class, 'json']);

return $router;

