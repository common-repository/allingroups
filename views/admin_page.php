<?php defined( 'ABSPATH' ) or die( 'No script kiddies please!' ); ?>

<div class="wrap">
    
    <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
    
    <div id="poststuff">
        <div id="post-body">
            <div id="post-body-content">
            
                <div id="aigform1"></div>
                
                <form method="post" action="options.php" id="aigoptionform">
                
                    <?php
                    settings_fields( 'allingroups_options' );
                    
                    $allingroups_email = get_option( 'allingroups_email' );
                    $allingroups_wp_identifier = get_option( 'allingroups_wp_identifier' );
                    $allingroups_teaser = get_option( 'allingroups_teaser' );
                    $allingroups_auto = get_option( 'allingroups_auto' );
                    $allingroups_playlist = get_option( 'allingroups_playlist' );
                    ?>
                    
                    <table class="form-table">
                        <tr>
                            <td colspan="2">
                                <div id="linked_to_allingroups" class="notice notice-success" style="display: none;">
                                    <p><?php _e( 'Linked to allingroups', 'allingroups' ) ?></p>
                                </div>
                                <div id="unable_to_connect_to_allingroups" class="notice notice-error" style="display: none;">
                                    <p><?php _e( 'Unable to connect to allingroups', 'allingroups' ) ?></p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div id="currentaig" style="display: none;">
                                    <h4><?php _e( 'Current allingroups account linked', 'allingroups' ) ?></h4>
                                    <?php $aig_account = $allingroups_email ? $allingroups_email : ''; ?>
                                    <?php echo esc_html($aig_account); ?>
                                    <button name="aigremove" id="aigremove"><?php _e( 'Remove', 'allingroups' ); ?></button>
                                </div>
                            </td>
                            <td>
                                <div id="aigform2">
                                    <h4 id="changeaig" style="display: none;"><?php _e( 'Change allingroups account linked', 'allingroups' ) ?></h4>
                                    <h4 id="connectaig" style="display: none;"><?php _e( 'Please connect with your Facebook account', 'allingroups' ) ?></h4>
                                    
                                    <input type="text" name="allingroups_email_2" id="allingroups_email_2" placeholder="Your e-mail" value="" autocomplete="off"/><br>
                                    <input type="hidden" name="allingroups_email" id="allingroups_email" value="<?php echo esc_attr( $aig_account ); ?>" autocomplete="off"/>
                                    <input type="password" name="allingroups_password_2" id="allingroups_password_2" placeholder="Your password" value="" autocomplete="off"/><br>
                                    
                                    <button name="aigconnect" id="aigconnect"><?php _e( 'Connect', 'allingroups' ) ?></button>
                                    
                                    <br /><br /><small><a href="http://allingroups.com/en/frequently-asked-questions/#why-does-allingroups-need-my-facebook-details-login-e-mail-address-password-isnt-it-dangerous-to-tell-allingroups-these-information">Why do you need my Facebook details?</a></small>
                                </div>
                                
                                <?php $wp_uid = $allingroups_wp_identifier ? $allingroups_wp_identifier : false; ?>
                                <input type="hidden" name="allingroups_wp_identifier" id="allingroups_wp_identifier" value="<?php echo esc_attr( $wp_uid ); ?>"/>
                            </td>
                        </tr>
                        <?php if ( $wp_uid ){ ?>
                        <tr>
                            <td>
                                <?php $wp_teaser = ($allingroups_teaser=='1') ? (int) $allingroups_teaser : 0; ?>
                                <label><?php _e( 'Publish article teaser', 'allingroups' ) ?></label>&nbsp;
                                <select name="allingroups_teaser" id="allingroups_teaser">
                                    <option value="0"><?php _e( 'No', 'allingroups' ) ?></option>
                                    <option value="1" <?php if ( $wp_teaser ) { echo 'selected="selected"'; } ?>><?php _e( 'Yes', 'allingroups' ) ?></option>
                                </select><br/>
                                
                                <?php $wp_auto = ( '0' == $allingroups_auto ) ? (int) $allingroups_auto : 1; ?>
                                <label title="<?php _e( 'don\'t ask me to publish', 'allingroups' ) ?>"><?php _e( 'publish all posts automatically', 'allingroups' ) ?></label>&nbsp;
                                <select name="allingroups_auto" id="allingroups_auto">
                                    <option value="0"><?php _e( 'No', 'allingroups' ) ?></option>
                                    <option value="1" <?php if ( $wp_auto ) { echo 'selected="selected"'; } ?>><?php _e( 'Yes', 'allingroups' ) ?></option>
                                </select>
                            </td>
                            <td>
                                <?php $wp_playlist = $allingroups_playlist ? $allingroups_playlist : 0; ?>
                                <script>
                                    var playlist = <?php echo $allingroups_playlist ? esc_html($allingroups_playlist) : 0; ?>;
                                </script>
                                <div id="playlist_loading">
                                    <?php _e( 'Your playlist are loading...', 'allingroups' ) ?>
                                </div>
                                <div id="playlist_loaded" style="display:none;">
                                    <label><?php _e( 'Autopublish your post in:', 'allingroups' ) ?></label>
                                    <select name="allingroups_playlist" id="allingroups_playlist">
                                        <option value="0"><?php _e( 'All your groups', 'allingroups' ) ?></option>
                                    </select>
                                    
                                    <div id="create_playlist">
                                        <button name="getGroups" id="getGroups"><?php _e( 'Get your groups', 'allingroups' ) ?></button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                        <tr>
                            <td>
                                <button type="submit" name="save">Save</button>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>
    </div>
</div>

<script>

	jQuery( document ).ready( function($) {
		
		<?php if ( $wp_uid ) { ?>
		jQuery.post(
			ajaxurl,
			{
				action: 'allingroups_api_call',
				activity: 'wpgetplaylist',
				wp_uid: '<?php echo esc_html($wp_uid); ?>',
				wp: '<?php echo bloginfo( 'rss2_url' ); ?>',
				wp_playlist: '<?php echo esc_html($wp_playlist); ?>',
				wp_teaser: '<?php echo esc_html($wp_teaser); ?>',
				wp_auto: '<?php echo esc_html($wp_auto); ?>'
			},
			function( response ) {
				
				console.log( response );
				
				if ( 'error' != response ) {
				
					try {
						
						var data = $.parseJSON( response );
						
						if ( 'object' === typeof data.playlists ){
							
							for ( var i = 0; i < data.playlists.length; i++ ) {
								$( '#allingroups_playlist' ).append( '<option value="' + data.playlists[ i ].plid + '">' + data.playlists[ i ].name + '</option>' );
							}
							
						}
						
					} catch (e) {
						
						if ( window.console && window.console.log )
							window.console.log( 'could not parse JSON' );
						
					}
					
					$( '#playlist_loading' ).hide();
					$( '#allingroups_playlist' ).val( playlist );
					$( '#playlist_loaded' ).show();
				
				}
				
			}
		);
		<?php } ?>
		
		$( 'body' ).on( 'click', '#getGroups', function( e ) {
			
			e.preventDefault();
			
			var data = {
				action: 'allingroups_api_call',
				activity: 'wpgetgroup',
				wp_uid: '<?php echo esc_html($wp_uid); ?>',
				wp: '<?php echo bloginfo( 'rss2_url' ); ?>'
			};
	
			jQuery.post(ajaxurl, data, function( response ) {
				
				if ( 'error' != response ) {
				
					try {
						
						var data = $.parseJSON( response );
						
						if ( 'object' === typeof data.groups ) {
					
							$( '#create_playlist' ).html( '<h4><?php _e( 'Create a playlist', 'allingroups' ) ?></h4><input type="text" name="plname" id="plname" placeholder="<?php _e( 'Name of the playlist', 'allingroups' ) ?>"/></br></br><label><?php _e( 'Select your groups', 'allingroups' ) ?></label></br>' );
							
							for (var i = 0; i < data.groups.length; i++ ) {
								$( '#create_playlist' ).append( '<input type="checkbox" value="' + data.groups[ i ].gid + '" id="gid' + data.groups[ i ].gid + '" name="groups"/><label for="gid' + data.groups[ i ].gid + '">' + data.groups[ i ].name + '</label></br>' );
							}
							
							$( '#create_playlist' ).append( '<button name="save_playlist" id="save_playlist"><?php _e( 'Save the playlist', 'allingroups' ) ?></button></br></br>' );
							
						}
						
					} catch (e) {
						
						if ( window.console && window.console.log )
							window.console.log( 'could not parse JSON' );
						
					}
				
				}
				
			});
			
		});
		
		$( 'body' ).on( 'click', '#save_playlist', function( e ) {
			
			e.preventDefault();
			
			$( '#playlist_loading' ).show();
			$( '#playlist_loaded' ).hide();
			
			var groups = new Array();
			
			$( 'input[name=groups]:checked' ).each( function( index ){
				groups.push( $( this ).val() );
			});
			
			var data = {
				action: 'allingroups_api_call',
				activity: 'wpcreateplaylist',
				wp_uid: '<?php echo esc_html($wp_uid); ?>',
				wp: '<?php echo bloginfo( 'rss2_url' ); ?>',
				playlist_name: $( '#plname' ).val(),
				groups: groups
			};
	
			jQuery.post(ajaxurl, data, function( response ) {
				
				if ( 'error' != response ) {
				
					try {
						
						var data = $.parseJSON( response );
						var j = 0;
				
						if ( 'object' === typeof data.playlists ) {
							
							$( '#allingroups_playlist' ).html( '<option value="0"><?php _e( 'All your groups', 'allingroups' ) ?></option>' );
							$( '#create_playlist' ).html( '<button name="getGroups" id="getGroups"><?php _e( 'Get your groups', 'allingroups' ) ?></button>');
							
							for ( var i = 0; i < data.playlists.length; i++ ) {
								
								$( '#allingroups_playlist' ).append( '<option value="' + data.playlists[ i ].plid + '">' + data.playlists[i].name + '</option>');
								
								if ( data.playlists[ i ].name == $( '#plname' ).val() ) {
									j = i;
								}
								
							}
							
						}
						
						$( '#playlist_loading' ).hide();
						$( '#allingroups_playlist' ).val( data.playlists[ j ].plid );
						$( '#playlist_loaded' ).show();
						
					} catch (e) {
						
						if ( window.console && window.console.log )
							window.console.log( 'could not parse JSON' );
						
					}
				
				}
				
			});
			
		});
		
		$( 'body' ).on( 'click', '#aigremove', function( e ) {
			
			alert("remove");
			
			e.preventDefault();
			
			var data = {
				action: 'allingroups_api_call',
				activity: 'wpremove',
				wp_uid: '<?php echo esc_html($wp_uid); ?>',
				wp: '<?php echo bloginfo( 'rss2_url' ); ?>'
			};
	
			jQuery.post(ajaxurl, data, function( response ) {
				
				$( '#allingroups_email' ).val( '' );
				$( '#allingroups_password' ).val( '' );
				$( '#allingroups_wp_identifier' ).val( '' );
				$( '#allingroups_playlist' ).val( '' );
				$( '#allingroups_teaser' ).val( '' );
				$( '#allingroups_auto' ).val( '' );
				$( '#aigoptionform' ).submit();
				
			});
			
		});
		
		$( 'body' ).on( 'click', '#aigconnect', function( e ) {
			
			e.preventDefault();
			
			var data = {
				action: 'allingroups_api_call',
				activity: 'wpconnect',
				email: $( '#allingroups_email_2' ).val(),
				password: $( '#allingroups_password_2' ).val(),
				<?php if ( $wp_uid ) { ?>
				wp_uid: '<?php echo esc_html($wp_uid); ?>',
				<?php } ?>
				wp: '<?php echo bloginfo( 'rss2_url' ); ?>'
			};
	
			jQuery.post(ajaxurl, data, function( response ) {
				
				//console.log( response );
				
				if ( 'error' != response ) {
				
					try {
						
						var data = $.parseJSON( response );
						
						if ( 'undefined' !== typeof data.wp_uid ) {
					
							$( '#allingroups_playlist' ).val( '0' );
							$( '#allingroups_email' ).val( $( '#allingroups_email_2' ).val() );
							$( '#allingroups_wp_identifier' ).val( data.wp_uid );
							
							check_identifier();
							
							$( '#aigoptionform' ).submit();
							
						} else if ( 'undefined' !== typeof data.checkpoint ) {
							
							$( '#aigform1' ).html( '<form method="POST" id="aigcheckpoint" action="#">CHECKPOINT<input type="hidden" name="a_url" value="' + data.checkpoint[1] + '"/><input type="hidden" name="a_email" value="' + data.email + '"/><input type="hidden" name="a_cpass" value="' + data.cpass + '"/><input type="hidden" name="action" value="wpcheckpoint"/><input type="hidden" name="wp" value="<?php echo bloginfo( 'rss2_url' ); ?>"/>' + data.checkpoint[2] + '</form>' );
							$( '#aigform1' ).show();
							
						}
						
					} catch (e) {
						
						if ( window.console && window.console.log )
							window.console.log( 'could not parse JSON' );
						
					}
				
				}
				
			});
			
		});
		
		$( 'body' ).on( 'submit', '#aigcheckpoint', function( e ) {
			
			e.preventDefault();
			
			var data = {
				action: 'allingroups_api_call',
				data: $( this ).serialize()
			};
	
			jQuery.post(ajaxurl, data, function( response ) {
				
				if ( 'error' != response ) {
				
					try {
						
						var data = $.parseJSON( response );
						
						if ( 'undefined' != typeof data.wp_uid ) {
					
							$( '#allingroups_playlist' ).val( '0' );
							$( '#allingroups_email' ).val( $( '#allingroups_email_2' ).val() );
							$( '#allingroups_wp_identifier' ).val( data.wp_uid );
							
							check_identifier();
							
							$( '#aigoptionform' ).submit();
							
						}
						else if ( 'undefined' != typeof data.checkpoint ) {
							
							$( '#aigform1' ).html( '<form method="POST" id="aigcheckpoint" action="#">CHECKPOINT<input type="hidden" name="a_url" value="' + data.checkpoint[1] + '"/><input type="hidden" name="a_email" value="' + data.email + '"/><input type="hidden" name="a_cpass" value="' + data.cpass + '"/><input type="hidden" name="action" value="wpcheckpoint"/><input type="hidden" name="wp" value="<?php echo bloginfo( 'rss2_url' ); ?>"/>' + json.checkpoint[2] + '</form>' );
							
							$( '#aigform1' ).show();
							
						}
						
					} catch (e) {
						
						if ( window.console && window.console.log )
							window.console.log( 'could not parse JSON' );
						
					}
				
				}
				
			});
			
		});
		
		var check_identifier = function() {
			
			if( $( '#allingroups_wp_identifier' ).val() != '' ) {
				show_success();
			}else{
				show_error();
			}
			
		};
		
		var show_success = function() {
			
			$( '#unable_to_connect_to_allingroups' ).hide();
			//$( '#aigform2' ).hide();
			$( '#linked_to_allingroups' ).show();
			$( '#currentaig' ).show();
			$( '#changeaig' ).show();
			$( '#connectaig' ).hide();
			
		};
		
		var show_error = function() {
			
			$( '#unable_to_connect_to_allingroups' ).show();
			//$( '#aigform2' ).show();
			$( '#linked_to_allingroups' ).hide();
			$( '#currentaig' ).hide();
			$( '#changeaig' ).hide();
			$( '#connectaig' ).show();
			
		};
		
		check_identifier();
		
	});
	
</script>
