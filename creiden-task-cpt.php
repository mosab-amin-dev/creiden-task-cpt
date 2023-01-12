<?php
/*
Plugin Name: creiden-task
 */

// Register Item Post Type
function creiden_task_cpt_callback() {

	$args = array(
		'label'     => __( 'Item', 'text_domain' ),
		'public'    => true,
		'supports'	=>['title','editor']
	);
	register_post_type( 'Item', $args );

}
add_action( 'init', 'creiden_task_cpt_callback');

//Just a way to standardize the APIs response
 function apiResponse($data = null  , $code = 200 , $message = null , $paginate = null){
	$arrayResponse = [
		'data' => $data ,
		'status' => $code == 200 || $code==201 || $code==204 || $code==205 ,
		'message' => $message ,
		'code' => $code ,
		'paginate' => $paginate
	];
	return new WP_REST_Response($arrayResponse,$code);
}
//Defining routes
add_action( 'init',function(){
	register_rest_route( 'test', '/items/', array(
		'methods'=>'GET',
		'callback'=>'index_callback'
	) );
});
add_action( 'init',function(){
	register_rest_route( 'test', '/items/(?P<item_id>\d+)', array(
		'methods'=>'GET',
		'callback'=>'view_callback'
	) );
});

add_action( 'init',function(){
	register_rest_route( 'test', '/items/', array(
		'methods'=>'POST',
		'callback'=>'create_callback'
	) );
});

add_action( 'init',function(){
	register_rest_route( 'test', '/items/(?P<item_id>\d+)', array(
		'methods'=>'PUT',
		'callback'=>'update_callback'
	) );
});
add_action( 'init',function(){
	register_rest_route( 'test', '/items/(?P<item_id>\d+)', array(
		'methods'=>'DELETE',
		'callback'=>'delete_callback'
	) );
});
//Item requests
function post_item_request($request){
	$post['post_title']=sanitize_text_field($request->get_param('title'));
	$post['post_content']=sanitize_text_field($request->get_param('content'));
	$post['post_status']='publish';
	$post['post_type']='item';
	return $post;
}
function put_item_request($request,$old_post){
	$post['ID']=$old_post->ID;
	$post['post_title']=sanitize_text_field($request->get_param('title'));
	$post['post_content']=sanitize_text_field($request->get_param('content'));
	$post['post_status']='publish';
	$post['post_type']='item';
	return $post;
}

//Item Crud callback functions
function index_callback($request){
	$posts=get_posts(['post_type'=>'item','post_status'=>'publish']);
	wp_reset_postdata();
	if(count($posts)>0){
		return apiResponse($posts,200,__('get_successful','text_domain'));
	}
	return apiResponse([],200,__('there_are_no_data','text_domain'));
}
function view_callback($request){
	$post=get_post($request->get_param('item_id'));
	wp_reset_postdata();
	if($post){
		return apiResponse($post,200,__('get_successful','text_domain'));
	}
	return apiResponse(null,200,__('there_are_no_data','text_domain'));
}

function create_callback($request){
	$new_post_id=wp_insert_post(post_item_request($request));
	if(!is_wp_error($new_post_id)){
		$post=get_post($new_post_id);
		return apiResponse($post,201,__('created_successful','text_domain'));
	}
	return apiResponse(null,400,__('there_are_no_data','text_domain'));
}
function update_callback($request){
	 $post=get_post($request->get_param('item_id'));
	if($post){	
		$updated_post=put_item_request($request,$post);
		$post=get_post($post=wp_update_post($updated_post));
			wp_reset_postdata();
		return apiResponse($post,200,__('updated_successful','text_domain'));
	}
	return apiResponse(null,200,__('there_are_no_data','text_domain'));
}

function delete_callback($request){
	$post=get_post($request->get_param('item_id'));
	if($post){	
		$post=wp_delete_post($post->ID);
			wp_reset_postdata();
	return apiResponse($post->ID,200,__('deleted_successful','text_domain'));
}
return apiResponse(null,200,__('there_are_no_data','text_domain'));
}