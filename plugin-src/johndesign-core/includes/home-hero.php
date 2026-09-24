<?php
if (!defined('ABSPATH')) exit;

/**
 * John Design Core 2.16.8 — hero d'accueil calqué sur la maquette fournie.
 * Le bloc historique home-01 est remplacé entièrement au rendu.
 */
function jd_core_home_hero_service_2168($label,$href,$type){
    $icons=[
        'web'=>'<svg viewBox="0 0 32 32" aria-hidden="true"><rect x="4" y="6" width="24" height="20" rx="3"></rect><path d="M4 11h24M10 8.5h.1M14 8.5h.1"></path></svg>',
        'id'=>'<svg viewBox="0 0 32 32" aria-hidden="true"><circle cx="16" cy="16" r="10"></circle><path d="M16 6v20M6 16h20M9 9l14 14M23 9L9 23"></path></svg>',
        'print'=>'<svg viewBox="0 0 32 32" aria-hidden="true"><path d="M8 12V5h16v7M8 23H5V12h22v11h-3"></path><rect x="8" y="19" width="16" height="8" rx="1"></rect></svg>',
        'sign'=>'<svg viewBox="0 0 32 32" aria-hidden="true"><path d="M7 7h18v12H7zM16 19v8M11 27h10"></path><path d="M10 11h12M10 15h8"></path></svg>',
    ];
    $icon=$icons[$type]??'';

    return '<a class="jd-home-hero-v2__service is-'.$type.'" href="'.esc_url($href).'">'
        .'<span class="jd-home-hero-v2__service-icon">'.$icon.'</span>'
        .'<span>'.esc_html($label).'</span>'
    .'</a>';
}

function jd_core_home_hero_html_2168(){
    $services=
        jd_core_home_hero_service_2168('Sites internet',home_url('/creation-site-internet/'),'web')
        .jd_core_home_hero_service_2168('Logo & identité',home_url('/identite-visuelle/'),'id')
        .jd_core_home_hero_service_2168('Supports imprimés',home_url('/print-signaletique/'),'print')
        .jd_core_home_hero_service_2168('Signalétique',home_url('/print-signaletique/'),'sign');

    return '<section class="jd-home-hero-v2">'
        .'<div class="jd-home-hero-v2__grid">'
            .'<div class="jd-home-hero-v2__left">'
                .'<h1>'
                    .'<span>Créons ensemble</span>'
                    .'<span>quelque chose</span>'
                    .'<span class="is-highlight">qui vous ressemble</span>'
                .'</h1>'
                .'<div class="jd-home-hero-v2__services" aria-label="Les services John Design">'
                    .$services
                .'</div>'
            .'</div>'
            .'<div class="jd-home-hero-v2__right">'
                .'<div class="jd-home-hero-v2__star" aria-hidden="true">'
                    .'<svg viewBox="0 0 140 140"><path d="M70 5v130M5 70h130M23 23l94 94M117 23l-94 94M54 8l32 124M8 54l124 32"></path></svg>'
                .'</div>'
                .'<div class="jd-home-hero-v2__actions">'
                    .'<a class="jd-home-hero-v2__cta" href="'.esc_url(home_url('/contact/')).'">Parlons de votre projet <span aria-hidden="true">↗</span></a>'
                    .'<a class="jd-home-hero-v2__explore" href="#offres">Explorer les offres <span aria-hidden="true">↓</span></a>'
                .'</div>'
            .'</div>'
        .'</div>'
    .'</section>';
}

function jd_core_home_hero_render_2168($block_content,$block){
    if(!is_front_page()) return $block_content;
    if(($block['blockName']??'')!=='johndesign/section') return $block_content;
    if(($block['attrs']['sectionId']??'')!=='home-01') return $block_content;

    return jd_core_home_hero_html_2168();
}
add_filter('render_block','jd_core_home_hero_render_2168',100,2);

/* Donne une ancre stable au bloc offres qui suit le hero. */
function jd_core_home_offers_anchor_2168($block_content,$block){
    if(!is_front_page()) return $block_content;
    if(($block['blockName']??'')!=='johndesign/section') return $block_content;
    if(($block['attrs']['sectionId']??'')!=='home-02') return $block_content;
    if(stripos($block_content,'id="offres"')!==false) return $block_content;
    return preg_replace('/<section\b/i','<section id="offres"',$block_content,1);
}
add_filter('render_block','jd_core_home_offers_anchor_2168',100,2);
