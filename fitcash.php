<?php
/*
Plugin Name: FitCash 
Plugin URI: http://jonbensonfitness.com/wp-plugin
Description: Import posts/articles from Jon Benson Fitness&copy; Host Blog to your blog via last rss feed. WP Cron settings for automatical import in regular intervals.
Version: 1.1.1
Author: John Benson
Author URI: http://jonbensonfitness.com
License: GPL2
*/

global $wpdb;

define('FITPURL', WP_PLUGIN_URL . '/' . str_replace(basename( __FILE__),"",plugin_basename(__FILE__)) );
define('FITPDIR', WP_PLUGIN_DIR . '/' . str_replace(basename( __FILE__),"",plugin_basename(__FILE__)) );


// relative path to WP_PLUGIN_DIR where the translation files will sit:
$plugin_path = plugin_basename( dirname( __FILE__ ) .'/lang' );
load_plugin_textdomain( 'jbf_import_posts', '', $plugin_path );

include_once( dirname(__FILE__) . '/fitcash-lastRSS.php');
include_once( dirname(__FILE__) . '/fitcash-functions.php');
include_once( dirname(__FILE__) . '/fitcash-option-page.php');

register_activation_hook( __FILE__, 'fitcash_plugin_activation');   
register_deactivation_hook( __FILE__, 'fitcash_plugin_deactivation'); 

add_action( 'admin_menu', 'fitcash_plugin_add_option_page');
add_action( 'admin_head', 'fitcash_plugin_load_header_tags');
add_filter( 'cron_schedules', 'fitcash_more_reccurences');
add_action( 'scheduled_import_article_hook', 'fitcash_import_articles' );





////////////////////////////////////////////////////////////////////////////////
// plugin activation hook
////////////////////////////////////////////////////////////////////////////////
function fitcash_plugin_activation() 
{
  //  check if default cat exists
  if ( !get_cat_ID('ImportFit') ) 
  {
    wp_create_category( 'ImportFit' );
  }

  add_option( 'fitcash_import_posts', array(), '', 'no');
  fitcash_set_option_defaults();

  fitcash_migrate_old_options();

  return; 
}

////////////////////////////////////////////////////////////////////////////////
// plugin deactivation hook
////////////////////////////////////////////////////////////////////////////////
function fitcash_plugin_deactivation() 
{
  wp_clear_scheduled_hook('scheduled_import_article_hook');

  delete_option('fitcash_import_posts');
}

////////////////////////////////////////////////////////////////////////////////
// add plugin option page
////////////////////////////////////////////////////////////////////////////////
function fitcash_plugin_add_option_page()
{
  add_menu_page( 'Fitcash', 'Fitcash', 5, __FILE__, 'fitcash_plugin_create_option_page');
  add_submenu_page(__FILE__, 'About', 'About', 5, 'sub-page', 'display_fit365Online_plugin_about');
  add_submenu_page(__FILE__, 'JV Profit Center', 'JV Profit Center', 5, 'sub-page2', 'javascript_to_redirect_to_jvprofitcenter');
//  add_options_page('JBF Import Posts', 'Import fit365Online', 8, __FILE__, 'jbf_create_option_page');
}

////////////////////////////////////////////////////////////////////////////////
// load plugin wp-admin css and js
////////////////////////////////////////////////////////////////////////////////
function fitcash_plugin_load_header_tags()
{
  $js_number_categories = get_categories(array('hide_empty' => false));
  $js_category_count=0;
  foreach ($js_number_categories as $category) 
    $js_category_count++;

  echo 	"\n\n";
  echo 	'<!-- Fitcash Import Posts - Plugin Option CSS -->' . "\n";
  echo 	'<link rel="stylesheet" type="text/css" media="all" href="' . FITPURL . 'css/plugin-option.css" />';
/*

  $data = get_plugin_data(__FILE__);
//  wp_enqueue_script( 'get_output', plugin_dir_url( __FILE__ ) . 'js/ajax/get_output.js', array( 'jquery', 'json2' ), "1.0.30", true );
  wp_enqueue_script( 'jbf_script', plugin_dir_url( __FILE__ ) . 'js/jbf_import_posts.js', array('jquery'), false, false);
  wp_register_script( 'jbf_script', plugin_dir_url( __FILE__ ) . 'js/jbf_import_posts.js', array('jquery'), false, false);
  wp_register_style( 'jbf_style', JBFPURL . 'css/plugin-option.css', false, false);
//  wp_register_script( 'jbf_script', JBFPURL . 'js/jbf_import_posts.js', array('jquery'), $data['Version']);
//  wp_register_style( 'jbf_style', JBFPURL . 'css/plugin-option.css', array(), $data['Version']);
  wp_enqueue_script('jbf_script');
  wp_enqueue_style( 'jbf_style');
*/
	
  return;
}


////////////////////////////////////////////////////////////////////////////////
// plugin options functions
////////////////////////////////////////////////////////////////////////////////
function fitcash_get_option($field) 
{
  if (!$options = wp_cache_get('fitcash_import_posts')) 
  {
    $options = get_option('fitcash_import_posts');
    wp_cache_set('fitcash_import_posts',$options);
  }
  return $options[$field];
}

function fitcash_update_option($field, $value) 
{
  fitcash_update_options(array($field => $value));
}

function fitcash_update_options($data) 
{
  $options = array_merge(get_option('fitcash_import_posts'),$data);
  update_option('fitcash_import_posts',$options);
  wp_cache_set('fitcash_import_posts',$options);
}

function fitcash_migrate_old_options() 
{
  global $wpdb;

  //  check for a old Option
  if (get_option('fit365online_import_schedule') === false) 
  {
    return;
  }

  $old_fields = array(
       '0' => 'blog_user_for_import',
       '1' => 'fit365noline_feed_url',
       '2' => 'jv_profit_center_id',
       '3' => 'import_from_feed365Online_under_this_category',
       '4' => 'disclaimer_prefix_for_fit365_online',
       '11' => 'fit365online_number_of_article_for_first_import',
       '12' => 'fit365online_number_of_article_for_subsequent_import',
       '13' => 'fit365online_import_schedule',
       '14' => 'fit365online_import_as_option'
       );

  $new_fields = array(
       '0' => 'fitcash_import_user_id',
       '1' => 'fitcash_import_feed_url',
       '2' => 'fitcash_jv_profit_center_id',
       '3' => 'fitcash_import_cats',
       '4' => 'fitcash_post_header_text',
       '5' => 'fitcash_post_footer_text',
       '6' => 'fitcash_spinning_header_text',
       '7' => 'fitcash_spinning_footer_text',
       '8' => 'fitcash_spinning_last_number',
       '9' => 'fitcash_text_vars',
       '10' => 'fitcash_text_variable',
       '11' => 'fitcash_count_post_first_import',
       '12' => 'fitcash_count_post_next_imports',
       '13' => 'fitcash_import_schedule',
       '14' => 'fitcash_publish_option'
       );

  foreach($old_fields as $index=>$field) 
  {
    if ( $index == 3 )
    {
      $cats = get_option($old_fields[$index]);
      if ( is_array($cats) )
        fitcash_update_option($new_fields[$index], $cats);
      else
        fitcash_update_option($new_fields[$index], array($cats));
    }
    else
      fitcash_update_option($new_fields[$index], get_option($old_fields[$index]));
    delete_option($old_fields[$index]);
  }
  $wpdb->query("OPTIMIZE TABLE `" . $wpdb->options . "`");

  return;
}

function fitcash_set_option_defaults()
{
  $current_user_id=1;
  global $current_user;    
  get_currentuserinfo();

  if ( $current_user->ID != '' ) 
    $current_user_id=$current_user->ID;

  $importfit_cat = intval(get_cat_ID('ImportFit'));
 
  $default_options = array(
       'fitcash_import_user_id'           => $current_user_id,
       'fitcash_import_feed_url'          => 'http://fit365online.com/rss_aff_for_jvpc.php',
       'fitcash_jv_profit_center_id'      => '',
       'fitcash_import_cats'              => array($importfit_cat),
       'fitcash_post_header_text'         => '[ Note: This article was written by fitness and nutrition author Jon Benson. I have his permission to share it with you. ]',
       'fitcash_post_footer_text'         => '[ Thank you for reading. If you are intrested in more informations please contact us or subscribe to our blog feed and newsletter. ]',
       'fitcash_spinning_header_text' => array(),
       'fitcash_spinning_footer_text' => array(),
       'fitcash_spinning_last_number' => 0,
       'fitcash_text_vars'            => 'off',
       'fitcash_text_variable'        => array(),
       'fitcash_count_post_first_import'  => 1,
       'fitcash_count_post_next_imports'  => 1,
       'fitcash_import_schedule'          => 'Daily',
       'fitcash_publish_option'           => 'publish'
        );

  $fitcash_options = get_option('fitcash_import_posts');

  foreach ($default_options as $def_option => $value )
  {
    if ( !$fitcash_options[$def_option] )
    {
      fitcash_update_option( $def_option, $value );
    }
  }

  return;
}




////////////////////////////////////////////////////////////////////////////////
// print plugin option page and check post data
////////////////////////////////////////////////////////////////////////////////
function fitcash_plugin_create_option_page()
{
  if ( $_POST['fitcash_add_cat_btn'] )
  {
    fitcash_add_category();
  }

  if ( $_POST['fitcash_update_options_btn'] )
  {
    fitcash_save_plugin_options();

    echo '<div id="message" class="updated fade">';
    echo '<strong>Plugin Settings saved !!!</strong>.</div>';
  }

  if ( $_POST['fitcash_import_btn'] )
  {
    fitcash_fetch_articles();
  }

  fitcash_plugin_print_option_page();

  return;
}



function fitcash_is_min_wp($version) 
{
  return version_compare( $GLOBALS['wp_version'], $version. 'alpha', '>=');
}



function fitcash_display_fit365Online_plugin_about()
{
?>
<script language="javascript" type="text/javascript">
window.open('http://www.jvprofitcenter.com/blog/?p=137', '_blank', 'toolbar=0,location=0,menubar=0');
</script>
<?php
}

function fitcash_javascript_to_redirect_to_jvprofitcenter()
{
?>
<script language="javascript" type="text/javascript">
window.open('http://www.jvprofitcenter.com/', '_blank', 'toolbar=0,location=0,menubar=0');
</script>
<?php
}

function fitcash_import_articles()
{
  $blog_user_for_import= fitcash_get_option('fitcash_import_user_id');
  fitcash_importArticles( fitcash_get_option('fitcash_import_feed_url'), fitcash_get_option('fitcash_jv_profit_center_id'), $blog_user_for_import, fitcash_get_option('fitcash_count_post_first_import'));
}

function fitcash_more_reccurences() 
{
    return array(
        'weekly' => array('interval' => 604800, 'display' => 'Once Weekly'),
        'monthly' => array('interval' => 2592000, 'display' => 'Once Monthly'),
        );
}


?>