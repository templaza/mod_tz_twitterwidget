<?php
	/**
	* @package		WDS Twitter Widget
	* @copyright	Web Design Services. All rights reserved. All rights reserved.
	* @license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
	*/

	// no direct access
	defined('_JEXEC') or die;
jimport('joomla.filesystem.folder');
require_once dirname(__FILE__).'/lib/twitteroauth/twitteroauth.php';
require_once dirname(__FILE__).'/lib/twitter-text/Autolink.php';
require_once dirname(__FILE__).'/lib/twitter-text/Extractor.php';
require_once dirname(__FILE__).'/lib/twitter-text/HitHighlighter.php';
 if(!class_exists('modTzTwitterWidgetHelper',false)) {
	class modTzTwitterWidgetHelper
	{
		private $data;
		private $cacheFile;

		public function __construct() {
			$this->cacheFile = dirname(__FILE__) . '/cache';
		}

		public function getData($params) {

			$twitterConnection = new TwitterOAuth(
				trim($params->get('consumer_key', '')), // Consumer Key
				trim($params->get('consumer_secret', '')), // Consumer secret
				trim($params->get('access_token', '')), // Access token
				trim($params->get('access_secret', ''))	// Access token secret
			);

			if($params->get('type', 1)) {
				$twitterData = $twitterConnection->get(
					'statuses/user_timeline',
					array(
						'screen_name' => trim($params->get('username', 'twitter')),
						'count' => trim($params->get('count', 5)),
					)
				);
			}
			else {
				$twitterData = $twitterConnection->get(
					'search/tweets',
					array(
						'q' => trim($params->get('query', '')),
						'count' => trim($params->get('count', 5)),
					)
				);
				if(!isset($twitterData->errors))
					$twitterData = $twitterData->statuses;
			}

			// if there are no errors
//            var_dump($twitterData ); die();
			if(!isset($twitterData->errors)) {
				$tweets = array();
					if(!isset($twitterData->errors)) {
                        if(isset($twitterData)){
                            foreach($twitterData as $tweet) {
                                $tweetDetails = new stdClass();
                                $tweetDetails->text = Twitter_Autolink::create($tweet->text)->setNoFollow(false)->addLinks();
                                $tweetDetails->time = $this->getTime($tweet->created_at);
                                $tweetDetails->id = $tweet->id_str;
                                $tweetDetails->screenName = $tweet->user->screen_name;
                                $tweetDetails->displayName = $tweet->user->name;
                                $tweetDetails->profileImage = $tweet->user->profile_image_url_https;

                                $tweets[] = $tweetDetails;
                            }
                        }
				}
				$data = new stdClass();
				$data->tweets = $tweets;

				$this->data = $data;


				$this->setCache();

				return $data;
			}
			else {
//                die();
				return $this->getCache();
			}
		}
        public static function getDocument($key=false)
        {
            self::getInstance()->document = JFactory::getDocument();
            $doc = self::getInstance()->document;
            if( is_string($key) ) return $doc->$key;

            return $doc;
        }
        public static function getInstance()
        {
            if( !self::$_instance ){
                self::$_instance = new self();
                self::getInstance()->getDocument();
                self::getInstance()->getDocument()->generate = self::getInstance();
            }
            return self::$_instance;
        }

        function addExtraCSS($data='', $prefix='css') {
            $outputpath = JPATH_ROOT . '/modules/mod_tz_twitterwidget/css/custom';
            $outputurl ='/modules/mod_tz_twitterwidget/css/custom';

            if (!JFile::exists($outputpath)){
                JFolder::create($outputpath);
                @chmod($outputpath, 0755);
            }

            $filename = $prefix.'-' . substr(md5($data), 0, 15) . '.css';
            $filepart = $outputpath . '/' . $filename;
            $filetime = JFile::exists($filepart) ? @filemtime($filepart) : -1;
            $rebuild = $filetime < 0; //filemtime == -1 => rebuild

            if($rebuild){
                JFile::write($filepart, $data);
                @chmod($filepart, 0644);
            }
            $doc = JFactory::getDocument();
            $filepath = JUri::base().'/modules/mod_tz_twitterwidget/css/custom/'.$filename.'';
            $doc->addStyleSheet($filepath);
            return true;
        }
        function hex2rgba($color, $opacity = false) {

            $default = 'rgb(0,0,0)';

            //Return default if no color provided
            if(empty($color))
                return $default;

            //Sanitize $color if "#" is provided
            if ($color[0] == '#' ) {
                $color = substr( $color, 1 );
            }

            //Check if color has 6 or 3 characters and get values
            if (strlen($color) == 6) {
                $hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
            } elseif ( strlen( $color ) == 3 ) {
                $hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
            } else {
                return $default;
            }

            //Convert hexadec to rgb
            $rgb =  array_map('hexdec', $hex);

            //Check if opacity is set(rgba or rgb)
            if($opacity){
                if(abs($opacity) > 1)
                    $opacity = 1.0;
                $output = 'rgba('.implode(",",$rgb).','.$opacity.')';
            } else {
                $output = 'rgb('.implode(",",$rgb).')';
            }

            //Return rgb(angel) color string
            return $output;
        }
		
		public function addStyles($params,$mod_ID) {
//            die();

			$styles = '';
			$border = $params->get('border_color', 'transparent');
			$styles .= '#TzMod'.$mod_ID.' .tztwd-tweets a {color: ' . $params->get('link_color', '#0084B4') . '}';
			$styles .= '#TzMod'.$mod_ID.' .tztwd-container {background-color: ' . $params->get('bgd_color', 'transparent') . '}';
            if($border !='transparent'){
                $styles .= '#TzMod'.$mod_ID.' .tztwd-header {border-bottom-color: ' . $border . '}';
                $styles .= '#TzMod'.$mod_ID.' .tztwd-container {border-color: ' . $border . '}';
                $styles .= '#TzMod'.$mod_ID.' .tztwd-copyright {border-top-color: ' . $border . '}';
                $styles .= '#TzMod'.$mod_ID.' .tztwd-tweet-container {border-bottom-color: ' . $border . '}';
            } else{
                $styles .= ' .tztwd-header, .tztwd-container, .tztwd-copyright,.tztwd-tweet-container{border:none !important;}  ';
            }
			$styles .= '#TzMod'.$mod_ID.' .tztwd {color: ' . $params->get('text_color', '#333') . '}';
			$styles .= '#TzMod'.$mod_ID.' a .tztwd-display-name {color: ' . $params->get('header_link_color', '#333') . '}';
			$styles .= '#TzMod'.$mod_ID.' a .tztwd-screen-name {color: ' . $params->get('header_sub_color', '#666') . '}';
			$styles .= '#TzMod'.$mod_ID.' a:hover .tztwd-screen-name {color: ' . $params->get('header_sub_hover_color', '#999') . '}';
			$styles .= '#TzMod'.$mod_ID.' .tztwd-header, #TzMod'.$mod_ID.' .tztwd-header a {color: ' . $params->get('search_title_color', '#333') . '}';
			if($params->get('width', '')) {
				$styles .= '#TzMod'.$mod_ID.' .tztwd-container {width: ' . intval($params->get('width', '')) . 'px;}';
			}
			if($params->get('height', '')) {
				$styles .= '#TzMod'.$mod_ID.' .tztwd {height: ' . intval($params->get('height', '')) . 'px; overflow: auto;}';
			}
			
			$doc = JFactory::getDocument();
			$doc->addStyleSheet(JURI::base() . 'modules/mod_tz_twitterwidget/css/tztwitterwidget.css');
//            var_dump($styles); die();
//			$doc->addStyleDeclaration($styles);
            if (trim($styles)) {
                modTzTwitterWidgetHelper::addExtraCSS($styles,'custom');
            }

		}

		private function setCache() {
//            var_dump($this->data);
//            die();
			JFile::write($this->cacheFile, $this->data);
		}

		private function getCache(){
			if(file_exists($this->cacheFile)) {
				$cache = JFile::read($this->cacheFile);
				if($cache !== false)
					return unserialize(JFile::read($this->cacheFile));
			}
			return false;
		}

		// parse time in a twitter style
		private function getTime($date)
		{
			$timediff = time() - strtotime($date);
			if($timediff < 60)
				return $timediff . 's';
			else if($timediff < 3600)
				return intval(date('i', $timediff)) . 'm';
			else if($timediff < 86400)
				return round($timediff/60/60) . 'h';
			else
				return JHTML::_('date', $date, 'M d');
		}
	}
}