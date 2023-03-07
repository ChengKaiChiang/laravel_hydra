<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Ory\Hydra\Client\Api\AdminApi;
use Ory\Hydra\Client\Api\PublicApi;
use Ory\Hydra\Client\Model\AcceptConsentRequest;
use Ory\Hydra\Client\Model\AcceptLoginRequest;
use Ory\Hydra\Client\Model\JsonError;
use Ory\Hydra\Client\Model\Oauth2TokenResponse;

class Hydra extends Command
{
    protected $signature = 'hydra {--times=1000000}';

    public function handle(AdminApi $admin, PublicApi $public)
    {
        $times = $this->option('times');

        for ($i = 0; $i < $times; $i++) {
            $start = microtime(true);
            $this->do($admin, $public);

            $this->line(sprintf(
                'OK, Use time: %s ms ,  Memory: %.3f MB',
                (int)((microtime(true) - $start) * 1000),
                memory_get_usage() / 1024 / 1024,
            ));
        }

        return 0;
    }

    private function do(AdminApi $admin, PublicApi $public): JsonError|Oauth2TokenResponse
    {
        $authorizeUri = 'http://127.0.0.1:4444/oauth2/auth';

        $query = Arr::query([
            'client_id' => 'ray',
            'redirect_uri' => 'http://127.0.0.1:8000/callback',
            'scope' => 'openid',
            'response_type' => 'code',
            'state' => Str::random(),
        ]);

        $response = Http::withoutRedirecting()->get($authorizeUri . '?' . $query);

        $csrfToken = $response->cookies()->getCookieByName('oauth2_authentication_csrf')->getValue();

        parse_str(parse_url($response->header('Location'), PHP_URL_QUERY), $query);

        // 帳密正確並取得使用者的資訊後，執行下面的程式碼
        $acceptLoginRequest = new AcceptLoginRequest([
            'remember' => true,
            'rememberFor' => 86400,
            'subject' => Str::random(),
        ]);
        $completedRequest = $admin->acceptLoginRequest($query['login_challenge'], $acceptLoginRequest);

        $response = Http::withoutRedirecting()
            ->withCookies(['oauth2_authentication_csrf' => $csrfToken], '127.0.0.1')
            ->get($completedRequest->getRedirectTo());

        $csrfToken = $response->cookies()->getCookieByName('oauth2_consent_csrf')->getValue();

        parse_str(parse_url($response->header('Location'), PHP_URL_QUERY), $query);

        $acceptConsentRequest = new AcceptConsentRequest([
            'grantScope' => ['openid'],
            'remember' => false,
            'rememberFor' => 0,
        ]);

        $completedRequest = $admin->acceptConsentRequest($query['consent_challenge'], $acceptConsentRequest);

        $response = Http::withoutRedirecting()
            ->withCookies(['oauth2_consent_csrf' => $csrfToken], '127.0.0.1')
            ->get($completedRequest->getRedirectTo());

        parse_str(parse_url($response->header('Location'), PHP_URL_QUERY), $query);

        return $public->oauth2Token(
            grantType: 'authorization_code',
            code: $query['code'],
            redirectUri: 'http://127.0.0.1:8000/callback',
        );
    }
}
