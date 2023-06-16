/*
 * @Author: SonLight Tech
 * @Date: 2023-04-19 09:04:24
 * @LastEditors: light
 * @LastEditTime: 2023-05-29 16:09:34
 * @Description: SonLight Tech版权所有
 */
var util={};
util.getQueryString=function(name) {
	try{
		var reg = new RegExp('(^|&)' + name + '=([^&]*)(&|$)', 'i');
		var r = window.location.search.substr(1).match(reg);
		if (r != null) {
			return decodeURIComponent(r[2]);
		}
	}catch(err){
		return '';
	}
	return '';
}
// 微信公众号jsapi支付
util.pay=function(params){
    var acid=util.getQueryString('i');
    if(!acid)return;
    var httpRequest = new XMLHttpRequest();
    var api='pay';
    if(params.api&&params.api=='V2'){
        api='payV2'
    }
    httpRequest.open("POST",'/index.php/admin/sunphp/'+api+'?i='+acid,true);
    httpRequest.setRequestHeader("Content-Type","application/x-www-form-urlencoded");

    var post_str=`orderFee=${params.orderFee}&payMethod=${params.payMethod}&orderTitle=${params.orderTitle}&orderTid=${params.orderTid}&module=${params.module}`;

    httpRequest.send(post_str);
    httpRequest.onreadystatechange = ()=>{
        if(httpRequest.readyState == 4 && httpRequest.status == 200){
            var data = JSON.parse(httpRequest.responseText);

            WeixinJSBridge.invoke(
                'getBrandWCPayRequest', {
                "appId": data.appId,     //公众号ID，由商户传入
                "timeStamp": data.timeStamp,         //时间戳，自1970年以来的秒数
                "nonceStr": data.nonceStr, //随机串
                "package": data.package,
                "signType": data.signType,         //微信签名方式：
                "paySign": data.paySign //微信签名
            },
                function (res) {
                    if (res.err_msg == "get_brand_wcpay_request:ok") {
                        // 使用以上方式判断前端返回,微信团队郑重提示：
                        //res.err_msg将在用户支付成功后返回ok，但并不保证它绝对可靠。
                        params.success(res);
                    }else{
                        params.fail(res);
                    }
                        params.complete(res);
                });

        }else{
            console.log(httpRequest.response);
        }
    }



}

// 微信手机浏览器wap支付
util.wechatWap=function(params){
    var acid=util.getQueryString('i');
    if(!acid)return;
    var httpRequest = new XMLHttpRequest();
    var api='wechatWap';

    httpRequest.open("POST",'/index.php/admin/sunphp/'+api+'?i='+acid,true);
    httpRequest.setRequestHeader("Content-Type","application/x-www-form-urlencoded");

    var post_str=`orderFee=${params.orderFee}&payMethod=${params.payMethod}&orderTitle=${params.orderTitle}&orderTid=${params.orderTid}&module=${params.module}`;

    httpRequest.send(post_str);
    httpRequest.onreadystatechange = ()=>{
        if(httpRequest.readyState == 4 && httpRequest.status == 200){
            var data = JSON.parse(httpRequest.responseText);
           if(data.h5_url){
                params.success(data);
            }else{
                params.fail(data);
            }
            params.complete(data);
        }else{
            console.log(httpRequest.response);
        }
    }
}

// 微信电脑浏览器扫码支付
util.wechatScan=function(params){
    var acid=util.getQueryString('i');
    if(!acid)return;
    var httpRequest = new XMLHttpRequest();
    var api='wechatScan';

    httpRequest.open("POST",'/index.php/admin/sunphp/'+api+'?i='+acid,true);
    httpRequest.setRequestHeader("Content-Type","application/x-www-form-urlencoded");

    var post_str=`orderFee=${params.orderFee}&payMethod=${params.payMethod}&orderTitle=${params.orderTitle}&orderTid=${params.orderTid}&module=${params.module}`;

    httpRequest.send(post_str);
    httpRequest.onreadystatechange = ()=>{
        if(httpRequest.readyState == 4 && httpRequest.status == 200){
            var data = JSON.parse(httpRequest.responseText);
            if(data.code_url){
                params.success(data);
            }else{
                params.fail(data);
            }
            params.complete(data);
        }else{
            console.log(httpRequest.response);
        }
    }
}




// 支付宝h5网页支付
util.alipayH5=function(params){
    var acid=util.getQueryString('i');
    if(!acid)return;
    var api='alipayH5';
    var url='/index.php/admin/sunphp/'+api+'?i='+acid;
    var query=`&orderFee=${params.orderFee}&payMethod=${params.payMethod}&orderTitle=${params.orderTitle}&orderTid=${params.orderTid}&module=${params.module}`;
    return url+query;
}

// 支付宝PC网页支付
util.alipayWeb=function(params){
    var acid=util.getQueryString('i');
    if(!acid)return;
    var api='alipayWeb';
    var url='/index.php/admin/sunphp/'+api+'?i='+acid;
    var query=`&orderFee=${params.orderFee}&payMethod=${params.payMethod}&orderTitle=${params.orderTitle}&orderTid=${params.orderTid}&module=${params.module}`;
    return url+query;
}