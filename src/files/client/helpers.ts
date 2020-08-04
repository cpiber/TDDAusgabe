
function clone(obj: any) {
  if (null == obj || "object" != typeof obj) return obj;
  var copy = obj.constructor();
  for (var attr in obj) {
    if (obj.hasOwnProperty(attr)) copy[attr] = obj[attr];
  }
  return copy;
}


// Minimum-height for tabs
function tabH() {
  var b = document.getElementById('tab-body'),
    w = window.outerWidth,
    h = document.getElementById('tab-head').offsetHeight;
  if (w >= 1160) {
    b.style.minHeight = h + 'px';
  } else {
    b.style.minHeight = '';
  }
}

export { clone, tabH };