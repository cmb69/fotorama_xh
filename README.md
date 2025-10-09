# Fotorama_XH

Fotorama_XH facilitates the presentation of image galleries on a website.
The galleries are fully responsive, thumbnails are automatically created,
and the gallery administration in the back-end offers a full-fledged user
interface.

- [Requirements](#requirements)
- [Download](#download)
- [Installation](#installation)
- [Settings](#settings)
- [Usage](#usage)
  - [Gallery Administration](#gallery-administration)
  - [Thumbnails](#thumbnails)
  - [External images](#external-images)
  - [Manual Editing of Gallery Files](#manual-editing-of-gallery-files)
- [Limitations](#limitations)
- [Troubleshooting](#troubleshooting)
- [License](#license)
- [Credits](#credits)

## Requirements

Fotorama_XH is a plugin for [CMSimple_XH](https://cmsimple-xh.org/).
It requires PHP ≥ 7.4.0 with the dom extension, and CMSimple_XH ≥ 1.7.0.
To create thumbnails, the PHP extensions gd and exif are recommended.
Fotorama_XH also requires [Plib_XH](https://github.com/cmb69/plib_xh) ≥ 1.12;
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

To embed a gallery on a page, write:

    {{{fotorama('name')}}}

where `name` has to be replaced with the name of a gallery you have created
in the [gallery administration](#gallery-administration).

You can embed multiple galleries on a single page.

### Gallery Administration

You can administrate your galleries under `Plugins` → `Fotorama` → `Galleries`.
The user interface is pretty much self-explaining; if in doubt, try it out.
Still, a couple of notes are in order:

* Fotorama is optimized for moderately large images (say, a few mega pixels).
  Loading of very large images may take too long for visitors who may have a
  rather slow internet connection; small images are not pleasant to view on
  large screens or retina devices.
  If your server supports WebP or even AVIF (that is reported in the system check),
  consider to use these formats, since they offer better compression than JPEG,
  and are widely supported nowadays (not as universially as JPEG, though).

* Only images inside of the images folder of CMSimple_XH are fully supported;
  while thumbnails will be displayed in the back-end for images outside of the
  folder, the images will not be shown in the actual galleries.

* Ordering of the images is possible via keyboard or drag and drop; the image
  thumbnails serve as handles; you can focus them, and then use the up and down
  (or left and right) arrows to move the images; or drag them to the desired
  place.

* `Hide details` shows the images in a compact view, which is particularly
  suitable for ordering or adding multiple images.

* While the `caption`s are displayed, the `description`s serve as alt attributes
  of the images, which is important information for visually impaired users.
  So consider to add `description`s which actually *describe* the image (like
  you might describe the image in a phone call).

### Thumbnails

The plugin creates thumbnails of the images whenever a gallery is saved in the
back-end.  These are stored under `plugins/fotorama/cache/`, and the folder
structure mirrors the structure of the image folder of CMSimple_XH.
The thumbnails are created in multiple sizes, so contemporary browsers can
choose the best fitting size.  Usually, there is no need to care about the
thumbnail cache; only when you are experimenting with many galleries/images,
but later decide to not publish these galleries, you can clear the cache (either
from the back-end or via FTP) to save some disk space.  Afterwards, you should
save all still existing galleries, so the required thumbnails are created.

Note that the thumbnail files are accessible to everyone.  If the original files
in the image folder of CMSimple_XH are protected, you need to apply the same
protection to the thumbnail cache.

Also note that the thumbnails do *not* retain any image meta data (except for ICC
color profiles) possibly stored in the original images.

### External images

It is also possible to use external images (i.e. images outside the images
folder) by specifying the fully qualified absolute URL.
The usual caveats apply in this case, for instance, the image might not be
available, and the image is loaded on behalf of visitors, possibly violating
their privacy.  Note that for legal reasons, no thumbnails are generated for
external images, but rather a default thumbnail is shown which you can change
by replacing `plugins/fotorama/images/external.svg` with an image of your choice.

You can freely mix external and internal images in a gallery.

Note, though, that external images are best avoided for the reasons stated above.

### Manual Editing of Gallery Files

The galleries are stored in the `content/` folder of CMSimple_XH.

If you edit gallery files manually, it is recommended to use an editor with support
for RelaxNG schema, and to validate against `gallery.rng` in the root folder of
the plugin.  Failure to do so might cause load errors of the galleries.  If that
happens, you can use the `Check` button in the plugin administration to find out
what is wrong with the gallery.

Note that after manual editing of a gallery file, it is reasonable to save it
again from the back-end, since the image dimensions are updated then, and also
any required thumbnails will be created.

## Limitations

For the galleries to be *fully* functional, a contemporary browser
with JavaScript is required.

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

The plugin uses [SimpleLightbox](https://simplelightbox.js.org/).
Many thanks to Andre Rinas, the developer of this library,
for his great work, and for publishing it under MIT license.

This plugin uses [Fotorama](https://fotorama.io/).
Many thanks to Artem Polikarpov, the developer of this library,
for his great work, and for publishing it under MIT license.

The plugin icon is designed by [Everaldo Coelho](https://www.everaldo.com/).
Many thanks for publishing this icon under LGPL.

[`external.svg`](https://commons.wikimedia.org/w/index.php?curid=112311856)
is designed by Pigeon43.  Many thanks for publishing this icon under CC BY-SA 4.0.

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