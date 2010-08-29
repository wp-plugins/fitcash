<?php

function fitcash_plugin_print_option_page()
{
  $options = get_option('fitcash_import_posts');

  $fitcash_import_user_id  = fitcash_get_option('fitcash_import_user_id');
  $fitcash_import_feed_url = fitcash_get_option('fitcash_import_feed_url');
  $fitcash_jv_profit_center_id = fitcash_get_option('fitcash_jv_profit_center_id');
  $fitcash_import_cats         = fitcash_get_option('fitcash_import_cats');
  $fitcash_post_header_text    = fitcash_get_option('fitcash_post_header_text');
  $fitcash_post_footer_text    = fitcash_get_option('fitcash_post_footer_text');
  $fitcash_spinning_header_text = fitcash_get_option('fitcash_spinning_header_text');
  $fitcash_spinning_footer_text = fitcash_get_option('fitcash_spinning_footer_text');
  $fitcash_count_post_first_import = fitcash_get_option('fitcash_count_post_first_import');
  $fitcash_count_post_next_imports = fitcash_get_option('fitcash_count_post_next_imports');
  $fitcash_import_schedule = fitcash_get_option('fitcash_import_schedule');
  $fitcash_publish_option  = fitcash_get_option('fitcash_publish_option');
  $fitcash_text_vars   = fitcash_get_option('fitcash_text_vars');
  $fitcash_num_text_vars   = fitcash_get_option('fitcash_num_text_vars');
  $fitcash_text_variable   = fitcash_get_option('fitcash_text_variable');

  $arr_frequency=array(
                    'daily'   => 'Daily',
                    'weekly'  => 'Weekly',
                    'monthly' => 'Monthly'
                      );

  $arr_import_as_options=array(
                    'publish' => 'Publish Immediately (Best Option)',
                    'draft'   => 'Save As Drafts'
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
  '    This free plugin allows you to import articles from Jon Benson\'s Fitness and nutrition blog under <a class="link-extern" href="http://www.jonbensonfitness.com" target="_blank" tilte="Jon Bensons Fitness">http://www.jonbensonfitness.com</a>, complete <b>with your affiliate links</b>.This provides you with automatic content that can also earn you commissions on all of Jon Benson\'s Clickbank Products.' . "\n" .
  '  </p>' . "\n" .
  '  <hr />' . "\n" .
  '  <div class="clear"></div>' . "\n" . 
  '  <form name="fitcash_import_post_form" method="post" action="">' . "\n";

  wp_nonce_field('fitcash_import_posts');

  echo
  '  <div class="metabox-holder has-right-sidebar" id="poststuff">' . "\n" .
  '    <div class="inner-sidebar float-right">' . "\n" .
  '      <div class="meta-box-sortabless ui-sortable" id="side-sortables">' . "\n" .
  '        <div class="postbox" id="fitcash_info">' . "\n" .
  '          <h3 class="hndle">Jon Benson Affiliate Tools</h3>' . "\n" .
  '          <div class="inside">' . "\n" .
  '            <ul>' . "\n" .
  '              <li><img class="img-link-ico" src="' . FITPURL . 'img/jv_profit_center_favicon.jpg" alt="JV Profit Center Logo" /><a class="link-extern" href="http://www.jvprofitcenter.com" target="_blank" title="JV Profit Center">JV Profit Center</a></li>' . "\n" .
  '              <li><img class="img-link-ico" src="http://www.jonbensonfitness.com/favicon.ico" alt="JonBensonFitness.com Logo" /><a class="link-extern" href="http://www.jonbensonfitness.com" target="_blank" title="JBF Product Support Center">JBF Product Support Center</a></li>' . "\n" .
  '            </ul>' . "\n" .
  '          </div>' . "\n" .
  '        </div>' . "\n" .
  '        <div class="postbox" id="fitcash_links">' . "\n" .
  '          <h3 class="hndle">Links</h3>' . "\n" .
  '          <div class="inside">' . "\n" .
  '            <ul>' . "\n" .
  '              <li><img class="img-link-ico" src="http://www.clickbank.com/favicon.ico" alt="Clickbank.com Logo" /><a class="link-extern" href="http://www.clickbank.com" target="_blank" title="Clickbank.com">Clickbank.com</a></li>' . "\n" .
  '            </ul>' . "\n" .
  '          </div>' . "\n" .
  '        </div>' . "\n" .
  '      </div>' . "\n" .
  '    </div>' . "\n" .
  '    <div class="has-sidebar fitcash-padded float_left">' . "\n" .
  '      <div class="has-sidebar-content" id="post-body-content">' . "\n" .
  '        <div class="meta-box-sortabless">' . "\n" .
  '          <div class="postbox float-left" id="fitcash-settings">' . "\n" .
  '            <h3>' . __('Settings', 'fitcash_import_posts') . '</h3>' . "\n" .
  '            <div class="inside">' . "\n" .
  '              <b>Import from JonBensonFitness.com</b>' . "\n" .
  '              <table class="form-table">' . "\n" .
  '              <tr><th class="fitcash_option_left_part"><label for="fitcash_jv_profit_center_id">Enter your JV Profit Center Id</label></th>' . "\n" .
  '                  <td><input type="text" id="fitcash_jv_profit_center_id" name="fitcash_jv_profit_center_id" value="' . $fitcash_jv_profit_center_id . '" /></td>' . "\n" .
  '              </tr>' . "\n" .
  '              <tr><th class="fitcash_option_left_part"><label for="fitcash_post_header_text">Edit the Default Article Header</label></th>' . "\n" .
  '                  <td><b><textarea rows="3" cols="55" id="fitcash_post_header_text" name="fitcash_post_header_text">' . html_entity_decode($fitcash_post_header_text, ENT_QUOTES) . '</textarea></b></td>' . "\n" .
  '              </tr>' . "\n" .
  '              <tr><th class="fitcash_option_left_part"><label for="fitcash_post_footer_text">Edit the Default Article Footer</label></th>' . "\n" .
  '                  <td><b><textarea rows="3" cols="55" id="fitcash_post_footer_text" name="fitcash_post_footer_text">' . html_entity_decode($fitcash_post_footer_text, ENT_QUOTES) . '</textarea></b></td>' . "\n" .
  '              </tr>' . "\n" .
  '              <tr><th class="fitcash_option_left_part"><label for="">How Frequently Do You Want New Articles Imported</label></th>' . "\n" .
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
  '              <tr><th class="fitcash_option_left_part"><label for="">Set New Articles On Import To... </label></th>' . "\n" .
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
  '              <tr><th class="fitcash_option_left_part"><label for="">Select the Categories For Imported Articles</label></th>' . "\n" .
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
  '                   New Category: <input type="text" value="" name="fitcash_new_cat_name"  />  Parent Category: <select name="fitcash_parent_cat" size="1">' . "\n";

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
  '              <tr><th class="fitcash_option_left_part"><label for="">Number Of Articles For First Import:</label></th>' . "\n" .
  '                  <td><select id="fitcash_count_post_first_import_select" name="fitcash_count_post_first_import">';

  for ( $i = 0; $i < 5; $i++ )
  {
    if ( ($i + 1) == $fitcash_count_post_first_import )
      $selected = ' selected="selected" ';
    else
      $selected = ' ';
    echo '       <option value="' . ($i + 1) . '" ' . $selected . '>' . ($i + 1) . '</option>' . "\n";
  }

  echo
  '                   </select>' . "\n" .
  '                   </td>' . "\n" .
  '              </tr>' . "\n" .
  '              </table>' . "\n" .
  '            </div>' . "\n" .
  '          </div>' . "\n" .
  '          <div class="submit">' . "\n" .
  '            <div class="div-wait" id="divwait0"><img src="' . FITPURL . 'img/loading.gif" /></div>' . "\n" .
  '            <input type="submit" class="button-secondary" value="Save Changes" id="fitcash_save_btn_above" name="fitcash_update_options_btn" onclick="document.getElementById(nameofDivWait).style.display=\'inline\';this.form.submit();" />' . "\n" .
  '            <div class="div-wait" id="divwait"><img src="' . FITPURL . 'img/loading.gif" /></div>' . "\n" .
  '            <input type="submit" class="button-primary" value="Import From JonBensonFitness.com" id="fitcash_import_posts_btn" name="fitcash_import_btn" onclick="document.getElementById(nameofDivWait).style.display=\'inline\';this.form.submit();" />' . "\n" .    
  '          </div>' . "\n" .
  '          <div class="postbox float-left" id="spinning-text-div">' . "\n" .
  '            <h3>' . __('Spinning Text - Create Unique Content for the Web !', 'fitcash') . '</h3>' . "\n" .
  '            <div class="inside">' . "\n" .
  '              <p>' . "\n" .
  '                With spinning text you can create unique content on your blog. These ten different texts will be added to your imported posts automatical. During each import done by the worpdress cron - the next header and footer of the list is taken and added to your imported posts' . "\n" .
  '              </p>' . "\n" .
  '              <table class="form-table">' . "\n";

  for ( $i = 0; $i < 10; $i++ )
  {
    echo
    '              <tr><th class="fitcash_option_left_part"><label for="fitcash_spinning_txt_header_' . $i . '">Optional Header Text ' . ($i + 1) . ':</label></th>' . "\n" .
    '                  <td><textarea rows="3" cols="55" id="fitcash_spinning_textarea_header_' . $i . '" name="fitcash_spinning_text_header_' . $i . '">' . html_entity_decode($fitcash_spinning_header_text[$i], ENT_QUOTES) . '</textarea></td>' . "\n" .
    '              </tr>' . "\n" .
    '              <tr><th class="fitcash_option_left_part"><label for="fitcash_spinning_txt_footer_' . $i . '">Optional Footer Text ' . ($i + 1) . ':</label></th>' . "\n" .
    '                  <td><textarea rows="3" cols="55" id="fitcash_spinning_textarea_footer_' . $i . '" name="fitcash_spinning_text_footer_' . $i . '">' . html_entity_decode($fitcash_spinning_footer_text[$i], ENT_QUOTES) . '</textarea></td>' . "\n" .
    '              </tr>' . "\n";
  }

  if ( $fitcash_text_vars == 'on' )
    $checked = ' checked="checked" ';
  else
    $checked = ' ';

  echo
  '              <tr><th class="fitcash_option_left_part"><label for=""></label></th>' . "\n" .
  '                  <td><div class="right-bottom"><a href="#Top">Back to Top</a></div></td>' . "\n" .
  '              </tr>' . "\n" .
  '              </table>' . "\n" .
  '            </div>' . "\n" .
  '          </div>' . "\n" .
  '          <div class="submit">' . "\n" .
  '            <div class="div-wait" id="divwait0"><img src="' . FITPURL . 'img/loading.gif" /></div>' . "\n" .
  '            <input type="submit" class="button-secondary" value="Save Changes" id="fitcash_save_btn_above" name="fitcash_update_options_btn" onclick="document.getElementById(nameofDivWait).style.display=\'inline\';this.form.submit();" />' . "\n" .
  '          </div>' . "\n" .
  '          <div class="postbox float-left" id="spinning-vars-div">' . "\n" .
  '            <h3>' . __('Randomly Changed Text Variables - Create Unique Content for the Web !', 'fitcash') . '</h3>' . "\n" .
  '            <div class="inside">' . "\n" .
  '              <p>' . "\n" .
  '                With this feature you can add variables to your header and footer text versions, which are changed randomly.<br /><br />' .
  '                They can be used like "... some text {variable 1 name} some more text ..." - then the "variable 1 name" will be replaced with the values you have set. Set the values comma seperated in {} - <b>please take care that there are no spaces between the commas and brackets like {},{},{},...</b>.<br /><br />' . "\n" .
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
      '                      Values:<br /><textarea rows="3" cols="55" name="fitcash_text_var_' . $i . '_values">' . $fitcash_text_variable[$i]['values'] . '</textarea></td>' . "\n" .
      '              </tr>' . "\n";
    }
  }

  echo
  '              <tr><th class="fitcash_option_left_part"><label for=""></label></th>' . "\n" .
  '                  <td><div class="right-bottom"><a href="#Top">Back to Top</a></div></td>' . "\n" .
  '              </tr>' . "\n" .
  '              </table>' . "\n" .
  '            </div>' . "\n" .
  '          </div>' . "\n" .
  '          <div class="submit">' . "\n" .
  '            <div class="div-wait" id="divwait2"><img src="' . FITPURL . 'img/loading.gif" /></div>' . "\n" .
  '            <input type="submit" class="button-secondary" value="Save Changes" id="fitcash_save_btn_below" name="fitcash_update_options_btn" onclick="document.getElementById(nameofDivWait).style.display=\'inline\';this.form.submit();" />' . "\n" .
  '          </div>' . "\n" .
  '        </div>' . "\n" .
  '      </div>' . "\n" .
  '    </div>' . "\n" .
  '  </div>' . "\n" .
  '  </form>' . "\n" .
  '</div' . "\n";
 
}




?>