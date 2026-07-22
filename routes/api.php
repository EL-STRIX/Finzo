<?php

use App\Shared\Core\Router;
use App\Presentation\Controllers\Admin\AdminManagementController;
use App\Presentation\Controllers\Admin\AdminOperationsController;
use App\Presentation\Controllers\Admin\AdminStaffController;
use App\Presentation\Controllers\Admin\AdminBranchController;
use App\Presentation\Controllers\Auth\AuthController;

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
use App\Presentation\Controllers\User\UserController;
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

use App\Presentation\Controllers\Public\PublicController;
$router->get('/Public/chart-stats.php', [PublicController::class, 'chartStats']);
// Uploads mapping to existing controllers
$router->post('/Uploads/upload-branch-cert.php', [\App\Presentation\Controllers\Uploads\UploadController::class, 'uploadBranchCert'], [\App\Presentation\Middleware\AdminAuthMiddleware::class]);
$router->post('/Uploads/upload-id-document.php', [\App\Presentation\Controllers\Uploads\UploadController::class, 'uploadIdDocument'], [\App\Presentation\Middleware\UserAuthMiddleware::class]);
// PDF Routes
use App\Presentation\Controllers\Pdf\PdfController;
$router->get('/Pdf/receipt-pdf.php', [PdfController::class, 'receipt']);
$router->get('/Pdf/staff-statement.php', [PdfController::class, 'staffStatement']);
$router->get('/Pdf/statement.php', [PdfController::class, 'statement']);
return $router;
