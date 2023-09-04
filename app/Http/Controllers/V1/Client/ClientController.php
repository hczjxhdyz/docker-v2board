<?php

namespace App\Http\Controllers\V1\Client;

use App\Http\Controllers\Controller;
use App\Protocols\General;
use App\Services\ServerService;
use App\Services\UserService;
use App\Utils\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ClientController extends Controller
{
    public function subscribe(Request $request)
    {
        $flag = $request->input('flag')
            ?? ($_SERVER['HTTP_USER_AGENT'] ?? '');
        $ip = $request->ip();

        // 判断是否有x-forward-ip
        $xForwardedFor = $request->header('x-forwarded-for');
        $ipAddresses = explode(',', $xForwardedFor);
        $realIpAddress = trim($ipAddresses[0]);
        if (!blank($realIpAddress)) $ip = $realIpAddress;

        if ($request->input('ip')) $ip = $request->input('ip');
        $flag = strtolower($flag);
        $user = $request->user;
        // account not expired and is not banned.

        $userService = new UserService();
        if ($userService->isAvailable($user)) {
            // 获取IP地址信息 如果是国外IP跳过
            $ip2region = new \Ip2Region();
            $geo = filter_var($ip,FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? $ip2region->memorySearch($ip) : [];
            $region = $geo['region'] ?? '';

            // 获取服务器列表
            $serverService = new ServerService();
            $servers = $serverService->getAvailableServers($user);
            // 如果是中国IP、则开开启订阅过滤
            $rejectServerCount = 0;
            if(!blank($region) && strpos($region, '中国') !== false){
                $serversFiltered = collect($servers)->filter(function($item) use ($region){
                    $excludes = $item['excludes'];
                    if(blank($excludes)) return true;
                    foreach($excludes as $v){
                        $excludeList = explode("|",str_replace(["｜",","," ","，"],"|",$v));
                        $containsAll = true;
                        foreach($excludeList as $needle){
                            $position = strpos($region, $needle);
                            if($position === false){
                                $containsAll = false;
                                break;
                            }
                        }
                        if ($containsAll === true) return false;
                    };
                    return true;
                })->values()->all() ?? [];
                $rejectServerCount = count($servers) - count($serversFiltered);
                $servers = $serversFiltered;
            }
            $this->setSubscribeInfoToServers($servers, $user, $rejectServerCount);
            if ($flag) {
                foreach (array_reverse(glob(app_path('Protocols') . '/*.php')) as $file) {
                    $file = 'App\\Protocols\\' . basename($file, '.php');
                    $class = new $file($user, $servers);
                    $classFlags = explode(',', $class->flag);
                    $isMatch = function() use ($classFlags, $flag){
                        foreach ($classFlags as $classFlag){
                            if(strpos($flag, $classFlag) !== false) return true;
                        }
                        return false;
                    };
                    // 判断是否匹配
                    if ($isMatch()) {
                        die($class->handle());
                    }
                }
            }
            $class = new General($user, $servers);
            die($class->handle());
        }
    }

    private function setSubscribeInfoToServers(&$servers, $user, $rejectServerCount = 0)
    {
        if (!isset($servers[0])) return;
        if($rejectServerCount > 0){
            array_unshift($servers, array_merge($servers[0], [
                'name' => "去除{$rejectServerCount}条不合适线路",
            ]));
        }
        if (!(int)Setting('show_info_to_server_enable', 0)) return;
        $useTraffic = $user['u'] + $user['d'];
        $totalTraffic = $user['transfer_enable'];
        $remainingTraffic = Helper::trafficConvert($totalTraffic - $useTraffic);
        $expiredDate = $user['expired_at'] ? date('Y-m-d', $user['expired_at']) : '长期有效';
        $userService = new UserService();
        $resetDay = $userService->getResetDay($user);
        // 筛选提示
        array_unshift($servers, array_merge($servers[0], [
            'name' => "套餐到期：{$expiredDate}",
        ]));
        if ($resetDay) {
            array_unshift($servers, array_merge($servers[0], [
                'name' => "距离下次重置剩余：{$resetDay} 天",
            ]));
        }
        array_unshift($servers, array_merge($servers[0], [
            'name' => "剩余流量：{$remainingTraffic}",
        ]));
    }
}
