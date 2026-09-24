<?php
if (!defined('ABSPATH')) exit;

/**
 * Homepage merchandising 2.16.6.
 * Ordre demandé :
 * Hero (+ raccourcis métiers) -> Jonathan -> Offres -> Réalisations -> reste de la page.
 */
function jd_core_home_block_plain_2166($block){
    if(($block['blockName']??'')!=='johndesign/section') return '';

    $sid=(string)($block['attrs']['sectionId']??'');
    $defs=jd_core_sections();
    $def=$defs[$sid]??[];
    $values=is_array($block['attrs']['fields']??null)?$block['attrs']['fields']:[];
    $parts=[$sid];

    foreach((array)($def['fields']??[]) as $field){
        $key=$field['key']??'';
        if(!$key) continue;
        $value=array_key_exists($key,$values)?$values[$key]:($field['default']??'');
        if(is_string($value)) $parts[]=$value;
    }
    foreach($values as $value){
        if(is_string($value)) $parts[]=$value;
    }

    $plain=html_entity_decode(wp_strip_all_tags(implode(' ',$parts)),ENT_QUOTES|ENT_HTML5,'UTF-8');
    $plain=preg_replace('/\s+/u',' ',trim($plain));
    return mb_strtolower($plain);
}

function jd_core_home_merchandising_2139(){
    $front=(int)get_option('page_on_front');
    if(!$front) return false;

    $raw=(string)get_post_field('post_content',$front);
    if(!$raw || strpos($raw,'wp:johndesign/section')===false) return false;

    $blocks=parse_blocks($raw);

    // Le vieux bloc "univers" home-03 reste supprimé.
    $before=count($blocks);
    $blocks=array_values(array_filter($blocks,function($block){
        return !(($block['blockName']??'')==='johndesign/section' && ($block['attrs']['sectionId']??'')==='home-03');
    }));
    $changed=count($blocks)!==$before;

    $targets=[
        'hero'=>null,
        'about'=>null,
        'services'=>null,
        'work'=>null,
    ];

    foreach($blocks as $i=>&$block){
        if(($block['blockName']??'')!=='johndesign/section') continue;

        $sid=(string)($block['attrs']['sectionId']??'');
        $plain=jd_core_home_block_plain_2166($block);

        if($sid==='home-01') $targets['hero']=$i;
        elseif($sid==='home-02') $targets['services']=$i;
        elseif($sid==='home-04') $targets['work']=$i;
        elseif(
            strpos($plain,'john design')!==false
            && strpos($plain,'jonathan')!==false
        ){
            $targets['about']=$i;
        }

        // La numérotation visible suit désormais la nouvelle hiérarchie.
        $eyebrows=[
            'home-02'=>'01 / CE QUE JE FAIS',
            'home-04'=>'02 / DU VRAI TRAVAIL',
        ];
        if(isset($eyebrows[$sid])){
            if(!isset($block['attrs']['fields']) || !is_array($block['attrs']['fields'])) $block['attrs']['fields']=[];
            if(($block['attrs']['fields']['f001']??'')!==$eyebrows[$sid]){
                $block['attrs']['fields']['f001']=$eyebrows[$sid];
                $changed=true;
            }
        }
    }
    unset($block);

    $indices=array_values(array_unique(array_filter(array_values($targets),function($v){
        return $v!==null;
    })));

    if(count($indices)>=3){
        sort($indices);
        $anchor=$indices[0];

        $ordered=[];
        foreach(['hero','about','services','work'] as $key){
            if($targets[$key]!==null) $ordered[]=$blocks[$targets[$key]];
        }

        $prefix=[];
        $suffix=[];
        foreach($blocks as $i=>$block){
            if(in_array($i,$indices,true)) continue;
            if($i<$anchor) $prefix[]=$block;
            else $suffix[]=$block;
        }

        $new_blocks=array_merge($prefix,$ordered,$suffix);
        if(serialize_blocks($new_blocks)!==serialize_blocks($blocks)){
            $blocks=$new_blocks;
            $changed=true;
        }
    }

    update_option('jd_core_home_order_diag_2166',[
        'hero'=>$targets['hero']!==null,
        'about'=>$targets['about']!==null,
        'services'=>$targets['services']!==null,
        'work'=>$targets['work']!==null,
        'checked_at'=>current_time('mysql'),
    ],false);

    if($changed){
        wp_update_post([
            'ID'=>$front,
            'post_content'=>wp_slash(serialize_blocks($blocks))
        ]);
    }

    return $changed;
}

/**
 * Force a fresh WordPress.com mShots capture for Un Max de Vie.
 * A cache-busting query is part of the captured target URL, giving mShots
 * a new cache key now that the site itself has been repaired.
 */
function jd_core_refresh_unmaxdevie_2139(){
    // mShots kept serving a stale/wrong visual for this project.
    // Use a live screenshot endpoint with a new cache key instead.
    $target='https://www.unmaxdevie.com/';
    $shot='https://image.thum.io/get/width/1200/crop/675/noanimate/'.rawurlencode($target).'?v=20260924-2';
    $changed=false;

    $library=get_option('jd_core_sections_library');
    if(is_array($library) && !empty($library['realisations-03']['fields'])){
        foreach($library['realisations-03']['fields'] as &$field){
            if(($field['key']??'')==='f038'){
                if(($field['default']??'')!==$shot){
                    $field['default']=$shot;
                    $changed=true;
                }
                break;
            }
        }
        unset($field);
        if($changed) update_option('jd_core_sections_library',$library,false);
    }

    $page=get_page_by_path('realisations');
    if($page instanceof WP_Post){
        $raw=(string)$page->post_content;
        $blocks=parse_blocks($raw);
        $post_changed=false;
        foreach($blocks as &$block){
            if(($block['blockName']??'')!=='johndesign/section') continue;
            if(($block['attrs']['sectionId']??'')!=='realisations-03') continue;
            if(!isset($block['attrs']['fields']) || !is_array($block['attrs']['fields'])) $block['attrs']['fields']=[];
            if(($block['attrs']['fields']['f038']??'')!==$shot){
                $block['attrs']['fields']['f038']=$shot;
                $post_changed=true;
            }
        }
        unset($block);
        if($post_changed){
            wp_update_post(['ID'=>$page->ID,'post_content'=>wp_slash(serialize_blocks($blocks))]);
            $changed=true;
        }
    }

    // Trigger the screenshot once so it is ready when the portfolio loads.
    wp_remote_get($shot,['timeout'=>0.01,'blocking'=>false,'redirection'=>2]);
    return $changed;
}
function jd_core_realisations_cleanup_2154(){
    $page=get_page_by_path('realisations');
    if(!($page instanceof WP_Post)) return false;

    $raw=(string)$page->post_content;
    if(!$raw || strpos($raw,'wp:johndesign/section')===false) return false;

    $blocks=parse_blocks($raw);
    $changed=false;
    $next=[];

    foreach($blocks as $block){
        if(($block['blockName']??'')!=='johndesign/section'){
            $next[]=$block;
            continue;
        }

        $fields=$block['attrs']['fields']??[];
        $joined='';
        if(is_array($fields)){
            foreach($fields as $value){
                if(is_string($value)) $joined.=' '.wp_strip_all_tags($value);
            }
        }
        $plain=mb_strtolower($joined);

        // Remove the old introductory hero completely.
        if(strpos($plain,'quelques projets')!==false || strpos($plain,'du vrai, pas du remplissage')!==false){
            $changed=true;
            continue;
        }

        if(is_array($fields)){
            foreach($fields as $key=>$value){
                if(!is_string($value)) continue;
                $new=str_ireplace(
                    ['Le travail en images.','Le travail en images'],
                    ['Découvrez mes réalisations.','Découvrez mes réalisations.'],
                    $value
                );
                if($new!==$value){
                    $block['attrs']['fields'][$key]=$new;
                    $changed=true;
                }
            }
        }

        $next[]=$block;
    }

    if($changed){
        wp_update_post([
            'ID'=>$page->ID,
            'post_content'=>wp_slash(serialize_blocks($next))
        ]);
    }
    return $changed;
}

function jd_core_content_simplify_2155(){
    $changed=false;

    $pages=get_posts([
        'post_type'=>'page',
        'post_status'=>['publish','draft','private'],
        'numberposts'=>-1,
        'suppress_filters'=>false,
    ]);

    foreach($pages as $page){
        if($page->post_name==='concept-faites-impression') continue;

        $raw=(string)$page->post_content;
        if(!$raw || strpos($raw,'wp:johndesign/section')===false) continue;

        $blocks=parse_blocks($raw);
        $next=[];
        $page_changed=false;

        foreach($blocks as $block){
            if(($block['blockName']??'')!=='johndesign/section'){
                $next[]=$block;
                continue;
            }

            $fields=$block['attrs']['fields']??[];
            $joined='';
            if(is_array($fields)){
                foreach($fields as $value){
                    if(is_string($value)) $joined.=' '.wp_strip_all_tags($value);
                }
            }
            $plain=mb_strtolower($joined);

            if(strpos($plain,'faites impression')!==false){
                $page_changed=true;
                continue;
            }

            if($page->post_name==='realisations' && is_array($fields)){
                foreach($fields as $key=>$value){
                    if(!is_string($value)) continue;
                    $new=str_ireplace('SITES INTERNET / À EXPLORER','SITES INTERNET',$value);
                    if($new!==$value){
                        $block['attrs']['fields'][$key]=$new;
                        $page_changed=true;
                    }
                }
            }

            $next[]=$block;
        }

        if($page_changed){
            wp_update_post([
                'ID'=>$page->ID,
                'post_content'=>wp_slash(serialize_blocks($next))
            ]);
            $changed=true;
        }
    }

    $concept=get_page_by_path('concept-faites-impression');
    if($concept instanceof WP_Post && $concept->post_status!=='draft'){
        wp_update_post(['ID'=>$concept->ID,'post_status'=>'draft']);
        $changed=true;
    }

    return $changed;
}

function jd_core_site_polish_2139(){
    if(get_option('jd_core_site_polish_version')===JD_CORE_VERSION) return;

    jd_core_home_merchandising_2139();
    jd_core_refresh_unmaxdevie_2139();
    jd_core_realisations_cleanup_2154();
    jd_core_content_simplify_2155();

    update_option('jd_core_site_polish_version',JD_CORE_VERSION,false);

    // Avoid serving the pre-update homepage/header from object/page cache.
    wp_cache_flush();
    if(function_exists('wp_cache_clear_cache')) wp_cache_clear_cache();
}
add_action('admin_init','jd_core_site_polish_2139',40);
add_action('wp_loaded','jd_core_site_polish_2139',20);
