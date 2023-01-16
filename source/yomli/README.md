Yomli 0.8.21.2
==============

Yomli is an advanced theme for [Datenstrom Yellow](https://datenstrom.se/yellow/) using [Croissant](https://github.com/yomli/croissant/). It is in use here: <https://yom.li/>

## How to customise a theme

All theme files are stored in your `system/themes` folder. All layout files are stored in your `system/layouts` folder. You can edit these files. Your changes will not be overwritten when the website is updated. You may need to translate some labels (from French).

## Installation

[Download extension](https://github.com/yomli/yellow-extensions/raw/main/zip/editions.zip) and copy zip file into your `system/extensions` folder. Right click if you use Safari.

Copy all files located in [manual](https://github.com/yomli/yellow-extensions/raw/main/zip/yomli-manual.zip) to the root of your website. Yellow can't copy those files by itself.

### New blog

Just a tip: edit `content/shared/page-new-blog.md` to something like this:

```
---
Title: New blog page
Published: @datetime
Author: My Name
Layout: blog
Tag: Example
Description: Or use [--more--]
Image: title-slug.jpg
ImageAlt: Mona Lisa, by Leonardo da Vinci.
---
This is a new blog page.
```

The cover image will be searched in `media/images/cover/title-slug.jpg`. I use this protocol to get them:

- Find a good artwork on https://artvee.com/
- Crop 16:9
- Resize with length: 800px
- `jpegoptim --size=64k title-slug.jpg`

## Designer

Yomli. [Get help](https://datenstrom.se/yellow/help/).
