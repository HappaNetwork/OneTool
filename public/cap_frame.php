<?php
error_reporting(0);

$sid = isset($_GET['sid'])?trim($_GET['sid']):null;
$aid = isset($_GET['aid'])?trim($_GET['aid']):null;
$uin = isset($_GET['uin'])?trim($_GET['uin']):null;
$step = isset($_GET['step'])?trim($_GET['step']):null;
//if(empty($sid) || !is_numeric($sid)) exit('参数sid不能为空');
if(empty($aid) || !is_numeric($aid)) exit('参数aid不能为空');
//if(empty($uin) || !is_numeric($uin)) exit('参数uin不能为空');
?>
<!DOCTYPE html>
<html>
<head lang="zh-CN">
    <meta charset="UTF-8">
    <meta name="renderer" content="webkit">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
    <title>滑块验证码</title>
</head>
<div id="cap_iframe" style="width:230px;height:220px;"></div>
</html>

<script>

    !function(e){var t={};function n(o){if(t[o])return t[o].exports;var r=t[o]={i:o,l:!1,exports:{__esModule: undefined}};return e[o].call(r.exports,r,r.exports,n),r.l=!0,r.exports}n.m=e,n.c=t,n.d=function(e,t,o){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:o})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var o=Object.create(null);if(n.r(o),Object.defineProperty(o,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var r in e)n.d(o,r,function(t){return e[t]}.bind(null,r));return o},n.n=function(e){var t=e&&e.__esModule?function(){return e["default"]}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="",n(n.s=28)}({28:function(e,t,n){"use strict";var o;!function(e,r){var a=function(e){function t(e,t){return e==t}var n=window.btoa||function(e){for(var t,n,o=String(e),r=0,a="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",c="";o.charAt(0|r)||(a="=",r%1);c+=a.charAt(63&t>>8-r%1*8)){if((n=o.charCodeAt(r+=.75))>255)return"base64encode outside range";t=t<<8|n}return c},o={add:function(t,n,o){e.document.addEventListener?t.addEventListener(n,o,!1):e.document.attachEvent?t.attachEvent("on"+n,o):t["on"+n]=o},remove:function(t,n,o){e.document.removeEventListener?t.removeEventListener(n,o,!1):e.document.detachEvent?t.detachEvent("on"+n,o):t["on"+n]=null}};function r(e){for(var t=[1,2052,1033,1028],n=t.length;n--;)if(t[n]===parseInt(e))return!0;return!1}var a=function(e,t,n){n=n||!1;var o={};for(var r in t)e[r]=t[r];if(n){for(var a in e)o[a]=e[a];return o}return e},c={id:0,start:function(){this.id||(o.add(e.document,"click",k),this.id=1)},end:function(){o.remove(e.document,"click",k),this.id=0}},i=function(){c.start()},d=function(){c.end()},u=navigator.userAgent&&/wechatdevtools/.test(navigator.userAgent),p="/tcaptcha-frame.db8b9289.js",f=1;!/windows/i.test(navigator.userAgent)&&("ontouchstart"in window||"ontouchstart"in document.createElement("div")||u)||(f=2);t("1","")||(f="1");var l="https://t.captcha.qq.com";l===["",""].join("")&&(l="https://t.captcha.qq.com");t("inner","open")&&(l="https://captcha.guard.qcloud.com");var s=t("https://captcha.gtimg.com/2","")?l:"https://captcha.gtimg.com/2",h=!1,v=0;window.AqSCodeCapDomain=l;var y=function(){},m=void 0;function g(t,n,o){var r=0;h=!1;for(var a=(new Date).getTime(),c=!1,i=0;i<t.length;i++){var d=function(e){if((e&&"load"===e.type||/^(loaded|complete)$/.test(this.readyState))&&(h=++r>=t.length)){var o=(new Date).getTime();v=o-a,y(),n&&n(c)}},u=e.document.createElement("script");u.type="text/javascript",u.async=!0,u.src=t[i],"onload"in u?(u.onload=d,o&&(u.onerror=o)):(c=!0,u.onreadystatechange=d),e.document.getElementsByTagName("head").item(0).appendChild(u)}}t("inner","open")?g([s+p]):g(["https://captcha.gtimg.com/1"+p],function(e){e&&"undefined"==typeof AqSCode?g([s+p]):window.AqSCode.capDomain=s},function(){g([s+p],function(e){e&&"undefined"==typeof AqSCode||(window.AqSCode.capDomain=s)})});var b=void 0,w={},$=function(){},C=function(){};function A(){if("undefined"!=typeof TCapMsg&&"undefined"!=typeof AqSCode){var e=j();e.ele=b,(m=new AqSCode(e)).listen($,C),m.start(i),m.end(d),w&&w.top&&m.initPos&&m.initPos({top:w.top}),w&&w.left&&m.initPos&&m.initPos({left:w.left}),m.create()}}function S(){return m.getTicket()}function _(e,t,n){b=e,"function"==typeof t?$=t:($=(w=t).callback&&"function"==typeof w.callback?w.callback:n,w.readyCallback&&"function"==typeof w.readyCallback&&(C=w.readyCallback),w.ready&&"function"==typeof w.ready&&(C=w.ready)),h?A():y=A}function j(){var e="",o="<?php echo $uin?>",u=1,h="",y="",m="",g="",b="";(t("inner","open")||t(o,""))&&(o="<?php echo $uin?>");var $="",C=0,A="<?php echo $sid?>";if(r($)||($="2052"),w&&w.start&&"[object Function]"==Object.prototype.toString.call(w.start)&&(i=function(){w.start&&w.start(),c.start()}),w&&w.end&&"[object Function]"==Object.prototype.toString.call(w.end)&&(d=function(){w.end&&w.end(),c.end()}),w&&w.lang&&r(w.lang)?$=w.lang:w.lang=$,w&&w.uin&&(o=w.uin),t("inner","open")&&w&&w.uid&&(o=w.uid),w&&w.capcd&&(y=w.capcd),w&&"undefined"!=typeof w.showHeader&&(e=w.showHeader?"0":"1"),w&&w.themeColor&&(m=w.themeColor),w&&w.type?(b=w.type,1==f&&"point"==b&&(b="",w.type=b)):(1==f?b="":2==f&&(b="point"),w.type=b),w&&"boolean"==typeof w.needFeedBack&&(u=w.needFeedBack?1:0),w&&w.theme&&(g=w.theme),w&&w.fwidth){var S=parseFloat(w.fwidth);S>0&&(C=S)}w&&w.pos&&(h=w.pos);var _=w&&w.enableDarkMode;"force"!==_&&(_=_?"1":"0");var j=w&&"force"===w.enableAged?"1":"0",k=w&&w.enableAged?"1":"0",E="?aid=<?php echo $aid?>&captype=&curenv=inner&protocol=https&clientype="+f+"&disturblevel=&apptype=2&noheader="+e+"&color="+m+"&showtype="+b+"&fb="+u+"&theme="+g+"&lang="+$+"&ua="+encodeURIComponent(n((navigator.userAgent||"").replace(/[\u00ff-\uffff]+/g,"")))+"&enableDarkMode="+_+"&aged="+j+"&enableAged="+k;t("inner","open")&&(E+="&asig="),E+="&grayscale=1",t(A,"")||(E+="&sid="+A);var q=[l+"/template/placeholder_v2.html"+E,l+"/cap_union_prehandle"+E,l+"/template/new_slide_placeholder.html"+E],P=a({frameJs:p,params:E,appid:"<?php echo $aid?>",src:q[0],domain:l,s_type:q[1],slide_src:q[2],s_type_suffix:E,uid:o,uin:o,lang:$,fb:u,theme:g,pos:h,htdoc_path:s,TCapIframeLoadTime:v,curenv:"inner",capcd:y,gettype:"cap_union_prehandle",fwidth:C,type:b,clientype:f,sid:A},w||{});return P}function k(e){m&&m.refresh&&m.refresh(e)}function E(){m&&m.destroy&&m.destroy()}return e.capInit=_,e.capGetTicket=S,e.capRefresh=k,e.capDestroy=E,e.CapObj=function(e){var t=j();t.ele=e;var n=new AqSCode(t);return n.create(),n},{capInit:_,capGetTicket:S,capRefresh:k,capDestroy:E}}(window);(n(29).cmd||n(30))&&((o=function(){return a}.call(t,n,t,a))===undefined||(a.exports=o))}()},29:function(e,t){e.exports=function(){throw new Error("define cannot be used indirect")}},30:function(e,t){(function(t){e.exports=t}).call(this,{})}});</script>
<script>
    capInit("", {
        type: "popup",
        callback: function (t) {
            console.log(t)
            if (t.ret === 0) {
                parent.window.onqqlogin({randstr:t.randstr, ticket:t.ticket, step: '<?php echo $step ?>'});
            }
        },
        showHeader: false,
        capcd: ''
    })
</script>