<?php

global $arr_fitcash_host_blog_type;

function fitcash_plugin_print_option_page()
{
  global $arr_fitcash_host_blog_type;

  $options = get_option('fitcash_import_posts');

  $fitcash_import_user_id          = fitcash_get_option('fitcash_import_user_id');
  $fitcash_import_feed_url         = fitcash_get_option('fitcash_import_feed_url');
  $fitcash_jv_profit_center_id     = fitcash_get_option('fitcash_jv_profit_center_id');
  $fitcash_import_cats             = fitcash_get_option('fitcash_import_cats');
  $fitcash_post_header_text        = fitcash_get_option('fitcash_post_header_text');
  $fitcash_post_footer_text        = fitcash_get_option('fitcash_post_footer_text');
  $fitcash_spinning_header_text    = fitcash_get_option('fitcash_spinning_header_text');
  $fitcash_spinning_footer_text    = fitcash_get_option('fitcash_spinning_footer_text');
  $fitcash_count_post_first_import = fitcash_get_option('fitcash_count_post_first_import');
  $fitcash_count_post_next_imports = fitcash_get_option('fitcash_count_post_next_imports');
  $fitcash_import_schedule  = fitcash_get_option('fitcash_import_schedule');
  $fitcash_publish_option   = fitcash_get_option('fitcash_publish_option');
  $fitcash_text_vars        = fitcash_get_option('fitcash_text_vars');
  $fitcash_num_text_vars    = fitcash_get_option('fitcash_num_text_vars');
  $fitcash_text_variable    = fitcash_get_option('fitcash_text_variable');
  $fitcash_host_blogs       = fitcash_get_option('fitcash_host_blogs');
  $fitcash_host_blog        = fitcash_get_option('fitcash_host_blog');


  $arr_frequency=array(
                    'daily'   => __('Daily', 'fitcash'),
                    'weekly'  => __('Weekly', 'fitcash'),
                    'monthly' => __('Monthly', 'fitcash')
                      );

  $arr_import_as_options=array(
                    'publish' => __('Publish Immediately (Best Option)', 'fitcash'),
                    'draft'   => __('Save As Drafts', 'fitcash')
                              );

  $arr_import_count = array(
       '1' => 1,
       '2' => 2,
       '3' => 3,
       '4' => 4,
       '5' => 5,
       '10' => 10,
       '15' => 15,
       '20' => 20,
       '25' => 25
    );

  //  check if default cat exists
  if ( !get_cat_ID('ImportFit') ) 
  {
    wp_create_category( 'ImportFit' );
  }

  $categories = get_categories(array('hide_empty' => false));

  echo
  '<div class="wrap">' . "\n" .
  '  <a name="Top"></a>' . "\n" .
  '  <div class="icon32"></div>' . "\n" .
  '  <h2>FitCash - Plugin Settings</h2>' . "\n" .
  '  <hr />' . "\n" .
  '  <p>' . "\n" .
  '    <b>Welcome to Jon Benson\'s WP Tool!</b><br />' . "\n" .
  '    ' . __('This free plugin allows you to import articles from Jon Benson\'s Fitness and nutrition blog under <a class="link-extern" href="http://www.jonbensonfitness.com" target="_blank" tilte="Jon Bensons Fitness">http://www.jonbensonfitness.com</a>, complete <b>with your affiliate links</b>. This provides you with automatic content that can also earn you commissions on all of Jon Benson\'s Clickbank Products.', 'fitcash') . '<br />' . "\n" .
  '    ' . __('You can also add content from other sources - add a new Host Blog Entry and get your content in regular intervalls from there.', 'fitcash') . "\n" .
  '  </p>' . "\n" .
  '  <hr />' . "\n" .
  '  <div class="clear"></div>' . "\n" . 
  '  <form name="fitcash_import_post_form" method="post" action="">' . "\n";

  wp_nonce_field('fitcash_import_posts');

  $url = $fitcash_host_blogs[$fitcash_host_blog]['url'];
  $url = str_replace( 'http://', '', $url);
  if ( strpos( $url, '/') === false )
    $url_display = $url;
  else
    $url_display = substr( $url, 0, strpos( $url, '/'));

  echo
  '  <div class="metabox-holder has-right-sidebar" id="plugin-panel-widgets">' . "\n" .
  '    <div class="postbox-container" id="plugin-main">' . "\n" .
  '      <div class="has-sidebar-content">' . "\n" .
  '        <div class="meta-box-sortables ui-sortable" id="normal-sortables" unselectable="on">' . "\n" .
  '          <div class="postbox ui-droppable" id="fitcash-settings">' . "\n" .
  '            <div title="' . __('Zum umschalten klicken', 'fitcash') . '" class="handlediv"><br /></div>' . "\n" .
  '            <h3 class="hndle">' . __('Settings', 'fitcash') . '</h3>' . "\n" .
  '            <div class="inside">' . "\n" .
  '              <b>Import from ' . $url_display . '</b>' . "\n" .
  '              <table class="form-table">' . "\n" .
  '              <tr><th class="fitcash_option_left_part"><label for="fitcash_jv_profit_center_id">Enter your JV Profit Center Id</label></th>' . "\n" .
  '                  <td><input type="text" id="fitcash_jv_profit_center_id" name="fitcash_jv_profit_center_id" value="' . $fitcash_jv_profit_center_id . '" /></td>' . "\n" .
  '              </tr>' . "\n" .
  '              <tr><th class="fitcash_option_left_part"><label for="fitcash_post_header_text">Edit the Default Article Header</label></th>' . "\n" .
  '                  <td><b><textarea rows="3" cols="45" id="fitcash_post_header_text" name="fitcash_post_header_text">' . html_entity_decode($fitcash_post_header_text, ENT_QUOTES) . '</textarea></b></td>' . "\n" .
  '              </tr>' . "\n" .
  '              <tr><th class="fitcash_option_left_part"><label for="fitcash_post_footer_text">Edit the Default Article Footer</label></th>' . "\n" .
  '                  <td><b><textarea rows="3" cols="45" id="fitcash_post_footer_text" name="fitcash_post_footer_text">' . html_entity_decode($fitcash_post_footer_text, ENT_QUOTES) . '</textarea></b></td>' . "\n" .
  '              </tr>' . "\n" .
  '              <tr><th class="fitcash_option_left_part"><label for="">' . __('How Frequently Do You Want New Articles Imported', 'fitcash') . '</label></th>' . "\n" .
  '                  <td><ul><li>' . "\n";

  foreach( $arr_frequency as $intervall => $value)
  {
    if ( $fitcash_import_schedule == $intervall )
      $checked = ' checked="checked" ';
    else
      $checked = ' ';
    echo '      <input type="radio" class="fitcash-radio" name="fitcash_import_schedule" id="fitcash_import_schedule_' . $intervall . '" value="' . $intervall . '"' . $checked . ' />' . "\n";
    echo '      <label for="fitcash_import_schedule_' . $intervall . '">' . $value . '</label>' . "\n";
  }

  echo
  '              </li></ul></td>' . "\n" .
  '              </tr>' . "\n" .
  '              <tr><th class="fitcash_option_left_part"><label for="">' . __('Set New Articles On Import To... ', 'fitcash') . '</label></th>' . "\n" .
  '                <td><ul><li>' . "\n";

  foreach( $arr_import_as_options as $key=>$value )
  {
    if( $fitcash_publish_option == $key )
      $checked = ' checked="checked" ';
    else
      $checked = ' ';
    echo '       <input type="radio" class="fitcash-radio" name="fitcash_publish_option" id="fitcash_publish_option_' . $key . '" value="' . $key . '"' . $checked . ' />' . "\n";
    echo '       <label for="fitcash_publish_option_' . $key . '">' . $value . '</label>' . "\n";
  }

  echo
  '              </li></ul></td>' . "\n" .
  '              </tr>' . "\n" .
  '              <tr><th class="fitcash_option_left_part"><label for="">' . __('Select the Categories For Imported Articles', 'fitcash') . '</label></th>' . "\n" .
  '                  <td><select name="fitcash_import_cats_select[]" id="fitcash_import_cats_select_tag" multiple="multiple" size="5">' . "\n";

  foreach( $categories as $cat)
  { 
    if ( in_array( $cat->cat_ID, $fitcash_import_cats ) )
    {
      echo '  <option value="' . $cat->cat_ID . '" selected="selected">' . $cat->cat_name . '</option>' . "\n";
    }
    else
    {
      echo '  <option value="' . $cat->cat_ID . '">' . $cat->cat_name . '</option>' . "\n";
    }
  }

  echo
  '                   </select><br /><br />' . "\n" .
  '                   <div class="margintb">New Category: <input type="text" value="" name="fitcash_new_cat_name"  /></div>' . "\n" .
  '                   <div class="margintb">Parent Category: <select name="fitcash_parent_cat" size="1"></div>' . "\n";

  echo '  <option value="0">' . __('None', 'fitcash') . '</option>' . "\n";
  foreach( $categories as $cat)
  { 
    echo '  <option value="' . $cat->cat_ID . '">' . $cat->cat_name . '</option>' . "\n";
  }

  echo
  '                   </select><br /><br />' . "\n" .
  '                   <div class="div-wait" id="divwait3"><img src="' . FITPURL . 'img/loading.gif" /></div>' . "\n" .
  '                   <input type="submit" class="button-primary" value="Add Category" id="fitcash_add_cat_btn" name="fitcash_add_cat_btn" onclick="document.getElementById(nameofDivWait).style.display=\'inline\';this.form.submit();" /><br />' . "\n" .    
  '                   </td>' . "\n" .
  '              </tr>' . "\n" .
  '              <tr><th class="fitcash_option_left_part"><label for="">' . __('Number Of Articles For First Import:', 'fitcash') . '</label></th>' . "\n" .
  '                  <td><select id="fitcash_count_post_first_import_select" name="fitcash_count_post_first_import">';

  foreach( $arr_import_count as $key => $value )
  {
    if ( $value == $fitcash_count_post_first_import )
      $selected = ' selected="selected" ';
    else
      $selected = ' ';
    echo '       <option value="' . $value . '" ' . $selected . '>' . $key . '</option>' . "\n";
  }
  for ( $i = 0; $i < 5; $i++ )
  {
  }

  echo
  '                   </select>' . "\n" .
  '                   </td>' . "\n" .
  '              </tr>' . "\n" .
  '              </table>' . "\n" .
  '              <div class="submit">' . "\n" .
  '                <div class="div-wait" id="divwaitms0"><img src="' . FITPURL . 'img/loading.gif" /></div>' . "\n" .
  '                <input type="submit" class="button-secondary" value="Save Changes" id="fitcash_save_btn_above" name="fitcash_update_options_btn" onclick="document.getElementById(nameofDivWait).style.display=\'inline\';this.form.submit();" />' . "\n" .
  '                <div class="div-wait" id="divwaitms1"><img src="' . FITPURL . 'img/loading.gif" /></div>' . "\n" .
  '                <input type="submit" class="button-primary" value="Import From ' . $url_display . '" id="fitcash_import_posts_btn" name="fitcash_import_btn" onclick="document.getElementById(nameofDivWait).style.display=\'inline\';this.form.submit();" />' . "\n" .    
  '              </div>' . "\n" .
  '            </div>' . "\n" .
  '          </div>' . "\n" .
  '          <div class="postbox ui-droppable" id="spinning-text-div">' . "\n" .
  '            <div title="' . __('Zum umschalten klicken', 'fitcash') . '" class="handlediv"><br /></div>' . "\n" .
  '            <h3 class="hndle">' . __('Spinning Text - Create Unique Content for the Web !', 'fitcash') . '</h3>' . "\n" .
  '            <div class="inside">' . "\n" .
  '              <p>' . "\n" .
  '                ' . __('With spinning text you can create unique content on your blog posts that will make each article imported more search engine-friendly. The headers and footers below will be automatically added at random to each imported article.', 'fitcash') . "\n" .
  '              </p>' . "\n" .
  '              <table class="form-table">' . "\n";

  for ( $i = 0; $i < 10; $i++ )
  {
    echo
    '              <tr><th class="fitcash_option_left_part"><label for="fitcash_spinning_txt_header_' . $i . '">Optional Header Text ' . ($i + 1) . ':</label></th>' . "\n" .
    '                  <td><textarea rows="3" cols="45" id="fitcash_spinning_textarea_header_' . $i . '" name="fitcash_spinning_text_header_' . $i . '">' . html_entity_decode($fitcash_spinning_header_text[$i], ENT_QUOTES) . '</textarea></td>' . "\n" .
    '              </tr>' . "\n" .
    '              <tr><th class="fitcash_option_left_part"><label for="fitcash_spinning_txt_footer_' . $i . '">Optional Footer Text ' . ($i + 1) . ':</label></th>' . "\n" .
    '                  <td><textarea rows="3" cols="45" id="fitcash_spinning_textarea_footer_' . $i . '" name="fitcash_spinning_text_footer_' . $i . '">' . html_entity_decode($fitcash_spinning_footer_text[$i], ENT_QUOTES) . '</textarea></td>' . "\n" .
    '              </tr>' . "\n";
  }

  if ( $fitcash_text_vars == 'on' )
    $checked = ' checked="checked" ';
  else
    $checked = ' ';

  echo
  '              <tr><th class="fitcash_option_left_part"><label for=""></label></th>' . "\n" .
  '                  <td></td>' . "\n" .
  '              </tr>' . "\n" .
  '              </table>' . "\n" .
  '              <div class="submit">' . "\n" .
  '                <div class="div-wait" id="divwaitspt0"><img src="' . FITPURL . 'img/loading.gif" /></div>' . "\n" .
  '                <input type="submit" class="button-secondary" value="Save Changes" id="fitcash_save_btn_spt" name="fitcash_update_options_btn" onclick="document.getElementById(nameofDivWait).style.display=\'inline\';this.form.submit();" />' . "\n" .
  '                <div class="right-bottom"><a href="#Top">Back to Top</a></div>' . "\n" .
  '              </div>' . "\n" .
  '            </div>' . "\n" .
  '          </div>' . "\n" .
  '          <div class="postbox ui-droppable" id="spinning-vars-div">' . "\n" .
  '            <div title="' . __('Zum umschalten klicken', 'fitcash') . '" class="handlediv"><br /></div>' . "\n" .
  '            <h3 class="hndle">' . __('Randomly Changed Text Variables - Create Unique Content for the Web !', 'fitcash') . '</h3>' . "\n" .
  '            <div class="inside">' . "\n" .
  '              <p>' . "\n" .
  '                ' . __('With this feature you can create your own dynamic variables, which will be automatically added to your headers and footers and changed randomly for even greater search engine optimization.', 'fitcash') . '<br /><br />' .
  '                ' . __('They can be used like "... some text {variable 1 name} some more text ..." - then the "variable 1 name" will be replaced with the values you have set. Set the values comma separated in {} - <b>please take care that there are no spaces between the commas and brackets like {},{},{},....</b>.', 'fitcash') . '<br /><br />' . "\n" .
  '              </p>' . "\n" .
  '              <table class="form-table">' . "\n" .
  '              <tr><th class="fitcash_option_left_part"><label for="fitcash_text_vars">Activate text variables: </label></th>' . "\n" .
  '                  <td><input type="checkbox" name="fitcash_text_vars" value="open" ' . $checked . ' /></td>' . "\n" .
  '              </tr>' . "\n";


  if ( $fitcash_text_vars == 'on' )
  {
    echo
    '              <tr><th class="fitcash_option_left_part"><label for="fitcash_add_text_var_btn"></label></th>' . "\n" .
    '                  <td>' . "\n" .
    '                   <div class="div-wait" id="divwait4"><img src="' . FITPURL . 'img/loading.gif" /></div>' . "\n" .
    '                   <input type="submit" class="button-primary" value="' . __('Add New Text Variable', 'fitcash') . '" id="fitcash_add_text_var_btn" name="fitcash_add_text_var_btn" onclick="document.getElementById(nameofDivWait).style.display=\'inline\';this.form.submit();" /><br />' . "\n" .    
    '                  </td>' . "\n" .
    '              </tr>' . "\n";

    for ( $i = 0; $i < $fitcash_num_text_vars; $i++ )
    {
      echo
      '              <tr><th class="fitcash_option_left_part"><label for="fitcash_dyn_txt_var_' . $i . '">Text Variable ' . ($i + 1) . ':</label></th>' . "\n" .
      '                  <td>Name:<br /><input type="text" name="fitcash_text_var_' . $i . '_name" value="' . $fitcash_text_variable[$i]['name'] . '" />' . "\n" .
      '                      <div class="div-wait" id="divwait_var_' . $i . '"><img src="' . FITPURL . 'img/loading.gif" /></div>' . "\n" .
      '                      <input type="submit" class="button-primary" value="' . __('Delete Variable', 'fitcash') . '" id="fitcash_delete_text_var_' . $i . '_btn" name="fitcash_delete_text_var_' . $i . '_btn" onclick="document.getElementById(nameofDivWait).style.display=\'inline\';this.form.submit();" /><br /><br />' . "\n" .    
      '                      Values:<br /><textarea rows="3" cols="45" name="fitcash_text_var_' . $i . '_values">' . $fitcash_text_variable[$i]['values'] . '</textarea></td>' . "\n" .
      '              </tr>' . "\n";
    }
  }

  echo
  '              <tr><th class="fitcash_option_left_part"><label for=""></label></th>' . "\n" .
  '                  <td></td>' . "\n" .
  '              </tr>' . "\n" .
  '              </table>' . "\n" .
  '              <div class="submit">' . "\n" .
  '                <div class="div-wait" id="divwaitspv0"><img src="' . FITPURL . 'img/loading.gif" /></div>' . "\n" .
  '                <input type="submit" class="button-secondary" value="Save Changes" id="fitcash_save_btn_spv" name="fitcash_update_options_btn" onclick="document.getElementById(nameofDivWait).style.display=\'inline\';this.form.submit();" />' . "\n" .
  '                <div class="right-bottom"><a href="#Top">Back to Top</a></div>' . "\n" .
  '              </div>' . "\n" .
  '            </div>' . "\n" .
  '          </div>' . "\n" .
  '        </div>' . "\n" .
  '      </div>' . "\n" .
  '    </div>' . "\n" .
  '    <div class="postbox-container" id="plugin-news">' . "\n" .
  '      <div class="meta-box-sortables ui-sortable" id="side-sortables" unselectable="on">' . "\n" .
  '        <div class="postbox ui-droppable" id="fitcash_info">' . "\n" .
  '          <div title="' . __('Zum umschalten klicken', 'fitcash') . '" class="handlediv"><br /></div>' . "\n" .
  '          <h3 class="hndle">Suggested Affiliate Tools</h3>' . "\n" .
  '          <div class="inside">' . "\n" .
  '            <ul>' . "\n" .
  '              <li><img class="img-link-ico" src="' . FITPURL . 'img/jv_profit_center_favicon.jpg" alt="JV Profit Center Logo" /><a class="link-extern" href="http://www.jvprofitcenter.com" target="_blank" title="JV Profit Center">JV Profit Center</a></li>' . "\n" .
  '            </ul>' . "\n" .
  '            <hr />' . "\n" .
  '            <h4>Let\'s Get Social!</h4>' . "\n" .    
  '            <p>This incredible affiliate tool shows you how you can drive tons of cheap traffic to any offer (including your blog!) using social media such as Facebook.</p>' . "\n" .
  '            <ul>' . "\n" .
  '              <li><img class="img-link-ico" src="http://www.letsgetsocial.com/favicon.ico" alt="Let\'s Get Social!" /><a class="link-extern" href="https://touchstone.infusionsoft.com/go/lgs/jb20/" target="_blank" title="Let\'s Get Social!">Click here to Read More</a></li>' . "\n" .
  '            </ul>' . "\n" .
  '            <hr />' . "\n" .
  '          </div>' . "\n" .
  '        </div>' . "\n" .
  '        <div class="postbox ui-droppable" id="fitcash_links">' . "\n" .
  '          <div title="' . __('Zum umschalten klicken', 'fitcash') . '" class="handlediv"><br /></div>' . "\n" .
  '          <h3 class="hndle">Links</h3>' . "\n" .
  '          <div class="inside">' . "\n" .
  '            <ul>' . "\n" .
  '              <li><img class="img-link-ico" src="http://www.clickbank.com/favicon.ico" alt="Clickbank.com Logo" /><a class="link-extern" href="http://www.clickbank.com" target="_blank" title="Clickbank.com">Clickbank.com</a></li>' . "\n" .
  '              <li><img class="img-link-ico" src="http://www.jonbensonforum.com/favicon.ico" alt="JonBensonFitness.com Logo" /><a class="link-extern" href="http://www.jonbensonfitness.com" target="_blank" title="JBF Product Support Center">JBF Product Support Center</a></li>' . "\n" .
//  '              <li><img class="img-link-ico" src="http://www.jonbensonfitness.com/favicon.ico" alt="JonBensonFitness.com Logo" /><a class="link-extern" href="http://www.jonbensonfitness.com" target="_blank" title="JBF Product Support Center">JBF Product Support Center</a></li>' . "\n" .
  '            </ul>' . "\n" .
  '          </div>' . "\n" .
  '        </div>' . "\n" .
  '      </div>' . "\n" .
  '    </div>' . "\n" .
  '  </div>' . "\n" .
  '  </form>' . "\n" .
  '</div' . "\n";
 
}


////////////////////////////////////////////////////////////////////////////////
// show host blog option page
////////////////////////////////////////////////////////////////////////////////
function fitcash_plugin_print_host_blog_option_page()
{
  global $arr_fitcash_host_blog_type;

  $fitcash_host_blogs       = fitcash_get_option('fitcash_host_blogs');
  $fitcash_host_blog        = fitcash_get_option('fitcash_host_blog');

  echo
  '<div class="wrap">' . "\n" .
  '  <a name="Top"></a>' . "\n" .
  '  <div class="icon32"></div>' . "\n" .
  '  <h2>FitCash - Plugin Settings</h2>' . "\n" .
  '  <hr />' . "\n" .
  '  <div class="clear"></div>' . "\n" . 
  '  <form name="fitcash_import_post_form" method="post" action="">' . "\n";

  wp_nonce_field('fitcash_import_posts');

  $url = $fitcash_host_blogs[$fitcash_host_blog]['url'];
  $url = str_replace( 'http://', '', $url);
  if ( strpos( $url, '/') === false )
    $url_display = $url;
  else
    $url_display = substr( $url, 0, strpos( $url, '/'));

  echo
  '  <div class="metabox-holder has-right-sidebar" id="plugin-panel-widgets">' . "\n" .
  '    <div class="postbox-container" id="plugin-main">' . "\n" .
  '      <div class="has-sidebar-content">' . "\n" .
  '        <div class="meta-box-sortables ui-sortable" id="normal-sortables" unselectable="on">' . "\n" .
  '          <div class="postbox ui-droppable" id="fitcash-host-blog">' . "\n" .
  '            <div title="' . __('Zum umschalten klicken', 'fitcash') . '" class="handlediv"><br /></div>' . "\n" .
  '            <h3 class="hndle">' . __('Hostblog', 'fitcash') . '</h3>' . "\n" .
  '            <div class="inside">' . "\n" .
  '              <b>Set Host Blog to import from</b>' . "\n" .
  '              <p>' . "\n" .
  '                ' . __('You can add different Host Blogs from which you want import your content. The content has to be in Atom or RSS format.', 'fitcash') . "\n" .
  '                ' . __('You can enter the url of a script which process different parameters like userid, number of posts to import etc. or the url of a feed or atom.', 'fitcash') . "\n" .
  '              </p>' . "\n" .
  '              <table class="form-table">' . "\n" .
  '              <tr><th class="fitcash_option_left_part"><label for="fitcash_host_blogs">' . __('Select the Host Blog from which you want import articles', 'fitcash') . '</label></th>' . "\n" .
  '                  <td><select name="fitcash_host_blogs" id="fitcash_host_blog_select" size="1">' . "\n";

  foreach( $fitcash_host_blogs as $key => $blog_entry)
  { 
    if ( $key == $fitcash_host_blog )
      echo '  <option value="' . $key . '" selected="selected">' . $blog_entry['url'] . '</option>' . "\n";
    else
      echo '  <option value="' . $key . '">' . $blog_entry['url'] . '</option>' . "\n";
  }
  $blog_type   = $fitcash_host_blogs[$fitcash_host_blog]['type'];
  $blog_script = $fitcash_host_blogs[$fitcash_host_blog]['script'];
  $blog_params = $fitcash_host_blogs[$fitcash_host_blog]['params'];

  echo
  '                   </select>' . "\n" .
  '                    <div class="div-wait" id="divwaithb0"><img src="' . FITPURL . 'img/loading.gif" /></div>' . "\n" .
  '                    <input type="submit" class="button-primary" value="Delete Entry" id="fitcash_delete_hb_btn" name="fitcash_delete_host_blog_btn" onclick="document.getElementById(nameofDivWait).style.display=\'inline\';this.form.submit();" /><br />' . "\n" .    
  '                    <div class="margintb"><label class="one-row label-hb" for="">Host Blog Type: </label>&nbsp;<b>' . $arr_fitcash_host_blog_type[$blog_type] . '</b><br />' . "\n" .
  '                    <div class="margintb one-row"><label class="label-hb" for="">Script: </label><b>' . $blog_script . '</b><br />' . "\n" .
  '                    <div class="margintb one-row"><i><u>Parameters</u></i><br />' . "\n" . 
  '                    <div class="margintb one-row"><label class="label-hb" for="">Variable</label>Value<br />' . "\n";

  if ( ($blog_type == 0) AND (count($blog_params) != 0) )
  {
    foreach ( $blog_params as $name => $value )
    {
      echo
      '                    <div class="margintb one-row"><label class="label-hb" for="">' . $name . '</label>' . $value . '<br />' . "\n";
    }
  }

  echo
  '                  </td>' . "\n" .
  '              </tr>' . "\n" .
  '              <tr><th class="fitcash_option_left_part"><label for="fitcash_host_blog_entry">' . __('Add Parameters To Host Blog Entry', 'fitcash') . '</label></th>' . "\n" .
  '                  <td>' . "\n" .
  '                    <div class="margintb one-row"><label class="label-hb" for="">Variable: </label><input type="text" size="25" value="" name="fitcash_hb_param_variable" /></div><br /><div class="clear"></div>' . "\n" .
  '                    <div class="margintb one-row"><label class="label-hb" for="">Value: </label><input type="text" size="25" value="" name="fitcash_hb_param_value" /></div><br /><br /><div class="clear"></div>' . "\n" .
  '                    <div class="div-wait" id="divwaithb1"><img src="' . FITPURL . 'img/loading.gif" /></div>' . "\n" .
  '                    <input type="submit" class="button-primary" value="Add Parameter" id="fitcash_add_param_btn" name="fitcash_add_parameters_btn" onclick="document.getElementById(nameofDivWait).style.display=\'inline\';this.form.submit();" /><br />' . "\n" .    
  '                  </td>' . "\n" .
  '              </tr>' . "\n" .
  '              <tr><th class="fitcash_option_left_part"><label for="fitcash_host_blog_entry">' . __('Create New Host Blog Entry', 'fitcash') . '</label></th>' . "\n" .
  '                  <td>' . "\n" .
  '                    <div class="margintb"><label class="label-hb" for="">URL: </label><input type="text" size="35" value="" name="fitcash_host_blog_entry"  /></div>' . "\n" .
  '                    <div class="margintb"><label class="one-row label-hb" for="">Host Blog Type: </label>' . "\n" .
  '                      <ul class="one-row"><li>' . "\n";

  foreach( $arr_fitcash_host_blog_type as $key => $type )
  {
    $checked = ' ';
    echo '      <input type="radio" class="fitcash-radio" name="fitcash_host_blog_type" id="fitcash_host_blog_type_' . $key . '" value="' . $key . '"' . $checked . ' />' . "\n";
    echo '      <label for="fitcash_host_blog_type_' . $key . '">' . $type . '</label>' . "\n";
  }

  echo
  '                      </li></ul></div><br />' . "\n" .
  '                    <div class="margintb one-row"><label class="label-hb" for="">Script: </label><input type="text" size="35" value="" name="fitcash_host_blog_script" /></div><br /><br /><div class="clear"></div>' . "\n" .
  '                    <div class="div-wait" id="divwaithb2"><img src="' . FITPURL . 'img/loading.gif" /></div>' . "\n" .
  '                    <input type="submit" class="button-primary" value="Add Entry" id="fitcash_add_hb_btn" name="fitcash_add_host_blog_btn" onclick="document.getElementById(nameofDivWait).style.display=\'inline\';this.form.submit();" /><br />' . "\n" .    
  '                  </td>' . "\n" .
  '              </tr>' . "\n" .
  '              </table>' . "\n" .
  '              <div class="submit">' . "\n" .
  '                <div class="div-wait" id="divwaithb3"><img src="' . FITPURL . 'img/loading.gif" /></div>' . "\n" .
  '                <input type="submit" class="button-secondary" value="Save Changes" id="fitcash_save_btn_hb" name="fitcash_update_options_btn" onclick="document.getElementById(nameofDivWait).style.display=\'inline\';this.form.submit();" />' . "\n" .
  '              </div>' . "\n" .
  '            </div>' . "\n" .
  '          </div>' . "\n" .
  '        </div>' . "\n" .
  '      </div>' . "\n" .
  '    </div>' . "\n" .
  '    <div class="postbox-container" id="plugin-news">' . "\n" .
  '      <div class="meta-box-sortables ui-sortable" id="side-sortables" unselectable="on">' . "\n" .
  '        <div class="postbox ui-droppable" id="fitcash_info">' . "\n" .
  '          <div title="' . __('Zum umschalten klicken', 'fitcash') . '" class="handlediv"><br /></div>' . "\n" .
  '          <h3 class="hndle">Suggested Affiliate Tools</h3>' . "\n" .
  '          <div class="inside">' . "\n" .
  '            <ul>' . "\n" .
  '              <li><img class="img-link-ico" src="' . FITPURL . 'img/jv_profit_center_favicon.jpg" alt="JV Profit Center Logo" /><a class="link-extern" href="http://www.jvprofitcenter.com" target="_blank" title="JV Profit Center">JV Profit Center</a></li>' . "\n" .
  '            </ul>' . "\n" .
  '            <hr />' . "\n" .
  '            <h4>Let\'s Get Social!</h4>' . "\n" .    
  '            <p>This incredible affiliate tool shows you how you can drive tons of cheap traffic to any offer (including your blog!) using social media such as Facebook.</p>' . "\n" .
  '            <ul>' . "\n" .
  '              <li><img class="img-link-ico" src="http://www.letsgetsocial.com/favicon.ico" alt="Let\'s Get Social!" /><a class="link-extern" href="https://touchstone.infusionsoft.com/go/lgs/jb20/" target="_blank" title="Let\'s Get Social!">Click here to Read More</a></li>' . "\n" .
  '            </ul>' . "\n" .
  '            <hr />' . "\n" .
  '          </div>' . "\n" .
  '        </div>' . "\n" .
  '        <div class="postbox ui-droppable" id="fitcash_links">' . "\n" .
  '          <div title="' . __('Zum umschalten klicken', 'fitcash') . '" class="handlediv"><br /></div>' . "\n" .
  '          <h3 class="hndle">Links</h3>' . "\n" .
  '          <div class="inside">' . "\n" .
  '            <ul>' . "\n" .
  '              <li><img class="img-link-ico" src="http://www.clickbank.com/favicon.ico" alt="Clickbank.com Logo" /><a class="link-extern" href="http://www.clickbank.com" target="_blank" title="Clickbank.com">Clickbank.com</a></li>' . "\n" .
  '              <li><img class="img-link-ico" src="http://www.jonbensonforum.com/favicon.ico" alt="JonBensonFitness.com Logo" /><a class="link-extern" href="http://www.jonbensonfitness.com" target="_blank" title="JBF Product Support Center">JBF Product Support Center</a></li>' . "\n" .
//  '              <li><img class="img-link-ico" src="http://www.jonbensonfitness.com/favicon.ico" alt="JonBensonFitness.com Logo" /><a class="link-extern" href="http://www.jonbensonfitness.com" target="_blank" title="JBF Product Support Center">JBF Product Support Center</a></li>' . "\n" .
  '            </ul>' . "\n" .
  '          </div>' . "\n" .
  '        </div>' . "\n" .
  '      </div>' . "\n" .
  '    </div>' . "\n" .
  '  </div>' . "\n" .
  '  </form>' . "\n" .
  '</div' . "\n";
 
}



?>