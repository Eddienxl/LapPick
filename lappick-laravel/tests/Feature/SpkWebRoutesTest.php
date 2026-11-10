<?php

namespace Tests\Feature;

use Tests\TestCase;

class SpkWebRoutesTest extends TestCase
{
    public function test_all_public_and_admin_spk_routes_render_successfully(): void
    {
        // 1. Home page (ID & EN) - verify no raw LaTeX delimiters or broken flag labels
        $this->get('/')
            ->assertStatus(200)
            ->assertSee('LapPick')
            ->assertDontSee('$CR')
            ->assertDontSee('🇮🇩');

        $this->get('/?lang=en')
            ->assertStatus(200)
            ->assertSee('Hybrid AHP + TOPSIS')
            ->assertSee('Catalog');

        // 2. Catalog & Laptop Detail (#326 HP 245 G8 Ryzen 5 - Student #1 Winner)
        $this->get('/catalog')->assertStatus(200)->assertSee('949');
        $this->get('/catalog/326')->assertStatus(200)->assertSee('245 g8');

        // 3. Wizard Preset Profiles (Student -> #326 HP & 30.30% C1 weight, Gamer -> #240 Acer Predator Helios 300)
        $this->get('/wizard?mode=preset&preset_slug=student')
            ->assertStatus(200)
            ->assertSee('[ID #326]')
            ->assertSee('30.30%')
            ->assertDontSee('\sqrt');

        $this->get('/wizard?mode=preset&preset_slug=gamer')
            ->assertStatus(200)
            ->assertSee('[ID #240]')
            ->assertSee('27.61%');

        // 4. Test Deliberately Inconsistent Custom Matrix (CR > 0.10 Guardrail)
        $inconsistentQuery = http_build_query([
            'mode' => 'custom_matrix',
            'matrix' => [
                0 => [1 => 9, 2 => 0.111, 3 => 9, 4 => 1, 5 => 1, 6 => 1, 7 => 1, 8 => 1],
                1 => [2 => 9, 3 => 1, 4 => 1, 5 => 1, 6 => 1, 7 => 1, 8 => 1],
                2 => [3 => 9, 4 => 1, 5 => 1, 6 => 1, 7 => 1, 8 => 1],
            ],
        ]);
        $this->get('/wizard?' . $inconsistentQuery)
            ->assertStatus(200)
            ->assertSee('Guardrail');

        // 5. Compare, History, Login, and Admin Panel
        $this->get('/compare?ids=326,240,499')->assertStatus(200)->assertSee('predator helios 300');
        $this->get('/history')->assertStatus(200)->assertSee('30.3%');
        $this->get('/login')->assertStatus(200);
        $this->get('/admin')->assertStatus(200)->assertSee('C1');
    }
}
