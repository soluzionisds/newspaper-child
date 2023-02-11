<?php
/* ----------------------------------------------------------------------------
    add featured image to JSON
 */
add_action( 'rest_api_init', 'register_rest_images' );
function register_rest_images() {
    register_rest_field( array( 'post' ),
        'fimg_url',
        array(
            'get_callback'    => 'get_rest_featured_image',
            'update_callback' => null,
            'schema'          => null,
        )
    );
}
function get_rest_featured_image( $object, $field_name, $request ) {
    if ( $object['featured_media'] ) {
        $img = wp_get_attachment_image_src( $object['featured_media'], 'large' ); // change 'thumbnail' to other image size if needed
        if ( empty( $img ) ) {
            return false;
        }
        return $img[0];
    }
    return false;
}

/* ----------------------------------------------------------------------------
    add user role to JSON

function get_user_roles($object, $field_name, $request) {
  return get_userdata($object['id'])->roles;
}

add_action('rest_api_init', function() {
  register_rest_field('user', 'roles', array(
    'get_callback' => 'get_user_roles',
    'update_callback' => null,
    'schema' => array(
      'type' => 'array'
    )
  ));
});
*/
