<?php
if (!defined('ABSPATH')) exit;

/**
 * SEO minimal John Design.
 * Ne prend la main que si aucun plugin SEO majeur n'est actif.
 */
function jd_core_has_external_seo(){
    return defined('WPSEO_VERSION')
        || defined('RANK_MATH_VERSION')
        || defined('AIOSEO_VERSION')
        || class_exists('AIOSEO\\Plugin\\AIOSEO');
}

function jd_core_seo_map(){
    return [
        'home' => [
            'focus' => 'graphiste à Aix-en-Provence',
            'secondary' => 'graphiste Pertuis, graphiste indépendant, communication visuelle',
            'intent' => 'Trouver un graphiste local capable de gérer web, identité, print et signalétique.',
            'title' => 'Graphiste à Aix-en-Provence & Pertuis | John Design',
            'description' => 'Graphiste à Aix-en-Provence et Pertuis : sites internet, logos, print et signalétique. John Design accompagne aussi vos projets partout en France.',
        ],
        'creation-site-internet' => [
            'focus' => 'création de site internet à Pertuis',
            'secondary' => 'création site web Pertuis, création site internet Aix-en-Provence, site vitrine',
            'intent' => 'Trouver un prestataire local pour créer ou refondre un site professionnel.',
            'title' => 'Création site internet Pertuis & Aix | John Design',
            'description' => 'Création de site internet à Pertuis et Aix-en-Provence : site vitrine clair, responsive et pensé pour votre activité, jusqu’à la mise en ligne.',
        ],
        'identite-visuelle' => [
            'focus' => 'création de logo à Aix-en-Provence',
            'secondary' => 'identité visuelle Aix-en-Provence, création logo Pertuis, charte graphique',
            'intent' => 'Trouver un graphiste pour créer un logo et une identité visuelle professionnelle.',
            'title' => 'Création logo Aix-en-Provence | John Design',
            'description' => 'Création de logo à Aix-en-Provence et Pertuis : identité visuelle, couleurs, typographies, charte graphique et déclinaisons pour votre activité.',
        ],
        'print-signaletique' => [
            'focus' => 'signalétique à Pertuis',
            'secondary' => 'marquage véhicule Pertuis, panneau publicitaire, adhésifs, enseigne, impression',
            'intent' => 'Trouver un prestataire local pour concevoir et produire des supports visibles sur le terrain.',
            'title' => 'Signalétique Pertuis & Aix-en-Provence | John Design',
            'description' => 'Signalétique à Pertuis et Aix-en-Provence : panneaux, adhésifs, marquage véhicule et supports imprimés, de la création à la fabrication et la pose.',
        ],
        'realisations' => [
            'secondary' => 'portfolio graphiste, réalisations site internet, logo, signalétique',
            'intent' => 'Rassurer et montrer des preuves avant une prise de contact.',
            'title' => 'Réalisations — Web, identité & signalétique | John Design',
            'description' => 'Découvrez des réalisations John Design : sites internet, identités visuelles, supports print, signalétique et marquage réalisés pour de vrais clients.',
        ],
        'a-propos' => [
            'secondary' => 'graphiste indépendant, graphiste Le Puy-Sainte-Réparade',
            'intent' => 'Présenter le professionnel derrière John Design et renforcer la confiance.',
            'title' => 'Jonathan Romain, graphiste indépendant | John Design',
            'description' => 'Jonathan Romain, graphiste indépendant depuis 2015 au Puy-Sainte-Réparade, accompagne les professionnels autour d’Aix-en-Provence et partout en France.',
        ],
        'methode' => [
            'intent' => 'Expliquer le processus de travail et lever les freins avant demande de devis.',
            'title' => 'Méthode de création & accompagnement | John Design',
            'description' => 'Découvrez la méthode John Design : échange, cadrage, création, ajustements, production et accompagnement jusqu’à la livraison de votre projet.',
        ],
        'contact' => [
            'intent' => 'Convertir une visite en demande de devis.',
            'title' => 'Contact & devis | John Design',
            'description' => 'Parlez de votre projet à John Design : site internet, logo, identité visuelle, print ou signalétique. Demandez un devis ou un premier échange.',
        ],
        'mentions-legales' => [
            'title' => 'Mentions légales — John Design',
            'description' => 'Mentions légales du site John Design.',
            'noindex' => true,
        ],
        'politique-de-confidentialite' => [
            'title' => 'Politique de confidentialité — John Design',
            'description' => 'Politique de confidentialité et traitement des données du site John Design.',
            'noindex' => true,
        ],
    ];
}

function jd_core_seo_current_key(){
    if (is_front_page()) return 'home';
    if (!is_page()) return '';
    $post=get_queried_object();
    return ($post instanceof WP_Post)?$post->post_name:'';
}
function jd_core_seo_current(){
    $key=jd_core_seo_current_key();
    $map=jd_core_seo_map();
    return $key && isset($map[$key]) ? $map[$key] : null;
}

add_filter('pre_get_document_title',function($title){
    if (is_admin() || jd_core_has_external_seo()) return $title;
    $seo=jd_core_seo_current();
    return !empty($seo['title']) ? $seo['title'] : $title;
},20);

add_filter('wp_robots',function($robots){
    $seo=jd_core_seo_current();
    if (!empty($seo['noindex'])) {
        $robots['noindex']=true;
        $robots['nofollow']=false;
    }
    return $robots;
},20);

add_action('wp_head',function(){
    if (is_admin() || jd_core_has_external_seo()) return;
    $seo=jd_core_seo_current();
    if (!$seo) return;

    $description=trim((string)($seo['description']??''));
    if ($description) echo "\n<meta name=\"description\" content=\"".esc_attr($description)."\">\n";

    $url=is_front_page()?home_url('/'):get_permalink();
    $title=(string)($seo['title']??wp_get_document_title());
    echo '<meta property="og:type" content="website">'."\n";
    echo '<meta property="og:locale" content="fr_FR">'."\n";
    echo '<meta property="og:site_name" content="John Design">'."\n";
    echo '<meta property="og:title" content="'.esc_attr($title).'">'."\n";
    if ($description) echo '<meta property="og:description" content="'.esc_attr($description).'">'."\n";
    echo '<meta property="og:url" content="'.esc_url($url).'">'."\n";
    echo '<meta name="twitter:card" content="summary_large_image">'."\n";

    if (is_front_page()) {
        $schema=[
            '@context'=>'https://schema.org',
            '@type'=>'ProfessionalService',
            'name'=>'John Design',
            'url'=>home_url('/'),
            'email'=>'mailto:'.jd_core_contact_email(),
            'description'=>$description,
            'areaServed'=>[
                '@type'=>'Country',
                'name'=>'France',
            ],
            'sameAs'=>[
                'https://www.instagram.com/johndesign_net/',
            ],
            'knowsAbout'=>[
                'Création de site internet',
                'Identité visuelle',
                'Logo',
                'Print',
                'Signalétique',
                'Marquage',
            ],
        ];
        echo '<script type="application/ld+json">'.wp_json_encode($schema,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).'</script>'."\n";
    }
},5);


/**
 * Intégration Yoast SEO.
 * On laisse Yoast gérer canonicals, sitemap, Open Graph et schéma,
 * mais John Design fournit les titres et descriptions validés du site.
 */
function jd_core_yoast_value($existing,$field){
    if (is_admin()) return $existing;
    $seo=jd_core_seo_current();
    if (!$seo) return $existing;
    if ($field==='title' && !empty($seo['title'])) return $seo['title'];
    if ($field==='description' && !empty($seo['description'])) return $seo['description'];
    return $existing;
}
add_filter('wpseo_title',function($value){return jd_core_yoast_value($value,'title');},20);
add_filter('wpseo_metadesc',function($value){return jd_core_yoast_value($value,'description');},20);
add_filter('wpseo_opengraph_title',function($value){return jd_core_yoast_value($value,'title');},20);
add_filter('wpseo_opengraph_desc',function($value){return jd_core_yoast_value($value,'description');},20);
add_filter('wpseo_twitter_title',function($value){return jd_core_yoast_value($value,'title');},20);
add_filter('wpseo_twitter_description',function($value){return jd_core_yoast_value($value,'description');},20);


/**
 * Ajustements éditoriaux réels pour les pages commerciales.
 * Objectif : faire apparaître naturellement la requête dans l'introduction
 * et plus loin dans le contenu, sans ajouter de texte caché ni bourrage.
 */
function jd_core_seo_content_tuning_map(){
    return [
        'home'=>[
            'intro'=>'Graphiste à Aix-en-Provence et autour de Pertuis, John Design vous accompagne avec un seul interlocuteur pour construire une communication cohérente.',
            'later'=>'Ainsi, votre identité, votre site et vos supports avancent dans la même direction, avec une approche simple et concrète.',
        ],
        'creation-site-internet'=>[
            'intro'=>'Pour votre création de site internet à Pertuis, John Design conçoit un site clair, responsive et cohérent avec votre activité.',
            'later'=>'De plus, chaque création de site internet à Pertuis est pensée pour guider vos visiteurs vers l’essentiel et faciliter la prise de contact.',
        ],
        'identite-visuelle'=>[
            'intro'=>'Pour votre création de logo à Aix-en-Provence, John Design construit une identité visuelle reconnaissable, cohérente et facile à décliner.',
            'later'=>'Ensuite, chaque création de logo à Aix-en-Provence peut se prolonger naturellement sur vos supports web, print et signalétique.',
        ],
        'print-signaletique'=>[
            'intro'=>'Pour votre signalétique à Pertuis, John Design conçoit des supports visibles et cohérents avec votre identité, du fichier jusqu’à la fabrication.',
            'later'=>'Enfin, votre signalétique à Pertuis peut être déclinée sur panneaux, adhésifs, marquage véhicule et autres supports selon le projet.',
        ],
    ];
}
function jd_core_plain_has($html,$needle){
    return mb_stripos(wp_strip_all_tags((string)$html),$needle)!==false;
}
function jd_core_tune_page_content($key,$post_id,$tuning){
    $raw=(string)get_post_field('post_content',$post_id);
    if(!$raw || strpos($raw,'wp:johndesign/section')===false) return false;
    $blocks=parse_blocks($raw);
    $defs=jd_core_sections();
    $targets=[];
    foreach($blocks as $bi=>$block){
        if(($block['blockName']??'')!=='johndesign/section') continue;
        $sid=$block['attrs']['sectionId']??'';
        $def=$defs[$sid]??null;
        if(!$def || empty($def['fields'])) continue;
        $used=[];
        if(!empty($def['template'])){
            preg_match_all('/\{\{(f\d+)\}\}/',$def['template'],$m);
            $used=array_values(array_unique($m[1]??[]));
        }
        foreach($def['fields'] as $field){
            $fk=$field['key']??'';
            if(!$fk || ($used && !in_array($fk,$used,true))) continue;
            if(in_array($field['type']??'',['image','url'],true)) continue;
            $label=(string)($field['label']??'');
            if(preg_match('/jd-review|jd-step-number|jd-eyebrow|Titre H[123]|Libellé \/ lien|jd-caption|jd-google/i',$label)) continue;
            $current=$block['attrs']['fields'][$fk]??($field['default']??'');
            $plain=trim(wp_strip_all_tags(str_replace('<br/>',' ',(string)$current)));
            if(mb_strlen($plain)<45) continue;
            $score=strpos($label,'jd-lead')!==false?0:1;
            $targets[]=['bi'=>$bi,'key'=>$fk,'score'=>$score,'value'=>(string)$current];
        }
    }
    if(!$targets) return false;
    usort($targets,function($a,$b){ return ($a['score']<=>$b['score']) ?: ($a['bi']<=>$b['bi']); });
    $changed=false;
    $focus=jd_core_seo_map()[$key]['focus']??'';
    if($focus && !jd_core_plain_has($raw,$focus)){
        $t=$targets[0];
        $blocks[$t['bi']]['attrs']['fields'][$t['key']]=wp_kses_post($tuning['intro'].' '.ltrim($t['value']));
        $changed=true;
    }
    $serialized=serialize_blocks($blocks);
    $occ=$focus?substr_count(mb_strtolower(wp_strip_all_tags($serialized)),mb_strtolower($focus)):0;
    if($focus && $occ<2){
        $t=$targets[count($targets)-1];
        $current=$blocks[$t['bi']]['attrs']['fields'][$t['key']]??$t['value'];
        if(!jd_core_plain_has($current,$focus)){
            $blocks[$t['bi']]['attrs']['fields'][$t['key']]=wp_kses_post(rtrim((string)$current).' '.$tuning['later']);
            $changed=true;
        }
    }
    if($changed){
        wp_update_post(['ID'=>$post_id,'post_content'=>wp_slash(serialize_blocks($blocks))]);
    }
    return $changed;
}
function jd_core_apply_seo_content_tuning(){
    $map=jd_core_seo_content_tuning_map();
    $changed=0;$missing=[];
    foreach($map as $key=>$tuning){
        $id=jd_core_seo_page_id($key);
        if(!$id){$missing[]=$key;continue;}
        if(jd_core_tune_page_content($key,$id,$tuning)) $changed++;
    }
    update_option('jd_core_seo_content_tuning_version',JD_CORE_VERSION,false);
    update_option('jd_core_seo_content_tuning_diag',[
        'changed'=>$changed,
        'missing'=>$missing,
        'checked_at'=>current_time('mysql'),
    ],false);
}
add_action('admin_init',function(){
    if(get_option('jd_core_seo_content_tuning_version')!==JD_CORE_VERSION){
        jd_core_apply_seo_content_tuning();
    }
},25);

/**
 * Synchronise les champs éditables de Yoast avec la stratégie John Design.
 * On ne fabrique pas les scores : Yoast garde son analyse réelle.
 */
function jd_core_seo_page_id($key){
    if($key==='home') return (int)get_option('page_on_front');
    $page=get_page_by_path($key);
    return ($page instanceof WP_Post)?(int)$page->ID:0;
}
function jd_core_sync_yoast_meta($force=false){
    if(!defined('WPSEO_VERSION')) return ['updated'=>0,'missing'=>[]];
    $map=jd_core_seo_map();
    $updated=0;$missing=[];
    foreach($map as $key=>$data){
        if(!empty($data['noindex'])) continue;
        $id=jd_core_seo_page_id($key);
        if(!$id){$missing[]=$key;continue;}
        if(!empty($data['focus'])){
            update_post_meta($id,'_yoast_wpseo_focuskw',sanitize_text_field($data['focus']));
        }else{
            delete_post_meta($id,'_yoast_wpseo_focuskw');
        }
        if(!empty($data['title'])) update_post_meta($id,'_yoast_wpseo_title',sanitize_text_field($data['title']));
        if(!empty($data['description'])) update_post_meta($id,'_yoast_wpseo_metadesc',sanitize_text_field($data['description']));
        $updated++;
    }
    update_option('jd_core_yoast_sync_version',JD_CORE_VERSION,false);
    update_option('jd_core_yoast_sync_diag',[
        'updated'=>$updated,
        'missing'=>$missing,
        'checked_at'=>current_time('mysql'),
    ],false);
    return ['updated'=>$updated,'missing'=>$missing];
}
add_action('admin_init',function(){
    if(!defined('WPSEO_VERSION')) return;
    if(get_option('jd_core_yoast_sync_version')!==JD_CORE_VERSION) jd_core_sync_yoast_meta();
},30);

function jd_core_manual_yoast_sync(){
    if(!current_user_can('manage_options')) wp_die('Accès refusé');
    check_admin_referer('jd_sync_yoast');
    $result=jd_core_sync_yoast_meta(true);
    wp_safe_redirect(add_query_arg([
        'page'=>'jd-core',
        'yoast_synced'=>1,
        'yoast_updated'=>(int)$result['updated'],
    ],admin_url('admin.php')).'#jd-seo');
    exit;
}
add_action('admin_post_jd_sync_yoast','jd_core_manual_yoast_sync');

/**
 * Les modules John Design sont des blocs dynamiques. Cette intégration officielle
 * fournit leur contenu réel à l'analyse JavaScript de Yoast dans Gutenberg.
 */
function jd_core_enqueue_yoast_analysis_bridge(){
    if(!defined('WPSEO_VERSION')) return;
    $screen=function_exists('get_current_screen')?get_current_screen():null;
    if(!$screen || $screen->base!=='post' || $screen->post_type!=='page') return;
    wp_enqueue_script(
        'jd-yoast-analysis',
        JD_CORE_URL.'assets/yoast-analysis.js',
        ['jquery','wp-data','jd-section-editor'],
        JD_CORE_VERSION,
        true
    );
    $post_id=isset($_GET['post'])?absint($_GET['post']):0;
    $key='';
    if($post_id){
        $front=(int)get_option('page_on_front');
        $key=$post_id===$front?'home':get_post_field('post_name',$post_id);
    }
    $map=jd_core_seo_map();
    wp_localize_script('jd-yoast-analysis','JD_YOAST_CONTEXT',[
        'key'=>$key,
        'focus'=>$map[$key]['focus']??'',
        'home'=>home_url('/'),
    ]);
}
add_action('admin_enqueue_scripts','jd_core_enqueue_yoast_analysis_bridge',30);
