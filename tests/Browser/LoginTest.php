<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->browse(function (Browser $browser) {
            for ($i = 0; $i < 1000000; $i++) {
                $browser->visit('http://127.0.0.1:8000/login');
                $browser->deleteCookie('oauth2_authentication_session');
                $browser->deleteCookie('oauth2_consent_csrf');
                $browser->deleteCookie('oauth2_authentication_csrf');
            }
        });
    }
}
