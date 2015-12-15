# SiteInfo
PHP class that retrieves some basic information from an URL.

##Usage

Create an instance of the class providing an URL as a parameter to the constructor:

```
$siteInfo = new SiteInfo('https://github.com/koas/SiteInfo');
```

Now you can call any of these methods:

**getTitle()**

**getDescription()**

**getKeywords()**

**getIcon()**: retrieves the favicon file URL

**getImage()**: this method tries some tags (OpenGraph, Twitter Cards, MS tile images, Apple icons) to retrieve a bigger image for the site. If no image is available the favicon image is returned.

If you want to retrieve any other data you can use the generic method **getTagValue**. For example, let's say you want to retrieve the content of the robots tag. The tag looks like this:

```
<meta name="robots" content="INDEX,FOLLOW" />
```

You should call **getTagValue** like this:

```
$robotsContent = $siteInfo->getTagValue('meta', 'name', 'robots', 'content');
```