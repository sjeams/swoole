import Request from '@/utils/requset.js'
let request = new Request().http

//全局定义请求头
export default {
    // 请求样式
    classifyLeft: function(data) {
        return request({
            url: "/category/list", //请求头
            method: "GET", //请求方式
            data: data, //请求数据
        })
    },
}
/*
请求样式：
    自定义名字: function(data) {
        return request({
            url: "/banner", //请求头
            method: "GET", //请求方式 
            data: data,    //请求数据
            token: token, // 可传  
            hideLoading: false, //加载样式
        })
    },
*/