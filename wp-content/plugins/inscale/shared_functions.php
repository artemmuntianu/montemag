<?php

function wp_is_get_dropdown_html($id, $btnTitle, $items)
{
    $result =
        "<div id='$id' class='dropdown'>" .
        '<button class="btn-dropdown">' . $btnTitle . '</button>' .
        '<div class="dropdown-content">';
    foreach ($items as $item) :
        $result .= "<a href='javascript:void(0)' data-attr-id='$item->id'>";
    if (isset($item->imgUrl))
        $result .= "<img src='$item->imgUrl' />";
    $result .=
        $item->text .
        '</a>';
    endforeach;
    $result .=
        '</div>' .
        '</div>';
    return $result;
}

function wp_is_get_product_images($productId)
{
    $result = array();
    $size = 'medium';
    $attachments = get_children(array(
        'post_parent' => $productId,
        'post_status' => 'inherit',
        'post_type' => 'attachment',
        'post_mime_type' => 'image',
        'order' => 'ASC',
        'orderby' => 'menu_order'
    ));
    if (empty($attachments))
        return $result;

    foreach ($attachments as $productId => $attachment) :
        $title = esc_html($attachment->post_title);
    $imgUrlFull = esc_url(wp_get_attachment_url($productId));
    $imgUrlScaled = esc_url(wp_get_attachment_image_src($productId, $size)[0]);
    $result[] = array(
        'id' => $productId,
        'name' => esc_attr($title),
        'imgUrlFull' => $imgUrlFull,
        'imgUrlScaled' => $imgUrlScaled
    );
    endforeach;

    return $result;
}

function wp_is_get_product_measures($productId)
{
    global $wpdb;
    $dbData = $wpdb->get_results(
        "SELECT * FROM wp_postmeta WHERE post_id=$productId AND meta_key in ('_width', '_height')",
        ARRAY_A
    );
    $result = array();
    $cmToMm = 10;
    foreach ($dbData as $id => $row) :
        switch ($row['meta_key']) {
        case '_width':
            $result[] = array('name' => 'width', 'value' => doubleval($row['meta_value']) * $cmToMm);
            break;
        case '_height':
            $result[] = array('name' => 'height', 'value' => doubleval($row['meta_value']) * $cmToMm);
            break;
    }
    endforeach;
    return $result;
}

function wp_is_get_backgrounds()
{
    global $wpdb, $inScalePlugin;
    $result = $wpdb->get_results('SELECT * FROM wp_is_backgrounds ORDER BY name', ARRAY_A);
    for ($i = 0; $i < count($result); $i++) {
        $curBgPtr = &$result[$i];
        $curBgPtr['pxPerMm'] = $curBgPtr['measurePx'] / $curBgPtr['measureMm'];
        if (strpos($curBgPtr['imageUrl'], 'http') === false)
            $curBgPtr['imageUrl'] = $inScalePlugin->baseUrl . $curBgPtr['imageUrl'];
    }
    return $result;
}