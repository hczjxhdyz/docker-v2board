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
                'name' => Setting('app_name', 'V2Board'),
                'content' => 'This is v2board test email',
                'url' => Setting('app_url')
            ]
        ]);
        return response([
            'data' => true,
            'log' => $obj->handle()
        ]);
    }

    public function setTelegramWebhook(Request $request)
    {
        $hookUrl = url('/api/v1/guest/telegram/webhook?access_token=' . md5(Setting('telegram_bot_token', $request->input('telegram_bot_token'))));
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
                'invite_force' => (int)Setting('invite_force', 0),
                'invite_commission' => Setting('invite_commission', 10),
                'invite_gen_limit' => Setting('invite_gen_limit', 5),
                'invite_never_expire' => Setting('invite_never_expire', 0),
                'commission_first_time_enable' => Setting('commission_first_time_enable', 1),
                'commission_auto_check_enable' => Setting('commission_auto_check_enable', 1),
                'commission_withdraw_limit' => Setting('commission_withdraw_limit', 100),
                'commission_withdraw_method' => Setting('commission_withdraw_method', Dict::WITHDRAW_METHOD_WHITELIST_DEFAULT),
                'withdraw_close_enable' => Setting('withdraw_close_enable', 0),
                'commission_distribution_enable' => Setting('commission_distribution_enable', 0),
                'commission_distribution_l1' => Setting('commission_distribution_l1'),
                'commission_distribution_l2' => Setting('commission_distribution_l2'),
                'commission_distribution_l3' => Setting('commission_distribution_l3')
            ],
            'site' => [
                'logo' => Setting('logo'),
                'force_https' => (int)Setting('force_https', 0),
                'stop_register' => (int)Setting('stop_register', 0),
                'app_name' => Setting('app_name', 'V2Board'),
                'app_description' => Setting('app_description', 'V2Board is best!'),
                'app_url' => Setting('app_url'),
                'subscribe_url' => Setting('subscribe_url'),
                'try_out_plan_id' => (int)Setting('try_out_plan_id', 0),
                'try_out_hour' => (int)Setting('try_out_hour', 1),
                'tos_url' => Setting('tos_url'),
                'currency' => Setting('currency', 'CNY'),
                'currency_symbol' => Setting('currency_symbol', '¥'),
            ],
            'subscribe' => [
                'plan_change_enable' => (int)Setting('plan_change_enable', 1),
                'reset_traffic_method' => (int)Setting('reset_traffic_method', 0),
                'surplus_enable' => (int)Setting('surplus_enable', 1),
                'new_order_event_id' => (int)Setting('new_order_event_id', 0),
                'renew_order_event_id' => (int)Setting('renew_order_event_id', 0),
                'change_order_event_id' => (int)Setting('change_order_event_id', 0),
                'show_info_to_server_enable' => (int)Setting('show_info_to_server_enable', 0)
            ],
            'frontend' => [
                'frontend_theme' => Setting('frontend_theme', 'v2board'),
                'frontend_theme_sidebar' => Setting('frontend_theme_sidebar', 'light'),
                'frontend_theme_header' => Setting('frontend_theme_header', 'dark'),
                'frontend_theme_color' => Setting('frontend_theme_color', 'default'),
                'frontend_background_url' => Setting('frontend_background_url'),
            ],
            'server' => [
                'server_token' => Setting('server_token'),
                'server_pull_interval' => Setting('server_pull_interval', 60),
                'server_push_interval' => Setting('server_push_interval', 60),
            ],
            'email' => [
                'email_template' => Setting('email_template', 'default'),
                'email_host' => Setting('email_host'),
                'email_port' => Setting('email_port'),
                'email_username' => Setting('email_username'),
                'email_password' => Setting('email_password'),
                'email_encryption' => Setting('email_encryption'),
                'email_from_address' => Setting('email_from_address')
            ],
            'telegram' => [
                'telegram_bot_enable' => Setting('telegram_bot_enable', 0),
                'telegram_bot_token' => Setting('telegram_bot_token'),
                'telegram_discuss_link' => Setting('telegram_discuss_link')
            ],
            'app' => [
                'windows_version' => Setting('windows_version'),
                'windows_download_url' => Setting('windows_download_url'),
                'macos_version' => Setting('macos_version'),
                'macos_download_url' => Setting('macos_download_url'),
                'android_version' => Setting('android_version'),
                'android_download_url' => Setting('android_download_url')
            ],
            'safe' => [
                'email_verify' => (int)Setting('email_verify', 0),
                'safe_mode_enable' => (int)Setting('safe_mode_enable', 0),
                'secure_path' => Setting('secure_path', Setting('frontend_admin_path', hash('crc32b', config('app.key')))),
                'email_whitelist_enable' => (int)Setting('email_whitelist_enable', 0),
                'email_whitelist_suffix' => Setting('email_whitelist_suffix', Dict::EMAIL_WHITELIST_SUFFIX_DEFAULT),
                'email_gmail_limit_enable' => Setting('email_gmail_limit_enable', 0),
                'recaptcha_enable' => (int)Setting('recaptcha_enable', 0),
                'recaptcha_key' => Setting('recaptcha_key'),
                'recaptcha_site_key' => Setting('recaptcha_site_key'),
                'register_limit_by_ip_enable' => (int)Setting('register_limit_by_ip_enable', 0),
                'register_limit_count' => Setting('register_limit_count', 3),
                'register_limit_expire' => Setting('register_limit_expire', 60),
                'password_limit_enable' => (int)Setting('password_limit_enable', 1),
                'password_limit_count' => Setting('password_limit_count', 5),
                'password_limit_expire' => Setting('password_limit_expire', 60)
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
                Setting::updateOrCreate(
                    ['name' => $k],
                    ['name' => $k, 'value' => $data[$k]]
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
