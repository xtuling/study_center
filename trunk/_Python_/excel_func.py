# -*- coding: utf-8 -*-

from app.utils.excel import *


def read_excel(filename, start=0, end=0, sheet=0, ver=2003):
    """
    读取 excel 2003/2007
    :param filename: 文件路径
    :param start: 起始序号
    :param end: 结束序号, 为 0 时, 表示读取到结尾
    :param sheet: sheet 序号
    :param ver: excel 版本
    :return: 数据列表
    """

    if '2003' == str(ver):
        excel = Excel2003Reader(filename, sheet)
    elif '2007' == str(ver):
        excel = Excel2007Reader(filename, sheet)
    else:
        excel = Excel2003Reader(filename, sheet)

    return excel.read(start, end)


def write_excel(filename, columns, rows, ver=2003):
    """
    写 excel 2003
    :param filename: 文件名称
    :param columns: 列信息
    :param rows: excel行信息
    :param ver: excel 版本
    :return: bool
    """

    if '2003' == str(ver):
        excel = Excel2003Writer(filename)
    elif '2007' == str(ver):
        excel = Excel2007Writer(filename)
    else:
        excel = Excel2003Writer(filename)

    # 创建 sheet
    sheet = excel.create_sheet('data')

    head_len = len(columns)
    for i in range(head_len):
        excel.set_cell(sheet, 0, i, columns[i])

    for i in range(len(rows)):
        for k in range(len(rows[i])):
            excel.set_cell(sheet, i + 1, k, rows[i][k])

    excel.save()
    return True


def write_excel_sheet(filename, sheets, data, ver=2003):
    """
    创建多 sheet 的 excel 文件
    :param filename: 文件名
    :param sheets: sheet 名称
    :param data: 数据
    :param ver: excel 版本
    :return:
    """

    if '2003' == str(ver):
        excel = Excel2003Writer(filename)
    elif '2007' == str(ver):
        excel = Excel2007Writer(filename)
    else:
        excel = Excel2003Writer(filename)

    for s in range(len(sheets)):
        sheet = excel.create_sheet(sheets[s])

        column_len = len(data[s]["columns"])
        for i in range(column_len):
            excel.set_cell(sheet, 0, i, data[s]["columns"][i])

        row_len = len(data[s]["rows"])
        for i in range(row_len):
            for k in range(column_len):
                excel.set_cell(sheet, i + 1, k, data[s]["rows"][k])

    excel.save()
    return True
