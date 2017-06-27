
var data = {
    "errcode": 0,
    "errmsg": "ok",
    "errsdkcode": "",
    "requestId": "",
    "timestamp": 1487016662000,
    "result": {
        "list": [{
            "a_id": "1",
            "at_id": "1",
            "number": "1",
            "mem_uid": "D0A0C79B7F0000013DC51B614A038FDF",
            "title": "1212",
            "dp_id": "1",
            "start_time": "1",
            "end_time": "2",
            "leave_time": "1",
            "approve_status": "1",
            "is_abnormal": "1",
            "created": "1",
            "is_abnormal_desc": "正常",
            "approve_status_desc": "审批中",
            "dp_name": [{
                "dp_id": "1",
                "dp_name": null
            }],
            "mem_name": [{
                "mem_uid": "D0A0C79B7F0000013DC51B614A038FDF",
                "mem_name": "张帅",
                "mem_face": "http://shp.qpic.cn/bizmp/b28orD0nfSzX1Lln6mxlicyejcySrMOn9RmichgR1IQ0f8079m3ODicCw/"
            }],
            "at_name": "1"
        }],
        "total": "1",
        "page": 1,
        "limit": 15
    }
};

function json2table(data, upNode) {

    var showItems = '';
    for(var key in data) {
        var showKey = upNode+"."+key;
        var showType = typeof data[key];
        if(Array.isArray(data[key])) {
            showKey += "[]"
            showType = 'array'
        }

        showItems += "\n||!!#3366ff " + showKey + " !! ||!!#5566ff " + showType + " !! || "
                     + data[key] + " ||";

        if(showType == 'object') {
            showItems += json2table(data[key], showKey);
        } else if(showType == 'array') {
            showItems += json2table(data[key][0], showKey);
        }
    }
    return showItems;
}

console.log(json2table(data, ''));
