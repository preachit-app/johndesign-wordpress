<?php
if (!defined('ABSPATH')) exit;

/**
 * John Design Core 2.16.18 — accueil orienté conversion.
 * Le haut du hero, le H1 et l'astérisque d'origine restent intacts.
 */

function jd_core_home_hero_services_inline_2169(){
    $snapshot=jd_core_home_services_snapshot_2151();
    if(preg_match('/<div class="jd-home-services-grid">(.*?)<\/div>\s*<\/section>/is',$snapshot,$m)){
        return '<div class="jd-home-hero-services-inline" aria-label="Les services John Design">'
            .'<div class="jd-home-services-grid">'.$m[1].'</div>'
        .'</div>';
    }
    return '';
}

function jd_core_home_hero_conversion_21618(){
    return '<div class="jd-home-hero-conversion">'
        .'<p class="jd-home-hero-promise">Sites internet, identité et supports qui donnent envie de vous choisir.</p>'
        .'<a class="jd-home-hero-rating" href="#avis" aria-label="Voir les avis clients John Design">'
            .'<span class="jd-home-hero-stars" aria-hidden="true">★★★★★</span>'
            .'<strong>5,0 Google</strong>'
            .'<span>16 avis clients</span>'
        .'</a>'
    .'</div>';
}

function jd_core_home_hero_keep_original_2169($block_content,$block){
    if(!is_front_page()) return $block_content;
    if(($block['blockName']??'')!=='johndesign/section') return $block_content;
    if(($block['attrs']['sectionId']??'')!=='home-01') return $block_content;

    $services=jd_core_home_hero_services_inline_2169();
    if(!$services) return $block_content;

    $conversion=jd_core_home_hero_conversion_21618();
    $under_title=$conversion.$services;

    // Ajoute une classe uniquement pour la respiration et le responsive.
    if(stripos($block_content,'jd-home-hero-original')===false){
        if(preg_match('/<section\b[^>]*\bclass=(["\'])(.*?)\1/i',$block_content)){
            $block_content=preg_replace_callback(
                '/<section\b([^>]*?)\bclass=(["\'])(.*?)\2([^>]*)>/i',
                function($m){
                    return '<section'.$m[1].'class='.$m[2].trim($m[3].' jd-home-hero-original').$m[2].$m[4].'>';
                },
                $block_content,
                1
            );
        }else{
            $block_content=preg_replace('/<section\b/i','<section class="jd-home-hero-original"',$block_content,1);
        }
    }

    // Le haut du hero, le H1, l'astérisque et les actions restent ceux du bloc d'origine.
    // Le paragraphe descriptif sous le H1 devient la promesse + la preuve + les 4 métiers.
    $replaced=false;
    $block_content=preg_replace_callback(
        '/<p\b[^>]*>.*?<\/p>/is',
        function($m)use($under_title,&$replaced){
            if($replaced) return $m[0];
            $plain=html_entity_decode(wp_strip_all_tags($m[0]),ENT_QUOTES|ENT_HTML5,'UTF-8');
            $plain=mb_strtolower(preg_replace('/\s+/u',' ',trim($plain)));
            if(
                strpos($plain,'graphiste à aix-en-provence')!==false
                && strpos($plain,'john design')!==false
            ){
                $replaced=true;
                return $under_title;
            }
            return $m[0];
        },
        $block_content
    );

    // Fallback si le texte source du paragraphe a été modifié.
    if(!$replaced && stripos($block_content,'jd-home-hero-conversion')===false){
        $block_content=preg_replace(
            '/<\/h1>/i',
            '</h1>'.$under_title,
            $block_content,
            1
        );
    }

    return $block_content;
}
add_filter('render_block','jd_core_home_hero_keep_original_2169',100,2);

/* Ancre stable pour "Explorer les offres". */
function jd_core_home_offers_anchor_2169($block_content,$block){
    if(!is_front_page()) return $block_content;
    if(($block['blockName']??'')!=='johndesign/section') return $block_content;
    if(($block['attrs']['sectionId']??'')!=='home-02') return $block_content;
    if(stripos($block_content,'id="offres"')!==false) return $block_content;
    return preg_replace('/<section\b/i','<section id="offres"',$block_content,1);
}
add_filter('render_block','jd_core_home_offers_anchor_2169',100,2);

function jd_core_home_role_attr_21618($html,$role){
    if(stripos($html,'data-jd-home-role=')!==false) return $html;
    return preg_replace(
        '/<section\b/i',
        '<section data-jd-home-role="'.esc_attr($role).'"',
        $html,
        1
    );
}

function jd_core_home_why_html_21618(){
    return '<section class="jd-home-why" data-jd-home-role="why" aria-label="Pourquoi choisir John Design">'
        .'<div class="jd-home-why__inner">'
            .'<p class="jd-home-why__eyebrow">03 / POURQUOI JOHN DESIGN ?</p>'
            .'<div class="jd-home-why__head">'
                .'<h2>Un seul interlocuteur.<br>Du premier échange à la mise en ligne ou à la pose.</h2>'
                .'<p>Vous échangez directement avec la personne qui conçoit votre projet. Moins d’intermédiaires, plus de cohérence.</p>'
            .'</div>'
            .'<div class="jd-home-why__points">'
                .'<div><span>01</span><strong>Direct.</strong><p>Vous parlez au créatif qui travaille réellement sur votre projet.</p></div>'
                .'<div><span>02</span><strong>Cohérent.</strong><p>Web, identité, print et signalétique avancent dans la même direction.</p></div>'
                .'<div><span>03</span><strong>Jusqu’au bout.</strong><p>Mise en ligne, fichiers prêts à utiliser ou accompagnement jusqu’à la pose.</p></div>'
            .'</div>'
        .'</div>'
    .'</section>';
}

/**
 * Identifie les sections existantes de l'accueil, renumérote le parcours
 * et injecte le bloc "Pourquoi John Design ?" sans toucher au contenu éditable.
 */
function jd_core_home_conversion_flow_21618($block_content,$block){
    if(!is_front_page()) return $block_content;
    if(($block['blockName']??'')!=='johndesign/section') return $block_content;

    $plain=html_entity_decode(
        wp_strip_all_tags((string)$block_content),
        ENT_QUOTES|ENT_HTML5,
        'UTF-8'
    );
    $plain=mb_strtolower(preg_replace('/\s+/u',' ',trim($plain)));

    $section_id=(string)($block['attrs']['sectionId']??'');
    $role='';

    if($section_id==='home-01'){
        $role='hero';
    }elseif(
        strpos($plain,'ce que je fais')!==false
        && strpos($plain,'site internet')!==false
        && strpos($plain,'signalétique')!==false
    ){
        $role='services';
    }elseif(strpos($plain,'découvrez mes réalisations')!==false){
        $role='work';
    }elseif(
        strpos($plain,'ia fait des merveilles')!==false
        && strpos($plain,'stickers')!==false
    ){
        $role='proof';
        $block_content=str_ireplace(
            'DU DESIGN, ET QUELQU’UN AVEC VOUS',
            '04 / DU DESIGN, ET QUELQU’UN AVEC VOUS',
            $block_content
        );
    }elseif(
        strpos($plain,'ce sont eux qui le disent')!==false
        && strpos($plain,'avis google')!==false
    ){
        $role='reviews';
        $block_content=str_ireplace(
            '06 / CE SONT EUX QUI LE DISENT',
            '05 / CE SONT EUX QUI LE DISENT',
            $block_content
        );
    }elseif(
        strpos($plain,'john design')!==false
        && strpos($plain,'c’est jonathan')!==false
    ){
        $role='about';
        $block_content=str_ireplace(
            '05 / UN GRAPHISTE, UN INTERLOCUTEUR',
            '06 / UN GRAPHISTE, UN INTERLOCUTEUR',
            $block_content
        );
    }elseif(
        strpos($plain,'du premier jet')!==false
        && strpos($plain,'on clarifie')!==false
        && strpos($plain,'je crée')!==false
    ){
        $role='method';
        $block_content=str_ireplace(
            '04 / COMMENT ON AVANCE',
            '07 / COMMENT ON AVANCE',
            $block_content
        );
    }elseif(
        strpos($plain,'les questions que vous vous posez')!==false
        && strpos($plain,'quel budget prévoir')!==false
    ){
        $role='faq';
    }elseif(
        strpos($plain,'on fait équipe')!==false
        && strpos($plain,'parlez-moi')!==false
        && strpos($plain,'votre projet')!==false
    ){
        $role='contact';
        $block_content=str_ireplace(
            '07 / ON FAIT ÉQUIPE ?',
            '08 / ON FAIT ÉQUIPE ?',
            $block_content
        );
    }

    if(!$role) return $block_content;

    $block_content=jd_core_home_role_attr_21618($block_content,$role);

    if($role==='proof'){
        return jd_core_home_why_html_21618().$block_content;
    }

    return $block_content;
}
add_filter('render_block','jd_core_home_conversion_flow_21618',120,2);
