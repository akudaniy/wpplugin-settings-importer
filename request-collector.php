<?php

/**
 * @package Settings Importer
 */

add_action('init', 'tsi_export_to_file');
function tsi_export_to_file() {
  global $wpdb, $table_prefix;

  if( isset($_POST['export_settings']) ) {

    // ambil setting yang mau diekspor dari init.php
    $option_names = isset($_POST['data']['options']) ? $_POST['data']['options'] : array();

    $exports = array();

    // populate options for export
    foreach ($option_names as $option_name) {
      $options = $wpdb->get_row("SELECT * FROM {$table_prefix}options WHERE option_name = '{$option_name}'");
      unset($options->option_id);
      $exports['options'][] = $options;
      
    }


    // populate taxonomy for export
    $taxonomies = isset($_POST['data']['taxonomies']) ? $_POST['data']['taxonomies'] : array();

    foreach ($taxonomies as $taxonomy) {

      $tts = $wpdb->get_results("SELECT term_id,parent FROM wp_term_taxonomy WHERE taxonomy = '{$taxonomy}' ORDER BY parent ASC");

      foreach ($tts as $n => $tt) {
        $term_detail = get_term_by('id', $tt->term_id, $taxonomy);

        $exports['taxonomies'][$taxonomy][$n]['name'] = $term_detail->name;
        $exports['taxonomies'][$taxonomy][$n]['slug'] = $term_detail->slug;
        $exports['taxonomies'][$taxonomy][$n]['taxonomy'] = $taxonomy;
        $exports['taxonomies'][$taxonomy][$n]['parent_name'] = '';
        $exports['taxonomies'][$taxonomy][$n]['parent_slug'] = '';

        if( $term_detail->parent > 0 ) {
          $thisterm_parent = get_term_by('id', $term_detail->parent, $taxonomy);
          $exports['taxonomies'][$taxonomy][$n]['parent_name'] = $thisterm_parent->name;
          $exports['taxonomies'][$taxonomy][$n]['parent_slug'] = $thisterm_parent->slug;
        }

      }
    
    }


    // populate active_plugins for export
    $active_plugins = isset($_POST['data']['active_plugins']) ? $_POST['data']['active_plugins'] : array();

    foreach ($active_plugins as $active_plugin) {
      $exports['active_plugins'][] = $active_plugin;
    }


    // populate posts for export
    $xposts = isset($_POST['data']['posts']) ? $_POST['data']['posts'] : array();

    foreach ($xposts as $n => $xpost_ID) {

      $post_obj = get_post( $xpost_ID );
      $exports['posts'][$xpost_ID]['post_title'] = $post_obj->post_title;
      $exports['posts'][$xpost_ID]['post_content'] = $post_obj->post_content;
      $exports['posts'][$xpost_ID]['post_excerpt'] = $post_obj->post_excerpt;
      $exports['posts'][$xpost_ID]['post_status'] = $post_obj->post_status;
      $exports['posts'][$xpost_ID]['post_parent'] = $post_obj->post_parent;
      $exports['posts'][$xpost_ID]['post_type'] = $post_obj->post_type;
      $exports['posts'][$xpost_ID]['comment_status'] = $post_obj->comment_status;

      // get this post terms
      foreach (get_taxonomies() as $taxonomy) {
        $post_terms = get_the_terms( $post_obj->ID, $taxonomy);
        $exports['posts'][$xpost_ID]['terms'][$taxonomy] = $post_terms;
      }      
    }
    
    $export_json = json_encode($exports);


    header('Content-Description: File Transfer');
    header("Content-Type: text/javascript; charset=utf-8");
    header('Content-Disposition: attachment; filename=tsi-export.json');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');

    echo $export_json;
    die();

  }

}


add_action('init', 'tsi_import_from_file');
function tsi_import_from_file() {
  global $wpdb, $table_prefix;

  if( isset($_FILES['export_file']) ) {

    // move uploaded file to ABSPATH
    $tsi_export_path = ABSPATH . '/' . $_FILES['export_file']['name'][0];
    move_uploaded_file($_FILES['export_file']['tmp_name'][0], $tsi_export_path);


    // file get contents isi json
    $tsi_export_content_json = file_get_contents( $tsi_export_path );


    // json decode into ARRAY format
    $tsi_exports = json_decode( $tsi_export_content_json, TRUE );


    /* ======================================================================
       Importing Options
     * ====================================================================== */
    if( isset($tsi_exports['options']) && is_array($tsi_exports['options']) ) {
    foreach ($tsi_exports['options'] as $option) {
      

      // dicek dulu apakah option_name sudah ada
      $is_opt_exists = $wpdb->get_row("SELECT option_name FROM {$table_prefix}options WHERE option_name = '{$option['option_name']}'");

      if( $is_opt_exists ) {
        
        // kalo ada pake SQL UPDATE
        $wpdb->query("UPDATE {$table_prefix}options SET option_value='{$option['option_value']}', autoload='{$option['autoload']}' WHERE option_name = '{$option['option_name']}'");

      } else {
        
        // kalo gak ada pake SQL INSERT
        $new_opts = array(
          'option_name'   => $option['option_name'],
          'option_value'  => $option['option_value'],
          'autoload'      => $option['autoload'],
          );
        $wpdb->query( construct_query_insert("{$table_prefix}options", $new_opts) );

      }
      
    }
    }


    /* ======================================================================
       Importing Taxonomies
     * ====================================================================== */
    if( isset($tsi_exports['taxonomies']) && is_array($tsi_exports['taxonomies']) ) {
    foreach ($tsi_exports['taxonomies'] as $taxonomy_name => $terms) {
      foreach ($terms as $term) {
        
        // this is a top level term. it has no parent.
        if( $term['parent_slug']=='' ) {

          wp_insert_term(
            $term['name'],  // the term 
            $taxonomy_name, // the taxonomy
            array(
              'description' => '',
              'slug'        => $term['slug'],
              'parent'      => 0,
            )
          );
        }

        else {

          // this term has parent. Check for the parent term_id for parent arguments
          $term_parent = get_term_by('slug', $term['parent_slug'], $taxonomy_name);

          wp_insert_term(
            $term['name'],  // the term 
            $taxonomy_name, // the taxonomy
            array(
              'description' => '',
              'slug'        => $term['slug'],
              'parent'      => $term_parent->term_id,
            )
          );

        }

      }
    }
    }


    /* ======================================================================
       Importing Posts
     * ====================================================================== */

    // delete Hello World post
    $post_hello_world_ID = $wpdb->get_var("SELECT ID FROM {$table_prefix}posts WHERE post_name = 'hello-world'");
    if( $post_hello_world_ID ) {
      wp_delete_post( $post_hello_world_ID, TRUE );
    }


    // delete Sample Page page
    $post_sample_page_ID = $wpdb->get_var("SELECT ID FROM {$table_prefix}posts WHERE post_name = 'sample-page'");
    if( $post_sample_page_ID ) {
      wp_delete_post( $post_sample_page_ID, TRUE );
    }

    if( isset($tsi_exports['posts']) && is_array($tsi_exports['posts']) ) {
    foreach ($tsi_exports['posts'] as $imposts) {

      // post categories container. It will contain IDs in array format
      $post_categories_IDs = array();

      // post tags container. It will contain names, in comma-delimited format. use implode
      $post_tags_names = array();

      // post tax inputs container. It will contain IDs in array format
      $post_tax_inputs = array();      


      foreach ($imposts['terms'] as $taxonomy_name => $terms) {
        $post_terms_names = array();
        $post_terms_IDs   = array();

        if( is_array($terms) ) {
          foreach ($terms as $n => $term) {
            // get the terms already in this database
            $get_terms = get_term_by('slug', $term['slug'], $taxonomy_name);

            if( $taxonomy_name=='post_tag' ) {
              $post_terms_names[] = $get_terms->name;
            }
              
            $post_terms_IDs[]   = $get_terms->term_id;
          }

          // populate the categories
          if( $taxonomy_name=='category' ) {
            $post_categories_IDs = $post_terms_IDs;
          }

          // populate the tags
          elseif( $taxonomy_name=='post_tag' ) {
            $post_tags_names = $post_terms_names;
            $post_tags_name  = implode(",", $post_tags_names);
          }

          // populate custom tax_inputs
          else {

            $post_tax_inputs[$taxonomy_name] = $post_terms_IDs;

          }

        }
      }


      // insert new post
      $new_post = array(
        'comment_status'  => $imposts['comment_status'],
        'post_category'   => $post_categories_IDs,
        'post_content'    => $imposts['post_content'],
        'post_excerpt'    => $imposts['post_excerpt'],
        'post_name'       => $imposts['post_name'],
        'post_parent'     => $imposts['post_parent'],
        'post_status'     => $imposts['post_status'],
        'post_title'      => $imposts['post_title'],
        'post_type'       => $imposts['post_type'],
        'tags_input'      => $post_tags_name,
        'tax_input'       => $post_tax_inputs, // array( 'taxonomy_name' => array( 'term', 'term2', 'term3' ) )
      );
      wp_insert_post( $new_post );

    }
    }



    /* ======================================================================
       Importing Active Plugins, Fetch, and Activate
     * ====================================================================== */
    if( isset($tsi_exports['active_plugins']) && is_array($tsi_exports['active_plugins']) ) {
    foreach ($tsi_exports['active_plugins'] as $active_plugin) {

      // $active_plugin = 'toro-settings-importer/init.php'
      tsi_do_import_plugins( $active_plugin );

    }
    }

    // hapus file export dari ABSPATH
    unlink($tsi_export_path);


    $redir = admin_url('admin.php?page=tsi-export&tsi_imported=1');
    header("Location: {$redir}");
    die();

  }

}


add_action('template_redirect', 'tsi_download_processor');
function tsi_download_processor() {
  global $post, $updirs;
}
