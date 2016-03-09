<?php

  require_once '../../../wp-blog-header.php';
  require_once 'lib/plaza-php/lib/the_city.php';
  require_once 'lib/plaza_collection.php';
  require_once 'lib/plaza_wordpress_cache.php';
  
  if( empty( $_GET['subdomain_key'] ) ) {
    echo 'Subdomain not set';
    
    
  } else {

    global $wpdb;
    $cacher = new PlazaWordPressCache( $_GET['subdomain_key'] );
    $cacher->set_db_connection($wpdb);

    $the_city = new TheCity( $_GET['subdomain_key'], true, $cacher);  
    $the_city->add_url_params('wp=1');
    if( isset($_GET['group_nickname']) ) { $the_city->set_group_nickname($_GET['group_nickname']); }
    

    $subdomain_key = $_GET['subdomain_key'];
    $plaza_choice = $_GET['plaza_display'];    
    $items_to_display = $_GET['items_to_display'];    
    $show_dates = isset($_GET['show_dates']) ? $_GET['show_dates'] : '';  
    $show_type = isset($_GET['show_type']) ? $_GET['show_type'] : '';   
    $plaza_choice_key = '';
    $plaza_display = '';

    switch($_GET['plaza_display']) {
      case 'all':
        $plaza_display = new PlazaCollection($the_city, $items_to_display);
        break;
      case 'topics':
        $plaza_display = $the_city->topics($items_to_display); 
        break;
      case 'events':
        $plaza_display = $the_city->events($items_to_display); 
        break;
      case 'prayers':
        $plaza_display = $the_city->prayers($items_to_display); 
        break;
      case 'needs':
        $plaza_display = $the_city->needs($items_to_display); 
        break;
      case 'albums':
        $plaza_display = $the_city->albums($items_to_display); 
        break;
      default:
        $plaza_choice = 'topics';
        $plaza_display = $the_city->topics($items_to_display); 
    }
    
    $html = array();


    $plaza_titles = $plaza_display->titles();
    if( empty($plaza_titles) ) {
      $html[] = "No $plaza_choice found";      
    } else {
      $item_count = 0;
      foreach($plaza_titles as $indx => $title) {  
        $item = $plaza_display->select($indx);  

        if($plaza_choice == 'all') {
          $str = get_class($item);
          $item_type_path  = strtolower($str) . 's';
        } else {
          $item_type_path = $plaza_choice;
        }


        $plaza_link_base = 'https://'.$_GET['subdomain_key'].'.onthecity.org/plaza/'.$item_type_path.'/'; 
        $plaza_link = $plaza_link_base . $item->id();   
        $item_date = '';

        if(!empty($show_dates)) {
          $item_created_at = get_class($item) == 'Event' ? $item->starting_at() : $item->created_at();
          if( !empty($item_created_at) ) {
            $item_created_at = date_parse($item_created_at);
            $item_date = implode( array($item_created_at['year'], $item_created_at['month'], $item_created_at['day']), '-');
          }
        }       

        // Format the date with the full month name, the date, and the date suffix,
        // e.g. "March 7th".
        $date = date_create($item_date);
        $item_date = date_format($date, 'F jS');

        if(!empty($show_type)) {
          $item_type = get_class($item);
          if(empty($item_date)) {
            $item_date = $item_type;
          } else {
            $item_date .= ' :: ' . $item_type;
          }
        }  

        // Strip unprintable characters from the content and display the first sentence of the content.
        $item_content = $item->content();
        $item_content = preg_replace('/[[:^print:]]/', ' ', $item_content);
        $item_content = strtok($item_content, '.!?');

        $item_display_date = empty($item_date) ? '' : '<div class="tc_wp_date">' . $item_date . '</div>';

        // Wrap every three entries in a new row.
        if ($item_count % 3 == 0)
          $html[] = "<div class='row'>";

        $html[] = "<div class='col-md-4'>
            <span class='eighteen'>$item_date - $title</span>
            <p>$item_content... <a href='$plaza_link'>Read More</a></p>
        </div>";

        $item_count++;

        if ($item_count % 3 == 0)
          $html[] = "</div>";
      }
    }

    echo implode($html, '');
  }

?>
