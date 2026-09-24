<?php
if (!defined('ABSPATH')) exit;

/**
 * John Design Core 2.16.9 — nettoyage définitif des pages.
 *
 * Pages de contenu conservées :
 * Accueil, Site internet, Identité visuelle, Print & signalétique,
 * Réalisations, À propos, Méthode, Contact.
 *
 * Mentions légales et confidentialité restent uniquement comme utilitaires
 * légaux/noindex et ne font pas partie de la navigation principale.
 */

function jd_core_main_page_slugs_2169(){
    return [
        'accueil',
        'creation-site-internet',
        'identite-visuelle',
        'print-signaletique',
        'realisations',
        'a-propos',
        'methode',
        'contact',
    ];
}

function jd_core_utility_page_slugs_2169(){
    return [
        'mentions-legales',
        'politique-de-confidentialite',
        'confidentialite',
    ];
}

function jd_core_page_allowed_for_install_2169($slug){
    if($slug==='home') return true;
    return in_array((string)$slug,array_merge(
        jd_core_main_page_slugs_2169(),
        jd_core_utility_page_slugs_2169()
    ),true);
}

function jd_core_cleanup_target_2169($slug,$title=''){
    $hay=mb_strtolower((string)$slug.' '.(string)$title);

    if(strpos($hay,'avis')!==false) return '/#avis';
    if(strpos($hay,'offre')!==false || strpos($hay,'service')!==false) return '/#offres';

    foreach(['site','web','wordpress'] as $word){
        if(strpos($hay,$word)!==false) return '/creation-site-internet/';
    }
    foreach(['logo','identité','identite','branding','marque','charte'] as $word){
        if(strpos($hay,$word)!==false) return '/identite-visuelle/';
    }
    foreach(['print','impression','signalétique','signaletique','panneau','adhésif','adhesif','marquage','vitrine'] as $word){
        if(strpos($hay,$word)!==false) return '/print-signaletique/';
    }
    foreach(['réalisation','realisation','portfolio','univers','projet'] as $word){
        if(strpos($hay,$word)!==false) return '/realisations/';
    }
    foreach(['propos','qui-suis','jonathan','graphiste'] as $word){
        if(strpos($hay,$word)!==false) return '/a-propos/';
    }
    foreach(['méthode','methode','process','façon-de-travailler','facon-de-travailler'] as $word){
        if(strpos($hay,$word)!==false) return '/methode/';
    }
    foreach(['contact','devis'] as $word){
        if(strpos($hay,$word)!==false) return '/contact/';
    }

    return '/';
}

function jd_core_cleanup_replace_links_2169($redirects){
    if(!$redirects || !is_array($redirects)) return 0;

    $front=(int)get_option('page_on_front');
    $allowed_ids=[$front];
    foreach(array_merge(jd_core_main_page_slugs_2169(),jd_core_utility_page_slugs_2169()) as $slug){
        $p=get_page_by_path($slug);
        if($p instanceof WP_Post) $allowed_ids[]=(int)$p->ID;
    }
    $allowed_ids=array_values(array_unique(array_filter($allowed_ids)));
    $updated=0;

    foreach($allowed_ids as $id){
        $raw=(string)get_post_field('post_content',$id);
        if($raw==='') continue;
        $next=$raw;

        foreach($redirects as $slug=>$target){
            $slug=trim((string)$slug,'/');
            if($slug==='') continue;

            $absolute_old=home_url('/'.$slug.'/');
            $absolute_new=str_starts_with($target,'/#')
                ?home_url('/').substr($target,1)
                :home_url($target);

            $next=str_replace(
                [
                    $absolute_old,
                    '/'.$slug.'/',
                    '/'.$slug,
                ],
                [
                    $absolute_new,
                    $target,
                    rtrim($target,'/'),
                ],
                $next
            );
        }

        if($next!==$raw){
            wp_update_post([
                'ID'=>$id,
                'post_content'=>wp_slash($next),
            ]);
            $updated++;
        }
    }

    return $updated;
}

function jd_core_cleanup_pages_2169(){
    if(get_option('jd_core_page_cleanup_version')===JD_CORE_VERSION) return;

    $front=(int)get_option('page_on_front');
    $privacy=(int)get_option('wp_page_for_privacy_policy');

    $allowed_slugs=array_merge(
        jd_core_main_page_slugs_2169(),
        jd_core_utility_page_slugs_2169()
    );

    $pages=get_posts([
        'post_type'=>'page',
        'post_status'=>['publish','draft','private','pending'],
        'numberposts'=>-1,
        'suppress_filters'=>false,
    ]);

    $removed=[];
    $redirects=(array)get_option('jd_core_deleted_page_redirects_2169',[]);

    foreach($pages as $page){
        $id=(int)$page->ID;
        $slug=(string)$page->post_name;

        if($id===$front || $id===$privacy) continue;
        if(in_array($slug,$allowed_slugs,true)) continue;

        $target=jd_core_cleanup_target_2169($slug,$page->post_title);
        $redirects[$slug]=$target;

        if(function_exists('wp_get_associated_nav_menu_items')){
            foreach((array)wp_get_associated_nav_menu_items($id,'post_type') as $menu_item_id){
                wp_delete_post((int)$menu_item_id,true);
            }
        }

        $removed[]=[
            'id'=>$id,
            'slug'=>$slug,
            'title'=>$page->post_title,
            'redirect'=>$target,
        ];

        wp_delete_post($id,true);
    }

    // Redirections historiques déjà connues, même si les anciennes pages
    // ont été supprimées lors d'une version précédente.
    $redirects=array_merge([
        'creation-site-web-sur-mesure'=>'/creation-site-internet/',
        'qui-suis-je'=>'/a-propos/',
        'services'=>'/#offres',
        'offres'=>'/#offres',
        'avis-clients'=>'/#avis',
        'concept-faites-impression'=>'/print-signaletique/',
        'univers'=>'/realisations/',
        'univers-creatif'=>'/realisations/',
    ],$redirects);

    update_option('jd_core_deleted_page_redirects_2169',$redirects,false);

    $updated_links=jd_core_cleanup_replace_links_2169($redirects);

    update_option('jd_core_page_cleanup_diag_2169',[
        'removed'=>$removed,
        'links_updated'=>$updated_links,
        'checked_at'=>current_time('mysql'),
    ],false);
    update_option('jd_core_page_cleanup_version',JD_CORE_VERSION,false);

    wp_cache_flush();
    if(function_exists('wp_cache_clear_cache')) wp_cache_clear_cache();
}
add_action('admin_init','jd_core_cleanup_pages_2169',80);
add_action('wp_loaded','jd_core_cleanup_pages_2169',30);

/** Redirige les anciennes URL supprimées avant qu'elles ne puissent devenir des 404. */
function jd_core_deleted_page_redirects_2169(){
    if(is_admin()) return;

    $path=(string)parse_url($_SERVER['REQUEST_URI']??'/',PHP_URL_PATH);
    $slug=trim($path,'/');
    if($slug==='' || strpos($slug,'/')!==false) return;

    $map=(array)get_option('jd_core_deleted_page_redirects_2169',[]);
    if(empty($map[$slug])) return;

    $target=(string)$map[$slug];
    if(str_starts_with($target,'/#')){
        $url=home_url('/').substr($target,1);
    }else{
        $url=home_url($target);
    }
    wp_safe_redirect($url,301);
    exit;
}
add_action('template_redirect','jd_core_deleted_page_redirects_2169',0);

/** Retire d'éventuels items de menu persistants qui pointeraient encore vers une page supprimée. */
function jd_core_filter_deleted_menu_links_2169($items){
    $map=(array)get_option('jd_core_deleted_page_redirects_2169',[]);
    if(!$map) return $items;

    foreach((array)$items as $key=>$item){
        $path=(string)parse_url((string)($item->url??''),PHP_URL_PATH);
        $slug=trim($path,'/');
        if($slug!=='' && isset($map[$slug])) unset($items[$key]);
    }
    return array_values($items);
}
add_filter('wp_nav_menu_objects','jd_core_filter_deleted_menu_links_2169',90);
