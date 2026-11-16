<?php

namespace App\Http\Middleware;

use App\Support\Localization\Locales;
use App\Support\Localization\LocalizedRoutes;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Middleware;
use Inertia\Support\Header;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->header(Header::INERTIA) && $request->header('X-Locale-Switch') === 'true'
            && LocalizedRoutes::key($request) !== null) {
            // Laravel normalizes query order; Inertia otherwise drops the visit's fragment.
            Inertia::preserveFragment();
        }

        if (! $request->header(Header::INERTIA) || ! $request->routeIs('login.store', 'two-factor.login.store')) {
            return parent::handle($request, $next);
        }

        return parent::handle($request, function (Request $request) use ($next): Response {
            $response = $next($request);

            if (! $response instanceof RedirectResponse || $request->user() === null) {
                return $response;
            }

            // Fortify resolves the destination; only local Filament pages need a full browser visit.
            $panelUrl = rtrim(route('filament.admin.pages.dashboard'), '/');
            $destination = str($response->getTargetUrl())->before('?')->before('#')->toString();

            return $destination === $panelUrl || str_starts_with($destination, $panelUrl.'/')
                ? Inertia::location($response)
                : $response;
        });
    }

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'locale' => LocalizedRoutes::locale($request),
            'availableLocales' => array_values(Locales::all()),
            'direction' => Locales::definition(LocalizedRoutes::locale($request))['direction'],
            'routeKey' => LocalizedRoutes::key($request),
            'localizedUrls' => LocalizedRoutes::alternates($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
