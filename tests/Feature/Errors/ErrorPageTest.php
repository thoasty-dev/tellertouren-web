<?php

namespace Tests\Feature\Errors;

use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    public function test_unknown_urls_render_the_branded_404_page_with_navigation(): void
    {
        $response = $this->get('/diese-tour-gibt-es-nicht');

        $response->assertNotFound()
            ->assertSee('Seite nicht gefunden')
            ->assertSee('<title>Seite nicht gefunden | Tellertouren</title>', false)
            ->assertSee('<meta name="robots" content="noindex">', false);

        $this->assertPageChromeIsPresent($response->getContent());
    }

    public function test_server_errors_render_the_branded_500_page_with_navigation(): void
    {
        config(['app.debug' => false]);

        Route::get('/__test-explosion', fn () => throw new RuntimeException('Boom'));

        $response = $this->get('/__test-explosion');

        $response->assertStatus(500)
            ->assertSee('Da ist etwas schiefgelaufen')
            ->assertDontSee('Boom');

        $this->assertPageChromeIsPresent($response->getContent());
    }

    public function test_status_codes_without_a_dedicated_view_fall_back_to_the_range_template(): void
    {
        Route::get('/__test-teapot', fn () => abort(418));
        Route::get('/__test-gateway', fn () => abort(504));

        $this->get('/__test-teapot')
            ->assertStatus(418)
            ->assertSee('418')
            ->assertSee('Diese Anfrage konnten wir nicht verarbeiten');

        $this->get('/__test-gateway')
            ->assertStatus(504)
            ->assertSee('504')
            ->assertSee('Da ist etwas schiefgelaufen');
    }

    public function test_dedicated_views_exist_for_the_remaining_handled_status_codes(): void
    {
        foreach ([403 => 'Kein Zugriff', 419 => 'Seite abgelaufen', 429 => 'Zu viele Anfragen', 503 => 'Kurze Wartungspause'] as $status => $headline) {
            Route::get("/__test-status-{$status}", fn () => abort($status));

            $this->get("/__test-status-{$status}")
                ->assertStatus($status)
                ->assertSee((string) $status)
                ->assertSee($headline);
        }
    }

    private function assertPageChromeIsPresent(string $content): void
    {
        $this->assertStringContainsString('aria-label="Hauptnavigation"', $content);
        $this->assertStringContainsString('href="'.route('articles.index.get').'"', $content);
        $this->assertStringContainsString('href="'.route('minigame.index').'"', $content);
        $this->assertStringContainsString('href="'.route('pages.site-notice.get').'"', $content);
    }
}
