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
    <script type="text/javascript">
        apiBase = '/bgtld/api/v1/';
    </script>
    <script type="text/javascript" src="assets/components/jquery/jquery.min.js"></script>
    <script type="text/javascript" src="assets/components/leaflet/dist/leaflet.js"></script>
    <script type="text/javascript" src="assets/components/leaflet.markercluster/dist/leaflet.markercluster.js"></script>
    <script type="text/javascript" src="assets/components/handlebars/handlebars.min.js"></script>
    <script type="text/javascript" src="assets/build/js/app.js"></script>
    <link rel="shortcut icon" href="assets/images/favicon.ico" />
</head>
<body>
    <div class="sidebar js-sidebar">
        <div class="sidebar-content">
            <div class="building-details js-building-details"></div>
        </div>
    </div>
    <div class="filter js-filter">
        <img src="assets/images/logo.png" class="logo" alt="" />
        <span class="btn btn-text filter-text">Ik zoek een pand </span>
        <span class="filter-query js-query">
            <button class="js-add-filter btn btn-primary"><i class="icon-right-open"></i></button>
        </span>
    </div>
    <div class="js-map map" id="map"></div>
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
                                <li><button class="filters-choice filters-choice-parent js-show-children" data-target=".js-filters-type">met als functie ...</button></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="filters-list filters-list-children js-filters-year">
                            <button class="filters-choice filters-choice-choosen js-add-filter"><i class="icon-left-open"></i> met een bouwjaar ...</button>
                            <ul>
                                <li><button class="filters-choice js-set-choice" data-category="year" data-text="<i class='icon-back-in-time'></i> bouwjaar voor 1906" data-value="minbouwjaar=0&amp;maxbouwjaar=1905">voor 1906</button></li>
                                <li><button class="filters-choice js-set-choice" data-category="year" data-text="<i class='icon-back-in-time'></i> bouwjaar van 1906 tot 1930" data-value="minbouwjaar=1906&amp;maxbouwjaar=1929">van 1906 tot 1930</button></li>
                                <li><button class="filters-choice js-set-choice" data-category="year" data-text="<i class='icon-back-in-time'></i> bouwjaar van 1930 tot 1980" data-value="minbouwjaar=1930&amp;maxbouwjaar=1979">van 1930 tot 1980</button></li>
                                <li><button class="filters-choice js-set-choice" data-category="year" data-text="<i class='icon-back-in-time'></i> bouwjaar van 1980 tot 1990" data-value="minbouwjaar=1980&amp;maxbouwjaar=1989">van 1980 tot 1990</button></li>
                                <li><button class="filters-choice js-set-choice" data-category="year" data-text="<i class='icon-back-in-time'></i> bouwjaar van 1990 tot 2000" data-value="minbouwjaar=1990&amp;maxbouwjaar=1999">van 1990 tot 2000</button></li>
                                <li><button class="filters-choice js-set-choice" data-category="year" data-text="<i class='icon-back-in-time'></i> bouwjaar van 2000 tot 2010" data-value="minbouwjaar=2000&amp;maxbouwjaar=2009">van 2000 tot 2010</button></li>
                                <li><button class="filters-choice js-set-choice" data-category="year" data-text="<i class='icon-back-in-time'></i> bouwjaar vanaf 2010" data-value="minbouwjaar=2010&amp;maxbouwjaar=9999">vanaf 2010</button></li>
                            </ul>
                        </div>
                        <div class="filters-list filters-list-children js-filters-woz">
                            <button class="filters-choice filters-choice-choosen js-add-filter"><i class="icon-left-open"></i> met een WOZ-waarde van ...</button>
                            <ul>
                                <li><button class="filters-choice js-set-choice" data-category="woz" data-text="<i class='icon-dollar'></i> WOZ-waarde van € 0,- en € 100.000,-" data-value="minwaarde=0&amp;maxwaarde=100000">€ 0,- en € 100.000,-</button></li>
                                <li><button class="filters-choice js-set-choice" data-category="woz" data-text="<i class='icon-dollar'></i> WOZ-waarde van € 100.000,- en € 150.000,-" data-value="minwaarde=100000&amp;maxwaarde=150000">€ 100.000,- en € 150.000,-</button></li>
                                <li><button class="filters-choice js-set-choice" data-category="woz" data-text="<i class='icon-dollar'></i> WOZ-waarde van € 150.000,- en € 200.000,-" data-value="minwaarde=150000&amp;maxwaarde=200000">€ 150.000,- en € 200.000,-</button></li>
                                <li><button class="filters-choice js-set-choice" data-category="woz" data-text="<i class='icon-dollar'></i> WOZ-waarde van € 200.000,- en € 300.000,-" data-value="minwaarde=200000&amp;maxwaarde=300000">€ 200.000,- en € 300.000,-</button></li>
                                <li><button class="filters-choice js-set-choice" data-category="woz" data-text="<i class='icon-dollar'></i> WOZ-waarde van € 300.000,- en € 500.000,-" data-value="minwaarde=300000&amp;maxwaarde=500000">€ 300.000,- en € 500.000,-</button></li>
                                <li><button class="filters-choice js-set-choice" data-category="woz" data-text="<i class='icon-dollar'></i> WOZ-waarde van € 500.000,- en € 1.000.000,-" data-value="minwaarde=500000&amp;maxwaarde=1000000">€ 500.000,- en € 1.000.000,-</button></li>
                                <li><button class="filters-choice js-set-choice" data-category="woz" data-text="<i class='icon-dollar'></i> WOZ-waarde van € 1.000.000,- en € 2.000.000,-" data-value="minwaarde=1000000&amp;maxwaarde=2000000">€ 1.000.000,- en € 2.000.000,-</button></li>
                                <li><button class="filters-choice js-set-choice" data-category="woz" data-text="<i class='icon-dollar'></i> WOZ-waarde van € 2.000.000,- en meer" data-value="minwaarde=2000000&amp;maxwaarde=99999999999">€ 2.000.000,- en meer</button></li>
                            </ul>
                        </div>
                        <div class="filters-list filters-list-children js-filters-used">
                            <button class="filters-choice filters-choice-choosen js-add-filter"><i class="icon-left-open"></i> wat ... gebruikt wordt</button>
                            <ul>
                                <li><button class="filters-choice js-set-choice" data-category="used" data-text="<i class='icon-star-empty'></i> niet in gebruik" data-value="ingebruik=0">niet</button></li>
                                <li><button class="filters-choice js-set-choice" data-category="used" data-text="<i class='icon-star'></i> in gebruik" data-value="ingebruik=Pand in gebruik">wel</button></li>
                            </ul>
                        </div>
                        <div class="filters-list filters-list-children js-filters-type">
                            <button class="filters-choice filters-choice-choosen js-add-filter"><i class="icon-left-open"></i> met als functie ...</button>
                            <ul>
                                <li><button class="filters-choice js-set-choice" data-category="type" data-text="<i class='icon-woon'></i> woning" data-value="type=woonfunctie"><i class="icon-woon"></i> Woning</button></li>
                                <li><button class="filters-choice js-set-choice" data-category="type" data-text="<i class='icon-winkel'></i> winkel" data-value="type=winkelfunctie"><i class="icon-winkel"></i> Winkel</button></li>
                                <li><button class="filters-choice js-set-choice" data-category="type" data-text="<i class='icon-cel'></i> cel" data-value="type=celfunctie"><i class="icon-cel"></i> Cel</button></li>
                                <li><button class="filters-choice js-set-choice" data-category="type" data-text="<i class='icon-kantoor'></i> kantoor" data-value="type=kantoorfunctie"><i class="icon-kantoor"></i> Kantoor</button></li>
                                <li><button class="filters-choice js-set-choice" data-category="type" data-text="<i class='icon-gezondheidszorg'></i> gezondheidszorg" data-value="type=gezondheidszorgfunctie"><i class="icon-gezondheidszorg"></i> Gezondheidszorg</button></li>
                                <li><button class="filters-choice js-set-choice" data-category="type" data-text="<i class='icon-logies'></i> logies" data-value="type=logiesfunctie"><i class="icon-logies"></i> Logies</button></li>
                                <li><button class="filters-choice js-set-choice" data-category="type" data-text="<i class='icon-industrie'></i> industrie" data-value="type=industriefunctie"><i class="icon-industrie"></i> Industrie</button></li>
                                <li><button class="filters-choice js-set-choice" data-category="type" data-text="<i class='icon-bijeenkomst'></i> bijeenkomst" data-value="type=bijeenkomstfunctie"><i class="icon-bijeenkomst"></i> Bijeenkomst</button></li>
                                <li><button class="filters-choice js-set-choice" data-category="type" data-text="<i class='icon-onderwijs'></i> onderwijs" data-value="type=onderwijsfunctie"><i class="icon-onderwijs"></i> Onderwijs</button></li>
                                <li><button class="filters-choice js-set-choice" data-category="type" data-text="<i class='icon-overige'></i> overig" data-value="type=overige functie"><i class="icon-overige"></i> Overig</button></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="js-loader loader"></div>
</body>
</html>
