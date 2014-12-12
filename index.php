<?php
$buildingsCall = 'http://dimitri.dev.freshheads.local/bgtld/api/v1/panden';
$buildingDetailsCall = 'http://dimitri.dev.freshheads.local/bgtld/api/v1/panden/';
$companyDetailsCall = 'http://dimitri.dev.freshheads.local/bgtld/api/v1/company/';
?>
<!DOCTYPE html>

<!--[if lt IE 8]><html xmlns:ng="http://angularjs.org" class="no-js lt-ie10 lt-ie9 lt-ie8" lang="nl"> <![endif]-->
<!--[if IE 8]>        <html xmlns:ng="http://angularjs.org" class="no-js ie8 lt-ie10 lt-ie9" lang="nl"> <![endif]-->
<!--[if IE 9]>        <html xmlns:ng="http://angularjs.org" class="no-js ie9 lt-ie10" lang="nl"> <![endif]-->
<!--[if gt IE 9]><!--><html class="no-js" lang="nl"><!--<![endif]-->
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="viewport" content="width=1024" />
    <meta name="apple-touch-fullscreen" content="yes" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <title>BGTLD</title>
    <link rel="stylesheet" href="assets/components/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="assets/icon-font/css/fontello.css" />
    <link rel="stylesheet" href="assets/icon-font/css/animation.css" />
    <link rel="stylesheet" href="assets/components/leaflet.markercluster/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="assets/components/leaflet.markercluster/dist/MarkerCluster.Default.css" />

    <link rel="stylesheet" href="assets/build/css/app.css" />
    <script type="text/javascript" src="assets/components/jquery/jquery.min.js"></script>
    <script type="text/javascript" src="assets/components/leaflet/dist/leaflet.js"></script>
    <script type="text/javascript" src="assets/components/leaflet.markercluster/dist/leaflet.markercluster.js"></script>
    <script type="text/javascript" src="assets/components/handlebars/handlebars.min.js"></script>
    <script type="text/javascript" src="assets/build/js/app.js"></script>
    <link rel="shortcut icon" href="/favicon.ico" />
</head>
<body>
<div class="sidebar js-sidebar">
    <div class="sidebar-content">
        <div class="building-details js-building-details"></div>
    </div>
</div>
<div class="filter js-filter">
    <span class="btn btn-text filter-text">Ik zoek een pand </span>
    <span class="filter-query js-query">
        <button class="js-add-filter btn btn-primary"><i class="icon-right-open"></i></button>
    </span>
</div>
<div class="js-map map" id="map" data-query-url="<?php echo $buildingsCall; ?>" data-detail-url="<?php echo $buildingDetailsCall; ?>"></div>
<div class="filters js-filters">
    <div class="filters-content">
        <div class="filters-holder js-filter-holder">
            <div class="row row-borderless">
                <div class="col-sm-6">
                    <div class="filters-list filters-all js-filters-all">
                        <ul>
                            <li><button class="filters-choice filters-choice-parent js-show-children" data-target=".js-filters-year">met een bouwjaar ...</button></li>
                            <li><button class="filters-choice filters-choice-parent js-show-children" data-target=".js-filters-woz">met een WOZ-waarde van ...</button></li>
                            <li><button class="filters-choice filters-choice-parent js-show-children" data-target=".js-filters-used">wat ... gebruikt wordt</button></li>
                            <li><button class="filters-choice filters-choice-parent js-show-children" data-target=".js-filters-functie">met als functie ...</button></li>
                        </ul>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="filters-list filters-list-children js-filters-year">
                        <button class="filters-choice filters-choice-choosen js-add-filter"><i class="icon-left-open"></i> met een bouwjaar ...</button>
                        <ul>
                            <li><button class="filters-choice js-set-choice" data-category="year" data-text="<i class='icon-back-in-time'></i> bouwjaar voor 1906" data-value="0">voor 1906</button></li>
                            <li><button class="filters-choice js-set-choice" data-category="year" data-text="<i class='icon-back-in-time'></i> bouwjaar van 1906 tot 1930" data-value="0">van 1906 tot 1930</button></li>
                            <li><button class="filters-choice js-set-choice" data-category="year" data-text="<i class='icon-back-in-time'></i> bouwjaar van 1930 tot 1980" data-value="0">van 1930 tot 1980</button></li>
                            <li><button class="filters-choice js-set-choice" data-category="year" data-text="<i class='icon-back-in-time'></i> bouwjaar van 1980 tot 1990" data-value="0">van 1980 tot 1990</button></li>
                            <li><button class="filters-choice js-set-choice" data-category="year" data-text="<i class='icon-back-in-time'></i> bouwjaar van 1990 tot 2000" data-value="0">van 1990 tot 2000</button></li>
                            <li><button class="filters-choice js-set-choice" data-category="year" data-text="<i class='icon-back-in-time'></i> bouwjaar van 2000 tot 2010" data-value="0">van 2000 tot 2010</button></li>
                            <li><button class="filters-choice js-set-choice" data-category="year" data-text="<i class='icon-back-in-time'></i> bouwjaar vanaf 2010" data-value="0">vanaf 2010</button></li>
                        </ul>
                    </div>
                    <div class="filters-list filters-list-children js-filters-woz">
                        <button class="filters-choice filters-choice-choosen js-add-filter"><i class="icon-left-open"></i> met een WOZ-waarde van ...</button>
                        <ul>
                            <li><button class="filters-choice js-set-choice" data-category="woz" data-text="<i class='icon-dollar'></i> WOZ-waarde van € 0,- en € 100.00,-" data-value="0">€ 0,- en € 100.000,-</button></li>
                            <li><button class="filters-choice js-set-choice" data-category="woz" data-text="<i class='icon-dollar'></i> WOZ-waarde van € 100.000,- en € 150.00,-" data-value="1">€ 100.000,- en € 150.000,-</button></li>
                            <li><button class="filters-choice js-set-choice" data-category="woz" data-text="<i class='icon-dollar'></i> WOZ-waarde van € 150.000,- en € 200.00,-" data-value="2">€ 150.000,- en € 200.000,-</button></li>
                            <li><button class="filters-choice js-set-choice" data-category="woz" data-text="<i class='icon-dollar'></i> WOZ-waarde van € 200.000,- en € 300.00,-" data-value="3">€ 200.000,- en € 300.000,-</button></li>
                            <li><button class="filters-choice js-set-choice" data-category="woz" data-text="<i class='icon-dollar'></i> WOZ-waarde van € 300.000,- en € 500.00,-" data-value="4">€ 300.000,- en € 500.000,-</button></li>
                            <li><button class="filters-choice js-set-choice" data-category="woz" data-text="<i class='icon-dollar'></i> WOZ-waarde van € 500.000,- en meer" data-value="5">€ 500.000,- en meer</button></li>
                        </ul>
                    </div>
                    <div class="filters-list filters-list-children js-filters-used">
                        <button class="filters-choice filters-choice-choosen js-add-filter"><i class="icon-left-open"></i> wat ... gebruikt wordt</button>
                        <ul>
                            <li><button class="filters-choice js-set-choice" data-category="used" data-text="<i class='icon-star-empty'></i> niet in gebruik" data-value="0">niet</button></li>
                            <li><button class="filters-choice js-set-choice" data-category="used" data-text="<i class='icon-star'></i> in gebruik" data-value="1">wel</button></li>
                        </ul>
                    </div>
                    <div class="filters-list filters-list-children js-filters-functie">
                        <button class="filters-choice filters-choice-choosen js-add-filter"><i class="icon-left-open"></i> met als functie ...</button>
                        <ul>
                            <li><button class="filters-choice js-set-choice" data-category="function" data-text="<i class='icon-woon'></i> woning" data-value="0"><i class="icon-woon"></i> Woning</button></li>
                            <li><button class="filters-choice js-set-choice" data-category="function" data-text="<i class='icon-winkel'></i> winkel" data-value="1"><i class="icon-winkel"></i> Winkel</button></li>
                            <li><button class="filters-choice js-set-choice" data-category="function" data-text="<i class='icon-cel'></i> cel" data-value="2"><i class="icon-cel"></i> Cel</button></li>
                            <li><button class="filters-choice js-set-choice" data-category="function" data-text="<i class='icon-kantoor'></i> kantoor" data-value="3"><i class="icon-kantoor"></i> Kantoor</button></li>
                            <li><button class="filters-choice js-set-choice" data-category="function" data-text="<i class='icon-gezondheidszorg'></i> gezondheidszorg" data-value="4"><i class="icon-gezondheidszorg"></i> Gezondheidszorg</button></li>
                            <li><button class="filters-choice js-set-choice" data-category="function" data-text="<i class='icon-logies'></i> logies" data-value="5"><i class="icon-logies"></i> Logies</button></li>
                            <li><button class="filters-choice js-set-choice" data-category="function" data-text="<i class='icon-industrie'></i> industrie" data-value="6"><i class="icon-industrie"></i> Industrie</button></li>
                            <li><button class="filters-choice js-set-choice" data-category="function" data-text="<i class='icon-bijeenkomst'></i> bijeenkomst" data-value="7"><i class="icon-bijeenkomst"></i> Bijeenkomst</button></li>
                            <li><button class="filters-choice js-set-choice" data-category="function" data-text="<i class='icon-onderwijs'></i> onderwijs" data-value="8"><i class="icon-onderwijs"></i> Onderwijs</button></li>
                            <li><button class="filters-choice js-set-choice" data-category="function" data-text="<i class='icon-overige'></i> overig" data-value="9"><i class="icon-overige"></i> Overig</button></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="js-loader loader"></div>
<script class="js-tree-template" type="text/x-handlebars-template">
    <div class="item item-tree clearfix">
        {{#if image}}
            <img src="{{image}}" alt=""  class="item-image" />
        {{/if}}
        <div class="item-details">
            <p class="item-name">{{boomtype}}</p>
            <dl>
                <dt>Nederlandse naam:</dt>
                <dd>{{nedboomsoort}}</dt>
                <dt>Latijnse naam:</dt>
                <dd>{{latboomsoort}}</dt>
                <dt>Diameter</dt>
                <dd>{{diameter}}</dd>
                <dt>Boomhoogte</dt>
                <dd>{{boomhoogte}}</dd>
                <dt>Aanlegjaar</dt>
                <dd>{{aanlegjaar}}<dd>
                <dt>Risicoklasse</dt>
                <dd>{{risicoklasse}}</dd>
                <dt>Boomconditie</dt>
                <dd>{{boomconditie}}</dd>
            </dl>
            <p>
                <strong>DBpedia omschrijving</strong> <a href="http://nl.dbpedia.org/page/Witte_paardenkastanje" target="_blank"><i class="icon-link-ext"></i></a><br/>
                {{description}}
            </p>
        </div>
    </div>
</script>
<script class="js-playset-template" type="text/x-handlebars-template">
    <div class="item">
        <div class="item-details">
            <p class="item-name">{{categorie}}</p>
            <dl>
                <dt>Type:</dt>
                <dd>{{toesteltype}}</dt>
                <dt>Leeftijdscategorie:</dt>
                <dd>{{leeftijdscategorie}}</dt>
                <dt>Aanlegjaar</dt>
                <dd>{{aanlegjaar}}<dd>
            </dl>
        </div>
    </div>
</script>
<script class="js-company-template" type="text/x-handlebars-template">
    <p><a href="{{url}}" target="_blank">Freebase link <i class="icon-link-ext"></i></a></p>
    <p class="company-description">{{description}}</p>
    <a href="{{website}}" target="_blank" class="company-website">{{website}}</a>
    {{#if leadership }}
        <div class="company-leadership">
            <p><strong>Onder leiding van:</strong></p>
            <div class="company-leader clearfix">
                <img src="{{leadership.image}}" alt="" class="company-leader-image" />
                <div class="company-leader-details">
                    <p class="company-leader-name">
                        {{leadership.name}}<br/>
                        <span class="meta">{{leadership.role}}</span>
                    </p>
                    <a href="{{leadership.url}}" target="_blank">Freebase link <i class="icon-link-ext"></i></a>
                </div>
            </div>
        </div>
    {{/if}}
</script>
<script class="js-building-template" type="text/x-handlebars-template">
    <div class="row">
        <div class="col-sm-6">
            <div class="building-details-content">
                <canvas class="building-map js-building-map" height="200"></canvas>
                <div class="building-info">
                    <p>
                        <strong>Adres</strong><br/>
                        {{straatnaam}} {{huisnummer}}<br/>
                        {{postcode}}, {{stad}}
                    </p>
                    <dl class="clearfix">
                        {{#if bouwjaar}}
                            <dt>Bouwjaar</dt>
                            <dd>{{bouwjaar}}</dd>
                        {{/if}}
                        {{#if gebruiksdoel}}
                            <dt>Gebruiksdoel</dt>
                            <dd>{{gebruiksdoel}}</dd>
                        {{/if}}
                        {{#if status}}
                            <dt>Status</dt>
                            <dd>{{status}}</dd>
                        {{/if}}
                        {{#if erfdienstbaarheid}}
                            <dt>Erfdienstbaarheid <a href="#"><i class="icon-link-ext"></i></a></dt>
                            <dd>{{erfdienstbaarheid}}</dd>
                        {{/if}}
                        {{#if cultureelerfgoed}}
                            <dt>Cultureelerfgoed <a href="#"><i class="icon-link-ext"></i></a></dt>
                            <dd>{{cultureelerfgoed}}</dd>
                        {{/if}}
                    </dl>

                    {{#if woz}}
                        <strong>WOZ-objecten</strong> <a href="#"><i class="icon-link-ext"></i></a>

                        <table>
                            {{#each woz}}
                                <tr>
                                    <th>{{type}}</th>
                                    <td>{{waarde}}</td>
                                </tr>
                            {{/each}}
                        </table>
                    {{/if}}

                    <p>
                        {{#if vestigingen}}
                            {{#ifCond vestigingen.length '>' 1}}
                                <button class="btn btn-primary btn-block js-show-organizations">In dit pand zijn {{vestigingen.length}} bedrijven gevestigd <i class="icon-right-open"></i></button>
                            {{else}}
                                <button class="btn btn-primary btn-block js-show-organizations">In dit pand is 1 bedrijf gevestigd <i class="icon-right-open"></i></button>
                            {{/ifCond}}
                        {{else}}
                            In dit pand zijn geen bedrijven gevestigd.
                        {{/if}}
                    </p>

                    <div class="row row-borderless">
                        <div class="col-sm-6">
                            <ul class="list">
                                <li><i class="icon-woon"></i> Woning <span class="meta">({{ verblijfsobjecten.woon }})</span></li>
                                <li><i class="icon-winkel"></i> Winkel <span class="meta">({{ verblijfsobjecten.winkel }})</span></li>
                                <li><i class="icon-cel"></i> Cel <span class="meta">({{ verblijfsobjecten.cel }})</span></li>
                                <li><i class="icon-kantoor"></i> Kantoor <span class="meta">({{ verblijfsobjecten.kantoor }})</span></li>
                                <li><i class="icon-gezondheidszorg"></i> Zorginstelling <span class="meta">({{ verblijfsobjecten.gezondheidszorg }})</span></li>
                                <li><i class="icon-sport"></i> Sport <span class="meta">({{ verblijfsobjecten.sport }})</span></li>
                            </ul>
                        </div>
                        <div class="col-sm-6">
                            <ul class="list">
                                <li><i class="icon-logies"></i> Logies <span class="meta">({{ verblijfsobjecten.logies }})</span></li>
                                <li><i class="icon-industrie"></i> Industrie <span class="meta">({{ verblijfsobjecten.industrie }})</span></li>
                                <li><i class="icon-bijeenkomst"></i> Bijeenkomst <span class="meta">({{ verblijfsobjecten.bijeenkomst }})</span></li>
                                <li><i class="icon-onderwijs"></i> Onderwijs <span class="meta">({{ verblijfsobjecten.onderwijs }})</span></li>
                                <li><i class="icon-overige"></i> Overig <span class="meta">({{ verblijfsobjecten.overige }})</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="building-details-content">
                <p><button class="btn btn-secondary js-show-building"><i class="icon-left-open"></i> Terug naar pand overzicht</button></p>
                {{#if vestigingen}}
                    <ul class="list list-organizations">
                        {{#each vestigingen}}
                            <li>
                                <a href="<?php echo $companyDetailsCall; ?>{{id}}" class="link link-show-organization js-show-organization" data-detail-url="<?php echo $companyDetailsCall; ?>{{id}}">{{naam}}</a>
                            </li>
                        {{/each}}
                    </ul>
                {{/if}}
            </div>
        </div>
    </div>
</script>
</body>
</html>