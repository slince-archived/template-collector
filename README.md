# 模板采集器

通用模板采集器基本组件

## 安装
 * 基于composer安装
  `composer require slince/template-collector *@dev `
 * 要求
    - php >= 5.5.9

## 使用

### 基本用法
```
use Slince\Collector\Collector;
use Slince\Event\Event;

$savePath = __DIR__ . '/html'; //保存路径
$entranceUrl = 'http://demo.sc.chinaz.com/Files/DownLoad/moban/201604/moban1178/index.html'; //入口链接
//创建采集器
$collector = new Collector($savePath, $entranceUrl);
//绑定事件
$collector->getDispatcher()->bind(Collector::EVENT_CAPTURED_URL_REPOSITORY, function(Event $event){
    $repository = $event->getArgument('repository');
    echo $repository->getUrl()->getUrlString() . " Captured OK!\r\n";
});
$collector->run();
```

### 事件绑定
事件绑定是可选的，如果不绑定事件采集器依然可以正常进行，但如果您需要知道采集器进度，绑定事件是个不二的选择；目前采集器
支持三种事件，
- `Collector::EVENT_FILTER_URL` url筛选事件，当采集器判断一个新链接是否需要被处理的时候触发
- `Collector::EVENT_CAPTURE_URL_REPOSITORY` 开始采集页面事件，当链接内容下载完毕开始处理采集时触发
- `Collector::EVENT_CAPTURED_URL_REPOSITORY` 页面采集完成事件，当链接内容采集完毕时触发

### 设置允许抓取的host
为了避免采集器过分采集，默认情况下采集器不会抓取host和入口链接的host不符的链接，所以如果你要采集的网站的资源文件使用了
其它域名，那么您需要设置允许抓取的host
```
$collector->setAllowedCaptureHosts([
    ...
]);
```

### 设置采集规则
如果您要采集的网站同类型链接过多，那么您可以设置采集规则避免重复下载
```
$collector->setUrlPatterns([
    'category' => '#/categories/\d+#',
    'product' => '#/products/\d+#',
    'article' => '#/articles/\d+#',
]);
```
- 如果符合采集规则的url没有文件扩展名，那么在生成本地文件的时候会采用采集规则的键名做文件名，比如例中的分类页
在下载到本地的时候会使用category.html做文件名

- 如果您需要只下载符合采集规则的url，那么您需要做个设置
```
$collector->setOnlyCaptureUrlPatterns(true);
```

### 白名单、黑名单链接
```
$collector->setBlacklistUrls([
   ...
]);
$collector->setWhitelistUrls([
   ...
]);
```



