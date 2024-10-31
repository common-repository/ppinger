<?php
/*
Plugin Name: PPinger
Plugin URI: http://cleverplugins.com
Description: Pings your blog to Pingomatic automatically.
Version: 3.1
Author: http://cleverplugins.com
Author URI: http://cleverplugins.com
Min WP Version: 3.4
Max WP Version: 3.9.2
Update Server: http://cleverplugins.com


== Changelog ==

= 3.1 = 
* Code fixes based on feedback from nekstrebor in support forum. Thank you :-)
* Added last message note - suggest from nekstrebor in support forum.
* Removed interval option - runs automatically twice a day.
* Code cleanup - Lots of old and unecessary code removed


= 3.0 = 
* Rewrite entire plugin
* WordPress 3.9 compatible


= 2.3 =
* WordPress 3.0 Compatible
* Backlinks can be turned on and off in settings.
* Ad service location updated.
* Dashboard Widget can now be turned on and off in the settings page
* Updated help/info page inside plugin.


License:

  Copyright 2010-2014 cleverplugins.com (admin@cleverplugins.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

  class ppinger {

  	private $_plugin_path;
  	private $_plugin_url;

  	function __construct() {
  		$this->_plugin_path = plugin_dir_path( __FILE__ );
  		$this->_plugin_url = plugin_dir_url( __FILE__ );
  		add_action('admin_menu', array(&$this,'add_pages') );
  		add_action( 'init', array( $this, 'init' ) );
  		add_action( 'admin_init', array(&$this,'register_settings') );
  		add_action('ppinger_cron', array(&$this,"_ping"));
  		add_filter('wp_loaded', array(&$this,'ppinger_load_textdomain') );
  		register_activation_hook( __FILE__, array( $this, 'activate' ) );
  		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
	} // end constructor


	public function init() {
		$nextscheduled = wp_next_scheduled('ppinger_cron');
		if ( ! wp_next_scheduled('ppinger_cron')) {
			wp_schedule_event( time(), 'twicedaily', 'ppinger_cron');
			$nextscheduled = wp_next_scheduled('ppinger_cron');
		}


	}

	function ppinger_load_textdomain() {
		load_plugin_textdomain( 'ppinger', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	function register_settings() {
		register_setting( 'ppinger', 'ppinger_activated'); 
		register_setting( 'ppinger', 'ppinger_interval');
	} 

	function add_pages() {
		add_options_page('PPinger', 'PPinger', 'manage_options', 'ppinger', array( $this, 'settings_page' ));
	}


function settings_page() {
	?>
	<div class="wrap">  
		<div style="float:right;">
			<a href="http://cleverplugins.com/" target="_blank"><img src='<?php echo plugin_dir_url(__FILE__); ?>cleverpluginslogo.png'></a>
		</div>
		<h2>PPinger</h2>
		<p><?php _e('PPinger - Pingomatic Pinger - Sends pings to pingomatic.com automatically twice a day.','ppinger');?></p>
		<h3><?php _e('Settings','ppinger'); ?></h3>
		<form method="post" action="options.php"> 
			<?php
			settings_fields('ppinger');
			do_settings_sections( 'ppinger' );

			$ppinger_activated = esc_attr( get_option( 'ppinger_activated' ) );

			$ppinger_interval = esc_attr( get_option( 'ppinger_interval' ) );

			$ppinger_lastmessage = esc_attr( get_option( 'ppinger_lastmessage' ) );
			if ($ppinger_lastmessage) {
				?>
				<div class="updated fade"><?php _e('Last log note:','ppinger');?> <?php echo $ppinger_lastmessage; ?></div>
				<?php
			}
			?>
			<table class="form-table">
				<tr valign="top">	
					<th scope="row" valign="top">
						<?php _e('Activate Ping?', 'ppinger'); ?>
					</th>
					<td>
						<input type="checkbox" id="ppinger_activated" name="ppinger_activated" value="on" <?php if ($ppinger_activated) echo " checked='checked'"; ?>/><br>
						<label for="ppinger_activated"><?php _e('Turns the automatic pinging on and off.','ppinger'); ?></label>
					</td>
				</tr>			
			</table>
			
			<p><?php _e('The interval you specify will then be used to dynamically create a seperate iframe pointing to the ping-url, pushing spider robots to your site to help with indexation. Since it uses an iframe to send the pings, it will not cause extra loading time for your website.','ppinger');?></p>

			<?php
			submit_button( __('Save Changes','ppinger'));
			?>

		</form>
	</div><!-- .wrap -->
	<?php
}


public function deactivate( $network_wide ) {
	$timestamp = wp_next_scheduled( 'ppinger_cron' );
	wp_unschedule_event( $timestamp, 'ppinger_cron' );

	} // end deactivate


	function _ping() {



		/*
		
	http://pingomatic.com/ping/?title=cleverplugins.com&blogurl=http%3A%2F%2Fcleverplugins.com%2F&rssurl=http%3A%2F%2Fcleverplugins.com%2Ffeed%2F&chk_weblogscom=on&chk_blogs=on&chk_feedburner=on&chk_newsgator=on&chk_myyahoo=on&chk_pubsubcom=on&chk_blogdigger=on&chk_weblogalot=on&chk_newsisfree=on&chk_topicexchange=on&chk_google=on&chk_tailrank=on&chk_skygrid=on&chk_collecta=on&chk_superfeedr=on



	http://pingomatic.com/ping/
	?title=cleverplugins.com
	&blogurl=http%3A%2F%2Fcleverplugins.com%2F
	&rssurl=http%3A%2F%2Fcleverplugins.com%2Ffeed%2F

	&chk_feedburner=on
	&chk_newsgator=on
	&chk_myyahoo=on
	&chk_pubsubcom=on
	&chk_blogdigger=on
	&chk_weblogalot=on
	&chk_newsisfree=on
	&chk_topicexchange=on
	&chk_google=on
	&chk_tailrank=on
	&chk_skygrid=on
	&chk_collecta=on
	&chk_superfeedr=on



		 */
	$ppinger_activated = esc_attr( get_option( 'ppinger_activated' ) );

	$ppinger_interval = esc_attr( get_option( 'ppinger_interval' ) );

	$rss_url 	= get_bloginfo('rss_url');

	$chkarray=array(
		"&chk_weblogscom=on",
		"&chk_blogs=on",
		"&chk_feedburner=on",
		"&chk_newsgator=on",
		"&chk_myyahoo=on",
		"&chk_pubsubcom=on",
		"&chk_blogdigger=on",
		"&chk_newsisfree=on",
		"&chk_topicexchange=on",
		"&chk_google=on",
		"&chk_tailrank=on",
		"&chk_skygrid=on",
		"&chk_collecta=on"
		);		

	$chks=rand(1,count($chkarray));
	$rand_index = array_rand($chkarray,$chks); 
	$chklist='';
	for ( $counter = 1; $counter <= $chks; $counter += 1) {
		$chklist .=$chkarray[$rand_index[$counter]];
	}

	$pingurl="http://pingomatic.com/ping/?title=".urlencode(get_bloginfo('name'))."&blogurl=".urlencode(site_url())."&rssurl=".urlencode($rssurl)."$chklist";

	wp_remote_get( $pingurl );

	$userip=$_SERVER['REMOTE_ADDR']; 

	$lastmessage = sprintf( __( 'A ping to Pingomatic made by a user from IP %s at %s.' , 'ppinger'),$userip, date_i18n( get_option( 'date_format' ).' '.get_option( 'time_format' ),current_time( 'timestamp' ) ));
		;

	update_option('ppinger_lastmessage',$lastmessage);

	} // _ping()

} // end class

new ppinger();
