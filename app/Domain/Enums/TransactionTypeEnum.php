<?php

namespace App\Domain\Enums;

enum TransactionTypeEnum: string
{
    case DEPOSIT = 'deposit';
    case WITHDRAWAL = 'withdrawal';
    case TRANSFER = 'transfer';
    case INTEREST_CREDIT = 'interest_credit';
}
