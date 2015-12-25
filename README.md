# ruTorrent

ruTorrent is a front-end for the popular Bittorrent client [rtorrent](http://rakshasa.github.io/rtorrent).
This project is released under the GPLv3 license, for more details, take a look at the LICENSE.md file in the source.

Note: This is a pre-release fork of ruTorrent that has not yet had a stable release, we do not advise it's use in production at this time.

## Runtime dependencies

* PHP >= 7.0.0
* rTorrent >= 0.9.4
* xmlrpc-c >= 1.32.05
* libtorrent >= 0.13.4

## Build dependencies

* SASS >= 3.4.0
* npm >= 2.11.0

## Coding standards & linters

All newly written and modified code should make a best effort to adhere to these standards.

* PHP - [PSR-2](http://www.php-fig.org/psr/psr-2/)
* SCSS - [scss-lint](https://github.com/brigade/scss-lint)

## Building

1. Compile the stylesheets
```
sass --scss --style compressed --update css/ --update plugins/theme/themes/
npm install
```
