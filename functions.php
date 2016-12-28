<?php

// Verrouiller l'accès à tout le site
// Utiliser une page de connexion en front
add_action( 'template_redirect', 'private_website' );
function private_website() {
	global $wp;
	$current_url = home_url(add_query_arg(array(),$wp->request));
	//si l'utilisateur n'est pas connecté, l'envoyer vers la page de connexion
	if (! is_user_logged_in()  && ! is_page( 'inscription' ) ) {
		// Page de login custom
		wp_redirect( home_url( '/inscription/' ) );
		exit();
	} 
	//rediriger les utilisateurs non-validés
	elseif ( is_user_logged_in() && get_user_meta(get_current_user_id(), 'validation', true)!="1" && ! (is_page( 'validation' ) || is_page( 'contact' ) || preg_match("/user/",$current_url))) {
	    wp_redirect( home_url('validation') );
	    exit();
	}
}

//Fonction qui modifie les champs d'information d'un contact
function my_new_contactmethods( $contactmethods ) {
    unset( $contactmethods['yim'] );
    unset( $contactmethods['aim'] );
    unset( $contactmethods['jabber'] );
    $contactmethods['facebook'] = 'Facebook';
    $contactmethods['gplus'] = 'Google+';
    return $contactmethods;
}
add_filter('user_contactmethods','my_new_contactmethods',10,1);


// Fonction qui permet d'ajouter du contenu juste au dessous du formulaire d'inscription
function add_info_login()  {
    echo '<p id="info">Utilisez de préférence une adresse <em>@eleves.ec-nantes.fr</em> pour une validation automatique de votre inscription. </p>'; 
} 
add_action('register_form','add_info_login');

// Fonction qui insere le lien vers le css qui surchargera celui d'origine
function custom_login_css()  {
    echo '<link rel="stylesheet" type="text/css" href="' . get_stylesheet_directory_uri() . '/design/style-login.css" />';
}
add_action('login_head', 'custom_login_css');

// Filtre qui permet de changer l'url du logo
function custom_url_login()  {
    return get_bloginfo( 'siteurl' ); // On retourne l'index du site
}
add_filter('login_headerurl', 'custom_url_login');

// Filtre qui permet de changer l'attribut title du logo
function custom_title_login($message) {
    return get_bloginfo('description'); // On retourne la description du site
}
add_filter('login_headertitle', 'custom_title_login');

// Valide automatiquement l'utilisateur en fonction de son adresse mail
function myplugin_registration_save( $user_id ) {
	$user_info = get_userdata( $user_id);
    if ( preg_match("/@eleves.ec-nantes.fr/", $user_info->user_email) )
		add_user_meta( $user_id, 'validation', 1 );
	else
		add_user_meta( $user_id, 'validation', 0 );
}
add_action( 'user_register', 'myplugin_registration_save', 10, 1 );

//Affiche la liste des utilisateurs
function user_list(){ ?>
	<table border="1" width="500">
   <tr>
      <th>ID</th>
      <th>Pseudo</th>
      <th>Site web</th>
      <th>Date d'inscription</th>
   </tr>

   <?php
   $users = get_users();
   foreach( $users as $user ) { ?>
   	  <tr>
	      <td><?php echo $user->ID; ?></td>
	      <td><?php echo $user->display_name; ?></td>
	      <td><?php if( !empty($user->user_url) ) {echo $user->user_url;} else{echo 'N/C';} ?></td>
	      <td><?php echo date_i18n( get_option('date_format'), strtotime( $user->user_registered ) ); ?></td>
      </tr>
 
  <?php	
  }
}
add_shortcode('jdr_user_list', 'user_list');
