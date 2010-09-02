<?php

global $wpdb;


////////////////////////////////////////////////////////////////////////////////
// print plugin option page and check post data
////////////////////////////////////////////////////////////////////////////////
function fitcash_save_plugin_options()
{
  $fitcash_num_text_vars = fitcash_get_option('fitcash_num_text_vars');

  $fitcash_spinning_header_text = array();
  for ( $i = 0; $i < 10; $i++ )
  {
    $fitcash_spinning_header_text[$i] = htmlentities($_POST['fitcash_spinning_text_header_' . $i], ENT_QUOTES);
    $fitcash_spinning_footer_text[$i] = htmlentities($_POST['fitcash_spinning_text_footer_' . $i], ENT_QUOTES);
  }

  $options = array(
//       'fitcash_import_user_id'           => $_POST['fitcash_import_user_id'],
//       'fitcash_import_feed_url'          => $_POST['fitcash_import_feed_url'],
       'fitcash_jv_profit_center_id'      => $_POST['fitcash_jv_profit_center_id'],
       'fitcash_import_cats'              => $_POST['fitcash_import_cats_select'],
       'fitcash_post_header_text'         => $_POST['fitcash_post_header_text'],
       'fitcash_post_footer_text'         => $_POST['fitcash_post_footer_text'],
       'fitcash_spinning_header_text'         => $fitcash_spinning_header_text,
       'fitcash_spinning_footer_text'         => $fitcash_spinning_footer_text,
       'fitcash_text_vars'            => $_POST['fitcash_text_vars'],
       'fitcash_num_text_vars'            => $fitcash_num_text_vars,
       'fitcash_count_post_first_import'  => intval($_POST['fitcash_count_post_first_import']),
//       'fitcash_count_post_next_imports'  => $_POST['fitcash_count_post_nect_import'],
       'fitcash_import_schedule'          => $_POST['fitcash_import_schedule'],
       'fitcash_publish_option'           => $_POST['fitcash_publish_option']
        );

  //  check if text vars is set 
  if (!isset( $_POST['fitcash_text_vars'] ))
  {
    $_POST['fitcash_text_vars'] = 'off';
    $fitcash_text_vars = 'off';
  }
  else
  {
    $_POST['fitcash_text_vars'] = 'on';
    $fitcash_text_vars = 'on';

    //  read text variables
    for ($i=0; $i < $fitcash_num_text_vars; $i++)
    {
      $values = $_POST['fitcash_text_var_' . $i . '_values'];
      $fitcash_text_variable[$i]['name']   = $_POST['fitcash_text_var_' . $i . '_name'];
      $fitcash_text_variable[$i]['values'] = htmlentities($values, ENT_QUOTES);
      $fitcash_text_variable[$i]['value']  = explode( '},{', $fitcash_text_variable[$i]['values']);
    }
    $options['fitcash_text_variable'] = $fitcash_text_variable;
  }
  $options['fitcash_text_vars'] = $fitcash_text_vars;

  fitcash_update_options($options);

  //  set cron event
  wp_clear_scheduled_hook('scheduled_import_article_hook');
  $schedule_interval = fitcash_get_option('fitcash_import_schedule');
  $no_of_days=1;
  if($schedule_interval=='weekly')
    $no_of_days=7;
  else if($schedule_interval=='monthly')
    $no_of_days=30;
        
  wp_schedule_event( time()+( $no_of_days*24*60*60), $schedule_interval, 'scheduled_import_article_hook' );

  return;
}


////////////////////////////////////////////////////////////////////////////////
// check all text var buttons if posted
////////////////////////////////////////////////////////////////////////////////
function fitcash_check_text_var_btns()
{
  $fitcash_text_variable = fitcash_get_option('fitcash_text_variable');

  // get count of text vars
  $fitcash_num_text_vars = fitcash_get_option('fitcash_num_text_vars');

  if ( $_POST['fitcash_add_text_var_btn'] )
  {
    $fitcash_num_text_vars++;
    fitcash_update_option( 'fitcash_num_text_vars', $fitcash_num_text_vars);

    return;    
  }

  // check each text var 
  for ( $i = 0; $i < $fitcash_num_text_vars; $i++ )
  {
    if ( $_POST['fitcash_delete_text_var_' . $i . '_btn'] )
    {
      // delete array field
      for ( $x = $i; $x < ($fitcash_num_text_vars - 1); $x++ )
      {
        $fitcash_text_variable[$x] = $fitcash_text_variable[$x + 1];
      }
      $fitcash_num_text_vars--;
      fitcash_update_option( 'fitcash_num_text_vars', $fitcash_num_text_vars);
      fitcash_update_option( 'fitcash_text_variable', $fitcash_text_variable);

      return;
    }
  }

  return;
}


////////////////////////////////////////////////////////////////////////////////
// add category
////////////////////////////////////////////////////////////////////////////////
function fitcash_add_category()
{
  fitcash_save_plugin_options();

  if ( !empty($_POST['fitcash_new_cat_name']) )
  {
    $new_cat = $_POST['fitcash_new_cat_name'];
    $cat_parent = $_POST['fitcash_parent_cat'];

    if ( $cat_parent != 0)  
      wp_create_category( $new_cat, $cat_parent ); 
    else
      wp_create_category( $new_cat); 

    echo '<div id="message" class="updated fade">';
    echo '<strong>' . __('Category added !!!', 'fitcash') . '</strong>.</div>';
  }
  else
  {
    echo '<div id="message" class="error fade">';
    echo '<strong>' . __('Category Name missing !!!', 'fitcash') . '</strong>.</div>';
  }

  return;
}



////////////////////////////////////////////////////////////////////////////////
// import articles from import_feed_url
////////////////////////////////////////////////////////////////////////////////
function fitcash_fetch_articles()
{
  $url = fitcash_get_option('fitcash_import_feed_url');

  fitcash_save_plugin_options();

  $blog_user_for_import = fitcash_get_option('fitcash_import_user_id');
  $jv_profit_center_id  = fitcash_get_option('fitcash_jv_profit_center_id');
    
  $no_of_imported_posts = fitcash_importArticles( $url, $jv_profit_center_id, $blog_user_for_import, fitcash_get_option('fitcash_count_post_first_import') );
    
  $url_display = substr( $url, strpos( $url, 'http://') + 7, (strpos( $url, '/', (strpos($url, 'http://') + 8)) - (strpos($url, 'http://') + 7)) );
  if ( $no_of_imported_posts == 0 )
  {
    echo '<div id="message" class="updated fade">';
    echo '<strong>' . __('Successfully imported from ', 'fitcash') . $url_display . __(', but didn\'t found any new posts.', 'fitcash') . '</strong></div>';
  }
  else if ( $no_of_imported_posts > 0 )
  {
    echo '<div id="message" class="updated fade">';
    echo '<strong>' . __('Successfully imported ', 'fitcash') . $no_of_imported_posts . __(' posts from ', 'fitcash') . $url_display . '.</strong></div>';
  }

  return;    
}


////////////////////////////////////////////////////////////////////////////////
// replace all variables in header and footer text phrases
////////////////////////////////////////////////////////////////////////////////
function fitcash_replace_text_vars( $text )
{
  $fitcash_text_variable   = fitcash_get_option('fitcash_text_variable');
  $fitcash_num_text_vars = fitcash_get_option('fitcash_num_text_vars');

  for ( $i=0; $i < $fitcash_num_text_vars; $i++ )  
  {
    while ( !(strpos( $text, '{' . $fitcash_text_variable[$i]['name'] . '}' ) === false) )
    {
      $replace = rand( 0, count($fitcash_text_variable[$i]['value']));
      $replace_text = $fitcash_text_variable[$i]['value'][$replace];
      $replace_text = str_replace( '{', '', $replace_text);
      $replace_text = str_replace( '}', '', $replace_text);
      $text = str_replace( '{' . $fitcash_text_variable[$i]['name'] . '}', $replace_text, $text);
    }
  }

  return $text;
}


////////////////////////////////////////////////////////////////////////////////
// import articles from import_feed_url
////////////////////////////////////////////////////////////////////////////////
function fitcash_importArticles($url,$jv_profit_center_id,$blog_user_id,$no_of_article_to_be_imported)
{
  global $wpdb;

  $no_of_imported_posts=0;

  if( trim($jv_profit_center_id) == "" )
  {
    _e('Invalid JV Profit Center affiliate ID. Please re-enter or create a valid ID at http://www.jvprofitcenter.com.', 'fitcash');
    return -1;
  }
  else if($jv_profit_center_id != "")
  {
    $url=$url."?userid=".$jv_profit_center_id;

    $rss = new fitcash_lastRSS();  
    $rss_content = $rss->Get($url);
    $items = $rss_content['items'];
    $i=0;
    $no_of_imported_posts=0;

    if($no_of_article_to_be_imported > count($items))
      $no_of_article_to_be_imported = count($items);

    //while ($i<count($items)){
    while ($i<$no_of_article_to_be_imported)
    {
      $postTitle=$items[$i]['title'];
      $postContent=$items[$i]['summary'];
        
      $postTitle=str_replace(']]&gt;','',str_replace('&lt;![CDATA[','',$postTitle));
//      $postTitle=str_replace(']]>','',str_replace('<![CDATA[','',$postTitle));
      $postContent=str_replace(']]&gt;','',str_replace('&lt;![CDATA[','',$postContent));

      if($items[$i]['is_error_msg']==1)
      {
        echo $postContent;
        return -1;
      }
        
      $postDate = $items[$i]['published'];
      $postTitle = fitcash_custom_htmlspecialchars_decode($postTitle);
      $postContent = fitcash_custom_htmlspecialchars_decode($postContent);
        
      $my_post = array();
      $my_post['post_title'] = $postTitle;

      $fitcash_spinning_header_text = fitcash_get_option('fitcash_spinning_header_text');
      $fitcash_spinning_footer_text = fitcash_get_option('fitcash_spinning_footer_text');
      $fitcash_spinning_last_number = fitcash_get_option('fitcash_spinning_last_number');
      $fitcash_text_variable        = fitcash_get_option('fitcash_text_variable');

      //  get last spinning text number
      if ( $fitcash_spinning_last_number == 9 )
        $fitcash_spinning_last_number = 0;
      else
        $fitcash_spinning_last_number++;
      $indx = $fitcash_spinning_last_number;
      fitcash_update_option( 'fitcash_spinning_last_number', $fitcash_spinning_last_number);

      //  check if spinning text is set
      if ( $fitcash_spinning_footer_text[$indx] != '' AND $fitcash_spinning_header_text[$indx] != '' )
      {
        $fitcash_header_text = $fitcash_spinning_header_text[$indx];
        $fitcash_footer_text = $fitcash_spinning_footer_text[$indx];
      }
      else
      {
        //  otherwise use default
        $fitcash_header_text = fitcash_get_option('fitcash_post_header_text');
        $fitcash_footer_text = fitcash_get_option('fitcash_post_footer_text');
      }
      //  replace variables if feature is on
      if ( fitcash_get_option('fitcash_text_vars') == 'on' )
      {
        $fitcash_header_text = fitcash_replace_text_vars( $fitcash_header_text );
        $fitcash_footer_text = fitcash_replace_text_vars( $fitcash_footer_text );
      }
      $my_post['post_content'] = '<p>' . $fitcash_header_text . '</p>' . $postContent . '<p>' . $fitcash_footer_text . '</p>';

      $my_post['post_status'] = fitcash_get_option("fitcash_publish_option");
      $my_post['post_author'] = $blog_user_id;
      $my_post['post_date'] =$postDate;
      $my_post['post_date_gmt'] = $postDate;
      $my_post['post_modified'] = $postDate;
      $my_post['post_modified_gmt'] = $postDate;
      $all_tags_for_a_post=$items[$i]['post_tags'];
      $tags_array=explode(",",$all_tags_for_a_post);
      $post_tag_id=array();

      if( !fitcash_postExists($postTitle) )
      {
                //////////////////////////////////
        remove_filter('content_save_pre', 'wp_filter_post_kses');
        remove_filter('excerpt_save_pre', 'wp_filter_post_kses');
        remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                /////////////////////////////////
        $post_id=wp_insert_post( $my_post );

        $cat_ids = fitcash_get_option('fitcash_import_cats');
//        $cat_ids=explode(",", fitcash_get_option('fitcash_import_cats'));
                    
        $tepm_post_id=$post_id;
        wp_set_post_categories($post_id, $cat_ids);
        foreach($tags_array as $a_tag)
        {
          if ( '' == $a_tag )
            continue;
          $a_slug = sanitize_term_field('slug', $a_tag, 0, 'post_tag', 'db');
          $a_tag_obj = get_term_by('slug', $a_slug, 'post_tag');
          $a_tag_id=0;
          if ( ! empty($a_tag_obj) )
            $a_tag_id = $a_tag_obj->term_id;
          if($a_tag_id==0)
          {
            $a_tag=$wpdb->escape($a_tag);
            $a_tag_id = wp_insert_term($a_tag, 'post_tag');
            if ( is_wp_error($a_tag_id) )
              continue;
            $a_tag_id = $a_tag_id['term_id'];
          }
          $post_tag_id[]=intval($a_tag_id);
        }
        wp_set_post_tags($tepm_post_id,$post_tag_id);
        $no_of_imported_posts++;
        //$mytags=array('good','bad','ugly');
        //wp_set_post_tags($tepm_post_id,$mytags);
      }
      $i++;
    }
  }

  return  $no_of_imported_posts;
}
    
function fitcash_postExists($postTitle)
{
  $retValue=false;
  global $wpdb;

  $table=$wpdb->prefix."posts";
  $result=mysql_query("select * from $table where post_title='".$postTitle."'");
    
  if(mysql_num_rows($result)>0)
    $retValue=true;
 
  return $retValue;
}
    
function fitcash_custom_htmlspecialchars_decode($str, $options="") 
{
  $trans = get_html_translation_table(HTML_SPECIALCHARS);
  //$trans = get_html_translation_table(HTML_SPECIALCHARS, $options);
  $decode = ARRAY();

  foreach ($trans AS $char=>$entity) 
  {
    $decode[$entity] = $char;
  }
  $str = strtr($str, $decode);

  return $str;
}



















?>