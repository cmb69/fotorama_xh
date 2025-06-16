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
  - [Manual Editing of Gallery Files](#manual-editing-of-gallery-files)
- [Limitations](#limitations)
- [Troubleshooting](#troubleshooting)
- [License](#license)
- [Credits](#credits)

## Requirements

Fotorama_XH is a plugin for [CMSimple_XH](https://cmsimple-xh.org/).
It requires PHP ≥ 7.4.0 with the dom extension, and CMSimple_XH ≥ 1.7.0.
To create thumbnails, the PHP extensions gd and exif are recommended.
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
1. Set write permissions for the subdirectories `cache/`, `config/`, `css/` and
   `languages/`.
1. Move `plugins/fotorama/editorhook.php` to `plugins/filebrowser/editorhooks/fotorama/script.php`
   so that the filebrowser is available when editing galleries.
1. Check under `Plugins` → `Fotorama` that all requirements for using the
   plugin are fulfilled.

## Settings

The configuration of the plugin is done as with many other CMSimple_XH
plugins in the back-end of the Website.
Go to `Plugins` → `Fotorama`.

You can change the default settings of Fotorama_XH under `Config`.
Hints for the options will be displayed
when hovering over the help icon with your mouse.

Localization is done under `Language`.
You can translate the character strings to your own language,
if there is no appropriate language file available,
or customize them according to your needs.

The look of Fotorama_XH can be customized under `Stylesheet`.
At the top of the file you find a `simple customization` section which allows
for some simple customizations.  Everything below needs advanced CSS knowledge
to customize.

## Usage

### Prepare a gallery

Navigate to `Plugins` → `Fotorama` → `Galleries` and use the form to create
an initial gallery with all images of the chosen folder.
The gallery will be stored in the `content/` folder of CMSimple_XH.
Each language has its own set of gallery definitions,
so you can localize the image captions.

After having successfully created the gallery,
you are redirected to the gallery editor
where you can fine-tune the gallery.
You can add and remove images, and reorder them.

- `width` and `ratio`:
  If these attributes are omitted,
  the width and aspect ratio are determined by the first image.
  Note that the images will be resized to fit within the width/ratio,
  so that it is possible to have portrait and landscape images
  mixed in the same gallery without distortion.
- `nav`:
  The required thumbnails are automatically generated on demand,
  and stored in the `cache/` folder of the plugin.
- `fullscreen`:
  This allows the visitor to enter fullscreen mode.
  Choose either `true`,
  what will restrict the fullscreen mode to the browser window,
  but also works for older browsers,
  or `native` what uses the full screen size
  if supported by the browser.

When the gallery is saved, it is automatically validated against the RelaxNG schema
(`gallery.rng`).

### External images

It is also possible to show external images
(i.e. images outside your images folder)
by specifying the fully qualified absolute URL.
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

### Manual Editing of Gallery Files

If you edit gallery files manually, it is recommended to use an editor with support
for RelaxNG schema, and to validate against `gallery.rng` in the root folder of
the plugin.  Failure to do so might cause load errors of the charts.  If that
happens, you can use the `Check` button in the plugin administration to find out
what is wrong with the gallery.

## Limitations

For the galleries to be *fully* functional,
JavaScript has to be enabled in the browser of the visitor.

If the PHP exif extension is not available, thumbnails of images with Exif
`Orientation` tags will not be displayed properly (they are rotated and or flipped).

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

The plugin icon is designed by [Everaldo Coelho](https://www.everaldo.com/).
Many thanks for publishing this icon under LGPL.
The plugin also uses icons from the
[Oxygen icon set](http://www.oxygen-icons.org/).
Many thanks for publishing this icon set under GPL.

Many thanks to [Jeffrey Friedl](https://regex.info/blog/photo-tech/color-spaces-page2)
for nicely demonstrating the effects of embedded ICC color profiles.

Many thanks to the community at the
[CMSimple_XH forum](https://www.cmsimpleforum.com/)
for tips, suggestions and testing.
Special thanks to *Traktorist* for providing early
and valuable feedback on the first beta version.

And last but not least many thanks to
[Peter Harteg](https://harteg.dk/), the “father” of CMSimple,
and all developers of [CMSimple_XH](https://www.cmsimple-xh.org/)
without whom this amazing CMS would not exist.