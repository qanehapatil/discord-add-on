<?php
/**
 * Admin setting
 */
class Ets_Pmpro_Admin_Setting
{
	function __construct()
	{
		// Add new menu option in the admin menu.
		add_action('admin_menu', array($this, 'ets_add_new_menu'));

		// Add script for back end.	
		add_action( 'admin_enqueue_scripts', array($this, 'ets_add_script' ));

		// Add script for front end.
		add_action('wp_enqueue_scripts', array($this, 'ets_add_script'));

		//Add new button in pmpro profile
		add_action('pmpro_account_bullets_bottom', array( $this, 'add_connect_discord_button' ));

		//Discord api callback
		add_action('init', array( $this, 'discord_api_callback' ));

		//change hook call on cancel and change
		add_action('pmpro_after_change_membership_level', array($this, 'change_discord_role_from_pmpro'), 10, 3);

		//Pmpro expiry
		add_action('pmpro_membership_post_membership_expiry', array($this, 'pmpro_expiry_membership'), 10 ,2);

		//front ajax function to disconnect from discord
		add_action('wp_ajax_disconnect_from_discord', array($this, 'disconnect_from_discord'));

		//back ajax function to disconnect from discord
        add_action('wp_ajax_nopriv_disconnect_from_discord', array($this, 'disconnect_from_discord'));
	}

	/**
	 * Function Name:- ets_add_script();
	 *
	 * Description:- localized script and style 
	 *
	 * @param:- None; 
	 *
	 * @return:- None; 
	 */
	public function ets_add_script(){

		wp_register_style(
		    'ets_pmpro_add_discord_style',
		    ETS_PMPRO_DISCORD_URL. 'asset/css/ets-pmpro-discord-style.css'
		); 
		wp_enqueue_style( 'ets_pmpro_add_discord_style');
	  
	    wp_register_script(
			'ets_pmpro_add_discord_script',
			ETS_PMPRO_DISCORD_URL . 'asset/js/ets-pmpro-add-discord-script.js',
			array('jquery')
		);
        wp_enqueue_script( 'ets_pmpro_add_discord_script' );
		
	 	$script_params = array(
			'admin_ajax' 		=> admin_url('admin-ajax.php')
		);  

	  	wp_localize_script( 'ets_pmpro_add_discord_script', 'etsPmproParams', $script_params ); 
	}

	/**
	 * Function Name:- ets_add_new_menu()
	 *
	 * Description:- add menu in admin dashboard.
	 *
	 * @param:- None;
	 *
	 * @return:- Add menu in admin dashboard. 
	 */
	public function ets_add_new_menu(){
		add_menu_page(__( 'ETS Settings', 'ets_pmpro_discord' ), __( 'ETS Settings', 'ets_pmpro_discord' ), 'manage_options', 'discord-options', array( $this, 'ets_setting_page' ), 'dashicons-admin-generic', 59);
	}

	/**
	 * Function Name:- ets_setting_page()
	 *
	 * Description:- new menu Description.
	 *
	 * @param:- menu_id;
	 *
	 * @return:- Show 2 tab in the page. 
	 */
	public function ets_setting_page(){
		$ets_discord_client_id = isset($_POST['ets_discord_client_id']) ? sanitize_text_field(trim($_POST['ets_discord_client_id'])) : '';

		$discord_client_secret = isset($_POST['ets_discord_client_secret']) ? sanitize_text_field(trim($_POST['ets_discord_client_secret'])) : '';

		$discord_bot_token = isset($_POST['ets_discord_bot_token']) ? sanitize_text_field(trim($_POST['ets_discord_bot_token'])) : '';

		$ets_discord_redirect_url = isset($_POST['ets_discord_redirect_url']) ? sanitize_text_field(trim($_POST['ets_discord_redirect_url'])) : '';

		$ets_discord_guild_id = isset($_POST['ets_discord_guild_id']) ? sanitize_text_field(trim($_POST['ets_discord_guild_id'])) : '';

		$ets_discord_roles = isset($_POST['ets_discord_role_mapping']) ? sanitize_textarea_field(trim($_POST['ets_discord_role_mapping'])) : '';
		
		if($ets_discord_client_id)
			update_option('ets_discord_client_id',$ets_discord_client_id);
		
		if($discord_client_secret)
			update_option('ets_discord_client_secret', $discord_client_secret);
		
		if($discord_bot_token) {
			update_option('ets_discord_bot_token', $discord_bot_token);
		}

		if($ets_discord_redirect_url) {
			update_option('ets_discord_redirect_url', $ets_discord_redirect_url);
		}

		if ( $ets_discord_guild_id ) {
			update_option('discord_guild_id', $ets_discord_guild_id);
		}

		if ( $ets_discord_roles ) {
			$ets_discord_roles = stripslashes( $ets_discord_roles );
			update_option('ets_discord_role_mapping',$ets_discord_roles);
		}

		$currUserName = "";
		$currentUser = wp_get_current_user();
		if ($currentUser) {
			$currUserName = $currentUser->user_login;
		}
		$ets_discord_client_id = get_option('ets_discord_client_id');
		$discord_client_secret = get_option('ets_discord_client_secret');
		$discord_bot_token = get_option('ets_discord_bot_token');
		$ets_discord_redirect_url = get_option('ets_discord_redirect_url');
		$ets_discord_roles = get_option('ets_discord_role_mapping');
		$ets_discord_guild_id = get_option('discord_guild_id');
		?>
		<h1><?php echo __("Discord App Settings","ets_pmpro_discord");?></h1>
		<div class="tab ets-tabs">
		  <button class="ets_tablinks active" onclick="openTab(event, 'ets_setting')"><?php echo __("Discord Settings", "ets_pmpro_discord"); ?></button>
		  <button class="ets_tablinks" onclick="openTab(event, 'ets_about_us')"><?php echo __("Support", "ets_pmpro_discord"); ?>	
		  </button> 
		</div>

		<div id="ets_setting" class="ets_tabcontent">
			<h3><?php echo __("Discord Settings", "ets_pmpro_discord");?></h3>
			<form method="post" action="#">
			  	<div class="ets-input-group">
			  		<label><?php echo __("Client ID", "ets_pmpro_discord");?> :</label>
			  			<input type="text" class="ets-input" name="ets_discord_client_id" value="<?php if(isset($ets_discord_client_id))echo $ets_discord_client_id;?>" required placeholder="Discord Client ID">
			  	</div>
			  	<div class="ets-input-group">
			  		<label><?php echo __( "Client Secret", "ets_pmpro_discord" );?> :</label>
			  			<input type="text" class="ets-input" name="ets_discord_client_secret" value="<?php if(isset($discord_client_secret))echo $discord_client_secret;?>" required placeholder="Discord Client Secret">
			  	</div>
			  	<div class="ets-input-group">
			  		<label><?php echo __( "Bot Token", "ets_pmpro_discord" );?> :</label>
			  			<input type="text" class="ets-input" name="ets_discord_bot_token" value="<?php if(isset($discord_bot_token))echo $discord_bot_token;?>" required placeholder="Discord Bot Token">
			  	</div>
			  	<div class="ets-input-group">
			  		<label><?php echo __( "Redirect URL", "ets_pmpro_discord" );?> :</label>
			  			<input type="text" class="ets-input" name="ets_discord_redirect_url"
			  			placeholder="Discord Redirect Url" value="<?php if(isset($ets_discord_redirect_url))echo $ets_discord_redirect_url;?>" required>
			  			<p class="description"><?php echo __( "Registered discord app url", "ets_pmpro_discord" );?></p>
			  	</div>
			  	<div class="ets-input-group">
			  		<label><?php echo __( "Guild Id", "ets_pmpro_discord" );?> :</label>
			  			<input type="text" class="ets-input" name="ets_discord_guild_id"
			  			placeholder="Discord Guild Id" value="<?php if(isset($ets_discord_guild_id))echo $ets_discord_guild_id;?>" required>
			  	</div>
			  	<div class="ets-input-group">
			  		<label><?php echo __( "Discord Roles PMPRO-Level Mappings", "ets_pmpro_discord" );?> :</label>
		  			<textarea class="ets-input" name="ets_discord_role_mapping"
			  			placeholder="Discord Roles PMPRO-Level Mappings" required><?php if(isset($ets_discord_roles))echo stripslashes($ets_discord_roles);?></textarea>
			  	</div>
			  	<p>
			  		<button type="submit" name="submit" value="ets_submit" class="ets-submit">
			  			<?php echo __("Submit", "ets_pmpro_discord");?>
			  		</button>
			  	</p>
			</form>
		</div>
		<div id="ets_about_us" class="ets_tabcontent">
			<div class="ets-details"> 
				<div class="ets-com-logo">
					<div class="ets-co-logo" > 
						<img src= <?php echo ETS_PMPRO_DISCORD_URL."asset/images/user-original.png;"?> > 
					</div>
				</div>
				<div class="ets-detail-dec"> 
					<h2><?php echo __("ExpressTech Software Solution Pvt Ltd","ets_pmpro_discord"); ?>.</h2>
					<a href="https://www.expresstechsoftwares.com/">
					<?php echo __("ExpressTech Software Solution Pvt Ltd", "ets_pmpro_discord"); ?>.</a>
					<?php echo __("is the leading Enterprise Wordpress development company", "ets_pmpro_discord"); ?>.
					<?php echo __("Contact us for any Wordpress Related development project", "ets_pmpro_discord"); ?>
					.<br> 
					<span><b><?php echo __("Email","ets_pmpro_discord"); ?>: </b>
					<a href="mailto:contact@expresstechsoftwares.com">contact@expresstechsoftwares.com</a> , 
					<a href="mailto:business@expresstechsoftwares.com">business@expresstechsoftwares.com</a>
					</span><br>
					<span><b><?php echo __("Skype","ets_pmpro_discord"); ?>: </b> ravi.soni971</span>
				</div>
			</div>
		   
			<div class="ets-support-lavel">
				<div class="ets-supp-form">
				  	<form accept="#" method="post">
						<table class="form-table">
							<tbody>						
								<tr>
									<th scope="row">
										<?php echo __("Full Name","ets_pmpro_discord"); ?>	 
									</th>
									<td>
										<input type="text" name="ets_user_name" placeholder="Enter Name" class="regular-text" required=""
										value="<?php echo $currUserName;
										 ?>">
										<p class="description">
											<?php echo __("Write your full name","ets_pmpro_discord");?>	
										</p>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo __("Contact Email","ets_pmpro_discord");?> 
									</th>
									<td>
										<input type="email" name="ets_user_email" placeholder=" Enter email" class="regular-text" required="" value="<?php echo get_option('admin_email');
										 ?>">
										<p class="description"><?php echo __("Write your contact email","ets_pmpro_discord");?></p>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<?php echo __("Subject","ets_pmpro_discord"); ?> 
									</th>
									<td>
										<input type="text" name="ets_support_subject" placeholder=" Enter your subject" class="regular-text" required="">
										<p class="description"><?php echo __("Write your support subject","ets_pmpro_discord");?></p>
								
									</td>
								</tr>
								<tr>
									<th scope="row">
									<?php echo __("Message","ets_pmpro_discord"); ?> 
									</th>
									<td>
										<textarea name="ets_support_msg" required="" class="ets-regular-text"></textarea>
										<p class="description"><?php echo __("Write your support message","ets_pmpro_discord");?></p>
									</td>
								</tr>
							</tbody>
						</table>
						<p class="submit">
							<input type="submit" name="save" id="save" class="ets-submit" value="Send">
						</p>
					</form>
				</div> 
			</div>
		</div>
		<?php
		$this->get_Support_Data();
	}

	/**
	 * Function get_Support_Data().
	 *
	 * Description:- send mail to submit support form.  
	 *
	 * @param None.
	 *
	 * @return None. 
	*/
	public function get_Support_Data()
	{
		if (isset($_POST['save'])) {
			$etsUserName 	= isset($_POST['ets_user_name']) ? sanitize_text_field(trim($_POST['ets_user_name'])) : "";
			$etsUserEmail 	= isset($_POST['ets_user_email']) ? sanitize_text_field(trim($_POST['ets_user_email'])) : "";
			$message  		= isset($_POST['ets_support_msg']) ? sanitize_text_field(trim($_POST['ets_support_msg'])) : "";
			$sub  			= isset($_POST['ets_support_subject']) ? sanitize_text_field(trim($_POST['ets_support_subject'])) : "";

			if($etsUserName && $etsUserEmail && $message && $sub){
				$subject 		= $sub;
				$to 			= 'contact@expresstechsoftwares.com';
				$content 		= "Name: " .$etsUserName."<br>";
				$content 		.= "Contact Email: " .$etsUserEmail."<br>";
				$content		.=  "Message: ".$message;
			    $headers 		= array();
			    $blockemail 	= get_bloginfo("admin_email");
				$headers[] 		= 'From: '.get_bloginfo("name") .' <'.$blockemail.'>'."\r\n";
				$mail = wp_mail( $to, $subject, $content, $headers );
			} 	
		}
	}

	/**
	 * Function Name:- add_connect_discord_button();
	 *
	 * Description:- Add link in pmpro profile 
	 *
	 * @param:- None; 
	 *
	 * @return:- new link; 
	 */
	public function add_connect_discord_button()
	{	
		$user_id = get_current_user_id();
		$access_token = get_user_meta( $user_id, "discord_access_token", true );
		if ($access_token) {
			?>
			<a href="#" class="ets-btn btn-disconnect" id="disconnect-discord" data-user-id="<?php echo $user_id; ?>"><?php echo __("Disconnect From Discord ", "ets_pmpro_discord");?></a>
			<img id="image-loader" src= <?php echo ETS_PMPRO_DISCORD_URL."asset/images/Spin-Preloader.gif;"?> >
		<?php
		}
		else {
		?>
			<a href="?action=discord-login" class="btn-connect ets-btn" target="_blank"><?php echo __("Connect To Discord", "ets_pmpro_discord");?></a>
		<?php
		}
		
	}

	/**
	 * Function Name:- get_current_level_id();
	 *
	 * Description:- get pmpro current level id
	 *
	 * @param:- None; 
	 *
	 * @return:- curr_level_id; 
	 */
	public function get_current_level_id($user_id)
	{
		if(is_user_logged_in() && function_exists('pmpro_hasMembershipLevel') && pmpro_hasMembershipLevel())
		{
			global $current_user;
			$membership_level = pmpro_getMembershipLevelForUser($user_id);
			$curr_level_id = $membership_level->ID;
			return $curr_level_id;
		}
	}

	/**
	 * Function create_discord_auth_token(); 
	 *
	 * Description: Call create auth token API.
	 *
	 * @param $code.
	 *
	 * @return API respnce json. 
	 */
	public function create_discord_auth_token($code)
	{
		$discord_token_api_url = ETS_DISCORD_API_URL.'oauth2/token';
		$args = array(
			'method'=> 'POST',
		    'headers' => array(
		        'Content-Type' => 'application/x-www-form-urlencoded'
		    ),
		    'body' => array(
	    		'client_id' => get_option('ets_discord_client_id'),
				'client_secret' => get_option('ets_discord_client_secret'),
				'grant_type' => 'authorization_code',
				'code' => $code,
				'redirect_uri' =>  get_option('ets_discord_redirect_url'),
				'scope' => 'identify email connections'
		    )    
		);

		$responce = wp_remote_post( $discord_token_api_url, $args );
		return $responce;
	}

	/**
	 * Function get_discord_current_user(); 
	 *
	 * Description: get discord current user API.
	 *
	 * @param $access_token.
	 *
	 * @return user_body.
	 */
	public function get_discord_current_user( $access_token )
	{
		$discord_cuser_api_url = ETS_DISCORD_API_URL.'users/@me';
		$param = array(
			'headers'      => array(
	        'Content-Type' => 'application/x-www-form-urlencoded',
	        'Authorization' => 'Bearer ' . $access_token
	    	)
	    );
		$user_responce = wp_remote_get( $discord_cuser_api_url, $param );
		$user_body = json_decode( wp_remote_retrieve_body( $user_responce ), true );
		return $user_body;
	}

	/**
	 * Function add_discord_member_in_guild(); 
	 *
	 * Description: add member into guild.
	 *
	 * @param $discord_user_id, $user_id, $access_token.
	 *
	 * @return json.
	 */
	public function add_discord_member_in_guild( $discord_user_id, $user_id, $access_token )
	{
		$guild_id = get_option('discord_guild_id');
		$discord_bot_token = get_option('ets_discord_bot_token');
		$ets_discord_role_mapping = json_decode(get_option('ets_discord_role_mapping'), true);
		$discord_role = '';
		$curr_level_id = $this->get_current_level_id( $user_id );
		if( $curr_level_id )
		{
			$discord_role = $ets_discord_role_mapping[ 'level_id_'.$curr_level_id ];
		}
		$guilds_memeber_api_url = ETS_DISCORD_API_URL.'guilds/'.$guild_id.'/members/'.$discord_user_id;
		$guild_args = array(
			'method'  => 'PUT',
		    'headers' => array(
		        'Content-Type'  => 'application/json',
		        'Authorization' => 'Bot ' . $discord_bot_token
		    ),
		    'body' => json_encode(
		    	array(
					"access_token" => $access_token,
					"roles"        => [
				            $discord_role
				        ]
				)
	    	)
		);
		update_user_meta($user_id, 'discord_role_id', $discord_role);
		$guild_responce = wp_remote_post( $guilds_memeber_api_url, $guild_args );
		$change_responce = $this->change_discord_role_api( $user_id, $discord_role );
		return $guild_responce;
	}

	/**
	 * Function Name:- discord_api_callback();
	 *
	 * Description:- call discord API
	 *
	 * @param:- None; 
	 *
	 * @return:- json; 
	 */
	public function discord_api_callback()
	{
		if (isset($_GET['action']) && $_GET['action'] == "discord-login" ) {
			$params = array(
			    'client_id' => get_option('ets_discord_client_id'),
			    'redirect_uri' => get_option('ets_discord_redirect_url'),
			    'response_type' => 'code',
			    'scope' => 'identify email connections guilds guilds.join messages.read'
			  );
			$discord_authorise_api_url = ETS_DISCORD_API_URL."oauth2/authorize?".http_build_query($params);

			header('Location: '.$discord_authorise_api_url);
			die();
		}

		if (isset($_GET['code'])) {
			$code = $_GET['code'];
			$user_id = get_current_user_id();
			$responce = $this->create_discord_auth_token( $code );
			$res_body = json_decode( wp_remote_retrieve_body( $responce ), true );
			$discord_exist_user_id = get_user_meta($user_id, "discord_user_id", true );
			
			if (array_key_exists( 'access_token', $res_body )) {				
				$access_token = $res_body['access_token'];
				update_user_meta( $user_id, "discord_access_token", $access_token );
				$user_body = $this->get_discord_current_user( $access_token );
				if ( array_key_exists( 'id', $user_body ) )
				{
					$discord_user_id = $user_body['id'];
					if ( $discord_exist_user_id == $discord_user_id ) {
						$role_delete = $this->delete_discord_role( $user_id );
					}
					update_user_meta($user_id, "discord_user_id", $discord_user_id );
					$guild_responce = $this->add_discord_member_in_guild( $discord_user_id, $user_id,$access_token );
				}	
			}
		}
	}

	/**
	 * Function delete_member_from_guild(); 
	 *
	 * Description: add member into guild.
	 *
	 * @param $discord_user_id, $user_id, $access_token.
	 *
	 * @return json.
	 */
	public function delete_member_from_guild($user_id)
	{
		$guild_id = get_option('discord_guild_id');
		$discord_bot_token = get_option('ets_discord_bot_token');
		$discord_user_id = get_user_meta($user_id , 'discord_user_id', true);
		$guilds_delete_memeber_api_url = ETS_DISCORD_API_URL.'guilds/'.$guild_id.'/members/'.$discord_user_id;
		$guild_args = array(
			'method'  => 'DELETE',
		    'headers' => array(
		        'Content-Type'  => 'application/json',
		        'Authorization' => 'Bot ' . $discord_bot_token
		    )   
		);
		$guild_responce = wp_remote_post( $guilds_delete_memeber_api_url, $guild_args );
		return $guild_responce;
	}
	
	/**
	 * Function change_discord_role_api(); 
	 *
	 * Description: call change discord role API.
	 *
	 * @param $user_id.
	 *
	 * @return configure SMTP. 
	 */
	public function change_discord_role_api( $user_id, $role_id )
	{
		$access_token = get_user_meta( $user_id, "discord_access_token", true );
		$guild_id = get_option( 'discord_guild_id' );
		$discord_user_id = get_user_meta($user_id, 'discord_user_id', true);
		$discord_bot_token = get_option('ets_discord_bot_token');
		$discord_change_role_api_url = ETS_DISCORD_API_URL.'guilds/'.$guild_id.'/members/'.$discord_user_id.'/roles/'.$role_id;
		if ( $access_token && $discord_user_id ) {
			$param = array(
						'method'=> 'PUT',
					    'headers' => array(
					        'Content-Type' => 'application/json',
					        'Authorization' => 'Bot ' .$discord_bot_token,
					        'Content-Length' => 0
					    )
					);

			$responce = wp_remote_get($discord_change_role_api_url, $param);
			update_user_meta( $user_id, 'discord_role_id', $role_id );
			return $responce;
		}
	}

	/**
	 * Function delete_discord_role(); 
	 *
	 * Description: Call detete discord role API
	 *
	 * @param $user_id.
	 *
	 * @return API responce. 
	 */
	public function delete_discord_role( $user_id )
	{
		$access_token = get_user_meta( $user_id, "discord_access_token", true );
		$guild_id = get_option( 'discord_guild_id' );
		$discord_user_id = get_user_meta( $user_id, 'discord_user_id', true);
		$discord_bot_token = get_option( 'ets_discord_bot_token' );
		$discord_role_id = get_user_meta( $user_id, 'discord_role_id', true );
		$discord_delete_role_api_url = ETS_DISCORD_API_URL.'guilds/'.$guild_id.'/members/'.$discord_user_id.'/roles/'.$discord_role_id;

		if ( $discord_user_id ) {
			$param = array(
					'method'=> 'DELETE',
				    'headers' => array(
				        'Content-Type' => 'application/json',
				        'Authorization' => 'Bot ' .$discord_bot_token,
				        'Content-Length' => 0
				    )
				);
			
			$responce = wp_remote_request( $discord_delete_role_api_url, $param );
			return $responce;
		}
	}

	/**
	 * Function change_discord_role_from_pmpro(); 
	 *
	 * Description: Change discord role form pmpro role
	 *
	 * @param $level_id, $user_id, $cancel_level.
	 *
	 * @return API responce. 
	 */
	public function change_discord_role_from_pmpro( $level_id, $user_id, $cancel_level )
	{
		$discord_user_id = get_user_meta($user_id, 'discord_user_id',true);
		if ( $discord_user_id ) {
			$role_delete = $this->delete_discord_role( $user_id );
			$ets_discord_role_mapping = json_decode(get_option( 'ets_discord_role_mapping' ), true );
			$role_id = '';
			$curr_level_id = $this->get_current_level_id($user_id);
			if( $level_id )
			{
				$role_id = $ets_discord_role_mapping['level_id_'.$level_id];
			}
			if ( $cancel_level ) {
				$role_id = $ets_discord_role_mapping['level_id_expired'];
			}
			$role_change = $this->change_discord_role_api($user_id, $role_id);
		}
	}

	/**
	 * Function Name:- disconnect_from_discord();
	 *
	 * Description:-cdisconnect from discord 
	 *
	 * @param:- none 
	 *
	 * @return:- json; 
	 */
	public function disconnect_from_discord()
	{
		$user_id = $_POST['user_id'];
		$ets_discord_role_mapping = json_decode(get_option( 'ets_discord_role_mapping' ), true );
		$role_id = '';
		$role_id = $ets_discord_role_mapping['level_id_expired'];
		$res = $this->delete_discord_role( $user_id );
		$responce = $this->change_discord_role_api( $user_id, $role_id );
		delete_user_meta( $user_id, 'discord_access_token' );
		$event_res = array(
			"status"  => 1,
			"message" => "Successsfully disconnected"
		);
		echo json_encode($event_res);
		die();
	}

	/**
	 * Function Name:- pmpro_expiry_membership();
	 *
	 * Description:- set discord spector role on pmpro expiry 
	 *
	 * @param:- $user_id, $level_id; 
	 *
	 * @return:- update role; 
	 */
	public function pmpro_expiry_membership( $user_id, $level_id )
	{	
		$ets_discord_role_mapping = json_decode(get_option( 'ets_discord_role_mapping' ), true );
		$role_id = '';
		$role_id = $ets_discord_role_mapping['level_id_expired'];
		$role_delete = $this->delete_discord_role( $user_id );
		$responce = $this->change_discord_role_api( $user_id, $role_id );
		update_option('expire_pmpro_member_1','expire');
	}
}
$ets_pmpro_admin_setting = new Ets_Pmpro_Admin_Setting();