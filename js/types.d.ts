declare function jQuery(selector: string, context: Element): jQuery;
declare class jQuery {
    static fn: jQuery;
    fotorama(config: any): void;
}

declare class SimpleLightbox {
    constructor(element: ArrayLike<Element>, config: any);
}

interface Window {
    setLink(url: string): void;
}
