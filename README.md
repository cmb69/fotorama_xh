# Fotorama_XH


Fotorama_XH facilitates to embed [Fotorama](https://fotorama.io/)
galleries on CMSimple_XH pages.
The plugin does not offer any image upload facility,
but instead uses images from the images folder of CMSimple_XH
or from somewhere else on the World Wide Web
(only JPEG is supported for now).
Every gallery can have its own settings,
and every image can have an additional caption.

- [Requirements](#requirements)
- [Download](#download)
- [Installation](#installation)
- [Settings](#settings)
- [Usage](#usage)
    - [Prepare a gallery](#prepare-a-gallery)
    - [External images](#external-images)
    - [Embed a gallery](#embed-a-gallery)
- [Limitations](#limitations)
- [Troubleshooting](#troubleshooting)
- [License](#license)
- [Credits](#credits)

## Requirements

Fotorama_XH is a plugin for [CMSimple_XH](https://cmsimple-xh.org/).
It requires PHP ≥ 7.4.0 with the dom, gd and SimpleXML extensions,
and CMSimple_XH ≥ 1.7.0.
Fotorama_XH also requires [Plib_XH](https://github.com/cmb69/plib_xh) ≥ 1.10;
if that is not already installed (see `Settings` → `Info`),
get the [lastest release](https://github.com/cmb69/plib_xh/releases/latest),
and install it.

## Download

The [lastest release](https://github.com/cmb69/fotorama_xh/releases/latest)
is available for download on Github.

## Installation

The installation is done as with many other CMSimple_XH plugins.

1. Backup the data on your server.
1. Unzip the distribution on your computer.
1. Upload the whole directory `fotorama/` to your server into
   the `plugins/` directory of CMSimple_XH.
1. Set write permissions for the subdirectories `cache/`, <!-- `config/`, --> `css/` and
   `languages/`.

## Settings

The configuration of the plugin is done as with many other CMSimple_XH
plugins in the back-end of the Website.
Go to `Plugins` → `Fotorama`.

<!--
You can change the default settings of Fotorama_XH under `Config`.
Hints for the options will be displayed
when hovering over the help icon with your mouse.
-->

Localization is done under `Language`.
You can translate the character strings to your own language,
if there is no appropriate language file available,
or customize them according to your needs.

The look of Fotorama_XH can be customized under `Stylesheet`.

## Usage

### Prepare a gallery

At first you have to upload some images into a subfolder
the image folder of CMSimple_XH.
You can either use the filebrowser of CMSimple_XH
or your favorite FTP client.
Then you have to create the gallery definition XML file in the back-end.
Navigate to `Plugins` → `Fotorama` → `Galleries` and use the form to create
an initial XML file with all images of the chosen folder.
The name of the gallery may only contain lower case Roman characters
(`a`-`z`), Arabic digits (`0`-`9`) and hyphens (`-`).
The name of the gallery will be used as filename (`.xml` is appended),
and the file is stored in the content folder of CMSimple_XH.
Each language has its own set of gallery definition files,
so you can localize the image captions.

After having successfully created the XML file,
you are redirected to the gallery editor
where you can fine-tune the gallery by editing the XML file.
You can remove and add `<pic>` elements, and reorder them.
For every `<pic>` element you can optionally specify
a `caption` attribute whose value will be shown in the gallery;
the caption will also be used as `alt` attribute of the HTML `<img>`.
You can change the value of the `path` attribute,
but you must not remove the attribute completely.
Note that you should not touch the first three
lines of the file (the XML and the doctype declaration).

Furthermore you can specify additional attributes
(the `path` attribute is mandatory again)
for the `<gallery>` element which affect the functionality
and look-and-feel of the gallery.
The following attributes are supported:

- `width` and `ratio`:
  These attributes specify the width and aspect ratio of the gallery,
  respectively.
  The width is either a plain number giving the width in pixels (e.g. `400`)
  or a percentage of the available horizontal space (e.g. `100%`),
  which is especially useful for responsive layouts.
  The ratio is either a fraction (e.g. `400/300` or `16/9`)
  or a decimal numer (e.g. `1.3333`).
  If these attributes are omitted,
  the width and aspect ratio are determined by the first image.
  Note that the images will be resized to fit within the width/ratio,
  so that it is possible to have portrait and landscape images
  mixed in the same gallery without distortion.
- `nav`:
  Only `thumbs` is allowed if the attribute is specified.
  This will turn the slim dot navigation into a thumbnail navigation.
  The required thumbnails are automatically generated on demand,
  and stored in the cache directory of the plugin.
- `fullscreen`:
  This allows the visitor to enter fullscreen mode.
  Choose either `true`,
  what will restrict the fullscreen mode to the browser window,
  but also works for older browsers,
  or `native` what uses the full screen size
  if supported by the browser.
- `transition`:
  Either `slide` (the default if the attribute is omitted),
  `crossfade` or `dissolve`.
  The latter is probably only useful if you have images
  that differ only slightly;
  otherwise `crossfade` is preferable.

When the file is saved, it is automatically validated against the RelaxNG schema
(`gallery.rng`).

If you have [Codeeditor_XH](https://github.com/cmb69/codeeditor_xh/releases)
installed, editing the XML is a bit more bearable.

### External images

It is also possible to show external images
(i.e. images outside your images folder)
by specifying the fully qualified absolute URL
of the image as `path` of the `<pic>` element.
The usual caveats apply in this case,
for instance, the image might not be available,
and there might be legal constraints.
Note that no thumbnails are generated for external images,
but rather a default thumbnail is shown which you can change by replacing
`plugins/fotorama/images/external.jpg` with an image of your choice.

You can freely mix external images and images in the gallery folder.

### Embed a gallery

To embed a gallery on a page simply write:

    {{{fotorama('%NAME%')}}}

where `%NAME%` is the name of the gallery, e.g.

    {{{fotorama('holidays')}}}

## Limitations

For the galleries to be *fully* functional,
JavaScript has to be enabled in the browser of the visitor.

## Troubleshooting

Report bugs and ask for support either on
[Github](https://github.com/cmb69/fotorama_xh/issues)
or in the [CMSimple_XH Forum](https://cmsimpleforum.com/).

## License

Fotorama_XH is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Fotorama_XH is distributed in the hope that it will be useful,
but *without any warranty*; without even the implied warranty of
*merchantibility* or *fitness for a particular purpose*. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Fotorama_XH.  If not, see <https://www.gnu.org/licenses/>.

Copyright 2015-2021 Christoph M. Becker

## Credits

This plugin is powered by [Fotorama](https://fotorama.io/).
Many thanks to Artem Polikarpov, the developer of this library,
for his great work, and for publishing it under MIT license.

The plugin icon is designed by [Everaldo Coelho](https://www.everaldo.com/). Many thanks for publishing this icon under LGPL.
The plugin also uses icons from the
[Oxygen icon set](http://www.oxygen-icons.org/).
Many thanks for publishing this icon set under GPL.

Many thanks to the community at the
[CMSimple_XH forum](https://www.cmsimpleforum.com/)
for tips, suggestions and testing.
Special thanks to *Traktorist* for providing early
and valuable feedback on the first beta version.

And last but not least many thanks to
[Peter Harteg](https://harteg.dk/), the “father” of CMSimple,
and all developers of [CMSimple_XH](https://www.cmsimple-xh.org/)
without whom this amazing CMS would not exist.