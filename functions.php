<?php
add_action( 'wp_enqueue_scripts', function () {
		wp_enqueue_style( 'ah-parent-style', get_template_directory_uri() . '/style.css' );
		wp_enqueue_style(
					'ah-child-style',
					get_stylesheet_uri(),
					array( 'ah-parent-style' ),
					wp_get_theme()->get( 'Version' )
				);
		wp_enqueue_style(
					'ah-google-fonts',
					'https://fonts.googleapis.com/css2?family=Caveat:wght@600;700&family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Public+Sans:wght@400;500;600;700;800&display=swap',
					array(),
					null
				);
} );

/**
 * Light styling for the Program Details admin screen so the entry form
	 * isn't a bare stacked list on wide screens.
	 */
add_action( 'admin_enqueue_scripts', function ( $hook ) {
		global $post;
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
					return;
		}
		if ( ! isset( $post ) || 'program' !== $post->post_type ) {
					return;
		}
		wp_add_inline_style(
					'wp-admin',
					'
					.acf-field-group .postbox { border-top: 3px solid #04342C; }
					.acf-field { padding: 16px 20px !important; }
					.acf-field .acf-label label { font-weight: 600; color: #04342C; }
					.acf-field input[type="text"],
					.acf-field input[type="url"],
					.acf-field textarea,
					.acf-field select { border-radius: 6px; border-color: #d8d5c9; }
					.acf-field input[type="text"]:focus,
					.acf-field input[type="url"]:focus,
					.acf-field textarea:focus,
					.acf-field select:focus { border-color: #04342C; box-shadow: 0 0 0 1px #04342C; }
					'
				);
} );

/**
 * Work out how to embed a program's video field.
	 * Supports direct video files, YouTube links, and Google Drive links.
	 * Falls back to a plain link for anything else.
	 */
function ah_render_program_video( $url ) {
		if ( empty( $url ) ) {
					return '';
		}

	$url = trim( $url );

	// Direct video file (mp4, webm, mov).
	if ( preg_match( '/\.(mp4|webm|mov)(\?.*)?$/i', $url ) ) {
				return '<div class="ah-detail-video"><video src="' . esc_url( $url ) . '" autoplay muted loop playsinline preload="metadata"></video></div>';
	}

	// YouTube.
	if ( preg_match( '/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/))([A-Za-z0-9_-]{6,})/', $url, $m ) ) {
				$id = $m[1];
				$src = 'https://www.youtube.com/embed/' . esc_attr( $id ) . '?autoplay=1&mute=1&loop=1&playlist=' . esc_attr( $id );
				return '<div class="ah-detail-video"><iframe src="' . esc_url( $src ) . '" allow="autoplay; encrypted-media" allowfullscreen></iframe></div>';
	}

	// Google Drive.
	if ( preg_match( '/drive\.google\.com\/file\/d\/([^\/]+)/', $url, $m ) ) {
				$src = 'https://drive.google.com/file/d/' . esc_attr( $m[1] ) . '/preview';
				return '<div class="ah-detail-video"><iframe src="' . esc_url( $src ) . '" allow="autoplay"></iframe></div>';
	}

	// Fallback: plain link.
	return '<p><a href="' . esc_url( $url ) . '">Watch video</a></p>';
}

/**
 * Render a program's media: prefer the video field, fall back to the
	 * still image field, and render nothing if neither is set.
	 */
function ah_render_program_media( $video_url, $image_url ) {
		$video_html = ah_render_program_video( $video_url );
		if ( ! empty( $video_html ) ) {
					return $video_html;
		}

	if ( ! empty( $image_url ) ) {
				return '<div class="ah-detail-video ah-detail-image"><img src="' . esc_url( $image_url ) . '" alt="" loading="lazy"></div>';
	}

	return '';
}
