<?php
if (!defined('ABSPATH')) exit;

/**
 * John Design Core 2.16.20 — conversion sans galerie redondante.
 * - preuve et CTA haut de page sur les 3 services
 * - les réalisations restent dans la galerie dédiée plus bas dans chaque page
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
        ],
        'identite-visuelle'=>[
            'promise'=>'Une identité reconnaissable et cohérente, pensée pour inspirer confiance et fonctionner sur tous vos supports.',
            'project'=>'Logo / identité visuelle',
        ],
        'print-signaletique'=>[
            'promise'=>'Des supports imprimés et une signalétique qui rendent votre activité visible, du fichier jusqu’à la fabrication et à la pose.',
            'project'=>'Signalétique / marquage',
        ],
    ];
    return $config[$page_key]??[];
}

function jd_core_conversion_arrow_21619(){
    return '<span class="jd-cta-arrow" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false"><path d="M7 17L17 7"></path><path d="M9 7H17V15"></path></svg></span>';
}

function jd_core_conversion_service_panel_21619($page_key){
    $config=jd_core_conversion_config_21619($page_key);
    if(!$config) return '';

    $contact=add_query_arg('project',$config['project'],home_url('/contact/'));
    $rating='<a class="jd-service-conversion__rating" href="'.esc_url(home_url('/#avis')).'">'
        .'<span aria-hidden="true">★★★★★</span><strong>5,0 Google</strong><em>16 avis clients</em>'
    .'</a>';

    return '<section class="jd-service-conversion jd-service-conversion--compact" aria-label="Pourquoi choisir John Design pour ce projet">'
        .'<div class="jd-service-conversion__inner">'
            .'<div class="jd-service-conversion__top">'
                .'<p class="jd-service-conversion__promise">'.esc_html($config['promise']).'</p>'
                .'<div class="jd-service-conversion__actions">'
                    .$rating
                    .'<a class="jd-service-conversion__cta" href="'.esc_url($contact).'">Demander une estimation '.jd_core_conversion_arrow_21619().'</a>'
                    .'<span class="jd-service-conversion__reassure">Premier échange sans engagement.</span>'
                .'</div>'
            .'</div>'
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
