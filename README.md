# wordpress-digitalchalk-sso-plugin

## Disclaimer

This plugin is not officially supported by DigitalChalk. This plugin serves as an example implementation for DigitalChalk SSO through Wordpress.

## Description

This is a plugin for Wordpress that enables Single Sign-On to the DigitalChalk LMS

## Installation

Download the latest version of the plugin from [GitHub Releases](https://github.com/digitalchalk/wordpress-digitalchalk-sso-plugin/releases/latest).

Install the usual way through the plugins option in Wordpress Admin panel.  No code changes to templates are required.

After installation and plugin activation (in WordPress wp-admin), go to Settings > Plugins > DigitalChalk SSO and set the parameters for the plugin, based on information from your DigitalChalk instructor account.

## Frequently Asked Questions

##### Q: Should I choose email or username in the settings?

###### A: This setting needs to match what your DigitalChalk account uses.  If you log into DigitalChalk with an email, select email here, regardless of how you log into WordPress.  Note that if you use email, each WordPress user must have an email address in their profile (although it doesn't have to be their username).  If you are unsure if your DigitalChalk account is set to email or username, contact DigitalChalk support.


## Version 1.1.0 Release Notes

This update brings the DigitalChalk Single Sign-on plugin up to modern standards for compatibility with PHP 8 and WordPress 6.6+.

## Requirements

- **PHP**: 8.0 or higher
- **WordPress**: 5.0 or higher
- **Tested up to**: WordPress 6.6

## Major Changes

### PHP 8 Compatibility
- **Fixed constructor issues**: Removed deprecated PHP 4-style constructor
- **Updated class naming**: Changed `wpdcsso` to `WPDCSSO` for consistency
- **Improved type handling**: Fixed potential type errors and improved variable initialization
- **Modern PHP syntax**: Updated to use null coalescing operators and other PHP 8 features

### Security Improvements
- **Input sanitization**: All user inputs are now properly sanitized using WordPress functions
- **Output escaping**: All outputs are properly escaped to prevent XSS attacks
- **Password fields**: Changed sensitive input fields to use `type="password"`
- **URL validation**: Added proper URL validation for configuration fields
- **Nonce verification**: Enhanced nonce verification for form submissions

### WordPress API Updates
- **Deprecated functions removed**: 
  - Replaced `screen_icon()` (deprecated since WP 3.8)
  - Updated `_e()` to `esc_html_e()` where appropriate
  - Modernized form handling
- **HTTP API**: Replaced cURL with WordPress native `wp_remote_*` functions
- **Better accessibility**: Improved form structure with proper fieldsets and labels
- **Modern UI**: Updated admin interface to match current WordPress standards

### Code Quality Improvements
- **Error handling**: Enhanced error handling with proper logging
- **Documentation**: Added comprehensive PHPDoc comments
- **Code organization**: Better structure and separation of concerns
- **Function naming**: Improved function naming conventions

### Bug Fixes
- **Redirect handling**: Replaced `wp_redirect()` with `wp_safe_redirect()` for security
- **Array access**: Fixed undefined array key warnings
- **JSON handling**: Improved JSON parsing with proper error checking
- **Exit statements**: Replaced `die()` with `exit` for consistency

## Migration Notes

### For Developers
- The main class is now named `WPDCSSO` instead of `wpdcsso`
- All cURL operations have been replaced with WordPress HTTP API
- Input validation is now stricter - ensure your integrations handle this properly

### For Site Administrators
- No configuration changes required
- All existing settings will be preserved
- The plugin will continue to work with your existing DigitalChalk setup

## Testing

This updated plugin has been tested with:
- PHP 8.0, 8.1, 8.2, and 8.3
- WordPress 5.0 through 6.6
- Various hosting environments

## Support

If you encounter any issues after updating, please:
1. Check that your server meets the minimum requirements
2. Verify your DigitalChalk API credentials are still valid
3. Check WordPress debug logs for any error messages
4. Contact DigitalChalk support with specific error details

## Releasing a New Version

Releases are built automatically via GitHub Actions. To publish a new version:

1. Update the version number in `src/wpdcsso/wpdcsso.php` (both the plugin header `Version:` and the `WPDCSSO_VERSION_NUM` constant)
2. Commit your changes to `master`
3. Tag the commit: `git tag v1.2.0 && git push origin v1.2.0`
4. The workflow will:
   - Validate the tag matches the plugin header version
   - Build the plugin zip with the correct directory structure
   - Create a GitHub Release with the zip attached
   - Update `update/latestversion` so existing installs see the new version

### How WordPress Update Detection Works

The file `update/latestversion` is a JSON manifest that the plugin's built-in updater (`WPDCSSO_Updater`) fetches from this repo to check for new versions. WordPress doesn't know about GitHub Releases natively — this manifest is the bridge. It contains the latest version number and the download URL pointing to the GitHub Release asset.

The GitHub Actions workflow automatically updates this file on each release, so no manual editing is needed. Do not remove the `update/` directory — without it, existing installations won't detect new versions.

The `releases/` directory is no longer used. Previous releases remain for historical reference.

## Changelog

### Version 1.1.1 (2026-01-12)
- **Added** GitHub Actions release process
- **Updated** More code modernization and cleanup

### Version 1.1.0 (2025-08-14)
- **Added**: PHP 8 compatibility
- **Added**: WordPress 6.6 compatibility
- **Added**: Enhanced security measures
- **Updated**: Modern WordPress APIs
- **Updated**: Improved accessibility
- **Fixed**: All deprecated function calls
- **Fixed**: Input sanitization and output escaping
- **Fixed**: Error handling and logging
- **Changed**: HTTP requests now use WordPress native functions
- **Changed**: Improved admin interface

#### 1.0.6
Added default API url button to update current value with default. Set the URL as the default if there is no url set (initial installation)

#### 1.0.5
Fixed ability to pass in Sandbox or Production URLs

#### 1.0.4
Updated to support more metadata fields and PHP7

#### 1.0.2
Stable auto updating release

#### 1.0.0
First public release with github updater.

### Other Info
Contributors: bobrob,ttolle,hdhensley

Tested up to: WordPress 6.6

Stable tag: 1.1.1

Tags: DigitalChalk,SSO

License: GPLv2 or later

License URI: http://www.gnu.org/licenses/gpl-2.0.html
