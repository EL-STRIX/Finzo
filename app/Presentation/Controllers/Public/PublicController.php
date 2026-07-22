<?php
namespace App\Presentation\Controllers\Public;

use App\Infrastructure\Repositories\BaseRepository;

class PublicController
{
    private BaseRepository $repository;

    public function __construct()
    {
        $this->repository = current(array_filter(
            // Just instantiate an anonymous repo to get PDO for raw query (for now)
            // or just use Database::getInstance() inside a new PublicRepository
            [new class extends BaseRepository {
                public function getStats() {
                    $pdo = $this->pdo;
                    $months = [];
                    for ($i = 5; $i >= 0; $i--) {
                        $months[] = date('Y-m', strtotime("-$i months"));
                    }
                    $deposits = array_fill(0, 6, 0);
                    $withdrawals = array_fill(0, 6, 0);
                    $transfers = array_fill(0, 6, 0);

                    $stmt = $pdo->query("
                        SELECT 
                            DATE_FORMAT(created_at, '%Y-%m') as month_str,
                            type,
                            SUM(amount) as total_amount
                        FROM transactions
                        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                        AND status = 'completed'
                        GROUP BY month_str, type
                    ");
                    $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                    foreach ($results as $row) {
                        $monthStr = $row['month_str'];
                        $type = $row['type'];
                        $valueInK = max(0, round($row['total_amount'] / 1000, 1));
                        if ($valueInK == 0 && $row['total_amount'] > 0) {
                            $valueInK = 0.1;
                        }

                        $index = array_search($monthStr, $months);
                        if ($index !== false) {
                            if ($type === 'deposit') { $deposits[$index] = $valueInK; }
                            elseif ($type === 'withdrawal') { $withdrawals[$index] = $valueInK; }
                            elseif ($type === 'transfer') { $transfers[$index] = $valueInK; }
                        }
                    }

                    $labels = array_map(function ($m) { return date('M', strtotime($m . '-01')); }, $months);

                    $calcChange = function($current, $prev) {
                        if ($prev == 0) return $current > 0 ? 100 : 0;
                        return round((($current - $prev) / $prev) * 100, 1);
                    };

                    return [
                        'labels' => $labels,
                        'datasets' => [
                            'deposits' => $deposits,
                            'withdrawals' => $withdrawals,
                            'transfers' => $transfers
                        ],
                        'changes' => [
                            'deposits' => $calcChange($deposits[5], $deposits[4]),
                            'withdrawals' => $calcChange($withdrawals[5], $withdrawals[4]),
                            'transfers' => $calcChange($transfers[5], $transfers[4])
                        ]
                    ];
                }
            }]
        ));
    }

    public function chartStats()
    {
        try {
            $data = $this->repository->getStats();
            $data['success'] = true;
            return $data;
        } catch (\PDOException $e) {
            throw new \App\Shared\Core\Exceptions\ApiException('Database error', 500);
        }
    }
}
