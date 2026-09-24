<?php
if (!defined('ABSPATH')) exit;
function jd_core_admin_menu(){add_menu_page('John Design','John Design','manage_options','jd-core','jd_core_admin_page','dashicons-art',58);}
add_action('admin_menu','jd_core_admin_menu');
function jd_core_build_block($sid){$defs=jd_core_sections();$fields=[];foreach($defs[$sid]['fields'] as $f)$fields[$f['key']]=$f['default'];$attrs=['sectionId'=>$sid,'fields'=>$fields];return '<!-- wp:johndesign/section '.wp_json_encode($attrs,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).' /-->';}
function jd_core_pages_data(){static $d=null;if($d===null){$stored=get_option('jd_core_pages_library');if(is_array($stored)&&$stored)$d=$stored;else{$file=JD_CORE_DIR.'data/pages.json';$d=file_exists($file)?(json_decode(file_get_contents($file),true)?:[]):[];}}return $d;}
function jd_core_install_site(){
 if(!current_user_can('manage_options'))return;check_admin_referer('jd_install_site');$data=jd_core_pages_data();$ids=[];
 foreach($data['sections'] as $slug=>$sections){$content=implode("\n\n",array_map('jd_core_build_block',$sections));$postarr=['post_title'=>$data['titles'][$slug]??ucwords(str_replace('-',' ',$slug)),'post_content'=>$content,'post_status'=>'publish','post_type'=>'page'];$existing=$slug==='home'?get_page_by_path(''):get_page_by_path($slug);if($slug==='home'){$front=(int)get_option('page_on_front');$existing=$front?get_post($front):get_page_by_path('accueil');$postarr['post_name']='accueil';}else{$postarr['post_name']=$slug;}if($existing&&$existing instanceof WP_Post){$postarr['ID']=$existing->ID;$id=wp_update_post(wp_slash($postarr),true);}else{$id=wp_insert_post(wp_slash($postarr),true);}if(!is_wp_error($id))$ids[$slug]=(int)$id;}
 if(!empty($ids['home'])){update_option('show_on_front','page');update_option('page_on_front',$ids['home']);}
 update_option('jd_v212_installed',current_time('mysql'));wp_safe_redirect(admin_url('admin.php?page=jd-core&installed=1'));exit;
}
add_action('admin_post_jd_install_site','jd_core_install_site');
function jd_core_save_settings(){
 if(!current_user_can('manage_options'))return;
 check_admin_referer('jd_save_settings');
 update_option('jd_contact_email',sanitize_email($_POST['contact_email']??'jonathan@johndesign.net'));
 update_option('jd_contact_copy_email',sanitize_email($_POST['contact_copy_email']??''));
 wp_safe_redirect(admin_url('admin.php?page=jd-core&saved=1'));exit;
}
add_action('admin_post_jd_save_settings','jd_core_save_settings');
function jd_core_admin_page(){ ?>
<div class="wrap"><h1>John Design — V2.12</h1><p>Le thème conserve la maquette V2.12. Chaque section est un module Gutenberg déplaçable ; son texte, ses liens et ses images sont modifiables dans la colonne de réglages du bloc.</p>
<?php if(isset($_GET['installed'])):?><div class="notice notice-success"><p>Les pages V2.12 ont été créées / mises à jour et la page d’accueil a été définie.</p></div><?php endif; ?>
<h2>1. E-mails du formulaire</h2>
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="max-width:760px">
<input type="hidden" name="action" value="jd_save_settings"><?php wp_nonce_field('jd_save_settings'); ?>
<table class="form-table" role="presentation"><tbody>
<tr><th scope="row"><label for="jd-contact-email">Boîte principale</label></th><td><input id="jd-contact-email" name="contact_email" type="email" class="regular-text" value="<?php echo esc_attr(jd_core_contact_email()); ?>"><p class="description">Adresse professionnelle qui reçoit les demandes.</p></td></tr>
<tr><th scope="row"><label for="jd-contact-copy-email">Copie directe (optionnel)</label></th><td><input id="jd-contact-copy-email" name="contact_copy_email" type="email" class="regular-text" value="<?php echo esc_attr(jd_core_contact_copy_email()); ?>" placeholder="votre-adresse@gmail.com"><p class="description">Pour recevoir une copie directement dans Gmail sans passer par un redirecteur externe.</p></td></tr>
</tbody></table>
<button class="button button-primary">Enregistrer</button>
</form>
<div id="jd-mail"></div>
<h2>2. Formulaire & e-mails</h2>
<?php $maildiag=get_option('jd_core_mail_diag',[]); ?>
<p>Le formulaire envoie les demandes à <strong><?php echo esc_html(jd_core_contact_email()); ?></strong><?php if(jd_core_contact_copy_email()) echo ' et une copie directe à <strong>'.esc_html(jd_core_contact_copy_email()).'</strong>'; ?>. Les notifications John Design et les confirmations client utilisent maintenant une mise en page HTML de marque. Protection active : nonce WordPress, champ piège invisible, délai minimum, limitation à 5 envois/heure/IP et filtre anti-liens.</p>
<?php if(isset($_GET['mailtest'])): ?><div class="notice <?php echo $_GET['mailtest']==='1'?'notice-success':'notice-error'; ?>"><p><?php echo $_GET['mailtest']==='1'?'Le test a été transmis par WordPress. Vérifiez maintenant la boîte '.esc_html(jd_core_contact_email()).'.':'Le test a échoué côté WordPress. Le diagnostic ci-dessous donne le dernier état connu.'; ?></p></div><?php endif; ?>
<?php if(!empty($maildiag)): ?><p><strong>Dernier diagnostic e-mail :</strong> <?php echo ($maildiag['status']??'')==='success'?'<span style="color:#16803a;font-weight:700">OK</span>':'<span style="color:#b42318;font-weight:700">Échec</span>'; ?> — <?php echo esc_html($maildiag['message']??''); ?><br><small><?php echo esc_html($maildiag['checked_at']??''); ?><?php if(!empty($maildiag['to'])) echo ' · vers '.esc_html($maildiag['to']); ?></small></p><?php endif; ?>
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="jd_test_mail"><?php wp_nonce_field('jd_test_mail'); ?><button class="button">Envoyer un e-mail de test</button></form>
<p><small>Important : un statut « OK » signifie que WordPress a accepté l’envoi. La réception réelle dépend ensuite du service mail de l’hébergement / SMTP.</small></p>

<h2>3. Installer / mettre à jour les pages V2.12</h2><p><strong>À faire sur une copie de préproduction d’abord.</strong> Cette action met à jour les pages portant les mêmes slugs et crée une révision WordPress avant la modification.</p><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('Installer la V2.12 sur les pages correspondantes ?');"><input type="hidden" name="action" value="jd_install_site"><?php wp_nonce_field('jd_install_site'); ?><button class="button button-primary button-hero">Installer / mettre à jour la V2.12</button></form>
<h2>4. Édition</h2><p>Ensuite : Pages → choisissez une page → cliquez un module John Design → modifiez son contenu à droite. Vous pouvez déplacer, dupliquer ou supprimer les modules comme des blocs Gutenberg.</p>
<h2>5. Mises à jour John Design</h2>
<?php $diag=get_option('jd_core_update_diag',[]); $remote=jd_core_remote_manifest(); ?>
<p><strong>Version installée :</strong> <?php echo esc_html(JD_CORE_VERSION); ?> &nbsp;·&nbsp; <strong>Version distante :</strong> <?php echo esc_html(is_array($remote)&&!empty($remote['version'])?$remote['version']:'non détectée'); ?></p>
<?php if(!empty($diag)): ?><p><strong>Connexion GitHub :</strong> <?php echo !empty($diag['ok'])?'<span style=\"color:#16803a;font-weight:700\">OK</span>':'<span style=\"color:#b42318;font-weight:700\">Échec</span>'; ?> — <?php echo esc_html(($diag['status']??0).' · '.($diag['error']??'')); ?><br><code><?php echo esc_html($diag['endpoint']??''); ?></code><br><small>Dernier contrôle : <?php echo esc_html($diag['checked_at']??''); ?></small></p><?php endif; ?>
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="jd_force_update_check"><?php wp_nonce_field('jd_force_update_check'); ?><button class="button">Vérifier maintenant les mises à jour GitHub</button></form>
<h2>6. SEO & indexation</h2>
<?php $seo_external=function_exists('jd_core_has_external_seo')&&jd_core_has_external_seo(); $yoast=defined('WPSEO_VERSION'); $public=(int)get_option('blog_public')===1; ?>
<p><strong>Indexation WordPress :</strong> <?php echo $public?'<span style="color:#16803a;font-weight:700">autorisée</span>':'<span style="color:#b42318;font-weight:700">désactivée</span>'; ?> &nbsp;·&nbsp; <strong>SEO :</strong> <?php echo $yoast?'Yoast SEO actif — titres et descriptions John Design injectés dans Yoast':($seo_external?'plugin SEO externe actif':'métadonnées John Design actives'); ?></p>
<p><strong>Sitemap WordPress :</strong> <code><?php echo esc_html(home_url('/wp-sitemap.xml')); ?></code></p>
<div id="jd-seo"></div>
<?php $yoastdiag=get_option('jd_core_yoast_sync_diag',[]); ?>
<h3>Optimisation Yoast des pages</h3>
<?php $contentdiag=get_option('jd_core_seo_content_tuning_diag',[]); ?>
<?php if(!empty($contentdiag)): ?><p><strong>Contenu SEO réel :</strong> <?php echo absint($contentdiag['changed']??0); ?> page(s) ajustée(s) automatiquement · <?php echo esc_html($contentdiag['checked_at']??''); ?>. Les phrases ajoutées sont visibles sur le site : aucun texte caché n’est utilisé.</p><?php endif; ?>
<p>John Design renseigne les requêtes cibles uniquement sur les pages qui ont une vraie intention de recherche commerciale. Les autres pages restent optimisées (titre et méta-description) sans forcer une requête artificielle juste pour obtenir un feu vert.</p>
<?php if(isset($_GET['yoast_synced'])): ?><div class="notice notice-success inline"><p>Yoast synchronisé sur <?php echo absint($_GET['yoast_updated']??0); ?> pages.</p></div><?php endif; ?>
<?php if(!empty($yoastdiag)): ?><p><small>Dernière synchronisation : <?php echo esc_html($yoastdiag['checked_at']??''); ?> · <?php echo absint($yoastdiag['updated']??0); ?> pages mises à jour<?php if(!empty($yoastdiag['missing'])) echo ' · pages manquantes : '.esc_html(implode(', ',$yoastdiag['missing'])); ?>.</small></p><?php endif; ?>
<table class="widefat striped" style="max-width:1050px;margin:12px 0 14px"><thead><tr><th>Page</th><th>Requête cible principale</th><th>Requêtes secondaires / rôle</th></tr></thead><tbody>
<?php foreach(jd_core_seo_map() as $slug=>$seo_row): if(!empty($seo_row['noindex'])) continue; ?>
<tr>
<td><strong><?php echo esc_html($slug==='home'?'Accueil':$slug); ?></strong></td>
<td><?php echo !empty($seo_row['focus'])?esc_html($seo_row['focus']):'<em>Pas de requête forcée</em>'; ?></td>
<td><?php echo !empty($seo_row['secondary'])?esc_html($seo_row['secondary']):esc_html($seo_row['intent']??'Page de support à la conversion'); ?></td>
</tr>
<?php endforeach; ?>
</tbody></table>
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="jd_sync_yoast"><?php wp_nonce_field('jd_sync_yoast'); ?><button class="button button-secondary">Resynchroniser Yoast maintenant</button></form>
<p><small>Sur la préproduction, garder l’indexation désactivée. Elle ne devra être activée qu’au moment de la mise en ligne définitive.</small></p>

<h2>7. Préparation à la mise en ligne</h2>
<?php $launch_checks=function_exists('jd_core_launch_checks')?jd_core_launch_checks():[]; ?>
<table class="widefat striped" style="max-width:900px"><tbody>
<?php foreach($launch_checks as $check): ?>
<tr><td style="width:230px"><strong><?php echo esc_html($check['label']); ?></strong></td><td style="width:90px"><?php echo !empty($check['ok'])?'<span style="color:#16803a;font-weight:700">OK</span>':'<span style="color:#b42318;font-weight:700">À vérifier</span>'; ?></td><td><?php echo esc_html($check['detail']); ?></td></tr>
<?php endforeach; ?>
</tbody></table>
<p><small>Les anciennes URL principales sont redirigées automatiquement, notamment <code>/creation-site-web-sur-mesure/</code> vers <code>/creation-site-internet/</code>. Sur WP Tiger, l’indexation doit rester désactivée jusqu’à la publication.</small></p>

</div><?php }
