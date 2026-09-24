<?php
if (!defined('ABSPATH')) exit;

/**
 * John Design Core 2.15.8 — passe de clarté éditoriale.
 * Objectif : le visiteur comprend immédiatement la prestation et le bénéfice,
 * sans formulations conceptuelles à décoder.
 */
function jd_core_clarity_global_map_2158(){
    return [
        'Une bonne image. Partout où l’on vous voit.'=>'Sites internet, logos, impression & signalétique.',
        "Une bonne image. Partout où l'on vous voit."=>'Sites internet, logos, impression & signalétique.',
        'Du premier regard au premier contact : une communication cohérente, pensée pour votre activité.'=>'Un seul interlocuteur pour votre site internet, votre logo, vos supports imprimés et votre signalétique.',
        'Un site qui vous fait choisir.'=>'Création ou refonte de votre site internet.',
        'Un site clair, rapide et agréable à parcourir. Pour présenter votre activité et faciliter les demandes.'=>'Création ou refonte d’un site professionnel, rapide et adapté au mobile. Pour présenter clairement votre activité et faciliter les demandes de contact.',
        'Une marque qui se remarque.'=>'Création de logo & identité visuelle.',
        'Une identité qui vous ressemble.'=>'Création de logo & identité visuelle.',
        'Un logo et un univers visuel reconnaissables. Pour faire bonne impression et rester en tête.'=>'Logo, couleurs, typographies et charte graphique. Une identité claire, cohérente et facile à décliner.',
        'Du concret. Du visible.'=>'Impression & signalétique.',
        'Des supports imprimés à la signalétique. Pour donner de la présence à votre activité, sur le terrain.'=>'Cartes de visite, flyers, affiches, panneaux, adhésifs et marquage véhicule.',
        'Votre univers. Un site qui lui va bien.'=>'Création ou refonte de votre site internet.',
        'Votre site doit travailler pour votre entreprise.'=>'Un site internet professionnel, pensé pour votre activité.',
        'Être reconnu avant même de lire votre nom.'=>'Du logo à une identité visuelle complète.',
        'Du fichier à l’objet réel.'=>'Impression, signalétique & marquage.',
        "Du fichier à l'objet réel."=>'Impression, signalétique & marquage.',
        'Du fichier jusqu’au support final'=>'De la création à l’impression et à la pose',
        "Du fichier jusqu'au support final"=>'De la création à l’impression et à la pose',
        'Votre entreprise, prête à être vue'=>'Supports imprimés & signalétique pour votre entreprise',
        'Une image cohérente, sur tous vos supports.'=>'Votre identité sur tous vos supports.',
        'Une communication qui donne envie de vous choisir.'=>'Sites internet, logos, impression & signalétique.',
        'Votre communication doit faire plus que simplement être jolie.'=>'Une communication claire, professionnelle et cohérente.',
        'Votre communication, de l’idée jusqu’au résultat.'=>'Un seul interlocuteur, de la création à la livraison.',
        "Votre communication, de l'idée jusqu'au résultat."=>'Un seul interlocuteur, de la création à la livraison.',
        'FAIRE BEAU, C’EST BIEN. FAIRE CHOISIR, C’EST MIEUX.'=>'SITES INTERNET, LOGOS, IMPRESSION & SIGNALÉTIQUE.',
        "FAIRE BEAU, C'EST BIEN. FAIRE CHOISIR, C'EST MIEUX."=>'SITES INTERNET, LOGOS, IMPRESSION & SIGNALÉTIQUE.',
        'Faire beau, c’est bien. Faire choisir, c’est mieux.'=>'Sites internet, logos, impression & signalétique.',
        'Faisons quelque chose de fort.'=>'Parlons de votre projet.',
        'Parlons de ce que votre entreprise pourrait devenir.'=>'Parlons de votre site, votre logo ou vos supports.',
        'Construire mon identité'=>'Créer mon logo & mon identité',
        'Découvrir le print'=>'Voir l’impression & la signalétique',
    ];
}

function jd_core_clarity_page_map_2158($page_key){
    $maps=[
        'creation-site-internet'=>[
            'Pour votre création de site internet, John Design conçoit un site clair, responsive et cohérent avec votre activité.'=>'Je crée ou refonds votre site internet pour qu’il soit professionnel, rapide, responsive et simple à faire évoluer.',
            'De plus, chaque création de site internet est pensée pour guider vos visiteurs vers l’essentiel et faciliter la prise de contact.'=>'Le site est pensé pour présenter clairement votre activité, rassurer vos visiteurs et faciliter les demandes de contact. Une IA ou un constructeur peut mettre une page en ligne rapidement ; quand il faut corriger un bug, optimiser le mobile ou faire évoluer le site, vous gardez ici un interlocuteur qui connaît le projet.',
            'Vous pourriez le faire vous-même. Mais est-ce vraiment votre métier ?'=>'Mettre un site en ligne est facile. Le rendre fiable, clair et efficace demande plus de travail.',
            'Canva peut créer un visuel. Une IA peut générer un logo. Un constructeur peut mettre un site en ligne.'=>'Une IA ou un constructeur peut mettre un site en ligne rapidement. Mais quand il faut corriger un bug, améliorer le mobile, accélérer le site ou le faire évoluer, vous avez besoin d’un interlocuteur qui connaît le projet.',
            'C’est précisément mon métier.'=>'Je crée le site, je le mets en ligne et je peux continuer à le suivre ensuite.',
        ],
        'identite-visuelle'=>[
            'John Design construit une identité visuelle reconnaissable, cohérente et facile à décliner sur tous vos supports.'=>'Je crée votre logo et votre identité visuelle : couleurs, typographies et règles simples pour garder une image cohérente sur tous vos supports.',
            'Ensuite, votre identité visuelle peut se prolonger naturellement sur vos supports web, print et signalétique.'=>'Votre identité est pensée pour fonctionner aussi bien sur votre site que sur vos cartes, affiches, panneaux ou véhicules.',
        ],
        'print-signaletique'=>[
            'John Design conçoit des supports visibles et cohérents avec votre identité, du fichier jusqu’à la fabrication.'=>'Je conçois vos cartes de visite, flyers, affiches, panneaux, adhésifs et marquages, puis je peux vous accompagner jusqu’à la fabrication.',
            'Enfin, vos supports peuvent être déclinés sur panneaux, adhésifs, marquage véhicule et autres formats selon le projet.'=>'Vous gardez un seul interlocuteur pour la création, la fabrication et, selon le projet, la pose.',
        ],
    ];
    return $maps[$page_key]??[];
}

function jd_core_clarity_replace_text_2158($text,$page_key=''){
    if(!is_string($text) || $text==='') return $text;
    $map=array_merge(jd_core_clarity_global_map_2158(),jd_core_clarity_page_map_2158($page_key));
    foreach($map as $from=>$to){
        $text=str_ireplace($from,$to,$text);
    }
    return $text;
}

function jd_core_clarity_h1_target_2158($page_key){
    $targets=[
        'creation-site-internet'=>'Création ou refonte de votre site internet.',
        'identite-visuelle'=>'Création de logo & identité visuelle.',
        'print-signaletique'=>'Impression, signalétique & marquage.',
    ];
    return $targets[$page_key]??'';
}

function jd_core_clarity_h1_is_clear_2158($page_key,$value){
    $plain=mb_strtolower(wp_strip_all_tags((string)$value));
    $keywords=[
        'creation-site-internet'=>['site'],
        'identite-visuelle'=>['logo','identité'],
        'print-signaletique'=>['impression','signalétique','marquage','print'],
    ];
    if(empty($keywords[$page_key])) return true;
    foreach($keywords[$page_key] as $keyword){
        if(mb_strpos($plain,$keyword)!==false) return true;
    }
    return false;
}

function jd_core_clarity_force_service_h1_2158(&$blocks,$page_key){
    $target=jd_core_clarity_h1_target_2158($page_key);
    if(!$target) return false;

    $defs=jd_core_sections();
    foreach($blocks as &$block){
        if(($block['blockName']??'')!=='johndesign/section') continue;
        $sid=$block['attrs']['sectionId']??'';
        $def=$defs[$sid]??null;
        if(!$def || empty($def['fields'])) continue;
        $template=(string)($def['template']??'');

        foreach($def['fields'] as $field){
            $key=$field['key']??'';
            if(!$key || in_array($field['type']??'',['image','url'],true)) continue;
            $label=(string)($field['label']??'');
            $is_h1=false;
            if($template && preg_match('/<h1\b[^>]*>.*?\{\{'.preg_quote($key,'/').'\}\}.*?<\/h1>/is',$template)) $is_h1=true;
            if(!$is_h1 && preg_match('/\bH1\b|titre principal/i',$label)) $is_h1=true;
            if(!$is_h1) continue;

            $current=$block['attrs']['fields'][$key]??($field['default']??'');
            if(jd_core_clarity_h1_is_clear_2158($page_key,$current)){
                unset($block);
                return false;
            }
            if(!isset($block['attrs']['fields']) || !is_array($block['attrs']['fields'])) $block['attrs']['fields']=[];
            $block['attrs']['fields'][$key]=$target;
            unset($block);
            return true;
        }
    }
    unset($block);
    return false;
}

function jd_core_clarity_pass_2158(){
    if(get_option('jd_core_clarity_version')===JD_CORE_VERSION) return;

    $front=(int)get_option('page_on_front');
    $pages=get_posts([
        'post_type'=>'page',
        'post_status'=>['publish','draft','private'],
        'numberposts'=>-1,
        'suppress_filters'=>false,
    ]);

    $changed_pages=0;
    $replacement_count=0;

    foreach($pages as $page){
        $raw=(string)$page->post_content;
        if(!$raw || strpos($raw,'wp:johndesign/section')===false) continue;

        $page_key=((int)$page->ID===$front)?'home':(string)$page->post_name;
        $blocks=parse_blocks($raw);
        $page_changed=false;

        foreach($blocks as &$block){
            if(($block['blockName']??'')!=='johndesign/section') continue;
            if(empty($block['attrs']['fields']) || !is_array($block['attrs']['fields'])) continue;

            foreach($block['attrs']['fields'] as $key=>$value){
                if(!is_string($value) || $value==='') continue;
                $next=jd_core_clarity_replace_text_2158($value,$page_key);
                if($next!==$value){
                    $block['attrs']['fields'][$key]=$next;
                    $page_changed=true;
                    $replacement_count++;
                }
            }
        }
        unset($block);

        if(jd_core_clarity_force_service_h1_2158($blocks,$page_key)) $page_changed=true;

        if($page_changed){
            wp_update_post([
                'ID'=>$page->ID,
                'post_content'=>wp_slash(serialize_blocks($blocks)),
            ]);
            $changed_pages++;
        }
    }

    update_option('jd_core_clarity_diag',[
        'changed_pages'=>$changed_pages,
        'replacements'=>$replacement_count,
        'checked_at'=>current_time('mysql'),
    ],false);
    update_option('jd_core_clarity_version',JD_CORE_VERSION,false);

    wp_cache_flush();
    if(function_exists('wp_cache_clear_cache')) wp_cache_clear_cache();
}
add_action('admin_init','jd_core_clarity_pass_2158',55);

/** Fallback de rendu : les anciennes formulations ne doivent pas réapparaître via un défaut de bloc. */
function jd_core_clarity_render_2158($block_content,$block){
    if(($block['blockName']??'')!=='johndesign/section') return $block_content;

    $page_key='';
    if(is_front_page()) $page_key='home';
    elseif(is_page()){
        $page=get_queried_object();
        if($page instanceof WP_Post) $page_key=(string)$page->post_name;
    }

    $block_content=jd_core_clarity_replace_text_2158($block_content,$page_key);
    $target=jd_core_clarity_h1_target_2158($page_key);
    if($target && preg_match('/<h1\b([^>]*)>(.*?)<\/h1>/is',$block_content,$m)){
        if(!jd_core_clarity_h1_is_clear_2158($page_key,$m[2])){
            $replacement='<h1'.$m[1].'>'.esc_html($target).'</h1>';
            $block_content=preg_replace('/<h1\b[^>]*>.*?<\/h1>/is',$replacement,$block_content,1);
        }
    }
    return $block_content;
}
add_filter('render_block','jd_core_clarity_render_2158',60,2);
