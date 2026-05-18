<?php
/**
 * NeoWeaver comments template.
 *
 * @package NeoWeaver
 */

if ( post_password_required() ) {
	return;
}
?>

<section id="comments" class="nw-comments" aria-label="<?php esc_attr_e( 'Comments', 'neoweaver' ); ?>">
	<?php if ( have_comments() ) : ?>
		<header class="nw-comments__header">
			<p class="nw-comments__eyebrow"><?php esc_html_e( 'Signal Thread', 'neoweaver' ); ?></p>
			<h2 class="nw-comments__title">
				<?php
				$comments_number = get_comments_number();
				if ( '1' === (string) $comments_number ) {
					esc_html_e( '1 transmission logged', 'neoweaver' );
				} else {
					echo esc_html( sprintf( __( '%s transmissions logged', 'neoweaver' ), number_format_i18n( $comments_number ) ) );
				}
				?>
			</h2>
		</header>

		<ol class="comment-list nw-comments__list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 56,
					'callback'    => 'neoweaver_comment_callback',
				)
			);
			?>
		</ol>

		<?php the_comments_navigation(); ?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() ) : ?>
		<p class="nw-comments__closed"><?php esc_html_e( 'Thread locked. No further transmissions accepted.', 'neoweaver' ); ?></p>
	<?php endif; ?>

	<?php
	$commenter = wp_get_current_commenter();

	comment_form(
		array(
			'class_container'      => 'nw-comment-form',
			'title_reply'          => __( 'Open a new transmission', 'neoweaver' ),
			'title_reply_before'   => '<h3 id="reply-title" class="comment-reply-title nw-comment-form__title">',
			'title_reply_after'    => '</h3>',
			'cancel_reply_link'    => __( 'Abort', 'neoweaver' ),
			'label_submit'         => __( 'Transmit', 'neoweaver' ),
			'class_submit'         => 'submit nw-comment-form__submit',
			'comment_notes_before' => '<p class="comment-notes nw-comment-form__notes">' . esc_html__( 'Use this channel to share lore, feedback, or tactical notes.', 'neoweaver' ) . '</p>',
			'comment_field'        => '<p class="comment-form-comment nw-comment-form__field"><label for="comment">' . esc_html__( 'Transmission', 'neoweaver' ) . '</label><textarea id="comment" name="comment" cols="45" rows="6" maxlength="65525" required="required" placeholder="' . esc_attr__( 'Write your message into the terminal…', 'neoweaver' ) . '"></textarea></p>',
			'fields'               => array(
				'author' => '<p class="comment-form-author nw-comment-form__field"><label for="author">' . esc_html__( 'Agent name', 'neoweaver' ) . ( wp_required_field_indicator() ) . '</label><input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30" maxlength="245" autocomplete="name" required="required"></p>',
				'email'  => '<p class="comment-form-email nw-comment-form__field"><label for="email">' . esc_html__( 'Secure channel', 'neoweaver' ) . ( wp_required_field_indicator() ) . '</label><input id="email" name="email" type="email" value="' . esc_attr( $commenter['comment_author_email'] ) . '" size="30" maxlength="100" autocomplete="email" required="required"></p>',
				'url'    => '<p class="comment-form-url nw-comment-form__field"><label for="url">' . esc_html__( 'Link signature', 'neoweaver' ) . '</label><input id="url" name="url" type="url" value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" maxlength="200" autocomplete="url"></p>',
			),
		)
	);
	?>
</section>

<?php
if ( ! function_exists( 'neoweaver_comment_callback' ) ) :
	/**
	 * Render a single comment.
	 *
	 * @param WP_Comment $comment Comment object.
	 * @param array      $args    Comment arguments.
	 * @param int        $depth   Depth.
	 */
	function neoweaver_comment_callback( $comment, $args, $depth ) {
		$GLOBALS['comment'] = $comment;
		?>
		<li <?php comment_class( 'nw-comment' ); ?> id="comment-<?php comment_ID(); ?>">
			<article class="nw-comment__card">
				<div class="nw-comment__avatar">
					<?php echo get_avatar( $comment, $args['avatar_size'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>

				<div class="nw-comment__body">
					<header class="nw-comment__meta">
						<div>
							<h4 class="nw-comment__author"><?php echo esc_html( get_comment_author() ); ?></h4>
							<p class="nw-comment__timestamp">
								<a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
									<time datetime="<?php comment_time( 'c' ); ?>"><?php printf( esc_html__( '%1$s at %2$s', 'neoweaver' ), esc_html( get_comment_date() ), esc_html( get_comment_time() ) ); ?></time>
								</a>
							</p>
						</div>
						<?php if ( '0' === $comment->comment_approved ) : ?>
							<p class="nw-comment__moderation"><?php esc_html_e( 'Transmission pending moderation.', 'neoweaver' ); ?></p>
						<?php endif; ?>
					</header>

					<div class="nw-comment__content">
						<?php comment_text(); ?>
					</div>

					<div class="nw-comment__actions">
						<?php
						comment_reply_link(
							array_merge(
								$args,
								array(
									'depth'     => $depth,
									'max_depth' => $args['max_depth'],
									'reply_text'=> __( 'Reply', 'neoweaver' ),
								)
							)
						);
						edit_comment_link( __( 'Edit', 'neoweaver' ), '<span class="nw-comment__edit">', '</span>' );
						?>
					</div>
				</div>
			</article>
		<?php
	}
endif;
