<?php
if (!defined('ABSPATH')) exit;

/**
 * Final homepage merchandising:
 * Hero -> Quelques réalisations -> Ce que je fais -> Univers -> ...
 * Also keeps the visible section numbering coherent.
 */
function jd_core_home_merchandising_2139(){
    $front=(int)get_option('page_on_front');
    if(!$front) return false;

    $raw=(string)get_post_field('post_content',$front);
    if(!$raw || strpos($raw,'wp:johndesign/section')===false) return false;

    $blocks=parse_blocks($raw);
    $hero_index=null;
    $work_index=null;
    $changed=false;

    foreach($blocks as $i=>&$block){
        if(($block['blockName']??'')!=='johndesign/section') continue;
        $sid=(string)($block['attrs']['sectionId']??'');

        if($sid==='home-01') $hero_index=$i;
        if($sid==='home-04') $work_index=$i;

        $eyebrows=[
            'home-04'=>'01 / QUELQUES RÉALISATIONS',
            'home-02'=>'02 / CE QUE JE FAIS',
            'home-03'=>'03 / L’UNIVERS JOHN DESIGN',
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

    if($hero_index!==null && $work_index!==null && $work_index!==$hero_index+1){
        $work=$blocks[$work_index];
        array_splice($blocks,$work_index,1);
        // Re-find the hero after removing the work block.
        $hero_index=null;
        foreach($blocks as $i=>$block){
            if(($block['blockName']??'')==='johndesign/section' && ($block['attrs']['sectionId']??'')==='home-01'){
                $hero_index=$i;break;
            }
        }
        if($hero_index!==null){
            array_splice($blocks,$hero_index+1,0,[$work]);
            $changed=true;
        }
    }

    if($changed){
        wp_update_post(['ID'=>$front,'post_content'=>wp_slash(serialize_blocks($blocks))]);
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
function jd_core_site_polish_2139(){
    if(get_option('jd_core_site_polish_version')===JD_CORE_VERSION) return;

    jd_core_home_merchandising_2139();
    jd_core_refresh_unmaxdevie_2139();

    update_option('jd_core_site_polish_version',JD_CORE_VERSION,false);

    // Avoid serving the pre-update homepage/header from object/page cache.
    wp_cache_flush();
    if(function_exists('wp_cache_clear_cache')) wp_cache_clear_cache();
}
add_action('admin_init','jd_core_site_polish_2139',40);
