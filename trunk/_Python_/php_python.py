#!/usr/bin/env python3
# -*- coding: UTF-8 -*-

import time
import socket
import os
import sys
import process

# -------------------------------------------------
# 基本配置
# -------------------------------------------------
LISTEN_PORT = 8237  # 服务侦听端口
CHARSET = "utf-8"  # 设置字符集（和PHP交互的字符集）

# -------------------------------------------------
# Oracle数据库连接配置
# -------------------------------------------------
# import cx_Oracle
# #数据库字符集
# os.environ['NLS_LANG'] = 'SIMPLIFIED CHINESE_CHINA.UTF8'
# #数据库连接池
# pool = cx_Oracle.SessionPool(
#     user='diaoyf',
#     password='700327',
#     dsn='127.0.0.1/xe',
#     min=5,
#     max=10,
#     increment=1,
#     connectiontype=cx_Oracle.Connection,
#     threaded=True,
#     getmode=cx_Oracle.SPOOL_ATTRVAL_NOWAIT,
#     homogeneous=True)
#
# def getConn():
#     """获得数据库连接的公共函数"""
#     return pool.acquire()
#
# def closeConn(conn):
#     """释放数据库连接的公共函数"""
#     pool.release(conn)

"""
将当前进程fork为一个守护进程

注意：如果你的守护进程是由inetd启动的，不要这样做！inetd完成了
所有需要做的事情，包括重定向标准文件描述符，需要做的事情只有
chdir() 和 umask()了
"""


def daemonize(stdin='/dev/null', stdout='/dev/null', stderr='dev/null'):
    """
    Fork当前进程为守护进程，重定向标准文件描述符
    （默认情况下定向到/dev/null）
    """
    # Perform first fork.
    try:
        pid = os.fork()
        if pid > 0:
            sys.exit(0)  # first parent out
    except OSError as e:
        sys.stderr.write("fork #1 failed: (%d) %s\n" % (e.errno, e.strerror))
        sys.exit(1)

    # 从母体环境脱离
    os.chdir("/")
    os.umask(0)
    os.setsid()
    # 执行第二次fork
    try:
        pid = os.fork()
        if pid > 0:
            sys.exit(0)  # second parent out
    except OSError as e:
        sys.stderr.write("fork #2 failed: (%d) %s]n" % (e.errno, e.strerror))
        sys.exit(1)

    # 进程已经是守护进程了，重定向标准文件描述符
    for f in sys.stdout, sys.stderr:
        f.flush()
    si = open(stdin, 'r')
    so = open(stdout, 'a+')
    se = open(stderr, 'a+', 1)
    os.dup2(si.fileno(), sys.stdin.fileno())
    os.dup2(so.fileno(), sys.stdout.fileno())
    os.dup2(se.fileno(), sys.stderr.fileno())


# -------------------------------------------------
# 主程序
#    请不要随意修改下面的代码
# -------------------------------------------------
if __name__ == '__main__':

    # 日志目录
    if 1 < len(sys.argv):
        log_dir = sys.argv[1]
    else:
        log_dir = sys.path[0]

    # 创建守护进程
    daemonize('/dev/null', log_dir + '/daemon_stdout.log', log_dir + '/daemon_error.log')

    print("-------------------------------------------")
    print("- PPython Service")
    print("- Time: %s" % time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time())))
    print("-------------------------------------------")

    sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)  # TCP/IP
    sock.bind(('', LISTEN_PORT))
    sock.listen(5)

    print("Listen port: %d" % LISTEN_PORT)
    print("charset: %s" % CHARSET)
    print("Server startup...")

    while 1:
        connection, address = sock.accept()  # 收到一个请求
        # print ("client's IP:%s, PORT:%d" % address)
        # 处理线程
        try:
            # 执行
            process.ProcessThread(connection).start()
        except:
            pass
