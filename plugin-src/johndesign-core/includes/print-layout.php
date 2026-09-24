<?php
if (!defined('ABSPATH')) exit;

/**
 * John Design Core 2.16.12 — galerie signalétique fiable.
 * Chaque réalisation est un vrai fichier image indépendant.
 */

function jd_core_print_proof_gallery_items_21612(){
    return [
        [
            'file'  => 'signage-01-eco-clim-3q.webp',
            'label' => 'Habillage adhésif Eco Clim System — vue trois quarts arrière',
        ],
        [
            'file'  => 'signage-05-cap-multicloture.webp',
            'label' => 'Marquage véhicule Cap Multiclôture',
        ],
        [
            'file'  => 'signage-03-golf.webp',
            'label' => 'Signalétique grand format Golf Sainte Baume',
        ],
        [
            'file'  => 'signage-04-artigues.webp',
            'label' => 'Autocollant Artigues sur véhicule',
        ],
        [
            'file'  => 'signage-02-eco-clim-back.webp',
            'label' => 'Habillage adhésif Eco Clim System — vue arrière',
        ],
    ];
}

function jd_core_print_proof_html_21612(){
    $items=jd_core_print_proof_gallery_items_21612();
    $slides='';
    $dots='';

    foreach($items as $i=>$item){
        $src=JD_CORE_URL.'assets/portfolio/'.$item['file'];
        $active=$i===0?' is-active':'';
        $loading=$i===0?'eager':'lazy';
        $hidden=$i===0?'false':'true';

        $slides.='<figure class="jd-print-proof-slider__slide'.$active.'" data-jd-print-slide="'.$i.'" aria-hidden="'.$hidden.'">'
            .'<img src="'.esc_url($src).'" alt="'.esc_attr($item['label']).'" width="760" height="760" loading="'.$loading.'" decoding="async">'
        .'</figure>';

        $dots.='<button type="button" class="jd-print-proof-slider__dot'.$active.'" data-jd-print-dot="'.$i.'" aria-label="Afficher la réalisation '.($i+1).'"'.($i===0?' aria-current="true"':'').'></button>';
    }

    return '<section class="jd-print-proof-v2" aria-label="Accompagnement print et signalétique">'
        .'<div class="jd-print-proof-v2__inner">'
            .'<div class="jd-print-proof-v2__copy">'
                .'<p class="jd-print-proof-v2__eyebrow">DU DESIGN, ET QUELQU’UN AVEC VOUS</p>'
                .'<h2>L’IA fait des merveilles.<br>Mais elle ne pose pas<br>vos stickers.</h2>'
                .'<p class="jd-print-proof-v2__text">Les idées prennent vie sur un écran, mais aussi sur votre vitrine, vos panneaux et les murs de votre commerce. Et là, vous pouvez compter sur moi : on en discute, on regarde ensemble, et je vous accompagne jusqu’à la pose.</p>'
                .'<a class="jd-print-proof-v2__cta" href="'.esc_url(home_url('/contact/')).'">Parlons de votre projet <span aria-hidden="true">↗</span></a>'
            .'</div>'
            .'<div class="jd-print-proof-slider" data-jd-print-slider>'
                .'<div class="jd-print-proof-slider__slides">'.$slides.'</div>'
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

function jd_core_print_proof_render_21612($block_content,$block){
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

    return jd_core_print_proof_html_21612();
}
add_filter('render_block','jd_core_print_proof_render_21612',95,2);
