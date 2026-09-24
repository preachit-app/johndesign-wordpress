<?php
if (!defined('ABSPATH')) exit;

/**
 * John Design Core 2.16.3 — petites galeries visuelles par service.
 * Elles réutilisent les images déjà présentes sur chaque page, sans légendes
 * ni textes sur les cartes.
 */

function jd_core_service_gallery_plain_2163($value){
    $value=(string)$value;
    $value=str_replace(['<br>','<br/>','<br />'],' ',$value);
    $value=wp_strip_all_tags($value);
    $value=html_entity_decode($value,ENT_QUOTES|ENT_HTML5,'UTF-8');
    $value=preg_replace('/\s+/u',' ',trim($value));
    return mb_strtolower($value);
}

function jd_core_service_gallery_block_text_2163($block){
    $parts=[];
    $fields=$block['attrs']['fields']??[];
    if(is_array($fields)){
        foreach($fields as $value){
            if(is_string($value)) $parts[]=$value;
        }
    }
    $sid=$block['attrs']['sectionId']??'';
    $defs=jd_core_sections();
    $def=$defs[$sid]??null;
    if($def && !empty($def['template'])) $parts[]=$def['template'];
    return jd_core_service_gallery_plain_2163(implode(' ',$parts));
}

function jd_core_service_gallery_images_2163($page_key){
    $page=get_page_by_path($page_key);
    if(!($page instanceof WP_Post)) return [];

    $blocks=parse_blocks((string)$page->post_content);
    $defs=jd_core_sections();
    $images=[];

    if($page_key==='print-signaletique'){
        $images[]=JD_CORE_URL.'assets/portfolio/eco-clim-truck.webp';
    }

    foreach($blocks as $block){
        if(($block['blockName']??'')!=='johndesign/section') continue;

        $plain=jd_core_service_gallery_block_text_2163($block);

        // Ne pas reprendre les blocs utilitaires ni le faux visuel IA de Print.
        if(
            (strpos($plain,'vos questions')!==false && strpos($plain,'sans détour')!==false)
            || strpos($plain,'une idée en tête')!==false
            || strpos($plain,'et pour la suite')!==false
            || strpos($plain,'studio en ligne')!==false
            || strpos($plain,'explorer la composition')!==false
            || ($page_key==='print-signaletique' && strpos($plain,'ia fait des merveilles')!==false)
        ) continue;

        $sid=$block['attrs']['sectionId']??'';
        $def=$defs[$sid]??null;
        if(!$def || empty($def['fields'])) continue;
        $values=is_array($block['attrs']['fields']??null)?$block['attrs']['fields']:[];

        foreach($def['fields'] as $field){
            if(($field['type']??'')!=='image') continue;
            $key=$field['key']??'';
            if(!$key) continue;

            $value=array_key_exists($key,$values)?$values[$key]:($field['default']??'');
            if(!is_string($value) || trim($value)==='') continue;

            $value=str_replace(
                ['{{SITE_URL}}','{{THEME_URI}}'],
                [rtrim(home_url(),'/'),rtrim(get_stylesheet_directory_uri(),'/')],
                $value
            );
            $value=esc_url_raw($value);
            if(!$value) continue;

            $images[]=$value;
        }
    }

    $unique=[];
    foreach($images as $image){
        if(!in_array($image,$unique,true)) $unique[]=$image;
    }

    return array_slice($unique,0,8);
}

function jd_core_service_gallery_html_2163($page_key){
    $images=jd_core_service_gallery_images_2163($page_key);
    if(count($images)<2) return '';

    $titles=[
        'identite-visuelle'=>[
            'eyebrow'=>'IDENTITÉ VISUELLE',
            'title'=>'Quelques visuels d’identité.'
        ],
        'print-signaletique'=>[
            'eyebrow'=>'PRINT & SIGNALÉTIQUE',
            'title'=>'Quelques réalisations en images.'
        ],
    ];
    if(empty($titles[$page_key])) return '';

    $cards='';
    foreach($images as $image){
        $cards.='<div class="jd-service-gallery-item">'
            .'<img src="'.esc_url($image).'" alt="" loading="lazy" decoding="async">'
        .'</div>';
    }

    $data=$titles[$page_key];

    return '<section class="jd-service-gallery" data-jd-horizontal-gallery>'
        .'<div class="jd-service-gallery-head">'
            .'<div>'
                .'<p class="jd-eyebrow">'.esc_html($data['eyebrow']).'</p>'
                .'<h2>'.esc_html($data['title']).'</h2>'
            .'</div>'
            .'<div class="jd-horizontal-gallery-controls" aria-label="Navigation de la galerie">'
                .'<button type="button" class="jd-horizontal-gallery-nav" data-jd-gallery-prev aria-label="Visuel précédent">←</button>'
                .'<button type="button" class="jd-horizontal-gallery-nav" data-jd-gallery-next aria-label="Visuel suivant">→</button>'
            .'</div>'
        .'</div>'
        .'<div class="jd-service-gallery-track" data-jd-gallery-track>'
            .$cards
        .'</div>'
    .'</section>';
}

function jd_core_service_gallery_render_2163($block_content,$block){
    if(($block['blockName']??'')!=='johndesign/section') return $block_content;

    $page_key='';
    if(is_page('identite-visuelle')) $page_key='identite-visuelle';
    elseif(is_page('print-signaletique')) $page_key='print-signaletique';
    else return $block_content;

    static $inserted=[];
    if(!empty($inserted[$page_key])) return $block_content;

    $plain=jd_core_service_gallery_plain_2163($block_content);
    $is_faq=strpos($plain,'vos questions')!==false && strpos($plain,'sans détour')!==false;
    $is_cta=strpos($plain,'une idée en tête')!==false;

    if(!$is_faq && !$is_cta) return $block_content;

    $gallery=jd_core_service_gallery_html_2163($page_key);
    if(!$gallery) return $block_content;

    $inserted[$page_key]=true;
    return $gallery.$block_content;
}
add_filter('render_block','jd_core_service_gallery_render_2163',75,2);
