<?php
if (!defined('ABSPATH')) exit;

/**
 * Nettoyage éditorial demandé après mise en production.
 * - retire les légendes de démonstration inutiles
 * - corrige le doublon de la page Site internet
 * - remplace le titre "Une marque qui dit bonjour."
 * - retire la note sur les vignettes de sites
 * - réutilise la photo camion/stickers de l'accueil sur la section IA de Print & signalétique
 */
function jd_core_cleanup_all_managed_pages_2140(){
    $replacements=[
        'Composition John Design · concept de démonstration, sans commande client.'=>'',
        'Étude créative fictive : aucun client, aucune commande ni résultat commercial n’est associé à cette composition.'=>'',
        'Les vignettes présentent une capture ou le nom du projet. Les sites des clients peuvent évoluer après leur livraison.'=>'',
        'Une marque qui dit bonjour.'=>'Une identité qui vous ressemble.',
        'Pour votre création de site internet à Pertuis, John Design conçoit un site clair, responsive et cohérent avec votre activité.'=>'Pour votre création de site internet, John Design conçoit un site clair, responsive et cohérent avec votre activité.',
        'De plus, chaque création de site internet à Pertuis est pensée pour guider vos visiteurs vers l’essentiel et faciliter la prise de contact.'=>'De plus, chaque création de site internet est pensée pour guider vos visiteurs vers l’essentiel et faciliter la prise de contact.',
        'Pour votre création de logo à Aix-en-Provence, John Design construit une identité visuelle reconnaissable, cohérente et facile à décliner.'=>'John Design construit une identité visuelle reconnaissable, cohérente et facile à décliner sur tous vos supports.',
        'Ensuite, chaque création de logo à Aix-en-Provence peut se prolonger naturellement sur vos supports web, print et signalétique.'=>'Ensuite, votre identité visuelle peut se prolonger naturellement sur vos supports web, print et signalétique.',
        'Pour votre signalétique à Pertuis, John Design conçoit des supports visibles et cohérents avec votre identité, du fichier jusqu’à la fabrication.'=>'John Design conçoit des supports visibles et cohérents avec votre identité, du fichier jusqu’à la fabrication.',
        'Enfin, votre signalétique à Pertuis peut être déclinée sur panneaux, adhésifs, marquage véhicule et autres supports selon le projet.'=>'Enfin, vos supports peuvent être déclinés sur panneaux, adhésifs, marquage véhicule et autres formats selon le projet.',
        'Ici, pas de longues descriptions : uniquement de vraies réalisations, cadrées de façon uniforme pour laisser le visuel parler.'=>'',
        'Jonathan Romain · Le Puy-Sainte-Réparade · Partout en France'=>'',

    ];

    $dedupe=[
        'Graphiste à Aix-en-Provence et autour de Pertuis, John Design vous accompagne avec un seul interlocuteur pour construire une communication cohérente.',
        'Ainsi, votre identité, votre site et vos supports avancent dans la même direction, avec une approche simple et concrète.',
        'Pour votre création de site internet, John Design conçoit un site clair, responsive et cohérent avec votre activité.',
        'De plus, chaque création de site internet est pensée pour guider vos visiteurs vers l’essentiel et faciliter la prise de contact.',
        'Pour votre création de logo à Aix-en-Provence, John Design construit une identité visuelle reconnaissable, cohérente et facile à décliner.',
        'Ensuite, chaque création de logo à Aix-en-Provence peut se prolonger naturellement sur vos supports web, print et signalétique.',
        'Pour votre signalétique à Pertuis, John Design conçoit des supports visibles et cohérents avec votre identité, du fichier jusqu’à la fabrication.',
        'Enfin, votre signalétique à Pertuis peut être déclinée sur panneaux, adhésifs, marquage véhicule et autres supports selon le projet.',
    ];

    $pages=get_posts([
        'post_type'=>'page',
        'post_status'=>['publish','draft','private'],
        'numberposts'=>-1,
        'suppress_filters'=>false,
    ]);

    $changed=0;
    foreach($pages as $page){
        $raw=(string)$page->post_content;
        if(strpos($raw,'wp:johndesign/section')===false) continue;

        $blocks=parse_blocks($raw);
        $page_changed=false;

        foreach($blocks as &$block){
            if(($block['blockName']??'')!=='johndesign/section') continue;
            if(empty($block['attrs']['fields']) || !is_array($block['attrs']['fields'])) continue;

            foreach($block['attrs']['fields'] as $key=>$value){
                if(!is_string($value) || $value==='') continue;
                $next=$value;

                foreach($replacements as $from=>$to){
                    $next=str_replace($from,$to,$next);
                }

                foreach($dedupe as $phrase){
                    $quoted=preg_quote($phrase,'/');
                    $next=preg_replace('/(?:'.$quoted.'(?:\\s|<br\\s*\\/?\\s*>|&nbsp;)*){2,}/iu',$phrase.' ',$next);
                }

                // Safety pass for accidental immediate duplicate sentences generated by earlier SEO migrations.
                $next=preg_replace('/([^.!?<>]{35,}[.!?])(?:\\s|<br\\s*\\/?\\s*>|&nbsp;)+\\1/iu','$1',$next);

                // Remove the unnecessary portfolio disclaimer/copy requested after launch.
                $next=preg_replace('/Ici,?\\s*pas de longues? descriptions?[^.!?]*(?:[.!?]|$)/iu','',$next);

                $next=trim(preg_replace('/[ \\t]{2,}/',' ',$next));
                if($next!==$value){
                    $block['attrs']['fields'][$key]=$next;
                    $page_changed=true;
                }
            }
        }
        unset($block);

        if($page_changed){
            wp_update_post(['ID'=>$page->ID,'post_content'=>wp_slash(serialize_blocks($blocks))]);
            $changed++;
        }
    }

    return $changed;
}
function jd_core_find_home_truck_image_2140(){
    $front=(int)get_option('page_on_front');
    if(!$front) return '';

    $blocks=parse_blocks((string)get_post_field('post_content',$front));
    $defs=jd_core_sections();

    foreach($blocks as $block){
        if(($block['blockName']??'')!=='johndesign/section') continue;
        $sid=$block['attrs']['sectionId']??'';
        $def=$defs[$sid]??null;
        if(!$def) continue;

        $fields=$block['attrs']['fields']??[];
        $haystack=mb_strtolower(
            wp_strip_all_tags(
                wp_json_encode($fields,JSON_UNESCAPED_UNICODE).' '.($def['template']??'')
            )
        );

        // We want the real Eco Clim vehicle photo, not the decorative
        // "Hello, la vraie vie" image from the IA/stickers section.
        if(strpos($haystack,'eco clim')===false && strpos($haystack,'adhésif')===false && strpos($haystack,'adhesif')===false){
            continue;
        }

        foreach(($def['fields']??[]) as $field){
            if(($field['type']??'')!=='image') continue;
            $key=$field['key']??'';
            if(!$key) continue;
            $value=$fields[$key]??($field['default']??'');
            $value=(string)$value;
            if(!$value || strpos($value,'{{THEME_URI}}')!==false) continue;
            if(strpos($value,'data:image/')===0 || filter_var($value,FILTER_VALIDATE_URL)){
                update_option('jd_core_truck_image_src',$value,false);
                return $value;
            }
        }
    }
    return '';
}

function jd_core_apply_truck_to_print_ai_2140($image){
    if(!$image) return false;

    $page=get_page_by_path('print-signaletique');
    if(!($page instanceof WP_Post)) return false;

    $blocks=parse_blocks((string)$page->post_content);
    $defs=jd_core_sections();
    $changed=false;

    foreach($blocks as &$block){
        if(($block['blockName']??'')!=='johndesign/section') continue;
        $sid=$block['attrs']['sectionId']??'';
        $def=$defs[$sid]??null;
        if(!$def) continue;

        $fields=$block['attrs']['fields']??[];
        $haystack=mb_strtolower(wp_strip_all_tags(wp_json_encode($fields,JSON_UNESCAPED_UNICODE).' '.($def['template']??'')));
        if(strpos($haystack,'ia fait des merveilles')===false && strpos($haystack,'stickers')===false) continue;

        foreach(($def['fields']??[]) as $field){
            if(($field['type']??'')!=='image') continue;
            $key=$field['key']??'';
            if(!$key) continue;
            if(!isset($block['attrs']['fields']) || !is_array($block['attrs']['fields'])) $block['attrs']['fields']=[];
            if(($block['attrs']['fields'][$key]??'')!==$image){
                $block['attrs']['fields'][$key]=$image;
                $changed=true;
            }
            break;
        }
    }
    unset($block);

    if($changed){
        wp_update_post(['ID'=>$page->ID,'post_content'=>wp_slash(serialize_blocks($blocks))]);
    }
    return $changed;
}

function jd_core_capture_home_truck_2145(){
    $front=(int)get_option('page_on_front');
    if(!$front) return '';

    $blocks=parse_blocks((string)get_post_field('post_content',$front));
    foreach($blocks as $block){
        if(($block['blockName']??'')!=='johndesign/section') continue;

        $attrs=$block['attrs']??[];
        $html=jd_core_render_section($attrs);
        $plain=mb_strtolower(wp_strip_all_tags($html));

        if(strpos($plain,'eco clim')===false && stripos($html,'eco clim')===false){
            continue;
        }

        // Prefer the exact image identified by its alt/figcaption context.
        if(preg_match('/<img\\b[^>]*alt=(["\\'])[^"\\']*eco\\s*clim[^"\\']*\\1[^>]*src=(["\\'])(.*?)\\2/i',$html,$m)){
            $src=html_entity_decode($m[3],ENT_QUOTES|ENT_HTML5,'UTF-8');
            if(strpos($src,'data:image/')===0 || filter_var($src,FILTER_VALIDATE_URL)){
                update_option('jd_core_truck_image_src',$src,false);
                return $src;
            }
        }

        // Fallback: any image in the Eco Clim block.
        if(preg_match('/<img\\b[^>]*\\bsrc=(["\\'])(.*?)\\1/i',$html,$m)){
            $src=html_entity_decode($m[2],ENT_QUOTES|ENT_HTML5,'UTF-8');
            if(strpos($src,'data:image/')===0 || filter_var($src,FILTER_VALIDATE_URL)){
                update_option('jd_core_truck_image_src',$src,false);
                return $src;
            }
        }
    }

    return jd_core_find_home_truck_image_2140();
}

function jd_core_content_cleanup_2140(){
    if(get_option('jd_core_content_cleanup_version')===JD_CORE_VERSION) return;

    $changed=jd_core_cleanup_all_managed_pages_2140();
    $truck=jd_core_capture_home_truck_2145();
    if(!$truck) $truck=jd_core_find_home_truck_image_2140();
    $print_changed=jd_core_apply_truck_to_print_ai_2140($truck);

    update_option('jd_core_content_cleanup_diag',[
        'changed_pages'=>$changed,
        'truck_found'=>(bool)$truck,
        'print_changed'=>(bool)$print_changed,
        'checked_at'=>current_time('mysql'),
    ],false);
    update_option('jd_core_content_cleanup_version',JD_CORE_VERSION,false);

    wp_cache_flush();
    if(function_exists('wp_cache_clear_cache')) wp_cache_clear_cache();
}
add_action('admin_init','jd_core_content_cleanup_2140',45);
