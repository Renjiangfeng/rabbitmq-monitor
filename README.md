## PHP版本 - rabbitmq-monitor

### 简介
rabbitmq-monitor使用PHP实现，配合定时计划任务或者supervisor，对Rabbitmq服务监控预警

### 功能实现
1. rabbitmq服务连接是否正常
2. rabbitmq队列是否存在消息积压


### 特点
1. 使用swoole process派生子进程，对rabbitmq服务和队列的消息长度进行监控
2. 使用redis对队列消息积压的次数进行记录
3. 当服务需要告警，可使用钉钉/邮件/短信方式预警（目前只实现了钉钉通知）。
4. 可以单独作为服务使用，也可以与PHP框架无缝结合。

#### 钉钉机器人注意事项
钉钉机器人配置(在PC端群组找智能助手添加自定义机器人)，参考地址：https://open.dingtalk.com/document/group/custom-robot-access,
这里 自定义机器人 Webhook 的消息推送，安全设置 自定义关键词是：Error 和 Notice , Webhook 的地址复制出来，里面的access_token就是配中的
钉钉机器人token，需要注意的是管给的调用频率是：**每个机器人每分钟最多发送20条消息到群里，如果超过20条，会限流10分钟。**

![机器人设置1](src/docs/Custom.png)
![机器人设置2](src/docs/keywords.png)


### 安装
环境依赖：
1. php >= 7.0
2. swoole扩展（版本无要求）
3. redis扩展 >= 2.6
4. amqp扩展（操作rabbitmq）


docker安装扩展
```

RUN pecl install -o -f ev redis; \
  rm -rf /tmp/pear \
  && docker-php-ext-enable redis \
  && docker-php-ext-enable ev


# amqp
RUN  apt-get update  --allow-releaseinfo-change  -y  && apt-get install -y librabbitmq-dev
RUN pecl install amqp && \
    echo "extension=amqp.so" > /usr/local/etc/php/conf.d/amqp.ini

# swoole
RUN pecl install swoole && \
    echo "extension=swoole.so" > /usr/local/etc/php/conf.d/swoole.ini
```


##### 独立安装：

- 进入目录：composer install

##### 接入项目：
- github https://github.com/Renjiangfeng/rabbitmq-monitor
- composer require renjiangfeng/rabbitmq-monitor


### 使用
复制根目录下的config.demo.php，并重命名为：config.php，修改配置文件里的参数。
主要配置说明：
```
//连接MQ失败预警
'connectRules' => [
    'connectFailTimes' => 3, //单次执行，连续连接MQ失败达到预警的次数
    'interval'         => 2, //尝试重连的时间间隔（单位：s）
    'mode'             => [ //预警模式
        'type'  => DINGDING_NOTICE,
        'token' => '钉钉机器人token', //钉钉机器人token
    ],
],
//监控队列配置【可添加多个队列】
'queueRules' => [
    //队列名称
    'test' => [
        'name'             => 'test', //队列名称
        'vhost'            => 'v1',
		'isConsecutive'    => 1, //1： 在有效时间内连续达到预警数量， 0：不需要连续，只需要在有效时间内达到预警数量即可，不配置，默认为1
        'warningMsgCount'  => 10, //队列积压达到预警的数量
        'warningTimes'     => 3, //连续监控到队列积压达到预警的次数，结合warningMsgCount使用
        'duringTime'       => 600, //在有效duringTime的时间内，检测到队列的数量连续warningTimes次达到warningMsgCount，则预警
        'mode'             => [ //预警模式
            'type'  => DINGDING_NOTICE,
            'token' => '钉钉机器人token',
        ],
    ],
],

```
### 启动
由于rabbitmq-warning不是常驻进程，需使用定时计划任务配合，例： * * * * * php /PATH/server start （每分钟执行一次）
参考 server 文件
### 支持
swoole

### laravel 安装使用：
- composer require renjiangfeng/rabbitmq-monitor
- 执行 php artisan vendor:publish --force --provider="Eric\EricRabbitmqMonitorServiceProvider"，config目录下会多出rabbitmq-monitor.php 文件，可以把配置写入到这里面
- 把执行的命令封装成artisan命令，使用supervisor管理起来，参考代码laravel-command