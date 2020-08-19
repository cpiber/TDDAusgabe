import { famdata } from "./client/js/familie_interfaces";

interface design {
  name: string,
  format?: string,
  elements?: designelement[],
}

interface designelement {
  html: string,
  position?: string,
  css?: string,
}

export interface familie extends famdata {
  Ortname?: string,
  Preis?: number,
  img?: string,
  isrc?: string,
}

export interface cardWindow extends Window {
  designs: design[],
  familie: familie,
  updateDesigns: () => void,
  updateCanvas: () => void,
}

declare const window : cardWindow;

window.designs = [];
window.familie = {};
window.updateDesigns = () => { };
window.updateCanvas = () => { };

const formats = {
  A3: [297, 420],
  A4: [210, 297],
  A5: [148, 210]
};
const pxConst = 3.7795275591;

window.onload = function () {
  const canvas = document.getElementById('testCanvas');
  const menu = document.getElementById('menu');

  const designs = document.getElementById('design-select') as HTMLSelectElement;
  const desChange = changeDesign.bind(designs, canvas) as () => void;
  window.updateCanvas = desChange;

  const updateDesigns = () => {
    designs.innerHTML = "";

    if (window.designs.length) {
      for (var i = 0; i < window.designs.length; i++) {
        const opt = document.createElement('option');
        opt.value = "" + i;
        const text = document.createTextNode(window.designs[i].name);
        opt.appendChild(text);
        designs.appendChild(opt);
      }
    }
    designs.value = "0";
    desChange();
  };
  window.updateDesigns = updateDesigns;
  updateDesigns();

  designs.addEventListener('change', desChange);

  const print = document.getElementById('drucken');
  print.addEventListener('click', function () {
    const b = canvas.style.border;
    canvas.style.border = "none";
    menu.style.display = "none";
    window.print();
    canvas.style.border = b;
    menu.style.display = "";
  });
};

function changeDesign(canvas: HTMLCanvasElement) {
  canvas.innerHTML = "";

  if (this.value === "") return;
  const design = window.designs[this.value];


  // Set canvas size
  const f = formats[design.format];
  const format = design.format;
  let matches: RegExpMatchArray;
  if (f && f.length) {
    canvas.style.height = (f[0] * pxConst) + "px";
    canvas.style.width = (f[1] * pxConst) + "px";
    canvas.style.border = "";
  } else if (format && (matches = format.match(/^(\d+)x(\d+)$/)) !== null) {
    canvas.style.height = (+matches[1] * pxConst) + "px";
    canvas.style.width = (+matches[2] * pxConst) + "px";
    canvas.style.border = "";
  } else {
    canvas.style.height = "";
    canvas.style.width = "";
    canvas.style.border = "none";
    if (format && format !== 'none' && format !== '') console.warn('Defaulting to no format');
  }

  // Add specified elements
  if (design.elements && design.elements.length) {
    for (let i = 0; i < design.elements.length; i++) {
      const element = design.elements[i];
      const div = document.createElement('div');
      
      let style = "";
      if (element.css) {
        style += element.css;
      }
      if (element.position && element.position.length === 2) {
        style += `;position:absolute;top:${+element.position[0] * pxConst}px;left:${+element.position[1] * pxConst}px`;
      }
      div.setAttribute('style', style);
      
      if (element.html) {
        const prop_reg = /(^|[^\\](?:\\\\)*)\$(\w*)/g;
        const esc_reg = /\\(.)/g;
        let inner_html = element.html;

        inner_html = inner_html.replace(prop_reg, (match, p1: string, p2: string) => {
          if (p2 in window.familie) return `${p1}${window.familie[p2] === null ? "" : window.familie[p2]}`;
          return match;
        });
        inner_html = inner_html.replace(esc_reg, "$1");

        div.innerHTML = inner_html;
      }

      canvas.appendChild(div);
    }
  }

  if (typeof (Event) === 'function') {
    // modern browsers
    window.dispatchEvent(new Event('resize'));
  } else {
    // for IE and other old browsers
    // causes deprecation warning on modern browsers
    var evt = window.document.createEvent('UIEvents');
    // @ts-ignore
    evt.initUIEvent('resize', true, false, window, 0);
    window.dispatchEvent(evt);
  }
}
