<?php

namespace Tests\Feature;

use App\Enums\LevelExpired;
use App\Models\Domain;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DomainExpiryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{int, LevelExpired, string}>
     */
    public static function batasLevel(): array
    {
        return [
            'lewat 5 hari' => [-5, LevelExpired::Critical, 'Expired 5 hari lalu'],
            'hari ini' => [0, LevelExpired::Critical, 'Expired hari ini'],
            '7 hari' => [7, LevelExpired::Critical, '7 hari lagi'],
            '8 hari' => [8, LevelExpired::Warning, '8 hari lagi'],
            '30 hari' => [30, LevelExpired::Warning, '30 hari lagi'],
            '31 hari' => [31, LevelExpired::Normal, '31 hari lagi'],
        ];
    }

    #[DataProvider('batasLevel')]
    public function test_level_and_label_follow_remaining_days(int $hari, LevelExpired $level, string $label): void
    {
        $domain = Domain::factory()->expiredDalam($hari)->create();

        $this->assertSame($hari, $domain->sisa_hari);
        $this->assertSame($level, $domain->level_expired);
        $this->assertSame($label, $domain->label_expired);
    }

    public function test_akan_expired_scope_only_includes_upcoming_within_range(): void
    {
        Domain::factory()->expiredDalam(-1)->create();
        $hariIni = Domain::factory()->expiredDalam(0)->create();
        $batas = Domain::factory()->expiredDalam(30)->create();
        Domain::factory()->expiredDalam(31)->create();

        $hasil = Domain::akanExpired()->pluck('id');

        $this->assertEqualsCanonicalizing([$hariIni->id, $batas->id], $hasil->all());
        $this->assertSame(1, Domain::akanExpired(7)->count());
    }
}
