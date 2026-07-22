<?php
namespace Tests\Feature\Api;

use PHPUnit\Framework\TestCase;
use App\Shared\Core\Database\ApiQueryBuilder;

class ApiPaginationTest extends TestCase
{
    /** @test */
    public function it_calculates_pagination_metadata_correctly()
    {
        $totalCount = 105;
        $paginationResult = [
            'current_page' => 3,
            'per_page' => 20
        ];
        
        $meta = ApiQueryBuilder::paginate($totalCount, $paginationResult);
        
        $this->assertEquals(105, $meta['total']);
        $this->assertEquals(3, $meta['current_page']);
        $this->assertEquals(20, $meta['per_page']);
        $this->assertEquals(6, $meta['last_page']); // 105/20 = ceil(5.25) = 6
        $this->assertTrue($meta['has_next']);
        $this->assertTrue($meta['has_previous']);
    }

    /** @test */
    public function it_prevents_sql_injection_on_sort_fields()
    {
        $_GET = [
            'sort' => 'id; DROP TABLE users;',
            'order' => 'ASC'
        ];

        $builder = new ApiQueryBuilder([], [], ['id', 'created_at']);
        $query = $builder->build();
        
        // Since 'id; DROP TABLE users;' is not in allowedSorts, it falls back to default 'id'
        $this->assertEquals('ORDER BY id ASC', $query['order_sql']);
    }
}