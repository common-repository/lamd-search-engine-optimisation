=== Plugin Name ===
Contributors: lamd
Tags: seo
Requires at least: 2.9
Tested up to: 3.2.1
Stable tag: trunk

LAMD SEO adds Search Engine Optimisation meta boxes for title, keywords and description onto posts, pages and public custom post types.

== Description ==

LAMD SEO adds Search Engine Optimisation meta boxes for title, keywords and description onto posts, pages and public custom post types. This plugin is very simple to use, and certainly less cumbersome than some. There are 3 simple settings which are default title, default keywords and default description.

If no default title is used the plugin falls back to a standard Wordpress title if no title is given for a page.

For more information visit [http://www.lamarketinganddesign.co.uk/wordpress-plugins](http://www.lamarketinganddesign.co.uk/wordpress-plugins "LA Marketing & Design")

== Installation ==

**Recommended usage**

1. Copy the lamd-seo folder to your /wp-content/plugins/ folder

2. Activate LAMD SEO on your plugin-page.

3. Replace the title tag in header.php with `<title><?php this_theme_title(); ?></title>`

4. Add `<?php this_theme_keywords(); ?>` after the title tag in header.php

5. Add `<?php this_theme_description(); ?>` after the title tag in header.php

6. Edit functions.php and insert the following code
`
function this_theme_title(){
	if (function_exists('lamd_seo_title')){
		lamd_seo_title();
	}else{
		wp_title('&laquo;',true,'right');
		if (!is_front_page()){
			echo " ";
		}
		bloginfo('name');
		if (get_option("description")!=""){
			echo " : ";
			bloginfo('description');
		}
	}
}
function this_theme_keywords(){
	if (function_exists('lamd_seo_keywords')){
		lamd_seo_keywords();
	}
}
function this_theme_description(){
	if (function_exists('lamd_seo_description')){
		lamd_seo_description();
	}
}
`

**Alternate usage**

1. Copy the lamd-seo folder to your /wp-content/plugins/ folder

2. Activate LAMD SEO on your plugin-page.

3. Replace the title tag in header.php with `<title><?php lamd_seo_title(); ?></title>`

4. Add `<?php lamd_seo_keywords(); ?>` after the title tag in header.php

5. Add `<?php lamd_seo_description(); ?>` after the title tag in header.php
