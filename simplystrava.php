<?php
/*
Plugin Name: Simply Strava
Plugin URI: http://www.njcyclist.com/simply_strava
Description: Strava Weekly Mileage Widget
Version: 1.0.4
License: GPLv3
Author: Doug Junkins
Author URI: http://www.njcyclist.com
*/


// Load Strava API Class
require ( WP_PLUGIN_DIR . '/' . plugin_basename(dirname(__FILE__)) . '/' . 'simplystrava_api.php');

// Check for localization file
load_plugin_textdomain('simplystrava', false, basename( dirname( __FILE__ ) ) . '/languages' );

//Set up admin menu
add_action ('admin_menu', 'simplystrava_create_menu');

function simplystrava_create_menu() {
    // New top level menu
    add_options_page('Simply Strava', 'Simply Strava', 'manage_options', __FILE__, 'simplystrava_settings_page');
    add_filter( "plugin_action_links", "simplystrava_settings_link", 10, 2 );
    // register settings
    add_action ( 'admin_init', 'simplystrava_register_settings' );
}

// create settings link on plugins page
function simplystrava_settings_link($links, $file) {
    static $this_plugin;
    if (!$this_plugin) $this_plugin = plugin_basename(__FILE__);
    if ($file == $this_plugin) {
        $settings_link = '<a href="options-general.php?page=simply-strava/simplystrava.php">' . __("Settings", "simply_strava") . '</a>';
        array_unshift($links, $settings_link);
    }
    return $links;
}

/* Create List of Timezones */
function createTZlist() {
  $tza = DateTimeZone::listAbbreviations();
  $tzlist = array();
  foreach ($tza as $zone)
    foreach ($zone as $item) 
      if (is_string($item['timezone_id']) && $item['timezone_id'] != '')
        $tzlist[] = $item['timezone_id'];
  $tzlist = array_unique($tzlist);
  asort($tzlist);
  return array_values($tzlist);
}


function simplystrava_register_settings() {
    // Register Settings
    register_setting ( 'simply_strava', 'simply_strava_rider_id' );
    register_setting ( 'simply_strava', 'simply_strava_auth' );
    register_setting ( 'simply_strava', 'simply_strava_timezone' );
    register_setting ( 'simply_strava', 'simply_strava_update_interval' );
    register_setting ( 'simply_strava', 'simply_strava_last_update' );
    register_setting ( 'simply_strava', 'simply_strava_color_bgrnd' );
    register_setting ( 'simply_strava', 'simply_strava_color_bars' );
    register_setting ( 'simply_strava', 'simply_strava_color_text' );
    register_setting ( 'simply_strava', 'simply_strava_logo' );
    register_setting ( 'simply_strava', 'simply_strava_units' );
}

// Inline css for admin screens
function simplystrava_admin_css () { ?>
<style type="text/css" >
.simplystrava_admin {padding-top: 15px;}
.simplystrava_admin .setting {display:block;padding: 1em;}
.simplystrava_admin .setting p.label_title {font-size:12px;font-weight:bold;display:block;margin-bottom:10px;}
.simplystrava_admin .setting label.no_bold {font-weight:normal;}
.simplystrava_admin .setting span.slim {width:200px;float:left;display:block;margin: 1px;padding: 3px;}
.simplystrava_admin .setting span.mesg {width:300px;float:left;isplay:block;margin: 1px;padding: 3px;}
.simplystrava_admin .setting p.desc {font-size:12px;font-style:italic;text-indent:10px; text-align:left;}
.simplystrava_admin .setting p.mesg {font-size:14px;text-align:left; padding-top: 20px;}
</style>

<?php }

add_action('admin_head', 'simplystrava_admin_css');

//html for settings form
function simplystrava_settings_page() { 

$tmptzlist = createTZlist();
$i=0;

foreach ($tmptzlist as $tz) 
    $tzlist[$i++] = $tz;

$plug_dir = WP_PLUGIN_DIR . '/' . plugin_basename(dirname(__FILE__)) . '/';

if (is_file($plug_dir . 'simply_strava.png')) {
    $jstoken = filemtime($plug_dir . 'simply_strava.png');
} else {
    $jstoken = filemtime($plug_dir . 'simplystrava.php');
}

?>

<div class="wrap simplystrava_admin">
    <div id="icon-options-general" class="icon32">
        <br> 
    </div>
    <h2>Simply Strava Settings</h2>

    <script type="text/javascript">

    function UpdateAuth(json) {
        jQuery.each(json, function (key, val) {
            if (key == 'error') {
                alert ("Authorization Failed:" + val);
            }
            if (key == 'id') {
                jQuery("#simply_strava_rider_id").val(val);
            }
            if (key == 'token') {
                jQuery("#simply_strava_auth").val(val);
            }
        });
    }

    function stravaAuth() {
        jQuery.getJSON(
             "<?php echo WP_PLUGIN_URL . '/' . plugin_basename(dirname(__FILE__)) . '/' . 'simplystrava_auth.php'; ?>",
             { email: jQuery("#simply_strava_user").val(), password: jQuery("#simply_strava_pwd").val(), auth: <?php echo $jstoken ?> },
             UpdateAuth
        );
    }

    </script>

    <form method="post" action="options.php">
        <?php settings_fields('simply_strava'); ?>

        <div class="setting">
            <span class="slim"><p class="label_title"><?php _e('Strava Username:', 'simplystrava') ?></p></span>
            <input name="simply_strava_user" type="text" id="simply_strava_user"
                value="" /></p>
            <p class="desc"><?php _e('Enter your Strava username') ?></p>

            <span class="slim"><p class="label_title"><?php _e('Strava Password:', 'simplystrava') ?></p></span>
            <input name="simply_strava_pwd" type="password" id="simply_strava_pwd"
                value="" /></p>
            <p class="desc"><?php _e('Enter your Strava password') ?></p>

            <p class="mesg"><?php _e('Use the button below to obtain your Strava numeric ID and authorization token automatically.') ?><br>
            <?php _e('Your username and password will not be stored on the server.') ?><br>
            <?php _e('Be sure to save the settings after the fields have been autopoulated.') ?></p>
            <p class="setting">
            <input type="button" class="button-primary"  onClick="stravaAuth()"
                value="<?php _e('Autopopulate ID & Token', 'simplystrava') ?>" />
            </p><hr>
           
            <p class="label_title"><?php _e('Strava ID:', 'simplystrava') ?></p>
            <p><label class="no_bold" for="simply_strava_rider_id"><span class="slim">
                <?php _e('Strava ID', 'simplystrava') ?></span>
            <input name="simply_strava_rider_id" type="text" id="simply_strava_rider_id"
                value="<?php form_option('simply_strava_rider_id'); ?>" /></label></p>
            <p class="desc"><?php _e('Enter your numeric Strava ID#') ?></p>

            <p class="label_title"><?php _e('Strava Token:', 'simplystrava') ?></p>
            <p><label class="no_bold" for="simply_strava_auth"><span class="slim">
                <?php _e('Strava Auth', 'simplystrava') ?></span>
            <input name="simply_strava_auth" type="text" id="simply_strava_auth"
                value="<?php form_option('simply_strava_auth'); ?>" /></label></p>
            <p class="desc"><?php _e('Enter your Strava Auth Code') ?></p>

            <p class="label_title"><?php _e('Strava Units:', 'simplystrava') ?></p>
            <p><label class="no_bold" for="simply_strava_units"><span class="slim">
                <?php _e('Strava Units (Miles or Kilometers)', 'simplystrava') ?></span>
            <select name="simply_strava_units" id="simply_strava_units" >
                <?php
                if (get_option('simply_strava_units') == 'imperial') { ?>
                     <option value='imperial' selected='true'>Miles</option>
                     <option value='metric'>Kilometers</option>
                <?php } else { ?>
                     <option value='imperial'>Miles</option>
                     <option value='metric' selected='true'>Kilometers</option>
                <?php } ?>
            </select></label></p>
            <p class="desc"><?php _e('Enter Kilometers or Miles') ?></p>

            <p class="label_title"><?php _e('Strava Timezone:', 'simplystrava') ?></p>
            <p><label class="no_bold" for="simply_strava_timezone"><span class="slim">
                <?php _e('Strava Timezone', 'simplystrava') ?></span>
            <select name="simply_strava_timezone" id="simply_strava_timezone" >
                <?php
	        foreach($tzlist as $item) {
	            $selected = (get_option('simply_strava_timezone')==$item) ? 'selected="true"' : '';
                    echo "<option value='$item' $selected>$item</option>";
                }
                ?>
            </select></label></p>
            <p class="desc"><?php _e('Enter your Strava timezone') ?></p>


            <p class="setting">
            <input type="submit" class="button-primary"
                value="<?php _e('Save Simply Strava Settings', 'simplystrava') ?>" />
            </p>

        </div>

    </form> 

</div>

<?php }

function simplystrava_enqueue_styles() {

    // url to stylesheet
    $simplystrava_css_url= WP_PLUGIN_URL . '/' . plugin_basename(dirname(__FILE__)) . '/simplystrava-widget.css';

    // register and enqueue style
    wp_register_style('simplystrava_styles', $simplystrava_css_url);
    wp_enqueue_style( 'simplystrava_style');

}

add_action ( 'wp_print_styles', 'simplystrava_enqueue_styles' );

/* Register the widget */
function simplystrava_register_widget() {
    register_widget( 'Simply_Strava_Widget' );
}

/* Begin Widget Class */
class Simply_Strava_Widget extends WP_Widget {

    private $debug = 0;
    private $simplystrava_dir;
    private $simplystrava_url;


/* Widget setup */
function Simply_Strava_Widget() {
    $widget_ops = array('classname' => 'simplystrava_widget', 'description' => __( 'Weekly Strava Activity', 'simplystrava') );
    // widget code goes here
    parent::WP_Widget( false, $name = 'Simply Strava', $widget_ops );
}

/* Display the widget */
function widget ($args, $instance) {

    //get widget arguments
    $this->simplystrava_dir = WP_PLUGIN_DIR . '/' . plugin_basename(dirname(__FILE__)) . '/';
    $this->simplystrava_url = WP_PLUGIN_URL . '/' . plugin_basename(dirname(__FILE__)) . '/';

    if ($this->debug) echo "DIR: $this->simplystrava_dir<br>\n";
    
    extract($args);
    //get widget title from instance variable
    $title = apply_filters('widget_title', $instance['title']);

    //insert before widget markup
    echo $before_widget;

    //if theres a title, echo it.
    if ( $title )
    echo $before_title . $title . $after_title;

    $strv_id = get_option('simply_strava_rider_id');
    $strv_token = get_option('simply_strava_auth');
    $strv_tz = get_option('simply_strava_timezone');
    $strv_units = get_option('simply_strava_units');


    if ($this->debug) {
        echo "Units are $strv_units<br>\n";
    }

    if (!$strv_id || trim($strv_id) == "" || !$strv_token || trim($strv_token) == "") {
        echo "Strava ID & Token Not Set<br>\n";
    }

    if (!$strv_timezone) {
        $strv_timezone = 'EST';
    }

    $stravaObj = new SimplyStrava ($strv_id, $strv_token, $strv_units);

    $ride_index = $stravaObj->list_rides();

    if ($ride_index) {
        reset($ride_index);

        $week = 0;
        date_default_timezone_set($strv_tz);
        $week0start = strtotime("last Sunday") + 86400;
        if ($this->debug) echo "Week0 Start: $week0start<br>\n";
        $week1start = $week0start - (86400*7);
        $week2start = $week1start - (86400*7);
        $week3start = $week2start - (86400*7);

        $date_label[0] = date("n/j", $week0start);
        $date_label[1] = date("n/j", $week1start);
        $date_label[2] = date("n/j", $week2start);
        $date_label[3] = date("n/j", $week3start);

        $weekly_distance = array(0, 0, 0, 0);
        $total_distance = 0;
        $total_time = 0;

        if ($this->debug) echo "Count of rides: " . count($ride_index) . "<br>\n";
 
        while(list ($id, $data) = each($ride_index)) {
            $id = $data['id'];
            if ($this->debug) echo "ID: $id<br>\n";

            $ride_details = $stravaObj->ride_details($data['id']);
            $start_date = $ride_details['start_date_local'];
            $distance = $ride_details['distance'];
            if ($this->debug) {
                echo "Ride distance: $distance<br>\n";
                echo "StartDate: $start_date<br>\n";
            }

            if ($start_date > $week0start) {
                $weekly_distance[0] += $distance;
            }
            if (($start_date < $week0start) && ($start_date > $week1start)) {
                $weekly_distance[1] += $distance;
            }
            if (($start_date < $week1start) && ($start_date > $week2start)) {
                $weekly_distance[2] += $distance;
            }
            if (($start_date < $week2start) && ($start_date > $week3start)) {
                $weekly_distance[3] += $distance;
            }
            if ($start_date < $week3start) {
                break;
            }
        }

        $columns = count ($weekly_distance);

        $width = 200;
        $height = 300;

        $padding = 5;
        $hoffset = 30;
        $voffset = 115;

        $column_width = ($width - $hoffset) / $columns;

        $im        = imagecreate($width,$height);
        $gray      = imagecolorallocate ($im,0x33,0x33,0xff);
        $gray_lite = imagecolorallocate ($im,0xee,0xee,0xee);
        $gray_dark = imagecolorallocate ($im,0x7f,0x7f,0x7f);
        $white     = imagecolorallocate ($im,0xff,0xff,0xff);


        $strava_im = imagecreatefrompng($this->simplystrava_dir . "strava_200.png");
        $logo_height = imagesy($strava_im) + 5;

        // Fill in the background of the image

        imagefilledrectangle($im,0,0,$width,$height,$white);
        imagecopy($im, $strava_im, 0, 0, 0, 0, imagesx($strava_im), imagesy($strava_im));
    
        if ($strv_units == 'metric') {
            $label = "Weekly Strava Distance (Km)";
        } else {
            $label = "Weekly Strava Mileage";
        }
        $label_x = (imagesx($im) - 7 * strlen($label)) / 2;
        imagestring($im, 3, $label_x, $height - 25, $label, $gray_dark);

        $maxv = 0;

        // Calculate the maximum value we are going to plot

        for($i=0;$i<$columns;$i++) {
            if ($this->debug) printf ("Week %i: %f<br>\n", $i, $weekly_distance[$i]);
            $maxv = max($weekly_distance[$i],$maxv);
        }

        // Now plot each column
        
        $scale = 25;
        if ($maxv < 100) {
             $scale = 10;
        }
        if (($maxv >= 100) && ($maxv < 300)) {
            $scale = 25;
        }
        if ($maxv >= 300) {
            $scale = 100;
        }

        $maxv = ($maxv + $scale) - ($maxv % $scale) + 2;

        for($i=0;$i<$columns;$i++)
        {
             $column_height = (($height - $voffset) / 100) * (( $weekly_distance[$i] / $maxv) *100);

             $x1 = ($width - $padding) - ($i*$column_width);
             $y1 = ($height-$column_height - $voffset) + $logo_height;
             $x2 = ($width - $padding) - ((($i+1)*$column_width)-$padding);
             $y2 = ($height - $voffset) + $logo_height;

             imagefilledrectangle($im,$x1,$y1,$x2,$y2,$gray);

             // This part is just for 3D effect

             imageline($im,$x1,$y1,$x1,$y2,$gray_lite);
             imageline($im,$x1,$y2,$x2,$y2,$gray_lite);
             imageline($im,$x2,$y1,$x2,$y2,$gray_dark);

             imagestring($im, 2, $x2 + $padding, ($height - $voffset) + 5 + $logo_height, $date_label[$i], $gray_dark);
        }

        for ($i = 0; $i < $maxv ; $i += $scale) {
            $y = (($height - $voffset) / 100) * (( $i / $maxv) * 100);
            imageline($im, $hoffset - $padding, ($height - $voffset - $y) + $logo_height, $width, ($height - $voffset - $y) + $logo_height, $gray_dark);
            imagestring($im, 2, 5, ($height - $voffset - $y - 5) + $logo_height, sprintf("%d", $i), $gray_dark);
        }

        // Send the PNG header information. Replace for JPEG or GIF or whatever

        imagepng($im,$this->simplystrava_dir . "simply_strava.png");
        $imageurl = $this->simplystrava_url . "simply_strava.png";

    }

    if ((!$this->debug ) && (is_file($this->simplystrava_dir . "simply_strava.png"))) echo '<img class="simplystrava_widget" src="' . $imageurl . '">';
    

    echo $after_widget;

}

/* Update the widget settings, just the title in this case */
function update( $new_instance, $old_instance ) {
    $instance = $old_instance;
    $instance['title'] = strip_tags($new_instance['title']);
    return $instance;
}

//form to display widget settings. Allows users to set title of widget
function form ( $instance ) {
    $title = esc_attr($instance['title']);
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">
            <?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                name="<?php echo $this->get_field_name('title'); ?>"
                type="text" value="<?php echo $title; ?>" />
        </p>
        <?php

}
}

/* Load the Widget */
add_action( 'widgets_init', 'simplystrava_register_widget' );

?>
