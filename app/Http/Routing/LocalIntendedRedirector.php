<?php

namespace App\Http\Routing;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

final class LocalIntendedRedirector extends Redirector
{
    /** @param array<string, string> $headers */
    public function intended($default = '/', $status = 302, $headers = [], $secure = null): RedirectResponse
    {
        $target = $this->getIntendedUrl();
        if (! is_string($target) || ! self::isLocal($target, $this->generator->getRequest())) {
            $this->session->forget('url.intended');
        }

        return parent::intended($default, $status, $headers, $secure);
    }

    private static function isLocal(string $target, Request $request): bool
    {
        if ($target === '' || preg_match('/[\\\\\x00-\x20\x7f]/', $target) || str_starts_with($target, '//')) {
            return false;
        }
        if (str_starts_with($target, '/')) {
            return true;
        }
        $parts = parse_url($target);

        return is_array($parts) && isset($parts['host'], $parts['scheme'])
            && ! isset($parts['user']) && ! isset($parts['pass'])
            && strtolower($parts['scheme']) === $request->getScheme()
            && strtolower($parts['host']) === strtolower($request->getHost())
            && ($parts['port'] ?? ($request->isSecure() ? 443 : 80)) === $request->getPort();
    }
}
