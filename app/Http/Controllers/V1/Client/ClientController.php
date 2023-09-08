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
        // 节点类型筛选
        $allowedTypes = ['vmess', 'vless', 'trojan', 'hysteria', 'shadowsocks'];
        $typesArr = $request->input('types') ?  collect(explode('|', str_replace(['|','｜',','], "|" , $request->input('types'))))->reject(function($type) use ($allowedTypes){
            return !in_array($type, $allowedTypes);
        })->values()->all() : [];

        //  节点关键词筛选字段获取
        $filterArr = (mb_strlen($request->input('filter')) > 20) ? null : explode("|" ,str_replace(['|','｜',','], "|" , $request->input('filter')));

        $flag = $request->input('flag') ?? $request->header('User-Agent', '');
        $ip = $request->input('ip') ?? $request->ip();

        $flag = strtolower($flag);
        $user = $request->user;
        // account not expired and is not banned.
        $userService = new UserService();
        if ($userService->isAvailable($user)) {
            // 获取IP地址信息
            $ip2region = new \Ip2Region();
            $geo = filter_var($ip,FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? $ip2region->memorySearch($ip) : [];
            $region = $geo['region'];

            // 获取服务器列表
            $serverService = new ServerService();
            $servers = $serverService->getAvailableServers($user);

            // 判断不满足，不满足的直接过滤掉
            $serversFiltered = collect($servers)->reject(function ($server) use ($typesArr, $filterArr, $region){
                // 过滤类型
                if($typesArr){
                    if(!in_array($server['type'], $typesArr)) return true;
                }
                // 过滤关键词
                if($filterArr){
                    $rejectFlag = true;
                    foreach($filterArr as $filter){
                        if(strpos($server['name'],$filter) !== false) $rejectFlag = false;
                    }
                    if($rejectFlag) return true;
                }
                // 过滤地区
                if(strpos($region, '中国') !== false){
                    $excludes = $server['excludes'];
                    if(blank($excludes)) return false;
                    foreach($excludes as $v){
                        $excludeList = explode("|",str_replace(["｜",","," ","，"],"|",$v));
                        $rejectFlag = false;
                        foreach($excludeList as $needle){
                            if(strpos($region, $needle) !== false){
                                return true;
                            }
                        }
                    };
                }
            })->values()->all();
            $this->setSubscribeInfoToServers($serversFiltered, $user, count($servers) - count($serversFiltered));
            $servers = $serversFiltered;
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
                        return $class->handle();
                    }
                }
            }
            $class = new General($user, $servers);
            return $class->handle();
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
