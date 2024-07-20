namespace InScale {

    export interface IPoint {
        x: number;
        y: number;
    }

    export interface IHotSpot extends IPoint {
        $el: JQuery;
    }

    export interface IBackground {
        id: number;
        name: string;
        image: HTMLImageElement;
        widthPx: number;
        heightPx: number;
        imageUrl: string;
        pxPerMm: number;
        scaleFactor: number;
    }

    export interface IProductMeasure {
        name: string;
        value: number;
    }

    export interface IProductImage {
        id: number;
        name: string;
        $el: JQuery;
        image: HTMLImageElement;
        widthPx: number;
        heightPx: number;
        imgUrlFull: string;
        imgUrlScaled: string;
        pxPerMm: number;
        scaleFactor: number;
    }

    export interface IProduct {
        id: number;
    }

    export interface IImageComposerVM {
        backgrounds: Array<IBackground>;
        background: IBackground;
        productImages: Array<IProductImage>;
        productImage: IProductImage;
        productMeasures: Array<IProductMeasure>;
        productMeasure: IProductMeasure;
        product: IProduct;
    }

    export interface IImageComposerOptions {
        $containerElem: JQuery;
        vm: IImageComposerVM
    }

}