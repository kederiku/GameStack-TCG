<?php

namespace Tests\Feature\Localization;

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\Game;
use App\Models\User;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsCatalog;
use Tests\Concerns\ForwardOnlyRefreshDatabase;
use Tests\TestCase;

class LocalizedMetadataTest extends TestCase
{
    use BuildsCatalog, ForwardOnlyRefreshDatabase;

    public function test_french_page_has_reciprocal_alternate(): void
    {
        $this->get('/fr')->assertOk()->assertSee('hreflang="en"', false)->assertSee('hreflang="x-default"', false);
        $this->get('/en')->assertSee('hreflang="fr"', false);
        foreach (['fr', 'en'] as $locale) {
            $head = $this->document('/'.$locale.'?utm_campaign=ignored');
            $this->assertSame($locale, $head->evaluate('string(/html/@lang)'));
            $this->assertSame('ltr', $head->evaluate('string(/html/@dir)'));
            $this->assertSame(url('/'.$locale), $head->evaluate('string(//head/link[@rel="canonical"]/@href)'));
            $this->assertSame(url('/fr'), $head->evaluate('string(//head/link[@hreflang="x-default"]/@href)'));
        }
    }

    public function test_catalog_selections_and_pagination_retain_their_identity_after_inertia_visits(): void
    {
        $game = Game::factory()->create(['slug' => 'pokemon']);
        $catalog = $this->catalog($game);
        $base = $this->expansion($catalog);
        $base->update(['name' => 'Cards 1']);
        DB::table('expansions')->insert(array_map(fn (int $i): array => [
            'catalog_id' => $catalog->id, 'key' => 'set-'.$i, 'type' => 'main', 'name' => 'Cards '.$i,
        ], range(2, 37)));
        $edition = $this->edition($base);
        $this->assertSame('en', $edition->language->code);
        $headers = ['X-Inertia' => 'true', 'X-Inertia-Version' => app(HandleInertiaRequests::class)->version(request())];
        foreach (['02' => 2, '999' => 2, '2bad' => 1] as $input => $page) {
            $suffix = $page === 1 ? '' : '?page=2';
            $this->get('/fr/pokemon?page='.$input, $headers)->assertOk()
                ->assertJsonPath('props.catalogData.expansions.current_page', $page)
                ->assertJsonPath('props.catalogData.expansions.data.0.key', $page === 2 ? 'set-37' : 'base-set')
                ->assertJsonPath('props.metadata.canonical', url('/fr/pokemon'.$suffix))
                ->assertJsonPath('props.metadata.alternates.en', url('/en/pokemon'.$suffix))
                ->assertJsonPath('props.metadata.alternates.x-default', url('/fr/pokemon'.$suffix));
        }
        $this->get('/fr/pokemon?page=02', $headers)->assertJsonPath('props.localizedUrls.en', '/en/pokemon?page=2');
        $this->get('/pokemon?lang=en&page=02')->assertRedirect('/en/pokemon?page=2');
        $this->get('/pokemon?lang=en&page=2bad')->assertRedirect('/en/pokemon');
        foreach ([
            ['/fr/pokemon?page=2&utm_medium=mail', '/fr/pokemon?page=2', null],
            ['/fr/pokemon?sort=recent&type=all&language=*&page=1', '/fr/pokemon', null],
            ['/fr/pokemon?utm_source=mail&page=2&q=%20cards%20', '/fr/pokemon?q=cards&page=2', 'noindex,follow'],
            ['/fr/pokemon/international/base-set?edition=EN&tracking=x', '/fr/pokemon/international/base-set?edition=en', 'noindex,follow'],
        ] as [$visit, $canonical, $robots]) {
            $data = $this->get($visit, $headers)->assertOk()->json('props.metadata');
            $this->assertIsArray($data);
            $this->assertSame(url($canonical), $data['canonical']);
            $this->assertSame($robots, $data['robots']);
            if ($robots === null) {
                $this->assertSame(url(str_replace('/fr/', '/en/', $canonical)), $data['alternates']['en']);
            }
        }
    }

    public function test_private_pages_and_signed_urls_expose_no_seo_links_or_secrets(): void
    {
        foreach (['/en/login', '/en/reset-password/private-token?email=private%40example.test', '/fr?signature=private-signature'] as $url) {
            $head = $this->document($url);
            $this->assertSame(0.0, $head->evaluate('count(//head/link[@rel="canonical" or @hreflang])'));
            $this->assertSame([
                'canonical' => null, 'alternates' => [], 'robots' => 'noindex,nofollow',
            ], $this->get($url, ['X-Inertia' => 'true', 'X-Inertia-Version' => app(HandleInertiaRequests::class)->version(request())])->assertOk()->json('props.metadata'));
            $this->assertStringContainsString('noindex', $head->evaluate('string(//head/meta[@name="robots"]/@content)'));
        }
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        foreach (['/en/settings/profile', '/fr/admin'] as $url) {
            $this->assertSame(0.0, $this->document($url)->evaluate('count(//head/link[@hreflang])'));
        }
    }

    private function document(string $url): DOMXPath
    {
        $dom = new DOMDocument;
        $dom->loadHTML($this->get($url)->assertOk()->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);

        return new DOMXPath($dom);
    }
}
