<?php

use App\Services\ThemeService;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function (Request $request) {
    if (Setting('app_url') && Setting('safe_mode_enable', 0)) {
        if ($request->server('HTTP_HOST') !== parse_url(Setting('app_url'))['host']) {
            abort(403);
        }
    }
    $renderParams = [
        'title' => Setting('app_name', 'V2Board'),
        'theme' => Setting('frontend_theme', 'v2board'),
        'version' => config('app.version'),
        'description' => Setting('app_description', 'V2Board is best'),
        'logo' => Setting('logo')
    ];

    if (!config("theme.{$renderParams['theme']}")) {
        $themeService = new ThemeService($renderParams['theme']);
        $themeService->init();
    }

    $renderParams['theme_config'] = config('theme.' . Setting('frontend_theme', 'v2board'));
    return view('theme::' . Setting('frontend_theme', 'v2board') . '.dashboard', $renderParams);
});

//TODO:: 兼容
Route::get('/' . Setting('secure_path', Setting('frontend_admin_path', hash('crc32b', config('app.key')))), function () {
    return view('admin', [
        'title' => Setting('app_name', 'V2Board'),
        'theme_sidebar' => Setting('frontend_theme_sidebar', 'light'),
        'theme_header' => Setting('frontend_theme_header', 'dark'),
        'theme_color' => Setting('frontend_theme_color', 'default'),
        'background_url' => Setting('frontend_background_url'),
        'version' => config('app.version'),
        'logo' => Setting('logo'),
        'secure_path' => Setting('secure_path', Setting('frontend_admin_path', hash('crc32b', config('app.key'))))
    ]);
});
