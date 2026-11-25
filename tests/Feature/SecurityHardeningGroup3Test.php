<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class SecurityHardeningGroup3Test extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::createWithRole([
            'name' => 'Budi Security',
            'email' => 'budi_sec3@banksampah.test',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ], 'nasabah');

        RateLimiter::clear(StrLower('budi_sec3@banksampah.test') . '|127.0.0.1');
        RateLimiter::clear('login_ip:127.0.0.1');
    }

    public function test_login_via_nama_gagal()
    {
        $response = $this->post('/login', [
            'email' => 'Budi Security', // input nama, bukan email/no_rek/hp
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_failed_login_recorded_in_audit_log()
    {
        AuditLog::truncate();

        $response = $this->post('/login', [
            'email' => 'budi_sec3@banksampah.test',
            'password' => 'passwordsalah',
        ]);

        $response->assertSessionHasErrors(['email']);

        $audit = AuditLog::where('action', 'failed_login')
            ->where('description', 'like', "%budi_sec3@banksampah.test%")
            ->first();

        $this->assertNotNull($audit);
        $this->assertEquals('Autentikasi', $audit->module);
        // Pastikan password tidak bocor ke log
        $this->assertStringNotContainsString('passwordsalah', $audit->description);
    }

    public function test_6_login_gagal_tidak_menghasilkan_http_500()
    {
        for ($i = 1; $i <= 6; $i++) {
            $response = $this->post('/login', [
                'email' => 'budi_sec3@banksampah.test',
                'password' => 'wrongpassword',
            ]);

            // Status harus berupa redirect back with errors (302), bukan 500 error
            $this->assertNotEquals(500, $response->status());
        }
    }

    public function test_register_route_returns_404()
    {
        $response = $this->get('/register');
        $response->assertStatus(404);

        $responsePost = $this->post('/register', [
            'name' => 'Hacker',
            'email' => 'hacker@banksampah.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $responsePost->assertStatus(404);
    }
}

function StrLower($val) {
    return mb_strtolower($val, 'UTF-8');
}
