<?php
/*
Plugin Name: FitCash 
Plugin URI: http://jvprofitcenter.com/blog/fitcash
Description: Import posts/articles from Jon Benson Fitness&copy; Host Blog to your blog via last rss feed. WP Cron settings for automatical import in regular intervals.
Version: 1.3.3
Author: Jon Benson
Author URI: http://jonbensonfitness.com
License: GPL2
*/

global $wpdb;

define('FITPURL', WP_PLUGIN_URL . '/' . str_replace(basename( __FILE__),"",plugin_basename(__FILE__)) );
define('FITPDIR', WP_PLUGIN_DIR . '/' . str_replace(basename( __FILE__),"",plugin_basename(__FILE__)) );

// relative path to WP_PLUGIN_DIR where the translation files will sit:
$plugin_path = dirname(plugin_basename(__FILE__)) . '/lang';
load_plugin_textdomain( 'fitcash', false, $plugin_path );


if ( function_exists('load_plugin_textdomain') ) 
{
  if ( !defined('WP_PLUGIN_DIR') ) 
  {
    load_plugin_textdomain( 'fitcash', str_replace( ABSPATH, '', dirname(__FILE__)) . '/lang');
  } 
  else 
  {
    load_plugin_textdomain( 'fitcash', false, dirname(plugin_basename(__FILE__)) . '/lang');
  }
}



include_once( dirname(__FILE__) . '/fitcash-lastRSS.php');
include_once( dirname(__FILE__) . '/fitcash-functions.php');
include_once( dirname(__FILE__) . '/fitcash-option-page.php');

register_activation_hook( __FILE__, 'fitcash_plugin_activation');   
register_deactivation_hook( __FILE__, 'fitcash_plugin_deactivation'); 

add_action( 'admin_init', 'fitcash_init_method');
add_action( 'admin_menu', 'fitcash_plugin_add_option_page');
add_action( 'admin_head', 'fitcash_plugin_load_header_tags');
add_filter( 'cron_schedules', 'fitcash_more_reccurences');
add_action( 'scheduled_import_articles_custom_hook', 'fitcash_import_articles_generic' );



$arr_fitcash_host_blog_type = array(
         0 => 'script',
         1 => 'no script'
     );



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
  wp_clear_scheduled_hook('scheduled_import_articles_custom_hook');

  delete_option('fitcash_import_posts');

  return;
}

////////////////////////////////////////////////////////////////////////////////
// add plugin option page
////////////////////////////////////////////////////////////////////////////////
function fitcash_plugin_add_option_page()
{
  add_menu_page( 'Fitcash Plugin Options', 'Fitcash', 8, 'fitcash-option-page', 'fitcash_plugin_create_option_page', 'http://www.jonbensonforum.com/favicon.ico');
  add_submenu_page( 'fitcash-option-page', 'Host Blog Entries', 'Host Blogs',       8, 'fitcash-host-blog-page', 'fitcash_plugin_create_host_blog_option_page');
  add_submenu_page( 'fitcash-option-page', 'About',             'About',            5, 'fitcash-about', 'fitcash_display_fit365Online_plugin_about');
  add_submenu_page( 'fitcash-option-page', 'JV Profit Center',  'JV Profit Center', 5, 'fitcash-sub-page', 'fitcash_javascript_to_redirect_to_jvprofitcenter');
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
// plugin init method
////////////////////////////////////////////////////////////////////////////////
function fitcash_init_method()
{
  if ( get_magic_quotes_gpc() ) 
  {
    $_POST      = array_map( 'stripslashes_deep', $_POST );
    $_GET       = array_map( 'stripslashes_deep', $_GET );
    $_COOKIE    = array_map( 'stripslashes_deep', $_COOKIE );
    $_REQUEST   = array_map( 'stripslashes_deep', $_REQUEST );
  }

  wp_enqueue_script('dashboard');
  wp_enqueue_script('postbox');
  wp_enqueue_script('jquery-ui-resizable');
  wp_enqueue_script('jquery-ui-droppable');
  wp_enqueue_script('wp-ajax-response');

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
       '14' => 'fit365online_number_of_article_for_first_import',
       '15' => 'fit365online_number_of_article_for_subsequent_import',
       '16' => 'fit365online_import_schedule',
       '17' => 'fit365online_import_as_option'
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
       '10' => 'fitcash_num_text_vars',
       '11' => 'fitcash_text_variable',
       '12' => 'fitcash_host_blogs',
       '13' => 'fitcash_host_blog',
       '14' => 'fitcash_count_post_first_import',
       '15' => 'fitcash_count_post_next_imports',
       '16' => 'fitcash_import_schedule',
       '17' => 'fitcash_publish_option'
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
       'fitcash_post_footer_text'         => '[ Thank you for reading. If you are interested in more information please post a reply or subscribe to our blog feed and newsletter. ]',
       'fitcash_spinning_header_text'     => array(),
       'fitcash_spinning_footer_text'     => array(),
       'fitcash_spinning_last_number'     => 0,
       'fitcash_text_vars'                => 'on',
       'fitcash_num_text_vars'            => 26,
       'fitcash_text_variable'            => array(),
       'fitcash_host_blogs'               => array( 
                 '0' => array( 'url' => 'http://fit365online.com', 
                               'type' => 0, 
                               'script' => 'rss_aff_for_jvpc.php', 
                               'params' => array( 'userid' => $fitcash_jv_profit_center_id ) ) 
                 ),
       'fitcash_host_blog'                => 0,
       'fitcash_count_post_first_import'  => 1,
       'fitcash_count_post_next_imports'  => 1,
       'fitcash_import_schedule'          => 'daily',
       'fitcash_publish_option'           => 'publish'
        );

  //  set default header and footer vars
  $fitcash_def_header = array(
     '[ The following article was {var1} by Jon Benson, fitness and fat-loss expert. I {var2} you&#039;ll {var3} it! ]',
     '[ Want to read a {var4} fitness article? Then check this one out... it was sent to me by Jon Benson. I have his {var5} to {var6} you. Enjoy! ]',
     '[ Jon Benson writes some of the {var7} fitness stuff on the Internet, so I know you&#039;ll {var3} this recent article from him... ]',
     '[ Sometimes I read an article so great I have to {var6} all my readers. This recent post by fitness expert Jon Benson is one of those articles. See if it doesn&#039;t help you with your {var10} goals. Thanks! ]',
     '[ You learn something new every day, right? Well, today I learned something {var11} from fitness author Jon Benson. He has given me {var17} to {var6} you. Let me know what you think by commenting below! ]',
     '[ I love reading {var14} fitness articles... and this one by Jon Benson fits the bill. You really {var15} check this out... enjoy! }',
     '[ I enjoy role models and fitness inspirations like Jon Benson (and many others)... so when Jon {var16} this article to me and gave me {var17} to pass it along to you, I jumped on the chance. Read this {var18} while it&#039;s fresh on your mind! ]',
     '[ There is a {var19} of bogus weight loss and fitness information out there. That&#039;s why reading Jon Benson&#039;s articles makes sense... and his newest article (which I have his {var5} to {var6} you) really rocked my world. Check it out... ]',
     '[ This article by Jon Benson cuts through the {var22} that is floating about in cyberspace when it comes to weight loss and fitness. I {var23} you give it a read! It&#039;s worth 5 minutes... trust me! ]',
     '[ Weight loss and body transformation is never easy, but Jon Benson is an expert at making it EASIER. This is {var24} killer article on how to get it done... faster and easier... enjoy! ]'
     );

  $fitcash_def_footer = array(
     '[ I hope you {var26} today&#039;s guest editorial by {var22} DO check out his latest book(s) using the links found in this blog or the banner below. Highly recommended! ]',
     '[ Learn more about Jon and his methods by clicking on the links and/or banner in the article above {var24}... and get yourself started on a {var25} path to body transformation! ]',
     '[ Like what you read today? Then {var26} Jon Benson&#039;s latest by clicking on the links or banners in this article {var24}... ]',
     '[ More from Jon Benson in upcoming blogs... but you can get more info and some freebies by {var8} Jon&#039;s pages found in the article above or the banner below. Thanks! ]',
     '[ Want to {var9} even more fat-loss and body transformation info? Then {var13} Jon&#039;s page by clicking on the links in the article above (or in the banner below) to get started today on reshaping YOUR body! ]',
     '[ My thoughts on this article: Jon is {var27} If you want to {var9} then {var26} the pages referenced in this article. Thanks! ]',
     '[ After this I bet you {var28}... and you can by {var29} the links in this post. Trust me: Jon has some {var14} stuff that you don&#039;t want to miss! ]',
     '[ Another {var11} article by Jon Benson... and there&#039;s more: {var13} to the pages in this post now and {var30} a lot more tips on weight loss, fitness and motivation. Enjoy! ]',
     '[ Some really {var25} stuff in this post, folks. And you should visit the pages above {var24} if you want to {var31}! ]',
     '[ I cannot speak highly enough about Jon and his {var32} on fitness and weight loss. Believe me, they WORK. I hope you {var22} this post and want to see more. Here&#039;s my tip: {var26} the pages in this article today. Your body will thank you! ]'
     );

  $fitcash_def_vars = array(
     0 => array( 'name' => 'var1', 'values' => '{written},{sent to me},{given to my readers}', 'value' => array() ),
     1 => array( 'name' => 'var2', 'values' => '{trust},{hope},{bet}', 'value' => array() ),
     2 => array( 'name' => 'var3', 'values' => '{enjoy},{benefit from},{love}', 'value' => array() ),
     3 => array( 'name' => 'var4', 'values' => '{great},{killer},{eye-opening}', 'value' => array() ),
     4 => array( 'name' => 'var5', 'values' => '{permission},{okay},{thumbs-up}', 'value' => array() ),
     5 => array( 'name' => 'var6', 'values' => '{share it with},{pass it along to},{forward it to}', 'value' => array() ),
     6 => array( 'name' => 'var7', 'values' => '{coolest},{most provocative},{most entertaining}', 'value' => array() ),
     7 => array( 'name' => 'var8', 'values' => '{visiting},{going to},{taking action now and checking out}', 'value' => array() ),
     8 => array( 'name' => 'var9', 'values' => '{learn},{discover},{dive into}', 'value' => array() ),
     9 => array( 'name' => 'var10', 'values' => '{fitness},{weight loss},{motivational}', 'value' => array() ),
     10 => array( 'name' => 'var11', 'values' => '{new},{wonderful},{exciting}', 'value' => array() ),
     11 => array( 'name' => 'var12', 'values' => '{permission},{the okay},{the thumbs-up}', 'value' => array() ),
     12 => array( 'name' => 'var13', 'values' => '{shoot over},{hop over},{visit}', 'value' => array() ),
     13 => array( 'name' => 'var14', 'values' => '{provocative},{fresh},{cutting-edge}', 'value' => array() ),
     14 => array( 'name' => 'var15', 'values' => '{must},{need to},{should}', 'value' => array() ),
     15 => array( 'name' => 'var16', 'values' => '{sent},{forwarded},{shot over}', 'value' => array() ),
     16 => array( 'name' => 'var17', 'values' => '{now},{asap},{today}', 'value' => array() ),
     17 => array( 'name' => 'var18', 'values' => '{lot},{ton},{plethora}', 'value' => array() ),
     18 => array( 'name' => 'var19', 'values' => '{clutter},{nonsense},{junk}', 'value' => array() ),
     19 => array( 'name' => 'var20', 'values' => '{highly suggest},{totally recommend},{practically insist}', 'value' => array() ),
     20 => array( 'name' => 'var21', 'values' => '{yet another},{another in his long-line of},{another winning, }', 'value' => array() ),
     21 => array( 'name' => 'var22', 'values' => '{enjoyed},{got a lot out of},{got super-motivated after reading}', 'value' => array() ),
     22 => array( 'name' => 'var23', 'values' => '{Jon Benson.},{fitness guru Jon Benson.},{fat-loss expert Jon Benson.}', 'value' => array() ),
     23 => array( 'name' => 'var24', 'values' => '{today},{immediately},{now}', 'value' => array() ),
     24 => array( 'name' => 'var25', 'values' => '{solid},{fast-track},{proven}', 'value' => array() ),
     25 => array( 'name' => 'var26', 'values' => '{visit},{jump over to},{take a look at}', 'value' => array() ),
     26 => array( 'name' => 'var27', 'values' => '{right on the money.},{really on to something here.},{saying what needs to be said.},{telling it like it is.}', 'value' => array() ),
     27 => array( 'name' => 'var28', 'values' => '{want to learn more},{would love to see more},{are wanting to read more}', 'value' => array() ),
     28 => array( 'name' => 'var29', 'values' => '{clicking on},{visiting},{checking out}', 'value' => array() ),
     29 => array( 'name' => 'var30', 'values' => '{pick up},{grab},{swipe},{get (for free)}', 'value' => array() ),
     30 => array( 'name' => 'var31', 'values' => '{learn more about reshaping your body},{discover more about weight loss and body-shaping},{find out more about Jon&#039;s weight loss and fitness tips}', 'value' => array() ),
     31 => array( 'name' => 'var32', 'values' => '{unique take},{unusual slant},{unorthodox but effective tips},{from-the-hip take}', 'value' => array() )
     );

  $default_options['fitcash_spinning_header_text'] = $fitcash_def_header;
  $default_options['fitcash_spinning_footer_text'] = $fitcash_def_footer;
  $default_options['fitcash_text_variable'] = $fitcash_def_vars;

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
// print plugin host blog option page and check post data
////////////////////////////////////////////////////////////////////////////////
function fitcash_plugin_create_host_blog_option_page()
{
  if ( $_POST['fitcash_add_parameters_btn'] )
  {
    fitcash_add_parameter();
  }

  if ( $_POST['fitcash_delete_host_blog_btn'] )
  {
    fitcash_delete_host_blog();
  }

  if ( $_POST['fitcash_add_host_blog_btn'] )
  {
    fitcash_add_host_blog();
  }

  if ( $_POST['fitcash_update_options_btn'] )
  {
    fitcash_save_plugin_hb_options();

    echo '<div id="message" class="updated fade">';
    echo '<strong>Plugin Settings saved !!!</strong></div>';
  }

  fitcash_plugin_print_host_blog_option_page();

  return;
}


////////////////////////////////////////////////////////////////////////////////
// print plugin option page and check post data
////////////////////////////////////////////////////////////////////////////////
function fitcash_plugin_create_option_page()
{
  fitcash_set_option_defaults();
  fitcash_migrate_old_options();
  if ( $_POST['fitcash_add_cat_btn'] )
  {
    fitcash_add_category();
  }

  if ( $_POST['fitcash_update_options_btn'] )
  {
    fitcash_save_plugin_options();

    echo '<div id="message" class="updated fade">';
    echo '<strong>Plugin Settings saved !!!</strong></div>';
  }

  if ( $_POST['fitcash_import_btn'] )
    fitcash_fetch_articles_generic();

  fitcash_check_text_var_btns();

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

function fitcash_more_reccurences() 
{
    return array(
        'weekly' => array('interval' => 604800, 'display' => 'Once Weekly'),
        'monthly' => array('interval' => 2592000, 'display' => 'Once Monthly'),
        );
}


?>