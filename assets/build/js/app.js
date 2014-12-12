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

$(document).on('ready', function() {
	$.ajax({
		url: 'http://dennis.dev.freshheads.local/bgtld/api/v1/panden',
		success: function(response) {
			console.log(response);
		},
		error: function(response) {
			console.log(response);
		}
	});
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
				.on('click', '.js-show-organizations', function(e) {
					e.preventDefault();

					ui.$sidebarContent.addClass('is-showing-organizations');
				})
				.on('click', '.js-show-building', function(e) {
					e.preventDefault();

					ui.$sidebarContent.removeClass('is-showing-organizations');
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
								var rendered = ui.companyTemplate(response);
								$target.html(rendered);
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
			
			var detailTemplateSource = $('.js-building-template').eq(0).html();
			self.detailTemplate = Handlebars.compile(detailTemplateSource);

			var companyTemplateSource = $('.js-company-template').eq(0).html();
			self.companyTemplate = Handlebars.compile(companyTemplateSource);
		},

		showDetails: function(object) {
			var self = this;

			self.$sidebar.addClass('is-active');
			var rendered = self.detailTemplate(object);
			self.$sidebarContent.html(rendered);

			var $canvas = $('.js-building-map');
			var c = $('.js-building-map').get(0);
			c.width = 400;

			self.ctx = c.getContext('2d');
			self.ctx.scale(0.75,1);

			self.drawBuilding(object.geometrie);
		},

		drawBuilding: function(geometry) {
			var self = this;
			var ctx = self.ctx;

			ctx.clearRect(0, 0, 400, 200);

			var bounds = [0, 0, 0, 0];

			ctx.fillStyle = '#fff';
			ctx.beginPath();

			var buildingCoordinates = JSON.parse(JSON.stringify(geometry));;

			for(var i = 0; i < buildingCoordinates.length; i++) {
				var point = buildingCoordinates[i];

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

			var distanceY = (bounds[2] - bounds[0]);
			var distanceX = (bounds[3] - bounds[1]);
			var ratio = distanceX / distanceY;
			ctx.scale(1,-1);
      		ctx.translate(0,-200);

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

			for(var i = 0; i < buildingCoordinates.length; i++) {
				var point = buildingCoordinates[i];

				point[0] = (point[0] - bounds[0]) * scale;
				point[1] = (point[1] - bounds[1]) * scale;
				
				if(i==0) {
					ctx.lineTo(appendX + point[1], appendY + point[0]);
				} else {
					ctx.lineTo(appendX + point[1], appendY + point[0]);
				}
			}

			ctx.closePath();
			ctx.fill();
			ctx.scale(1,-1);
      		ctx.translate(0,-200);
			ctx.restore();

			if(self.currentBuilding) {
				self.map.removeLayer(self.currentBuilding);
			}

			self.currentBuilding = L.polygon(geometry, {
				color: '#17ba8e',
				fillColor: '#17ba8e',
				fillOpacity: 0.5
			}).addTo(maps.map);
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
			}).setView([52.23, 4.4836127758026], 10);	
			var zoomControl = new L.Control.Zoom({ position: 'bottomleft'} );
            zoomControl.addTo(self.map);
			L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
			    attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://mapbox.com">Mapbox</a>',
			    maxZoom: 18,
			    boxZoom: false
			}).addTo(self.map);

			// self.map.on('click', function(e) {
			// 	building.push([e.latlng.lat,e.latlng.lng]);
			// });
		},

		getResults: function() {
			var self = this;

			loader.setLoader();

			$.ajax({
				url: self.apiUrls.queryUrl,
				crossDomain: true,
				data: {
					year: (query.choices.year ? query.choices.year.value : null),
					woz: (query.choices.woz ? query.choices.woz.value : null),
					used: (query.choices.used ? query.choices.used.value : null),
					functie: (query.choices.functie ? query.choices.functie.value : null)
				},
				dataType: 'json',
				success: function(response) {
					self.results = response.results;
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
				showCoverageOnHover: false,
				disableClusteringAtZoom: 15
			});

			for(var i = self.amountOfMarkers;i; i--){
				var markerData = self.results[i - 1];
				self.markers.addLayer(new L.Marker([markerData.lat,markerData.lng], {
					icon: baseIcon,
					id: markerData.id
				}));
			}

			self.markers.on('click', function(e) {
				setTimeout(function() {
					self.map.invalidateSize();
					self.map.panTo(e.latlng);
					self.map.setZoom(17);
				}, 300);

				$.ajax({
					url: self.apiUrls.detailUrl + e.layer.options.id,
					dataType: 'json',
					success: function(response) {
						ui.$sidebarContent.removeClass('is-showing-organizations');
						ui.showDetails(response);

						$.ajax({
							url: 'assets/data/bomen.json',
							dataType: 'json',
							success: function(response) {
								maps.setTrees(response.results);
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
			
			for(var i = 0; i < self.trees.length; i++) {
				self.map.removeLayer(self.trees[i]);
			}

			var treeTemplateSource = $('.js-tree-template').eq(0).html();
			var treeTemplate = Handlebars.compile(treeTemplateSource);

			for(var i = 0; i < trees.length; i++) {
				var treeData = trees[i];

				var tree = L.polygon(treeData.geometrie, {
					color: false,
					fillColor: '#40c019',
					fillOpacity: 1
				})
					.addTo(self.map)
					.bindPopup(treeTemplate(treeData), {
						maxWidth: 600
					});

				self.trees.push(tree);
			}
		},

		setPlaysets: function(playsets) {
			var self = this;

			for(var i = 0; i < self.playsets.length; i++) {
				self.map.removeLayer(self.playsets[i]);
			}

			var playsetIcon = new self.iconBase({iconUrl: 'assets/images/marker-playground.png'});
			
			var playsetTemplateSource = $('.js-playset-template').eq(0).html();
			var playsetTemplate = Handlebars.compile(playsetTemplateSource);

			for(var i = 0; i < playsets.length; i++) {
				var playsetData = playsets[i];

				var playset = L.marker([playsetData.lat, playsetData.lng], {
					icon: playsetIcon
				})
					.addTo(self.map)
					.bindPopup(playsetTemplate(playsetData), {
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