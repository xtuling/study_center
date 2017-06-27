# -*- coding: UTF-8 -*-

# -------------------------------------------------
#    请不要随意修改文件中的代码
# -------------------------------------------------


import sys
import time
import threading
import socket
import json
import datetime

import php_python

REQUEST_MIN_LEN = 10  # 合法的request消息包最小长度
TIMEOUT = 180  # socket处理时间180秒

pc_dict = {}  # 预编译字典，key:调用模块、函数、参数字符串，值是编译对象
global_env = {}  # global环境变量


def index(byte_str, c, pos=0):
    """
    查找c字符在bytes中的位置(从0开始)，找不到返回-1
    pos: 查找起始位置
    """
    for i in range(len(byte_str)):
        if i <= pos:
            continue
        if byte_str[i] == c:
            return i
    else:
        return -1


def parse_php_req(p):
    """
    解析PHP请求消息
    返回：元组（模块名，函数名，入参list）
    """

    str_p = bytes.decode(p)
    params = json.loads(str_p)

    module_func = params[0]  # 第一个元素是调用模块和函数名
    # print("模块和函数名:%s" % module_func)
    # print("参数:%s" % params[1:])
    pos = module_func.find("::")
    module = module_func[:pos]  # 模块名
    func = module_func[pos + 2:]  # 函数名
    return module, func, params[1:]


class ProcessThread(threading.Thread):
    """
    preThread 处理线程
    """

    def __init__(self, client_socket):
        threading.Thread.__init__(self)

        # 客户socket
        self._socket = client_socket

    def run(self):

        start = datetime.datetime.now()
        start_clock = time.clock()

        # ---------------------------------------------------
        #    1.接收消息
        # ---------------------------------------------------

        try:
            self._socket.settimeout(TIMEOUT)  # 设置socket超时时间
            first_buf = self._socket.recv(16 * 1024)  # 接收第一个消息包(bytes)
            if len(first_buf) < REQUEST_MIN_LEN:  # 不够消息最小长度
                print("非法包，小于最小长度: %s" % first_buf)
                self._socket.close()
                return

            first_comma = index(first_buf, 0x2c)  # 查找第一个","分割符
            total_len = int(first_buf[0:first_comma])  # 消息包总长度
            print("消息长度:%d" % total_len)
            req_msg = first_buf[first_comma + 1:]
            while len(req_msg) < total_len:
                req_msg = req_msg + self._socket.recv(16 * 1024)

                # 调试
                # print ("请求包：%s" % req_msg)

        except Exception as e:
            print('接收消息异常', e)
            self._socket.close()
            return

        # ---------------------------------------------------
        #    2.调用模块、函数检查，预编译。
        # ---------------------------------------------------

        # 从消息包中解析出模块名、函数名、入参list
        module, func, params = parse_php_req(req_msg)

        if module not in pc_dict:  # 预编译字典中没有此编译模块
            # 检查模块、函数是否存在
            try:
                call_mod = __import__(module)  # 根据module名，反射出module
                pc_dict[module] = call_mod  # 预编译字典缓存此模块
            except Exception as e:
                print('模块不存在:%s' % module, e)
                self._socket.sendall(("F" + "module '%s' is not exist!" % module).encode(php_python.CHARSET))  # 异常
                self._socket.close()
                return
        else:
            call_mod = pc_dict[module]  # 从预编译字典中获得模块对象

        try:
            getattr(call_mod, func)
        except Exception as e:
            print('函数不存在:%s' % func, e)
            self._socket.sendall(("F" + "function '%s()' is not exist!" % func).encode(php_python.CHARSET))  # 异常
            self._socket.close()
            return

        # ---------------------------------------------------
        #    3.Python函数调用
        # ---------------------------------------------------

        try:
            params = ','.join([repr(x) for x in params])
            # print ("调用函数及参数：%s(%s)" % (module+'.'+func, params) )

            # 加载函数
            compile_str = "import %s\nret = %s(%s)" % (module, module + '.' + func, params)
            print("函数调用代码:%s" % compile_str)
            compile_func = compile(compile_str, "", "exec")

            if func not in global_env:
                global_env[func] = compile_func
            local_env = {}
            exec(compile_func, global_env, local_env)  # 函数调用
            # print (global_env)
            # print (local_env)
        except Exception as e:
            print('调用Python业务函数异常', e)
            err_type, err_msg, traceback = sys.exc_info()
            self._socket.sendall(("F%s" % err_msg).encode(php_python.CHARSET))  # 异常信息返回
            self._socket.close()
            return

        # ---------------------------------------------------
        #    4.结果返回给PHP
        # ---------------------------------------------------
        # retType = type(local_env['ret'])
        # print ("函数返回：%s" % retType)
        resp_str = json.dumps(local_env['ret'])  # 函数结果组装为PHP序列化字符串

        try:
            # 加上成功前缀'S'
            resp_str = "S" + resp_str
            # 调试
            # print ("返回包：%s" % resp_str)
            self._socket.sendall(resp_str.encode(php_python.CHARSET))
        except Exception as e:
            print('发送消息异常', e)
            err_type, err_msg, traceback = sys.exc_info()
            self._socket.sendall(("F%s" % err_msg).encode(php_python.CHARSET))  # 异常信息返回
        finally:
            self._socket.close()

            end = datetime.datetime.now()
            end_clock = time.clock()

            start_str = start.strftime('%H:%M:%S')
            end_str = end.strftime('%H:%M:%S')
            print("开始时间：%s" % start_str)
            print("结束时间：%s" % end_str)
            print("读取时间 %d 秒" % (end.timestamp() - start.timestamp()))
            print("CPU时间 %d" % (end_clock - start_clock))
            return
