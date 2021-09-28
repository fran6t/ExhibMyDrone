/*!
* Photo Sphere Viewer 4.3.0-rc.1
* @copyright 2014-2015 Jérémy Heleine
* @copyright 2015-2021 Damien "Mistic" Sorel
* @licence MIT (https://opensource.org/licenses/MIT)
*/
(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory(require('photo-sphere-viewer'), require('three')) :
  typeof define === 'function' && define.amd ? define(['photo-sphere-viewer', 'three'], factory) :
  (global = typeof globalThis !== 'undefined' ? globalThis : global || self, (global.PhotoSphereViewer = global.PhotoSphereViewer || {}, global.PhotoSphereViewer.EquirectangularTilesAdapter = factory(global.PhotoSphereViewer, global.THREE)));
}(this, (function (photoSphereViewer, THREE) { 'use strict';

  function _extends() {
    _extends = Object.assign || function (target) {
      for (var i = 1; i < arguments.length; i++) {
        var source = arguments[i];

        for (var key in source) {
          if (Object.prototype.hasOwnProperty.call(source, key)) {
            target[key] = source[key];
          }
        }
      }

      return target;
    };

    return _extends.apply(this, arguments);
  }

  function _inheritsLoose(subClass, superClass) {
    subClass.prototype = Object.create(superClass.prototype);
    subClass.prototype.constructor = subClass;
    subClass.__proto__ = superClass;
  }

  function _assertThisInitialized(self) {
    if (self === void 0) {
      throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
    }

    return self;
  }

  /**
   * @summary Loading task
   * @memberOf PSV.adapters.EquirectangularTilesAdapter
   * @private
   */
  var Task = /*#__PURE__*/function () {
    /**
     * @param {string} id
     * @param {number} priority
     * @param {function(Task): Promise} fn
     */
    function Task(id, priority, fn) {
      this.id = id;
      this.priority = priority;
      this.fn = fn;
      this.status = Task.STATUS.PENDING;
    }

    var _proto = Task.prototype;

    _proto.start = function start() {
      var _this = this;

      this.status = Task.STATUS.RUNNING;
      return this.fn(this).then(function () {
        _this.status = Task.STATUS.DONE;
      }, function () {
        _this.status = Task.STATUS.ERROR;
      });
    };

    _proto.cancel = function cancel() {
      this.status = Task.STATUS.CANCELLED;
    };

    _proto.isCancelled = function isCancelled() {
      return this.status === Task.STATUS.CANCELLED;
    };

    return Task;
  }();
  Task.STATUS = {
    PENDING: 0,
    RUNNING: 1,
    CANCELLED: 2,
    DONE: 3,
    ERROR: 4
  };

  /**
   * @summary Loading queue
   * @memberOf PSV.adapters.EquirectangularTilesAdapter
   * @private
   */

  var Queue = /*#__PURE__*/function () {
    /**
     * @param {int} concurency
     */
    function Queue(concurency) {
      this.concurency = concurency;
      this.runningTasks = {};
      this.tasks = {};
    }

    var _proto = Queue.prototype;

    _proto.enqueue = function enqueue(task) {
      this.tasks[task.id] = task;
    };

    _proto.clear = function clear() {
      Object.values(this.tasks).forEach(function (task) {
        return task.cancel();
      });
      this.tasks = {};
      this.runningTasks = {};
    };

    _proto.setPriority = function setPriority(taskId, priority) {
      if (this.tasks[taskId]) {
        this.tasks[taskId].priority = priority;
      }
    };

    _proto.setAllPriorities = function setAllPriorities(priority) {
      Object.values(this.tasks).forEach(function (task) {
        task.priority = priority;
      });
    };

    _proto.start = function start() {
      var _this = this;

      if (Object.keys(this.runningTasks).length >= this.concurency) {
        return;
      }

      var nextTask = Object.values(this.tasks).filter(function (task) {
        return task.status === Task.STATUS.PENDING && task.priority > 0;
      }).sort(function (a, b) {
        return a.priority - b.priority;
      }).pop();

      if (nextTask) {
        this.runningTasks[nextTask.id] = true;
        nextTask.start().then(function () {
          if (!nextTask.isCancelled()) {
            delete _this.tasks[nextTask.id];
            delete _this.runningTasks[nextTask.id];

            _this.start();
          }
        });
        this.start(); // start tasks until max concurrency is reached
      }
    };

    return Queue;
  }();

  /**
   * @callback TileUrl
   * @summary Function called to build a tile url
   * @memberOf PSV.adapters.EquirectangularTilesAdapter
   * @param {int} col
   * @param {int} row
   * @returns {string}
   */

  /**
   * @typedef {Object} PSV.adapters.EquirectangularTilesAdapter.Panorama
   * @summary Configuration of a tiled panorama
   * @property {string} [baseUrl] - low resolution panorama loaded before tiles
   * @property {int} width - complete panorama width (height is always width/2)
   * @property {int} cols - number of vertical tiles
   * @property {int} rows - number of horizontal tiles
   * @property {PSV.adapters.EquirectangularTilesAdapter.TileUrl} tileUrl - function to build a tile url
   */

  /**
   * @typedef {Object} PSV.adapters.EquirectangularTilesAdapter.Options
   * @property {boolean} [showErrorTile=true] - shows a warning sign on tiles that cannot be loaded
   */

  /**
   * @typedef {Object} PSV.adapters.EquirectangularTilesAdapter.Tile
   * @private
   * @property {int} col
   * @property {int} row
   * @property {int} angle
   */

  var SPHERE_SEGMENTS = 64;
  var NB_VERTICES = 3 * (SPHERE_SEGMENTS * 2 + (SPHERE_SEGMENTS / 2 - 2) * SPHERE_SEGMENTS * 2);
  var NB_GROUPS = SPHERE_SEGMENTS * 2 + (SPHERE_SEGMENTS / 2 - 2) * SPHERE_SEGMENTS;
  var QUEUE_CONCURENCY = 4;

  function tileId(tile) {
    return tile.col + "x" + tile.row;
  }

  function powerOfTwo(x) {
    return Math.log(x) / Math.log(2) % 1 === 0;
  }
  /**
   * @summary Adapter for tiled panoramas
   * @memberof PSV.adapters
   */


  var EquirectangularTilesAdapter = /*#__PURE__*/function (_AbstractAdapter) {
    _inheritsLoose(EquirectangularTilesAdapter, _AbstractAdapter);

    /**
     * @param {PSV.Viewer} psv
     * @param {PSV.adapters.EquirectangularTilesAdapter.Options} options
     */
    function EquirectangularTilesAdapter(psv, options) {
      var _this;

      _this = _AbstractAdapter.call(this, psv) || this;
      /**
       * @member {PSV.adapters.EquirectangularTilesAdapter.Options}
       * @private
       */

      _this.config = _extends({
        showErrorTile: true
      }, options);
      /**
       * @member {external:THREE.MeshBasicMaterial[]}
       * @private
       */

      _this.materials = [];
      /**
       * @member {PSV.adapters.EquirectangularTilesAdapter.Queue}
       * @private
       */

      _this.queue = new Queue(QUEUE_CONCURENCY);
      /**
       * @type {Object}
       * @property {int} colSize - size in pixels of a column
       * @property {int} rowSize - size in pixels of a row
       * @property {int} facesByCol - number of mesh faces by column
       * @property {int} facesByRow - number of mesh faces by row
       * @property {Record<string, boolean>} tiles - loaded tiles
       * @property {external:THREE.SphereGeometry} geom
       * @property {*} originalUvs
       * @property {external:THREE.MeshBasicMaterial} errorMaterial
       * @private
       */

      _this.prop = {
        colSize: 0,
        rowSize: 0,
        facesByCol: 0,
        facesByRow: 0,
        tiles: {},
        geom: null,
        originalUvs: null,
        errorMaterial: null
      };
      /**
       * @member {external:THREE.ImageLoader}
       * @private
       */

      _this.loader = new THREE.ImageLoader();

      if (_this.psv.config.withCredentials) {
        _this.loader.setWithCredentials(true);
      }

      _this.psv.on(photoSphereViewer.CONSTANTS.EVENTS.POSITION_UPDATED, _assertThisInitialized(_this));

      _this.psv.on(photoSphereViewer.CONSTANTS.EVENTS.ZOOM_UPDATED, _assertThisInitialized(_this));

      return _this;
    }

    var _proto = EquirectangularTilesAdapter.prototype;

    _proto.destroy = function destroy() {
      var _this$prop$errorMater, _this$prop$errorMater2, _this$prop$errorMater3;

      this.psv.off(photoSphereViewer.CONSTANTS.EVENTS.POSITION_UPDATED, this);
      this.psv.off(photoSphereViewer.CONSTANTS.EVENTS.ZOOM_UPDATED, this);

      this.__cleanup();

      (_this$prop$errorMater = this.prop.errorMaterial) == null ? void 0 : (_this$prop$errorMater2 = _this$prop$errorMater.map) == null ? void 0 : _this$prop$errorMater2.dispose();
      (_this$prop$errorMater3 = this.prop.errorMaterial) == null ? void 0 : _this$prop$errorMater3.dispose();
      delete this.queue;
      delete this.loader;
      delete this.prop.geom;
      delete this.prop.originalUvs;
      delete this.prop.errorMaterial;

      _AbstractAdapter.prototype.destroy.call(this);
    };

    _proto.handleEvent = function handleEvent(e) {
      /* eslint-disable */
      switch (e.type) {
        case photoSphereViewer.CONSTANTS.EVENTS.POSITION_UPDATED:
        case photoSphereViewer.CONSTANTS.EVENTS.ZOOM_UPDATED:
          this.__refresh();

          break;
      }
      /* eslint-enable */

    }
    /**
     * @summary Clears loading queue, dispose all materials
     * @private
     */
    ;

    _proto.__cleanup = function __cleanup() {
      this.queue.clear();
      this.prop.tiles = {};
      this.materials.forEach(function (mat) {
        var _mat$map;

        mat == null ? void 0 : (_mat$map = mat.map) == null ? void 0 : _mat$map.dispose();
        mat == null ? void 0 : mat.dispose();
      });
      this.materials.length = 0;
    }
    /**
     * @override
     */
    ;

    _proto.supportsTransition = function supportsTransition() {
      return false;
    }
    /**
     * @override
     * @param {PSV.adapters.EquirectangularTilesAdapter.Panorama} panorama
     * @returns {Promise.<PSV.TextureData>}
     */
    ;

    _proto.loadTexture = function loadTexture(panorama) {
      var _this2 = this;

      if (typeof panorama !== 'object' || !panorama.width || !panorama.cols || !panorama.rows || !panorama.tileUrl) {
        return Promise.reject(new photoSphereViewer.PSVError('Invalid panorama configuration, are you using the right adapter?'));
      }

      if (panorama.cols > SPHERE_SEGMENTS) {
        return Promise.reject(new photoSphereViewer.PSVError("Panorama cols must not be greater than " + SPHERE_SEGMENTS + "."));
      }

      if (panorama.rows > SPHERE_SEGMENTS / 2) {
        return Promise.reject(new photoSphereViewer.PSVError("Panorama rows must not be greater than " + SPHERE_SEGMENTS / 2 + "."));
      }

      if (!powerOfTwo(panorama.cols) || !powerOfTwo(panorama.rows)) {
        return Promise.reject(new photoSphereViewer.PSVError('Panorama cols and rows must be powers of 2.'));
      }

      panorama.height = panorama.width / 2;
      this.prop.colSize = panorama.width / panorama.cols;
      this.prop.rowSize = panorama.height / panorama.rows;
      this.prop.facesByCol = SPHERE_SEGMENTS / panorama.cols;
      this.prop.facesByRow = SPHERE_SEGMENTS / 2 / panorama.rows;

      this.__cleanup();

      if (this.prop.geom) {
        this.prop.geom.setAttribute('uv', this.prop.originalUvs.clone());
      }

      var panoData = {
        fullWidth: panorama.width,
        fullHeight: panorama.height,
        croppedWidth: panorama.width,
        croppedHeight: panorama.height,
        croppedX: 0,
        croppedY: 0
      };

      if (panorama.baseUrl) {
        return this.psv.textureLoader.loadImage(panorama.baseUrl, function (p) {
          return _this2.psv.loader.setProgress(p);
        }).then(function (img) {
          return {
            texture: photoSphereViewer.utils.createTexture(img),
            panoData: panoData
          };
        });
      } else {
        return Promise.resolve({
          texture: null,
          panoData: panoData
        });
      }
    }
    /**
     * @override
     */
    ;

    _proto.createMesh = function createMesh(scale) {
      if (scale === void 0) {
        scale = 1;
      }

      var geometry = new THREE.SphereGeometry(photoSphereViewer.CONSTANTS.SPHERE_RADIUS * scale, SPHERE_SEGMENTS, SPHERE_SEGMENTS / 2, -Math.PI / 2).toNonIndexed();
      var i = 0;
      var k = 0; // first row

      for (; i < SPHERE_SEGMENTS * 3; i += 3) {
        geometry.addGroup(i, 3, k++);
      } // second to before last rows


      for (; i < NB_VERTICES - SPHERE_SEGMENTS * 3; i += 6) {
        geometry.addGroup(i, 6, k++);
      } // last row


      for (; i < NB_VERTICES; i += 3) {
        geometry.addGroup(i, 3, k++);
      }

      this.prop.geom = geometry;
      this.prop.originalUvs = geometry.getAttribute('uv').clone();
      var mesh = new THREE.Mesh(geometry, this.materials);
      mesh.scale.set(-1, 1, 1);
      return mesh;
    }
    /**
     * @summary Applies the base texture and starts the loading of tiles
     * @override
     */
    ;

    _proto.setTexture = function setTexture(mesh, textureData) {
      var _this3 = this;

      if (textureData.texture) {
        var material = new THREE.MeshBasicMaterial({
          side: THREE.BackSide,
          map: textureData.texture
        });

        for (var i = 0; i < NB_GROUPS; i++) {
          this.materials.push(material);
        }
      }

      setTimeout(function () {
        return _this3.__refresh();
      });
    }
    /**
     * @summary Compute visible tiles and load them
     * @private
     */
    ;

    _proto.__refresh = function __refresh() {
      var _this4 = this;

      var viewerSize = this.psv.prop.size;
      var panorama = this.psv.config.panorama;
      var tilesToLoad = [];

      for (var col = 0; col <= panorama.cols; col++) {
        for (var row = 0; row <= panorama.rows; row++) {
          // TODO prefilter with less complex math if possible
          var tileTexturePosition = {
            x: col * this.prop.colSize,
            y: row * this.prop.rowSize
          };
          var tilePosition = this.psv.dataHelper.sphericalCoordsToVector3(this.psv.dataHelper.textureCoordsToSphericalCoords(tileTexturePosition));

          if (tilePosition.dot(this.psv.prop.direction) > 0) {
            var tileViewerPosition = this.psv.dataHelper.vector3ToViewerCoords(tilePosition);

            if (tileViewerPosition.x >= 0 && tileViewerPosition.x <= viewerSize.width && tileViewerPosition.y >= 0 && tileViewerPosition.y <= viewerSize.height) {
              (function () {
                var angle = tilePosition.angleTo(_this4.psv.prop.direction);

                _this4.__getAdjacentTiles(col, row).forEach(function (tile) {
                  var existingTile = tilesToLoad.find(function (c) {
                    return c.row === tile.row && c.col === tile.col;
                  });

                  if (existingTile) {
                    existingTile.angle = Math.min(existingTile.angle, angle);
                  } else {
                    tilesToLoad.push(_extends({}, tile, {
                      angle: angle
                    }));
                  }
                });
              })();
            }
          }
        }
      }

      this.__loadTiles(tilesToLoad);
    }
    /**
     * @summary Get the 4 adjacent tiles
     * @private
     */
    ;

    _proto.__getAdjacentTiles = function __getAdjacentTiles(col, row) {
      var panorama = this.psv.config.panorama;
      return [{
        col: col - 1,
        row: row - 1
      }, {
        col: col,
        row: row - 1
      }, {
        col: col,
        row: row
      }, // eslint-disable-line object-shorthand
      {
        col: col - 1,
        row: row
      }].map(function (tile) {
        // examples are for cols=16 and rows=8
        if (tile.row < 0) {
          // wrap on top
          tile.row = -tile.row - 1; // -1 => 0, -2 => 1

          tile.col += panorama.cols / 2; // change hemisphere
        } else if (tile.row >= panorama.rows) {
          // wrap on bottom
          tile.row = panorama.rows - 1 - (tile.row - panorama.rows); // 8 => 7, 9 => 6

          tile.col += panorama.cols / 2; // change hemisphere
        }

        if (tile.col < 0) {
          // wrap on left
          tile.col += panorama.cols; // -1 => 15, -2 => 14
        } else if (tile.col >= panorama.cols) {
          // wrap on right
          tile.col -= panorama.cols; // 16 => 0, 17 => 1
        }

        return tile;
      });
    }
    /**
     * @summary Loads tiles and change existing tiles priority
     * @param {PSV.adapters.EquirectangularTilesAdapter.Tile[]} tiles
     * @private
     */
    ;

    _proto.__loadTiles = function __loadTiles(tiles) {
      var _this5 = this;

      this.queue.setAllPriorities(0);
      tiles.forEach(function (tile) {
        var id = tileId(tile);
        var priority = Math.PI / 2 - tile.angle;

        if (_this5.prop.tiles[id]) {
          _this5.queue.setPriority(id, priority);
        } else {
          _this5.prop.tiles[id] = true;

          _this5.queue.enqueue(new Task(id, priority, function (task) {
            return _this5.__loadTile(tile, task);
          }));
        }
      });
      this.queue.start();
    }
    /**
     * @summary Loads and draw a tile
     * @param {PSV.adapters.EquirectangularTilesAdapter.Tile} tile
     * @param {PSV.adapters.EquirectangularTilesAdapter.Task} task
     * @return {Promise}
     * @private
     */
    ;

    _proto.__loadTile = function __loadTile(tile, task) {
      var _this6 = this;

      var panorama = this.psv.config.panorama;
      var url = panorama.tileUrl(tile.col, tile.row);
      return new Promise(function (resolve, reject) {
        return _this6.loader.load(url, resolve, undefined, reject);
      }).then(function (image) {
        if (!task.isCancelled()) {
          var material = new THREE.MeshBasicMaterial({
            side: THREE.BackSide,
            map: photoSphereViewer.utils.createTexture(image)
          });

          _this6.__swapMaterial(tile.col, tile.row, material);

          _this6.psv.needsUpdate();
        }
      }).catch(function () {
        if (!task.isCancelled() && _this6.config.showErrorTile) {
          var material = _this6.__getErrorMaterial();

          _this6.__swapMaterial(tile.col, tile.row, material);

          _this6.psv.needsUpdate();
        }
      });
    }
    /**
     * @summary Applies a new texture to the faces
     * @param {int} col
     * @param {int} row
     * @param {external:THREE.MeshBasicMaterial} material
     * @private
     */
    ;

    _proto.__swapMaterial = function __swapMaterial(col, row, material) {
      var _this7 = this;

      var uvs = this.prop.geom.getAttribute('uv');

      for (var c = 0; c < this.prop.facesByCol; c++) {
        var _loop = function _loop(r) {
          // position of the face (two triangles of the same square)
          var faceCol = col * _this7.prop.facesByCol + c;
          var faceRow = row * _this7.prop.facesByRow + r;
          var isFirstRow = faceRow === 0;
          var isLastRow = faceRow === SPHERE_SEGMENTS / 2 - 1; // first vertex for this face (3 or 6 vertices in total)

          var firstVertex = void 0;

          if (isFirstRow) {
            firstVertex = faceCol * 3;
          } else if (isLastRow) {
            firstVertex = NB_VERTICES - SPHERE_SEGMENTS * 3 + faceCol * 3;
          } else {
            firstVertex = 3 * (SPHERE_SEGMENTS + (faceRow - 1) * SPHERE_SEGMENTS * 2 + faceCol * 2);
          } // swap material


          var matIndex = _this7.prop.geom.groups.find(function (g) {
            return g.start === firstVertex;
          }).materialIndex;

          _this7.materials[matIndex] = material; // define new uvs

          var top = 1 - r / _this7.prop.facesByRow;
          var bottom = 1 - (r + 1) / _this7.prop.facesByRow;
          var left = c / _this7.prop.facesByCol;
          var right = (c + 1) / _this7.prop.facesByCol;

          if (isFirstRow) {
            uvs.setXY(firstVertex, (left + right) / 2, top);
            uvs.setXY(firstVertex + 1, left, bottom);
            uvs.setXY(firstVertex + 2, right, bottom);
          } else if (isLastRow) {
            uvs.setXY(firstVertex, right, top);
            uvs.setXY(firstVertex + 1, left, top);
            uvs.setXY(firstVertex + 2, (left + right) / 2, bottom);
          } else {
            uvs.setXY(firstVertex, right, top);
            uvs.setXY(firstVertex + 1, left, top);
            uvs.setXY(firstVertex + 2, right, bottom);
            uvs.setXY(firstVertex + 3, left, top);
            uvs.setXY(firstVertex + 4, left, bottom);
            uvs.setXY(firstVertex + 5, right, bottom);
          }
        };

        for (var r = 0; r < this.prop.facesByRow; r++) {
          _loop(r);
        }
      }

      uvs.needsUpdate = true;
    }
    /**
     * @summary Generates an material for errored tiles
     * @return {external:THREE.MeshBasicMaterial}
     * @private
     */
    ;

    _proto.__getErrorMaterial = function __getErrorMaterial() {
      if (!this.prop.errorMaterial) {
        var canvas = document.createElement('canvas');
        canvas.width = this.prop.colSize;
        canvas.height = this.prop.rowSize;
        var ctx = canvas.getContext('2d');
        ctx.fillStyle = '#333';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.font = canvas.width / 5 + "px serif";
        ctx.fillStyle = '#a22';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText('⚠', canvas.width / 2, canvas.height / 2);
        var texture = new THREE.CanvasTexture(canvas);
        this.prop.errorMaterial = new THREE.MeshBasicMaterial({
          side: THREE.BackSide,
          map: texture
        });
      }

      return this.prop.errorMaterial;
    };

    return EquirectangularTilesAdapter;
  }(photoSphereViewer.AbstractAdapter);

  return EquirectangularTilesAdapter;

})));
//# sourceMappingURL=equirectangular-tiles.js.map
