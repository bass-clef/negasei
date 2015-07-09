  

  /* ElementRectクラス */
  

/* HTML要素の位置サイズ保存用に */
function ElementRect()
{
  this.val = [];
}

ElementRect.prototype.clear = function() {
  this.val = [];
}

ElementRect.prototype.getName = function(left, top, right, bottom) {
  return ''+ left +','+ top +','+ right +','+ bottom +'';
}

ElementRect.prototype.register = function(left, top, right, bottom){
  var name = this.getName(left, top, right, bottom);
  if (undefined === this.val[this.getName(left, top, right, bottom)]) {
    this.val[name] = true;
  }
  return name;
}

ElementRect.prototype.isRegistered = function(left, top, right, bottom){
  return undefined !== this.val[this.getName(left, top, right, bottom)];
}
