var InScale;
(function (InScale) {
    var $ = jQuery;
    $(function () {
        window['InScale'].imageComposer = new ImageComposer({
            $containerElem: $('#is-image-composer-container'),
            vm: window['InScale'].imageComposerVM
        });
    });
    var ImageComposer = (function () {
        function ImageComposer(options) {
            var _this = this;
            this.buttons = {
                $btnSave: null,
                $btnCancel: null
            };
            this.marginPx = 5;
            this.$container = options.$containerElem;
            this.widthPx = this.$container.width() - this.marginPx * 2;
            this.vm = options.vm;
            this.appendComboboxWithBackgrounds();
            this.appendComboboxWithImages();
            this.appendComboboxWithMeasures();
            this.appendButtons();
            this.loadImage(this.vm.background.imageUrl).then(function (backgroundImage) {
                _this.vm.background.image = backgroundImage;
                _this.vm.background.scaleFactor = _this.widthPx / _this.vm.background.image.width;
                _this.heightPx = _this.vm.background.image.height * _this.vm.background.scaleFactor;
                _this.$editor = $('<div />', {
                    class: 'editor',
                    css: {
                        height: _this.heightPx,
                        width: _this.widthPx,
                        'margin-left': _this.marginPx,
                        'background-image': "url(" + _this.vm.background.imageUrl + ")"
                    }
                });
                _this.$container.append(_this.$editor);
                _this.offsetPx = _this.$editor.offset();
                _this.initProductImage().then(function () {
                    _this.initEvents();
                });
            });
        }
        ImageComposer.prototype.appendButtons = function () {
            this.buttons.$btnSave = this.$container.append($('<div />', {
                text: 'Save',
                class: 'ui-button ui-corner-all ui-widget ui-state-focus'
            }));
        };
        ImageComposer.prototype.appendComboboxWithBackgrounds = function () {
            this.$backgroundSelector = $('<select/>', {
                class: 'background-selector'
            });
            for (var i = 0; i < this.vm.backgrounds.length; i++) {
                var cur = this.vm.backgrounds[i];
                this.$backgroundSelector.append($('<option/>', {
                    value: cur.id,
                    html: cur.name
                }));
            }
            this.$container.append(this.$backgroundSelector);
        };
        ImageComposer.prototype.appendComboboxWithImages = function () {
            this.$imageSelector = $('<select/>', {
                class: 'image-selector'
            });
            for (var i = 0; i < this.vm.productImages.length; i++) {
                var cur = this.vm.productImages[i];
                this.$imageSelector.append($('<option/>', {
                    value: cur.id,
                    html: cur.name
                }));
            }
            this.$container.append(this.$imageSelector);
        };
        ImageComposer.prototype.appendComboboxWithMeasures = function () {
            this.$measureSelector = $('<select/>', {
                class: 'measure-selector'
            });
            for (var i = 0; i < this.vm.productMeasures.length; i++) {
                var cur = this.vm.productMeasures[i];
                this.$measureSelector.append($('<option/>', {
                    value: cur.name,
                    html: cur.name + ": " + cur.value
                }));
            }
            this.$container.append(this.$measureSelector);
        };
        ImageComposer.prototype.initProductImage = function () {
            var _this = this;
            return new Promise(function (resolve, reject) {
                _this.loadImage(_this.vm.productImage.imgUrlFull).then(function (productImage) {
                    _this.vm.productImage.image = productImage;
                    _this.vm.productImage.pxPerMm = _this.vm.productImage.image.width / _this.vm.productMeasure.value;
                    var pxPerMmDiff = _this.vm.background.pxPerMm / _this.vm.productImage.pxPerMm;
                    _this.vm.productImage.widthPx = _this.vm.productImage.image.width * pxPerMmDiff * _this.vm.background.scaleFactor;
                    _this.vm.productImage.heightPx = _this.vm.productImage.image.height * pxPerMmDiff * _this.vm.background.scaleFactor;
                    _this.vm.productImage.$el = $('<div />', {
                        class: 'product-image',
                        css: {
                            width: _this.vm.productImage.widthPx,
                            height: _this.vm.productImage.heightPx,
                            'background-image': "url(" + _this.vm.productImage.imgUrlFull + ")"
                        }
                    });
                    _this.$editor.prepend(_this.vm.productImage.$el);
                    resolve();
                });
            });
        };
        ImageComposer.prototype.initEvents = function () {
            var _this = this;
            var zIndex;
            this.vm.productImage.$el.draggable({
                containment: 'parent',
                start: function (event, ui) {
                    zIndex = _this.vm.productImage.$el.css('z-index');
                    _this.vm.productImage.$el
                        .css('z-index', 'auto')
                        .addClass('dragged');
                },
                drag: function (event, ui) {
                    var dx = ui.offset.left - _this.vm.productImage.$el.offset().left;
                    var dy = ui.offset.top - _this.vm.productImage.$el.offset().top;
                    var newX = parseInt(event.target.style.left) + dx;
                    var newY = parseInt(event.target.style.top) + dy;
                    _this.vm.productImage.$el[0].style.left = newX + 'px';
                    _this.vm.productImage.$el[0].style.top = newY + 'px';
                },
                stop: function (event, ui) {
                    _this.vm.productImage.$el
                        .css('z-index', zIndex)
                        .removeClass('dragged');
                }
            });
            this.buttons.$btnSave.click(function () {
                var $form = $('.content form');
                $form
                    .empty()
                    .append($("<input type='hidden' name='backgroundId' value='" + _this.vm.background.id + "' />"))
                    .append($("<input type='hidden' name='productId' value='" + _this.vm.product.id + "' />"))
                    .append($("<input type='hidden' name='productHotSpot' value='" + JSON.stringify(_this.getProductImagePosRelative(_this.vm.productImage)) + "' />"))
                    .submit();
            });
        };
        ImageComposer.prototype.loadImage = function (imageUrl) {
            return new Promise(function (resolve, reject) {
                var image = new Image();
                image.src = imageUrl;
                image.onload = function () { return resolve(image); };
            });
        };
        ImageComposer.prototype.getProductImagePosRelative = function (image) {
            var productOffsetPx = image.$el.offset();
            return {
                x: (productOffsetPx.left - this.offsetPx.left) / this.widthPx,
                y: (productOffsetPx.top - this.offsetPx.top) / this.heightPx
            };
        };
        return ImageComposer;
    }());
    InScale.ImageComposer = ImageComposer;
})(InScale || (InScale = {}));
//# sourceMappingURL=imageComposer.js.map