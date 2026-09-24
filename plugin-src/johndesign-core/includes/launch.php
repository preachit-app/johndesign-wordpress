<?php
if (!defined('ABSPATH')) exit;

/**
 * Redirections héritées du site précédent.
 * Elles sont exactes et n'interfèrent pas avec les nouvelles pages.
 */
function jd_core_legacy_redirects(){
    if (is_admin() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)) return;

    $path=parse_url($_SERVER['REQUEST_URI']??'/',PHP_URL_PATH);
    $path='/'.ltrim((string)$path,'/');
    if(substr($path,-1)!=='/') $path.='/';

    $map=[
        '/creation-site-web-sur-mesure/' => '/creation-site-internet/',
        '/qui-suis-je/'                  => '/a-propos/',
        '/services/'                     => '/#offres',
        '/avis-clients/'                 => '/#avis',
        '/concept-faites-impression/'      => '/print-signaletique/',
        '/offres/'                        => '/#offres',
        '/univers/'                       => '/realisations/',
        '/univers-creatif/'               => '/realisations/',
    ];

    if(isset($map[$path])){
        wp_safe_redirect(home_url($map[$path]),301,'John Design');
        exit;
    }

    // Si l'ancien domaine arrive jusqu'à WordPress, conserver le chemin vers le domaine canonique.
    $host=strtolower((string)($_SERVER['HTTP_HOST']??''));
    if($host==='johndesign.net'||$host==='www.johndesign.net'){
        $target='https://johndesign.fr'.($_SERVER['REQUEST_URI']??'/');
        wp_redirect($target,301,'John Design');
        exit;
    }
}
add_action('template_redirect','jd_core_legacy_redirects',1);

/** La page concept redondante reste une redirection 301 et sort des navigations/sitemaps. */
function jd_core_legacy_concept_page_id_2157(){
    $page=get_page_by_path('concept-faites-impression');
    return ($page instanceof WP_Post)?(int)$page->ID:0;
}
function jd_core_hide_legacy_concept_menu_2157($items){
    foreach((array)$items as $key=>$item){
        $path=parse_url((string)($item->url??''),PHP_URL_PATH);
        $path='/'.trim((string)$path,'/').'/';
        if($path==='/concept-faites-impression/') unset($items[$key]);
    }
    return array_values($items);
}
add_filter('wp_nav_menu_objects','jd_core_hide_legacy_concept_menu_2157',20);

function jd_core_exclude_legacy_concept_yoast_2157($ids){
    $ids=is_array($ids)?$ids:[];
    $id=jd_core_legacy_concept_page_id_2157();
    if($id) $ids[]=$id;
    return array_values(array_unique(array_map('intval',$ids)));
}
add_filter('wpseo_exclude_from_sitemap_by_post_ids','jd_core_exclude_legacy_concept_yoast_2157');

function jd_core_exclude_legacy_concept_core_sitemap_2157($args,$post_type){
    if($post_type!=='page') return $args;
    $id=jd_core_legacy_concept_page_id_2157();
    if(!$id) return $args;
    $excluded=isset($args['post__not_in'])?(array)$args['post__not_in']:[];
    $excluded[]=$id;
    $args['post__not_in']=array_values(array_unique(array_map('intval',$excluded)));
    return $args;
}
add_filter('wp_sitemaps_posts_query_args','jd_core_exclude_legacy_concept_core_sitemap_2157',10,2);

function jd_core_launch_checks(){
    $host=parse_url(home_url('/'),PHP_URL_HOST);
    $is_staging=(bool)preg_match('/wptiger|staging|preprod|dev\./i',(string)$host);
    $public=(int)get_option('blog_public')===1;

    $front=(int)get_option('page_on_front');
    $front_ok=$front>0 && get_post_status($front)==='publish';

    $needed=[
        'creation-site-internet',
        'identite-visuelle',
        'print-signaletique',
        'realisations',
        'a-propos',
        'methode',
        'contact',
    ];
    $missing=[];
    foreach($needed as $slug){
        $p=get_page_by_path($slug);
        if(!$p || $p->post_status!=='publish') $missing[]=$slug;
    }

    return [
        [
            'label'=>'HTTPS',
            'ok'=>str_starts_with(home_url('/'),'https://'),
            'detail'=>home_url('/'),
        ],
        [
            'label'=>'Permaliens',
            'ok'=>(string)get_option('permalink_structure')!=='',
            'detail'=>(string)get_option('permalink_structure')?:'Structure simple',
        ],
        [
            'label'=>'Page d’accueil',
            'ok'=>$front_ok,
            'detail'=>$front_ok?get_the_title($front):'Aucune page d’accueil publiée définie',
        ],
        [
            'label'=>'Pages principales',
            'ok'=>empty($missing),
            'detail'=>empty($missing)?'Toutes présentes':'Manquantes : '.implode(', ',$missing),
        ],
        [
            'label'=>'E-mail du formulaire',
            'ok'=>(bool)is_email(jd_core_contact_email()),
            'detail'=>jd_core_contact_email(),
        ],
        [
            'label'=>$is_staging?'Indexation préproduction':'Indexation production',
            'ok'=>$is_staging?!$public:$public,
            'detail'=>$public?'Moteurs de recherche autorisés':'Moteurs de recherche découragés',
        ],
        [
            'label'=>'Sitemap',
            'ok'=>true,
            'detail'=>home_url('/wp-sitemap.xml'),
        ],
    ];
}
