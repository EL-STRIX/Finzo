<?php
namespace App\Shared\Core\Database;

class ApiQueryBuilder
{
    private array $allowedFilters = [];
    private array $searchFields = [];
    private array $allowedSorts = [];
    private string $defaultSortField = 'id';
    private string $defaultSortOrder = 'DESC';

    public function __construct(array $allowedFilters = [], array $searchFields = [], array $allowedSorts = [])
    {
        $this->allowedFilters = $allowedFilters;
        $this->searchFields = $searchFields;
        $this->allowedSorts = $allowedSorts;
    }

    public function build(): array
    {
        $bindings = [];
        $whereClauses = [];

        $params = $_GET;

        // 1. Pagination
        $page = max(1, (int)($params['page'] ?? 1));
        $perPage = max(1, min(100, (int)($params['per_page'] ?? 20))); // Max 100 per page limit
        $offset = ($page - 1) * $perPage;

        // 2. Sorting
        $sortField = $this->defaultSortField;
        if (!empty($params['sort']) && in_array($params['sort'], $this->allowedSorts)) {
            // Mitigate SQL Injection on ORDER BY
            $sortField = preg_replace('/[^a-zA-Z0-9_]/', '', $params['sort']);
        }
        
        $sortOrder = $this->defaultSortOrder;
        if (!empty($params['order']) && in_array(strtoupper($params['order']), ['ASC', 'DESC'])) {
            $sortOrder = strtoupper($params['order']);
        }
        
        $orderSql = "ORDER BY {$sortField} {$sortOrder}";

        // 3. Search (Partial Match across configured fields)
        if (!empty($params['search']) && !empty($this->searchFields)) {
            $searchTerm = $params['search'];
            $searchClauses = [];
            foreach ($this->searchFields as $field) {
                // Ensure field name is clean
                $cleanField = preg_replace('/[^a-zA-Z0-9_\.]/', '', $field);
                $searchClauses[] = "{$cleanField} LIKE ?";
                $bindings[] = '%' . $searchTerm . '%';
            }
            if (!empty($searchClauses)) {
                $whereClauses[] = '(' . implode(' OR ', $searchClauses) . ')';
            }
        }

        // 4. Exact Match Filters
        foreach ($this->allowedFilters as $filter) {
            if (isset($params[$filter]) && $params[$filter] !== '') {
                $cleanFilter = preg_replace('/[^a-zA-Z0-9_\.]/', '', $filter);
                $whereClauses[] = "{$cleanFilter} = ?";
                $bindings[] = $params[$filter];
            }
        }

        // 5. Date Range Filters (created_at)
        if (!empty($params['start_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $params['start_date'])) {
            $whereClauses[] = "DATE(created_at) >= ?";
            $bindings[] = $params['start_date'];
        }
        if (!empty($params['end_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $params['end_date'])) {
            $whereClauses[] = "DATE(created_at) <= ?";
            $bindings[] = $params['end_date'];
        }

        // Assemble WHERE SQL
        $whereSql = empty($whereClauses) ? '1=1' : implode(' AND ', $whereClauses);

        return [
            'where_sql' => $whereSql,
            'order_sql' => $orderSql,
            'limit' => $perPage,
            'offset' => $offset,
            'bindings' => $bindings,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage
            ]
        ];
    }
    
    public static function paginate(int $totalCount, array $paginationResult): array
    {
        $page = $paginationResult['current_page'];
        $perPage = $paginationResult['per_page'];
        $lastPage = max(1, ceil($totalCount / $perPage));
        
        return [
            'total' => $totalCount,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => $lastPage,
            'has_next' => $page < $lastPage,
            'has_previous' => $page > 1
        ];
    }
}