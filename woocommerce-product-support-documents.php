<?php
/*
 * Plugin Name: WooCommerce Product Support Documents
 * Text Domain: woocommerce-product-support-documents
 * Domain Path: /languages
 */

register_activation_hook(__FILE__, 'wpsd_activate');
register_deactivation_hook(__FILE__, 'wpsd_deactivate');

function wpsd_activate() {

}

function wpsd_deactivate() {

}

add_action( 'init', 'wpsd_load_textdomain' );
function wpsd_load_textdomain() {
	load_plugin_textdomain( 'woocommerce-product-support-documents', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}

add_action('add_meta_boxes', 'wpsd_add_support_documents_box');
function wpsd_add_support_documents_box() {
    add_meta_box(
        'wpsd_support_documents_box',
        __('Support Documents', 'woocommerce-product-support-documents'),
        'wpsd_display_support_documents_editor',
        'product'
    );
}

function wpsd_display_support_documents_editor($post) {
    $meta = get_post_meta($post->ID, 'wpsd_support_documents', true);
    echo '<input type="hidden" name="wpsd_nonce" value="'.wp_create_nonce(basename(__FILE__)).'" />';
    echo '<table class="wpsd-document-table">';
    echo '<tbody>';
    echo '  <tr class="wpsd-document-template">';
    echo '      <td>';
    echo '          <span class="wpsd-document-drag">↕️</span>';
    echo '      </td>';
    echo '      <td>';
    echo '          <input type="text" value="">';
    echo '      </td>';
    echo '      <td>';
    echo '          <input type="text" value="">';
    echo '      </td>';
    echo '      <td>';
    printf('          <button class="button wpsd-document-choose-media">%s</button>', esc_html(__('Choose media...', 'woocommerce-product-support-documents')));
    echo '      </td>';
    echo '      <td>';
    printf('          <button class="button wpsd-document-delete">%s</button>', esc_html(__('Delete', 'woocommerce-product-support-documents')));
    echo '      </td>';
    echo '  </tr>';
    if (!empty($meta)) {
        $documents = json_decode($meta, true);
        foreach ($documents as $document) {
            $title = esc_attr($document['title']);
            $link = esc_attr($document['link']);

            echo '  <tr class="wpsd-document">';
            echo '      <td>';
            echo '          <span class="wpsd-document-drag">↕️</span>';
            echo '      </td>';
            echo '      <td>';
            echo "          <input type=\"text\" name=\"wpsd-document-title[]\" value=\"{$title}\">";
            echo '      </td>';
            echo '      <td>';
            echo "          <input type=\"text\" name=\"wpsd-document-link[]\" value=\"{$link}\">";
            echo '      </td>';
            echo '      <td>';
            printf('          <button class="button wpsd-document-choose-media">%s</button>', esc_html(__('Choose media...', 'woocommerce-product-support-documents')));
            echo '      </td>';
            echo '      <td>';
            printf('          <button class="button wpsd-document-delete">%s</button>', esc_html(__('Delete', 'woocommerce-product-support-documents')));
            echo '      </td>';
            echo '  </tr>';
        }
    }
    echo '</tbody>';
    echo '<tfoot>';
    echo '  <td>';
    echo '  </td>';
    echo '  <td>';
    printf('    <button class="button wpsd-document-add-button">%s</button>', esc_html(__('Add a document', 'woocommerce-product-support-documents')));
    echo '  </td>';
    echo '  <td>';
    echo '  </td>';
    echo '  <td>';
    echo '  </td>';
    echo '  <td>';
    echo '  </td>';
    echo '</tfoot>';
    echo '</table>';
}

add_action('save_post', 'wpsd_save_support_documents');
function wpsd_save_support_documents($post_id) {
    if (array_key_exists('wpsd-document-title', $_POST) &&
        array_key_exists('wpsd-document-link', $_POST)) {        
        $titles = $_POST['wpsd-document-title'];
        $links = $_POST['wpsd-document-link'];

        $documents = array();
        foreach ($titles as $index => $title) {
            $documents[] = [
                'title' => $title,
                'link' => $links[$index]
            ];
        }

        update_post_meta(
            $post_id,
            'wpsd_support_documents',
            json_encode($documents, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS)
        );
    }
}

add_filter( 'woocommerce_product_tabs', 'wpsd_add_support_document_tab');
function wpsd_add_support_document_tab($tabs) {
    $tabs['wpsd_tab'] = array(
        'title'     => __('Support Documents', 'woocommerce-product-support-documents'),
        'priority'  => 30,
        'callback'  => 'wpsd_display_support_document_tab'
    );
    return $tabs;
}

function wpsd_display_support_document_tab() {
    global $post;
    $meta = get_post_meta($post->ID, 'wpsd_support_documents', true);
    if (!$meta) {
        echo esc_html(__('This product has not been added any support document yet.', 'woocommerce-product-support-documents'));
        return;
    }
    
    echo '<table class="wpsd-support-document-table">';
    echo '    <thead>';
    echo '        <tr>';
    echo '            <th>';
    echo '                ' . esc_html(__('Title', 'woocommerce-product-support-documents'));
    echo '            </th>';
    echo '            <th>';
    echo '                ' . esc_html(__('Link', 'woocommerce-product-support-documents'));
    echo '            </th>';
    echo '        </tr>';
    echo '    </thead>';
    echo '    <tbody>';
    $documents = json_decode($meta, true);
    foreach ($documents as $document) {
        $title = esc_html($document['title']);
        $link_href = esc_attr($document['link']);
        $link_text = esc_url($document['link']);

        echo '        <tr>';
        echo "            <td>{$title}</td>";
        echo '            <td>';
        echo "                <a href=\"{$link_href}\" target=\"_blank\">{$link_text}</a>";
        echo '            </td>';
        echo '        </tr>';
    }
    echo '    </tbody>';
    echo '</table>';
}

add_action('admin_enqueue_scripts', 'wpsd_add_admin_scripts');
function wpsd_add_admin_scripts() {
    if (is_admin()) {
        wp_enqueue_script('wpsd_repeatable_fields_js', plugin_dir_url(__FILE__) . '/admin/js/repeatable-fields.js', array('jquery'), false, true);
        wp_enqueue_style('wpsd_repeatable_fields_css', plugin_dir_url(__FILE__) . '/admin/css/repeatable-fields.css');
    }
}

add_action('wp_enqueue_scripts', 'wpsd_add_scripts');
function wpsd_add_scripts() {
    wp_enqueue_style('wpsd_support_documents_css', plugin_dir_url(__FILE__) . '/public/css/support-documents.css');
}
