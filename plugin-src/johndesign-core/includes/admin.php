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
function jd_core_save_settings(){if(!current_user_can('manage_options'))return;check_admin_referer('jd_save_settings');update_option('jd_contact_email',sanitize_email($_POST['contact_email']??'jonathan@johndesign.net'));wp_safe_redirect(admin_url('admin.php?page=jd-core&saved=1'));exit;}
add_action('admin_post_jd_save_settings','jd_core_save_settings');
function jd_core_admin_page(){ ?>
<div class="wrap"><h1>John Design — V2.12</h1><p>Le thème conserve la maquette V2.12. Chaque section est un module Gutenberg déplaçable ; son texte, ses liens et ses images sont modifiables dans la colonne de réglages du bloc.</p>
<?php if(isset($_GET['installed'])):?><div class="notice notice-success"><p>Les pages V2.12 ont été créées / mises à jour et la page d’accueil a été définie.</p></div><?php endif; ?>
<h2>1. E-mail du formulaire</h2><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="jd_save_settings"><?php wp_nonce_field('jd_save_settings'); ?><input name="contact_email" type="email" class="regular-text" value="<?php echo esc_attr(jd_core_contact_email()); ?>"><button class="button button-primary">Enregistrer</button></form>
<h2>2. Installer / mettre à jour les pages V2.12</h2><p><strong>À faire sur une copie de préproduction d’abord.</strong> Cette action met à jour les pages portant les mêmes slugs et crée une révision WordPress avant la modification.</p><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('Installer la V2.12 sur les pages correspondantes ?');"><input type="hidden" name="action" value="jd_install_site"><?php wp_nonce_field('jd_install_site'); ?><button class="button button-primary button-hero">Installer / mettre à jour la V2.12</button></form>
<h2>3. Édition</h2><p>Ensuite : Pages → choisissez une page → cliquez un module John Design → modifiez son contenu à droite. Vous pouvez déplacer, dupliquer ou supprimer les modules comme des blocs Gutenberg.</p>
<h2>4. Mises à jour John Design</h2>
<?php $diag=get_option('jd_core_update_diag',[]); $remote=jd_core_remote_manifest(); ?>
<p><strong>Version installée :</strong> <?php echo esc_html(JD_CORE_VERSION); ?> &nbsp;·&nbsp; <strong>Version distante :</strong> <?php echo esc_html(is_array($remote)&&!empty($remote['version'])?$remote['version']:'non détectée'); ?></p>
<?php if(!empty($diag)): ?><p><strong>Connexion GitHub :</strong> <?php echo !empty($diag['ok'])?'<span style=\"color:#16803a;font-weight:700\">OK</span>':'<span style=\"color:#b42318;font-weight:700\">Échec</span>'; ?> — <?php echo esc_html(($diag['status']??0).' · '.($diag['error']??'')); ?><br><code><?php echo esc_html($diag['endpoint']??''); ?></code><br><small>Dernier contrôle : <?php echo esc_html($diag['checked_at']??''); ?></small></p><?php endif; ?>
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="jd_force_update_check"><?php wp_nonce_field('jd_force_update_check'); ?><button class="button">Vérifier maintenant les mises à jour GitHub</button></form>
</div><?php }
