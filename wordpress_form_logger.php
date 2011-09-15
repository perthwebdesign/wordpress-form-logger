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

	//.. Include Silex library.
	require_once __DIR__.'/silex.phar';
	use Symfony\Component\HttpFoundation\Response;

	/**
	 * 
	 */
	class WordpressFormLogger extends Silex\Application {
		
		var $PluginTemplateDirectory = NULL;
		var $ThemeTemplateDirectory = NULL;
		var $TemplateFileExtension = ".php";
		
		var $LogTableName = "wordpress_form_logger";
		
		function __construct() {
			parent::__construct();
			
			$this->PluginTemplateDirectory = __DIR__ . "/templates/"; 
			$this->ThemeTemplateDirectory = WP_CONTENT_DIR . "/themes/" . get_template() . "/formtemplates/";
			
			$this['debug'] = true;
			$this->register(new Silex\Extension\SessionExtension());
			$this->register(new Silex\Extension\DoctrineExtension(), array(
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
		}
		
		//.. Set and return unique user session id.
		function getSetUniqueID() {
			
			if ( is_null($this['session']->get('user')) ) {
				$this['session']->set('user', array('uuid' => uniqid()));
			}
			
			$UserSesion = $this['session']->get('user');
			return $UserSesion['uuid'];
		}
			
		//.. Checks to see if a template exists, and returns it.
		function getTemplateFile( $TemplateFilename ) {
			
			$TemplateFilename = $TemplateFilename . $this->TemplateFileExtension;
			
			if ( file_exists( $this->ThemeTemplateDirectory . $TemplateFilename ) ) {
				return file_get_contents( $this->ThemeTemplateDirectory . $TemplateFilename );
			} elseif ( file_exists( $this->PluginTemplateDirectory . $TemplateFilename ) ) {
				return file_get_contents( $this->PluginTemplateDirectory . $TemplateFilename );
			} else {
				return false;
			};
		}
		
		//.. Save submitted data to the log table.
		function setSubmittedResults( $FormName ) {
			
			$FormData = array( 
				'session_id' => $this->getSetUniqueID(),
				'name' => $FormName,
				'data' => $this->prepareSubmittedData($_REQUEST)
			);
			
			return $this['db']->insert(
				"wordpress_form_logger",
				$FormData
			);
		}

		//.. Readies the submitted for to be inserted into the log table.		
		private function prepareSubmittedData( $Data ) {
			return json_encode( $Data );	
		}
	
		//.. retrives any, already stored data from the log table.	
		function getSubmittedResults( $FormName, $UserID=null ) {
			
			if ( is_null( $UserID ) ) {
				$UserID = $this->getSetUniqueID();	
			}
			
			$Query = $this['db']->createQueryBuilder();
			$Query->select("*")
				->from("$this->LogTableName", "formLogger")
				->where("formLogger.session_id = '$UserID'")
				->andwhere("formLogger.name = '$FormName'")
			;
		
			return $Query->execute()->fetch();
		}
	}
	

	$app = new WordpressFormLogger; 


	
	//.. Sorts out which form to present to the user.
	$app->match('/form/{name}', function (Silex\Application $app, $name) use ($app) {
	
		//.. Establish a user id.
		$UniqueUserId = $app->getSetUniqueID();
	
		//.. Retrieve the template file
		$TemplateHTML = $app->getTemplateFile( $name );
	
		echo $TemplateHTML;
	
	});
	
	//.. write the form results to 
	$app->match('/saveform/{name}', function (Silex\Application $app, $name) use ($app) {
		
		$ReferringFormName = $name;
		
		
		$Query = $app['db']->createQueryBuilder();
		$Query->select("*")
			->from("$app->LogTableName", "formLogger")
			->where("formLogger.session_id = '$app->getSetUniqueID'")
			->andwhere("formLogger.name = '$ReferringFormName'")
		;
		
		$SubmissionResults = $Query->execute()->fetch();

		if( $SubmissionResults == false ) {
			$app->setSubmittedResults( $ReferringFormName );
		}
	});
	
	$app->match('/viewresults/{name}/{userid}', function (Silex\Application $app, $name, $userid) use ($app) {
			
		var_dump($app->getSubmittedResults( $name , $userid ));
	});

	

	var_dump($_SERVER['REQUEST_URI']);

	//.. Very Basic route setup. Only run on http://...../processimages/
	// if (
		// $_SERVER['REQUEST_URI'] == "/saveform/" ||
		// $_SERVER['REQUEST_URI'] == "/form/"
	// )
	// {
		$app->run();
	// }

