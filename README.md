[![TYPO3 14](https://img.shields.io/badge/TYPO3-14-orange.svg?style=flat-square)](https://get.typo3.org/version/14)
[![License](https://poser.pugx.org/gaya/typo3-content-usage/license)](https://packagist.org/packages/gaya/typo3-content-usage)

# ext:content-usage

This TYPO3 extension analyzes the database to generate a report of content usage:

- Doktypes
- Ctypes

## Installation

```sh
composer require gaya/typo3-content-usage
```

## Content analysis

### Doktypes

List all doktypes declared on the TYPO3 instance and list all pages (actives, disabled, deleted) which are using these doktypes.

### CType

List all ctypes declared on the TYPO3 instance and list all contents (actives, disabled, deleted) which are using these CTypes.

## Why

This content reporting has been designed for maintainers:

- to quickly find where a content type is used
- to help in a code cleanup phase by identifying unused content types

### Help & Support

* Issues: [https://github.com/agence-gaya/typo3-content-usage/issues](https://github.com/agence-gaya/typo3-content-usage/issues)
