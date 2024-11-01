=== Template Dictionary ===
Contributors: radovank
Tags: template, dictionary, variables, settings, options
Requires at least: 4.5
Tested up to: 5.5.3
Stable tag: 1.6.1
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A plugin for developers which provides template variables dictionary editable in backend.

== Description ==

Template Dictionary is a plugin which can be used to create template variables editable by admins.

The plugin is multilingual ready. It includes Polylang integration, another multilingual plugin can be integrated with filters.

= Admin pages =
There are few admin pages for managing the plugin.

* **Template Dictionary** – view, edit and delete the template variables values
* **Settings list** – view, edit and delete template variables settings
* **Add setting** – add new or edit template variables setting
* **Edit values** – edit a value, this page is accessable from the page **Template Dictionary**
* **Export/Import** – export settings and values to xml or import it from xml

= Usage in template =
You can use a value in template by calling function `get` of Template Dictionary object, which can be accessed by function `TmplDict()`.

For example, if you set a setting with code *the_code*, you can use it's value by:

`TmplDict()->get( 'the_code' );`

This function returns the value. If you need to echo the value, you can use function `eget`. Both functions have an optional argument `$default`, which is the default returned/echoed value, if the admin value is empty.

You can also get the value by accessing the code as property:

`$value = TmplDict()->the_code;`

= Usage with shortcode =
You can also use a shortcode `[tmpl_dict]` with attributes `code`, `default` and `do_shortcode`. The last attribute says, if the function `do_shortcode` will be called on the value.

= Setup template language =
Default template language can be set by setting the constant `TMPL_DICT_DEFAULT_LANG` in wp-config. Default value of this constant consists of two first characters of the current WP locale. **It is important to set the default language constant properly if you plan to create your site multilingual in future.**

**If you have a multilingual site**, you need to set current template language with the filter `template_dictionary_language`. For users of Polylang, it is already integrated in this plugin, the current template language is the Polylang current language slug.

= Setup available languages =
**If you have a multilingual site**, you also need to set available languages with the filter `template_dictionary_languages`. Again, if Polylang is activated, these available languages consist of Polylang languages slugs.

If your site is not multilingual, there is only one available language – the default language.

= Use JS object =
To generate JavaScript dictionary object, you need to define `TMPL_DICT_JS_VAR_NAME` constant. Add to `wp_config.php`:

`define( 'TMPL_DICT_JS_VAR_NAME', 'dict' );`

Then you can use it in JS:

`$('#some-element').text( dict.the_code );`

== Screenshots ==

1. Setting up a variable
2. Filling out values
3. Values list
4. Various field types
5. Export/Import page
6. Using values in template

== Changelog ==
= 1.6.1 =
* Fix: When exporting both settings and values, export empty settings too.
* Fix: Correct number of placeholders for wpdb::prepare.

= 1.6 =
* Added method to get whole dictionary array.
* Added JS dictionary object.

= 1.5 =
* Added term field type.
* Add admin info notice if `TMPL_DICT_DEFAULT_LANG` constant is not set.

= 1.4 =
* Fix: Page for editing settings was not showing.
* Show admin submenu opened on Edit Values page.

= 1.3 =
* Fix: screen option per_page on settings list
* Enabled caching of values
* Set user locale as language in admin if it is in available languages list.
* Refactored admin pages

= 1.02 =
* Added post-multiple field type.
* Added method __get for accessing dictionary values as properties.

= 1.01 =
* Fix: notice after deleting setting
* Do not show empty field options in settings list.
