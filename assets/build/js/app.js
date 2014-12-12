var building = [];

Handlebars.registerHelper('ifCond', function (v1, operator, v2, options) {

    switch (operator) {
        case '==':
            return (v1 == v2) ? options.fn(this) : options.inverse(this);
        case '===':
            return (v1 === v2) ? options.fn(this) : options.inverse(this);
        case '<':
            return (v1 < v2) ? options.fn(this) : options.inverse(this);
        case '<=':
            return (v1 <= v2) ? options.fn(this) : options.inverse(this);
        case '>':
            return (v1 > v2) ? options.fn(this) : options.inverse(this);
        case '>=':
            return (v1 >= v2) ? options.fn(this) : options.inverse(this);
        case '&&':
            return (v1 && v2) ? options.fn(this) : options.inverse(this);
        case '||':
            return (v1 || v2) ? options.fn(this) : options.inverse(this);
        default:
            return options.inverse(this);
    }
});

$.fn.digits = function(){ 
    return this.each(function(){ 
        $(this).text( $(this).text().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1.") ); 
    })
};

$(document).on('ready', function() {
	var links = {
		init: function() {
			$(document)
				.on('click', '.js-add-filter', function(e) {
					e.preventDefault();

					$('.js-filters').fadeIn();
					$('.filters-list').removeClass('is-active');
					$('.js-filter-holder').removeClass('children-shown');
				})
				.on('click', '.js-show-children', function(e) {
					e.preventDefault();

					var $el = $(this),
						elData = $el.data(),
						$target = $(elData.target);

					$('.js-filters').fadeIn();
					$('.filters-list').removeClass('is-active');
					$('.js-filter-holder').addClass('children-shown');
					$target.addClass('is-active');
				})
				.on('click', '.js-set-choice', function(e) {
					e.preventDefault();

					var $el = $(this),
						elData = $el.data();

					query.choices[elData.category] = {
						value: elData.value,
						text: elData.text
					}

					query.refreshChoices();
					$('.js-filters').fadeOut();
					$('.js-filter').addClass('is-filled');
				})
				.on('click', '.js-show-building-subitems', function(e) {
					e.preventDefault();

					var $el = $(this),
						elData = $el.data(),
						$target = $(elData.target);

					ui.$sidebarContent.addClass('is-showing-subitems');

					$('.js-building-subitems').removeClass('is-active');
					$target.addClass('is-active');
				})
				.on('click', '.js-show-building', function(e) {
					e.preventDefault();

					ui.$sidebarContent.removeClass('is-showing-subitems');
				})
				.on('click', '.js-show-organization', function(e) {
					e.preventDefault();

					var $el = $(this),
						elData = $el.data(),
						$target = $el.next('.js-company-details');

					if($target.length) {
						$el.toggleClass('is-showing-details');
					} else {
						$target = $('<div class="company-details js-company-details"></div>').insertAfter($el);
						$.ajax({
							url: elData.detailUrl,
							dataType: 'json',
							success: function(response) {
								$el.addClass('is-showing-details');
								$target.html(ui.companyTemplate(response));
							}
						});
					}
				});
		}
	};

	var query = {
		choices: {
			year: null,
			woz: null,
			used: null,
			functie: null
		},
		$holder: $('.js-query'),

		refreshChoices: function() {
			var self = this,
			choicesLength = Object.keys(self.choices).length;
			var choices = 0;

			self.$holder.empty();

			for(category in self.choices) {
				var choice = self.choices[category];

				if(choice) {
					choices++;
					var $choice = $('<button class="query-item btn js-filter js-show-children" data-target=".js-filters-' + category + '">' + choice.text + '</button>').appendTo(self.$holder);
				}
			}

			if(choices > 1) {
				$choice.before(' en ');
			}

			if(choices < 4) {
				$(' <button class="js-add-filter btn btn-primary"><i class="icon-plus"></i></button>').appendTo(self.$holder);
			}

			setTimeout(function() {
				maps.getResults();
			}, 500);
		}
	};

	var loader = {
		$holder: $('.js-loader'),

		setLoader: function() {
			var self = this;

			self.$holder.addClass('is-loading');
		},

		removeLoader: function() {
			var self = this;

			self.$holder.removeClass('is-loading');
		}
	}

	var ui = {
		$sidebar: $('.js-sidebar'),
		$sidebarContent: $('.js-building-details'),

		init: function() {
			var self = this;

			self.getTemplates();
		},

		getTemplates: function() {
			var self = this;

			$.ajax({
                url: 'assets/templates/building-template.html',
                cache: true,
                success: function (template) {
					var templateSource = template;
					self.detailTemplate = Handlebars.compile(templateSource);
                }
            });
			$.ajax({
                url: 'assets/templates/company-template.html',
                cache: true,
                success: function (template) {
					var templateSource = template;
					self.companyTemplate = Handlebars.compile(templateSource);
                }
            });
			$.ajax({
                url: 'assets/templates/tree-template.html',
                cache: true,
                success: function (template) {
					var templateSource = template;
					self.treeTemplate = Handlebars.compile(templateSource);
                }
            });
			$.ajax({
                url: 'assets/templates/playset-template.html',
                cache: true,
                success: function (template) {
					var templateSource = template;
					self.playsetTemplate = Handlebars.compile(templateSource);
                }
            });

            self.dbpediaTemplate = Handlebars.compile('<strong>DBpedia omschrijving</strong> <a href="{{uri}}" target="_blank"><i class="icon-link-ext"></i></a><br/>{{description}}');
		},

		showDetails: function(object) {
			var self = this;

			self.$sidebar.addClass('is-active');
			self.$sidebarContent.html(ui.detailTemplate(object));

			self.$sidebarContent.find('.currency').digits();

			var $canvas = $('.js-building-map');
			var c = $('.js-building-map').get(0);
			c.width = 400;

			self.ctx = c.getContext('2d');
			self.ctx.scale(0.75,1);

			if(object.geometrie) {
				self.drawBuilding(object.geometrie);
			}
		},

		drawBuilding: function(geometry) {
			var self = this;
			var ctx = self.ctx;

			ctx.clearRect(0, 0, 400, 200);

			var bounds = [0, 0, 0, 0];

			var buildingCoordinates = JSON.parse(JSON.stringify(geometry));

			ctx.scale(1,-1);
      		ctx.translate(0,-200);

			for(var i = 0; i < buildingCoordinates[0].length; i++) {
				var object = buildingCoordinates[0][i];

				for(var j = 0; j < object.length; j++) {
					var point = object[j];

					if(!bounds[0] || point[0] < bounds[0]) {
						bounds[0] = point[0];
					}

					if(!bounds[1] || point[1] < bounds[1]) {
						bounds[1] = point[1];
					}

					if(!bounds[2] || point[0] > bounds[2]) {
						bounds[2] = point[0];
					}

					if(!bounds[3] || point[1] > bounds[3]) {
						bounds[3] = point[1];
					}
				}
			}

			var distanceY = (bounds[2] - bounds[0]);
			var distanceX = (bounds[3] - bounds[1]);
			var ratio = distanceX / distanceY;

			if(ratio < 1) {
				// vertical building
				var scale = 180 / distanceY;
				var appendX = (400 - distanceX * scale) / 2 + 70;
				var appendY = 10;
			} else {
				// horizontal building
				var scale = 380 / distanceX;
				var appendX = 80;
				var appendY = (200 - distanceY * scale) / 2;
			}

			var color = ['#fff', '#97c000', '#97c000', '#97c000'];

			for(var i = 0; i < buildingCoordinates[0].length; i++) {
				var object = buildingCoordinates[0][i];

				ctx.fillStyle = color[i];
				ctx.beginPath();

				for(var j = 0; j < object.length; j++) {
					var point = object[j];

					point[0] = (point[0] - bounds[0]) * scale;
					point[1] = (point[1] - bounds[1]) * scale;
					
					if(j==0) {
						ctx.lineTo(appendX + point[1], appendY + point[0]);
					} else {
						ctx.lineTo(appendX + point[1], appendY + point[0]);
					}
				}

				ctx.closePath();
				ctx.fill();
			}
			ctx.scale(1,-1);
      		ctx.translate(0,-200);
			ctx.restore();

			if(self.currentBuilding) {
				maps.map.removeLayer(self.currentBuilding);
			}

			if(geometry) {
				self.currentBuilding = L.multiPolygon(geometry, {
					stroke: false,
					fillColor: '#97c000',
					fillOpacity: 1
				}).addTo(maps.map);
			}
		}
	}

	var maps = {
		apiUrls: $('.js-map').data(),

		iconBase: L.Icon.extend({
			options: {
				iconUrl:      'assets/images/marker.png',
				iconSize:     [26, 42],
				iconAnchor:   [13, 40],
				shadowUrl:    'assets/images/marker-shade.png',
				shadowSize:   [42, 31],
				shadowAnchor: [7, 25],
				popupAnchor:  [-3, -76]
			}
		}),
		markers: [],
		trees: [],
		playsets: [],

		init: function() {
			var self = this;

			self.map = L.map('map', {
				zoomControl:false
			}).setView([52.15959828480465, 4.505670530026864], 10);	
			var zoomControl = new L.Control.Zoom({ position: 'bottomleft'} );
            zoomControl.addTo(self.map);
			//L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
			L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
			    attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://mapbox.com">Mapbox</a>',
			    maxZoom: 18,
			    boxZoom: false
			}).addTo(self.map);
		},

		getResults: function() {
			var self = this;

			loader.setLoader();

			$.ajax({
				url: apiBase + 'panden' + '?' +
					(query.choices.year ? query.choices.year.value + '&' : '') +
					(query.choices.woz ? query.choices.woz.value + '&' : '') +
					(query.choices.used ? query.choices.used.value + '&' : '') +
					(query.choices.type ? query.choices.type.value + '&' : ''),
				crossDomain: true,
				dataType: 'json',
				success: function(response) {
					self.results = response;
					self.setMarkers();
				}
			});
		},

		setMarkers: function() {
			var self = this;

			self.amountOfMarkers = self.results.length;

			var baseIcon = new self.iconBase();

			if(self.markers) {
				self.map.removeLayer(self.markers);
			}

			self.markers = new L.MarkerClusterGroup({
				spiderfyOnMaxZoom: false,
				showCoverageOnHover: true,
				disableClusteringAtZoom: 17
			});

			for(var i = self.amountOfMarkers;i; i--){
				var markerData = self.results[i - 1];
				if(markerData.lat && markerData.lng) {
					self.markers.addLayer(new L.Marker([markerData.lat,markerData.lng], {
						icon: baseIcon,
						id: markerData.id
					}));
				}
			}

			self.markers.on('click', function(e) {
				setTimeout(function() {
					self.map.invalidateSize();
					self.map.panTo(e.latlng);
					self.map.setZoom(18);
				}, 300);

				$.ajax({
					url: apiBase + 'panden/' + e.layer.options.id,
					dataType: 'json',
					success: function(response) {
						ui.$sidebarContent.removeClass('is-showing-organizations');
						ui.showDetails(response);

						$.ajax({
							url: apiBase + 'bomen?lat=' + e.latlng.lat + '&lng=' + e.latlng.lng,
							dataType: 'json',
							success: function(response) {
								maps.setTrees(response);
							}
						});

						$.ajax({
							url: 'assets/data/speeltoestellen.json',
							dataType: 'json',
							success: function(response) {
								maps.setPlaysets(response.results);
							}
						});
					}
				});
			});
			self.map.addLayer(self.markers);

			loader.removeLoader();
		},

		setTrees: function(trees) {
			var self = this;
			
			if(self.trees) {
				self.map.removeLayer(self.trees);
			}

			var treeIcon = new self.iconBase({
				iconUrl: 'assets/images/marker-tree.png',
				shadowUrl: 'assets/images/marker-shade-tree.png'
			});

			self.trees = L.geoJson(trees, {
			    onEachFeature: function (feature, layer) {
			        layer
			        	.setIcon(treeIcon)
				        .bindPopup(ui.treeTemplate(feature.properties), {
				 			maxWidth: 600
				 		})
				 		.on('click', function(e) {
				 			$.ajax({
				 				url: 'http://lookup.dbpedia.org/api/search.asmx/PrefixSearch?MaxHits=1&QueryClass=Species&QueryString=' + e.target.feature.properties.latboomsoort,
				 				headers: { 'Accept': 'application/json' },
				 				success: function(response) {
				 					if(response.results.length) {
				 						$('.js-dbpedia-description').html(ui.dbpediaTemplate(response.results[0]));
				 						console.log(response.results[0]);
				 					}
				 				}
				 			});
				 		});
			    }
			}).addTo(maps.map);
		},

		setPlaysets: function(playsets) {
			var self = this;

			for(var i = 0; i < self.playsets.length; i++) {
				self.map.removeLayer(self.playsets[i]);
			}

			var playsetIcon = new self.iconBase({iconUrl: 'assets/images/marker-playground.png'});

			for(var i = 0; i < playsets.length; i++) {
				var playsetData = playsets[i];

				var playset = L.marker([playsetData.lat, playsetData.lng], {
					icon: playsetIcon
				})
					.addTo(self.map)
					.bindPopup(ui.playsetTemplate(playsetData), {
						maxWidth: 450
					});

				self.playsets.push(playset);
			}
		}
	};

	ui.init();
	links.init();
	maps.init();
});