<?php

// edit on github test master

// Theme được chia sẻ bởi Thế Hòa
// Bạn có quyền sao chép, sửa đổi nhưng vui lòng giữ lại 1 dòng bản quyền ở footer. Cám ơn bạn đã ủng hộ :)

/** Start the engine **/
require_once(TEMPLATEPATH.'/lib/init.php');

/** Add support for custom background **/
if (function_exists('add_custom_background')) {
    add_custom_background();
}

/** Việt hóa khung Breadcrumb
=> Tác giả: UnlimiteD
@ Chưa có địa chỉ liên lạc ^^!
**/
add_filter('genesis_breadcrumb_args', 'custom_breadcrumb_args');
function custom_breadcrumb_args($args) {
   $args['labels']['prefix'] = 'Bạn đang ở: '; //marks the spot
   $args['sep'] = ' / ';
   $args['home'] = 'Trang chủ';
   $args['author'] = 'Tác giả: ';
   $args['tag'] = 'Thẻ: ';
   $args['date'] = 'Ngày: ';
   $args['search'] = 'Tìm: ';
   $args['tax'] = '';
   return $args;
}
/** Bài viết liên quan / Bài viết cùng chuyên mục
=> Tác giả: Nutatu
@ Địa chỉ: dethanhcong.com
**/
function related_posts_shortcode( $atts ) {
	extract(shortcode_atts(array(
	    'limit' => '5', //So bai viet hien thi
	), $atts));

	global $wpdb, $post, $table_prefix;

	if ($post->ID) {
		$retval = '<ul style="font-weight:bold;">';
 		// Get tags
		$tags = wp_get_post_tags($post->ID);
		$tagsarray = array();
		foreach ($tags as $tag) {
			$tagsarray[] = $tag->term_id;
		}
		$tagslist = implode(',', $tagsarray);

		// Do the query
		$q = "SELECT p.*, count(tr.object_id) as count
			FROM $wpdb->term_taxonomy AS tt, $wpdb->term_relationships AS tr, $wpdb->posts AS p WHERE tt.taxonomy ='post_tag' AND tt.term_taxonomy_id = tr.term_taxonomy_id AND tr.object_id  = p.ID AND tt.term_id IN ($tagslist) AND p.ID != $post->ID
				AND p.post_status = 'publish'
				AND p.post_date_gmt < NOW()
 			GROUP BY tr.object_id
			ORDER BY count DESC, p.post_date_gmt DESC
			LIMIT $limit;";

		$related = $wpdb->get_results($q);
 		if ( $related ) {
			foreach($related as $r) {
				$retval .= '<li><a style="font-weight:bold;" title="'.wptexturize($r->post_title).'" href="'.get_permalink($r->ID).'">'.wptexturize($r->post_title).'</a></li>';
			}
		} else {
			$retval .= '<li>No related posts found</li>';
		}
		$retval .= '</ul></div>';
		return $retval;
	}
	return;
}
add_shortcode('related_posts', 'related_posts_shortcode');
function htr_relate($content){
	$content=$content;
        if (is_single()){
	$content.='<div class="bvlq" style="float: left;"><h3>Bài viết liên quan</h3>';
	$content.='[related_posts]';
    }
	return $content;
}
add_filter ('the_content', 'htr_relate');
// Bài cùng chuyên mục
function bai_viet_chuyen_muc( $atts ) {
	extract(shortcode_atts(array(
	    'limit' => '5',
	), $atts));
	global $post;
	$related_cat='<ul style="font-weight:bold;">';
	$categories = get_the_category($post->ID);
	if ($categories) {
		$category_ids = array();
		foreach($categories as $individual_category) $category_ids[] = $individual_category->term_id;
		$args=array(
			'category__in' => $category_ids,
			'post__not_in' => array($post->ID),
			'showposts'=>$limit,
			'caller_get_posts'=>1
		);
		$rl_cat = new wp_query($args);
		if( $rl_cat->have_posts() ) {
			while ($rl_cat->have_posts()) {
				$rl_cat->the_post();
				$post = get_post($post_id);
				$title=$post->post_title;
				$link=get_permalink($post->id);
				$related_cat.='<li><a href="'.$link.'" title="'.$title.'">'.$title.'</a></li>';
			}
		}
		wp_reset_query();
	}
	$related_cat.= '</ul></div>';
	return $related_cat;
}
add_shortcode('baivietcm', 'bai_viet_chuyen_muc');
function dtc_bai_cung_chuyen_muc($content){
	$content=$content;
        if (is_single()){
	$content.='<div class="bvnn" style="float:right;"><h3>Bài cùng chuyên mục</h3>';
	$content.='[baivietcm]';
    }
	return $content;
}
add_filter ('the_content', 'dtc_bai_cung_chuyen_muc');

/** Thêm Google tìm kiếm tùy chỉnh và Việt hóa khung tìm kiếm
 => Tác giả: Lion Phạm
 @ địa chỉ: afublog.com
*/
remove_filter('get_search_form','genesis_search_form');
add_filter('get_search_form', 'genesis_google_search_form');
function genesis_google_search_form() {
$search_text = get_search_query() ? esc_attr( apply_filters( 'the_search_query', get_search_query() ) ) : apply_filters('genesis_search_text', esc_attr__('Nhập từ khóa tìm kiếm ...', 'genesis'));
$button_text = apply_filters( 'genesis_search_button_text', esc_attr__( 'Tìm kiếm', 'genesis' ) );
$onfocus = " onfocus=\"if (this.value == '$search_text') {this.value = '';}\"";
$onblur = " onblur=\"if (this.value == '') {this.value = '$search_text';}\"";
$form = '
<form action="http://thehoa.s2u.vn/tim/" id="cse-search-box">
    <input type="hidden" name="cx" value="003464906602381273473:zrcfvwq_-ec" />
    <input type="hidden" name="cof" value="FORID:10" />
    <input type="hidden" name="ie" value="UTF-8" />
<input type="search" value="'. $search_text .'" name="q" class="s" id="searchSite" title="Tìm kiếm thông tin"'. $onfocus . $onblur .' />
<input type="submit" class="searchsubmit" name="sa" value="'. $button_text .'" />
</form>
';
return apply_filters('genesis_google_search_form', $form, $search_text, $button_text);
}

/** Thêm chính sách phản hồi, bình luận
 => Tác giả: Lion Phạm
 @ địa chỉ: afublog.com
*/
add_action( 'genesis_after_comments', 'single_post_comment_policy' );
function single_post_comment_policy() {
    if ( is_single() ) {
    ?>
    <div class="comment-policy-box">
        <p class="comment-policy"><strong>Chính sách phản hồi / bình luận:</strong><br />Mọi phản hồi, bình luận của bạn đều sẽ được xét duyệt trước khi cho đăng tải! The Hoa's Blog sẽ không chấp nhận những phản hồi, bình luận có chứa một trong những nội dung sau:<br />
		<ul><li>- Phản hồi, bình luận không có tinh thần thiện chí, xây dựng, cầu thị, không liên quan tới chủ đề của bài viết!</li>
		<li>- Phản hồi, bình luận không được viết bằng tiếng Việt (có dấu) hoặc tiếng Anh, sử dụng từ ngữ thiếu văn hóa, thô tục, kích động, phản động hay spam gây ảnh hưởng tới bài viết cũng như tới bạn đọc khác.</li>
		</ul></p>
    </div><br />
    <?php
    }
}

/** Thêm thông tin tác giả ở cuối bài viết
 => Tác giả: Thế Hòa
 @ email: thehoa@s2u.vn
*/
add_action( 'genesis_before_comments', 'single_post_author_box' );
function single_post_author_box() {
    if ( is_single() ) {
    ?>
<div class="author-box">
<p><?php echo get_avatar( get_the_author_email(), '72' ); ?>
<?php _e("Tác giả: ", 'studiopress'); ?> <strong><?php the_author(); ?></strong><br />
<?php the_author_meta( 'description' ); ?></p>
<div class="clear"></div>
</div>
    <?php
    }
}

/* Việt hóa tiêu đề Bình luận
 => author: Brian Gardner
 @ link: http://dev.studiopress.com/modify-speak-your-mind.htm
*/
add_filter('genesis_title_comments', 'custom_genesis_title_comments');
function custom_genesis_title_comments() {
    $title = '<h3>Các phản hồi, bình luận:</h3>';
    return $title;
}

// Việt hóa "speak your mind"
add_filter('genesis_comment_form_args', 'custom_comment_form_args');
function custom_comment_form_args($args) {
    $args['title_reply'] = 'Viết ý kiến, cảm nhận của bạn:';
    return $args;
}

/* Việt hóa và chỉnh sửa chân trang theo ý các bạn :]
 => author: Brian Gardner
 @ link: http://dev.studiopress.com/customize-footer-section.htm
*/
remove_action( 'genesis_footer', 'genesis_do_footer' );
add_action( 'genesis_footer', 'child_do_footer' );
function child_do_footer() {
    ?>
    <div class="wrap"> 
<div class="gototop"><p><a href="#wrap">Lên đầu trang</a></p></div><div class="creds"><p>Copyright &copy; 2011 - Template <strong>G-rebuild</strong> by <a href="http://thehoa.s2u.vn">The Hoa</a></p></div>
	</div><!-- end .wrap -->
    <?php
}

// Việt hóa cái chữ Home trên menu :D
add_action( 'genesis_nav_home_text', 'child_change_nav_home_text' );
function child_change_nav_home_text() {
    return 'Trang chủ';
}

// Việt hóa Read more
add_filter('excerpt_more', 'get_the_content_more_link');
add_filter( 'the_content_more_link', 'get_the_content_more_link' );
function get_the_content_more_link() {
    return '<br />[<a class="more-link" href="' . get_permalink() . '" rel="nofollow">Đọc tiếp &#x2026;</a>]';
}

// Đem cái menu phụ lên top cho nó đỉnh :))
remove_action('genesis_after_header', 'genesis_do_subnav');
add_action('genesis_before_header', 'genesis_do_subnav');

// Thêm thông tin tác giả vào trang author
function genesis_extra_author_archive_author_box(){
if( is_author() )
add_action('genesis_before_loop', 'genesis_author_box', 15);
}
add_action('genesis_before_loop','genesis_extra_author_archive_author_box');

// Sửa thông tin bài viết
add_filter('genesis_post_info', 'post_info_filter');
function post_info_filter($post_info) {
if (!is_page()) {
    $post_info = 'Viết ngày: [post_date], tác giả: [post_author_posts_link], lúc: [post_time] [post_comments] [post_edit]';
    return $post_info;
}}

// Việt hóa số bình luận trên thông tin bài viết
add_filter('genesis_post_comments_shortcode', 'custom_post_comments_shortcode');
function custom_post_comments_shortcode($output){
    $output = preg_replace('/#comments"\>(\d+) Comments/', '#comments"><span class="number">Có ${1}</span> bình luận', $output);
    return $output;
}

// Thêm thanh điều hướng Bài viết trước và sau
add_action('genesis_before_comments', 'custom_post_nav');
function custom_post_nav(){?>
    <div class="post-nav">
    <div class="next-post-nav">
    <span class="next">Bài trước:</span> <?php next_post_link('%link', '%title'); ?>
    </div>
    <div class="prev-post-nav">
    <span class="prev">Bài sau:</span> <?php previous_post_link('%link', '%title'); ?>
    </div>
    </div>
<?php
}

?>
