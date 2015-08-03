<?php
	/*
	Plugin Name: Evvent importer
	Plugin URI:  http://URI_Of_Page_Describing_Plugin_and_Updates
	Description: Importing events from evvnt into events calendar
	Version:     0.1
	Author:      Cosinschi
	Author URI:  http://URI_Of_The_Plugin_Author
	License:     GPL2
	License URI: https://www.gnu.org/licenses/gpl-2.0.html
	Domain Path: /languages
	Text Domain: my-toolset
	*/

	if ( !defined( 'ABSPATH' ) )
		die( '-1' );

	class evvntimport{

		


		function importevents() {

			$username = 'user';
			$password = 'pass';
			$today = date('Y-m-d',strtotime("-1 days"));
			$loginUrl = 'https://api.evvnt.com/events?newer_than='."$today".'&country=US&page=1';


	//init curl
			$ch = curl_init();

	//Set the URL to work with
			curl_setopt($ch, CURLOPT_URL, $loginUrl);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
	// ENABLE HTTP POST
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

	//Set the post parameters
			curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);


			$content =  curl_exec($ch);
			
			$info = curl_getinfo($ch);

			return json_decode($content,true);

			curl_close($ch);

			

		}


		function importcategories() {

			$username = 'user';
			$password = 'pass';
			$loginUrl = 'https://api.evvnt.com/categories';


	//init curl
			$ch = curl_init();

	//Set the URL to work with
			curl_setopt($ch, CURLOPT_URL, $loginUrl);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
	// ENABLE HTTP POST
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

	//Set the post parameters
			curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);


			$content =  curl_exec($ch);
			
			$info = curl_getinfo($ch);

			return json_decode($content,true);

			curl_close($ch);

			

		}


	}

	add_action('admin_menu', 'evvent_plugin_setup_menu');

	function evvent_plugin_setup_menu(){
		add_menu_page( 'Evvent Plugin Page', 'Evvent importer', 'manage_options', 'evvent-plugin', 'test_init','',29 );
	}



	function test_init(){
		global $wpdb;

		$evvent = new evvntimport();


		//$evventcategories=$evvent->importcategories();

		$evventcontent=$evvent->importevents();


		$args = array(
			'posts_per_page'   => -1,
			'post_type'        => 'event',	
			'post_status'      => 'publish',
			'suppress_filters' => true 
			);
		$posts_array = get_posts( $args ); 

		foreach ($posts_array as $posts) {
			$postc[] = $posts->post_title;
		}




	 //foreach ($posts_array as  $postcontent) {
	 	//echo $postcontent;
	 //}


		
		echo "<form method='post' action=''  >";

		foreach ($evventcontent as $key => $value) {
			//foreach ($value as $k => $v) {

			$checked='';

			if (in_array($value['title'], $postc)) {
				$checked = ' disabled="disabled" checked="checked" '; 

			}

			$date=date("m/d/Y",strtotime($value['start_time']));


			echo "<input type=\"checkbox\" name=\"events[]\" value='".$value['title']."' '.$checked.' />";





			echo $value['title'].'    ';
			echo ' - ','<b>',$value['venue']['town'],'</b>';

			echo "</br>";

			echo '<strong>',$value['summary'],'</strong>';

			echo "</br>";

			echo 'Start date -  ',$date;

			echo "</br>";

			echo "</br>";




			
		}




		echo "<input type=\"submit\" value=\"Import\">";

		echo "<input type=\"hidden\" name=\"ba\" value=\"1\" />";

		echo "</form>";



		

		
		
	} // end function test_init

	function insert_events() {

		if ( isset( $_POST['ba'])   ) {

			global $wpdb;

			$evventcontent=evvntimport::importevents();

			$checkbox=$_POST['events'];
			//echo '<pre>',print_r($evventcontent),'</pre>';

			foreach($evventcontent as $key => $value)
			{
				$evventcontent[$key] = (array) $value;
			}   

		//echo '<pre>',print_r($evventcontent[0]),'</pre>';

			$NewArray = array();

			for ($j=0; $j<count($checkbox);$j++) {
				for ($i=0; $i<count($evventcontent);$i++) {
					if ($checkbox[$j]==$evventcontent[$i]['title']) {
						$NewArray[] = $evventcontent[$i];
	    		//echo '<pre>',print_r($checkbox),'</pre>';
					}
				}




	} // end for loop






					//echo '<pre>',print_r($NewArray),'</pre>';



			 // end foreach




		// end second foreach
	//require(dirname(__FILE__) . '/wp-load.php');
	//global $user_ID;

	foreach ($NewArray as $k => $data) {
		


		
		$new_post= 
		array( 
			'post_author' => '1',	
			'post_content' => $data['description'],
			'post_title' => $data['title'],
			'post_name' => $data['title'],
			'post_type' => 'event',
			'post_status' => 'publish'
			
			
			);



		$post_id = wp_insert_post($new_post);

		$sdt = new DateTime( $data['start_time']);

		$sdate = $sdt->format('Y-m-d');
		$stime = $sdt->format('H:i:s');
		$sdtime = $sdt->format('Y-m-d H:i:s');

		$edt = new DateTime( $data['end_time']);

		$edate = $edt->format('Y-m-d');
		$etime = $edt->format('H:i:s');
		$edtime = $edt->format('Y-m-d H:i:s');


		$postarray= 
		array( 'zooming_factor'=>'13',
			'map_view'=>'Terrain Map',
			'address'=> $data[Venue][adress_1],
			'st_time'=> $stime,
			'end_time'=> $etime,
			'st_date'=> $sdate,
			'end_date'=> $edate,
			'set_st_time'=> $sdtime,
			'set_end_time'=> $edtime,
			'event_type'=> 'Regular Event',
			'organiser_name'=> $data[organiser_name],
			'organiser_contact'=> $data[contact][email],
			'organiser_website'=> $data[links][Facebook],
			'geo_latitude'=> $data[venue][latitude],
			'geo_longitude'=> $data[venue][longitude],
			'facebook'=> $data[links][Facebook],
			'website'=> $data[links][Website],
			'organizer_website'=> $data[links][Booking],
			'address'=> $data[venue][address_1].','.$data[venue][town]);

		$metainsert="INSERT IGNORE INTO ".$wpdb->prefix ."postmeta (post_id, meta_key,meta_value) VALUES ";
		foreach ( $postarray as $meta => $metval ) {
			$metainsert .= $wpdb->prepare(
				"(%d, %s, %s),",
				$post_id , $meta, $metval
				);
		}

		$metainsert = rtrim( $metainsert, ',' ) . ';';




		$wpdb->query( $metainsert);






		$city=$data[venue][town];
		$city_slug=$data[venue][town];

		$countryname=$data[venue][country];

		$scall_factor=13;
		$map_type='ROADMAP';
		$post_type='event';
		$categories='all,';

		//global $wpdb;

		$countryidsql=$wpdb->get_var( $wpdb->prepare("SELECT country_id FROM ".$wpdb->prefix ."countries WHERE iso_code_2= %s",$countryname) );
		update_post_meta( $post_id, 'country_id', $countryidsql);


		$que="INSERT IGNORE INTO ".$wpdb->prefix ."multicity (country_id, cityname,city_slug,scall_factor,map_type,post_type,categories,lat,lng) VALUES (%d,%s, %s, %d,%s,%s,%s,%s,%s)";
		$wpdb->query($wpdb->prepare($que,$countryidsql, $city,$city_slug,$scall_factor,$map_type,$post_type,$categories,$data[venue][latitude],$data[venue][longitude]));

		$taxonomyid=80;
		$order=0;

		$blah="INSERT IGNORE INTO ".$wpdb->prefix ."term_relationships (`object_id` ,`term_taxonomy_id` ,`term_order`) VALUES (%d,%d,%d)";
		$wpdb->query($wpdb->prepare($blah,$post_id,$taxonomyid,$order));




		$cityidsql=$wpdb->get_var( $wpdb->prepare("SELECT city_id FROM ".$wpdb->prefix ."multicity WHERE cityname= %s",$city) );
		update_post_meta( $post_id, 'post_city_id', $cityidsql);





	/**$state=$args['Venue'][StateProvince];

	$sqll=$wpdb->get_var( $wpdb->prepare("SELECT zones_id FROM $wpdb->prefix . 'zones'  WHERE country_id=%d AND zone_code=$s",$country_id,$state) );
	update_post_meta( $event_id, 'zones_id', $sqll);
	$blahh="UPDATE $wpdb->prefix . 'multicity' SET zones_id=%s WHERE country_id=%d AND city_name=%s";
	$wpdb->query($wpdb->prepare($blahh,$sqll,$country_id,$city));*/

	$permalink=get_permalink( $post_id);

	$url=array("url"=>$permaink);

	$url=json_encode($url);

	$username = 'user';
	$password = 'pass';
	$loginUrl = 'https://api.evvnt.com/publishers/4930/published_events/'.$data[id];

	




	/** $ch = curl_init();

	        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));

	curl_setopt($ch, CURLOPT_URL, $loginUrl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);




	//Set headers



	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	curl_setopt($ch, CURLOPT_POSTFIELDS,$url);


	$content =  curl_exec($ch);

	$info = curl_getinfo($ch);

	print_r($info);

	curl_close($ch); */




	if (isset($data['image_url'])) {

		$url         = 'http:'. $data['image_url'] ;
		$uploads     = wp_upload_dir();
		$wp_filetype = wp_check_filetype( $url, null );
		$filename    = wp_unique_filename( $uploads['path'], basename('evvnt_' . $post_id  ), $unique_filename_callback = null ) . '.' . $wp_filetype['ext'];
		$full_path   = $uploads['path'] . "/" . $filename;



		$file_saved = file_put_contents( $uploads['path'] . "/" . $filename,file_get_contents($url));

		
			// Attach to the event
		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
			'post_content' => '',
			'post_status' => 'inherit',
			'guid' => $uploads['url'] . "/" . $filename
			);

		$attach_id = wp_insert_attachment( $attachment, $full_path, $post_id );

		

			// Set as featured image
		set_post_thumbnail($post_id, $attach_id);

			// Attach attachment metadata
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$attach_data = wp_generate_attachment_metadata( $attach_id, $full_path );
		wp_update_attachment_metadata( $attach_id,  $attach_data );

		} //end isset image



			} // end foreach $newarray

			

		} // end if isset POST

	} // end insert_events function

	add_action( 'init', 'insert_events' );
