<?php
if (!defined('ABSPATH')) exit;

/**
 * John Design Core 2.16.9 — le hero d'origine reste intact au-dessus du H1.
 * On remplace seulement le paragraphe situé sous le titre par les 4 métiers.
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

function jd_core_home_hero_keep_original_2169($block_content,$block){
    if(!is_front_page()) return $block_content;
    if(($block['blockName']??'')!=='johndesign/section') return $block_content;
    if(($block['attrs']['sectionId']??'')!=='home-01') return $block_content;

    $services=jd_core_home_hero_services_inline_2169();
    if(!$services) return $block_content;

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
    // Seul le paragraphe descriptif sous le H1 est remplacé par la grille 2×2.
    $replaced=false;
    $block_content=preg_replace_callback(
        '/<p\b[^>]*>.*?<\/p>/is',
        function($m)use($services,&$replaced){
            if($replaced) return $m[0];
            $plain=html_entity_decode(wp_strip_all_tags($m[0]),ENT_QUOTES|ENT_HTML5,'UTF-8');
            $plain=mb_strtolower(preg_replace('/\s+/u',' ',trim($plain)));
            if(
                strpos($plain,'graphiste à aix-en-provence')!==false
                && strpos($plain,'john design')!==false
            ){
                $replaced=true;
                return $services;
            }
            return $m[0];
        },
        $block_content
    );

    // Fallback : si le paragraphe a été édité, place la grille juste après le H1
    // sans reconstruire le reste du hero.
    if(!$replaced && stripos($block_content,'jd-home-hero-services-inline')===false){
        $block_content=preg_replace(
            '/<\/h1>/i',
            '</h1>'.$services,
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
