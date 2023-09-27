# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.7.1] - 27-09-2023

### Changed

- Fixed crashes when media hub tries to optimize non-existant files

## [1.7.0] - 27-09-2023

### Added

- Added new MediaManipulator class that allows better customization of image optimization/conversions
- Added debounce to search field to reduce queries
- Added loader to search

### Changed

- Made collections case-insensitive
- Fixed some UI problems with latest Nova
- Removed Media from global search

## [1.6.2] - 03-07-2023

### Changed

- Fixed styling issues with latest Nova versions
- Fixed MediaCast not working in Nova views
- Fixed missing translations (thanks to [@RibesAlexandre](https://github.com/RibesAlexandre))

## [1.6.1] - 20-03-2023

### Added

- Allow moving media items between collections in bulk (thanks to [@ndrez-outl1ne](https://github.com/ndrez-outl1ne))

### Changed

- Updated packages

## [1.6.0] - 27-02-2023

### Added

- Added missing translation keys (thanks to [@mrleblanc101](https://github.com/mrleblanc101))

### Changed

- Tons of UI changes to better fit Nova 4 (thanks to [@mrleblanc101](https://github.com/mrleblanc101))

## [1.5.5] - 09-02-2023

### Added

- Added custom MediaCast casting option (thanks to [@nullthoughts](https://github.com/nullthoughts))

### Changed

- Fixed DropZone not working on latest version of Nova
- Updated Italian translations (thanks to [@trippo](https://github.com/trippo))

## [1.5.4] - 31-01-2023

### Changed

- Fix post-processing cleanup not deleting temporary copies

## [1.5.3] - 30-01-2023

### Changed

- Fixed issue where all the conversions were performed on the same local file cumulatively
- Improved collections endpoints (thanks to [@robindrost](https://github.com/robindrost))
- Italian translations fixes (thanks to [@trippo](https://github.com/trippo))
- Added `getUrl(forConversion:)` helper function to Media
- Allow original image optimizations to be disabled on a per-collection basis

## [1.5.2] - 23-01-2023

### Changed

- Fixed some cases where the original file was not deleted from /tmp folder

## [1.5.1] - 14-12-2022

### Changed

- Fixed unexpected InvalidManipulationError when no format was specified

## [1.5.0] - 12-12-2022

NB! Breaking change - file names are no longer automagically encoded. This should fix default adapters, but might cause issues with third-party adapters or if you use Thumbor.

### Added

- Added `deleteOriginal(true/false)` option to FileHandler
- Added `format` option to conversions config

### Changed

- Fixed file naming issue with remote files (thanks to [@michalsrodek](https://github.com/michalsrodek))
- File names are no longer double encoded with default drivers (thanks to [@SteJaySulli](https://github.com/SteJaySulli))
- Changed search case insensitivity with LOWER() (instead of UPPER())
- Improved UploadedFile error handling and displaying
- Fixed "system" dark mode setting not always setting correct colour
- Improved media hub view responsiveness
- Updated packages

## [1.4.2] - 29-11-2022

### Fixed

- Fixed searching alt and title values when using postgres database.

## [1.4.1] - 09-11-2022

### Added

- Added drag-and-drop support to MediaHub tool view

## [1.4.0] - 09-11-2022

### Added

- Added FileValidator support (see new config option `file_validator`)
- Added mime type validation (thanks to [@murdercode](https://github.com/murdercode))

### Changed

- Improved toasts on media upload
- Updated packages

## [1.3.1] - 08-11-2022

### Changed

- Allow multiple files quick upload (thanks to [@ndrez-outl1ne](https://github.com/ndrez-outl1ne))

## [1.3.0] - 02-11-2022

### Added

- Added `withCustomFields()` to MediaHub Tool which allows adding custom data fields for media items
- Added quick upload to "choose media" modal (thanks to [@ndrez-outl1ne](https://github.com/ndrez-outl1ne))
- Added media ID to MediaItem component (thanks to [@murdercode](https://github.com/murdercode))
- Added Italian translations (thanks to [@murdercode](https://github.com/murdercode))

### Changed

- Fetch more images per page (now 72 per page, instead of 18) (thanks to [@ndrez-outl1ne](https://github.com/ndrez-outl1ne))
- Choose media modal is now full-screen and the media items list is scrollable (thanks to [@ndrez-outl1ne](https://github.com/ndrez-outl1ne))
- Selected media items now scroll horizontally instead of wrapping (thanks to [@ndrez-outl1ne](https://github.com/ndrez-outl1ne))
- Allow searching items by their data fields (thanks to [@murdercode](https://github.com/murdercode))
- Clicking on a MediaItem component's file name now opens view/edit modal for quick access
- Misc UI fixes (reduced media items size in some places)
- Updated packages

## [1.2.8] - 21-10-2022

### Added

- Added option to configure image conversion driver
- Added hideFromMenu() to MediaHub Tool

### Changed

- Report file upload errors via Laravel's report() helper

## [1.2.7] - 13-10-2022

### Changed

- Fixed sink not working

## [1.2.6] - 13-10-2022

### Changed

- Refactored RemoteFile to use Http instead of `file_get_contents` to improve compatibility with different servers and redirects
- Refactored RemoteFile disk to disk copying to use streams

## [1.2.5] - 04-10-2022

### Changed

- Fixed file names being saved as url encoded

## [1.2.4] - 26-09-2022

### Changed

- Fixed media url returning encoded `/` inside paths.

## [1.2.3] - 26-09-2022

### Changed

- Fixed file name sanitizer not handling extensionless names

## [1.2.2] - 26-09-2022

### Changed

- Fixed default queue handling

## [1.2.1] - 21-09-2022

### Changed

- Improved filtering functionality
- Changed english translations

## [1.2.0] - 21-09-2022

### Added

- FileHandler now encodes file names.
  - This is backwards compatible, since we will avoid double encoding.
  - New files will now be urlencoded using php's `urlencode`
- `OrderBy` and `Search` filters

## [1.1.6] - 19-09-2022

### Fixed

- Fixes wrong modal width issue.
  - Version `4.14.0` of laravel/nova introduced a new default width for modals that broke our modals.

## [1.1.5] - 12-09-2022

### Changed

- Fixed compatibility with nova-translatable

## [1.1.4] - 06-09-2022

### Changed

- Append array values to formData with index
  - Fixes an issue when using alongside other packages that don't respect `data[]` key, but instead want `data[0...n]`

## [1.1.3] - 06-09-2022

### Changed

- Fixed issue with "Move to collection" confirmation modal getting stuck on loading state
- Improved deduplication handling in the UI
- Updated packages

## [1.1.2] - 25-08-2022

### Changed

- Made optimize and convert jobs into a single job to decease costs and increase performance when using cloud storage

## [1.1.1] - 25-08-2022

### Added

- Added DatePathMaker as an optional configuration option

## [1.1.0] - 25-08-2022

### Added

- Added file deduplication via original file hash
- Added file name to MediaItem component

### Changed

- Fixed cloud storage support for file processing
- Fixed cloud storage directory count calculation
- Filesystem refactoring and misc improvements
- Fixed missing `formatForNova` data
- Fixed data fields UI in media view modal

## [1.0.7] - 23-08-2022

### Changed

- Allow FileHelpers::getFileHash() to read file from specified disk

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
