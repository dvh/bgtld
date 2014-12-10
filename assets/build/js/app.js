$(document).on('ready', function() {
	var links = {
		init: function() {
			$(document).on('click', '.js-add-filter', function(e) {
				e.preventDefault();

				console.log('as');

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
			'year': null,
			'woz': null,
			'used': null,
			'housing': null
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
		}
	};

	var maps = {
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

			self.getResults();
		},

		getResults: function() {
			var self = this;

			$.ajax({
				url: 'assets/data/panden.json',
				dataType: 'json',
				success: function(response) {
					self.results = response;
					self.setMarkers();
				}
			});
			// return [{
			//     "type": "Feature",
			//     "properties": {
			//     	"id": "1"
			//     },
			//     "geometry": {
			//         "type": "Point",
			//         "coordinates": [5.6, 52.23]
			//     }
			// }];
		},

		setMarkers: function() {
			var self = this;

			self.amountOfMarkers = self.results.length;

			var baseIcon = new self.iconBase();

			for(var i = self.amountOfMarkers;i; i--){
				var markerData = self.results[i - 1];
				// L.marker([markerData.lat,markerData.lon], {
				// 	pointToLayer: function (feature, latlng) {
				// 		return L.marker(latlng, {icon: baseIcon});
				// 	}
				// }).addTo(self.map);
			}
		}
	};

	links.init();
	maps.init();
});