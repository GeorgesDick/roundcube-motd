<?php

/**
 * Motd (Message Of The Day)
 *
 * @version 0.1 - 24.01.2011
 * @author Georges DICK
 * @website http://georgesdick.com
 * @licence GNU GPL
 *
 **/
 
/**
 *
 * Usage: Similar to http://mail4us.net/myroundcube/
 *
 **/
 
class motd extends rcube_plugin
{

  function init(){
    // Chargement des textes dans la bonne langue
    $this->add_texts('localization/', false);
    // Methode a appeler au demarrage du plugin
    $this->register_action('plugin.motd', array($this, 'motd_startup'));     
    // "hook" de l'affichage du texte du jour
    $this->add_hook('template_object_motd_message', array($this, 'motd_html_motd_message'));
	// "hook" de l'affichage du bouton de demande de non affichage
    $this->add_hook('template_object_motd_disable', array($this, 'motd_html_disable'));
	// "hook" d'invocation lors d'une connexion reussie
    $this->add_hook('login_after', array($this, 'ma_fonction'));
	// "action" lors du click de la case de demande de non affichage
    $this->register_action('plugin.motd_disable', array($this, 'motd_disable'));    
  }
  
  function ma_fonction($args){
write_log('motd', 'Dans ma_fonction()');
    // Recuperation de l'objet (type singleton) de l'API RoundCube
    $rcmail = rcmail::get_instance();
	// Demande de lancement de l'action qui va "demarrer" le plugin
    $rcmail->output->redirect(array('_action' => 'plugin.motd', '_task' => 'mail'));
  }
  
  function motd_startup(){
    $rcmail = rcmail::get_instance();
	// Construction du chemin d'acces au fichier dans le bon langage
    $filename = "./plugins/motd/motd/" . $_SESSION['language'] . ".html";
	// On ne veut connaitre que sa date de derniere modification
    if(file_exists($filename))
      $datefic = date ('omdHi', filemtime($filename));
    else
      $datefic = 0;
	// L'utilisateur a-t-il demande a ne plus voir le message (et si oui, quand) ?
    $monresu = $rcmail->config->get('nomotd') - $datefic;
	// Non, ou avant la derniere modification
    if ($monresu < 0) {
	  // Inclusion de la feuille de style
      if(!file_exists("plugins/motd/skins/$skin/motd.css"))
        $skin  = $rcmail->config->get('skin');
      else
        $skin = "default";
      $this->include_stylesheet('skins/' . $skin . '/motd.css');
	  // Demande d'affichage de la page
      $rcmail->output->send("motd.motd");
      }
    else {
	  // Oui, il ne veut plus voir ce message, on envoie sur la page d'accueil
      $rcmail->output->redirect(array('_action' => '', '_mbox' => 'INBOX'));
    }
  }

  function motd_html_motd_message($args){
    // Construction du chemin d'acces au fichier dans le bon langage et lecture du fichier
    if(file_exists("./plugins/motd/motd/" . $_SESSION['language'] . ".html"))
      $content = file_get_contents("./plugins/motd/motd/" . $_SESSION['language'] . ".html");
    else
	  // Si on a pas le bon langage, on affichera en anglais U.S.
      $content = file_get_contents("./plugins/motd/motd/en_US.html.dist");
	// Generation du code HTML a afficher
    $motd  = '<fieldset><legend>' . $this->gettext('motd') . '</legend>';
    $motd .= $content;
    $motd .= '</fieldset>';
	// Valorisation et renvoi du tableau de retour (API de RoundCube)
    $args['content'] = $motd;
    return $args;
  }

  function motd_html_disable($args){
    // Generation du code HTML a afficher pour la case a cocher de demande de fin d'affichage
    $html  = '<br />';
	// On lancera l'action "plugin.motd_disable"
    $html .= '<form name="f" method="post" action="./?_action=plugin.motd_disable">';
    $html .= '<table width="100%"><tr><td align="right">';
    $html .= $this->gettext('disablemotd') . '&nbsp;' . '<input name="_motddisable" value="1" onclick="document.forms.f.submit()" type="checkbox" />&nbsp;';
    $html .= '</td></tr></table>';
    $html .= '</form>';
	// Valorisation et renvoi du tableau de retour (API de RoundCube)
    $args['content'] = $html;
    return $args;
  }  

  function motd_disable(){
    // A-t-on bien recu le resultat du bon formulaire ?
    if($_POST['_motddisable'] == 1){
	  // Oui : on positionne une variable "nomotd" a la date du jour
      $rcmail = rcmail::get_instance();
      $a_prefs = $rcmail->user->get_prefs();
      $a_prefs['nomotd'] = date ('omdHi');
      $rcmail->user->save_prefs($a_prefs);
	  // ... et on renvoie sur la page d'accueil
      $rcmail->output->redirect(array('_action' => '', '_mbox' => 'INBOX'));
    }
    return;
  }

}

?>
