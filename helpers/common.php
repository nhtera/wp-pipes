<?php
/**
 * @package          WP Pipes plugin - PIPES
 * @version          $Id: common.php 170 2014-01-26 06:34:40Z thongta $
 * @author           wppipes.com
 * @copyright        2014 wppipes.com. All rights reserved.
 * @license          GNU/GPL v3, see LICENSE
 */
defined( 'PIPES_CORE' ) or die( 'Restricted access' );

class ogb_common {
	static function get_params_render( $dir, $file, $values, $xpath = '//config', $control = 'jform', $type = 'plugins', $show_group = false ) {
		$xml_dir = PIPES_PATH . DS . $dir;

		jimport( 'includes.form.form' );
		jimport( 'includes.form.field' );
		jimport( 'includes.html.html' );
		jimport( 'includes.html.select' );
		jimport( 'includes.form.helper' );
		jimport( 'includes.registry.registry' );
		jimport( 'includes.string.string' );
		JForm::addFormPath( $xml_dir );

		JForm::addFieldPath( JPATH_SITE . DS . 'libraries' . DS . 'joomla' . DS . 'form' . DS . 'fields' );
		JForm::addFieldPath( $dir . DS . 'fields' );

		$name    = 'com_wppipes.' . $type;
		$options = array(
			'control'   => $control,
			'load_data' => true
		);

		if ( ! is_file( $xml_dir . DS . $file . '.xml' ) ) {
			return false;
		}
		jimport( 'filesystem.folder' );
		if ( JFolder::exists( $xml_dir . DS . 'elements' ) ) {
			JForm::addFieldPath( $xml_dir . DS . 'elements' );
		}
		if ( JFolder::exists( $xml_dir . DS . 'fields' ) ) {
			JForm::addFieldPath( $xml_dir . DS . 'fields' );
		}
		$form = JForm::getInstance( $name, $file, $options, false, $xpath );

		$values = json_decode( $values );
		$values = array( 'params' => $values );
		$temp   = new JRegistry;
		$temp->loadArray( $values );

		if ( isset( $_GET['x'] ) ) {
			echo "\n\n<br /><i><b>File:</b>" . __FILE__ . ' <b>Line:</b>' . __LINE__ . "</i><br />\n\n";
			$file = $xml_dir . DS . $file . '.xml';
			echo '<br />' . $file . ' [ XML file ]';
			$a = is_file( $file );
			echo "[ file exist: ";
			var_dump( $a );
			echo " ]";
			echo '<pre>';
			print_r( $temp );
			echo '</pre>';
		}

		$form->bind( $temp );

		$fieldSets = $form->getFieldsets();
		$li        = array();
//		foreach ($fieldSets as $name => $fieldSet) {

		/*$label = empty($fieldSet->label) ? 'COM_CONFIG_'.$name.'_FIELDSET_LABEL' : $fieldSet->label;
		$li	.= JHtml::_('tabs.panel', JText::_($label), 'publishing-details');
		if (isset($fieldSet->description) && !empty($fieldSet->description)) {
			$li	.= '<p class="tab-description">'.JText::_($fieldSet->description).'</p>';
		}*/
//			foreach ($form->getFieldset($name) as $field){
//				$li[]	= '<li>'.($field->hidden?'':$field->label).$field->input.'</li>';
//			}
//		}

		$element = '';
		// load basic fieldset
		$plugin_type = explode( ".", $type );
//		$i           = 1;
		foreach ( $form->getFieldset( 'basic' ) AS $field ) {
			if ( $field->class == 'fullwidth' ) {
				$li_class = 'col-md-12';
//				$i ++;
			} else {
				$li_class = 'col-md-6';
			}

//			if ( $i % 2 == 1 ) {
//				$li_class .= ' pipes-left';
//			} else {
//				$li_class .= ' pipes-right';
//			}
			if ( $field->hidden ) {
				$li_class .= ' hidden';
			}
			$li[] = '<li class="' . $li_class . '"><div class="form-group">' . ( $field->hidden ? '' : $field->label ) . $field->input . '</div></li>';
		}

		$element .= '
			<div class="tab-pane active" id="' . $plugin_type[0] . '-basic">
				<ul class="unstyled config-option-list">
		';
		foreach ( $li AS $key => $el ) {
			$element .= $el;
		}
		$element .= '
				</ul>
			</div>
		';

		$li = array();
		// load advanced fieldset
		$element .= '
			<div class="tab-pane" id="' . $plugin_type[0] . '-advanced">
				<ul class="unstyled config-option-list">
		';
//		$i = 1;
		foreach ( $form->getFieldset( 'advanced' ) AS $field ) {
			if ( $field->class == 'fullwidth' ) {
				$li_class = 'col-md-12';
//				$i++;
			} else {
				$li_class = 'col-md-6';
			}
//			if ( $i % 2 == 1 ) {
//				$li_class .= ' pipes-left';
//			} else {
//				$li_class .= ' pipes-right';
//			}
//			$i ++;
			$li[] = '<li class="' . $li_class . '"><div class="form-group">' . ( $field->hidden ? '' : $field->label ) . $field->input . '</div></li>';
		}

		foreach ( $li as $key => $el ) {
			$element .= $el;
		}
		$element .= '
				</ul>
			</div>
		';

		// load help tab
		$element .= '
			<div class="tab-pane" id="' . $plugin_type[0] . '-help">
		';
		
		$help_file_path = OBGRAB_SITE . "/plugins/{$plugin_type[0]}s/{$file}/language/en-GB/en-GB.plg_wppipes-{$plugin_type[0]}_{$file}.html";
		
		if ( JFile::exists( $help_file_path ) ) {
			ob_start();
			include( $help_file_path );
			$element .= ob_get_contents();
			ob_end_clean();
		} else {
			$element .= '
				' . __( 'No guide available!' ) . '
			';
		}
		$element .= '
			</div>
		';

		//$li = str_replace('class="hasTooltip"','class="hasTip hasTooltip"', $li);
		$element = str_replace( '<label', '<label data-toggle="tooltip"', $element );
		$element = str_replace( 'title=', 'data-original-title=', $element );
		//$html = '<div class="ogb-params"><ul class="unstyled config-option-list">'.$li.'</ul><div class="clr"></div></div>';
		$html = '<div class="ogb-params"><div class="tab-content">' . $element . '</div><div class="clr"></div></div>';

		return $html;
	}

	public static function get_param_pipe( $item_id, $code ) {
		/*get params of processors  */
		global $wpdb;
		$qry   = "SELECT `params` FROM `{$wpdb->prefix}wppipes_pipes` WHERE `item_id`={$item_id} AND `code`='{$code}' ORDER BY `ordering`";
		$pipes = $wpdb->get_results( $qry );

		return $pipes;
	}

	public static function get_default_data( $type = '', $id ) {
		$id   = filter_input( INPUT_GET, 'id' );
		$path = OGRAB_EDATA . 'item-' . $id . DS . 'row-default.dat';
		if ( ! is_file( $path ) ) {
			return null;
		}
		$default = file_get_contents( $path );
		$default = unserialize( $default );
		if ( $type == '' ) {
			return $default;
		} else {
			return $default->$type;
		}
	}

	public static function empty_folder( $path ) {
		if ( substr( $path, 0, 1 ) == '/' ) {
			$path = substr( $path, 1 );
		}
		$url_path  = JURI::root() . $path;
		$url_path  = JPath::clean( $url_path );
		$to        = array( 'host' => str_replace( "\\", "/", $url_path ), 'path' => $path );
		$dest_path = isset ( $to['path'] ) ? JPATH_ROOT . DS . $to['path'] : '';

		$folders = JFolder::folders( $dest_path, '.', false, true, array(), array() );
		foreach ( $folders as $folder ) {
			if ( is_link( $folder ) ) {
				// Don't descend into linked directories, just delete the link.
				jimport( 'joomla.filesystem.file' );
				if ( JFile::delete( $folder ) !== true ) {
					// JFile::delete throws an error
					return false;
				}
			} elseif ( JFolder::delete( $folder ) !== true ) {
				// JFolder::delete throws an error
				return false;
			}
		}
	}


	public static function renderIWantBtn() {
		global $isJ25;
		$bar = JToolBar::getInstance( 'toolbar' );
		if ( $isJ25 ) {
			$label = '<span class="fa fa-heart-o fa-3x"></span>';
		} else {
			$label = '<span class="fa fa-heart-o"></span>';
		}
		$iwant_button = "
			<div id=\"foobla\">
				<a type=\"button\" class=\"btn btn-link btn-small dropdown-toggle\" onclick=\"display_form();\" style=\"text-decoration: none;\">
					{$label}
				</a>
				<div class=\"dropdown-iwant\" id=\"dropdown-iwant\" style=\"display:none;width:400px\">
					<a id=\"iwant-close\" class=\"btn btn-link btn-micro\" href=\"javascript:void()\" onclick=\"document.getElementById('dropdown-iwant').style.display='none'\"><i class=\"fa fa-times-circle\"></i></a>
					<h4 style=\"text-align: left;\">
						" . JText::_( 'COM_OBGRABBER_IWANT_INFO' ) . "
					</h4>
					<div class=\"form-group\">
						<textarea rows=\"5\" name=\"iwant\" id=\"iwantto\" class=\"input-block-level\"></textarea>
					</div>
					<p id=\"iwanto_thanks\" class=\"alert alert-info\" style=\"font-size:11px;text-align:left;margin-bottom:10px\">
					</p>
					<div class=\"form-group pull-right\">
						<a id=\"iw_btn\" class=\"button btn btn-primary\" href=\"#\">" . JText::_( 'COM_OBGRABBER_SEND' ) . "</a>
					</div>
				</div>
			</div>
		";
		$bar->appendButton( 'Custom', $iwant_button, 'iwant' );
	}

	public static function getManifest( $element ) {
		$db  = JFactory::getDbo();
		$sql = "SELECT `manifest_cache` FROM `#__extensions` WHERE `type`='component' AND `element`='{$element}'";
		$db->setQuery( $sql );
		$res      = $db->loadResult();
		$manifest = new JRegistry( $res );

		return $manifest;
	}

	/**
	 * Get current version of the extension
	 * @return (string) version number from manifest_cache
	 */
	public static function getVersion( $element ) {
		$manifest = self::getManifest( $element );
		$version  = $manifest->get( 'version' );

		return $version;
	}

	/**
	 * Get latest version of the extension from Update Stream
	 * @return (string) latest version number from #__updates table
	 */
	public static function getNewVersion( $element ) {
		$db  = JFactory::getDbo();
		$ext = JComponentHelper::getComponent( $element );
		$sql = 'SELECT `version` FROM `#__updates` WHERE `extension_id`=' . $ext->id . ' ORDER BY update_id DESC LIMIT 1';
		$db->setQuery( $sql );
		$newVersion = $db->loadResult();

		return $newVersion;
	}

	/**
	 * Check if there is new version available
	 * @return bool
	 */
	public static function hasNewVersion( $element ) {
		$current_version = self::getVersion( $element );
		$update_version  = self::getNewVersion( $element );
		if ( version_compare( $current_version, $update_version, '>' ) == 1 ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Render Version Notification with Update button (go to the standard Joomla Update)
	 * @return string
	 */
	public static function versionNotify() {
		global $option;
		$html = '';
		if ( self::hasNewVersion( $option ) ) {
			$html .= '<div class="alert alert-error">';
			$html .= sprintf( JText::_( 'COM_OBGRABBER_NEWVERSION_AVAILABLE_NEW' ), self::getNewVersion( $option ) );
			$html .= ' <a class="btn btn-primary" href="index.php?option=com_installer&view=update&filter_search=wppipes">';
			$html .= '<i class="fa fa-upload"></i> ' . JText::_( 'COM_OBGRABBER_UPDATE_NOW' );
			$html .= '</a>';
			$html .= '</div>';
		}

		return $html;
	}

	/**
	 * Check if the cronjob is setting up correctly
	 */
	public static function checkCronjobSettings() {
		global $option;
		jimport( 'joomla.application.component.helper' );
		$params = JComponentHelper::getParams( $option );

		$cronjob_active = $params->get( 'cronjob_active' );
		$js_active      = $params->get( 'js_active' );

		$html = '';
		if ( $cronjob_active + $js_active < 1 ) {
			$html .= '<div class="alert alert-error">';
			$uri    = JFactory::getURI();
			$return = $uri->toString();
			$return = base64_encode( $return );
			$html .= sprintf( JText::_( 'COM_OBGRABBER_MSG_SETTING_CRONJOB' ), $return );
			$html .= '</div>';
		}

		return $html;
	}
}

class ogbLib {
	public static function call_method( $className, $method, $args = array() ) {
		$res = call_user_func_array( array( $className, $method ), $args );

		return $res;
	}
}

//ogbFile::get_curl($url);
class ogbFile {
	public static function write( $path, $txt = '' ) {
		$path = self::clean($path);
		$folder = dirname($path);
		if(!is_dir($folder)){
			ogbFolder::create($folder);
		}
		$ret = is_int(file_put_contents($path, $txt));
		return $ret;
	}
	
	public static function read( $file ) {
		return file_get_contents( $file );
	}

	public static function get_content( $file ) {
		return file_get_contents( $file );
	}

	public static function clean($path, $ds = DIRECTORY_SEPARATOR)
	{
		if (!is_string($path))
		{
			throw new UnexpectedValueException('obFile::clean: $path is not a string.');
		}

		$path = trim($path);

		if (empty($path))
		{
			$path = JPATH_ROOT;
		}
		// Remove double slashes and backslashes and convert all slashes and backslashes to DIRECTORY_SEPARATOR
		// If dealing with a UNC path don't forget to prepend the path with a backslash.
		elseif (($ds == '\\') && ($path[0] == '\\' ) && ( $path[1] == '\\' ))
		{
			$path = "\\" . preg_replace('#[/\\\\]+#', $ds, $path);
		}
		else
		{
			$path = preg_replace('#[/\\\\]+#', $ds, $path);
		}

		return $path;
	}

	public static function get_curl( $url ) {
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_HEADER, 0 );

		ob_start();
		curl_exec( $ch );

		if ( isset( $_GET['x11'] ) ) {
			echo '<br /><i><b>File:</b>' . __FILE__ . ' <b>Line:</b>' . __LINE__ . "</i><br /> \n";
			$info = curl_getinfo( $ch );
			echo '<pre>';
			print_r( $info );
			echo '</pre>';
		}
		curl_close( $ch );

		return ob_get_clean();
	}

	public static function getHost() {
		global $ogb_host;
		if ( ! $ogb_host ) {
			$ogb_host = site_url();
		}

		return $ogb_host;
	}
}

class ogbFolder {
	public static function create($path, $mode=0755){
		$path = ogbFile::clean($path);
		if(is_dir($path)){
			return true;
		}
		$i = 0;
		
		$parent = dirname($path);
//		JFolder::create();
		if(is_dir($parent)){
			// First set umask
			$origmask = @umask(0);
			// Create the path
			if (!$ret = @mkdir($path, $mode))
			{
				@umask($origmask);
				return false;
			}
			// Reset umask
			@umask($origmask);
		} else {
			self::create($parent, $mode);
			self::create($path, $mode);
		}
	}
	
	public static function files(){
		JFolder::files();
	}
}

class ogbDb {
	public static function query($sql){
		global $wpdb;
		$wpdb->query( $sql );
	}
}

//echo obg_sbug(__FILE__,__LINE__,true);
function obg_sbug( $file, $line, $stime = false, $msg = '', $microtime = false ) {
	$smtime = $microtime ? '[' . microtime() . ']' : '';
	$time   = $stime ? "[" . date( 'Y-m-d H:i:s' ) . "]" : '';
	echo "\n\n<br />" . $time . $smtime . "<i>[ <b>File:</b>" . $file . ' ][ <b>Line:</b>' . $line . "]</i><br />\n\n";
	if ( $msg != '' ) {
		echo '<br />' . $msg . "<br />\n";
	}
}

function ogb_show( $text, $desc = '', $width = 900, $mheight = 600 ) {
	$style = "margin:5px auto;background:#f8f8f8;border: 2px solid #009900;max-height: {$mheight}px;overflow: auto;padding: 5px;width: {$width}px;";
	echo '<div style="' . $style . '">';
	echo "<b><i>{$desc}</i></b><hr />" . $text;
	echo '</div>';

}

function ogb_pr( $arr, $desc = '', $width = 1200, $mheight = 600 ) {
	$style = "background:#f8f8f8;border: 2px solid #009900;max-height: {$mheight}px;overflow: auto;padding: 5px;width: {$width}px;";
	echo '<pre style="' . $style . '">';
	echo $desc;
	print_r( $arr );
	echo '</pre>';
}