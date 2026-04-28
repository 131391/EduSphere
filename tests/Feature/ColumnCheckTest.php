<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Schema;

class ColumnCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_check_columns()
    {
        $columns = Schema::getColumnListing('hostel_attendances');
        fwrite(STDERR, print_r($columns, TRUE));
        $this->assertTrue(true);
    }
}
