
  
  /* Mapクラス */


function Map()
{
  this.val = [];
  this.img = new Image();
  this.chara = new Character();
  this.reDraw = true;
}


/* 初期化 */
Map.prototype.init = function(x, y) {
  this.val[x][y] = {
    x:0,
    y:0,
    flag:0
  };
}
Map.prototype.src = function(src) {
  this.img.src = src;
}
Map.prototype.charaSrc = function(src) {
  this.chara.src(src);
}


/* アクセサ */
/* setter */
Map.prototype.set = function(){
  if (2 <= arguments.length) {
    x = arguments[0];
    y = arguments[1];
    if (undefined === this.val[x]) {
      this.val[x] = [];
    }
    if (undefined === this.val[x][y]) {
      this.init(x, y);
    }
  }
  switch(arguments.length){
  case 3:
    // set(x, y, flag);
    this.val[x][y].flag = arguments[2];
    break;

  case 4:
    // set(x, y, xId, yId);
    this.val[x][y].x = arguments[2];
    this.val[x][y].y = arguments[3];
    break;

  case 5:
    // set(x, y, xId, yId, flag);
    this.val[x][y].x = arguments[2];
    this.val[x][y].y = arguments[3];
    this.val[x][y].flag = arguments[4];
    break;
  }
  
  // 再描画を要請
  if (!this.reDraw) {
    this.reDraw = true;
    requestAnimationFrame(rpgDrawMain);
  }
}
/* getter */
Map.prototype.x = function(x, y) {
  if (undefined == this.val[x]) {
    return null;
  }
  if (undefined == this.val[x][y]) {
    return null;
  }
  return this.val[x][y].x;
}
Map.prototype.y = function(x, y) {
  if (undefined == this.val[x]) {
    return null;
  }
  if (undefined == this.val[x][y]) {
    return null;
  }
  return this.val[x][y].y;
}
Map.prototype.flag = function(x, y) {
  if (undefined == this.val[x]) {
    return null;
  }
  if (undefined == this.val[x][y]) {
    return null;
  }
  return this.val[x][y].flag;
}


/* 強制再描画 */
Map.prototype.forceReDraw = function() {
  this.reDraw = true;
  requestAnimationFrame(rpgDrawMain);
}


/* map塗りつぶし */
Map.prototype.fill = function(left, top, right, bottom, xId, yId, func) {
  for(var x=left; x<right; x++) {
    for(var y=top; y<bottom; y++) {
      if ("function" === typeof(func)) {
        if (func(this.val[x][y])) {
          this.set(x, y, xId, yId);
        }
      } else {
        this.set(x, y, xId, yId);
      }
    }
  }
}
Map.prototype.fillWithFlag = function(left, top, right, bottom, xId, yId, flag, func) {
  for(var x=left; x<right; x++) {
    for(var y=top; y<bottom; y++) {
      if ("function" === typeof(func)) {
        if (func(this.val[x][y])) {
          this.set(x, y, xId, yId, flag);
        }
      } else {
        this.set(x, y, xId, yId, flag);
      }
    }
  }
}


/* 任意の四角形をタイルで塗りつぶし */
Map.prototype.fillTile = function(left, top, right, bottom, xBase, yBase, flag) {
  var width = right-left;
  var height = bottom-top;
  if (width == height && 1 == width) {
    this.set(left, top, xBase, yBase, flag+MAPF_BACK_C);
    return;
  }

  // 頂点
  // 左上
  if (isBackTopMap(this.flag(left-1, top)) && isBackTopMap(this.flag(left-2, top))) {
    this.set(left-1, top, xBase+1, yBase+1, flag+MAPF_BACK_T);
    this.set(left, top, xBase+1, yBase+1, flag+MAPF_BACK_T);
  } else {
    this.set(left, top, xBase, yBase+1, flag+MAPF_BACK_LT);
  }

  // 右上
  if (isBackTopMap(this.flag(right-1, top)) && isBackTopMap(this.flag(right, top))) {
    this.set(right-1, top, xBase+1, yBase+1, flag+MAPF_BACK_T);
    this.set(right, top, xBase+1, yBase+1, flag)+MAPF_BACK_T;
  } else {
    this.set(right-1, top, xBase+2, yBase+1, flag+MAPF_BACK_RT);
  }

  // 右下
  if (flag+MAPF_BACK_LB == this.flag(right-1, bottom-1) && isBackBottomMap(this.flag(right, bottom-1))) {
    this.set(right-1, bottom-1, xBase+1, yBase+3, flag+MAPF_BACK_B);
    this.set(right, bottom-1, xBase+1, yBase+3, flag+MAPF_BACK_B);
  } else {
    this.set(right-1, bottom-1, xBase+2, yBase+3, flag+MAPF_BACK_RB);
    this.set(right-1, bottom, xBase+5, yBase+7, flag+MAPF_BACK_RB);
  }

  // 左下
  if (isBackBottomMap(this.flag(left-1, bottom-1)) && isBackBottomMap(this.flag(left-2, bottom-1))) {
    this.set(left-1, bottom-1, xBase+1, yBase+3, flag+MAPF_BACK_B);
    this.set(left, bottom-1, xBase+1, yBase+3, flag+MAPF_BACK_B);
  } else {
    this.set(left, bottom-1, xBase, yBase+3, flag+MAPF_BACK_LB);
    this.set(left, bottom, xBase+3, yBase+7, flag+MAPF_BACK_LB);
  }

  // 四辺
  if (2 < width) {
    for(var x=1; x<width-1; x++) {
      this.set(left+x, top, xBase+1, yBase+1, flag+MAPF_BACK_T);
      this.set(left+x, bottom-1, xBase+1, yBase+3, flag+MAPF_BACK_B);
      this.set(left+x, bottom, xBase+4, yBase+7, flag+MAPF_BACK_B);
    }
  }
  if (2 < height) {
    for(var y=1; y<height-1; y++) {
      this.set(left, top+y, xBase, yBase+2, flag+MAPF_BACK_L);
      this.set(right-1, top+y, xBase+2, yBase+2, flag+MAPF_BACK_R);
    }
  }

  // 中央
  if (2 < width && 2 < height) {
    this.fillWithFlag(left+1, top+1, left+width-1, top+height-1, xBase+1, yBase+2, flag+MAPF_BACK_C);
  }
}


/* 描画 */
Map.prototype.draw = function(ctx) {
  this.reDraw = false;
  // 背景
  for(var y=0; y<canvas.tipHeight; y++){
    for(var x=0; x<canvas.tipWidth; x++){
      if (undefined === this.val[x][y]) {
        continue;
      }
      ctx.drawImage(this.img, tip.width*this.x(x, y), tip.height*this.y(x, y), tip.width, tip.height, tip.width*x, tip.height*y, tip.width, tip.height);
    }
  }

  // キャラ
  this.chara.draw();
}
