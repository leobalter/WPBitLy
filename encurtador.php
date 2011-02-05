/*
Plugin Name: Encurtador de Link
Plugin URI: link para a página do plugin
Description: descrição do plugin
Author: WPmeetupRJ
Version: 1.0
Author URI: site do autor
*/

function processarLink($url) {
	$bitlyLogin =  get_option('bitlyLogin');
	$bitlyAPIKey = get_option('bitlyAPIKey');
	if ( ! $bitlyLogin || ! $bitlyAPIKey ) {
		return false;
	}
 
	if ( !$url && in_the_loop() ) {
		$url = get_permalink();
	}
 
	if ( !$url ) { return false; }
 
	// use esse hack para garantir que o urlencode não seja usado uma segunda vez seguida
	$url = urldecode($url);
	$url = urlencode($url);
 
	$api_call = file_get_contents("http://api.bit.ly/shorten?version=2.0.1&longUrl=".$url."&login=".$bitlyLogin."&apiKey=".$bitlyAPIKey);
 
	// o bitly retorna a informação toda em json
	$bitlyinfo = json_decode(utf8_encode($api_call),true);
 
	if ( $bitlyinfo['errorCode']==0 ) {
		return $bitlyinfo['results'][urldecode($url)]['shortUrl'];
	} else {
		return false;
	}
}

if (is_admin())
	add_action('admin_menu', 'bitly_adminmenu');
 
function bitly_adminmenu() {
	add_options_page( 'Encurtamento Bit.Ly' , 'Encurtamento Bit.Ly' , 'manage_options' , 'opcoes-bitly' , 'mostraOpcoesBitly' );
}

function mostraOpcoesBitly() {
	if (!current_user_can('manage_options')) {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	if ( isset($_POST['atualizarBitLy']) ) {
		echo '<div class="updated"><p>Dados atualizados!</p></div>';
		update_option('bitlyAPIKey', $_POST['bitlyAPIKey']);
		update_option('bitlyLogin', $_POST['bitlyLogin']);
	}
	$bitlyLogin =  get_option('bitlyLogin');
	$bitlyAPIKey = get_option('bitlyAPIKey');
	? >
	<div class="wrap">
		<h3>Bit Ly API info</h3>
		<form id="opcoesbitly" method="POST">
			<p>
				<label for="bitlyLogin">Login:</label>
				<input type="text" value="< ?php echo $bitlyLogin; ? >" id="bitlyLogin" name="bitlyLogin" />
			</p>
			<p>
				<label for="bitlyAPIKey">API Key:</label>
				<input type="text" value="< ?php echo $bitlyAPIKey; ? >" id="bitlyAPIKey" name="bitlyAPIKey" />
			</p>
			<p>
				<input type="submit" name="atualizarBitLy" value="< ?php _e('Update'); ? >" />;
			</p>
		</form>
	</div>
	< ?php 
}


// Implementação exemplo: [pegalinkcurto url="http://leobalter.net"]
add_shortcode('pegalinkcurto', 'usarShortcodeBitly'); 
function usarShortcodeBitly($atts) {
	extract( shortcode_atts( array('url' => ''), $atts ) );
	return processarLink($url);
}