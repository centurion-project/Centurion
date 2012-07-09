//This function is called when scripts/helper/util.js is loaded.
/*Class ooMap prototype pattern*/

    var ooMap = function(){
    };

    ooMap.prototype = {
        _map : null,
        _markers : [], 
        _indexes  : {},
        _lastIndex : null,
        _content : [],
        _contentOpen : [],
        _clusterer : null,
        _mcOptions : null,
        _opts : {
            lat : 48.9,
            lng : 2.2,
            zoom : 9,
            mapType : google.maps.MapTypeId.ROADMAP,
            mapTypeControl : true,
            scrollwheel : true,
            zoomControl : true,
            panControl : true,
            streetViewControl : true,
            zoomControlStyle : "DEFAULT",
            onClickMarker : function onClickMarker(){
            }
        },
        _init : function _init(opts){
            var mapOptions;
            //extend opts
            $.extend(this._opts, opts);

            if (!this._opts.mapId || !document.getElementById(this._opts.mapId)) return;

            this._buildMap();
            //createMarkers
            if (this._opts.datas) this._createMarkers();
        },
        _buildMap : function _buildMap(){
            var mapCenter = this._transformLatLng(this._opts.lat, this._opts.lng),
                mapOptions = {
                  zoom: this._opts.zoom,
                  center: mapCenter,
                  mapTypeId: this._opts.mapType,
                  scrollwheel : this._opts.scrollwheel,
                  mapTypeControl : this._opts.mapTypeControl,
                  zoomControl : this._opts.zoomControl,
                  panControl : this._opts.panControl,
                  streetViewControl : this._opts.streetViewControl
                };

            if (this._opts.zoomControlStyle){   
                mapOptions['zoomControlOptions'] = {};
                mapOptions['zoomControlOptions']['style'] = google.maps.ZoomControlStyle[this._opts.zoomControlStyle]
            }

            this._map = new google.maps.Map(document.getElementById(this._opts.mapId),
                    mapOptions);

            if (this._opts.stylesMap) this._customMap();
            if (this._opts.clusterer) this._buildClusterer()
        },
        _customMap : function(){
            var stylesMap = new google.maps.StyledMapType(this._opts.stylesMap);
            this._map.mapTypes.set(this._opts.stylesId, stylesMap);
            this._map.setMapTypeId(this._opts.stylesId);
        },
        _transformLatLng : function _transformLatLng(lat, lng){
            return new google.maps.LatLng(lat, lng)
        },
        _createMarkers : function _createMarkers(){
            for (var i = 0, ln = this._opts.datas.length; i < ln; i++){
                this._createMarker(this._opts.datas[i], i);
            }
            //clusterer
            if (this._clusterer) this._clusterer.addMarkers(this._markers)
        },
        _createMarker : function _createMarker(opts, index){
            var that = this, id = ( opts.id || this._generateId() ),
                lat = (opts.latLng.lat || undefined), 
                lng = (opts.latLng.lng || undefined), 
                iconUrl = (opts.icon && opts.icon.url) ? opts.icon.url : undefined, 
                content = (opts.content || undefined),
                marker, infowindow;
                if ( !lat || !lng) return

                if (!this._opts.customIcon){
                   marker = new google.maps.Marker({
                      position : this._transformLatLng(lat,lng),
                      icon : iconUrl,
                      visible : (this._opts.visible || false)
                  });
                  marker.setMap(this._map)
                } else {
                  //TODO => put in option
                  marker = new MarkerWithLabel({
                         position: this._transformLatLng(lat,lng),
                         draggable: false,
                         raiseOnDrag: false,
                         map: this._map,
                         labelContent: index+1,
                         labelAnchor: new google.maps.Point(17,45),
                         labelClass: "map-labels", // the CSS class for the label
                         labelStyle: {opacity: 1},
                         labelInBackground: false
                       });
                  if (iconUrl){
                    marker.setIcon(iconUrl)
                  }
                }
                if (content){
                    if ( !this._opts.infobox ){
                        infowindow = new google.maps.InfoWindow({
                            content: content
                        });
                    } else {
                        var contentBoxBottom = document.createElement("div");
                        $(contentBoxBottom).addClass("box-map-bottom");
                        var contentBoxTop = document.createElement("div");
                        $(contentBoxTop).addClass("box-map-top");
                        var contentBoxRepeat = document.createElement("div");
                        $(contentBoxRepeat).addClass("box-map-repeat");

                        var contentBox = document.createElement("div");
                        $(contentBox).addClass("box-map-inner");

                        contentBox.innerHTML = content;
                        //contentBox.appendChild(picto);
                        contentBoxRepeat.appendChild(contentBox)
                        contentBoxTop.appendChild(contentBoxRepeat)
                        contentBoxBottom.appendChild(contentBoxTop)
                        infowindow = new InfoBox(this._opts.infobox);
                        infowindow.setContent(contentBoxBottom);
                    }

                    if (this._opts.clickInfowindow){
                        this._opts.clickInfowindow(infowindow, id);
                    }

                    //add event click on marker
                    google.maps.event.addListener(marker, 'click', function() {
                      //close infowindowopen
                      that.closeInfoWindow();
                      infowindow.open( that._map, marker);
                      that._contentOpen.push(infowindow);
                      that._opts.onClickMarker(id);
                    });
                }
                //store
                this._markers.push(marker);
                this._content.push(infowindow);
                this._indexes[id] = index;
                this._lastIndex = index;

        },
        _generateId : function _generateId(){
            /*
        Copyright (c) 2008, Robert Kieffer
        All rights reserved.

        Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

            * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
            * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
            * Neither the name of Robert Kieffer nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.

        THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, 
        THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
        INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; 
        OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
        ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
        */
            // Private array of chars to use
          var CHARS = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.split(''); 

          return (function (len, radix) {
            var chars = CHARS, uuid = [], rnd = Math.random;
            radix = radix || chars.length;

            if (len) {
              // Compact form
              for (var i = 0; i < len; i++) uuid[i] = chars[0 | rnd()*radix];
            } else {
              // rfc4122, version 4 form
              var r;

              // rfc4122 requires these characters
              uuid[8] = uuid[13] = uuid[18] = uuid[23] = '-';
              uuid[14] = '4';

              // Fill in random data.  At i==19 set the high bits of clock sequence as
              // per rfc4122, sec. 4.1.5
              for (var i = 0; i < 36; i++) {
                if (!uuid[i]) {
                  r = 0 | rnd()*16;
                  uuid[i] = chars[(i == 19) ? (r & 0x3) | 0x8 : r & 0xf];
                }
              }
            }

            return uuid.join('');
          })();
        },
        _getMarker : function _getMarker(id){ 
            return this._markers[this._indexes[id]]
        },
        _displayMarker : function _displayMarker(id, visible){
            var marker = this._getMarker(id);
            if (marker) marker.setVisible(visible);

            if (!visible){
                this.closeInfoWindow();
            }
        },
        _buildClusterer : function _buildClusterer(){
            this._mcOptions = {
                gridSize : (this._opts.clusterer.gridSize || 50),
                maxZoom : (this._opts.clusterer.maxZoom || 15)
            }
            if (this._opts.clusterer.styles) this._mcOptions['styles'] = this._opts.clusterer.styles;
            this._clusterer = new MarkerClusterer(this._map, this._markers, this._mcOptions);
        },
        _getPosition : function _getPosition(marker){
            return marker.getPosition();
        },
        _getZoom : function _getZoom(){
            return this._map.getZoom()
        },
        _zoomMarker : function _zoomMarker(marker, zoomLevel, animated){
            var pos = this._getPosition(marker);
            this._map[ (animated) ? 'panTo': 'setCenter'](pos);
            if ( this._getZoom() != zoomLevel) {
                this._map.setZoom((zoomLevel || 17));
            }
        },
        closeInfoWindow : function closeInfoWindow(){
            for (var i = 0, len = this._contentOpen.length; i < len; i++){
                  this._contentOpen[i].close();
              }
              this._contentOpen = [];
        },
        /*public*/
        showMarkers : function showMarkers(ids){
            var arr = [];
            if ( !ids){
                for (prop in this._indexes ){
                    arr.push(prop);
                }
            } else {
                if (ids instanceof Array) {
                    arr = ids;
                }
            }

            for (var i = 0, len = arr.length; i < len; i++ ){
                this.showMarker(arr[i]);
            }
        },
        showMarker : function showMarker(id){
            if ( !id) return;
            this._displayMarker(id,true);
        },
        hideMarkers : function hideMarkers(ids){
            var arr = [];
            if ( !ids){
                for (prop in this._indexes ){
                    arr.push(prop);
                }
            } else {
                if (ids instanceof Array) {
                    arr = ids;
                }
            }

            for (var i = 0, len = arr.length; i < len; i++ ){
                this.hideMarker(arr[i]);
            }
        },
        hideMarker : function hideMarker(id){
            if ( !id) return;
            this._displayMarker(id,false);
            //todo : hide infobull
        },
        zoomOn : function zoomOn(id, zoomLevel, animated){
            var m = this.getMarker(id);
            if (m){
                this._zoomMarker(m, zoomLevel, animated)
            }
        },
        getMap : function getMap(){
            return this._map;
        },
        getMarker : function getMarker(id){
            if (!id) return;
            return this._getMarker(id)
        },
        latLng : function latLng(lat,lng){
            if (!lat || !lng) return
            return this._transformLatLng(lat,lng);
        },
        refreshClusterer : function refreshClusterer(){
            if (this._clusterer) {
                var m = this.getVisibleMarkers();
                this._clusterer.clearMarkers();
                this._clusterer = new MarkerClusterer(this._map, m, this._mcOptions);
            }
        },
        getVisibleMarkers : function getVisibleMarkers(){
            if (this._markers){
                var mA = [];
                for (var i=0,len=this._markers.length; i < len; i++){
                    if (this._markers[i].getVisible()){
                        mA.push(this._markers[i]);
                    }
                }

                return mA;
            }
        },
        getPosition : function getPosition(marker){
            if( !marker) return;
            return this._getPosition(marker);
        },
        setCenter : function(marker){
            if ( !marker) return;
            this._map.setCenter(this._getPosition(marker));
        },
        setZoom : function(level){
            if ( !level) return;
            this._map.setZoom(level)
        },
        setup : function setup (opts){
            this._init(opts);
        },
        //todo : find a better function name + add opts to be less specific
        findByAutocomplete : function findByAutocomplete(elem, cls, callbackSelect){
            var geocoder = new google.maps.Geocoder();
            elem.autocomplete({
                minLength: 2,
                source: function(request, response) {
                    geocoder.geocode( 
                    { 
                      address : request.term
                    }, function(results, status) {
                      if (status == google.maps.GeocoderStatus.OK) {
                        response($.map(results, function(item) {
                            return {
                                label: item.formatted_address,
                                value: item.formatted_address,
                                lat : item.geometry.location.Ra,
                                lng : item.geometry.location.Sa
                            }
                        }))
                      } 
                    });
                },
                select: function(event, ui) {
                    $(this).val(ui.item.label);
                    $(this).data('lat',ui.item.lat);
                    $(this).data('lng',ui.item.lng);
                    callbackSelect(ui.item.lat, ui.item.lng)
                },
                open: function(event, ui) {
                    var widget = $(this).autocomplete('widget');
                    //widget.width(dimW)
                    
                    if ( !widget.hasClass(cls) ){
                         widget.addClass(cls);
                    }
                    
                    widget.css({
                        'top' :'+=5'
                    });   
                    widget.css( 'width', $(this).innerWidth());
                }
            });
        },
        setIconHover : function setIconHover(id, level){
            if (!id) return;
            var marker = this.getMarker(id);
            if (!marker) return;
            marker.setIcon(this._opts.datas[this._indexes[id]].icon.iconHover.url);
            marker.setZIndex(level || 2);

        },
        unsetIconHover : function unsetIconHover(id, level){
            if (!id) return;

            var marker = this.getMarker(id);
            if (!marker) return;
            marker.setIcon(this._opts.datas[this._indexes[id]].icon.url)
            marker.setZIndex(level || 1);
        },
        getInfowindow : function getInfowindow (id){
            if (!id) return;
            var infowindow = this._content[this._indexes[id]];

            return (infowindow || false)
        },
        LatLngBounds : function LatLngBounds(sw, ne){
            return new google.maps.LatLngBounds(sw, ne);
        },
        showInfoWindow : function showInfoWindow(id){
            if (!id) return;
            var marker = this.getMarker(id);
            if (!marker) return;

            google.maps.event.trigger(marker, 'click')

        },
        refresh : function refresh(){
            google.maps.event.trigger(this._map, 'resize');
        },
        addMarker : function addMarker(opts){ 
            if (!this._lastIndex){
                this._lastIndex=-1;
            }
            this._createMarker(opts, (this._lastIndex+1));
        },
        setPosition: function setPosition(markerId, lat, lng){ 
            var marker = this._getMarker(markerId);
            if (!marker) return;
            marker.setPosition(this._transformLatLng(lat,lng));
        },
        renderDraggable : function renderDraggable(marker, opts){
            marker.setDraggable(true);
            if(opts.dragEnd){
                google.maps.event.addListener(marker, 'dragend', function() {
                    opts.dragEnd(this);
                });
            }
        }
    }
    /*end prototype*/ 




