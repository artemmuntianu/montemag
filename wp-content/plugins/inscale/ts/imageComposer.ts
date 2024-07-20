namespace InScale {
    let $ = jQuery;
    $(() => {
        window['InScale'].imageComposer = new ImageComposer(<IImageComposerOptions>{
            $containerElem: $('#is-image-composer-container'),
            vm: window['InScale'].imageComposerVM
        });
    });
    export class ImageComposer {
        vm: IImageComposerVM;
        $container: JQuery;
        $editor: JQuery;
        $backgroundSelector: JQuery;
        $imageSelector: JQuery;
        $measureSelector: JQuery;
        buttons = {
            $btnSave: null as JQuery,
            $btnCancel: null as JQuery
        };
        widthPx: number;
        heightPx: number;
        offsetPx: JQueryCoordinates;
        marginPx = 5;

        public constructor(options: IImageComposerOptions) {
            this.$container = options.$containerElem;
            this.widthPx = this.$container.width() - this.marginPx * 2;
            this.vm = options.vm;
            this.appendComboboxWithBackgrounds();
            this.appendComboboxWithImages();
            this.appendComboboxWithMeasures();
            this.appendButtons();
            this.loadImage(this.vm.background.imageUrl).then((backgroundImage) => {
                this.vm.background.image = backgroundImage;
                this.vm.background.scaleFactor = this.widthPx / this.vm.background.image.width;
                this.heightPx = this.vm.background.image.height * this.vm.background.scaleFactor;
                this.$editor = $('<div />',
                    {
                        class: 'editor',
                        css: {
                            height: this.heightPx,
                            width: this.widthPx,
                            'margin-left': this.marginPx,
                            'background-image': `url(${this.vm.background.imageUrl})`
                        }
                    });
                this.$container.append(this.$editor);
                this.offsetPx = this.$editor.offset();
                this.initProductImage().then(() => {
                    this.initEvents();
                });
            });
        }

        private appendButtons() {
            this.buttons.$btnSave = this.$container.append($('<div />', {
                text: 'Save',
                class: 'ui-button ui-corner-all ui-widget ui-state-focus'
            }));
        }

        private appendComboboxWithBackgrounds() {
            this.$backgroundSelector = $('<select/>', {
                class: 'background-selector'
            });
            for (let i = 0; i < this.vm.backgrounds.length; i++) {
                let cur = this.vm.backgrounds[i];
                this.$backgroundSelector.append($('<option/>', {
                    value: cur.id,
                    html: cur.name
                }));
            }
            this.$container.append(this.$backgroundSelector);
        }

        private appendComboboxWithImages() {
            this.$imageSelector = $('<select/>', {
                class: 'image-selector'
            });
            for (let i = 0; i < this.vm.productImages.length; i++) {
                let cur = this.vm.productImages[i];
                this.$imageSelector.append($('<option/>', {
                    value: cur.id,
                    html: cur.name
                }));
            }
            this.$container.append(this.$imageSelector);
        }

        private appendComboboxWithMeasures() {
            this.$measureSelector = $('<select/>', {
                class: 'measure-selector'
            });
            for (let i = 0; i < this.vm.productMeasures.length; i++) {
                let cur = this.vm.productMeasures[i];
                this.$measureSelector.append($('<option/>', {
                    value: cur.name,
                    html: `${cur.name}: ${cur.value}`
                }));
            }
            this.$container.append(this.$measureSelector);
        }

        private initProductImage() {
            return new Promise((resolve, reject) => {
                this.loadImage(this.vm.productImage.imgUrlFull).then((productImage) => {
                    this.vm.productImage.image = productImage;
                    this.vm.productImage.pxPerMm = this.vm.productImage.image.width / this.vm.productMeasure.value;
                    const pxPerMmDiff = this.vm.background.pxPerMm / this.vm.productImage.pxPerMm;
                    this.vm.productImage.widthPx = this.vm.productImage.image.width * pxPerMmDiff * this.vm.background.scaleFactor;
                    this.vm.productImage.heightPx = this.vm.productImage.image.height * pxPerMmDiff * this.vm.background.scaleFactor;
                    this.vm.productImage.$el = $('<div />',
                        {
                            class: 'product-image',
                            css: {
                                width: this.vm.productImage.widthPx,
                                height: this.vm.productImage.heightPx,
                                'background-image': `url(${this.vm.productImage.imgUrlFull})`
                            }
                        });
                    this.$editor.prepend(this.vm.productImage.$el);
                    resolve();
                });
            });
        }

        private initEvents() {
            let zIndex;
            (<any>this.vm.productImage.$el).draggable({
                containment: 'parent',
                start: (event, ui) => {
                    zIndex = this.vm.productImage.$el.css('z-index');
                    this.vm.productImage.$el
                        .css('z-index', 'auto')
                        .addClass('dragged');
                },
                drag: (event, ui) => {
                    const dx = ui.offset.left - this.vm.productImage.$el.offset().left;
                    const dy = ui.offset.top - this.vm.productImage.$el.offset().top;
                    const newX = parseInt(event.target.style.left) + dx;
                    const newY = parseInt(event.target.style.top) + dy;
                    this.vm.productImage.$el[0].style.left = newX + 'px';
                    this.vm.productImage.$el[0].style.top = newY + 'px';
                },
                stop: (event, ui) => {
                    this.vm.productImage.$el
                        .css('z-index', zIndex)
                        .removeClass('dragged');
                }
            });

            this.buttons.$btnSave.click(() => {
                const $form = $('.content form');
                $form
                    .empty()
                    .append($(`<input type='hidden' name='backgroundId' value='${this.vm.background.id}' />`))
                    .append($(`<input type='hidden' name='productId' value='${this.vm.product.id}' />`))
                    .append($(`<input type='hidden' name='productHotSpot' value='${JSON.stringify(this.getProductImagePosRelative(this.vm.productImage))}' />`))
                    .submit();
            });
        }

        private loadImage(imageUrl: string) {
            return new Promise<HTMLImageElement>((resolve, reject) => {
                const image = new Image();
                image.src = imageUrl;
                image.onload = () => resolve(image);
            });
        }

        private getProductImagePosRelative(image: IProductImage): IPoint {
            const productOffsetPx = image.$el.offset();
            return {
                x: (productOffsetPx.left - this.offsetPx.left) / this.widthPx,
                y: (productOffsetPx.top - this.offsetPx.top) / this.heightPx
            }
        }
    }

}