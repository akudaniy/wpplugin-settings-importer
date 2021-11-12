<?php

define('TSI_BASE_WPPLUGIN_INFO', 'http://api.wordpress.org/plugins/info/1.0/');


/**
 * $plugin_basename = 'toro-settings-importer/init.php'
 */
function tsi_get_plugin_info( $plugin_basename ) {

  $pluginz = explode("/", $plugin_basename);
  $plugin_slug = $pluginz[0];

  $args = (object) array( 'slug' => $plugin_slug );
  $request = array( 'action' => 'plugin_information', 'timeout' => 15, 'request' => serialize( $args) );
  $url = 'http://api.wordpress.org/plugins/info/1.0/';

  $response = wp_remote_post( $url, array( 'body' => $request ) );
  $plugin_info = unserialize( $response['body'] );

  return $plugin_info;

}



/**
 * $plugin_basename = 'toro-settings-importer/init.php'
 */
function tsi_do_import_plugins( $plugin_basename ) {
  global $wp_filesystem;

  $pluginz = explode("/", $plugin_basename);
  $plugin_slug = $pluginz[0];

  $plugin_info = tsi_get_plugin_info( $plugin_basename );

  // download the zip file from WordPress repository
  $plugin_fzip = file_get_contents( $plugin_info->download_link ); 
  $plugin_fzip_basename = pathinfo( $plugin_info->download_link, PATHINFO_BASENAME ); // hello-dolly.1.6.zip
  

  $updirs = wp_upload_dir();
  /* -----------------------------------------------------------------------------
  $updirs = array ( [path]    => C:\path\to\wordpress\wp-content\uploads\2010\05
                    [url]     => http://example.com/wp-content/uploads/2010/05
                    [subdir]  => /2010/05
                    [basedir] => C:\path\to\wordpress\wp-content\uploads
                    [baseurl] => http://example.com/wp-content/uploads
                    [error]   =>
                   )
  -------------------------------------------------------------------------------- */

  $local_pluginzip_fpath = $updirs['basedir'] . "/{$plugin_fzip_basename}";

  $fh = fopen( $local_pluginzip_fpath, "wb+");
  fwrite($fh, $plugin_fzip);
  fclose($fh);

  // extract the plugin zip file into wp-content/plugins/
  $WP_PLUGIN_DIR = WP_PLUGIN_DIR; // /var/www/domain.com/wp-content/plugins -> no trailing slash

  require_once( ABSPATH . '/wp-admin/includes/file.php' );
  
  WP_Filesystem();
  $unzipfile = unzip_file( $local_pluginzip_fpath, $WP_PLUGIN_DIR);


  // delete the downloaded plugin zip file
  unlink( $local_pluginzip_fpath );


  // activate the plugin
  tsi_activate_plugins( $plugin_basename );

     
  /*if ( $unzipfile ) {
    echo 'Successfully unzipped the file!';       
  } else {
    echo 'There was an error unzipping the file.';       
  }*/

}




/**
 * Activate the downloaded and unzipped plugin
 */
function tsi_activate_plugins( $plugin_basename ) {
  
  if ( ! current_user_can('activate_plugins') )
    wp_die(__('You do not have sufficient permissions to activate plugins for this site.'));
  $plugins = FALSE;
  $plugins = get_option('active_plugins'); // get active plugins
  
  if ( $plugins ) {
    
    // plugins to active
    $plugins_to_active = array(
      $plugin_basename
    );
    
    foreach ( $plugins_to_active as $plugin ) {
      if ( ! in_array( $plugin, $plugins ) ) {
        array_push( $plugins, $plugin );
        update_option( 'active_plugins', $plugins );
      }
    }
    
  } // end if $plugins

}