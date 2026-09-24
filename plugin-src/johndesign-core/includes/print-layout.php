<?php
if (!defined('ABSPATH')) exit;

/**
 * John Design Core 2.16.11 — preuve terrain Print avec galerie intégrée.
 * Une seule photo est visible à la fois dans le bloc de droite.
 */

function jd_core_print_proof_gallery_items_21611(){
    return [
        'Habillage adhésif Eco Clim System — vue trois quarts arrière',
        'Marquage véhicule Cap Multiclôture',
        'Signalétique grand format Golf Sainte Baume',
        'Autocollant Artigues sur véhicule',
        'Habillage adhésif Eco Clim System — vue arrière',
    ];
}

function jd_core_print_proof_html_21611(){
    $sprite=JD_CORE_URL.'assets/portfolio/signage-gallery-sprite.webp';
    $labels=jd_core_print_proof_gallery_items_21611();

    $dots='';
    foreach($labels as $i=>$label){
        $dots.='<button type="button" class="jd-print-proof-slider__dot'.($i===0?' is-active':'').'" data-jd-print-dot="'.$i.'" aria-label="Afficher la réalisation '.($i+1).'"'.($i===0?' aria-current="true"':'').'></button>';
    }

    return '<section class="jd-print-proof-v2" aria-label="Accompagnement print et signalétique">'
        .'<div class="jd-print-proof-v2__inner">'
            .'<div class="jd-print-proof-v2__copy">'
                .'<p class="jd-print-proof-v2__eyebrow">DU DESIGN, ET QUELQU’UN AVEC VOUS</p>'
                .'<h2>L’IA fait des merveilles.<br>Mais elle ne pose pas<br>vos stickers.</h2>'
                .'<p class="jd-print-proof-v2__text">Les idées prennent vie sur un écran, mais aussi sur votre vitrine, vos panneaux et les murs de votre commerce. Et là, vous pouvez compter sur moi : on en discute, on regarde ensemble, et je vous accompagne jusqu’à la pose.</p>'
                .'<a class="jd-print-proof-v2__cta" href="'.esc_url(home_url('/contact/')).'">Parlons de votre projet <span aria-hidden="true">↗</span></a>'
            .'</div>'
            .'<div class="jd-print-proof-slider" data-jd-print-slider data-count="'.count($labels).'" data-labels="'.esc_attr(wp_json_encode($labels,JSON_UNESCAPED_UNICODE)).'">'
                .'<div class="jd-print-proof-slider__frame" role="img" aria-label="'.esc_attr($labels[0]).'" style="background-image:url('.esc_url($sprite).');background-position:center 0%;"></div>'
                .'<div class="jd-print-proof-slider__ui">'
                    .'<div class="jd-print-proof-slider__dots">'.$dots.'</div>'
                    .'<div class="jd-print-proof-slider__arrows">'
                        .'<button type="button" class="jd-print-proof-slider__arrow" data-jd-print-prev aria-label="Photo précédente">←</button>'
                        .'<button type="button" class="jd-print-proof-slider__arrow" data-jd-print-next aria-label="Photo suivante">→</button>'
                    .'</div>'
                .'</div>'
            .'</div>'
        .'</div>'
    .'</section>';
}

function jd_core_print_proof_render_21611($block_content,$block){
    if(!is_page('print-signaletique')) return $block_content;
    if(($block['blockName']??'')!=='johndesign/section') return $block_content;

    $plain=html_entity_decode(
        wp_strip_all_tags((string)$block_content),
        ENT_QUOTES|ENT_HTML5,
        'UTF-8'
    );
    $plain=mb_strtolower(preg_replace('/\s+/u',' ',trim($plain)));

    if(
        strpos($plain,'ia fait des merveilles')===false
        || strpos($plain,'stickers')===false
    ){
        return $block_content;
    }

    return jd_core_print_proof_html_21611();
}
add_filter('render_block','jd_core_print_proof_render_21611',95,2);
