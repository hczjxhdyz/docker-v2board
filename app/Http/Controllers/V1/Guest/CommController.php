<?php

namespace App\Http\Controllers\V1\Guest;

use App\Http\Controllers\Controller;
use App\Utils\Dict;
use Illuminate\Support\Facades\Http;

class CommController extends Controller
{
    public function config()
    {
        return response([
            'data' => [
                'tos_url' => Setting('tos_url'),
                'is_email_verify' => (int)Setting('email_verify', 0) ? 1 : 0,
                'is_invite_force' => (int)Setting('invite_force', 0) ? 1 : 0,
                'email_whitelist_suffix' => (int)Setting('email_whitelist_enable', 0)
                    ? $this->getEmailSuffix()
                    : 0,
                'is_recaptcha' => (int)Setting('recaptcha_enable', 0) ? 1 : 0,
                'recaptcha_site_key' => Setting('recaptcha_site_key'),
                'app_description' => Setting('app_description'),
                'app_url' => Setting('app_url'),
                'logo' => Setting('logo'),
            ]
        ]);
    }

    private function getEmailSuffix()
    {
        $suffix = Setting('email_whitelist_suffix', Dict::EMAIL_WHITELIST_SUFFIX_DEFAULT);
        if (!is_array($suffix)) {
            return preg_split('/,/', $suffix);
        }
        return $suffix;
    }
}
