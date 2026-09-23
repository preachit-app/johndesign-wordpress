<?php
if (!defined('ABSPATH')) exit;

/**
 * SEO minimal John Design.
 * Ne prend la main que si aucun plugin SEO majeur n'est actif.
 */
function jd_core_has_external_seo(){
    return defined('WPSEO_VERSION')
        || defined('RANK_MATH_VERSION')
        || defined('AIOSEO_VERSION')
        || class_exists('AIOSEO\\Plugin\\AIOSEO');
}

function jd_core_seo_map(){
    return [
        'home' => [
            'focus' => 'graphiste indépendant',
            'title' => 'Graphiste indépendant — Web, identité & print | John Design',
            'description' => 'Graphiste indépendant, John Design crée sites internet, identités visuelles, supports print et signalétique pour entreprises, associations et indépendants.',
        ],
        'creation-site-internet' => [
            'focus' => 'site',
            'title' => 'Création de site internet sur mesure | John Design',
            'description' => 'Votre site doit être clair, rapide et fidèle à votre activité. John Design conçoit votre site internet sur mesure, de la structure à la mise en ligne.',
        ],
        'identite-visuelle' => [
            'focus' => 'identité',
            'title' => 'Identité visuelle & création de logo | John Design',
            'description' => 'Votre identité doit être reconnaissable et simple à utiliser. John Design crée logo, univers graphique et déclinaisons cohérentes pour votre activité.',
        ],
        'print-signaletique' => [
            'focus' => 'print',
            'title' => 'Print, signalétique & marquage | John Design',
            'description' => 'John Design conçoit vos supports print, panneaux, adhésifs, signalétique et marquage, avec accompagnement possible jusqu’à la fabrication et la pose.',
        ],
        'realisations' => [
            'focus' => 'réalisations',
            'title' => 'Réalisations — Web, print & signalétique | John Design',
            'description' => 'Découvrez les réalisations John Design : sites internet, identité visuelle, print, signalétique, marquage et supports de communication pour de vrais projets.',
        ],
        'a-propos' => [
            'focus' => 'graphiste indépendant',
            'title' => 'Graphiste indépendant — À propos | John Design',
            'description' => 'Jonathan Romain est graphiste indépendant depuis 2015. Découvrez John Design et une approche directe, créative et concrète de la communication visuelle.',
        ],
        'methode' => [
            'focus' => 'méthode',
            'title' => 'Méthode de création & accompagnement | John Design',
            'description' => 'Découvrez la méthode John Design : échange, cadrage, création, ajustements, préparation des fichiers et accompagnement jusqu’au lancement de votre projet.',
        ],
        'contact' => [
            'focus' => 'contact',
            'title' => 'Contact & devis graphiste | John Design',
            'description' => 'Contactez John Design pour parler de votre projet : site internet, identité, print, signalétique ou communication globale. Demande de devis et premier échange.',
        ],
        'mentions-legales' => [
            'title' => 'Mentions légales — John Design',
            'description' => 'Mentions légales du site John Design.',
            'noindex' => true,
        ],
        'politique-de-confidentialite' => [
            'title' => 'Politique de confidentialité — John Design',
            'description' => 'Politique de confidentialité et traitement des données du site John Design.',
            'noindex' => true,
        ],
    ];
}

function jd_core_seo_current_key(){
    if (is_front_page()) return 'home';
    if (!is_page()) return '';
    $post=get_queried_object();
    return ($post instanceof WP_Post)?$post->post_name:'';
}
function jd_core_seo_current(){
    $key=jd_core_seo_current_key();
    $map=jd_core_seo_map();
    return $key && isset($map[$key]) ? $map[$key] : null;
}

add_filter('pre_get_document_title',function($title){
    if (is_admin() || jd_core_has_external_seo()) return $title;
    $seo=jd_core_seo_current();
    return !empty($seo['title']) ? $seo['title'] : $title;
},20);

add_filter('wp_robots',function($robots){
    $seo=jd_core_seo_current();
    if (!empty($seo['noindex'])) {
        $robots['noindex']=true;
        $robots['nofollow']=false;
    }
    return $robots;
},20);

add_action('wp_head',function(){
    if (is_admin() || jd_core_has_external_seo()) return;
    $seo=jd_core_seo_current();
    if (!$seo) return;

    $description=trim((string)($seo['description']??''));
    if ($description) echo "\n<meta name=\"description\" content=\"".esc_attr($description)."\">\n";

    $url=is_front_page()?home_url('/'):get_permalink();
    $title=(string)($seo['title']??wp_get_document_title());
    echo '<meta property="og:type" content="website">'."\n";
    echo '<meta property="og:locale" content="fr_FR">'."\n";
    echo '<meta property="og:site_name" content="John Design">'."\n";
    echo '<meta property="og:title" content="'.esc_attr($title).'">'."\n";
    if ($description) echo '<meta property="og:description" content="'.esc_attr($description).'">'."\n";
    echo '<meta property="og:url" content="'.esc_url($url).'">'."\n";
    echo '<meta name="twitter:card" content="summary_large_image">'."\n";

    if (is_front_page()) {
        $schema=[
            '@context'=>'https://schema.org',
            '@type'=>'ProfessionalService',
            'name'=>'John Design',
            'url'=>home_url('/'),
            'email'=>'mailto:'.jd_core_contact_email(),
            'description'=>$description,
            'areaServed'=>[
                '@type'=>'Country',
                'name'=>'France',
            ],
            'sameAs'=>[
                'https://www.instagram.com/johndesign_net/',
            ],
            'knowsAbout'=>[
                'Création de site internet',
                'Identité visuelle',
                'Logo',
                'Print',
                'Signalétique',
                'Marquage',
            ],
        ];
        echo '<script type="application/ld+json">'.wp_json_encode($schema,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).'</script>'."\n";
    }
},5);


/**
 * Intégration Yoast SEO.
 * On laisse Yoast gérer canonicals, sitemap, Open Graph et schéma,
 * mais John Design fournit les titres et descriptions validés du site.
 */
function jd_core_yoast_value($existing,$field){
    if (is_admin()) return $existing;
    $seo=jd_core_seo_current();
    if (!$seo) return $existing;
    if ($field==='title' && !empty($seo['title'])) return $seo['title'];
    if ($field==='description' && !empty($seo['description'])) return $seo['description'];
    return $existing;
}
add_filter('wpseo_title',function($value){return jd_core_yoast_value($value,'title');},20);
add_filter('wpseo_metadesc',function($value){return jd_core_yoast_value($value,'description');},20);
add_filter('wpseo_opengraph_title',function($value){return jd_core_yoast_value($value,'title');},20);
add_filter('wpseo_opengraph_desc',function($value){return jd_core_yoast_value($value,'description');},20);
add_filter('wpseo_twitter_title',function($value){return jd_core_yoast_value($value,'title');},20);
add_filter('wpseo_twitter_description',function($value){return jd_core_yoast_value($value,'description');},20);


/**
 * Synchronise les champs éditables de Yoast avec la stratégie John Design.
 * On ne fabrique pas les scores : Yoast garde son analyse réelle.
 */
function jd_core_seo_page_id($key){
    if($key==='home') return (int)get_option('page_on_front');
    $page=get_page_by_path($key);
    return ($page instanceof WP_Post)?(int)$page->ID:0;
}
function jd_core_sync_yoast_meta($force=false){
    if(!defined('WPSEO_VERSION')) return ['updated'=>0,'missing'=>[]];
    $map=jd_core_seo_map();
    $updated=0;$missing=[];
    foreach($map as $key=>$data){
        if(empty($data['focus'])) continue;
        $id=jd_core_seo_page_id($key);
        if(!$id){$missing[]=$key;continue;}
        update_post_meta($id,'_yoast_wpseo_focuskw',sanitize_text_field($data['focus']));
        update_post_meta($id,'_yoast_wpseo_title',sanitize_text_field($data['title']));
        update_post_meta($id,'_yoast_wpseo_metadesc',sanitize_text_field($data['description']));
        $updated++;
    }
    update_option('jd_core_yoast_sync_version',JD_CORE_VERSION,false);
    update_option('jd_core_yoast_sync_diag',[
        'updated'=>$updated,
        'missing'=>$missing,
        'checked_at'=>current_time('mysql'),
    ],false);
    return ['updated'=>$updated,'missing'=>$missing];
}
add_action('admin_init',function(){
    if(!defined('WPSEO_VERSION')) return;
    if(get_option('jd_core_yoast_sync_version')!==JD_CORE_VERSION) jd_core_sync_yoast_meta();
},30);

function jd_core_manual_yoast_sync(){
    if(!current_user_can('manage_options')) wp_die('Accès refusé');
    check_admin_referer('jd_sync_yoast');
    $result=jd_core_sync_yoast_meta(true);
    wp_safe_redirect(add_query_arg([
        'page'=>'jd-core',
        'yoast_synced'=>1,
        'yoast_updated'=>(int)$result['updated'],
    ],admin_url('admin.php')).'#jd-seo');
    exit;
}
add_action('admin_post_jd_sync_yoast','jd_core_manual_yoast_sync');

/**
 * Les modules John Design sont des blocs dynamiques. Cette intégration officielle
 * fournit leur contenu réel à l'analyse JavaScript de Yoast dans Gutenberg.
 */
function jd_core_enqueue_yoast_analysis_bridge(){
    if(!defined('WPSEO_VERSION')) return;
    $screen=function_exists('get_current_screen')?get_current_screen():null;
    if(!$screen || $screen->base!=='post' || $screen->post_type!=='page') return;
    wp_enqueue_script(
        'jd-yoast-analysis',
        JD_CORE_URL.'assets/yoast-analysis.js',
        ['jquery','wp-data'],
        JD_CORE_VERSION,
        true
    );
}
add_action('admin_enqueue_scripts','jd_core_enqueue_yoast_analysis_bridge',30);
