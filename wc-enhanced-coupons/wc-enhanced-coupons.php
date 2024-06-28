<?php
/*
Plugin Name: WooCommerce Enhanced Coupons
Description: Adiciona campos de categoria aos cupons padrão e aplica desconto apenas quando as categorias específicas estão presentes no carrinho.
Version: 1.0
Author: Lucas Dantas
Author URI: https://linkedin.com/in/lucas-de-sousa-dantas
*/

if (!defined('ABSPATH')) {
    exit;
}

add_action('woocommerce_coupon_options', 'wce_add_coupon_meta_fields');
function wce_add_coupon_meta_fields() {
    woocommerce_wp_select(
        array(
            'id' => 'wce_category_1',
            'label' => __('Categoria 1', 'woocommerce'),
            'description' => __('Selecione a primeira categoria de produtos.', 'woocommerce'),
            'options' => wce_get_product_categories()
        )
    );
    woocommerce_wp_select(
        array(
            'id' => 'wce_category_2',
            'label' => __('Categoria 2', 'woocommerce'),
            'description' => __('Selecione a segunda categoria de produtos.', 'woocommerce'),
            'options' => wce_get_product_categories()
        )
    );
}

add_action('woocommerce_coupon_options_save', 'wce_save_coupon_meta_fields');
function wce_save_coupon_meta_fields($post_id) {
    $category_1 = isset($_POST['wce_category_1']) ? $_POST['wce_category_1'] : '';
    $category_2 = isset($_POST['wce_category_2']) ? $_POST['wce_category_2'] : '';
    update_post_meta($post_id, 'wce_category_1', sanitize_text_field($category_1));
    update_post_meta($post_id, 'wce_category_2', sanitize_text_field($category_2));
}

add_filter('woocommerce_coupon_is_valid', 'wce_is_coupon_valid', 10, 2);
function wce_is_coupon_valid($valid, $coupon) {
    if (!$valid) {
        return false;
    }

    $category_1 = get_post_meta($coupon->get_id(), 'wce_category_1', true);
    $category_2 = get_post_meta($coupon->get_id(), 'wce_category_2', true);

    if (empty($category_1) || empty($category_2)) {
        return $valid;
    }

    $category_1_present = false;
    $category_2_present = false;

    foreach (WC()->cart->get_cart() as $cart_item) {
        $product_id = $cart_item['product_id'];
        $product_cats = wc_get_product_term_ids($product_id, 'product_cat');

        if (in_array($category_1, $product_cats)) {
            $category_1_present = true;
        }

        if (in_array($category_2, $product_cats)) {
            $category_2_present = true;
        }
    }

    return $category_1_present && $category_2_present;
}

function wce_get_product_categories() {
    $categories = get_terms('product_cat', array('hide_empty' => false));
    $options = array();
    foreach ($categories as $category) {
        $options[$category->term_id] = $category->name;
    }
    return $options;
}
?>
