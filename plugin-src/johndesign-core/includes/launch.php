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
