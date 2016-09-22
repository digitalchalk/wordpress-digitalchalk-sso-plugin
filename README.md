#wordpress-digitalchalk-sso-plugin

#DISCLAIMER

This plugin is provided AS-IS.  Due to differences and updates to the WooCommerce system, this plugin is not officially supported by DigitalChalk, and should be used only as an example of integration.  Any changes required to this plugin to make it work with your particular installation is your responsibility.  We encourage you to fork this project and modify it to work with your own system.

#Description

This is a plugin for Wordpress that enables Single Sign-On to the DigitalChalk LMS

#Installation

Download the latest version of the plugin from the releases directory.  The current version is [wpdcsso-1.0.3.zip](https://github.com/digitalchalk/wordpress-digitalchalk-sso-plugin/raw/master/releases/wpdcsso.1.0.3.zip).

Install the usual way through the plugins option in Wordpress Admin panel.  No code changes to templates are required.

After installation and plugin activation (in WordPress wp-admin), go to Settings > Plugins > DigitalChalk SSO and set the parameters for the plugin, based on information from your DigitalChalk instructor account.

#Frequently Asked Questions
#####Q: Should I choose email or username in the settings?
######A: This setting needs to match what your DigitalChalk account uses.  If you log into DigitalChalk with an email, select email here, regardless of how you log into WordPress.  Note that if you use email, each WordPress user must have an email address in their profile (although it doesn't have to be their username).  If you are unsure if your DigitalChalk account is set to email or username, contact DigitalChalk support.


###Changelog
####1.0.2
Stable auto updating release

####1.0
First public release with github updater.

###Other Info
Contributors: bobrob,ttolle

Requires at least: 3.0.1

Tested up to: WordPress 3.9

Stable tag: 1.0.3

Tags: DigitalChalk,SSO

License: GPLv2 or later

License URI: http://www.gnu.org/licenses/gpl-2.0.html
