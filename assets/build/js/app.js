

var building = [];

$(document).on('ready', function() {
	var links = {
		init: function() {
			$(document).on('click', '.js-add-filter', function(e) {
				e.preventDefault();

				$('.js-filters').fadeIn();
				$('.filters-list').removeClass('is-active');
				$('.js-filters-all').addClass('is-active');
			});

			$(document).on('click', '.js-show-children', function(e) {
				e.preventDefault();

				var $el = $(this),
					elData = $el.data(),
					$target = $(elData.target);

				$('.js-filters-all').removeClass('is-active');
				$target.addClass('is-active');
			});

			$(document).on('click', '.js-set-choice', function(e) {
				e.preventDefault();

				var $el = $(this),
					elData = $el.data(),
					$target = $('.js-choice-' + elData.category);

				query.choices[elData.category] = {
					value: elData.value,
					text: elData.text
				}

				query.refreshChoices();
				$('.js-filters').fadeOut();
				$('.js-filter').addClass('is-filled');
			});
		}
	};

	var query = {
		choices: {
			year: null,
			woz: null,
			used: null,
			housing: null
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
					var $choice = $('<button class="query-item btn js-filter js-choice-' + category + '">' + choice.text + '</button>').appendTo(self.$holder);
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

		init: function() {
			var self = this;
			
			self.detailTemplate = $('.js-building-template').eq(0).html();
			Mustache.parse(self.detailTemplate);

			var c = $('.js-building-map').get(0);

			self.ctx = c.getContext('2d');
		},

		showDetails: function(object) {
			var self = this;

			self.$sidebar.addClass('is-active');
			var rendered = Mustache.render(self.detailTemplate, object);
			self.$sidebar.find('.js-building-details').html(rendered);

			self.drawBuilding(object.geometrie);
		},

		drawBuilding: function(geometry) {
			var self = this;

			var ctx = self.ctx;

			ctx.clearRect(0, 0, 280, 200);

			var bounds = [0, 0, 0, 0];

			ctx.fillStyle = '#f00';
			ctx.beginPath();

			for(var i = 0; i < geometry.length; i++) {
				var point = geometry[i];

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
				var appendX = (280 - distanceX * scale) / 2;
				var appendY = 10;
			} else {
				// horizontal building
				var scale = 260 / distanceX;
				var appendX = 10;
				var appendY = (200 - distanceY * scale) / 2;
			}

			for(var i = 0; i < geometry.length; i++) {
				var point = geometry[i];

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

		init: function() {
			var self = this;

			self.map = L.map('map', {
				zoomControl:false
			}).setView([52.23, 5.6], 7);	
			L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			    attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://mapbox.com">Mapbox</a>',
			    maxZoom: 18,
			    boxZoom: false
			}).addTo(self.map);
		},

		getResults: function() {
			var self = this;

			loader.setLoader();

			$.ajax({
				url: self.apiUrls.queryUrl,
				data: {
					year: (query.choices.year ? query.choices.year.value : null),
					woz: (query.choices.woz ? query.choices.woz.value : null),
					used: (query.choices.used ? query.choices.used.value : null),
					housing: (query.choices.housing ? query.choices.housing.value : null)
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
				disableClusteringAtZoom: 17
			});

			for(var i = self.amountOfMarkers;i; i--){
				var markerData = self.results[i - 1];
				self.markers.addLayer(new L.Marker([markerData.lat,markerData.lng], {
					icon: baseIcon,
					id: markerData.id
				}));
			}

			self.markers.on('click', function(e) {
				$.ajax({
					url: self.apiUrls.detailUrl + e.layer.options.id,
					dataType: 'json',
					success: function(response) {
						ui.showDetails(response);
					}
				});
			});
			self.map.addLayer(self.markers);

			loader.removeLoader();
		}
	};

	ui.init();
	links.init();
	maps.init();
});