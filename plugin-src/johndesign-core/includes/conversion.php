<?php
if (!defined('ABSPATH')) exit;

/**
 * John Design Core 2.16.19 — dernière couche de conversion.
 * - preuve et CTA haut de page sur les 3 services
 * - mini galerie de réalisations immédiatement après le hero
 * - CTA préqualifié vers le formulaire
 */

function jd_core_conversion_page_key_21619(){
    if(is_page('creation-site-internet')) return 'creation-site-internet';
    if(is_page('identite-visuelle')) return 'identite-visuelle';
    if(is_page('print-signaletique')) return 'print-signaletique';
    return '';
}

function jd_core_conversion_config_21619($page_key){
    $config=[
        'creation-site-internet'=>[
            'promise'=>'Un site professionnel qui présente clairement votre activité, rassure vos visiteurs et facilite les demandes de contact.',
            'project'=>'Création de site internet',
            'gallery_title'=>'Quelques sites réalisés.',
            'gallery_cta'=>'Voir les réalisations',
        ],
        'identite-visuelle'=>[
            'promise'=>'Une identité reconnaissable et cohérente, pensée pour inspirer confiance et fonctionner sur tous vos supports.',
            'project'=>'Logo / identité visuelle',
            'gallery_title'=>'Quelques identités en images.',
            'gallery_cta'=>'Voir les réalisations',
        ],
        'print-signaletique'=>[
            'promise'=>'Des supports imprimés et une signalétique qui rendent votre activité visible, du fichier jusqu’à la fabrication et à la pose.',
            'project'=>'Signalétique / marquage',
            'gallery_title'=>'Quelques réalisations terrain.',
            'gallery_cta'=>'Voir les réalisations',
        ],
    ];
    return $config[$page_key]??[];
}

function jd_core_conversion_arrow_21619(){
    return '<span class="jd-cta-arrow" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false"><path d="M7 17L17 7"></path><path d="M9 7H17V15"></path></svg></span>';
}

function jd_core_conversion_service_images_21619($page_key){
    $items=[];

    if($page_key==='creation-site-internet' && function_exists('jd_core_web_portfolio_items_2163')){
        foreach(array_slice(jd_core_web_portfolio_items_2163(),0,3) as $item){
            $items[]=[
                'src'=>jd_core_web_portfolio_shot_2163($item),
                'href'=>(string)($item['url']??''),
                'alt'=>'Aperçu du site '.(string)($item['name']??''),
            ];
        }
        return $items;
    }

    if(function_exists('jd_core_service_gallery_images_2163')){
        $images=array_slice(jd_core_service_gallery_images_2163($page_key),0,3);
        foreach($images as $image){
            $items[]=[
                'src'=>(string)$image,
                'href'=>home_url('/realisations/'),
                'alt'=>'Réalisation John Design',
            ];
        }
    }

    return $items;
}

function jd_core_conversion_service_panel_21619($page_key){
    $config=jd_core_conversion_config_21619($page_key);
    if(!$config) return '';

    $contact=add_query_arg('project',$config['project'],home_url('/contact/'));
    $rating='<a class="jd-service-conversion__rating" href="'.esc_url(home_url('/#avis')).'">'
        .'<span aria-hidden="true">★★★★★</span><strong>5,0 Google</strong><em>16 avis clients</em>'
    .'</a>';

    $cards='';
    foreach(jd_core_conversion_service_images_21619($page_key) as $i=>$item){
        $target=$page_key==='creation-site-internet'?' target="_blank" rel="noopener noreferrer"':'';
        $cards.='<a class="jd-service-conversion__work" href="'.esc_url($item['href']).'"'.$target.'>'
            .'<img src="'.esc_url($item['src']).'" alt="'.esc_attr($item['alt']).'" loading="'.($i===0?'eager':'lazy').'" decoding="async">'
        .'</a>';
    }

    $gallery='';
    if($cards!==''){
        $gallery='<div class="jd-service-conversion__gallery-head">'
            .'<h2>'.esc_html($config['gallery_title']).'</h2>'
            .'<a href="'.esc_url(home_url('/realisations/')).'">'.esc_html($config['gallery_cta']).' '.jd_core_conversion_arrow_21619().'</a>'
        .'</div>'
        .'<div class="jd-service-conversion__gallery">'.$cards.'</div>';
    }

    return '<section class="jd-service-conversion" aria-label="Pourquoi choisir John Design pour ce projet">'
        .'<div class="jd-service-conversion__inner">'
            .'<div class="jd-service-conversion__top">'
                .'<p class="jd-service-conversion__promise">'.esc_html($config['promise']).'</p>'
                .'<div class="jd-service-conversion__actions">'
                    .$rating
                    .'<a class="jd-service-conversion__cta" href="'.esc_url($contact).'">Demander une estimation '.jd_core_conversion_arrow_21619().'</a>'
                    .'<span class="jd-service-conversion__reassure">Premier échange sans engagement.</span>'
                .'</div>'
            .'</div>'
            .$gallery
        .'</div>'
    .'</section>';
}

function jd_core_conversion_service_render_21619($block_content,$block){
    $page_key=jd_core_conversion_page_key_21619();
    if(!$page_key) return $block_content;
    if(($block['blockName']??'')!=='johndesign/section') return $block_content;

    static $inserted=[];
    if(!empty($inserted[$page_key])) return $block_content;

    if(stripos($block_content,'<h1')===false) return $block_content;

    $inserted[$page_key]=true;

    if(stripos($block_content,'jd-service-hero-conversion')===false){
        $block_content=jd_core_web_layout_add_class_2163($block_content,'jd-service-hero-conversion');
    }

    return $block_content.jd_core_conversion_service_panel_21619($page_key);
}
add_filter('render_block','jd_core_conversion_service_render_21619',145,2);
