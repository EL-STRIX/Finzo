<?php

use App\Core\Router;
use App\Controllers\Admin\AdminManagementController;
use App\Controllers\Admin\AdminOperationsController;
use App\Controllers\Admin\AdminStaffController;
use App\Controllers\Admin\AdminBranchController;
use App\Controllers\Auth\AuthController;

$router = new Router();

// ==========================================
// AUTH ROUTES
// ==========================================
$router->post('/auth/login.php', [AuthController::class, 'login']);
$router->post('/auth/admin-login.php', [AuthController::class, 'adminLogin']);
$router->post('/auth/staff-login.php', [AuthController::class, 'staffLogin']);
$router->post('/auth/logout.php', [AuthController::class, 'logout']);
$router->post('/auth/register.php', [AuthController::class, 'register']);
$router->post('/auth/forgot-password.php', [AuthController::class, 'forgotPassword']);
$router->post('/auth/reset-password.php', [AuthController::class, 'resetPassword']);
$router->get('/auth/get-csrf-token.php', [AuthController::class, 'getCsrfToken']);

// ==========================================
// ADMIN ROUTES
// ==========================================
// Admin Management
$router->post('/Admin/add-admin.php', [AdminManagementController::class, 'addAdmin']);
$router->post('/Admin/admin-action.php', [AdminManagementController::class, 'adminAction']);
$router->get('/Admin/get-admin-detail.php', [AdminManagementController::class, 'getAdminDetail']);
$router->get('/Admin/get-admins.php', [AdminManagementController::class, 'getAdmins']);
$router->get('/Admin/get-locked-admins.php', [AdminManagementController::class, 'getLockedAdmins']);
$router->get('/Admin/get-next-admin-id.php', [AdminManagementController::class, 'getNextAdminId']);
$router->post('/Admin/profile.php', [AdminManagementController::class, 'profile']);
$router->post('/Admin/update-admin-profile.php', [AdminManagementController::class, 'updateAdminProfile']);
$router->post('/Admin/upload-admin-photo.php', [AdminManagementController::class, 'uploadAdminPhoto']);
$router->get('/Admin/get-admin-profile.php', [AdminManagementController::class, 'getAdminProfile']);

// Admin Operations
$router->get('/Admin/audit-log.php', [AdminOperationsController::class, 'auditLog']);
$router->get('/Admin/dashboard.php', [AdminOperationsController::class, 'dashboard']);
$router->get('/Admin/deleted-users.php', [AdminOperationsController::class, 'deletedUsers']);
$router->get('/Admin/export-users.php', [AdminOperationsController::class, 'exportUsers']);
$router->get('/Admin/interest.php', [AdminOperationsController::class, 'interest']);
$router->post('/Admin/interest.php', [AdminOperationsController::class, 'interest']);
$router->get('/Admin/locked-accounts.php', [AdminOperationsController::class, 'lockedAccounts']);
$router->get('/Admin/locked-account-detail.php', [AdminOperationsController::class, 'lockedAccountDetail']);
$router->get('/Admin/pending-approvals.php', [AdminOperationsController::class, 'pendingApprovals']);
$router->post('/Admin/recover-user.php', [AdminOperationsController::class, 'recoverUser']);
$router->get('/Admin/review-application.php', [AdminOperationsController::class, 'reviewApplication']);
$router->post('/Admin/review-application.php', [AdminOperationsController::class, 'reviewApplication']);
$router->get('/Admin/suspended-users.php', [AdminOperationsController::class, 'suspendedUsers']);
$router->get('/Admin/transactions.php', [AdminOperationsController::class, 'transactions']);
$router->get('/Admin/users.php', [AdminOperationsController::class, 'users']);
$router->get('/Admin/suspended-user-detail.php', [AdminOperationsController::class, 'suspendedUserDetail']);
$router->post('/Admin/unlock-account.php', [AdminOperationsController::class, 'unlockAccount']);
$router->post('/Admin/user-action.php', [AdminOperationsController::class, 'userAction']);
$router->get('/Admin/user-detail.php', [AdminOperationsController::class, 'userDetail']);

// Admin Staff
$router->post('/Admin/add-staff.php', [AdminStaffController::class, 'addStaff']);
$router->get('/Admin/get-staff-detail.php', [AdminStaffController::class, 'getStaffDetail']);
$router->get('/Admin/staff-detail.php', [AdminStaffController::class, 'staffDetail']);
$router->post('/Admin/generate-staff-password.php', [AdminStaffController::class, 'generateStaffPassword']);
$router->get('/Admin/get-next-staff-id.php', [AdminStaffController::class, 'getNextStaffId']);
$router->post('/Admin/reset-staff-password.php', [AdminStaffController::class, 'resetStaffPassword']);
$router->post('/Admin/staff-action.php', [AdminStaffController::class, 'staffAction']);
$router->post('/Admin/upload-staff-photo.php', [AdminStaffController::class, 'uploadStaffPhoto']);
$router->get('/Admin/get-staff.php', [AdminStaffController::class, 'getStaffList']);
$router->get('/Admin/get-staff-list.php', [AdminStaffController::class, 'getStaffList']);
$router->get('/Admin/suspended-staff-detail.php', [AdminStaffController::class, 'suspendedStaffDetail']);
$router->get('/Admin/get-suspended-staff.php', [AdminStaffController::class, 'getSuspendedStaff']);

// Admin Branch
$router->post('/Admin/delete-branch.php', [AdminBranchController::class, 'deleteBranch']);
$router->get('/Admin/get-branches.php', [AdminBranchController::class, 'getBranches']);

// More routes will be added as we refactor other controllers

return $router;
