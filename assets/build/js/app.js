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
	
	var developerConsole = {
  	messages: [],
  	
  	init: function() {
    	var self = this;
    	
    	this.$code = $('.js-code');
    	this.$holder = $('.developer-mode-console');
    	this.$trigger = $('.js-developer-mode-trigger');
    	this.$loader = $('.js-developer-mode-loader');
    	
    	this.$trigger.on('click', function() {
      	var $developerMode = $('.developer-mode');
      	
      	$developerMode.toggleClass('is-open');
    		
    		maps.map.invalidateSize();
      	
      	if($developerMode.hasClass('is-open')) {
        	self.$trigger.text(self.$trigger.data('opened'));
      	} else {
        	self.$trigger.text(self.$trigger.data('closed'));
      	}
    	});
    	
    	this.speak();
    	
    	this.setMessage('Logging started');
    	this.setEmptyline();
  	},
  	
  	startLoading: function() {
    	this.$loader.addClass('is-loading');
  	},
  	
  	stopLoading: function() {
    	this.$loader.removeClass('is-loading');
  	},
  	
		setMessage: function(message, type) {  		
  		if(!type) type = 'default';
  		
  		if(type == 'speak') {
    		message = message.replace(/([<a].*[\/a>])/g, "");
    		this.messages.push(message);
  		} else {
    		message = message.replace(/</g, "&lt;");
    		message = message.replace(/\*\*(.*)\*\*(:(.*):)/gi, '<a href="$1" class="has-popup" data-title="$3" target="_blank">&lt;$1&gt; <i class="icon-link-ext"></i></a>');
    		message = message.replace(/\*\*(.*)\*\*/gi, '<a href="$1" target="_blank">$1 <i class="icon-link-ext"></i></a>');
    		
    		this.$code.append('<span class="is-' + type + '">' + message + '</span>');
  		}
  		
  		this.scrollToBottom();
		},
		
		speak: function() {
  		var self = this;
  		
  		setInterval(function() {
    		var message = self.messages.shift();
    		if(message) {
      		responsiveVoice.speak(message, 'Dutch Female');
    		}
  		}, 3000);
		},
		
		setNewline: function() {
  		this.$code.append('<span>---------------------------</span>');
  		
  		this.scrollToBottom();
		},
		
		setEmptyline: function() {
  		this.$code.append('<span>&nbsp;</span>');
  		
  		this.scrollToBottom();
		},
		
		replaceLoader: function() {
  		this.$code.append(this.$loader);
		},
		
		scrollToBottom: function() {
  		this.replaceLoader();
  		
      var holder = this.$holder.get(0);
  		holder.scrollTop = holder.scrollHeight;
		}
	}

	var ui = {
		$sidebar: $('.js-sidebar'),
		$sidebarContent: $('.js-building-details'),
		$trigger: $('.js-sidebar-trigger'),

		init: function() {
			var self = this;

			self.getTemplates();
    	
    	this.$trigger.on('click', function() {
      	self.$sidebar.removeClass('is-active');
      });
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

			self.dbpediaTemplate = Handlebars.compile('<strong>DBpedia omschrijving</strong> <a href="{{uri}}" target="_blank"><i class="icon-link-ext"></i></a><br/>{{description}}');
		},

		showDetails: function(object) {
			var self = this;

			self.$sidebar.addClass('is-active');
			self.$sidebarContent.html(ui.detailTemplate($.extend(object, {apibase: apiBase})));

			self.$sidebarContent.find('.currency').digits();

			var $canvas = $('.js-building-map');
			var c = $('.js-building-map').get(0);
			
			if(object.geoJson) {
				self.drawBuilding(object.geoJson);
			}
			
			if(object.seperations) {
  			if(object.seperations.results.length) {
    			maps.setSeperations(object.seperations);
  			}
			}
		},

		drawBuilding: function(geoJson) {
  		if(maps.building) {
    		maps.map.removeLayer(maps.building);
  		}
  		maps.building = L.geoJson(geoJson, {
        style: {
            weight: 0,
            color: '#666',
            fillColor: '#97c000',
            fillOpacity: 1
        }
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
		mapType: 'pdok',
		crs: {
  		pdok: new L.Proj.CRS.TMS(
  	    'EPSG:28992',
			  '+proj=sterea +lat_0=52.15616055555555 +lon_0=5.38763888888889 +k=0.9999079 +x_0=155000 +y_0=463000 +ellps=bessel +units=m +towgs84=565.2369,50.0087,465.658,-0.406857330322398,0.350732676542563,-1.8703473836068,4.0812 +no_defs',
        [-285401.92,22598.08,595401.9199999999,903401.9199999999], {
          resolutions: [3440.640, 1720.320, 860.160, 430.080, 215.040, 107.520, 53.760, 26.880, 13.440, 6.720, 3.360, 1.680, 0.840, 0.420, 0.210, 0.105]
        }
      ),
			osm: L.CRS.EPSG3857
		},
		baseLayer: {
  		pdok: new L.TileLayer('http://geodata.nationaalgeoregister.nl/tms/1.0.0/brtachtergrondkaart/{z}/{x}/{y}.png', {
	        tms: true,
	        minZoom: 3,
	        maxZoom: 12,
 	        opacity: 0.3,
	        attribution: 'Kaartgegevens: &copy; <a href="http://www.cbs.nl">CBS</a>, <a href="http://www.kadaster.nl">Kadaster</a>, <a href="http://openstreetmap.org">OpenStreetMap</a><span class="printhide">-auteurs (<a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>).</span>',
	        continuousWorld: true
	    }),
	    osm: L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
				attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery Â© <a href="http://mapbox.com">Mapbox</a>',
				maxZoom: 18,
        opacity: 0.3,
				boxZoom: false
			})
		},
		zoomDifference: 5,
		markers: [],
		trees: [],

		init: function() {
			var self = this;
			
			$('.js-map-switcher').on('click', function(e) {
  			  e.preventDefault();
  			  
  			  $('.js-map-switcher').removeClass('is-active');
  			  
  			  var map = $(this).addClass('is-active').data('map');
  			  
  			  self.setMap(map);
			}).filter('[data-map=' + self.mapType + ']').addClass('is-active');
			
			self.setMap(self.mapType);
		},
		
		setMap: function(map) {
			var self = this;
      
      if(!self.map) {
        self.zoom = 9;
        
  			self.map = L.map('map', {
  			  continuousWorld: true,
  			  crs: self.crs[map],
  			  zoomControl:false,
  			  doubleClickZoom: false,
  			  center: new L.LatLng(52.15959828480465, 4.505670530026864),
  			  zoom: self.zoom
  			});
  			
  			var zoomControl = new L.Control.Zoom({ position: 'bottomleft'} );
  			zoomControl.addTo(self.map);
    
  			self.curMap = self.baseLayer[map];
  			self.curMap.addTo(self.map);
			
  			switch(map) {
    			case 'pdok':
      			
      			// Thijs: PDOK TMS layers: luchtfoto, BGT layers
      
      			var luchtfoto = new L.TileLayer('http://geodata1.nationaalgeoregister.nl/luchtfoto/tms/1.0.0/luchtfoto_jpeg/EPSG28992/{z}/{x}/{y}.jpeg', {
      	        tms: true,
      	        minZoom: 3,
      	        maxZoom: 14,
      	        opacity: 0.3,
      	        attribution: 'Kaartgegevens: &copy; <a href="http://www.kadaster.nl">Kadaster</a></span>',
      	        continuousWorld: true
      	    });
      
      			var bgtachtergrond = new L.TileLayer('http://geodata.nationaalgeoregister.nl/tms/1.0.0/bgtachtergrond/{z}/{x}/{y}.png', {
      	        tms: true,
      	        minZoom: 3,
      	        opacity: 0.3,
      	        maxZoom: 15,
      	        attribution: 'Kaartgegevens: &copy; <a href="http://www.cbs.nl">CBS</a>, <a href="http://www.kadaster.nl">Kadaster</a>, <a href="http://openstreetmap.org">OpenStreetMap</a><span class="printhide">-auteurs (<a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>).</span>',
      	        continuousWorld: true
      	    });
      			
      			var bgtomtrekgericht = new L.TileLayer('http://geodata.nationaalgeoregister.nl/tms/1.0.0/bgtomtrekgericht/{z}/{x}/{y}.png', {
      	        tms: true,
      	        opacity: 0.1,
      	        minZoom: 3,
      	        maxZoom: 15,
      	        attribution: 'Kaartgegevens: &copy; <a href="http://www.cbs.nl">CBS</a>, <a href="http://www.kadaster.nl">Kadaster</a>, <a href="http://openstreetmap.org">OpenStreetMap</a><span class="printhide">-auteurs (<a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>).</span>',
      	        continuousWorld: true
      	    });
      			
      			self.map.addLayer(bgtachtergrond);
      			self.map.addLayer(bgtomtrekgericht);
      			
      			var baseLayers = {"BRT Achtergrondkaart": self.baseLayer[map], "PDOK Luchtfoto": luchtfoto};
      			var bgtlayers = {"BGT achtergrond" : bgtachtergrond, "BGT bgtomtrekgericht": bgtomtrekgericht};
            
            L.control.layers(baseLayers, bgtlayers).addTo(self.map);
  
    			break;
  			}
      } else {
    		self.map.removeLayer(self.curMap);
        var center = self.map.getCenter();
        self.map.options.crs = self.crs[map];
        self.map.setView(center); //we need this, because after changing crs the center is shifted (as mentioned above probably it's an issue to)
        switch(self.mapType) {
          case 'pdok':
            var zoom = self.map.getZoom() + self.zoomDifference;
          break;
          case 'osm':
            var zoom = self.map.getZoom() - self.zoomDifference;
          break;
        }
        self.map._resetView(self.map.getCenter(), zoom, true); //we need this to redraw all layers (polygons, markers...) in the new projection.
        
  			self.curMap = self.baseLayer[map];
        self.map.addLayer(self.curMap);
      };
			
			self.mapType = map;
		},

		getResults: function() {
			var self = this;

			loader.setLoader();
			
			var url = apiBase + 'panden' + '?' +
				(query.choices.year ? query.choices.year.value + '&' : '') +
				(query.choices.woz ? query.choices.woz.value + '&' : '') +
				(query.choices.used ? query.choices.used.value + '&' : '') +
				(query.choices.type ? query.choices.type.value + '&' : '');

      developerConsole.startLoading();      
			developerConsole.setMessage('Connecting to ' + url);
			
			$.ajax({
				url: url,
				crossDomain: true,
				dataType: 'json',
				success: function(response) {
					self.data = response;
					
  				developerConsole.setMessage('Succesfull request made', 'success');
  				developerConsole.setEmptyline();
  				developerConsole.setMessage('Found ' + response.results.length + ' items', 'log');
  				developerConsole.setMessage(response.results.length + ' item' + (response.results.length == 1 ? '' : 's') + ' gevonden', 'speak');
  				developerConsole.setEmptyline();
  				developerConsole.setMessage('Sparql query used:');
  				developerConsole.setNewline();
  				developerConsole.setMessage(response.sparql, 'data');
  				developerConsole.setEmptyline();
  				developerConsole.setEmptyline();
					developerConsole.stopLoading();
					self.setMarkers();
				},
				error: function(e) {
  				developerConsole.setMessage('Failed getting a succesfull request from ' + url, 'error');
				}
			});
		},

		setMarkers: function() {
			var self = this;
			
			if(maps.building) {
    		maps.map.removeLayer(maps.building);
  		}
			if(maps.trees) {
    		maps.map.removeLayer(maps.trees);
  		}
			if(maps.seperations) {
    		maps.map.removeLayer(maps.seperations);
  		}

			self.amountOfMarkers = self.data.results.length;

			var baseIcon = new self.iconBase();

			if(self.markers) {
				self.map.removeLayer(self.markers);
			}

			if(self.trees) {
				self.map.removeLayer(self.trees);
			}

			self.markers = new L.MarkerClusterGroup({
				spiderfyOnMaxZoom: false,
				showCoverageOnHover: true,
				disableClusteringAtZoom: 17
			});
			
			for(var i = self.amountOfMarkers;i; i--){
				var markerData = self.data.results[i - 1];

 				self.markers.addLayer(new L.geoJson(markerData.geoJson, {
   				style: {
            weight: 0,
            color: "#999",
            opacity: 1,
            fillColor: "#333",
            fillOpacity: 0.8
     			},   				
   				id: markerData.id
 				}));
			}

			self.markers.on('click', function(e) {
  			
  			var url = apiBase + 'panden/' + e.layer.options.id;
        
				developerConsole.startLoading();
  			developerConsole.setMessage('Connecting to ' + url);
  			
				$.ajax({
					url: url,
					dataType: 'json',
					success: function(response) {
					
    				developerConsole.setMessage('Succesfull request made', 'success');
            developerConsole.setEmptyline();
    				developerConsole.setMessage('Found "' + response.definition.label + '" **' + response.definition.id + '**:' + response.definition.source + ': ' + response.definition.text + ': 1 time', 'log');
    				developerConsole.setMessage(response.definition.label + ' gevonden', 'speak');
    				developerConsole.setEmptyline();
    				developerConsole.setMessage('Sparql query used for "Pand":');
            developerConsole.setNewline();
    				developerConsole.setMessage(response.sparql, 'data');
            developerConsole.setEmptyline();
            developerConsole.setEmptyline();
    				developerConsole.setMessage('Found "' + response.seperations.definition.label + '" **' + response.seperations.definition.id + '**:' + response.seperations.definition.source + ': ' + response.seperations.definition.text + ': ' + (response.seperations ? response.seperations.results.length : 0) + ' times', 'log');
    				developerConsole.setMessage(response.seperations.results.length + ' scheiding' + (response.seperations.results.length == 1 ? '' : 'en') + ' gevonden', 'speak');
    				developerConsole.setEmptyline();
    				developerConsole.setMessage('Sparql query used for "Scheiding":');
            developerConsole.setNewline();
    				developerConsole.setMessage(response.seperations.sparql, 'data');
            developerConsole.setEmptyline();
            developerConsole.setEmptyline();
            developerConsole.stopLoading();
            developerConsole.setMessage(response.vestigingen.length + ' vestiging' + (response.vestigingen.length == 1 ? '' : 'en') + ' gevonden', 'speak');
            developerConsole.setMessage(response.woz.length + ' WOZ-object' + (response.woz.length == 1 ? '' : 'en') + ' gevonden', 'speak');
            
            for(i in response.vestigingen) {
              var vestiging = response.vestigingen[i];
              
              if(vestiging.nvwaControles.sparql) {
                developerConsole.setMessage('Found "nvwacontrole" for "' + vestiging.naam + '"', 'log');
                developerConsole.setMessage('nvwa controle gevonden voor ' + vestiging.naam, 'speak');
                developerConsole.setEmptyline();
        				developerConsole.setMessage('Sparql query used for "nvwacontrole":');
                developerConsole.setNewline();
        				developerConsole.setMessage(vestiging.nvwaControles.sparql, 'data');
                developerConsole.setEmptyline();
        				developerConsole.setEmptyline();
              }
            }
    				
						ui.$sidebarContent.removeClass('is-showing-organizations');
						ui.$sidebarContent.removeClass('is-showing-subitems');
    				
						ui.showDetails(response);
            
            setTimeout(function() {
    					self.map.invalidateSize();
    					self.map.panTo(e.latlng);
    					self.map.setZoom(13 + (self.mapType == 'pdok' ? 0 : self.zoomDifference));
            }, 1000);
            
            var url = apiBase + 'bomen?lat=' + e.latlng.lat + '&lng=' + e.latlng.lng;
            
    				developerConsole.startLoading();
      			developerConsole.setMessage('Connecting to ' + url);

						$.ajax({
							url: url,
							dataType: 'json',
							success: function(response) {
					
        				developerConsole.setMessage('Succesfull request made', 'success');
                developerConsole.setEmptyline();
                
                if(response.features.length) {
          				developerConsole.setMessage('Found "' + response.features.length + '" trees', 'log');
          				developerConsole.setMessage(response.features.length + ' ' + (response.features.length == 1 ? 'boom' : 'bomen') + ' gevonden', 'speak');
                } else {
          				developerConsole.setMessage('Didn\'t find any trees in the buildings surroundings');
                }
        				developerConsole.setEmptyline();
                developerConsole.setEmptyline();
                developerConsole.stopLoading();
                
								maps.setTrees(response);
							}
						});
					},
  				error: function(e) {
    				developerConsole.setMessage('Failed getting a succesfull request from ' + url, 'error');
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
  						
			
        			var url = 'http://lookup.dbpedia.org/api/search.asmx/PrefixSearch?MaxHits=1&QueryClass=Species&QueryString=' + e.target.feature.properties.latboomsoort;
              
              developerConsole.startLoading();
        			developerConsole.setMessage('Connecting to ' + url);
              
							$.ajax({
								url: url,
								headers: { 'Accept': 'application/json' },
								success: function(response) {
					
          				developerConsole.setMessage('Succesfull request made', 'success');
          				developerConsole.setEmptyline();
                  
									if(response.results.length) {
            				developerConsole.setMessage('Found extra tree information resolving to: **' + response.results[0].uri + '**', 'log');
										$('.js-dbpedia-description').html(ui.dbpediaTemplate(response.results[0]));
									} else {
            				developerConsole.setMessage('No extra tree information found', 'log');
									}
          				developerConsole.setEmptyline();
                  developerConsole.setEmptyline();
                  developerConsole.stopLoading();
								},
        				error: function(e) {
          				developerConsole.setMessage('Failed getting a succesfull request from ' + url, 'error');
        				}
							});
						});
				}
			}).addTo(maps.map);
		},
		
		setSeperations: function(seperations) {
			var self = this;

			if(self.seperations) {
				self.map.removeLayer(self.seperations);
			}
      
      var stripes = new L.StripePattern();
      stripes.addTo(self.map);
      
			self.seperations = L.geoJson(seperations.results, {
  			style: {
          weight: 4,
          color: 'blue',
          opacity: 1,
          fillColor: '#333',
          fillOpacity: 0
  			},
  			onEachFeature: function(feature, layer) {
    			layer.on('click', function() {
      			ui.$sidebarContent.addClass('is-showing-subitems');

  					$('.js-building-subitems').removeClass('is-active');
  					$('.js-building-seperations').addClass('is-active');
      		}).on('mouseover', function() {
      			layer.setStyle({
        			color: 'red'
      			});
      			$('.js-scheiding[data-id="' + feature.properties.id + '"]').addClass('is-active');
    			}).on('mouseout', function() {
      			layer.setStyle({
        			color: 'blue'
      			});
      			$('.js-scheiding[data-id="' + feature.properties.id + '"]').removeClass('is-active');
      		});
      		$('.js-scheiding[data-id="' + feature.properties.id + '"]').on('mouseover', function() {
      			layer.setStyle({
        			color: 'red'
      			});
      		}).on('mouseout', function() {
      			layer.setStyle({
        			color: 'blue'
      			});
          });
  			}
			}).addTo(self.map);
		}
	};
  
  developerConsole.init();
	ui.init();
	links.init();
	maps.init();
});