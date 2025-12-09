# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial release
- Feedback widget with customizable position and styling
- OpenProject integration for automatic work package creation
- Screenshot support for feedback submissions
- Dark mode support
- Configurable authentication requirements
- Comprehensive configuration options

### Fixed
- Fixed widget script loading using `@vite()` instead of `asset()`
- Fixed widget visibility issues by using inline styles instead of dynamic Tailwind classes
- Fixed `openModal()` method name to `open()` in widget JavaScript
- Fixed middleware configuration to use array format instead of string
- Improved widget initialization with better error handling and debug logging

