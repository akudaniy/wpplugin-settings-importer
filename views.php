<?php
/**
 * @package Settings Importer
 */
 
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
if( ! is_dir($updirs['basedir'] . '/tmp/') ) {
  mkdir( $updirs['basedir'] . '/tmp/');
}



/**
 * Displays inline custom css styling
 */
function tsi_head_css() { ?>
  <style type="text/css">
  .attached-images-list {display:block;}
  .attached-images-list:after {content:""; display:table; line-height:0; clear:both;}
  .attached-images-list .item {display:inline-block; padding:3px; box-shadow:0 0 3px rgba(0,0,0,.5); margin:5px; width:100px; height:100px;}
  .label-wrap {background-color:#DDD; margin:0 5px 5px 0; padding:0 5px; display:inline-block;}
  .label-wrap:hover {background-color:#CCC;}

  tr.thumbnail td {padding:0; border-bottom:1px solid #F1F1F1;}
  tr.thumbnail:hover {background-color:#CCC;}

  .form-table-geturls {border-collapse: collapse; clear: both; margin-top: 0.5em; width:100%;}
  .attention-box {padding:10px 20px; background-color:#FCF9A5; border:solid 1px #FDD47B;}
  </style>
<?php 

}



/**
 * Displays the form
 */
function tsi_export_fn( $params=array() ) {
  global $wpdb, $table_prefix, $updirs, $theme_opts;

  tsi_head_css();
  
  ?>
  <div class="wrap">

  <h2>Export &amp; Import Blog Settings</h2>


  <h3>Export</h3>
  <p>Download setting yang ada pada blog ini untuk diaplikasikan pada blog lain</p>

  <form action="" method="post">

  <h4>Options</h4>
  <p>
    <?php 
    $option_names = tsi_exported_option_names();
    foreach ($option_names as $option_name) : ?>
    <label class="label-wrap" for="data_options_<?php echo $option_name ?>">
      <input type="checkbox" id="data_options_<?php echo $option_name ?>" name="data[options][]" checked="checked" value="<?php echo $option_name; ?>"> <?php echo $option_name; ?>
    </label>
    <?php endforeach; ?>  
  </p>

  <h4>Taxonomies</h4>
  <p>
    <?php foreach ( get_taxonomies() as $taxonomy ) : ?>
    <label class="label-wrap" for="data_taxonomies_<?php echo $taxonomy ?>">
      <input type="checkbox" id="data_taxonomies_<?php echo $taxonomy ?>" name="data[taxonomies][]" value="<?php echo $taxonomy; ?>"> <?php echo $taxonomy; ?>
    </label>
    <?php endforeach; ?>
  </p>

  <h4>Active Plugins</h4>
  <p>
    <?php $active_plugins = get_option('active_plugins'); ?>
    <?php foreach ($active_plugins as $active_plugin) : 
    $pluginz = explode("/", $active_plugin);
    $plugin_slug = $pluginz[0];

    // $plugin_info = tsi_get_plugin_info( $plugin_slug );

    // don't list this plugin itself
    if( $plugin_slug != 'toro-settings-importer' ) :
    ?>
    <label class="label-wrap" for="data_taxonomies_<?php echo $plugin_slug ?>">
      <input type="checkbox" id="data_taxonomies_<?php echo $plugin_slug ?>" name="data[active_plugins][]" value="<?php echo $active_plugin; ?>"> <?php echo $plugin_slug; ?>
    </label>
    <?php endif; ?>
    <?php endforeach; ?>
  </p>

  <h4>Posts</h4>

  <ul>
    <?php 

    $xposts = $wpdb->get_results("SELECT * FROM {$table_prefix}posts WHERE post_status = 'publish'");

    foreach ($xposts as $n => $xpost) : ?>
    <li>
      <label for="data_posts_<?php echo $n ?>">
        <input type="checkbox" id="data_posts_<?php echo $n ?>" name="data[posts][]" value="<?php echo $xpost->ID; ?>"> <?php echo $xpost->post_title; ?>
      </label>
    </li>
    <?php endforeach; ?>
  </ul>

  <input type="submit" name="export_settings" id="export_settings" class="button button-primary" value="Export Settings">
  </form>

  <h3>Import</h3>
  <p>Upload dan import setting dari blog lain via export file <code>tsi-export.json</code></p>
  <form action="" enctype="multipart/form-data" method="post">
    <input name="export_file[]" type="file" />
    <input type="submit" name="import_settings" id="import_settings" class="button button-primary" value="Import Settings">
  </form>

  </div>
  <?php 

}



/**
 * Register Settings Importer custom menu in WordPress Administration screen
 *
 * @uses admin_menu hook
 */
function tsi_importer_menu () {
  $icon_url = plugins_url('', __FILE__) . '/lib/img/wolppr-icon.png';
  add_menu_page('Export &amp; Import Blog Settings', 'Export Import', 'manage_options', 'tsi-export', 'tsi_export_fn', $icon_url, '4.5');

  // Add a submenu to the custom top-level menu:
  // add_submenu_page('tsi-export', __('Import Settings', WF_PLUGIN_TEXTDOMAIN), __('Import', WF_PLUGIN_TEXTDOMAIN), 'manage_options', 'tsi-import', 'tsi_import_fn');

}
add_action( 'admin_menu' ,'tsi_importer_menu' );


add_action( 'admin_notices', 'tsi_admin_notice' );
function tsi_admin_notice(){
  global $current_screen;
  if ( isset($_GET['tsi_imported']) ) {
    echo '<div class="updated settings-error"><p>Setting sukses diimpor</p></div>';
  }
}
