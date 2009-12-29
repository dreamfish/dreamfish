<?php
/**
 * @defgroup Templates Templates
 * @file
 * @ingroup Templates
 */
if( !defined( 'MEDIAWIKI' ) ) die( -1 );

/**
 * HTML template for Special:Userlogin form
 * @ingroup Templates
 */
class UserloginTemplate extends QuickTemplate {
	function execute() {
		if( $this->data['message'] ) {
?>
	<div class="<?php $this->text('messagetype') ?>box">
		<?php if ( $this->data['messagetype'] == 'error' ) { ?>
			<h2><?php $this->msg('loginerror') ?></h2>
		<?php } ?>
		<?php $this->html('message') ?>
	</div>
	<div class="visualClear"></div>
<?php } ?>
<div id="loginstart"><?php $this->msgWiki( 'loginstart' ); ?></div>
<div id="userloginForm">
<form name="userlogin" method="post" action="<?php $this->text('action') ?>">
	<h2><?php $this->msg('login') ?></h2>
	<p id="userloginlink"><?php $this->html('link') ?></p>
	<?php $this->html('header'); /* pre-table point for form plugins... */ ?>
	<div id="userloginprompt"><?php  $this->msgWiki('loginprompt') ?></div>
	<?php if( @$this->haveData( 'languages' ) ) { ?><div id="languagelinks"><p><?php $this->html( 'languages' ); ?></p></div><?php } ?>
	<table>
		<tr>
			<td class="mw-label"><label for='wpName1'><?php $this->msg('yourname') ?></label></td>
			<td class="mw-input">
				<input type='text' class='loginText' name="wpName" id="wpName1"
					tabindex="1"
					value="<?php $this->text('name') ?>" size='20' />
			</td>
		</tr>
		<tr>
			<td class="mw-label"><label for='wpPassword1'><?php $this->msg('yourpassword') ?></label></td>
			<td class="mw-input">
				<input type='password' class='loginPassword' name="wpPassword" id="wpPassword1"
					tabindex="2"
					value="" size='20' />
			</td>
		</tr>
	<?php if( $this->data['usedomain'] ) {
		$doms = "";
		foreach( $this->data['domainnames'] as $dom ) {
			$doms .= "<option>" . htmlspecialchars( $dom ) . "</option>";
		}
	?>
		<tr id="mw-user-domain-section">
			<td class="mw-label"><?php $this->msg( 'yourdomainname' ) ?></td>
			<td class="mw-input">
				<select name="wpDomain" value="<?php $this->text( 'domain' ) ?>"
					tabindex="3">
					<?php echo $doms ?>
				</select>
			</td>
		</tr>
	<?php }
	if( $this->data['canremember'] ) { ?>
		<tr>
			<td></td>
			<td class="mw-input">
				<input type='checkbox' name="wpRemember"
					tabindex="4"
					value="1" id="wpRemember"
					<?php if( $this->data['remember'] ) { ?>checked="checked"<?php } ?>
					/> <label for="wpRemember"><?php $this->msg('remembermypassword') ?></label>
			</td>
		</tr>
		<?php } ?>
		<tr>
			<td></td>
			<td class="mw-submit">
				<input type='submit' name="wpLoginattempt" id="wpLoginattempt" tabindex="5" value="<?php $this->msg('login') ?>" />&nbsp;<?php if( $this->data['useemail'] && $this->data['canreset']) { ?><input type='submit' name="wpMailmypassword" id="wpMailmypassword"
					tabindex="6"
									value="<?php $this->msg('mailmypassword') ?>" />
				<?php } ?>
			</td>
		</tr>
	</table>
<?php if( @$this->haveData( 'uselang' ) ) { ?><input type="hidden" name="uselang" value="<?php $this->text( 'uselang' ); ?>" /><?php } ?>
</form>
</div>
<div id="loginend"><?php $this->msgWiki( 'loginend' ); ?></div>
<?php

	}
}

/**
 * @ingroup Templates
 */
class UsercreateTemplate extends QuickTemplate {
	function addInputItem( $name, $value, $type, $msg ) {
		$this->data['extraInput'][] = array(
			'name' => $name,
			'value' => $value,
			'type' => $type,
			'msg' => $msg,
		);
	}
	
	function execute() {
		if( $this->data['message'] ) {
?>
	<div class="<?php $this->text('messagetype') ?>box">
		<?php if ( $this->data['messagetype'] == 'error' ) { ?>
			<h2><?php $this->msg('loginerror') ?></h2>
		<?php } ?>
		<?php $this->html('message') ?>
	</div>
	<div class="visualClear"></div>
<?php } ?>
<div id="userlogin">

<form name="userlogin2" id="userlogin2" method="post" action="<?php $this->text('action') ?>">
	<h2>Join Dreamfish</h2>
	
	<table cellspacing="10px;">
	<tr>
		<td colspan="2">
			<div id="userloginlink">
				<?php $this->html('link') ?>
			</div>
		</td>
		<td>
			<div id="userloginlink" style="width:450px;">
				Learn more about 
				<a href="javascript: void(0)" 
			   		onclick="window.open('http://network.dreamfish.com/wiki/Is_Dreamfish_for_you%3F', 
			  		'membership_window', 
			  		'target=_top, scrollbars=yes, resizable=yes, width=700, height=500'); 
			   		return false;">
			   		membership</a>.
			</div>
		</td>	
	</tr>

	<?php if( @$this->haveData( 'languages' ) ) { ?><div id="languagelinks"><p><?php $this->html( 'languages' ); ?></p></div><?php } ?>
	
		<!-- Full name -->
		<tr>
			<td class="mw-label">
				<label for='wpRealName'><div style="font-size:18px;"><?php $this->msg('yourrealname') ?></div></label>
			</td>
			<td class="mw-input">
				<input type='text' class='loginText' name="wpRealName" id="wpRealName"
					tabindex="6"
					value="<?php $this->text('realname') ?>" size='30' />
			</td>
			<td>
				<div style="font-size:80%;">
					Please fill in your full name. Using your full name will help you to connect with members.
				</div>
			</td>
		</tr>
		
		<!-- Email -->
		<tr>
			<?php if( $this->data['useemail'] ) { ?>
				<td class="mw-label">
					<label for='wpEmail'><div style="font-size:18px;"><?php $this->msg('youremail') ?></div></label>
				</td>
				<td class="mw-input">
					<input type='text' class='loginText' name="wpEmail" id="wpEmail"
						tabindex="2" 
						value="<?php $this->text('email') ?>" size='30' />
				</td>
				<td>
					<div style="font-size:80%;">
						Email allows you to communicate with other members and retrieve a lost password if necessary.
					</div>
				</td>
			<?php } ?>
		</tr>
		
		<!-- Username -->
		<tr>
			<td class="mw-label">
				<label for='wpName2'><div style="font-size:18px;"><?php $this->msg('yourname') ?></div></label>				
			</td>
			<td class="mw-input">
				<input type='text' class='loginText' name="wpName" id="wpName2"
					tabindex="3"
					value="<?php $this->text('name') ?>" size='30' />
			</td>
			<td>&nbsp;</td>
		</tr>
		
		<!-- Password -->
		<tr>
			<td class="mw-label"><div style="font-size:18px;"><label for='wpPassword2'><?php $this->msg('yourpassword') ?></div></label></td>
			<td class="mw-input">
				<input type='password' class='loginPassword' name="wpPassword" id="wpPassword2"
					tabindex="4" 
					value="" size='30' />
			</td>
			<td>&nbsp;</td>
		</tr>
		
		<!-- Retype Password -->
		<tr>
			<td class="mw-label"><div style="font-size:18px;"><label for='wpRetype'><?php $this->msg('yourpasswordagain') ?></div></label></td>
			<td class="mw-input">
				<input type='password' class='loginPassword' name="wpRetype" id="wpRetype"
					tabindex="5"
					value="" size='30' />
			</td>
			<td>&nbsp;</td>
		</tr>

	<!-- reCAPTCHA -->
		<tr>
			<td>&nbsp;</td>
				<td colspan="2"
					
				<!-- Will need the next line for the reCAPTCHA plugin if we didn't need to customize the reCAPTCHA-->
				<!-- <?php $this->html('header'); /* pre-table point for form plugins... */ ?>  -->
				
				<!-- Replace the form plugins for reCAPTCHA with the following block of code -->	
				<div class='captcha'>
					<script>var RecaptchaOptions = { theme:'white', tabindex:6 }; </script> 
					<script type="text/javascript" src="http://api.recaptcha.net/challenge?k=6Les_QcAAAAAAGxcW2ZZOlgbsMKL6AuwzCNM-D2a"></script>
	
					<noscript>
				  		<iframe src="http://api.recaptcha.net/noscript?k=6Les_QcAAAAAAGxcW2ZZOlgbsMKL6AuwzCNM-D2a" 
				  			height="300" width="500" frameborder="0">
				  		</iframe>
				  		<br>
				  		<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
				  		<input type="hidden" name="recaptcha_response_field" value="manual_challenge">
					</noscript>
				</div>
			</td>
		</tr>
		<tr>
			<td></td>
			<td class="mw-submit">
				<input type='submit' name="wpCreateaccount" id="wpCreateaccount"
					tabindex="<?php echo $tabIndex++; ?>"
					value="<?php $this->msg('createaccount') ?>" />
				<?php if( $this->data['createemail'] ) { ?>
				<input type='submit' name="wpCreateaccountMail" id="wpCreateaccountMail"
					tabindex="<?php echo $tabIndex++; ?>"
					value="<?php $this->msg('createaccountmail') ?>" />
				<?php } ?>
			</td>
		</tr>
		<!-- Acceptance message -->
		<tr>
			<td>&nbsp;</td>
			<td colspan="2">
				<div style="font-size:80%;">
					By clicking on "Create My Acount" above, you confirm to accept 
					<a href="javascript: void(0)" 
			   		onclick="window.open('http://network.dreamfish.com/wiki/Is_Dreamfish_for_you%3F', 
			  		'membership_window', 
			  		'target=_top, scrollbars=yes, resizable=yes, width=700, height=500'); 
			   		return false;">
					Dreamfish membership guidelines</a>			
					and <a href="javascript: void(0)" 
			   		onclick="window.open('http://network.dreamfish.com/wiki/Terms_of_Service', 
			  		'membership_window', 
			  		'target=_top, scrollbars=yes, resizable=yes, width=700, height=500'); 
			   		return false;">terms of service</a>.</div>
			</td>
		</tr>
		
	</table>
<?php if( @$this->haveData( 'uselang' ) ) { ?><input type="hidden" name="uselang" value="<?php $this->text( 'uselang' ); ?>" /><?php } ?>
</form>
</div>
<div id="signupend"><?php $this->msgWiki( 'signupend' ); ?></div>
<?php

	}
}
