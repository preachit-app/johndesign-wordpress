<?php
if (!defined('ABSPATH')) exit;
function jd_core_contact_email(){return sanitize_email(get_option('jd_contact_email','jonathan@johndesign.net'));}
function jd_core_contact_form($variant='home'){
 $full=$variant==='full';$uid=wp_unique_id('jd-form-');$action=esc_url(admin_url('admin-post.php'));$start=time();$return=esc_url((is_ssl()?'https':'http').'://'.($_SERVER['HTTP_HOST']??'').($_SERVER['REQUEST_URI']??'/'));
 ob_start(); ?>
 <form class="jd-contact-form" method="post" action="<?php echo $action; ?>" autocomplete="on">
 <input type="hidden" name="action" value="jd_contact_submit"><input type="hidden" name="jd_variant" value="<?php echo esc_attr($variant); ?>"><input type="hidden" name="jd_started" value="<?php echo esc_attr($start); ?>"><input type="hidden" name="jd_return" value="<?php echo esc_attr($return); ?>"><?php wp_nonce_field('jd_contact','jd_nonce'); ?>
 <div class="jd-form-grid"><div class="jd-field"><label for="<?php echo $uid; ?>name">Votre nom *</label><input id="<?php echo $uid; ?>name" autocomplete="name" name="name" required type="text"></div><div class="jd-field"><label for="<?php echo $uid; ?>email">Votre e-mail *</label><input id="<?php echo $uid; ?>email" autocomplete="email" name="email" required type="email"></div><?php if($full): ?><div class="jd-field"><label for="<?php echo $uid; ?>company">Votre activité / entreprise</label><input id="<?php echo $uid; ?>company" autocomplete="organization" name="company" type="text"></div><?php endif; ?><div class="jd-field"><label for="<?php echo $uid; ?>phone">Téléphone</label><input id="<?php echo $uid; ?>phone" autocomplete="tel" name="phone" type="tel"></div></div>
 <div class="jd-field"><label for="<?php echo $uid; ?>project">Votre projet *</label><select id="<?php echo $uid; ?>project" name="project" required><option value="">Choisir</option><option>Création / refonte de site</option><option>Identité visuelle / logo</option><option>Print / signalétique</option><option>Projet global</option><option>Autre</option></select></div>
 <?php if($full): ?><div class="jd-form-grid"><div class="jd-field"><label for="<?php echo $uid; ?>budget">Budget envisagé</label><input id="<?php echo $uid; ?>budget" name="budget" placeholder="Une fourchette, même indicative"></div><div class="jd-field"><label for="<?php echo $uid; ?>deadline">Échéance souhaitée</label><input id="<?php echo $uid; ?>deadline" name="deadline" placeholder="Une date ou une période"></div></div><?php endif; ?>
 <div class="jd-field"><label for="<?php echo $uid; ?>message"><?php echo $full?'Parlez-moi de votre besoin *':'En quelques mots *'; ?></label><textarea id="<?php echo $uid; ?>message" name="message" required rows="<?php echo $full?5:4; ?>" placeholder="Expliquez-moi ce que vous voulez lancer, améliorer ou créer."></textarea></div>
 <div class="jd-form-hp" aria-hidden="true"><label for="<?php echo $uid; ?>website">Ne pas remplir</label><input id="<?php echo $uid; ?>website" name="website" autocomplete="off" tabindex="-1" type="text"></div>
 <label class="jd-consent"><input name="consent" required type="checkbox" value="1"> J’accepte que ces informations soient utilisées pour répondre à ma demande.</label>
 <button type="submit">Envoyer ma demande</button><p class="jd-caption">Réponse directement par e-mail. Vos informations ne sont utilisées que pour traiter votre demande.</p>
 <?php if(isset($_GET['jd_contact'])): $ok=$_GET['jd_contact']==='success'; ?><p class="jd-form-status <?php echo $ok?'':'is-error'; ?>" role="status"><?php echo $ok?'Merci, votre message a bien été envoyé.':'Le message n’a pas pu être envoyé. Réessayez ou écrivez directement à '.esc_html(jd_core_contact_email()).'.'; ?></p><?php endif; ?>
 </form><?php return ob_get_clean();
}
function jd_core_contact_submit(){
 if(!isset($_POST['jd_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['jd_nonce'])),'jd_contact'))wp_die('Requête invalide.',403);
 $return=esc_url_raw(wp_unslash($_POST['jd_return']??home_url('/contact/')));if(!$return)$return=home_url('/contact/');
 if(!empty($_POST['website'])){wp_safe_redirect(add_query_arg('jd_contact','success',$return));exit;}
 $started=absint($_POST['jd_started']??0);if(!$started||time()-$started<3){wp_safe_redirect(add_query_arg('jd_contact','error',$return));exit;}
 $ip=$_SERVER['REMOTE_ADDR']??'';$key='jd_rate_'.md5(wp_salt('nonce').'|'.$ip);$count=(int)get_transient($key);if($count>=5){wp_safe_redirect(add_query_arg('jd_contact','error',$return));exit;}set_transient($key,$count+1,HOUR_IN_SECONDS);
 $name=sanitize_text_field(wp_unslash($_POST['name']??''));$email=sanitize_email(wp_unslash($_POST['email']??''));$phone=sanitize_text_field(wp_unslash($_POST['phone']??''));$company=sanitize_text_field(wp_unslash($_POST['company']??''));$project=sanitize_text_field(wp_unslash($_POST['project']??''));$budget=sanitize_text_field(wp_unslash($_POST['budget']??''));$deadline=sanitize_text_field(wp_unslash($_POST['deadline']??''));$message=sanitize_textarea_field(wp_unslash($_POST['message']??''));
 if(!$name||!is_email($email)||!$project||!$message||empty($_POST['consent'])){wp_safe_redirect(add_query_arg('jd_contact','error',$return));exit;}
 $subject='[John Design] Nouvelle demande — '.$project;$body="Nom : $name\nE-mail : $email\nTéléphone : $phone\nEntreprise : $company\nProjet : $project\nBudget : $budget\nÉchéance : $deadline\n\n$message\n";$headers=['Content-Type: text/plain; charset=UTF-8','Reply-To: '.$name.' <'.$email.'>'];
 $sent=wp_mail(jd_core_contact_email(),$subject,$body,$headers);wp_safe_redirect(add_query_arg('jd_contact',$sent?'success':'error',$return));exit;
}
add_action('admin_post_nopriv_jd_contact_submit','jd_core_contact_submit');add_action('admin_post_jd_contact_submit','jd_core_contact_submit');
