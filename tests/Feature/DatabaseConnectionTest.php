<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Tests\TestCase;

class DatabaseConnectionTest extends TestCase
{
    public function test_mysql_is_the_only_database_connection(): void
    {
        $this->assertSame('mysql', config('database.default'));
        $this->assertSame('mysql', DB::connection()->getDriverName());
        $this->assertSame('mysql', config('queue.failed.database'));
        $this->assertSame('mysql', config('queue.batching.database'));

        // Laravel's built-in SQLite connection is switched off
        $this->assertNull(config('database.connections.sqlite'));
        $this->expectException(InvalidArgumentException::class);
        DB::connection('sqlite');
    }

    public function test_a_clear_error_is_shown_when_mysql_is_not_running(): void
    {
        // Nothing listens on port 1, just like when MySQL is stopped
        config(['database.connections.mysql.port' => 1]);
        DB::purge('mysql');

        $this->actingAs(User::factory()->make(['role' => 'staff']))
            ->get('/dashboard')
            ->assertStatus(503)
            ->assertSee('Cannot connect to the MySQL database')
            ->assertSee('XAMPP Control Panel');
    }
}
