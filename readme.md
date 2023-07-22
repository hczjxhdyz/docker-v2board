# **V2Board**

- PHP7.3+
- Composer
- MySQL5.5+
- Redis
- Laravel

## Demo
[演示站点](https://demo.v2board.com)

## Docker安装

### 1、安装 docker 
[官方安装教程](https://docs.docker.com/engine/install/)

### 2 克隆仓库代码
```
git clone https://github.com/hczjxhdyz/docker-v2board
```
> 执行这条命令之前确保git已经安装,或者你以其他方式下载代码到服务器
### 3 修改配置文件（使用默认配置可以跳过这一步）
```
cd docker-v2board           #进入项目目录
cd docker                   #进入docker文件夹
cp env.sample .env && cp docker-compose.sample.yml  docker-compose.yml #复制配置文件
vi .env                     #进入环境变量里面修改你需要更改的配置
```
### 4 安装composer依赖
```
docker compose run --rm php80 composer install
```
### 5 执行安装指导
> 默认数据库配置：  
数据库地址: mariadb
数据库名: v2board  
数据库用户名: root  
数据库密码: 123456  
```
docker compose run --rm php80 php artisan v2board:install
```
> 初始化之后并不能直接访问站点，需要执行第六步启动之后才可以访问。
### 6 启动
```
docker compose up -d php80 supervisor nginx
```
> 至此、程序已经成功部署了。

### 关闭
```
docker compose stop
```

## 使用
在第5步安装指导那边已经有后台入口了。



