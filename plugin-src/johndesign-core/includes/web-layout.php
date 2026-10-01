<?php
if (!defined('ABSPATH')) exit;

/**
 * John Design Core 2.16.3 — parcours Site internet + galerie web minimale.
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
 * Les cartes web ne montrent que la capture : pas de nom, pas d'URL,
 * pas de cartouche ni d'overlay. Toute la capture reste cliquable.
 */

function jd_core_web_layout_plain_2163($value){
    $value=(string)$value;
    $value=str_replace(['<br>','<br/>','<br />'],' ',$value);
    $value=wp_strip_all_tags($value);
    $value=html_entity_decode($value,ENT_QUOTES|ENT_HTML5,'UTF-8');
    $value=preg_replace('/\s+/u',' ',trim($value));
    return mb_strtolower($value);
}

function jd_core_web_layout_block_text_2163($block){
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

    return jd_core_web_layout_plain_2163(implode(' ',$parts));
}

function jd_core_web_layout_type_2163($plain){
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

    if(
        strpos($plain,'vos questions')!==false
        && strpos($plain,'sans détour')!==false
    ) return 'faq';

    if(strpos($plain,'une idée en tête')!==false) return 'cta';

    return '';
}

function jd_core_web_layout_persist_2163(){
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
        $type=jd_core_web_layout_type_2163(jd_core_web_layout_block_text_2163($block));
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
add_action('admin_init','jd_core_web_layout_persist_2163',65);
add_action('wp_loaded','jd_core_web_layout_persist_2163',10);

function jd_core_web_layout_add_class_2163($html,$class){
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

function jd_core_web_portfolio_items_2163(){
    return [
        ['name'=>'Action Formation Theresa','url'=>'https://actionformationtheresa.fr/'],
        ['name'=>'Cap Multiclôture','url'=>'https://www.capmulticloture.com/'],
        ['name'=>'Église Marseille Kléber','url'=>'https://eglise-marseille-kleber.fr/'],
        [
            'name'=>'Un Max de Vie',
            'url'=>'https://www.unmaxdevie.com/',
            'shot'=>JD_CORE_URL.'assets/portfolio/umdv-site.webp'
        ],
        ['name'=>'Sonicscape Studio','url'=>'https://www.sonicscape-studio.fr/'],
        ['name'=>'ACM Construction Piscine','url'=>'https://www.acm-construction-piscine.fr/'],
        ['name'=>'Bourgaud Services','url'=>'http://bourgaud-services.fr/'],
    ];
}

function jd_core_web_portfolio_shot_2163($item){
    if(!empty($item['shot'])) return (string)$item['shot'];
    $url=(string)($item['url']??'');
    return 'https://s0.wp.com/mshots/v1/'.rawurlencode($url).'?w=1200&h=750';
}


function jd_core_web_portfolio_slider_21632(){
    $items=jd_core_web_portfolio_items_2163();
    if(!$items) return '';

    $slides='';
    $dots='';
    foreach($items as $i=>$item){
        $url=(string)$item['url'];
        $shot=jd_core_web_portfolio_shot_2163($item);
        $active=$i===0?' is-active':'';
        $hidden=$i===0?'false':'true';
        $loading=$i===0?'eager':'lazy';

        $slides.='<a class="jd-web-proof-slider__slide'.$active.'" data-jd-web-slide="'.$i.'" href="'.esc_url($url).'" target="_blank" rel="noopener noreferrer" aria-hidden="'.$hidden.'" aria-label="Visiter '.esc_attr($item['name']).'">'
            .'<span class="jd-web-proof-slider__browser">'
                .'<span class="jd-web-proof-slider__bar" aria-hidden="true"><i></i><i></i><i></i></span>'
                .'<img src="'.esc_url($shot).'" alt="Aperçu du site '.esc_attr($item['name']).'" loading="'.$loading.'" decoding="async">'
            .'</span>'
        .'</a>';

        $dots.='<button type="button" class="jd-web-proof-slider__dot'.$active.'" data-jd-web-dot="'.$i.'" aria-label="Afficher la réalisation '.($i+1).'"'.($i===0?' aria-current="true"':'').'></button>';
    }

    return '<div class="jd-web-proof-slider jd-web-hero-slider" data-jd-web-slider>'
        .'<div class="jd-web-proof-slider__slides">'.$slides.'</div>'
        .'<div class="jd-web-proof-slider__ui">'
            .'<div class="jd-web-proof-slider__dots">'.$dots.'</div>'
            .'<div class="jd-web-proof-slider__arrows">'
                .'<button type="button" class="jd-web-proof-slider__arrow" data-jd-web-prev aria-label="Site précédent">←</button>'
                .'<button type="button" class="jd-web-proof-slider__arrow" data-jd-web-next aria-label="Site suivant">→</button>'
            .'</div>'
        .'</div>'
    .'</div>';
}

function jd_core_web_hero_21632(){
    $rating='<a class="jd-web-hero__rating" href="'.esc_url(home_url('/#avis')).'">'
        .'<span aria-hidden="true">★★★★★</span>'
        .'<strong>5,0 Google</strong>'
        .'<em>16 avis clients</em>'
    .'</a>';

    return '<section class="jd-web-hero-v2" aria-labelledby="jd-web-hero-title">'
        .'<div class="jd-web-hero-v2__inner">'
            .'<div class="jd-web-hero-v2__copy">'
                .'<p class="jd-eyebrow">WEB / WORDPRESS</p>'
                .'<h1 id="jd-web-hero-title">Création ou refonte<br>de votre site internet.</h1>'
                .'<p class="jd-web-hero-v2__intro">Je crée ou refonds votre site pour qu’il soit clair, rapide et adapté au mobile, avec un design professionnel pensé pour donner confiance et faciliter les prises de contact.</p>'
                .'<div class="jd-web-hero-v2__proof">'
                    .'<h2>Découvrez quelques sites que nous avons créés.</h2>'
                    .'<p>Nos clients sont satisfaits.</p>'
                    .$rating
                .'</div>'
                .'<a class="jd-web-hero-v2__cta" href="#tarifs">Découvrir nos tarifs '.jd_core_web_pricing_arrow_21626().'</a>'
            .'</div>'
            .'<div class="jd-web-hero-v2__visual">'
                .jd_core_web_portfolio_slider_21632()
            .'</div>'
        .'</div>'
    .'</section>';
}

function jd_core_web_portfolio_gallery_21628(){
    $items=jd_core_web_portfolio_items_2163();
    if(!$items) return '';

    $slides='';
    $dots='';
    foreach($items as $i=>$item){
        $url=(string)$item['url'];
        $shot=jd_core_web_portfolio_shot_2163($item);
        $active=$i===0?' is-active':'';
        $hidden=$i===0?'false':'true';
        $loading=$i===0?'eager':'lazy';

        $slides.='<a class="jd-web-proof-slider__slide'.$active.'" data-jd-web-slide="'.$i.'" href="'.esc_url($url).'" target="_blank" rel="noopener noreferrer" aria-hidden="'.$hidden.'" aria-label="Visiter '.esc_attr($item['name']).'">'
            .'<span class="jd-web-proof-slider__browser">'
                .'<span class="jd-web-proof-slider__bar" aria-hidden="true"><i></i><i></i><i></i></span>'
                .'<img src="'.esc_url($shot).'" alt="Aperçu du site '.esc_attr($item['name']).'" loading="'.$loading.'" decoding="async">'
            .'</span>'
        .'</a>';

        $dots.='<button type="button" class="jd-web-proof-slider__dot'.$active.'" data-jd-web-dot="'.$i.'" aria-label="Afficher la réalisation '.($i+1).'"'.($i===0?' aria-current="true"':'').'></button>';
    }

    $rating='<a class="jd-web-proof__rating" href="'.esc_url(home_url('/#avis')).'">'
        .'<span aria-hidden="true">★★★★★</span><strong>5,0 Google</strong><em>16 avis clients</em>'
    .'</a>';

    return '<section class="jd-web-proof" aria-labelledby="jd-web-proof-title">'
        .'<div class="jd-web-proof__inner">'
            .'<div class="jd-web-proof__copy">'
                .'<p class="jd-eyebrow">RÉALISATIONS WEB</p>'
                .'<h2 id="jd-web-proof-title">Quelques sites que nous avons créés.</h2><p class="jd-web-proof__satisfaction">Et nos clients sont satisfaits.</p>'
                .$rating

            .'</div>'
            .'<div class="jd-web-proof-slider" data-jd-web-slider>'
                .'<div class="jd-web-proof-slider__slides">'.$slides.'</div>'
                .'<div class="jd-web-proof-slider__ui">'
                    .'<div class="jd-web-proof-slider__dots">'.$dots.'</div>'
                    .'<div class="jd-web-proof-slider__arrows">'
                        .'<button type="button" class="jd-web-proof-slider__arrow" data-jd-web-prev aria-label="Site précédent">←</button>'
                        .'<button type="button" class="jd-web-proof-slider__arrow" data-jd-web-next aria-label="Site suivant">→</button>'
                    .'</div>'
                .'</div>'
            .'</div>'
        .'</div>'
    .'</section>';
}



/**
 * John Design Core 2.16.26 — tarifs Site internet.
 * Première lecture volontairement courte, détails à la demande et vocabulaire
 * compréhensible sans jargon technique.
 */
function jd_core_web_pricing_arrow_21626(){
    return '<span class="jd-cta-arrow" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false"><path d="M7 17L17 7"></path><path d="M9 7H17V15"></path></svg></span>';
}

function jd_core_web_pricing_21626(){
    $plans=[
        [
            'class'=>'',
            'label'=>'ESSENTIEL',
            'title'=>'Une présence simple et pro.',
            'price'=>'990 €',
            'intro'=>'Pour un artisan, un indépendant ou une petite activité qui veut être bien présenté en ligne.',
            'project'=>'Site Essentiel - à partir de 990 €',
            'highlights'=>[
                'Une page claire et personnalisée',
                'Adapté mobile, tablette et ordinateur',
                'Formulaire de contact + mise en ligne',
            ],
            'items'=>[
                ['label'=>'Design personnalisé','help'=>'Pas de modèle générique : la page est adaptée à votre activité et à votre image.'],
                ['label'=>'WordPress + Divi','help'=>'Une base professionnelle que votre site pourra faire évoluer.'],
                ['label'=>'Adapté à tous les écrans','help'=>'Le site se réorganise proprement sur ordinateur, tablette et téléphone.'],
                ['label'=>'Formulaire de contact','help'=>'Vos visiteurs peuvent vous écrire directement depuis le site.'],
                ['label'=>'Préparation pour Google','help'=>'Titres, structure de page et réglages essentiels pour aider Google à comprendre votre activité.'],
                ['label'=>'Mise en ligne','help'=>'Je m’occupe des réglages techniques jusqu’à l’ouverture du site.'],
            ],
        ],
        [
            'class'=>' is-featured',
            'label'=>'VITRINE',
            'badge'=>'LE PLUS CHOISI',
            'title'=>'Pour présenter votre entreprise.',
            'price'=>'1 490 €',
            'intro'=>'Pour expliquer clairement vos services, rassurer vos visiteurs et faciliter les demandes de contact.',
            'project'=>'Site Vitrine - à partir de 1 490 €',
            'highlights'=>[
                'Jusqu’à environ 5 pages',
                'Design personnalisé',
                'Préparation Google + statistiques',
            ],
            'items'=>[
                ['label'=>'Jusqu’à environ 5 pages','help'=>'Par exemple : accueil, services, à propos, réalisations et contact.'],
                ['label'=>'WordPress + Divi','help'=>'Un site professionnel, simple à faire évoluer dans le temps.'],
                ['label'=>'Adapté à tous les écrans','help'=>'La mise en page est travaillée pour ordinateur, tablette et téléphone.'],
                ['label'=>'Formulaire de contact','help'=>'Un parcours simple pour transformer une visite en demande.'],
                ['label'=>'Préparation pour Google','help'=>'Titres, structure des pages et réglages essentiels pour que Google comprenne correctement votre activité.'],
                ['label'=>'Statistiques de fréquentation','help'=>'Pour savoir combien de personnes visitent votre site et quelles pages elles consultent.'],
                ['label'=>'Suivi Google','help'=>'Pour vérifier que Google voit correctement votre site et suivre sa présence dans les recherches.'],
                ['label'=>'Mise en ligne & prise en main','help'=>'Le site est livré prêt à fonctionner et je vous explique l’essentiel.'],
            ],
        ],
        [
            'class'=>'',
            'label'=>'VITRINE +',
            'title'=>'Pour un site plus complet.',
            'price'=>'1 990 €',
            'intro'=>'Pour une activité avec davantage de services, de contenus ou un besoin de visibilité locale plus poussé.',
            'project'=>'Site Vitrine Plus - à partir de 1 990 €',
            'highlights'=>[
                'Environ 8 à 10 pages',
                'Réalisations, actualités ou blog si besoin',
                'Travail local plus poussé pour Google',
            ],
            'items'=>[
                ['label'=>'Environ 8 à 10 pages','help'=>'Pour détailler plusieurs services, métiers ou secteurs d’intervention.'],
                ['label'=>'Contenus évolutifs','help'=>'Réalisations, actualités ou blog peuvent être ajoutés si votre activité en a besoin.'],
                ['label'=>'Formulaires plus avancés','help'=>'Pour recueillir des demandes plus précises selon votre activité.'],
                ['label'=>'Adapté à tous les écrans','help'=>'Chaque page est pensée pour rester claire sur mobile, tablette et ordinateur.'],
                ['label'=>'Préparation Google renforcée','help'=>'Structure et contenus travaillés plus finement, notamment pour votre activité et votre zone géographique.'],
                ['label'=>'Statistiques + suivi Google','help'=>'Mesure des visites et contrôle de la bonne prise en compte du site par Google.'],
                ['label'=>'Accompagnement renforcé','help'=>'Plus de contenu à organiser, intégrer et ajuster avec vous.'],
            ],
        ],
    ];

    $cards='';
    foreach($plans as $plan){
        $highlights='';
        foreach($plan['highlights'] as $highlight){
            $highlights.='<li><span aria-hidden="true">•</span><strong>'.esc_html($highlight).'</strong></li>';
        }

        $items='';
        foreach($plan['items'] as $item){
            $items.='<li>'
                .'<span class="jd-web-pricing__check" aria-hidden="true">✓</span>'
                .'<span><strong>'.esc_html($item['label']).'</strong><small>'.esc_html($item['help']).'</small></span>'
            .'</li>';
        }

        $badge=!empty($plan['badge'])
            ?'<span class="jd-web-pricing__badge">'.esc_html($plan['badge']).'</span>'
            :'';

        $contact=add_query_arg('project',$plan['project'],home_url('/contact/'));

        $cards.='<article class="jd-web-pricing__card'.esc_attr($plan['class']).'">'
            .'<div class="jd-web-pricing__card-top">'
                .'<div><p class="jd-web-pricing__label">'.esc_html($plan['label']).'</p>'.$badge.'</div>'
                .'<h3>'.esc_html($plan['title']).'</h3>'
                .'<p class="jd-web-pricing__price"><span>À partir de</span><strong>'.esc_html($plan['price']).'</strong></p>'
                .'<p class="jd-web-pricing__intro">'.esc_html($plan['intro']).'</p>'
            .'</div>'
            .'<ul class="jd-web-pricing__highlights">'.$highlights.'</ul>'
            .'<details class="jd-web-pricing__card-details">'
                .'<summary><span>Voir ce qui est compris</span><span class="jd-web-pricing__detail-plus" aria-hidden="true">+</span></summary>'
                .'<ul class="jd-web-pricing__features">'.$items.'</ul>'
            .'</details>'
            .'<a class="jd-web-pricing__cta" href="'.esc_url($contact).'">Parler de cette formule '.jd_core_web_pricing_arrow_21626().'</a>'
        .'</article>';
    }

    $specific=add_query_arg('project','Projet de site spécifique - sur devis',home_url('/contact/'));
    $subscription=add_query_arg('project','Site vitrine en abonnement - 290 € + 89 €/mois pendant 24 mois',home_url('/contact/'));

    return '<section class="jd-web-pricing" id="tarifs" aria-labelledby="jd-web-pricing-title">'
        .'<div class="jd-web-pricing__inner">'
            .'<header class="jd-web-pricing__head">'
                .'<div>'
                    .'<p class="jd-eyebrow">TARIFS SITE INTERNET</p>'
                    .'<h2 id="jd-web-pricing-title">Des prix clairs.<br>Pas de jargon.</h2>'
                .'</div>'
                .'<p>Vous voyez rapidement le budget à prévoir. Le devis final précise ensuite exactement ce qui est compris pour votre projet.</p>'
            .'</header>'
            .'<div class="jd-web-pricing__grid">'.$cards.'</div>'
            .'<div class="jd-web-pricing__specific">'
                .'<div>'
                    .'<p class="jd-web-pricing__label">PROJET SPÉCIFIQUE</p>'
                    .'<h3>Votre besoin sort du cadre classique ?</h3>'
                    .'<p>Boutique en ligne, réservation, espace privé, plusieurs langues ou fonctionnalité particulière : on définit le besoin avant de chiffrer.</p>'
                    .'<div class="jd-web-pricing__specific-tags"><span>E-commerce</span><span>Réservation</span><span>Espace privé</span><span>Multilingue</span></div>'
                .'</div>'
                .'<div class="jd-web-pricing__specific-action">'
                    .'<strong>Sur devis</strong>'
                    .'<a href="'.esc_url($specific).'">Parler de mon projet '.jd_core_web_pricing_arrow_21626().'</a>'
                .'</div>'
            .'</div>'
            .'<details class="jd-web-pricing__more">'
                .'<summary>'
                    .'<span><strong>Les termes expliqués simplement</strong><small>Google, statistiques, mobile et maintenance : ce que cela veut vraiment dire.</small></span>'
                    .'<span class="jd-web-pricing__plus" aria-hidden="true">+</span>'
                .'</summary>'
                .'<div class="jd-web-pricing__more-body">'
                    .'<div class="jd-web-pricing__explain"><span class="jd-web-pricing__number">01</span><h3>Préparation pour Google</h3><p>Titres, structure des pages et réglages essentiels pour que Google comprenne correctement votre activité, vos services et votre zone.</p><p class="jd-web-pricing__fine">Cela crée de bonnes bases de référencement, sans promettre une première place dans les résultats.</p></div>'
                    .'<div class="jd-web-pricing__explain"><span class="jd-web-pricing__number">02</span><h3>Statistiques de fréquentation</h3><p>Pour savoir combien de personnes visitent votre site, d’où elles arrivent et quelles pages elles consultent.</p></div>'
                    .'<div class="jd-web-pricing__explain"><span class="jd-web-pricing__number">03</span><h3>Suivi Google</h3><p>Un outil permet de vérifier que Google voit correctement votre site et de suivre sa présence dans les recherches.</p></div>'
                    .'<div class="jd-web-pricing__explain"><span class="jd-web-pricing__number">04</span><h3>Adapté à tous les écrans</h3><p>Votre site est conçu pour rester lisible et agréable sur ordinateur, tablette et téléphone. On parle parfois de site « responsive ».</p></div>'
                    .'<div class="jd-web-pricing__explain"><span class="jd-web-pricing__number">05</span><h3>Maintenance</h3><p>Après la mise en ligne, je peux continuer à gérer les mises à jour, sauvegardes, vérifications et petites corrections. La formule est présentée plus bas sur cette page.</p></div>'
                .'</div>'
            .'</details>'
            .'<div class="jd-web-pricing__subscription">'
                .'<div>'
                    .'<p class="jd-web-pricing__label">UNE AUTRE FAÇON DE DÉMARRER</p>'
                    .'<h3>Vous préférez lisser votre investissement ?</h3>'
                    .'<p>Un site vitrine avec hébergement et maintenance inclus, sans régler toute la création en une seule fois.</p>'
                .'</div>'
                .'<div class="jd-web-pricing__subscription-action">'
                    .'<p><strong>290 €</strong><span>mise en service</span></p>'
                    .'<i aria-hidden="true">+</i>'
                    .'<p><strong>89 € / mois</strong><span>pendant 24 mois</span></p>'
                    .'<a href="'.esc_url($subscription).'">Découvrir cette formule '.jd_core_web_pricing_arrow_21626().'</a>'
                .'</div>'
                .'<p class="jd-web-pricing__subscription-note">Après 24 mois, le site vous appartient. Vous pouvez arrêter ou continuer uniquement la maintenance.</p>'
            .'</div>'
            .'<p class="jd-web-pricing__legal">Tarifs indicatifs « à partir de ». Le contenu exact et le prix sont toujours validés dans un devis avant de commencer.</p>'
        .'</div>'
    .'</section>';
}

function jd_core_web_layout_render_2163($block_content,$block){
    if(!is_page('creation-site-internet')) return $block_content;
    if(($block['blockName']??'')!=='johndesign/section') return $block_content;

    if(stripos($block_content,'<h1')!==false){
        return jd_core_web_hero_21632();
    }

    $plain=jd_core_web_layout_plain_2163($block_content);
    $type=jd_core_web_layout_type_2163($plain);

    if($type==='remove-studio' || $type==='remove-related') return '';

    if($type==='why'){
        return '';
    }

    if($type==='scope'){
        return jd_core_web_pricing_21626();
    }

    if($type==='start'){
        return jd_core_web_layout_add_class_2163(
            $block_content,
            'jd-web-flow-section jd-web-flow-pink'
        );
    }

    if($type==='faq'){
        $faq=jd_core_web_layout_add_class_2163(
            $block_content,
            'jd-web-flow-section jd-web-flow-card jd-web-flow-faq'
        );

        return '<div class="jd-web-maintenance-injected jd-web-maintenance-in-flow">'
            .jd_core_web_maintenance_2151()
            .'</div>'
            .$faq;
    }

    if($type==='cta'){
        return jd_core_web_layout_add_class_2163(
            $block_content,
            'jd-web-flow-section jd-web-flow-soft jd-web-flow-cta'
        );
    }

    return $block_content;
}
add_filter('render_block','jd_core_web_layout_render_2163',70,2);
