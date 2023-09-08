<?php

namespace App\Http\Controllers\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ConfigSave;
use App\Jobs\SendEmailJob;
use App\Models\Setting;
use App\Services\TelegramService;
use App\Utils\Dict;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;

class ConfigController extends Controller
{
    public function getEmailTemplate()
    {
        $path = resource_path('views/mail/');
        $files = array_map(function ($item) use ($path) {
            return str_replace($path, '', $item);
        }, glob($path . '*'));
        return response([
            'data' => $files
        ]);
    }

    public function getThemeTemplate()
    {
        $path = public_path('theme/');
        $files = array_map(function ($item) use ($path) {
            return str_replace($path, '', $item);
        }, glob($path . '*'));
        return response([
            'data' => $files
        ]);
    }

    public function testSendMail(Request $request)
    {
        $obj = new SendEmailJob([
            'email' => $request->user['email'],
            'subject' => 'This is v2board test email',
            'template_name' => 'notify',
            'template_value' => [
                'name' => SettingWithoutCache('app_name', 'V2Board'),
                'content' => 'This is v2board test email',
                'url' => SettingWithoutCache('app_url')
            ]
        ]);
        return response([
            'data' => true,
            'log' => $obj->handle()
        ]);
    }

    public function setTelegramWebhook(Request $request)
    {
        $hookUrl = url('/api/v1/guest/telegram/webhook?access_token=' . md5(SettingWithoutCache('telegram_bot_token', $request->input('telegram_bot_token'))));
        $telegramService = new TelegramService($request->input('telegram_bot_token'));
        $telegramService->getMe();
        $telegramService->setWebhook($hookUrl);
        return response([
            'data' => true
        ]);
    }

    public function fetch(Request $request)
    {
        $key = $request->input('key');
        $data = [
            'invite' => [
                'invite_force' => (int)SettingWithoutCache('invite_force', 0),
                'invite_commission' => SettingWithoutCache('invite_commission', 10),
                'invite_gen_limit' => SettingWithoutCache('invite_gen_limit', 5),
                'invite_never_expire' => SettingWithoutCache('invite_never_expire', 0),
                'commission_first_time_enable' => SettingWithoutCache('commission_first_time_enable', 1),
                'commission_auto_check_enable' => SettingWithoutCache('commission_auto_check_enable', 1),
                'commission_withdraw_limit' => SettingWithoutCache('commission_withdraw_limit', 100),
                'commission_withdraw_method' => SettingWithoutCache('commission_withdraw_method', Dict::WITHDRAW_METHOD_WHITELIST_DEFAULT),
                'withdraw_close_enable' => SettingWithoutCache('withdraw_close_enable', 0),
                'commission_distribution_enable' => SettingWithoutCache('commission_distribution_enable', 0),
                'commission_distribution_l1' => SettingWithoutCache('commission_distribution_l1'),
                'commission_distribution_l2' => SettingWithoutCache('commission_distribution_l2'),
                'commission_distribution_l3' => SettingWithoutCache('commission_distribution_l3')
            ],
            'site' => [
                'logo' => SettingWithoutCache('logo'),
                'force_https' => (int)SettingWithoutCache('force_https', 0),
                'stop_register' => (int)SettingWithoutCache('stop_register', 0),
                'app_name' => SettingWithoutCache('app_name', 'V2Board'),
                'app_description' => SettingWithoutCache('app_description', 'V2Board is best!'),
                'app_url' => SettingWithoutCache('app_url'),
                'subscribe_url' => SettingWithoutCache('subscribe_url'),
                'try_out_plan_id' => (int)SettingWithoutCache('try_out_plan_id', 0),
                'try_out_hour' => (int)SettingWithoutCache('try_out_hour', 1),
                'tos_url' => SettingWithoutCache('tos_url'),
                'currency' => SettingWithoutCache('currency', 'CNY'),
                'currency_symbol' => SettingWithoutCache('currency_symbol', '¥'),
            ],
            'subscribe' => [
                'plan_change_enable' => (int)SettingWithoutCache('plan_change_enable', 1),
                'reset_traffic_method' => (int)SettingWithoutCache('reset_traffic_method', 0),
                'surplus_enable' => (int)SettingWithoutCache('surplus_enable', 1),
                'new_order_event_id' => (int)SettingWithoutCache('new_order_event_id', 0),
                'renew_order_event_id' => (int)SettingWithoutCache('renew_order_event_id', 0),
                'change_order_event_id' => (int)SettingWithoutCache('change_order_event_id', 0),
                'show_info_to_server_enable' => (int)SettingWithoutCache('show_info_to_server_enable', 0)
            ],
            'frontend' => [
                'frontend_theme' => SettingWithoutCache('frontend_theme', 'v2board'),
                'frontend_theme_sidebar' => SettingWithoutCache('frontend_theme_sidebar', 'light'),
                'frontend_theme_header' => SettingWithoutCache('frontend_theme_header', 'dark'),
                'frontend_theme_color' => SettingWithoutCache('frontend_theme_color', 'default'),
                'frontend_background_url' => SettingWithoutCache('frontend_background_url'),
            ],
            'server' => [
                'server_token' => SettingWithoutCache('server_token'),
                'server_pull_interval' => SettingWithoutCache('server_pull_interval', 60),
                'server_push_interval' => SettingWithoutCache('server_push_interval', 60),
            ],
            'email' => [
                'email_template' => SettingWithoutCache('email_template', 'default'),
                'email_host' => SettingWithoutCache('email_host'),
                'email_port' => SettingWithoutCache('email_port'),
                'email_username' => SettingWithoutCache('email_username'),
                'email_password' => SettingWithoutCache('email_password'),
                'email_encryption' => SettingWithoutCache('email_encryption'),
                'email_from_address' => SettingWithoutCache('email_from_address')
            ],
            'telegram' => [
                'telegram_bot_enable' => SettingWithoutCache('telegram_bot_enable', 0),
                'telegram_bot_token' => SettingWithoutCache('telegram_bot_token'),
                'telegram_discuss_link' => SettingWithoutCache('telegram_discuss_link')
            ],
            'app' => [
                'windows_version' => SettingWithoutCache('windows_version'),
                'windows_download_url' => SettingWithoutCache('windows_download_url'),
                'macos_version' => SettingWithoutCache('macos_version'),
                'macos_download_url' => SettingWithoutCache('macos_download_url'),
                'android_version' => SettingWithoutCache('android_version'),
                'android_download_url' => SettingWithoutCache('android_download_url')
            ],
            'safe' => [
                'email_verify' => (int)SettingWithoutCache('email_verify', 0),
                'safe_mode_enable' => (int)SettingWithoutCache('safe_mode_enable', 0),
                'secure_path' => SettingWithoutCache('secure_path', SettingWithoutCache('frontend_admin_path', hash('crc32b', config('app.key')))),
                'email_whitelist_enable' => (int)SettingWithoutCache('email_whitelist_enable', 0),
                'email_whitelist_suffix' => SettingWithoutCache('email_whitelist_suffix', Dict::EMAIL_WHITELIST_SUFFIX_DEFAULT),
                'email_gmail_limit_enable' => SettingWithoutCache('email_gmail_limit_enable', 0),
                'recaptcha_enable' => (int)SettingWithoutCache('recaptcha_enable', 0),
                'recaptcha_key' => SettingWithoutCache('recaptcha_key'),
                'recaptcha_site_key' => SettingWithoutCache('recaptcha_site_key'),
                'register_limit_by_ip_enable' => (int)SettingWithoutCache('register_limit_by_ip_enable', 0),
                'register_limit_count' => SettingWithoutCache('register_limit_count', 3),
                'register_limit_expire' => SettingWithoutCache('register_limit_expire', 60),
                'password_limit_enable' => (int)SettingWithoutCache('password_limit_enable', 1),
                'password_limit_count' => SettingWithoutCache('password_limit_count', 5),
                'password_limit_expire' => SettingWithoutCache('password_limit_expire', 60)
            ]
        ];
        // 获取数据库设置列表
        $settings = Setting::all()->pluck('value','name')->toArray();
        foreach($data as $k1 => $values){
            foreach($values as $k2 => $v){
                if(array_key_exists($k2, $settings)){
                    $data[$k1][$k2] = $settings[$k2];
                }
            }
        }
        if ($key && isset($data[$key])) {
            return response([
                'data' => [
                    $key => $data[$key]
                ]
            ]);
        };
        // TODO: default should be in Dict
        return response([
            'data' => $data
        ]);
    }

    public function save(ConfigSave $request)
    {
        $data = $request->validated();
        $config = config('v2board');
        foreach (ConfigSave::RULES as $k => $v) {
            if (!in_array($k, array_keys(ConfigSave::RULES))) {
                unset($config[$k]);
                continue;
            }
            if (array_key_exists($k, $data)) {
                $value = $data[$k];
                if (is_array($value)) $value = json_encode($value);
                Setting::updateOrCreate(
                    ['name' => $k],
                    ['name' => $k, 'value' => $value]
                );
                // $config[$k] = $data[$k];
            }
        }
        // $data = var_export($config, 1);
        // if (!File::put(base_path() . '/config/v2board.php', "<?php\n return $data ;")) {
        //     abort(500, '修改失败');
        // }
        // if (function_exists('opcache_reset')) {
        //     if (opcache_reset() === false) {
        //         abort(500, '缓存清除失败，请卸载或检查opcache配置状态');
        //     }
        // }
        // Artisan::call('config:cache');
        return response([
            'data' => true
        ]);
    }
}
