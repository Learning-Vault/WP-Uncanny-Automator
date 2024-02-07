<?php

use Uncanny_Automator\Recipe;

/**
 * Class Automator_Sample_Action
 */
class LV_Action {
	use Recipe\Actions;


	/**
	 * Automator_Sample_Action constructor.
	 */
	public function __construct() {
		$this->setup_action();
	}

	/**
	 *
	 */

	protected function setup_action() {

		$this->set_integration( 'LV' );
		$this->set_action_code( 'LV_CREATE_BADGE' );
		$this->set_action_meta( 'LV_BADGEDETAILS' );
		$this->set_requires_user( false );
		/* translators: Action - WordPress */
		$this->set_sentence( sprintf( esc_attr__( 'Create a {{Learning Vault Badge:%1$s}} ', 'uncanny-automator' ), $this->get_action_meta() ) );
		/* translators: Action - WordPress */
		$this->set_readable_sentence( esc_attr__( 'Create a {{Learning Vault Badge}}', 'uncanny-automator' ) );
		$options_group = array(
			$this->get_action_meta() => array(
				Automator()->helpers->recipe->field->text(
					array(
						'option_code' => 'API_KEY',
						'label'       => 'LV API Key',
						'description' => 'Get your API key from www.learningvault.io under Issuing Badges -> API',
						'placeholder' => '',
						'input_type'  => 'text',
						'default'     => '',
					)
				),

				Automator()->helpers->recipe->field->text(
					array(
						'option_code' => 'AUTH_TOKEN',
						'label'       => 'LV Authorisation Token',
						'description' => 'Get your Authorisation Token from www.learningvault.io under Issuing Badges -> API',
						'placeholder' => '',
						'input_type'  => 'text',
						'default'     => '',
					)
				),

				
				Automator()->helpers->recipe->field->text(
					array(
						'option_code' => 'ACTIVITY_ID',
						'label'       => 'Badge ID',
						'description' => 'Get your Badge ID from www.learningvault.io under Preparing Badges -> Managing Badges -> ',
						'placeholder' => '',
						'input_type'  => 'text',
						'default'     => '',
					)
				),
				
				Automator()->helpers->recipe->field->text(
					array(
						'option_code' => 'ACTIVITY_TIME',
						'label'       => 'Activity Time',
						'description' => "In the format of 2021-11-10T08:51:02.465Z OR pick the variable 'Date and Time' -> 'Current Date and time'",
						'placeholder' => '',
						'input_type'  => 'text',
						'default'     => '',
					)
				),



				/* translators: Email field */
				Automator()->helpers->recipe->field->text(
					array(
						'option_code' => 'USER_ID',
						'label'       => 'Email',
						'description' => 'Recipients Email address',
						'placeholder' => 'Enter from email',
						'input_type'  => 'email',
						'default'     => '',
					)
				),


				Automator()->helpers->recipe->field->text(
					array(
						'option_code' => 'FIRST_NAME',
						'label'       => 'First Name',
						'description' => '',
						'placeholder' => '',
						'input_type'  => 'text',
						'default'     => '',
					)
				),


				Automator()->helpers->recipe->field->text(
					array(
						'option_code' => 'LAST_NAME',
						'label'       => 'Last Name',
						'description' => '',
						'placeholder' => '',
						'input_type'  => 'text',
						'default'     => '',
					)
				),

			
			
				// Automator()->helpers->recipe->field->text(
				// 	array(
				// 		'option_code' => 'SAMPLE_EMAILBODY',
				// 		'label'       => 'Body',
				// 		'input_type'  => 'textarea',
				// 	)
				// ),
			),
		);

		$this->set_options_group( $options_group );

		$this->register_action();
	}

	/**
	 * @param int $user_id
	 * @param array $action_data
	 * @param int $recipe_id
	 * @param array $args
	 * @param $parsed
	 */
	protected function process_action( int $user_id, array $action_data, int $recipe_id, array $args, $parsed ) {
		$action_meta = $action_data['meta'];
		

		//Parse UTC Zulu Time
		$activityTime = DateTime::createFromFormat( 'Y-m-d\TH:i:s\Z' , Automator()->parse->text( $action_meta['ACTIVITY_TIME'], $recipe_id, $user_id, $args ));//->format('Y-m-d');

		if(!$activityTime){
			// try wordpress format with a space
			$activityTime = DateTime::createFromFormat( get_option( 'date_format' ). ' ' .get_option( 'time_format' ) . ' P', Automator()->parse->text( $action_meta['ACTIVITY_TIME'], $recipe_id, $user_id, $args ) . " ".wp_timezone_string( ));//->format('Y-m-d');
			if(!$activityTime){
				// try wordpress format without a space
				$activityTime = DateTime::createFromFormat( get_option( 'date_format' ).get_option( 'time_format' ) . ' P', Automator()->parse->text( $action_meta['ACTIVITY_TIME'], $recipe_id, $user_id, $args ) . " ".wp_timezone_string( ));//->format('Y-m-d');
			}
		}
		if(!$activityTime){
			$action_data["complete_with_errors"] = true;
			Automator()->complete->action( $user_id, $action_data, $recipe_id, "Could not parse Activity Date of: " . Automator()->parse->text( $action_meta['ACTIVITY_TIME'], $recipe_id, $user_id, $args ));
			return;
		}

		$data = array(
			'ActivityId'      => Automator()->parse->text( $action_meta['ACTIVITY_ID'], $recipe_id, $user_id, $args ),
			'ActivityTime'    => $activityTime->format('Y-m-d\TH:i:sP'),
			'UserId'      => Automator()->parse->text( $action_meta['USER_ID'], $recipe_id, $user_id, $args ),
			'Email'     => Automator()->parse->text( $action_meta['USER_ID'], $recipe_id, $user_id, $args ),
			'FirstName' => Automator()->parse->text( $action_meta['FIRST_NAME'], $recipe_id, $user_id, $args ),
			'LastName'    => Automator()->parse->text( $action_meta['LAST_NAME'], $recipe_id, $user_id, $args ),
		);


		
		$url = "https://badges.learningvault.io/api/ActivityEvents";

		  	$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));

			$headers = array(
				"Accept: application/json",
				"Content-Type: application/json",
				"ApiKey: ".Automator()->parse->text( $action_meta['API_KEY'], $recipe_id, $user_id, $args ),
				"Authorization: Bearer ".Automator()->parse->text( $action_meta['AUTH_TOKEN'], $recipe_id, $user_id, $args )
			 );

			 curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

			 //for debug only!
			 curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			 curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			 curl_setopt($curl, CURLOPT_HEADER, true);    // we want headers
			 //curl_setopt($curl, CURLOPT_NOBODY, true);    // we don't need body
			 $resp = curl_exec($curl);
			 $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			 curl_close($curl);

		// If there was an error, it'll be logged in action log with an error message.
		if ( $httpcode != 200 ) {
			//$error_message = $this->get_error_message();
			// Complete action with errors and log Error message.
			$action_data["complete_with_errors"] = true;
			$errMsg = "An Error Occured: HTTP " .$httpcode;
			if($httpcode == 406){
				//HTTP Invalid model
				$errMsg = sprintf("Invalid Model: %s", print_r($data, 1));
			}

			Automator()->complete->action( $user_id, $action_data, $recipe_id,  $errMsg);
			return;
		}
		// Everything went fine. Complete action.
		Automator()->complete->action( $user_id, $action_data, $recipe_id );
	}
}
