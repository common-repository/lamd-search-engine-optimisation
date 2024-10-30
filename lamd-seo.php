<?php
/*
Plugin Name: LAMD SEO
Description: LAMD SEO adds Search Engine Optimisation meta boxes for title, keywords and description onto posts, pages and public custom post types.
Version: 1.0.0
Author: lamd
Author URI: http://www.lamarketinganddesign.co.uk
*/

/* Meta Box */
add_action('add_meta_boxes','lamd_seo_add_meta_boxes',20);
function lamd_seo_add_meta_boxes(){
	if (current_user_can('publish_posts')){
		generate_lamd_seo_meta_box();
	}
}
add_action('init','lamd_seo_init');
function lamd_seo_init(){
	if (current_user_can('publish_posts')){
		add_action('save_post','verify_lamd_seo_meta_box');
	}
}
function lamd_seo_post_types(){
	$rtn=array(
		"post"=>"post",
		"page"=>"page",
	);
	$rtn_custom=get_post_types(array('_builtin'=>false,'publicly_queryable'=>true),'names');
	$rtn_custom=array_merge($rtn,$rtn_custom);
	return apply_filters('lamd-seo-post-types',$rtn_custom);
}
function generate_lamd_seo_meta_box(){
	$seo_parts=lamd_seo_post_types();
	foreach ($seo_parts as $seo_part){
		add_meta_box('lamd_seo_metabox', __('Search Engine Optimisation','lamd-seo'),'lamd_seo_meta_box',$seo_part,'normal','high');
	}
}
function lamd_seo_meta_box($meta_data){
	$seo_meta_title=get_post_meta($meta_data->ID,'_lamd_seo_title',true);
	$seo_meta_keywords=get_post_meta($meta_data->ID,'_lamd_seo_keywords',true);
	$seo_meta_description=get_post_meta($meta_data->ID,'_lamd_seo_description',true);
	wp_nonce_field(plugin_basename(__FILE__),'seo_meta_nonce');
	echo "<p>Leave the field empty to use the default value.</p>\n";
	echo "<p><label><strong>Secondary Page Title</strong></label><br /><input type=\"text\" id=\"seo_meta_title\" name=\"seo_meta_title\" value=\"".$seo_meta_title."\" style=\"width:95%;\" /></p>\n";
	echo "<p><label><strong>Keywords</strong></label><br /><input type=\"text\" id=\"seo_meta_keywords\" name=\"seo_meta_keywords\" value=\"".$seo_meta_keywords."\" style=\"width:95%;\" /></p>\n";
	echo "<p>Separate keyword terms with commas.</p>\n";
	echo "<p><label><strong>Description</strong></label><br /><textarea id=\"seo_meta_description\" name=\"seo_meta_description\" style=\"width:95%;\" />".$seo_meta_description."</textarea></p>\n";
	echo "<p>Carriage returns will be removed.</p>\n";
}
function verify_lamd_seo_meta_box($meta_data){
	if (!wp_verify_nonce($_POST['seo_meta_nonce'],plugin_basename(__FILE__))){
		return $meta_data;
	}
	if (defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE){
		return $meta_data;
	}
	$seo_parts=lamd_seo_post_types();
	if ((!isset($seo_parts[$_POST['post_type']]))||(!current_user_can('edit_page',$meta_data))){
		return $meta_data;
	}
	$seo_meta_title=trim($_POST["seo_meta_title"]);
	$seo_meta_keywords=trim($_POST["seo_meta_keywords"]);
	$seo_meta_description=str_replace(array("\r","\n"),array(" "," "),trim($_POST["seo_meta_description"]));
	$seo_keywords=explode(',',$seo_meta_keywords);
	$seo_meta_keywords='';
	$seo_meta_sep='';
	foreach($seo_keywords as $seo_key=>$seo_keyword){
		if (trim($seo_keyword)!=''){
			$seo_meta_keywords.=$seo_meta_sep.trim($seo_keyword);
			$seo_meta_sep=', ';
		}
	}
	while (strpos($seo_meta_description,'  ')!==false){
		$seo_meta_description=str_replace('  ',' ',$seo_meta_description);
	}
	update_post_meta($meta_data,"_lamd_seo_title",$seo_meta_title);
	update_post_meta($meta_data,"_lamd_seo_keywords",$seo_meta_keywords);
	update_post_meta($meta_data,"_lamd_seo_description",$seo_meta_description);
	return $meta_data;
}

/* Admin Options */
add_action('admin_menu','lamd_seo_admin_menu');
function lamd_seo_admin_menu(){
	add_options_page('LAMD SEO Options','LAMD SEO','manage_options','lamd-seo-options','lamd_seo_admin_options');
	add_action('admin_init','lamd_seo_register_settings');
}
function lamd_seo_register_settings(){
	register_setting('lamd-seo-settings-group','lamd_seo_default_title');
	register_setting('lamd-seo-settings-group','lamd_seo_default_keywords');
	register_setting('lamd-seo-settings-group','lamd_seo_default_description');
}
function lamd_seo_admin_options(){
	?>
    <div class="wrap">
      <h2>LAMD SEO Options</h2>
      <form method="post" action="options.php">
      <?php settings_fields('lamd-seo-settings-group'); ?>
      <table class="form-table">
        <tr valign="top">
          <th scope="row"><label for="lamd_seo_default_title">Default Title</label></th>
          <td><input type="text" class="regular-text" id="lamd_seo_default_title" name="lamd_seo_default_title" value="<?php echo get_option('lamd_seo_default_title'); ?>" /></td>
        </tr>
        <tr valign="top">
          <th scope="row"><label for="lamd_seo_default_keywords">Default Keywords</label></th>
          <td><input type="text" class="regular-text" id="lamd_seo_default_keywords" name="lamd_seo_default_keywords" value="<?php echo get_option('lamd_seo_default_keywords'); ?>" /></td>
        </tr>
        <tr valign="top">
          <th scope="row"><label for="lamd_seo_default_description">Default Description</label></th>
          <td><textarea class="large-text" id="lamd_seo_default_description" name="lamd_seo_default_description"><?php echo get_option('lamd_seo_default_description'); ?></textarea></td>
        </tr>
      </table>
      <p class="submit"><input id="submit" class="button-primary" type="submit" value="Save Changes" name="submit"></p>
      </form>
    </div>
    <?php
}

/* Title and Meta Tags */
function lamd_seo_title(){
	global $post;
	$seo_default_title=get_option('lamd_seo_default_title');
	if (is_single()||is_page()){
		$seo_meta_title=get_post_meta($post->ID,'_lamd_seo_title',true);
		if ($seo_meta_title==''){
			if ($seo_default_title==''){
				wp_title('&laquo;',true,'right');
				if (!is_front_page()){
					echo " ";
				}
				bloginfo('name');
				if (get_option("description")!=""){
					echo " : ";
					bloginfo('description');
				}
			}else{
				echo htmlentities2($seo_default_title);
			}
		}else{
			echo htmlentities2($seo_meta_title);
		}
	}else{
		if ($seo_default_title==''){
			wp_title('&laquo;',true,'right');
			if (!is_front_page()){
				echo " ";
			}
			bloginfo('name');
			if (get_option("description")!=""){
				echo " : ";
				bloginfo('description');
			}
		}else{
			echo htmlentities2($seo_default_title);
		}
	}
}
function lamd_seo_keywords(){
	global $post;
	$seo_default_keywords=get_option('lamd_seo_default_keywords');
	if (is_single()||is_page()){
		$seo_meta_keywords=get_post_meta($post->ID,'_lamd_seo_keywords',true);
		if ($seo_meta_keywords==''){
			if ($seo_default_keywords!=''){
				echo "<meta name=\"keywords\" content=\"".htmlentities2($seo_default_keywords)."\" />\n";
			}
		}else{
			echo "<meta name=\"keywords\" content=\"".htmlentities2($seo_meta_keywords)."\" />\n";
		}
	}else{
		if ($seo_default_keywords!=''){
			echo "<meta name=\"keywords\" content=\"".htmlentities2($seo_default_keywords)."\" />\n";
		}
	}
}
function lamd_seo_description(){
	global $post;
	$seo_default_description=get_option('lamd_seo_default_description');
	if (is_single()||is_page()){
		$seo_meta_description=get_post_meta($post->ID,'_lamd_seo_description',true);
		if ($seo_meta_description==''){
			if ($seo_default_description!=''){
				echo "<meta name=\"description\" content=\"".htmlentities2($seo_default_description)."\" />\n";
			}
		}else{
			echo "<meta name=\"description\" content=\"".htmlentities2($seo_meta_description)."\" />\n";
		}
	}else{
		if ($seo_default_description!=''){
			echo "<meta name=\"description\" content=\"".htmlentities2($seo_default_description)."\" />\n";
		}
	}
}
?>