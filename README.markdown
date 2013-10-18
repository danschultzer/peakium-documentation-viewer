# Peakium Documentation Viewer

Peakium Documentation Viewer is built to display multi-format documentation in a beautiful presenter. It is built to require no maintenance. It can version track documentation, thus making it easy to maintain and update documentation in git repositories and archive files.

## Composer

> Warning: require(/path/to/documentation/viewer/vendor/autoload.php): failed to open stream: No such file or directory

The documentation viewer requires [Composer](http://getcomposer.org/) to work. Install composer and run composer inside the directory. Composer will download and install all required dependencies in the `vendor` directory.

    $ composer install

Installation guide for [Linux](http://getcomposer.org/doc/00-intro.md#installation-nix) and [Windows](http://getcomposer.org/doc/00-intro.md#installation-windows).

## Setup

You need to create the file `config.json` and add the appropriate configuration values. Use `config.json.default` as example.

You install (and update) the documentation files by running

    $ php bin/install

This command will download and install (or update) from all documentation URL's specified in the `documentation` key in `config.json`, into the directory `docs`.

## Configuration Parameters

### Global

Key          | Description
:------------|:-----------
`title`      | The base HTML page title.
`layout-dir` | The layout directory to use, `default` used by default.

### Pages

The `pages` key consists of a directory with one or more page(s). The order of each page in the configuration, will be the order they are displayed in the sidebar menu, including the section.

The page can contain following parameters:

Key         | Description
:---------- |:-----------
`title`     | The title for the page, displayed in the menu.
`section`   | The section the page should be categorised in.
`format`    | The type of documentation. `view` means that it will be loaded from the `view` directory. `markdown` means that it will parse a markdown file.
`reference` | The location of the documentation. For format `view` this would be in the `view` directory. For `markdown` this would be in the `docs` directory.

#### Markdown specific values
Key              | Description
:----------------|:-----------
`default_file`   | The file to load first in a documentation directory. Default is `README`.
`file_extension` | The extension of the documentation files. Default is `.md`.
`depth`          | The depth that a markdown file should display. When not reaching maximum depth the documentation viewer will include any subsequent linked file in the same page, in the order they are found.

### Documentation

The `documentation` key consists of a directory with one or more documentation location(s). They are used to download and version track documentations. The format are as below:

    "directory-name": "url"

The directory-name will be the actual name that the directory will be installed in under `docs`, and this is also the `reference` value for the `pages`.

## Custom Controllers

You can add custom controllers in the controller/ directory. [Read more](/controller/README.markdown)

The custom controller will be loaded first, thus if you access `http://localhost/my_controller_name/` it will try to load the `MyControllerName` class first (filename would be `my_controller_name.php`). If no class or file is found, the documentation viewer will try find the appropriate page in the `pages` key of `config.json`.

## Layout

Layout can be modified in `view`. The best way to make your custom layout is to create a new directory in `view`. `default` is used as an example layout.

Any assets (images, css, etc) can be added in `public/assets` directory. This directory also contains a `default` directory as example assets.

## Requirements

* PHP 5.3+
* Composer
* Git for git documentation

## Authors

[Dan Schultzer](http://twitter.com/danschultzer)

[Kasper Christensen](http://twitter.com/kc_aplosweb)