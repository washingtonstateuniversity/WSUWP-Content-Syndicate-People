# WSUWP Content Syndicate People

[![Build Status](https://travis-ci.org/washingtonstateuniversity/WSUWP-Content-Syndicate-People.svg?branch=master)](https://travis-ci.org/washingtonstateuniversity/WSUWP-Content-Syndicate-People)

A WordPress plugin to retrieve and display people from people.wsu.edu.

## Shortcode attributes

This plugin adds the following attributes to shortcodes registered with or extended from WSUWP Content Syndicate:

* `classification` - Classification slug to retrieve people by.
* `display_fields` - A comma-separated list of fields to display for each profile. Defaults to `photo,name,title,office,email`. Additional fields include `degree`, `address`, `phone`, and `website`.
* `filters` - A comma-separated list of filtering options. Allowed values include `search`, `location`, `organization`, `classification`, `tag`, and `category`. The `category` value combines terms from university categories and site categories. Labels for each filter option can be adjusted using the attributes below:
  * `category_filter_label` - Defaults to `Filter by category`.
  * `classification_filter_label` - Defaults to `Filter by classification`.
  * `location_filter_label` - Defaults to `Filter by location`.
  * `organization_filter_label` - Defaults to `Filter by organization`.
  * `search_filter_label` - Defaults to `Type to search`.
  * `tag_filter_label` - Defaults to `Filter by tag`.
* `photo_size` - Specify the size of the photo to display for each profile. Defaults to `thumbnail`. Other allowed values include `medium` and `large`.
* `link` - Link a person's name and photo to their full profile. Works only when the `host` attribute is being used.
* `website_link_text` - Link text to display for the `website` field when it is being output. Defaults to `Website`.
* `nid` - Displays an individual person associated with the given WSU Network ID.
