<?php

namespace App\Domain\Enums;

enum AuditActionEnum: string
{
    case USER_LOGIN = 'user_login';
    case USER_LOGOUT = 'user_logout';
    case ACCOUNT_LOCKED = 'account_locked';
    case PROFILE_UPDATE = 'profile_update';
    case ADMIN_LOGIN = 'admin_login';
    case ADMIN_PASSWORD_CHANGE = 'admin_password_change';
    case ADMIN_PASSWORD_RESET_REQUEST = 'admin_password_reset_request';
    case ADMIN_PASSWORD_RESET_COMPLETE = 'admin_password_reset_complete';
    case KYC_APPROVED = 'kyc_approved';
    case KYC_REJECTED = 'kyc_rejected';
    case SYSTEM_SETTINGS_UPDATED = 'system_settings_updated';
    case SECURITY_ALERT = 'security_alert';
    case FUND_TRANSFER = 'fund_transfer';
    case STAFF_DEPOSIT = 'staff_deposit';
    case STAFF_WITHDRAWAL = 'staff_withdrawal';
    case STAFF_PROFILE_UPDATE = 'staff_profile_update';
    case STAFF_PASSWORD_CHANGE = 'staff_password_change';
    case BENEFICIARY_ADDED = 'beneficiary_added';
    case BENEFICIARY_DELETED = 'beneficiary_deleted';
    case KYC_SUBMITTED = 'kyc_submitted';
}
