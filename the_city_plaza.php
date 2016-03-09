<?php
/*
Plugin Name: The City Plaza Widget
Plugin URI: https://github.com/joshpeterson/thecity-plaza-wordpress/tree/sbapeters
Description: This widget allows you to pull your OnTheCity.org plaza information into your WordPress website (modified for the sbapeters.org site).
Author: Wes Hays (Modified by Josh Peterson)
Version: 0.8.6 (modified)
Author URI: http://www.OnTheCity.org
*/

include_once 'plaza_scripts.php';
require_once 'lib/plaza-php/lib/the_city.php';
require_once 'lib/plaza_wordpress_cache.php';


class The_City_Plaza_Widget extends WP_Widget {
  
  function __construct() {
    $widget_ops = array('classname' => 'the_city_plaza_widget', 
                        'description' => 'Pulls information from your church\'s OnTheCity.org Plaza page.' );
    $this->WP_Widget('the-city-plaza-widget', 'The City Plaza', $widget_ops);
  }
  

  function form($instance) {
    /* Set up some default widget settings. */
		$defaults = array( 'subdomain_key' => '', 
                       'title_link' => '1',
                       'group_nickname' => '',
                       'plaza_display' => 'prayers', 
                       'items_to_display' => '10',
                       'show_dates' => '0', 
                       'show_type' => '0',
                       'cache_duration' => '86400');

		$instance = wp_parse_args( (array) $instance, $defaults );    

    $title = strip_tags($instance['title']);
    $subdomain_key = strip_tags($instance['subdomain_key']);
    $title_link = strip_tags($instance['title_link']);
    $group_nickname = strip_tags($instance['group_nickname']);
    $plaza_display = strip_tags($instance['plaza_display']);
    $items_to_display = strip_tags($instance['items_to_display']);
    $show_dates = strip_tags($instance['show_dates']);
    $show_type = strip_tags($instance['show_type']);
    $cache_duration = strip_tags($instance['cache_duration']);

    $clear_cache_msg = '';
    if (isset($_POST['clear_cache_now'])) {
      global $wpdb;
      $cacher = new PlazaWordPressCache( $subdomain_key );
      $cacher->set_db_connection($wpdb);
      $cacher->clear_cache();
      $clear_cache_msg = 'Cache cleared';
    }

    ?>

    <p>
      <label for="<?php echo $this->get_field_id('title'); ?>">
        Widget Title: 
        <input class="widefat" 
               id="<?php echo $this->get_field_id('title'); ?>" 
               name="<?php echo $this->get_field_name('title'); ?>" 
               type="text" 
               value="<?php echo attribute_escape($title); ?>" />
       </label>
       <i>The title to display at the top of the widget</i>

      <br>

      <?php
        $title_link_checked = empty($title_link) ? '' : 'checked="checked"';
      ?>
      <label for="<?php echo $this->get_field_id('title_link'); ?>">
        <input type="checkbox" 
               id="<?php echo $this->get_field_id('title_link'); ?>" 
               name="<?php echo $this->get_field_name('title_link'); ?>"
               <?php echo $title_link_checked ?> /> Title links to main plaza page      
      </label>         
    </p>


    <p>
     <label for="<?php echo $this->get_field_id('subdomain_key'); ?>">
       Subdomain: 
       <input class="widefat" 
              id="<?php echo $this->get_field_id('subdomain_key'); ?>" 
              name="<?php echo $this->get_field_name('subdomain_key'); ?>" 
              type="text" 
              value="<?php echo attribute_escape($subdomain_key); ?>" />
      </label>
      <i>Ex: https://[subdomain].OnTheCity.org</i>
    </p>


   <p>
     <label for="<?php echo $this->get_field_id('group_nickname'); ?>">
       Group Nickname (optional): 
       <input class="widefat" 
              id="<?php echo $this->get_field_id('group_nickname'); ?>" 
              name="<?php echo $this->get_field_name('group_nickname'); ?>" 
              type="text" 
              value="<?php echo attribute_escape($group_nickname); ?>" />
      </label>
      <i>Only items for this group will be pulled.</i>
    </p>    

  
    <p>
      <?php 
        $topics_s = $events_s = $prayers_s = $needs_s = $album_s = '';
        switch($instance['plaza_display']) {
          case 'all':
            $all_s = 'selected="selected"'; 
            break;
          case 'topics':
            $topics_s = 'selected="selected"'; 
            break;
          case 'events':
            $events_s = 'selected="selected"'; 
            break;
          case 'prayers':
            $prayers_s = 'selected="selected"';
            break;
          case 'needs':
            $needs_s = 'selected="selected"'; 
            break;
          case 'albums':
            $album_s = 'selected="selected"';
            break;
        }
      ?> 
    
      <label for="<?php echo $this->get_field_id('plaza_display'); ?>">
        Display:        			
        <select class="widefat" 
                id="<?php echo $this->get_field_id('plaza_display'); ?>" 
                name="<?php echo $this->get_field_name('plaza_display'); ?>">
            <option value="all" <?php echo $all_s; ?> >Show All</option>
        		<option value="topics" <?php echo $topics_s; ?> >Topics</option>
        		<option value="events" <?php echo $events_s; ?> >Events</option>
        		<option value="prayers" <?php echo $prayers_s; ?> >Prayers</option>
        		<option value="needs" <?php echo $needs_s; ?> >Needs</option>
        		<option value="albums" <?php echo $album_s; ?> >Albums</option>
        </select>
      </label>


      <?php
        $show_dates_checked = empty($show_dates) ? '' : 'checked="checked"';
      ?>
      <label for="<?php echo $this->get_field_id('show_dates'); ?>">
        <input type="checkbox" 
               id="<?php echo $this->get_field_id('show_dates'); ?>" 
               name="<?php echo $this->get_field_name('show_dates'); ?>"
               <?php echo $show_dates_checked ?> /> Show Dates        
      </label>

      <br>

      <?php
        $show_type_checked = empty($show_type) ? '' : 'checked="checked"';
      ?>
      <label for="<?php echo $this->get_field_id('show_type'); ?>">
        <input type="checkbox" 
               id="<?php echo $this->get_field_id('show_type'); ?>" 
               name="<?php echo $this->get_field_name('show_type'); ?>"
               <?php echo $show_type_checked ?> /> Show Plaza item type above title      
      </label>      
    </p>
    

    <p>
      <label for="<?php echo $this->get_field_id('items_to_display'); ?>">
        Items to display:              
        <select class="widefat" 
                id="<?php echo $this->get_field_id('items_to_display'); ?>" 
                name="<?php echo $this->get_field_name('items_to_display'); ?>">

            <?php 
              $item_count_selected = '';
              for($i=1; $i<=15; $i++) {
                $item_count_selected = $items_to_display == $i ? 'selected="selected"' : '';
                echo "<option value=\"$i\" $item_count_selected>$i</option>";                
              }
            ?>
        </select>
      </label>      
    </p>

    
    <p>
      <?php 
        $one_hour = $one_day = $one_week = $one_month = '';
        switch($instance['cache_duration']) {
          case '3600': // One Hour
            $one_hour = 'selected="selected"'; 
            break;
          case '86400': // One Day
            $one_day = 'selected="selected"'; 
            break;
          case '604800': // One Week
            $one_week = 'selected="selected"';
            break;
          case '2592000': // One Month (30 days)
            $one_month = 'selected="selected"'; 
        }
      ?> 
    
      <label for="<?php echo $this->get_field_id('cache_duration'); ?>">
        Cache data for:        			
        <select class="widefat" 
                id="<?php echo $this->get_field_id('cache_duration'); ?>" 
                name="<?php echo $this->get_field_name('cache_duration'); ?>">
        		<option value="3600" <?php echo $one_hour; ?> >One Hour</option>
        		<option value="86400" <?php echo $one_day; ?> >One Day</option>
        		<option value="604800" <?php echo $one_week; ?> >One Week</option>
        		<option value="2592000" <?php echo $one_month; ?> >One Month (30 days)</option>
        </select>
      </label>

      <label for="clear_cache_now">
        <input type="checkbox" id="clear_cache_now" name="clear_cache_now" /> Clear cache on save       
      </label>

      <?php if(!empty($clear_cache_msg)) { echo '<div style="color:#990000">'.$clear_cache_msg.'</div>'; } ?>
    </p>
    <?php
  }



  function update($new_instance, $old_instance) {
    $instance = $old_instance;

    // This is done this way for older versions of PHP otherwise I would combine them all into one line.
    $nickname = strip_tags($new_instance['group_nickname']);
    $nickname = strtolower($nickname);
    $nickname = trim($nickname);

    $instance['title'] = strip_tags($new_instance['title']);
    $instance['title_link'] = strip_tags($new_instance['title_link']);
    $instance['subdomain_key'] = strip_tags($new_instance['subdomain_key']);
    $instance['group_nickname'] = $nickname;
    $instance['plaza_display'] = strip_tags($new_instance['plaza_display']);
    $instance['items_to_display'] = strip_tags($new_instance['items_to_display']);
    $instance['show_dates'] = strip_tags($new_instance['show_dates']);
    $instance['show_type'] = strip_tags($new_instance['show_type']);
    $instance['cache_duration'] = strip_tags($new_instance['cache_duration']);
    return $instance;
  }
  


  function widget($args, $instance) {
    extract($args);

    $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
    $title_link = empty($instance['title_link']) ? '' : $instance['title_link'];
    $subdomain_key = empty($instance['subdomain_key']) ? ' ' : $instance['subdomain_key'];
    $group_nickname = empty($instance['group_nickname']) ? '' : $instance['group_nickname'];
    $plaza_display = empty($instance['plaza_display']) ? ' ' : $instance['plaza_display'];
    $items_to_display = empty($instance['items_to_display']) ? '10' : $instance['items_to_display'];
    $show_dates = empty($instance['show_dates']) ? ' ' : $instance['show_dates'];
    $show_type = empty($instance['show_type']) ? ' ' : $instance['show_type'];
    $cache_duration = empty($instance['cache_duration']) ? ' ' : $instance['cache_duration'];

    echo $before_widget;
    echo "<div class='container'>";
    if (!empty( $title )) {
      $display_title = $title;
      if(!empty($title_link)) {
        $display_title = '<a href="http://'.$subdomain_key.'.onthecity.org/plaza" target="_blank">'.$title.'</a>';
      }
      $html = "<div class='row'>
		 <div class='col-md-2 col-md-offset-5'>
		   <h1 style='text-align: center;'>$title</h1>
                   <hr>
                 </div>
               </div>";
      echo $html;
    };

    include dirname(__FILE__).'/widget_info.php';
    echo "</div>";
    echo $after_widget;
  }

}

add_action('widgets_init', create_function('', 'return register_widget("The_City_Plaza_Widget");'));

?>