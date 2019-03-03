在使用 PHP 做简单的爬虫的时候，我们经常会遇到需要下载远程图片的需求，所以下面来简单实现这个需求。

### 1.使用 curl
####比如我们有下面这两张图片：
```
$images = [

    'https://dn-laravist.qbox.me/2015-09-22_00-17-06j.png',

    'https://dn-laravist.qbox.me/2015-09-23_00-58-03j.png'

];
```
###第一步，我们可以直接来使用最简单的代码实现：
```
function download($url, $path = 'images/')

{

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

    $file = curl_exec($ch);

    curl_close($ch);

    $filename = pathinfo($url, PATHINFO_BASENAME);

    $resource = fopen($path . $filename, 'a');

    fwrite($resource, $file);

    fclose($resource);

}
```
#那在下载远程图片的时候就可以这样：
```
foreach ( $images as $url ) {

    download($url);

}
```
##2.封装一个类
###缕清思路之后，我们可以将这个基本的功能封装到一个类中：
```
class Spider {


    public function downloadImage($url, $path = 'images/')

    {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

        $file = curl_exec($ch);

        curl_close($ch);

        $filename = pathinfo($url, PATHINFO_BASENAME);

        $resource = fopen($path . $filename, 'a');

        fwrite($resource, $file);

        fclose($resource);

    }

}    
```
#在者，我们还可以这样稍微优化一下：
```
public function downloadImage($url, $path='images/')

    {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

        $file = curl_exec($ch);

        curl_close($ch);


        $this->saveAsImage($url, $file, $path);

    }


    private function saveAsImage($url, $file, $path)

    {

        $filename = pathinfo($url, PATHINFO_BASENAME);

        $resource = fopen($path . $filename, 'a');

        fwrite($resource, $file);

        fclose($resource);

    }
   ```
#封装成类之后，我们可以这样调用代码来下载图片：
```
$spider = new Spider();


foreach ( $images as $url ) {

    $spider->downloadImage($url);

}
```
这样，对付基本的远程图片下载就OK了。