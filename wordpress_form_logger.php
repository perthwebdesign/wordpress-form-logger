<?php
/*
Plugin Name: Wordpress Form Logger
Plugin URI: http://www.pwd.net.au
Description: Handles logging of forms from wordpress frontend
Author: Matt Boddy
Version: 0.1
Last Updated: 13/09/2011
Author URI: http://www.pwd.net.au
*/

require_once __DIR__.'/silex.phar';

	$app = new Silex\Application(); 

	$app['debug'] = true;
	$app->register(new Silex\Extension\SessionExtension());
	$app->register(new Silex\Extension\DoctrineExtension(), array(
	    'db.options' => array (
	        'driver'    => 'pdo_mysql',
	        'host'      => DB_HOST,
	        'dbname'    => DB_NAME,
	        'user'      => DB_USER,
	        'password'  => DB_PASSWORD,
	    ),
	    'db.dbal.class_path'    => __DIR__.'/vendor/doctrine-dbal/lib',
	    'db.common.class_path'  => __DIR__.'/vendor/doctrine-common/lib',
	));
	
	use Symfony\Component\HttpFoundation\Response;
	
	
	//.. display a test form
	$app->match('/form/', function () use ($app) {
		
		$response = new Response();
		
		if ( is_null($app['session']->get('user')) ) {
			$app['session']->set('user', array('uuid' => uniqid()));
		}
		
		$UserSesion = $app['session']->get('user');
		
		$html = '<form method="post" action="/saveform/">';
		$html .= '<input type="text" name="pants" />';
		$html .= '<input type="text" name="pants1" />';
		$html .= '<input type="text" name="pants2" />';
		$html .= '<input type="text" name="pants3" />';
		$html .= '<input type="submit" />';
		$html .= '</form>';
		
		echo $html;
		die("test");
	});
	
	//.. write the form results to 
	$app->match('/saveform/', function () use ($app) {
		
		//.. establish referring form
		$ReferringFormName = end(
			explode( "/",
				trim( $_SERVER['HTTP_REFERER'], "/" )
			)
		);
		
		$UserSession = $app['session']->get('user');
		$UUID = $UserSession['uuid'];
		
		
		$Query = $app['db']->createQueryBuilder();
		$Query->select("*")
			->from("wordpress_form_logger", "formLogger")
			->where("formLogger.session_id = '$UUID'")
			->andwhere("formLogger.name = '$ReferringFormName'")
		;
		
		$SubmissionResults = $Query->execute()->fetch();

		if( $SubmissionResults == false ) {
			$FormData = array( 
				'session_id' => $UserSession['uuid'],
				'name' => $ReferringFormName,
				'data' => json_encode($_REQUEST)
			);
			
			//.. Insert into logger table
			$app['db']->insert(
				"wordpress_form_logger",
				$FormData
			);
		}
	});


	//.. Very Basic route setup. Only run on http://...../processimages/
	if (
		$_SERVER['REQUEST_URI'] == "/saveform/" ||
		$_SERVER['REQUEST_URI'] == "/form/"
	)
	{
		$app->run();
	}

