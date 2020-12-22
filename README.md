# md2mcbb

将markdown转换为mcbbs bbcode并加以美化的程序

## 优势

- 非简易标签的替换
- 在转换的过程中加以美化
- 提供多种模板供选用
- 对markdown语法支持较为全面

## 劣势

- 对于markdown出现html代码的情况无支持
- mcbbs引用无法多层，不作处理
- mcbbs的 [code] 代码块中不能出现 [/code] ，导致markdown代码块中不能出现 [/code] ，目前未找到解决方案
- 对 [ 字符转码使用添加不可见图片的方式，在复制时可能会显现

## 如何使用？

打开运行站

![](https://s1.ax1x.com/2020/08/28/dTm9sO.md.png)

将代码复制到mcbbs

![](https://s1.ax1x.com/2020/08/28/dTnP10.md.png)

## 如何安装？

````shell script
git clone https://github.com/Mohist-Community/md2mcbb.git
cp .env.example .env
composer install
````

（和其他laravel程序一样的啦~只不够这个不用配置数据库

## 二进制版本如何使用？

二进制版本仍需php环境（版本5.3+ 无拓展依赖）
````shell script
./md2mcbb [输入文件] [输出文件]
````

文件留空或输入 - 解析为使用std

可能的方式如下：

````shell script
cat README.md | ./md2mcbb > op.txt
cat README.md | ./md2mcbb - op.txt
cat README.md | ./md2mcbb
./md2mcbb README.md > op.txt
./md2mcbb README.md op.txt
./md2mcbb README.md
````

## 效果怎么样？

你可以通过下面的页码进行选择，都是使用本markdown使用不同的美化方案实现的

**加粗** *斜体* `下划线` [链接](https://www.mcbbs.net)

- aa
  - bb
    - cc
    - dd
  - ee
  - ff

| 加密方式   |                                             建议 |
| ---------- | ----------------------------------------------- |
| BCrypt     |        安全性极高，便于迁移到Xenforo等现代化应用 |
| Plain      |                                         安全性无 |

## 开源协议

Licensed under the MIT

Copyright 2020 Mohist-Community

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
