<?php
if (!defined('ABSPATH')) exit;

/**
 * John Design Core 2.16.2 — parcours Site internet + galerie web.
 *
 * Ordre visuel :
 * 1. Web / WordPress
 * 2. Pourquoi le faire
 * 3. Tout ce qu'il faut pour votre site professionnel
 * 4. Pour bien commencer
 * 5. Maintenance & suivi
 * 6. Quelques sites réalisés
 * 7. Vos questions sans détour
 * 8. Une idée en tête
 *
 * Les blocs "Studio en ligne" et "Et pour la suite / Une image cohérente"
 * restent supprimés.
 */

function jd_core_web_layout_plain_2162($value){
    $value=(string)$value;
    $value=str_replace(['<br>','<br/>','<br />'],' ',$value);
    $value=wp_strip_all_tags($value);
    $value=html_entity_decode($value,ENT_QUOTES|ENT_HTML5,'UTF-8');
    $value=preg_replace('/\s+/u',' ',trim($value));
    return mb_strtolower($value);
}

function jd_core_web_layout_block_text_2162($block){
    if(($block['blockName']??'')!=='johndesign/section') return '';

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

    return jd_core_web_layout_plain_2162(implode(' ',$parts));
}

function jd_core_web_layout_type_2162($plain){
    if(!$plain) return '';

    if(
        strpos($plain,'studio en ligne')!==false
        || (
            strpos($plain,'une idée du style')!==false
            && strpos($plain,'explorer la composition')!==false
        )
    ) return 'remove-studio';

    if(
        strpos($plain,'et pour la suite')!==false
        && (
            strpos($plain,'une image cohérente')!==false
            || (
                strpos($plain,'identité visuelle')!==false
                && strpos($plain,'print')!==false
            )
        )
    ) return 'remove-related';

    if(strpos($plain,'pourquoi le faire')!==false) return 'why';

    if(
        strpos($plain,'ce que l’on peut construire')!==false
        || strpos($plain,"ce que l'on peut construire")!==false
        || strpos($plain,'tout ce qu’il faut pour votre site professionnel')!==false
        || strpos($plain,"tout ce qu'il faut pour votre site professionnel")!==false
    ) return 'scope';

    if(strpos($plain,'pour bien commencer')!==false) return 'start';

    // Tolère virgule, point, saut de ligne ou autre ponctuation entre les deux expressions.
    if(
        strpos($plain,'vos questions')!==false
        && strpos($plain,'sans détour')!==false
    ) return 'faq';

    if(strpos($plain,'une idée en tête')!==false) return 'cta';

    return '';
}

function jd_core_web_layout_persist_2162(){
    if(get_option('jd_core_web_layout_version')===JD_CORE_VERSION) return;

    $page=get_page_by_path('creation-site-internet');
    if(!($page instanceof WP_Post)){
        update_option('jd_core_web_layout_version',JD_CORE_VERSION,false);
        return;
    }

    $raw=(string)$page->post_content;
    if(!$raw || strpos($raw,'wp:johndesign/section')===false){
        update_option('jd_core_web_layout_version',JD_CORE_VERSION,false);
        return;
    }

    $blocks=parse_blocks($raw);
    $next=[];
    $removed=0;

    foreach($blocks as $block){
        $type=jd_core_web_layout_type_2162(jd_core_web_layout_block_text_2162($block));
        if($type==='remove-studio' || $type==='remove-related'){
            $removed++;
            continue;
        }
        $next[]=$block;
    }

    if($removed){
        wp_update_post([
            'ID'=>$page->ID,
            'post_content'=>wp_slash(serialize_blocks($next)),
        ]);
    }

    update_option('jd_core_web_layout_diag',[
        'removed_blocks'=>$removed,
        'checked_at'=>current_time('mysql'),
    ],false);
    update_option('jd_core_web_layout_version',JD_CORE_VERSION,false);

    wp_cache_flush();
    if(function_exists('wp_cache_clear_cache')) wp_cache_clear_cache();
}
add_action('admin_init','jd_core_web_layout_persist_2162',65);
add_action('wp_loaded','jd_core_web_layout_persist_2162',10);

function jd_core_web_layout_add_class_2162($html,$class){
    if(!$html || !$class) return $html;

    if(preg_match('/<section\b[^>]*\bclass=(["\'])(.*?)\1/i',$html)){
        return preg_replace_callback(
            '/<section\b([^>]*?)\bclass=(["\'])(.*?)\2([^>]*)>/i',
            function($m)use($class){
                return '<section'.$m[1].'class='.$m[2].trim($m[3].' '.$class).$m[2].$m[4].'>';
            },
            $html,
            1
        );
    }

    if(preg_match('/<section\b/i',$html)){
        return preg_replace('/<section\b/i','<section class="'.esc_attr($class).'"',$html,1);
    }

    return '<div class="'.esc_attr($class).'">'.$html.'</div>';
}

function jd_core_web_portfolio_items_2162(){
    return [
        ['name'=>'Action Formation Theresa','url'=>'https://actionformationtheresa.fr/'],
        ['name'=>'Cap Multiclôture','url'=>'https://www.capmulticloture.com/'],
        ['name'=>'Église Marseille Kléber','url'=>'https://eglise-marseille-kleber.fr/'],
        ['name'=>'Un Max de Vie','url'=>'https://www.unmaxdevie.com/'],
        ['name'=>'Sonicscape Studio','url'=>'https://www.sonicscape-studio.fr/'],
        ['name'=>'ACM Construction Piscine','url'=>'https://www.acm-construction-piscine.fr/'],
        ['name'=>'Bourgaud Services','url'=>'http://bourgaud-services.fr/'],
    ];
}

function jd_core_web_portfolio_shot_2162($url){
    if(stripos($url,'unmaxdevie.com')!==false){
        return 'https://image.thum.io/get/width/1200/crop/750/noanimate/'.rawurlencode($url).'?v=20260924-3';
    }
    return 'https://s0.wp.com/mshots/v1/'.rawurlencode($url).'?w=1200&h=750';
}

function jd_core_web_portfolio_gallery_2162(){
    $items=jd_core_web_portfolio_items_2162();
    if(!$items) return '';

    $cards='';
    foreach($items as $i=>$item){
        $url=(string)$item['url'];
        $host=(string)wp_parse_url($url,PHP_URL_HOST);
        $host=preg_replace('/^www\./i','',$host);
        $shot=jd_core_web_portfolio_shot_2162($url);
        $accent=($i%3)+1;

        $cards.='<a class="jd-web-portfolio-card is-accent-'.$accent.'" href="'.esc_url($url).'" target="_blank" rel="noopener noreferrer">'
            .'<span class="jd-web-portfolio-visual">'
                .'<img src="'.esc_url($shot).'" alt="Aperçu du site '.esc_attr($item['name']).'" loading="lazy" decoding="async">'
                .'<span class="jd-web-portfolio-arrow" aria-hidden="true">↗</span>'
                .'<span class="jd-web-portfolio-hover">Visiter le site</span>'
            .'</span>'
            .'<span class="jd-web-portfolio-meta">'
                .'<strong>'.esc_html($item['name']).'</strong>'
                .'<span>'.esc_html($host).'</span>'
            .'</span>'
        .'</a>';
    }

    return '<section class="jd-web-portfolio-strip" data-jd-web-portfolio aria-labelledby="jd-web-portfolio-title">'
        .'<div class="jd-web-portfolio-head">'
            .'<div>'
                .'<p class="jd-eyebrow">QUELQUES SITES RÉALISÉS</p>'
                .'<h2 id="jd-web-portfolio-title">Des sites en ligne,<br>à découvrir.</h2>'
                .'<p>Faites défiler la galerie et cliquez sur un projet pour visiter le site.</p>'
            .'</div>'
            .'<div class="jd-web-portfolio-controls" aria-label="Navigation de la galerie">'
                .'<button type="button" class="jd-web-portfolio-nav" data-jd-web-portfolio-prev aria-label="Site précédent">←</button>'
                .'<button type="button" class="jd-web-portfolio-nav" data-jd-web-portfolio-next aria-label="Site suivant">→</button>'
            .'</div>'
        .'</div>'
        .'<div class="jd-web-portfolio-track" data-jd-web-portfolio-track>'
            .$cards
        .'</div>'
    .'</section>';
}

/**
 * Alternance visuelle et ordre du bas de page :
 * Pour bien commencer → Maintenance → Galerie → FAQ → CTA.
 */
function jd_core_web_layout_render_2162($block_content,$block){
    if(!is_page('creation-site-internet')) return $block_content;
    if(($block['blockName']??'')!=='johndesign/section') return $block_content;

    $plain=jd_core_web_layout_plain_2162($block_content);
    $type=jd_core_web_layout_type_2162($plain);

    if($type==='remove-studio' || $type==='remove-related') return '';

    if($type==='why'){
        return jd_core_web_layout_add_class_2162(
            $block_content,
            'jd-web-flow-section jd-web-flow-soft'
        );
    }

    if($type==='scope'){
        return jd_core_web_layout_add_class_2162(
            $block_content,
            'jd-web-flow-section jd-web-flow-card jd-web-flow-scope'
        );
    }

    if($type==='start'){
        return jd_core_web_layout_add_class_2162(
            $block_content,
            'jd-web-flow-section jd-web-flow-pink'
        );
    }

    if($type==='faq'){
        $faq=jd_core_web_layout_add_class_2162(
            $block_content,
            'jd-web-flow-section jd-web-flow-card jd-web-flow-faq'
        );

        return '<div class="jd-web-maintenance-injected jd-web-maintenance-in-flow">'
            .jd_core_web_maintenance_2151()
            .'</div>'
            .jd_core_web_portfolio_gallery_2162()
            .$faq;
    }

    if($type==='cta'){
        return jd_core_web_layout_add_class_2162(
            $block_content,
            'jd-web-flow-section jd-web-flow-soft jd-web-flow-cta'
        );
    }

    return $block_content;
}
add_filter('render_block','jd_core_web_layout_render_2162',70,2);
