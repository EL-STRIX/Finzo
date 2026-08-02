<?php

namespace App\Application\Services;

class LoanCalculationService
{
    /**
     * Calculate monthly EMI and generate amortization schedule.
     * 
     * @param float $principal
     * @param float $annualInterestRate (e.g., 10.5 for 10.5%)
     * @param int $tenureMonths
     * @param string $disbursementDate 'Y-m-d'
     * @return array
     */
    public function generateAmortizationSchedule(float $principal, float $annualInterestRate, int $tenureMonths, string $disbursementDate): array
    {
        $monthlyRate = ($annualInterestRate / 12) / 100;
        
        // Handle 0% interest case
        if ($monthlyRate == 0) {
            $emi = $principal / $tenureMonths;
        } else {
            $emi = ($principal * $monthlyRate * pow(1 + $monthlyRate, $tenureMonths)) / (pow(1 + $monthlyRate, $tenureMonths) - 1);
        }

        $emi = round($emi, 2);
        
        $schedule = [];
        $balance = $principal;
        
        $date = new \DateTime($disbursementDate);
        
        for ($i = 1; $i <= $tenureMonths; $i++) {
            $date->modify('+1 month');
            
            $interestComponent = round($balance * $monthlyRate, 2);
            $principalComponent = round($emi - $interestComponent, 2);
            
            // Adjust the last month to clear out any rounding errors
            if ($i === $tenureMonths) {
                $principalComponent = $balance;
                $emi = $principalComponent + $interestComponent;
                $balance = 0;
            } else {
                $balance -= $principalComponent;
            }
            
            $schedule[] = [
                'installment_no' => $i,
                'due_date' => $date->format('Y-m-d'),
                'emi_amount' => $emi,
                'principal_component' => $principalComponent,
                'interest_component' => $interestComponent,
                'outstanding_balance' => round($balance, 2)
            ];
        }

        return $schedule;
    }
}
