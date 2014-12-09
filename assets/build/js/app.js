$(document).on('ready', function() {
	var maps = {
		iconBase: L.Icon.extend({
			options: {
				shadowUrl: 'leaf-shadow.png',
				iconSize:     [38, 95],
				shadowSize:   [50, 64],
				iconAnchor:   [22, 94],
				shadowAnchor: [4, 62],
				popupAnchor:  [-3, -76]
			}
		}),
		markers: [],

		init: function() {
			var self = this;

			self.map = L.map('map').setView([52.23, 5.6], 7);
			L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			    attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://mapbox.com">Mapbox</a>',
			    maxZoom: 18
			}).addTo(self.map);

			self.results = self.getResults();
			self.setMarkers();
		},

		getResults: function() {
			return [{
				id: 1,
				lat: 52.23,
				lon: 5.6
			}];
		},

		setMarkers: function() {
			var self = this;

			self.amountOfMarkers = self.results.length;

			for(var i = self.amountOfMarkers;i; i--){
				var markerData = self.results[i - 1];

				var marker = L.marker([markerData.lat, markerData.lon]).addTo(self.map);
				marker.bindPopup(markerData.id);

				self.markers.push(marker);
			}

			var greenIcon = new self.iconBase({iconUrl: 'leaf-green.png'}),
				redIcon = new self.iconBase({iconUrl: 'leaf-red.png'}),
				orangeIcon = new self.iconBase({iconUrl: 'leaf-orange.png'});

			L.icon({
			    iconUrl: 'assets/images/leaf-green.png',
			    shadowUrl: 'assets/images/leaf-shadow.png',

			    iconSize:     [38, 95], // size of the icon
			    shadowSize:   [50, 64], // size of the shadow
			    iconAnchor:   [22, 94], // point of the icon which will correspond to marker's location
			    shadowAnchor: [4, 62],  // the same for the shadow
			    popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
			})
		}
	};

	maps.init();
});