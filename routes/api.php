<?php

use App\Shared\Core\Router;
use App\Presentation\Api\V1\Admin\AdminManagementController;
use App\Presentation\Api\V1\Admin\AdminOperationsController;
use App\Presentation\Api\V1\Admin\AdminStaffController;
use App\Presentation\Api\V1\Admin\AdminBranchController;
use App\Presentation\Api\V1\Auth\AuthController;

$router = new Router();

// ==========================================
// AUTH ROUTES
// ==========================================
$router->post('/auth/login.php', [AuthController::class, 'login']);
$router->post('/auth/admin-login.php', [AuthController::class, 'adminLogin']);
$router->post('/auth/staff-login.php', [AuthController::class, 'staffLogin']);
$router->post('/auth/logout.php', [AuthController::class, 'logout']);
$router->get('/auth/logout.php', [AuthController::class, 'logout']);
$router->post('/auth/register.php', [AuthController::class, 'register']);
$router->post('/auth/forgot-password.php', [AuthController::class, 'forgotPassword']);
$router->post('/auth/reset-password.php', [AuthController::class, 'resetPassword']);
$router->get('/auth/get-csrf-token.php', [AuthController::class, 'getCsrfToken']);

// ==========================================
// ADMIN ROUTES
// ==========================================
// Admin Management
$router->post('/Admin/add-admin.php', [AdminManagementController::class, 'addAdmin'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/Admin/admin-action.php', [AdminManagementController::class, 'adminAction'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/Admin/get-admin-detail.php', [AdminManagementController::class, 'getAdminDetail'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/Admin/get-admins.php', [AdminManagementController::class, 'getAdmins'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/Admin/get-locked-admins.php', [AdminManagementController::class, 'getLockedAdmins'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/Admin/get-next-admin-id.php', [AdminManagementController::class, 'getNextAdminId'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/Admin/profile.php', [AdminManagementController::class, 'profile'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/Admin/update-admin-profile.php', [AdminManagementController::class, 'updateAdminProfile'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/Admin/upload-admin-photo.php', [AdminManagementController::class, 'uploadAdminPhoto'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/Admin/get-admin-profile.php', [AdminManagementController::class, 'getAdminProfile'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);

// Admin Operations
$router->get('/Admin/audit-log.php', [AdminOperationsController::class, 'auditLog'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/Admin/dashboard.php', [AdminOperationsController::class, 'dashboard'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/Admin/deleted-users.php', [AdminOperationsController::class, 'deletedUsers'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/Admin/export-users.php', [AdminOperationsController::class, 'exportUsers'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/Admin/interest.php', [AdminOperationsController::class, 'interest'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/Admin/interest.php', [AdminOperationsController::class, 'interest'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/Admin/locked-accounts.php', [AdminOperationsController::class, 'lockedAccounts'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/Admin/locked-account-detail.php', [AdminOperationsController::class, 'lockedAccountDetail'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/Admin/pending-approvals.php', [AdminOperationsController::class, 'pendingApprovals'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/Admin/view-kyc-document.php', [AdminOperationsController::class, 'viewKycDocument'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/Admin/recover-user.php', [AdminOperationsController::class, 'recoverUser'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/Admin/review-application.php', [AdminOperationsController::class, 'reviewApplication'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/Admin/review-application.php', [AdminOperationsController::class, 'reviewApplication'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/Admin/suspended-users.php', [AdminOperationsController::class, 'suspendedUsers'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/Admin/transactions.php', [AdminOperationsController::class, 'transactions'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/Admin/users.php', [AdminOperationsController::class, 'users'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/Admin/suspended-user-detail.php', [AdminOperationsController::class, 'suspendedUserDetail'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/Admin/unlock-account.php', [AdminOperationsController::class, 'unlockAccount'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/Admin/user-action.php', [AdminOperationsController::class, 'userAction'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/Admin/user-detail.php', [AdminOperationsController::class, 'userDetail'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);

// Admin Staff
$router->post('/Admin/add-staff.php', [AdminStaffController::class, 'addStaff'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/Admin/get-staff-detail.php', [AdminStaffController::class, 'getStaffDetail'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/Admin/staff-detail.php', [AdminStaffController::class, 'staffDetail'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/Admin/generate-staff-password.php', [AdminStaffController::class, 'generateStaffPassword'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/Admin/get-next-staff-id.php', [AdminStaffController::class, 'getNextStaffId'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/Admin/reset-staff-password.php', [AdminStaffController::class, 'resetStaffPassword'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/Admin/staff-action.php', [AdminStaffController::class, 'staffAction'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/Admin/upload-staff-photo.php', [AdminStaffController::class, 'uploadStaffPhoto'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/Admin/get-staff.php', [AdminStaffController::class, 'getStaffList'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/Admin/get-staff-list.php', [AdminStaffController::class, 'getStaffList'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/Admin/suspended-staff-detail.php', [AdminStaffController::class, 'suspendedStaffDetail'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/Admin/get-suspended-staff.php', [AdminStaffController::class, 'getSuspendedStaff'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);

// Admin Branch
$router->post('/Admin/delete-branch.php', [AdminBranchController::class, 'deleteBranch'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/Admin/get-branches.php', [AdminBranchController::class, 'getBranches'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);

// More routes will be added as we refactor other controllers


// ==========================================
// USER ROUTES
// ==========================================
use App\Presentation\Api\V1\User\UserController;
use App\Presentation\Middleware\UserAuthMiddleware;

$router->get('/User/account-lookup.php', [UserController::class, 'accountLookup'], [UserAuthMiddleware::class]);
$router->post('/User/account-settings.php', [UserController::class, 'accountSettings'], [UserAuthMiddleware::class]);
$router->get('/User/accounts.php', [UserController::class, 'accounts'], [UserAuthMiddleware::class]);
$router->get('/User/beneficiary.php', [UserController::class, 'beneficiary'], [UserAuthMiddleware::class]);
$router->post('/User/beneficiary.php', [UserController::class, 'beneficiary'], [UserAuthMiddleware::class]);
$router->post('/User/change-password.php', [UserController::class, 'changePassword'], [UserAuthMiddleware::class]);
$router->post('/User/complete-profile.php', [UserController::class, 'completeProfile'], [UserAuthMiddleware::class]);
$router->get('/User/dashboard.php', [UserController::class, 'dashboard'], [UserAuthMiddleware::class]);
$router->get('/User/notifications.php', [UserController::class, 'notifications'], [UserAuthMiddleware::class]);
$router->post('/User/notifications.php', [UserController::class, 'notifications'], [UserAuthMiddleware::class]);
$router->get('/User/profile.php', [UserController::class, 'profile'], [UserAuthMiddleware::class]);
$router->post('/User/profile.php', [UserController::class, 'profile'], [UserAuthMiddleware::class]);
$router->get('/User/statement.php', [UserController::class, 'statement'], [UserAuthMiddleware::class]);
$router->get('/User/transactions.php', [UserController::class, 'transactions'], [UserAuthMiddleware::class]);
$router->post('/User/transfer.php', [UserController::class, 'transfer'], [UserAuthMiddleware::class]);
$router->post('/User/verify-pin.php', [UserController::class, 'verifyPin'], [UserAuthMiddleware::class]);

use App\Presentation\Api\V1\Public\PublicController;
$router->get('/Public/chart-stats.php', [PublicController::class, 'chartStats']);
// Uploads mapping to existing controllers
$router->post('/Uploads/upload-branch-cert.php', [\App\Presentation\Api\V1\Uploads\UploadController::class, 'uploadBranchCert'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/Uploads/upload-id-document.php', [\App\Presentation\Api\V1\Uploads\UploadController::class, 'uploadIdDocument'], [\App\Presentation\Middleware\UserAuthMiddleware::class]);
// PDF Routes
use App\Presentation\Api\V1\Pdf\PdfController;
$router->get('/Pdf/receipt-pdf.php', [PdfController::class, 'receipt']);
$router->get('/Pdf/staff-statement.php', [PdfController::class, 'staffStatement']);
$router->get('/Pdf/statement.php', [PdfController::class, 'statement']);

// ==========================================
// REST STANDARDIZED ROUTES (PHASE 16.1)
// ==========================================

// Auth
$router->post('/api/auth/login', [AuthController::class, 'login']);
$router->post('/api/auth/admin-login', [AuthController::class, 'adminLogin']);
$router->post('/api/auth/staff-login', [AuthController::class, 'staffLogin']);
$router->post('/api/auth/logout', [AuthController::class, 'logout']);
$router->post('/api/auth/register', [AuthController::class, 'register']);
$router->post('/api/auth/forgot-password', [AuthController::class, 'forgotPassword']);
$router->post('/api/auth/reset-password', [AuthController::class, 'resetPassword']);
$router->get('/api/auth/csrf-token', [AuthController::class, 'getCsrfToken']);

// Admins
$router->get('/api/admins', [AdminManagementController::class, 'getAdmins'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/api/admins', [AdminManagementController::class, 'addAdmin'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/admins/locked', [AdminManagementController::class, 'getLockedAdmins'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/admins/next-id', [AdminManagementController::class, 'getNextAdminId'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/admins/profile', [AdminManagementController::class, 'getAdminProfile'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/api/admins/profile', [AdminManagementController::class, 'updateAdminProfile'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]); // PUT/PATCH preferred, but forms use POST
$router->post('/api/admins/profile/photo', [AdminManagementController::class, 'uploadAdminPhoto'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/admins/{id}', [AdminManagementController::class, 'getAdminDetail'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->patch('/api/admins/{id}', [AdminManagementController::class, 'adminAction'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);

// Admin Operations
$router->get('/api/admin/dashboard', [AdminOperationsController::class, 'dashboard'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/admin/audit-logs', [AdminOperationsController::class, 'auditLog'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/admin/interest', [AdminOperationsController::class, 'interest'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/api/admin/interest', [AdminOperationsController::class, 'interest'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/admin/applications/{id}', [AdminOperationsController::class, 'reviewApplication'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/api/admin/applications/{id}/review', [AdminOperationsController::class, 'reviewApplication'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);

// Users (via Admin)
$router->get('/api/users', [AdminOperationsController::class, 'users'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/users/suspended', [AdminOperationsController::class, 'suspendedUsers'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/users/deleted', [AdminOperationsController::class, 'deletedUsers'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/users/locked', [AdminOperationsController::class, 'lockedAccounts'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/users/locked/{id}', [AdminOperationsController::class, 'lockedAccountDetail'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/api/users/locked/{id}/unlock', [AdminOperationsController::class, 'unlockAccount'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/users/pending', [AdminOperationsController::class, 'pendingApprovals'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/users/{id}', [AdminOperationsController::class, 'userDetail'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/users/{id}/kyc', [AdminOperationsController::class, 'viewKycDocument'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->patch('/api/users/{id}', [AdminOperationsController::class, 'userAction'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/api/users/{id}/recover', [AdminOperationsController::class, 'recoverUser'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);

// Transactions (Admin)
$router->get('/api/transactions', [AdminOperationsController::class, 'transactions'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);

// Staff
$router->get('/api/staff', [AdminStaffController::class, 'getStaffList'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/api/staff', [AdminStaffController::class, 'addStaff'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/staff/suspended', [AdminStaffController::class, 'getSuspendedStaff'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/staff/next-id', [AdminStaffController::class, 'getNextStaffId'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->get('/api/staff/{id}', [AdminStaffController::class, 'getStaffDetail'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->patch('/api/staff/{id}', [AdminStaffController::class, 'staffAction'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/api/staff/{id}/password/generate', [AdminStaffController::class, 'generateStaffPassword'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/api/staff/{id}/password/reset', [AdminStaffController::class, 'resetStaffPassword'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/api/staff/{id}/photo', [AdminStaffController::class, 'uploadStaffPhoto'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);

// Branches
$router->get('/api/branches', [AdminBranchController::class, 'getBranches'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->delete('/api/branches/{id}', [AdminBranchController::class, 'deleteBranch'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);

// User (Self)
$router->get('/api/user/dashboard', [UserController::class, 'dashboard'], [UserAuthMiddleware::class]);
$router->get('/api/user/accounts', [UserController::class, 'accounts'], [UserAuthMiddleware::class]);
$router->get('/api/user/accounts/lookup', [UserController::class, 'accountLookup'], [UserAuthMiddleware::class]);
$router->post('/api/user/accounts/settings', [UserController::class, 'accountSettings'], [UserAuthMiddleware::class]);
$router->get('/api/user/beneficiaries', [UserController::class, 'beneficiary'], [UserAuthMiddleware::class]);
$router->post('/api/user/beneficiaries', [UserController::class, 'beneficiary'], [UserAuthMiddleware::class]);
$router->get('/api/user/profile', [UserController::class, 'profile'], [UserAuthMiddleware::class]);
$router->post('/api/user/profile', [UserController::class, 'profile'], [UserAuthMiddleware::class]);
$router->post('/api/user/profile/complete', [UserController::class, 'completeProfile'], [UserAuthMiddleware::class]);
$router->get('/api/user/notifications', [UserController::class, 'notifications'], [UserAuthMiddleware::class]);
$router->post('/api/user/notifications', [UserController::class, 'notifications'], [UserAuthMiddleware::class]);
$router->get('/api/user/statement', [UserController::class, 'statement'], [UserAuthMiddleware::class]);
$router->get('/api/user/transactions', [UserController::class, 'transactions'], [UserAuthMiddleware::class]);
$router->post('/api/user/transactions/transfer', [UserController::class, 'transfer'], [UserAuthMiddleware::class]);
$router->post('/api/user/security/change-password', [UserController::class, 'changePassword'], [UserAuthMiddleware::class]);
$router->post('/api/user/security/verify-pin', [UserController::class, 'verifyPin'], [UserAuthMiddleware::class]);

// Public / Uploads / PDFs
$router->get('/api/public/stats', [PublicController::class, 'chartStats']);
$router->post('/api/uploads/branch-cert', [UploadController::class, 'uploadBranchCert'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/api/uploads/id-document', [UploadController::class, 'uploadIdDocument'], [\App\Presentation\Middleware\UserAuthMiddleware::class]);
$router->get('/api/documents/receipt', [PdfController::class, 'receipt']);
$router->get('/api/documents/staff-statement', [PdfController::class, 'staffStatement']);
$router->get('/api/documents/statement', [PdfController::class, 'statement']);


// ==========================================
// V1 API ROUTES (PHASE 16.2)
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


use App\Presentation\Api\V1\Docs\SwaggerController;
$router->get('/api/docs', [SwaggerController::class, 'index']);
$router->get('/api/docs/openapi.json', [SwaggerController::class, 'json']);

return $router;
