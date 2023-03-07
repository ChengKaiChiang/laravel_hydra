<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Ory\Hydra\Client\Api\AdminApi;
use Ory\Hydra\Client\Api\PublicApi;
use Ory\Hydra\Client\Model\AcceptConsentRequest;
use Ory\Hydra\Client\Model\AcceptLoginRequest;

Route::middleware('guest')->group(function () {
    Route::get('login', function () {
        $authorizeUri = 'http://127.0.0.1:4444/oauth2/auth';

        $query = \Illuminate\Support\Arr::query([
            'client_id' => 'ray',
            'redirect_uri' => 'http://127.0.0.1:8000/callback',
            'scope' => 'openid',
            'response_type' => 'code',
            'state' => '1a2b3c4d',
        ]);

        return redirect($authorizeUri . '?' . $query);
    })->name('login');

    Route::get('/oauth2/login', function (Request $request, AdminApi $adminApi) {
        $loginChallenge = $request->input('login_challenge');
        $adminApi->getLoginRequest($loginChallenge);

        // 帳密正確並取得使用者的資訊後，執行下面的程式碼
        $acceptLoginRequest = new AcceptLoginRequest([
            'remember' => true,
            'rememberFor' => 86400,
            'subject' => '3367',
        ]);
        $completedRequest = $adminApi->acceptLoginRequest($loginChallenge, $acceptLoginRequest);
        Log::info('redirect to', [
            'redirect_to' => $completedRequest,
        ]);
        return redirect()->away($completedRequest->getRedirectTo());
    })->name('oauth2.login');

    Route::get('/oauth2/consent', function(Request $request, AdminApi $adminApi) {
        $consentChallenge = $request->input('consent_challenge');
        $consentRequest = $adminApi->getConsentRequest($consentChallenge);
        Log::info('get consent request', json_decode((string)$consentRequest, true));

        $acceptConsentRequest = new AcceptConsentRequest([
            'grantScope' => ['openid'],
            'remember' => false,
            'rememberFor' => 0,
        ]);
        $completedRequest = $adminApi->acceptConsentRequest($consentChallenge, $acceptConsentRequest);
        return redirect()->away($completedRequest->getRedirectTo());
    })->name('oauth2.consent');

    Route::get('callback', function(Request $request, PublicApi $hydra) {
        dump($request->all());
        $redirectUri = 'http://127.0.0.1:8000/callback';
        $tokenResponse = $hydra->oauth2Token(
            grantType: 'authorization_code',
            code: $request->input('code'),
            redirectUri: $redirectUri
        );
        dump(json_decode((string)$tokenResponse, true));
        return response('拿到身分驗證回應了');
    });
});
