<?php
if (isset($_GET['action']) && $_GET['action'] === 'image_combining') {
    global $wpdb;
    global $inScalePlugin;
    require_once __DIR__ . '/../shared_functions.php';
    $dbBackgrounds = wp_is_get_backgrounds();
    $productId = $_GET['product'];
    $dbProduct = $wpdb->get_row("SELECT * FROM wp_posts WHERE id = $productId", ARRAY_A);
    $productImages = wp_is_get_product_images($productId);
    $productMeasures = wp_is_get_product_measures($productId);
    $imageComposerVM = (object)[
        'backgrounds' => $dbBackgrounds,
        'background' => $dbBackgrounds[0],
        'productImages' => $productImages,
        'productImage' => $productImages[0],
        'productMeasures' => $productMeasures,
        'productMeasure' => $productMeasures[0],
        'product' => $dbProduct
    ];
    echo
        '<H2>Image Combining</H2>' .
        '<div id="is-image-composer-container"></div>' .
        '<script>' .
        'window["InScale"] = {' .
        'imageComposerVM: ' . json_encode($imageComposerVM) .
        '}' .
        '</script>';
} else {
    echo '<H2>Products</H2>';
    require 'product_list_table.php';
    $table = new InScale_Product_List_Table();
    $table->prepare_items();
    $table->display();
}