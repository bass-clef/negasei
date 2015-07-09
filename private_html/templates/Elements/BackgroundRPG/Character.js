
  
  /* Characterクラス */


function Character(x, y)
{
  var x, y, width, height, anix, aniy;
  this.chara = new Image();
  this.x = x;
  this.y = y;
}


/* 初期化 */
Character.prototype.src = function(src) {
  this.chara.src = src;
}


/* アクセサ */
/* getter */
Character.prototype.pos = function(x, y) {
  this.x = x;
  this.y = y;
}


/* 移動 */
Character.prototype.move = function() {

}
Character.prototype.left = function() {

}


/* 描画 */
Character.prototype.draw = function() {
  
  
}

