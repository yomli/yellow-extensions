Editions 0.8.21.4
==============

Editions is a simple theme for [Datenstrom Yellow](https://datenstrom.se/yellow/) using [Croissant](https://github.com/yomli/croissant/). It is in use here: <https://editions.yom.li/>

## How to customise a theme

All theme files are stored in your `system/themes` folder. All layout files are stored in your `system/layouts` folder. You can edit these files. Your changes will not be overwritten when the website is updated. You may need to translate some labels (from French).

## Installation

[Download extension](https://github.com/yomli/yellow-extensions/raw/main/zip/editions.zip) and copy zip file into your `system/extensions` folder. Right click if you use Safari.

Copy all files located in [manual](https://github.com/yomli/yellow-extensions/raw/main/zip/editions-manual.zip) to the root of your website. Yellow can't copy those files by itself.

### New blog

Just a tip: edit `content/shared/page-new-blog.md` to something like this:

```
---
Title: Book title
Subtitle:
Collection:
Published: @datetime
Author: My Name
Layout: blog
Tag: Example
Details: Pages->320|Format->14 x 17cm|ISBN (paper)->978-
LinksRead: Lyber->/lyber/
LinksPaperback: Lulu->
LinksEbook: Lulu->
---
This is a new book
```

## Designer

Yomli. [Get help](https://datenstrom.se/yellow/help/).
