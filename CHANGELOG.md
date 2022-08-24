# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.6] - 23-08-2022

### Changed

- Fixed Media model incorrect references (thanks to [@vodnicearv](https://github.com/vodnicearv))
- Mark selected media items with a checkmark instead of hiding them
- Fixed overriding of toArray breaking Nova UI when overriding configurable media model
- Fixed inertia navigation crashing after using a context menu
- Updated packages

## [1.0.5] - 17-08-2022

### Changed

- Fixed constructor not accepting a single argument

## [1.0.4] - 01-08-2022

### Added

- Added `storeMediaFromBase64()` function to MediaHub
- Added `withModelData()` function to FileHandler

## [1.0.3] - 01-08-2022

### Changed

- Fixed `nova-translatable` having a fixed version

## [1.0.2] - 19-07-2022

### Changed

- Fixed MediaItem long file name overflow
- Fixed context menu not working as expected when having multiple MediaHub fields on the same resource
- Fixed context menu positioning on scrollable pages

## [1.0.1] - 19-07-2022

### Changed

- Fixed path generation (thanks to [@godkinmo](https://github.com/godkinmo))
- Fixed hasValue and fill() functions not working with empty values (thanks to [@godkinmo](https://github.com/godkinmo))
- Fixed non-casted array attributes not showing selected media after saving
- Selected media count is now shown in the choose media modal
- Fixed conversions and empty directories not being deleted after deleting media
- Fixed file name overflow in the view media modal
- Updated packages

## [1.0.0] - 04-07-2022

Initial release.
