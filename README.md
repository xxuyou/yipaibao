[![荆秀实时数据推送服务](https://github.com/xxuyou/yipaibao/blob/master/screenshot/logo-160.png)](http://xxuyou.com)

# WSS Real-time Data Push Service

## WSS 服务

[![Gitter](https://img.shields.io/badge/Powered%20by-ThinkJS%20Framework-brightgreen.svg)](http://thinkjs.org) [![Gitter](https://img.shields.io/badge/Powered%20by-Socket.IO-brightgreen.svg)](http://socket.io) [![Gitter](https://img.shields.io/badge/Powered%20by-JWT.IO-brightgreen.svg)](http://jwt.io)

WSS 是 **荆秀实时数据推送服务** 的英文简称。

WSS 提供一个位于广域互联网的数据转发服务，服务方式是接收到发布者的数据请求后，依据预先设立的应用划定广播边界，在边界内对应用的全部订阅者进行数据副本的群发服务。

![WSS 服务机制](https://github.com/xxuyou/yipaibao/blob/master/screenshot/workflow000001.png)

WSS 封装了复杂的内部实现，对外开放了简单的发布／订阅机制（Pub/Sub），简单几步即可建立基于此机制的业务。

WSS 服务给提供了基于 HTTP 1.1 的 RESTful API，发布者可使用 GET/DELETE/PUT/POST 指令来操作 WSS 服务提供的资源；订阅者基于事件监听机制，即可实现被动接收 WSS 服务主动推送过来的消息数据。

WSS 服务提供了开放于广域互联网的 URI，使得任何互联网终端均可连接和订阅事件。事件订阅基于标准 WebSocket 协议而并非私有协议，使用者无须担心对未来软件系统升级的潜在的兼容性问题。

目前 WSS 提供 S2C（Server to Client）的单向数据广播（Broadcast 下行）模式（可理解为群发），尚不支持 C2S（Client to Server）的交互模式，这是 WSS 的业务方向所决定的。

WSS 路线图（暂定）：
1. S2D 单点触达（Server to Device）模式，实现对特定单点、终端进行数据触达
1. D2D 终端间触达（Device 2 Device）模式，实现特定多个单点间一对一数据触达
1. 钩子 服务端触达（Webhook）模式，推送数据给服务器
1. ......

## 最佳实践

接入 WSS 服务的几个步骤：

1. **建立应用** 根据业务建立一个应用，名为 `my_test_app` 用于管理自有软件系统的全部数据池数据；如有必要，可以建立多个应用（此步骤无需编程）
1. **发布数据** 发布者在特定事件发生时（例如拍卖开始、成功创建订单时），向 WSS 服务 RESTful API 的应用 `my_test_app/auction_begin` 的数据池发布一个 JSON 数据包（数据池如不存在 WSS 会自动创建；数据包内容自定，UTF-8 字符编码格式；此步骤需编程实现）
1. **接收订阅** 合法的订阅者在特定的场景（例如某个 Web 页面）中监听 `my_test_app` 的 `auction_begin` 数据池对应的事件（此步骤需编程实现），即可实时接收到发来的数据包。一个合法订阅者可建立多个订阅；事件分为 `:add` `:update` `:append` `:delete` 等，详见下面描述

要接入 WSS 服务，需要在服务端和客户端分别进行相关工作。

### 服务端接入 RESTful API

接入方可根据自己所用的开发语言，使用符合语义的指令来操作资源。

请求包和响应包均为 JSON 格式。

- **GET** 获取指定应用下的数据池保存的数据
  - `Method` GET
  - `URI` http://xxuyou.com/rest/ 资源地址
  - `Parameters` 参数组
    - `AuthKey` 1Ufd******PitR 应用密钥
    - `AppName` my_test_app 应用名
    - `PoolName` client_price 数据池名
  - `Trigger Client Event` 对应客户端触发事件
    - `None`

```sh
#!/bin/bash
curl -4 'http://xxuyou.com/rest/1Ufd******PitR/my_test_app/client_price'
```

```php
<?php
$uri = "http://xxuyou.com/rest/";
$authKey = "1Ufd******PitR";
$appName  = "my_test_app";
$poolName = "client_price";
$url = $uri . $authKey . $appName . $poolName;

$ssl = substr($url, 0, 8) == 'https://' ? true : false;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
if ($ssl) {
    //不验证证书
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
};
$result = curl_exec($ch);
curl_close($ch);
var_dump($result); // 得到返回的数据
?>
```

- **DELETE** 删除指定应用下的数据池，该键中保存的数据也将一并删除
  - `Method` DELETE
  - `URI` http://xxuyou.com/rest/ 资源地址
  - `Parameters` 参数组
    - `AuthKey` 1Ufd******PitR 应用密钥
    - `AppName` my_test_app 应用名
    - `PoolName` client_price 数据池名
  - `Trigger Client Event` 对应客户端触发事件
    - `:delete` 删除整个数据池时触发


```sh
#!/bin/bash
curl -4 -X DELETE 'http://xxuyou.com/rest/1Ufd******PitR/my_test_app/client_price'
```

```php
<?php
$uri = "http://xxuyou.com/rest/";
$authKey = "1Ufd******PitR";
$appName  = "my_test_app";
$poolName = "client_price";
$url = $uri . $authKey . $appName . $poolName;
$method = "DELETE";

$ssl = substr($url, 0, 8) == self::SSL ? true : false;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
if ($method) curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method); // 声明 Method
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, true);
if ($ssl) {
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //不验证证书
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //
};
$result = curl_exec($ch);
curl_close($ch);
var_dump($result); // 得到操作结果
?>
```


- **PUT** 修改指定应用下的数据池中的数据（可理解为覆盖操作）
  - `Method` PUT
  - `URI` http://xxuyou.com/rest/ 资源地址
  - `Parameters` 参数组
    - `AuthKey` 1Ufd******PitR 应用密钥
    - `AppName` my_test_app 应用名
    - `PoolName` action_341 数据池名
  - `Request Header` Content-Type: application/json 请求头内容类型申明
  - `Request Body` {...}  请求体内容，UTF-8 编码 JSON 格式
  - `Trigger Client Event` 对应客户端触发事件
    - `:update` 当数据池已经存在时触发（推荐监听此事件）
    - `:add` 当数据池不存在时触发


```sh
#!/bin/bash
curl -4 -X PUT \
-H 'Content-Type: application/json' \
-d '{"event": "auction_close", "action_id": 341, "result": 1, "complete_price": 1450000,  "customer_id": 87, "order_id": 629}' \
'http://xxuyou.com/rest/1Ufd******PitR/my_test_app/action_341'
```

```php
<?php
$uri = "http://xxuyou.com/rest/";
$authKey = "1Ufd******PitR";
$appName  = "my_test_app";
$poolName = "action_341";
$url = $uri . $authKey . $appName . $poolName;
$method = "PUT";
$header = array('Content-Type: application/json');
$body = '{"event": "auction_close", "action_id": 341, "result": 1, "complete_price": 1450000,  "customer_id": 87, "order_id": 629}';

$ssl = substr($url, 0, 8) == self::SSL ? true : false;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
if ($method) curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method); // 声明 Method
if ($header) curl_setopt($ch, CURLOPT_HTTPHEADER, $header);//设置HTTP头
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);    //PUT数据
if ($ssl) {
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //不验证证书
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //
};
$result = curl_exec($ch);
curl_close($ch);
var_dump($result); // 操作成功会返回一个唯一ID
?>
```


- **POST** 在指定应用下的数据池中新增数据（可理解为追加操作）
  - `Method` POST
  - `URI` http://xxuyou.com/rest/ 资源地址
  - `Parameters` 参数组
    - `AuthKey` 1Ufd******PitR 应用密钥
    - `AppName` my_test_app 应用名
    - `PoolName` client_price 数据池名
  - `Request Header` Content-Type: application/json 请求头内容类型申明
  - `Request Body` {...}  请求体内容，UTF-8 编码 JSON 格式
  - `Trigger Client Event` 对应客户端触发事件
    - `:append` 当数据池已经存在时触发（推荐监听此事件）
    - `:add` 当数据池不存在时触发


```sh
#!/bin/bash
curl -4 -X POST \
-H 'Content-Type: application/json' \
-d '{"event": "auction_price", "new_price": 1450000, "action_id": 341, "customer_id": 87}' \
'http://xxuyou.com/rest/1Ufd******PitR/my_test_app/action_341'
```

```php
<?php
$uri = "http://xxuyou.com/rest/";
$authKey = "1Ufd******PitR";
$appName  = "my_test_app";
$poolName = "action_341";
$url = $uri . $authKey . $appName . $poolName;
$method = "POST";
$header = array('Content-Type: application/json');
$body = '{"event": "auction_price", "new_price": 1450000, "action_id": 341, "customer_id": 87}';

$ssl = substr($url, 0, 8) == self::SSL ? true : false;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
if ($method) curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method); // 声明 Method
if ($header) curl_setopt($ch, CURLOPT_HTTPHEADER, $header);//设置HTTP头
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);    //POST数据
if ($ssl) {
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //不验证证书
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //
};
$result = curl_exec($ch);
curl_close($ch);
var_dump($result); // 操作成功会返回一个唯一ID
?>
```


> 按：鉴于目前业界对于 REST 的理解和实践、实现有所差异（特别是对于 PUT 和 POST 表征的理解有所差异），此处并不讨论何为“正确的 REST 表征描述”，仅采用“合适的 REST 表征描述”，不必过于纠结。


### 客户端接入

客户端接入 WSS 服务分为两个部分：
- 服务端：生成身份鉴权令牌（JWT Token）放置在客户端 HTML Javascript 代码中备用
- 客户端：连接 WSS 服务器，递交令牌申明合法身份建立对应用的事件监听

#### 服务端：生成身份鉴权令牌

WSS 服务是一个公开在广域互联网上的实时数据推送服务，因此在创建订阅者之前，需要解决一个客户端身份鉴权的问题。

WSS 服务使用 JWT(JSON Web Tokens) [JWT 官网](https://jwt.io) 来进行令牌鉴权（鉴别权限），因此需要接入方在连接 WSS 服务之前生成身份鉴权令牌。

接入方在生成身份鉴权令牌的数据包 `payload` 需要以下三个参数，均为必填项；可增加其他业务参数，WSS 在鉴权成功后会返回全部参数。

- `Payload`
  - `app` my_test_app 应用名
  - `uid` 71  客户端用户唯一ID
  - `exp` 18623523110 令牌过期时间戳


```php
<?php
$payload = array(
    'app' => 'my_test_app',
    'uid' => 71,
    'exp' => time() + 86700
);
$secretKey = "1Ufd******PitR"; // 超级密钥
$token = \JWT::encode($payload, $secretKey, 'HS256'); // JWT 加密参数恒定为 HS256
?>
```

#### 客户端：递交令牌申明合法身份建立连接

客户端运行环境为兼容主流内核（如：Trident内核／Gecko内核／WebKit内核／Presto内核）的 Web 浏览器中，最佳调用方式是使用 Javascript 脚本。

WSS 服务在客户端接入推荐使用 Socket.IO [官网](https://socket.io) 代码库，此代码库使用起来已经相当简单了，为了进一步方便使用，WSS 封装了连接、鉴权、监听事件的工厂方法，直接引入即可使用。

```js
<script src="js/wss.class.js?version=3.4.6"></script>
<script>
    $(function () {
        /**
         * 连接参数，以下示例均为必填项
         * update @ 2017-05-20
         */
        var config = {
            "debug": true, // 打开调试模式，会输出连接和鉴权信息
            "info":  document.getElementById('result'), // 指定显示调试信息的元素容器
            "url":   "http://xxuyou.com/",   // wss 服务器 url
            "app":   "my_test_app",   // 自己创建的 app name，用于域权限验证
            "jwt":   "<?php echo $token; ?>", // 使用 JWT 制作的用户令牌，用于用户身份验证
            "listener": []  // 待注册的监听事件，数组内放置多个监听函数
        };
        // 注册监听函数，可以注册多个
        config['listener'].push({
            "event":    "client_price:update",
            "callback": function (res) {
                console.log('[CLIENT][INFO] 注册监听事件 receive [client_price:update]:', res);
            }
        });
        config['listener'].push({
            "event":    "action_341:append",
            "callback": function (res) {
                console.log('[CLIENT][INFO] 注册监听事件 receive [action_341:append]:', res);
            }
        });
        // 初始化 wss 实例
        var initWssServ = function (config) {
            var _wss = false;
            if (!WSS) {
                console.log('[CLIENT][ERR] WSS is not defined');
                alert('WSS is not defined');
                return false;
            };
            try {
                _wss = new WSS(config);
            } catch(e) {
                console.log('[CLIENT][ERR] e', e);
                alert(e.message);
                return false;
            };
            return _wss.instance;
        };
        var _wss = initWssServ(config);
        if (_wss) {
            // 连接成功
            //console.log('[CLIENT][INFO] 连接成功');
            // 除了上面注册监听事件以外
            // 还可以继续建立新的监听
            _wss.on('auction_begin:update', function (res) {
                console.log('[CLIENT][INFO] 继续监听事件 receive [auction_begin:update]:', res);
            });
            // 可以建立多个监听事件
            _wss.on('auction_close:update', function (res) {
                console.log('[CLIENT][INFO] 继续监听事件 receive [auction_close:update]:', res);
            });
        } else {
            throw new Error("[CLIENT][ERR]错误：连接 WSS 服务器失败！请刷新页面重新尝试！");
        };
    });
</script>
```

> WSS 工厂类返回的是 `Socket` 对象

> Socket.IO 绑定事件使用 `Socket.on()` 方法，解除绑定事件使用 `Socket.off()` 方法，详见其 [官网 Docs Client API](https://socket.io/docs/client-api/)。

## 第三方库依赖

要使用 WSS 服务，接入方的服务端和客户端需要使用以下的第三方开源代码。

### JWT.IO

JWT(JSON Web Tokens) [JWT 官网](https://jwt.io) 是一个跨语言的基于 JSON 格式的互联网端对端身份加密认证通信的发起者。

以下是其官网上的描述：

> JSON Web Tokens are an open, industry standard RFC 7519 method for representing claims securely between two parties.
> JWT.IO allows you to decode, verify and generate JWT.

JWT.IO 提供了非常多的开发语言接入 SDK ，详情请前往 [JWT 官网](https://jwt.io) 查看。

### Socket.IO

Socket.IO [官网](https://socket.io) 是一个跨平台的实时数据通信的引擎实现。它将 WebSocket 协议进行了全面封装，使用起来更加方便。

以下是其官网上的描述：

> Socket.IO enables real-time bidirectional event-based communication.
> It works on every platform, browser or device, focusing equally on reliability and speed.

## 名词解释

### EDA 事件驱动

事件驱动在计算机软件领域是一种开发模式和实现方式，一个典型的软件就是 Windows（官网 [Microsoft](http://www.microsoft.com/windows)）系列操作系统。

软件通过对一系列操作进行响应，比如鼠标点击按钮、键盘按下和松开等等，软件相应的功能就会被触发开始运行。

订阅者接入 WSS 服务后，是被动的等待接收数据。订阅者仅仅知道需要监听哪个事件（数据池），但并不知道何时会收到数据，只有接收到数据后才能做出响应，这也是典型的 EDA 开发机制。

### App 应用

应用是 WSS 服务的逻辑边界，是一个人为约定的一个集合。可以理解为“一组业务”或者“xx系统专用”。其主要用途是用于将多个数据池进行逻辑分组，使得软件系统流程运行更加清晰和符合计算机语言语义。

典型的建立应用的方式是根据自有软件一一对应，例如财务软件建立 `app_finance` 应用；进销存软件建立 `app_erp` 应用，等等（当然也可以只建立一个应用为多个软件所用）。

WSS 服务根据应用来约定资源占用许可（最大同时连接数、最大数据存储空间、最大数据下行流量），WSS 会统计每个应用下全部数据池的连接数、存储及流量，汇总得到每个应用的资源占用（需要注意：不同应用下的数据池不通用）。

WSS 收费范围也是基于应用，即每个应用可以选择不同的费用标准。

### Auth 应用密钥

WSS 服务运行在广域互联网（也称“公网”）上，需要对连接到 WSS 服务的发布和订阅客户端进行鉴权（鉴定权限）,阻止非法资源操作、非法连接和数据泄露。

WSS 服务的每个应用均可建立一个长度为 40 字节的应用密钥，用于发布方调用 RESTful API 时、和制作订阅方鉴权令牌时使用。

WSS 服务使用应用和应用密钥互相印证的方式来实现对发布者的身份鉴权。

WSS 服务使用 JWT（JSON WebToken）Token 来实现订阅者身份鉴权。订阅者连接 WSS 服务时递交此 Token，WSS 服务解密鉴权后确认客户端身份、应用合法后，后续订阅方法会正常运行；否则连接会被 WSS 服务强制关闭。

应用密钥通常会保存在用户的软件服务器上，需要谨慎保护，以防泄露被他人盗用造成损失。

建议不定期更换密钥。

### Pool 数据池

数据池是 WSS 应用内一个或者多个事先约定发布和订阅双方互通数据的管道, 以一个自定义的名字来建立约定和实现约定。

数据池完全自定义，只要确保名称唯一即可。

数据池数量不限，可根据不同的业务事件建立多个数据池。

数据池也不需要事先建立，如果 RESTful API 操作的数据池不存在，WSS 会自动创建（GET 和 DELETE 操作不会触发自动创建）。

### Pub 发布

客户软件通过 WSS 的 RESTful API 操作一个应用的数据池资源，就是“发布”动作（发布动作包含 DELETE/PUT/POST 资源操作）。

一个发布可以被多个订阅者订阅；一个订阅者也可以订阅多个发布。

### Sub 订阅

客户软件连接到 WSS 服务器后，监听数据池数据变化就是“订阅”动作（也可称为事件监听）。

一个发布可能触发一个或者多个事件，前面 RESTful API 中已经列举全部事件，订阅者可根据业务需要选用相应的事件来监听。

PUT 和 POST 操作会有多个事件触发，是为了方便业务上不同的需求，例如有些业务只监听某个数据第一次发生变化，有些业务则需要监听某个数据每次发生的变化，等等。后续 WSS 还将适时推出更多的事件推送，以适应更多的业务需求，简化订阅者开发工作。

一个发布可以被多个订阅者订阅；一个订阅者也可以订阅多个发布。

订阅客户端接入实时数据推送的机制涉及到 HTTP/1.1 协议和 WebSocket 协议，为了方便处理需要引入一个 Javascript 对象库（[Socket.IO 官网](https://socket.io)），该对象库可以大大简化客户端接入的步骤和方法。

### Broadcast 广播

当 N 个订阅者成功连接上 WSS 服务后，WSS 会维持全部的合法连接。当发布者发布数据时，WSS 立即会把数据的多个副本按照应用、数据池规则发送给全部订阅者，这一动作就是广播。

举个例子：

> 订阅者1成功连接后，订阅了 test1、test2 和 test3 数据池。

> 订阅者2成功连接后，订阅了 test1、test3 数据池。

> 订阅者3成功连接后，订阅了 test4 数据池。

> 订阅者4成功连接后，没有订阅任何数据池。

> 当发布者发布数据池 test1 ，数据为 {"name":"test1 OK"}，那么订阅者1、2会收到此 JSON 数据包；

> 当发布者发布数据池 test2 ，数据为 {"name":"test2 OK"}，那么订阅者1会收到此 JSON 数据包；

> 当发布者发布数据池 test4 ，数据为 {"name":"test4 OK"}，那么订阅者3会收到此 JSON 数据包；

> 前面三个发布动作，订阅者4不会收到任何数据。

WSS 服务目前不支持向特定的订阅者广播数据，但是可以使用业务系统规划来实现这个需求，具体思路是：

1. 给一组订阅者约定一个共同使用的数据池
1. 发布者在特定业务向这个数据池发布数据
1. 订阅者在进入监听时根据业务需要决定是否监听此数据池

监听此数据池事件的订阅者可收到数据，而没有监听此数据池的订阅者收不到数据，这样即可实现对特定订阅者广播数据的业务。

###### _# for yipaiart_

_Fin._
