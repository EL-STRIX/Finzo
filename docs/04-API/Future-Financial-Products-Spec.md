# Future Financial Products — Clean Architecture Specification

This technical specification defines the bounded contexts, interfaces, endpoints, and data contracts for the four upcoming Finzo Customer Financial Products:
1. **Digital Loans & Credit Facilities**
2. **Next-Gen Cards & Controls**
3. **High-Yield Term Deposits & Investments (Fixed Deposits)**
4. **Bharat BillPay (BBPS) Utilities & Recharges**

---

## 1. Digital Loans & Credit Facilities

### 1.1 Architecture Layering
- **Controller**: `App\Presentation\Api\V1\User\UserLoanController`
- **Application Service**: `App\Application\Services\UserLoanService` (implements `UserLoanServiceInterface`)
- **Domain Contract**: `App\Domain\Contracts\Services\UserLoanServiceInterface`, `App\Domain\Contracts\Repositories\LoanRepositoryInterface`
- **Infrastructure Repository**: `App\Infrastructure\Repositories\LoanRepository`
- **Domain Entities**: `App\Domain\Models\LoanApplication`, `App\Domain\Models\LoanRepaymentSchedule`

### 1.2 Proposed Endpoints
| Method | Route | Description | Auth |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/user/loans` | List user's active loans & applications | `UserAuthMiddleware` |
| `POST` | `/api/v1/user/loans/calculator` | Server-side actuarial EMI calculation & amortization table | `UserAuthMiddleware` |
| `POST` | `/api/v1/user/loans/apply` | Submit digital loan application with KYC verification | `UserAuthMiddleware`, `CsrfMiddleware` |
| `GET` | `/api/v1/user/loans/{id}/schedule` | Retrieve detailed repayment breakdown | `UserAuthMiddleware` |

### 1.3 Service Interface Blueprint
```php
namespace App\Domain\Contracts\Services;

interface UserLoanServiceInterface
{
    public function getActiveLoans(int $userId): array;
    public function calculateAmortization(float $principal, int $tenureMonths, float $annualRate): array;
    public function applyForCredit(int $userId, array $applicationData): array;
    public function getRepaymentSchedule(int $userId, int $loanId): array;
}
```

---

## 2. Next-Gen Cards & Controls

### 2.1 Architecture Layering
- **Controller**: `App\Presentation\Api\V1\User\UserCardController`
- **Application Service**: `App\Application\Services\UserCardService` (implements `UserCardServiceInterface`)
- **Domain Contract**: `App\Domain\Contracts\Services\UserCardServiceInterface`, `App\Domain\Contracts\Repositories\CardRepositoryInterface`
- **Infrastructure Repository**: `App\Infrastructure\Repositories\CardRepository`
- **Domain Entities**: `App\Domain\Models\PaymentCard`, `App\Domain\Models\CardControlPreference`

### 2.2 Proposed Endpoints
| Method | Route | Description | Auth |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/user/cards` | Retrieve user's debit/credit/virtual cards | `UserAuthMiddleware` |
| `POST` | `/api/v1/user/cards/request` | Order new physical metal card or issue virtual card | `UserAuthMiddleware`, `CsrfMiddleware` |
| `PATCH` | `/api/v1/user/cards/{id}/freeze` | Instantly lock/unlock card transactions | `UserAuthMiddleware`, `CsrfMiddleware` |
| `PATCH` | `/api/v1/user/cards/{id}/limits` | Update daily online purchase, ATM, and international limits | `UserAuthMiddleware`, `CsrfMiddleware` |
| `POST` | `/api/v1/user/cards/{id}/cvv` | Retrieve dynamic time-based CVV (requires PIN re-authentication) | `UserAuthMiddleware`, `CsrfMiddleware` |

### 2.3 Service Interface Blueprint
```php
namespace App\Domain\Contracts\Services;

interface UserCardServiceInterface
{
    public function getUserCards(int $userId): array;
    public function requestCard(int $userId, string $cardType, string $deliveryAddress): array;
    public function toggleFreezeStatus(int $userId, int $cardId, bool $freeze): bool;
    public function updateSpendLimits(int $userId, int $cardId, array $limits): bool;
    public function getDynamicCvv(int $userId, int $cardId, string $txnPin): string;
}
```

---

## 3. High-Yield Term Deposits & Investments (Fixed Deposits)

### 3.1 Architecture Layering
- **Controller**: `App\Presentation\Api\V1\User\UserInvestmentController`
- **Application Service**: `App\Application\Services\UserInvestmentService` (implements `UserInvestmentServiceInterface`)
- **Domain Contract**: `App\Domain\Contracts\Services\UserInvestmentServiceInterface`, `App\Domain\Contracts\Repositories\UserInvestmentRepositoryInterface`
- **Infrastructure Repository**: `App\Infrastructure\Repositories\UserInvestmentRepository`
- **Database Table**: `fixed_deposits` (Already provisioned in MySQL)

### 3.2 Proposed Endpoints
| Method | Route | Description | Auth |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/user/investments` | Retrieve active term deposits & portfolio stats | `UserAuthMiddleware` |
| `POST` | `/api/v1/user/investments/fd/create` | Open a new Term Deposit with debited funds | `UserAuthMiddleware`, `CsrfMiddleware` |
| `POST` | `/api/v1/user/investments/fd/{id}/close` | Premature or maturity liquidation into savings account | `UserAuthMiddleware`, `CsrfMiddleware` |
| `PATCH` | `/api/v1/user/investments/fd/{id}/auto-renew`| Toggle maturity auto-renewal preference | `UserAuthMiddleware`, `CsrfMiddleware` |

### 3.3 Service Interface Blueprint
```php
namespace App\Domain\Contracts\Services;

interface UserInvestmentServiceInterface
{
    public function getUserInvestments(int $userId): array;
    public function createFixedDeposit(int $userId, float $amount, int $tenureMonths, bool $autoRenew): array;
    public function closeFixedDeposit(int $userId, int $fdId): array;
    public function toggleAutoRenew(int $userId, int $fdId, bool $autoRenew): bool;
    public function calculateMaturityYield(float $principal, int $tenureMonths, float $interestRate): array;
}
```

---

## 4. Bharat BillPay (BBPS) Utilities & Recharges

### 4.1 Architecture Layering
- **Controller**: `App\Presentation\Api\V1\User\UserBillPaymentController`
- **Application Service**: `App\Application\Services\UserBillPaymentService` (implements `UserBillPaymentServiceInterface`)
- **Domain Contract**: `App\Domain\Contracts\Services\UserBillPaymentServiceInterface`, `App\Domain\Contracts\Repositories\BillPaymentRepositoryInterface`
- **Infrastructure Gateway**: `App\Infrastructure\Gateways\BbpsGateway` (NPCI Bharat BillPay API integration)
- **Infrastructure Repository**: `App\Infrastructure\Repositories\BillPaymentRepository`

### 4.2 Proposed Endpoints
| Method | Route | Description | Auth |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/user/billers` | List supported billers by category (Electricity, Gas, Telecom, etc.) | `UserAuthMiddleware` |
| `POST` | `/api/v1/user/billers/fetch` | Fetch live outstanding bill details via BBPS gateway | `UserAuthMiddleware` |
| `POST` | `/api/v1/user/billers/pay` | Execute real-time bill payment from savings account | `UserAuthMiddleware`, `CsrfMiddleware` |
| `GET` | `/api/v1/user/billers/history` | Paginated utility payment receipts and ledger entries | `UserAuthMiddleware` |

### 4.3 Service Interface Blueprint
```php
namespace App\Domain\Contracts\Services;

interface UserBillPaymentServiceInterface
{
    public function getCategoriesAndBillers(?string $category = null): array;
    public function fetchBillerDetails(string $billerId, string $consumerNumber): array;
    public function payBill(int $userId, string $billerId, string $consumerNumber, float $amount, string $pin): array;
    public function getPaymentHistory(int $userId, int $page = 1, int $perPage = 15): array;
}
```
