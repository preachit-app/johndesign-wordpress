<?php
if (!defined('ABSPATH')) exit;

/**
 * John Design Core 2.16.7 — section preuve terrain de la page Print.
 * Le bloc est reconstruit entièrement au rendu WordPress pour ne dépendre
 * ni du markup historique de la section, ni de JavaScript.
 */

function jd_core_print_proof_html_2167(){
    $truck=jd_core_get_truck_image_src();
    if(!$truck) return '';

    return '<section class="jd-print-proof-v2" aria-label="Accompagnement print et signalétique">'
        .'<div class="jd-print-proof-v2__inner">'
            .'<div class="jd-print-proof-v2__copy">'
                .'<p class="jd-print-proof-v2__eyebrow">DU DESIGN, ET QUELQU’UN AVEC VOUS</p>'
                .'<h2>L’IA fait des merveilles.<br>Mais elle ne pose pas<br>vos stickers.</h2>'
                .'<p class="jd-print-proof-v2__text">Les idées prennent vie sur un écran, mais aussi sur votre vitrine, vos panneaux et les murs de votre commerce. Et là, vous pouvez compter sur moi : on en discute, on regarde ensemble, et je vous accompagne jusqu’à la pose.</p>'
                .'<a class="jd-print-proof-v2__cta" href="'.esc_url(home_url('/contact/')).'">Parlons de votre projet <span aria-hidden="true">↗</span></a>'
            .'</div>'
            .'<figure class="jd-print-proof-v2__visual">'
                .'<img src="'.esc_url($truck).'" alt="Habillage adhésif Eco Clim System sur véhicule utilitaire" loading="lazy" decoding="async">'
            .'</figure>'
        .'</div>'
    .'</section>';
}

function jd_core_print_proof_render_2167($block_content,$block){
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

    $html=jd_core_print_proof_html_2167();
    return $html!==''?$html:$block_content;
}
add_filter('render_block','jd_core_print_proof_render_2167',95,2);
