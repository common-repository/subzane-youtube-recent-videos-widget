<?php
/*  Copyright 2011  Andreas Norman

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Generalx Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

		Plugin Name: Norman YouTube Plugin
		Plugin URI: http://www.andreasnorman.se/norman-youtube-plugin/
		Description: This plugin can allows you to display a thumbnail list of YouTube videos in your sidebar. You can also add custom lists to your posts and pages using shortcode.
		Author: Andreas Norman
		Version: 1.9
		Author URI: http://www.andreasnorman.se
*/

if (!class_exists("NormanYouTubePlugin")) {
	class NormanYouTubePlugin {
		
		function getVideos($num, $url, $type, $sortorder = 'published') {
			if ($num > 0) {
				$num_param = '&max-results='.$num;
			} else {
				$num_param = '';
			}

			if (!empty($url)) {
				if ($type=='user') {
					$url = 'http://gdata.youtube.com/feeds/api/videos?v=2&author='.$url.$num_param.'&orderby='.$sortorder;
				} else if ($type=='favorites') {
					$url = 'http://gdata.youtube.com/feeds/api/users/'.$url.'/favorites?v=2'.$num_param.'&orderby='.$sortorder;
					} else if ($type=='playlist') {
						$url = 'http://gdata.youtube.com/feeds/api/playlists/'.$url.'?v=2'.$num_param;
				} else {
					$url = 'http://gdata.youtube.com/feeds/api/videos?q='.$url.'&orderby='.$sortorder.$num_param.'&v=2';
				}

				$sxml = simplexml_load_file($url);
				$i = 0;
				$videoobj;

				foreach ($sxml->entry as $entry) {
					if ($i == $num && !empty($num_param)) {
						break;
					}
					// get nodes in media: namespace for media information
					$media = $entry->children('http://search.yahoo.com/mrss/');

					if ($media->group->player && $media->group->player->attributes() && $media->group->thumbnail && $media->group->thumbnail[0]->attributes()) {
						// get video player URL
						$attrs = $media->group->player->attributes();
						$videoobj[$i]['url'] = (string) $attrs['url'];

						// get video thumbnail
						$attrs = $media->group->thumbnail[0]->attributes();

						$videoobj[$i]['thumb'] = (string) $attrs['url']; 
						$videoobj[$i]['title'] = (string) $media->group->title;
						$i++;
					}
		    }
			} else {
				return null;
			}
			return $videoobj;
		}

		function admin_add_page() {
			add_options_page('Norman Youtube Plugin', 'Norman Youtube Plugin', 'manage_options', 'Norman_youTube_plugin_page', array('NormanYouTubePlugin', 'options_page'));
		}
		
		function admin_init() {
			register_setting( 'Norman_youtube_options', 'settings' );
		}
		
		function options_page() {
			?>
			<div class="wrap">
			<h2>Norman Youtube Settings</h2>
			<p>General settings for Norman Youtube Widget</p>
			<form action="options.php" method="post">
				<?php 
					settings_fields('Norman_youtube_options');
					$options = get_option('settings'); 
					$hd = empty($options['hd']) ? 0 : $options['hd'];
					$lightbox = empty($options['lightbox']) ? 0 : $options['lightbox'];
				?>

				<table class="form-table" border="0" cellspacing="5" cellpadding="5">
					<tbody>
						<tr valign="top">
							<th scope="row"><label for="settings[lightbox]">Active lightbox support:</label></th>
							<td>
								<input id="settings[lightbox]" name="settings[lightbox]" size="40" value="1" <?php echo ($lightbox==1?'checked="checked"':''); ?> type="checkbox" />
								<span class="description">Launches YouTube Links into <a href="http://fancyapps.com/fancybox/">fancyBox Lightbox</a> instead of a new page. Only affects links created by this plugin.</span>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="settings[hd]">Prefer HD video:</label></th>
							<td>
								<input id="settings[hd]" name="settings[hd]" size="40" type="checkbox" value="1" <?php echo ($hd==1?'checked="checked"':''); ?> />
								<span class="description">Uses HD quality on videos when available.</span>
							</td>
						</tr>
					</tbody>
				</table>

				<p class="submit">
						<input type="submit" class="button-primary" name="wp_paginate_save" value="<?php esc_attr_e('Save Changes'); ?>">
				</p>
			</form>
			<h2>Shortcode Usage</h2>
			<h4>Example:	</h4>
			<p>[sz-youtube value="Norman" type="favorites" max="5" sortorder="viewCount"]</p>
			<h4>Parameters:	</h4>
			<ul>
				<li><strong>value</strong> = a username or a tag</li>
				<li><strong>type</strong> = (tag/user/favorites)</li>
				<li><strong>max</strong> = Max number of videos to list</li>
				<li><strong>sortorder</strong> = Order in which to present (published/relevance/viewCount/rating)</li>
				<li><strong>autoplay</strong> = Autoplay videos in lightbox or not (1/0)</li>
				<li><strong>related</strong> = Display related videos or not (1/0)</li>
				<li><strong>lightbox</strong> = Use lightbox or not (1/0)</li>
				<li><strong>aspect</strong> = Aspect ratio (4:3, 16:9 or 16:10)</li>
				<li><strong>width</strong> = Width of the video. Height is calculated from width and aspect</li>
				<li><strong>hd</strong> = HD Video if available (1/0)</li>
				<li><strong>fullscreen</strong> = Video in fullscreen or not (1/0)</li>
			</ul>
			<p><strong>Notice:</strong> The height is automatically calculated from the width and aspect ration.</p>
			
			<h2>Need Support?</h2>
			<p>For questions, issues or feature requests, please post them as <a href="http://www.andreasnorman.se/norman-youtube-plugin/">comments on my plugin page</a></p>
			<h2>Like To Contribute?</h2>
			<p>If you would like to contribute, the following is a list of ways you can help:</p>
			<ul>
				<li>» Blog about or link to Norman YouTube Plugin so others can find out about it</li>
				<li>» Report issues, provide feedback, request features, etc.</li>
				<li>» <a href="http://wordpress.org/extend/plugins/subzane-youtube-recent-videos-widget/">Rate the plugin on the WordPress Plugins Page</a></li>
				<li>» <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=LH7UZV983QMWC">Make a donation</a></li>
			</ul>
			<h2>Other Links</h2>
			<ul>
				<li>» <a href="http://twitter.com/andreasnorman">@andreasnorman</a> on Twitter</li>
				<li>» <a href="http://www.andreasnorman.se">andreasnorman.se</a></li>
				<li>» <a href="http://fancyapps.com/fancybox/">fancyBox by Jānis Skarnelis</a></li>
			</ul>
			</div>
			<?php	
		}

		function fixlink($url, $autoplay = 0, $related = 0, $fullscreen = 0, $hd = 0) {
			return 'http://www.youtube.com/embed/'.substr($url, strpos($url, '=')+1, 11).'?autoplay='.$autoplay.'&rel='.$related.'&fs='.$fullscreen.'&hd='.$hd;
		}
		
		function styles () {
			$plugin_url = plugins_url ( plugin_basename ( dirname ( __FILE__ ) ) );

			wp_register_style('Norman_youtube_plugin', $plugin_url . '/style.css');
			wp_enqueue_style( 'Norman_youtube_plugin');

			wp_register_style('NormanYouTubePluginFancybox_style', $plugin_url.'/fancyBox/source/jquery.fancybox.css');
		  wp_enqueue_style( 'NormanYouTubePluginFancybox_style');
		}
		
		function scripts() {
			$plugin_url = plugins_url ( plugin_basename ( dirname ( __FILE__ ) ) );
			
			wp_register_script( 'NormanYouTubePluginJquery_script', $plugin_url.'/fancyBox/lib/jquery-1.7.1.min.js');
		  wp_enqueue_script( 'NormanYouTubePluginJquery_script' );

			wp_register_script( 'NormanYouTubePluginFancybox_script', $plugin_url.'/fancyBox/source/jquery.fancybox.pack.js');
		  wp_enqueue_script( 'NormanYouTubePluginFancybox_script' );

			wp_register_script( 'NormanYouTubePlugin_script', $plugin_url.'/script.js');
		  wp_enqueue_script( 'NormanYouTubePlugin_script' );
		}
		
	}
} //End Class NormanYouTubePlugin

if (class_exists("NormanYouTubePlugin")) {
	$NormanYouTubePlugin = new NormanYouTubePlugin();
}

//Actions and Filters   
if (isset($NormanYouTubePlugin)) {
	add_action('widgets_init', create_function('', 'return register_widget("NormanYouTubeWidget");'));
	add_action('wp_enqueue_scripts', array('NormanYouTubePlugin', 'scripts'));
	add_action('wp_print_styles', array('NormanYouTubePlugin', 'styles'));
	add_action('admin_menu', array('NormanYouTubePlugin', 'admin_add_page'));
	add_action('admin_init',  array('NormanYouTubePlugin', 'admin_init'));
	add_shortcode('sz-youtube', 'NormanYoutubeShortcode');
}

function NormanYoutubeShortcode($atts) {
	global $NormanYouTubePlugin;
	
	extract(shortcode_atts(array(
		'max' => '10',
		'type' => 'tag',
		'autoplay' => '0',
		'related' => '0',
		'fullscreen' => '0',
		'lightbox' => '0',
		'aspect' => '4:3',
		'width' => '425',
		'hd' => '0',
		'value' => '',
		'sortorder' => 'published',
	), $atts));
	$videos = $NormanYouTubePlugin->getVideos($max, $value, $type, $sortorder);
	if ($aspect == '4:3') {
		$height = ceil($width / 1.333)+25;
	} else if ($aspect == '16:9') {
		$height = ceil($width / 1.778)+25;
	} else if ($aspect == '16:10') {
		$height = ceil($width / 1.6)+25;
	}

	$str = '';

	foreach ($videos as $video) {
		if ($lightbox == 1) {
			$str .= '<li><div><a class="fancyYouTube fancybox.iframe" href="'.$NormanYouTubePlugin->fixlink($video['url'], $autoplay, $related, $fullscreen, $hd).'">';
		} else {
			$str .= '<li><div><a href="'.$video['url'].'">';
		}
		$str .= '<img alt="'.$video['title'].'" src="'.$video['thumb'].'" /><p>'.$video['title'].'</p></a></div></li>';
	}

	return '
	<div class="sz-youtube-list">
		<ul>
		'.$str.'
		</ul>
	</div>
	';
}


class NormanYouTubeWidget extends WP_Widget {
	protected $NormanYouTubePlugin;
	
	function NormanYouTubeWidget() {
		parent::WP_Widget(false, $name = 'Norman YouTube Widget');	
		$this->NormanYouTubePlugin = new NormanYouTubePlugin();
	}

	function widget($args, $instance) {
    extract( $args );
		$options = get_option('settings'); 

		$hd = empty($options['hd']) ? 0 : $options['hd'];
		$lightbox = empty($options['lightbox']) ? 0 : $options['lightbox'];

		$title = empty($instance['title']) ? 'YouTube Feed' : $instance['title'];
		$num = empty($instance['num']) ? 0 : ($instance['num']);
		$type = empty($instance['type']) ? 'user' : ($instance['type']);
		$sortorder = empty($instance['sortorder']) ? 'published' : ($instance['sortorder']);
		$url = ($instance['url']);
		$aspect = empty($instance['aspect']) ? '4:3' : ($instance['aspect']);
		$fullscreen = empty($instance['fullscreen']) ? 0 : ($instance['fullscreen']);
		$related = empty($instance['related']) ? 0 : ($instance['related']);
		$autoplay = empty($instance['autoplay']) ? 0 : ($instance['autoplay']);
		$width = empty($instance['width']) ? '425' : ($instance['width']);
		$target = empty($instance['target']) ? '' : ($instance['target']);
		if ($aspect == '4:3') {
			$height = ceil($width / 1.333)+25;
		} else if ($aspect == '16:9') {
			$height = ceil($width / 1.778)+25;
		} else if ($aspect == '16:10') {
			$height = ceil($width / 1.6)+25;
		}

		$videos = $this->NormanYouTubePlugin->getVideos($num, $url, $type, $sortorder);

		echo $before_widget;
		echo $before_title . $title . $after_title;
		if ($videos != null) {
			echo '<ul class="sz-videolisting">';
			foreach ($videos as $video) {
				if ($lightbox == 1) {
					echo  '<li><a class="fancyYouTube fancybox.iframe" href="'.$this->NormanYouTubePlugin->fixlink($video['url'], $autoplay, $related, $fullscreen, $hd).'">';
				} else {
					if (!empty($target)) {
						echo  '<li><a target="'.$target.'" href="'.$video['url'].'">';
					} else {
						echo  '<li><a href="'.$video['url'].'">';
					}
				}
				echo  '<img alt="'.$video['title'].'" src="'.$video['thumb'].'" /><span>'.$video['title'].'</span></a></li>';
			}
			echo  '</ul>';
		} else {
			echo  '<p>No videos found</p>';
		}
		echo $after_widget;
  }

	function update($new_instance, $old_instance) {				
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['num'] = strip_tags($new_instance['num']);
		$instance['type'] = strip_tags($new_instance['type']);
		$instance['sortorder'] = strip_tags($new_instance['sortorder']);
		$instance['url'] = strip_tags($new_instance['url']);
		$instance['fullscreen'] = strip_tags($new_instance['fullscreen']);
		$instance['aspect'] = strip_tags($new_instance['aspect']);
		$instance['related'] = strip_tags($new_instance['related']);
		$instance['autoplay'] = strip_tags($new_instance['autoplay']);
		$instance['width'] = strip_tags($new_instance['width']);
		$instance['target'] = strip_tags($new_instance['target']);
		$instance['related'] = strip_tags($new_instance['related']);
		
		return $instance;
	}

	function form($instance) {
		$options = get_option('settings'); 
		$lightbox = empty($options['lightbox']) ? 0 : $options['lightbox'];
		
		$title = empty($instance['title']) ? 'YouTube Feed' : esc_attr($instance['title']);
		$num = empty($instance['num']) ? 0 : esc_attr($instance['num']);
		$type = empty($instance['type']) ? 'user' : esc_attr($instance['type']);
		$sortorder = empty($instance['sortorder']) ? 'published' : esc_attr($instance['sortorder']);
		$url = esc_attr($instance['url']);
		$aspect = empty($instance['aspect']) ? '4:3' : esc_attr($instance['aspect']);
		$fullscreen = empty($instance['fullscreen']) ? 0 : esc_attr($instance['fullscreen']);
		$related = empty($instance['related']) ? 0 : esc_attr($instance['related']);
		$autoplay = empty($instance['autoplay']) ? 0 : esc_attr($instance['autoplay']);
		$width = empty($instance['width']) ? '425' : esc_attr($instance['width']);
		$target = empty($instance['target']) ? '' : esc_attr($instance['target']);
		?>
    <p>
      <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
      <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
    </p>

    <p>
      <label for="<?php echo $this->get_field_id('type'); ?>"><?php _e('Find videos by:'); ?></label> 
			<select id="<?php echo $this->get_field_id('type'); ?>" name="<?php echo $this->get_field_name('type'); ?>">
				<option value="tag" <?php echo ($type=='tag'?'selected="selected"':''); ?> >Keywords</option>
				<option value="playlist" <?php echo ($type=='playlist'?'selected="selected"':''); ?> >Playlist</option>
				<option value="user" <?php echo ($type=='user'?'selected="selected"':''); ?> >Specific Username</option>
				<option value="favorites" <?php echo ($type=='favorites'?'selected="selected"':''); ?> >User favorites</option>
			</select>
    </p>

    <p>
      <label for="<?php echo $this->get_field_id('sortorder'); ?>"><?php _e('Sort order:'); ?></label> 
			<select id="<?php echo $this->get_field_id('sortorder'); ?>" name="<?php echo $this->get_field_name('sortorder'); ?>">
				<option value="published" <?php echo ($sortorder=='published'?'selected="selected"':''); ?> >When published</option>
				<option value="relevance" <?php echo ($sortorder=='relevance'?'selected="selected"':''); ?> >Relevance</option>
				<option value="viewCount" <?php echo ($sortorder=='viewCount'?'selected="selected"':''); ?> >By viewCount</option>
				<option value="rating" <?php echo ($sortorder=='rating'?'selected="selected"':''); ?> >By rating</option>
			</select>
    </p>
<!-- 
		<h3>Info</h3>
		<p>
			<b>Keywords:</b> Will search all video metadata for videos matching the term. Video metadata includes titles, keywords, descriptions, authors usernames, and categories.<br/>
			<b>Specific username:</b> A YouTube username<br/>
			<b>Playlist:</b> The ID of a specific playlist<br/>
			<b>Favorites:</b> The Favorites of a specific user<br/>
		</p>
-->
    <p>
      <label for="<?php echo $this->get_field_id('url'); ?>"><?php _e('Keywords, Username or Playlist ID:'); ?></label> 
      <input class="widefat" id="<?php echo $this->get_field_id('url'); ?>" name="<?php echo $this->get_field_name('url'); ?>" type="text" value="<?php echo $url; ?>" />
    </p>

    <p>
      <label for="<?php echo $this->get_field_id('num'); ?>"><?php _e('Max number of videos (set 0 to get full feed):'); ?></label> 
      <input class="widefat" id="<?php echo $this->get_field_id('num'); ?>" name="<?php echo $this->get_field_name('num'); ?>" type="text" value="<?php echo $num; ?>" />
    </p>

    <p>
      <label for="<?php echo $this->get_field_id('autoplay'); ?>"><?php _e('Autoplay video:'); ?></label> 
      <input <?php echo ($autoplay=='1'?'checked="checked"':''); ?> id="<?php echo $this->get_field_id('autoplay'); ?>" name="<?php echo $this->get_field_name('autoplay'); ?>" type="checkbox" value="1" />
    </p>

    <p>
      <label for="<?php echo $this->get_field_id('related'); ?>"><?php _e('Show related videos:'); ?></label> 
      <input <?php echo ($related=='1'?'checked="checked"':''); ?> id="<?php echo $this->get_field_id('related'); ?>" name="<?php echo $this->get_field_name('related'); ?>" type="checkbox" value="1" />
    </p>
		<?php if ($lightbox == 0) {
		?>
    <p>
      <label for="<?php echo $this->get_field_id('fullscreen'); ?>"><?php _e('Fullscreen:'); ?></label> 
      <input <?php echo ($fullscreen=='1'?'checked="checked"':''); ?> id="<?php echo $this->get_field_id('fullscreen'); ?>" name="<?php echo $this->get_field_name('fullscreen'); ?>" type="checkbox" value="1" />
    </p>

    <p>
      <label for="<?php echo $this->get_field_id('target'); ?>"><?php _e('Target window:'); ?></label> 
			<select id="<?php echo $this->get_field_id('target'); ?>" name="<?php echo $this->get_field_name('target'); ?>">
				<option value="" <?php echo ($target==''?'selected="selected"':''); ?> >None</option>
				<option value="_blank" <?php echo ($target=='_blank'?'selected="selected"':''); ?> >_blank</option>
				<option value="_self" <?php echo ($target=='_self'?'selected="selected"':''); ?> >_self</option>
				<option value="_top" <?php echo ($target=='_top'?'selected="selected"':''); ?> >_top</option>
			</select>
    </p>
		<?php
		} else {
		?>
    <p>
      <label for="<?php echo $this->get_field_id('aspect'); ?>"><?php _e('Aspect ratio:'); ?></label> 
			<select id="<?php echo $this->get_field_id('aspect'); ?>" name="<?php echo $this->get_field_name('aspect'); ?>">
				<option value="4:3" <?php echo ($aspect=='4:3'?'selected="selected"':''); ?> >4:3</option>
				<option value="16:9" <?php echo ($aspect=='16:9'?'selected="selected"':''); ?> >16:9</option>
				<option value="16:10" <?php echo ($aspect=='16:10'?'selected="selected"':''); ?> >16:10</option>
			</select>
    </p>
		
    <p>
      <label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Lightbox Width:'); ?></label> 
      <input class="widefat" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo $width; ?>" />
    </p>
		<?php	
		}
		?>


	  <?php 
	}

}


?>