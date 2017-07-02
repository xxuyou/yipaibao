/**
 * WSS核心类
 * 负责连接 io、鉴权、绑定监听事件
 * 2017-07-02 fixed [Multiple sockets open after reconnect #430](https://github.com/socketio/socket.io/issues/430)
 *
 * @author Xuejun Guan <guanxuejun@gmail.com> (http://xxuyou.com)
 * @version 3.5.0
 * @license Commercial Licensing.
 * @update 2017-07-02
 *
 */

/**
 * Factory WSS
 * var wss = new WSS({...});
 *
 * @param option {"url": "", "app": "", "jwt": ""}
 * @constructor
 */
var WSS = function (option) {
  var _self = this;
  /**
   * Public member
   */
  _self.version = "3.5.0";
  _self.author  = "Xuejun Guan <guanxuejun@gmail.com> (https://xxuyou.com)";
  _self.license = "Commercial Licensing.";
  _self.update  = "2017-07-02";
  _self.debug   = false;
  _self.info    = option['info']; // 调试错误输出的 html 容器
  _self.instance= undefined;
  _self.binded  = false; // 标记是否已经绑定事件
  /**
   * Private member
   */
  var _uk = ""; // 放置 auth Ack 里面返回的 key
  var _running = false;
  var _output = function (key, msg) {
    console.log(key, msg);
    var _tNode = document.createElement('p');
    _tNode.innerHTML = '<kbd>' + key + '</kbd> ' + (msg ? JSON.stringify(msg) : '');
    _self.info.insertBefore(_tNode, _self.info.childNodes[0]);
  };
  var _construct = function () {
    if (_self.debug === true) {
      console.log('[DEBUG][INFO] fire init');
      _output('[DEBUG][INFO] fire init');
    };
    if (!_running) {
      if (_self.debug === true) _output('[DEBUG][INFO] running', _running);
      return false;
    };
    if (!_self.instance) {
      alert('Realtime Service connect fail!');
      if (_self.debug === true) _output('[DEBUG][ERR] fail', 'Realtime Service connect fail!');
    };
    // regist listen events
    if (option['listener'] && _self.binded === false) {
      if (Array.isArray(option['listener']) && option['listener'].length > 0) {
        var lisCount = option['listener'].length;
        for (var i=0; i<lisCount; i++) {
          var listener = option['listener'][i];
          _self.instance.on(listener['event'], listener['callback']);
        };
        if (_self.debug === true) _output('[DEBUG][INFO] bind listener', lisCount);
      } else {
        if (_self.debug === true) _output('[DEBUG][INFO] defined listener, but length is zero.');
      };
      _self.binded = true;
    } else {
      if (_self.debug === true) _output('[DEBUG][INFO] did not define listener');
    };
  };
  /**
   * Process
   *
   */
  if (!option.hasOwnProperty('url')) throw new Error('[ERR]parameter URL is required!');
  if (!option.hasOwnProperty('app')) throw new Error('[ERR]parameter app is required!');
  if (!option.hasOwnProperty('jwt')) throw new Error('[ERR]parameter jwt is required!');
  if (option.hasOwnProperty('debug')) _self.debug = !!option['debug'];
  if (_self.debug === true) _output('[DEBUG][INFO] option', option);
  _self.instance = io(option['url'], {"transports": ['websocket', 'polling']});
  if (!_self.instance) {
    alert('Realtime Service connect fail!');
    if (_self.debug === true) _output('[DEBUG][ERR] fail', 'Realtime Service connect fail!');
  } else {
    if (_self.debug === true) _output('[DEBUG][INFO] init OK', 'ready to connect');
    _self.instance.on('connect', function () {
      if (_self.debug === true) _output('[DEBUG][INFO] event connect', 'socket.id: ' + _self.instance['id']);
      _running = false;
      var _auth = {"token": option['jwt'], "app": option['app']};
      if (_self.debug === true) _output('[DEBUG][INFO] auth data', _auth);
      _self.instance.emit('auth', _auth, function (verify) {
        if (_self.debug === true) _output('[DEBUG][INFO] auth Ack data', verify);
        if (!verify) {
          alert('Authentication fail. Service not response.');
          return false;
        };
        if (!verify.hasOwnProperty('err')) {
          alert('Authentication fail. Response data format incorrect!');
          return false;
        };
        if (verify['err'] > 0) {
          alert('Authentication fail. ' + verify['msg']);
          return false;
        };
        _uk = verify['data']['uk'];
        _running = true;
        _self.instance.on('connect_error', function (err) {
          _running = false;
          if (_self.debug === true) _output('[DEBUG][ERR] connect_error', err);
          console.log('[ERR]connect_error: ', err);
        });
        _self.instance.on('error', function (err) {
          _running = false;
          if (_self.debug === true) _output('[DEBUG][ERR] error', err);
          console.log('[ERR]error: ', err);
        });
        _self.instance.on('reconnect', function (attempt) {
          _running = false;
          if (_self.debug === true) _output('[DEBUG][ERR] reconnect', attempt);
          console.log('[ERR]reconnect: ', attempt);
        });
        _self.instance.on('reconnecting', function (attempt) {
          _running = false;
          if (_self.debug === true) _output('[DEBUG][ERR] reconnecting', attempt);
          console.log('[ERR]reconnecting: ', attempt);
        });
        _self.instance.on('reconnect_error', function (err) {
          _running = false;
          if (_self.debug === true) _output('[DEBUG][ERR] reconnect_error', err);
          console.log('[ERR]reconnect_error: ', err);
        });
        _construct();
      });
    });
  };
};