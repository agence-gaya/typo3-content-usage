# ext:content-usage

This TYPO3 extension analyse database to generate a report of content usage:

- Doktypes
- Ctypes

## Installation

composer require gaya/typo3-content-usage

## Content analysis

### Doktypes

List all doktypes declared on the TYPO3 instance and list all pages (actives, disabled, deleted) which are using these doktypes.

### CType

List all ctypes declared on the TYPO3 instance and list all contents (actives, disabled, deleted) which are using these CTypes.

## Why

This content reporting has been designed for maintainers:

- to quickly find where a content type is used
- to help in a code cleanup phase by identifying unused content types
